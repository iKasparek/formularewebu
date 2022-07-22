<?php
ini_set("display_errors", 1);

class OdesilacPoptavky
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

    private function ulozPoptavku($popt_datum, $popt_firma, $popt_email, $popt_osoba, $popt_telefon, $popt_text, $popt_gdpr, $popt_overeni, $popt_captcha)
    {
        //$_SESSION['odeslanaZprava'] = '';
        $zprava_html_telo           = '';
        $notifikace_zprava          = '';
        $zprava_text                = '';
        $popt_gdpr_insert           = ($popt_gdpr == 'ano' ? 1 : 0);
        Databaze::dotaz('
            INSERT INTO `tbl_poptavky`
            (`popt_datum`, `popt_firma`, `popt_email`, `popt_osoba`, `popt_telefon`, `popt_text`, `popt_gdpr`, `popt_overeni`, `popt_captcha`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ', array($popt_datum, $popt_firma, $popt_email, $popt_osoba, $popt_telefon, $popt_text, $popt_gdpr_insert, $popt_overeni, $popt_captcha));

        $popt_posledniId    = Databaze::getLastId();

        $zprava_text       .= '<table cellspacing="0" cellpadding="0" border="0" width="100%" style="width: 100%; border-collapse: collapse; font-weight: normal;">'."\n";
        $zprava_text       .= '<tr><td colspan="2"><h3>Děkujeme za projevený zájem</h3></td></tr>'."\n";
        $zprava_text       .= '<tr><td colspan="2">Váš požadavek nám byla odeslán a my na něj budeme v nejbližší možné době reagovat.</td></tr>'."\n";
        $zprava_text       .= '<tr><td colspan="2">Uvedli jste tyto údaje:</td></tr>'."\n";
        $zprava_text       .= '<tr><td>Firma: </td><td><strong>' . $popt_firma . '</strong></td></tr>'."\n";
        $zprava_text       .= '<tr><td>Kontaktní osoba: </td><td><strong>' . $popt_osoba . '</strong></td></tr>'."\n";
        $zprava_text       .= '<tr><td>Kontaktní e-mail: </td><td><strong>' . $popt_email . '</strong></td></tr>'."\n";
        $zprava_text       .= '<tr><td>Kontaktní telefon: </td><td><strong>' . $popt_telefon . '</strong></td></tr>'."\n";
        $zprava_text       .= '<tr><td colspan="2">Poznámka:</td></tr>'."\n";
        $zprava_text       .= '<tr><td colspan="2">' . $popt_text . '<br></td></tr>'."\n";
        $zprava_text       .= '<tr><td colspan="2">Souhlas GDPR: <strong>' . strtoupper($popt_gdpr) . '</strong><br></td></tr>'."\n";
        $zprava_text       .= '</table>'."\n";

        $zprava_html_telo   = $this->sablonaEmailu->vypisSablonu();
        $zprava_html_telo   = str_replace("{{email_odesilatel}}", strip_tags($popt_firma), $zprava_html_telo);
        $zprava_html_telo   = str_replace("{{email_adresa}}", strip_tags($popt_email), $zprava_html_telo);
        $zprava_html_telo   = str_replace("{{email_zprava}}", $zprava_text, $zprava_html_telo);

        $this->potvrzeni_odeslani_mailu = $this->odesilacEmailu->odesliEmail($popt_email, $zprava_html_telo);

        Databaze::dotaz('
            UPDATE `tbl_poptavky` SET `popt_odeslano` = ? WHERE popt_id = '.$popt_posledniId.'
        ', array($this->potvrzeni_odeslani_mailu));
    }

    public function pridejPoptavku()
    {
        if( (isset($_POST['popt_email'])) && (isset($_POST['popt_telefon'])) && ($_POST['poptavkaOdeslat'] == 'odeslat') )
        {
            if ($this->captcha->over())
            {
                $this->ulozPoptavku($_POST['popt_datum'], $_POST['popt_firma'], $_POST['popt_email'], $_POST['popt_osoba'], $_POST['popt_telefon'], $_POST['popt_text'], $_POST['popt_gdpr'], $_POST['zprava_overeni'], $_POST['zprava_captcha']);
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
        $popt_firma         = (isset($_POST['popt_firma']) ? $_POST['popt_firma'] : '');
        $popt_osoba         = (isset($_POST['popt_osoba']) ? $_POST['popt_osoba'] : '');
        $popt_email         = (isset($_POST['popt_email']) ? $_POST['popt_email'] : '');
        $popt_telefon       = (isset($_POST['popt_telefon']) ? $_POST['popt_telefon'] : '');
        $popt_text          = (isset($_POST['popt_text']) ? $_POST['popt_text'] : '');

        echo('                  <form action="" method="post" name="formPoptavka" id="formPoptavka" class="ms-4">')."\n";
            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="popt_firma" class="col-sm-4 col-form-label">Společnost/firma: <span class="text-danger float-end">*</span></label>')."\n";
            echo('                            <div class="col-sm-8">')."\n";
            echo('                              <input type="text" name="popt_firma" id="popt_firma" class="form-control" value="' . htmlspecialchars($popt_firma) . '" placeholder="Zadejte název společnosti" required="required">')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="popt_osoba" class="col-sm-4 col-form-label">Kontaktní osoba: <span class="text-danger float-end">*</span></label>')."\n";
            echo('                            <div class="col-sm-8">')."\n";
            echo('                              <input type="text" name="popt_osoba" id="popt_osoba" class="form-control" value="' . htmlspecialchars($popt_osoba) . '" placeholder="Zadejte jméno a příjmení kontaktní osoby" required="required">')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="popt_email" class="col-sm-4 col-form-label">Kontaktní e-mail: <span class="text-danger float-end">*</span></label>')."\n";
            echo('                            <div class="col-sm-8">')."\n";
            echo('                              <input type="text" name="popt_email" id="popt_email" class="form-control" value="' . htmlspecialchars($popt_email) . '" placeholder="Zadejte kontaktní e-mail" required="required">')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="popt_telefon" class="col-sm-4 col-form-label">Telefon: <span class="text-danger float-end">*</span></label>')."\n";
            echo('                            <div class="col-sm-8">')."\n";
            echo('                              <input type="text" name="popt_telefon" id="popt_telefon" class="form-control" value="' . htmlspecialchars($popt_telefon) . '" placeholder="Zadejte kontaktní telefonní číslo" required="required">')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="popt_text" class="col-sm-4 col-form-label">Popis požadavku:</label>')."\n";
            echo('                            <div class="col-sm-8">')."\n";
            echo('                              <textarea name="popt_text" id="popt_text" class="form-control" placeholder="Popište prosím svůj požadavek.">' . htmlspecialchars($popt_text) . '</textarea>')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="popt_datum" class="col-sm-4 col-form-label" title="Datum a čas udělení souhlasu">Datum:</label>')."\n";
            echo('                            <div class="col-sm-8">')."\n";
            echo('                              <input type="datetime" name="popt_datum" id="popt_datum" class="form-control-plaintext" value="' . date("Y-m-d H:i:s") . '"  title="Datum a čas udělení souhlasu" readonly="readonly">')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            echo('                          <div class="form-group row mb-3">')."\n";
            echo('                            <label for="" class="col-sm-4 col-form-label">Souhlas:</label>')."\n";
            echo('                            <div class="col-sm-8">')."\n";
            echo('                              <div class="form-check">')."\n";
            echo('                                <input class="form-check-input" type="checkbox" value="ano" name="popt_gdpr" id="popt_gdpr">')."\n";
            echo('                                <label class="form-check-label" for="popt_gdpr">')."\n";
            echo('                                  Souhlasím sochranou osobních údajů GDPR.')."\n";
            echo('                                </label>')."\n";
            echo('                              </div>')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

            $this->captcha->vypis();

            echo('                         <input type="hidden" name="zprava_captcha" value="" />')."\n";

            echo('                          <div class="form-group row mt-lg-5 mb-3">')."\n";
            echo('                            <div class="col-sm-6 lh-sm"><span class="text-danger fw-bold h5">*</span> - <small>takto označené údaje jsou povinné</small></div>')."\n";
            echo('                            <div class="col-sm-6">')."\n";
            echo('                              <button type="submit" name="poptavkaOdeslat" id="poptavkaOdeslat" class="btn btn-success" value="odeslat">Odeslat požadavek</button>')."\n";
            echo('                            </div>')."\n";
            echo('                          </div>')."\n";

        echo('                        </form>')."\n";

    }

    public function vypisPoptavku()
    {
        $this->vypisFormular();
    }

}
