<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendMail($to,$title,$body,$from="",$filename="") {
	$mail = new PHPMailer(TRUE);
	$mail->isHTML(true);
	if($from != ""):
		$mail->setFrom($from);
	endif;
   	$mail->addAddress($to);
   	if($filename != ""):
   		$mail->addAttachment($filename);
   	endif;
   	$mail->Subject = $title;
   	$mail->Body = $body;
   	$mail->send();
}