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

use VJ\User\RememberMeEncoder;

class RememberMeEncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseInvalidClientToken1()
    {
        RememberMeEncoder::parseClientToken('abc');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseInvalidClientToken2()
    {
        RememberMeEncoder::parseClientToken('abc|abc');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseInvalidClientToken3()
    {
        RememberMeEncoder::parseClientToken('1|a|abc');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseInvalidClientToken4()
    {
        RememberMeEncoder::parseClientToken('1|1|abc');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseInvalidClientToken5()
    {
        RememberMeEncoder::parseClientToken('1.5|1|12345678123456781234567812345678');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseInvalidClientToken6()
    {
        RememberMeEncoder::parseClientToken('0x14|1|12345678123456781234567812345678');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseInvalidClientToken7()
    {
        RememberMeEncoder::parseClientToken('1|-100|12345678123456781234567812345678');
    }

    public function testParseClientToken()
    {
        $raw = '12345678123456781234567812345678';
        $sha256 = hash('sha256', '-100|12345|' . $raw);
        $token = RememberMeEncoder::parseClientToken('-100|12345|' . $raw);
        $this->assertEquals(-100, $token['uid']);
        $this->assertEquals(12345, $token['expire']);
        $this->assertEquals($sha256, $token['token']);
    }

    public function testGenerateClientToken()
    {
        $token1 = RememberMeEncoder::generateClientToken(-100, 12345);
        $this->assertEquals(4 + 2 + 5 + 32, strlen($token1));
        $this->assertStringStartsWith('-100|12345|', $token1);

        $token2 = RememberMeEncoder::generateClientToken(12345678, 54321);
        $this->assertEquals(8 + 2 + 5 + 32, strlen($token2));
        $this->assertStringStartsWith('12345678|54321|', $token2);

        $this->assertNotEquals(substr($token1, -32), substr($token2, -32));
    }
} 