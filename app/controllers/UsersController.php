<?php

class UsersController extends BaseController {

    public function __construct() {
        //\Debugbar::disable();
    }

    public function index() {

            return View::make('user.show', compact(''));
    }

    public function add() {
            //$roles = Role::getRoles();
            return View::make('user.create',compact(''));
    }

    /**
     * @return mixed
     */
    public function store() {

        $data = Input::all();
        $CompanyID = User::get_companyID();

        $data['Status'] = isset($data['Status']) ? 1 : 0;
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
            $data['password'] = Hash::make($data['password']);
        }else{
            unset($data['password']);
        }
        unset($data['password_confirmation']);

        if ($user = User::create($data)) {
            UserProfile::create(array("UserID"=>DB::getPdo()->lastInsertId() ));
            Cache::forget('user_defaults');
            return Response::json(array("status" => "success", "message" => "User Successfully Created",'LastID'=>$user->UserID));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating User."));
        }
    }

    public function edit($id) {
            $user = DB::table('tblUser')->where(['UserID' => $id])->first();
            $roles = Role::getRoles();
            return View::make('user.edit',compact('roles','user'));
    }

    public function update($id) {

        $data = Input::all();
        $user = User::find($id);

        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $data['Status'] = isset($data['Status']) ? 1 : 0;        
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

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if(!empty($data['password'])){
            $data['password'] = Hash::make($data['password']);
        }else{
            unset($data['password']);
        }
        unset($data['password_confirmation']);
        if ($user->update($data)) {
            Cache::forget('user_defaults');
            return Response::json(array("status" => "success", "message" => "User Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating User."));
        }
    }

    public function ajax_datagrid() {
        $companyID = User::get_companyID();
        if (isset($_GET['sSearch_0']) && $_GET['sSearch_0'] == '') {
            $users = User::where(["CompanyID" => $companyID, "Status" => 1])->select(array('Status', 'FirstName', 'LastName', 'EmailAddress', 'AdminUser', 'UserID')); // by Default Status 1
        } else {
            $users = User::where(["CompanyID" => $companyID])->select(array('Status', 'FirstName', 'LastName', 'EmailAddress', 'AdminUser', 'UserID'));
        }
        return Datatables::of($users)->make();
    }

    public function exports($type) {
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
                $file_path = getenv('UPLOAD_PATH') .'/Users.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Users.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

    }

    public function edit_profile($id){
        //if( User::checkPermission('User') ) {

            $user_id = User::get_userID();
            $hasUserProfile = UserProfile::where("UserID",$user_id)->count();
            if($hasUserProfile == 0){
                UserProfile::create(array("UserID"=>$user_id));
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

        if(Input::hasFile('Picture'))
        {
            $file = Input::file('Picture');
            $extension = '.'. $file->getClientOriginalExtension();
            $destinationPath = public_path() . '/' . Config::get('app.user_profile_pictures_path');

            //Create profile picture dir if not exists
            if(!file_exists($destinationPath)){
                mkdir($destinationPath);
            }

            $fileName = urlencode(str_replace(' ','',strtolower($user_data['FirstName']) .'_'. strtolower($user_data['LastName'] .'_'.str_random(4) ) .$extension));
            $file->move($destinationPath, $fileName);
            $picture = $fileName;

            //Delete old picture
            if(!empty($user_profile->Picture)){
                $delete_previous_file =  $destinationPath . "/" . $user_profile->Picture;
                if(file_exists($delete_previous_file)){
                    @unlink($delete_previous_file);
                }
            }
            $user_profile_data['Picture'] = $picture;

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

        if (!empty($data['Roles'])) {
            $user_data['Roles'] = implode(',', (array) $data['Roles']);
        }

        $rules = array(
            'FirstName' => 'required',
            'LastName' => 'required',
            'EmailAddress' => 'required|email|unique:tblUser,EmailAddress,' . $id . ',UserID',
        );

        if(!empty($data['password'])){

            if($data['password'] != $data['password_confirmation']){
                return Response::json(array("status" => "failed", "message" => "Password and Confirm Password are not matching."));
            }

            $user_data['password'] = Hash::make($data['password']);
            unset($data['password_confirmation']);
            //$rules = array_merge($rules , ['password' => 'confirmed']);

        }
        $validator = Validator::make($user_data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

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
        foreach ($permissions as $permission) {
            $users_ = DB::table('tblUser')->where(['CompanyID' => $companyID, 'Status' => 1])->get();

            foreach ($users_ as $user) {
                $users[ucfirst($permission)][$user->UserID] = $user->EmailAddress;
            }
        }
        return View::make('user.users_dropdown', compact('users'));
    }
}