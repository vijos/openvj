<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Core;

class Response extends \Symfony\Component\HttpFoundation\Response
{
    /**
     * 重定向到一个 URL.
     *
     * Usage example:
     *  $response->redirect('/');
     *
     * @param string $url
     * @param bool $permanent 是否是永久重定向(301)
     */
    public function redirect($url, $permanent = false)
    {
        $this->setStatusCode($permanent ? Response::HTTP_MOVED_PERMANENTLY : Response::HTTP_FOUND);
        $this->headers->set('content-type', 'text/plain');
        $this->headers->set('location', $url);
        if ('cli' !== PHP_SAPI) {
            // we must set content here. otherwise headers won't be sent immediately
            $this->setContent('Redirected to: ' . $url);
        }
        $this->send();
    }

    /**
     * 使用 JSON 响应
     *
     * Usage example:
     *  $response->json(['ok' => true]);
     *
     * @param array $data
     * @param int $status
     */
    public function json($data, $status = Response::HTTP_OK)
    {
        $this->setStatusCode($status);
        $this->headers->set('content-type', 'application/json');
        $this->setCharset('UTF-8');
        $this->setContent(json_encode($data));
        $this->send();
    }
} 