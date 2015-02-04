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

class RememberMeEncoder
{
    /**
     * 解析客户端 token 为数据库可接受的格式
     *
     * @param string $token
     * @return array
     */
    public static function parseClientToken($token)
    {
        $token_parts = explode('|', $token);
        if (count($token_parts) !== 2) {
            throw new \InvalidArgumentException();
        }
        if (!Validator::int()->validate($token_parts[0])) {
            throw new \InvalidArgumentException();
        }
        if (strlen($token_parts[1]) !== 32) {
            throw new \InvalidArgumentException();
        }
        return [
            'uid' => (int)$token_parts[0],
            'token' => hash('sha256', $token_parts[1]),
        ];
    }

    /**
     * 生成一个返回给客户端的 token
     *
     * @param int $uid
     * @return string
     */
    public static function generateClientToken($uid)
    {
        $uid = (int)$uid;
        $token = Application::get('random')->generateString(32);
        return $uid . '|' . $token;
    }

} 