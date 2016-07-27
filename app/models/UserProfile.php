<?php

class UserProfile extends \Eloquent {
    protected $fillable = [];
    protected $guarded= [];
    protected $table = "tblUserProfile";
    protected $primaryKey = "UserProfileID";

    public static function get_user_picture_url($user_id){

        $user_profile_img = UserProfile::where(["UserID"=>$user_id])->pluck('Picture');
        if(empty($user_profile_img)){
            $user_profile_img =  \Illuminate\Support\Facades\URL::to('assets/images/placeholder-male.gif');
        }else{

            if(AmazonS3::getS3Client() == 'NoAmazon'){
                $user_profile_img = \Illuminate\Support\Facades\URL::to($user_profile_img);
            }else{
                $user_profile_img = AmazonS3::unSignedImageUrl($user_profile_img);// str_replace("\\\\",'/',$destinationPath);
            }
        }
        return $user_profile_img;
    }
}