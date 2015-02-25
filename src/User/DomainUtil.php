<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\User;

use VJ\VJ;

class DomainUtil
{
    /**
     * 获得全局域的 ID
     *
     * @return \MongoId
     */
    public static function getGlobalDomainId()
    {
        return new \MongoId(VJ::DOMAIN_GLOBAL);
    }

    /**
     * 根据域对象判断域是否有效
     *
     * @param array $domain
     * @return bool
     */
    public static function isDomainObjectValid(array $domain = null)
    {
        if ($domain === null || (isset($domain['invalid']) && $domain['invalid'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 判断是否是全局域 ID
     *
     * @param \MongoId $domainId
     * @return bool
     */
    public static function isGlobalDomainId(\MongoId $domainId)
    {
        return (string)$domainId === VJ::DOMAIN_GLOBAL;
    }
}