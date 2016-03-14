<?php

class RoleController extends \BaseController {
	/**
	 * Display a listing of the resource.
	 * GET /roles
	 *
	 * @return Response

	  */

    public function ajax_datagrid() {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $select = ['RoleName','Active','CreatedBy','updated_at','RoleID'];
        $roles = Role::select($select)->where('companyID',$CompanyID);
        return Datatables::of($roles)->make();
    }

    public function index() {

        $id=0;
        $companyID = User::get_companyID();
        $users = User::getUserIDList(0);
        $roles = Role::getRoles(0);
        $resources = ResourceCategories::getResourceCategories();
        Resources::insertResources();
        return View::make('roles.index', compact('id','gateway','resource','users','roles','resources'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /roles/create
     *
     * @return Response
     */
    public function create(){
        if($resources = Resources::orderby('ResourceName')->get()){
            return Response::json(array("status" => "success", "resources" => $resources));
        } else {
            return Response::json(array("status" => "failed", "message" => "Some thing wrong."));
        }
    }

    /**
     * Show the form for editing a existing resource.
     * GET /roles/edit
     *
     * @return Response
     */
    public function edit($id){
        $CompanyID = User::get_companyID();
        $role = Role::find($id);
        $select = ['tblPermission.PermissionID','tblResource.ResourceName','tblPermission.action'];
        $permission = Resources::select($select)->leftjoin('tblPermission',function($join)use($CompanyID,$role){
            $join->on('tblPermission.resource','=','tblResource.ResourceName');
            $join->on('tblPermission.CompanyID','=',DB::raw($CompanyID));
            $join->on('tblPermission.role','=',DB::raw("'".$role->RoleName."'"));
        });
        $permissions = $permission->get();
        if($permissions){
            return Response::json(array("status" => "success", "resources" => $permissions));
        } else {
            return Response::json(array("status" => "failed", "message" => "Some thing wrong."));
        }
        return $permission;
    }

    public function storerole(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $rules = array(
            "RoleName" => "required|unique:tblRole,RoleName,null,RoleID,CompanyID,".$CompanyID,
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $RoleName = $data['RoleName'];
        $role = ['CompanyID'=>$CompanyID,'RoleName'=>$RoleName,'CreatedBy'=>User::get_user_full_name(),'Active'=>1];
        if (Role::create($role)) {
            return Response::json(array("status" => "success", "message" => "Role Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Role."));
        }
    }


    public function storepermission(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $rules = array(
            "ResourceName" => "required|unique:tblResource,ResourceName,null,ResourceID",
            "ResourceValue" => "required",
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $resource = ['ResourceName'=>$data['ResourceName'],'ResourceValue'=>$data['ResourceValue'],'CreatedBy'=>User::get_user_full_name(),'CompanyID'=>$CompanyID];
        if (Resources::create($resource)) {
            return Response::json(array("status" => "success", "message" => "Resource Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Resource."));
        }
    }


	/**
	 * Update the specified resource in storage.
	 * PUT /roles/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function update() {

        $data = Input::all();
        $CompanyID = User::get_companyID();
        $users = [];
        $roles = [];
        $resources = [];
        $userid = [];
        foreach($data as $row){
            if(array_key_exists('user',$row)){
                $users[] = $row;
                $userid[] = $row['user'];
            }elseif(array_key_exists('role',$row)){
                $roles[] =$row;
            }elseif(array_key_exists('resource',$row)){
                $resources[] =$row;
            }
        }
        if(count($users)>0 && count($roles) >0) {
            foreach ($users as $index1=>$user) {
                foreach ($roles as $index2=>$role) {
                    $data = ['UserID' => $user['user'], 'RoleID' => $role['role']];
                    if((array_key_exists('AddRemove',$role) && $role['AddRemove']=='add') || (array_key_exists('AddRemove',$user) && $user['AddRemove']=='add')){
                        UserRole::create($data);
                    }else{
                        UserRole::where($data)->delete();
                    }
                }
            }
        }elseif(count($users)>0 && count($resources) >0) {
            $udelete = false;
            foreach($users as $u){
                if(!empty($u['AddRemove'])){
                    $udelete = true;
                }
            }
            //if part run when left side permissions and right side users
            //else part run when left side users and right side permissions
            if($udelete){
                UserPermission::whereIn('resourceID',$resources)->delete();
            }else{
                UserPermission::whereIn('UserID',$userid)->delete();
            }

            foreach($users as $uindex=>$user){
                if(array_key_exists('AddRemove',$user)){
                    $addremove = $user['AddRemove'];
                }
                foreach($resources as $index=>$resource){
                    if(array_key_exists('AddRemove',$resource)){
                        $addremove = $row['AddRemove'];
                    }
                    $data = ['UserID'=>$user['user'],'resourceID'=>$resource['resource'],'AddRemove'=>$addremove,'CompanyID'=>$CompanyID];
                    UserPermission::create($data);
                }
            }
        }elseif(count($roles)>0 && count($resources)>0){
            foreach($roles as $index1=>$role){
                foreach($resources as $index2=>$resource){
                    $data = ['roleID'=>$role['role'],'resourceID'=>$resource['resource'],'CompanyID'=>$CompanyID];
                    if((array_key_exists('AddRemove',$role) && $role['AddRemove']=='add') || (array_key_exists('AddRemove',$resource) && $resource['AddRemove']=='add')){
                        RolePermission::create($data);
                    }else{
                        RolePermission::where($data)->delete();
                    }
                }
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Please add or remove options on both sides. No change found."));
        }
        return Response::json(array("status" => "success", "message" => "Selection Updated"));
    }

	/**
	 * Remove the specified resource from storage.
	 * DELETE /roles/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function delete($id) {
        if( intval($id) > 0){
            $role = Role::find($id);
            if(!Role::checkForeignKeyByRole($role->RoleName)) {
                try {
                    Permission::where(['role'=>$role->RoleName])->delete();
                    $result = Role::find($id)->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "Role Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Role."));
                    }
                } catch (Exception $ex) {
                    return Response::json(array("status" => "failed", "message" => "Role is in Use, You cant delete this Role."));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Role is in Use, You cant delete this Role."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Role is in Use, You cant delete this Role."));
        }
    }

    /**
     * Get Role Field Value
     */
    /*public function get($id,$field){
        if($id>0 && !empty($field)){
            return json_encode(Role::where(["RoleID"=>$id])->pluck($field));
        }
        return json_encode('');
    }*/

    public function ajax_user_list($action){
        if($action=='user'){
            return Response::json(array("status" => "success", "result" => ''));
        }
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $user = [];
        $role = [];
        $resource = [];
        $users = [];
        foreach($data as $row){
            if(array_key_exists('user',$row)){
                $user[] = $row['user'];
            }elseif(array_key_exists('role',$row)){
                $role[] =$row['role'];
            }elseif(array_key_exists('resource',$row)){
                $resource[] =$row['resource'];
            }
        }
        if($action=='role') {
            $roleids = implode(',',$role);
            $id=0;
            $query = "call prc_GetAjaxUserList (".$CompanyID.",'".$id."','".$roleids."',1)";
            $excel_data  = DB::select($query);
            $users = json_decode(json_encode($excel_data),true);
        }elseif($action = 'resource'){
            $id = implode(',',$resource);
            $roleids =0;
            $query = "call prc_GetAjaxUserList (".$CompanyID.",'".$id."','".$roleids."',2)";
            $excel_data  = DB::select($query);
            $users = json_decode(json_encode($excel_data),true);
        }

        if(!empty($users)){
            return Response::json(array("status" => "success", "result" => $users));
        } else {
            return Response::json(array("status" => "failed", "message" => "Some thing wrong."));
        }
    }

    public function ajax_role_list($action){
        if($action=='role'){
            return Response::json(array("status" => "success", "result" => ''));
        }
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $user = [];
        $role = [];
        $resource = [];
        $roles = [];
        foreach($data as $row){
            if(array_key_exists('user',$row)){
                $user[] = $row['user'];
            }elseif(array_key_exists('role',$row)){
                $role[] =$row['role'];
            }elseif(array_key_exists('resource',$row)){
                $resource[] =$row['resource'];
            }
        }
        if($action=='user') {
            $UserID = implode(',',$user);
            $ResourceID =0;
            $query = "call prc_GetAjaxRoleList (".$CompanyID.",'".$UserID."','".$ResourceID."',1)";
            $excel_data  = DB::select($query);
            $roles = json_decode(json_encode($excel_data),true);
        }elseif($action=='resource'){
            $ResourceID = implode(',',$resource);
            $UserID =0;
            $query = "call prc_GetAjaxRoleList (".$CompanyID.",'".$UserID."','".$ResourceID."',2)";
            $excel_data  = DB::select($query);
            $roles = json_decode(json_encode($excel_data),true);
        }
        if(!empty($roles)){
            return Response::json(array("status" => "success", "result" => $roles));
        } else {
            return Response::json(array("status" => "failed", "message" => "Some thing wrong."));
        }
    }

    public function ajax_resource_list($action){
        if($action=='resource'){
            return Response::json(array("status" => "success", "result" => ''));
        }
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $user = [];
        $role = [];
        $resource = [];
        $resources =[];
        foreach($data as $row){
            if(array_key_exists('user',$row)){
                $user[] = $row['user'];
            }elseif(array_key_exists('role',$row)){
                $role[] =$row['role'];
            }elseif(array_key_exists('resource',$row)){
                $resource[] =$row['resource'];
            }
        }
        if($action == 'user') {
            $id = implode(',',$user);

            $roleids = UserRole::where(['UserID'=>$id])->select('RoleID')->lists('RoleID');
            if(count($roleids)>0){
                $roleids = implode(',',$roleids);
            }else{
                $roleids = 0;
            }
            $query = "call prc_GetAjaxResourceList (".$CompanyID.",'".$id."','".$roleids."',1)";
            $excel_data  = DB::select($query);
            $resources = json_decode(json_encode($excel_data),true);

        }elseif($action=='role'){
            $roleids = implode(',',$role);
            $id = 0;
            $query = "call prc_GetAjaxResourceList (".$CompanyID.",'".$id."','".$roleids."',2)";
            $excel_data  = DB::select($query);
            $resources = json_decode(json_encode($excel_data),true);
        }
        if(!empty($resources)){
            return Response::json(array("status" => "success", "result" => $resources));
        } else {
            return Response::json(array("status" => "failed", "message" => "Some thing wrong."));
        }
    }

    public function showcategory() {
        $id=0;
        $companyID = User::get_companyID();
        $users = User::getUserIDList(0);
        $roles = Role::getRoles(0);
        $resources = ResourceCategories::getResourceCategories();
        $actions = Resources::getResources();
        Resources::insertResources();
        return View::make('roles.category', compact('id','gateway','resource','users','roles','resources','actions'));
    }

    public function storecategory(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $ResourceCategory = ResourceCategories::find($data['ResourceCategoryID']);
        $rules = array(
            "ResourceCategoryName" => "required|unique:tblResourceCategories,ResourceCategoryName,null,ResourceCategoryID,CompanyID,".$CompanyID,
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $ResourceCategoryName = $data['ResourceCategoryName'];
        $category = ['CompanyID'=>$CompanyID,'ResourceCategoryName'=>$ResourceCategoryName];
        if(!empty($data['action']) && $data['action']=='edit'){
            if ($ResourceCategory->update($category)) {
                return Response::json(array("status" => "success", "message" => "Category Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem updating Role."));
            }
        }elseif(ResourceCategories::create($category)) {
            return Response::json(array("status" => "success", "message" => "Category Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Category."));
        }
    }

    public function categoryupdate(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $users = [];
        $roles = [];
        $resources = [];
        $actions = [];
        $userid = [];
        foreach($data as $row){
            if(array_key_exists('user',$row)){
                $users[] = $row;
                $userid[] = $row['user'];
            }elseif(array_key_exists('role',$row)){
                $roles[] =$row;
            }elseif(array_key_exists('resource',$row)){
                $resources[] =$row;
            }elseif(array_key_exists('action',$row)){
                $actions[] =$row;
            }
        }
        if(count($resources)>0 && count($actions) >0) {
            foreach ($resources as $index1=>$resource) {
                ResourceCategoryMapping::whereIn('ResourceCategoryID',$resource)->delete();
                foreach ($actions as $index2=>$action) {
                    $data = ['ResourceID' => $action['action'], 'ResourceCategoryID' => $resource['resource'], 'CompanyID' => $CompanyID];
                    ResourceCategoryMapping::create($data);
                }
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Please add or remove options on both sides. No change found."));
        }
        return Response::json(array("status" => "success", "message" => "Selection Updated"));
    }

    public function ajax_action_list($action){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $user = [];
        $role = [];
        $resource = [];
        $resources =[];
        foreach($data as $row){
            if(array_key_exists('user',$row)){
                $user[] = $row['user'];
            }elseif(array_key_exists('role',$row)){
                $role[] =$row['role'];
            }elseif(array_key_exists('resource',$row)){
                $resource[] =$row['resource'];
            }
        }

        if($action == 'resourcecategory') {
            $id = implode(',',$resource);
            $query = "call prc_GetAjaxActionList (".$CompanyID.",'".$id."',1)";
            $excel_data  = DB::select($query);
            $resources = json_decode(json_encode($excel_data),true);
        }
        if(!empty($resources)){
            return Response::json(array("status" => "success", "result" => $resources));
        } else {
            return Response::json(array("status" => "failed", "message" => "Some thing wrong."));
        }

    }

    public function skippermissionaction() {
        $id=0;
        $CompanyID = User::get_companyID();
        $query = "call prc_GetSkipResourceList (".$CompanyID.")";
        $excel_data  = DB::select($query);
        $actions = json_decode(json_encode($excel_data),true);

        return View::make('roles.skipaction', compact('actions'));
    }

    public function storpermissionaction(){
        $data = Input::all();
        $action='';
        $actionlist = array();
        if(!empty($data['ActionID'])){
            $actionids=$data['ActionID'];
            if(count($data['ActionID'])>0){
                foreach($actionids as $actionid){
                    $action.= $actionid.',';
                }
                $action = rtrim($action,',');
            }
            CompanySetting::setKeyVal('SkipPermissionAction',$action);
            $CompanyID = User::get_companyID();
            $query = "call prc_GetSkipResourceList (".$CompanyID.")";
            $excel_data  = DB::select($query);
            $actionlist = json_decode(json_encode($excel_data),true);
            return Response::json(array("status" => "success", "message" => "Action succefully saved.", 'actiondata' => $actionlist));

        }else{
            return Response::json(array("status" => "failed", "message" => "No Action Selected."));
        }
    }

}