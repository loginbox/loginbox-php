<?php

declare(strict_types = 1);

namespace Loginbox\Box;

use Panda\Ui\Controls\Form;
use Panda\Ui\Controls\FormFactory;
use Panda\Ui\DOMPrototype;
use Panda\Ui\Html\HTMLElement;

/**
 * Class Login
 * Create offline Login Box.
 *
 * @package Loginbox\Box
 * @version 0.1
 */
class Login extends HTMLElement
{
    const LGN_TYPE_PAGE = "page";
    const LGN_TYPE_DIALOG = "dialog";

    /**
     * The header element.
     *
     * @type    HTMLElement
     */
    private $header;

    /**
     * Login constructor.
     *
     * @param DOMPrototype $HTMLDocument
     */
    public function __construct($HTMLDocument)
    {
        // Create HTMLElement
        parent::__construct($HTMLDocument, $name = "div", $value = "", $id = "", $class = "loginDialog");
    }

    /**
     * Build the login box.
     *
     * @param string $username
     * @param string $loginType
     * @param string $returnUrl
     *
     * @return $this
     */
    public function build($username = "", $loginType = self::LGN_TYPE_PAGE, $returnUrl = "")
    {
        // Build header
        $this->buildHeader();

        // Build main body
        $this->buildMainForm($username, $loginType, $returnUrl);

        // Build footer
        $this->buildFooter($returnUrl);

        return $this;
    }

    /**
     * Build the box header.
     */
    private function buildHeader()
    {
        // Header container
        $this->header = $this->getHTMLDocument()->create("div", "", "", "header");
        $this->append($this->header);

        // Login Dialog Title
        $dialogTitle = $this->getHTMLDocument()->create("div", "Account Login", "", "ltitle");
        $this->header->append($dialogTitle);
    }

    /**
     * Build the main dialog form.
     *
     * @param    string $usernameValue
     *        The default username value for the input.
     *        It is empty by default.
     *
     * @param    string $logintype
     *        The login dialog type.
     *        See class constants for more information.
     *
     * @param    string $return_url
     *        Provide a redirect url after successful login.
     *        Leave empty for default action (reload or redirect to my).
     *        It is empty by default.
     *
     * @return    void
     */
    private function buildMainForm($usernameValue = "", $logintype = self::LGN_TYPE_PAGE, $return_url = "")
    {
        // Main Container
        $mainContainer = $this->getHTMLDocument()->create("div", "", "", "main");
        $this->append($mainContainer);

        // Build social login
        $socialLoginContainer = $this->getHTMLDocument()->create("div", "", "", "social");
        $mainContainer->append($socialLoginContainer);

        // Build login form
        $loginForm = new Form($this->getHTMLDocument(), new FormFactory($this->getHTMLDocument()), "loginForm", "/login", false, false, false);
        $mainContainer->append($loginForm);

        // Set login type or return url to dialog
        if (empty($return_url)) {
            $input = $loginForm->getHTMLFormFactory()->buildInput($type = "hidden", $name = "logintype", $value = $logintype, $id = "", $class = "", $autofocus = false, $required = false);
            $loginForm->append($input);
        } else {
            $input = $loginForm->getHTMLFormFactory()->buildInput($type = "hidden", $name = "return_url", $value = $return_url, $id = "", $class = "", $autofocus = false, $required = false);
            $loginForm->append($input);
        }

        // Form container
        $formContainer = $this->getHTMLDocument()->create("div", "", "", "formContainer");
        $loginForm->append($formContainer);

        // Username
        $input = $loginForm->getHTMLFormFactory()->buildInput($type = "text", $name = "username", $value = $usernameValue, $id = "", $class = "lpinp", $autofocus = true, $required = true);
        $input->attr("placeholder", ucfirst("username"));
        $formContainer->append($input);

        // Password
        $input = $loginForm->getHTMLFormFactory()->buildInput($type = "password", $name = "password", $value = "", $id = "", $class = "lpinp", $autofocus = false, $required = true);
        $input->attr("placeholder", ucfirst("password"));
        $formContainer->append($input);

        // Remember me container
        $rcont = $this->getHTMLDocument()->create("div", "", "", "rcont");
        $formContainer->append($rcont);

        // Public session
        $rsession = $this->getHTMLDocument()->create("div", "", "rsession", "rocnt selected");
        $rcont->append($rsession);

        $ricnt = $this->getHTMLDocument()->create("div", "", "", "ricnt");
        $rsession->append($ricnt);

        $input = $loginForm->getHTMLFormFactory()->buildInput($type = "radio", $name = "rememberme", $value = "off", $id = "", $class = "lpchk", $autofocus = false, $required = false);
        $input->attr("checked", true);
        $ricnt->append($input);
        $label = $loginForm->getHTMLFormFactory()->buildLabel($text = "This Session Only", $input->attr("id"), $class = "lplbl");
        $ricnt->append($label);

        // Private session
        $rsession = $this->getHTMLDocument()->create("div", "", "rtrust", "rocnt");
        $rcont->append($rsession);

        $ricnt = $this->getHTMLDocument()->create("div", "", "", "ricnt");
        $rsession->append($ricnt);

        $input = $loginForm->getHTMLFormFactory()->buildInput($type = "radio", $name = "rememberme", $value = "on", $id = "", $class = "lpchk", $autofocus = false, $required = false);
        $ricnt->append($input);
        $label = $loginForm->getHTMLFormFactory()->buildLabel($text = "One month", $input->attr("id"), $class = "lplbl");
        $ricnt->append($label);

        // Remember me notes
        $rnotes = $this->getHTMLDocument()->create("div", "", "", "rnotes");
        $formContainer->append($rnotes);

        $nt = $this->getHTMLDocument()->create("div", "I trust this computer. I will be logged out after one month of inactivity.", "", "nt rsession selected");
        $rnotes->append($nt);

        $nt = $this->getHTMLDocument()->create("div", "This is a public computer.", "", "nt rtrust");
        $rnotes->append($nt);

        // Login button
        $input = $loginForm->getHTMLFormFactory()->buildSubmitButton("Login");
        $formContainer->append($input);
    }

    /**
     * Build the dialog footer.
     *
     * @param string $returnUrl Sets the register dialog link with the given return url after registration.
     *                          It is empty by default.
     */
    private function buildFooter($returnUrl = "")
    {
        // Footer container
        $footer = $this->getHTMLDocument()->create("div", "", "", "footer");
        $this->append($footer);

        // Register link
        $wl = $this->getHTMLDocument()->getHTMLFactory()->buildWeblink("/register", "_self", "Create an Account", "", "");
        $hlink = $this->getHTMLDocument()->create("h4", $wl, "", "register");
        $footer->append($hlink);

        // Bull
        $bull = $this->getHTMLDocument()->create("span");
        $footer->append($bull);
        $bull->innerHTML(" • ");

        // Forgot password link
        $wl = $this->getHTMLDocument()->getHTMLFactory()->buildWeblink("/login/forgot", "_self", "I can't login", "", "");
        $hlink = $this->getHTMLDocument()->create("h4", $wl, "", "forgot");
        $footer->append($hlink);
    }
}