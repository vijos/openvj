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

use Respect\Validation\Validator;
use VJ\Core\Exception\InvalidArgumentException;
use VJ\Core\Exception\UserException;
use VJ\Security\KeywordFilter;
use VJ\VJ;

class SeriesTag extends Tag
{
    private $seriesName;

    /**
     * @param string $seriesName
     * @throws InvalidArgumentException
     * @throws UserException
     */
    public function __construct($seriesName)
    {
        if (!is_string($seriesName)) {
            throw new InvalidArgumentException('name', 'type_invalid');
        }
        if (!mb_check_encoding($seriesName, 'UTF-8')) {
            throw new InvalidArgumentException('name', 'encoding_invalid');
        }
        $keyword = KeywordFilter::isContainGeneric($seriesName);
        if ($keyword !== false) {
            throw new UserException('Problem.Tag.name_forbid', [
                'keyword' => $keyword
            ]);
        }
        if (!Validator::length(VJ::TAG_MIN, VJ::TAG_MAX)->validate($seriesName)) {
            throw new UserException('Problem.Tag.invalid_length');
        }

        $this->seriesName = $seriesName;
    }

    /**
     * get series name
     *
     * @return string
     */
    public function getSeriesName()
    {
        return $this->seriesName;
    }

    /**
     * 获取标签显示名称
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->seriesName;
    }

    /**
     * 获取标签分段数据
     *
     * @return string[]
     */
    public function getParts()
    {
        return [$this->seriesName];
    }

    /**
     * 获取标签类别
     *
     * @return string
     */
    public function getType()
    {
        return 'series';
    }
}