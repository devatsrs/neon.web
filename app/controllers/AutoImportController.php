<?php

class AutoImportController extends \BaseController {

	public function __construct(){

	 }

	public function index()
	{
		$trunks = Trunk::getTrunkDropdownIDList();
		$jobTypes  = JobType::getJobTypeIDListByWhere();
		$jobStatus = JobStatus::getJobStatusIDList();
		$accounts = Account::getAccountIDList();
		return View::make('autoimport.index', compact('trunks','jobStatus','jobTypes', 'accounts'));

	}





	public function ajax_datagrid($type)
	{
		$CompanyID = User::get_companyID();
		$data = Input::all();
		$data['iDisplayStart'] +=1;
		$columns = array('Type','Header Info','Created','JobId','Status');
		$sort_column = $columns[$data['iSortCol_0']];
		$jobType = !empty($data["jobType"]) ? $data["jobType"] : 0;
		$jobStatus = !empty($data["jobStatus"]) ? $data["jobStatus"] : 0;
		$search = !empty($data['Search']) ? $data['Search'] : '';
		$AccountID = !empty($data['AccountID']) ? $data['AccountID'] : 0;
		$query = "call prc_getAutoImportMail (".$jobType.",".$jobStatus.",".$CompanyID.",'".$search."','".$AccountID."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."' ";

		if( isset($data['Export']) && $data['Export'] == 1) {
			$excel_data  = DB::select($query.',1)');
			$excel_data = json_decode(json_encode($excel_data),true);
			foreach($excel_data as $rowno => $rows){
				foreach($rows as $colno => $colval){
					$excel_data[$rowno][$colno] = str_replace( "<br>" , "\n" ,$colval );
				}
			}

			if($type=='csv'){
				$file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/AutoImport.csv';
				$NeonExcel = new NeonExcelIO($file_path);
				$NeonExcel->download_csv($excel_data);
			}elseif($type=='xlsx'){
				$file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/AutoImport.xls';
				$NeonExcel = new NeonExcelIO($file_path);
				$NeonExcel->download_excel($excel_data);
			}
		}
		$query .=',0)';

		\Illuminate\Support\Facades\Log::info($query);

		return DataTableSql::of($query)->make();

	}


