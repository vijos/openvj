<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Problem;

use Respect\Validation\Validator;
use VJ\Core\Application;
use VJ\Core\Exception\InvalidArgumentException;
use VJ\Core\Exception\UserException;
use VJ\MarkdownParser;
use VJ\Problem\Tag\Tag;
use VJ\Security\KeywordFilter;
use VJ\VJ;
use VJ\Vote\VoteUtil;

// TODO: ES Index

class ProblemUtil
{
    /** @var MarkdownParser $parser */
    private static $parser = null;

    private static function initParser()
    {
        if (self::$parser === null) {
            self::$parser = new MarkdownParser(Application::get('config')['filter']['problem']);
        }
    }

    private static function encodeHistory(array $history)
    {
        return new \MongoBinData(gzcompress(json_encode($history), 9));
    }

    private static function decodeHistory(\MongoBinData $data)
    {
        return json_decode(gzuncompress($data->bin), true);
    }

    public static function generateLink($title)
    {
        return VJ::removeUrlUnsafeChar($title);
    }

    public static function canonicalizeLink($link)
    {
        return mb_strtolower($link, 'UTF-8');
    }

    public static function createProblem($uid, $title, array $visibleDomains)
    {
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }
        if (!is_string($title)) {
            throw new InvalidArgumentException('title', 'type_invalid');
        }
        if (!mb_check_encoding($title, 'UTF-8')) {
            throw new InvalidArgumentException('title', 'encoding_invalid');
        }
        if (!Validator::length(VJ::PROBLEM_TITLE_MIN, VJ::PROBLEM_TITLE_MAX)) {
            throw new UserException('ProblemUtil.title_invalid_length');
        }
        foreach ($visibleDomains as &$domain) {
            if (!$domain instanceof \MongoId) {
                throw new InvalidArgumentException('visibleDomains', 'type_invalid');
            }
        }
        $keyword = KeywordFilter::isContainGeneric($title);
        if ($keyword !== false) {
            throw new UserException('ProblemUtil.title_forbid', [
                'keyword' => $keyword
            ]);
        }

        self::initParser();
        $markdown = Application::coll('System')->findOne(['_id' => 'ProblemTemplate'])['markdown'];
        $html = self::$parser->parse($markdown);
        $title = VJ::removeEmoji($title);
        $link = self::generateLink($title);

        $doc = VoteUtil::attachDocument([
            'owner' => (int)$uid,
            'at' => new \MongoDate(),
            'title' => $title,
            'link' => $link,
            'llink' => self::canonicalizeLink($link),
            'raw' => $markdown,
            'html' => $html,
            'visible' => $visibleDomains,
            'tags' => [],
            'history' => self::encodeHistory([])
        ]);

        try {
            Application::coll('Problem')->insert($doc);
        } catch (\MongoCursorException $e) {
            throw new UserException('ProblemUtil.createProblem.title_exists');
        }

        return $doc['_id'];
    }

    public static function getProblemById(\MongoId $pid)
    {
        $rec = Application::coll('Problem')->findOne([
            '_id' => $pid
        ]);

        return $rec;
    }

    public static function getProblemByLink($uid, $link)
    {
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }
        if (!is_string($link)) {
            throw new InvalidArgumentException('link', 'type_invalid');
        }
        if (!mb_check_encoding($link, 'UTF-8')) {
            throw new InvalidArgumentException('link', 'encoding_invalid');
        }

        $rec = Application::coll('Problem')->findOne([
            'owner' => (int)$uid,
            'llink' => self::canonicalizeLink($link)
        ]);

        return $rec;
    }

    public static function getProblemOwnerById(\MongoId $pid)
    {
        $p = self::getProblemById($pid);

        if ($p === null) {
            return null;
        } else {
            return (int)$p['owner'];
        }
    }

    public static function modifyContent(\MongoId $pid, $uid, $markdown)
    {
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }
        if (!is_string($markdown)) {
            throw new InvalidArgumentException('markdown', 'type_invalid');
        }
        if (!mb_check_encoding($markdown, 'UTF-8')) {
            throw new InvalidArgumentException('markdown', 'encoding_invalid');
        }
        if (!Validator::length(VJ::PROBLEM_CONTENT_MIN, VJ::PROBLEM_CONTENT_MAX)) {
            throw new UserException('ProblemUtil.content_invalid_length');
        }

        self::initParser();
        $html = self::$parser->parse($markdown);

        $keyword = KeywordFilter::isContainGeneric(strip_tags($html));
        if ($keyword !== false) {
            throw new UserException('ProblemUtil.content_forbid', [
                'keyword' => $keyword
            ]);
        }

        // process history
        // WARNING: not atomic operation here
        $rec = Application::coll('Problem')->findOne([
            '_id' => $pid
        ]);
        if ($rec === null) {
            throw new UserException('ProblemUtil.modifyContent.invalid_problem');
        }
        // push history
        $history = self::decodeHistory($rec['history']);
        $history[] = [
            'owner' => (int)$uid,
            'at' => new \MongoDate(),
            'raw' => $markdown
        ];
        Application::coll('Problem')->update([
            '_id' => $pid
        ], [
            '$set' => [
                'history' => self::encodeHistory($history)
            ]
        ]);

        // update content
        $result = Application::coll('Problem')->update([
            '_id' => $pid
        ], [
            '$set' => [
                'raw' => $markdown,
                'html' => $html
            ]
        ]);

        return ($result['n'] === 1);
    }

    public static function modifyMeta(\MongoId $pid, $title, array $tags, array $visibleDomains)
    {
        if (!is_string($title)) {
            throw new InvalidArgumentException('title', 'type_invalid');
        }
        if (!mb_check_encoding($title, 'UTF-8')) {
            throw new InvalidArgumentException('title', 'encoding_invalid');
        }
        if (!Validator::length(VJ::PROBLEM_TITLE_MIN, VJ::PROBLEM_TITLE_MAX)) {
            throw new UserException('ProblemUtil.title_invalid_length');
        }
        $keyword = KeywordFilter::isContainGeneric($title);
        if ($keyword !== false) {
            throw new UserException('ProblemUtil.title_forbid', [
                'keyword' => $keyword
            ]);
        }
        foreach ($visibleDomains as $domain) {
            if (!$domain instanceof \MongoId) {
                throw new InvalidArgumentException('visibleDomains', 'type_invalid');
            }
        }
        foreach ($tags as $tag) {
            if (!$tag instanceof Tag) {
                throw new InvalidArgumentException('tags', 'type_invalid');
            }
        }

        $title = VJ::removeEmoji($title);
        $link = self::generateLink($title);

        try {
            $result = Application::coll('Problem')->update([
                '_id' => $pid
            ], [
                '$set' => [
                    'title' => $title,
                    'link' => $link,
                    'llink' => self::canonicalizeLink($link),
                    'tags' => array_map(function (Tag $tag) {
                        return $tag->serializeForDb();
                    }, $tags),
                    'visible' => $visibleDomains
                ]
            ]);
        } catch (\MongoCursorException $e) {
            throw new UserException('ProblemUtil.modifyMeta.title_exists');
        }

        return ($result['n'] === 1);
    }
}