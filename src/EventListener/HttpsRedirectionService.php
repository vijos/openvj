<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use VJ\Core\Application;
use VJ\Core\Request;
use VJ\Core\Response;
use VJ\Util;

class HttpsRedirectionService
{
    // route.dispatch.before
    public function onEvent($event, Request $request, Response $response)
    {
        $this->redirect(Application::get('config')['security']['enforce_https'], $request, $response);
    }

    public function redirect($enforceHttps, Request $request, Response $response)
    {
        if (!$enforceHttps) {
            return;
        }

        $nossl = $request->get('nossl');
        if ($nossl !== null) {
            $nossl = strtolower($nossl);
            if ($nossl === 'false' || $nossl === 'off') {
                $response->headers->clearCookie('nossl');
                $request->cookies->remove('nossl');
            } else {
                $response->headers->setCookie(new Cookie('nossl', 'on'));
                $request->cookies->set('nossl', 'on');
            }
        }

        if (!$request->isSecure() && $request->cookies->get('nossl') == null) {
            $ua = Util::getUserAgentSafe();
            if (
                $ua !== null &&
                stripos($ua, 'Baiduspider') === false &&
                stripos($ua, 'Sogou web spider') === false &&
                stripos($ua, 'Sosospider') === false
            ) {
                $response->redirect('https://' .
                    $request->headers->get('host', Application::get('config')['canonical']) .
                    $request->getRequestUri());
            }
        }
    }
} 