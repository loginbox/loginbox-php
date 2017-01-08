<?php

/*
 * This file is part of loginBox php library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loginbox\Box;

use Exception;
use InvalidArgumentException;
use Loginbox\Config\LoginSettings;
use Loginbox\Config\SocialSettings;
use Loginbox\Config\ThemeSettings;
use Panda\Localization\Translation\JsonProcessor;
use Panda\Localization\Translator;
use Panda\Ui\Contracts\Factories\HTMLFormFactoryInterface;
use Panda\Ui\Html\HTMLDocument;
use Panda\Ui\Html\HTMLElement;

/**
 * Class Login
 * Create offline Login Box.
 *
 * @package Loginbox\Box
 *
 * @version 0.1
 */
class Login extends HTMLElement
{
    const LB_TYPE_PAGE = 'page';
    const LB_TYPE_DIALOG = 'dialog';

    const LB_MODE_LOGIN = 'login';
    const LB_MODE_REGISTER = 'register';
    const LB_MODE_RECOVER = 'recover';
    const LB_MODE_RESET = 'reset';

    /**
     * @type HTMLFormFactoryInterface
     */
    private $formFactory;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var LoginSettings
     */
    private $settings;

    /**
     * @var ThemeSettings
     */
    private $themeSettings;

    /**
     * @var SocialSettings
     */
    private $socialSettings;

    /**
     * @var \DOMNode
     */
    private $htmlNode;

    /**
     * Login constructor.
     *
     * @param HTMLDocument             $HTMLDocument
     * @param HTMLFormFactoryInterface $FormFactory
     */
    public function __construct(HTMLDocument $HTMLDocument, HTMLFormFactoryInterface $FormFactory)
    {
        // Set form factory
        $FormFactory->setHTMLDocument($HTMLDocument);
        $this->formFactory = $FormFactory;
        $this->htmlNode = $HTMLDocument->select('html')->item(0);

        // Create translator
        $this->translator = new Translator(new JsonProcessor(__DIR__ . '/../../../lang/'));

        // Set empty settings
        $this->setSettings(new LoginSettings());
        $this->setThemeSettings(new ThemeSettings());
        $this->setSocialSettings(new SocialSettings());

        // Create HTMLElement
        parent::__construct($HTMLDocument, $name = 'div', $value = '', $id = '', $class = 'identity-login-box-container');
    }

    /**
     * Build the login box.
     *
     * @param string $username
     * @param string $mode
     * @param string $loginType
     *
     * @return $this
     *
     * @throws Exception
     */
    public function build($username = '', $mode = self::LB_MODE_LOGIN, $loginType = self::LB_TYPE_PAGE)
    {
        // Check for settings
        if (empty($this->getSettings())) {
            throw new Exception('Login settings are not defined.');
        }

        // Append this to html
        $this->getHTMLDocument()->getHTMLHandler()->append($this->htmlNode, $this);

        // Load html into the container
        $html = file_get_contents(__DIR__ . '/../../../assets/html/login.html');
        $this->innerHTML($html);

        if ($loginType == static::LB_TYPE_DIALOG) {
            $identityBox = $this->select('.identity-login-box')->item(0);
            $this->getHTMLHandler()->addClass($identityBox, 'dialog');
        }

        // Append resources
        $this->appendResources();

        // Build main body
        $this->buildLoginForm($username);
        $this->buildRegistrationForm();
        $this->buildPasswordRecoveryForm();

        // Build social logins
        $this->buildSocialLogin();

        // Set selected form
        $this->selectBox($mode);

        // Translate the entire box
        $this->translateBox();

        // Apply theme
        $this->applyTheme();

        return $this;
    }

    /**
     * Append Javascript and css to the loginbox.
     *
     * @return $this
     */
    private function appendResources()
    {
        // Append script
        $loginbox_js = file_get_contents(__DIR__ . '/../../../assets/js/loginbox.js');
        $script = $this->getHTMLDocument()->create('script', $loginbox_js);
        $this->prepend($script);

        // Append css
        $loginbox_css = file_get_contents(__DIR__ . '/../../../assets/css/loginbox.css');
        $style = $this->getHTMLDocument()->create('style', $loginbox_css);
        $this->prepend($style);

        return $this;
    }

