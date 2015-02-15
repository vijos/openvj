<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Core\Event;

use Symfony\Component\EventDispatcher\Event;

class GenericEvent extends Event
{
    private $argv;

    public function __construct(array $argv = [])
    {
        $this->argv = $argv;
    }

    public function getArgv()
    {
        return $this->argv;
    }
}