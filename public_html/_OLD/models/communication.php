<?php
include_once 'includes/database.php';

class Communication
	{
    	function mailme($sendto,$sendername,$from,$subject,$sendmailbody,$bcc="")	{
			global $SERVER_NAME;
			
			$subject = nl2br($subject);
			$sendmailbody = nl2br($sendmailbody);
			$sendto = $sendto;
			if($bcc!="")
			{
				$headers = "Bcc: ".$bcc."\n";
			}
			$headers = "MIME-Version: 1.0\n";
			$headers .= "Content-type: text/html; charset=utf-8 \n";
			$headers .= "X-Priority: 3\n";
			$headers .= "X-MSMail-Priority: Normal\n";
			$headers .= "X-Mailer: PHP/"."MIME-Version: 1.0\n";
			$headers .= "From: " . $from . "\n";
			$headers .= "Content-Type: text/html\n";
			mail("$sendto","$subject","$sendmailbody","$headers");
		}
    }
?>