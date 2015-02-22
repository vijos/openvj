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

class ContestTag implements TagInterface
{
    private $name;
    private $year;

    /**
     * @param string $name
     * @param int|null $year
     * @throws InvalidArgumentException
     * @throws UserException
     */
    public function __construct($name, $year = null)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('name', 'type_invalid');
        }
        if (!mb_check_encoding($name, 'UTF-8')) {
            throw new InvalidArgumentException('name', 'encoding_invalid');
        }
        $keyword = KeywordFilter::isContainGeneric($name);
        if ($keyword !== false) {
            throw new UserException('Problem.Tag.name_forbid', [
                'keyword' => $keyword
            ]);
        }
        if (!Validator::length(VJ::TAG_MIN, VJ::TAG_MAX)->validate($name)) {
            throw new UserException('Problem.Tag.invalid_length');
        }
        if ($year !== null) {
            if (!Validator::int()->validate($year)) {
                throw new InvalidArgumentException('year', 'type_invalid');
            }
            $year = (int)$year;
        }

        $this->name = $name;
        $this->year = $year;
    }

    /**
     * get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * get year
     *
     * @return int|null
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * 获取标签显示名称
     *
     * @return string
     */
    public function getDisplayName()
    {
        if ($this->year !== null) {
            return $this->name . ' ' . $this->year;
        } else {
            return $this->name;
        }
    }

    /**
     * 获取标签分段数据
     *
     * @return string[]
     */
    public function getParts()
    {
        if ($this->year !== null) {
            return [$this->name, (string)$this->year];
        } else {
            return [$this->name];
        }
    }

    /**
     * 获取标签类别
     *
     * @return string
     */
    public function getType()
    {
        return 'contest';
    }
}