    /**
     * Build the main login form.
     *
     * @param string $username The default username value for the input
     *
     * @return $this
     */
    private function buildLoginForm($username = '')
    {
        // Form Container
        $formContainer = $this->getHTMLDocument()->select('.identity-login-box .box-main.login .form-container')->item(0);

        // Build form
        $form = $this->getFormFactory()->buildForm('login-form', $this->getSettings()->getLoginUrl(), true, false);
        $this->getHTMLHandler()->append($formContainer, $form);

        // Set return url to dialog
        if (!empty($this->getSettings()->getLoginRedirectUrl())) {
            $input = $form->getHTMLFormFactory()->buildInput($type = 'hidden', $name = 'redirect_url', $value = $this->getSettings()->getLoginRedirectUrl(), $id = '', $class = '', $autofocus = false, $required = false);
            $form->append($input);
        }
        if ($this->getSettings()->isRememberMe()) {
            $input = $form->getHTMLFormFactory()->buildInput($type = 'hidden', $name = 'rememberme', $value = '1', $id = '', $class = '', $autofocus = false, $required = false);
            $form->append($input);
        }

        // Create input container
        $inputContainer = $this->getHTMLDocument()->create('div', '', '', 'inp-container');
        $form->append($inputContainer);

        // Username
        $input = $form->getHTMLFormFactory()->buildInput($type = 'text', $name = 'username', $value = $username, $id = '', $class = 'uiFormInput lpinp', $autofocus = empty($username), $required = true);
        $input->attr('placeholder', ucfirst($this->translate('loginbox.login.form.username')));
        $inputContainer->append($input);

        // Password
        $input = $form->getHTMLFormFactory()->buildInput($type = 'password', $name = 'password', $value = '', $id = '', $class = 'uiFormInput lpinp', $autofocus = !empty($username), $required = true);
        $input->attr('placeholder', ucfirst($this->translate('loginbox.login.form.password')));
        $inputContainer->append($input);

        // Error container
        $errContainer = $this->getHTMLDocument()->create('div', '', '', 'err-container');
        $form->append($errContainer);

        // Login button
        $input = $form->getHTMLFormFactory()->buildSubmitButton($this->translate('loginbox.login.form.submit'), '', '', 'positive lpbtn');
        $form->append($input);

        return $this;
    }

    /**
     * Build the registration form.
     *
     * @return $this
     */
    private function buildRegistrationForm()
    {
        // Form Container
        $formContainer = $this->getHTMLDocument()->select('.identity-login-box .box-main.register .form-container')->item(0);

        // Build form
        $form = $this->getFormFactory()->buildForm('register-form', $this->getSettings()->getRegisterUrl(), true, false);
        $this->getHTMLHandler()->append($formContainer, $form);

        // Set return url to dialog
        if (!empty($this->getSettings()->getRegisterRedirectUrl())) {
            $input = $form->getHTMLFormFactory()->buildInput($type = 'hidden', $name = 'redirect_url', $value = $this->getSettings()->getRegisterRedirectUrl(), $id = '', $class = '', $autofocus = false, $required = false);
            $form->append($input);
        }
        // Login indicator
        if (!empty($this->getSettings()->isLoginAfterRegistration())) {
            $input = $form->getHTMLFormFactory()->buildInput($type = 'hidden', $name = 'login', $value = '1', $id = '', $class = '', $autofocus = false, $required = false);
            $form->append($input);
        }

        // Create input container
        $inputContainer = $this->getHTMLDocument()->create('div', '', '', 'inp-container');
        $form->append($inputContainer);

        // Name
        $input = $form->getHTMLFormFactory()->buildInput($type = 'text', $name = 'full_name', $value = '', $id = '', $class = 'uiFormInput lpinp', $autofocus = true, $required = true);
        $input->attr('placeholder', ucfirst($this->translate('loginbox.register.form.full_name')));
        $inputContainer->append($input);

        // Username
        $input = $form->getHTMLFormFactory()->buildInput($type = 'email', $name = 'email', $value = '', $id = '', $class = 'uiFormInput lpinp', $autofocus = false, $required = true);
        $input->attr('placeholder', ucfirst($this->translate('loginbox.register.form.email')));
        $inputContainer->append($input);

        // Password
        $input = $form->getHTMLFormFactory()->buildInput($type = 'password', $name = 'password', $value = '', $id = '', $class = 'uiFormInput lpinp', $autofocus = false, $required = true);
        $input->attr('placeholder', ucfirst($this->translate('loginbox.register.form.password')));
        $inputContainer->append($input);

        // Error container
        $errContainer = $this->getHTMLDocument()->create('div', '', '', 'err-container');
        $form->append($errContainer);

        // Login button
        $input = $form->getHTMLFormFactory()->buildSubmitButton($this->translate('loginbox.register.form.submit'), '', '', 'positive lpbtn');
        $form->append($input);


        // Set terms url
        $terms = $this->select('.identity-login-box .box-main.register a.terms')->item(0);
        $this->getHTMLHandler()->attr($terms, 'href', $this->getSettings()->getRegisterTermsUrl());

        return $this;
    }

