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
            AmazonS3::getS3Client();
            if(file_exists(public_path($user_profile_img)))
            {
                $user_profile_img = \Illuminate\Support\Facades\URL::to($user_profile_img);
            }elseif(AmazonS3::$isAmazonS3 == 'Amazon'){
                $user_profile_img = AmazonS3::unSignedImageUrl($user_profile_img);// str_replace("\\\\",'/',$destinationPath);
            }
        }
        return $user_profile_img;
    }
}