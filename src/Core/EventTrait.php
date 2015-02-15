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

use VJ\Core\Event\GenericEvent;

trait EventTrait
{
    /**
     * @param string $event
     * @param callable $callback
     */
    public static function on($event, callable $callback)
    {
        Application::get('event')->addListener($event, $callback);
    }

    /**
     * @param string $event
     * @param array $argv
     */
    public static function emit($event, $argv = array())
    {
        Application::get('event')->dispatch($event, new GenericEvent($argv));
    }
} 