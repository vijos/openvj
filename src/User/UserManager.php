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

use Symfony\Component\HttpFoundation\Cookie;
use VJ\Core\Application;
use VJ\Core\Exception\UserException;
use VJ\VJ;

class UserManager
{
    /**
     * @param int $uid
     * @return array|null
     */
    public static function getUserByUid($uid)
    {
        $user = Application::coll('User')->findOne(['_id' => (int)$uid]);
        return $user;
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
        if ($user === null ||
            (isset($user['banned']) && $user['banned']) ||
            (isset($user['deleted']) && $user['deleted'])
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 成功登录后初始化 session
     *
     * @param array $user
     * @param int $from
     */
    private static function prepareLoginSession($user, $from)
    {
        Application::getSession()->set('user', $user);
        Application::getSession()->set('loginType', $from);
    }

    /**
     * 用户名密码登录
     *
     * @param string $usernameEmail
     * @param string $password
     * @param bool $remember
     * @return array
     * @throws UserException
     */
    public static function interactiveLogin($usernameEmail, $password, $remember = false)
    {
        $user = UserCredential::checkPasswordCredential($usernameEmail, $password);
        if ($remember) {
            self::generateRememberMeToken($user);
        }

        self::prepareLoginSession($user, VJ::LOGIN_TYPE_INTERACTIVE);
        Application::emit('user.login.succeeded', [VJ::LOGIN_TYPE_INTERACTIVE, $user, $usernameEmail, $password]);

        return $user;
    }

    /**
     * Cookie 已记忆会话登录
     *
     * @return array
     * @throws UserException
     */
    public static function rememberMeTokenLogin()
    {
        $token_field = Application::get('config')['session']['remember_token'];
        $clientToken = Application::get('request')->cookies->get($token_field);
        try {
            $user = UserCredential::checkCookieTokenCredential($clientToken);
        } catch (UserException $e) {
            // 对于无效 token 需要删除 cookie
            self::invalidateRememberMeToken();
            throw $e;
        }

        // 对于有效 token，需要重新生成一份新 token，并继承其过期时间
        $token = RememberMeEncoder::parseClientToken($clientToken);
        self::invalidateRememberMeToken();
        self::generateRememberMeToken($user, $token['expire']);

        self::prepareLoginSession($user, VJ::LOGIN_TYPE_COOKIE);
        Application::emit('user.login.succeeded', [VJ::LOGIN_TYPE_COOKIE, $user]);

        return $user;
    }

    /**
     * 无效化已记忆会话
     */
    public static function invalidateRememberMeToken()
    {
        $token_field = Application::get('config')['session']['remember_token'];
        $clientToken = Application::get('request')->cookies->get($token_field);
        if ($clientToken !== null) {
            UserCredential::invalidateRememberMeClientToken($clientToken);
        }
        Application::get('request')->cookies->remove($token_field);
        Application::get('response')->headers->clearCookie($token_field);
    }

    /**
     * 创建一个记忆会话
     * @param array $user
     * @param int|null $expire
     */
    public static function generateRememberMeToken($user, $expire = null)
    {
        $token_field = Application::get('config')['session']['remember_token'];
        if ($expire === null) {
            $expire = time() + (int)Application::get('config')['session']['remember_ttl'];
        }
        $clientToken = UserCredential::createRememberMeClientToken($user['_id'],
            Application::get('request')->getClientIp(),
            Application::get('request')->headers->get('user-agent'),
            $expire
        );
        Application::get('request')->cookies->set($token_field, $clientToken);
        Application::get('response')->headers->setCookie(new Cookie($token_field, $clientToken));
    }

} 