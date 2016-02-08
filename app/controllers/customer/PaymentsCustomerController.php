<?php

class PaymentsCustomerController extends \BaseController {


    public function ajax_datagrid() {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $data['AccountID'] = User::get_userID();
        $data['InvoiceNo']=$data['InvoiceNo']!= ''?"'".$data['InvoiceNo']."'":'null';
        $data['Status'] = 'NULL';
        $data['type'] = $data['type'] != ''?"'".$data['type']."'":'null';
        $data['paymentmethod'] = $data['paymentmethod'] != ''?"'".$data['paymentmethod']."'":'null';
        $columns = array('AccountName','InvoiceNo','Amount','PaymentType','PaymentDate','Status','CreatedBy');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getPayments (".$CompanyID.",".$data['AccountID'].",".$data['InvoiceNo'].",".$data['Status'].",".$data['type'].",".$data['paymentmethod'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
        $Payments = DataTableSql::of($query,'sqlsrv2')->getProcResult(array('AccountName','PaymentID','AccountID','Amount','PaymentType','Currency','PaymentDate','Status','PaymentProof','CreatedBy','InvoiceNo','PaymentMethod','Notes'));
        return $Payments->make();
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
        $method = array(''=>'Select Method','CASH'=>'CASH','PAYPAL'=>'PAYPAL','CHEQUE'=>'CHEQUE','CREDIT CARD'=>'CREDIT CARD','BANK TRANSFER'=>'BANK TRANSFER');
        $action = array(''=>'Select Action','Payment In'=>'Payment out','Payment Out'=>'Payment In');
        $status = array(''=>'Select Status','Pending Approval'=>'Pending Approval','Approved'=>'Approved','Rejected'=>'Rejected');
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
            $save['Status'] = 'Pending Approval';
            if (Payment::create($save)) {
                $companyID = User::get_companyID();
                $result = Company::select('PaymentRequestEmail','CompanyName')->where("CompanyID", '=', $companyID)->first();
                $PaymentRequestEmail =explode(',',$result->PaymentRequestEmail);
                $data['EmailToName'] = $result->CompanyName;
                $data['Subject']= 'Payment verification';
                $save['AccountName'] = User::get_user_full_name();
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
                /*foreach($billingadminemails as $billingadminemail){
                    if(filter_var($billingadminemail, FILTER_VALIDATE_EMAIL)) {
                        $data['EmailTo'] = $billingadminemail;
                        $status = sendMail('emails.admin.payment', $data);
                    }
                }*/
                $message = isset($status['message'])?' and '.$status['message']:'';
                return Response::json(array("status" => "success", "message" => "Payment Successfully Created ". $message ));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Payment."));
            }
        }else{
            return $isvalid['message'];
        }
    }


    public function exports() {
        $CompanyID = User::get_companyID();

        $data = Input::all();

        $data['iDisplayStart'] +=1;
        $data['AccountID'] = User::get_userID();
        $data['InvoiceNo']=$data['InvoiceNo']!= ''?"'".$data['InvoiceNo']."'":'null';
        $data['Status'] = 'Approved';
        $data['type'] = $data['type'] != ''?"'".$data['type']."'":'null';
        $data['paymentmethod'] = $data['paymentmethod'] != ''?"'".$data['paymentmethod']."'":'null';
        $columns = array('AccountName','InvoiceNo','Amount','PaymentType','PaymentDate','Status','CreatedBy');
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getPayments (".$CompanyID.",".$data['AccountID'].",".$data['InvoiceNo'].",".$data['Status'].",".$data['type'].",".$data['paymentmethod'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',1)";

        $excel_data  = DB::connection('sqlsrv2')->select($query);
        $excel_data = json_decode(json_encode($excel_data),true);
        Excel::create('Payments', function ($excel) use ($excel_data) {
            $excel->sheet('Payments', function ($sheet) use ($excel_data) {
                $sheet->fromArray($excel_data);
            });
        })->download('xls');
    }

}