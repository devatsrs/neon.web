<?php
class Payment extends \Eloquent {
	protected $fillable = [];
    protected $connection = 'sqlsrv2';
    protected $guarded = array('PaymentID');
    protected $table = 'tblPayment';
    protected  $primaryKey = "PaymentID";

    public static $method = array(''=>'Select Method','CASH'=>'CASH','PAYPAL'=>'PAYPAL','CHEQUE'=>'CHEQUE','CREDIT CARD'=>'CREDIT CARD','BANK TRANSFER'=>'BANK TRANSFER');
    public static $action = array(''=>'Select Action','Payment In'=>'Payment In','Payment Out'=>'Payment Out');
    public static $status = array(''=>'Select Status','Pending Approval'=>'Pending Approval','Approved'=>'Approved','Rejected'=>'Rejected');
    //public $timestamps = false; // no created_at and updated_at

    public static $credit_card_type = array(
        'American Express'=>'American Express',
        'Australian BankCard'=>'Australian BankCard',
        'Diners Club'=>"Diners Club",
        'Discover'=>'Discover',
        'MasterCard'=>'MasterCard',
        'Visa'=>'Visa',
        "JCB"=>"JCB",
    );

    public static function validate($id=0){
        $valid = array('valid'=>0,'message'=>'Some thing wrong with payment validation','data'=>'');
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        unset($data['customers']);
        /*if(isset($data['InvoiceNo']) && trim($data['InvoiceNo']) == '' ) {
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please enter invoice number"));
            return $valid;
        }
        $result = Invoice::select('InvoiceNumber')->where('InvoiceNumber','=',$data['InvoiceNo'])->where('CompanyID','=',$companyID)->first();
        if(empty($result)){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Invoice number is not exist in invoices."));
            return $valid;
        }
        if($id>0){
            $result = payment::select('InvoiceNo')->where('InvoiceNo','=',$data['InvoiceNo'])->where('CompanyID','=',$companyID)->where('PaymentID','<>',$id)->first();
            if (!empty($result)) {
                $valid['message'] = Response::json(array("status" => "failed", "message" => "Invoice number already exist in Payments."));
                return $valid;
            }
            $Payment = Payment::findOrFail($id);
        }else{
            $result = payment::select('InvoiceNo')->where('InvoiceNo', '=', $data['InvoiceNo'])->where('CompanyID', '=', $companyID)->first();
            if (!empty($result)) {
                $valid['message'] = Response::json(array("status" => "failed", "message" => "Invoice number already exist in Payments."));
                return $valid;
            }
        }*/
        if(isset($data['AccountID']) && trim($data['AccountID']) == '' ) {
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Account Name from dropdown"));
            return $valid;
        }elseif(isset($data['PaymentDate'])&& trim($data['PaymentDate']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Payment Date"));
            return $valid;
        }elseif(isset($data['PaymentMethod'])&& trim($data['PaymentMethod']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Payment Method from dropdown"));
            return $valid;
        }elseif(isset($data['PaymentType'])&& trim($data['PaymentType']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Payment Type from dropdown"));
            return $valid;
        }elseif(isset($data['Currency'])&& trim($data['Currency']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please set Currency in setting"));
            return $valid;
        }elseif(isset($data['Amount'])&& trim($data['Amount']) == ''){
            $valid['message'] = Response::json(array("status" => "failed", "message" => "Please enter Amount"));
            return $valid;
        }elseif(isset($data['Status'])&& trim($data['Status']) == ''){
            if(User::is_admin()){
                $valid['message'] = Response::json(array("status" => "failed", "message" => "Please select Status from dropdown"));
                return $valid;
            }
        }
        if (Input::hasFile('PaymentProof')){
            $upload_path = Config::get('app.payment_proof_path');
            $destinationPath = $upload_path.'/SampleUpload/'.Company::getName().'/';
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['PAYMENT_PROOF']) ;
            $proof = Input::file('PaymentProof');
            // ->move($destinationPath);
            $ext = $proof->getClientOriginalExtension();
            if (in_array(strtolower($ext), array("pdf",'png','jpg','gif'))) {
                $filename = rename_upload_file($destinationPath,$proof->getClientOriginalName());
                $fullPath = $destinationPath .$filename;
                $proof->move($destinationPath,$filename);
                if(!AmazonS3::upload($destinationPath.$filename,$amazonPath)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $data['PaymentProof'] = $amazonPath . $filename;
            }else{
                $valid['message'] = Response::json(array("status" => "failed", "message" => "Please Upload file with given extensions."));
                return $valid;
            }
        }else{
            unset($data['PaymentProof']);
        }

        if($id==0){
            $today = date('Y-m-d');
            $data['CreatedBy'] = User::get_user_full_name();
            $data['created_at'] =  $today;
            $data['ModifyBy'] = '';
            $data['updated_at'] =  '';
        }else{
            $today = date('Y-m-d');
            $data['ModifyBy'] = User::get_user_full_name();
            $data['updated_at'] =  $today;
        }

        $valid['valid'] = 1;
        $valid['data'] = $data;
        return $valid;
    }

    public static function upload_check($file_name,$data){
        $selection = $data['selection'];
        $status = array('status' => 1,
                        'message' => '',
                        'ProcessID'=>'');
        $CompanyID = User::get_companyID();
        $where = ['CompanyId'=>$CompanyID];
        if(User::is("AccountManager") ){
            $where['Owner']=User::get_userID();
        }
        $Accounts = Account::where($where)->select(['AccountName','AccountID'])->lists('AccountID','AccountName');
        $Accounts = array_change_key_case($Accounts);
        if (!empty($file_name)) {
            $results =  Excel::load($file_name, function ($reader){
                $reader->formatDates(true, 'Y-m-d');
            })->get();
            $results = json_decode(json_encode($results), true);
            $lineno = 2;
            $ProcessID = GUID::generate();
            $status['ProcessID'] = $ProcessID;
            $batchinsert = [];
            $counter = 0;
            foreach($results as $row){
                if (empty($row[$selection['AccountName']])) {
                    $status['message'].= ' <br>Account Name is empty at line no' . $lineno;
                    $status['status'] = 0;
                }elseif (!in_array(strtolower(trim($row[$selection['AccountName']])), $Accounts)) {
                    $status['message'].= " <br>Invalid Account Name '" . $row[$selection['AccountName']] . "' at line no " . $lineno;
                    $status['status'] = 0;
                }
                if (empty($row[$selection['PaymentDate']])) {
                    $status['message'].= ' <br>Payment Date is empty at line no ' . $lineno;
                    $status['status'] = 0;
                }else{
                    $date = formatSmallDate(trim($row[$selection['PaymentDate']]),$selection['DateFormat']);
                    if (empty($date)) {
                        $status['message'].= '<br>Invalid Payment Date at line no ' . $lineno;
                        $status['status'] = 0;
                    }
                }
                if (empty($row[$selection['PaymentMethod']])) {
                    $status['message'].= ' <br>Payment Method is empty at line no ' . $lineno;
                    $status['status'] = 0;
                }elseif (!in_array(strtolower(trim($row[$selection['PaymentMethod']])), array_map('strtolower', Payment::$method))) {
                    $status['message'] .= " <br>Invalid Payment Method : '" . $row[$selection['PaymentMethod']] . "' at line no " . $lineno;
                    $status['status'] = 0;
                }
                if (empty($row[$selection['PaymentType']])) {
                    $status['message'].= ' <br>Action is empty at line no ' . $lineno;
                    $status['status'] = 0;
                }elseif(!in_array(strtolower(trim($row[$selection['PaymentType']])), array_map('strtolower', Payment::$action) )){
                    $status['message'] .= " <br>Invalid Action : '".$row[$selection['PaymentType']]."' at line no ".$lineno;
                    $status['status'] = 0;
                }

                if (empty($row[$selection['Amount']])) {
                    $status['message'].= ' <br>Amount is empty at line no ' . $lineno;
                    $status['status'] = 0;
                }elseif(!is_numeric($row[$selection['Amount']])){
                    $status['message'].= ' <br>Invalid Amount at line no ' . $lineno;
                    $status['status'] = 0;
                }
                if ($status['status'] != 0) {
                    $PaymentStatus = 'Pending Approval';
                    if(User::is('BillingAdmin') || User::is_admin()){
                        $PaymentStatus = 'Approved';
                    }
                    $temp = array('CompanyID' => $CompanyID,
                        'ProcessID' => $ProcessID,
                        'AccountID' => $Accounts[strtolower(trim($row[$selection['AccountName']]))],
                        'PaymentDate' => trim($row[$selection['PaymentDate']]),
                        'PaymentMethod' => trim(strtoupper($row[$selection['PaymentMethod']])),
                        'PaymentType' => trim(ucfirst($row[$selection['PaymentType']])),
                        'Status' => $PaymentStatus,
                        'Amount' => trim($row[$selection['Amount']]);
                    if (!empty($row[$selection['InvoiceNo']])) {
                        $temp['InvoiceNo'] = trim($row[$selection['InvoiceNo']]);
                    }
                    if (!empty($row[$selection['Notes']])) {
                        $temp['Notes'] = trim($row[$selection['Notes']]);
                    }
                    $batchinsert[$counter] = $temp;
                }
                $counter++;
                $lineno++;
            }
            if($status['status'] == 0){
                return $status;
            }
            if($status['status'] == 1) {
                if (!PaymentTemp::insert($batchinsert)) {
                    $status['message'] = 'Some thing wrong with database';
                    return $status;
                }
            }
            $result = DB::connection('sqlsrv2')->select("CALL  prc_validatePayments ('" . $CompanyID . "','".$ProcessID."')");
            if(!empty($result)>0){
                $status['message'] = $result;
                $status['status'] = 1;
                return $status;
            }
            $status['status'] = 2;
            return $status;
        }
    }

}