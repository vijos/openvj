<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Problem\Tag;

abstract class Tag implements TagInterface
{
    /**
     * 为 ElasticSearch 序列化
     *
     * @return string[]
     */
    final public function serializeForIndex()
    {
        return $this->getParts();
    }

    /**
     * 为数据库序列化
     *
     * @return array
     */
    final public function serializeForDb()
    {
        return [
            'type' => $this->getType(),
            'parts' => $this->getParts(),
            'name' => $this->getDisplayName()
        ];
    }
}