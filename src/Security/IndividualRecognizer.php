<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Security;

use VJ\Core\Exception\InvalidArgumentException;
use VJ\Core\Request;
use VJ\User\UserSession;

class IndividualRecognizer
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

    /**
     * 取得用户标示符
     *
     * @return string
     */
    public function getUserIdentifier()
    {
        $user = $this->session->getUser();
        return 'uid=' . $user['uid'];
    }

    /**
     * 取得 IP 标示符
     *
     * @return string
     */
    public function getIpIdentifier()
    {
        return 'ip=' . $this->request->getClientIp();
    }

    /**
     * 取得混合标示符
     *
     * @return string
     */
    public function getMixedIdentifier()
    {
        return $this->getIpIdentifier() . ':' . $this->getUserIdentifier();
    }

    /**
     * 取得标示符
     *
     * @param $type
     * @return string
     * @throws InvalidArgumentException
     */
    public function getIdentifier($type)
    {
        switch ($type) {
            case 'ip':
                return $this->getIpIdentifier();
            case 'user':
                return $this->getUserIdentifier();
            case 'mixed':
                return $this->getMixedIdentifier();
            default:
                throw new InvalidArgumentException('type', 'value_invalid');
        }
    }
}