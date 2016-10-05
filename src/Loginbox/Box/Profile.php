<?php

/*
 * This file is part of loginBox php library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loginbox\Box;

use Panda\Ui\Contracts\Factories\HTMLFormFactoryInterface;
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
        parent::__construct($HTMLDocument, $name = 'div', $value = '', $id = '', $class = 'identity-profile-box-container');
    }

    /**
     * Build the profile box.
     *
     * @return $this
     */
    public function build()
    {
        return $this;
    }

    /**
     * @return HTMLFormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }
}
