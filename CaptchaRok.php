<?php

/*
 * To jsem se naučil na
 * IT sociální síti WWW.ITNETWORK.CZ
 *
 */

class CaptchaRok implements Captcha
{

    public function vypis()
    {
        echo('Zadejte aktuální rok: ');
        echo('<input type="text" name="overeni" />');
    }

    public function over()
    {
        return ($_POST['overeni'] == date("Y"));
    }

}
