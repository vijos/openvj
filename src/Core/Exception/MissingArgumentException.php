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
use VJ\Core\Response;

class MissingArgumentException extends UserException
{
    public function __construct($name)
    {
        parent::__construct('missing_argument', Response::HTTP_BAD_REQUEST, [
            'name' => Application::transExceptionArgumentName($name),
        ]);
    }
}