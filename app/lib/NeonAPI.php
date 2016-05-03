<?php

class NeonAPI{
    protected static $api_url = '';

    public function __construct() {
        self::$api_url = getenv('Neon_API_URL');
    }
    public static function login(){
        self::$api_url = getenv('Neon_API_URL');
        $curl = new Curl\Curl();
        $call_method = 'login';
        $curl->post(self::$api_url.$call_method, array(
            'EmailAddress' => Input::get('email'),
            'password' => Input::get('password'),
			"LicenceHost" =>$_SERVER['HTTP_HOST'],
			"LicenceIP" => $_SERVER['SERVER_ADDR'],
			"LicenceKey" =>  getenv('LICENCE_KEY'),

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
	
   public static function login_by_id($id){
        $curl = new Curl\Curl();
        $call_method = 'l/'.$id;

        $api_url = getenv('Neon_API_URL');

        $curl->get($api_url.$call_method, array());
        $curl->close();

        $response = json_decode($curl->response);
        if(isset($response->token)){
            self::setToken($response->token);
            return true;
        }
        return false;

    }
    protected static function setToken($api_token){
        /*$UserID =  User::get_userID();
        User::where(array('UserID'=>$UserID))->update(array('api_token'=>$api_token));*/
        Session::set("api_token", $api_token );

    }
    protected static function getToken(){
/*        $UserID =  User::get_userID();
        $api_token = User::where(array('UserID'=>$UserID))->pluck('api_token');*/
        $api_token = Session::get("api_token",'');
        return $api_token;
    }
    public static function request($call_method,$post_data=array(),$post=true,$is_array=false,$is_upload=false){		
        self::$api_url = getenv('Neon_API_URL');
        $token = self::getToken();
        $curl = new Curl\Curl();

        $curl->setHeader('Authorization', 'Bearer '.$token);
        if($is_upload) {
            //$curl->setOpt(CURLOPT_RETURNTRANSFER, true);
            $curl->setOpt(CURLOPT_POSTFIELDS, true);
            $curl->setOpt(CURLOPT_RETURNTRANSFER,true);
            $curl->setOpt(CURLOPT_POST,true);
        }
        if($post) {
            $curl->post(self::$api_url . $call_method, $post_data);
        }else{
            $curl->get(self::$api_url.$call_method,$post_data);
        }

        $curl->close();
        self::parse_header($curl->response_headers);		
			Log::info($curl->response);
        return json_decode($curl->response,$is_array);
    }
    protected static function parse_header($response_headers){
        foreach ((array)$response_headers as $response_header) {
            if (strpos($response_header, 'Bearer') !== false) {
                $new_api_token = trim(str_replace('Authorization: Bearer', '', $response_header));
                self::setToken($new_api_token);
            }
        }
    }

    public static function curl_File($files){
        $postfields=[];
        foreach ($files as $file) {
            $f = new Symfony\Component\HttpFoundation\File\File($file->getRealPath());
            $mime = $f->getMimeType();
            $postfields['image'] = new CURLFile($file->getRealPath(),$mime,$file->getClientOriginalName());
            Log::info($file->getRealPath());
        }
        return $postfields;
    }

    public static function base64byte($files){
        $files_array = [];
        foreach ($files as $file){
            $filename = $file->getRealPath();
            $f = new Symfony\Component\HttpFoundation\File\File($file->getRealPath());
            $handle    = fopen($filename, "r");
            $data      = fread($handle, filesize($filename));
            $files_array[] = array(
                'mimeType'=>$f->getMimeType(),
                'fileExtension'=>$file->getClientOriginalExtension(),
                'fileName'=>$file->getClientOriginalName(),
                'file' => base64_encode($data)
            );
        }
        return $files_array;
    }
}