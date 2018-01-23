<?php
class CurrencyConversion extends \Eloquent {

    protected $table = 'tblCurrencyConversion';
    protected $primaryKey = "ConversionID";
    protected $fillable = [];
    protected $guarded = ['ConversionID'];
    static protected $enable_cache = true;
    public static $rules = array(
        'Name' => 'required',
        'CurrencyFromID' => 'required',
        'CurrencyToID' => 'required',
        'Value' => 'required|numeric',
    );

    public static function checkForeignKeyById($id) {
        /*
         * Tables To Check Foreign Key before Delete.
         * */


        if( $id < 0 ){
            return true;
        }else{
            return false;
        }

    }
    public static function isDefined($CompanyID,$CurrencyToID)
    {
        $currencytocount = CurrencyConversion::where(array('CompanyID' => $CompanyID, 'CurrencyID' => $CurrencyToID))->count();
        if ($currencytocount > 0) {
            return true;
        }
        return false;
    }

}