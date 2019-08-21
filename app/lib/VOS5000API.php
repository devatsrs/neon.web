<?php

class VOS5000API{
    protected static $api_url = '';

    public function __construct() {
        self::$api_url = CompanyConfiguration::get('NEON_API_URL').'/';
    }


    protected static function setToken($api_token){
        Session::set("api_token", $api_token );

    }
    protected static function getToken(){
        $api_token = Session::get("api_token",'');
        return $api_token;
    }
    public static function request($call_method,$CompanyGatewayID,$CompanyGatewayTitle,  $post_data=array(),$post=true,$is_array=false){

        self::$api_url = CompanyGateway::getSettingFieldByCompanyGateWayID('APIURL',$CompanyGatewayID);

        //self::$api_url = "http://89.187.80.57:1527";

        if(!empty(self::$api_url)){
            self::$api_url.="/external/server/";

            $token = self::getToken();
            $curl = new Curl\Curl();

            //$curl->setHeader('Authorization', 'Bearer '.$token);

            /*$post_data['LicenceKey'] = getenv('LICENCE_KEY');
            $post_data['CompanyName']= getenv('COMPANY_NAME');
            $post_data['LoginType']= 'user';	 //default user*/

            $post_data=json_encode($post_data);

            //$post_data='{"accounts":["IC-TEST"]}';

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

            //self::parse_header($curl->response_headers);
            $response = self::makeResponse($curl,$is_array);
            return $response;


        }else{
            return Response::json(['status'=>'failed','message'=>'API URL Not Found On '.$CompanyGatewayTitle.' Gateway']);

        }

       // return $response;
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



}