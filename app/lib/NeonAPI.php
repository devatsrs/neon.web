<?php

class NeonAPI{
    protected static $api_url = '';

    public function __construct() {
        self::$api_url = CompanyConfiguration::get('NEON_API_URL').'/';
    }

    public static function login($type = "user"){
        self::$api_url = CompanyConfiguration::get('NEON_API_URL').'/';
        $curl = new Curl\Curl();
        $call_method = 'login';
        $curl->post(self::$api_url.$call_method, array(
            'LoggedEmailAddress' => Input::get('email'),
            'password' => Input::get('password'),
			'LicenceKey' =>  getenv('LICENCE_KEY'),
            'CompanyName'=>getenv('COMPANY_NAME'),
			'LoginType' =>$type
	    ));
        $curl->close();
        $response = json_decode($curl->response);  
        if(isset($response->token)){
            self::setToken($response->token);
            return true;
        }
        return false;
    }

	
	public static function logout()
	{
		NeonAPI::request('logout',[]);		 
	}

    public static function login_by_id($id,$type = 'user'){
        $curl = new Curl\Curl();
        $call_method = 'l/'.$id;

        self::$api_url = CompanyConfiguration::get('NEON_API_URL').'/';
        $request = array(
            'LoggedUserID' => $id,
            "LicenceKey" =>  getenv('LICENCE_KEY'),
            'CompanyName'=>getenv('COMPANY_NAME'),
            'LoginType' => $type
        );
        $curl->post(self::$api_url.$call_method, $request );

        Log::info("request");
        Log::info($request);
        Log::info("api_url:".self::$api_url.$call_method);

        $response = json_decode($curl->response);
        Log::info("Response");
        Log::info(print_r($response,true));
        if(isset($response->token)){
            self::setToken($response->token);
            return true;
        }else{
            Log::info("-----Not Loggedin on API-----");
            Log::info($request);
            Log::info(print_r($response,true));
            Log::info("_ENV");
            Log::info($_ENV);
            Log::info("findEnvironmentVariable");
        }
        return false;

    }
    protected static function setToken($api_token){
        Session::set("api_token", $api_token );

    }
    protected static function getToken(){
        $api_token = Session::get("api_token",'');
        return $api_token;
    }
    public static function request($call_method,  $post_data=array(),$post=true,$is_array=false,$is_upload=false){
        self::$api_url = CompanyConfiguration::get('NEON_API_URL').'/';
		$customer 	= Session::get('customer'); //customer check
        $token = self::getToken();
        $curl = new Curl\Curl();

        $curl->setHeader('Authorization', 'Bearer '.$token);
        if($is_upload) {
            //$curl->setOpt(CURLOPT_RETURNTRANSFER, true);
            $curl->setOpt(CURLOPT_POSTFIELDS, true);
            $curl->setOpt(CURLOPT_RETURNTRANSFER,true);
            $curl->setOpt(CURLOPT_POST,true);
        }
        $post_data['LicenceKey'] = getenv('LICENCE_KEY');
        $post_data['CompanyName']= getenv('COMPANY_NAME');
		$post_data['LoginType']= 'user';	 //default user
		
		
		$customer 	= Session::get('customer'); //customer check
    	if($customer==1) {
			$post_data['LoginType']= 'customer';	
		}
		
		\Illuminate\Support\Facades\Log::info(self::$api_url . $call_method);
        if($post === 'delete') {
            $curl->delete(self::$api_url . $call_method, $post_data);
        }else if($post === 'put') {
            $curl->put(self::$api_url . $call_method, $post_data);
        }else if($post) {
            $curl->post(self::$api_url . $call_method, $post_data);
        }else{
            $curl->get(self::$api_url.$call_method,$post_data);
        }

        $curl->close();
        self::parse_header($curl->response_headers);
        $response = self::makeResponse($curl,$is_array);
        return $response;
    }
    protected static function parse_header($response_headers){
        foreach ((array)$response_headers as $response_header) {
            if (strpos($response_header, 'Bearer') !== false) {
                $new_api_token = trim(str_replace('Authorization: Bearer', '', $response_header));
                self::setToken($new_api_token);
            }
        }
    }

    protected  static function makeResponse($curl,$is_array){
        $response = json_decode($curl->response,$is_array);
        if($curl->http_status_code!=200){
			$array = json_decode(json_encode($curl), true);			
		    $response = self::errorResponse($is_array,$curl->http_status_code);
        }
        return $response;
    }

