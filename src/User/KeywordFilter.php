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
}