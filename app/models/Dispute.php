<?php

class Dispute extends \Eloquent {
	protected $fillable = [];
	protected $connection = 'sqlsrv2';

	protected $guarded = array("DisputeID");
	protected $table = 'tblDispute';
	protected  $primaryKey = "DisputeID";
	const  PENDING = 0;
	const SETTELED =1;
	const CANCEL  = 2;

	public static $Status = [''=>'Select a Status',self::PENDING=>'Pending',self::SETTELED=>'Setteled',self::CANCEL=>'Cancel'];

	public static function reconcile($companyID,$accountID,$StartDate,$EndDate,$GrandTotal,$TotalMinutes){

		$output = array("total"=>0,"total_difference"=>0,"total_difference_per"=>0, "minutes"=>0,"minutes_difference" =>0, "minutes_difference_per" => 0 );

		if ( !empty($accountID) && !empty($StartDate) && !empty($EndDate) ) {

			$query = "call prc_invoice_in_reconcile (" . $companyID . ",".$accountID.",'".$StartDate."','".$EndDate."');";
			$result  = DB::connection('sqlsrvcdr')->select($query);
			$result_array = json_decode(json_encode($result),true);

			$output = Dispute::calculate_dispute($GrandTotal,$result_array[0]["DisputeTotal"],$TotalMinutes,$result_array[0]["DisputeMinutes"] );

		}

		return $output;
	}
	public static function calculate_dispute($GrandTotal,$DisputeTotal,$TotalMinutes,$DisputeMinutes){

		$DisputeTotal = number_format($DisputeTotal,4);
		$DisputeDifference = number_format($GrandTotal - $DisputeTotal,4);
		$total_average = $GrandTotal + $DisputeTotal / 2;

		if($total_average > 0){
			$DisputeDifferencePer =  number_format(abs(($DisputeDifference / $total_average) * 100),4);
		}else{
			$DisputeDifferencePer =  0;
		}

		$DisputeMinutes = $DisputeMinutes;
		$MinutesDifference = $TotalMinutes - $DisputeMinutes;
		$minutes_average = $TotalMinutes + $DisputeMinutes / 2;

		if($minutes_average > 0){
			$MinutesDifferencePer =  number_format(abs(($MinutesDifference / $minutes_average) * 100),4);
		}else{
			$MinutesDifferencePer = 0;
		}

		return array(

			"DisputeTotal" => $DisputeTotal,
			"DisputeDifference" => $DisputeDifference,
			"DisputeDifferencePer" => $DisputeDifferencePer,

			"DisputeMinutes" => $DisputeMinutes,
			"MinutesDifference" => $MinutesDifference,
			"MinutesDifferencePer" => $MinutesDifferencePer,
		);

	}

	public static function add_update_dispute($data= array()) {

		$data['CompanyID'] =  User::get_companyID();

		$data['DisputeDifferencePer'] = str_replace( "%" , "" , $data['DisputeDifferencePer']);
		$data['MinutesDifferencePer'] = str_replace( "%" , "" , $data['MinutesDifferencePer']);

		$rules = array(
			'CompanyID' => 'required|numeric',
			'InvoiceID' => 'required|numeric',
			'DisputeTotal' => 'required|numeric',
			'DisputeDifference' => 'required|numeric',
			'DisputeDifferencePer' => 'required|numeric',
			'DisputeMinutes' => 'required|numeric',
			'MinutesDifference' => 'required|numeric',
			'MinutesDifferencePer' => 'required|numeric',
		);


		$validator = Validator::make($data, $rules);

		if ($validator->fails()) {

			return json_validator_response($validator);
		}

		if(isset($data["DisputeID"]) && $data["DisputeID"] > 0 ){

			$disputeData["DisputeID"]  = $data["DisputeID"];
		}

		$disputeData["CompanyID"]               = $data["CompanyID"];
		$disputeData["InvoiceID"]               = $data["InvoiceID"];
		$disputeData["DisputeTotal"]            = $data["DisputeTotal"];
		$disputeData["DisputeDifference"]       = $data["DisputeDifference"];
		$disputeData["DisputeDifferencePer"]    = $data["DisputeDifferencePer"];

		$disputeData["DisputeMinutes"]          = $data["DisputeMinutes"];
		$disputeData["MinutesDifference"]       = $data["MinutesDifference"];
		$disputeData["MinutesDifferencePer"]    = $data["MinutesDifferencePer"];

		$disputeData["Status"]      = isset($data["Status"])?$data["Status"]:0;
		$disputeData["Notes"]      = isset($data["Notes"])?$data["Notes"]:"";
		$disputeData['created_at']  = date("Y-m-d H:i:s");
		$disputeData['CreatedBy']   = User::get_user_full_name();

		if(!empty($disputeData["DisputeID"]) && $disputeData["DisputeID"] > 0 ) {

			if(Dispute::find($data["DisputeID"])->update($disputeData) ) {

				return Response::json(array("status" => "success", "message" => "Dispute updated successfully."));

			} else {

				return Response::json(array("status" => "failed", "message" => "Failed to updated dispute."));

			}

		}else if ( Dispute::insert($disputeData) ) {

			return Response::json(array("status" => "success", "message" => "Dispute inserted successfully."));

		} else {

			return Response::json(array("status" => "failed", "message" => "Failed to updated dispute."));
		}

	}
}