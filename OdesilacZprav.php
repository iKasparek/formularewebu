<?php
ini_set("display_errors", 1);

class OdesilacZprav
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

    private function ulozPrispevek($zprava_odesilatel, $zprava_email, $zprava_text, $zprava_overeni, $zprava_captcha)
    {
        //$_SESSION['odeslanaZprava'] = '';
        $zprava_html_telo           = '';
        $notifikace_zprava          = '';
        Databaze::dotaz('
            INSERT INTO `tbl_prijatezpravy`
            (`zprava_datum`, `zprava_odesilatel`, `zprava_email`, `zprava_text`, `zprava_overeni`, `zprava_captcha`)
            VALUES (NOW(), ?, ?, ?, ?, ?)
        ', array($zprava_odesilatel, $zprava_email, $zprava_text, $zprava_overeni, $zprava_captcha));

        $zprava_posledniId = Databaze::getLastId();

        $zprava_html_telo = $this->sablonaEmailu->vypisSablonu();
        $zprava_html_telo = str_replace("{{email_odesilatel}}", strip_tags($zprava_odesilatel), $zprava_html_telo);
        $zprava_html_telo = str_replace("{{email_adresa}}", strip_tags($zprava_email), $zprava_html_telo);
        $zprava_html_telo = str_replace("{{email_zprava}}", '<strong>' . strip_tags($zprava_text) . '</strong>', $zprava_html_telo);

        $this->potvrzeni_odeslani_mailu = $this->odesilacEmailu->odesliEmail($zprava_email, $zprava_html_telo);

        Databaze::dotaz('
            UPDATE `tbl_prijatezpravy` SET `zprava_odeslano` = ? WHERE zprava_id = '.$zprava_posledniId.'
        ', array($this->potvrzeni_odeslani_mailu));

    }

    private function vyberPrispevky()
    {
        $vysledek = Databaze::dotaz('
            SELECT `zprava_datum`, `zprava_odesilatel`, `zprava_email`, `zprava_text`
            FROM `tbl_prijatezpravy`
            ORDER BY `zprava_datum` DESC
            LIMIT 30
        ');
        return $vysledek->fetchAll();
    }

    public function pridejKontaktni()
    {
        if (isset($_POST['zprava_text']) && isset($_POST['zprava_email']) )
        {
            if ($this->captcha->over())
            {
                $this->ulozPrispevek($_POST['zprava_odesilatel'], $_POST["zprava_email"], $_POST['zprava_text'], $_POST['zprava_overeni'], $_POST['zprava_captcha']);
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

    private function vypisPrispevky()
    {
        echo('<table border="1">');
        $prispevky = $this->vyberPrispevky();
        foreach ($prispevky as $prispevek)
        {
            echo('<tr><td>');
            echo(htmlspecialchars($prispevek['zprava_odesilatel']));
            echo(' (' . date("j.m.Y H:i", strtotime($prispevek["datum"])) . ') ');
            //echo(htmlspecialchars($prispevek['zprava_text']));
            echo('</td></tr>');
        }
        echo('</table><br />');
    }

    public function vypisFormular()
    {
        $zprava_odesilatel  = (isset($_POST['zprava_odesilatel']) ? $_POST['zprava_odesilatel'] : '');
        $zprava_email       = (isset($_POST["zprava_email"]) ? $_POST["zprava_email"] : '');
        $zprava_text        = (isset($_POST['zprava_text']) ? $_POST['zprava_text'] : '');
        echo('                  <form method="post" class="php-email-form kontaktni-formular">')."\n";
            echo('                      <div class="row">')."\n";
            echo('                        <div class="col form-group">')."\n";
            echo('                          <input type="text" name="zprava_odesilatel" id="zprava_odesilatel" class="form-control" value="' . htmlspecialchars($zprava_odesilatel) . '" placeholder="Vaše jméno a příjmení" required>')."\n";
            echo('                        </div>')."\n";
            echo('                        <div class="col form-group">')."\n";
            echo('                          <input type="email" name="zprava_email" id="zprava_email" class="form-control" value="' . htmlspecialchars($zprava_email) . '" placeholder="Váš e-mail" required>')."\n";
            echo('                        </div>')."\n";
            echo('                      </div>')."\n";
            /*echo('                      <div class="form-group">')."\n";
            echo('                        <input type="text" class="form-control" name="subject" id="subject" placeholder="Předmět" required>')."\n";
            echo('                      </div>')."\n";*/
            echo('                      <div class="form-group">')."\n";
            echo('                        <textarea name="zprava_text" id="zprava_text" class="form-control" rows="5" placeholder="Zpráva" required>' . htmlspecialchars($zprava_text) . '</textarea>')."\n";
            echo('                      </div>')."\n";
            $this->captcha->vypis();
            echo('                         <input type="hidden" name="zprava_captcha" value="" />')."\n";
            echo('                      <div class="text-center"><button type="submit">Odeslat</button></div>')."\n";
        echo('                        </form>')."\n";

    }

    public function vypisKontaktni()
    {
        $this->vypisFormular();
    }

}
