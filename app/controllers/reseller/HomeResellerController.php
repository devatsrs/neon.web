<?php

class HomeResellerController extends BaseController {

    public function __construct() {
    }

    public function home() {

        if(Auth::check()){
            return Redirect::to('reseller/monitor');
        }else{
            $loginpath='reseller/dologin';
            create_site_configration_cache();
            return View::make('reseller.login',Compact('loginpath'));
        }

    }

    public function doLogin() {		
        if (Request::ajax()) {
            $data = Input::all();
            if (User::reseller_login($data)) {
                $redirect_to = URL::to("/reseller/monitor");
                if(isset($data['redirect_to'])){
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
        return Redirect::to('reseller/login')->with('message', 'Your are now logged out!');
    }


}