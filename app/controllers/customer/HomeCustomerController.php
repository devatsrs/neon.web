<?php

class HomeCustomerController extends BaseController {

    public function __construct() {
    }

    public function home() {

        if(Auth::check()){
            if(CompanyConfiguration::get('CUSTOMER_DASHBOARD_DISPLAY') == 0 && CompanyConfiguration::get('CUSTOMER_NOTICEBOARD_DISPLAY') == 1) {
                return Redirect::to('customer/noticeboard');
            } else {
                return Redirect::to('customer/monitor');
            }
        }else{

            if(isset($_GET["lang"]) && !empty($_GET["lang"])){
                $language = $_GET["lang"];
            }else{
                $language=NeonCookie::getCookie('customer_language');
            }
            $languageList=Translation::getLanguageDropdownList();
            if(!array_key_exists($language,$languageList)){
                $language=Translation::$default_lang_ISOcode;
            }
//            set_cus_language($language);
            App::setLocale($language);
            NeonCookie::setCookie('customer_language',$language,365);

            if( DB::table('tblLanguage')->where(['ISOCode'=>$language, "is_rtl"=>"y"])->count()){
                NeonCookie::setCookie('customer_alignment',"right",365);
                $customer_alignment="right";
            }else{
                NeonCookie::setCookie('customer_alignment',"left",365);
                $customer_alignment="left";
            }

            create_site_configration_cache();
            $loginpath='customer/dologin';
            return View::make('customer.login',Compact('loginpath', "language", "customer_alignment"));
        }

    }

    public function doLogin() {
        if (Request::ajax()) {
            $data = Input::all();
            if (User::user_login($data) && NeonAPI::login("customer")) {
                $redirect_to = URL::to("/customer/monitor");
                if (CompanyConfiguration::get('CUSTOMER_DASHBOARD_DISPLAY') == 0 && CompanyConfiguration::get('CUSTOMER_NOTICEBOARD_DISPLAY') == 1){
                    $redirect_to =  URL::to('customer/noticeboard');
                }else if(isset($data['redirect_to'])){
                    $redirect_to = $data['redirect_to'];
                }
                echo json_encode(array("login_status" => "success", "redirect_url" => $redirect_to));
                return;
            } else {
                echo json_encode(array("login_status" => "invalid"));
                return;
            }
        }
    }

    public function dologout() {

        Session::flush();
        Auth::logout();
        return Redirect::to('customer/login')->with('message', 'Your are now logged out!');
    }


}