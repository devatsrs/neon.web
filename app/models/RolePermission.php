<?php

class RolePermission extends \Eloquent {

    protected $guarded = array('RolePermissionID');

    protected $table = 'tblRolePermission';

    protected  $primaryKey = "RolePermissionID";

}