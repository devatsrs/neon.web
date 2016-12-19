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
		$mail->CharSet = 'UTF-8';
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
		$mail->clearAllRecipients();
		
		$mail =  self::add_email_address($mail,$data,'EmailTo');
		$mail =  self::add_email_address($mail,$data,'cc');
		$mail =  self::add_email_address($mail,$data,'bcc');
		
		if(SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$imapSlug))
		{
			$ImapData =  SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$imapSlug);
			
			$mail->AddReplyTo($ImapData->EmailTrackingEmail, $config->CompanyName);
		}
		
		if(isset($data['AttachmentPaths']) && count($data['AttachmentPaths'])>0) {
        foreach($data['AttachmentPaths'] as $attachment_data) { 
            $file = \Nathanmac\GUID\Facades\GUID::generate()."_". basename($attachment_data['filepath']); 
            $Attachmenturl = AmazonS3::unSignedUrl($attachment_data['filepath']);
            file_put_contents($file,file_get_contents($Attachmenturl));
            $mail->AddAttachment($file,$attachment_data['filename']);
        }
    } 
		
		$emailto = is_array($data['EmailTo'])?implode(",",$data['EmailTo']):$data['EmailTo'];
		if (!$mail->send()) {
					$status['status'] = 0;
					$status['message'] .= $mail->ErrorInfo . ' ( Email Address: ' . $emailto . ')';
		} else {
					$mail->clearAllRecipients();
					$status['status'] = 1;
					$status['message'] = 'Email has been sent';
					$status['body'] = $body;
					$status['message_id']	=	$mail->getLastMessageID(); 
		}
		
		
		return $status;
	
	}
	
	static function add_email_address($mail,$data,$type='EmailTo') //type add,bcc,cc
	{
		if(isset($data[$type]))
		{
			if(!is_array($data[$type])){
				$email_addresses = explode(",",$data[$type]);
			}
			else{
				$email_addresses = $data[$type];
			}
	
			if(count($email_addresses)>0){
				foreach($email_addresses as $email_address){
					if($type='EmailTo'){
						$mail->addAddress(trim($email_address));
					}
					if($type='cc'){
						$mail->AddCC(trim($email_address));
					}
					if($type='bcc'){
						$mail->AddBCC(trim($email_address));
					}
				}
			}
		}
		return $mail;
	}
}
?>