    protected static function errorResponse($is_array,$Code){
        if($is_array){
            $response['status'] = 'failed';
            $response['message'] = ["error" => [cus_lang("HTTP_STATUS_500_MSG")]];
            $response['Code'] =$Code;
        }else{
            $response = new stdClass;
            $response->status = 'failed';
            $response->message = ["error" => [cus_lang("HTTP_STATUS_500_MSG")]];
            $response->Code =$Code;
        }
        return $response;
    }

    public static function curl_File($files){
        $postfields=[];
        foreach ($files as $file) {
            $f = new Symfony\Component\HttpFoundation\File\File($file->getRealPath());
            $mime = $f->getMimeType();
            $postfields[] = new CURLFile($file->getRealPath(),$mime,$file->getClientOriginalName());
        }
        return $postfields;
    }

    public static function base64byte($files){
        $files_array = [];
        foreach ($files as $file){
            $filePath = $file->getRealPath();
            $handle    = fopen($filePath, "r");
            $data      = fread($handle, filesize($filePath));
            $files_array[] = array(
                'fileExtension'=>$file->getClientOriginalExtension(),
                'fileName'=>$file->getClientOriginalName(),
                'file' => base64_encode($data)
            );
        }
        return $files_array;
    }

    public static function UploadFileLocal($data){
        $filesArray = [];
        $uploadedFile = [];
        $files = Input::file('fileattachments');
        $attachmentsinfo = $data['attachmentsinfo'];
        if(!empty($attachmentsinfo)){
            $filesArray = json_decode($attachmentsinfo,true);
        }
        foreach ($files as $file){
            $upload_path = CompanyConfiguration::get('TEMP_PATH');
            $file_name_without_ext = GUID::generate();
            $file_name = $file_name_without_ext . '.' . $file->getClientOriginalExtension();
            $file->move($upload_path, $file_name);
            $uploadedFile[]	=	 array ("filename"=>$file->getClientOriginalName(),"filepath"=>$upload_path . '/' . $file_name);
        }
        if(!empty($filesArray) && count($filesArray)>0) {
            $filesArray	=	array_merge($filesArray,$uploadedFile);
        } else {
            $filesArray	=	$uploadedFile;
        }
        return $filesArray;
    }

    public static function RegisterApiLogin($data){
        try{
            /*if(!Auth::attempt(array('EmailAddress' => $data['EmailAddress'], 'password' => $data['password'] ,'Status'=> 1 ))){
                return false;
            }*/
            $user = User::where(['EmailAddress'=>$data['EmailAddress'],'Status'=>1])->first();
            if(!empty($user)){
                if (User::checkPassword($data["password"],$user->password)) {
                    $ReturnData=array();

                    $ReturnData['UserID']=$user->UserID;
                    $ReturnData['CompanyID']=$user->CompanyID;
                    $ReturnData['FirstName']=$user->FirstName;
                    $ReturnData['LastName']=$user->LastName;
                    $ReturnData['EmailAddress']=$user->EmailAddress;
                    $ReturnData['Roles']=$user->Roles;
                    $ReturnData['AccountingUser']=$user->Roles;

                    Log::info(print_r($user,true));
                    User::find($user->UserID)->update(['LastLoginDate' => date('Y-m-d H:i:s')]);
                    return $ReturnData;
                }else{
                    return false;
                }
            }

        }catch(Exception $e){
            Log::info("RegisterApiLogin ".$e->getMessage());
            return false;
        }


    }

    public static function getLicenceResponse($request){
        $license  = $request->only('LicenceKey','CompanyName');
        $license['LicenceHost'] = $request->getHttpHost();
        $license['LicenceIP'] = $request->getClientIp();
        $licenseCacheKey = 'LicenceApiResponse' . $license['LicenceKey'];
        Log::info("getLicenceResponse");
        Log::info($license);
        if (!Cache::has($licenseCacheKey)) {
            $LicenceApiResponse = Company::ValidateApiLicenceKey($license);
            if (!empty($LicenceApiResponse)) {
                if ($LicenceApiResponse['Status'] != 1) {
                    return $LicenceApiResponse;
                }
                Cache::forever($licenseCacheKey, $LicenceApiResponse);
            } else {
                $LicenceApiResponse['Status'] = 0;
                $LicenceApiResponse['Message'] = 'Some thing wrong with license';
            }
        }else{
            $LicenceApiResponse = Cache::get($licenseCacheKey);
        }
        return $LicenceApiResponse;
    }


}