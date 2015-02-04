<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Core\Session;

class MongoDBSessionHandler implements \SessionHandlerInterface
{
    /** @var \MongoCollection $collection */
    private $collection;

    /**
     * {@inheritdoc}
     */
    public function __construct(\MongoCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        $this->collection->remove([
            '_id' => $this->encodeSessionId($sessionId)
        ], [
            'justOne' => true
        ]);

        return true;
    }

    /**
     * 将原始 Session ID 不可逆编码，进一步提高安全性
     * 这里使用 sha1 算法
     * @param string $sessionIdRaw
     * @return string
     */
    private function encodeSessionId($sessionIdRaw)
    {
        return sha1($sessionIdRaw);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        // 使用 MongoDB TTL Index 特性，因此这里不需要 gc
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        $this->collection->update([
            '_id' => $this->encodeSessionId($sessionId)
        ], [
            '$set' => [
                'data' => unserialize($data),   // we use MongoDB native data storage
                'expireat' => new \MongoDate(time() + (int)ini_get('session.gc_maxlifetime')),
            ]
        ], [
            'upsert' => true,
            'multiple' => false,
        ]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        $record = $this->collection->findOne([
            '_id' => $this->encodeSessionId($sessionId)
        ]);

        if ($record == null) {
            return '';
        }

        if ($record['expireat']->sec > time()) {
            return '';
        }

        return serialize($record['data']);
    }
}