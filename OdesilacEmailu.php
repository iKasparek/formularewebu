<?php
ini_set("display_errors", 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class OdesilacEmailu
{

    public function __construct()
    {
    }

    public function odesliEmail($zprava_email, $zprava_html_telo, $zprava_kategorie = '')
    {

        $potvrzeni_odeslani_mailu = '';

        $mail = new PHPMailer(true);

        try {
            $mail->CharSet    = 'UTF-8';
            $mail->SMTPDebug  = SMTP::DEBUG_OFF;      //SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Host       = 'smtp.server';
            $mail->SMTPAuth   = true;
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];
            $mail->Username   = 'username';
            $mail->Password   = 'password';
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom('odesilaci@example.com', 'Firma a jmeno');
            $mail->addAddress($zprava_email);
            $mail->addReplyTo('preposilaci@example.com', 'Firma a jmeno');
            $mail->addCC('kopie@example.com');
            $mail->addBCC('skryta@example.com');
            if($zprava_kategorie == 'newsletter') {
                $mail->addBCC('kopie1@example.com');
            } else {
                $mail->addBCC('kopie2@example.com');
            }

            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Zpráva z webu pro FIRMA';
            $mail->Body    = $zprava_html_telo;
            $mail->AltBody = $zprava_html_telo;

            $mail->send();
            $potvrzeni_odeslani_mailu = 'Zpráva nám byla odeslána, kopie odešla na adresu '.$zprava_email;
        } catch (Exception $e) {
            $potvrzeni_odeslani_mailu = "Zprávu se nepodařilo odeslat. Výpis chyby:\n {$mail->ErrorInfo}";
        }

        return $potvrzeni_odeslani_mailu;

    }

}
