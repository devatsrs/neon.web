<?php

class InvoicesController extends \BaseController {
	
	public function ajax_datagrid_total() 
	{
        $data 						 = 	Input::all();
		$data['iDisplayStart'] 		 =	0;
        $data['iDisplayStart'] 		+=	1;
		$data['iSortCol_0']			 =  0;     
		$data['sSortDir_0']			 =  'desc';
        $companyID 					 =  User::get_companyID();
        $columns 					 =  ['InvoiceID','AccountName','InvoiceNumber','IssueDate','GrandTotal','PendingAmount','InvoiceStatus','InvoiceID'];
        $data['InvoiceType'] 		 = 	$data['InvoiceType'] == 'All'?'':$data['InvoiceType'];
        $data['zerovalueinvoice'] 	 =  $data['zerovalueinvoice']== 'true'?1:0;
        $data['IssueDateStart'] 	 =  empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd']        =  empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];
        $sort_column 				 =  $columns[$data['iSortCol_0']];
		
        $query = "call prc_getInvoice (".$companyID.",".intval($data['AccountID']).",'".$data['InvoiceNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."',".intval($data['InvoiceType']).",'".$data['InvoiceStatus']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',".intval($data['CurrencyID'])."";
		
        if(isset($data['Export']) && $data['Export'] == 1)
		{
            if(isset($data['zerovalueinvoice']) && $data['zerovalueinvoice'] == 1)
			{
                $excel_data  = DB::connection('sqlsrv2')->select($query.',1,0,1,"")');
            }
			else
			{
                $excel_data  = DB::connection('sqlsrv2')->select($query.',1,0,0,"")');
            }
			
            $excel_data = json_decode(json_encode($excel_data),true);
            Excel::create('Invoice', function ($excel) use ($excel_data)
			{
                $excel->sheet('Invoice', function ($sheet) use ($excel_data)
				{
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
		
        if(isset($data['zerovalueinvoice']) && $data['zerovalueinvoice'] == 1)
		{
            $query = $query.',0,0,1,"")';
        }
		else
		{
            $query .=',0,0,0,"")';
        }
    	
		$result   = DataTableSql::of($query,'sqlsrv2')->getProcResult(array('ResultCurrentPage','Total_grand_field'));
		$result2  = $result['data']['Total_grand_field'][0]->total_grand;
		$result4  = array(
			"total_grand"=>$result['data']['Total_grand_field'][0]->total_grand,
			"os_pp"=>$result['data']['Total_grand_field'][0]->first_amount.' / '.$result['data']['Total_grand_field'][0]->second_amount,
		);
		
		return json_encode($result4,JSON_NUMERIC_CHECK);		
	}

    public function ajax_datagrid($type) {
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $companyID = User::get_companyID();
        $columns = ['InvoiceID','AccountName','InvoiceNumber','IssueDate','GrandTotal','PendingAmount','InvoiceStatus','InvoiceID'];
        $data['InvoiceType'] = $data['InvoiceType'] == 'All'?'':$data['InvoiceType'];
        $data['zerovalueinvoice'] = $data['zerovalueinvoice']== 'true'?1:0;
        $data['IssueDateStart'] = empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd'] = empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getInvoice (".$companyID.",".intval($data['AccountID']).",'".$data['InvoiceNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."',".intval($data['InvoiceType']).",'".$data['InvoiceStatus']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',".intval($data['CurrencyID'])."";
        if(isset($data['Export']) && $data['Export'] == 1) {
            if(isset($data['zerovalueinvoice']) && $data['zerovalueinvoice'] == 1){
                $excel_data  = DB::connection('sqlsrv2')->select($query.',1,0,1,"")');
            }else{
                $excel_data  = DB::connection('sqlsrv2')->select($query.',1,0,0,"")');
            }
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Invoice.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Invoice.xlsx';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

            /*Excel::create('Invoice', function ($excel) use ($excel_data) {
                $excel->sheet('Invoice', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        if(isset($data['zerovalueinvoice']) && $data['zerovalueinvoice'] == 1){
            $query = $query.',0,0,1,"")';
        }else{
            $query .=',0,0,0,"")';
        }
        //echo $query;exit;
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
        $companyID = User::get_companyID();
        $accounts = Account::getAccountIDList();
		$DefaultCurrencyID    	=   Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $invoice_status_json = json_encode(Invoice::get_invoice_status());
        $emailTemplates = EmailTemplate::getTemplateArray(array('Type'=>EmailTemplate::INVOICE_TEMPLATE));
        $templateoption = [''=>'Select',1=>'New Create',2=>'Update'];
        $InvoiceNo = Invoice::where(array('CompanyID'=>$companyID,'InvoiceType'=>Invoice::INVOICE_OUT))->get(['InvoiceNumber']);
        $InvoiceNoarray = array();
        foreach($InvoiceNo as $Invoicerow){
            $InvoiceNoarray[] = $Invoicerow->InvoiceNumber;
        }
        $invoice = implode(',',$InvoiceNoarray);
        return View::make('invoices.index',compact('products','accounts','invoice_status_json','invoice','emailTemplates','templateoption','DefaultCurrencyID'));

    }

    /**
     * Show the form for creating a new resource.
     * GET /invoices/create
     *
     * @return Response
     */
    public function create()
    {

        $accounts = Account::getAccountIDList();
        $products = Product::getProductDropdownList();
        $taxes = TaxRate::getTaxRateDropdownIDListForInvoice();
        //$gateway_product_ids = Product::getGatewayProductIDs();
        return View::make('invoices.create',compact('accounts','products','taxes'));

    }

    /**
     *
     * */
    public function edit($id){


        //$str = preg_replace('/^INV/', '', 'INV021000');;
        if($id > 0) {

            $Invoice = Invoice::find($id);
            $InvoiceDetail = InvoiceDetail::where(["InvoiceID"=>$id])->get();
            $accounts = Account::getAccountIDList();
            $products = Product::getProductDropdownList();
            //$gateway_product_ids = Product::getGatewayProductIDs();
            $Account = Account::where(["AccountID" => $Invoice->AccountID])->select(["AccountName","BillingEmail", "TaxRateID","RoundChargesAmount","CurrencyId","InvoiceTemplateID"])->first();
            $CurrencyID = !empty($Invoice->CurrencyID)?$Invoice->CurrencyID:$Account->CurrencyId;
            $RoundChargesAmount = 2;
            if($Account->RoundChargesAmount > 0){
                $RoundChargesAmount = $Account->RoundChargesAmount;
            }
            $InvoiceTemplateID =$Account->InvoiceTemplateID;
            $InvoiceNumberPrefix = ($InvoiceTemplateID>0)?InvoiceTemplate::find($InvoiceTemplateID)->InvoiceNumberPrefix:'';
            $Currency = Currency::find($CurrencyID);
            $CurrencyCode = !empty($Currency)?$Currency->Code:'';
            $CompanyName = Company::getName();
            $taxes =  TaxRate::getTaxRateDropdownIDListForInvoice();
            $invoicelog =  InVoiceLog::where(array('InvoiceID'=>$id))->get();
            return View::make('invoices.edit', compact( 'id', 'Invoice','InvoiceDetail','InvoiceTemplateID','InvoiceNumberPrefix',  'CurrencyCode','CurrencyID','RoundChargesAmount','accounts', 'products', 'taxes','CompanyName','Account','invoicelog'));
        }
    }

    /**
     * Store Invoice
     */
    public function store(){
        $data = Input::all();

        if($data){

            $companyID = User::get_companyID();
            $CreatedBy = User::get_user_full_name();

            //$CurrencyId = Account::where("AccountID",intval($data["AccountID"]))->pluck('CurrencyId');
            $isAutoInvoiceNumber = true;
            if(!empty($data["InvoiceNumber"])){
                $isAutoInvoiceNumber = false;
            }
            $InvoiceData = array();
            $InvoiceData["CompanyID"] = $companyID;
            $InvoiceData["AccountID"] = intval($data["AccountID"]);
            $InvoiceData["Address"] = $data["Address"];
            $InvoiceData["InvoiceNumber"] = $LastInvoiceNumber = ($isAutoInvoiceNumber)?InvoiceTemplate::getAccountNextInvoiceNumber($data["AccountID"]):$data["InvoiceNumber"];
            $InvoiceData["IssueDate"] = $data["IssueDate"];
            $InvoiceData["PONumber"] = $data["PONumber"];
            $InvoiceData["SubTotal"] = str_replace(",","",$data["SubTotal"]);
            //$InvoiceData["TotalDiscount"] = str_replace(",","",$data["TotalDiscount"]);
			$InvoiceData["TotalDiscount"] = 0;
            $InvoiceData["TotalTax"] = str_replace(",","",$data["TotalTax"]);
            $InvoiceData["GrandTotal"] = floatval(str_replace(",","",$data["GrandTotal"]));
            $InvoiceData["CurrencyID"] = $data["CurrencyID"];
            $InvoiceData["InvoiceType"] = Invoice::INVOICE_OUT;
            $InvoiceData["InvoiceStatus"] = Invoice::AWAITING;
            $InvoiceData["ItemInvoice"] = Invoice::ITEM_INVOICE;
            $InvoiceData["Note"] = $data["Note"];
            $InvoiceData["Terms"] = $data["Terms"];
            $InvoiceData["FooterTerm"] = $data["FooterTerm"];
            $InvoiceData["CreatedBy"] = $CreatedBy;

            ///////////
            $rules = array(
                'CompanyID' => 'required',
                'AccountID' => 'required',
                'Address' => 'required',
                'InvoiceNumber' => 'required|unique:tblInvoice,InvoiceNumber,NULL,InvoiceID,CompanyID,'.$companyID,
                'IssueDate' => 'required',
                'CurrencyID' => 'required',
                'GrandTotal' => 'required',
                'InvoiceType' => 'required',
            );
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($InvoiceData, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            try{
                DB::connection('sqlsrv2')->beginTransaction();
                $Invoice = Invoice::create($InvoiceData);
                //Store Last Invoice Number.
                if($isAutoInvoiceNumber) {
                    InvoiceTemplate::find(Account::find($data["AccountID"])->InvoiceTemplateID)->update(array("LastInvoiceNumber" => $LastInvoiceNumber ));
                }

                $InvoiceDetailData = array();

                foreach($data["InvoiceDetail"] as $field => $detail){
                    $i=0;
                    foreach($detail as $value){
                        if( in_array($field,["Price","Discount","TaxAmount","LineTotal"])){
                            $InvoiceDetailData[$i][$field] = str_replace(",","",$value);
                        }else{
                            $InvoiceDetailData[$i][$field] = $value;
                        }
						$InvoiceDetailData[$i]["Discount"] 	= 	0;
                        $InvoiceDetailData[$i]["InvoiceID"] = $Invoice->InvoiceID;
                        $InvoiceDetailData[$i]["created_at"] = date("Y-m-d H:i:s");
                        $InvoiceDetailData[$i]["CreatedBy"] = $CreatedBy;
                        if(empty($InvoiceDetailData[$i]['ProductID'])){
                            unset($InvoiceDetailData[$i]);
                        }
                        $i++;
                    }
                }
                $invoiceloddata = array();
                $invoiceloddata['InvoiceID']= $Invoice->InvoiceID;
                $invoiceloddata['Note']= 'Created By '.$CreatedBy;
                $invoiceloddata['created_at']= date("Y-m-d H:i:s");
                $invoiceloddata['InvoiceLogStatus']= InVoiceLog::CREATED;
                InVoiceLog::insert($invoiceloddata);
                if (!empty($InvoiceDetailData) && InvoiceDetail::insert($InvoiceDetailData)) {
                    $pdf_path = Invoice::generate_pdf($Invoice->InvoiceID);
                    if (empty($pdf_path)) {
                        $error['message'] = 'Failed to generate Invoice PDF File';
                        $error['status'] = 'failure';
                        return $error;
                    } else {
                        $Invoice->update(["PDF" => $pdf_path]);
                    }

                    Log::info('PDF fullPath ' . $pdf_path);

                    DB::connection('sqlsrv2')->commit();

                    return Response::json(array("status" => "success", "message" => "Invoice Successfully Created",'LastID'=>$Invoice->InvoiceID,'redirect' => URL::to('/invoice')));
                } else {
                    DB::connection('sqlsrv2')->rollback();
                    return Response::json(array("status" => "failed", "message" => "Problem Creating Invoice."));
                }
            }catch (Exception $e){
                Log::info($e);
                DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Problem Creating Invoice. \n" . $e->getMessage()));
            }

        }

    }

    /**
     * Store Invoice
     */
    public function update($id){
        $data = Input::all();
        if(!empty($data) && $id > 0){

            $Invoice = Invoice::find($id);
            $companyID = User::get_companyID();
            $CreatedBy = User::get_user_full_name();

            $InvoiceData = array();
            $InvoiceData["CompanyID"] = $companyID;
            $InvoiceData["AccountID"] = $data["AccountID"];
            $InvoiceData["Address"] = $data["Address"];
            $InvoiceData["InvoiceNumber"] = $data["InvoiceNumber"];
            $InvoiceData["IssueDate"] = $data["IssueDate"];
            $InvoiceData["PONumber"] = $data["PONumber"];
            $InvoiceData["SubTotal"] = str_replace(",","",$data["SubTotal"]);
            //$InvoiceData["TotalDiscount"] = str_replace(",","",$data["TotalDiscount"]);
			$InvoiceData["TotalDiscount"] = 0;
            $InvoiceData["TotalTax"] = str_replace(",","",$data["TotalTax"]);
            $InvoiceData["GrandTotal"] = floatval(str_replace(",","",$data["GrandTotal"]));
            $InvoiceData["CurrencyID"] = $data["CurrencyID"];
            $InvoiceData["Note"] = $data["Note"];
            $InvoiceData["Terms"] = $data["Terms"];
            $InvoiceData["FooterTerm"] = $data["FooterTerm"];
            $InvoiceData["ModifiedBy"] = $CreatedBy;
            //$InvoiceData["InvoiceType"] = Invoice::INVOICE_OUT;

            ///////////
            $rules = array(
                'CompanyID' => 'required',
                'AccountID' => 'required',
                'Address' => 'required',
                'InvoiceNumber' => 'required|unique:tblInvoice,InvoiceNumber,'.$id.',InvoiceID,CompanyID,'.$companyID,
                'IssueDate' => 'required',
                'CurrencyID' => 'required',
                'GrandTotal' => 'required',
                //'InvoiceType' => 'required',
            );
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');
            $validator = Validator::make($InvoiceData, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            try{

                DB::connection('sqlsrv2')->beginTransaction();
                if(isset($Invoice->InvoiceID)) {

                    $Extralognote = '';
                    if($Invoice->GrandTotal != $InvoiceData['GrandTotal']){
                        $Extralognote = ' Total '.$Invoice->GrandTotal.' To '.$InvoiceData['GrandTotal'];
                    }
                    $invoiceloddata = array();
                    $invoiceloddata['InvoiceID']= $Invoice->InvoiceID;
                    $invoiceloddata['Note']= 'Updated By '.$CreatedBy.$Extralognote;
                    $invoiceloddata['created_at']= date("Y-m-d H:i:s");
                    $invoiceloddata['InvoiceLogStatus']= InVoiceLog::UPDATED;
                    $Invoice->update($InvoiceData);
                    InVoiceLog::insert($invoiceloddata);
                    $InvoiceDetailData = array();
                    //Delete all Invoice Data and then Recreate.
                    InvoiceDetail::where(["InvoiceID" => $Invoice->InvoiceID])->delete();
                    if (isset($data["InvoiceDetail"])) {
                        foreach ($data["InvoiceDetail"] as $field => $detail) {
                            $i = 0;
                            foreach ($detail as $value) {
                                if( in_array($field,["Price","Discount","TaxAmount","LineTotal"])){
                                    $InvoiceDetailData[$i][$field] = str_replace(",","",$value);
                                }else{
                                    $InvoiceDetailData[$i][$field] = $value;
                                }
								$InvoiceDetailData[$i]["Discount"] 	= 	0;
                                $InvoiceDetailData[$i]["InvoiceID"] = $Invoice->InvoiceID;
                                $InvoiceDetailData[$i]["created_at"] = date("Y-m-d H:i:s");
                                $InvoiceDetailData[$i]["updated_at"] = date("Y-m-d H:i:s");
                                $InvoiceDetailData[$i]["CreatedBy"] = $CreatedBy;
                                $InvoiceDetailData[$i]["ModifiedBy"] = $CreatedBy;
                                if(isset($InvoiceDetailData[$i]["InvoiceDetailID"])){
                                    unset($InvoiceDetailData[$i]["InvoiceDetailID"]);
                                }
                                if(empty($InvoiceDetailData[$i]['ProductID'])){
                                    unset($InvoiceDetailData[$i]);
                                }
                                $i++;
                            }
                        }
                        //print_r($InvoiceDetailData);
                        if (InvoiceDetail::insert($InvoiceDetailData)) {
                            $pdf_path = Invoice::generate_pdf($Invoice->InvoiceID);
                            if (empty($pdf_path)) {
                                $error['message'] = 'Failed to generate Invoice PDF File';
                                $error['status'] = 'failure';
                                return $error;
                            } else {
                                $Invoice->update(["PDF" => $pdf_path]);
                            }

                            Log::info('PDF fullPath ' . $pdf_path);
                            DB::connection('sqlsrv2')->commit();
                            return Response::json(array("status" => "success", "message" => "Invoice Successfully Updated", 'LastID' => $Invoice->InvoiceID));
                        }
                    }else{
                        return Response::json(array("status" => "success", "message" => "Invoice Successfully Updated, There is no product in Invoice", 'LastID' => $Invoice->InvoiceID));
                    }
                }
            }catch (Exception $e){
                DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Problem Updating Invoice. \n " . $e->getMessage()));
            }
        }
    }

    /**
    Calculate total on Product Change
     */
    public function calculate_total(){
        $data = Input::all();
        $response = array();
        $error = "";
        if(isset($data['product_type']) && Product::$ProductTypes[$data['product_type']] && isset($data['account_id']) && isset($data['product_id']) && isset($data['qty'])) {
            $AccountID = intval($data['account_id']);
            $Account = Account::find($AccountID);
            if (!empty($Account)) {
                $InvoiceTemplate = InvoiceTemplate::find($Account->InvoiceTemplateID);
                if (isset($InvoiceTemplate->InvoiceTemplateID) && $InvoiceTemplate->InvoiceTemplateID > 0) {
                    $decimal_places = 2;
                    $decimal_places = ($Account->RoundChargesAmount > 0) ? $Account->RoundChargesAmount : $decimal_places;

                    if (Product::$ProductTypes[$data['product_type']] == Product::ITEM) {

                        $companyID = User::get_companyID();
                        $data['CompanyID'] = $companyID;

                        $Product = Product::find($data['product_id']);
                        if (!empty($Product)) {

                            $Account = Account::find($data['account_id']);
                            $ProductAmount = number_format($Product->Amount, $decimal_places,".","");
                            $ProductDescription = $Product->Description;

                            $TaxRates = array();
                            $TaxRates = TaxRate::where(array('CompanyID' => User::get_companyID(), "TaxType" => TaxRate::TAX_ALL))->select(['TaxRateID', 'Title', 'Amount'])->first();
                            if(!empty($TaxRates)){
                                $TaxRates->toArray();
                            }
                            $AccountTaxRate = explode(",", $Account->TaxRateId);
                            //\Illuminate\Support\Facades\Log::error(print_r($TaxRates, true));

                            $TaxRateAmount = $TaxRateId = 0;
                            $TaxRateTitle = 'VAT';
                            if (isset($TaxRates['TaxRateID']) && in_array($TaxRates['TaxRateID'], $AccountTaxRate)) {

                                $TaxRateId = $TaxRates['TaxRateID'];
                                $TaxRateAmount = 0;
                                $TaxRateTitle = $TaxRates['Title'];
                                if (isset($TaxRates['Amount'])) {
                                    $TaxRateAmount = $TaxRates['Amount'];
                                }

                            }

                            $TotalTax = number_format((($ProductAmount * $data['qty'] * $TaxRateAmount) / 100), $decimal_places,".","");
                            $SubTotal = number_format($ProductAmount * $data['qty'], $decimal_places,".",""); //number_format(($ProductAmount + $TotalTax) , 2);

                            $response = [
                                "status" => "success",
                                "product_description" => $ProductDescription,
                                "product_amount" => $ProductAmount,
                                "product_tax_rate_id" => $TaxRateId,
                                "product_total_tax_rate" => $TotalTax,
                                "sub_total" => $SubTotal,
                                "decimal_places" => $decimal_places,
                                "product_tax_title" => $TaxRateTitle,
                            ];
                        } else {
                            $error = "No Product Found.";
                        }

                    } else {

                        $error = "No Invoice Template Assigned to Account";
                    }
                } else {
                    $error = "No Account Found";
                }
                if (empty($response)) {
                    $response = [
                        "status" => "failure",
                        "message" => $error
                    ];
                }
                return json_encode($response);
            }

        }

    }

    /**
     * Get Account Information
     */
    public function getAccountInfo()
    {
        $data = Input::all();
        if (isset($data['account_id']) && $data['account_id'] > 0 ) {
            $fields =["CurrencyId","Address1","Address2","Address3","City","PostCode","Country","InvoiceTemplateID"];
            $Account = Account::where(["AccountID"=>$data['account_id']])->select($fields)->first();
            $Currency = Currency::getCurrencySymbol($Account->CurrencyId);
            $InvoiceTemplateID = $Account->InvoiceTemplateID;
            $CurrencyId = $Account->CurrencyId;
            $Address = Account::getFullAddress($Account);
            $Terms = $FooterTerm = '';
            if(isset($InvoiceTemplateID) && $InvoiceTemplateID > 0) {
                $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
                $Terms = $InvoiceTemplate->Terms;
                $FooterTerm = $InvoiceTemplate->FooterTerm;
                $return = ['Terms','FooterTerm','Currency','CurrencyId','Address','InvoiceTemplateID'];
            }else{
                return Response::json(array("status" => "failed", "message" => "You can not create Invoice for this Account. as It has no Invoice Template assigned" ));
            }
            return Response::json(compact($return));
        }
    }


    public function delete($id)
    {
        if( $id > 0){
            try{
                DB::connection('sqlsrv2')->beginTransaction();
                InvoiceDetail::where(["InvoiceID"=>$id])->delete();
                Invoice::find($id)->delete();
                DB::connection('sqlsrv2')->commit();
                return Response::json(array("status" => "success", "message" => "Invoice Successfully Deleted"));

            }catch (Exception $e){
                DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Invoice is in Use, You cant delete this Currency. \n" . $e->getMessage() ));
            }

        }
    }


    public function print_preview($id) {
        //not in use.

        $Invoice = Invoice::find($id);
        $InvoiceDetail = InvoiceDetail::where(["InvoiceID"=>$id])->get();
        $Account  = Account::find($Invoice->AccountID);
        $Currency = Currency::find($Account->CurrencyId);
        $CurrencyCode = !empty($Currency)?$Currency->Code:'';
        $InvoiceTemplate = InvoiceTemplate::find($Account->InvoiceTemplateID);
        if(empty($InvoiceTemplate->CompanyLogoUrl)){
            $logo = 'http://placehold.it/250x100';
        }else{
            $logo = AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key);
        }
        return View::make('invoices.invoice_view', compact('Invoice','InvoiceDetail','Account','InvoiceTemplate','CurrencyCode','logo'));
    }
    public function invoice_preview($id)
	{
        $Invoice = Invoice::find($id);
		
        if(!empty($Invoice))
		{
            $InvoiceDetail  	= 	InvoiceDetail::where(["InvoiceID" => $id])->get();
            $Account 			= 	Account::find($Invoice->AccountID);
            $Currency 			= 	Currency::find($Account->CurrencyId);
            $CurrencyCode 		= 	!empty($Currency) ? $Currency->Code : '';
            $CurrencySymbol 	=  	Currency::getCurrencySymbol($Account->CurrencyId);
			$companyID 			= 	User::get_companyID();
			$query 				= 	"CALL `prc_getInvoicePayments`('".$id."','".$companyID."');";			
			$result   			=	DataTableSql::of($query,'sqlsrv2')->getProcResult(array('result'));			
			$payment_log		= 	array("total"=>$result['data']['result'][0]->total_grand,"paid_amount"=>$result['data']['result'][0]->paid_amount,"due_amount"=>$result['data']['result'][0]->due_amount);
						
            return View::make('invoices.invoice_cview', compact('Invoice', 'InvoiceDetail', 'Account', 'InvoiceTemplate', 'CurrencyCode', 'logo','CurrencySymbol','payment_log'));
        }
    }

    // not in use
    public function pdf_view($id) {
        \Debugbar::disable();

        // check if Invoice has usege or Subscription then download PDF directly.
        $hasUsageInInvoice =  InvoiceDetail::where("InvoiceID",$id)
            ->Where(function($query)
            {
                $query->where("ProductType",Product::USAGE)
                    ->orWhere("ProductType",Product::SUBSCRIPTION);
            })->count();
        if($hasUsageInInvoice > 0){
            $PDF = Invoice::where("InvoiceID",$id)->pluck("PDF");
            if(!empty($PDF)){
                $PDFurl = AmazonS3::preSignedUrl($PDF);
                header('Location: '.$PDFurl);
                exit;

            }else{
                return '';
            }
        }
        $pdf_path = $this->generate_pdf($id);
        return Response::download($pdf_path);
    }

    public function cview($id) {
        $account_inv = explode('-',$id);
        if(isset($account_inv[0]) && intval($account_inv[0]) > 0 && isset($account_inv[1]) && intval($account_inv[1]) > 0  ) {
            $AccountID = intval($account_inv[0]);
            $InvoiceID = intval($account_inv[1]);
            $Invoice = Invoice::where(["InvoiceID"=>$InvoiceID,"AccountID"=>$AccountID])->first();
            if(count($Invoice)>0) {
                $invoiceloddata = array();
                $invoiceloddata['Note']= 'Viewed By Unknown';
                if(!empty($_GET['email'])){
                    $invoiceloddata['Note']= 'Viewed By '. $_GET['email'];
                }

                $invoiceloddata['InvoiceID']= $Invoice->InvoiceID;
                $invoiceloddata['created_at']= date("Y-m-d H:i:s");
                $invoiceloddata['InvoiceLogStatus']= InVoiceLog::VIEWED;
                InVoiceLog::insert($invoiceloddata);

                return self::invoice_preview($InvoiceID);
            }
        }
        echo "Something Went wrong";
    }

    // not in use
    public function cpdf_view($id){
        $account_inv = explode('-',$id);
        if(isset($account_inv[0]) && $account_inv[0] > 0 && isset($account_inv[1]) && $account_inv[1] > 0  ) {
            $AccountID = intval($account_inv[0]);
            $InvoiceID = intval($account_inv[1]);
            $Invoice = Invoice::where(["InvoiceID" => $InvoiceID, "AccountID" => $AccountID])->first();
            if (count($Invoice) > 0) {
                return $this->pdf_view($InvoiceID);
            }
        }
//        echo "Something Went wrong";
    }

    //Generate Item Based Invoice PDF
    public function generate_pdf($id){
        if($id>0) {
            $Invoice = Invoice::find($id);
            $InvoiceDetail = InvoiceDetail::where(["InvoiceID" => $id])->get();
            $Account = Account::find($Invoice->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency)?$Currency->Code:'';
            $InvoiceTemplate = InvoiceTemplate::find($Account->InvoiceTemplateID);
            if (empty($InvoiceTemplate->CompanyLogoUrl)) {
                $as3url =  public_path("/assets/images/250x100.png");
            } else {
                $as3url = (AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key));
            }
            $logo = getenv('UPLOAD_PATH') . '/' . basename($as3url);
            file_put_contents($logo, file_get_contents($as3url));
            chmod($logo,0777);
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

            $body = View::make('invoices.pdf', compact('Invoice', 'InvoiceDetail', 'Account', 'InvoiceTemplate', 'usage_data', 'CurrencyCode', 'logo'))->render();
            $destination_dir = getenv('UPLOAD_PATH') . '/'. AmazonS3::generate_path(AmazonS3::$dir['INVOICE_UPLOAD'],$Account->CompanyId) ;
            if (!file_exists($destination_dir)) {
                mkdir($destination_dir, 0777, true);
            }
            $save_path = $destination_dir .  GUID::generate().'-'. $file_name;
            PDF::loadHTML($body)->setPaper('a4')->setOrientation('potrait')->save($save_path);
            chmod($save_path,0777);
            //@unlink($logo);
            return $save_path;
        }
    }

    public function bulk_invoice(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $rules = array(
            'StartDate' => 'required',
            'EndDate' => 'required',
            'AccountID'=>'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $data['StartDate'] = $data['StartDate'].' '.$data['StartTime'];
        $data['EndDate'] = $data['EndDate'].' '.$data['EndTime'];
        if($data['StartDate'] >= $data['EndDate']){
            return Response::json(array("status" => "failed", "message" => "Dates are invalid"));
        }
        $jobType = JobType::where(["Code" => 'BI'])->get(["JobTypeID", "Title"]);
        $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
        $jobdata["CompanyID"] = $CompanyID;
        $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
        $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
        $jobdata["JobLoggedUserID"] = User::get_userID();
        $jobdata["Title"] =  (isset($jobType[0]->Title) ? $jobType[0]->Title : '').($data['GenerateSend'] == 1?' Generate & Send':' Generate');
        $jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
        $jobdata["CreatedBy"] = User::get_user_full_name();
        $jobdata["Options"] = json_encode($data);
        $jobdata["updated_at"] = date('Y-m-d H:i:s');
        $JobID = Job::insertGetId($jobdata);
        if($JobID){
            return json_encode(["status" => "success", "message" => "Bulk Invoice Job Added in queue to process.You will be notified once job is completed. "]);
        }else{
            return json_encode(array("status" => "failed", "message" => "Problem Creating Bulk Invoice."));
        }

    }

    public function add_invoice_in(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $rules = array(
            'AccountID' => 'required',
            'IssueDate' => 'required',
            'StartDate' => 'required',
            'EndDate' => 'required',
            'GrandTotal'=>'required|numeric',
            'InvoiceNumber'=>'required|unique:tblInvoice,InvoiceNumber',
        );
        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');
        $data['StartDate'] = $data['StartDate'].' '.$data['StartTime'];
        $data['EndDate'] = $data['EndDate'].' '.$data['EndTime'];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if($data['StartDate'] >= $data['EndDate']){
            return Response::json(array("status" => "failed", "message" => "Dates are invalid"));
        }
        $fields =["CurrencyId","Address1","Address2","Address3","City","Country","InvoiceTemplateID"];
        $Account = Account::where(["AccountID"=>$data['AccountID']])->select($fields)->first();
        if (Input::hasFile('Attachment')) {
            $upload_path = Config::get('app.upload_path');
            $Attachment = Input::file('Attachment');
            // ->move($destinationPath);
            $ext = $Attachment->getClientOriginalExtension();
            if (in_array($ext, array("pdf", "jpg", "png", "gif"))) {
                $file_name = GUID::generate() . '.' . $Attachment->getClientOriginalExtension();
                $Attachment->move($upload_path, $file_name);
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['VENDOR_UPLOAD']);
                if (!AmazonS3::upload($upload_path . '/' . $file_name, $amazonPath)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
            }
        }

        $CreatedBy = User::get_user_full_name();
        $Address = Account::getFullAddress($Account);

        $InvoiceData = array();
        $InvoiceData["CompanyID"] = $CompanyID;
        $InvoiceData["AccountID"] = $data["AccountID"];
        $InvoiceData["Address"] = $Address;
        $InvoiceData["InvoiceNumber"] = $data["InvoiceNumber"];
        $InvoiceData["IssueDate"] = $data["IssueDate"];
        $InvoiceData["GrandTotal"] = floatval(str_replace(",","",$data["GrandTotal"]));
        $InvoiceData["CurrencyID"] = $Account->CurrencyId;
        $InvoiceData["InvoiceType"] = Invoice::INVOICE_IN;
        if(isset($fullPath)) {
            $InvoiceData["Attachment"] = $fullPath;
        }
        $InvoiceData["CreatedBy"] = $CreatedBy;
        if($Invoice = Invoice::create($InvoiceData)) {
            $InvoiceDetailData =array();
            $InvoiceDetailData['InvoiceID'] = $Invoice->InvoiceID;
            $InvoiceDetailData['StartDate'] = $data['StartDate'];
            $InvoiceDetailData['EndDate'] = $data['EndDate'];
            $InvoiceDetailData['Price'] = floatval(str_replace(",","",$data["GrandTotal"]));
            $InvoiceDetailData['Qty'] = 1;
            $InvoiceDetailData['LineTotal'] = floatval(str_replace(",","",$data["GrandTotal"]));
            $InvoiceDetailData["created_at"] = date("Y-m-d H:i:s");
            $InvoiceDetailData['Description'] = 'Invoice In';
            $InvoiceDetailData['ProductID'] = 0;
            $InvoiceDetailData["CreatedBy"] = $CreatedBy;
            InvoiceDetail::insert($InvoiceDetailData);
            return Response::json(["status" => "success", "message" => "Invoice in updated successfully"]);
        }else{
            return Response::json(["status" => "success", "message" => "Problem Updating Invoice"]);
        }

    }
    public function update_invoice_in($id){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $rules = array(
            'AccountID' => 'required',
            'IssueDate' => 'required',
            'GrandTotal'=>'required|numeric',
            'InvoiceNumber' => 'required|unique:tblInvoice,InvoiceNumber,'.$id.',InvoiceID,CompanyID,'.$CompanyID,
        );
        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $fields =["CurrencyId","Address1","Address2","Address3","City","Country","InvoiceTemplateID"];
        $Account = Account::where(["AccountID"=>$data['AccountID']])->select($fields)->first();
        if (Input::hasFile('Attachment')) {
            $upload_path = Config::get('app.upload_path');
            $Attachment = Input::file('Attachment');
            // ->move($destinationPath);
            $ext = $Attachment->getClientOriginalExtension();
            if (in_array($ext, array("pdf", "jpg", "png", "gif"))) {
                $file_name = GUID::generate() . '.' . $Attachment->getClientOriginalExtension();
                $Attachment->move($upload_path, $file_name);
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['VENDOR_UPLOAD']);
                if (!AmazonS3::upload($upload_path . '/' . $file_name, $amazonPath)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
            }
        }

        $CreatedBy = User::get_user_full_name();
        $Address = Account::getFullAddress($Account);

        $InvoiceData = array();
        $InvoiceData["CompanyID"] = $CompanyID;
        $InvoiceData["AccountID"] = $data["AccountID"];
        $InvoiceData["Address"] = $Address;
        $InvoiceData["InvoiceNumber"] = $data["InvoiceNumber"];
        $InvoiceData["IssueDate"] = $data["IssueDate"];
        $InvoiceData["GrandTotal"] = floatval(str_replace(",","",$data["GrandTotal"]));
        $InvoiceData["CurrencyID"] = $Account->CurrencyId;
        $InvoiceData["InvoiceType"] = Invoice::INVOICE_IN;
        if(isset($fullPath)) {
            $InvoiceData["Attachment"] = $fullPath;
        }
        $InvoiceData["ModifiedBy"] = $CreatedBy;

        $InvoiceDetailData =array();
        $InvoiceDetailData['StartDate'] = $data['StartDate'].' '.$data['StartTime'];
        $InvoiceDetailData['EndDate'] = $data['EndDate'].' '.$data['EndTime'];
        $InvoiceDetailData['Price'] = floatval(str_replace(",","",$data["GrandTotal"]));
        $InvoiceDetailData['LineTotal'] = floatval(str_replace(",","",$data["GrandTotal"]));
        $InvoiceDetailData["updated_at"] = date("Y-m-d H:i:s");
        $InvoiceDetailData['Description'] = $data['Description'];
        $InvoiceDetailData["ModifiedBy"] = $CreatedBy;
        if(Invoice::find($id)->update($InvoiceData)) {
            if(InvoiceDetail::find($data['InvoiceDetailID'])->update($InvoiceDetailData)) {
                return Response::json(["status" => "success", "message" => "Invoice in updated successfully"]);
            }else{
                return Response::json(["status" => "success", "message" => "Problem Updating Invoice"]);
            }
        }else{
            return Response::json(["status" => "success", "message" => "Problem Updating Invoice"]);
        }
    }
    public function  download_doc_file($id){
        $DocumentFile = Invoice::where(["InvoiceID"=>$id])->pluck('Attachment');
        if(file_exists($DocumentFile)){
            download_file($DocumentFile);
        }else{
            $FilePath =  AmazonS3::preSignedUrl($DocumentFile);
            header('Location: '.$FilePath);
        }
        exit;
    }

    public function invoice_email($id) {
        $Invoice = Invoice::find($id);
        if(!empty($Invoice)) {
            $Account = Account::find($Invoice->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CompanyName = Company::getName();
            if (!empty($Currency)) {
                $Subject = "New Invoice " . Invoice::getFullInvoiceNumber($Invoice,$Account). ' from ' . $CompanyName . ' ('.$Account->AccountName.')';
                $RoundChargesAmount = 2;
                if($Account->RoundChargesAmount > 0){
                    $RoundChargesAmount = $Account->RoundChargesAmount;
                }

                $data = [
                    'CompanyName' => $CompanyName,
                    'GrandTotal'       => number_format($Invoice->GrandTotal,$RoundChargesAmount),
                    'CurrencyCode'     =>$Currency->Code
                ];
                $Message = Invoice::getInvoiceEmailTemplate($data);
                return View::make('invoices.email', compact('Invoice', 'Account', 'Subject','Message','CompanyName'));
            }
        }
    }
    public function send($id){
        if($id){
            set_time_limit(600); // 10 min time limit.
            $CreatedBy = User::get_user_full_name();
            $data = Input::all();
            $Invoice = Invoice::find($id);
            $Company = Company::find($Invoice->CompanyID);
            $CompanyName = $Company->CompanyName;
            $InvoiceGenerationEmail = CompanySetting::getKeyVal('InvoiceGenerationEmail');
            $InvoiceGenerationEmail = ($InvoiceGenerationEmail =='Invalid Key')?$Company->Email:$InvoiceGenerationEmail;
            $emailtoCustomer = getenv('EmailToCustomer');
            if(intval($emailtoCustomer) == 1){
                $CustomerEmail = $data['Email'];
            }else{
                $CustomerEmail = $Company->Email;
            }
            $data['EmailTo'] = explode(",",$CustomerEmail);
            $data['InvoiceURL'] = "URL::to('/invoice/'.$Invoice->AccountID.'-'.$Invoice->InvoiceID.'/cview'";
            $data['AccountName'] = Account::find($Invoice->AccountID)->AccountName;
            $data['CompanyName'] = $CompanyName;
            $rules = array(
                'AccountName' => 'required',
                'InvoiceURL' => 'required',
                'Subject'=>'required',
                'EmailTo'=>'required',
                'Message'=>'required',
                'CompanyName'=>'required',
            );
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            /*
             * Send to Customer
             * */
            //$status = sendMail('emails.invoices.send',$data);
            $status = 0;
            $CustomerEmails = $data['EmailTo'];
            foreach($CustomerEmails as $singleemail){
                $singleemail = trim($singleemail);
                if (filter_var($singleemail, FILTER_VALIDATE_EMAIL)) {
                    $data['EmailTo'] = $singleemail;
                    $data['InvoiceURL']= URL::to('/invoice/'.$Invoice->AccountID.'-'.$Invoice->InvoiceID.'/cview?email='.$singleemail);
                    $status = $this->sendInvoiceMail('emails.invoices.send',$data);
                }
            }
            if($status['status']==0){
                $status['status'] = 'failure';
            }else{
                $status['status'] = "success";
                $Invoice->update(['InvoiceStatus' => Invoice::SEND ]);
                $invoiceloddata = array();
                $invoiceloddata['InvoiceID']= $Invoice->InvoiceID;
                $invoiceloddata['Note']= 'Sent By '.$CreatedBy;
                $invoiceloddata['created_at']= date("Y-m-d H:i:s");
                $invoiceloddata['InvoiceLogStatus']= InVoiceLog::SENT;
                InVoiceLog::insert($invoiceloddata);
                /*
                    Insert email log in account
                */
                $logData = ['AccountID'=>$Invoice->AccountID,
                    'EmailTo'=>$CustomerEmail,
                    'Subject'=>$data['Subject'],
                    'Message'=>$data['Message']];
                email_log($logData);
            }
            /*
             * Send to Staff
             * */
            $Account = Account::find($Invoice->AccountID);
            if(!empty($Account->Owner))
            {
                $AccountManager = User::find($Account->Owner);
                $InvoiceGenerationEmail .= ',' . $AccountManager->EmailAddress;
            }
            $sendTo = explode(",",$InvoiceGenerationEmail);
            //$sendTo[] = User::get_user_email();
            $data['Subject'] .= ' ('.$Account->AccountName.')';//Added by Abubakar
            $data['EmailTo'] = $sendTo;
            $data['InvoiceURL']= URL::to('/invoice/'.$Invoice->InvoiceID.'/invoice_preview');
            //$StaffStatus = sendMail('emails.invoices.send',$data);
            $StaffStatus = $this->sendInvoiceMail('emails.invoices.send',$data);
            if($StaffStatus['status']==0){
                $status['message'] .= ', Enable to send email to staff : ' . $StaffStatus['message'];
            }

            return Response::json(array("status" => $status['status'], "message" => "".$status['message']));
        }else{
            return Response::json(["status" => "failure", "message" => "Problem Sending Invoice"]);
        }
    }

    function sendInvoiceMail($view,$data){
        $status = array('status' => 0, 'message' => 'Something wrong with sending mail.');
        $companyID = User::get_companyID();
        $mail = setMailConfig($companyID);
        $body = View::make($view,compact('data'))->render();

        if(getenv('APP_ENV') != 'Production'){
            $data['Subject'] = 'Test Mail '.$data['Subject'];
        }
        $mail->Body = $body;
        $mail->Subject = $data['Subject'];

        if(is_array($data['EmailTo'])){
            foreach((array)$data['EmailTo'] as $email_address){
                if(!empty($email_address)) {
                    $email_address = trim($email_address);
                    $mail->addAddress($email_address);
                    if (!$mail->send()) {
                        $mail->clearAllRecipients();
                        $status['status'] = 0;
                        $status['message'] .= $mail->ErrorInfo . ' ( Email Address: ' . $email_address . ')';
                    } else {
                        $status['status'] = 1;
                        $status['message'] = 'Email has been sent';
                    }
                }
            }
        }else{
            if(!empty($data['EmailTo'])) {
                $email_address = trim($data['EmailTo']);
                $mail->addAddress($email_address);
                if (!$mail->send()) {
                    $mail->clearAllRecipients();
                    $status['status'] = 0;
                    $status['message'] .= $mail->ErrorInfo . ' ( Email Address: ' . $data['EmailTo'] . ')';
                } else {
                    $status['status'] = 1;
                    $status['message'] = 'Email has been sent';
                }
            }
        }
        return $status;
    }
    public function bulk_send_invoice_mail(){
        $data = Input::all();
        $companyID = User::get_companyID();
        if(!empty($data['criteria'])){
            $invoiceid = $this->getInvoicesIdByCriteria($data);
            $invoiceid = rtrim($invoiceid,',');
            $data['InvoiceIDs'] = $invoiceid;
            unset($data['criteria']);
        }
        else{
            unset($data['criteria']);
        }

        $jobType = JobType::where(["Code" => 'BIS'])->get(["JobTypeID", "Title"]);
        $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
        $jobdata["CompanyID"] = $companyID;
        $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
        $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
        $jobdata["JobLoggedUserID"] = User::get_userID();
        $jobdata["Title"] =  (isset($jobType[0]->Title) ? $jobType[0]->Title : '');
        $jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
        $jobdata["CreatedBy"] = User::get_user_full_name();
        $jobdata["Options"] = json_encode($data);
        $jobdata["updated_at"] = date('Y-m-d H:i:s');
        $JobID = Job::insertGetId($jobdata);
        if($JobID){
            return Response::json(array("status" => "success", "message" => "Bulk Invoice Send Job Added in queue to process.You will be notified once job is completed. "));
        }else{
            return Response::json(array("status" => "success", "message" => "Problem Creating Job Bulk Invoice Send."));
        }
    }
    public function invoice_change_Status(){
        $data = Input::all();
        $username = User::get_user_full_name();
        $invoice_status = Invoice::get_invoice_status();
        if(!empty($data['criteria']))
        {
            $invoiceid = $this->getInvoicesIdByCriteria($data);
            $InvoiceIDs =array_filter(explode(',',$invoiceid),'intval');

        }else{
            $InvoiceIDs =array_filter(explode(',',$data['InvoiceIDs']),'intval');
        }
        if (is_array($InvoiceIDs) && count($InvoiceIDs)) {

            if (Invoice::whereIn('InvoiceID',$InvoiceIDs)->update([ 'ModifiedBy'=>$username,'InvoiceStatus' => $data['InvoiceStatus']])) {
                $Extralognote = '';
                if($data['InvoiceStatus'] == Invoice::CANCEL){
                    $Extralognote = ' Cancel Reason: '.$data['CancelReason'];
                }
                foreach($InvoiceIDs as $InvoiceID) {
                    $invoiceloddata = array();
                    $invoiceloddata['InvoiceID'] = $InvoiceID;
                    $invoiceloddata['Note'] = $invoice_status[$data['InvoiceStatus']].' By ' . $username.$Extralognote;
                    $invoiceloddata['created_at'] = date("Y-m-d H:i:s");
                    $invoiceloddata['InvoiceLogStatus'] = InVoiceLog::UPDATED;
                    InVoiceLog::insert($invoiceloddata);
                }

                return Response::json(array("status" => "success", "message" => "Invoice Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Invoice."));
            }
        }

    }

    /*
     * Download Output File
     * */
    public function downloadUsageFile($id){
        //if( User::checkPermission('Job') && intval($id) > 0 ) {
        $OutputFilePath = Invoice::where("InvoiceID", $id)->pluck("UsagePath");
        $FilePath =  AmazonS3::preSignedUrl($OutputFilePath);
        if(is_amazon() == true){
            header('Location: '.$FilePath);
        }else if(file_exists($FilePath)){
            download_file($FilePath);
        }
        exit;
    }

    /*
     * Download Output File for Customer
     * */
    public function cdownloadUsageFile($id){
        $account_inv = explode('-',$id);
        if(isset($account_inv[0]) && intval($account_inv[0]) > 0 && isset($account_inv[1]) && intval($account_inv[1]) > 0  ) {
            $AccountID = intval($account_inv[0]);
            $InvoiceID = intval($account_inv[1]);
            $this->downloadUsageFile($InvoiceID);
        }
    }
    public function invoice_regen(){
        $data = Input::all();
        if(!empty($data['criteria'])){
            $invoiceid = $this->getInvoicesIdByCriteria($data);
            $invoiceid = rtrim($invoiceid,',');
            $data['InvoiceIDs'] = $invoiceid;
            unset($data['criteria']);
        }
        else{
            unset($data['criteria']);
        }
        $CompanyID = User::get_companyID();
        $InvoiceIDs =array_filter(explode(',',$data['InvoiceIDs']),'intval');
        if (is_array($InvoiceIDs) && count($InvoiceIDs)) {
            $jobType = JobType::where(["Code" => 'BIR'])->first(["JobTypeID", "Title"]);
            $jobStatus = JobStatus::where(["Code" => "P"])->first(["JobStatusID"]);
            $jobdata["CompanyID"] = $CompanyID;
            $jobdata["JobTypeID"] = $jobType->JobTypeID ;
            $jobdata["JobStatusID"] =  $jobStatus->JobStatusID;
            $jobdata["JobLoggedUserID"] = User::get_userID();
            $jobdata["Title"] =  $jobType->Title;
            $jobdata["Description"] = $jobType->Title ;
            $jobdata["CreatedBy"] = User::get_user_full_name();
            $jobdata["Options"] = json_encode($data);
            $jobdata["updated_at"] = date('Y-m-d H:i:s');
            $JobID = Job::insertGetId($jobdata);
            if($JobID){
                return json_encode(["status" => "success", "message" => "Invoice Regeneration Job Added in queue to process.You will be notified once job is completed."]);
            }else{
                return json_encode(array("status" => "failed", "message" => "Problem Creating Bulk Invoice."));
            }
        }

    }
    public static function display_invoice($InvoiceID){
        $Invoice = Invoice::find($InvoiceID);
        $PDFurl = '';
        if(is_amazon() == true){
            $PDFurl =  AmazonS3::preSignedUrl($Invoice->PDF);
        }else{
            $PDFurl = Config::get('app.upload_path')."/".$Invoice->PDF;
        }
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="'.basename($PDFurl).'"');
        echo file_get_contents($PDFurl);
        exit;
    }
    public static function download_invoice($InvoiceID){
        $Invoice = Invoice::find($InvoiceID);
        $FilePath =  AmazonS3::preSignedUrl($Invoice->PDF);
        if(is_amazon() == true){
            header('Location: '.$FilePath);
        }else if(file_exists($FilePath)){
            download_file($FilePath);
        }
        exit;
    }
    public function invoice_payment($id)
    {
        $account_inv = explode('-', $id);
        if (isset($account_inv[0]) && intval($account_inv[0]) > 0 && isset($account_inv[1]) && intval($account_inv[1]) > 0) {
            $AccountID = intval($account_inv[0]);
            $InvoiceID = intval($account_inv[1]);
            $Invoice = Invoice::where(["InvoiceID" => $InvoiceID, "AccountID" => $AccountID])->first();
            $Account = Account::where(['AccountID'=>$AccountID])->first();
            if (count($Invoice) > 0) {
                $CurrencyCode = Currency::getCurrency($Invoice->CurrencyID);
                $CurrencySymbol =  Currency::getCurrencySymbol($Invoice->CurrencyID);
                return View::make('invoices.invoice_payment', compact('Invoice','CurrencySymbol','Account','CurrencyCode'));
            }
        }
    }

    public function pay_invoice(){
        $data = Input::all();
        $InvoiceID = $data['InvoiceID'];
        $AccountID = $data['AccountID'];
        $rules = array(
            'CardNumber' => 'required|digits_between:13,19',
            'ExpirationMonth' => 'required',
            'ExpirationYear' => 'required',
            'NameOnCard' => 'required',
            'CVVNumber' => 'required',
            //'Title' => 'required|unique:tblAutorizeCardDetail,NULL,CreditCardID,CompanyID,'.$CompanyID
        );

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if (date("Y") == $data['ExpirationYear'] && date("m") > $data['ExpirationMonth']) {
            return Response::json(array("status" => "failed", "message" => "Month must be after " . date("F")));
        }
        $card = CreditCard::validCreditCard($data['CardNumber']);
        if ($card['valid'] == 0) {
            return Response::json(array("status" => "failed", "message" => "Please enter valid card number"));
        }
        $Invoice = Invoice::where('InvoiceStatus','!=',Invoice::PAID)->where(["InvoiceID" => $InvoiceID, "AccountID" => $AccountID])->first();
        $account = Account::where(['AccountID'=>$AccountID])->first();
        if(!empty($Invoice)) {
            $data['GrandTotal'] = $Invoice->GrandTotal;
            $response = AuthorizeNet::pay_invoice($data);
            $Notes = '';
            if($response->response_code == 1) {
                $Notes = 'AuthorizeNet transaction_id ' . $response->transaction_id;
            }else{
                $Notes = isset($response->response->xml->messages->message->text) && $response->response->xml->messages->message->text != '' ? $response->response->xml->messages->message->text : $response->response_reason_text ;
            }
            if ($response->approved) {
                $Invoice = Invoice::find($Invoice->InvoiceID);
                $paymentdata = array();
                $paymentdata['CompanyID'] = $Invoice->CompanyID;
                $paymentdata['AccountID'] = $Invoice->AccountID;
                $paymentdata['InvoiceNo'] = Invoice::getFullInvoiceNumber($Invoice,$account);
                $paymentdata['PaymentDate'] = date('Y-m-d');
                $paymentdata['PaymentMethod'] = $response->method;
                $paymentdata['CurrencyID'] = $account->CurrencyId;
                $paymentdata['PaymentType'] = 'Payment In';
                $paymentdata['Notes'] = $Notes;
                $paymentdata['Amount'] = floatval($response->amount);
                $paymentdata['Status'] = 'Approved';
                $paymentdata['CreatedBy'] = 'customer';
                $paymentdata['ModifyBy'] = 'customer';
                $paymentdata['created_at'] = date('Y-m-d H:i:s');
                $paymentdata['updated_at'] = date('Y-m-d H:i:s');
                Payment::insert($paymentdata);
                $transactiondata = array();
                $transactiondata['CompanyID'] = $account->CompanyId;
                $transactiondata['AccountID'] = $AccountID;
                $transactiondata['InvoiceID'] = $Invoice->InvoiceID;
                $transactiondata['Transaction'] = $response->transaction_id;
                $transactiondata['Notes'] = $Notes;
                $transactiondata['Amount'] = floatval($Invoice->RemaingAmount);
                $transactiondata['Status'] = TransactionLog::SUCCESS;
                $transactiondata['created_at'] = date('Y-m-d H:i:s');
                $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                $transactiondata['CreatedBy'] = 'customer';
                $transactiondata['ModifyBy'] = 'customer';
                TransactionLog::insert($transactiondata);
                $Invoice->update(array('InvoiceStatus' => Invoice::PAID));
                return Response::json(array("status" => "success", "message" => "Invoice paid successfully"));
            }else{
                $transactiondata = array();
                $transactiondata['CompanyID'] = $Invoice->CompanyID;
                $transactiondata['AccountID'] = $AccountID;
                $transactiondata['InvoiceID'] = $Invoice->InvoiceID;
                $transactiondata['Transaction'] = $response->transaction_id;
                $transactiondata['Notes'] = $Notes;
                $transactiondata['Amount'] = floatval($Invoice->RemaingAmount);
                $transactiondata['Status'] = TransactionLog::FAILED;
                $transactiondata['created_at'] = date('Y-m-d H:i:s');
                $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                $transactiondata['CreatedBy'] = 'customer';
                $transactiondata['ModifyBy'] = 'customer';
                TransactionLog::insert($transactiondata);
                return Response::json(array("status" => "failed", "message" => $response->response_reason_text));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Invoice not found"));
        }
    }
    public function invoice_thanks($id)
    {
        $account_inv = explode('-', $id);
        if (isset($account_inv[0]) && intval($account_inv[0]) > 0 && isset($account_inv[1]) && intval($account_inv[1]) > 0) {
            $AccountID = intval($account_inv[0]);
            $InvoiceID = intval($account_inv[1]);
            $Invoice = Invoice::where(["InvoiceID" => $InvoiceID, "AccountID" => $AccountID])->first();
            if (count($Invoice) > 0) {
                return View::make('invoices.invoice_thanks', compact('Invoice'));
            }
        }
    }
    public function generate(){
        $CompanyID = User::get_companyID();
        $UserID = User::get_userID();
        $CronJobCommandID = CronJobCommand::where(array('Command'=>'invoicegenerator','CompanyID'=>$CompanyID))->pluck('CronJobCommandID');
        $CronJobID = CronJob::where(array('CronJobCommandID'=>(int)$CronJobCommandID,'CompanyID'=>$CompanyID))->pluck('CronJobID');
        if($CronJobID > 0) {

            $jobType = JobType::where(["Code" => 'BI'])->get(["JobTypeID", "Title"]);
            $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
            $jobdata["CompanyID"] = $CompanyID;
            $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
            $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
            $jobdata["JobLoggedUserID"] = $UserID;
            $jobdata["Title"] = "[Auto] " . (isset($jobType[0]->Title) ? $jobType[0]->Title : '') . ' Generate & Send';
            $jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
            $jobdata["CreatedBy"] = User::get_user_full_name($UserID);
            //$jobdata["Options"] = json_encode(array("accounts" => $AccountIDs));
            $jobdata['Options'] = json_encode(array('CronJobID'=>$CronJobID));
            $jobdata["updated_at"] = date('Y-m-d H:i:s');
            $JobID = Job::insertGetId($jobdata);
            /*if(getenv('APP_OS') == 'Linux'){
                pclose(popen(getenv('PHPExePath') . " " . getenv('RMArtisanFileLocation') . "  invoicegenerator " . $CompanyID . " $CronJobID $UserID ". " &", "r"));
            }else{
                pclose(popen("start /B " . getenv('PHPExePath') . " " . getenv('RMArtisanFileLocation') . "  invoicegenerator " . $CompanyID . " $CronJobID $UserID ", "r"));
            }*/
            if($JobID>0) {
                return Response::json(array("status" => "success", "message" => "Invoice Generation Job Added in queue to process.You will be notified once job is completed. "));
            }
        }
        return Response::json(array("status" => "success", "message" => "Problem Creating Invoice Generation Job"));

    }
    public function ajax_getEmailTemplate($id){
        $filter =array('Type'=>EmailTemplate::INVOICE_TEMPLATE);
        if($id == 1){
          $filter['UserID'] =   User::get_userID();
        }
        return EmailTemplate::getTemplateArray($filter);
    }

    public function getInvoicesIdByCriteria($data){
        $companyID = User::get_companyID();
        $criteria = json_decode($data['criteria'],true);
        $query = "call prc_getInvoice (".$companyID.",'".$criteria['AccountID']."','".$criteria['InvoiceNumber']."','".$criteria['IssueDateStart']."','".$criteria['IssueDateEnd']."','".$criteria['InvoiceType']."','".$criteria['InvoiceStatus']."','' ,'','',''";
        if(!empty($criteria['zerovalueinvoice'])){
            $query = $query.',2,0,1';
        }else{
            $query = $query.',2,0,0';
        }
        $query .= ",'')";
        $exceldatas  = DB::connection('sqlsrv2')->select($query);
        $exceldatas = json_decode(json_encode($exceldatas),true);
        $invoiceid='';
        foreach($exceldatas as $exceldata){
            $invoiceid.= $exceldata['InvoiceID'].',';
        }
        return $invoiceid;
    }

    public function sageExport(){
        $data = Input::all();		
        $companyID = User::get_companyID();
        if(!empty($data['InvoiceIDs'])){
            $query = "call prc_getInvoice (".$companyID.",0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',0,'',1 ,".count($data['InvoiceIDs']).",'','',''";
            if(isset($data['MarkPaid']) && $data['MarkPaid'] == 1){
                $query = $query.',0,2,0';
            }else{
                $query = $query.',0,1,0';
            }
            if(!empty($data['InvoiceIDs'])){
                $query = $query.",'".$data['InvoiceIDs']."')";
            }
			else			
            $query .= ")";
            $excel_data  = DB::connection('sqlsrv2')->select($query);
            $excel_data = json_decode(json_encode($excel_data),true);

            $file_path = getenv('UPLOAD_PATH') .'/InvoiceSageExport.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($excel_data);

            /*Excel::create('InvoiceSageExport', function ($excel) use ($excel_data) {
                $excel->sheet('InvoiceSageExport', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });

            })->download('csv');*/

        }else{			

            $criteria = json_decode($data['criteria'],true);
            $criteria['InvoiceType'] = $criteria['InvoiceType'] == 'All'?'':$criteria['InvoiceType'];
            $criteria['zerovalueinvoice'] = $criteria['zerovalueinvoice']== 'true'?1:0;
			 $criteria['IssueDateStart'] 	 =  empty($criteria['IssueDateStart'])?'0000-00-00 00:00:00':$criteria['IssueDateStart'];
    	    $criteria['IssueDateEnd']        =  empty($criteria['IssueDateEnd'])?'0000-00-00 00:00:00':$criteria['IssueDateEnd'];
            $query = "call prc_getInvoice (".$companyID.",'".intval($criteria['AccountID'])."','".$criteria['InvoiceNumber']."','".$criteria['IssueDateStart']."','".$criteria['IssueDateEnd']."','".$criteria['InvoiceType']."','".$criteria['InvoiceStatus']."','' ,'','','',''";
            if(isset($data['MarkPaid']) && $data['MarkPaid'] == 1){
                $query = $query.',0,2';
            }else{
                $query = $query.',0,1';
            }
            if(!empty($criteria['zerovalueinvoice'])){
                $query = $query.',1';
            }else{
                $query = $query.',0';
            }
            $query .= ",'')";
            $excel_data  = DB::connection('sqlsrv2')->select($query);
            $excel_data = json_decode(json_encode($excel_data),true);

            $file_path = getenv('UPLOAD_PATH') .'/InvoiceSageExport.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($excel_data);
            /*Excel::create('InvoiceSageExport', function ($excel) use ($excel_data) {
                $excel->sheet('InvoiceSageExport', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('csv');*/

        }

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