<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Test\EventListener;

use VJ\Core\Application;
use VJ\EventListener\LoginLogService;
use VJ\VJ;
use Zumba\PHPUnit\Extensions\Mongo\Client\Connector;
use Zumba\PHPUnit\Extensions\Mongo\DataSet\DataSet;
use Zumba\PHPUnit\Extensions\Mongo\TestTrait;

class LoginLogServiceTest extends \PHPUnit_Framework_TestCase
{
    use TestTrait;

    public function getMongoConnection()
    {
        $connection = new Connector(Application::get('mongo_client'));
        $connection->setDb(Application::get('config')['mongodb']['db'] . (MODE_TEST ? '-test' : ''));
        return $connection;
    }

    public function getMongoDataSet()
    {
        $dataset = new DataSet($this->getMongoConnection());
        $dataset->setFixture([
            'LoginLog' => []
        ]);
        return $dataset;
    }

    public function testAppendLog()
    {
        $service = new LoginLogService();
        $service->appendLog(0, VJ::LOGIN_TYPE_INTERACTIVE, 'chrome', '0.0.0.0');

        $this->assertEquals(1, Application::coll('LoginLog')->find()->count());

        $rec = Application::coll('LoginLog')->findOne();
        $this->assertNotNull($rec);
        $this->assertEquals(0, $rec['uid']);
        $this->assertEquals(VJ::LOGIN_TYPE_INTERACTIVE, $rec['type']);
        $this->assertEquals('chrome', $rec['ua']);
        $this->assertEquals('0.0.0.0', $rec['ip']);
        $this->assertLessThanOrEqual(5, time() - $rec['at']->sec);
        $this->assertGreaterThanOrEqual(0, time() - $rec['at']->sec);
    }

}