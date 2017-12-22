<?php

class HomeResellerController extends BaseController {

    public function __construct() {
    }

    public function home() {
        log::info('reseller home');
        if(Auth::check()){			
			return Redirect::to('reseller/profile');			
        }else{
            $loginpath='reseller/dologin';
            create_site_configration_cache();
            return View::make('resellerpanel.login',Compact('loginpath'));
        }

    }

    public function doLogin() {
        if (Request::ajax()) {
            $data = Input::all();
            log::info(print_r($data,true));
            // && NeonAPI::login("reseller")
            //if (Auth::attempt(array('EmailAddress' => $data['email'], 'password' => $data['password'] ,'Status'=> 1 )) && NeonAPI::login("reseller")) {}

            if (Auth::attempt(array('EmailAddress' => $data['email'], 'password' => $data['password'] ,'Status'=> 1 ))){
                log::info('attempt sucess');
               if(NeonAPI::login("reseller")) {
                log::info('reseller');
                Session::set("reseller", 1);
                User::setUserPermission();
                Log::info("Current Login Date : ".date('Y-m-d H:i:s'));
                Log::info(print_r(Auth::user(),true));
                Log::info('User ID '.Auth::user()->UserID);
                User::find(Auth::user()->UserID)->update(['LastLoginDate' => date('Y-m-d H:i:s')]);
                create_site_configration_cache();
                $query_data =  parse_url($_SERVER['HTTP_REFERER']);
                if(isset($query_data['query'])){parse_str($query_data['query']);}
                if(!isset($redirect_to)){
                    $redirect_to = URL::to("/reseller/profile");
                }
                if(isset($data['redirect_to'])){
                    $redirect_to = $data['redirect_to'];
                }
                echo json_encode(array("login_status" => "success", "redirect_url" => $redirect_to));
                return;
            } else {
                echo json_encode(array("login_status" => "invalid"));
                return;
            }
        }else{
                Session::flush();
                Auth::logout();
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