<?php
/**
 * Created by PhpStorm.
 * User: bhavin
 * Date: 15/08/2016
 * Time: 15:00 PM
 */


class NeonCookie
{
    /** Retrieve a cookie.
    *
    * @param  string  $name
    * @param  mixed   $default
    * @return string
    */
    public static function getCookie($name , $default = null){
        $cookie = $default;
        if(isset($_COOKIE[$name])){
            $cookie = $_COOKIE[$name];
        }
        return $cookie;
    }

    /**
     * Create a new cookie instance.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  int     $expire (in days)
     * @param  string  $path
     * @param  string  $domain
     * @param  bool    $secure
     * @param  bool    $httponly
     */
    public static function setCookie($name,$value,$expire = 1,$path = '/',$domain = '' ,$secure = false, $httponly = false){
        $expire_day = strtotime('+'.$expire.' days');
        if($domain == ''){
            $domain = $_SERVER['HTTP_HOST'];
        }
        //Log::error("setcookie ('$name','$value','$expire_day','/','$domain','$secure','$httponly')");
        setcookie($name,$value,$expire_day,$path,$domain,$secure,$httponly);
    }

    public static function deleteCookie($name){
        if(isset($_COOKIE[$name])){
            unset($_COOKIE[$name]);
            setcookie($name, '', time() - 3600);
        }
    }

}