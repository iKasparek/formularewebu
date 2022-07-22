<?php
  ini_set("display_errors", 1);
/*
 *
 * Toto jsem se naučil na
 * IT sociální síti WWW.ITNETWORK.CZ
 *
 */

class OdesilacFaktury
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

    private function ulozFakturu($fakt_datum, $fakt_spolecnost, $fakt_ico, $fakt_adresa, $fakt_email, $fakt_osoba, $fakt_telefon, $fakt_text, $fakt_gdpr, $fakt_overeni, $fakt_captcha)
    {
        //$_SESSION['odeslanaZprava'] = '';
        $zprava_html_telo           = '';
        $notifikace_zprava          = '';
        $zprava_text                = '';
        $fakt_gdpr_insert           = ($fakt_gdpr == 'ano' ? 1 : 0);
        Databaze::dotaz('
            INSERT INTO `tbl_fakturace`
            (`fakt_datum`, `fakt_spolecnost`, `fakt_ico`, `fakt_adresa`, `fakt_email`, `fakt_osoba`, `fakt_telefon`, `fakt_text`, `fakt_gdpr`, `fakt_overeni`, `fakt_captcha`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ', array($fakt_datum, $fakt_spolecnost, $fakt_ico, $fakt_adresa, $fakt_email, $fakt_osoba, $fakt_telefon, $fakt_text, $fakt_gdpr_insert, $fakt_overeni, $fakt_captcha));

        $fakt_posledniId    = Databaze::getLastId();

        $zprava_text       .= '<table cellspacing="0" cellpadding="0" border="0" width="100%" style="width: 100%; border-collapse: collapse; font-weight: normal;">'."\n";
        $zprava_text       .= '<tr><td colspan="2"><h3>Děkujeme za projevený zájem</h3><br></td></tr>'."\n";
        $zprava_text       .= '<tr><td colspan="2">Váš požadavek nám byla odeslán a my na něj budeme v nejbližší možné době reagovat.<br></td></tr>'."\n";
        $zprava_text       .= '<tr><td colspan="2">Uvedli jste tyto údaje:</td></tr>'."\n";
        $zprava_text       .= '<tr><td>Společnost: </td><td><strong>' . $fakt_spolecnost . '</strong></td></tr>'."\n";
        $zprava_text       .= '<tr><td>IČ: </td><td><strong>' . $fakt_ico . '</strong></td></tr>'."\n";
        $zprava_text       .= '<tr><td>Fakturační adresa: </td><td><strong>' . $fakt_adresa . '</strong></td></tr>'."\n";
        $zprava_text       .= '<tr><td>E-mail: </td><td><strong>' . $fakt_email . '</strong></td></tr>'."\n";
        $zprava_text       .= '<tr><td>Kontaktní osoba: </td><td><strong>' . $fakt_osoba . '</strong></td></tr>'."\n";
        $zprava_text       .= '<tr><td>Telefon: </td><td><strong>' . $fakt_telefon . '</strong></td></tr>'."\n";
        $zprava_text       .= '<tr><td colspan="2">Poznámka:</td></tr>'."\n";
        $zprava_text       .= '<tr><td colspan="2">' . $fakt_text . '<br></td></tr>'."\n";
        $zprava_text       .= '<tr><td colspan="2">Souhlas se zasíláním daňových dokladů ve formátu PDF a s ochranou osobních údajů GDPR: <strong>' . strtoupper($fakt_gdpr) . '</strong></td></tr>'."\n";
        $zprava_text       .= '</table>'."\n";

        $zprava_html_telo   = $this->sablonaEmailu->vypisSablonu();
        $zprava_html_telo   = str_replace("{{email_odesilatel}}", strip_tags($fakt_spolecnost), $zprava_html_telo);
        $zprava_html_telo   = str_replace("{{email_adresa}}", strip_tags($fakt_email), $zprava_html_telo);
        $zprava_html_telo   = str_replace("{{email_zprava}}", ($zprava_text), $zprava_html_telo);

        $this->potvrzeni_odeslani_mailu = $this->odesilacEmailu->odesliEmail($fakt_email, $zprava_html_telo);

        Databaze::dotaz('
            UPDATE `tbl_fakturace` SET `fakt_odeslano` = ? WHERE fakt_id = '.$fakt_posledniId.'
        ', array($this->potvrzeni_odeslani_mailu));
    }

    public function pridejFakturu()
    {
        if( (isset($_POST['fakt_email'])) && (isset($_POST['fakt_telefon'])) && ($_POST['fakturaOdeslat'] == 'odeslat') )
        {
            if ($this->captcha->over())
            {
                $this->ulozFakturu($_POST['fakt_datum'], $_POST['fakt_spolecnost'], $_POST['fakt_ico'], $_POST['fakt_adresa'], $_POST['fakt_email'], $_POST['fakt_osoba'], $_POST['fakt_telefon'], $_POST['fakt_text'], $_POST['fakt_gdpr'], $_POST['zprava_overeni'], $_POST['zprava_captcha']);
                $notifikace_zprava = '<div class="alert alert-success alert-dismissible fade show" role="alert">Zpráva byla odeslána. Děkujeme. ('.$this->potvrzeni_odeslani_mailu.') <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zavřít"></button></div>';
                $this->kontrolerZprav->pridejZpravu($notifikace_zprava);
                header('Location: https://presmerovana.adresa');
                exit;
            }
            $notifikace_zprava = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Odeslání se nepodařilo ověřit. Vyplňte správně všechny pole a zkuste to znovu, prosím.'.$potvrzeni_odeslani_mailu.' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zavřít"></button></div>';
            $this->kontrolerZprav->pridejZpravu($notifikace_zprava);
        }
        return false;
    }

    public function vypisFormular()
    {
        $fakt_spolecnost    = (isset($_POST['fakt_spolecnost']) ? $_POST['fakt_spolecnost'] : '');
        $fakt_ico           = (isset($_POST['fakt_ico']) ? $_POST['fakt_ico'] : '');
        $fakt_adresa        = (isset($_POST['fakt_adresa']) ? $_POST['fakt_adresa'] : '');
        $fakt_email         = (isset($_POST['fakt_email']) ? $_POST['fakt_email'] : '');
        $fakt_osoba         = (isset($_POST['fakt_osoba']) ? $_POST['fakt_osoba'] : '');
        $fakt_telefon       = (isset($_POST['fakt_telefon']) ? $_POST['fakt_telefon'] : '');
        $fakt_text          = (isset($_POST['fakt_text']) ? $_POST['fakt_text'] : '');

        echo('                  <form action="" method="post" name="formFakturace" id="formFakturace" class="ms-4">')."\n";
            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="fakt_spolecnost" class="col-sm-2 col-form-label">Společnost: <span class="text-danger float-end">*</span></label>')."\n";
            echo('                            <div class="col-sm-10">')."\n";
            echo('                              <input type="text" name="fakt_spolecnost" id="fakt_spolecnost" class="form-control" value="' . htmlspecialchars($fakt_spolecnost) . '" placeholder="Zadejte název společnosti" required="required">')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="fakt_ico" class="col-sm-2 col-form-label">IČ: <span class="text-danger float-end">*</span></label>')."\n";
            echo('                            <div class="col-sm-10">')."\n";
            echo('                              <input type="text" name="fakt_ico" id="fakt_ico" class="form-control" value="' . htmlspecialchars($fakt_ico) . '" placeholder="Zadejte středisko pro objednávku" required="required">')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="fakt_adresa" class="col-sm-2 col-form-label d-flex align-items-center">Fakturační adresa: <span class="text-danger float-end">*</span></label>')."\n";
            echo('                            <div class="col-sm-10 d-flex align-items-center">')."\n";
            echo('                              <input type="text" name="fakt_adresa" id="fakt_adresa" class="form-control" value="' . htmlspecialchars($fakt_adresa) . '" placeholder="Zadejte fakturační adresu" required="required">')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="fakt_email" class="col-sm-2 col-form-label">E-mail: <span class="text-danger float-end">*</span></label>')."\n";
            echo('                            <div class="col-sm-10">')."\n";
            echo('                              <input type="text" name="fakt_email" id="fakt_email" class="form-control" value="' . htmlspecialchars($fakt_email) . '" placeholder="Zadejte e-mail(y) pro zasílání faktur" required="required">')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="fakt_osoba" class="col-sm-2 col-form-label">Osoba: <span class="text-danger float-end">*</span></label>')."\n";
            echo('                            <div class="col-sm-10">')."\n";
            echo('                              <input type="text" name="fakt_osoba" id="fakt_osoba" class="form-control" value="' . htmlspecialchars($fakt_osoba) . '" placeholder="Zadejte kontaktní osobu pro fakturaci" required="required">')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="fakt_telefon" class="col-sm-2 col-form-label">Telefon: <span class="text-danger float-end">*</span></label>')."\n";
            echo('                            <div class="col-sm-10">')."\n";
            echo('                              <input type="text" name="fakt_telefon" id="fakt_telefon" class="form-control" value="' . htmlspecialchars($fakt_telefon) . '" placeholder="Zadejte telefonní kontakt pro fakturaci" required="required">')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="fakt_text" class="col-sm-2 col-form-label">Poznámka:</label>')."\n";
            echo('                            <div class="col-sm-10">')."\n";
            echo('                              <textarea name="fakt_text" id="fakt_text" class="form-control" placeholder="Zde můžete uvést doplňující informace.">' . htmlspecialchars($fakt_text) . '</textarea>')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="fakt_datum" class="col-sm-2 col-form-label" title="Datum a čas udělení souhlasu">Datum:</label>')."\n";
            echo('                            <div class="col-sm-10">')."\n";
            echo('                              <input type="datetime" name="fakt_datum" id="fakt_datum" class="form-control-plaintext" value="' . date("Y-m-d H:i:s") . '"  title="Datum a čas udělení souhlasu" readonly="readonly">')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="" class="col-sm-2 col-form-label">Souhlas:</label>')."\n";
            echo('                            <div class="col-sm-10">')."\n";
            echo('                              <div class="form-check">')."\n";
            echo('                                <input class="form-check-input" type="checkbox" value="ano" name="fakt_gdpr" id="fakt_gdpr">')."\n";
            echo('                                <label class="form-check-label" for="fakt_gdpr">')."\n";
            echo('                                  Souhlasím se zasíláním daňových dokladů ve formátu PDF, s obchodními podmínkami a GDPR.')."\n";
            echo('                                </label>')."\n";
            echo('                              </div>')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            $this->captcha->vypis();


            echo('                         <input type="hidden" name="zprava_captcha" value="" />')."\n";

            echo('                          <div class="form-group row mt-lg-5 mb-3">')."\n";
            echo('                            <div class="col-sm-6 lh-sm"><span class="text-danger fw-bold h5">*</span> - takto označené údaje jsou povinné</div>')."\n";
            echo('                            <div class="col-sm-6">')."\n";
            echo('                              <button type="submit" name="fakturaOdeslat" id="fakturaOdeslat" class="btn btn-success" value="odeslat">Odeslat požadavek</button>')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

        echo('                        </form>')."\n";

    }

    public function vypisFakturu()
    {
        $this->vypisFormular();
    }

}
