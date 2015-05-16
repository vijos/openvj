<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\User\Resolver;

use VJ\Core\Application;

class VijosResolver
{
    private $source;

    public function __construct(\MongoDB $source)
    {
        $this->source = $source;
    }

    /**
     * 接收 Vijos UID，返回数据库中的 _id
     *
     * @param $vijosUID
     * @return array
     */
    public function resolveId($vijosUID)
    {
        $user = Application::coll('User')->find([
            'uid' => (int)$vijosUID
        ]);

        return $user['_id'];
    }
}