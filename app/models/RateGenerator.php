<?php

class RateGenerator extends \Eloquent {

    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblRateGenerator';
    protected $primaryKey = "RateGeneratorId";

    const LOWEST_PRICE  = 0;
    const HIGHEST_PRICE = 1;

	public function raterule()
    {
        return $this->hasMany('RateRule');
    }

    public static function rateGeneratorList($companyID){
        return RateGenerator::join("tblTrunk","tblTrunk.TrunkID","=","tblRateGenerator.TrunkID")
            ->where([
                "tblRateGenerator.CompanyID" => $companyID
            ])
            ->select(array(
                'tblRateGenerator.RateGeneratorName',
                'tblTrunk.Trunk',
                'tblRateGenerator.Status',
                'tblRateGenerator.RateGeneratorId',
                'tblRateGenerator.TrunkID',
                'tblRateGenerator.CodeDeckId'
            ))
            ->lists('RateGeneratorName', 'RateGeneratorId');
    }
    public static function checkExchangeRate($RateGeneratorId){
        $status = array(
            'status'=>0,
            'message'=>''
        );
        $RateGenerator = RateGenerator::find($RateGeneratorId);
        $CompanyID = $RateGenerator->CompanyID;
        $CurrencyId = Company::getCompanyField($CompanyID,'CurrencyId');
        $RateRule = RateRule::where(array('RateGeneratorId'=>$RateGeneratorId))->get();

        foreach($RateRule as $RateRuleRow){
            $RateRuleSource = RateRuleSource::where(array('RateRuleId'=>$RateRuleRow->RateRuleId))->get();
            foreach($RateRuleSource as $RateRuleSourceRow) {
                $CurrencyToID = Account::where(["AccountID" => $RateRuleSourceRow->AccountId])->pluck('CurrencyId');
                if($CurrencyToID != $CurrencyId){
                    if (!CurrencyConversion::isDefined($CompanyID,$CurrencyToID)) {
                        $status['status'] = 1;
                        $CurrencyToCode = Currency::getCurrencyCode($CurrencyToID);
                        $status['message'] = "Exchange Rate $CurrencyToCode not defined ";
                        return $status;
                    }
                }
            }
        }
        return $status;
    }
	
	public static function getRateGenerators(){
        $compantID = User::get_companyID();
        $where = ['CompanyID'=>$compantID];      
        $RateGenerators = RateGenerator::select(['RateGeneratorId','RateGeneratorName'])->where($where)->orderBy('RateGeneratorName', 'asc')->lists('RateGeneratorName','RateGeneratorId');
        if(!empty($RateGenerators)){
            $RateGenerators = [''=>'Select'] + $RateGenerators;
        }
        return $RateGenerators;
    }
}