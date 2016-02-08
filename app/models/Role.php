<?php

class Role extends \Eloquent {

    protected $guarded = array('RoleID');

    protected $table = 'tblRole';

    protected  $primaryKey = "RoleID";

    protected $roles = [];

    public static function getRoles($old=1){
        $CompanyID = User::get_companyID();
        if($old==1){
            $roles = ["Admin"=>"Admin", "Account Manager"=>"Account Manager","Rate Manager"=>"Rate Manager","Billing Admin"=>"Billing Admin"];
        }else {
            $roles = Role::where('CompanyID', $CompanyID)->select('RoleID', 'RoleName')->orderBy('RoleName','Asc')->lists('RoleName', 'RoleID');
        }
        return $roles;
    }
}