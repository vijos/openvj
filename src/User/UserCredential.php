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

class UserCredential
{
    /**
     * 检查密码是否正确
     *
     * @param string $field
     * @param string $password
     * @return array
     * @throws UserException
     */
    public static function checkPasswordCredential($field, $password)
    {
        if (Validator::email()->validate($field)) {
            $user = UserManager::getUserByEmail($field);
        } else {
            $user = UserManager::getUserByUsername($field);
        }

        if (!UserManager::isUserValid($user)) {
            throw new UserException('error.checkCredential.user_not_valid');
        }

        $verified = PasswordEncoder::verify($password, $user['salt'], $user['hash']);
        if (!$verified) {
            throw new UserException('error.checkCredential.wrong_password');
        }

        return $user;
    }

    /**
     * 检查记忆令牌是否正确
     *
     * @param string $clientToken
     * @return array
     * @throws UserException
     */
    public static function checkCookieTokenCredential($clientToken)
    {
        try {
            $token = RememberMeEncoder::parseClientToken($clientToken);
        } catch (\InvalidArgumentException $e) {
            throw new UserException('error.checkCredential.invalid_rememberme_token');
        }

        $record = Application::coll('RememberMeToken')->findOne([
            'uid' => (int)$token['uid'],
            'token' => $token['token'],
        ]);

        if ($record === null) {
            throw new UserException('error.checkCredential.invalid_rememberme_token');
        }

        if ($record['expireat']->sec <= time()) {
            throw new UserException('error.checkCredential.invalid_rememberme_token');
        }

        $user = UserManager::getUserByUid($record['uid']);

        if (!UserManager::isUserValid($user)) {
            throw new UserException('error.checkCredential.user_not_valid');
        }

        return $user;
    }

    /**
     * 创建一个记忆令牌
     *
     * @param int $uid
     * @param string $ip
     * @param string $userAgent
     * @param int $expire
     * @return string
     */
    public static function createRememberMeClientToken($uid, $ip, $userAgent, $expire)
    {
        $clientToken = RememberMeEncoder::generateClientToken((int)$uid, (int)$expire);
        $token = RememberMeEncoder::parseClientToken($clientToken);

        Application::coll('RememberMeToken')->insert([
            'uid' => $token['uid'],
            'token' => $token['token'],
            'ua' => $userAgent,
            'ip' => $ip,
            'expireat' => new \MongoDate((int)$expire),
        ]);

        return $clientToken;
    }

    /**
     * 使一个记忆令牌无效
     *
     * @param string $clientToken
     * @return bool
     */
    public static function invalidateRememberMeClientToken($clientToken)
    {
        try {
            $token = RememberMeEncoder::parseClientToken($clientToken);

            Application::coll('RememberMeToken')->remove([
                'uid' => $token['uid'],
                'token' => $token['token'],
            ], [
                'justOne' => true
            ]);
            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }
}