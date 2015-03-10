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
    private $prefix = 'keyword-filter-test-';
    private $key = 'simple-keywords';

    public function testBuildTree()
    {
        $redis = Application::get('redis');
        $filter = new KeywordFilter($redis, $this->prefix);
        $redis->delete($this->prefix . $this->key);
        $filter->contains('The quick brown foo jumps over the lazy dog', $this->key, function () {
            return ['foo', 'bar', 'baz', 'quux', 'thud', 'grunt'];
        });
        $this->assertNotNull($redis->get($this->prefix . $this->key));
    }

    public function testSimpleKeywords()
    {
        $redis = Application::get('redis');
        $filter = new KeywordFilter($redis, $this->prefix);
        $miss = function () {
            return ['foo', 'bar', 'baz', 'quux', 'thud', 'grunt'];
        };

        $this->assertEquals($filter->contains(
            'The quick brown foo jumps over the lazy dog', $this->key, $miss), 'foo');
        $this->assertEquals($filter->contains(
            'The quick brown fox jumps over the lazy dog', $this->key, $miss), false);
        $this->assertEquals($filter->contains(
            'The quick brown bar jumps over the lazy baz', $this->key, $miss), 'bar');
        $this->assertEquals($filter->contains(
            'The quick brown BAR jumps over the lazy dog', $this->key, $miss), 'bar');
        $this->assertEquals($filter->contains(
            'The quick brown FOX jumps over the lazy dog', $this->key, $miss), false);
        $this->assertEquals($filter->contains(
            'The quick brown quux grunts over the lazy thud', $this->key, $miss), 'quux');
    }
}
