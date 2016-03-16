<?php

class NeonAPI extends \Eloquent {

    public static function login(){
        $curl = new Curl\Curl();
        $call_method = 'login';

        $api_url = getenv('NeonApiUrl');

        $curl->post($api_url.$call_method, array(
            'EmailAddress' => Input::get('email'),
            'password' => Input::get('password'),
        ));
        $curl->close();

        $response = json_decode($curl->response);
        if(isset($response->token)){
            self::setToken($response->token);
            return true;
        }
        return false;
    }
    public static function login_by_id($id){
        $curl = new Curl\Curl();
        $call_method = 'l/'.$id;

        $api_url = getenv('NeonApiUrl');

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
        $UserID =  User::get_userID();
        User::where(array('UserID'=>$UserID))->update(array('api_token'=>$api_token));
    }
    protected static function getToken(){
        $UserID =  User::get_userID();
        $api_token = User::where(array('UserID'=>$UserID))->pluck('api_token');
        return $api_token;
    }
    public static function postrequest($call_method,$post_data=array()){
        $api_url = getenv('NeonApiUrl');
        $token = self::getToken();
        $curl = new Curl\Curl();
        $curl->setHeader('Authorization', 'Bearer '.$token);
        $curl->post($api_url.$call_method,$post_data);
        $curl->close();
        self::parse_header($curl->response_headers);

        return json_decode($curl->response);
    }
    protected static function parse_header($response_headers){
        foreach ((array)$response_headers as $response_header) {
            if (strpos($response_header, 'Bearer') !== false) {
                $new_api_token = trim(str_replace('Authorization: Bearer', '', $response_header));
                self::setToken($new_api_token);
            }
        }
    }
    public static function getrequest($call_method,$get_data=array()){
        $api_url = getenv('NeonApiUrl');
        $token = self::getToken();
        $curl = new Curl\Curl();
        $curl->setHeader('Authorization', 'Bearer '.$token);
        $curl->get($api_url.$call_method,$get_data);
        $curl->close();
        //Log::info($call_method);
        self::parse_header($curl->response_headers);
        return json_decode($curl->response);
    }


}