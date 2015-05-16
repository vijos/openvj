<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Test;

use VJ\Discussion\DiscussionUtil;
use VJ\Comment\CommentUtil;

class DiscussionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateAndQueryDiscussion()
    {
        $topicId = new \MongoId();
        $owner = 12;
        $title = "联通10元包";
        $markdown = "大减价";
        $result = DiscussionUtil::createDiscussion($topicId, $owner, $title, $markdown);

        $doc = DiscussionUtil::queryDiscussion($result['_id']);
        $this->assertEquals(12, $doc['owner']);
        $this->assertEquals("联通10元包", $doc['title']);
        $this->assertEquals("大减价", $doc['raw']);
    }

    public function testModifyDiscussion()
    {
        $topicId = new \MongoId();
        $owner = 12;
        $title = "电信8元包";
        $markdown = "大增价";
        $result = DiscussionUtil::createDiscussion($topicId, $owner, $title, $markdown);

        $modifyMarkdown = "大减价";
        $queryResult = DiscussionUtil::modifyDiscussion($result['_id'], $modifyMarkdown);

        $this->assertNotEquals(Null, $queryResult);
    }

    public function testQueryTopicName()
    {
        $topicId = new \MongoId("5063114bd386d8fadbd6b004");
        $queryResult = DiscussionUtil::queryTopicName($topicId);
        $this->assertEquals('Share', $queryResult);

    }
}
