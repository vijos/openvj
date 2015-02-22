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

class CategoryTag implements TagInterface
{
    private $category;
    private $subCategory;

    /**
     * @param string $category
     * @param string|null $subCategory
     * @throws InvalidArgumentException
     * @throws UserException
     */
    public function __construct($category, $subCategory = null)
    {
        if (!is_string($category)) {
            throw new InvalidArgumentException('category', 'type_invalid');
        }
        if (!mb_check_encoding($category, 'UTF-8')) {
            throw new InvalidArgumentException('category', 'encoding_invalid');
        }
        if (!Validator::length(VJ::TAG_MIN, VJ::TAG_MAX)->validate($category)) {
            throw new UserException('Problem.Tag.invalid_length');
        }
        $keyword = KeywordFilter::isContainGeneric($category);
        if ($keyword !== false) {
            throw new UserException('Problem.Tag.name_forbid', [
                'keyword' => $keyword
            ]);
        }
        if (!is_string($subCategory)) {
            throw new InvalidArgumentException('subCategory', 'type_invalid');
        }
        if (!mb_check_encoding($subCategory, 'UTF-8')) {
            throw new InvalidArgumentException('subCategory', 'encoding_invalid');
        }
        if (!Validator::length(VJ::TAG_MIN, VJ::TAG_MAX)->validate($subCategory)) {
            throw new UserException('Problem.Tag.invalid_length');
        }
        $keyword = KeywordFilter::isContainGeneric($subCategory);
        if ($keyword !== false) {
            throw new UserException('Problem.Tag.name_forbid', [
                'keyword' => $keyword
            ]);
        }

        $this->category = $category;
        $this->subCategory = $subCategory;
    }

    /**
     * get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * get subcategory
     *
     * @return string
     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * 获取标签显示名称
     *
     * @return string
     */
    public function getDisplayName()
    {
        if ($this->subCategory !== null) {
            return $this->category . ' ' . $this->subCategory;
        } else {
            return $this->category;
        }
    }

    /**
     * 获取标签分段数据
     *
     * @return string[]
     */
    public function getParts()
    {
        if ($this->subCategory !== null) {
            return [$this->category, $this->subCategory];
        } else {
            return [$this->category];
        }
    }

    /**
     * 获取标签类别
     *
     * @return string
     */
    public function getType()
    {
        return 'category';
    }
}