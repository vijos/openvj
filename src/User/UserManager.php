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
use VJ\Core\Exception\UserException;

class UserManager
{
    /**
     * @param string $field
     * @param string $password
     * @return bool
     * @throws UserException
     */
    public static function checkPasswordCredential($field, $password)
    {
        if (Validator::email()->validate($field)) {
            $user = self::getUserByEmail($field);
        } else {
            $user = self::getUserByUsername($field);
        }

        if ($user === null) {
            throw new UserException('error.checkCredential.user_not_found');
        }

        if (!self::isUserValid($user)) {
            throw new UserException('error.checkCredential.user_not_valid');
        }

        $verified = PasswordEncoder::verify($password, $user['salt'], $user['hash']);
        return $verified;
    }

    /**
     * @param string $email
     * @return array|null
     */
    public static function getUserByEmail($email)
    {
        $user = Application::coll('User')->findOne(['lmail' => EmailCanonicalizer::canonicalize($email)]);
        return $user;
    }

    /**
     * @param string $username
     * @return array|null
     */
    public static function getUserByUsername($username)
    {
        $user = Application::coll('User')->findOne(['luser' => UsernameCanonicalizer::canonicalize($username)]);
        return $user;
    }

    /**
     * 判断用户是否有效（未被删除、未被封禁）
     *
     * @param array $user
     * @return bool
     */
    public static function isUserValid($user)
    {
        if ($user === null || $user['banned'] || $user['deleted']) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $clientToken
     * @return array|bool
     */
    public static function checkCookieTokenCredential($clientToken)
    {
        try {
            $token = RememberMeEncoder::parseClientToken($clientToken);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        $record = Application::coll('RememberMeToken')->findOne([
            'uid' => (int)$token['uid'],
            'token' => $token['token']
        ]);

        if ($record === null) {
            return false;
        }

        if ($record['expireat']->sec > time()) {
            return false;
        }

        return $record;
    }
} 