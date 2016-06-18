<?php

declare(strict_types = 1);

namespace Loginbox\Box;

use Panda\Ui\Contracts\Factories\HTMLFormFactoryInterface;
use Panda\Ui\Html\Controls\Form;
use Panda\Ui\Html\HTMLDocument;
use Panda\Ui\Html\HTMLElement;

/**
 * Class Profile
 * Create offline Profile Box.
 *
 * @package Loginbox\Box
 *
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
     * @param HTMLDocument             $HTMLDocument
     * @param HTMLFormFactoryInterface $FormFactory
     */
    public function __construct(HTMLDocument $HTMLDocument, HTMLFormFactoryInterface $FormFactory)
    {
        // Set form factory
        $FormFactory->setHTMLDocument($HTMLDocument);
        $this->formFactory = $FormFactory;

        // Create HTMLElement
        parent::__construct($HTMLDocument, $name = 'div', $value = '', $id = '', $class = 'profileBox');
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
        // Build simple form
        $form = $this->getFormFactory()->buildForm('logoutForm', $logoutUrl, true, false);

        $logoutSubmit = $form->getHTMLFormFactory()->buildInput($type = 'submit', $name = 'logout', $value = 'Logout', $id = '', $class = 'logoutButton', $required = false, $autofocus = false);
        $form->append($logoutSubmit);

        return $form;
    }

    /**
     * @return HTMLFormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }
}