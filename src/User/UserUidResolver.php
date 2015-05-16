<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\User;

use Respect\Validation\Validator;
use VJ\Core\Application;
use VJ\Core\Exception\InvalidArgumentException;
use VJ\DeferredResolver;

class UserUidResolver extends DeferredResolver
{
    protected function preprocessValue(&$uid)
    {
        if (!Validator::int($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }
        $uid = (int)$uid;
    }

    protected function query($chunk)
    {
        return Application::coll('User')->find([
            'uid' => ['$in' => $chunk]
        ]);
    }

    protected function getValueField($document)
    {
        return (string)$document['uid'];
    }
}