    /**
     * Build the password recovery form
     *
     * @return $this
     */
    private function buildPasswordRecoveryForm()
    {
        // Build password recovery form
        // Form Container
        $formContainer = $this->getHTMLDocument()->select('.identity-login-box .box-main.recover .form-container')->item(0);

        // Build form
        $form = $this->getFormFactory()->buildForm('password-recovery-form', $this->getSettings()->getPasswordRecoveryUrl(), true, false);
        $this->getHTMLHandler()->append($formContainer, $form);

        // Create input container
        $inputContainer = $this->getHTMLDocument()->create('div', '', '', 'inp-container');
        $form->append($inputContainer);

        // Email
        $input = $form->getHTMLFormFactory()->buildInput($type = 'email', $name = 'email', $value = '', $id = '', $class = 'uiFormInput lpinp', $autofocus = false, $required = true);
        $input->attr('placeholder', ucfirst($this->translate('loginbox.recover.form.email')));
        $inputContainer->append($input);

        // Error container
        $errContainer = $this->getHTMLDocument()->create('div', '', '', 'err-container');
        $form->append($errContainer);

        // Login button
        $input = $form->getHTMLFormFactory()->buildSubmitButton($this->translate('loginbox.recover.form.submit'), '', '', 'positive lpbtn');
        $form->append($input);

        // Build password reset form
        // Form Container
        $formContainer = $this->getHTMLDocument()->select('.identity-login-box .box-main.reset .form-container')->item(0);

        // Build form
        $form = $this->getFormFactory()->buildForm('password-reset-form', $this->getSettings()->getPasswordResetUrl(), true, false);
        $this->getHTMLHandler()->append($formContainer, $form);

        // Create input container
        $inputContainer = $this->getHTMLDocument()->create('div', '', '', 'inp-container');
        $form->append($inputContainer);

        // Email
        $input = $form->getHTMLFormFactory()->buildInput($type = 'text', $name = 'challenge', $value = '', $id = '', $class = 'uiFormInput lpinp', $autofocus = true, $required = true);
        $input->attr('placeholder', ucfirst($this->translate('loginbox.reset.form.challenge')));
        $inputContainer->append($input);

        // Password
        $input = $form->getHTMLFormFactory()->buildInput($type = 'password', $name = 'password', $value = '', $id = '', $class = 'uiFormInput lpinp', $autofocus = false, $required = true);
        $input->attr('placeholder', ucfirst($this->translate('loginbox.reset.form.password')));
        $inputContainer->append($input);

        // Password
        $input = $form->getHTMLFormFactory()->buildInput($type = 'password', $name = 'password_confirm', $value = '', $id = '', $class = 'uiFormInput lpinp', $autofocus = false, $required = true);
        $input->attr('placeholder', ucfirst($this->translate('loginbox.reset.form.password_confirm')));
        $inputContainer->append($input);

        // Error container
        $errContainer = $this->getHTMLDocument()->create('div', '', '', 'err-container');
        $form->append($errContainer);

        // Login button
        $input = $form->getHTMLFormFactory()->buildSubmitButton($this->translate('loginbox.reset.form.submit'), '', '', 'positive lpbtn');
        $form->append($input);

        return $this;
    }

    /**
     * Translate the entire box.
     *
     * @return $this
     */
    private function translateBox()
    {
        // Get all data-translate elements
        $translatable = $this->select('*[data-translate]');
        foreach ($translatable as $item) {
            $key = $this->getHTMLHandler()->attr($item, 'data-translate');
            $translation = $this->translator->translate($key, 'translations', $this->getSettings()->getLocale());
            $this->getHTMLHandler()->attr($item, 'data-translate', null);
            $this->getHTMLHandler()->nodeValue($item, $translation);
        }

        return $this;
    }

