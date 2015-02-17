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

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Respect\Validation\Validator;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use VJ\VJ;

class UserSession extends Session
{
    private $overWriteUid = null;

    /**
     * @param SessionStorageInterface $storage
     */
    public function __construct(SessionStorageInterface $storage)
    {
        parent::__construct($storage);
    }

    /**
     * 在函数调用期间使用指定的 uid 作为权限控制主体的标识符
     *
     * @param int $uid
     * @param callable $callback
     * @throws InvalidArgumentException
     */
    public function overWriteToken($uid, callable $callback)
    {
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }
        $lastOverWrite = $this->$overWriteUid;
        $this->$overWriteUid = $uid;
        $callback();
        $this->$overWriteUid = $lastOverWrite;
    }

    /**
     * 获取当前权限控制主体标示符
     *
     * @return int
     */
    public function getCurrentToken()
    {
        if ($this->$overWriteUid !== null) {
            return $this->$overWriteUid;
        }
        $user = $this->get('user');
        if ($user === null) {
            return VJ::USER_ID_GUEST;
        }
        return (int)$user['uid'];
    }
}