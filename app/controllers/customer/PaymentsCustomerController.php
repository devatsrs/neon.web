<?php

class PaymentsCustomerController extends \BaseController {


    public function ajax_datagrid($type) {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $data['AccountID'] = User::get_userID();
        $data['InvoiceNo']=$data['InvoiceNo']!= ''?"'".$data['InvoiceNo']."'":'null';
        //$data['Status'] = 'NULL';
        $data['Status'] = 'Approved';
        $data['type'] = $data['type'] != ''?"'".$data['type']."'":'null';
        $data['paymentmethod'] = $data['paymentmethod'] != ''?"'".$data['paymentmethod']."'":'null';
        $data['p_paymentstartdate'] 	 = 		$data['PaymentDate_StartDate']!=''?"".$data['PaymentDate_StartDate']."":'null';
        $data['p_paymentstartTime'] 	 = 		$data['PaymentDate_StartTime']!=''?"".$data['PaymentDate_StartTime']."":'00:00:00';
        $data['p_paymentenddate'] 	 	 = 		$data['PaymentDate_EndDate']!=''?"".$data['PaymentDate_EndDate']."":'null';
        $data['p_paymentendtime'] 	 	 = 		$data['PaymentDate_EndTime']!=''?"".$data['PaymentDate_EndTime']."":'00:00:00';
        $data['p_paymentstart']			 =		'null';
        $data['p_paymentend']			 =		'null';

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

        // $data['recall_on_off'] = isset($data['recall_on_off'])?($data['recall_on_off']== 'true'?1:0):0;
        $data['recall_on_off'] = 0;

        $account                     = Account::find($data['AccountID']);
        $CurrencyId                  = $account->CurrencyId;
        $accountCurrencyID 		     = empty($CurrencyId)?'0':$CurrencyId;

        $columns = array('InvoiceNo','Amount','PaymentType','PaymentDate','Status','CreatedBy');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getPayments (".$CompanyID.",".$data['AccountID'].",".$data['InvoiceNo'].",'".$data['Status']."',".$data['type'].",".$data['paymentmethod'].",".$data['recall_on_off'].",".$accountCurrencyID.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',1,".$data['p_paymentstart'].",".$data['p_paymentend']."";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',2)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Payment.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Payment.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*Excel::create('Payment', function ($excel) use ($excel_data) {
                $excel->sheet('Payment', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
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
        $CurrencyId = Company::where("CompanyID", '=', $companyID)->pluck('CurrencyId');
        $currency = Currency::where('CurrencyId',$CurrencyId)->pluck('Code');
        $AccountID = User::get_userID();
        $method = array(''=>'Select ','CASH'=>'CASH','PAYPAL'=>'PAYPAL','CHEQUE'=>'CHEQUE','CREDIT CARD'=>'CREDIT CARD','BANK TRANSFER'=>'BANK TRANSFER');
        $action = array(''=>'Select ','Payment In'=>'Payment out','Payment Out'=>'Payment In');
        $status = array(''=>'Select ','Pending Approval'=>'Pending Approval','Approved'=>'Approved','Rejected'=>'Rejected');
        return View::make('customer.payments.index', compact('id','currency','method','type','status','action','AccountID'));
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
            unset($save['Currency']);
            $save['Status'] = 'Pending Approval';
            if(isset($save['InvoiceNo'])) {
                $save['InvoiceID'] = (int)Invoice::where(array('FullInvoiceNumber'=>$save['InvoiceNo'],'AccountID'=>$save['AccountID']))->pluck('InvoiceID');
            }
            if (Payment::create($save)) {
                $companyID = User::get_companyID();
                $PendingApprovalPayment = Notification::getNotificationMail(Notification::PendingApprovalPayment);

                $PendingApprovalPayment = explode(',', $PendingApprovalPayment);
                $data['EmailToName'] = Company::getName();
                $data['Subject']= 'Payment verification';
                $save['AccountName'] = User::get_user_full_name();
                $data['data'] = $save;
                $data['data']['Currency'] = Currency::getCurrencyCode($save['CurrencyID']);
                $data['data']['AccountName'] = Customer::get_accountName();
                $data['data']['CreatedBy'] = Customer::get_accountName();
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

                foreach($PendingApprovalPayment as $billingemail){
                    if(filter_var($billingemail, FILTER_VALIDATE_EMAIL)) {
                        $data['EmailTo'] = $billingemail;
                        $status = sendMail('emails.admin.payment', $data);
                    }
                }

                foreach($billingadminemails as $billingadminemail){
                    if(filter_var($billingadminemail->EmailAddress, FILTER_VALIDATE_EMAIL)) {
                        $data['EmailTo'] = $billingadminemail->EmailAddress;
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

    /** not in use **/
    public function exports() {
        $CompanyID = User::get_companyID();

        $data = Input::all();
		$data['recall_on_off'] = 0;
        $data['iDisplayStart'] +=1;
        $data['AccountID'] = User::get_userID();
        $data['InvoiceNo']=$data['InvoiceNo']!= ''?"'".$data['InvoiceNo']."'":'null';
        $data['Status'] = 'Approved';
        $data['type'] = $data['type'] != ''?"'".$data['type']."'":'null';
        $data['paymentmethod'] = $data['paymentmethod'] != ''?"'".$data['paymentmethod']."'":'null';
        $columns = array('AccountName','InvoiceNo','Amount','PaymentType','PaymentDate','Status','CreatedBy');
        $sort_column = $columns[$data['iSortCol_0']];
		$data['p_paymentstart']			 =		'null';		
		$data['p_paymentend']			 =		'null';
        $query = "call prc_getPayments (".$CompanyID.",".$data['AccountID'].",".$data['InvoiceNo'].",".$data['Status'].",".$data['type'].",".$data['paymentmethod'].",".$data['recall_on_off'].",0,".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',".$data['p_paymentstart'].",".$data['p_paymentend'].",1)";

        $excel_data  = DB::connection('sqlsrv2')->select($query);
        $excel_data = json_decode(json_encode($excel_data),true);
        Excel::create('Payments', function ($excel) use ($excel_data) {
            $excel->sheet('Payments', function ($sheet) use ($excel_data) {
                $sheet->fromArray($excel_data);
            });
        })->download('xls');
    }

    public function ajax_datagrid_total(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] 		 =	0;
        $data['iDisplayStart'] 		+=	1;
        $data['iSortCol_0']			 =  0;
        $data['sSortDir_0']			 =  'desc';
        $data['AccountID'] = User::get_userID();
        $data['InvoiceNo']=$data['InvoiceNo']!= ''?"'".$data['InvoiceNo']."'":'null';
        //$data['Status'] = 'NULL';
        $data['Status'] = 'Approved';
        $data['type'] = $data['type'] != ''?"'".$data['type']."'":'null';
        $data['paymentmethod'] = $data['paymentmethod'] != ''?"'".$data['paymentmethod']."'":'null';
        $data['p_paymentstartdate'] 	 = 		$data['PaymentDate_StartDate']!=''?"".$data['PaymentDate_StartDate']."":'null';
        $data['p_paymentstartTime'] 	 = 		$data['PaymentDate_StartTime']!=''?"".$data['PaymentDate_StartTime']."":'00:00:00';
        $data['p_paymentenddate'] 	 	 = 		$data['PaymentDate_EndDate']!=''?"".$data['PaymentDate_EndDate']."":'null';
        $data['p_paymentendtime'] 	 	 = 		$data['PaymentDate_EndTime']!=''?"".$data['PaymentDate_EndTime']."":'00:00:00';
        $data['p_paymentstart']			 =		'null';
        $data['p_paymentend']			 =		'null';

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

        // $data['recall_on_off'] = isset($data['recall_on_off'])?($data['recall_on_off']== 'true'?1:0):0;
        $data['recall_on_off'] = 0;

        $account                     = Account::find($data['AccountID']);
        $CurrencyId                  = $account->CurrencyId;
        $accountCurrencyID 		     = empty($CurrencyId)?'0':$CurrencyId;

        $columns = array('InvoiceNo','Amount','PaymentType','PaymentDate','Status','CreatedBy');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getPayments (".$CompanyID.",".$data['AccountID'].",".$data['InvoiceNo'].",'".$data['Status']."',".$data['type'].",".$data['paymentmethod'].",".$data['recall_on_off'].",".$accountCurrencyID.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',1,".$data['p_paymentstart'].",".$data['p_paymentend'].",0)";

        $result   = DataTableSql::of($query,'sqlsrv2')->getProcResult(array('ResultCurrentPage','Total_grand_field'));
        $result2  = $result['data']['Total_grand_field'][0]->total_grand;
        $result4  = array(
            "total_grand"=>$result['data']['Total_grand_field'][0]->total_grand,
           // "os_pp"=>$result['data']['Total_grand_field'][0]->first_amount.' / '.$result['data']['Total_grand_field'][0]->second_amount,
        );

        return json_encode($result4,JSON_NUMERIC_CHECK);

    }

}