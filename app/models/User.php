<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class User extends Eloquent implements UserInterface, RemindableInterface {

    use UserTrait,
        RemindableTrait;

    public static $rules = array(
    );

    protected $guarded = array();
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tblUser';

    protected  $primaryKey = "UserID";

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password');

    // customer login
    public static function user_login($data = array()){
        if(!empty($data) && isset($data["email"]) && isset($data["password"]) ){
            Config::set('auth.model', 'Customer');
            Config::set('auth.table', 'tblAccount');
            $auth = Auth::createEloquentDriver();
            Auth::setProvider($auth->getProvider());
            //$customer = Customer::where('BillingEmail','like','%'.$data["email"].'%')->first();
			$customer = Customer::whereRaw("FIND_IN_SET('".$data['email']."',BillingEmail) !=0")->first(); 
            if($customer) {
                //if (Hash::check($data["password"], $customer->password)) {
                if (self::checkPassword($data["password"],$customer->password)) {
                    Auth::login($customer);
                    Session::set("customer", 1);
					Session::set("CustomerEmail", $data["email"]);
                    Log::info("============Web Login Success===========");
                    return true;
                }
            }
            /*if (Auth::attempt(array('BillingEmail' => $data['email'], 'password' => $data['password'] ,'Status'=> 1 ,"VerificationStatus"=> Account::VERIFIED ))) {
                Session::set("customer", 1 );
                return true;
            }
            /*else{
                $queries = DB::getQueryLog();

                print_r($queries);
            }*/

        }
        return false;

    }

    public static function checkPermission($resource, $abort = true) {

        $role = User::get_user_role_array();

        if(count($role)>0){

            if(in_array('Admin',$role)){
                return true;
            }

            if( Permission::hasPermission($role , $resource ) ){
                return true;
            }
        }
        if($abort) {
            App::abort(403, 'Unauthorized action.');
        }
            return false;

    }

    public static function checkPermissionnew($resource, $abort = false) {
        if( Permission::hasPermissionnew($resource) ){
            return true;
        }
        return false;

    }


    public static function hasPermission($resource) {
        return self::checkPermission($resource,false);
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier() {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword() {
        return $this->password;
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail() {
        return $this->EmailAddress;
    }

    // not using
    public function login($email, $password) {

        if (empty($email))
            return false;

        $result = $this->where("EmailAddress", '=', $email)->where("Status", '=', 1)->where('password', '=', $password)->first();

        if (!empty($result)) {

            //Check Role
//            if($role == 1){
//
//            }

            return array("login_status" => "success", "redirect_url" => URL::to("/process_redirect"));
        } else {
            return array("login_status" => "invalid");
        }
    }

    /*
     * Get User by Email
     */

    public function get_user_by_email($email) {

        if (empty($email)) {
            return FALSE;
        }

        $result = $this->where(["EmailAddress"=>$email])->first();

        if (!empty($result)) {
            return $result;
        } else {
            return FALSE;
        }
    }

    public static function get_companyID(){
        if(Auth::guest()){
            return $CompanyID = SiteIntegration::GetComapnyIdByKey();
        }else {
            /*
            $customer=Session::get('customer');
            //$reseller=Session::get('reseller');
            if($customer==1){
                return $CompanyID = Customer::get_companyID();
            }else{
                return Auth::user()->CompanyID;
            }*/
            return $CompanyID = Session::get('customer')==1?Customer::get_companyID():Auth::user()->CompanyID;
        }
    }

    public static function get_userID(){
        $customer=Session::get('customer');
        //$reseller=Session::get('reseller');
        if($customer==1){
            return Customer::get_accountID();
        }
        return Auth::user()->UserID;
    }

    public static function get_user_full_name(){
        $customer=Session::get('customer');
        //$reseller=Session::get('reseller');
        if($customer==1){
            return Customer::get_user_full_name();
        }
        return Auth::user()->FirstName.' '. Auth::user()->LastName;

    }

    public static function get_user_role(){
        return Auth::user()->Roles;
    }

    public static function get_user_role_array(){
        $roles = Auth::user()->Roles;

        $roles = explode(',',$roles);

        return $roles;
    }
    public static function get_user_roles($id=0){

        $CompanyID = User::get_companyID();
        $select = ['tblRole.RoleID'];
        $check = 0;
        if($id==0){
            $check = 1;
            $id = Auth::user()->UserID;
        }

        $select[] = DB::raw('tblUserRole.RoleID,tblRole.RoleName');
        $role = Role::join('tblUserRole', function ($join) use ($CompanyID,$id) {
            $join->on('tblUserRole.RoleID', '=', 'tblRole.RoleID');
            $join->on('tblRole.CompanyID', '=', DB::raw($CompanyID));
            $join->on('tblUserRole.UserID','=',DB::raw($id));
        });
        $roles = $role->select($select)->distinct()->get()->lists('RoleName');
        if($check==1) {
            $roless = '';
            if (count($roles) > 0) {
                foreach ($roles as $role) {
                    $roless .= $role . ',';
                }
                $roless = rtrim($roless, ',');
            } elseif (User::is_admin()) {
                return 'Admin';
            }
        }else{
            $roless = $role->select($select)->distinct()->get()->lists('RoleID');
        }
        return $roless;
    }
    public static function get_user_role_array_new(){
        $CompanyID = User::get_companyID();
        $select = ['tblRole.RoleID'];
        $id = Auth::user()->UserID;
        $select[] = DB::raw('tblUserRole.RoleID');
        $role = Role::join('tblUserRole', function ($join) use ($CompanyID,$id) {
            $join->on('tblUserRole.RoleID', '=', 'tblRole.RoleID');
            $join->on('tblRole.CompanyID', '=', DB::raw($CompanyID));
            $join->on('tblUserRole.UserID','=',DB::raw($id));
        });

        $roles = $role->select($select)->distinct()->get()->lists('RoleID');
        return $roles;
    }

    public static function is($user_role){
        /*$roles = self::get_user_role_array();
        foreach($roles as $role){
            if($user_role == $role){
                return true;
            }
        }*/
        if(Session::has('user_category_permission')) {
            $user_category_permission = Session::get('user_category_permission');
            if (!empty($user_role)) {
                if (in_array($user_role, $user_category_permission)) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function get_user_email(){
        $customer=Session::get('customer');
        if($customer==1){
            return Customer::get_Email();
        }
        return Auth::user()->EmailAddress;
    }


    public static function checkMinRights($user_role){
        if(User::is_admin()){
            return true;
        }
        $roles = self::get_user_role_array();
        foreach($roles as $role){
            if($user_role == $role){
                return true;
            }

        }
    }

    public static function getOwnerUsersbyRole(){
        $companyID = User::get_companyID();
        $account_owners = array();
        /*$account_owners = DB::table('tblUser')->where(["CompanyID" => $companyID, "Status" => 1])->where(function($query) {
            $query->where('Roles', 'like', '%Account Manager%')
                  ->orwhere('Roles', 'like', '%Admin%');
        })
            ->select(array(DB::raw("concat(tblUser.FirstName,' ',tblUser.LastName) as FullName"), 'UserID'))->orderBy('FullName')->lists('FullName', 'UserID');*/

        $user = array();
        $result = DB::table('tblResourceCategories')->select('ResourceCategoryID')->where(["CompanyID" => $companyID, "ResourceCategoryName" => 'AccountManager'])->first();
        if(count($result)>0 && !empty($result->ResourceCategoryID)){
            $user1 = array();
            $query = "call prc_GetAjaxUserList (".$companyID.",'".$result->ResourceCategoryID."','0',2)";
            $data  = DB::select($query);
            $userdatas = json_decode(json_encode($data),true);
            foreach($userdatas as $userdata){
                if($userdata['Checked'] == 'true' || $userdata['AddRemove'] =='add'){
                    $user1['UserID'] = $userdata['UserID'];
                    $user[]=$user1['UserID'];
                }
            }
        }
        $account_owners = DB::table('tblUser')->where(["CompanyID" => $companyID, "Status" => 1])->where(function($query) use ($user) {
            $query->where('AdminUser', '=', '1')
                ->orwhereIn('UserID',$user);
        })
            ->select(array(DB::raw("concat(tblUser.FirstName,' ',tblUser.LastName) as FullName"), 'UserID'))->orderBy('FullName')->lists('FullName', 'UserID');

        if(!empty($account_owners)){
            $account_owners = array(""=> "Select Owner")+$account_owners;
        }

        return $account_owners;

    }
    public static function getUserIDList($select = 1){
        $where = array('Status'=>1,'CompanyID'=>User::get_companyID());
        $user = User::where($where);
        if($select==0){
            $user->where('AdminUser','!=',1);
        }
        $row = $user->select(array(DB::raw("concat(tblUser.FirstName,' ',tblUser.LastName) as FullName"), 'UserID'))->orderBy('FullName')->lists('FullName', 'UserID');
        if(!empty($row) & $select==1){
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }
	 
	public static function getUserIDListAll($select = 1){
        $where = array('Status'=>1,'CompanyID'=>User::get_companyID());
        $user = User::where($where);
        
        $row = $user->select(array(DB::raw("concat(tblUser.FirstName,' ',tblUser.LastName) as FullName"), 'UserID'))->orderBy('FullName')->lists('FullName', 'UserID');
        if(!empty($row) & $select==1){
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }
	
	
	 public static function getUserIDListOnly($select = 1){
        $where = array('Status'=>1,'CompanyID'=>User::get_companyID());
        $user = User::where($where);
        if($select==0){
            $user->where('AdminUser','!=',1);
        }
        $row = $user->select(array(DB::raw("concat(tblUser.FirstName,' ',tblUser.LastName) as FullName"),'EmailAddress'))->orderBy('FullName')->lists('FullName', 'EmailAddress');
        return $row;
    }
	
	

    public static function get_currentUser(){
        $customer=Session::get('customer');
        if($customer==1){
            return Customer::get_currentUser();
        }
        return Auth::user();
    }

    public static function is_admin(){
        if(!empty(User::get_currentUser()->AdminUser) && User::get_currentUser()->AdminUser>0) {
            return true;
        }
        return false;
    }

    public function get_user_by_remember_token($remember_token) {
        if (empty($remember_token)) {
            return FALSE;
        }
        $result = $this->where(["remember_token"=>$remember_token])->first();
        if (!empty($result)) {
            return $result;
        } else {
            return FALSE;
        }
    }

    public static function checkCategoryPermission($resourcecontroller,$action)
    {	
        if(user::is_admin()){
            return true;
        }elseif(Session::has('user_category_permission')) {
            $user_category_permission = Session::get('user_category_permission'); 
            if(!empty($resourcecontroller)) {
                $resourcecontrollerAll = $resourcecontroller . '.All';
                if(in_array($resourcecontrollerAll, $user_category_permission)) {
                    return true;
                }
                if(!empty($action)) {
                    $rarrays=explode(',',$action);
                    if(count($rarrays)>0){
                        foreach($rarrays as $rarray){
                            if(!empty($rarray)){
                            $resourcecategory = $resourcecontroller . '.' . $rarray;
                            if (in_array($resourcecategory, $user_category_permission)) {
                                return true;
                            }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    public static function setUserPermission(){
        $CompanyID = User::get_companyID();
        $Userid = User::get_userID();
        $query = "call prc_GetAllResourceCategoryByUser (".$CompanyID.",'".$Userid."')";
        $excel_data  = DB::select($query);
        $resourcescat = json_decode(json_encode($excel_data),true);
        $usrcategoryname = [];
        if(count($resourcescat)){
            foreach($resourcescat as $row){
                if(!empty($row['ResourceCategoryName'])) {
                    $usrcategoryname[$row['ResourceCategoryName']] = $row['ResourceCategoryName'];
                }
            }
            Session::put('user_category_permission', $usrcategoryname);
        }

        $usrcat = [];
        if(count($resourcescat)){
            foreach($resourcescat as $row){
                $usrcat[] = $row['ResourceCategoryID'];
            }
        }
        $resources = Resources::whereIn('CategoryID',$usrcat)->select(['ResourceValue'])->get();
        if (count($resources) > 0) {
            $resource_array = [];
            foreach($resources as $row){
                if(!empty($row->ResourceValue)) {
                    $resource_array[$row->ResourceValue] = $row->ResourceValue;
                }
            }
            Session::put('user_permission', $resource_array);
        }
    }

    /**
     * User::has("Payment","Add")  will check Payment.Add or Paymente.*
     * check Permission - For Buttons
     */
    public static function can($ResourceCategoryName ,$ResourceCategoryAction = '*'){

        if(User::is_admin()){
            return true;
        }

        if(User::is($ResourceCategoryName.'.'.$ResourceCategoryAction) || User::is($ResourceCategoryName.'.*')) {
            return true;
        }
        return false;

    }

    public static function getEmailByUserName($CompanyID,$Name){
        $useremail = '';
        $users = User::where(["CompanyID"=>$CompanyID,"Status"=>'1'])->get();
        if(count($users)>0){
            foreach($users as $user){
                $username = $user->FirstName.' '. $user->LastName;
                if($username==$Name){
                    if(!empty($user->EmailAddress)){
                        $useremail = $user->EmailAddress;
                    }
                }
            }
        }
        return $useremail;
    }

    public static function checkPassword($LoginPassword,$Password){
        $result=false;
        try{
            if(Hash::check($LoginPassword, $Password) || $LoginPassword==Crypt::decrypt($Password)){
                $result=true;
            }
        }catch(Exception $e){

        }

        return $result;
    }
}
