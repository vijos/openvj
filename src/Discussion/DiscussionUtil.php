<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Discussion;

use Respect\Validation\Validator;
use VJ\Core\Application;
use VJ\Core\Exception\InvalidArgumentException;
use VJ\Core\Exception\UserException;
use VJ\MarkdownParser;
use VJ\Security\KeywordFilter;
use VJ\VJ;

class DiscussionUtil
{
    /** @var MarkdownParser $parser */
    private static $parser = null;

    private static function initParser()
    {
        if (self::$parser === null) {
            self::$parser = new MarkdownParser(Application::get('config')['filter']['comment']);
        }
    }

    /**
     * 创建主题
     *
     * @param \MongoId $topicId
     * @param int $owner
     * @param string title
     * @param string $markdown
     * @return array|null
     * @throws InvalidArgumentException
     * @throws UserException
     */
    public static function createDiscussion(\MongoId $topicId, $owner, $title, $markdown)
    {
        if (!Validator::int()->validate($owner)) {
            throw new InvalidArgumentException('owner', 'type_invalid');
        }
        if (!is_string($title)) {
            throw new InvalidArgumentException('markdown', 'type_invalid');
        }
        if (!mb_check_encoding($title, 'UTF-8')) {
            throw new InvalidArgumentException('markdown', 'encoding_invalid');
        }
        if (!is_string($markdown)) {
            throw new InvalidArgumentException('markdown', 'type_invalid');
        }
        if (!mb_check_encoding($markdown, 'UTF-8')) {
            throw new InvalidArgumentException('markdown', 'encoding_invalid');
        }

        if (!Validator::length(VJ::COMMENT_MIN, VJ::COMMENT_MAX)) {
            throw new UserException('DiscussionUtil.content_invalid_length');
        }

        self::initParser();
        $discussionId = new \MongoId();
        $html = self::$parser->parse($markdown);

        $keyword = KeywordFilter::isContainGeneric(strip_tags($html));
        if ($keyword !== false) {
            throw new UserException('DiscussionUtil.content_forbid', [
                'keyword' => $keyword
            ]);
        }

        $doc = [
            '_id' => $discussionId,
            'owner' => (int)$owner,
            'topicId' => $topicId,
            'at' => new \MongoDate(),
            'title' => $title,
            'raw' => $markdown,
            'html' => $html,
        ];

        Application::coll('Discussion')->insert($doc);
        Application::emit('discussion.create.succeeded', [$topicId, $discussionId]);

        return [
            '_id' => $discussionId,
            'html' => $html
        ];
    }

    /**
     * 返回主题信息
     *
     * @param \MongoId $discussionId
     * @return array|null
     */
    public static function queryDiscussion(\MongoId $discussionId)
    {
        $query = [
            '_id' => $discussionId
        ];

        $doc = Application::coll('Discussion')->findOne($query);

        if ($doc === null) {
            return null;
        }

        return $doc;
    }

    /**
     * 修改主题
     *
     * @param \MongoId $discussionId
     * @param string $markdown
     * @return array|null
     * @throws InvalidArgumentException
     * @throws UserException
     */
    public static function modifyDiscussion(\MongoId $discussionId, $markdown)
    {
        if (!is_string($markdown)) {
            throw new InvalidArgumentException('markdown', 'type_invalid');
        }
        if (!mb_check_encoding($markdown, 'UTF-8')) {
            throw new InvalidArgumentException('markdown', 'encoding_invalid');
        }
        //if (!Validator::length(VJ::COMMENT_MIN, VJ::COMMENT_MAX)) {
        //throw new UserException('CommentUtil.content_invalid_length');
        //}

        self::initParser();
        $html = self::$parser->parse($markdown);

        $keyword = KeywordFilter::isContainGeneric(strip_tags($html));
        if ($keyword !== false) {
            throw new UserException('CommentUtil.content_forbid', [
                'keyword' => $keyword
            ]);
        }

        $result = Application::coll('Discussion')->update([
            '_id' => $discussionId,
        ], [
            '$set' => [
                'raw' => $markdown,
                'html' => $html
            ]
        ]);

        if ($result['n'] === 1) {
            Application::emit('discussion.modify.succeeded', [$discussionId]);
            return [
                '_id' => $discussionId,
                'html' => $html,
            ];
        } else {
            return null;
        }
    }

    /**
     * 获取分类名称
     *
     * @param \MongoId $topicId
     * @return String|null
     * @throws InvalidArgumentException
     */
    public static function queryTopicName(\MongoId $topicId)
    {
        $doc = Application::coll('Topic')->findOne([
            '_id' => $topicId,
        ]);

        if ($doc === null) {
            return null;
        }

        return $doc['name'];
    }
}
