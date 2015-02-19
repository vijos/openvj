<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Test;

use VJ\MarkdownParser;

class MarkdownParserTest extends \PHPUnit_Framework_TestCase
{
    private $filter = 'h3,h4,p,a[href|title],ul,ol,li,img[src|alt],blockquote,pre,code[class],span,strong,em,br,del,table,thead,tbody,tr,th,td';

    public function testParseMarkdownStrong()
    {
        $parser = new MarkdownParser($this->filter);
        $html = $parser->parse('**hello** world');
        $this->assertEquals('<p><strong>hello</strong> world</p>', trim($html));
    }

    public function testParseMarkdownSpace()
    {
        $parser = new MarkdownParser($this->filter);
        $html = $parser->parse('hello  world');
        $this->assertEquals('<p>hello  world</p>', trim($html));
    }

    public function testParseSpecialCharacters1()
    {
        $parser = new MarkdownParser($this->filter);
        $html = $parser->parse('this is a <tag>.');
        $this->assertEquals('<p>this is a &lt;tag&gt;.</p>', trim($html));
    }

    public function testParseSpecialCharacters2()
    {
        $parser = new MarkdownParser($this->filter);
        $html = $parser->parse('1 & 2, 1 &amp; 2');
        $this->assertEquals('<p>1 &amp; 2, 1 &amp; 2</p>', trim($html));
    }

    public function testParseSpecialCharacters3()
    {
        $parser = new MarkdownParser($this->filter);
        $html = $parser->parse('tag in code test: `#include<iostream>`.');
        $this->assertEquals('<p>tag in code test: <code>#include&lt;iostream&gt;</code>.</p>', trim($html));
    }

    public function testParseSpecialCharacters4()
    {
        $parser = new MarkdownParser($this->filter);
        $html = $parser->parse('&amp in code test: `1 & 2, 1 &amp; 2`.');
        $this->assertEquals('<p>&amp;amp in code test: <code>1 &amp; 2, 1 &amp;amp; 2</code>.</p>', trim($html));
    }

    public function testParseTags1()
    {
        $parser = new MarkdownParser($this->filter);
        $html = $parser->parse('HTML tags like <script> should be escaped.');
        $this->assertEquals('<p>HTML tags like &lt;script&gt; should be escaped.</p>', trim($html));
    }

    public function testParseTags2()
    {
        $parser = new MarkdownParser($this->filter);
        $html = $parser->parse('HTML tags like `<script>` should be escaped.');
        $this->assertEquals('<p>HTML tags like <code>&lt;script&gt;</code> should be escaped.</p>', trim($html));
    }

    public function testFilteringHeader()
    {
        $parser = new MarkdownParser($this->filter);
        $html = $parser->parse('# H1 tag is not allowed');
        $this->assertEquals("<p>H1 tag is not allowed\n</p>", trim($html));
        $html = $parser->parse('#### However H4 is allowed');
        $this->assertEquals('<h4>However H4 is allowed</h4>', trim($html));
    }

    public function testDangerousLink()
    {
        $parser = new MarkdownParser($this->filter);
        $html = $parser->parse('[click me!](https://example.com)');
        $this->assertEquals('<p><a href="https://example.com">click me!</a></p>', trim($html));
        $html = $parser->parse('[click me!](javascript:xx)');
        $this->assertEquals('<p><a>click me!</a></p>', trim($html));
    }
}