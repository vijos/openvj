<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Test\User;

use VJ\Core\Application;
use VJ\Core\Exception\UserException;
use Zumba\PHPUnit\Extensions\Mongo\Client\Connector;
use Zumba\PHPUnit\Extensions\Mongo\DataSet\DataSet;
use Zumba\PHPUnit\Extensions\Mongo\TestTrait;

class UserCredentialTest extends \PHPUnit_Framework_TestCase
{
    use TestTrait;

    private $fixture = [
        'User' => [
            [ // password: test_password
                'uid' => 0,
                'user' => 'test_User',
                'luser' => 'test_user',
                'mail' => 'test@example.com',
                'lmail' => 'test@example.com',
                'salt' => '5b26d1542f68297831044e4cfe10052344e20fea',
                'hash' => 'openvj|$2y$10$5b26d1542f68297831044eOCPuejIMxU6peNfQQUw.HUz8CoxOZ1.',
            ],
            [ // password: test_password
                'uid' => 1,
                'user' => 'test_User2',
                'luser' => 'test_user2',
                'mail' => 'test2@example.com',
                'lmail' => 'test2@example.com',
                'salt' => '5b26d1542f68297831044e4cfe10052344e20fea',
                'hash' => 'openvj|$2y$10$5b26d1542f68297831044eOCPuejIMxU6peNfQQUw.HUz8CoxOZ1.',
                'banned' => true,
            ]
        ]
    ];
    private $rememberMeTokens = [];

    public function __construct()
    {
        // generate client token
        // valid
        $expire = time() + 24 * 60 * 60;
        $token = Application::get('user_credential')->createRememberMeToken(0, "127.0.0.1", "VJTest/233", (int)$expire);
        $this->rememberMeTokens[] = $token;

        // expired
        $expire = time() - 24 * 60 * 60;
        $token = Application::get('user_credential')->createRememberMeToken(0, "127.0.0.1", "VJTest/233", (int)$expire);
        $this->rememberMeTokens[] = $token;

        // user not valid
        $expire = time() + 24 * 60 * 60;
        $token = Application::get('user_credential')->createRememberMeToken(1, "127.0.0.1", "VJTest/233", (int)$expire);
        $this->rememberMeTokens[] = $token;
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

    public function testCheckPasswordCredentialUserNotExist()
    {
        $throw = false;
        try {
            Application::get('user_credential')->checkPasswordCredential('test', '', true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkPasswordCredential.user_not_valid', $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');

        $throw = false;
        try {
            Application::get('user_credential')->checkPasswordCredential('test_nonexist@example.com', '', true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkPasswordCredential.user_not_valid', $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');
    }

    public function testCheckPasswordCredentialWrongPassword()
    {
        $throw = false;
        try {
            Application::get('user_credential')->checkPasswordCredential('Test_user', 'test_wrong_password', true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkPasswordCredential.wrong_password', $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');

        $throw = false;
        try {
            Application::get('user_credential')->checkPasswordCredential('TEST@example.com', 'test_wrong_password',
                true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkPasswordCredential.wrong_password', $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');
    }

    public function testCheckPasswordCredentialUserInvalid()
    {
        $throw = false;
        try {
            Application::get('user_credential')->checkPasswordCredential('Test_user2', 'test_password', true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkPasswordCredential.user_not_valid', $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');
    }

    public function testCheckPasswordCredentialPassed()
    {
        $user = Application::get('user_credential')->checkPasswordCredential('Test_User', 'test_password', true);
        $this->assertEquals($this->fixture['User'][0]['uid'], $user['uid']);
        $this->assertEquals($this->fixture['User'][0]['user'], $user['user']);
    }

    public function testCheckCookieTokenCredentialInvalid()
    {

        // null
        $throw = false;
        try {
            Application::get('user_credential')->checkRememberMeTokenCredential(null, true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkRememberMeTokenCredential.invalid_rememberme_token',
                $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');

        // not exist
        $throw = false;
        try {
            Application::get('user_credential')->checkRememberMeTokenCredential('123456789012345678901234567890',
                true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkRememberMeTokenCredential.invalid_rememberme_token',
                $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');

        // expired
        $throw = false;
        try {
            Application::get('user_credential')->checkRememberMeTokenCredential($this->rememberMeTokens[1], true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkRememberMeTokenCredential.invalid_rememberme_token',
                $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');

        // user banned
        $throw = false;
        try {
            Application::get('user_credential')->checkRememberMeTokenCredential($this->rememberMeTokens[2], true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkRememberMeTokenCredential.user_not_valid', $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');
    }

    public function testCheckCookieTokenCredentialPassed()
    {
        $user = Application::get('user_credential')->checkRememberMeTokenCredential($this->rememberMeTokens[0], true);
        $this->assertEquals($this->fixture['User'][0]['uid'], $user['uid']);
        $this->assertEquals($this->fixture['User'][0]['user'], $user['user']);
    }

    public function testCreateRememberMeClientToken()
    {
        $token = Application::get('user_credential')->createRememberMeToken(0, '1.2.3.4', "VJTest/233",
            time() + 60);

        // assert valid
        $user = Application::get('user_credential')->checkRememberMeTokenCredential($token, true);
        $this->assertEquals($this->fixture['User'][0]['uid'], $user['uid']);
        $this->assertEquals($this->fixture['User'][0]['user'], $user['user']);

        // assert record
        $record = Application::coll('Token')->findOne([
            'purpose' => 'rememberme',
            'token' => $token,
        ]);
        $this->assertEquals('1.2.3.4', $record['data']['ip']);
        $this->assertEquals("VJTest/233", $record['data']['ua']);
    }

    public function testInvalidateRememberMeClientToken()
    {
        $token = Application::get('user_credential')->createRememberMeToken(0, '1.2.3.4', "VJTest/233",
            time() + 60);

        Application::get('user_credential')->invalidateRememberMeToken($token);

        $throw = false;
        try {
            Application::get('user_credential')->checkRememberMeTokenCredential($token, true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkRememberMeTokenCredential.invalid_rememberme_token',
                $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');

        $record = Application::coll('Token')->findOne([
            'purpose' => 'rememberme',
            'token' => $token,
        ]);
        $this->assertNull($record);
    }

    public function testSetCredential()
    {
        $ret = Application::get('user_credential')->setCredential(0, 'new_password');
        $this->assertEquals(1, $ret);

        $user = Application::get('user_credential')->checkPasswordCredential('test_user', 'new_password', true);
        $this->assertNotNull($user);
        $this->assertEquals('test_user', $user['luser']);
    }
}