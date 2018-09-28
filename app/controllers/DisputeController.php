<?php

class DisputeController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 * GET /products
	 *
	 * @return Response

	 */

	public function ajax_datagrid($type) {

		$data 							 = 		Input::all();
		$CompanyID 						 = 		User::get_companyID();
		$data['iDisplayStart'] 			+=		1;
		$data['InvoiceType'] 			 = 		$data['InvoiceType'] == 'All'?'':$data['InvoiceType'];
		$data['AccountID'] 				 = 		$data['AccountID']!= ''?$data['AccountID']:'NULL';
		$data['InvoiceNo']				 =		$data['InvoiceNo']!= ''?"'".$data['InvoiceNo']."'":'NULL';
		$data['Status'] 				 = 		$data['Status'] != ''?$data['Status']:'NULL';
		$data['p_disputestartdate'] 	 = 		$data['DisputeDate_StartDate']!=''?$data['DisputeDate_StartDate']:'NULL';
		$data['p_disputeenddate'] 	 	 = 		$data['DisputeDate_EndDate']!=''?$data['DisputeDate_EndDate']:'NULL';
		$data['p_disputestart']			 =		'NULL';
		$data['p_disputeend']			 =		'NULL';
		$data['tag']					 =		(isset($data['tag']) && $data['tag']!='')?$data['tag']:'';

		if($data['p_disputestartdate']!='' && $data['p_disputestartdate']!='NULL')
		{
			$data['p_disputestart']		=	"'".$data['p_disputestartdate']."'"; //.' '.$data['p_disputestartTime']."'";
		}
		if($data['p_disputeenddate']!='' && $data['p_disputeenddate']!='NULL')
		{
			$data['p_disputeend']			=	"'".$data['p_disputeenddate']."'"; //.' '.$data['p_disputeendtime']."'";
		}

		if($data['p_disputestart']!='NULL' && $data['p_disputeend']=='')
		{
			$data['p_disputeend'] 			= 	"'".date("Y-m-d H:i:s")."'";
		}

		$columns = array('InvoiceType','AccountName','InvoiceNo','DisputeAmount','Status','created_at', 'CreatedBy','Notes','DisputeID');
		$sort_column = $columns[$data['iSortCol_0']];

		$query = "call prc_getDisputes (".$CompanyID.",".intval($data['InvoiceType']).",".$data['AccountID'].",".$data['InvoiceNo'].",".$data['Status'].",".$data['p_disputestart'].",".$data['p_disputeend'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

		if(isset($data['Export']) && $data['Export'] == 1) {
			$excel_data  = DB::connection('sqlsrv2')->select($query.',1,"'.$data['tag'].'")');
			$excel_data = json_decode(json_encode($excel_data),true);
			if($type=='csv'){
				$file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Dispute.csv';
				$NeonExcel = new NeonExcelIO($file_path);
				$NeonExcel->download_csv($excel_data);
			}elseif($type=='xlsx'){
				$file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Dispute.xls';
				$NeonExcel = new NeonExcelIO($file_path);
				$NeonExcel->download_excel($excel_data);
			}
		}
		$query .=',0,"'.$data['tag'].'")';
		return DataTableSql::of($query,'sqlsrv2')->make();
	}


	public function index()
	{
		Invoice::multiLang_init();
		$id=0;
		$currency = Currency::getCurrencyDropdownList();
		$currency_ids = json_encode(Currency::getCurrencyDropdownIDList());
		$accounts = Account::getAccountIDList();
		$InvoiceTypes =  array(''=>'Select' , Invoice::INVOICE_OUT=>"Sent",Invoice::INVOICE_IN=>"Received");
		$emailTemplates = EmailTemplate::getTemplateArray(array('StaticType'=>EmailTemplate::DYNAMICTEMPLATE));
		$bulk_type = 'disputes';
		return View::make('disputes.index', compact('id','currency','status','accounts','currency_ids','InvoiceTypes','emailTemplates','bulk_type'));

	}

	/**
	 * Show the form for creating a new resource.
	 * GET /products/create
	 *
	 * @return Response
	 */
	public function create(){

		$data = Input::all();
		$data['sendEmail']=0;
		$data['DisputeAttachment']=1;
		$output = Dispute::add_update_dispute($data);

		return $output;

	}


	/**
	 * Update the specified resource in storage.
	 * PUT /products/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		if( $id > 0 ) {

			$data = Input::all();
			$data['sendEmail']=0;
			$data['DisputeAttachment']=1;
			$output = Dispute::add_update_dispute($data);

			return $output;
		}else {

			return Response::json(array("status" => "failed", "message" => "Invalid DisputeID."));
		}
	}

	//not in use
	public function reconcile()
	{
		$data = Input::all();
		$companyID =  User::get_companyID();

		$rules = array(
			'InvoiceNo'=>'required|numeric',
			'AccountID'=>'required|numeric',
		);

		$verifier = App::make('validation.presence');
		$verifier->setConnection('sqlsrvcdr');

		$validator = Validator::make($data, $rules);

		$validator->setPresenceVerifier($verifier);
		if ($validator->fails()) {
			return json_validator_response($validator);
		}

		$Invoice = InvoiceDetail::where("InvoiceNo",$data['InvoiceNo'])->select(['StartDate','EndDate','Price','TotalMinutes'])->first();

		$StartDate = $Invoice->StartDate;
		$EndDate = $Invoice->EndDate;
		$TotalMinutes = $Invoice->TotalMinutes;
		$GrandTotal = $Invoice->Price;
		$accountID = $data['AccountID'];

		$output = Dispute::reconcile($companyID,$accountID,$StartDate,$EndDate,$GrandTotal,$TotalMinutes);

		if(isset($data["DisputeID"]) && $data["DisputeID"] > 0 ){

			$output["DisputeID"]  = $data["DisputeID"];
		}

		return Response::json( array_merge($output, array("status" => "success", "message" => ""  )));
	}


	public function change_status(){

		$data = Input::all();

		$rules = array(
			'Notes'=>'required',
			'DisputeID'=>'required|numeric',
			'Status'=>'required|numeric',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->fails()) {
			return json_validator_response($validator);
		}
		$Dispute = Dispute::findOrFail($data["DisputeID"]);
		$Dispute->Notes = date("Y-m-d H:i:s") .': '. User::get_user_full_name() . ' has Changed Dispute Status to ' . Dispute::$Status[$data["Status"]] .' -:- ' . $data['Notes'] . PHP_EOL.  PHP_EOL .  $Dispute->Notes;
		$Dispute->Status = $data["Status"];

		if ($Dispute->update()) {
			return Response::json(array("status" => "success", "message" => "Dispute Status Successfully Updated"));
		} else {
			return Response::json(array("status" => "failed", "message" => "Failed Updating Dispute Status."));
		}

	}

	public function  download_attachment($id){
		$FileName = Dispute::where(["DisputeID"=>$id])->pluck('Attachment');
		$FilePath =  AmazonS3::preSignedUrl($FileName);
		download_file($FilePath);

	}

	public function view($id){


		$CompanyID = User::get_companyID();
		$query = "call prc_getDisputeDetail ( ". $CompanyID . "," .$id ." )";
		$Dispute_array  = DB::connection('sqlsrv2')->select($query);

		$Dispute = [
		'DisputeID'   => '',
		'InvoiceType'   => '',
		'AccountName'   => '',
		'InvoiceNo'   => '',
		'DisputeAmount'   => '',
		'Notes'   => '',
		'Status'   => '',
		'created_at'   => '',
		'CreatedBy'   => '',
		'Attachment'   => '',
		'updated_at   => '
		];

        if(count($Dispute_array)>0) {

            $Dispute_array = (array) array_shift($Dispute_array);

            $Dispute = [
				'DisputeID'     => $Dispute_array['DisputeID'],
				'InvoiceType'   => $Dispute_array['InvoiceType'],
				'AccountName'   => $Dispute_array['AccountName'],
				'InvoiceNo'   	=> $Dispute_array['InvoiceNo'],
				'DisputeAmount' => $Dispute_array['DisputeAmount'],
				'Notes'   		=> $Dispute_array['Notes'],
				'Status'   		=> $Dispute_array['Status'],
				'created_at'    => $Dispute_array['created_at'],
				'CreatedBy'     => $Dispute_array['CreatedBy'],
				'Attachment'    => $Dispute_array['Attachment'],
				'updated_at'    => $Dispute_array['updated_at'],
			];

		}

 		return View::make('disputes.view', compact('Dispute'));


 	}

	public function disputes_email($id) {
		$Dispute = Dispute::find($id);
		if(!empty($Dispute)) {
			$Account = Account::find($Dispute->AccountID);
			$Currency = Currency::find($Account->CurrencyId);
			$companyID = User::get_companyID();
			$CompanyName = Company::getName();
			if (!empty($Currency)) {
				$Attachment="";
				$templateData	 	 = 	 EmailTemplate::getSystemEmailTemplate($Dispute->CompanyID, Dispute::EMAILTEMPLATE, $Account->LanguageID );
				$data['InvoiceURL']	 =   URL::to('/invoice/'.$Dispute->AccountID.'-'.$Dispute->DisputeID.'/cview?email=#email');
				$Message	 		 =	 EmailsTemplates::SendDisputeSingle($id,'body',$data);
				$Subject	 		 =	 EmailsTemplates::SendDisputeSingle($id,"subject",$data);

				$response_api_extensions 	=    Get_Api_file_extentsions();
				if(isset($response_api_extensions->headers)){ return	Redirect::to('/logout'); 	}
				$response_extensions		=	json_encode($response_api_extensions['allowed_extensions']);
				$max_file_size				=	get_max_file_size();
				$AttachmentURL=$Dispute->Attachment;
				if($AttachmentURL!=''){
					$reversedParts = explode('/', strrev($AttachmentURL), 2);
					$Attachment=strrev($reversedParts[0]);
				}

				if(!empty($Subject) && !empty($Message)){
					$from	 = $templateData->EmailFrom;
					return View::make('disputes.email', compact('Dispute', 'Account', 'Subject','Message','CompanyName','from','response_extensions','max_file_size','Attachment'));
				}
				return Response::json(["status" => "failure", "message" => "Subject or message is empty"]);


			}
		}
	}

	public function bulk_send_dispute_mail(){
		$data = Input::all();
		$companyID = User::get_companyID();
		if(!empty($data['criteria'])){
			$disputeid = $this->getDisputesIdByCriteria($data);
			$disputeid = rtrim($disputeid,',');
			$data['DisputeIDs'] = $disputeid;
			unset($data['criteria']);
		}
		else{
			unset($data['criteria']);
		}

		$jobType = JobType::where(["Code" => 'BDS'])->get(["JobTypeID", "Title"]);
		$jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
		$jobdata["CompanyID"] = $companyID;
		$jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
		$jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
		$jobdata["JobLoggedUserID"] = User::get_userID();
		$jobdata["Title"] =  (isset($jobType[0]->Title) ? $jobType[0]->Title : '');
		$jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
		$jobdata["CreatedBy"] = User::get_user_full_name();
		$jobdata["Options"] = json_encode($data);
		$jobdata["created_at"] = date('Y-m-d H:i:s');
		$jobdata["updated_at"] = date('Y-m-d H:i:s');
		$JobID = Job::insertGetId($jobdata);
		if($JobID){
			return Response::json(array("status" => "success", "message" => "Bulk Dispute Send Job Added in queue to process.You will be notified once job is completed. "));
		}else{
			return Response::json(array("status" => "success", "message" => "Problem Creating Job Bulk Dispute Send."));
		}
	}

	public function getDisputesIdByCriteria($data){
		$companyID = User::get_companyID();
		$criteria = json_decode($data['criteria'],true);
		Log::info(print_r($criteria,true));
		$criteria['InvoiceType'] 		 = 		$criteria['InvoiceType'] == 'All'?'':$criteria['InvoiceType'];
		$criteria['AccountID'] 				 = 		empty($criteria['AccountID'])?'NULL':$criteria['AccountID'];
		$criteria['InvoiceNo']				 =		empty($criteria['InvoiceNo'])?'NULL':$criteria['InvoiceNo'];
		$criteria['Status'] 				 = 		isset($criteria['Status']) && $criteria['Status'] != ''?$criteria['Status']:'NULL';

		$criteria['p_disputestartdate'] 	 = 		$criteria['DisputeDate_StartDate']!=''?$criteria['DisputeDate_StartDate']:'NULL';
		$criteria['p_disputeenddate'] 	 	 = 		$criteria['DisputeDate_EndDate']!=''?$criteria['DisputeDate_EndDate']:'NULL';
		$criteria['p_disputestart']			 =		'NULL';
		$criteria['p_disputeend']			 =		'NULL';
		$data['tag']					 =		(isset($criteria['tag']) && $criteria['tag']!='')?$criteria['tag']:'';

		if($criteria['p_disputestartdate']!='' && $criteria['p_disputestartdate']!='NULL')
		{
			$criteria['p_disputestart']		=	"'".$criteria['p_disputestartdate']."'"; //.' '.$data['p_disputestartTime']."'";
		}
		if($criteria['p_disputeenddate']!='' && $criteria['p_disputeenddate']!='NULL')
		{
			$criteria['p_disputeend']			=	"'".$criteria['p_disputeenddate']."'"; //.' '.$data['p_disputeendtime']."'";
		}

		if($criteria['p_disputestart']!='NULL' && $criteria['p_disputeend']=='')
		{
			$criteria['p_disputeend'] 			= 	"'".date("Y-m-d H:i:s")."'";
		}
		Log::info(print_r($criteria,true));
		$query = "call prc_getDisputes (".$companyID.",".intval($criteria['InvoiceType']).",".$criteria['AccountID'].",".$criteria['InvoiceNo'].",".$criteria['Status'].",".$criteria['p_disputestart'].",".$criteria['p_disputeend'].",'','','','',2,'".$data['tag']."')";
		Log::info($query);

		$exceldatas  = DB::connection('sqlsrv2')->select($query);
		$exceldatas = json_decode(json_encode($exceldatas),true);
		$disputeid='';
		foreach($exceldatas as $exceldata){
			$disputeid.= $exceldata['DisputeID'].',';
		}
		return $disputeid;
	}

	public function send($id){
		if($id){
			set_time_limit(600); // 10 min time limit.
			$CreatedBy = User::get_user_full_name();
			$data = Input::all(); //Log::info(print_r($data,true)); exit;
			$postdata = Input::all();
			$Dispute = Dispute::find($id);
			$Company = Company::find($Dispute->CompanyID);
			$CompanyName = $Company->CompanyName;
			//$InvoiceGenerationEmail = CompanySetting::getKeyVal('InvoiceGenerationEmail');
			$InvoiceCopy = Notification::getNotificationMail(Notification::InvoiceCopy,$Dispute->CompanyID);
			$InvoiceCopy = empty($InvoiceCopy)?$Company->Email:$InvoiceCopy;
			$emailtoCustomer = CompanyConfiguration::get('EMAIL_TO_CUSTOMER',$Dispute->CompanyID);
			if(intval($emailtoCustomer) == 1){
				$CustomerEmail = $data['Email'];
			}else{
				$CustomerEmail = $Company->Email;
			}
			$data['EmailTo'] = explode(",",$CustomerEmail);
			//$data['InvoiceURL'] = URL::to('/invoice/'.$Dispute->AccountID.'-'.$Invoice->InvoiceID.'/cview');
			$data['AccountName'] = Account::find($Dispute->AccountID)->AccountName;
			$data['CompanyName'] = $CompanyName;
			$rules = array(
				'AccountName' => 'required',
				'Subject'=>'required',
				'EmailTo'=>'required',
				'Message'=>'required',
				'CompanyName'=>'required',
			);
			$validator = Validator::make($data, $rules);
			if ($validator->fails()) {
				return json_validator_response($validator);
			}

            /*
             * Send to Customer
             * */
			//$status = sendMail('emails.invoices.send',$data);
			$status = 0;
			$body = '';
			$CustomerEmails = $data['EmailTo'];
            $attachment=$Dispute->Attachment;
			foreach($CustomerEmails as $singleemail){
				$singleemail = trim($singleemail);
				if (filter_var($singleemail, FILTER_VALIDATE_EMAIL)) {

					$data['EmailTo'] 		= 	$singleemail;
					//$data['InvoiceURL']		=   URL::to('/invoice/'.$Invoice->AccountID.'-'.$Invoice->InvoiceID.'/cview?email='.$singleemail);
					$body					=	EmailsTemplates::ReplaceEmail($singleemail,$postdata['Message']);
					$data['Subject']		=	$postdata['Subject'];
					//$InvoiceBillingClass =	 Invoice::GetInvoiceBillingClass($Invoice);

					if($attachment && $attachment!=''){
						$data['AttachmentPaths']= array([
							"filename"=>pathinfo($attachment, PATHINFO_BASENAME),
							"filepath"=>$attachment
						]);
					}

					//attachment Form Start

					$attachmentsinfo        =	$data['attachmentsinfo'];
					if(!empty($attachmentsinfo) && count($attachmentsinfo)>0){
						$files_array = json_decode($attachmentsinfo,true);
					}

					if(!empty($files_array) && count($files_array)>0) {
						$FilesArray = array();
						foreach($files_array as $key=> $array_file_data){
							$file_name  = basename($array_file_data['filepath']);
							$amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['EMAIL_ATTACHMENT'],'',$Dispute->CompanyID);
							$destinationPath = CompanyConfiguration::get('UPLOAD_PATH',$Dispute->CompanyID) . '/' . $amazonPath;

							if (!file_exists($destinationPath)) {
								mkdir($destinationPath, 0777, true);
							}
							copy($array_file_data['filepath'], $destinationPath . $file_name);
							if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath,$Dispute->CompanyID)) {
								return Response::json(array("status" => "failed", "message" => "Failed to upload file." ));
							}
							$data['AttachmentPaths'][] = array ("filename"=>$array_file_data['filename'],"filepath"=>$amazonPath . $file_name);
							@unlink($array_file_data['filepath']);
						}
						//$data['AttachmentPaths']		=	$FilesArray;
						//array_push($data['AttachmentPaths'],$FilesArray);
					}
					
					//attachment Form End

					if(isset($postdata['email_from']) && !empty($postdata['email_from']))
					{
						$data['EmailFrom']	=	$postdata['email_from'];
					}else{
						$data['EmailFrom']	=	EmailsTemplates::GetEmailTemplateFrom(Invoice::EMAILTEMPLATE);
					}

					$status 				= 	$this->sendDisputesMail($body,$data,0);

					//$body 				=   View::make('emails.invoices.send',compact('data'))->render();  // to store in email log
				}
			}

			$status['status'] = "success";

			return Response::json(array("status" => $status['status'], "message" => "".$status['message']));
		}else{
			return Response::json(["status" => "failure", "message" => "Problem Sending Invoice"]);
		}
	}

	function sendDisputesMail($view,$data,$type=1){

		$status 		= 	array('status' => 0, 'message' => 'Something wrong with sending mail.');
		if(isset($data['email_from'])){
			$data['EmailFrom'] = $data['email_from'];
		}
		if(is_array($data['EmailTo']))
		{
			$status 			= 	sendMail($view,$data,$type);
		}
		else
		{
			if(!empty($data['EmailTo']))
			{
				$data['EmailTo'] 	= 	trim($data['EmailTo']);
				$status 			= 	sendMail($view,$data,0);
			}
		}
		return $status;
	}

	public function delete($id) {
		if( intval($id) > 0){
			try {
				$Dispute=Dispute::find($id);
				AmazonS3::delete($Dispute->Attachment);
				$result = $Dispute->delete();
				if ($result) {
					return Response::json(array("status" => "success", "message" => "Dispute Successfully Deleted"));
				} else {
					return Response::json(array("status" => "failed", "message" => "Problem Deleting Dispute."));
				}
			} catch (Exception $ex) {
				Log::info("========== Exception Generated While Deleting Dispute ==========");
				Log::info(print_r($ex,true));
				return Response::json(array("status" => "failed", "message" => "Dispute is in Use, You cant delete this Dispute."));
			}
		}else{
			return Response::json(array("status" => "failed", "message" => "Dispute is in Use, You cant delete this Dispute."));
		}
	}


}