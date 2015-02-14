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

use VJ\Core\Application;
use VJ\Core\Exception\InvalidArgumentException;
use VJ\Core\Exception\MissingArgumentException;
use VJ\Util;

class PasswordEncoder
{
    const HASH_TYPE_VJ2 = 'vj2';
    const HASH_TYPE_OPENVJ = 'openvj';

    /**
     * 验证密码是否符合哈希值
     *
     * @param string $password
     * @param string $salt
     * @param string $hash
     * @return bool
     */
    public static function verify($password, $salt, $hash)
    {
        $component = explode('|', $hash);
        if (count($component) < 2) {
            return false;
        }
        if ($component[0] == self::HASH_TYPE_VJ2) {
            if (count($component) !== 3) {
                return false;
            }
            $username = base64_decode($component[1]);
            try {
                $targetHash = self::encode($password, $salt, self::HASH_TYPE_VJ2, $username);
            } catch (\InvalidArgumentException $e) {
                return false;
            }
            return Util::slowEquals($hash, $targetHash);
        } else {
            if ($component[0] == 'openvj') {
                $targetHash = self::encode($password, $salt, self::HASH_TYPE_OPENVJ);
                try {
                    return Util::slowEquals($hash, $targetHash);
                } catch (\InvalidArgumentException $e) {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * 将密码进行哈希
     *
     * @param string $password
     * @param string $salt
     * @param string $type
     * @param null $username
     * @return string
     */
    public static function encode($password, $salt, $type, $username = null)
    {
        if (strlen($salt) < 22) {
            throw new InvalidArgumentException('salt', 'too_short');
        }

        if ($type == self::HASH_TYPE_VJ2) {
            if ($username === null) {
                throw new MissingArgumentException('username');
            }
            return self::HASH_TYPE_VJ2 . '|' .
            base64_encode($username) . '|' .
            self::encodeVJ2($password, $salt, $username);
        } else {
            if ($type == self::HASH_TYPE_OPENVJ) {
                return self::HASH_TYPE_OPENVJ . '|' . self::encodeOpenVJ($password, $salt);
            } else {
                throw new InvalidArgumentException('type', 'value_invalid');
            }
        }
    }

    /**
     * 按照 VJ2 格式哈希
     *
     * @param string $password
     * @param string $salt
     * @param string $username
     * @return string
     */
    private static function encodeVJ2($password, $salt, $username)
    {
        return sha1(md5($username . $password) . $salt . sha1($password . $salt));
    }

    /**
     * 按照 VJ3 格式哈希
     *
     * @param string $password
     * @param string $salt
     * @return string
     */
    private static function encodeOpenVJ($password, $salt)
    {
        return password_hash($password, PASSWORD_BCRYPT, [
            'salt' => $salt,
            'cost' => 10,
        ]);
    }

    /**
     * 根据密码生成盐和哈希
     *
     * @param string $password
     * @return array
     */
    public static function generateHash($password)
    {
        $salt = self::generateSalt();
        return [
            'salt' => $salt,
            'hash' => self::encode($password, $salt, self::HASH_TYPE_OPENVJ)
        ];
    }

    /**
     * 生成盐
     *
     * @return string
     */
    public static function generateSalt()
    {
        return Application::get('random_secure')->generateString(60);
    }

    /**
     * 检查哈希是否是过期哈希（VJ2 版本哈希）
     *
     * @param string $hash
     * @return bool
     */
    public static function isOutdated($hash)
    {
        $component = explode('|', $hash);
        if ($component[0] == self::HASH_TYPE_OPENVJ) {
            return false;
        } else {
            return true;
        }
    }
} 