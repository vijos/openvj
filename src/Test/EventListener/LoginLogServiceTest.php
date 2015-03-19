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
use VJ\Core\Event\GenericEvent;
use VJ\Core\Request;
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
        $connection->setDb(Application::get('config')['mongodb']['db']);
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
        $types = [
            VJ::LOGIN_TYPE_FAILED_WRONG_PASSWORD,
            VJ::LOGIN_TYPE_INTERACTIVE,
            VJ::LOGIN_TYPE_COOKIE
        ];

        foreach ($types as $type) {
            $request = new Request([], [], [], [], [], [
                'PHP_SELF' => '/app.php',
                'REQUEST_METHOD' => 'GET',
                'HTTP_USER_AGENT' => 'chrome',
                'REMOTE_ADDR' => '1.2.3.4',
                'SERVER_PORT' => 80,
            ]);

            Application::coll('LoginLog')->remove();

            $service = new LoginLogService($request);
            $service->onEvent(new GenericEvent(), $type, ['uid' => 123]);

            $this->assertEquals(1, Application::coll('LoginLog')->find()->count());
            $rec = Application::coll('LoginLog')->findOne();
            $this->assertNotNull($rec);
            $this->assertEquals(123, $rec['uid']);
            $this->assertEquals($type, $rec['type']);
            $this->assertEquals('chrome', $rec['ua']);
            $this->assertEquals('1.2.3.4', $rec['ip']);
            $this->assertLessThanOrEqual(5, time() - $rec['at']->sec);
            $this->assertGreaterThanOrEqual(-2, time() - $rec['at']->sec);
        }
    }
}