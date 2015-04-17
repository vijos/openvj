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

class Exception extends \Exception
{
    protected $rawErrorCode;

    /**
     * @param string $exceptionCode
     * @param array $placeholder
     * @param int $httpCode
     */
    public function __construct($exceptionCode, $placeholder = [], $httpCode = Response::HTTP_BAD_REQUEST)
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