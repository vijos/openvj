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

class VJ
{
    const LOGIN_TYPE_INTERACTIVE = 0;
    const LOGIN_TYPE_COOKIE = 1;
    const LOGIN_TYPE_FAILED_WRONG_PASSWORD = 50;
    const LOGIN_TYPE_FAILED_USER_INVALID = 51;

    const USER_GENDER_UNKNOWN = 0;
    const USER_GENDER_MALE = 1;
    const USER_GENDER_FEMAIL = 2;
    const USER_GENDER_OTHER = 3;

    const USER_ID_GUEST = 0;
    const USER_ID_SYSTEM = 1;

    const DOMAIN_GLOBAL = '000000000000000000000000';
}