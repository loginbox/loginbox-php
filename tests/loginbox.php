<?php
// Bootstrap test
include 'bootstrap.php';

// Build sample html document
$htmlDocument = new \Panda\Ui\Html\HTMLPage(new \Panda\Ui\Handlers\HTMLHandler(), new \Panda\Ui\Factories\HTMLFactory());
$htmlDocument->build('Loginbox');
$htmlDocument->addMeta('viewport', 'width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0');
$htmlDocument->addScript('js/jquery/jquery-2.1.4.min.js');
$htmlDocument->addStyle('css/loginpage.css');

// Create loginbox
$login = new \Loginbox\Box\Login($htmlDocument, new \Panda\Ui\Factories\FormFactory());

// Set settings
$settings = new \Loginbox\Config\LoginSettings();
$settings->setLocale('en_US');
$login->setSettings($settings);

// Build loginbox
$login->build();
$htmlDocument->getBody()->append($login);

echo $htmlDocument->getHTML();