    /**
     * Translate the given key
     *
     * @param string $key
     * @param string $locale
     *
     * @return string
     */
    private function translate($key, $locale = null)
    {
        $locale = $locale ?: $this->getSettings()->getLocale();

        return $this->translator->translate($key, 'translations', $locale);
    }

    /**
     * enabled the selected box mode
     *
     * @param string $mode
     */
    private function selectBox($mode)
    {
        // Check arguments
        if (empty($mode)) {
            throw new InvalidArgumentException('There is no login mode selected.');
        }

        // Show box
        $boxes = $this->select('.identity-login-box .box-main');
        foreach ($boxes as $box) {
            $this->getHTMLHandler()->addClass($box, 'hidden');
        }
        $selectedBoxes = $this->select('.identity-login-box .box-main.' . $mode);
        foreach ($selectedBoxes as $box) {
            $this->getHTMLHandler()->removeClass($box, 'hidden');
        }

        // Hide footer link
        $footerLinks = $this->select('.identity-login-box .box-footer .ft-lnk');
        foreach ($footerLinks as $footerLink) {
            $this->getHTMLHandler()->removeClass($footerLink, 'hidden');
        }
        $selectedFooterLinks = $this->select('.identity-login-box .box-footer .ft-lnk.' . $mode);
        foreach ($selectedFooterLinks as $footerLink) {
            $this->getHTMLHandler()->addClass($footerLink, 'hidden');
        }
    }

    /**
     * Apply the selected theme to the box
     */
    private function applyTheme()
    {
        // Check logo
        if (!empty($this->getThemeSettings()->getLogoUrl())) {
            // Create image
            $img = $this->getHTMLDocument()->create('img');
            $img->attr('src', $this->getThemeSettings()->getLogoUrl());

            $logo_ico = $this->select('.identity-login-box .box-header .login-ico')->item(0);
            $this->getHTMLHandler()->append($logo_ico, $img);
        }

        // Check theme color
        if (!empty($this->getThemeSettings()->getThemeColor())) {
            $color = $this->getThemeSettings()->getThemeColor();

            // Apply color
            $items = $this->select('.identity-login-box .bx-hd-title');
            foreach ($items as $item) {
                $this->getHTMLHandler()->style($item, 'color', $color);
            }
            $items = $this->select('.identity-login-box .bx-sub-title.action');
            foreach ($items as $item) {
                $this->getHTMLHandler()->style($item, 'color', $color);
            }
            $items = $this->select('.identity-login-box .lpbtn');
            foreach ($items as $item) {
                $this->getHTMLHandler()->style($item, 'background-color', $color);
            }
        }
    }

    /**
     * Build social login urls
     */
    private function buildSocialLogin()
    {
        // Check if it's enabled
        $socialContainer = $this->select('.identity-login-box .login-register-container .social-container')->item(0);
        if (!$this->getSocialSettings()->isEnabled()) {
            $this->getHTMLHandler()->remove($socialContainer);

            return;
        }

        // Enable social container
        $this->getHTMLHandler()->removeClass($socialContainer, 'hidden');

        // Get all social networks
        $networks = $this->getSocialSettings()->getAllSocialNetworks();
        foreach ($networks as $network) {
            // Get button
            $scButton = $this->select('.identity-login-box .login-register-container .social-container .btn_social.' . $network)->item(0);

            // Get url
            $url = $this->getSocialSettings()->getSocialNetworkUrl($network);
            if (!empty($url)) {
                $this->getHTMLHandler()->attr($scButton, 'href', $url);
            } else {
                $this->getHTMLHandler()->remove($scButton);
            }
        }
    }

    /**
     * @return HTMLFormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @return LoginSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param LoginSettings $settings
     *
     * @return Login
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return ThemeSettings
     */
    public function getThemeSettings(): ThemeSettings
    {
        return $this->themeSettings;
    }

    /**
     * @param ThemeSettings $themeSettings
     */
    public function setThemeSettings(ThemeSettings $themeSettings)
    {
        $this->themeSettings = $themeSettings;
    }

    /**
     * @return SocialSettings
     */
    public function getSocialSettings(): SocialSettings
    {
        return $this->socialSettings;
    }

    /**
     * @param SocialSettings $socialSettings
     */
    public function setSocialSettings(SocialSettings $socialSettings)
    {
        $this->socialSettings = $socialSettings;
    }
}
