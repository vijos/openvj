<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Test\Security;

use VJ\Core\Application;
use VJ\Security\KeywordFilter;

class KeywordFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleKeywords() {
        $redis = Application::get('redis');
        $prefix = 'keyword-filter-test-';
        $filter = new KeywordFilter($redis, $prefix);
        $key = 'simple-keywords';
        $miss = function () {
            return ['foo', 'bar', 'baz', 'quux', 'thud', 'grunt'];
        };
        $this->assertEquals($filter->contains(
            'The quick brown foo jumps over the lazy dog', $key, $miss), 'foo');
        $this->assertEquals($filter->contains(
            'The quick brown fox jumps over the lazy dog', $key, $miss), false);
        $this->assertEquals($filter->contains(
            'The quick brown bar jumps over the lazy baz', $key, $miss), 'bar');
        $this->assertEquals($filter->contains(
            'The quick brown BAR jumps over the lazy dog', $key, $miss), 'bar');
        $this->assertEquals($filter->contains(
            'The quick brown FOX jumps over the lazy dog', $key, $miss), false);
        $this->assertEquals($filter->contains(
            'The quick brown quux grunts over the lazy thud', $key, $miss), 'quux');
    }
}
