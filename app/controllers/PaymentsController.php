<?php

class PaymentsController extends \BaseController {
	
			public function ajax_datagrid_total()
		{
			$data 							 = 		Input::all();
			$CompanyID 						 = 		User::get_companyID();
			$data['iDisplayStart'] 		 	 =		0;
			$data['iDisplayStart'] 			+=		1;
			$data['iSortCol_0']			 	 =  	0;     
			$data['sSortDir_0']			 	 =  	'desc';
			$data['AccountID'] 				 = 		$data['AccountID']!= ''?$data['AccountID']:0;
			$data['InvoiceNo']				 =		$data['InvoiceNo']!= ''?"'".$data['InvoiceNo']."'":'null';
			$data['Status'] 				 = 		$data['Status'] != ''?"'".$data['Status']."'":'null';
			$data['type'] 					 = 		$data['type'] != ''?"'".$data['type']."'":'null';
			$data['paymentmethod'] 			 = 		$data['paymentmethod'] != ''?"'".$data['paymentmethod']."'":'null';
			$data['p_paymentstartdate'] 	 = 		empty($data['PaymentDate_StartDate']) ?'null':"".$data['PaymentDate_StartDate']."";
			$data['p_paymentenddate'] 	     = 		empty($data['p_paymentenddate']) ?'null':"".$data['p_paymentenddate']."";
            $data['p_paymentstartTime'] 	 = 		empty($data['PaymentDate_StartTime'])?'00:00:00':"".$data['PaymentDate_StartTime']."";
            $data['p_paymentendtime']   	 = 		empty($data['p_paymentendtime'])?'00:00:00':"".$data['p_paymentendtime']."";
			$data['p_paymentstart']			 =		'null';		
			$data['p_paymentend']			 =		'null';
			$data['CurrencyID'] 			 = 		empty($data['CurrencyID'])?'0':$data['CurrencyID'];
			$data['tag'] 			 = 		empty($data['tag'])?'':$data['tag'];

			if($data['p_paymentstartdate']!='' && $data['p_paymentstartdate']!='null' && $data['p_paymentstartTime']!='')
			{
				 $data['p_paymentstart']		=	"'".$data['p_paymentstartdate'].' '.$data['p_paymentstartTime']."'";	
			}		
			
			if($data['p_paymentenddate']!='' && $data['p_paymentenddate']!='null' && $data['p_paymentendtime']!='')
			{
				 $data['p_paymentend']			=	"'".$data['p_paymentenddate'].' '.$data['p_paymentendtime']."'";	
			}
			
			if($data['p_paymentstart']!='null' && $data['p_paymentend']=='null') 
			{
				$data['p_paymentend'] 			= 	"'".date("Y-m-d H:i:s")."'";
			}

			$data['recall_on_off'] = isset($data['recall_on_off'])?($data['recall_on_off']== 'true'?1:0):0;
			$columns = array('AccountName','InvoiceNo','Amount','PaymentType','PaymentDate','Status','CreatedBy','Notes');
			$sort_column = $columns[$data['iSortCol_0']];

            // AccountManger Condition
            $userID = 0;
            if(User::is('AccountManager')) { // Account Manager
                $userID = User::get_userID();
            }

			$query = "call prc_getPayments (".$CompanyID.",".$data['AccountID'].",".$data['InvoiceNo'].",'',".$data['Status'].",".$data['type'].",".$data['paymentmethod'].",".$data['recall_on_off'].",".$data['CurrencyID'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0,".$data['p_paymentstart'].",".$data['p_paymentend'].",0,".$userID.",'".$data['tag']."')";
		   
			$result   = DataTableSql::of($query,'sqlsrv2')->getProcResult(array('ResultCurrentPage','Total_grand_field'));
			$result2  = $result['data']['Total_grand_field'][0]->total_grand;
			$result4  = array(
					"total_grand"=>$result['data']['Total_grand_field'][0]->total_grand
			);
				
			return json_encode($result4,JSON_NUMERIC_CHECK);		
		}

