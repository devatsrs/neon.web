<?php

class PaymentUploadTemplate extends \Eloquent {
	protected $fillable = [];
	protected $guarded = array();
	protected $table = 'tblPaymentUploadTemplate';
	protected $primaryKey = "PaymentUploadTemplateID";

	public static function getTemplateIDList(){
		$row = PaymentUploadTemplate::where(['CompanyID'=>User::get_companyID()])->orderBy('Title')->lists('Title', 'PaymentUploadTemplateID');
		$row = array(""=> "Select")+$row;
		return $row;
	}
}