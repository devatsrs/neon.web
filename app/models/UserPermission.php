<?php

class UserPermission extends \Eloquent {

    protected $guarded = array('UserPermissionID');

    protected $table = 'tblUserPermission';

    protected  $primaryKey = "UserPermissionID";

}