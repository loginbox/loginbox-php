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
    protected $login_redirect_url = '';

    /**
     * @var string
     */
    protected $register_url;

    /**
     * @var string
     */
    protected $register_redirect_url = '';

    /**
     * @var string
     */
    protected $register_terms_url = '';

    /**
     * @var bool
     */
    protected $login_after_registration = false;

    /**
     * @var string
     */
    protected $password_recovery_url = '';

    /**
     * @var string
     */
    protected $password_reset_url = '';

    /**
     * @return boolean
     */
    public function isRememberMe()
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
    public function getLoginRedirectUrl()
    {
        return $this->login_redirect_url;
    }

    /**
     * @param string $login_redirect_url
     */
    public function setLoginRedirectUrl(string $login_redirect_url)
    {
        $this->login_redirect_url = $login_redirect_url;
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
    public function getRegisterRedirectUrl()
    {
        return $this->register_redirect_url;
    }

    /**
     * @param string $register_redirect_url
     */
    public function setRegisterRedirectUrl(string $register_redirect_url)
    {
        $this->register_redirect_url = $register_redirect_url;
    }

    /**
     * @return string
     */
    public function getRegisterTermsUrl()
    {
        return $this->register_terms_url;
    }

    /**
     * @param string $register_terms_url
     */
    public function setRegisterTermsUrl(string $register_terms_url)
    {
        $this->register_terms_url = $register_terms_url;
    }

    /**
     * @return boolean
     */
    public function isLoginAfterRegistration()
    {
        return $this->login_after_registration;
    }

    /**
     * @param boolean $login_after_registration
     */
    public function setLoginAfterRegistration(bool $login_after_registration)
    {
        $this->login_after_registration = $login_after_registration;
    }

    /**
     * @return string
     */
    public function getPasswordRecoveryUrl()
    {
        return $this->password_recovery_url;
    }

    /**
     * @param string $password_recovery_url
     */
    public function setPasswordRecoveryUrl(string $password_recovery_url)
    {
        $this->password_recovery_url = $password_recovery_url;
    }

    /**
     * @return string
     */
    public function getPasswordResetUrl()
    {
        return $this->password_reset_url;
    }

    /**
     * @param string $password_reset_url
     */
    public function setPasswordResetUrl(string $password_reset_url)
    {
        $this->password_reset_url = $password_reset_url;
    }
}
