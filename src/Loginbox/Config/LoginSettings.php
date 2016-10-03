<?php

/*
 * This file is part of loginBox php library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loginbox\Config;

/**
 * Class LoginSettings
 * @package Loginbox\Config
 */
class LoginSettings extends GlobalSettings
{
    /**
     * @var bool
     */
    protected $rememberMe = false;

    /**
     * @var string
     */
    protected $login_url;

    /**
     * @var string
     */
    protected $login_return_url = '';

    /**
     * @var string
     */
    protected $register_url;

    /**
     * @var string
     */
    protected $register_return_url = '';

    /**
     * @var string
     */
    protected $forgot_password_url;

    /**
     * @var string
     */
    protected $reset_password_url;

    /**
     * @return boolean
     */
    public function isRememberMe(): bool
    {
        return $this->rememberMe;
    }

    /**
     * @param boolean $rememberMe
     */
    public function setRememberMe(bool $rememberMe)
    {
        $this->rememberMe = $rememberMe;
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->login_url;
    }

    /**
     * @param string $login_url
     */
    public function setLoginUrl($login_url)
    {
        $this->login_url = $login_url;
    }

    /**
     * @return string
     */
    public function getLoginReturnUrl(): string
    {
        return $this->login_return_url;
    }

    /**
     * @param string $login_return_url
     */
    public function setLoginReturnUrl(string $login_return_url)
    {
        $this->login_return_url = $login_return_url;
    }

    /**
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->register_url;
    }

    /**
     * @param string $register_url
     */
    public function setRegisterUrl($register_url)
    {
        $this->register_url = $register_url;
    }

    /**
     * @return string
     */
    public function getRegisterReturnUrl(): string
    {
        return $this->register_return_url;
    }

    /**
     * @param string $register_return_url
     */
    public function setRegisterReturnUrl(string $register_return_url)
    {
        $this->register_return_url = $register_return_url;
    }

    /**
     * @return string
     */
    public function getForgotPasswordUrl()
    {
        return $this->forgot_password_url;
    }

    /**
     * @param string $forgot_password_url
     */
    public function setForgotPasswordUrl($forgot_password_url)
    {
        $this->forgot_password_url = $forgot_password_url;
    }

    /**
     * @return string
     */
    public function getResetPasswordUrl()
    {
        return $this->reset_password_url;
    }

    /**
     * @param string $reset_password_url
     */
    public function setResetPasswordUrl($reset_password_url)
    {
        $this->reset_password_url = $reset_password_url;
    }
}