    public function ajax_datagrid($type)
	{
        $data 							 = 		Input::all();
        $CompanyID 						 = 		User::get_companyID();
        $data['iDisplayStart'] 			+=		1;
        $data['AccountID'] 				 = 		$data['AccountID']!= ''?$data['AccountID']:0;
        $data['InvoiceNo']				 =		$data['InvoiceNo']!= ''?"'".$data['InvoiceNo']."'":'null';
        $data['Status'] 				 = 		$data['Status'] != ''?"'".$data['Status']."'":'null';
        $data['type'] 					 = 		$data['type'] != ''?"'".$data['type']."'":'null';
        $data['paymentmethod'] 			 = 		$data['paymentmethod'] != ''?"'".$data['paymentmethod']."'":'null';		
		$data['p_paymentstartdate'] 	 = 		$data['PaymentDate_StartDate']!=''?"".$data['PaymentDate_StartDate']."":'null';
		$data['p_paymentstartTime'] 	 = 		$data['PaymentDate_StartTime']!=''?"".$data['PaymentDate_StartTime']."":'00:00:00';		
		$data['p_paymentenddate'] 	 	 = 		$data['PaymentDate_EndDate']!=''?"".$data['PaymentDate_EndDate']."":'null';
		$data['p_paymentendtime'] 	 	 = 		$data['PaymentDate_EndTime']!=''?"".$data['PaymentDate_EndTime']."":'00:00:00';
		$data['p_paymentstart']			 =		'null';		
		$data['p_paymentend']			 =		'null';
		$data['CurrencyID'] 			 = 		empty($data['CurrencyID'])?'0':$data['CurrencyID'];
        $data['tag'] 			 = 		empty($data['tag'])?'':$data['tag'];
		 
		if($data['p_paymentstartdate']!='' && $data['p_paymentstartdate']!='null' && $data['p_paymentstartTime']!='')
		{
			 $data['p_paymentstart']		=	"'".$data['p_paymentstartdate'].' '.$data['p_paymentstartTime']."'";	
		}		
		
		if($data['p_paymentenddate']!='' && $data['p_paymentenddate']!='null' && $data['p_paymentendtime']!='')
		{
			 $data['p_paymentend']			=	"'".$data['p_paymentenddate'].' '.$data['p_paymentendtime']."'";	
		}
		
		if($data['p_paymentstart']!='null' && $data['p_paymentend']=='null') 
		{
			$data['p_paymentend'] 			= 	"'".date("Y-m-d H:i:s")."'";
		}

        $data['recall_on_off'] = isset($data['recall_on_off'])?($data['recall_on_off']== 'true'?1:0):0;
        $columns = array('PaymentID','AccountName','InvoiceNo','Amount','PaymentType','PaymentDate','Status','CreatedBy','Notes');
        $sort_column = $columns[$data['iSortCol_0']];

        // AccountManger Condition
        $userID = 0;
        if(User::is('AccountManager')) { // Account Manager
            $userID = User::get_userID();
        }

        $query = "call prc_getPayments (".$CompanyID.",".$data['AccountID'].",".$data['InvoiceNo'].",'',".$data['Status'].",".$data['type'].",".$data['paymentmethod'].",".$data['recall_on_off'].",".$data['CurrencyID'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0,".$data['p_paymentstart'].",".$data['p_paymentend']."";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1,'.$userID.',"'.$data['tag'].'")');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Payment.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Payment.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0,'.$userID.',"'.$data['tag'].'")';
        return DataTableSql::of($query,'sqlsrv2')->make();
    }
    /**
     * Display a listing of the resource.
     * GET /payments
     *
     * @return Response
     */
    public function index()
    {
        $id=0;
		$companyID = User::get_companyID();
        $PaymentUploadTemplates = PaymentUploadTemplate::getTemplateIDList();
        $currency = Currency::getCurrencyDropdownList(); 
		$currency_ids = json_encode(Currency::getCurrencyDropdownIDList()); 		
        $accounts = Account::getAccountIDList();
		$DefaultCurrencyID    	=   Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        return View::make('payments.index', compact('id','currency','accounts','PaymentUploadTemplates','currency_ids','DefaultCurrencyID'));
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /payments/create
	 *
	 * @return Response
	 */
    public function create()
    {
        $isvalid = Payment::validate();
        $sendemail = 1;
        $message = '';
        if($isvalid['valid']==1) {
            $save = $isvalid['data'];


            /* for Adding payment from Invoice  */
            if(isset($save['InvoiceID'])) {
                $InvoiceID = $save['InvoiceID'];
                $OutstandingAmount = $save['OutstandingAmount'];
                unset($save['InvoiceID']);
                unset($save['OutstandingAmount']);
            }

            if(isset($save['AccountName'])) {
                $AccountName = $save['AccountName'];
                unset($save['AccountName']);
            }
            $PaymentOldAmount = 0;
            if(isset($save['InvoiceNo'])) {
                $save['InvoiceID'] = (int)Invoice::where(array('FullInvoiceNumber'=>$save['InvoiceNo'],'AccountID'=>$save['AccountID']))->pluck('InvoiceID');
                $InvoiceID = $save['InvoiceID'];
                $OutstandingAmount = DB::connection('sqlsrv2')
                    ->table('tblInvoice')
                    ->where('InvoiceID', $InvoiceID)
                    ->where('CompanyID', $save['CompanyID'])
                    ->pluck('GrandTotal');
                $PaymentOldAmount = DB::connection('sqlsrv2')
                    ->table('tblPayment')
                    ->where('InvoiceID', $InvoiceID)
                    ->where('CompanyID', $save['CompanyID'])
                    ->sum('Amount');

            }

            $save['Status'] = 'Pending Approval';
            if(User::is('BillingAdmin') || User::is_admin() ) {
                $save['Status'] = 'Approved';
                $sendemail = 0;
            }
			unset($save['Currency']); 
            if (Payment::create($save)) {
                if(isset($InvoiceID) && !empty($InvoiceID)){
                    $Invoice = Invoice::find($InvoiceID);
                    $CreatedBy = User::get_user_full_name();
                    $invoice_status = Invoice::get_invoice_status();
                    $amount = $save['Amount'] + $PaymentOldAmount;
                    $GrandTotal = $Invoice->GrandTotal;
                    $invoiceloddata = array();
                    $invoiceloddata['InvoiceID']= $InvoiceID;

                    $invoiceloddata['created_at']= date("Y-m-d H:i:s");
                    $invoiceloddata['InvoiceLogStatus'] = InVoiceLog::UPDATED;

                    if($amount >= $OutstandingAmount){
                        $Invoice->update(['InvoiceStatus'=>Invoice::PAID]);
                        $invoiceloddata['Note'] = $invoice_status[Invoice::PAID].' By ' . $CreatedBy;
                    }else{
                        $Invoice->update(['InvoiceStatus'=>Invoice::PARTIALLY_PAID]);
                        $invoiceloddata['Note'] = $invoice_status[Invoice::PARTIALLY_PAID].' By ' . $CreatedBy;
                    }

                    InVoiceLog::insert($invoiceloddata);
                }
                if($sendemail==1) {
                    $companyID = User::get_companyID();
                    $PendingApprovalPayment = Notification::getNotificationMail(Notification::PendingApprovalPayment,$companyID);

                    $PendingApprovalPayment = explode(',', $PendingApprovalPayment);
                    $data['EmailToName'] = Company::getName();
                    $data['Subject'] = 'Payment verification';
                    $save['AccountName'] = $AccountName;
                    $data['data'] = $save;
                    $data['data']['Currency'] = Currency::getCurrencyCode($data['data']['CurrencyID']);
                    //$billingadminemails = User::where(["CompanyID" => $companyID, "Status" => 1])->where('Roles', 'like', '%Billing Admin%')->get(['EmailAddress']);
                    $resource = DB::table('tblResourceCategories')->select('ResourceCategoryID')->where(["ResourceCategoryName" => 'BillingAdmin', "CompanyID" => $companyID])->first();
                    $userid = [];
                    if (!empty($resource->ResourceCategoryID)) {
                        $permission = DB::table('tblUserPermission')->where(["AddRemove" => 'add', "CompanyID" => $companyID, "resourceID" => $resource->ResourceCategoryID])->get();
                        if (count($permission) > 0) {
                            foreach ($permission as $pr) {
                                $userid[] = $pr->UserID;
                            }
                        }
                    }
                    $billingadminemails = User::where(["CompanyID" => $companyID, "Status" => 1])->whereIn('UserID', $userid)->get(['EmailAddress']);
                    foreach ($PendingApprovalPayment as $billingemail) {
                        $billingemail = trim($billingemail);
                        if (filter_var($billingemail, FILTER_VALIDATE_EMAIL)) {
                            $data['EmailTo'] = $billingemail;
                            $status = sendMail('emails.admin.payment', $data);
                        }
                    }
                    foreach ($billingadminemails as $billingadminemail) {
                        $billingadminemail = trim($billingadminemail);
                        if (filter_var($billingadminemail, FILTER_VALIDATE_EMAIL)) {
                            $data['EmailTo'] = $billingadminemail;
                            $status = sendMail('emails.admin.payment', $data);
                        }
                    }
                    $message = isset($status['message']) ? ' and ' . $status['message'] : '';
                }
                return Response::json(array("status" => "success", "message" => "Payment Successfully Created ". $message ));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Payment."));
            }
        }else{
            return $isvalid['message'];
        }
    }

    public function payments_quickbookpost(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $PaymentIDs =array_filter(explode(',',$data['PaymentIDs']),'intval');
        if (is_array($PaymentIDs) && count($PaymentIDs)) {
            $jobType = JobType::where(["Code" => 'QPP'])->first(["JobTypeID", "Title"]);
            $jobStatus = JobStatus::where(["Code" => "P"])->first(["JobStatusID"]);
            $jobdata["CompanyID"] = $CompanyID;
            $jobdata["JobTypeID"] = $jobType->JobTypeID ;
            $jobdata["JobStatusID"] =  $jobStatus->JobStatusID;
            $jobdata["JobLoggedUserID"] = User::get_userID();
            $jobdata["Title"] =  $jobType->Title;
            $jobdata["Description"] = $jobType->Title ;
            $jobdata["CreatedBy"] = User::get_user_full_name();
            $jobdata["Options"] = json_encode($data);
            $jobdata["created_at"] = date('Y-m-d H:i:s');
            $jobdata["updated_at"] = date('Y-m-d H:i:s');
            $JobID = Job::insertGetId($jobdata);
            if($JobID){
                return json_encode(["status" => "success", "message" => "Payment Post in quickbook Job Added in queue to process.You will be notified once job is completed."]);
            }else{
                return json_encode(array("status" => "failed", "message" => "Problem Payment Post in Quickbook ."));
            }
        }

    }


    /**
     * Update the specified resource in storage.
     * PUT /payments/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        if( $id > 0 ) {
            $Payment = Payment::findOrFail($id);
            $isvalid = Payment::validate($id);
            if($isvalid['valid']==1){
                $save = $isvalid['data'];
                unset($save['AccountName']);
                if ($Payment->update($save)) {
                    return Response::json(array("status" => "success", "message" => "payment Successfully Updated"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Creating Payment."));
                }
            }else{
                return $isvalid['message'];
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Payment."));
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /payments/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function recall($id) {
        $data = Input::all();
        $rules['RecallReasoan'] = 'required';
        $validator = Validator::make($data, $rules);
        $data['RecallBy'] =  User::get_user_full_name();
        $data['Recall'] = 1;
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $where=[];
        if(isset($data['criteria']) && !empty($data['criteria'])){
            $criteria= json_decode($data['criteria'],true);
            $criteria['p_paymentstart']			 =		'null';
            $criteria['p_paymentend']			 =		'null';

            if($criteria['PaymentDate_StartDate']!='' && $criteria['PaymentDate_StartDate']!='null' && $criteria['PaymentDate_StartTime']!='')
            {
                $criteria['p_paymentstart']		=	$criteria['PaymentDate_StartDate'].' '.$criteria['PaymentDate_StartTime'];
            }

            if($criteria['PaymentDate_EndDate']!='' && $criteria['PaymentDate_EndDate']!='null' && $criteria['PaymentDate_EndDate']!='')
            {
                $criteria['p_paymentend']			=	$criteria['PaymentDate_EndDate'].' '.$criteria['PaymentDate_EndTime'];
            }

            if($criteria['p_paymentstart']!='null' && $criteria['p_paymentend']=='null')
            {
                $criteria['p_paymentend'] 			= 	date("Y-m-d H:i:s");
            }

            if($criteria['p_paymentstart']=='null'){
                $criteria['p_paymentstart'] = '';
            }

            if($criteria['p_paymentend']=='null'){
                $criteria['p_paymentend'] = '';
            }

            $where['Recall'] = isset($data['recall_on_off'])?($data['recall_on_off']== 'true'?1:0):0;

            if(!empty($criteria['AccountID'])){
                $where['AccountID'] = $criteria['AccountID'];
            }
            /*if(!empty($criteria['InvoiceNo'])){
                $where['InvoiceNo'] = $criteria['InvoiceNo'];
            }*/
            if(!empty($criteria['Status'])){
                $where['Status'] = $criteria['Status'];
            }
            if(!empty($criteria['type'])){
                $where['PaymentType'] = $criteria['type'];
            }
            if(!empty($criteria['paymentmethod'])){
                $where['PaymentMethod'] = $criteria['paymentmethod'];
            }
            if(!empty($criteria['CurrencyID'])){
                $where['CurrencyID'] = $criteria['CurrencyID'];
            }
        }
        try {
            $PaymentIDs = !empty($data['PaymentIDs'])?explode(',',$data['PaymentIDs']):'';
            unset($data['PaymentIDs']);
            unset($data['criteria']);
            if(!empty($where)){
                $Payments = Payment::where($where);
                if(!empty($criteria['p_paymentstart']) && !empty($criteria['p_paymentend'])){
                    $Payments->whereBetween('PaymentDate', array($criteria['p_paymentstart'], $criteria['p_paymentend']));
                }
                if(!empty($criteria['InvoiceNo'])){
                    $Payments->where('InvoiceNo','like','%'.$criteria['InvoiceNo'].'%');
                }
                $PaymentIDs = $Payments->lists('PaymentID');
                $result = Payment::whereIn('PaymentID',$PaymentIDs)->update($data);
            }
            elseif(is_array($PaymentIDs)){
                $result = Payment::whereIn('PaymentID',$PaymentIDs)->update($data);
            }else{
                if($id>0) {
                    $PaymentIDs=array($id);
                    $result = Payment::find($id)->update($data);
                }else{
                    return Response::json(array("status" => "failed", "message" => "Problem Changing Payment Status."));
                }
            }
            if ($result) {
                if(is_array($PaymentIDs)){
                    foreach($PaymentIDs as $PaymentID){
                        $InvoiceID=Payment::where('PaymentID',$PaymentID)->pluck('InvoiceID');
                        if(!empty($InvoiceID)){
                            $GrandTotal= Invoice::where(['InvoiceID'=>$InvoiceID])->pluck('GrandTotal');
                            $paymentTotal = Payment::where(['InvoiceID'=>$InvoiceID, 'Recall'=>0])->sum('Amount');
                            if($paymentTotal==0){
                                Invoice::find($InvoiceID)->update(["InvoiceStatus"=>Invoice::SEND]);
                            }else if($paymentTotal>=$GrandTotal){
                                Invoice::find($InvoiceID)->update(["InvoiceStatus"=>Invoice::PAID]);
                            }else if($paymentTotal<$GrandTotal){
                                Invoice::find($InvoiceID)->update(["InvoiceStatus"=>Invoice::PARTIALLY_PAID]);
                            }
                        }

                        $CreditNoteID=Payment::where('PaymentID',$PaymentID)->pluck('CreditNotesID');
                        if(!empty($CreditNoteID)){
                            //get payment amount from payments - creditnote paid amount
                            $PaymentRecallAmount=Payment::where('PaymentID',$PaymentID)->pluck('Amount');
                            $PaidAmount= CreditNotes::where(['CreditNotesID'=>$CreditNoteID])->pluck('PaidAmount');
                            $RecallAmount = $PaidAmount - $PaymentRecallAmount;
                            CreditNotes::find($CreditNoteID)->update(array("PaidAmount" => $RecallAmount ));

                            /*$GrandTotal= CreditNotes::where(['CreditNotesID'=>$CreditNoteID])->pluck('GrandTotal');
                            $paymentTotal = Payment::where(['CreditNotesID'=>$CreditNoteID, 'Recall'=>0])->sum('Amount');
                            if($paymentTotal==0){
                                Invoice::find($InvoiceID)->update(["InvoiceStatus"=>Invoice::SEND]);
                            }else if($paymentTotal>=$GrandTotal){
                                Invoice::find($InvoiceID)->update(["InvoiceStatus"=>Invoice::PAID]);
                            }else if($paymentTotal<$GrandTotal){
                                Invoice::find($InvoiceID)->update(["InvoiceStatus"=>Invoice::PARTIALLY_PAID]);
                            }*/
                        }
                    }
                }

                return Response::json(array("status" => "success", "message" => "Payment Status Changed Successfully"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Changing Payment Status."));
            }
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function payment_approve_reject($id,$action){
        if(User::is('BillingAdmin')  || User::is_admin() ) {
            if ($id && $action) {
                $data = Input::all();
                $rules['Notes'] = 'required';
                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return json_validator_response($validator);
                }
                $Payment = Payment::findOrFail($id);
                $save = array();
                if ($action == 'approve') {
                    $save['Status'] = 'Approved';
                } else if ($action == 'reject') {
                    $save['Status'] = 'Rejected';
                }

                $Payment->Notes .= ' '.$data['Notes'];
                if ($Payment->update($save)) {
                    $managerinfo =  Account::getAccountManager($Payment->AccountID);
                    if(!empty($managerinfo)) {
                        $emaildata['EmailToName'] = $managerinfo->FirstName.' '.$managerinfo->LastName;
                        $emaildata['Subject'] = 'Payment '.$save['Status'].' '.$managerinfo->AccountName;
                        $save['Amount'] = $Payment->Amount;
                        $save['PaymentType'] = $Payment->PaymentType;
                        $save['Currency'] = Currency::getCurrencyCode($Payment->CurrencyID);
                        $save['PaymentDate'] = $Payment->PaymentDate;
                        $save['Notes'] = $Payment->Notes;
                        $save['AccountName'] = $managerinfo->AccountName;
                        $emaildata['data'] = $save;
                        $emaildata['EmailTo'] = $managerinfo->EmailAddress;
                        $status = sendMail('emails.admin.paymentstatus',$emaildata);
                    }
                    return Response::json(array("status" => "success", "message" => Lang::get('routes.CUST_PANEL_PAGE_PAYMENTS_MSG_PAYMENT_SUCCESSFULLY_UPDATED')));
                } else {
                    return Response::json(array("status" => "failed", "message" =>  Lang::get("routes.CUST_PANEL_PAGE_PAYMENTS_MSG_PROBLEM_CREATING_PAYMENT")));
                }
            }
        }else{
            return Response::json(array("status" => "failed", "message" => Lang::get("routes.CUST_PANEL_PAGE_PAYMENTS_MSG_YOU_HAVE_NOT_PERMISSION_TO_APPROVE_OR_REJECT")));
        }
    }

    /* Refill Datagrid against File options changed once Check button clicked
     * */
    function ajaxfilegrid(){
        try {
            $data = Input::all();
            $file_name = $data['TempFileName'];
            $grid = getFileContent($file_name, $data);
            $grid['filename'] = $data['TemplateFile'];
            $grid['tempfilename'] = $data['TempFileName'];
            if ($data['PaymentUploadTemplateID'] > 0) {
                $PaymentUploadTemplate = PaymentUploadTemplate::find($data['PaymentUploadTemplateID']);
                $grid['PaymentUploadTemplate'] = json_decode(json_encode($PaymentUploadTemplate), true);
                //$grid['PaymentUploadTemplate']['Options'] = json_decode($PaymentUploadTemplate->Options,true);
            }
            $grid['PaymentUploadTemplate']['Options'] = array();
            $grid['PaymentUploadTemplate']['Options']['option'] = $data['option'];
            $grid['PaymentUploadTemplate']['Options']['selection'] = $data['selection'];
            return Response::json(array("status" => "success", "data" => $grid));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    /* When File uploads
     * Upload file to server to temp location path
     * Send File top 10 data to show in grid.
     * */
    public function check_upload() {
        try {
            $data = Input::all();
            if (Input::hasFile('excel')) {
                $upload_path = CompanyConfiguration::get('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array(strtolower($ext), array("csv", "xls", "xlsx"))) {
                    $file_name_without_ext = GUID::generate();
                    $file_name = $file_name_without_ext . '.' . $excel->getClientOriginalExtension();
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
            if (!empty($file_name)) {

                if ($data['PaymentUploadTemplateID'] > 0) {
                    $PaymentUploadTemplate = PaymentUploadTemplate::find($data['PaymentUploadTemplateID']);
                    $options = json_decode($PaymentUploadTemplate->Options, true);
                    $data['Delimiter'] = $options['option']['Delimiter'];
                    $data['Enclosure'] = $options['option']['Enclosure'];
                    $data['Escape'] = $options['option']['Escape'];
                    $data['Firstrow'] = $options['option']['Firstrow'];
                }

                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;//$upload_path.'\\'.'temp.'.$ext;
                $grid['filename'] = $file_name;
                if (!empty($PaymentUploadTemplate)) {
                    $grid['PaymentUploadTemplate'] = json_decode(json_encode($PaymentUploadTemplate), true);
                    $grid['PaymentUploadTemplate']['Options'] = json_decode($PaymentUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "data" => $grid));
            }
        }catch(Exception $ex) {
            Log::info($ex);
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    /*
     * Validate Bulk Payment Column Mapping on file.
     * */
    public function validate_column_mapping() {
        $data = Input::all();

        //$rules['selection.AccountName'] = 'required';
        //$rules['selection.PaymentDate'] = 'required';
        //$rules['selection.PaymentMethod'] = 'required';
        //$rules['selection.PaymentType'] = 'required';
        //$rules['selection.Amount'] = 'required';

        Payment::$importpaymentrules['selection.AccountName'] = 'required';
        Payment::$importpaymentrules['selection.PaymentDate'] = 'required';
        Payment::$importpaymentrules['selection.PaymentMethod'] = 'required';
        Payment::$importpaymentrules['selection.PaymentType'] = 'required';
        Payment::$importpaymentrules['selection.Amount'] = 'required';

        $validator = Validator::make($data, Payment::$importpaymentrules,Payment::$importpaymentmessages);


        //$validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $response = Payment::validate_payments($data);
        if ( $response['status'] != 'Success' ) {
            return Response::json(array("status" => "failed", "message" => $response['message']  ,"ProcessID" => $response["ProcessID"],'confirmshow'=>$response["confirmshow"] ));
        }else{
            return Response::json(array("status" => "success", "message" => $response['message'] ,"ProcessID" => $response["ProcessID"],'confirmshow'=>$response["confirmshow"] ));
        }

    }

    public function confirm_bulk_upload() {
        $data = json_decode(str_replace('Skip loading','',json_encode(Input::all(),true)),true);//Input::all();
        $CompanyID = User::get_companyID();
        $ProcessID = $data['ProcessID'];

        $file_name = basename($data['TemplateFile']);
        $temp_path = CompanyConfiguration::get('TEMP_PATH').'/' ;
        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['PAYMENT_UPLOAD']);
        if(JobType::checkJobType('PU') == 0){
            return Response::json(array("status" => "failure", "message" => "Job Type not Defined."));
        }
        $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
        copy($temp_path . $file_name, $destinationPath . $file_name);

        if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
            return Response::json(array("status" => "failed", "message" => "Failed to upload payments file." ));
        }

        if(!empty($data['TemplateName'])) {
            $save = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . $file_name];
            $save['created_by'] = User::get_user_full_name();
            $option["option"] = $data['option'];
            $option["selection"] = filterArrayRemoveNewLines($data['selection']);
            $save['Options'] = str_replace('Skip loading','',json_encode($option));//json_encode($option);

            if ( isset($data['PaymentUploadTemplateID']) && $data['PaymentUploadTemplateID'] > 0 ) {
                $template = PaymentUploadTemplate::find($data['PaymentUploadTemplateID']);
                $template->update($save);
            } else {
                $template = PaymentUploadTemplate::create($save);
            }
            $data['PaymentUploadTemplateID'] = $template->PaymentUploadTemplateID;
        }
        $fullPath = $amazonPath . $file_name;
        $jobType = JobType::where(["Code" => 'PU'])->get(["JobTypeID", "Title"]);

        $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
        $jobdata["CompanyID"] = $CompanyID;
        $jobdata["JobTypeID"] = !empty($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
        $jobdata["JobStatusID"] = !empty($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
        $jobdata["JobLoggedUserID"] = User::get_userID();
        $jobdata["Title"] =  (!empty($jobType[0]->Title) ? $jobType[0]->Title : '');
        $jobdata["Description"] = !empty($jobType[0]->Title) ? $jobType[0]->Title : '';
        $jobdata["CreatedBy"] = User::get_user_full_name();
        $jobdata["Options"] = json_encode($data);
        $jobdata["created_at"] = date('Y-m-d H:i:s');
        $jobdata["updated_at"] = date('Y-m-d H:i:s');
        $JobID = Job::insertGetId($jobdata);

        $jobfiledata["JobID"] = $JobID;
        $jobfiledata["FileName"] = basename($fullPath);
        $jobfiledata["FilePath"] = $fullPath;
        $jobfiledata["HttpPath"] = 0;
        $jobfiledata["CreatedBy"] = User::get_user_full_name();
        $jobfiledata["updated_at"] = date('Y-m-d H:i:s');
        $JobFileID = JobFile::insertGetId($jobfiledata);
        $UserID = User::get_userID();
        //echo "CALL  prc_insertPayments ('" . $CompanyID . "','".$ProcessID."','".$UserID."')";exit();
        try {
            DB::connection('sqlsrv2')->beginTransaction();

            $result = DB::connection('sqlsrv2')->statement("CALL  prc_insertPayments ('" . $CompanyID . "','".$ProcessID."','".$UserID."')");
            DB::connection('sqlsrv2')->commit();

            $jobupdatedata['JobStatusID'] = JobStatus::where('Code','S')->pluck('JobStatusID');
            $jobupdatedata['JobStatusMessage'] = 'Payments uploaded successfully';
            $jobupdatedata['JobStatusID'] = JobStatus::where('Code','S')->pluck('JobStatusID');
            Job::where(["JobID" => $JobID])->update($jobupdatedata);

        }catch ( Exception $err ){
            try{
                DB::connection('sqlsrv2')->rollback();
            }catch (Exception $err) {
                Log::error($err);
            }
            $jobdata['JobStatusID'] = JobStatus::where('Code', 'F')->pluck('JobStatusID');
            $jobdata['JobStatusMessage'] = 'Exception: ' . $err->getMessage();
            Job::where(["JobID" => $JobID])->update($jobdata);
            Log::error($err);
            
            return Response::json(array("status" => "failure", "message" => "Error in Uploading Payments."));
        }
        if($result){
            return Response::json(array("status" => "success", "message" => "Payments Successfully Uploaded"));
        }else{
            return Response::json(array("status" => "failure", "message" => "Error in Uploading Payments."));
        }
    }

    public function download_sample_excel_file(){
        $filePath = public_path() .'/uploads/sample_upload/PaymentUploadSample.csv';
        download_file($filePath);

    }

    public function  download_doc($id){
        $FileName = Payment::where(["PaymentID"=>$id])->pluck('PaymentProof');
        $FilePath =  AmazonS3::preSignedUrl($FileName);
        download_file($FilePath);
        exit;
    }

    public function get_currency_invoice_numbers($id){
        $Currency_Symbol = Account::getCurrency($id);
        $InvoiceNumbers_ = Invoice::where(['AccountID'=>intval($id)])->select('FullInvoiceNumber')->get()->toArray();

        $InvoiceNumbers = array();
        foreach($InvoiceNumbers_ as $row){
            $InvoiceNumbers[] = $row['FullInvoiceNumber'];
        }
        return Response::json(array("status" => "success", "message" => "" , "Currency_Symbol"=>$Currency_Symbol, "InvoiceNumbers" => $InvoiceNumbers));


    }

}