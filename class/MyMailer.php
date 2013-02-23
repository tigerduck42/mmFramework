<?php
require_once(DIR_FRAMEWORK . "/thirdParty/PHPMailer/class.phpmailer.php");

class MyMailer extends PHPMailer {
	
	public function __construct($exceptions = false) {
		parent::__construct($exceptions);
		
		$config = Config::getInstance();
		if($config->mailer == "smtp") {
			$this->IsSMTP();
			$this->Host = $config->mailHostName;
			if(strlen($config->mailPort)) {
				$this->Port = $config->mailPort;
			}
			if(strlen($config->mailUsername) && strlen($config->mailPassword)) {
				$this->SMTPAuth = TRUE;
			}
			else {
				$this->SMTPAuth = FALSE;
			}
			if(strlen($config->mailSecure)) {
				$this->SMTPSecure = $config->mailSecure;
			}
			$this->Username = $config->mailUsername;
			$this->Password = $config->mailPassword;	
		}
		else {
			$this->IsMail();
		}
		
		if(!is_null($config->mailSender) && self::ValidateAddress($config->mailSender)) {
			$this->Sender = $config->mailSender;
		}
		
		$this->CharSet = "utf-8";
		
	}
	
	public function AddRecipients($recipientArray) {
		if(!is_array($recipientArray)) {
			trigger_error("Recipients not defined!", E_USER_ERROR);
			return FALSE;
		}
		
		if(!isset($recipientArray['TO'])) {
			trigger_error("No TO recipients defined!", E_USER_ERROR);
			return FALSE;
		}
		
		foreach($recipientArray['TO'] as $email => $name) {
			$this->AddAddress($email, $name);
		}
		
		if(isset($recipientArray['CC'])) {
			foreach($recipientArray['CC'] as $email => $name) {
				$this->AddCC($email, $name);
			}
		}
		
		if(isset($recipientArray['BCC'])) {
			foreach($recipientArray['BCC'] as $email => $name) {
				$this->AddBCC($email, $name);
			}
		}
	}
	
	
	public function Send(){
		$success = parent::Send();
		
		if(!$success) {
			trigger_error("Mail could not be send!\n" . $this->ErrorInfo, E_USER_ERROR);
		}
		return $success;
	}
	
}

?>
