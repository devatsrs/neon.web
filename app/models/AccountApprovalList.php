<?php

class AccountApprovalList extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('AccountApprovalListID');

    protected $table = 'tblAccountApprovalList';

    protected  $primaryKey = "AccountApprovalListID";

    public  static  function isVerfiable($AccountID){
        $AccountApproval = AccountApproval::getRequiredList($AccountID);
        $configcount = count($AccountApproval);
        $listcount = 0;
        foreach((array)$AccountApproval as $row){
            $row->AccountApprovalID;
            if(AccountApprovalList::where(['AccountID'=>$AccountID, "tblAccountApprovalList.CompanyID"=> User::get_companyID()])->where('AccountApprovalID',$row->AccountApprovalID)->count()){
                $listcount++;
            }
        }
        if($listcount == $configcount){
            return true;
        }
        return false;
    }
}