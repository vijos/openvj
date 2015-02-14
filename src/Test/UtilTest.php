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

use VJ\Util;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    public function testSlowEquals()
    {
        $this->assertTrue(Util::slowEquals('abcdefg', 'abcdefg'));
        $this->assertTrue(Util::slowEquals('abcdEfg', 'abcdEfg'));
        $this->assertTrue(Util::slowEquals('/abc/*defg', '/abc/*defg'));
        $this->assertFalse(Util::slowEquals('abcd', 'abc'));
        $this->assertFalse(Util::slowEquals('abc', 'abcd'));
        $this->assertFalse(Util::slowEquals('abce', 'abcd'));
        $this->assertFalse(Util::slowEquals('babc', 'cabc'));
        $this->assertFalse(Util::slowEquals('abcdef', 'abxdef'));
    }
} 