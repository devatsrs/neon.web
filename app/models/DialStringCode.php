<?php

class DialStringCode extends \Eloquent {

	// Add your validation rules here
	/*
    public static $rules = [
            'DialString' => 'required',
            'ChargeCode' => 'required',
            'DialStringID' => 'required',
	];*/
    protected $table = 'tblDialStringCode';
    protected  $primaryKey = "DialStringCodeID";
    protected $fillable = [];
    protected $guarded = ['DialStringCodeID'];

    public static $DialStringStorerules = array(
        'DialString' => 'required|unique:tblDialStringCode,DialString',
        'ChargeCode'=>'required'
    );

    public static $DialStringStoreMessages = array(
        'DialString.required' =>'Prefix Field is required',
        'ChargeCode.required' =>'Charge Code Field is required',
        'DialString.unique' =>'Prefix has already be taken'
    );


    public static $DialStringUploadrules = array(
        'selection.DialString' => 'required',
        'selection.ChargeCode'=>'required'
    );

    public static $DialStringUploadMessages = array(
        'selection.DialString.required' =>'Prefix Field is required',
        'selection.ChargeCode.required' =>'Charge Code Field is required'
    );

}