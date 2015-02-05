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
     * @param string $messageCode
     * @param int $httpCode
     * @param array $placeholder
     */
    public function __construct($messageCode, $httpCode = 0, $placeholder = array())
    {
        $this->rawErrorCode = $messageCode;

        parent::__construct(
            Application::get('i18n')->trans($messageCode, $placeholder), $httpCode
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