<?php

/*
 * This file is part of loginBox php library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loginbox\Config;

use ReflectionClass;

/**
 * Class SocialSettings
 * @package Loginbox\Config
 */
class SocialSettings
{
    const SC_FACEBOOK = 'fb';
    const SC_GOOGLE = 'gp';
    const SC_GITHUB = 'gh';
    const SC_TWITTER = 'tt';
    const SC_LINKEDIN = 'lin';
    const SC_WINDOWS = 'win';

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var array
     */
    protected $social_network_urls = [];

    /**
     * @return array
     */
    public static function getAllSocialNetworks()
    {
        $thisReflectionClass = new ReflectionClass(__CLASS__);

        return $thisReflectionClass->getConstants();
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @param $network
     *
     * @return string
     */
    public function getSocialNetworkUrl($network)
    {
        return $this->social_network_urls[$network];
    }

    /**
     * @param string $network
     * @param string $url
     */
    public function setSocialNetworkUrl($network, $url)
    {
        $this->social_network_urls[$network] = $url;
    }
}
