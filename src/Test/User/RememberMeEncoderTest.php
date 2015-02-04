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
        RememberMeEncoder::parseClientToken('1|abc');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseInvalidClientToken4()
    {
        RememberMeEncoder::parseClientToken('1.5|abc');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseInvalidClientToken5()
    {
        RememberMeEncoder::parseClientToken('0x14|abc');
    }

    public function testParseClientToken()
    {
        $raw = '12345678123456781234567812345678';
        $sha256 = 'fed9efbd5a8ef6820d639dbcb831daf9d6308312cc73d6188beb54a9a148e29a';
        $token = RememberMeEncoder::parseClientToken('-100|' . $raw);
        $this->assertEquals(-100, $token['uid']);
        $this->assertEquals($sha256, $token['token']);
    }

    public function testGenerateClientToken()
    {
        $token1 = RememberMeEncoder::generateClientToken(-100);
        $this->assertEquals(4 + 1 + 32, strlen($token1));
        $this->assertEquals('-100|', substr($token1, 0, 5));

        $token2 = RememberMeEncoder::generateClientToken(12345678);
        $this->assertEquals(8 + 1 + 32, strlen($token2));
        $this->assertEquals('12345678|', substr($token2, 0, 9));

        $this->assertNotEquals(substr($token1, 5), substr($token2, 9));
    }
} 