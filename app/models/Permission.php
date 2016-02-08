<?php

class Permission extends \Eloquent {
    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblPermission';
    protected $primaryKey = "PermissionID";

    public static function hasPermission($role , $resource ){

        if(is_array($role)){
            foreach($role as $rol_){
                $has_permission = Permission::where(['Role'=>$role,'resource'=>$resource])->count();
                if($has_permission)
                    return true;
            }
        }else{

            $has_permission = Permission::where(['Role'=>$role,'resource'=>$resource])->count();

            if($has_permission){
                return true;
            }
        }


        return false;

    }

    public static function hasPermissionnew($resource )
    {
        //return true;
        if (Session::has('user_permission')) {
            $user_permission = Session::get('user_permission');
            if (in_array($resource, $user_permission)) {
                return true;
            }
        }
        return false;
    }

}