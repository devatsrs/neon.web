<?php

class InvoicesCustomerController extends \BaseController {

    public function ajax_datagrid($type) {
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $companyID = Customer::get_companyID();
        $columns = ['InvoiceID','AccountName','InvoiceNumber','IssueDate','GrandTotal','InvoiceStatus','InvoiceID'];
        $data['InvoiceType'] = $data['InvoiceType'] == 'All'?'':$data['InvoiceType'];
        $data['zerovalueinvoice'] = $data['zerovalueinvoice']== 'true'?1:0;
        $data['AccountID'] = Customer::get_accountID();
        $data['IssueDateStart'] = empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd'] = empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];
        $data['Overdue'] = $data['Overdue']== 'true'?1:0;
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_CustomerPanel_getInvoice (".$companyID.",".intval($data['AccountID']).",'".$data['InvoiceNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."',".intval($data['InvoiceType']).",".$data['Overdue'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {
            if(isset($data['zerovalueinvoice']) && $data['zerovalueinvoice'] == 1){
                $excel_data  = DB::connection('sqlsrv2')->select($query.',1,1)');
            }else{
                $excel_data  = DB::connection('sqlsrv2')->select($query.',1,0)');
            }
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID) .'/Invoice.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID) .'/Invoice.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

        }
        if(isset($data['zerovalueinvoice']) && $data['zerovalueinvoice'] == 1){
            $query = $query.',0,1)';
        }else{
            $query .=',0,0)';
        }
        return DataTableSql::of($query,'sqlsrv2')->make();
    }
    /**
     * Display a listing of the resource.
     * GET /invoices
     *
     * @return Response
     */
    public function index()
    {
        Invoice::multiLang_init();
        $CompanyID 					 =  Customer::get_companyID();
        $invoice_status_json = json_encode(Invoice::get_invoice_status());
        return View::make('customer.invoices.index',compact('invoice_status_json','CompanyID'));
    }


    public function print_preview($id) {

        $Invoice = Invoice::find($id);
        $InvoiceDetail = InvoiceDetail::where(["InvoiceID"=>$id])->get();
        $Account  = Account::find($Invoice->AccountID);
        $Currency = Currency::find($Account->CurrencyId);
        $CurrencyCode = !empty($Currency)?$Currency->Code:'';
        $InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($Invoice->AccountID);
        $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
        if(empty($InvoiceTemplate->CompanyLogoUrl)){
            $logo = 'http://placehold.it/250x100';
        }else{
            $logo = AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key);
        }
        return View::make('invoices.invoice_view', compact('Invoice','InvoiceDetail','Account','InvoiceTemplate','CurrencyCode','logo'));
    }
    public function invoice_preview($id) {

        $Invoice = Invoice::find($id);
        if(!empty($Invoice)) {
            $InvoiceDetail = InvoiceDetail::where(["InvoiceID" => $id])->get();
            $Account = Account::find($Invoice->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency) ? $Currency->Code : '';
            $InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($Invoice->AccountID);
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            if (empty($InvoiceTemplate->CompanyLogoUrl)) {
                $logo = 'http://placehold.it/250x100';
            } else {
                $logo = AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key);
            }
            return View::make('invoices.invoice_cview', compact('Invoice', 'InvoiceDetail', 'Account', 'InvoiceTemplate', 'CurrencyCode', 'logo'));
        }
    }

    public function pdf_view($id) {

        $pdf_path = $this->generate_pdf($id);
        return Response::download($pdf_path);
    }

    /** This function is not in use now, We are creating PDF with RMService */
    public function generate_pdf($id){
        if($id>0) {
            $Invoice = Invoice::find($id);
            $InvoiceDetail = InvoiceDetail::where(["InvoiceID" => $id])->get();
            $Account = Account::find($Invoice->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency)?$Currency->Code:'';
            $InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($Invoice->AccountID);
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            if (empty($InvoiceTemplate->CompanyLogoUrl)) {
                $as3url = 'http://placehold.it/250x100';
            } else {
                $as3url = (AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key));
            }
            $logo = CompanyConfiguration::get('UPLOAD_PATH') . '/' . basename($as3url);
            file_put_contents($logo, file_get_contents($as3url));
            $usage_data = array();
            $file_name = 'Invoice--' . date('d-m-Y') . '.pdf';
            if($InvoiceTemplate->InvoicePages == 'single_with_detail') {
                foreach ($InvoiceDetail as $Detail) {
                    if (isset($Detail->StartDate) && isset($Detail->EndDate) && $Detail->StartDate != '1900-01-01' && $Detail->EndDate != '1900-01-01') {

                        $companyID = $Account->CompanyId;
                        $start_date = $Detail->StartDate;
                        $end_date = $Detail->EndDate;
                        $pr_name = 'call prc_getInvoiceUsage (';

                        $query = $pr_name . $companyID . ",'" . $Invoice->AccountID . "','" . $start_date . "','" . $end_date . "')";
                        DB::connection('sqlsrv2')->setFetchMode(PDO::FETCH_ASSOC);
                        $usage_data = DB::connection('sqlsrv2')->select($query);
                        $usage_data = json_decode(json_encode($usage_data), true);
                        $file_name =  'Invoice-From-' . Str::slug($start_date) . '-To-' . Str::slug($end_date) . '.pdf';
                        break;
                    }
                }
            }
			$print_type = 'Invoice';
            $body = View::make('invoices.pdf', compact('Invoice', 'InvoiceDetail', 'Account', 'InvoiceTemplate', 'usage_data', 'CurrencyCode', 'logo','print_type'))->render();
            $destination_dir = CompanyConfiguration::get('UPLOAD_PATH') . '/'. AmazonS3::generate_path(AmazonS3::$dir['INVOICE_UPLOAD'],$Account->CompanyId) ;
            if (!file_exists($destination_dir)) {
                mkdir($destination_dir, 0777, true);
            }
            $save_path = $destination_dir .  GUID::generate().'-'. $file_name;
            PDF::loadHTML($body)->setPaper('a4')->setOrientation('potrait')->save($save_path);
            //@unlink($logo);
            return $save_path;
        }
    }

    public function pay_now($id){
        $data = Input::all();
        //$id = User::get_userID();
        $account = Account::find($id);
        $CompanyID = $account->CompanyId;
        //$CompanyID = User::get_companyID();
        $CreatedBy = Customer::get_user_full_name();
        $Invoiceids = $data['InvoiceIDs'];
        $AccountPaymentProfileID = $data['AccountPaymentProfileID'];
        $PaymentData = array();
        $PaymentData['AccountID'] = $id;
        $PaymentData['CompanyID'] = $CompanyID;
        $PaymentData['CreatedBy'] = $CreatedBy;
        $PaymentData['AccountPaymentProfileID'] = $AccountPaymentProfileID;
        $PaymentData['InvoiceIDs'] = $Invoiceids;
        $CustomerProfile = AccountPaymentProfile::find($AccountPaymentProfileID);

        $outstanginamount = Account::getOutstandingInvoiceAmount($CompanyID,$account->AccountID,$Invoiceids, get_round_decimal_places($account->AccountID));
        if ($outstanginamount > 0 ) {
            if (!empty($CustomerProfile)) {

                $Invoices = explode(',', $Invoiceids);
                $fullnumber = '';
                if(count($Invoices)>0){
                    foreach($Invoices as $inv){
                        $AllInvoice = Invoice::find($inv);
                        $fullnumber.= $AllInvoice->FullInvoiceNumber.',';
                    }
                }
                if($fullnumber!=''){
                    $fullnumber = rtrim($fullnumber,',');
                }

                $PaymentData['InvoiceNumber'] = $fullnumber;
                $PaymentGateway = PaymentGateway::getName($CustomerProfile->PaymentGatewayID);
                $PaymentData['PaymentGateway'] = $PaymentGateway;
                $PaymentData['outstanginamount'] = $outstanginamount;
                $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($CustomerProfile->PaymentGatewayID);
                $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $CompanyID);
                $PaymentResponse = $PaymentIntegration->paymentWithProfile($PaymentData);
                return json_encode($PaymentResponse);
            }else{
                return json_encode(array("status" => "failed", "message" => Lang::get('routes.PAGE_INVOICE_MSG_ACCOUNT_PROFILE_NOT_SET')));
            }

        }else{
            return json_encode(array("status" => "failed", "message" => Lang::get('routes.CUST_PANEL_PAGE_INVOICE_MODAL_PAY_NOW_MSG_TOTAL_OUTSTANDING_IS_LESS_OR_EQUAL_TO_ZERO')));
        }




        //return AccountPaymentProfile::paynow($CompanyID,$id,$Invoiceids,$CreatedBy,$AccountPaymentProfileID);

    }

    public function  download_invoice_file($id){
        $DocumentFile = Invoice::where(["InvoiceID"=>$id])->pluck('Attachment');
        $FilePath =  AmazonS3::preSignedUrl($DocumentFile);
        if(file_exists($FilePath)){
            download_file($FilePath);
        }else if(is_amazon() == true){
            header('Location: '.$FilePath);
        }
        exit;
        /*$DocumentFile = CompanyConfiguration::get('UPLOAD_PATH') . '/'.$DocumentFile;
        if(file_exists($DocumentFile)){
            download_file($DocumentFile);
        }else{
            echo $DocumentFile;
            $FilePath =  AmazonS3::preSignedUrl($DocumentFile);
            echo $FilePath;exit;
            header('Location: '.$FilePath);
        }
        exit;*/
    }

    public function ajax_datagrid_total()
    {
        $data 						 = 	Input::all();
        $data['iDisplayStart'] 		 =	0;
        $data['iDisplayStart'] 		+=	1;
        $data['iSortCol_0']			 =  0;
        $data['sSortDir_0']			 =  'desc';
        $companyID 					 =  Customer::get_companyID();
        $columns 					 =  ['InvoiceID','AccountName','InvoiceNumber','IssueDate','GrandTotal','PendingAmount','InvoiceStatus','InvoiceID'];
        $data['InvoiceType'] 		 = 	$data['InvoiceType'] == 'All'?'':$data['InvoiceType'];
        $data['zerovalueinvoice'] 	 =  $data['zerovalueinvoice']== 'true'?1:0;
        $data['IssueDateStart'] = empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd'] = empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];
        $data['Overdue'] = $data['Overdue']== 'true'?1:0;
        $sort_column 				 =  $columns[$data['iSortCol_0']];
        $data['AccountID'] = Customer::get_accountID();

        $query = "call prc_CustomerPanel_getInvoice (".$companyID.",".intval($data['AccountID']).",'".$data['InvoiceNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."',".intval($data['InvoiceType']).",".$data['Overdue'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['zerovalueinvoice']) && $data['zerovalueinvoice'] == 1)
        {
            $query = $query.',0,1)';
        }
        else
        {
            $query .=',0,0)';
        }

        $result   = DataTableSql::of($query,'sqlsrv2')->getProcResult(array('ResultCurrentPage','Total_grand_field'));
        $result2  = $result['data']['Total_grand_field'][0]->total_grand;
        $result4  = array(
			"total_grand"=>$result['data']['Total_grand_field'][0]->currency_symbol.$result['data']['Total_grand_field'][0]->total_grand,
			"os_pp"=>$result['data']['Total_grand_field'][0]->currency_symbol.$result['data']['Total_grand_field'][0]->TotalPayment.'/'.$result['data']['Total_grand_field'][0]->TotalPendingAmount,
		);

        return json_encode($result4,JSON_NUMERIC_CHECK);
    }

    public function getInvoiceDetail(){
        $data = Input::all();
        $InvoiceDetail = InvoiceDetail::where(["InvoiceID" => $data['InvoiceID']])->select(["InvoiceDetailID","StartDate", "EndDate","Description"])->first();

        $result = array();
        $result['InvoiceDetailID'] = $InvoiceDetail->InvoiceDetailID;
        $StartTime =  explode(' ',$InvoiceDetail->StartDate);
        $EndTime =  explode(' ',$InvoiceDetail->EndDate);
        $result['StartDate'] = $StartTime[0];
        $result['EndDate'] = $EndTime[0];
        $result['Description'] = $InvoiceDetail->Description;
        $result['StartTime'] = $StartTime[1];
        $result['EndTime'] = $EndTime[1];
        //return json_encode($result);

        return Response::json(array('InvoiceDetailID' => $result['InvoiceDetailID'], 'StartDate' => $result['StartDate'],'EndDate'=>$result['EndDate'],'Description'=>$result['Description'],'StartTime'=>$result['StartTime'],'EndTime'=>$result['EndTime']));

    }

}