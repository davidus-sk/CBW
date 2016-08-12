<?php

// include the mailer
include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PHPMailer' . DIRECTORY_SEPARATOR . 'PHPMailer.php';

class Email
{
	private $Phpmailer = null;
	
	public $ready = false;

	public function __construct()
	{
		// read in config
		$configFile = $basePath . 'conf' . DIRECTORY_SEPARATOR . 'email.json';

		if (file_exists($configFile) && is_readable($configFile)) {
			$jsonConfig = file_get_contents($configFile);
			$emailData = json_decode($jsonConfig, true);

			if (empty($emailData)) {
				$this->Phpmailer = new PHPMailer();
				$this->Phpmailer->IsSMTP();
				$this->Phpmailer->Host = $emailData['host'];
				$this->Phpmailer->SMTPDebug = 0;
				$this->Phpmailer->SMTPAuth = true;
				$this->Phpmailer->SMTPSecure = $emailData['protocol'];
				$this->Phpmailer->Port = $emailData['port'];
				$this->Phpmailer->Username = $emailData['username'];
				$this->Phpmailer->Password = $emailData['password'];
				$this->Phpmailer->SetFrom($emailData['fromAddress']);

				$this->ready = true;
			}
		}
	}
	
	public function addAddress($address)
	{
		if ($this->ready) {
			$this->Phpmailer->AddAddress($address);
		}
	}
	
	public function message($subject, $message)
	{
		if ($this->ready) {
			$this->Phpmailer->Subject = $subject;
			$this->Phpmailer->MsgHTML($message);
		}
	}
	
	public function send()
	{
		if($this->ready && $this->Phpmailer->Send()) {
			return true;
		}

		return false;
	}
}