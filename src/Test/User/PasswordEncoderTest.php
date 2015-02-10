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

use VJ\User\PasswordEncoder;

class PasswordEncoderTest extends \PHPUnit_Framework_TestCase
{
    public function testEncodeVJ2()
    {
        $password = 'this_is_test_password';
        $salt = 'a4c4faf1b8cb70c640c1ee9b8df2d31f47ad36fc';
        $username = '世界你好';
        $hash = 'vj2|5LiW55WM5L2g5aW9|488fdef0417301a0541aefd562806ac24a4d8a67';
        $this->assertEquals($hash,
            PasswordEncoder::encode($password, $salt, PasswordEncoder::HASH_TYPE_VJ2, $username));
    }

    public function testEncodeOpenVJ()
    {
        $password = 'this_is_test_password';
        $salt = 'a4c4faf1b8cb70c640c1ee9b8df2d31f47ad36fc';
        $hash = 'openvj|$2y$10$a4c4faf1b8cb70c640c1eeWT./4q1ijyFoaSAi2lYr2zXW/MKJify';
        $this->assertEquals($hash, PasswordEncoder::encode($password, $salt, PasswordEncoder::HASH_TYPE_OPENVJ));
    }

    /**
     * @expectedException \VJ\Core\Exception\InvalidArgumentException
     */
    public function testEncodeInvalid1()
    {
        $this->assertFalse(PasswordEncoder::encode('123', 'shortsalt', PasswordEncoder::HASH_TYPE_OPENVJ));
    }

    /**
     * @expectedException \VJ\Core\Exception\InvalidArgumentException
     */
    public function testEncodeInvalid2()
    {
        $this->assertFalse(PasswordEncoder::encode('123', '1234567890123456789012', 'invalid type'));
    }

    /**
     * @expectedException \VJ\Core\Exception\MissingArgumentException
     */
    public function testEncodeInvalid3()
    {
        $this->assertFalse(PasswordEncoder::encode('123', '1234567890123456789012', PasswordEncoder::HASH_TYPE_VJ2));
    }

    public function testVerifyInvalid()
    {
        $this->assertFalse(PasswordEncoder::verify('a', '1234567890123456789012', 'x'));
        $this->assertFalse(PasswordEncoder::verify('a', '1234567890123456789012', 'vj2|x'));
        $this->assertFalse(PasswordEncoder::verify('a', '1234567890123456789012', 'vj2|哟|10000'));
        $this->assertFalse(PasswordEncoder::verify('a', '1234567890123456789012', 'openvj|'));
    }

    public function testVerifyVJ2()
    {
        $password = 'this_is_test_password';
        $salt = 'a4c4faf1b8cb70c640c1ee9b8df2d31f47ad36fc';
        $hash = 'vj2|5LiW55WM5L2g5aW9|488fdef0417301a0541aefd562806ac24a4d8a67'; // username="世界你好"
        $this->assertTrue(PasswordEncoder::verify($password, $salt, $hash));

        $password = 'this_is_wrong_test_password';
        $salt = 'a4c4faf1b8cb70c640c1ee9b8df2d31f47ad36fc';
        $hash = 'vj2|5LiW55WM5L2g5aW9|488fdef0417301a0541aefd562806ac24a4d8a67'; // username="世界你好"
        $this->assertFalse(PasswordEncoder::verify($password, $salt, $hash));

        $password = 'this_is_test_password';
        $salt = 'a4c4faf1b8cb70c640c1ee9b8df2d31f47ad36fc';
        $hash = 'vj2|IOS4lueVjOS9oOWlvQ==|488fdef0417301a0541aefd562806ac24a4d8a67'; // username=" 世界你好"
        $this->assertFalse(PasswordEncoder::verify($password, $salt, $hash));
    }

    public function testVerifyOpenVJ()
    {
        $password = 'this_is_test_password';
        $salt = 'a4c4faf1b8cb70c640c1ee9b8df2d31f47ad36fc';
        $hash = 'openvj|$2y$10$a4c4faf1b8cb70c640c1eeWT./4q1ijyFoaSAi2lYr2zXW/MKJify';
        $this->assertTrue(PasswordEncoder::verify($password, $salt, $hash));
    }

    public function testGenerateSalt()
    {
        $salt1 = PasswordEncoder::generateSalt();
        $salt2 = PasswordEncoder::generateSalt();
        $this->assertEquals(60, strlen($salt1));
        $this->assertEquals(60, strlen($salt2));
        $this->assertNotEquals($salt1, $salt2);
    }

    public function testGenerateHash()
    {
        $hash = PasswordEncoder::generateHash('hello_world');
        $this->assertNotNull($hash['salt']);
        $this->assertNotNull($hash['hash']);
        $this->assertFalse(PasswordEncoder::verify('hello', $hash['salt'], $hash['hash']));
        $this->assertTrue(PasswordEncoder::verify('hello_world', $hash['salt'], $hash['hash']));
    }

} 