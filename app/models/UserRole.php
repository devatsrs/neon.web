<?php

class UserRole extends \Eloquent {

    protected $guarded = array('UserRoleID');

    protected $table = 'tblUserRole';

    protected  $primaryKey = "UserRoleID";

}