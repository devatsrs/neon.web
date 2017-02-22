<?php

class Dispute extends \Eloquent {
	protected $fillable = [];
	protected $connection = 'sqlsrv2';

	protected $guarded = array("DisputeID");
	protected $table = 'tblDispute';
	protected  $primaryKey = "DisputeID";
	const  PENDING = 0;
	const SETTLED =1;
	const CANCEL  = 2;

	public static $Status = [''=>'Select a Status',self::PENDING=>'Pending',self::SETTLED=>'Settled',self::CANCEL=>'Cancel'];

	public static function reconcile($companyID,$accountID,$StartDate,$EndDate,$GrandTotal,$TotalMinutes){

		$output = array("DisputeTotal"=>0,"DisputeDifference"=>0,"DisputeDifferencePer"=>0, "DisputeMinutes"=>0,"MinutesDifference" =>0, "MinutesDifferencePer" => 0 );

		if ( !empty($accountID) && !empty($StartDate) && !empty($EndDate) ) {

			$query = "call prc_invoice_in_reconcile (" . $companyID . ",".$accountID.",'".$StartDate."','".$EndDate."');";
			$result  = DB::connection('sqlsrvcdr')->select($query);
			$result_array = json_decode(json_encode($result),true);

			$output = Dispute::calculate_dispute($GrandTotal,$result_array[0]["DisputeTotal"],$TotalMinutes,$result_array[0]["DisputeMinutes"] );

			$round_places = get_round_decimal_places($accountID);

			$output["DisputeTotal"] 					= number_format($output["DisputeTotal"] , $round_places , '.' , '' );
			$output["DisputeDifference"] 		= number_format($output["DisputeDifference"] , $round_places , '.' , '' );
			$output["DisputeDifferencePer"] 	= number_format($output["DisputeDifferencePer"] , $round_places , '.' , '');
			$output["MinutesDifferencePer"] 	= number_format($output["MinutesDifferencePer"] , $round_places , '.' , '' );

		}

		return $output;
	}


	public static function calculate_dispute($GrandTotal,$DisputeTotal,$TotalMinutes,$DisputeMinutes){

		if($DisputeTotal > 0 ){

			if($DisputeTotal > $GrandTotal){

				$formula_total = (100 - ($GrandTotal / $DisputeTotal ) * 100);
			}else {

				$formula_total = (100 - ($DisputeTotal / $GrandTotal ) * 100);
			}

		}else{

			$formula_total = 100;
		}
		$DisputeDifference = $GrandTotal - $DisputeTotal;

		$DisputeDifferencePer =  $formula_total;

		if($DisputeMinutes > 0) {

			if ( $DisputeMinutes > $TotalMinutes ){

				$formula_seconds = (100 - ($TotalMinutes / $DisputeMinutes ) * 100);
			}else {

				$formula_seconds = (100 - ($DisputeMinutes / $TotalMinutes ) * 100);
			}

		}else{

			$formula_seconds = 100;
		}

		$MinutesDifference = $TotalMinutes - $DisputeMinutes;

		$MinutesDifferencePer =  $formula_seconds;


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

		$rules = array(
			'CompanyID' => 'required|numeric',
			'AccountID' => 'required|numeric',
			'DisputeAmount' => 'required|numeric',
			'InvoiceType' => 'required|numeric',
		);


		$validator = Validator::make($data, $rules);

		if ($validator->fails()) {

			return json_validator_response($validator);
		}

		if (Input::hasFile('Attachment')){
			$upload_path = CompanyConfiguration::get('UPLOAD_PATH');
			$amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['DISPUTE_ATTACHMENTS'],$data["AccountID"]) ;
			$destinationPath = $upload_path . '/' . $amazonPath;
			$proof = Input::file('Attachment');

			$ext = $proof->getClientOriginalExtension();
			if (in_array(strtolower($ext), array('pdf','png','jpg','gif','xls','csv','xlsx'))) {
				$filename = rename_upload_file($destinationPath,$proof->getClientOriginalName());

				$proof->move($destinationPath,$filename);
				if(!AmazonS3::upload($destinationPath.$filename,$amazonPath)){
					return Response::json(array("status" => "failed", "message" => "Failed to upload."));
				}
				$data['Attachment'] = $amazonPath . $filename;
				$disputeData["Attachment"]             = $data["Attachment"];

			}else{
				$valid['message'] = Response::json(array("status" => "failed", "message" => "Please Upload file with given extensions."));
				return $valid;
			}
		}else{
			unset($data['Attachment']);
		}


		if(isset($data["DisputeID"]) && $data["DisputeID"] > 0 ){

			$disputeData["DisputeID"]  = $data["DisputeID"];
		}

		$disputeData["CompanyID"]               = $data["CompanyID"];
		$disputeData["InvoiceType"]             = $data["InvoiceType"];
		$disputeData["InvoiceNo"]               = $data["InvoiceNo"];
		$disputeData["AccountID"]               = $data["AccountID"];
		$disputeData["DisputeAmount"]           = $data["DisputeAmount"];

		$disputeData["Status"]      = isset($data["Status"])?$data["Status"]:0;
		$disputeData["Notes"]      = isset($data["Notes"])?$data["Notes"]:"";
		$disputeData['created_at']  = date("Y-m-d H:i:s");
		$disputeData['CreatedBy']   = User::get_user_full_name();

		if(!empty($disputeData["DisputeID"]) && $disputeData["DisputeID"] > 0 ) {

			$dispute = Dispute::find($data["DisputeID"]);

			if(!empty($dispute->Attachment)){
				//delete old Attachment file.
				$FilePath =  AmazonS3::preSignedUrl($dispute->Attachment);
				@unlink($FilePath);
			}
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