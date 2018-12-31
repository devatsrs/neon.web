<?php
class RateType  extends \Eloquent {
    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblRateType';
    protected $primaryKey = "RateTypeID";

    public static function getAllTypes()
    {
        $RateTypes = RateType::lists('Title', 'RateTypeID');
        return $RateTypes;
    }
}