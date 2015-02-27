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
use VJ\Core\Exception\InvalidArgumentException;
use VJ\Core\Exception\UserException;
use VJ\VJ;

class UserCredential
{
    public $password_encoder;
    public $rememberme_encoder;

    /**
     * @param PasswordEncoder $password_encoder
     * @param RememberMeEncoder $rememberme_encoder
     */
    public function __construct(
        PasswordEncoder $password_encoder,
        RememberMeEncoder $rememberme_encoder
    ) {
        $this->password_encoder = $password_encoder;
        $this->rememberme_encoder = $rememberme_encoder;
    }

    /**
     * 检查密码是否正确
     *
     * @param string $field
     * @param string $password
     * @param bool $secretly
     * @return array
     * @throws UserException
     */
    public function checkPasswordCredential($field, $password, $secretly = false)
    {
        if (Validator::email()->validate($field)) {
            $user = UserUtil::getUserObjectByEmail($field);
        } else {
            $user = UserUtil::getUserByUsername($field);
        }

        if (!UserUtil::isUserObjectValid($user)) {
            if (!$secretly) {
                Application::emit('user.login.failed.user_invalid', [VJ::LOGIN_TYPE_FAILED_USER_INVALID, $field]);
                Application::info('credential.login.not_found', ['login' => $field]);
            }
            throw new UserException('UserCredential.checkPasswordCredential.user_not_valid');
        }

        $verified = $this->password_encoder->verify($password, $user['salt'], $user['hash']);
        if (!$verified) {
            if (!$secretly) {
                Application::emit('user.login.failed.wrong_password', [VJ::LOGIN_TYPE_FAILED_WRONG_PASSWORD, $user]);
                Application::info('credential.login.wrong_password', ['uid' => $user['uid']]);
            }
            throw new UserException('UserCredential.checkPasswordCredential.wrong_password');
        }

        if (!$secretly) {
            Application::emit('user.login.succeeded', [VJ::LOGIN_TYPE_INTERACTIVE, $user, $field, $password]);
            Application::info('credential.login.ok', ['uid' => $user['uid']]);
        }
        return $user;
    }

    /**
     * 检查记忆令牌是否正确
     *
     * @param string $clientToken
     * @param bool $secretly
     * @return array
     * @throws UserException
     */
    public function checkCookieTokenCredential($clientToken, $secretly = false)
    {
        try {
            $token = $this->rememberme_encoder->parseClientToken($clientToken);
        } catch (InvalidArgumentException $e) {
            throw new UserException('UserCredential.checkCookieTokenCredential.invalid_rememberme_token');
        }

        $record = Application::coll('RememberMeToken')->findOne([
            'uid' => $token['uid'],
            'token' => $token['token'],
        ]);

        if ($record === null) {
            throw new UserException('UserCredential.checkCookieTokenCredential.invalid_rememberme_token');
        }

        if ($record['expireat']->sec < time()) {
            throw new UserException('UserCredential.checkCookieTokenCredential.invalid_rememberme_token');
        }

        $user = UserUtil::getUserObjectByUid($record['uid']);

        if (!UserUtil::isUserObjectValid($user)) {
            throw new UserException('UserCredential.checkCookieTokenCredential.user_not_valid');
        }

        if (!$secretly) {
            Application::emit('user.login.succeeded', [VJ::LOGIN_TYPE_COOKIE, $user]);
            Application::info('credential.login.ok', ['uid' => $user['uid']]);
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
     * @throws InvalidArgumentException
     */
    public function createRememberMeClientToken($uid, $ip, $userAgent, $expire)
    {
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }
        if (!Validator::int()->validate($expire)) {
            throw new InvalidArgumentException('expire', 'type_invalid');
        }

        $clientToken = $this->rememberme_encoder->generateClientToken((int)$uid, (int)$expire);
        $token = $this->rememberme_encoder->parseClientToken($clientToken);

        if (!is_string($userAgent) || !mb_check_encoding($userAgent, 'UTF-8')) {
            $userAgent = null;
        }

        Application::coll('RememberMeToken')->insert([
            'uid' => $token['uid'],
            'token' => $token['token'],
            'ua' => $userAgent,
            'ip' => $ip,
            'at' => new \MongoDate(),
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
    public function invalidateRememberMeClientToken($clientToken)
    {
        try {
            $token = $this->rememberme_encoder->parseClientToken($clientToken);

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

    /**
     * 设置用户密码
     *
     * @param int $uid
     * @param string $password
     * @return bool
     * @throws InvalidArgumentException
     */
    public function setCredential($uid, $password)
    {
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }

        $newHashSaltPair = $this->password_encoder->generateHash($password);
        $status = Application::coll('User')->update([
            'uid' => (int)$uid
        ], [
            '$set' => [
                'hash' => $newHashSaltPair['hash'],
                'salt' => $newHashSaltPair['salt'],
            ]
        ]);

        if ($status['n'] === 1) {
            Application::info('credential.set', ['uid' => $uid]);
            return true;
        } else {
            return false;
        }
    }
}