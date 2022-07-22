<?php
ini_set("display_errors", 1);

class CaptchaObrazek implements Captcha
{

    public function generujObrazek()
    {
        $sirka    = 130;
        $vyska    = 30;
        $obrazek  = imagecreate($sirka, $vyska);
        $bila     = imagecolorallocate($obrazek, 255, 255, 255);
        $modra    = imagecolorallocate($obrazek, 0, 0, 255);
        imagefilledrectangle($obrazek, 0, 0, $sirka, $vyska, $bila);

        $znaky    = 'abcdefghijkmnoprstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $znaku    = mb_strlen($znaky);
        $text     = '';
        $font     = __DIR__ . '/../../assets/fonts/CourierPrime-Regular.ttf';

        // pro barvy si vytvoříme pole
        $colors = array();
        for($i = 0; $i < 100; $i++) {
            $colors[] = imagecolorallocate($obrazek, rand(1,255), rand(1,255), rand(1,255));
        }

        for($i = 0; $i < 6; $i++) {
            $pismeno = $znaky[rand(0, $znaku - 1)];
            $text   .= $pismeno;
            //imagestring($obrazek, 5, 20 + $i * 15, 10, $pismeno, $modra);
            imagettftext($obrazek, 15, 5, 20 + $i * 15, 25, $modra, $font, $pismeno);
        }

        for($i = 0; $i < 60; $i++) {
            $x1 = rand(5, $sirka - 5);
            $y1 = rand(5, $vyska - 5);
            $x2 = $x1 - 4 + rand(0, 8);
            $y2 = $y1 - 4 + rand(0, 8);
            imageline($obrazek, $x1, $y1, $x2, $y2, $colors[rand(0, count($colors) - 1)]);
        }

        $_SESSION['captcha'] = $text;
        imagejpeg($obrazek);
    }

    public function vypis($vypisElementy = '')
    {
        if($vypisElementy != 'small') {
            echo('                      <div class="row">')."\n";
            echo('                        <div class="col form-group">')."\n";
        }
        echo('                          <label for="zprava_overeni" id="label_zprava_overeni"><small>Přepište text z obrázku:</small>')."\n";
        echo('                          <img src="//example.com/obrazek.php?' . time() . '"></label>');
        if($vypisElementy != 'small') {
            echo('                        </div>')."\n";
            echo('                        <div class="col form-group">')."\n";
        }
        echo('                          <input type="text" name="zprava_overeni" id="zprava_overeni" class="form-control" required="required">')."\n";
        if($vypisElementy != 'small') {
            echo('                        </div>')."\n";
            echo('                      </div>')."\n";
        }

        /*echo('                      <div class="form-group">')."\n";
        echo('                        <label for="zprava_overeni">Přepiš text z obrázku')."\n";
        echo('                        <img src="obrazek.php?' . time() . '"></label>');
        echo('                        <input type="text" name="zprava_overeni" id="zprava_overeni" class="form-control" required="required">')."\n";
        echo('                      </div>')."\n";*/

        /*echo('                                Přepiš text z obrázku: ');
        echo('<img src="obrazek.php?' . time() . '" style="margin: 1rem;">');
        echo('<input type="text" name="zprava_overeni" />');*/
    }

    public function over()
    {
        return (isset($_SESSION['captcha']) &&
            ($_POST['zprava_overeni'] == $_SESSION['captcha']));
    }

}