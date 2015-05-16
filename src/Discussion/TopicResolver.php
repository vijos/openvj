<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Discussion;

use VJ\Core\Application;
use VJ\Core\Exception\InvalidArgumentException;
use VJ\DeferredResolver;

class TopicResolver extends DeferredResolver
{
    protected function preprocessValue(&$topicId)
    {
        if (!$topicId instanceof \MongoId) {
            throw new InvalidArgumentException('topicId', 'type_invalid');
        }
        $topicId = (string)$topicId;
    }

    protected function postprocessValues()
    {
        foreach ($this->queryValues as &$id) {
            $id = new \MongoId($id);
        }
    }

    protected function query($chunk)
    {
        return Application::coll('Topic')->find([
            '_id' => ['$in' => $chunk]
        ]);
    }

    protected function getValueField($document)
    {
        return (string)$document['_id'];
    }
}