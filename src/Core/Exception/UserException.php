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

class UserException extends \Exception
{
    private $rawErrorCode;

    /**
     * @param string $exceptionCode
     * @param int $httpCode
     * @param array $placeholder
     */
    public function __construct($exceptionCode, $httpCode = 0, $placeholder = array())
    {
        $this->rawErrorCode = $exceptionCode;

        parent::__construct(
            Application::transExceptionCode($exceptionCode, $placeholder), $httpCode
        );
    }

    /**
     * @return string
     */
    public function getUserErrorCode()
    {
        return $this->rawErrorCode;
    }
} 