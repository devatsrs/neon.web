<?php
/**
 * Created by PhpStorm.
 * User: CodeDesk
 * Date: 01/06/2016 d/m/y
 * Time: 4:01 PM
 */
use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class Reseller extends Eloquent implements UserInterface, RemindableInterface {

    use UserTrait,
        RemindableTrait;

    protected $guarded 		= 	array('');
    protected $table 		= 	'tblAccount';
    protected $primaryKey 	= 	"AccountID";
	
	public static function get_companyID(){
        return Auth::user()->CompanyId;
    }

    public static function get_accountID(){
        return Auth::user()->AccountID;
    }

    public static function get_user_full_name(){
        return Auth::user()->FirstName.' '. Auth::user()->LastName;
    }

    public static function get_accountName(){
        return Auth::user()->AccountName;
    }

    public static function get_AuthorizeID(){
        return Auth::user()->AutorizeProfileID;
    }

    public static function get_Email(){
        return Auth::user()->Email;
    }

    public static function get_Billing_Email(){
        return Auth::user()->ResellerEmail;
    }

    public static function get_currentUser(){
        return Auth::user();
    }

    public static function get_customer_picture_url($AccountID){
        $user_profile_img = Customer::where(["AccountID"=>$AccountID])->pluck('Picture');
        if(!empty($user_profile_img)) {
            return AmazonS3::unSignedImageUrl($user_profile_img);
        }
        return '';

    }
    public function getRememberToken(){
        return null; // not supported
    }

    public function setRememberToken($value){
        // not supported
    }

    public function getRememberTokenName(){
        return null; // not supported
    }

    /**
     * Overrides the method to ignore the remember token.
     */
    public function setAttribute($key, $value){
        $isRememberTokenAttribute = $key == $this->getRememberTokenName();
        if (!$isRememberTokenAttribute)
        {
            parent::setAttribute($key, $value);
        }
    }
	
	 public function getReminderEmail() {
        return $this->ResellerEmail;
    }
	 public function getAuthIdentifier() {
        return $this->getKey();
    }
	
	static function checkResellerPermission($controller,$function)
	{
		$CompanyID 		= 	self::get_companyID();
		$query 			= 	"call prc_GetResellerPanelAccess (".$CompanyID.",'".$controller.'.'.$function."')";
		$result   		= 	DB::select($query);		
		if($result[0]->found_record==0){
			return true;
		}else{
			return false;
		}
	}
}