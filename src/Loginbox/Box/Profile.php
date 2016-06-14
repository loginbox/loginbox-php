<?php

declare(strict_types = 1);

namespace Loginbox\Box;

use Panda\Ui\Contracts\Factories\HTMLFormFactoryInterface;
use Panda\Ui\Controls\Form;
use Panda\Ui\DOMPrototype;
use Panda\Ui\Factories\FormFactory;
use Panda\Ui\Html\HTMLElement;
use Panda\Ui\Templates\Forms\SimpleForm;

/**
 * Class Profile
 * Create offline Profile Box.
 *
 * @package Loginbox\Box
 * @version 0.1
 */
class Profile extends HTMLElement
{
    /**
     * @type HTMLFormFactoryInterface
     */
    private $formFactory;

    /**
     * Login constructor.
     *
     * @param DOMPrototype             $HTMLDocument
     * @param HTMLFormFactoryInterface $FormFactory
     */
    public function __construct($HTMLDocument, $FormFactory = null)
    {
        // Create HTMLElement
        parent::__construct($HTMLDocument, $name = 'div', $value = '', $id = '', $class = 'profileBox');
        $this->formFactory = $FormFactory ?: new FormFactory($HTMLDocument);
    }

    /**
     * Build the profile box.
     *
     * @param string $logoutUrl
     *
     * @return $this
     */
    public function build($logoutUrl = '')
    {
        // Build the view panels
        $this->buildProfileViewPanel($logoutUrl);

        return $this;
    }

    /**
     * Build the profile view panel.
     *
     * @param $logoutUrl
     *
     * @return $this
     */
    private function buildProfileViewPanel($logoutUrl)
    {
        // Header container
        $viewPanel = $this->getViewPanel('open active');
        $this->append($viewPanel);

        $profileMenu = $this->getHTMLDocument()->create('ul', '', '', 'profileMenu');
        $viewPanel->append($profileMenu);

        $a = $this->getHTMLDocument()->getHTMLFactory()->buildWeblink('/profile', '_self', 'Profile', '', '');
        $mItem = $this->getHTMLDocument()->create('li', $a, '', 'mitem profile');
        $profileMenu->append($mItem);

        $mseparator = $this->getHTMLDocument()->create('li', '', '', 'mseparator');
        $profileMenu->append($mseparator);

        // Build logout form
        $logoutForm = $this->getLogoutForm($logoutUrl);
        $formContainer = $this->getHTMLDocument()->create('div', $logoutForm, '', 'formContainer logout');
        $mItem = $this->getHTMLDocument()->create('li', $formContainer, '', 'mitem logout');
        $profileMenu->append($mItem);

        return $this;
    }

    /**
     * Create a profile box view panel
     *
     * @param string $class
     *
     * @return $this
     */
    private function getViewPanel($class = '')
    {
        return $this->getHTMLDocument()->create('div', '', '', 'viewPanel')->addClass($class);
    }

    /**
     * Build the logout inline async form.
     *
     * @param $logoutUrl
     *
     * @return HTMLElement
     */
    private function getLogoutForm($logoutUrl)
    {
        $form = new SimpleForm($this->getHTMLDocument(), null, '', $logoutUrl, true, false);
        $form->build(false, false);

        $logoutSubmit = $form->getHTMLFormFactory()->buildInput($type = 'submit', $name = 'logout', $value = 'Logout', $id = '', $class = 'logoutButton', $required = false, $autofocus = false);
        $form->append($logoutSubmit);

        return $form;
    }
}