<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class Helper{

    public static function sendMail($view,$data){




        $status = array('status' => 0, 'message' => 'Something wrong with sending mail.');
        $mandrill =0;
        if(isset($data['mandrill']) && $data['mandrill'] ==1){
            $mandrill = 1;
        }
        $mail = Helper::setMailConfig($data['CompanyID'],$mandrill);
        $mail->isHTML(true);
        if(isset($data['isHTML']) && $data['isHTML'] == 'false'){
            $mail->isHTML(false);
        }
        $body = htmlspecialchars_decode(View::make($view,compact('data'))->render());
        if(!is_array($data['EmailTo']) && strpos($data['EmailTo'],',') !== false){
            $data['EmailTo']  = explode(',',$data['EmailTo']);
        }
        if(is_array($data['EmailTo'])){
            foreach((array)$data['EmailTo'] as $email_address){
                $mail->addAddress(trim($email_address));
            }
        }else{
            $mail->addAddress(trim($data['EmailTo']),$data['EmailToName']);
        }
        if(isset($data['attach'])){
            $mail->addAttachment($data['attach']);
        }
        if(isset($data['EmailFrom'])){
            $mail->From = $data['EmailFrom'];
            if(isset($data['EmailFromName'])){
                $mail->FromName = $data['EmailFromName'];
            }
        }
        if(getenv('APP_ENV') != 'Production'){
            $data['Subject'] = 'Test Mail '.getenv('RMArtisanFileLocation').' '.$data['Subject'];
        }
        $mail->Body = $body;
        $mail->Subject = $data['Subject'];
        if (!$mail->send()) {
            $status['status'] = 0;
            $status['message'] .= $mail->ErrorInfo;
            $status['body'] = '';
            return $status;
        } else {
            $status['status'] = 1;
            $status['message'] = 'Email has been sent';
            $status['body'] = $body;
            return $status;
        }
    }
    public static function setMailConfig($CompanyID,$mandrill){
        $result = Company::select('SMTPServer','SMTPUsername','CompanyName','SMTPPassword','Port','IsSSL','EmailFrom')->where("CompanyID", '=', $CompanyID)->first();
        if($mandrill == 1) { 
			$ManrdilDbData   	 = 	IntegrationConfiguration::where(array('CompanyId'=>$CompanyID,"IntegrationID"=>10))->first();
			$ManrdilSettings     = 	isset($ManrdilDbData->Settings)?json_decode($ManrdilDbData->Settings):"";
			
            Config::set('mail.host', $ManrdilSettings->MandrilSmtpServer);
            Config::set('mail.port',  $ManrdilSettings->MandrilPort);
            Config::set('mail.from.address', $result->EmailFrom);
            Config::set('mail.from.name', $result->CompanyName);
            Config::set('mail.encryption', ( $ManrdilSettings->MandrilSmtpServer == 1 ? 'SSL' : 'TLS'));
            Config::set('mail.username',  $ManrdilSettings->MandrilUserName);
            Config::set('mail.password',  $ManrdilSettings->MandrilPassword);
        }else{
            Config::set('mail.host', $result->SMTPServer);
            Config::set('mail.port', $result->Port);
            Config::set('mail.from.address', $result->EmailFrom);
            Config::set('mail.from.name', $result->CompanyName);
            Config::set('mail.encryption', ($result->IsSSL == 1 ? 'SSL' : 'TLS'));
            Config::set('mail.username', $result->SMTPUsername);
            Config::set('mail.password', $result->SMTPPassword);
        }
        extract(Config::get('mail'));

        $mail = new \PHPMailer();
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
        return $mail;

    }

    public static function FileSizeConvert($bytes)
    {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach($arBytes as $arItem)
        {
            if($bytes >= $arItem["VALUE"])
            {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

    /**  Array to CSV conversion
    /*
    Array
    (
        [0] => Array
        (
            [AreaPrefix] => 1
            [Country] => USA
            [Description] => USA-Fixed-Others
            [NoOfCalls] => 6
            [Duration] => 12:34
            [BillDuration] => 12:34
            [TotalCharges] => .24650
            [Trunk] => Other
        )
     )
     */
    static function array_to_csv($array = array()){
        $output = "";
        if(count($array)) {
            $keys = array_keys($array[0]);
            $output .= implode(",", $keys) . PHP_EOL;
            foreach ($array as $key => $row) {
                $values = array_values($row);
                if (count($values)) {
                    $output .= implode(",", $values) . PHP_EOL;
                }

            }
        }
        return $output;




    }

    static function email_log($data){
        $status = array('status' => 0, 'message' => 'Something wrong with Saving log.');
        if(!isset($data['User']) && empty($data['User'])){
            $status['message'] = 'User object not set in Account mail log';
            return $status;
        }
        if(!isset($data['EmailFrom']) && empty($data['EmailFrom'])){
            $status['message'] = 'Email From not set in Account mail log';
            return $status;
        }
        if(!isset($data['Subject']) && empty($data['Subject'])){
            $status['message'] = 'Subject not set in Account mail log';
            return $status;
        }
        if(!isset($data['Message']) && empty($data['Message'])){
            $status['message'] = 'Message not set in Account mail log';
            return $status;
        }
        if(!isset($data['ProcessID']) && empty($data['ProcessID'])){
            $status['message'] = 'ProcessID not set in Account mail log';
            return $status;
        }
        if(!isset($data['JobID']) && empty($data['JobID'])){
            $status['message'] = 'JobID not set in Account mail log';
            return $status;
        }
        $user = $data['User'];
        if(is_array($data['EmailTo'])){
            $data['EmailTo'] = implode(',',$data['EmailTo']);
        }
        $logData = ['EmailFrom'=>$data['EmailFrom'],
                    'EmailTo'=>$data['EmailTo'],
                    'Subject'=>$data['Subject'],
                    'Message'=>$data['Message'],
                    'AccountID'=>$data['AccountID'],
                    'CompanyID'=>$user->CompanyID,
                    'ProcessID'=>$data['ProcessID'],
                    'JobID'=>$data['JobID'],
                    'UserID'=>$user->UserID,
                    'CreatedBy'=>$user->FirstName.' '.$user->LastName];
        try {
            if (AccountEmailLog::Create($logData)) {
                $status['status'] = 1;
            }
        } catch (\Exception $e) {
            $status['status'] = 0;
        }
        return $status;
    }
    public static function EmailsendCDRFileReProcessed($CompanyID,$ErrorEmail,$JobTitle,$renamefilenames){
        $ComanyName = Company::getName($CompanyID);
        $emaildata['CompanyID'] = $CompanyID;
        $emaildata['CompanyName'] = $ComanyName;
        $emaildata['EmailTo'] = $ErrorEmail;
        $emaildata['EmailToName'] = '';
        $emaildata['Subject'] = 'CronJob has ReProcessed CDR Files';
        $emaildata['JobTitle'] = $JobTitle;
        $emaildata['Message'] = 'Please check this files are reprocess <br>'.implode('<br>',$renamefilenames);
        Log::info(' rename files');
        Log::info($renamefilenames);
        $result = Helper::sendMail('emails.cronjoberroremail', $emaildata);
    }
    public static function errorFiles($CompanyID,$ErrorEmail,$JobTitle,$errorfilenames){
        $ComanyName = Company::getName($CompanyID);
        $emaildata['CompanyID'] = $CompanyID;
        $emaildata['CompanyName'] = $ComanyName;
        $emaildata['EmailTo'] = $ErrorEmail;
        $emaildata['EmailToName'] = '';
        $emaildata['Subject'] = 'CronJob File Has Errors while Reading';
        $emaildata['JobTitle'] = $JobTitle;
        $emaildata['Message'] = 'Please check this file has error <br>'.$errorfilenames;
        Log::info(' error files');
        Log::info($errorfilenames);
        $result = Helper::sendMail('emails.cronjoberroremail', $emaildata);
    }

}