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
use Symfony\Component\HttpFoundation\Cookie;
use VJ\Core\Application;
use VJ\Core\Exception\InvalidArgumentException;
use VJ\Core\Exception\UserException;
use VJ\Util;
use VJ\VJ;

class UserManager
{
    /**
     * @param int $uid
     * @return array|null
     */
    public static function getUserByUid($uid)
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
    public static function getUserByEmail($email)
    {
        if (!is_string($email) || !mb_check_encoding($email, 'UTF-8')) {
            return null;
        }
        $user = Application::coll('User')->findOne(['lmail' => EmailCanonicalizer::canonicalize($email)]);
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
        $user = Application::coll('User')->findOne(['luser' => UsernameCanonicalizer::canonicalize($username)]);
        return $user;
    }

    /**
     * 判断用户是否有效（未被封禁）
     *
     * @param array $user
     * @return bool
     */
    public static function isUserValid(array $user = null)
    {
        if ($user === null || (isset($user['banned']) && $user['banned'])) {
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
    private static function prepareLoginSession(array $user, $from)
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
        if (!is_string($usernameEmail)) {
            throw new InvalidArgumentException('usernameEmail', 'type_invalid');
        }
        if (!is_string($password)) {
            throw new InvalidArgumentException('password', 'type_invalid');
        }
        $user = UserCredential::checkPasswordCredential($usernameEmail, $password);
        if ($remember) {
            self::generateRememberMeToken($user);
        }

        self::prepareLoginSession($user, VJ::LOGIN_TYPE_INTERACTIVE);

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
    public static function generateRememberMeToken(array $user, $expire = null)
    {
        $token_field = Application::get('config')['session']['remember_token'];
        if ($expire === null) {
            $expire = time() + (int)Application::get('config')['session']['remember_ttl'];
        }
        $clientToken = UserCredential::createRememberMeClientToken($user['uid'],
            Util::getClientIp(),
            Util::getUserAgentSafe(),
            $expire
        );
        Application::get('request')->cookies->set($token_field, $clientToken);
        Application::get('response')->headers->setCookie(new Cookie($token_field, $clientToken));
    }

    /**
     * 创建用户
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @return int UID
     * @throws InvalidArgumentException
     * @throws UserException
     */
    public static function createUser($username, $password, $email)
    {
        if (!is_string($username)) {
            throw new InvalidArgumentException('username', 'type_invalid');
        }
        if (!is_string($password)) {
            throw new InvalidArgumentException('password', 'type_invalid');
        }
        if (!is_string($email)) {
            throw new InvalidArgumentException('password', 'type_invalid');
        }

        // 检查用户名
        if (!mb_check_encoding($username, 'UTF-8')) {
            throw new InvalidArgumentException('username', 'encoding_invalid');
        }
        $username = trim($username);
        if (!Validator::regex('/^\S*$/')->length(3, 16)->validate($username)) {
            throw new InvalidArgumentException('username', 'format_invalid');
        }

        // 检查密码
        if (!Validator::length(5, 30)->validate($password)) {
            throw new InvalidArgumentException('password', 'format_invalid');
        }

        // 检查 email
        if (!Validator::email()->validate($email)) {
            throw new InvalidArgumentException('password', 'format_invalid');
        }

        // 处理用户名
        $username = Util::removeEmoji($username);

        // 检查用户名和 Email 是否唯一
        if (self::getUserByUsername($username) !== null) {
            throw new UserException('createUser.user_exists');
        }
        if (self::getUserByEmail($email) !== null) {
            throw new UserException('createUser.email_exists');
        }

        // 生成 hash & salt
        $hashSaltPair = PasswordEncoder::generateHash($password);

        // 插入记录
        try {
            $_id = new \MongoId();
            Application::coll('User')->insert([
                '_id' => $_id,
                'uid' => $_id, // 将在成功插入后更新
                'user' => $username,
                'luser' => UsernameCanonicalizer::canonicalize($username),
                'mail' => $email,
                'lmail' => EmailCanonicalizer::canonicalize($email),
                'salt' => $hashSaltPair['salt'],
                'hash' => $hashSaltPair['hash'],
                'g' => $email,
                'gender' => VJ::USER_GENDER_UNKNOWN,
                'regat' => new \MongoDate(),
                'regip' => Util::getClientIp(),
            ]);
        } catch (\MongoCursorException $e) {
            // 插入失败
            throw new UserException('createUser.user_or_email_exists');
        }

        // 插入成功：更新 uid
        // 获取递增 uid
        $counterRec = Application::coll('System')->findAndModify([
            '_id' => 'UserCounter'
        ], [
            '$inc' => ['count' => 1]
        ], [], [
            'new' => true,
            'upsert' => true
        ]);
        $uid = (int)$counterRec['count'];

        try {
            // 修改 uid
            Application::coll('User')->update([
                '_id' => $_id
            ], [
                '$set' => ['uid' => $uid]
            ]);
        } catch (\MongoCursorException $e) {
            // 修改 uid 失败，或创建角色失败，或创建资料失败 (uid 与现有记录重复)
            Application::critical('createUser.uidDuplicate', ['uid' => $uid]);
            // 删除用户记录
            Application::coll('User')->remove(['_id' => $_id], ['justOne' => true]);
            throw new UserException('createUser.internal');
        }

        // 加入全局域 此处不应有异常
        DomainManager::joinDomain($uid, DomainManager::getGlobalDomain());

        return $uid;
    }
} 