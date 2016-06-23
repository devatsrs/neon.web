<?php

class DialStringCode extends \Eloquent {

	// Add your validation rules here
	public static $rules = [
            'DialString' => 'required',
            'ChargeCode' => 'required',
            'Description' => 'required',
            'DialStringID' => 'required',
	];
    protected $table = 'tblDialStringCode';
    protected  $primaryKey = "DialStringCodeID";
    protected $fillable = [];
    protected $guarded = ['DialStringCodeID'];

    public static $DialStringUploadrules = array(
        'selection.DialString' => 'required',
        'selection.ChargeCode'=>'required',
        'selection.Description'=>'required',
    );

    public static $DialStringUploadMessages = array(
        'selection.DialString.required' =>'Dial String Field is required',
        'selection.ChargeCode.required' =>'Charge Code Field is required',
        'selection.Description.required' =>'Description Field is required'
    );

}