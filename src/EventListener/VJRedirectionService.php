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

use VJ\Core\Application;
use VJ\Core\EventListener;
use VJ\Core\Request;
use VJ\Core\Response;

class VJRedirectionService extends EventListener
{
    // route.dispatch.before
    public function onEvent($event, ...$argv)
    {
        $this->redirect(
            Application::get('config')['security']['enforce_https'],
            $argv[0],
            $argv[1]);
    }

    /**
     * @param bool $enforceHttps
     * @param Request $request
     * @param Response $response
     */
    public function redirect($enforceHttps, Request $request, Response $response)
    {
        if (stripos($request->getRequestUri(), '.asp') !== false) {

            $ua = $request->headers->get('user-agent');

            if (
                $enforceHttps &&
                $ua !== null &&
                stripos($ua, 'Baiduspider') === false &&
                stripos($ua, 'Sogou web spider') === false &&
                stripos($ua, 'Sosospider') === false
            ) {
                $prefix = 'https://';
            } else {
                $prefix = 'http://';
            }

            $uri = $request->getRequestUri();
            $host = Application::get('config')['canonical'];

            if (stripos($uri, '/problem_show.asp') !== false) {
                $location = $prefix . $host . '/problem/' . $request->query->get('id');
            } else {
                if (stripos($uri, '/user_show.asp') !== false) {
                    $location = $prefix . $host . '/user/' . $request->query->get('id');
                } else {
                    if (stripos($uri, '/problem_discuss.asp') !== false) {
                        $location = $prefix . $host . '/problem/' . $request->query->get('id');
                    } else {
                        if (stripos($uri, '/problem_discuss_show.asp') !== false) {
                            $location = $prefix . $host . '/problem/' . $request->query->get('id');
                        } else {
                            if (stripos($uri, '/problem2.asp') !== false) {
                                $location = $prefix . $host . '/problem';
                            } else {
                                $location = $prefix . $host;
                            }
                        }
                    }
                }
            }

            $response->redirect($location, true);
        }

        //TODO: redirect VJ2 style URIs
    }
} 