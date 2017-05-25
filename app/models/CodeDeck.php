<?php

class CodeDeck extends \Eloquent {

	// Add your validation rules here
	public static $rules = [
            'Code' =>      'required',
            'CompanyID' =>  'required',
            'Description' => 'required',
            'codedeckid' => 'required',
	];
    protected $table = 'tblRate';
    protected  $primaryKey = "RateID";
    protected $fillable = [];
    protected $guarded = ['RateID'];

    static public function checkForeignKeyById($id) {
        /*
         * Tables To Check Foreign Key before Delete.
         * */

        //@TODO: need to check this is not correct .
        $hasInCustomerRate = CustomerRate::where("RateID",$id)->count();
        $hasInRateTableRate = RateTableRate::where("RateID",$id)->count();
        $hasInVendorRate = VendorRate::where("RateID",$id)->count();

        if( intval($hasInCustomerRate) > 0 || intval($hasInRateTableRate) > 0 || intval($hasInVendorRate) > 0    ){
            return true;
        }else{
            return false;
        }

    }
    public static function  getCodeDropdownList($CodeDeckId,$CompanyID){
        return array(""=> "Select") +CodeDeck::where(["CompanyID" => $CompanyID,'CodeDeckId'=>$CodeDeckId])->lists('Code', 'RateID');
    }
}