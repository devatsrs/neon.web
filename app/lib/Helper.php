<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class Helper{

    public static function getFormValue($Key) {
        if (empty(Input::old($Key))) {
            return  !empty($_REQUEST[$Key]) ? $_REQUEST[$Key] : "";
        }
        return Input::old($Key);
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


    public static function sendMail($view,$data,$ViewType=1){
        $companyID = $data['CompanyID'];
        if($ViewType){
            $body 	=  html_entity_decode(View::make($view,compact('data'))->render());
        }
        else{
            $body  = $view;
        }
        Log::info('Email Body.' . $body);

        if(SiteIntegration::CheckCategoryConfiguration(false,SiteIntegration::$EmailSlug,$companyID)){
            $status = 	 SiteIntegration::SendMail($view,$data,$companyID,$body);
        }
        else
        {
            $config = Company::select('SMTPServer','SMTPUsername','CompanyName','SMTPPassword','Port','IsSSL','EmailFrom')->where("CompanyID", '=', $companyID)->first();


            //If configuration not set then use parent company's configuration
            if($config != false && empty($config->SMTPUsername) && Reseller::IsResellerByCompanyID($companyID) > 0){
                $ParentCompanyID = Reseller::getCompanyIDByChildCompanyID($companyID);
                $config = Company::select('SMTPServer', 'SMTPUsername', 'CompanyName', 'SMTPPassword', 'Port', 'IsSSL', 'EmailFrom')->where("CompanyID", '=', $ParentCompanyID)->first();
            }

            if($config = !false) $config->SMTPPassword = Crypt::decrypt($config->SMTPPassword);
            $status = 	 PHPMAILERIntegtration::SendMail($view,$data,$config,$companyID,$body);
        }


        return $status;
    }
}