<?php

class BaseController extends Controller {

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if(NeonCookie::getCookie('customer_language')){
            App::setLocale(NeonCookie::getCookie('customer_language'));
            \Carbon\Carbon::setLocale(NeonCookie::getCookie('customer_language'));
        }

        //@TODO: load translated arrays;
        // for example Account::$cdr_type = translated array
        // etc...

        //Set Company Timezone
        if(Auth::check()) {
            $Timezone = Company::getCompanyTimeZone(0);
            if (isset($Timezone) && $Timezone != '') {
                date_default_timezone_set($Timezone);
                Config::set('app.timezone',$Timezone);
            }
        }
		
		

        $route = Route::currentRouteAction();
        if(!Auth::guest() && Session::get("customer") != 1 && Session::get("reseller") != 1){
            $controller = explode('@',$route);
            if(isset($controller[0]) && isset($controller[1])){
                $action = $controller[1];
                $str_all = $controller[0].'.*';
                $str = str_replace('@','.',$route);

                if(!$this->skipAllowedActions($action)) {
                    if(!Auth::guest()) {
                        if (!user::is_admin()) {
                            if (!User::checkPermissionnew($str)) {
                                if (!User::checkPermissionnew($str_all)) {
                                    App::abort(403, 'You have not access to' . $str);
                                }
                            }
                        }
                    }
                }
            }else{
                throw new Exception(" Error on BaseController.");

            }
        }
        if ( ! is_null($this->layout))
        {
            $this->layout = View::make($this->layout);
        }
    }

    /**
     * Get Model Field Value
     */
    public function get($id,$field){

        if($id>0 && !empty($field) && isset($this->model)){
            $Model = new $this->model;
            return json_encode($Model->where([$Model->primaryKey=>$id])->pluck($field));
        }
        return json_encode('');
    }

    public function skipAllowedActions($action){
        $allowed_actions = $this->getAllowedActions();
        if(in_array($action,$allowed_actions)) {
            return true;
        }
        return false;
    }

    public function getAllowedActions(){

        return  array('get_users_dropdown','process_redirect','doforgot_password','doreset_password','doRegistration','loadDashboardJobsDropDown','loadDashboardMsgsDropDown','cview','cdownloadUsageFile','display_invoice','download_invoice','invoice_payment','pay_invoice','invoice_thanks','search_customer_grid','edit_profile','update_profile','dologout','/Wysihtml5/getfiles','/Wysihtml5/file_upload','home','activate_support_email');

    }
	
	 public function validateTicketLicence(){		
	 	$license =   Tickets::CheckTicketLicense(); 
		if(!$license){
			Redirect::to('/')->send();
		}
		return $license;
	 }
}