	public function GetemailReadById($id){
		$CompanyID = User::get_companyID();
		$result = AutoImport::getEmailById($id);
		$upload_path = CompanyConfiguration::get($CompanyID,'UPLOAD_PATH');
		$amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['AUTOIMPORT_UPLOAD'],'',$CompanyID);
		$path = AmazonS3::unSignedUrl($amazonPath, $CompanyID);
		//echo $fullPath = $upload_path . "/". $amazonPath;
		return Response::json(array("data" => $result[0], "path" => $path));
	}

	public function RecheckMail($id){
		$CompanyID 	= User::get_companyID();
		$emailDetails 	= AutoImport::find($id);
		if($emailDetails && $emailDetails->Attachment!=""){
			$arrAttachment = json_decode($emailDetails->Attachment);
			if(empty($arrAttachment)){
				return Response::json(array("status" => "failed", "message" => " Not Match "));
			}
			$MatchedAttachmentFileNames=[];
			foreach($arrAttachment as $Attachment){
				$path_parts = pathinfo($Attachment->filename);
				if(!array_key_exists("extension", $path_parts)){
					$path_parts["extension"]="";
				}
				if (in_array(strtolower($path_parts["extension"]), array('xls','csv','xlsx') )) {
					$MatchedAttachmentFileNames[strtolower($path_parts["filename"])] = $Attachment->filepath;
				}
			}

			$query = "call prc_ImportSettingMatch ( '".$CompanyID."', '".$emailDetails->From."','".addslashes($emailDetails->Subject)."', '".addslashes(implode(", ", array_keys($MatchedAttachmentFileNames)))."' )";
//			Log::info($query);
			$results = DB::select($query);
			if(!empty($results)){
				$countEmails=0;
				$errorEmailMSG="";
				foreach($results as $matchData){

					$data=array();
					if($matchData->Type == 1){
						// For Vendor Rate
						$job_type = "VU" ;
						$data['Trunk'] = $matchData->TrunkID;
						$data["AccountID"] = $matchData->TypePKID;
						$data['codedeckid'] = VendorTrunk::where(["AccountID" => $matchData->TypePKID,'TrunkID'=>$data['Trunk']])->pluck("CodeDeckId");
						$AccountID = $matchData->TypePKID;
					}else{
						// For RateTable
						$job_type = "RTU" ;
						$ratetable = RateTable::where('RateTableId','=',$matchData->TypePKID)->select("RateTableName")->get();
						$data["ratetablename"] = $ratetable[0]->RateTableName;
						$data["RateTableID"] = $matchData->TypePKID;
						$data['codedeckid']="";
						$AccountID = 0;
					}

					try {
						$options=json_decode($matchData->options);
						$arrOptions=array();
						$arrOptions["skipRows"]=$options->skipRows;
						$arrOptions["importratesheet"]=$options->importratesheet;
						$arrOptions["option"]=$options->option;
						$arrOptions["selection"]=$options->selection;
						$arrOptions["Trunk"]=$matchData->TrunkID;
						$arrOptions["codedeckid"]=$data['codedeckid'];
						$arrOptions["uploadtemplate"]=$matchData->ImportFileTempleteID;
						$arrOptions["checkbox_replace_all"]=$options->Settings->checkbox_replace_all;
						$arrOptions["checkbox_rates_with_effected_from"]=$options->Settings->checkbox_rates_with_effected_from;
						$arrOptions["checkbox_add_new_codes_to_code_decks"]=$options->Settings->checkbox_add_new_codes_to_code_decks;
						$arrOptions["checkbox_review_rates"]=$options->Settings->checkbox_review_rates;
						$arrOptions["radio_list_option"]=$options->Settings->radio_list_option;
						$data['Options'] = json_encode($arrOptions);

						$data['uploadtemplate'] = $matchData->ImportFileTempleteID;
						$fullPath = $MatchedAttachmentFileNames[trim($matchData->lognFileName)];

						$data['full_path'] = $fullPath;
						$data["CompanyID"] = $CompanyID;
						$data['checkbox_replace_all'] = $arrOptions["checkbox_replace_all"];
						$data['checkbox_rates_with_effected_from'] = $arrOptions["checkbox_rates_with_effected_from"];
						$data['checkbox_add_new_codes_to_code_decks'] = $arrOptions["checkbox_add_new_codes_to_code_decks"];
						$data['radio_list_option'] = $arrOptions["radio_list_option"];
						DB::beginTransaction();
						$jobId = Job::CreateAutoImportJob($CompanyID,$job_type,$data);
						DB::commit();

						/* Job Block End */
						$jobID = !empty($jobId) ? $jobId : 0;

						$SaveData = array(
							"AccountID" => $AccountID,
							"AccountName" => $emailDetails->AccountName,
							"Subject" => $emailDetails->Subject,
							"Description" => $emailDetails->Description,
							"Attachment" => $emailDetails->Attachment,
							"To" => $emailDetails->To,
							"From" => $emailDetails->From,
							"CC" => $emailDetails->CC,
							"MailDateTime" => $emailDetails->MailDateTime,
							"MessageId" => $emailDetails->MessageId,
							"created_at" => date('Y-m-d H:i:s'),
							"created_by" => "System",
							"JobID" => $jobID,
							"CompanyID" => $CompanyID
						);
						AutoImport::insert($SaveData);
						$countEmails++;

					}catch (\Exception $e){
						Log::info($query);
						Log::info("Template Not Valid Error:" . $e->getMessage());
						return Response::json(array("status" => "failed", "message" => " Template Not Valid "));
					}
				}

				if($countEmails>0){
					$emailDetails->delete();
					return Response::json(array("status" => "success", "message" => $countEmails." Jobs Locked "));
				}
			}
		}
		return Response::json(array("status" => "failed", "message" => " Not Match "));
	}

	public function GetAttachment($id,$attachmentID){
		$result = AutoImport::find($id);
		if($result)
		{
			$attachments 	=   json_decode($result->Attachment);
			$attachment 	=   $attachments[$attachmentID];
			$FilePath 		=  	AmazonS3::preSignedUrl($attachment->filepath);

			if(file_exists($FilePath)){
				download_file($FilePath);
			}else{
				header('Location: '.$FilePath);
			}
		}
		exit;
	}

}