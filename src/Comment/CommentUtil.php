<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Comment;

use Respect\Validation\Validator;
use VJ\Core\Application;
use VJ\Core\Exception\InvalidArgumentException;
use VJ\Core\Exception\UserException;
use VJ\MarkdownParser;
use VJ\Security\KeywordFilter;
use VJ\VJ;
use VJ\Vote\VoteUtil;

class CommentUtil
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
     * 返回所有评论
     *
     * @param string $ref
     * @param \MongoId $after
     * @return array
     * @throws InvalidArgumentException
     */
    public static function getComment($ref, \MongoId $after = null)
    {
        if (!is_string($ref)) {
            throw new InvalidArgumentException('ref', 'type_invalid');
        }
        if (!mb_check_encoding($ref, 'UTF-8')) {
            throw new InvalidArgumentException('ref', 'encoding_invalid');
        }

        if ($after) {
            $query = [
                'ref' => $ref,
                'deleted' => false,
                '_id' => [
                    '$gt' => $after
                ]
            ];
        } else {
            $query = [
                'ref' => $ref,
                'deleted' => false,
            ];
        }

        $cursor = Application::coll('Comment')->find($query, [
            'votes' => 0,
            'raw' => 0,
            'replies.raw' => 0,
        ])->sort(['_id' => 1])->limit(VJ::COMMENT_PAGE_SIZE);

        $result = iterator_to_array($cursor, false);

        // filter deleted replies
        array_walk($result, function (&$value) {
            $value['replies'] = array_values(array_filter($value['replies'], function ($reply) {
                return !$reply['deleted'];
            }));
        });

        return $result;
    }

    /**
     * vote up comment
     *
     * @param \MongoId $commentId
     * @param string $ref
     * @param int $uid
     * @return int|null
     * @throws InvalidArgumentException
     */
    public static function voteUp(\MongoId $commentId, $ref, $uid)
    {
        if (!is_string($ref)) {
            throw new InvalidArgumentException('ref', 'type_invalid');
        }
        if (!mb_check_encoding($ref, 'UTF-8')) {
            throw new InvalidArgumentException('ref', 'encoding_invalid');
        }
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }
        return VoteUtil::voteUp(Application::coll('Comment'), [
            '_id' => $commentId,
            'ref' => $ref
        ], $uid);
    }

    /**
     * vote down comment
     *
     * @param \MongoId $commentId
     * @param string $ref
     * @param int $uid
     * @return int|null
     * @throws InvalidArgumentException
     */
    public static function voteDown(\MongoId $commentId, $ref, $uid)
    {
        if (!is_string($ref)) {
            throw new InvalidArgumentException('ref', 'type_invalid');
        }
        if (!mb_check_encoding($ref, 'UTF-8')) {
            throw new InvalidArgumentException('ref', 'encoding_invalid');
        }
        if (!Validator::int()->validate($uid)) {
            throw new InvalidArgumentException('uid', 'type_invalid');
        }
        return VoteUtil::voteDown(Application::coll('Comment'), [
            '_id' => $commentId,
            'ref' => $ref
        ], $uid);
    }

    /**
     * 创建评论
     *
     * @param string $ref
     * @param int $owner
     * @param string $markdown
     * @return array
     * @throws InvalidArgumentException
     * @throws UserException
     */
    public static function createComment($ref, $owner, $markdown)
    {
        if (!is_string($ref)) {
            throw new InvalidArgumentException('ref', 'type_invalid');
        }
        if (!mb_check_encoding($ref, 'UTF-8')) {
            throw new InvalidArgumentException('ref', 'encoding_invalid');
        }
        if (!Validator::int()->validate($owner)) {
            throw new InvalidArgumentException('owner', 'type_invalid');
        }
        if (!is_string($markdown)) {
            throw new InvalidArgumentException('markdown', 'type_invalid');
        }
        if (!mb_check_encoding($markdown, 'UTF-8')) {
            throw new InvalidArgumentException('markdown', 'encoding_invalid');
        }
        if (!Validator::length(VJ::COMMENT_MIN, VJ::COMMENT_MAX)) {
            throw new UserException('CommentUtil.content_invalid_length');
        }

        self::initParser();
        $commentId = new \MongoId();
        $html = self::$parser->parse($markdown);

        $keyword = KeywordFilter::isContainGeneric(strip_tags($html));
        if ($keyword !== false) {
            throw new UserException('CommentUtil.content_forbid', [
                'keyword' => $keyword
            ]);
        }

        $doc = VoteUtil::attachDocument([
            '_id' => $commentId,
            'ref' => $ref,
            'owner' => (int)$owner,
            'at' => new \MongoDate(),
            'raw' => $markdown,
            'html' => $html,
            'replies' => [],
            'deleted' => false,
        ]);

        Application::coll('Comment')->insert($doc);
        Application::emit('comment.create.succeeded', [$ref, $commentId]);

        return [
            '_id' => $commentId,
            'html' => $html,
        ];
    }

    /**
     * 获取评论所有者
     *
     * @param \MongoId $commentId
     * @param string $ref
     * @return int|null
     * @throws InvalidArgumentException
     */
    public static function getCommentOwner(\MongoId $commentId, $ref)
    {
        if (!is_string($ref)) {
            throw new InvalidArgumentException('ref', 'type_invalid');
        }
        if (!mb_check_encoding($ref, 'UTF-8')) {
            throw new InvalidArgumentException('ref', 'encoding_invalid');
        }

        $doc = Application::coll('Comment')->findOne([
            '_id' => $commentId,
            'ref' => $ref,
            'deleted' => false,
        ]);

        if ($doc === null) {
            return null;
        }

        return (int)$doc['owner'];
    }

    /**
     * 修改评论
     *
     * @param \MongoId $commentId
     * @param string $ref
     * @param string $markdown
     * @return array|null
     * @throws InvalidArgumentException
     * @throws UserException
     */
    public static function modifyComment(\MongoId $commentId, $ref, $markdown)
    {
        if (!is_string($ref)) {
            throw new InvalidArgumentException('ref', 'type_invalid');
        }
        if (!mb_check_encoding($ref, 'UTF-8')) {
            throw new InvalidArgumentException('ref', 'encoding_invalid');
        }
        if (!is_string($markdown)) {
            throw new InvalidArgumentException('markdown', 'type_invalid');
        }
        if (!mb_check_encoding($markdown, 'UTF-8')) {
            throw new InvalidArgumentException('markdown', 'encoding_invalid');
        }
        if (!Validator::length(VJ::COMMENT_MIN, VJ::COMMENT_MAX)) {
            throw new UserException('CommentUtil.content_invalid_length');
        }

        self::initParser();
        $html = self::$parser->parse($markdown);

        $keyword = KeywordFilter::isContainGeneric(strip_tags($html));
        if ($keyword !== false) {
            throw new UserException('CommentUtil.content_forbid', [
                'keyword' => $keyword
            ]);
        }

        $result = Application::coll('Comment')->update([
            '_id' => $commentId,
            'ref' => $ref,
            'deleted' => false,
        ], [
            '$set' => [
                'raw' => $markdown,
                'html' => $html,
                'modifyat' => new \MongoDate()
            ]
        ]);

        if ($result['n'] === 1) {
            Application::emit('comment.modify.succeeded', [$ref, $commentId]);
            return [
                '_id' => $commentId,
                'html' => $html,
            ];
        } else {
            return null;
        }
    }

    /**
     * 删除评论
     *
     * @param \MongoId $commentId
     * @param string $ref
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function deleteComment(\MongoId $commentId, $ref)
    {
        if (!is_string($ref)) {
            throw new InvalidArgumentException('ref', 'type_invalid');
        }
        if (!mb_check_encoding($ref, 'UTF-8')) {
            throw new InvalidArgumentException('ref', 'encoding_invalid');
        }

        $result = Application::coll('Comment')->update([
            '_id' => $commentId,
            'ref' => $ref,
            'deleted' => false,
        ], [
            '$set' => [
                'deleted' => true
            ]
        ]);

        $success = $result['n'] === 1;
        if ($success) {
            Application::emit('comment.delete.succeeded', [$ref, $commentId]);
        }

        return $success;
    }

    /**
     * 创建回复
     *
     * @param \MongoId $commentId
     * @param string $ref
     * @param int $owner
     * @param string $markdown
     * @return array|null
     * @throws InvalidArgumentException
     * @throws UserException
     */
    public static function createReply(\MongoId $commentId, $ref, $owner, $markdown)
    {
        if (!is_string($ref)) {
            throw new InvalidArgumentException('ref', 'type_invalid');
        }
        if (!mb_check_encoding($ref, 'UTF-8')) {
            throw new InvalidArgumentException('ref', 'encoding_invalid');
        }
        if (!Validator::int()->validate($owner)) {
            throw new InvalidArgumentException('owner', 'type_invalid');
        }
        if (!is_string($markdown)) {
            throw new InvalidArgumentException('markdown', 'type_invalid');
        }
        if (!mb_check_encoding($markdown, 'UTF-8')) {
            throw new InvalidArgumentException('markdown', 'encoding_invalid');
        }
        if (!Validator::length(VJ::COMMENT_MIN, VJ::COMMENT_MAX)) {
            throw new UserException('CommentUtil.content_invalid_length');
        }

        self::initParser();
        $replyId = new \MongoId();
        $html = self::$parser->parse($markdown);

        $keyword = KeywordFilter::isContainGeneric(strip_tags($html));
        if ($keyword !== false) {
            throw new UserException('CommentUtil.content_forbid', [
                'keyword' => $keyword
            ]);
        }

        $doc = [
            '_id' => $replyId,
            'owner' => (int)$owner,
            'at' => new \MongoDate(),
            'raw' => $markdown,
            'html' => $html,
            'deleted' => false,
        ];

        try {
            $result = Application::coll('Comment')->update([
                '_id' => $commentId,
                'ref' => $ref,
                'deleted' => false,
            ], [
                '$push' => [
                    'replies' => $doc
                ]
            ]);

            if ($result['n'] === 1) {
                Application::emit('comment.reply.create.succeeded', [$ref, $commentId, $replyId]);
                return [
                    '_id' => $replyId,
                    'html' => $html
                ];
            } else {
                return null;
            }
        } catch (\MongoException $e) {
            // might be 16MB limit exceeded
            return null;
        }
    }

    /**
     * 获取回复所有者
     *
     * @param \MongoId $commentId
     * @param string $ref
     * @param \MongoId $replyId
     * @return int|null
     * @throws InvalidArgumentException
     */
    public static function getReplyOwner(\MongoId $commentId, $ref, \MongoId $replyId)
    {
        if (!is_string($ref)) {
            throw new InvalidArgumentException('ref', 'type_invalid');
        }
        if (!mb_check_encoding($ref, 'UTF-8')) {
            throw new InvalidArgumentException('ref', 'encoding_invalid');
        }

        $doc = Application::coll('Comment')->findOne([
            '_id' => $commentId,
            'ref' => $ref,
            'deleted' => false,
            'replies._id' => $replyId
        ], [
            'replies.$' => 1
        ]);

        if ($doc === null) {
            return null;
        }

        return (int)$doc['replies'][0]['owner'];
    }

    /**
     * 修改回复
     *
     * @param \MongoId $commentId
     * @param string $ref
     * @param \MongoId $replyId
     * @param string $markdown
     * @return array|null
     * @throws InvalidArgumentException
     * @throws UserException
     */
    public static function modifyReply(\MongoId $commentId, $ref, \MongoId $replyId, $markdown)
    {
        if (!is_string($ref)) {
            throw new InvalidArgumentException('ref', 'type_invalid');
        }
        if (!mb_check_encoding($ref, 'UTF-8')) {
            throw new InvalidArgumentException('ref', 'encoding_invalid');
        }
        if (!is_string($markdown)) {
            throw new InvalidArgumentException('markdown', 'type_invalid');
        }
        if (!mb_check_encoding($markdown, 'UTF-8')) {
            throw new InvalidArgumentException('markdown', 'encoding_invalid');
        }
        if (!Validator::length(VJ::COMMENT_MIN, VJ::COMMENT_MAX)) {
            throw new UserException('CommentUtil.content_invalid_length');
        }

        self::initParser();
        $html = self::$parser->parse($markdown);

        $keyword = KeywordFilter::isContainGeneric(strip_tags($html));
        if ($keyword !== false) {
            throw new UserException('CommentUtil.content_forbid', [
                'keyword' => $keyword
            ]);
        }

        $result = Application::coll('Comment')->update([
            '_id' => $commentId,
            'ref' => $ref,
            'deleted' => false,
            'replies' => [
                '$elemMatch' => [
                    '_id' => $replyId,
                    'deleted' => false
                ]
            ]
        ], [
            '$set' => [
                'replies.$.raw' => $markdown,
                'replies.$.html' => $html,
                'replies.$.modifyat' => new \MongoDate()
            ]
        ]);

        if ($result['n'] === 1) {
            Application::emit('comment.reply.modify.succeeded', [$ref, $commentId, $replyId]);
            return [
                '_id' => $replyId,
                'html' => $html,
            ];
        } else {
            return null;
        }
    }

    /**
     * 删除回复
     *
     * @param \MongoId $commentId
     * @param string $ref
     * @param \MongoId $replyId
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function deleteReply(\MongoId $commentId, $ref, \MongoId $replyId)
    {
        if (!is_string($ref)) {
            throw new InvalidArgumentException('ref', 'type_invalid');
        }
        if (!mb_check_encoding($ref, 'UTF-8')) {
            throw new InvalidArgumentException('ref', 'encoding_invalid');
        }

        $result = Application::coll('Comment')->update([
            '_id' => $commentId,
            'ref' => $ref,
            'deleted' => false,
            'replies' => [
                '$elemMatch' => [
                    '_id' => $replyId,
                    'deleted' => false
                ]
            ]
        ], [
            '$set' => [
                'replies.$.deleted' => true
            ]
        ]);

        $success = $result['n'] === 1;
        if ($success) {
            Application::emit('comment.reply.delete.succeeded', [$ref, $commentId, $replyId]);
        }

        return $success;
    }
}