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

use Respect\Validation\Validator;
use VJ\Core\Exception\InvalidArgumentException;

class Sender
{
    private $provider;

    public function __construct(MailingProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    private function send($type, array $to, $subject, $html)
    {
        if (!Validator::arr()->each(Validator::email())->validate($to)) {
            throw new InvalidArgumentException('to', 'format_invalid');
        }
        $this->provider->send($type, $to, $subject, $html);
    }

    public function sendVerification(array $to, $subject, $html)
    {
        $this->send('verification', $to, $subject, $html);
    }

    public function sendReport(array $to, $subject, $html)
    {
        $this->send('report', $to, $subject, $html);
    }
}