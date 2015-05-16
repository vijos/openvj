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
use VJ\Discussion\TopicUtil;
use Zumba\PHPUnit\Extensions\Mongo\Client\Connector;
use Zumba\PHPUnit\Extensions\Mongo\DataSet\DataSet;
use Zumba\PHPUnit\Extensions\Mongo\TestTrait;

class TopicUtilTest extends \PHPUnit_Framework_TestCase
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

    public function testQueryTopicName()
    {
        $topicId = new \MongoId("5063114bd386d8fadbd6b004");
        $queryResult = TopicUtil::getTopicById($topicId);
        $this->assertEquals('Share', $queryResult['name']);
    }
}
