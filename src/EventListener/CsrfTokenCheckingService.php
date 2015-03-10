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

use FastRoute\Dispatcher;
use VJ\Core\Event\GenericEvent;
use VJ\Core\Exception\UserException;
use VJ\Core\Request;
use VJ\Core\Response;
use VJ\User\UserSession;

class CsrfTokenCheckingService
{
    private $request;
    private $session;

    /**
     * @param Request $request
     * @param UserSession $session
     */
    public function __construct(Request $request, UserSession $session)
    {
        $this->request = $request;
        $this->session = $session;
    }

    public function onEvent(GenericEvent $event, $dispatchStatus, array $dispatchHandler = null)
    {
        if ($dispatchStatus == Dispatcher::FOUND) {
            if (isset($dispatchHandler['csrf']) && $dispatchHandler['csrf'] === true) {
                $csrfToken = $this->request->headers->get('csrfToken');
                if ($csrfToken === null) {
                    $csrfToken = $this->request->request->get('csrfToken');
                }
                if ($csrfToken === null) {
                    $csrfToken = $this->request->query->get('csrfToken');
                }
                if ($csrfToken !== $this->session->getCsrfToken()) {
                    throw new UserException('invalid_csrf_token', [], Response::HTTP_FORBIDDEN);
                }
            }
        }
    }
}