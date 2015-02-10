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

use VJ\Core\Exception\AccessDeniedException;
use VJ\Core\Exception\NotFoundException;

class Controller
{
    protected $request;
    protected $response;

    /**
     * @param Request $req
     * @param Response $res
     */
    public function __construct(Request $req, Response $res)
    {
        $this->request = $req;
        $this->response = $res;
    }

    /**
     * @throws NotFoundException
     */
    public function notFound()
    {
        throw new NotFoundException();
    }

    /**
     * @throws AccessDeniedException
     */
    public function accessDenied()
    {
        throw new AccessDeniedException();
    }

    /**
     * @param string $template
     * @param array $param
     * @return string
     */
    public function render($template, $param = array())
    {
        return Application::get('templating')->render($template, $param);
    }

    /**
     * @param string $url
     * @param bool $permanent
     */
    public function redirect($url, $permanent = false)
    {
        $this->response->redirect($url, $permanent);
    }

    /**
     * @param array $data
     * @param int $status
     */
    public function json($data, $status = Response::HTTP_OK)
    {
        $this->response->json($data, $status);
    }
} 