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
use VJ\Core\Event\GenericEvent;
use VJ\Core\Request;
use VJ\Core\Response;

class HttpsRedirectionService
{
    private $request;
    private $response;
    private $enforceHttps;

    /**
     * @param Request $request
     * @param Response $response
     * @param bool $enforceHttps
     */
    public function __construct(Request $request, Response $response, $enforceHttps = false)
    {
        $this->request = $request;
        $this->response = $response;
        $this->enforceHttps = $enforceHttps;
    }

    // route.dispatch.before
    public function onEvent(GenericEvent $event)
    {
        if (!$this->enforceHttps) {
            return;
        }

        $nossl = $this->request->get('nossl');
        if ($nossl !== null) {
            $nossl = strtolower($nossl);
            if ($nossl === 'false' || $nossl === 'off') {
                $this->response->headers->clearCookie('nossl');
                $this->request->cookies->remove('nossl');
            } else {
                $this->response->headers->setCookie(new Cookie('nossl', 'on'));
                $this->request->cookies->set('nossl', 'on');
            }
        }

        if (!$this->request->isSecure() && $this->request->cookies->get('nossl') == null) {
            $ua = $this->request->headers->get('user-agent');
            if (
                $ua !== null &&
                stripos($ua, 'Baiduspider') === false &&
                stripos($ua, 'Sogou web spider') === false &&
                stripos($ua, 'Sosospider') === false
            ) {
                $this->response->redirect('https://' .
                    $this->request->headers->get('host', Application::get('config')['canonical']) .
                    $this->request->getRequestUri());
                $event->stopPropagation();
            }
        }
    }
} 