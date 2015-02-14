<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\User;

use Respect\Validation\Validator;
use VJ\Core\Application;
use VJ\Core\Exception\InvalidArgumentException;
use VJ\Core\Exception\UserException;
use VJ\Util;
use VJ\VJ;

class DomainManager
{
    /**
     * 获得全局域的 ID
     *
     * @return \MongoId
     */
    public static function getGlobalDomain()
    {
        return new \MongoId(VJ::DOMAIN_GLOBAL);
    }

    /**
     * 根据域对象判断域是否有效
     *
     * @param array $domain
     * @return bool
     */
    public static function isDomainValid(array $domain = null)
    {
        if ($domain === null || (isset($domain['invalid']) && $domain['invalid'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 判断是否是全局域 ID
     *
     * @param \MongoId $domain
     * @return bool
     */
    public static function isGlobalDomain(\MongoId $domain)
    {
        return $domain->id === VJ::DOMAIN_GLOBAL;
    }

    /**
     * 加入域
     *
     * @param int $uid
     * @param \MongoId $domain
     * @return bool
     * @throws InvalidArgumentException
     * @throws UserException
     */
    public static function joinDomain($uid, \MongoId $domain)
    {
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }
        $uid = (int)$uid;

        if (!self::isGlobalDomain($domain)) {
            // 检查域是否存在
            $d = Application::coll('Domain')->findOne(['_id' => $domain]);
            if (!self::isDomainValid($d)) {
                throw new UserException('DomainManager::joinDomain.invalid_domain');
            }
        }

        // 检查用户是否存在
        $user = UserManager::getUserByUid($uid);
        if (!UserManager::isUserValid($user)) {
            throw new UserException('DomainManager::joinDomain.invalid_user');
        }

        // 添加 MEMBER 角色
        Application::coll('UserRole')->update([
            'uid' => $uid
        ], [
            '$addToSet' => [
                'd.' . $domain->id => 'DOMAIN_MEMBER'
            ]
        ], [
            'upsert' => true
        ]);

        // 创建空资料
        $document = [
            'pref' => new \stdClass(),
            'rp' => 0.0,
            'rp_s' => 0.0,
            'rank' => -1,
            'level' => 0,
        ];
        if (self::isGlobalDomain($domain)) {
            $document += [
                'sig' => '',
                'sigraw' => '',
                'contacts' => [],
            ];
        }
        Application::coll('UserInfo')->update([
            'uid' => $uid,
            'domain' => new \MongoId(VJ::DOMAIN_GLOBAL)
        ], [
            '$setOnInsert' => $document
        ], [
            'upsert' => true
        ]);

        // 操作非全局域则插入操作记录
        if (!self::isGlobalDomain($domain)) {
            Application::coll('DomainLog')->insert([
                'uid' => RoleManager::getCurrentToken(),
                'at' => new \MongoDate(),
                'type' => 'join',
                'ua' => Util::getUserAgentSafe(),
                'ip' => Util::getClientIp(),
                'target_uid' => $uid,
                'target_domain' => $domain,
            ]);
        }

        return true;
    }
}