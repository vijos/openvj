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

use VJ\Core\Response;

class CoreException extends Exception
{
    /**
     * @param string $exceptionCode
     * @param array $placeholder
     */
    public function __construct($exceptionCode, $placeholder = [])
    {
        parent::__construct($exceptionCode, $placeholder, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}