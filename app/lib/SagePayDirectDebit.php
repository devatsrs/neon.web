<?php

/** SagePayDirectDebit
 * https://sagepay.co.za/integration/sage-pay-integration-documents/debit-order-collection-technical-guide
 * Created by PhpStorm.
 * User: bhavin
 * Date: 06/09/2017
 * Time: 6:25 PM
 */
class SagePayDirectDebit
{

    var $ServiceKey ;
    var $SoftwareVendorKey ;
    var $ipn ;
    var $status ;
    var $item_title;
    var $method;
    var $BatchUpload;


    //https://sagepay.co.za/integration/sage-pay-integration-documents/pay-now-gateway-technical-guide/

    function __Construct(){

        $this->method  = SiteIntegration::$SagePayDirectDebitSlug;

        $sagepay_obj	 = SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$SagePayDirectDebitSlug);

        if( !empty($sagepay_obj) ) {

			$this->ServiceKey 	            = 	$sagepay_obj->ServiceKey;
            $this->SoftwareVendorKey		= 	$sagepay_obj->SoftwareVendorKey;
            $this->BatchUpload		= 	$sagepay_obj->BatchUpload;

            $this->status = true;

        }else{

            $this->status = false;
        }

    }

    public function status(){

        return $this->status;
    }

    public function verifyBankAccount($data){
        // Create the SoapClient instance
        $url         = "https://ws.sagepay.co.za/NIWS/niws_validation.svc?singleWsdl";
        try{
            $client     = new SoapClient($url, array("trace" => 1, "exception" => 0));
        } catch (Exception $e) {
            Log::error($e);
            //return ["return_var"=>$e->getMessage()];
            $response['status'] = 'fail';
            $response['error'] = $e->getMessage();

            return $response;
        }
        $param = array();
        $param['ServiceKey']=$this->ServiceKey;
        $param['AccountNumber']=$data['AccountNumber'];
        $param['BranchCode']=$data['BranchCode'];
        $param['AccountType']=$data['AccountType'];
        try {
            $soapresponse = $client->__soapCall('ValidateBankAccount', array($param));
            if(isset($soapresponse->ValidateBankAccountResult) && $soapresponse->ValidateBankAccountResult!=''){
                Log::info(print_r($soapresponse,true));
                $result = $soapresponse->ValidateBankAccountResult;
                if($result==0){
                    $response['status'] = 'Success';
                    $response['VerifyStatus'] = 'verified';
                }elseif($result==1){
                    $response['status'] = 'fail';
                    $response['error'] = 'Invalid branch code';
                }elseif($result==2){
                    $response['status'] = 'fail';
                    $response['error'] = 'Account number failed check digit validation';
                }elseif($result==3){
                    $response['status'] = 'fail';
                    $response['error'] = 'Invalid account type';
                }elseif($result==4){
                    $response['status'] = 'fail';
                    $response['error'] = 'Input data incorrect';
                }elseif($result==100){
                    $response['status'] = 'fail';
                    $response['error'] = 'Authentication failed';
                }else{
                    $response['status'] = 'fail';
                    $response['error'] = 'Web service error';
                }

            }else{
                $response['status'] = 'fail';
                $response['error'] = 'Web service error';
            }


        }catch (Exception $e) {
            Log::error($e);
            //return ["return_var"=>$e->getMessage()];
            $response['status'] = 'fail';
            $response['error'] = $e->getMessage();

            return $response;
        }

        return $response;
    }

    public function sagebatchfileexport($data){
        $MarkPaid = $data['MarkPaid'];
        $AllFileData = $this->sagebatchfiledownload($data);
        //log::info($AllFileData);
        $Response = $this->getSageFormat($AllFileData,$MarkPaid);
        return $Response;
    }

    public function sagebatchfiledownload($data){
        $Invoices  = $data['Invoices'];
        $CompanyID = $data['CompanyID'];
        $AllFileData = array();
        foreach($Invoices as $Invoice){
            log::info('invoice '.$Invoice);
            $Invdata = Invoice::find($Invoice);
            $outstanginamount = Account::getOutstandingInvoiceAmount($CompanyID,$Invdata->AccountID,$Invoice, 2);
            if ($outstanginamount > 0 ) {
                $CustomerProfile = $this->getAccountPaymentProfile($Invdata->AccountID);
                if(!empty($CustomerProfile)){
                    //log::info($CustomerProfile);
                    $customerData = array();
                    $Options = json_decode($CustomerProfile->Options,true);
                    if(!empty($Options['VerifyStatus']) && $Options['VerifyStatus']='verified'){
                        $customerData['AccountName'] = $Options['AccountName'];
                        $customerData['BankAccountName'] = Crypt::decrypt($Options['BankAccountName']);
                        $customerData['AccountNumber'] = Crypt::decrypt($Options['AccountNumber']);
                        $customerData['BranchCode'] = Crypt::decrypt($Options['BranchCode']);
                        $customerData['AccountHolderType'] = $Options['AccountHolderType'];
                        $customerData['InvoiceID'] = $Invoice;
                        $customerData['FullInvoiceNumber'] = $Invdata->FullInvoiceNumber;
                        $customerData['Amount'] = $outstanginamount;
                        if(empty($customerData['AccountName']) || empty($customerData['BankAccountName']) || empty($customerData['AccountNumber'])
                            || empty($customerData['BranchCode']) || empty($customerData['AccountHolderType']) || empty($customerData['InvoiceID']) || empty($customerData['Amount'])
                        ){
                            log::info("Somthing is blank");
                        }else{
                            log::info("Invoice Full Number ".$customerData['FullInvoiceNumber']);
                            $AllFileData[] = $customerData;
                        }
                    }
                }
            }
        }
        return $AllFileData;
    }

    public function getAccountPaymentProfile($AccountID){
        $response = false;
        $Accounts = Account::find($AccountID);
        if(!empty($Accounts->PaymentMethod) && $Accounts->PaymentMethod='SagePayDirectDebit'){
            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($Accounts->PaymentMethod);
            if (!empty($PaymentGatewayID)) {
                $CustomerProfile = AccountPaymentProfile::getActiveProfile($AccountID, $PaymentGatewayID);
                if(!empty($CustomerProfile)){
                    return $CustomerProfile;
                }
            }
        }
        return $response;
    }

    public function getSageFormat($data,$MarkPaid){
    /**
     * Header
     * Record Identifier(H),Service Key,Version(1),Instruction(Update,SameDay,TwoDay),Batch name(retrieving Batch and load report),Action Date(CCYYMMDD),Software Vendor Key
     * Example
     * H   C74EF975-5429-4663-85FB-2A64CA0FB9EF	1	TwoDay	My Test Batch	20100331	24ade73c-98cf-47b3-99be-cc7b867b3080
     * Key Record
     * Record Identifier(T),Account reference,Account name,Banking detail type,Bank account name,Bank account type(Bank account),Branch code,Filler(0),Bank account number,Amount,	Extra 1
     * T 101 102 131 132 133 134 135 136 162 301
     * Example
     * T	Acc001	AccName001	1	BankAccountName001	1	470010	0	25000000000	100 INV100
     * Footer record
     * Record Identifier(F),No of transactions(A count of the transaction records),Sum of amounts(in cents),End-of-file indicator(9999)
     * Example
     * F	2	0	9999
     *
     */
      $Response = array();
      $BatchName = 'sage'.date('YmdHis');
      $ActionDate = date('Ymd');
      $Instruction = !empty($this->BatchUpload)?$this->BatchUpload:'TwoDay';
      $content='';
      $header  ='H'."\t".$this->ServiceKey."\t".'1'."\t".$Instruction."\t".$BatchName."\t".$ActionDate."\t".$this->SoftwareVendorKey;
      $recordheader='K'."\t".'101'."\t".'102'."\t".'131'."\t".'132'."\t".'133'."\t".'134'."\t".'135'."\t".'136'."\t".'162'."\t".'301';
      $record = '';
      $rowcount=0;
      $rowamount=0;
      if(!empty($data) && count($data)>0){
          foreach($data as $sagedata){
              $amount = $sagedata['Amount']*100;
              $record.='T'."\t".$sagedata['AccountName']."\t".$sagedata['AccountName']."\t".'1'."\t".$sagedata['BankAccountName']."\t".$sagedata['AccountHolderType']."\t".$sagedata['BranchCode']."\t".'0'."\t".$sagedata['AccountNumber']."\t".$amount."\t".$sagedata['FullInvoiceNumber']."\n";
              $rowcount++;
              $rowamount=$rowamount+$amount;
          }
      }

      //$recoed  ='T'."\t".'Acc001'."\t".'1'."\t".'BankAccountName001'."\t".'1'."\t".'470010'."\t".'25000000000'."\t".'100'."\t".'INV100';
      //$recoed1 ='T'."\t".'Acc002'."\t".'1'."\t".'BankAccountName002'."\t".'1'."\t".'470010'."\t".'25000000001'."\t".'100'."\t".'INV200';
      $footer  ='F'."\t".$rowcount."\t".$rowamount."\t".'9999';

      $AllContent = $header."\n".$recordheader."\n".$record.$footer;
      log::info('invoice post content');
      //log::info($AllContent);
      $filename = 'InvoiceSagePayExport'.date('Ymdhis').'.txt';
      $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/'.$filename;
      $username = User::get_user_full_name();
      log::info('File Path '.$file_path);
      file_put_contents($file_path,$AllContent);
      if(!empty($MarkPaid) && $MarkPaid==1){
          if(!empty($data) && count($data)>0){
              foreach($data as $sagedata){
               $InvoiceID = $sagedata['InvoiceID'];
               $Invoice = Invoice::find($InvoiceID);
               $Invoice->update(array('InvoiceStatus' => Invoice::PAID));
               $invoiceloddata = array();
               $invoiceloddata['InvoiceID'] = $InvoiceID;
               $invoiceloddata['Note'] = 'Paid(SagePayExport) By ' . $username;
               $invoiceloddata['created_at'] = date("Y-m-d H:i:s");
               $invoiceloddata['InvoiceLogStatus'] = InVoiceLog::UPDATED;
               InVoiceLog::insert($invoiceloddata);
              }
          }
      }
        $Response['file_path'] = $file_path;
     return $Response;
       //$response = $this->uploadBachfile($AllContent);
       //return $response;
    }

    public function uploadBachfile($AllContent){
        log::info('invoice batch start');
        $url         = "https://ws.sagepay.co.za/NIWS/NIWS_NIF.svc?singleWsdl";
        try{
            $client     = new SoapClient($url, array("trace" => 1, "exception" => 0));
        } catch (Exception $e) {
            Log::error($e);
            //return ["return_var"=>$e->getMessage()];
            $response['status'] = 'fail';
            $response['error'] = $e->getMessage();

            return $response;
        }
        $param = array();
        $param['ServiceKey']=$this->ServiceKey;
        $param['File']=$AllContent;
        try {
            $soapresponse = $client->__soapCall('BatchFileUpload', array($param));
            if(isset($soapresponse->BatchFileUploadResult) && $soapresponse->BatchFileUploadResult!=''){
                Log::info(print_r($soapresponse,true));
                $result = $soapresponse->BatchFileUploadResult;
                if($result==100){
                    $response['status'] = 'fail';
                    $response['error'] = 'Authentication failure';
                }elseif($result==102){
                    $response['status'] = 'fail';
                    $response['error'] = 'Parameter error';
                }elseif($result==200){
                    $response['status'] = 'fail';
                    $response['error'] = 'General code exception';
                }else{
                    $response['status'] = 'Success';
                    $response['response'] = $result;
                }

            }else{
                $response['status'] = 'fail';
                $response['error'] = 'Web service error';
            }


        }catch (Exception $e) {
            Log::error($e);
            //return ["return_var"=>$e->getMessage()];
            $response['status'] = 'fail';
            $response['error'] = $e->getMessage();

            //return $response;
        }
        //Log::error($response);
        log::info('invoice batch start');

        return $response;
    }

}