<?php

class PaymentsController extends \BaseController {


    public function ajax_datagrid() {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $data['AccountID'] = $data['AccountID']!= ''?$data['AccountID']:0;
        $data['InvoiceNo']=$data['InvoiceNo']!= ''?"'".$data['InvoiceNo']."'":'null';
        $data['Status'] = $data['Status'] != ''?"'".$data['Status']."'":'null';
        $data['type'] = $data['type'] != ''?"'".$data['type']."'":'null';
        $data['paymentmethod'] = $data['paymentmethod'] != ''?"'".$data['paymentmethod']."'":'null';
        $data['recall_on_off'] = isset($data['recall_on_off'])?($data['recall_on_off']== 'true'?1:0):0;
        $columns = array('AccountName','InvoiceNo','Amount','PaymentType','PaymentDate','Status','CreatedBy');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getPayments (".$CompanyID.",".$data['AccountID'].",".$data['InvoiceNo'].",".$data['Status'].",".$data['type'].",".$data['paymentmethod'].",".$data['recall_on_off'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            Excel::create('Payment', function ($excel) use ($excel_data) {
                $excel->sheet('Payment', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        $query .=',0)';
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
        $currency = Currency::getCurrencyDropdownList();
        $InvoiceNo = Invoice::where(array('CompanyID'=>$companyID,'InvoiceType'=>Invoice::INVOICE_OUT))->get(['InvoiceNumber']);
        $InvoiceNoarray = array();
        foreach($InvoiceNo as $Invoicerow){
            $InvoiceNoarray[] = $Invoicerow->InvoiceNumber;
        }
        $invoice = implode(',',$InvoiceNoarray);
        $accounts = Account::getAccountIDList();
        return View::make('payments.index', compact('id','currency','method','type','status','action','accounts','invoice'));
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

            $save['Status'] = 'Pending Approval';
            if(User::is('BillingAdmin')) {
                $save['Status'] = 'Approved';
            }
            if (Payment::create($save)) {
                if(isset($InvoiceID) && !empty($InvoiceID)){
                    $Invoice = Invoice::find($InvoiceID);
                    $CreatedBy = User::get_user_full_name();
                    $invoice_status = Invoice::get_invoice_status();
                    $amount = $save['Amount'];
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
                $companyID = User::get_companyID();
                $result = Company::select('PaymentRequestEmail','CompanyName')->where("CompanyID", '=', $companyID)->first();
                $PaymentRequestEmail =explode(',',$result->PaymentRequestEmail);
                $data['EmailToName'] = $result->CompanyName;
                $data['Subject']= 'Payment verification';
                $save['AccountName'] = $AccountName;
                $data['data'] = $save;
                //$billingadminemails = User::where(["CompanyID" => $companyID, "Status" => 1])->where('Roles', 'like', '%Billing Admin%')->get(['EmailAddress']);
                $resource = DB::table('tblResourceCategories')->select('ResourceCategoryID')->where([ "ResourceCategoryName"=>'BillingAdmin',"CompanyID" => $companyID])->first();
                $userid=[];
                if(!empty($resource->ResourceCategoryID)){
                    $permission = DB::table('tblUserPermission')->where([ "AddRemove"=>'add',"CompanyID" => $companyID, "resourceID" => $resource->ResourceCategoryID])->get();
                    if(count($permission)>0){
                        foreach($permission as $pr){
                            $userid[]=$pr->UserID;
                        }
                    }
                }
                $billingadminemails = User::where(["CompanyID" => $companyID, "Status" => 1])->whereIn('UserID', $userid)->get(['EmailAddress']);
                foreach($PaymentRequestEmail as $billingemail){
                    if(filter_var($billingemail, FILTER_VALIDATE_EMAIL)) {
                        $data['EmailTo'] = $billingemail;
                        $status = sendMail('emails.admin.payment', $data);
                    }
                }
                foreach($billingadminemails as $billingadminemail){
                    if(filter_var($billingadminemail, FILTER_VALIDATE_EMAIL)) {
                        $data['EmailTo'] = $billingadminemail;
                        $status = sendMail('emails.admin.payment', $data);
                    }
                }
                $message = isset($status['message'])?' and '.$status['message']:'';
                return Response::json(array("status" => "success", "message" => "Payment Successfully Created ". $message ));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Payment."));
            }
        }else{
            return $isvalid['message'];
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
        if( intval($id) > 0){
            $data = Input::all();
            $rules['RecallReasoan'] = 'required';
            $validator = Validator::make($data, $rules);
            $data['RecallBy'] =  User::get_user_full_name();
            $data['Recall'] = 1;
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            try {
                $result = Payment::find($id)->update($data);
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Payment Status Changed Successfully"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Changing Payment Status."));
                }
            } catch (Exception $ex) {
                return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Payment id is invalid."));
        }
    }

    public function payment_approve_reject($id,$action){
        if(User::is('BillingAdmin')) {
            if ($id && $action) {
                $Payment = Payment::findOrFail($id);
                $save = array();
                if ($action == 'approve') {
                    $save['Status'] = 'Approved';
                } else if ($action == 'reject') {
                    $save['Status'] = 'Rejected';
                }
                $data = Input::all();
                $Payment->Notes .= '<br/>'.$data['Notes'];
                if ($Payment->update($save)) {
                    $managerinfo =  Account::getAccountManager($Payment->AccountID);
                    if(!empty($managerinfo)) {
                        $emaildata['EmailToName'] = $managerinfo->FirstName.' '.$managerinfo->LastName;
                        $emaildata['Subject'] = 'Payment '.$save['Status'].' '.$managerinfo->AccountName;
                        $save['Amount'] = $Payment->Amount;
                        $save['PaymentType'] = $Payment->PaymentType;
                        $save['Currency'] = $Payment->Currency;
                        $save['PaymentDate'] = $Payment->PaymentDate;
                        $save['Notes'] = $Payment->Notes;
                        $save['AccountName'] = $managerinfo->AccountName;
                        $emaildata['data'] = $save;
                        $emaildata['EmailTo'] = $managerinfo->EmailAddress;
                        $status = sendMail('emails.admin.paymentstatus',$emaildata);
                    }
                    return Response::json(array("status" => "success", "message" => "payment Successfully Updated"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Creating Payment."));
                }
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "You have not permission to Approve or reject"));
        }
    }

    public function check_upload() {
        try {
            $data = Input::all();
            if (Input::hasFile('excel')) {
                $upload_path = getenv('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array($ext, array("csv", "xls", "xlsx"))) {
                    $file_name_without_ext = GUID::generate();
                    $file_name = $file_name_without_ext . '.' . $excel->getClientOriginalExtension();
                    $excel->move($upload_path, $file_name);
                    $file_name = $upload_path . '/' . $file_name;
                } else {
                    return Response::json(array("status" => "failed", "message" => "Please select excel or csv file."));
                }
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select a file."));
            }
        }catch(Exception $ex) {
            Log::info($ex);
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function upload() {

        \Debugbar::disable();

        ini_set('max_execution_time', 0);
        $data = Input::all();

        if (Input::hasFile('excel')) {

            $id = User::get_companyID();
            $excel = Input::file('excel'); // ->move($destinationPath);
            $ext = $excel->getClientOriginalExtension();
            $upload_path = getenv('TEMP_PATH');

            if (in_array($ext, array("csv", "xls", "xlsx"))) {
                $file_name = "Payments_". GUID::generate() . '.' . $ext;
                $excel->move($upload_path, $file_name);
                $file_name = $upload_path . '/' . $file_name;

                $status = Payment::upload_check($file_name);
                if($status['status']==0){
                    return Response::json(array("status" => "failed", "message" => $status['message'],'payments'=>$status['payments']));
                }


                $file_name = basename($file_name);
                $temp_path = getenv('TEMP_PATH').'/' ;
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['PAYMENT_UPLOAD']);

                $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;
                copy($temp_path . $file_name, $destinationPath . $file_name);

                if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload vendor rates file."));
                }
                $fullPath = $amazonPath . $file_name;
                $data['full_path'] = $fullPath;
                try {
                    DB::beginTransaction();
                    unset($data['excel']); //remove unnecesarry object.
                    $result = Job::logJob("PU", $data);
                    if ($result['status'] != "success") {
                        DB::rollback();
                        return Response::json(["status" => "failed", "message" => $result['message']]);
                    }
                    DB::commit();
                    return Response::json(["status" => "success", "message" => "File Uploaded, Job Added in queue to process. You will be informed once Job Done. "]);
                } catch (Exception $ex) {
                    DB::rollback();
                    return Response::json(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
                }

            } else {
                return Response::json(array("status" => "failed", "message" => "Allowed Extension .xls, .xlxs, .csv."));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "Please upload excel/csv file <5MB."));
        }
    }

    public function download_sample_excel_file(){
        $filePath = public_path() .'/uploads/sample_upload/PaymentUploadSample.csv';
        download_file($filePath);

    }

    public function  download_doc($id){
        $FileName = Payment::where(["PaymentID"=>$id])->pluck('PaymentProof');
        $FilePath =  AmazonS3::preSignedUrl($FileName);
        header('Location: '.$FilePath);
        exit;
    }

    public function getCurrency($id){
        return Account::getCurrency($id);
    }

}