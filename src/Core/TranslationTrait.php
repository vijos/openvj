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
    public static function trans($id, $parameters = array())
    {
        return Application::get('i18n')->trans($id, $parameters);
    }

    public static function transExceptionCode($code, $parameters = array())
    {
        return self::trans('error.code.' . $code, $parameters);
    }

    public static function transExceptionArgumentName($name, $parameters = array())
    {
        return self::trans('error.argument.' . $name, $parameters);
    }

    public static function transExceptionMessage($msg, $parameters = array())
    {
        return self::trans('error.msg.' . $msg, $parameters);
    }
}