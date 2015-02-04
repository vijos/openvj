<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Test\EventListener;

use VJ\Core\Application;
use VJ\Core\Request;
use VJ\Core\Response;
use VJ\EventListener\VJRedirectionService;

class VJRedirectionServiceTest extends \PHPUnit_Framework_TestCase
{
    private $ua_https = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17';
    private $ua_http = 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)';

    public function testEnforceHttpsTrueFromBrowser()
    {
        // enforce_https = true
        $request = new Request(['id' => '100'], [], [], [], [], [
            'PHP_SELF' => '/app.php',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/problem_show.asp?id=100',
            'HTTP_HOST' => Application::get('config')['canonical'],
            'HTTP_USER_AGENT' => $this->ua_https,
            'SERVER_PORT' => 80,
        ]);
        $response = new Response();

        $service = new VJRedirectionService();
        $service->redirect(true, $request, $response);
        $this->assertEquals(
            'https://' . Application::get('config')['canonical'] . '/problem/100',
            $response->headers->get('location'));
        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
    }

    public function testEnforceHttpsTrueFromSpider()
    {
        $request = new Request(['id' => '100'], [], [], [], [], [
            'PHP_SELF' => '/app.php',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/problem_show.asp?id=100',
            'HTTP_HOST' => Application::get('config')['canonical'],
            'HTTP_USER_AGENT' => $this->ua_http,
            'SERVER_PORT' => 80,
        ]);
        $response = new Response();

        $service = new VJRedirectionService();
        $service->redirect(true, $request, $response);
        $this->assertEquals(
            'http://' . Application::get('config')['canonical'] . '/problem/100',
            $response->headers->get('location'));
        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
    }

    public function testEnforceHttpsFalse()
    {
        $request = new Request(['id' => '100'], [], [], [], [], [
            'PHP_SELF' => '/app.php',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/problem_show.asp?id=100',
            'HTTP_HOST' => Application::get('config')['canonical'],
            'HTTP_USER_AGENT' => $this->ua_https,
            'SERVER_PORT' => 80,
        ]);
        $response = new Response();

        $service = new VJRedirectionService();
        $service->redirect(false, $request, $response);
        $this->assertEquals(
            'http://' . Application::get('config')['canonical'] . '/problem/100',
            $response->headers->get('location'));
        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
    }
} 