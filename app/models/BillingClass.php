<?php

class BillingClass extends \Eloquent
{
    protected $guarded = array("BillingClassID");

    protected $table = 'tblBillingClass';

    protected $primaryKey = "BillingClassID";
    
    
    public static $messages = array(
        'RoundChargesAmount.required' =>'The currency field is required',
        'InvoiceTemplateID.required' =>'Invoice Template  field is required',
    );
    
    const  ACCOUNT_BALANCE = 1;
    const  PREFERRED_METHOD = 2;
    public static $AutoPayMethod = array('0'=>'Select' ,self::ACCOUNT_BALANCE => 'Account Balance',self::PREFERRED_METHOD=>'Preferred Method');

    public static $SendInvoiceSetting = array(""=>"Please Select an Option", "automatically"=>"Automatically", "after_admin_review"=>"After Admin Review");
    public static $AutoPaymentSetting = array("never"=>"Never", "invoiceday"=>"On Invoice Date","duedate"=>"On Due Date");

    public static function getDropdownIDList($CompanyID=0){
        if($CompanyID==0){
            $CompanyID = User::get_companyID();
        }
        $DropdownIDList = BillingClass::where(array("CompanyID"=>$CompanyID))->lists('Name', 'BillingClassID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

    public static function getInvoiceTemplateID($BillingClassID){
        return BillingClass::where('BillingClassID',$BillingClassID)->pluck('InvoiceTemplateID');
    }
    public static function getPaymentDueInDays($BillingClassID){
        return BillingClass::where('BillingClassID',$BillingClassID)->pluck('PaymentDueInDays');
    }
    public static function getRoundChargesAmount($BillingClassID){
        $RoundChargesAmount = '';
        if(!empty($BillingClassID)){
            $RoundChargesAmount = BillingClass::where('BillingClassID',$BillingClassID)->pluck('RoundChargesAmount');
        }
        return $RoundChargesAmount;
    }
    public static function getAccounts($BillingClassID){
        return Account::join('tblAccountBilling','tblAccountBilling.AccountID','=','tblAccount.AccountID')->where('BillingClassID',$BillingClassID)->orderBy('AccountName')->get(['AccountName']);
    }
	
	 public static function getTaxRate($BillingClassID){
        return BillingClass::where('BillingClassID',$BillingClassID)->pluck('TaxRateID');
    }
	
	public static function getTaxRateType($BillingClassID,$type){
		$final 			=   array();
        $result 		=   BillingClass::where('BillingClassID',$BillingClassID)->pluck('TaxRateID');
	    $resultarray 	= 	explode(",",$result);
		
		foreach($resultarray as $resultdata)	{
			if(TaxRate::where(['TaxRateId'=>$resultdata,'TaxType'=>$type])->count()){
				$final[]  = $resultdata;
			}
		}
		
		return $final;
    }

    public static function getBillingClassListByCompanyID($CompanyID=0){
        if($CompanyID==0){
            $CompanyID = User::get_companyID();
        }

        $Count = Reseller::IsResellerByCompanyID($CompanyID);
        if($Count==0){
            $DropdownIDList = BillingClass::where(array("CompanyID"=>$CompanyID))->lists('Name', 'BillingClassID');
        }else{
            $DropdownIDList = DB::table('tblBillingClass as b1')->leftJoin('tblBillingClass as b2',function ($join) use($CompanyID){
                $join->on('b1.BillingClassID', '=', 'b2.ParentBillingClassID');
                $join->on('b1.IsGlobal','=', DB::raw('1'));
                $join->on('b2.CompanyID','=', DB::raw($CompanyID));
            })->select(['b1.Name','b1.BillingClassID'])
                ->where(function($q) use($CompanyID) {
                    $q->where('b1.CompanyID', $CompanyID)
                        ->orWhere('b1.IsGlobal', '1');
                })->whereNull('b2.BillingClassID')
                ->lists('Name','BillingClassID');
        }
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

}