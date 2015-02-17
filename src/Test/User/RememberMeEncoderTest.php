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

class RememberMeEncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \VJ\Core\Exception\InvalidArgumentException
     */
    public function testParseInvalidClientToken1()
    {
        Application::get('rememberme_encoder')->parseClientToken('abc');
    }

    /**
     * @expectedException \VJ\Core\Exception\InvalidArgumentException
     */
    public function testParseInvalidClientToken2()
    {
        Application::get('rememberme_encoder')->parseClientToken('abc|abc');
    }

    /**
     * @expectedException \VJ\Core\Exception\InvalidArgumentException
     */
    public function testParseInvalidClientToken3()
    {
        Application::get('rememberme_encoder')->parseClientToken('1|a|abc');
    }

    /**
     * @expectedException \VJ\Core\Exception\InvalidArgumentException
     */
    public function testParseInvalidClientToken4()
    {
        Application::get('rememberme_encoder')->parseClientToken('1|1|abc');
    }

    /**
     * @expectedException \VJ\Core\Exception\InvalidArgumentException
     */
    public function testParseInvalidClientToken5()
    {
        Application::get('rememberme_encoder')->parseClientToken('1.5|1|12345678123456781234567812345678');
    }

    /**
     * @expectedException \VJ\Core\Exception\InvalidArgumentException
     */
    public function testParseInvalidClientToken6()
    {
        Application::get('rememberme_encoder')->parseClientToken('0x14|1|12345678123456781234567812345678');
    }

    /**
     * @expectedException \VJ\Core\Exception\InvalidArgumentException
     */
    public function testParseInvalidClientToken7()
    {
        Application::get('rememberme_encoder')->parseClientToken('1|-100|12345678123456781234567812345678');
    }

    public function testParseClientToken()
    {
        $raw = '12345678123456781234567812345678';
        $sha256 = hash('sha256', '-100|12345|' . $raw);
        $token = Application::get('rememberme_encoder')->parseClientToken('-100|12345|' . $raw);
        $this->assertEquals(-100, $token['uid']);
        $this->assertEquals(12345, $token['expire']);
        $this->assertEquals($sha256, $token['token']);
    }

    public function testGenerateClientToken()
    {
        $token1 = Application::get('rememberme_encoder')->generateClientToken(-100, 12345);
        $this->assertEquals(4 + 2 + 5 + 32, strlen($token1));
        $this->assertStringStartsWith('-100|12345|', $token1);

        $token2 = Application::get('rememberme_encoder')->generateClientToken(12345678, 54321);
        $this->assertEquals(8 + 2 + 5 + 32, strlen($token2));
        $this->assertStringStartsWith('12345678|54321|', $token2);

        $this->assertNotEquals(substr($token1, -32), substr($token2, -32));
    }
} 