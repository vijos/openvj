<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\User\Importer;

use VJ\Core\Application;
use VJ\Core\Exception\UserException;
use VJ\ImporterInterface;
use VJ\User\UserUtil;
use VJ\VJ;

class VijosImporter implements ImporterInterface
{
    private $source;

    public function __construct(\MongoDB $source)
    {
        $this->source = $source;
    }

    /**
     * 导入 Vijos 数据库用户表
     *
     * @param callable $progress
     * @param callable $done
     * @return bool
     * @throws UserException
     */
    public function import(callable $progress = null)
    {
        if (Application::coll('User')->count() > 0) {
            throw new UserException('User.Importer.VijosImporter.userCollectionNotEmpty');
        }

        $MAX_USER_ID = 1;

        $cursor = $this->source->selectCollection('User')->find()->sort([
            '_id' => 1
        ]);

        foreach ($cursor as $user) {
            $_id = new \MongoId();
            $uid = (int)$user['_id'];

            if ($uid > $MAX_USER_ID) {
                $MAX_USER_ID = $uid;
            }

            // vijos 中，username 被 escape 过
            $user['user'] = htmlspecialchars_decode($user['user']);

            if (is_callable($progress)) {
                $progress($uid, $user['user']);
            }

            $doc = [
                '_id' => $_id,
                'uid' => $uid,
                'user' => $user['user'],
                'luser' => UserUtil::canonicalizeUsername($user['user']),
                'g' => $user['g'],
                'gender' => (int)$user['sex'] + 1,  // vijos 中 0=male, 1=female, 2=other
                'regat' => (int)$user['treg'],
                'regip' => $user['ipreg'],
                'loginat' => (int)$user['tlogin'],
                'loginip' => '255.255.255.255'    // TODO: 根据 LoginLog 查找 last login ip
            ];

            // 检查是否有 Email 重叠
            $count = $this->source->selectCollection('User')->find([
                'lmail' => mb_strtolower($user['mail'], 'UTF-8')
            ])->count();
            if ($count > 1) {
                $doc['mail'] = Application::get('random')->generateString(20, VJ::RANDOM_CHARS) . '@openvj';
                $doc['lmail'] = mb_strtolower($doc['mail'], 'UTF-8');
                $doc['omail'] = $user['mail'];
            } else {
                $doc['mail'] = $user['mail'];
                $doc['lmail'] = UserUtil::canonicalizeEmail($doc['mail']);
                $doc['salt'] = $user['salt'];
                $doc['hash'] = 'vj2|' . base64_encode(mb_strtolower($user['user'], 'UTF-8')) . '|' . $user['pass'];
            }

            try {
                Application::coll('User')->insert($doc);
                Application::emit('user.created', [$uid]);
            } catch (\MongoCursorException $e) {
                // TODO: Duplicate user
            }
        }

        // Update UID counter
        Application::coll('System')->update([
            '_id' => 'UserCounter'
        ], [
            '$set' => [
                'count' => $MAX_USER_ID + 1
            ]
        ], [
            'upsert' => true
        ]);

        return true;
    }
}