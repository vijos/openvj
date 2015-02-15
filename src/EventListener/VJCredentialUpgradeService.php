<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\EventListener;

use VJ\Core\Application;
use VJ\Core\Event\GenericEvent;
use VJ\User\PasswordEncoder;
use VJ\User\UserCredential;
use VJ\User\UserManager;
use VJ\VJ;

class VJCredentialUpgradeService
{
    public function onEvent(GenericEvent $event, $type, $user, $field = null, $password = null)
    {
        if ($type !== VJ::LOGIN_TYPE_INTERACTIVE) {
            return;
        }

        // seamlessly upgrade VJ2 password hash to VJ3
        $this->upgradeUserCredential($user['uid'], $password);
    }

    /**
     * VJ2 登录哈希到 OpenVJ 版本登录哈希
     *
     * @param int $uid
     * @param string $password
     * @return bool
     */
    public function upgradeUserCredential($uid, $password)
    {
        $user = UserManager::getUserObjectByUid($uid);
        if ($user === null) {
            return false;
        }

        if (!PasswordEncoder::isOutdated($user['hash'])) {
            return false;
        }

        Application::info('credential.upgrade', ['uid' => $user['uid']]);
        return UserCredential::setCredential($uid, $password);
    }
}