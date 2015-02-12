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

trait LoggerTrait
{
    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function debug($message, $context = array())
    {
        return Application::get('log')->addDebug($message, ['trace' => debug_backtrace()] + $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function info($message, $context = array())
    {
        return Application::get('log')->addInfo($message, self::getClientInfo() + $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function notice($message, $context = array())
    {
        return Application::get('log')->addNotice($message, self::getClientInfo() + $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function warn($message, $context = array())
    {
        return Application::get('log')->addWarning($message, self::getClientInfo() + $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function error($message, $context = array())
    {
        return Application::get('log')->addError($message, self::getClientInfo() + $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function critical($message, $context = array())
    {
        return Application::get('log')->addCritical($message, self::getClientInfo() + $context);
    }

    /**
     * @param int $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function log($level, $message, $context = array())
    {
        return Application::get('log')->addRecord($level, $message, $context);
    }

    /**
     * @return array
     */
    public static function getClientInfo()
    {
        return [
            'ip' => Application::get('request')->getClientIp(),
            'userAgent' => mb_substr(Application::get('request')->headers->get('user-agent'), 0, 256)
        ];
    }
} 