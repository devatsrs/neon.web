<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;

class GlobalAdmin extends Eloquent implements UserInterface  {

    use UserTrait;

    protected $guarded = array('');
    protected $table = 'tblGlobalAdmin';
    protected  $primaryKey = "GlobalAdminID";

    public static function is_global_user($data = array()){
        if(!empty($data) && isset($data["email"]) && isset($data["password"]) ){

            $GlobalAdmin = GlobalAdmin::where(["EmailAddress" => $data["email"], "Status" => 1 ])->first();
            if($GlobalAdmin) {
                if (Hash::check($data["password"], $GlobalAdmin['password'])) {
                    Session::set("global_admin", 1 );
                    return true;
                }
            }
            /*if (Auth::once(array('EmailAddress' => $data['email'], 'password' => $data['password'] ,'Status'=> 1 ))) {
                Session::set("global_admin", 1 );
                return true;
            }*/
            /*else{
                $queries = DB::getQueryLog();

                print_r($queries);
            }*/

        }
        return false;

    }

}