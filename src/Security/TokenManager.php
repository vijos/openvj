<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Security;

use Respect\Validation\Validator;
use VJ\Core\Application;
use VJ\Core\Exception\InvalidArgumentException;
use VJ\VJ;

class TokenManager
{
    /**
     * 创建并返回一个 token
     *
     * @param string $purpose
     * @param string $identifier 唯一标识，为空则不需要
     * @param int $expireAt
     * @param mixed $data
     * @param int $length
     * @return array
     * @throws InvalidArgumentException
     * @throws \Exception
     * @throws \MongoException
     */
    public function generate($purpose, $identifier, $expireAt, $data = null, $length = 30)
    {
        if (!is_string($purpose)) {
            throw new InvalidArgumentException('purpose', 'type_invalid');
        }
        if (!mb_check_encoding($purpose, 'UTF-8')) {
            throw new InvalidArgumentException('purpose', 'encoding_invalid');
        }
        if (!Validator::int()->validate($expireAt)) {
            throw new InvalidArgumentException('expireAt', 'type_invalid');
        }

        $token = Application::get('random')->generateString($length, VJ::RANDOM_CHARS);

        try {
            if ($identifier !== null) {

                if (!is_string($identifier)) {
                    throw new InvalidArgumentException('identifier', 'type_invalid');
                }
                if (!mb_check_encoding($identifier, 'UTF-8')) {
                    throw new InvalidArgumentException('identifier', 'encoding_invalid');
                }

                $result = Application::coll('Token')->update([
                    'purpose' => $purpose,
                    'identifier' => $identifier
                ], [
                    '$set' => [
                        'token' => $token,
                        'expireat' => new \MongoDate($expireAt),
                        'data' => $data
                    ]
                ], [
                    'upsert' => true
                ]);

                return [
                    'token' => $token,
                    'update' => $result['updatedExisting']
                ];
            } else {
                $result = Application::coll('Token')->insert([
                    'purpose' => $purpose,
                    'identifier' => null,
                    'token' => $token,
                    'expireat' => new \MongoDate($expireAt),
                    'data' => $data
                ]);

                return [
                    'token' => $token
                ];
            }
        } catch (\MongoException $ex) {
            if ($ex->getCode() === 12) {
                throw new InvalidArgumentException('data', 'encoding_invalid');
            } else {
                throw $ex;
            }
        }
    }

    /**
     * 查找并返回 token 的信息
     *
     * @param string $purpose
     * @param string $token
     * @return array|null
     * @throws InvalidArgumentException
     */
    public function find($purpose, $token)
    {
        if (!is_string($purpose)) {
            throw new InvalidArgumentException('purpose', 'type_invalid');
        }
        if (!mb_check_encoding($purpose, 'UTF-8')) {
            throw new InvalidArgumentException('purpose', 'encoding_invalid');
        }
        if (!is_string($token)) {
            throw new InvalidArgumentException('token', 'type_invalid');
        }
        if (!mb_check_encoding($token, 'UTF-8')) {
            throw new InvalidArgumentException('token', 'encoding_invalid');
        }

        $record = Application::coll('Token')->findOne([
            'purpose' => $purpose,
            'token' => $token
        ]);
        if ($record === null) {
            return null;
        }
        if ($record['expireat']->sec < time()) {
            return null;
        }

        return $record;
    }

    /**
     * 使 token 无效
     *
     * @param string $purpose
     * @param string $token
     * @return bool
     * @throws InvalidArgumentException
     */
    public function invalidate($purpose, $token)
    {
        if (!is_string($purpose)) {
            throw new InvalidArgumentException('purpose', 'type_invalid');
        }
        if (!mb_check_encoding($purpose, 'UTF-8')) {
            throw new InvalidArgumentException('purpose', 'encoding_invalid');
        }
        if (!is_string($token)) {
            throw new InvalidArgumentException('token', 'type_invalid');
        }
        if (!mb_check_encoding($token, 'UTF-8')) {
            throw new InvalidArgumentException('token', 'encoding_invalid');
        }

        $result = Application::coll('Token')->remove([
            'purpose' => $purpose,
            'token' => $token
        ], [
            'justOne' => true
        ]);

        return ($result['n'] === 1);
    }
}
