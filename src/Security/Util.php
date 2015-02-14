<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Security;

use VJ\Core\Application;

class Util
{
    /**
     * timing attack 安全的字符串比较
     *
     * @param string $a
     * @param string $b
     * @return bool
     */
    public static function slowEquals($a, $b)
    {
        $diff = strlen($a) ^ strlen($b);
        for ($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $diff === 0;
    }

    /**
     * 获取 UserAgent
     *
     * @return string|null
     */
    public static function getUserAgent()
    {
        return Application::get('request')->headers->get('user-agent');
    }

    /**
     * 获取数据库插入安全的 UserAgent
     *
     * @return string|null
     */
    public static function getUserAgentSafe()
    {
        return mb_substr(self::getUserAgent(), 0, 256);
    }

    /**
     * 获取 IP
     *
     * @return string
     */
    public static function getClientIp()
    {
        return Application::get('request')->getClientIp();
    }
} 