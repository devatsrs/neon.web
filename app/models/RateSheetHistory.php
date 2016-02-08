<?php

class RateSheetHistory extends \Eloquent {

    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblRateSheetHistory';
    protected $primaryKey = "RateSheetHistoryID";

    public static function AddtoHistory($JobID, $JobType, $JobData) {
        $rules = array(
            'JobID' => 'required',
            'Title' => 'required',
            'Type' => 'required',
            'CreatedBy' => 'required',
        );
        $data["JobID"] = $JobID;
        $data["Title"] = $JobData["Title"];
        $data["Description"] = $JobData["Description"];
        $data["Type"] = $JobType;
        $data["CreatedBy"] = $JobData["CreatedBy"];
        
        $validator = Validator::make($data, $rules);

        if (!$validator->fails()) {
            RateSheetHistory::insert($data);
        }
    }

}