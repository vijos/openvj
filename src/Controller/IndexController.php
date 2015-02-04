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

use VJ\Core\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        return $this->render('index.twig');
    }
}