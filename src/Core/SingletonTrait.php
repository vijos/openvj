<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Core;

trait SingletonTrait
{
    protected static $instance = null;

    /**
     * @return Application
     */
    public static function Instance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * protected to prevent cloning
     */
    protected function __clone()
    {
    }
}