<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Security;

use VJ\Core\Application;

class KeywordFilter
{
    private $redis;
    private $prefix;

    /**
     * @param \Redis $redis
     * @param string $prefix
     */
    public function __construct(\Redis $redis, $prefix = 'CACHE:FILTER')
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
    }

    /**
     * Naive keyword filter
     *
     * @param string $text
     * @param string $cacheKey
     * @param callable $miss
     * @return bool|string
     */
    public function contains($text, $cacheKey, callable $miss)
    {
        $value = $this->redis->hget($this->prefix, $cacheKey);
        if ($value === false) {
            // Haven't found data in the cache, we need to build the tree
            $tree = array();
            $keywords = $miss();
            foreach ($keywords as $keyword) {
                $ptr = 0;
                $len = strlen($keyword);
                assert("$len != 0");
                for ($idx = 0; $idx < $len; ++$idx) {
                    $ptr += ord($keyword[$idx]);
                    if (!isset($tree[$ptr])) {
                        if (!isset($tree[-1])) {
                            $tree[-1] = 1;
                        }
                        $tree[$ptr] = $tree[-1]++;
                    }
                    $ptr = $tree[$ptr] * 256;
                }
                $tree[$ptr] = -1;
            }
            $this->redis->hset($this->prefix, $cacheKey, serialize($tree));
        } else {
            $tree = unserialize($value);
        }

        $text = mb_strtolower($text, 'UTF-8');
        $active = array();
        $len = strlen($text);
        for ($cur = 0; $cur < $len; ++$cur) {
            $ord = ord($text[$cur]);
            $active[$cur] = 0;
            foreach ($active as $start => $ptr) {
                if (isset($tree[$ptr + $ord])) {
                    $ptr = $tree[$ptr + $ord] * 256;
                    if (isset($tree[$ptr])) {
                        return substr($text, $start, $cur - $start + 1);
                    }
                    $active[$start] = $ptr;
                } else {
                    unset($active[$start]);
                }
            }
        }

        return false;
    }

    /**
     * 测试是否包含通用关键字
     *
     * @param $text
     * @return bool|string
     */
    public static function isContainGeneric($text)
    {
        return Application::get('keyword_filter')->contains($text, 'general', function () {
            $rec = Application::coll('System')->findOne([
                '_id' => 'FilterKeyword'
            ]);
            if (!$rec) {
                return [];
            } else {
                return $rec['general'];
            }
        });
    }

    /**
     * 测试是否包含用于用户名的额外关键字
     *
     * @param $text
     * @return bool|string
     */
    public static function isContainName($text)
    {
        return Application::get('keyword_filter')->contains($text, 'name', function () {
            $rec = Application::coll('System')->findOne([
                '_id' => 'FilterKeyword'
            ]);
            if (!$rec) {
                return [];
            } else {
                return $rec['name'];
            }
        });
    }
}
