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
    public static function getGlobalDomainId()
    {
        return new \MongoId(VJ::DOMAIN_GLOBAL);
    }

    /**
     * 根据域对象判断域是否有效
     *
     * @param array $domain
     * @return bool
     */
    public static function isDomainObjectValid(array $domain = null)
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
    public static function isGlobalDomainId(\MongoId $domainId)
    {
        return $domainId->id === VJ::DOMAIN_GLOBAL;
    }

    /**
     * 加入域
     *
     * @param int $uid
     * @param \MongoId $domainId
     * @return bool
     * @throws InvalidArgumentException
     * @throws UserException
     */
    public static function joinDomainById($uid, \MongoId $domainId)
    {
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }
        $uid = (int)$uid;

        if (!self::isGlobalDomainId($domainId)) {
            // 检查域是否存在
            $d = Application::coll('Domain')->findOne(['_id' => $domainId]);
            if (!self::isDomainObjectValid($d)) {
                throw new UserException('DomainManager::joinDomain.invalid_domain');
            }
        }

        // 检查用户是否存在
        $user = UserManager::getUserObjectByUid($uid);
        if (!UserManager::isUserObjectValid($user)) {
            throw new UserException('DomainManager::joinDomain.invalid_user');
        }

        // 添加 MEMBER 角色
        Application::coll('UserRole')->update([
            'uid' => $uid
        ], [
            '$addToSet' => [
                'd.' . $domainId->id => 'DOMAIN_MEMBER'
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
        if (self::isGlobalDomainId($domainId)) {
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
        if (!self::isGlobalDomainId($domainId)) {
            Application::coll('DomainLog')->insert([
                'uid' => RoleManager::getCurrentToken(),
                'at' => new \MongoDate(),
                'type' => 'join',
                'ua' => Util::getUserAgentSafe(),
                'ip' => Util::getClientIp(),
                'target_uid' => $uid,
                'target_domain' => $domainId,
            ]);
        }

        return true;
    }
}