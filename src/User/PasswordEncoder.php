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

use VJ\Security\Util;

class PasswordEncoder
{
    const HASH_TYPE_VJ2 = 'vj2';
    const HASH_TYPE_OPENVJ = 'openvj';

    /**
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
     * @param string $password
     * @param string $salt
     * @param string $type
     * @param null $username
     * @return string
     */
    public static function encode($password, $salt, $type, $username = null)
    {
        if (strlen($salt) < 22) {
            throw new \InvalidArgumentException('salt too short');
        }

        if ($type == self::HASH_TYPE_VJ2) {
            if ($username === null) {
                throw new \InvalidArgumentException('required username');
            }
            return self::HASH_TYPE_VJ2 . '|' . base64_encode($username) . '|' . sha1(md5($username . $password) . $salt . sha1($password . $salt));
        } else {
            if ($type == self::HASH_TYPE_OPENVJ) {
                return self::HASH_TYPE_OPENVJ . '|' . password_hash($password, PASSWORD_BCRYPT, [
                    'salt' => $salt,
                    'cost' => 10,
                ]);
            } else {
                throw new \InvalidArgumentException('unknown hash type');
            }
        }
    }

    /**
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