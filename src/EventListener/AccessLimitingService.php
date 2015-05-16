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
use VJ\Security\IndividualRecognizer;

class AccessLimitingService
{
    private $request;
    private $response;
    private $ir;
    private $redis;

    /**
     * @param Request $request
     * @param Response $response
     * @param IndividualRecognizer $ir
     * @param \Redis $redis
     */
    public function __construct(Request $request, Response $response, IndividualRecognizer $ir, \Redis $redis)
    {
        $this->request = $request;
        $this->response = $response;
        $this->ir = $ir;
        $this->redis = $redis;
    }

    public function onEvent(GenericEvent $event, $eventName)
    {
        $identifier = $this->ir->getMixedIdentifier();

        $denyExpire = $this->redis->get('SECURITY:DENY:' . $identifier);
        if ($denyExpire === false) {
            // not listed in blacklist
            return;
        }

        $this->response->setStatusCode(Response::HTTP_FORBIDDEN);
        $this->response->headers->set('content-type', 'text/html');
        $this->response->setContent(Application::trans('error.code.ip_blacklisted', [
            'seconds' => ($denyExpire === 0 ? mt_rand() : ceil(($denyExpire - time()) / 60) * 60)
        ]));
        $this->response->send();
        $event->stopPropagation();
    }
}