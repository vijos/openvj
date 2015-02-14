<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\User;

use VJ\Core\Application;

class KeywordFilter
{
    /**
     * Naive keyword filter
     *
     * @param string $text
     * @param string $cacheKey
     * @param callable $miss
     * @return bool|string
     */
    public static function isContain($text, $cacheKey, callable $miss)
    {
        $value = Application::get('redis')->get('naive-filter-' . $cacheKey);
        if ($value === false) {
            $keywords = $miss();
            Application::get('redis')->set('naive-filter-' . $cacheKey, serialize($keywords));
        } else {
            $keywords = unserialize($value);
        }

        $text = strtolower($text);

        foreach ($keywords as $keyword) {
            if (strpos($text, $keyword)) {
                return $keyword;
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
    public static function isContainGeneral($text)
    {
        return self::isContain($text, 'general', function () {
            $rec = Application::coll('System')->findOne([
                '_id' => 'FilterKeyword'
            ]);
            if ($rec) {
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
        return self::isContain($text, 'name', function () {
            $rec = Application::coll('System')->findOne([
                '_id' => 'FilterKeyword'
            ]);
            if ($rec) {
                return [];
            } else {
                return $rec['name'];
            }
        });
    }
}