<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\User\Importer;

use VJ\Core\Application;

class VijosUidResolver
{
    private $source;

    public function __construct(\MongoDB $source)
    {
        $this->source = $source;
    }

    /**
     * 接收 Vijos Uid，返回其位于数据库中的 _id
     *
     * @param $vijosUid
     * @return array
     */
    public function resolveUid($vijosUid)
    {
        $user = Application::coll('User')->find([
            'uid' => (int)$vijosUid
        ]);

        return $user['_id'];
    }
}