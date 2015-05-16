<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ;

class DeferredResolve
{
    private $to;

    public function __construct(array &$to)
    {
        $this->to = &$to;
    }

    /**
     * 将请求结果记录到指定对象
     *
     * @param $target
     */
    public function to(&$target)
    {
        $this->to[] = &$target;
    }
}