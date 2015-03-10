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

use Respect\Validation\Validator;
use VJ\Core\Application;
use VJ\Core\Controller;
use VJ\Core\Exception\InvalidArgumentException;
use VJ\Core\Exception\MissingArgumentException;
use VJ\Core\Exception\NotFoundException;
use VJ\Core\Exception\UserException;
use VJ\User\UserUtil;

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
                return null;
            } catch (UserException $ex) {
                return $this->render('login.twig', [
                    'TITLE' => Application::trans('page.login.title'),
                    'ERROR' => $ex->getMessage()
                ]);
            }
        }
    }

    public function registrationAction()
    {
        return $this->render('registration.twig', [
            'TITLE' => Application::trans('page.reg.title')
        ]);
    }

    public function registrationSendMailAction()
    {
        $email = $this->request->request->get('email');
        if ($email === null) {
            throw new MissingArgumentException('email');
        }
        if (!Validator::email()->validate($email)) {
            throw new UserException('controller.reg.invalid_email');
        }
        if (UserUtil::getUserObjectByEmail($email) !== null) {
            throw new UserException('controller.reg.duplicate_email');
        }

        // generate one-time token
        $token = Application::get('token_generator')->generate(
            'reg',
            UserUtil::canonicalizeEmail($email),
            time() + 4 * 60 * 60,
            [
                'email' => $email
            ]
        )['token'];

        // send token
        Application::get('mail_sender')->sendVerification(
            [$email],
            Application::trans('email.reg_validation.subject'),
            'email/reg_validation.twig',
            [
                'TOKEN' => $token,
                'EMAIL' => $email,
            ]
        );
        $this->response->json([]);
        return null;
    }

    public function registrationCompleteAction()
    {
        $username = $this->request->request->get('username');
        if ($username === null) {
            throw new MissingArgumentException('username');
        }
        $password = $this->request->request->get('password');
        $passwordRepeat = $this->request->request->get('password-repeat');
        if ($password !== $passwordRepeat) {
            throw new UserException('controller.reg.password_mismatch');
        }
        $email = $this->request->request->get('email', '');
        $email_mac = $this->request->request->get('sign_email', '');
        if (Application::get('message_signer')->sign($email) !== $email_mac) {
            throw new InvalidArgumentException('email', 'sign_invalid');
        }
        $token = $this->request->request->get('token', '');
        $token_mac = $this->request->request->get('sign_token', '');
        if (Application::get('message_signer')->sign($token) !== $token_mac) {
            throw new InvalidArgumentException('token', 'sign_invalid');
        }

        Application::get('user_manager')->createUser($username, $password, $email);
        Application::get('token_generator')->invalidate('reg', $token);
        Application::get('user_manager')->interactiveLogin($username, $password);
        $this->response->json([]);
        return null;
    }

    public function registrationVerifiedAction(array $params)
    {
        if (strlen($params['token']) !== 30) {
            throw new NotFoundException();
        }
        $tokenRec = Application::get('token_generator')->find('reg', $params['token']);
        if ($tokenRec === null) {
            // TODO: show error on reg page
            throw new UserException('controller.reg.invalid_token');
        }
        return $this->render('registration.verified.twig', [
            'TITLE' => Application::trans('page.reg.verified.title'),
            'EMAIL' => $tokenRec['data']['email'],
            'TOKEN' => $params['token'],
            'SIGN_EMAIL' => Application::get('message_signer')->sign($tokenRec['data']['email']),
            'SIGN_TOKEN' => Application::get('message_signer')->sign($params['token']),
        ]);
    }

    public function logoutAction()
    {
        Application::get('user_manager')->logout();
        $this->response->redirect('/');
    }
}