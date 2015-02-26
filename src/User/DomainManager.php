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
use VJ\Core\Request;
use VJ\VJ;

class DomainManager
{
    /** @var UserSession $session */
    private $session;
    /** @var Request $request */
    private $request;

    /**
     * @param UserSession $session
     * @param Request $request
     */
    public function __construct(UserSession $session, Request $request)
    {
        $this->session = $session;
        $this->request = $request;
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
    public function joinDomainById($uid, \MongoId $domainId)
    {
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }
        $uid = (int)$uid;

        if (!DomainUtil::isGlobalDomainId($domainId)) {
            // 检查域是否存在
            $d = Application::coll('Domain')->findOne(['_id' => $domainId]);
            if (!DomainUtil::isDomainObjectValid($d)) {
                throw new UserException('DomainManager.joinDomain.invalid_domain');
            }
        }

        // 检查用户是否存在
        $user = UserUtil::getUserObjectByUid($uid);
        if (!UserUtil::isUserObjectValid($user)) {
            throw new UserException('DomainManager.joinDomain.invalid_user');
        }

        // 添加 MEMBER 角色
        Application::coll('UserRole')->update([
            'uid' => $uid
        ], [
            '$addToSet' => [
                'd.' . (string)$domainId => 'DOMAIN_MEMBER'
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
        if (DomainUtil::isGlobalDomainId($domainId)) {
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
        if (!DomainUtil::isGlobalDomainId($domainId)) {
            Application::coll('DomainLog')->insert([
                'uid' => $this->session->getCurrentToken(),
                'at' => new \MongoDate(),
                'type' => 'join',
                'ua' => $this->request->getUserAgent(),
                'ip' => $this->request->getClientIp(),
                'target_uid' => $uid,
                'target_domain' => $domainId,
            ]);
        }

        return true;
    }
}