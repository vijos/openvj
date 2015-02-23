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
use VJ\Core\Application;
use VJ\Core\Exception\InvalidArgumentException;

class Sender
{
    private $provider;

    public function __construct(MailingProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    private function send($type, array $to, $subject, $template, $params)
    {
        if (!Validator::arr()->each(Validator::email())->validate($to)) {
            throw new InvalidArgumentException('to', 'format_invalid');
        }

        $html = Application::get('templating')->render($template, array_merge([
            'SUBJECT' => $subject
        ], $params));
        $this->provider->send($type, $to, $subject, $html);
    }

    public function sendVerification(array $to, $subject, $template, $params)
    {
        $this->send('verification', $to, $subject, $template, $params);
    }

    public function sendReport(array $to, $subject, $template, $params)
    {
        $this->send('report', $to, $subject, $template, $params);
    }
}