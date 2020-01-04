<?php

use app\controllers\api\Codes;

class ApiController extends Controller {
    protected function setupLayout()
    {
        //Set Company Timezone
        if(Auth::check()) {
            $Timezone = Company::getCompanyTimeZone(0);
            if (isset($Timezone) && $Timezone != '') {
                date_default_timezone_set($Timezone);
                Config::set('app.timezone',$Timezone);
            }
        }

        if ( ! is_null($this->layout))
        {
            $this->layout = View::make($this->layout);
        }
    }

    public function login(){
        $LicenceApiResponse = Company::getLicenceResponse();

        if(!$LicenceApiResponse["Status"]){
            return Response::json($LicenceApiResponse);
        }
        $Request = Input::all();
        $rules = array(
            'EmailAddress' =>  'required',
            'password' => 'required',
        );
        $validator = Validator::make($Request, $rules);

        if ($validator->fails()) {
            return Response::json(["ErrorMessage"=>Codes::$Code401[1]], Codes::$Code401[0]);
        }

        $validate=NeonAPI::RegisterApiLogin($Request);
        if (! $validate ) {
            return Response::json(["ErrorMessage"=>Codes::$Code401[1]], Codes::$Code401[0]);
        }

        return Response::json([
            "status"  => "Success",
            "message" => "Login Success",
            "data"    => $validate
        ], Codes::$Code200[0]);
    }

    public function logout(){
        Session::flush();
        Auth::logout();
        return Response::json(["status"=>"Success", "message"=>"Logout Success"]);
    }

}