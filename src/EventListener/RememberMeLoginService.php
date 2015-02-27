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
use VJ\User\UserManager;

class RememberMeLoginService
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function onEvent(GenericEvent $event)
    {
        if (!$this->userManager->isLoggedIn() && $this->userManager->isRememberMeTokenProvided()) {
            try {
                Application::get('user_manager')->rememberMeTokenLogin();
            } catch (\Exception $e) {
            }
        }
    }
}