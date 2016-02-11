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
        $status = array('status' => 1, 'message' => '');
        $CompanyID = User::get_companyID();
        $where = ['CompanyId'=>$CompanyID];
        if(!User::is_admin()){
            $where['Owner']=User::get_userID();
        }
        $Accounts = Account::where($where)->select(['AccountName','AccountID'])->lists('AccountID','AccountName');
        //$file_name = 'C:\\uploads\\1\\PaymentUpload\\2016\\02\\05\\Payments_8A15D299-4B46-48C6-B5C9-7935888C87A9.csv';
        if (!empty($file_name)) {
            $results =  Excel::load($file_name, function ($reader){
                $reader->formatDates(true, 'Y-m-d');
            })->get();
            $results = json_decode(json_encode($results), true);
            $lineno = 2;
            $ProcessID = GUID::generate();
            $batchinsert = [];
            $counter = 0;
            foreach($results as $row){
                if(isset($selection['AccountName'])) {
                    if (empty($row[$selection['AccountName']])) {
                        $status['message'] .= ' \n\rAccount Name is empty at line no' . $lineno;
                        $status['status'] = 0;
                    }
                }else{
                    $status['message'] = 'Not valid';
                    $status['status'] = 0;
                    return $status;
                }
                if(!in_array($row[$selection['AccountName']], $Accounts)){
                    $status['message'] .= ' \n\r'.$row[$selection['AccountName']].' is not exist in system at line no '.$lineno;
                    $status['status'] = 0;
                }
                if(isset($selection['PaymentDate'])) {
                    if (empty($row['Payment Date'])) {
                        $status['message'] .= ' \n\rPayment Date is empty at line no ' . $lineno;
                        $status['status'] = 0;
                    }else{
                        $date = formatSmallDate($row[$selection['PaymentDate']]);
                        if (empty($date)) {
                            $status['message'] .= '\n\rPayment Date is not valid at line no ' . $lineno;
                            $status['status'] = 0;
                        }
                    }
                }else{
                    $status['message'] = 'Not valid';
                    $status['status'] = 0;
                    return $status;
                }
                if(isset($selection['PaymentMethod'])) {
                    if (empty($row[$selection['PaymentMethod']])) {
                        $status['message'] .= ' \n\rPayment Method is empty at line no ' . $lineno;
                        $status['status'] = 0;
                    }
                }else{
                    $status['message'] = 'Not valid';
                    $status['status'] = 0;
                    return $status;
                }
                if(!in_array($row[$selection['PaymentMethod']], Payment::$method)){
                    $status['message'] .= ' \n\rInvalid Payment Method : '.$row[$selection['PaymentMethod']].' at line no '.$lineno;
                    $status['status'] = 0;
                    return $status;
                }
                if(isset($selection['PaymentType'])) {
                    if (empty($row[$selection['PaymentType']])) {
                        $status['message'] .= ' \n\rAction is empty at line no ' . $lineno;
                        $status['status'] = 0;
                    }
                }else{
                    $status['message'] = 'Not valid';
                    $status['status'] = 0;
                    return $status;
                }
                if(!in_array($row[$selection['PaymentType']], Payment::$action)){
                    $status['message'] .= ' \n\rInvalid Payment Type : '.$row[$selection['PaymentType']].' at line no '.$lineno;
                    $status['status'] = 0;
                    return $status;
                }
                if(isset($selection['Amount'])) {
                    if (empty($row[$selection['Amount']])) {
                        $status['message'] .= ' \n\rAmount is empty at line no ' . $lineno;
                        $status['status'] = 0;
                    }
                }else{
                    $status['message'] = 'Not valid';
                    $status['status'] = 0;
                    return $status;
                }
                if(isset($Accounts[$row[$selection['AccountName']]])) {
                    $temp = array('CompanyID' => $CompanyID,
                        'ProcessID' => $ProcessID,
                        'AccountID' => $Accounts[$row[$selection['AccountName']]],
                        'PaymentDate' => $row[$selection['PaymentDate']],
                        'PaymentMethod' => $row[$selection['PaymentMethod']],
                        'PaymentType' => $row[$selection['PaymentType']],
                        'Amount' => $row[$selection['Amount']]);
                    if (isset($selection['InvoiceNo'])) {
                        $temp['InvoiceNo'] = $row[$selection['InvoiceNo']];
                    }
                    if (isset($selection['Notes'])) {
                        $temp['Notes'] = $row[$selection['Notes']];
                    }
                    $batchinsert[$counter] = $temp;
                }
                $counter++;
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