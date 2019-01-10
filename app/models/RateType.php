<?php

class RateType extends \Eloquent {

   // protected $connection = 'neon_routingengine';
    protected $fillable = [];
    protected $guarded = array('RateTypeID');
    protected $table = 'tblRateType';
    public  $primaryKey = "RateTypeID"; //Used in BasedController
    CONST SLUG_DID = 'did';
    CONST SLUG_VOICECALL = 'voicecall';


    public static function getRateTypeDropDownList(){
        $row=array();
        $row = RateType::where('Active',1)->orderby('RateTypeID','desc')->lists('Title', 'RateTypeID');
        return $row;
    }

    public static function getRateTypeIDBySlug($Slug){
        return RateType::where(['Slug'=>$Slug,'Active'=>1])->pluck('RateTypeID');
    }

    public static function getRateTypeTitleBySlug($Slug){
        return RateType::where(['Slug'=>$Slug,'Active'=>1])->pluck('Title');
    }

}