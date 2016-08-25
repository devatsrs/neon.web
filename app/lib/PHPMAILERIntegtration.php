<?php 
class PHPMAILERIntegtration{ 

	public function __construct(){
	 } 


	public static function SetEmailConfiguration($config,$companyID)
	{
		$result = Company::select('SMTPServer','SMTPUsername','CompanyName','SMTPPassword','Port','IsSSL','EmailFrom')->where("CompanyID", '=', $companyID)->first();
	
		Config::set('mail.host',$config->SMTPServer);
		Config::set('mail.port',$config->Port);
		Config::set('mail.from.address',$config->EmailFrom);
		Config::set('mail.from.name',$config->CompanyName);
		Config::set('mail.encryption',($config->IsSSL==1?'SSL':'TLS'));
		Config::set('mail.username',$config->SMTPUsername);
		Config::set('mail.password',$config->SMTPPassword);
		extract(Config::get('mail'));
	
		$mail = new PHPMailer;
		//$mail->SMTPDebug = 3;                               // Enable verbose debug output
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = $host;  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = $username;                 // SMTP username
	
		$mail->Password = $password;                           // SMTP password
		$mail->SMTPSecure = $encryption;                            // Enable TLS encryption, `ssl` also accepted
	
		$mail->Port = $port;                                    // TCP port to connect to
	
		$mail->From = $from['address'];
		$mail->FromName = $from['name'];
		$mail->isHTML(true);
	
		return $mail;		
	}	 
	
	public static function SendMail($view,$data,$config,$companyID='',$body)
	{
		if(empty($companyID)){
			 $companyID = User::get_companyID();
		}
		
		 $mail 		=   self::SetEmailConfiguration($config,$companyID);
		 $status 	= 	array('status' => 0, 'message' => 'Something wrong with sending mail.');
	
		if(getenv('APP_ENV') != 'Production'){
			$data['Subject'] = 'Test Mail '.$data['Subject'];
		}
		$mail->Body = $body;
		$mail->Subject = $data['Subject'];
		if(!is_array($data['EmailTo']) && strpos($data['EmailTo'],',') !== false){
			$data['EmailTo']  = explode(',',$data['EmailTo']);
		}
	
		if(isset($data['cc'])) {
			if (is_array($data['cc'])) {
				foreach ($data['cc'] as $cc_address) {
					$user_data = User::where(["EmailAddress" => $cc_address])->get();
					$mail->AddCC($cc_address, $user_data[0]['FirstName'] . ' ' . $user_data[0]['LastName']);
				}
			}
		}
	
		if(isset($data['cc'])) {
			if (is_array($data['bcc'])) {
				foreach ($data['bcc'] as $bcc_address) {
					$user_data = User::where(["EmailAddress" => $bcc_address])->get();
	
					$mail->AddBCC($bcc_address, $user_data[0]['FirstName'] . ' ' . $user_data[0]['LastName']);
				}
			}
		}
		if(is_array($data['EmailTo'])){
			foreach((array)$data['EmailTo'] as $email_address){
				if(!empty($email_address)) {
					$email_address = trim($email_address);
					$mail->clearAllRecipients();
					$mail->addAddress($email_address); //trim Added by Abubakar
					if (!$mail->send()) {
						$status['status'] = 0;
						$status['message'] .= $mail->ErrorInfo . ' ( Email Address: ' . $email_address . ')';
					} else {
						$status['status'] = 1;
						$status['message'] = 'Email has been sent';
						$status['body'] = $body;
					}
				}
			}
		}else{
			if(!empty($data['EmailTo'])) {
				$email_address = trim($data['EmailTo']);
				$mail->clearAllRecipients();
				$mail->addAddress($email_address); //trim Added by Abubakar
				if (!$mail->send()) {
					$status['status'] = 0;
					$status['message'] .= $mail->ErrorInfo . ' ( Email Address: ' . $data['EmailTo'] . ')';
				} else {
					$status['status'] = 1;
					$status['message'] = 'Email has been sent';
					$status['body'] = $body;
				}
			}
		}
		return $status;
	
	}
}
?>