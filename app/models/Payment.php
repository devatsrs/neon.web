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

}