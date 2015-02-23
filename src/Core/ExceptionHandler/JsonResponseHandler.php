<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Core\ExceptionHandler;

use VJ\Core\Exception\UserException;
use Whoops\Exception\Formatter;
use Whoops\Handler\Handler;
use Whoops\Util\Misc;

class JsonResponseHandler extends Handler
{
    private $debug;

    public function __construct($debug)
    {
        $this->debug = $debug;
    }

    private function isAjaxRequest()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    public function handle()
    {
        if (!$this->isAjaxRequest()) {
            return Handler::DONE;
        }

        $ex = $this->getException();
        if ($ex instanceof UserException) {
            $obj = [
                'message' => $ex->getMessage(),
                'code' => $ex->getUserErrorCode(),
                'status' => $ex->getCode()
            ];
        } else {
            if ($this->debug) {
                $obj = [
                    'message' => $ex->getMessage(),
                    'code' => $ex->getCode(),
                    'status' => 500
                ];
            } else {
                $obj = [
                    'message' => 'Server internal error',
                    'code' => -1,
                    'status' => 500
                ];
            }
        }

        if ($this->debug) {
            $obj['detail'] = Formatter::formatExceptionAsDataArray($this->getInspector(), true);
        }

        $this->getRun()->sendHttpCode($obj['status']);
        if (Misc::canSendHeaders()) {
            header('Content-Type: application/json');
        }

        echo json_encode($obj);
        return Handler::QUIT;
    }
}
