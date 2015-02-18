<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Mail;

use VJ\BackgroundUtil;

class BgServiceMailingProvider implements MailingProviderInterface
{
    public function send($type, array $to, $subject, $html)
    {
        $response = BackgroundUtil::post('/mail/send/' . $type)->body([
            'to' => $to,
            'subject' => $subject,
            'html' => $html,
        ], 'json')->send();

        $body = (array)$response->body;

        return $body;
    }
}