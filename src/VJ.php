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

class VJ
{
    const LOGIN_TYPE_INTERACTIVE = 0;
    const LOGIN_TYPE_COOKIE = 1;
    const LOGIN_TYPE_FAILED_WRONG_PASSWORD = 50;
    const LOGIN_TYPE_FAILED_USER_INVALID = 51;

    const USER_GENDER_UNKNOWN = 0;
    const USER_GENDER_MALE = 1;
    const USER_GENDER_FEMAIL = 2;
    const USER_GENDER_OTHER = 3;

    const USER_ID_GUEST = 0;
    const USER_ID_SYSTEM = 1;

    const DOMAIN_GLOBAL = '000000000000000000000000';

    public static function bootstrap()
    {
        Application::Instance();
    }

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
}