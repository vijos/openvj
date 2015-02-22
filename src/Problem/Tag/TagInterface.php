<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Problem\Tag;

interface TagInterface
{
    /**
     * 获取标签显示名称
     *
     * @return string
     */
    public function getDisplayName();

    /**
     * 获取标签分段数据
     *
     * @return string[]
     */
    public function getParts();

    /**
     * 获取标签类别
     *
     * @return string
     */
    public function getType();
}