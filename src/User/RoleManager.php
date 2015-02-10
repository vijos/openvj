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

class RoleManager
{
    private static $overWriteUid = null;

    /**
     * 在函数调用期间使用指定的 uid 作为权限控制主体的标识符
     *
     * @param int $uid
     * @param callable $callback
     */
    public static function overWriteToken($uid, callable $callback)
    {
        $lastOverWrite = self::$overWriteUid;
        self::$overWriteUid = $uid;
        $callback();
        self::$overWriteUid = $lastOverWrite;
    }

    /**
     * 创建一个角色
     *
     * @param string $name
     * @param bool $internal
     * @param \MongoId|null $domain
     * @param int|null $owner
     * @return bool
     */
    public static function createRole($name, $internal = false, \MongoId $domain = null, $owner = null)
    {
        if (!$internal) {
            if ($domain === null || $owner === null || !$domain instanceof \MongoId) {
                throw new \InvalidArgumentException('required domain and owner');
            }
            if (!mb_check_encoding($name, 'UTF-8')) {
                throw new \InvalidArgumentException('invalid name');
            }
            if (!preg_match('/^\$[0-9a-zA-Z_]{1,20}$/', $name)) {
                throw new \InvalidArgumentException('invalid name');
            }
        }

        $name = strtoupper($name);

        if (!$internal) {
            $result = Application::coll('Role')->update([
                'domain' => $domain,
                'name' => $name,
            ], [
                '$setOnInsert' => [
                    'owner' => (int)$owner,
                    'at' => new \MongoDate()
                ]
            ], [
                'upsert' => true
            ]);
        } else {
            $result = Application::coll('Role')->update([
                'internal' => true,
                'name' => $name,
            ], [], [
                'upsert' => true
            ]);
        }

        return ($result['n'] === 1);
    }

    /**
     * 获得一个域下所有角色
     *
     * @param \MongoId $domain
     * @return array
     */
    public static function getDomainRoles(\MongoId $domain)
    {
        $cursor = Application::coll('Role')->find([
            '$or' => [
                ['internal' => true],
                ['domain' => $domain]
            ]
        ]);

        return iterator_to_array($cursor, false);
    }
}