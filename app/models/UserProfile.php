<?php

class UserProfile extends \Eloquent {
    protected $fillable = [];
    protected $guarded= [];
    protected $table = "tblUserProfile";
    protected $primaryKey = "UserProfileID";

    public static function get_user_picture_url($user_id){

        $user_profile_img = UserProfile::where(["UserID"=>$user_id])->pluck('Picture');
        $destinationPath = Config::get('app.user_profile_pictures_path');
        global $public_path;
        if(empty($user_profile_img)){
            $user_profile_img =  asset('assets/images/placeholder-male.gif');
        }else{
            $destinationPath=  str_replace("\\\\",'/',$destinationPath);
            $user_profile_img = URL::asset($destinationPath .'/' .$user_profile_img);
        }
        return $user_profile_img;
    }
}