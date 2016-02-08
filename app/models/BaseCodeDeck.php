<?php

class BaseCodeDeck extends \Eloquent {
	protected $fillable = [];

    public static $rules = [
        'CodeDeckName' =>      'required',
        'CompanyID' =>  'required',
    ];
    protected $table = 'tblCodeDeck';
    protected  $primaryKey = "CodeDeckId";
    protected $guarded = ['CodeDeckId'];

    public static function  getCodedeckIDList(){
        $company_id = User::get_companyID();
        $row = BaseCodeDeck::where(['CompanyId'=>$company_id])->lists('CodeDeckName','CodeDeckId');
        $row = array(""=> "Select a CodeDeck") + $row;
        return $row;

    }
    public static function getCodeDeckName($id){
        return BaseCodeDeck::find($id)->CodeDeckName;
    }
    public static function checkForeignKeyById($id) {

        $hasInCodeDeck = CodeDeck::where("CodeDeckId",$id)->count();
        $hasInAccount = Account::where("CodeDeckId",$id)->count();
        $hasInRateGenerator = RateGenerator::where("CodeDeckId",$id)->count();
        $hasInCustomerTrunk = CustomerTrunk::where("CodeDeckId",$id)->count();

        if( intval($hasInCodeDeck) > 0 || intval($hasInAccount) > 0 || intval($hasInRateGenerator) > 0  || intval($hasInCustomerTrunk) > 0    ){
            return true;
        }else{
            return false;
        }

    }
}