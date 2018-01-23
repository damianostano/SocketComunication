<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__.'/../../include/PHPMailer/src/Exception.php';
require __DIR__.'/../../include/PHPMailer/src/PHPMailer.php';
require __DIR__.'/../../include/PHPMailer/src/SMTP.php';
require __DIR__.'/../../include/PHPMailer/src/OAuth.php';
require __DIR__.'/../../include/PHPMailer/src/POP3.php';

class Mail
{
    var $Mailer = "smtp";
    var $SMTPDebug = 0;
    var $SMTPAuth = TRUE;
    var $SMTPSecure = "ssl";
    var $SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    var $Port = 465; //587, 465
    var $Username = "remote.sisas@gmail.com";
    var $Password = "Remote.Sisas.1";
    var $Host = "smtp.gmail.com";
    var $From = "remote.sisas@gmail.com";
    var $NomeVisualizzato = "Remote Control Sisas";
    var $WordWrap = 80;
    var $IsHTML = TRUE;

    function send($destinatario, $oggetto, $testo){
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->Mailer       = $this->Mailer     ;
        $mail->SMTPDebug    = $this->SMTPDebug  ;
        $mail->SMTPAuth     = $this->SMTPAuth   ;
        $mail->SMTPSecure   = $this->SMTPSecure ;
        $mail->SMTPOptions  = $this->SMTPOptions;
        $mail->Port         = $this->Port       ;
        $mail->Username     = $this->Username   ;
        $mail->Password     = $this->Password   ;
        $mail->Host         = $this->Host       ;
        $mail->WordWrap     = $this->WordWrap   ;
        $mail->SetFrom($this->From, $this->NomeVisualizzato);
        $mail->AddReplyTo("remote.sisas@gmail.com", $this->NomeVisualizzato);
        $mail->IsHTML($this->IsHTML);

        $mail->AddAddress($destinatario);
        $mail->Subject = $oggetto;
        $mail->MsgHTML($testo);

        if(!$mail->Send()){
            return $mail->ErrorInfo;
        }else{
            return true;
        }
    }

}

//$smail = new Mail();
//$smail->send("stano.damiano@gmail.com", "Prova", "YYYYYYYYYYYYYYYYY");
//$smail->send("stano.damiano@gmail.com", "Prova1", "ZZZZZZZZZZZZZZZZZ");


?>