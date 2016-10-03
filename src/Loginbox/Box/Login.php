<?php

/*
 * This file is part of loginBox php library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loginbox\Box;

use Exception;
use Loginbox\Config\LoginSettings;
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
    const LGN_TYPE_PAGE = 'page';
    const LGN_TYPE_DIALOG = 'dialog';

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

        // Create HTMLElement
        parent::__construct($HTMLDocument, $name = 'div', $value = '', $id = '', $class = 'loginBox-login-box-container');
    }

    /**
     * Build the login box.
     *
     * @param string $username
     * @param string $loginType
     *
     * @return $this
     *
     * @throws Exception
     */
    public function build($username = '', $loginType = self::LGN_TYPE_PAGE)
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

        // Append resources
        $this->appendResources();

        // Build main body
        $this->buildLoginForm($username, $loginType);

        // Translate the entire box
        $this->translate();

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
     * Build the main dialog form.
     *
     * @param string $username  The default username value for the input.
     * @param string $logintype The login dialog type.
     *
     * @return $this
     */
    private function buildLoginForm($username = '', $logintype = self::LGN_TYPE_PAGE)
    {
        // Form Container
        $formContainer = $this->getHTMLDocument()->select('.login-box .login-container .login-box-form-container')->item(0);

        // Build login form
        $loginForm = $this->getFormFactory()->buildForm('login-form', $this->getSettings()->getLoginUrl(), true, false);
        $this->getHTMLHandler()->append($formContainer, $loginForm);

        // Set login type or return url to dialog
        $input = $loginForm->getHTMLFormFactory()->buildInput($type = 'hidden', $name = 'logintype', $value = $logintype, $id = '', $class = '', $autofocus = false, $required = false);
        $loginForm->append($input);
        if (!empty($this->getSettings()->getLoginReturnUrl())) {
            $input = $loginForm->getHTMLFormFactory()->buildInput($type = 'hidden', $name = 'return_url', $value = $this->getSettings()->getLoginReturnUrl(), $id = '', $class = '', $autofocus = false, $required = false);
            $loginForm->append($input);
        }
        if ($this->getSettings()->isRememberMe()) {
            $input = $loginForm->getHTMLFormFactory()->buildInput($type = 'hidden', $name = 'rememberme', $value = '1', $id = '', $class = '', $autofocus = false, $required = false);
            $loginForm->append($input);
        }

        // Username
        $input = $loginForm->getHTMLFormFactory()->buildInput($type = 'text', $name = 'username', $value = $username, $id = '', $class = 'lpinp', $autofocus = true, $required = true);
        $input->attr('placeholder', ucfirst('username'));
        $loginForm->append($input);

        // Password
        $input = $loginForm->getHTMLFormFactory()->buildInput($type = 'password', $name = 'password', $value = '', $id = '', $class = 'lpinp', $autofocus = false, $required = true);
        $input->attr('placeholder', ucfirst('password'));
        $loginForm->append($input);

        // Login button
        $input = $loginForm->getHTMLFormFactory()->buildSubmitButton('Login');
        $loginForm->append($input);

        return $this;
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
     * Translate the entire box.
     *
     * @return $this
     */
    private function translate()
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
}
