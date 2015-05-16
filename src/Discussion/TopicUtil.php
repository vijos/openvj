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

class TopicUtil
{
    /**
     * 获取分类名称
     *
     * @param \MongoId $topicId
     * @return array|null
     */
    public static function getTopicById(\MongoId $topicId)
    {
        $doc = Application::coll('Topic')->findOne([
            '_id' => $topicId,
        ]);

        return $doc;
    }
}