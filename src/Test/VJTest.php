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

use VJ\VJ;

class VJTest extends \PHPUnit_Framework_TestCase
{
    public function testSlowEquals()
    {
        $this->assertTrue(VJ::slowEquals('abcdefg', 'abcdefg'));
        $this->assertTrue(VJ::slowEquals('abcdEfg', 'abcdEfg'));
        $this->assertTrue(VJ::slowEquals('/abc/*defg', '/abc/*defg'));
        $this->assertFalse(VJ::slowEquals('abcd', 'abc'));
        $this->assertFalse(VJ::slowEquals('abc', 'abcd'));
        $this->assertFalse(VJ::slowEquals('abce', 'abcd'));
        $this->assertFalse(VJ::slowEquals('babc', 'cabc'));
        $this->assertFalse(VJ::slowEquals('abcdef', 'abxdef'));
    }
}