<?php

class HomeCustomerController extends BaseController {

    public function __construct() {
    }

    public function home() {

        if(Auth::check()){
            return Redirect::to('customer/dashboard');
        }else{
            $loginpath='customer/dologin';
            create_site_configration_cache();
            return View::make('customer.login',Compact('loginpath'));
        }

    }

    public function doLogin() {
        if (Request::ajax()) {
            $data = Input::all();
            if (User::user_login($data)) {
                $redirect_to = URL::to("/customer/dashboard");
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
        return Redirect::to('customer/login')->with('message', 'Your are now logged out!');
    }


}