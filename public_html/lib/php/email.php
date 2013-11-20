<?
function email($from, $fromEmail, $to, $toEmail, $subject, $htmlBody, $plainBody = '', $ccEmail = '', $bccEmail = '', $replyToEmail = '', $attachments = array()){
	
	$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
	
	#$mail->IsSendmail(); // telling the class to use SendMail transport
	$mail->IsMail();

    $logger = new Jk_Logger(APP_PATH . 'logs/mail.log');

	try {
		
		// Set To
		$mail->AddAddress($toEmail, $to);
		
		// Set From
		$mail->SetFrom($fromEmail, $from);
		
		// Set CC
		$mail->ClearCCs();
		if ($ccEmail != '') {
			$mail->AddCC($ccEmail);
		}
		
		// Set Bcc
		$mail->ClearBCCs();
		if ($bccEmail != '') {
			$mail->AddBCC($bccEmail);
		}
		
		// Set Reply To
		if ($replyToEmail != '') {
			$mail->ClearReplyTos();
			$mail->AddReplyTo($replyToEmail);
		}
		
		// Set Subject
		$mail->Subject = $subject;
		
		// Set Text Body
		$mail->AltBody = $plainBody != '' ? $plainBody : '';// : 'To view the message, please use an HTML compatible email viewer.'; // optional - MsgHTML will create an alternate automatically
		
		// Set HTML Body
		$mail->MsgHTML($htmlBody);
		
		// Set Attachments
		if (!empty($attachments)) {
			foreach ($attachments as $attachment) {
				if (!is_file($attachment['path'])) {
					continue;
				}
				
				if (isset($attachment['type']) && $attachment['type'] != '') {
					$mail->AddAttachment($attachment['path'], $attachment['filename'], 'base64', $attachment['type']);
				}
				else {
					$mail->AddAttachment($attachment['path'], $attachment['filename']);
				}
			}
		}


        $mail->IsSMTP();
        $mail->SMTPAuth   = true; // enable SMTP authentication
        $mail->Port       = 587;   // set the SMTP server port
        $mail->Host       = "smtp.mandrillapp.com"; // SMTP server
        $mail->Username   = "admin@offlineinc.net";     // SMTP server username
        $mail->Password   = "pDcuwfUPxKb7lEoEjX1ybw";


		// Send Email
		$mail->Send();


		//echo "Message Sent OK</p>\n";
		return array('sent' => true, 'error' => NULL);
		//return true;
	} 
	catch (phpmailerException $e) {
		$errorMsg = $e->errorMessage();
        $logger->LogInfo("phpmailerException: message:" . $e->errorMessage());
	  	return array('sent' => false, 'error' => $errorMsg);
	  	//return false;
		//echo $e->errorMessage(); //Pretty error messages from PHPMailer
	
	} 
	catch (Exception $e) {
		$errorMsg = $e->getMessage();
        $logger->LogInfo("Mail Exception: message:" . $e->errorMessage());
		return array('sent' => false, 'error' => $errorMsg);
	  	//return false;
		//echo $e->getMessage(); //Boring error messages from anything else!
	
	}
}
?>
