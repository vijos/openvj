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

use VJ\Core\Application;
use VJ\Discussion\DiscussionUtil;
use Zumba\PHPUnit\Extensions\Mongo\Client\Connector;
use Zumba\PHPUnit\Extensions\Mongo\DataSet\DataSet;
use Zumba\PHPUnit\Extensions\Mongo\TestTrait;

class DiscussionUtilTest extends \PHPUnit_Framework_TestCase
{
    use TestTrait;

    private $fixture;

    public function __construct()
    {
        $this->fixture = [
            'Topic' => [
                [
                    '_id' => new \MongoId('5063114bd386d8fadbd6b004'),
                    'name' => 'Share'
                ]
            ]
        ];
    }

    public function getMongoConnection()
    {
        $connection = new Connector(Application::get('mongo_client'));
        $connection->setDb(Application::get('config')['mongodb']['db']);
        return $connection;
    }

    public function getMongoDataSet()
    {
        $dataset = new DataSet($this->getMongoConnection());
        $dataset->setFixture($this->fixture);
        return $dataset;
    }

    public function testCreateAndQueryDiscussion()
    {
        $topicId = new \MongoId('5063114bd386d8fadbd6b004');
        $owner = 12;
        $title = "联通10元包";
        $markdown = "大减价";
        $result = DiscussionUtil::createDiscussion($topicId, $owner, $title, $markdown);

        $doc = DiscussionUtil::queryDiscussion($result['_id']);
        $this->assertEquals((string)$topicId, (string)$doc['topicId']);
        $this->assertEquals(12, $doc['owner']);
        $this->assertEquals("联通10元包", $doc['title']);
        $this->assertEquals("大减价", $doc['raw']);
    }

    public function testModifyDiscussion()
    {
        $topicId = new \MongoId('5063114bd386d8fadbd6b004');
        $owner = 12;
        $title = "电信8元包";
        $markdown = "大增价";
        $result = DiscussionUtil::createDiscussion($topicId, $owner, $title, $markdown);

        $modifyMarkdown = "大减价";
        $queryResult = DiscussionUtil::modifyDiscussion($result['_id'], $modifyMarkdown);

        $this->assertNotEquals(null, $queryResult);
    }
}
