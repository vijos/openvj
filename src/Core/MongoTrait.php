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

trait MongoTrait
{
    /**
     * @param string $collection
     * @return \MongoCollection
     */
    public static function coll($collection)
    {
        return Application::get('mongodb')->selectCollection($collection);
    }
} 