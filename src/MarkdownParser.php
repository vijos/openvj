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

use Breezewish\Marked\Marked;
use VJ\Core\Application;
use VJ\Core\Exception\InvalidArgumentException;

class MarkdownParser
{
    private $purifier;

    public function __construct($htmlFilter)
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('AutoFormat.RemoveSpansWithoutAttributes', true);
        $config->set('HTML.Allowed', $htmlFilter);
        $config->set('Cache.SerializerPath', Application::$CACHE_DIRECTORY);
        $this->purifier = new \HTMLPurifier($config);
    }

    public function parse($markdown)
    {
        if (!is_string($markdown)) {
            throw new InvalidArgumentException('markdown', 'type_invalid');
        }
        if (!mb_check_encoding($markdown, 'UTF-8')) {
            throw new InvalidArgumentException('markdown', 'encoding_invalid');
        }

        // 目的：我们希望用户输入在 code block 以外的尖括号当成普通字符对待，而不是 HTML 标签被处理

        // 替换所有尖括号（成对出现时会被当成标签而忽略）
        $markdown = str_replace('<', '&lt;', $markdown);
        $markdown = str_replace('>', '&gt;', $markdown);

        $html = Marked::render($markdown, [
            'gfm' => true,
            'tables' => true,
            'breaks' => true,
            'sanitize' => false,
            'langPrefix' => 'prettyprint lang-'
        ]);

        // 恢复 <code> 中的特殊符号（它们被 escape 了两次，第二次是 markdown 引擎）
        $pBegin = 0;
        while (false !== $pBegin = stripos($html, '<code>', $pBegin)) {
            $pEnd = strpos($html, '</code>', $pBegin + 6);
            if ($pEnd === false) {
                break;
            }
            $inner = substr($html, $pBegin + 6, $pEnd - $pBegin - 6);
            $inner = str_replace('&amp;lt;', '&lt;', $inner);
            $inner = str_replace('&amp;gt;', '&gt;', $inner);
            $html = substr_replace($html, $inner, $pBegin + 6, $pEnd - $pBegin - 6);
            $pBegin += strlen($inner) + 6;
        }

        // 由 HTML Purifier 进一步过滤内容确保输入安全
        $html = $this->purifier->purify($html);

        return $html;
    }
}