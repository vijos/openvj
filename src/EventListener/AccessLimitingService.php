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

    public function onEvent(GenericEvent $event)
    {
        $identifier = $this->ir->getMixedIdentifier();
        if ($this->redis->get('SECURITY:DENY:' . $identifier) !== false) {
            $this->response->setStatusCode(Response::HTTP_FORBIDDEN);
            $this->response->headers->set('content-type', 'text/html');
            $this->response->setContent('<h1>Forbidden: Access denied</h1><p>Reason: Your IP is blacklisted temporarily. Please wait a moment and retry.</p>');
            $this->response->send();
            $event->stopPropagation();
        }
    }
}