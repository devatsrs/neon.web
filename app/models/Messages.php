<?php

class Messages extends \Eloquent {

    protected $fillable 	= 	['PID'];
    protected $table 		= 	"tblMessages";
    protected $primaryKey 	= 	"MsgID";
    public $timestamps 		= 	false; // no created_at and updated_at

    public static function logMsgRecord($JobType, $options = "") {
		              
		$rules = array(
			'CompanyID' => 'required',                
			'MsgLoggedUserID' => 'required',
			'Title' => 'required',
			'CreatedBy' => 'required',
		);

		$CompanyID 					= 	User::get_companyID();
		$options["CompanyID"] 		= 	$CompanyID;
		$data["CompanyID"] 			= 	$CompanyID;
		$data["AccountID"] 			= 	$options["AccountID"];
		$data["MsgLoggedUserID"] 	= 	User::get_userID();
		$data["Title"] 				= 	Account::getCompanyNameByID($data["AccountID"]) ;
		$data["Description"] 		= 	Account::getCompanyNameByID($data["AccountID"]);
		$data["CreatedBy"] 			= 	User::get_user_full_name();
		$data["updated_at"] 		= 	date('Y-m-d H:i:s');

		$validator 					= 	Validator::make($data, $rules);
		if ($validator->fails()) {
			return validator_response($validator);
		}

		if ($JobID = Job::insertGetId($data)) {                   
				return array("status" => "success", "message" => "Job Logged Successfully");
		} else {
			   return array("status" => "failed", "message" => "Problem Inserting Job.");
		}
    }


    public static function getMsgDropDown($reset = 0){
        $companyID = User::get_companyID();
        $userID = User::get_userID();
        $isAdmin = (User::is_admin() || User::is('RateManager'))?1:0;
        $query = "Call prc_getMsgsDropdown (".$companyID.",".$userID.",".$isAdmin .",".$reset.")" ; 
        $dropdownData = DataTableSql::of($query)->getProcResult(array('jobs','totalNonVisitedJobs'));
        return $dropdownData;

    }

}