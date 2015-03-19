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
use VJ\Core\Event\GenericEvent;
use VJ\Core\Request;

class LoginLogService
{
    /** @var Request $request */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function onEvent(GenericEvent $event, $type, $user)
    {
        $ip = $this->request->getClientIp();
        $userAgent = $this->request->getUserAgent();

        $doc = [
            'uid' => (int)$user['uid'],
            'at' => new \MongoDate(),
            'type' => (int)$type,
            'ua' => $userAgent,
            'ip' => $ip
        ];
        Application::coll('LoginLog')->insert($doc);
    }
}