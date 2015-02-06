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
use VJ\VJ;

class LoginLogService
{
    public function onEvent($event, $type, $user)
    {
        $ip = Application::get('request')->getClientIp();
        $userAgent = Application::get('request')->headers->get('user-agent');

        if ($event === 'user.login.succeeded') {
            Application::info('login.attempt.successful: uid=' . $user['_id'] . ', IP=' . $ip . ', UserAgent=' . $userAgent);
        } else {
            if ($type == VJ::LOGIN_TYPE_FAILED_WRONG_PASSWORD) {
                Application::info('login.attempt.failed: uid=' . $user['_id'] . ', IP=' . $ip . ', UserAgent=' . $userAgent);
            } else {
                Application::info('login.attempt.not_found: login=' . $user . ', IP=' . $ip . ', UserAgent=' . $userAgent);
            }
        }

        if (
            $type == VJ::LOGIN_TYPE_INTERACTIVE
            || $type == VJ::LOGIN_TYPE_COOKIE
            || $type == VJ::LOGIN_TYPE_FAILED_WRONG_PASSWORD
        ) {
            $this->appendLog($user['_id'], $type, $userAgent, $ip);
        }
    }

    /**
     * 插入一条登录记录
     *
     * @param int $uid
     * @param int $type
     * @param string $ua
     * @param string $ip
     * @return bool
     */
    public function appendLog($uid, $type, $ua, $ip)
    {
        Application::coll('LoginLog')->insert([
            'uid' => (int)$uid,
            'at' => new \MongoDate(),
            'type' => (int)$type,
            'ua' => $ua,
            'ip' => $ip
        ]);

        return true;
    }
}