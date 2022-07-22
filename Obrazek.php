<?php
ini_set("display_errors", 1);
/*
 *  To jsem se naučil na
 *  na www.devbook.cz :-)
 */

//session_start();
//require(__DIR__ . '/vendor/formularewebu/Captcha.php');
//require(__DIR__ . '/vendor/formularewebu/CaptchaObrazek.php');

require(__DIR__ . '/Captcha.php');
require(__DIR__ . '/CaptchaObrazek.php');

$captcha = new CaptchaObrazek();
$captcha->generujObrazek();