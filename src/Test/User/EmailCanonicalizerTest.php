<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Test\User;

use VJ\User\EmailCanonicalizer;

class EmailCanonicalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testCanonicalize()
    {
        $this->assertEquals('test@example.com', EmailCanonicalizer::canonicalize(' TEST@eXamPLE.com'));
    }
} 