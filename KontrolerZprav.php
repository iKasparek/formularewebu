<?php
ini_set("display_errors", 1);

class KontrolerZprav
{

    public function __construct()
    {
    }


    public function pridejZpravu($zprava)
    {
        if(isset($_SESSION['odeslanaZprava']))
            $_SESSION['odeslanaZprava'][] = $zprava;
        else
            $_SESSION['odeslanaZprava']   = array($zprava);
    }


    public function vratZpravy()
    {
        if(isset($_SESSION['odeslanaZprava']))
        {
            $zpravy = $_SESSION['odeslanaZprava'];
            unset($_SESSION['odeslanaZprava']);
            //return $zpravy;
            foreach($zpravy as $zprava) {
                echo $zprava;
            }
        }
        else
            return array();
    }


    public function vypis()
    {
        $this->vratZpravy();
    }

}
