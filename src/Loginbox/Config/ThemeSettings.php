<?php

/*
 * This file is part of loginBox php library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loginbox\Config;

/**
 * Class ThemeSettings
 * @package Loginbox\Config
 */
class ThemeSettings
{
    /**
     * @var string
     */
    protected $logo_url = '';

    /**
     * @var string
     */
    protected $theme_color = '';

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->logo_url;
    }

    /**
     * @param string $logo_url
     */
    public function setLogoUrl(string $logo_url)
    {
        $this->logo_url = $logo_url;
    }

    /**
     * @return string
     */
    public function getThemeColor()
    {
        return $this->theme_color;
    }

    /**
     * @param string $theme_color
     */
    public function setThemeColor(string $theme_color)
    {
        $this->theme_color = $theme_color;
    }
}
