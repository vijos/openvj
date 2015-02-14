<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ;

use VJ\Core\Application;

class Util
{
    /**
     * 移除字符串中的 emoji
     *
     * @param string $str
     * @return string
     */
    public static function removeEmoji($str)
    {
        $str = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $str);
        $str = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $str);
        $str = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $str);
        $str = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $str);
        $str = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $str);
        return $str;
    }

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