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
use VJ\Core\Exception\Exception;
use VJ\Core\Exception\InvalidArgumentException;
use VJ\Core\Exception\UserException;
use VJ\VJ;

class UserCredential
{
    public $password_encoder;

    /**
     * @param PasswordEncoder $password_encoder
     */
    public function __construct(PasswordEncoder $password_encoder) {
        $this->password_encoder = $password_encoder;
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
            $user = UserUtil::getUserObjectByUsername($field);
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
     * 检查 记住我 token 是否正确
     *
     * @param string $clientToken
     * @param bool $secretly
     * @return array
     * @throws UserException
     */
    public function checkRememberMeTokenCredential($token, $secretly = false)
    {
        try {
            $tokenRec = Application::get('token_manager')->find('rememberme', $token);

            if ($tokenRec === null) {
                throw new UserException('UserCredential.checkRememberMeTokenCredential.invalid_rememberme_token');
            }

            //是否需要检查 user-agent 和 ip 地址呢

            $user = UserUtil::getUserObjectByUid($tokenRec['data']['uid']);

            if (!UserUtil::isUserObjectValid($user)) {
                throw new UserException('UserCredential.checkRememberMeTokenCredential.user_not_valid');
            }

            if (!$secretly) {
                Application::emit('user.login.succeeded', [VJ::LOGIN_TYPE_TOKEN, $user]);
                Application::info('credential.login.ok', ['uid' => $user['uid']]);
            }

            return $user;
        } catch (InvalidArgumentException $e) {
            throw new UserException('UserCredential.checkRememberMeTokenCredential.invalid_rememberme_token');
        }
    }

    /**
     * 创建一个 记住我 token
     *
     * @param int $uid
     * @param string $ip
     * @param string $userAgent
     * @param int $expireAt
     * @return string
     * @throws InvalidArgumentException
     */
    public function createRememberMeToken($uid, $ip, $userAgent, $expireAt)
    {
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }
        if (!Validator::int()->validate($expireAt)) {
            throw new InvalidArgumentException('expireAt', 'type_invalid');
        }

        if (!is_string($userAgent) || !mb_check_encoding($userAgent, 'UTF-8')) {
            $userAgent = null;
        }

        if (!is_string($ip) || !mb_check_encoding($ip, 'UTF-8')) {
            $ip = null;
        }

        $tokenMixed = Application::get('token_manager')->generate('rememberme', null, $expireAt, [
            'uid' => $uid,
            'ua' => $userAgent,
            'ip' => $ip,
            'at' => new \MongoDate()
        ]);

        return $tokenMixed['token'];
    }

    /**
     * 使一个 记住我 token 无效
     *
     * @param string $clientToken
     * @return bool
     */
    public function invalidateRememberMeToken($token)
    {
        try {
            return Application::get('token_manager')->invalidate('rememberme', $token);
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