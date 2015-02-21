<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Core\Exception;

use VJ\Core\Application;

class InvalidArgumentException extends UserException
{
    /**
     * @param string $name
     * @param string $msg
     * @param array $msgPlaceholder
     */
    public function __construct($name, $msg, $msgPlaceholder = [])
    {
        parent::__construct('invalid_argument', [
            '%name%' => $name,
            '%msg%' => Application::transExceptionMessage($msg, $msgPlaceholder),
        ]);
    }
}