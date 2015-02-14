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
use VJ\EventListener\VJCredentialUpgradeService;
use VJ\User\PasswordEncoder;
use VJ\User\UserCredential;
use Zumba\PHPUnit\Extensions\Mongo\Client\Connector;
use Zumba\PHPUnit\Extensions\Mongo\DataSet\DataSet;
use Zumba\PHPUnit\Extensions\Mongo\TestTrait;

class VJCredentialUpgradeServiceTest extends \PHPUnit_Framework_TestCase
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
            'User' => [
                [
                    'uid' => 0,
                    'user' => 'test_User',
                    'luser' => 'test_user',
                    'mail' => 'test@example.com',
                    'lmail' => 'test@example.com',
                    'salt' => '5b26d1542f68297831044e4cfe10052344e20fea',
                    'hash' => 'openvj|$2y$10$5b26d1542f68297831044eOCPuejIMxU6peNfQQUw.HUz8CoxOZ1.',
                    // password: test_password
                ],
                [
                    'uid' => 1,
                    'user' => '世界你好',
                    'luser' => '世界你好',
                    'mail' => null,
                    'lmail' => null,
                    'salt' => 'a4c4faf1b8cb70c640c1ee9b8df2d31f47ad36fc',
                    'hash' => 'vj2|5LiW55WM5L2g5aW9|488fdef0417301a0541aefd562806ac24a4d8a67',
                    // password: this_is_test_password
                ]
            ]
        ]);
        return $dataset;
    }

    public function testUpgradeUserCredentialInvalid()
    {
        $service = new VJCredentialUpgradeService();
        // not exist
        $this->assertFalse($service->upgradeUserCredential(100, 'test_password'));
        // needn't upgrade
        $this->assertFalse($service->upgradeUserCredential(0, 'test_password'));
    }

    public function testUpgradeUserCredentialPassed()
    {
        $service = new VJCredentialUpgradeService();
        $this->assertTrue($service->upgradeUserCredential(1, 'this_is_test_password'));

        $user = UserCredential::checkPasswordCredential('世界你好', 'this_is_test_password', true);
        $this->assertFalse(PasswordEncoder::isOutdated($user['hash']));
    }

}