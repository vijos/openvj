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

class RememberMeEncoder
{
    /**
     * 解析客户端 token
     *
     * @param string $clientToken
     * @return array
     */
    public static function parseClientToken($clientToken)
    {
        if (!is_string($clientToken)) {
            throw new InvalidArgumentException('clientToken', 'type_invalid');
        }
        if (!mb_check_encoding($clientToken, 'UTF-8')) {
            throw new InvalidArgumentException('clientToken', 'encoding_invalid');
        }
        $token_parts = explode('|', $clientToken);
        if (count($token_parts) !== 3) {
            throw new InvalidArgumentException('clientToken', 'format_invalid');
        }
        if (!Validator::int()->validate($token_parts[0])) {
            throw new InvalidArgumentException('clientToken', 'format_invalid');
        }
        if (!Validator::int()->validate($token_parts[1])) {
            throw new InvalidArgumentException('clientToken', 'format_invalid');
        }
        if ((int)$token_parts[1] <= 0) {
            throw new InvalidArgumentException('clientToken', 'format_invalid');
        }
        if (strlen($token_parts[2]) !== 32) {
            throw new InvalidArgumentException('clientToken', 'format_invalid');
        }
        return [
            'uid' => (int)$token_parts[0],
            'expire' => (int)$token_parts[1],
            'token' => hash('sha256', $clientToken),
        ];
    }

    /**
     * 生成一个返回给客户端的 token
     *
     * @param int $uid
     * @param int $expire
     * @return string
     */
    public static function generateClientToken($uid, $expire)
    {
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }
        $token = Application::get('random')->generateString(32);
        return (int)$uid . '|' . (int)$expire . '|' . $token;
    }

} 