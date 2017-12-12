<?php
//Use \DebugBar;
class GlobalAdminsController extends \BaseController {

    public function __construct()
    {

        //
    }
    public function select_company()
	{

        $isGlobalAdmin = Session::get("global_admin" );
        //echo "<pre>";print_r($isGlobalAdmin);exit();
        if($isGlobalAdmin == 1) {
            $companies = ["" => "Select a Company"];
            $companies += Company::where(["Status" => "1"])->lists("CompanyName", "CompanyID");

            return View::make("globaladmins.select_company", compact('companies'));
        }else{

            return Redirect::to(URL::to("/"));

        }

	}


}