<?php

class GlobalAdminsController extends \BaseController {

    public function __construct()
    {

        \Debugbar::disable();
    }
    public function select_company()
	{

        $isGlobalAdmin = Session::get("global_admin" );
        if($isGlobalAdmin == 1) {
            $companies = Company::where(["Status" => "1"])->lists("CompanyName", "CompanyID");
            $companies[""] = "Select a company";

            return View::make("globaladmins.select_company", compact('companies'));
        }else{

            return Redirect::to(URL::to("/"));

        }

	}


}