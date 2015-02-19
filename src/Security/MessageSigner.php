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

use VJ\VJ;

class MessageSigner
{
    private $hashAlgo;
    private $secret;

    /**
     * @param string $hashAlgo
     * @param string $secret
     */
    public function __construct($hashAlgo, $secret)
    {
        $this->hashAlgo = $hashAlgo;
        $this->secret = $secret;
    }

    /**
     * 对一个消息进行签名，返回 MAC
     *
     * @param string $message
     * @return string
     */
    public function sign($message)
    {
        return hash_hmac($this->hashAlgo, $message, $this->secret);
    }

    /**
     * 检验消息和 MAC 是否匹配
     *
     * @param $message
     * @param $sign
     * @return bool
     */
    public function verify($message, $sign)
    {
        if (!is_string($message) || !is_string($sign)) {
            return false;
        }
        $messageSign = $this->sign($message);
        return VJ::slowEquals($messageSign, $sign);
    }
}