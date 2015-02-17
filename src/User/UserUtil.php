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

class UserUtil
{
    /**
     * @param int $uid
     * @return array|null
     */
    public static function getUserObjectByUid($uid)
    {
        if (!Validator::int()->validate($uid)) {
            return null;
        }
        $user = Application::coll('User')->findOne(['uid' => (int)$uid]);
        return $user;
    }

    /**
     * @param string $email
     * @return array|null
     */
    public static function getUserObjectByEmail($email)
    {
        if (!is_string($email) || !mb_check_encoding($email, 'UTF-8')) {
            return null;
        }
        $user = Application::coll('User')->findOne(['lmail' => self::canonicalizeEmail($email)]);
        return $user;
    }

    /**
     * @param string $username
     * @return array|null
     */
    public static function getUserByUsername($username)
    {
        if (!is_string($username) || !mb_check_encoding($username, 'UTF-8')) {
            return null;
        }
        $user = Application::coll('User')->findOne(['luser' => self::canonicalizeUsername($username)]);
        return $user;
    }

    /**
     * 判断用户是否有效（未被封禁）
     *
     * @param array $user
     * @return bool
     */
    public static function isUserObjectValid(array $user = null)
    {
        if ($user === null || (isset($user['banned']) && $user['banned'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $username
     * @return string
     */
    public static function canonicalizeUsername($username)
    {
        return trim(strtolower($username));
    }

    /**
     * @param string $email
     * @return string
     */
    public static function canonicalizeEmail($email)
    {
        // TODO: Gmail-like canonicalize
        return trim(strtolower($email));
    }

}