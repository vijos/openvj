<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Controller;

use VJ\Core\Application;
use VJ\Core\Controller;
use VJ\Core\Exception\MissingArgumentException;
use VJ\Core\Exception\UserException;

class IndexController extends Controller
{
    public function indexAction()
    {
        return $this->render('index.twig');
    }

    public function loginAction()
    {
        if ($this->request->getMethod() === 'GET') {
            return $this->render('login.twig', [
                'TITLE' => Application::trans('page.login.title')
            ]);
        } else {
            try {
                if ($this->request->request->get('username') === null) {
                    throw new MissingArgumentException('username');
                }
                if ($this->request->request->get('password') === null) {
                    throw new MissingArgumentException('password');
                }
                Application::get('user_manager')->interactiveLogin(
                    $this->request->request->get('username'),
                    $this->request->request->get('password'),
                    $this->request->request->get('remember_me') === 'enable'
                );
                $this->response->redirect('/');
            } catch (UserException $ex) {
                return $this->render('login.twig', [
                    'TITLE' => Application::trans('page.login.title'),
                    'ERROR' => $ex->getMessage()
                ]);
            }
        }
    }
}