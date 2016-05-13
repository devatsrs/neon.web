<?php

class CDRController extends BaseController {

    
    public function __construct() {

    }


    /** CDR Upload
     * @return mixed
     * @TODO: name need to fix for upload and show
     */
    public function index() {
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $UploadTemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplate::TEMPLATE_CDR);
        $trunks = Trunk::getTrunkDropdownIDList();
        $trunks = $trunks+array(0=>'Find From CustomerPrefix');
        return View::make('cdrupload.upload',compact('dashboardData','account','gateway','UploadTemplate','trunks'));
    }
    public function upload(){
            $data = Input::all();
            $histdata = array();
            $CompanyID = User::get_companyID();
            $rules = array(
                'CompanyGatewayID' => 'required',
                'AccountID' => 'required',
            );
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if (Input::hasFile('excel')) {
            $upload_path = Config::get('app.upload_path');
            $excel = Input::file('excel');
            // ->move($destinationPath);
            $ext = $excel->getClientOriginalExtension();
            if (in_array($ext, array("csv", "xls", "xlsx"))) {
                $file_name = GUID::generate() . '.' . $excel->getClientOriginalExtension();
                $excel->move($upload_path,$file_name);
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['CDR_UPLOAD']) ;
                if(!AmazonS3::upload($upload_path.'/'.$file_name,$amazonPath)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                if($data["AccountID"] >0 ){
                   $account = Account::find($data["AccountID"]);
                    if($account->CDRType == ''){
                        return Response::json(array("status" => "failed", "message" => "Setup CDR Format in Account edit"));
                    }
                }
                $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
                $jobType = JobType::where(["Code" => 'CDR'])->get(["JobTypeID", "Title"]);
                $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
                $histdata['CompanyID'] = $jobdata["CompanyID"] = $CompanyID;
                $histdata['AccountID']= $jobdata["AccountID"] = $account->AccountID;
                $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
                $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
                $jobdata["JobLoggedUserID"] = User::get_userID();
                $jobdata["Title"] = Account::getCompanyNameByID($data["AccountID"]) . ' ' . (isset($jobType[0]->Title) ? $jobType[0]->Title : '');
                $jobdata["Description"] = Account::getCompanyNameByID($data["AccountID"]) . ' ' . isset($jobType[0]->Title) ? $jobType[0]->Title : '';
                $histdata['CreatedBy'] = $jobdata["CreatedBy"] = User::get_user_full_name();
                $jobdata["Options"] = json_encode($data);
                $jobdata["updated_at"] = date('Y-m-d H:i:s');
                $JobID = Job::insertGetId($jobdata);
                /*$histdata['CompanyGatewayID'] = $data['CompanyGatewayID'];
                $histdata['StartDate'] = $data['StartDate'];
                $histdata['EndDate'] = $data['EndDate'];
                $histdata['created_at'] = date('Y-m-d H:i:s');

                CDRUploadHistory::insert($histdata);*/


                $jobfiledata["JobID"] = $JobID;
                $jobfiledata["FileName"] = basename($fullPath);
                $jobfiledata["FilePath"] = $fullPath;
                $jobfiledata["HttpPath"] = 0;
                $jobfiledata["CreatedBy"] = User::get_user_full_name();
                $jobfiledata["updated_at"] = date('Y-m-d H:i:s');
                $JobFileID = JobFile::insertGetId($jobfiledata);
                return json_encode(["status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed. "]);
            } else {
                return json_encode(array("status" => "failed", "message" => "Please upload excel/csv file only."));
            }
        } else {
                return json_encode(array("status" => "failed", "message" => "Please upload excel/csv file <5MB."));
        }

    }
    public function bulk_upload(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $histdata = array();
        /** not required*/
        /*$rules = array(
            'CompanyGatewayID' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }*/
        if (Input::hasFile('excel')) {
            $upload_path = Config::get('app.upload_path');
            $excel = Input::file('excel');
            // ->move($destinationPath);
            $ext = $excel->getClientOriginalExtension();
            if (in_array($ext, array("csv", "xls", "xlsx"))) {
                $file_name = GUID::generate() . '.' . $excel->getClientOriginalExtension();
                $excel->move($upload_path,$file_name);
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['CDR_UPLOAD']) ;
                if(!AmazonS3::upload($upload_path.'/'.$file_name,$amazonPath)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
                $jobType = JobType::where(["Code" => 'CDR'])->get(["JobTypeID", "Title"]);
                $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
                $histdata['CompanyID']= $jobdata["CompanyID"] = $CompanyID;
                $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
                $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
                $jobdata["JobLoggedUserID"] = User::get_userID();
                $jobdata["Title"] =  (isset($jobType[0]->Title) ? $jobType[0]->Title : '');
                $jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
                $histdata['CreatedBy']= $jobdata["CreatedBy"] = User::get_user_full_name();
                $jobdata["Options"] = json_encode($data);
                $jobdata["updated_at"] = date('Y-m-d H:i:s');
                $JobID = Job::insertGetId($jobdata);
                /*$histdata['CompanyGatewayID'] = $data['CompanyGatewayID'];
                $histdata['StartDate'] = $data['StartDate'];
                $histdata['EndDate'] = $data['EndDate'];
                $histdata['created_at'] = date('Y-m-d H:i:s');

                CDRUploadHistory::insert($histdata);*/


                $jobfiledata["JobID"] = $JobID;
                $jobfiledata["FileName"] = basename($fullPath);
                $jobfiledata["FilePath"] = $fullPath;
                $jobfiledata["HttpPath"] = 0;
                $jobfiledata["CreatedBy"] = User::get_user_full_name();
                $jobfiledata["updated_at"] = date('Y-m-d H:i:s');
                $JobFileID = JobFile::insertGetId($jobfiledata);
                return json_encode(["status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed. "]);
            } else {
                return json_encode(array("status" => "failed", "message" => "Please upload excel/csv file only."));
            }
        } else {
            return json_encode(array("status" => "failed", "message" => "Please upload excel/csv file <5MB."));
        }

    }
    public function download_sample_excel_file($type){
        $filePath = '';
        if($type == 'detail'){
            $filePath =  public_path() .'/uploads/sample_upload/CDRDetailUploadSample.csv';
        }else if($type == 'summary'){
            $filePath =  public_path() .'/uploads/sample_upload/CDRSummaryUploadSample.csv';
        }
        download_file($filePath);
    }
    public function get_accounts($CompanyGatewayID){
        $account=GatewayAccount::getAccountNameByGatway($CompanyGatewayID);
        $html_text = '';
        foreach($account as $accountid =>$account_name){
            $html_text .= '<option value="' .$accountid. '">'.$account_name.'</option>';
        }
        echo $html_text;
    }
    public function show(){
        $gateway = CompanyGateway::getCompanyGatewayIdList();
		 $companyID 				= 	User::get_companyID();
		$DefaultCurrencyID    	=   Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $rate_cdr = array();
        $Settings = CompanyGateway::where(array('Status'=>1,'CompanyID'=>User::get_companyID()))->lists('Settings', 'CompanyGatewayID');
        foreach($Settings as $CompanyGatewayID => $Setting){
            $Setting = json_decode($Setting);
            if(isset($Setting->RateCDR) && $Setting->RateCDR == 1){
                $rate_cdr[$CompanyGatewayID] =1;
            }else{
                $rate_cdr[$CompanyGatewayID] =0;
            }
        }
		 $accounts = Account::getAccountIDList();
        return View::make('cdrupload.show',compact('dashboardData','account','gateway','rate_cdr','DefaultCurrencyID','accounts'));
    }
	
		public function ajax_datagrid_total($type)
		{
			
			$data						 =   Input::all();
			$data['iDisplayStart'] 		 =	0;
			$data['iDisplayStart'] 		+=	1;
			$data['iSortCol_0']			 =  0;
			$data['sSortDir_0']			 =  strtoupper('desc');
			$companyID 					 =	 User::get_companyID();
			$columns 					 = 	 array('AccountName','connect_time','disconnect_time','duration','cost','cli','cld');
			$sort_column 				 = 	 $columns[$data['iSortCol_0']];
			$data['zerovaluecost'] 	 	 =   $data['zerovaluecost']== 'true'?1:0;
			$data['CurrencyID'] 		 = 	 empty($data['CurrencyID'])?'0':$data['CurrencyID'];
	
			
			$query = "call prc_GetCDR (".$companyID.",".(int)$data['CompanyGatewayID'].",'".$data['StartDate']."','".$data['EndDate']."',".(int)$data['AccountID'].",'".$data['CDRType']."' ,'".$data['CLI']."','".$data['CLD']."',".$data['zerovaluecost'].",".$data['CurrencyID'].", ".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
	
			$result   = DataTableSql::of($query,'sqlsrv2')->getProcResult(array('DataGrid','SumData'));
			
			$result4  = array(
				"totalcount"=>$result['data']['SumData'][0]->totalcount,
				"total_billed_duration"=>$result['data']['SumData'][0]->total_billed_duration,
				"total_cost"=>$result['data']['SumData'][0]->total_cost
			);
			
			return json_encode($result4,JSON_NUMERIC_CHECK);
		
		}
	
    public function ajax_datagrid($type){
        $data						 =   Input::all();
        $data['iDisplayStart'] 		+=	 1;
        $companyID 					 =	 User::get_companyID();
        $columns 					 = 	 array('AccountName','connect_time','disconnect_time','billed_duration','cost','cli','cld');
        $sort_column 				 = 	 $columns[$data['iSortCol_0']];
		$data['zerovaluecost'] 	 	 =   $data['zerovaluecost']== 'true'?1:0;
		$data['CurrencyID'] 		 = 	 empty($data['CurrencyID'])?'0':$data['CurrencyID'];

		
        $query = "call prc_GetCDR (".$companyID.",".(int)$data['CompanyGatewayID'].",'".$data['StartDate']."','".$data['EndDate']."',".(int)$data['AccountID'].",'".$data['CDRType']."' ,'".$data['CLI']."','".$data['CLD']."',".$data['zerovaluecost'].",".$data['CurrencyID'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/CDR.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/CDR.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

            /*Excel::create('CDR', function ($excel) use ($excel_data) {
                $excel->sheet('CDR', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
         $query .=',0)';
        return DataTableSql::of($query, 'sqlsrv2')->make();
    }
	
    public function delete_cdr(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $query = "call prc_DeleteCDR (".$companyID.",'".(int)$data['CompanyGatewayID']."','".$data['StartDate']."','".$data['EndDate']."','".(int)$data['AccountID']."')";
        $results = DB::connection('sqlsrv2')->statement($query);
        if ($results) {
            return Response::json(array("status" => "success", "message" => "CDR Successfully Cleared."));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Clearing CDR."));
        }

    }
    public function cdr_recal() {
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        return View::make('cdrupload.cdrrecal',compact('dashboardData','account','gateway'));
    }

    public function rate_cdr(){

        $data = Input::all();
        $CompanyID = User::get_companyID();
        $histdata = array();
        $rules = array(
            'CompanyGatewayID' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $jobType = JobType::where(["Code" => 'RCC'])->get(["JobTypeID", "Title"]);
        $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
        $histdata['CompanyID'] = $jobdata["CompanyID"] = $CompanyID;
        $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
        $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
        $jobdata["JobLoggedUserID"] = User::get_userID();
        $jobdata["Title"] = (isset($jobType[0]->Title) ? $jobType[0]->Title : '');
        $jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
        $histdata['CreatedBy'] = $jobdata["CreatedBy"] = User::get_user_full_name();
        $jobdata["Options"] = json_encode($data);
        $jobdata["updated_at"] = date('Y-m-d H:i:s');
        $JobID = Job::insertGetId($jobdata);
        if ($JobID) {
            return array("status" => "success", "message" => "Job Logged Successfully");
        } else {
            return array("status" => "failed", "message" => "Job Insertion Error");
        }
    }
    public function ajaxfilegrid(){
        try {
            $data = Input::all();
            $file_name = $data['TemplateFile'];
            $grid = getFileContent($file_name, $data);
            if ($data['FileUploadTemplateID'] > 0) {
                $FileUploadTemplate = FileUploadTemplate::find($data['FileUploadTemplateID']);
                $grid['FileUploadTemplate'] = json_decode(json_encode($FileUploadTemplate), true);
                //$grid['FileUploadTemplate']['Options'] = json_decode($FileUploadTemplate->Options,true);
            }
            $grid['FileUploadTemplate']['Options'] = array();
            $grid['FileUploadTemplate']['Options']['option'] = $data['option'];
            $grid['FileUploadTemplate']['Options']['selection'] = $data['selection'];

            return Response::json(array("status" => "success", "message" => "data refreshed", "data" => $grid));
        }catch (Exception $e){
            return Response::json(array("status" => "failed", "message" => $e->getMessage()));
        }
    }
    public function storeTemplate() {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        if(isset($data['FileUploadTemplateID']) && $data['FileUploadTemplateID']>0) {
            $rules = array('TemplateName' => 'required|unique:tblFileUploadTemplate,Title,'.$data['FileUploadTemplateID'].',FileUploadTemplateID',
                'TemplateFile' => 'required',
                );
        }else{
            $rules = array('TemplateName' => 'required|unique:tblFileUploadTemplate,Title,NULL,FileUploadTemplateID',
                'TemplateFile' => 'required',
                );
        }
        $rules['Account'] = 'required';
        $rules['Authentication'] = 'required';
        if($data['RateFormat'] == Company::CHARGECODE) {
            $rules['ChargeCode'] = 'required';
        }
        if(!empty($data['selection']['ChargeCode'])){
            $data['ChargeCode'] = $data['selection']['ChargeCode'];
        }
        if(!empty($data['selection']['Account'])){
            $data['Account'] = $data['selection']['Account'];
        }
        if(!empty($data['selection']['Authentication'])){
            $data['Authentication'] = $data['selection']['Authentication'];
        }
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = basename($data['TemplateFile']);

        $temp_path = getenv('TEMP_PATH').'/';
        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['TEMPLATE_FILE']);
        $amazonCDRPath = AmazonS3::generate_upload_path(AmazonS3::$dir['CDR_UPLOAD']);
        $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;
        $destinationCDRPath = getenv("UPLOAD_PATH") . '/' . $amazonCDRPath;
        copy($temp_path.$file_name,$destinationPath.$file_name);
        copy($temp_path.$file_name,$destinationCDRPath.$file_name);
        if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath)){
            return Response::json(array("status" => "failed", "message" => "Failed to upload template sample file."));
        }
        $save = ['CompanyID'=>$CompanyID,'Title'=>$data['TemplateName'],'TemplateFile'=>$amazonPath.$file_name];
        $save['created_by'] = User::get_user_full_name();
        $option["option"]= $data['option'];//['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
        $option["selection"] = $data['selection'];//['connect_time'=>$data['connect_time'],'disconnect_time'=>$data['disconnect_time'],'billed_duration'=>$data['billed_duration'],'duration'=>$data['duration'],'cld'=>$data['cld'],'cli'=>$data['cli'],'Account'=>$data['Account'],'cost'=>$data['cost']];
        $save['Options'] = json_encode($option);
        $save['Type'] = FileUploadTemplate::TEMPLATE_CDR;
        if(isset($data['FileUploadTemplateID']) && $data['FileUploadTemplateID']>0) {
            $template = FileUploadTemplate::find($data['FileUploadTemplateID']);
            $template->update($save);
        }else {/**/
            $template = FileUploadTemplate::create($save);
        }
        if ($template) {
            //Inserting Job Log
            $data['FileUploadTemplateID'] = $template->FileUploadTemplateID;
            $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
            $data['full_path'] = $fullPath;
            $jobType = JobType::where(["Code" => 'CDR'])->get(["JobTypeID", "Title"]);
            $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
            $histdata['CompanyID']= $jobdata["CompanyID"] = $CompanyID;
            $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
            $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
            $jobdata["JobLoggedUserID"] = User::get_userID();
            $jobdata["Title"] =  (isset($jobType[0]->Title) ? $jobType[0]->Title : '');
            $jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
            $histdata['CreatedBy']= $jobdata["CreatedBy"] = User::get_user_full_name();
            $jobdata["Options"] = json_encode($data);
            $jobdata["updated_at"] = date('Y-m-d H:i:s');
            $JobID = Job::insertGetId($jobdata);
            /*$histdata['CompanyGatewayID'] = $data['CompanyGatewayID'];
            $histdata['StartDate'] = $data['StartDate'];
            $histdata['EndDate'] = $data['EndDate'];
            $histdata['created_at'] = date('Y-m-d H:i:s');

            CDRUploadHistory::insert($histdata);*/


            $jobfiledata["JobID"] = $JobID;
            $jobfiledata["FileName"] = basename($fullPath);
            $jobfiledata["FilePath"] = $fullPath;
            $jobfiledata["HttpPath"] = 0;
            $jobfiledata["Options"] = json_encode($data);
            $jobfiledata["CreatedBy"] = User::get_user_full_name();
            $jobfiledata["updated_at"] = date('Y-m-d H:i:s');
            $JobFileID = JobFile::insertGetId($jobfiledata);
            return Response::json(array("status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed."));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Template."));
        }
    }

    public function check_upload()
    {
        try {
            $data = Input::all();
            $rules = array(
                'CompanyGatewayID' => 'required',
            );
            if($data['RateCDR']){
                $rules['TrunkID'] = 'required';
                $rules['RateFormat'] = 'required';
            }
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if (Input::hasFile('excel')) {
                $upload_path = getenv('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array($ext, array("csv", "xls", "xlsx"))) {
                    $file_name = GUID::generate() . '.' . $excel->getClientOriginalExtension();
                    $excel->move($upload_path, $file_name);
                    $file_name = $upload_path . '/' . $file_name;
                } else {
                    return Response::json(array("status" => "failed", "message" => "Please select excel or csv file."));
                }
            } else if (isset($data['TemplateFile'])) {
                $file_name = $data['TemplateFile'];
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select a file."));
            }
            if ($data['FileUploadTemplateID'] > 0) {
                $FileUploadTemplate = FileUploadTemplate::find($data['FileUploadTemplateID']);
                $options = json_decode($FileUploadTemplate->Options, true);
                $data['Delimiter'] = $options['option']['Delimiter'];
                $data['Enclosure'] = $options['option']['Enclosure'];
                $data['Escape'] = $options['option']['Escape'];
                $data['Firstrow'] = $options['option']['Firstrow'];
            }
		 
            if (!empty($file_name)) {
                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;
                $grid['filename'] = $file_name;
                if (!empty($FileUploadTemplate)) {
                    $grid['FileUploadTemplate'] = json_decode(json_encode($FileUploadTemplate), true);
                    $grid['FileUploadTemplate']['Options'] = json_decode($FileUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "message" => "file uploaded", "data" => $grid));
            }
        } catch (Exception $e) {
            return Response::json(array("status" => "failed", "message" => $e->getMessage()));
        }
    }
    public function vendorcdr_show(){
		 $companyID 				= 	User::get_companyID();
		$DefaultCurrencyID    	=   Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        return View::make('cdrupload.vendorcdr',compact('gateway','DefaultCurrencyID'));
    }
	
		public function ajax_datagrid_vendorcdr_total($type)
		{
				
			$data 							 =   Input::all();
			$data['iDisplayStart'] 		 	 =	 0;
			$data['iDisplayStart'] 			+=	 1;
			$data['iSortCol_0']			 	 =   0;
			$data['sSortDir_0']				 =   strtoupper('desc');
			$companyID 						 = 	 User::get_companyID();
			$columns 						 = 	 array('AccountName','connect_time','disconnect_time','billed_duration','selling_cost','buying_cost','cli','cld');
			$sort_column 				 	 = 	 $columns[$data['iSortCol_0']];
			$data['zerovaluebuyingcost']	 =   $data['zerovaluebuyingcost']== 'true'?1:0;		
			$data['CurrencyID'] 		 	 = 	 empty($data['CurrencyID'])?'0':$data['CurrencyID'];
			
			$query = "call prc_GetVendorCDR (".$companyID.",".(int)$data['CompanyGatewayID'].",'".$data['StartDate']."','".$data['EndDate']."',".(int)$data['AccountID'].",'".$data['CLI']."','".$data['CLD']."',".$data['zerovaluebuyingcost'].",".$data['CurrencyID'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
	
			$result   = DataTableSql::of($query,'sqlsrv2')->getProcResult(array('DataGrid','SumData'));
			Log::info($result['data']);
			$result4  = array(
				"totalcount"=>$result['data']['SumData'][0]->totalcount,
				"total_billed_duration"=>$result['data']['SumData'][0]->total_billed_duration,
				"total_cost"=>$result['data']['SumData'][0]->total_cost
			);
			
			return json_encode($result4,JSON_NUMERIC_CHECK);
		}
		
    public function ajax_datagrid_vendorcdr($type){
        $data 							 =   Input::all();
        $data['iDisplayStart'] 			+=	 1;
        $companyID 						 = 	 User::get_companyID();
        $columns 						 = 	 array('AccountName','connect_time','disconnect_time','billed_duration','selling_cost','buying_cost','cli','cld');
        $sort_column 				 	 = 	 $columns[$data['iSortCol_0']];
		$data['zerovaluebuyingcost']	 =   $data['zerovaluebuyingcost']== 'true'?1:0;		
		$data['CurrencyID'] 		 	 = 	 empty($data['CurrencyID'])?'0':$data['CurrencyID'];
		
        $query = "call prc_GetVendorCDR (".$companyID.",".(int)$data['CompanyGatewayID'].",'".$data['StartDate']."','".$data['EndDate']."',".(int)$data['AccountID'].",'".$data['CLI']."','".$data['CLD']."',".$data['zerovaluebuyingcost'].",".$data['CurrencyID'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Vendor CDR.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Vendor CDR.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*Excel::create('Vendor CDR', function ($excel) use ($excel_data) {
                $excel->sheet('Vendor CDR', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .=',0)';

        return DataTableSql::of($query, 'sqlsrv2')->make();
    }
    public function vendorcdr_upload() {
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $UploadTemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplate::TEMPLATE_VENDORCDR);
        $trunks = Trunk::getTrunkDropdownIDList();
        $trunks = $trunks+array(0=>'Find From VendorPrefix');
        return View::make('cdrupload.vendorcdrupload',compact('dashboardData','account','gateway','UploadTemplate','trunks'));
    }
    public function check_vendorupload()
    {
        try {
            $data = Input::all();
            $rules = array(
                'CompanyGatewayID' => 'required',
            );
            if($data['RateCDR']){
                $rules['TrunkID'] = 'required';
            }
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if (Input::hasFile('excel')) {
                $upload_path = getenv('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array($ext, array("csv", "xls", "xlsx"))) {
                    $file_name = GUID::generate() . '.' . $excel->getClientOriginalExtension();
                    $excel->move($upload_path, $file_name);
                    $file_name = $upload_path . '/' . $file_name;
                } else {
                    return Response::json(array("status" => "failed", "message" => "Please select excel or csv file."));
                }
            } else if (isset($data['TemplateFile'])) {
                $file_name = $data['TemplateFile'];
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select a file."));
            }
            if ($data['FileUploadTemplateID'] > 0) {
                $FileUploadTemplate = FileUploadTemplate::find($data['FileUploadTemplateID']);
                $options = json_decode($FileUploadTemplate->Options, true);
                $data['Delimiter'] = $options['option']['Delimiter'];
                $data['Enclosure'] = $options['option']['Enclosure'];
                $data['Escape'] = $options['option']['Escape'];
                $data['Firstrow'] = $options['option']['Firstrow'];
            }
			
            if (!empty($file_name)) {
                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;
                $grid['filename'] = $file_name;
                if (!empty($FileUploadTemplate)) {
                    $grid['FileUploadTemplate'] = json_decode(json_encode($FileUploadTemplate), true);
                    $grid['FileUploadTemplate']['Options'] = json_decode($FileUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "message" => "file uploaded", "data" => $grid));
            }
        } catch (Exception $e) {
            return Response::json(array("status" => "failed", "message" => $e->getMessage()));
        }
    }
    public function storeVendorTemplate() {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        if(isset($data['FileUploadTemplateID']) && $data['FileUploadTemplateID']>0) {
            $rules = array('TemplateName' => 'required|unique:tblFileUploadTemplate,Title,'.$data['FileUploadTemplateID'].',FileUploadTemplateID',
                'TemplateFile' => 'required');
        }else{
            $rules = array('TemplateName' => 'required|unique:tblFileUploadTemplate,Title,NULL,FileUploadTemplateID',
                'TemplateFile' => 'required');
        }
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = basename($data['TemplateFile']);

        $temp_path = getenv('TEMP_PATH').'/';
        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['TEMPLATE_FILE']);
        $amazonCDRPath = AmazonS3::generate_upload_path(AmazonS3::$dir['CDR_UPLOAD']);
        $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;
        $destinationCDRPath = getenv("UPLOAD_PATH") . '/' . $amazonCDRPath;
        copy($temp_path.$file_name,$destinationPath.$file_name);
        copy($temp_path.$file_name,$destinationCDRPath.$file_name);
        if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath)){
            return Response::json(array("status" => "failed", "message" => "Failed to upload template sample file."));
        }
        $save = ['CompanyID'=>$CompanyID,'Title'=>$data['TemplateName'],'TemplateFile'=>$amazonPath.$file_name];
        $save['created_by'] = User::get_user_full_name();
        $option["option"]= $data['option'];//['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
        $option["selection"] = $data['selection'];//['connect_time'=>$data['connect_time'],'disconnect_time'=>$data['disconnect_time'],'billed_duration'=>$data['billed_duration'],'duration'=>$data['duration'],'cld'=>$data['cld'],'cli'=>$data['cli'],'Account'=>$data['Account'],'cost'=>$data['cost']];
        $save['Options'] = json_encode($option);
        $save['Type'] = FileUploadTemplate::TEMPLATE_VENDORCDR;
        if(isset($data['FileUploadTemplateID']) && $data['FileUploadTemplateID']>0) {
            $template = FileUploadTemplate::find($data['FileUploadTemplateID']);
            $template->update($save);
        }else {/**/
            $template = FileUploadTemplate::create($save);
        }
        if ($template) {
            //Inserting Job Log
            $data['FileUploadTemplateID'] = $template->FileUploadTemplateID;
            $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
            $data['full_path'] = $fullPath;
            $jobType = JobType::where(["Code" => 'VDR'])->get(["JobTypeID", "Title"]);
            $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
            $histdata['CompanyID']= $jobdata["CompanyID"] = $CompanyID;
            $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
            $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
            $jobdata["JobLoggedUserID"] = User::get_userID();
            $jobdata["Title"] =  (isset($jobType[0]->Title) ? $jobType[0]->Title : '');
            $jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
            $histdata['CreatedBy']= $jobdata["CreatedBy"] = User::get_user_full_name();
            $jobdata["Options"] = json_encode($data);
            $jobdata["updated_at"] = date('Y-m-d H:i:s');
            $JobID = Job::insertGetId($jobdata);
            /*$histdata['CompanyGatewayID'] = $data['CompanyGatewayID'];
            $histdata['StartDate'] = $data['StartDate'];
            $histdata['EndDate'] = $data['EndDate'];
            $histdata['created_at'] = date('Y-m-d H:i:s');

            CDRUploadHistory::insert($histdata);*/


            $jobfiledata["JobID"] = $JobID;
            $jobfiledata["FileName"] = basename($fullPath);
            $jobfiledata["FilePath"] = $fullPath;
            $jobfiledata["HttpPath"] = 0;
            $jobfiledata["Options"] = json_encode($data);
            $jobfiledata["CreatedBy"] = User::get_user_full_name();
            $jobfiledata["updated_at"] = date('Y-m-d H:i:s');
            $JobFileID = JobFile::insertGetId($jobfiledata);
            return Response::json(array("status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed."));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Template."));
        }
    }


}
