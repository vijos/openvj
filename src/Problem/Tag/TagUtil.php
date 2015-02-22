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

use VJ\Core\Exception\InvalidArgumentException;

class TagUtil
{
    public static function unserializeFromDb(array $tagObject)
    {
        if (!isset($tagObject['type'])) {
            throw new InvalidArgumentException('tagObject', 'type_invalid');
        }
        if (!isset($tagObject['parts']) || !is_array($tagObject['parts']) || count($tagObject) === 0) {
            throw new InvalidArgumentException('tagObject', 'type_invalid');
        }
        switch ($tagObject['type']) {
            case 'category':
                return new CategoryTag(...$tagObject['parts']);
            case 'contest':
                return new ContestTag(...$tagObject['parts']);
            case 'series':
                return new SeriesTag(...$tagObject['parts']);
            default:
                throw new InvalidArgumentException('tagObject', 'value_invalid');
        }
    }
}