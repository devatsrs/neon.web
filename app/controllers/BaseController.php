<?php

class BaseController extends Controller {

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        $route = Route::currentRouteAction();
        if(!Auth::guest() && Session::get("customer") != 1){
            $controller = explode('@',$route);
            if(isset($controller[0]) && isset($controller[1])){
                $action = $controller[1];
                $str_all = $controller[0].'.*';
                $str = str_replace('@','.',$route);
                if(!$this->skipAllowedActions($str_all,$str)) {
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

    /**
     * @param $str_all - controller skip permission
     * @param $action - only asction skip permission
     * @return bool
     */
    public function skipAllowedActions($str_all,$action){
        //$allowed_actions = $this->getAllowedActions();
        $allowed_actions = Resources::getSkipResourcesValue();
        if(in_array($str_all,$allowed_actions)) {
            return true;
        }else if(in_array($action,$allowed_actions)) {
            return true;
        }else{
            return false;
        }

    }

    //not in use
    public function getAllowedActions(){
        
        return  array('get_users_dropdown','process_redirect','doforgot_password','doreset_password','doRegistration','loadDashboardJobsDropDown','cview','cdownloadUsageFile','display_invoice','download_invoice','invoice_payment','pay_invoice','invoice_thanks','search_customer_grid','edit_profile','update_profile','dologout','/Wysihtml5/getfiles','/Wysihtml5/file_upload','home');

    }
}
