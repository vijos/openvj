<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ;

use Httpful\Request;
use VJ\Core\Application;

class BackgroundUtil
{
    /**
     * 传递 clientAuth 证书，并要求检验 API 服务器证书
     *
     * @param Request $request
     * @return Request
     */
    private static function authenticate(Request $request)
    {
        $request->additional_curl_opts[CURLOPT_SSL_VERIFYPEER] = true;
        $request->additional_curl_opts[CURLOPT_SSL_VERIFYHOST] = 0;
        $request->additional_curl_opts[CURLOPT_CAINFO] = Application::$CONFIG_DIRECTORY . '/cert-ca.crt';

        return $request->authenticateWithCert(
            Application::$CONFIG_DIRECTORY . '/cert-bg-client.crt',
            Application::$CONFIG_DIRECTORY . '/cert-bg-client.key'
        );
    }

    /**
     * GET
     *
     * @param $url
     * @return Request
     */
    public static function get($url)
    {
        return self::authenticate(Request::get(Application::getConfig('background.url') . $url));
    }

    /**
     * POST
     *
     * @param $url
     * @return Request
     */
    public static function post($url)
    {
        return self::authenticate(Request::post(Application::getConfig('background.url') . $url));
    }
}