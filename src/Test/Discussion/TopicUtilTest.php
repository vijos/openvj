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
                ],
                [
                    '_id' => new \MongoId('5063114bd386d8fadbd6b005'),
                    'name' => 'News'
                ],
                [
                    '_id' => new \MongoId('5063114bd386d8fadbd6b006'),
                    'name' => 'Contest'
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

    public function testQueryTopic()
    {
        $topicId = new \MongoId('5063114bd386d8fadbd6b004');
        $queryResult = TopicUtil::getTopicById($topicId);
        $this->assertEquals('Share', $queryResult['name']);
    }

    /**
     * 测试批量查询 TopicId 对应的内容
     */
    public function testBatchQueryTopic()
    {
        $cursor = [
            ['topicId' => new \MongoId('5063114bd386d8fadbd6b004'), '_id' => 1],
            ['topicId' => new \MongoId('5063114bd386d8fadbd6b005'), '_id' => 2],
            ['topicId' => new \MongoId('5063114bd386d8fadbd6b006'), '_id' => 3],
        ];

        // 假设以上 $cursor 是 MongoDB 查询数据库以后获得的光标
        // 现在需要为它们的每一条记录都根据 topicId 查询 topicObject

        $result = [];
        $resolver = TopicUtil::getResolver();
        foreach ($cursor as $doc) {
            // 需要根据该对象的 topicId 查询对应的 topic，查询到的结果放入该对象的 topicObj
            $resolver->resolve($doc['topicId'])->to($doc['topicObj']);
            // 这里是一般的逻辑，注意需要使用引用
            $result[] = &$doc;
            // 注意一定要有这行
            unset($doc);
        }
        // 最后需要调用 done() 执行
        $resolver->done();

        // 此时，应当已查询完毕且对象已插入回去

        // 先测试 $result 数组是否包含了原本应该包含的内容
        $this->assertEquals(3, count($result));
        $this->assertEquals(1, $result[0]['_id']);
        $this->assertEquals('5063114bd386d8fadbd6b004', (string)$result[0]['topicId']);
        $this->assertEquals(2, $result[1]['_id']);
        $this->assertEquals('5063114bd386d8fadbd6b005', (string)$result[1]['topicId']);
        $this->assertEquals(3, $result[2]['_id']);
        $this->assertEquals('5063114bd386d8fadbd6b006', (string)$result[2]['topicId']);

        // 再测试 $result 数组每一项是否包含了正确的 topicObj
        $this->assertTrue(isset($result[0]['topicObj']['name']));
        $this->assertEquals('Share', $result[0]['topicObj']['name']);
        $this->assertTrue(isset($result[1]['topicObj']['name']));
        $this->assertEquals('News', $result[1]['topicObj']['name']);
        $this->assertTrue(isset($result[2]['topicObj']['name']));
        $this->assertEquals('Contest', $result[2]['topicObj']['name']);
    }

    /**
     * 测试多条记录引用同一个 ID 时，是否可以正确查询并插入相应位置
     */
    public function testBatchQueryTopicDuplicate()
    {
        $cursor = [
            ['topicId' => new \MongoId('5063114bd386d8fadbd6b004'), '_id' => 1],
            ['topicId' => new \MongoId('5063114bd386d8fadbd6b005'), '_id' => 2],
            ['topicId' => new \MongoId('5063114bd386d8fadbd6b005'), '_id' => 3],
        ];

        $result = [];
        $resolver = TopicUtil::getResolver();
        foreach ($cursor as $doc) {
            $resolver->resolve($doc['topicId'])->to($doc['topicObj']);
            $result[] = &$doc;
            unset($doc);
        }
        $resolver->done();

        $this->assertEquals(3, count($result));
        $this->assertEquals('Share', $result[0]['topicObj']['name']);
        $this->assertEquals('News', $result[1]['topicObj']['name']);
        $this->assertEquals('News', $result[2]['topicObj']['name']);
    }

    /**
     * 测试引用不存在的 ID 时，相应位置是否为 NULL
     */
    public function testBatchQueryTopicNotFound()
    {
        $cursor = [
            ['topicId' => new \MongoId('5063114bd386d8fadbd6b004'), '_id' => 1],
            ['topicId' => new \MongoId('111111111111111111111111'), '_id' => 2],
            ['topicId' => new \MongoId('111111111111111111155555'), '_id' => 3],
        ];

        $result = [];
        $resolver = TopicUtil::getResolver();
        foreach ($cursor as $doc) {
            $resolver->resolve($doc['topicId'])->to($doc['topicObj']);
            $result[] = &$doc;
            unset($doc);
        }
        $resolver->done();

        $this->assertEquals(3, count($result));
        $this->assertEquals('Share', $result[0]['topicObj']['name']);
        $this->assertNull($result[1]['topicObj']);
        $this->assertNull($result[2]['topicObj']);
    }
}
