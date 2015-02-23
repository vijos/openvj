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
        ],
        'RememberMeToken' => []
    ];
    private $rememberMeClientTokens = [];

    public function __construct()
    {
        // generate client token
        // valid
        $expire = time() + 24 * 60 * 60;
        $clientToken = Application::get('rememberme_encoder')->generateClientToken(0, (int)$expire);
        $token = Application::get('rememberme_encoder')->parseClientToken($clientToken);
        $token['expireat'] = new \MongoDate($token['expire']);
        unset($token['expire']);
        $this->rememberMeClientTokens[] = $clientToken;
        $this->fixture['RememberMeToken'][] = $token;

        // expired
        $expire = time() - 24 * 60 * 60;
        $clientToken = Application::get('rememberme_encoder')->generateClientToken(0, (int)$expire);
        $token = Application::get('rememberme_encoder')->parseClientToken($clientToken);
        $token['expireat'] = new \MongoDate($token['expire']);
        unset($token['expire']);
        $this->rememberMeClientTokens[] = $clientToken;
        $this->fixture['RememberMeToken'][] = $token;

        // user not valid
        $expire = time() + 24 * 60 * 60;
        $clientToken = Application::get('rememberme_encoder')->generateClientToken(1, (int)$expire);
        $token = Application::get('rememberme_encoder')->parseClientToken($clientToken);
        $token['expireat'] = new \MongoDate($token['expire']);
        unset($token['expire']);
        $this->rememberMeClientTokens[] = $clientToken;
        $this->fixture['RememberMeToken'][] = $token;
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
        $this->assertEquals($this->fixture['User'][0], $user);
    }

    public function testCheckCookieTokenCredentialInvalid()
    {
        // invalid format
        $throw = false;
        try {
            Application::get('user_credential')->checkCookieTokenCredential('1|2|a', true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkCookieTokenCredential.invalid_rememberme_token',
                $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');

        // null
        $throw = false;
        try {
            Application::get('user_credential')->checkCookieTokenCredential(null, true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkCookieTokenCredential.invalid_rememberme_token',
                $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');

        // not exist
        $throw = false;
        try {
            Application::get('user_credential')->checkCookieTokenCredential('1|100|12345678123456781234567812345678',
                true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkCookieTokenCredential.invalid_rememberme_token',
                $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');

        // expired
        $throw = false;
        try {
            Application::get('user_credential')->checkCookieTokenCredential($this->rememberMeClientTokens[1], true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkCookieTokenCredential.invalid_rememberme_token',
                $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');

        // user banned
        $throw = false;
        try {
            Application::get('user_credential')->checkCookieTokenCredential($this->rememberMeClientTokens[2], true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkCookieTokenCredential.user_not_valid', $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');
    }

    public function testCheckCookieTokenCredentialPassed()
    {
        $user = Application::get('user_credential')->checkCookieTokenCredential($this->rememberMeClientTokens[0], true);
        $this->assertEquals($this->fixture['User'][0], $user);
    }

    public function testCreateRememberMeClientToken()
    {
        $clientToken = Application::get('user_credential')->createRememberMeClientToken(0, '1.2.3.4', null,
            time() + 60);
        $token = Application::get('rememberme_encoder')->parseClientToken($clientToken);

        // assert valid
        $user = Application::get('user_credential')->checkCookieTokenCredential($clientToken, true);
        $this->assertEquals($this->fixture['User'][0], $user);

        // assert record
        $record = Application::coll('RememberMeToken')->findOne([
            'uid' => $token['uid'],
            'token' => $token['token'],
        ]);
        $this->assertEquals('1.2.3.4', $record['ip']);
        $this->assertEquals(null, $record['ua']);
        $this->assertEquals($token['expire'], $record['expireat']->sec);
    }

    public function testInvalidateRememberMeClientToken()
    {
        $clientToken = Application::get('user_credential')->createRememberMeClientToken(0, '1.2.3.4', null,
            time() + 60);
        $token = Application::get('rememberme_encoder')->parseClientToken($clientToken);

        Application::get('user_credential')->invalidateRememberMeClientToken($clientToken);

        $throw = false;
        try {
            Application::get('user_credential')->checkCookieTokenCredential($clientToken, true);
        } catch (UserException $e) {
            $throw = true;
            $this->assertEquals('UserCredential.checkCookieTokenCredential.invalid_rememberme_token',
                $e->getUserErrorCode());
        }
        $this->assertTrue($throw, 'Expect thrown exception');

        $record = Application::coll('RememberMeToken')->findOne([
            'uid' => $token['uid'],
            'token' => $token['token'],
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