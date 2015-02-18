<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Core;

class Request extends \Symfony\Component\HttpFoundation\Request
{
    /**
     * 获取原始 UserAgent
     *
     * @return array|string
     */
    public function getUserAgentRaw()
    {
        return $this->headers->get('user-agent');
    }

    /**
     * 获取经过截断后的 UserAgent
     *
     * @return null|string
     */
    public function getUserAgent()
    {
        $ua = $this->getUserAgentRaw();
        if (!is_string($ua) || !mb_check_encoding($ua, 'UTF-8')) {
            return null;
        } else {
            return mb_substr($ua, 0, 256);
        }
    }
} 