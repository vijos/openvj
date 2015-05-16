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
use VJ\User\DomainManager;
use VJ\User\DomainUtil;

class DomainService
{
    /**
     * @var DomainManager $domain_manager
     */
    private $domain_manager;

    /**
     * @param DomainManager $domain_manager
     */
    public function __construct(DomainManager $domain_manager)
    {
        $this->domain_manager = $domain_manager;
    }

    public function onEvent(GenericEvent $event, $eventName, $uid)
    {
        switch ($eventName) {
            case 'user.created':
                $this->domain_manager->joinDomainById($uid, DomainUtil::getGlobalDomainId());
                break;
        }
    }
}