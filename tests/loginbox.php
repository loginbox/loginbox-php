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

// Set theme settings
$themeSettings = new \Loginbox\Config\ThemeSettings();
$themeSettings->setLogoUrl('https://ratein.io/assets/images/logo.svg');
$themeSettings->setThemeColor('#335f7f');
$login->setThemeSettings($themeSettings);

// Set social settings
$socialSettings = new \Loginbox\Config\SocialSettings();
$socialSettings->setEnabled(true);
$socialSettings->setSocialNetworkUrl(\Loginbox\Config\SocialSettings::SC_FACEBOOK, 'https://facebook.com');
$socialSettings->setSocialNetworkUrl(\Loginbox\Config\SocialSettings::SC_GOOGLE, 'https://google.com');
$socialSettings->setSocialNetworkUrl(\Loginbox\Config\SocialSettings::SC_LINKEDIN, 'https://linkedin.com');
$login->setSocialSettings($socialSettings);

// Build loginbox
$login->build('', \Loginbox\Box\Login::LB_MODE_LOGIN);
$htmlDocument->getBody()->append($login);

echo $htmlDocument->getHTML();