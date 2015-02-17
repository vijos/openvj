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
use VJ\Core\Request;
use VJ\Core\Response;
use VJ\Security\KeywordFilter;
use VJ\VJ;

class UserManager
{
    private $session;
    private $request;
    private $response;
    private $domain_manager;
    public $user_credential;

    /**
     * @param UserSession $session
     * @param Request $request
     * @param Response $response
     * @param DomainManager $domain_manager
     * @param UserCredential $user_credential
     */
    public function __construct(
        UserSession $session,
        Request $request,
        Response $response,
        DomainManager $domain_manager,
        UserCredential $user_credential
    ) {
        $this->session = $session;
        $this->request = $request;
        $this->response = $response;
        $this->domain_manager = $domain_manager;
        $this->user_credential = $user_credential;
    }

    /**
     * 成功登录后初始化 session
     *
     * @param array $user
     * @param int $from
     */
    private function prepareLoginSessionByObject(array $user, $from)
    {
        $this->session->set('user', $user);
        $this->session->set('loginType', $from);
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
    public function interactiveLogin($usernameEmail, $password, $remember = false)
    {
        if (!is_string($usernameEmail)) {
            throw new InvalidArgumentException('usernameEmail', 'type_invalid');
        }
        if (!is_string($password)) {
            throw new InvalidArgumentException('password', 'type_invalid');
        }
        $user = $this->user_credential->checkPasswordCredential($usernameEmail, $password);
        if ($remember) {
            $this->generateRememberMeTokenForObject($user);
        }

        $this->prepareLoginSessionByObject($user, VJ::LOGIN_TYPE_INTERACTIVE);

        return $user;
    }

    /**
     * Cookie 已记忆会话登录
     *
     * @return array
     * @throws UserException
     */
    public function rememberMeTokenLogin()
    {
        $token_field = Application::get('config')['session']['remember_token'];
        $clientToken = $this->request->cookies->get($token_field);
        try {
            $user = $this->user_credential->checkCookieTokenCredential($clientToken);
        } catch (UserException $e) {
            // 对于无效 token 需要删除 cookie
            $this->invalidateRememberMeToken();
            throw $e;
        }

        // 对于有效 token，需要重新生成一份新 token，并继承其过期时间
        $token = $this->user_credential->rememberme_encoder->parseClientToken($clientToken);
        $this->invalidateRememberMeToken();
        $this->generateRememberMeTokenForObject($user, $token['expire']);

        $this->prepareLoginSessionByObject($user, VJ::LOGIN_TYPE_COOKIE);

        return $user;
    }

    /**
     * 无效化已记忆会话
     */
    public function invalidateRememberMeToken()
    {
        $token_field = Application::get('config')['session']['remember_token'];
        $clientToken = $this->request->cookies->get($token_field);
        if ($clientToken !== null) {
            $this->user_credential->invalidateRememberMeClientToken($clientToken);
        }
        $this->request->cookies->remove($token_field);
        $this->response->headers->clearCookie($token_field);
    }

    /**
     * 创建一个记忆会话
     *
     * @param array $user
     * @param int|null $expire
     */
    public function generateRememberMeTokenForObject(array $user, $expire = null)
    {
        $token_field = Application::get('config')['session']['remember_token'];
        if ($expire === null) {
            $expire = time() + (int)Application::get('config')['session']['remember_ttl'];
        }
        $clientToken = $this->user_credential->createRememberMeClientToken($user['uid'],
            $this->request->getClientIp(),
            $this->request->getUserAgent(),
            $expire
        );
        $this->request->cookies->set($token_field, $clientToken);
        $this->response->headers->setCookie(new Cookie($token_field, $clientToken));
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
    public function createUser($username, $password, $email)
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

        // 检查关键字
        $keyword = KeywordFilter::isContainGeneric($username);
        if ($keyword === false) {
            $keyword = KeywordFilter::isContainName($username);
        }
        if ($keyword !== false) {
            throw new UserException('UserManager::createUser.name_invalid', Response::HTTP_BAD_REQUEST, [
                'keyword' => $keyword
            ]);
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
        $username = VJ::removeEmoji($username);

        // 检查用户名和 Email 是否唯一
        if (UserUtil::getUserByUsername($username) !== null) {
            throw new UserException('UserManager::createUser.user_exists');
        }
        if (UserUtil::getUserObjectByEmail($email) !== null) {
            throw new UserException('UserManager::createUser.email_exists');
        }

        // 生成 hash & salt
        $hashSaltPair = $this->user_credential->password_encoder->generateHash($password);

        // 插入记录
        try {
            $_id = new \MongoId();
            Application::coll('User')->insert([
                '_id' => $_id,
                'uid' => $_id, // 将在成功插入后更新
                'user' => $username,
                'luser' => UserUtil::canonicalizeUsername($username),
                'mail' => $email,
                'lmail' => UserUtil::canonicalizeEmail($email),
                'salt' => $hashSaltPair['salt'],
                'hash' => $hashSaltPair['hash'],
                'g' => $email,
                'gender' => VJ::USER_GENDER_UNKNOWN,
                'regat' => new \MongoDate(),
                'regip' => $this->request->getClientIp(),
            ]);
        } catch (\MongoCursorException $e) {
            // 插入失败
            throw new UserException('UserManager::createUser.user_or_email_exists');
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
            // 修改 uid 失败（uid 重复），则删除用户记录
            Application::critical('createUser.uidDuplicate', ['uid' => $uid]);
            Application::coll('User')->remove(['_id' => $_id], ['justOne' => true]);
            throw new UserException('UserManager::createUser.internal');
        }

        // 加入全局域 此处不应有异常
        $this->domain_manager->joinDomainById($uid, DomainUtil::getGlobalDomainId());

        return $uid;
    }
} 