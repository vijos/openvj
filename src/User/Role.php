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

class Role
{
    private $roles;

    /**
     * @param int|null $uid
     */
    public function __construct($uid = null)
    {
        if ($uid === null || !Validator::int()->validate($uid)) {
            $this->roles = [];
            return;
        }

        $data = Application::coll('UserRole')->findOne([
            'uid' => (int)$uid
        ]);

        if ($data === null) {
            $this->roles = [];
            return;
        }

        $this->roles = $data['d'];
    }

    /**
     * 得到该用户所有角色
     *
     * @param \MongoId $domainId
     * @param bool $isResourceOwner
     * @return array
     */
    public function getRoles(\MongoId $domainId, $isResourceOwner = false)
    {
        $domainId = (string)$domainId;
        if (!isset($this->roles[$domainId])) {
            $roles = [];
        } else {
            $roles = $this->roles[$domainId];
        }
        $roles[] = 'EVERYONE';
        if ($isResourceOwner) {
            $roles[] = 'OWNER';
        }

        return $roles;
    }

    /**
     * 是否拥有一项权限（白名单）
     *
     * @param string $permissionName
     * @param \MongoId $domainId
     * @param bool $isResourceOwner
     * @return bool
     */
    public function hasPermission($permissionName, \MongoId $domainId, $isResourceOwner = false)
    {
        $roles = $this->getRoles($domainId, $isResourceOwner);

        $result = Application::coll('PermissionAllow')->findOne([
            'domain' => $domainId,
            'val' => $permissionName,
            'role' => ['$in' => $roles],
        ]);

        return ($result !== null);
    }
}