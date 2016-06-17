<?php

class DialPlanCode extends \Eloquent {

	// Add your validation rules here
	public static $rules = [
            'DialString' => 'required',
            'ChargeCode' => 'required',
            'Description' => 'required',
            'DialPlanID' => 'required',
	];
    protected $table = 'tblDialPlanCode';
    protected  $primaryKey = "DialPlanCodeID";
    protected $fillable = [];
    protected $guarded = ['DialPlanCodeID'];

    public static $DialPlanUploadrules = array(
        'selection.DialString' => 'required',
        'selection.ChargeCode'=>'required',
        'selection.Description'=>'required',
    );

    public static $DialPlanUploadMessages = array(
        'selection.DialString.required' =>'Dial String Field is required',
        'selection.ChargeCode.required' =>'Charge Code Field is required',
        'selection.Description.required' =>'Description Field is required'
    );

}