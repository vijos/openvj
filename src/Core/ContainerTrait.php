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

trait ContainerTrait
{
    /**
     * @param string $n
     * @param mixed $v
     */
    public static function set($n, $v)
    {
        Application::$container[$n] = $v;
    }

    /**
     * @param string $n
     * @return mixed
     */
    public static function get($n)
    {
        return Application::$container[$n];
    }

    /**
     * @param string $n
     */
    public static function delete($n)
    {
        unset(Application::$container[$n]);
    }

    /**
     * @param string $n
     * @return bool
     */
    public static function has($n)
    {
        return Application::exists($n);
    }

    /**
     * @param string $n
     * @return bool
     */
    public static function exists($n)
    {
        return isset(Application::$container[$n]);
    }
} 