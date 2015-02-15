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

trait TranslationTrait
{
    /**
     * @param string $id
     * @param array $parameters
     * @return string
     */
    public static function trans($id, $parameters = [])
    {
        return Application::get('i18n')->trans($id, $parameters);
    }

    /**
     * @param string $code
     * @param array $parameters
     * @return string
     */
    public static function transExceptionCode($code, $parameters = [])
    {
        return self::trans('error.code.' . $code, $parameters);
    }

    /**
     * @param string $msg
     * @param array $parameters
     * @return string
     */
    public static function transExceptionMessage($msg, $parameters = [])
    {
        return self::trans('error.msg.' . $msg, $parameters);
    }
}