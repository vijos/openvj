<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Test\Security;

use VJ\Security\MessageSigner;

class MessageSignerTest extends \PHPUnit_Framework_TestCase
{
    public function testSignAndVerifyMessage()
    {
        $signer = new MessageSigner('sha256', 'a_secret');
        $msg_1 = 'this_is_a_message';
        $mac_1 = $signer->sign($msg_1);
        $msg_2 = 'this_is_a_message_';
        $mac_2 = $signer->sign($msg_2);

        $this->assertTrue($signer->verify($msg_1, $mac_1));
        $this->assertTrue($signer->verify($msg_2, $mac_2));
        $this->assertNotEquals($mac_1, $mac_2);

        $this->assertFalse($signer->verify($msg_2, $mac_1));
        $this->assertFalse($signer->verify($msg_1, $mac_2));

        $this->assertFalse($signer->verify($msg_1, $mac_1 . 'x'));
        $this->assertFalse($signer->verify($msg_1 . 'x', $mac_1));
        $this->assertFalse($signer->verify($msg_1 . 'x', $mac_1 . 'x'));
    }
}