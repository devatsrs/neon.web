<?php
use Illuminate\Support\Facades\Crypt;
class UsersController extends BaseController {

    public function __construct() {
        //
    }

    public function index() {

        return View::make('user.show', compact(''));
    }

    public function add() {
        $roles = Role::getRoles(0);
        return View::make('user.create',compact('roles'));
    }

    /**
     * @return mixed
     */
    public function store() {

        $data = Input::all();
        $CompanyID = User::get_companyID();

        $data['Status'] = isset($data['Status']) ? 1 : 0;
        $data['JobNotification'] = isset($data['JobNotification']) ? 1 : 0;

        // we need atleast one admin user for admin panele login
        $AdminUser = User::where([ "AdminUser"=>1,"CompanyID" => $CompanyID])->count();
        if($AdminUser>0){
            $data['AdminUser'] = isset($data['AdminUser']) ? 1 : 0;
        }else{
            $data['AdminUser']=1;
        }
        $data['CompanyID'] = $CompanyID;
        /*if (!empty($data['Roles'])) {
            $data['Roles'] = implode(',', (array) $data['Roles']);
        }*/
        $rules = array(
            'FirstName' => 'required|min:2',
            'LastName' => 'required|min:2',
            'password' => 'required|confirmed|min:3',
            //'Roles' => 'required',
            'EmailAddress' => 'required|email|min:5|unique:tblUser,EmailAddress',
            'Status' => 'required',
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if(!empty($data['password'])){
            //$data['password'] = Hash::make($data['password']);
            $data['password'] = Crypt::encrypt($data['password']);
        }else{
            unset($data['password']);
        }

        $roles = isset($data['Roles'])?$data['Roles']:'';
        unset($data['password_confirmation']);
        unset($data['Roles']);

        if ($user = User::create($data)) {
            $UserID = DB::getPdo()->lastInsertId();
            UserProfile::create(array("UserID"=>$UserID));

            if(!empty($roles) && $data['AdminUser']==0) {
                foreach ($roles as $index2 => $roleID) {
                    UserRole::create(['UserID' => $UserID, 'RoleID' => $roleID]);
                }
            }
            Cache::forget('user_defaults');
            return Response::json(array("status" => "success", "message" => "User Successfully Created",'LastID'=>$user->UserID));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating User."));
        }
    }

    public function edit($id) {
        $user = DB::table('tblUser')->where(['UserID' => $id])->first();
        $roles = Role::getRoles(0);
        $userRoles = User::get_user_roles($id);
        return View::make('user.edit',compact('roles','user','userRoles'));
    }

    public function update($id) {

        $data = Input::all();
        $user = User::find($id);

        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $data['Status'] = isset($data['Status']) ? 1 : 0;
        $data['JobNotification'] = isset($data['JobNotification']) ? 1 : 0;
        $AdminUser = User::where([ "AdminUser"=>1,"CompanyID" => $companyID])->count();
        if($AdminUser>0){
            $data['AdminUser'] = isset($data['AdminUser']) ? 1 : 0;
        }else{
            $data['AdminUser']=1;
        }
        /*
        if (!empty($data['Roles'])) {
            $data['Roles'] = implode(',', (array) $data['Roles']);
        }*/


        $rules = array(
            'FirstName' => 'required',
            'LastName' => 'required',
            //'password' => 'required|confirmed|min:3',
            //'Roles' => 'required',
            'EmailAddress' => 'required|email|unique:tblUser,EmailAddress,' . $id . ',UserID',
            'Status' => 'required',
        );

        if(!empty($data['password']) || !empty($data['password_confirmation'])){
            $rules['password'] = 'required|confirmed|min:3';
        }
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if(!empty($data['password'])){
            //$data['password'] = Hash::make($data['password']);
            $data['password'] =Crypt::encrypt($data['password']);
        }else{
            unset($data['password']);
        }
        $roles = isset($data['Roles'])?$data['Roles']:'';
        unset($data['password_confirmation']);
        unset($data['Roles']);
        if ($user->update($data)) {
            //@todo: Need to optimize code like implemented in user roles.
            UserRole::where(['UserID' => $id])->delete();
            if(!empty($roles) && $data['AdminUser']==0) {
                foreach ($roles as $index2 => $roleID) {
                    UserRole::create(['UserID' => $id, 'RoleID' => $roleID]);
                }
            }
            Cache::forget('user_defaults');
            return Response::json(array("status" => "success", "message" => "User Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating User."));
        }
    }

    /*public function ajax_datagrid() {
        $companyID = User::get_companyID();
        $where = ["CompanyID" => $companyID];
        $select = ['Status', 'FirstName', 'LastName', 'EmailAddress', 'AdminUser', 'UserID'];
        if (isset($_GET['sSearch_0']) && $_GET['sSearch_0'] == '') { // by Default Status 1
            $where['Status'] = 1;
        }
        $users = User::where($where)->select($select);
        return Datatables::of($users)
            ->edit_column('AdminUser',function($row){
            $rules = '';
            if($row->AdminUser==0){
                $RoleName = UserRole::where(['UserID'=>$row->UserID])->join('tblRole','tblUserRole.RoleID','=','tblRole.RoleID')->select('RoleName')->lists('RoleName','RoleName');
                if(!empty($RoleName)) {
                    $rules = implode(',', $RoleName);
                }
            }else{
                $rules = 'Admin';
            }
            return $rules;
        })->make();
    }*/

    public function ajax_datagrid($type) {
        $CompanyID = User::get_companyID();
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $data['Status'] = 0;
        if (isset($_GET['sSearch_0']) && $_GET['sSearch_0'] == 1) { // by Default Status 1
            $data['Status'] = 1;
        }
        $columns = ['Status','FirstName', 'LastName', 'EmailAddress', 'Role'];
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getUsers (".$CompanyID.",".$data['Status'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Users.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Users.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query)->make();
    }

    /*public function exports($type) {
            $data = Input::all();
            $companyID = User::get_companyID();

            if (isset($data['sSearch_0']) && ($data['sSearch_0'] == '' || $data['sSearch_0'] == 1)) {
                $users = User::where(["CompanyID" => $companyID, "Status" => 1])->orderBy("UserID", "desc")->get(['FirstName', 'LastName', 'EmailAddress', 'Roles']);
            } else {
                $users = User::where(["CompanyID" => $companyID, "Status" => 0])->orderBy("UserID", "desc")->get(['FirstName', 'LastName', 'EmailAddress', 'Roles']);
            }

//        Excel::create('Users', function($excel) use($data) {
//                    $excel->sheet('Users', function($sheet) use($data) {
//                                $sheet->fromArray([print_r($data,true)]);
//                            });
//                })->download('xls');

            $excel_data = json_decode(json_encode($users),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Users.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Users.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

    }*/

    public function edit_profile($id){
        //if( User::checkPermission('User') ) {

        //$user_id = User::get_userID();
        $hasUserProfile = UserProfile::where("UserID",$id)->count();
        if($hasUserProfile == 0){
            UserProfile::create(array("UserID"=>$id));
        }
        $countries = Country::getCountryDropdownList();
        $user = DB::table('tblUser')->where(['UserID' => $id])->first();
        $user_profile = UserProfile::where(['UserID' => $id])->first();
        $timezones = TimeZone::getTimeZoneDropdownList();
        return View::make('user.edit_profile')->with(compact('user', 'user_profile', 'countries','timezones'));
        //}

    }

    public function update_profile($id){
        global $public_path;
        $data = Input::all();
        $user = User::find($id);
        $user_profile = UserProfile::where(["UserID"=>$id])->first();

        /*User Fields*/
        $user_data['FirstName'] = $data['FirstName'];
        $user_data['LastName'] = $data['LastName'];
        $user_data['EmailAddress'] = $data['EmailAddress'];
        $user_data['updated_by'] = User::get_user_full_name();
        $user_data['JobNotification'] = isset($data['JobNotification'])?1:0;


        if(Input::hasFile('Picture'))
        {


            /* $file = Input::file('Picture');
             $extension = '.'. $file->getClientOriginalExtension();
             $destinationPath = public_path() . '/' . Config::get('app.user_profile_pictures_path');*/



            $file = Input::file('Picture');
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['USER_PROFILE_IMAGE']);
            $destinationPath = public_path($amazonPath);
            $filename 		 	= 	rename_upload_file($destinationPath,$file->getClientOriginalName());
            $file->move($destinationPath, $filename);
            if (!AmazonS3::upload($destinationPath . $filename, $amazonPath)) {
                return Response::json(array("status" => "failed", "message" => "Failed to upload file." ));
            }

            $user_profile_data['Picture'] = $amazonPath . "/" . $filename;

            //Delete old picture
            if(!empty($user_profile->Picture)){
                $delete_previous_file =  $destinationPath . "/" . $user_profile->Picture;
                if(file_exists($delete_previous_file)){
                    @unlink($delete_previous_file);
                }
            }

        }

        /*Profile Fields */
        $user_profile_data['City'] = $data['City'];
        $user_profile_data['PostCode'] = $data['PostCode'];
        $user_profile_data['Country'] = $data['Country'];
        $user_profile_data['Address1'] = $data['Address1'];
        $user_profile_data['Address2'] = $data['Address2'];
        $user_profile_data['Address3'] = $data['Address3'];
        $user_profile_data['Utc'] = $data['Utc'];
        $user_profile_data['updated_by'] = User::get_user_full_name();

        $rules = array(
            'FirstName' => 'required',
            'LastName' => 'required',
            'EmailAddress' => 'required|email|unique:tblUser,EmailAddress,' . $id . ',UserID',
        );

        if(!empty($data['password']) || !empty($data['password_confirmation'])){
            $rules['password'] = 'required|confirmed|min:3';
            $user_data['password'] = $data['password'];
            $user_data['password_confirmation'] = $data['password_confirmation'];
        }
        $validator = Validator::make($user_data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if(!empty($data['password'])){
            //$user_data['password'] = Hash::make($data['password']);
            $user_data['password'] = Crypt::encrypt($data['password']);
        }else{
            unset($user_data['password']);
        }
        unset($user_data['password_confirmation']);

        if ($user->update($user_data)) {
            $user_profile->update($user_profile_data);
            Cache::forget('user_defaults');
            return Response::json(array("status" => "success", "message" => "User Profile Successfully Updated"));

        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating User Profile."));
        }

    }

    public function get_users_dropdown($companyID = 0){

        $users = ["" => "Select a User "];
        /*$permissions = ["Admin","Account Manager","Rate Manager"];  //Config::get('app.permissions');
        foreach ($permissions as $permission) {
            $users_ = DB::table('tblUser')->where(['CompanyID' => $companyID, 'Status' => 1])->where("Roles", "like", "%" . $permission . "%")->get();

            foreach ($users_ as $user) {
                $users[ucfirst($permission)][$user->UserID] = $user->EmailAddress . " - " . $user->Roles;
            }
        }*/
        $permissions = ["All User"];  //Config::get('app.permissions');
        $roles = Role::get();
        $admins_ = DB::table('tblUser')->where(['CompanyID' => $companyID, 'Status' => 1, 'AdminUser' => 1])->get();
        foreach ($admins_ as $admin) {
            $users['Admin'][$admin->UserID] = $admin->EmailAddress;
        }
        $users_ = DB::table('tblUser')->where(['CompanyID' => $companyID, 'Status' => 1])->get();
        foreach ($roles as $role) {
            foreach ($users_ as $user) {
                if(UserRole::where(['UserID'=>$user->UserID, 'RoleID'=>$role->RoleID])->count() > 0)
                    $users[ucfirst($role->RoleName)][$user->UserID] = $user->EmailAddress;
            }
        }
        return View::make('user.users_dropdown', compact('users'));
    }

    public function job_notification($id, $status) {
        if ($id > 0 && ( $status == 0 || $status == 1)) {
            if (User::find($id)->update(["JobNotification" => $status, "updated_by" => User::get_user_full_name()])) {
                return Response::json(array("status" => "success", "message" => "Status Successfully Changed"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Changing Status."));
            }
        }
    }

}