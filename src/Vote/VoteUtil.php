<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Vote;

use Respect\Validation\Validator;
use VJ\Core\Exception\InvalidArgumentException;

class VoteUtil
{
    /**
     * @param array $document
     * @return array
     */
    public static function attachDocument(array $document)
    {
        $document['voting'] = 0;
        $document['votes'] = [];
        return $document;
    }

    /**
     * Vote Up
     *
     * @param \MongoCollection $collection
     * @param array $query
     * @param int $uid
     * @return int|null
     * @throws InvalidArgumentException
     */
    public static function voteUp(\MongoCollection $collection, array $query, $uid)
    {
        return self::vote($collection, $query, $uid, 1);
    }

    /**
     * Vote Down
     *
     * @param \MongoCollection $collection
     * @param array $query
     * @param int $uid
     * @return int|null
     * @throws InvalidArgumentException
     */
    public static function voteDown(\MongoCollection $collection, array $query, $uid)
    {
        return self::vote($collection, $query, $uid, -1);
    }

    /**
     * 若投票成功，返回投票后的投票值，若失败，返回 null
     *
     * @param \MongoCollection $collection
     * @param array $query
     * @param int $uid
     * @param int $value
     * @return int|null
     * @throws InvalidArgumentException
     */
    public static function vote(\MongoCollection $collection, array $query, $uid, $value)
    {
        if ($value !== 1 && $value !== -1) {
            throw new InvalidArgumentException('value', 'value_invalid');
        }
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }

        $value = (int)$value;

        $result = $collection->findAndModify(array_merge($query, [
            'votes.' . strval(intval($uid)) => [
                '$exists' => false
            ]
        ]), [
            '$set' => [
                'votes' . strval(intval($uid)) => $value
            ],
            '$inc' => [
                'voting' => $value
            ]
        ], [
            'voting' => 1
        ], [
            'new' => true
        ]);

        if ($result === null) {
            // vote failed
            return null;
        } else {
            // vote succeeded
            return $result['voting'];
        }
    }
}