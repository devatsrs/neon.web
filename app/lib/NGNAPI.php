<?php

class NGNAPI extends \Eloquent {
    protected static $api_url = '';

    public function __construct() {
        self::$api_url = getenv('NGN_API_URL');
    }



    /* ---------------- old *---------------------------*/

    public static function request($call_method,$post_data=array(),$post=true){
        self::$api_url = getenv('NGN_API_URL');
        $token = self::getToken();
        $curl = new Curl\Curl();
        $curl->setHeader('Authorization', 'Bearer '.$token);
        if($post) {
            $curl->post(self::$api_url . $call_method, $post_data);
        }else{
            $curl->get(self::$api_url.$call_method,$post_data);
        }

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

    /** Encode file data to base64_encode
     * @param $files
     * @return array
     */
    public static function convert_files_to_base64byte($files){
        $files_array = [];
        foreach ($files as $file) {
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