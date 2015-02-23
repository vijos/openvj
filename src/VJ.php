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

    const COMMENT_PAGE_SIZE = 100;
    const COMMENT_MIN = 3;
    const COMMENT_MAX = 300 * 1000; // 300 KB~900 KB

    const PROBLEM_TITLE_MIN = 1;
    const PROBLEM_TITLE_MAX = 30;

    const PROBLEM_CONTENT_MIN = 10;
    const PROBLEM_CONTENT_MAX = 300 * 1000; // 300*1000 characters, 300 KB~900 KB

    const TAG_MIN = 1;
    const TAG_MAX = 15;

    const RANDOM_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    const DOMAIN_GLOBAL = '000000000000000000000000';

    public static function bootstrap()
    {
        Application::Instance();
        Application::get('templating')->addFunction(new \Twig_SimpleFunction('url', function ($relative) {
            return VJ::makeUrl($relative);
        }));
    }

    /**
     * 生成完整 URL 地址
     *
     * @param string $relative
     * @return string
     */
    public static function makeUrl($relative)
    {
        if (Application::getConfig('security.enforce_https')) {
            $prefix = 'https://';
        } else {
            $prefix = 'http://';
        }
        return $prefix . Application::getConfig('canonical') . $relative;
    }

    /**
     * 移除字符串中不适合出现在 URL 中的符号
     *
     * @param string $str
     * @return string
     */
    public static function removeUrlUnsafeChar($str)
    {
        $str = preg_replace('/\s/g', '', $str);
        $str = str_replace([
            '&',
            '$',
            '+',
            ',',
            '/',
            '\\',
            ':',
            ';',
            '=',
            '?',
            '@',
            '#',
            '<',
            '>',
            '[',
            ']',
            '{',
            '}',
            '|',
            '*',
            '!',
            '"',
            "'",
            '^',
            '~',
            '%',
            '(',
            ')'
        ], '', $str);
        return $str;
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