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

use VJ\Core\Event\GenericEvent;
use VJ\Core\Request;
use VJ\Core\Response;

class VJRedirectionService
{
    private $request;
    private $response;
    private $canonical;
    private $enforceHttps;

    /**
     * @param Request $request
     * @param Response $response
     * @param string $canonical
     * @param bool $enforceHttps
     */
    public function __construct(Request $request, Response $response, $canonical, $enforceHttps = false)
    {
        $this->request = $request;
        $this->response = $response;
        $this->canonical = $canonical;
        $this->enforceHttps = $enforceHttps;
    }

    // route.dispatch.before
    public function onEvent(GenericEvent $event)
    {
        if (stripos($this->request->getRequestUri(), '.asp') !== false) {
            $ua = $this->request->getUserAgent();
            if (
                $this->enforceHttps &&
                $ua !== null &&
                stripos($ua, 'Baiduspider') === false &&
                stripos($ua, 'Sogou web spider') === false &&
                stripos($ua, 'Sosospider') === false
            ) {
                $prefix = 'https://';
            } else {
                $prefix = 'http://';
            }

            $uri = $this->request->getRequestUri();
            $host = $this->canonical;

            if (stripos($uri, '/problem_show.asp') !== false) {
                $location = $prefix . $host . '/problem/' . $this->request->query->get('id');
            } elseif (stripos($uri, '/user_show.asp') !== false) {
                $location = $prefix . $host . '/user/' . $this->request->query->get('id');
            } elseif (stripos($uri, '/problem_discuss.asp') !== false) {
                $location = $prefix . $host . '/problem/' . $this->request->query->get('id');
            } elseif (stripos($uri, '/problem_discuss_show.asp') !== false) {
                $location = $prefix . $host . '/problem/' . $this->request->query->get('id');
            } elseif (stripos($uri, '/problem2.asp') !== false) {
                $location = $prefix . $host . '/problem';
            } else {
                $location = $prefix . $host;
            }

            $this->response->redirect($location, true);
            $event->stopPropagation();

            //TODO: redirect VJ2 style URIs
        }
    }

} 