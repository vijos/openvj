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
use VJ\Core\EventListener;
use VJ\Core\Request;
use VJ\Core\Response;

class HttpsRedirectionService extends EventListener
{
    // route.dispatch.before
    public function onEvent($event, ...$argv)
    {
        $this->redirect(
            Application::get('config')['security']['enforce_https'],
            $argv[0],
            $argv[1]);
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
            $ua = $request->headers->get('user-agent');
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