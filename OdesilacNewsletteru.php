<?php
ini_set("display_errors", 1);

class OdesilacNewsletteru
{

    private $captcha;
    private $potvrzeni_odeslani_mailu;
    private $sablonaEmailu;
    private $odesilacEmailu;
    private $kontrolerZprav;

    public function __construct()
    {
        $this->captcha              = new CaptchaObrazek();
        $this->sablonaEmailu        = new SablonaEmailu();
        $this->odesilacEmailu       = new OdesilacEmailu();
        $this->kontrolerZprav       = new KontrolerZprav();
    }

    private function pridejDoDatabaze($newsletter_email, $zprava_overeni, $zprava_captcha)
    {
        //$_SESSION['odeslanaZprava'] = '';
        $zprava_html_telo           = '';
        $notifikace_zprava          = '';
        Databaze::dotaz('
            INSERT INTO `tbl_newsletter`
            (`lett_datum`, `lett_email`, `lett_overeni`, `lett_captcha`)
            VALUES (NOW(), ?, ?, ?)
        ', array($newsletter_email, $zprava_overeni, $zprava_captcha));

        $zprava_posledniId = Databaze::getLastId();

        $zprava_html_telo = $this->sablonaEmailu->vypisSablonu();
        $zprava_html_telo = str_replace('{{email_odesilatel}}', 'Přidání adresy pro příjem newsletteru', $zprava_html_telo);
        $zprava_html_telo = str_replace('{{email_adresa}}', strip_tags($newsletter_email), $zprava_html_telo);
        $zprava_html_telo = str_replace('{{email_zprava}}', '<strong>Děkujeme za projevený zájem a zaslanou adresu pro příjem newsletteru.</strong>', $zprava_html_telo);

        $this->potvrzeni_odeslani_mailu = $this->odesilacEmailu->odesliEmail($newsletter_email, $zprava_html_telo, 'newsletter');

        Databaze::dotaz('
            UPDATE `tbl_newsletter` SET `lett_odeslano` = ? WHERE lett_id = '.$zprava_posledniId.'
        ', array($this->potvrzeni_odeslani_mailu));

    }

    public function pridejNewsletter()
    {
        if (isset($_POST['zprava_overeni']) && isset($_POST['newsletter_mail']) )
        {
            if ($this->captcha->over())
            {
                $this->pridejDoDatabaze($_POST["newsletter_mail"], $_POST['zprava_overeni'], $_POST['zprava_captcha']);
                $notifikace_zprava = '<div class="alert alert-success alert-dismissible fade show" role="alert">Zpráva byla odeslána. Děkujeme. ('.$this->potvrzeni_odeslani_mailu.') <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zavřít"></button></div>';
                $this->kontrolerZprav->pridejZpravu($notifikace_zprava);
                header('Location: https://presmerovana.adresa/');
                exit;
            }
            $notifikace_zprava = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Odeslání se nepodařilo ověřit. Vyplňte správně všechny pole a zkuste to znovu, prosím.'.$potvrzeni_odeslani_mailu.' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zavřít"></button></div>';
            $this->kontrolerZprav->pridejZpravu($notifikace_zprava);
        }
        return false;
    }

    public function vypisFormular()
    {
        $newsletter_email = (isset($_POST["newsletter_mail"]) ? $_POST["newsletter_mail"] : '');
            echo('            <form action="" method="post" id="newsletter_form">')."\n";
            echo('              <input type="email" name="newsletter_mail" id="newsletter_mail" value="' . htmlspecialchars($newsletter_email) . '" placeholder="Zadejte Vaši e-mailovou adresu...">')."\n";
            $this->captcha->vypis('small');
            echo('              <input type="hidden" name="zprava_captcha" value="" />')."\n";
            echo('              <input type="submit" value="Odeslat">')."\n";
            echo('            </form>')."\n";

    }

    public function vypisNewsletter()
    {
        $this->vypisFormular();
    }

}
