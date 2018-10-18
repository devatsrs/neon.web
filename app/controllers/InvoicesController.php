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
        $data['Overdue'] = $data['Overdue']== 'true'?1:0;
        $sort_column 				 =  $columns[$data['iSortCol_0']];
        $data['InvoiceStatus'] = is_array($data['InvoiceStatus'])?implode(',',$data['InvoiceStatus']):$data['InvoiceStatus'];
        $query = "call prc_getInvoice (".$companyID.",".intval($data['AccountID']).",'".$data['InvoiceNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."',".intval($data['InvoiceType']).",'".$data['InvoiceStatus']."',".$data['Overdue'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',".intval($data['CurrencyID'])."";
        $InvoiceHideZeroValue = Invoice::getCookie('InvoiceHideZeroValue');
        // Account Manager Condition
        $userID = 0;
        if(User::is('AccountManager')) { // Account Manager
            $userID = User::get_userID();
        }
        //set Cookie
        if($data['zerovalueinvoice'] != $InvoiceHideZeroValue){
            if($data['zerovalueinvoice'] == 0){
                $hidevalue = 0;
            }else{
                $hidevalue = 1;
            }
            NeonCookie::setCookie('InvoiceHideZeroValue',$hidevalue,60);
        }
        if(isset($data['Export']) && $data['Export'] == 1)
		{
            if(isset($data['zerovalueinvoice']) && $data['zerovalueinvoice'] == 1)
			{
                $excel_data  = DB::connection('sqlsrv2')->select($query.',1,0,1,"",'.$userID.',"'.$data['tag'].'")');
            }
			else
			{
                $excel_data  = DB::connection('sqlsrv2')->select($query.',1,0,0,"",'.$userID.',"'.$data['tag'].'")');
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
            $query = $query.',0,0,1,"",'.$userID.',"'.$data['tag'].'")';
        }
		else
		{
            $query .=',0,0,0,"",'.$userID.',"'.$data['tag'].'")';
        }

		$result   = DataTableSql::of($query,'sqlsrv2')->getProcResult(array('ResultCurrentPage','Total_grand_field'));
		$result2  = $result['data']['Total_grand_field'][0]->total_grand;
		$result4  = array(
			"total_grand"=>$result['data']['Total_grand_field'][0]->currency_symbol.$result['data']['Total_grand_field'][0]->total_grand,
			"os_pp"=>$result['data']['Total_grand_field'][0]->currency_symbol.$result['data']['Total_grand_field'][0]->TotalPayment.' / '.$result['data']['Total_grand_field'][0]->TotalPendingAmount,
		);
		
		return json_encode($result4,JSON_NUMERIC_CHECK);		
	}

    public function ajax_datagrid($type) {
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $companyID = User::get_companyID();
        $columns = ['InvoiceID','AccountName','InvoiceNumber','IssueDate','InvoicePeriod','GrandTotal','PendingAmount','InvoiceStatus','DueDate','DueDays','InvoiceID'];
        $data['InvoiceType'] = $data['InvoiceType'] == 'All'?'':$data['InvoiceType'];
        $data['zerovalueinvoice'] = $data['zerovalueinvoice']== 'true'?1:0;
        $data['IssueDateStart'] = empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd'] = empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];
        $data['CurrencyID'] = empty($data['CurrencyID'])?'0':$data['CurrencyID'];
        $data['Overdue'] = $data['Overdue']== 'true'?1:0;
        $sort_column = $columns[$data['iSortCol_0']];

        // Account Manager Condition
        $userID = 0;
        if(User::is('AccountManager')) { // Account Manager
            $userID = User::get_userID();
        }

        $query = "call prc_getInvoice (".$companyID.",".intval($data['AccountID']).",'".$data['InvoiceNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."',".intval($data['InvoiceType']).",'".$data['InvoiceStatus']."',".$data['Overdue'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',".intval($data['CurrencyID'])."";
        if(isset($data['Export']) && $data['Export'] == 1) {
            if(isset($data['zerovalueinvoice']) && $data['zerovalueinvoice'] == 1){
                $excel_data  = DB::connection('sqlsrv2')->select($query.',1,0,1,"",'.$userID.',"'.$data['tag'].'")');
            }else{
                $excel_data  = DB::connection('sqlsrv2')->select($query.',1,0,0,"",'.$userID.',"'.$data['tag'].'")');
            }
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Invoice.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Invoice.xls';
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
            $query = $query.',0,0,1,"",'.$userID.',"'.$data['tag'].'")';
        }else{
            $query .=',0,0,0,"",'.$userID.',"'.$data['tag'].'")';
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
        Payment::multiLang_init();
        $CompanyID = User::get_companyID();
        $accounts = Account::getAccountIDList();
		$DefaultCurrencyID    	=   Company::where("CompanyID",$CompanyID)->pluck("CurrencyId");
        $invoice_status_json = json_encode(Invoice::get_invoice_status());
        //$emailTemplates = EmailTemplate::getTemplateArray(array('Type'=>EmailTemplate::INVOICE_TEMPLATE));
		$emailTemplates = EmailTemplate::getTemplateArray(array('StaticType'=>EmailTemplate::DYNAMICTEMPLATE));
        $templateoption = [''=>'Select',1=>'New Create',2=>'Update'];
        $data['StartDateDefault'] 	  	= 	'';
		$data['IssueDateEndDefault']  	= 	'';
        $InvoiceHideZeroValue = NeonCookie::getCookie('InvoiceHideZeroValue',1);
        $Quickbook = new BillingAPI($CompanyID);
        $check_quickbook = $Quickbook->check_quickbook($CompanyID);
        $check_quickbook_desktop = $Quickbook->check_quickbook_desktop($CompanyID);
		$bulk_type = 'invoices';
        //print_r($_COOKIE);exit;
        return View::make('invoices.index',compact('products','accounts','invoice_status_json','emailTemplates','templateoption','DefaultCurrencyID','data','invoice','InvoiceHideZeroValue','check_quickbook','check_quickbook_desktop','bulk_type','CompanyID'));

    }

    /**
     * Show the form for creating a new resource.
     * GET /invoices/create
     *
     * @return Response
     */
    public function create()
    {
        $companyID  =   User::get_companyID();
        $accounts 	= 	Account::getAccountIDList();
        $products 	= 	Product::getProductDropdownList($companyID);
        $taxes 		= 	TaxRate::getTaxRateDropdownIDListForInvoice(0,$companyID);
		//echo "<pre>"; 		print_r($taxes);		echo "</pre>"; exit;
        //$gateway_product_ids = Product::getGatewayProductIDs();
		$BillingClass = BillingClass::getDropdownIDList($companyID);

        $Type =  Product::DYNAMIC_TYPE;
        $productsControllerObj = new ProductsController();
        $DynamicFields = $productsControllerObj->getDynamicFields($companyID,$Type);
        $itemtypes 	= 	ItemType::getItemTypeDropdownList($companyID);

        return View::make('invoices.create',compact('accounts','products','taxes','BillingClass','DynamicFields','itemtypes'));

    }

    /**
     *
     * */
    public function edit($id){


        //$str = preg_replace('/^INV/', '', 'INV021000');;
        if($id > 0) {

            $Invoice = Invoice::find($id);
            $CompanyID = $Invoice->CompanyID;
			$InvoiceBillingClass =	 Invoice::GetInvoiceBillingClass($Invoice);			
            $InvoiceDetail = InvoiceDetail::where(["InvoiceID"=>$id])->get();
            $accounts = Account::getAccountIDList();
            $products = Product::getProductDropdownList($CompanyID);
            //$gateway_product_ids = Product::getGatewayProductIDs();
            $Account = Account::where(["AccountID" => $Invoice->AccountID])->select(["AccountName","BillingEmail", "CurrencyId"])->first(); //"TaxRateID","RoundChargesAmount","InvoiceTemplateID"
            $CurrencyID = !empty($Invoice->CurrencyID)?$Invoice->CurrencyID:$Account->CurrencyId;
            $RoundChargesAmount = get_round_decimal_places($Invoice->AccountID);
            $InvoiceTemplateID = BillingClass::getInvoiceTemplateID($InvoiceBillingClass);
            $InvoiceNumberPrefix = ($InvoiceTemplateID>0)?InvoiceTemplate::find($InvoiceTemplateID)->InvoiceNumberPrefix:'';
            $Currency = Currency::find($CurrencyID);
            $CurrencyCode = !empty($Currency)?$Currency->Code:'';
            $CompanyName = Company::getName($CompanyID);
            $taxes =  TaxRate::getTaxRateDropdownIDListForInvoice(0,$CompanyID);
            $invoicelog =  InVoiceLog::where(array('InvoiceID'=>$id))->get();
			$InvoiceAllTax =  InvoiceTaxRate::where(["InvoiceID"=>$id,"InvoiceTaxType"=>1])->get();
			$BillingClass = BillingClass::getDropdownIDList($CompanyID);

            $Type =  Product::DYNAMIC_TYPE;
            $productsControllerObj = new ProductsController();
            $DynamicFields = $productsControllerObj->getDynamicFields($CompanyID,$Type);
            $itemtypes 	= 	ItemType::getItemTypeDropdownList($CompanyID);
			
            return View::make('invoices.edit', compact( 'id', 'Invoice','InvoiceDetail','InvoiceTemplateID','InvoiceNumberPrefix',  'CurrencyCode','CurrencyID','RoundChargesAmount','accounts', 'products', 'taxes','CompanyName','Account','invoicelog','InvoiceAllTax','BillingClass','InvoiceBillingClass','DynamicFields','itemtypes'));
        }
    }

    /**
     * Store Invoice
     */
    public function store(){
        $data = Input::all();
        unset($data['BarCode']);
        if($data){
            $companyID = User::get_companyID();
            $CreatedBy = User::get_user_full_name();

            //$CurrencyId = Account::where("AccountID",intval($data["AccountID"]))->pluck('CurrencyId');
            $isAutoInvoiceNumber = true;
			$InvoiceData = array();
            if(!empty($data["InvoiceNumber"])){
                $isAutoInvoiceNumber = false;
				$InvoiceData["InvoiceNumber"] =  $data["InvoiceNumber"];
            }
			
			
			 if(isset($data['BillingClassID']) && $data['BillingClassID']>0){  
				$InvoiceTemplateID  = 	BillingClass::getInvoiceTemplateID($data['BillingClassID']);
				$InvoiceData["InvoiceNumber"] = $LastInvoiceNumber = ($isAutoInvoiceNumber)?InvoiceTemplate::getNextInvoiceNumber($InvoiceTemplateID):$data["InvoiceNumber"];
			 }
            
            $InvoiceData["CompanyID"] = $companyID;
            $InvoiceData["AccountID"] = intval($data["AccountID"]);
            $InvoiceData["Address"] = $data["Address"];
         
            $InvoiceData["IssueDate"] = $data["IssueDate"];
            $InvoiceData["PONumber"] = $data["PONumber"];
            $InvoiceData["SubTotal"] = str_replace(",","",$data["SubTotal"]);
            //$InvoiceData["TotalDiscount"] = str_replace(",","",$data["TotalDiscount"]);
			$InvoiceData["TotalDiscount"] = 0;
            $InvoiceData["TotalTax"] = str_replace(",","",$data["TotalTax"]);
			$InvoiceData["GrandTotal"] = floatval(str_replace(",","",$data["GrandTotalInvoice"]));
            //$InvoiceData["GrandTotal"] = floatval(str_replace(",","",$data["GrandTotal"]));
            $InvoiceData["CurrencyID"] = $data["CurrencyID"];
            $InvoiceData["InvoiceType"] = Invoice::INVOICE_OUT;
            $InvoiceData["InvoiceStatus"] = Invoice::AWAITING;
            $InvoiceData["ItemInvoice"] = Invoice::ITEM_INVOICE;
            $InvoiceData["Note"] = $data["Note"];
            $InvoiceData["Terms"] = $data["Terms"];
            $InvoiceData["FooterTerm"] = $data["FooterTerm"];
            $InvoiceData["CreatedBy"] = $CreatedBy;
			$InvoiceData['InvoiceTotal'] = str_replace(",","",$data["GrandTotal"]);
			$InvoiceData['BillingClassID'] =$data["BillingClassID"];
			
            //$InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($data["AccountID"]);
			
            if(!isset($InvoiceTemplateID) || (int)$InvoiceTemplateID == 0){
                return Response::json(array("status" => "failed", "message" => "Please enable billing."));
            }
            ///////////
            $rules = array(
                'CompanyID' => 'required',
                'AccountID' => 'required|integer|min:1',
                'Address' => 'required',
				'BillingClassID'=> 'required',
                'InvoiceNumber' => 'required|unique:tblInvoice,InvoiceNumber,NULL,InvoiceID,CompanyID,'.$companyID,
                'IssueDate' => 'required',
                'CurrencyID' => 'required',
                'GrandTotal' => 'required',
                'InvoiceType' => 'required',
            );
			$message = ['BillingClassID.required'=>'Billing Class field is required','AccountID'=>'Client field is required','AccountID.min'=>'Client field is required'];
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($InvoiceData, $rules,$message);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if(empty($data["InvoiceDetail"])) {
                return json_encode(["status"=>"failed","message"=>"Please select atleast one item."]);
            }

            try{
                $InvoiceData["FullInvoiceNumber"] = ($isAutoInvoiceNumber)?InvoiceTemplate::find($InvoiceTemplateID)->InvoiceNumberPrefix.$LastInvoiceNumber:$LastInvoiceNumber;
                DB::connection('sqlsrv2')->beginTransaction();
                $Invoice = Invoice::create($InvoiceData);
                //Store Last Invoice Number.
                if($isAutoInvoiceNumber) {
                    InvoiceTemplate::find($InvoiceTemplateID)->update(array("LastInvoiceNumber" => $LastInvoiceNumber ));
                }

                $InvoiceDetailData = $InvoiceTaxRates = $InvoiceAllTaxRates = array();

                foreach($data["InvoiceDetail"] as $field => $detail){ 
                    $i=0;
                    foreach($detail as $value){
                        if( in_array($field,["Price","Discount","TaxAmount","LineTotal"])){
                            $InvoiceDetailData[$i][$field] = str_replace(",","",$value);
                        }else if($field == "ProductID"){
                            if(!empty($value)) {
                                $pid = explode('-', $value);
                                $InvoiceDetailData[$i][$field] = $pid[1];
                            } else {
                                $InvoiceDetailData[$i][$field] = "";
                            }
                        }else{
                            $InvoiceDetailData[$i][$field] = $value;
                        }
						$InvoiceDetailData[$i]["Discount"] 	= 	0;
                        $InvoiceDetailData[$i]["InvoiceID"] = $Invoice->InvoiceID;
                        $InvoiceDetailData[$i]["created_at"] = date("Y-m-d H:i:s");
                        $InvoiceDetailData[$i]["CreatedBy"] = $CreatedBy;
                       /* if($field == 'TaxRateID'){
                            $InvoiceTaxRates[$i][$field] = $value;
                            $InvoiceTaxRates[$i]['Title'] = TaxRate::getTaxName($value);
                            $InvoiceTaxRates[$i]["created_at"] = date("Y-m-d H:i:s");
                            $InvoiceTaxRates[$i]["InvoiceID"] = $Invoice->InvoiceID;
                        }
						if($field == 'TaxAmount'){
                            $InvoiceTaxRates[$i][$field] = str_replace(",","",$value);
                        }
                       */
					    if(empty($InvoiceDetailData[$i]['ProductID'])){
                            unset($InvoiceDetailData[$i]);
                        }
                        $i++;
                    }
                }

                //StockHistory
                $StockHistory=array();
                $temparray=array();
                foreach($InvoiceDetailData as $CheckInvoiceHistory){
                    $prodType=intval($CheckInvoiceHistory['ProductType']);
                    if($prodType==1) {
                        $ProductID = intval($CheckInvoiceHistory['ProductID']);
                        $InvoiceID = intval($CheckInvoiceHistory['InvoiceID']);
                        $Qty = intval($CheckInvoiceHistory['Qty']);
                        $temparray['CompanyID']=$companyID;
                        $temparray['ProductID']=$ProductID;
                        $temparray['InvoiceID']=$InvoiceID;
                        $temparray['Qty']=$Qty;
                        $temparray['Reason']='';
                        $temparray['InvoiceNumber']=$InvoiceData["FullInvoiceNumber"];
                        $temparray['created_by']=User::get_user_full_name();

                        array_push($StockHistory,$temparray);
                        //$returnValidateData = stockHistoryValidateCalculation($companyID, $ProdID, $InvoiceID, $Qty, '', $InvoiceData["FullInvoiceNumber"]);
                        /*if ($returnValidateData && $returnValidateData['status'] == 'failed') {
                            return Response::json($returnValidateData);
                        }*/
                    }
                }

                $historyData=StockHistoryCalculations($StockHistory);

				//product tax
				if(isset($data['Tax']) && is_array($data['Tax'])){
					foreach($data['Tax'] as $j => $taxdata){
						$InvoiceTaxRates[$j]['TaxRateID'] 	= 	$j;
						$InvoiceTaxRates[$j]['Title'] 		= 	TaxRate::getTaxName($j);
						$InvoiceTaxRates[$j]["created_at"] 	= 	date("Y-m-d H:i:s");
						$InvoiceTaxRates[$j]["InvoiceID"] 	= 	$Invoice->InvoiceID;
						$InvoiceTaxRates[$j]["TaxAmount"] 	= 	$taxdata;
					}
				}
				
				//Invoice tax
				if(isset($data['InvoiceTaxes']) && is_array($data['InvoiceTaxes'])){
					foreach($data['InvoiceTaxes']['field'] as  $p =>  $InvoiceTaxes){
                        if(!empty($InvoiceTaxes)) {
                            $InvoiceAllTaxRates[$p]['TaxRateID'] = $InvoiceTaxes;
                            $InvoiceAllTaxRates[$p]['Title'] = TaxRate::getTaxName($InvoiceTaxes);
                            $InvoiceAllTaxRates[$p]["created_at"] = date("Y-m-d H:i:s");
                            $InvoiceAllTaxRates[$p]["InvoiceTaxType"] = 1;
                            $InvoiceAllTaxRates[$p]["InvoiceID"] = $Invoice->InvoiceID;
                            $InvoiceAllTaxRates[$p]["TaxAmount"] = $data['InvoiceTaxes']['value'][$p];
                        }
					}
				}
				
                /*$InvoiceTaxRates 	 = 	merge_tax($InvoiceTaxRates);
				$InvoiceAllTaxRates  = 	merge_tax($InvoiceAllTaxRates);*/
				
                $invoiceloddata = array();
                $invoiceloddata['InvoiceID']= $Invoice->InvoiceID;
                $invoiceloddata['Note']= 'Created By '.$CreatedBy;
                $invoiceloddata['created_at']= date("Y-m-d H:i:s");
                $invoiceloddata['InvoiceLogStatus']= InVoiceLog::CREATED;
                InVoiceLog::insert($invoiceloddata);
                /*if(!empty($InvoiceTaxRates)) { //product tax
                    InvoiceTaxRate::insert($InvoiceTaxRates);
                }*/
				
				 if(!empty($InvoiceAllTaxRates)) { //Invoice tax
                    InvoiceTaxRate::insert($InvoiceAllTaxRates);
                } 
                if (!empty($InvoiceDetailData) && InvoiceDetail::insert($InvoiceDetailData)) {
                    $InvoiceTaxRates1=TaxRate::getInvoiceTaxRateByProductDetail($Invoice->InvoiceID);
                    if(!empty($InvoiceTaxRates1)) { //Invoice tax
                        InvoiceTaxRate::insert($InvoiceTaxRates1);
                    }
                    $pdf_path = Invoice::generate_pdf($Invoice->InvoiceID); 
                    if (empty($pdf_path)) {
                        $error['message'] = 'Failed to generate Invoice PDF File';
                        $error['status'] = 'failure';
                        return $error;
                    } else {
                        $Invoice->update(["PDF" => $pdf_path]);
                    }

                    DB::connection('sqlsrv2')->commit();
                    $SuccessMsg="Invoice Successfully Created.";
                    $message='';
                    if(!empty($historyData)){
                        foreach($historyData as $msg){
                            $message.=$msg;
                            $message.="\n\r";
                        }
                    }
                    return Response::json(array("status" => "success","warning"=>$message, "message" => $SuccessMsg,'LastID'=>$Invoice->InvoiceID,'redirect' => URL::to('/invoice/'.$Invoice->InvoiceID.'/edit')));
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
        unset($data['BarCode']);
        if(!empty($data) && $id > 0){
            $Invoice = Invoice::find($id);
            $companyID = User::get_companyID();
            $CreatedBy = User::get_user_full_name();
            $FullInvoiceNumber=$Invoice->FullInvoiceNumber;
            $OldProductsarr=InvoiceDetail::where(['InvoiceID'=>$Invoice->InvoiceID])->get(['ProductID','Qty','ProductType','InvoiceDetailID'])->toArray();

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
            $InvoiceData["GrandTotal"] = floatval(str_replace(",","",$data["GrandTotalInvoice"]));
            $InvoiceData["CurrencyID"] = $data["CurrencyID"];
            $InvoiceData["Note"] = $data["Note"];
            $InvoiceData["Terms"] = $data["Terms"];
            $InvoiceData["FooterTerm"] = $data["FooterTerm"];
            $InvoiceData["ModifiedBy"] = $CreatedBy;
			$InvoiceData['InvoiceTotal'] = str_replace(",","",$data["GrandTotal"]);
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

            if(empty($data["InvoiceDetail"])) {
                return json_encode(["status"=>"failed","message"=>"Please select atleast one item."]);
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
					$InvoiceDetailData = $StockHistoryData = $InvoiceTaxRates = $InvoiceAllTaxRates = array();
                    //Delete all Invoice Data and then Recreate.
                    InvoiceDetail::where(["InvoiceID" => $Invoice->InvoiceID])->delete();
                    InvoiceTaxRate::where(["InvoiceID" => $Invoice->InvoiceID])->delete();
                    if (isset($data["InvoiceDetail"])) {
                        foreach ($data["InvoiceDetail"] as $field => $detail) {
                            $i = 0;
                            foreach ($detail as $value) {								
                                if( in_array($field,["Price","Discount","TaxAmount","LineTotal"])){
                                    $InvoiceDetailData[$i][$field] = str_replace(",","",$value);
                                }else if($field == "ProductID"){
                                    if(!empty($value)) {
                                        $pid = explode('-', $value);
                                        $InvoiceDetailData[$i][$field] = $pid[1];
                                        $StockHistoryData[$i][$field] = $pid[1];
                                        $StockHistoryData[$i]["InvoiceID"] = $Invoice->InvoiceID;

                                        /**
                                         *  1. if product is new not in old and exists in new
                                         *  2. existinsg product checke update qty with old
                                         */

                                    } else {
                                        $InvoiceDetailData[$i][$field] = "";
                                        $StockHistoryData[$i][$field] = "";
                                    }
                                }else{
                                    $InvoiceDetailData[$i][$field] = $value;
                                    $StockHistoryData[$i][$field] = $value;
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
                                /*if($field == 'TaxRateID'){
									$txname = TaxRate::getTaxName($value);
                                    $InvoiceTaxRates[$txname][$j][$field] = $value;
                                    $InvoiceTaxRates[$txname][$j]['Title'] = TaxRate::getTaxName($value);
                                    $InvoiceTaxRates[$txname][$j]["created_at"] = date("Y-m-d H:i:s");
                                    $InvoiceTaxRates[$txname][$j]["InvoiceID"] = $Invoice->InvoiceID;
                                }
								if($field == 'TaxRateID2'){
									$txname = TaxRate::getTaxName($value);
                                    $InvoiceTaxRates[$txname][$j][$field] = $value;
                                    $InvoiceTaxRates[$txname][$j]['Title'] = TaxRate::getTaxName($value);
                                    $InvoiceTaxRates[$txname][$j]["created_at"] = date("Y-m-d H:i:s");
                                    $InvoiceTaxRates[$txname][$j]["InvoiceID"] = $Invoice->InvoiceID;
                                }
                                if($field == 'TaxAmount'){
                                    $InvoiceTaxRates[$txname][$field] = str_replace(",","",$value);
                                }*/
                                $i++;								
                            }
                        }

						if(isset($data['Tax']) && is_array($data['Tax'])){
							foreach($data['Tax'] as $j => $taxdata)
							{
							 	$InvoiceTaxRates[$j]['TaxRateID'] 	= 	$j;
                                $InvoiceTaxRates[$j]['Title'] 		= 	TaxRate::getTaxName($j);
                                $InvoiceTaxRates[$j]["created_at"] 	= 	date("Y-m-d H:i:s");
                                $InvoiceTaxRates[$j]["InvoiceID"] 	= 	$Invoice->InvoiceID;
								$InvoiceTaxRates[$j]["TaxAmount"] 	= 	$taxdata;
							}
						}
						
						if(isset($data['InvoiceTaxes']) && is_array($data['InvoiceTaxes'])){
                            foreach($data['InvoiceTaxes']['field'] as  $p =>  $InvoiceTaxes){
                                if(!empty($InvoiceTaxes)) {
                                    $InvoiceAllTaxRates[$p]['TaxRateID'] = $InvoiceTaxes;
                                    $InvoiceAllTaxRates[$p]['Title'] = TaxRate::getTaxName($InvoiceTaxes);
                                    $InvoiceAllTaxRates[$p]["created_at"] = date("Y-m-d H:i:s");
                                    $InvoiceAllTaxRates[$p]["InvoiceTaxType"] = 1;
                                    $InvoiceAllTaxRates[$p]["InvoiceID"] = $Invoice->InvoiceID;
                                    $InvoiceAllTaxRates[$p]["TaxAmount"] = $data['InvoiceTaxes']['value'][$p];
                                }
                            }
				        }
						
                        /*$InvoiceTaxRates 	  =     merge_tax($InvoiceTaxRates);
						$InvoiceAllTaxRates   = 	merge_tax($InvoiceAllTaxRates);*/
						
                        /*if(!empty($InvoiceTaxRates)) { //product tax
                            InvoiceTaxRate::insert($InvoiceTaxRates);
                        }*/

						 if(!empty($InvoiceAllTaxRates)) { //Invoice tax
                 		   InvoiceTaxRate::insert($InvoiceAllTaxRates);
                         }
						
                        if (!empty($InvoiceDetailData) && InvoiceDetail::insert($InvoiceDetailData)) {
                            $InvoiceTaxRates1=TaxRate::getInvoiceTaxRateByProductDetail($Invoice->InvoiceID);
                            if(!empty($InvoiceTaxRates1)) { //Invoice tax
                                InvoiceTaxRate::insert($InvoiceTaxRates1);
                            }
                            $pdf_path = Invoice::generate_pdf($Invoice->InvoiceID);
                            if (empty($pdf_path)) {
                                $error['message'] = 'Failed to generate Invoice PDF File';
                                $error['status'] = 'failure';
                                return $error;
                            } else {
                                $Invoice->update(["PDF" => $pdf_path]);
                            }

                            //StockHistory Maintain
                            $MultiProductSumQtyArr=array();
                            $OldProductsarr=sumofQtyIfSameProduct($OldProductsarr);
                            $MultiProductSumQtyArr=sumofQtyIfSameProduct($InvoiceDetailData);

                            $StockHistory=array();
                            $temparray=array();

                            //For Create New If not Exist
                            foreach($MultiProductSumQtyArr as $InvoiceHistory){
                                $prodType=intval($InvoiceHistory['ProductType']);
                                if($prodType==1) {
                                    $ProdID = intval($InvoiceHistory['ProductID']);
                                    $InvoiceID = intval($InvoiceHistory['InvoiceID']);
                                    $Qty = intval($InvoiceHistory['Qty']);
                                    $key_of_arr = searchArrayByProductID($ProdID, $OldProductsarr);
                                    if(intval($key_of_arr) < 0){
                                        //Create New
                                        $temparray['CompanyID']=$companyID;
                                        $temparray['ProductID']=$ProdID;
                                        $temparray['InvoiceID']=$InvoiceID;
                                        $temparray['Qty']=$Qty;
                                        $temparray['Reason']='';
                                        $temparray['InvoiceNumber']=$FullInvoiceNumber;
                                        $temparray['created_by']=User::get_user_full_name();

                                        array_push($StockHistory,$temparray);

                                    }
                                }
                            }

                            if(!empty($StockHistory)){
                                $historyData=StockHistoryCalculations($StockHistory);
                            }

                            //StockHistory update/delete.
                            $StockHistoryUpdate=array();
                            $temparrayUpdate=array();
                            foreach($OldProductsarr as $OldProduct){
                                $prodType=intval($OldProduct['ProductType']);
                                $ProdID=$OldProduct['ProductID'];
                                $oldQty=intval($OldProduct['Qty']);
                                if($prodType==1) {
                                    $InvoiceDetailID=$OldProduct['InvoiceDetailID'];
                                    $InvoiceNo=Invoice::where('InvoiceID',$id)->pluck('FullInvoiceNumber');
                                    $key_of_arr = searchArrayByProductID($ProdID, $MultiProductSumQtyArr);
                                    if(intval($key_of_arr)>=0){
                                        //Update Prod
                                        $res_prod=getArrayByProductID($ProdID, $MultiProductSumQtyArr);
                                        $temparrayUpdate['ProductID']=intval($res_prod['ProductID']);
                                        $temparrayUpdate['Qty']=intval($res_prod['Qty']);
                                        $temparrayUpdate['Reason']='';
                                        $temparrayUpdate['oldQty']=$oldQty;

                                    }else{
                                        //delete Prod
                                        $temparrayUpdate['ProductID']=$ProdID;
                                        $temparrayUpdate['Qty']=$OldProduct['Qty'];
                                        $temparrayUpdate['Reason']='delete_prodstock';
                                        $temparrayUpdate['oldQty']=$oldQty;
                                    }
                                    $temparrayUpdate['CompanyID']=$companyID;
                                    $temparrayUpdate['InvoiceID']=$id;
                                    $temparrayUpdate['InvoiceNumber']=$InvoiceNo;
                                    $temparrayUpdate['created_by']=User::get_user_full_name();
                                    array_push($StockHistoryUpdate,$temparrayUpdate);
                                }
                            }

                            if(!empty($StockHistoryUpdate)){
                                $historyData=stockHistoryUpdateCalculations($StockHistoryUpdate);
                            }

                            //End Stock History Maintain

                            DB::connection('sqlsrv2')->commit();
                            $message='';
                            if(!empty($historyData)){
                                foreach($historyData as $msg){
                                    $message.=$msg;
                                    $message.="\n";
                                }
                            }
                            return Response::json(array("status" => "success","warning"=>$message, "message" => "Invoice Successfully Updated", 'LastID' => $Invoice->InvoiceID));
                        } else {
                            DB::connection('sqlsrv2')->rollback();
                            return Response::json(array("status" => "failed", "message" => "Problem Updating Invoice."));
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
                //$InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($AccountID);
				$InvoiceTemplateID   = 	BillingClass::getInvoiceTemplateID($data['BillingClassID']);
                $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
                if (isset($InvoiceTemplate->InvoiceTemplateID) && $InvoiceTemplate->InvoiceTemplateID > 0) {
                    $decimal_places = get_round_decimal_places($AccountID);

                    if (Product::$ProductTypes[$data['product_type']] == Product::ITEM) {

                        $companyID = User::get_companyID();
                        $data['CompanyID'] = $companyID;

                        $Product = Product::find($data['product_id']);
                        if (!empty($Product)) {


                            $ProductAmount = number_format($Product->Amount, $decimal_places,".","");
                            $ProductDescription = $Product->Description;

                            $TaxRates = array();
                            $TaxRates = TaxRate::where(array('CompanyID' => User::get_companyID(), "TaxType" => TaxRate::TAX_ALL))->select(['TaxRateID', 'Title', 'Amount','FlatStatus'])->first();
                            if(!empty($TaxRates)){
                                $TaxRates->toArray();
                            }
                            //$AccountTaxRate = explode(",",AccountBilling::getTaxRate($AccountID));
							$AccountTaxRate =  explode(",",BillingClass::getTaxRate($data['BillingClassID']));
							//\Illuminate\Support\Facades\Log::error(print_r($TaxRates, true));

                            $TaxRateAmount = $TaxRateId = $FlatStatus =  0; 
                            $TaxRateTitle = 'VAT';
                            if (isset($TaxRates['TaxRateID']) && in_array($TaxRates['TaxRateID'], $AccountTaxRate)) {

                                $TaxRateId = $TaxRates['TaxRateID'];
                                $TaxRateAmount = 0;
                                $TaxRateTitle = $TaxRates['Title'];
                                if (isset($TaxRates['Amount'])) {
                                    $TaxRateAmount = $TaxRates['Amount'];
                                }
								
								if (isset($TaxRates['FlatStatus'])) {
                                    $FlatStatus = $TaxRates['FlatStatus'];
                                }

                            }
							
							if($FlatStatus==1){	
                           
						    	$TotalTax  =  number_format($TaxRateAmount, $decimal_places,".","");
							}
							else
							{
								$TotalTax  =  number_format((($ProductAmount * $data['qty'] * $TaxRateAmount) / 100), $decimal_places,".","");
							
							}
                            $SubTotal = number_format($ProductAmount * $data['qty'], $decimal_places,".",""); //number_format(($ProductAmount + $TotalTax) , 2);

                            $response = [
                                "status" => "success",
                                "product_description" => $ProductDescription,
                                "product_amount" => $ProductAmount,
                               // "product_tax_rate_id" => $TaxRateId,
                                //"product_total_tax_rate" => $TotalTax,
								 "product_total_tax_rate" => 0,								
                                "sub_total" => $SubTotal,
                                "decimal_places" => $decimal_places,
                                "product_tax_title" => $TaxRateTitle,
                            ];
                        } else {
                            $error = "No Product Found.";
                        }

                    }elseif(Product::$ProductTypes[$data['product_type']] == Product::SUBSCRIPTION) {
                        $companyID = User::get_companyID();
                        $data['CompanyID'] = $companyID;

                        $Subscription = BillingSubscription::find($data['product_id']);
                        if (!empty($Subscription)) {
                            /*if($AccountBilling->BillingCycleType=='daily'){
                                $ProductAmount = number_format($Subscription->DailyFee, $decimal_places,".","");
                            }elseif($AccountBilling->BillingCycleType=='weekly'){
                                $ProductAmount = number_format($Subscription->WeeklyFee, $decimal_places,".","");
                            }elseif($AccountBilling->BillingCycleType=='monthly'){
                                $ProductAmount = number_format($Subscription->MonthlyFee, $decimal_places,".","");
                            }elseif($AccountBilling->BillingCycleType=='quarterly'){
                                $ProductAmount = number_format($Subscription->QuarterlyFee, $decimal_places,".","");
                            }elseif($AccountBilling->BillingCycleType=='yearly'){
                                $ProductAmount = number_format($Subscription->AnnuallyFee, $decimal_places,".","");
                            }else{
                                $ProductAmount = number_format($Subscription->MonthlyFee, $decimal_places,".","");
                            }*/
							
							$ProductAmount = number_format($Subscription->MonthlyFee, $decimal_places,".","");
							if(!is_numeric($ProductAmount)){
								$ProductAmount = number_format(0, $decimal_places,".","");
							}

                            $ProductDescription = $Subscription->InvoiceLineDescription;

                            $TaxRates = array();
                            $TaxRates = TaxRate::where(array('CompanyID' => User::get_companyID(), "TaxType" => TaxRate::TAX_ALL))->select(['TaxRateID', 'Title', 'Amount'])->first();
                            if(!empty($TaxRates)){
                                $TaxRates->toArray();
                            }
                            //$AccountTaxRate = explode(",", $AccountBilling->TaxRateId);
                           // $AccountTaxRate = explode(",",AccountBilling::getTaxRate($AccountID));
						    $AccountTaxRate =  explode(",",BillingClass::getTaxRate($data['BillingClassID']));

                            $TaxRateAmount = $TaxRateId = 0;
                            if (isset($TaxRates['TaxRateID']) && in_array($TaxRates['TaxRateID'], $AccountTaxRate)) {

                                $TaxRateId = $TaxRates['TaxRateID'];
                                $TaxRateAmount = 0;
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
                                //"product_tax_rate_id" => $TaxRateId,
                                //"product_total_tax_rate" => $TotalTax,
                                "product_total_tax_rate" => 0,
                                "sub_total" => $SubTotal,
                                "decimal_places" => $decimal_places,
                            ];
                        } else {
                            $error = "No Subscription Found.";
                        }
                    } else {

                        $error = "No Invoice Template Assigned to Account";
                    }
                } else {
                    $error = "Billing Class Not Found, Please select Account and Billing Class both.";
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
            $fields =["CurrencyId","Address1","AccountID","Address2","Address3","City","PostCode","Country"];
            $Account = Account::where(["AccountID"=>$data['account_id']])->select($fields)->first();
            $Currency = Currency::getCurrencySymbol($Account->CurrencyId);
            $InvoiceTemplateID  = 	AccountBilling::getInvoiceTemplateID($Account->AccountID);
            $CurrencyId = $Account->CurrencyId;
            $Address = Account::getFullAddress($Account);

            $Terms = $FooterTerm = $InvoiceToAddress ='';
			
			 $AccountTaxRate = AccountBilling::getTaxRateType($Account->AccountID,TaxRate::TAX_ALL);
			//\Illuminate\Support\Facades\Log::error(print_r($TaxRates, true));
		
           // if(isset($InvoiceTemplateID) && $InvoiceTemplateID > 0) {
                $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
                /* for item invoice generate - invoice to address as invoice template */
				
				if(isset($InvoiceTemplateID) && $InvoiceTemplateID > 0) {
                	$message = $InvoiceTemplate->InvoiceTo;
                	$replace_array = Invoice::create_accountdetails($Account);
	                $text = Invoice::getInvoiceToByAccount($message,$replace_array);
    	            $InvoiceToAddress = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
				    $Terms = $InvoiceTemplate->Terms;
    	            $FooterTerm = $InvoiceTemplate->FooterTerm;
				}
				else{
					$InvoiceToAddress 	= 	'';
				    $Terms 				= 	'';
    	            $FooterTerm 		= 	'';
				}
				$BillingClassID     =   AccountBilling::getBillingClassID($data['account_id']);
				
                $return = ['Terms','FooterTerm','Currency','CurrencyId','Address','InvoiceTemplateID','AccountTaxRate','InvoiceToAddress','BillingClassID'];
            /*}else{
                return Response::json(array("status" => "failed", "message" => "You can not create Invoice for this Account. as It has no Invoice Template assigned" ));
            }*/
            return Response::json(compact($return));
        }
    }
	
	public function getBillingclassInfo(){
		
        $data = Input::all();
        if ((isset($data['BillingClassID']) && $data['BillingClassID'] > 0 ) && (isset($data['account_id']) && $data['account_id'] > 0 ) ) {
            $fields =["CurrencyId","Address1","AccountID","Address2","Address3","City","PostCode","Country"];
            $Account = Account::where(["AccountID"=>$data['account_id']])->select($fields)->first();
            $InvoiceTemplateID  = 	BillingClass::getInvoiceTemplateID($data['BillingClassID']);
            $Terms = $FooterTerm = $InvoiceToAddress ='';						
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
                /* for item invoice generate - invoice to address as invoice template */
				
			if(isset($InvoiceTemplateID) && $InvoiceTemplateID > 0) {
				$message = $InvoiceTemplate->InvoiceTo;
				$replace_array = Invoice::create_accountdetails($Account);
				$text = Invoice::getInvoiceToByAccount($message,$replace_array);
				$InvoiceToAddress = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
				$Terms = $InvoiceTemplate->Terms;
				$FooterTerm = $InvoiceTemplate->FooterTerm;			
				$AccountTaxRate  = BillingClass::getTaxRateType($data['BillingClassID'],TaxRate::TAX_ALL);
				$return = ['Terms','FooterTerm','InvoiceTemplateID','InvoiceToAddress','AccountTaxRate'];
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
        $InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($Invoice->AccountID);
        $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
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
            //$companyID 			= 	User::get_companyID();
			$companyID 			= 	$Account->CompanyId; // User::get_companyID();
            /*
			$query 				= 	"CALL `prc_getInvoicePayments`('".$id."','".$companyID."');";			
			$result   			=	DataTableSql::of($query,'sqlsrv2')->getProcResult(array('result'));			
			$payment_log		= 	array("total"=>$result['data']['result'][0]->total_grand,"paid_amount"=>$result['data']['result'][0]->paid_amount,"due_amount"=>$result['data']['result'][0]->due_amount);
            */
            $PaymentMethod = '';
            $ShowAllPaymentMethod = $Account->ShowAllPaymentMethod;
            if(empty($ShowAllPaymentMethod)){
                $PaymentMethod = $Account->PaymentMethod;
            }
            $InvoiceBillingClass =	 Invoice::GetInvoiceBillingClass($Invoice);
            $InvoiceTemplateID = BillingClass::getInvoiceTemplateID($InvoiceBillingClass);
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);

            $StripeACHGatewayID = PaymentGateway::StripeACH;
            $StripeACHCount=0;
            $AccountPaymentProfile = AccountPaymentProfile::where(['PaymentGatewayID'=> $StripeACHGatewayID,'AccountID'=>$Account->AccountID,'Status'=>1])->count();
            if($AccountPaymentProfile>0){
                $StripeACHCount=1;
            }
            $payment_log = Payment::getPaymentByInvoice($id);

            $paypal_button = $sagepay_button = "";
            $paypal = new PaypalIpn($Invoice->CompanyID);
            if(!empty($paypal->status)){
                $paypal->item_title =  Company::getName($Invoice->CompanyID).  ' Invoice #'.$Invoice->FullInvoiceNumber;
                $paypal->item_number =  $Invoice->FullInvoiceNumber;
                $paypal->curreny_code =  $CurrencyCode;

                $paypal->amount = $payment_log['final_payment'];

                //@TODO: this code is duplicate in view please centralize it.
                /*
                if($Invoice->InvoiceStatus==Invoice::PAID){
                    // full payment done.
                    $paypal->amount = 0;
                }elseif($Invoice->InvoiceStatus!=Invoice::PAID && $payment_log['paid_amount']>0){
                    //partial payment.
                    $paypal->amount = number_format($payment_log['due_amount'],get_round_decimal_places($Invoice->AccountID),'.','');
                }else {
                    $paypal->amount = number_format($payment_log['total'],get_round_decimal_places($Invoice->AccountID),'.','');
                } */

                $paypal_button = $paypal->get_paynow_button($Invoice->AccountID,$Invoice->InvoiceID);
            }
            if ( (new SagePay($Invoice->CompanyID))->status()) {

                $SagePay = new SagePay($Invoice->CompanyID);

                $SagePay->item_title =  Company::getName($Invoice->CompanyID).  ' Invoice #'.$Invoice->FullInvoiceNumber;
                $SagePay->item_number =  $Invoice->FullInvoiceNumber;
                $SagePay->curreny_code =  $CurrencyCode;


                $SagePay->amount = $payment_log['final_payment'];

                $sagepay_button = $SagePay->get_paynow_button($Invoice->AccountID,$Invoice->InvoiceID);

            }

            return View::make('invoices.invoice_cview', compact('Invoice', 'InvoiceDetail', 'Account', 'InvoiceTemplate', 'CurrencyCode', 'logo','CurrencySymbol','payment_log','paypal_button','sagepay_button','StripeACHCount','ShowAllPaymentMethod','PaymentMethod','InvoiceTemplate'));
        }
    }

    // not in use
    public function pdf_view($id) {


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

    //Generate Item Based Invoice PDF - not using
    public function generate_pdf($id){   
        if($id>0) {
            $Invoice = Invoice::find($id);
            $InvoiceDetail = InvoiceDetail::where(["InvoiceID" => $id])->get();
            $Account = Account::find($Invoice->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency)?$Currency->Code:'';
			$InvoiceTemplateID = Invoice::GetInvoiceTemplateID($Invoice);
            //$InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($Invoice->AccountID);
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            if (empty($InvoiceTemplate->CompanyLogoUrl)) {
                $as3url =  public_path("/assets/images/250x100.png"); 
            } else {
                $as3url = (AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key));
            }  
            $logo_path = CompanyConfiguration::get('UPLOAD_PATH') . '/logo/' . $Account->CompanyId;
            @mkdir($logo_path, 0777, true);
            //RemoteSSH::run("chmod -R 777 " . $logo_path); 
            $logo = $logo_path  . '/'  . basename($as3url); 
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
			$print_type = 'Invoice';
            $body = View::make('invoices.pdf', compact('Invoice', 'InvoiceDetail', 'Account', 'InvoiceTemplate', 'usage_data', 'CurrencyCode', 'logo','print_type'))->render();
            $destination_dir = CompanyConfiguration::get('UPLOAD_PATH') . '/'. AmazonS3::generate_path(AmazonS3::$dir['INVOICE_UPLOAD'],$Account->CompanyId) ;
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
        $jobdata["created_at"] = date('Y-m-d H:i:s');
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
        $fields =["CurrencyId","Address1","Address2","Address3","City","Country"];
        $Account = Account::where(["AccountID"=>$data['AccountID']])->select($fields)->first();
        $message = '';
        if (Input::hasFile('Attachment')) {
            $upload_path = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID);
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['VENDOR_UPLOAD'],$CompanyID);
            $destinationPath = $upload_path . '/' . $amazonPath;
            $Attachment = Input::file('Attachment');
            // ->move($destinationPath);
            $ext = $Attachment->getClientOriginalExtension();
            if (in_array(strtolower($ext), array("pdf", "jpg", "png", "gif"))) {
                $file_name = GUID::generate() . '.' . $Attachment->getClientOriginalExtension();
                $Attachment->move($destinationPath, $file_name);
                if (!AmazonS3::upload($destinationPath.$file_name, $amazonPath,$CompanyID)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
            }else{
                $message = $ext.' extension is not allowed. file not uploaded.';
            }
        }

        $CreatedBy = User::get_user_full_name();
        $Address = Account::getFullAddress($Account);

        $InvoiceData = array();
        $InvoiceData["CompanyID"] = $CompanyID;
        $InvoiceData["AccountID"] = $data["AccountID"];
        $InvoiceData["Address"] = $Address;
        $InvoiceData["InvoiceNumber"] = $data["InvoiceNumber"];
        $InvoiceData["FullInvoiceNumber"] = $data["InvoiceNumber"];
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
            $InvoiceDetailData['TotalMinutes'] = $data['TotalMinutes'];
            $InvoiceDetailData['Price'] = floatval(str_replace(",","",$data["GrandTotal"]));
            $InvoiceDetailData['Qty'] = 1;
            $InvoiceDetailData['ProductType'] = Product::INVOICE_PERIOD;
            $InvoiceDetailData['LineTotal'] = floatval(str_replace(",","",$data["GrandTotal"]));
            $InvoiceDetailData["created_at"] = date("Y-m-d H:i:s");
            $InvoiceDetailData['Description'] = 'Invoice In';
            $InvoiceDetailData['ProductID'] = 0;
            $InvoiceDetailData["CreatedBy"] = $CreatedBy;
            InvoiceDetail::insert($InvoiceDetailData);

            //if( $data["DisputeTotal"] != '' && $data["DisputeDifference"] != '' && $data["DisputeMinutes"] != '' && $data["MinutesDifference"] != '' ){
            if( !empty($data["DisputeAmount"])  ){

                //Dispute::add_update_dispute(array( "DisputeID"=> $data["DisputeID"],  "InvoiceID"=>$Invoice->InvoiceID,"DisputeTotal"=>$data["DisputeTotal"],"DisputeDifference"=>$data["DisputeDifference"],"DisputeDifferencePer"=>$data["DisputeDifferencePer"],"DisputeMinutes"=>$data["DisputeMinutes"],"MinutesDifference"=>$data["MinutesDifference"],"MinutesDifferencePer"=>$data["MinutesDifferencePer"]));
                Dispute::add_update_dispute(array( "DisputeID"=> $data["DisputeID"],"InvoiceType"=>Invoice::INVOICE_IN,  "AccountID"=> $data["AccountID"], "InvoiceNo"=>$data["InvoiceNumber"],"DisputeAmount"=>$data["DisputeAmount"],"sendEmail"=>1));

            }

            return Response::json(["status" => "success", "message" => "Invoice in Created successfully. ".$message]);

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
        $fields =["CurrencyId","Address1","Address2","Address3","City","Country"];
        $Account = Account::where(["AccountID"=>$data['AccountID']])->select($fields)->first();
        $message = '';
        if (Input::hasFile('Attachment')) {
            $upload_path = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID);
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['VENDOR_UPLOAD'],$CompanyID);
            $destinationPath = $upload_path . '/' . $amazonPath;
            $Attachment = Input::file('Attachment');
            // ->move($destinationPath);
            $ext = $Attachment->getClientOriginalExtension();
            if (in_array(strtolower($ext), array("pdf", "jpg", "png", "gif"))) {
                $file_name = GUID::generate() . '.' . $Attachment->getClientOriginalExtension();
                $Attachment->move($destinationPath, $file_name);
                if (!AmazonS3::upload($destinationPath.$file_name, $amazonPath,$CompanyID)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
            }else{
                $message = $ext.' extension is not allowed. file not uploaded.';
            }
        }

        $CreatedBy = User::get_user_full_name();
        $Address = Account::getFullAddress($Account);

        $InvoiceData = array();
        $InvoiceData["CompanyID"] = $CompanyID;
        $InvoiceData["AccountID"] = $data["AccountID"];
        $InvoiceData["Address"] = $Address;
        $InvoiceData["InvoiceNumber"] = $data["InvoiceNumber"];
        $InvoiceData["FullInvoiceNumber"] = $data["InvoiceNumber"];
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
        $InvoiceDetailData['TotalMinutes'] = floatval(str_replace(",","",$data["TotalMinutes"]));
        $InvoiceDetailData['LineTotal'] = floatval(str_replace(",","",$data["GrandTotal"]));
        $InvoiceDetailData["updated_at"] = date("Y-m-d H:i:s");
        $InvoiceDetailData['Description'] = $data['Description'];
        $InvoiceDetailData["ModifiedBy"] = $CreatedBy;
        if(Invoice::find($id)->update($InvoiceData)) {
            if(InvoiceDetail::find($data['InvoiceDetailID'])->update($InvoiceDetailData)) {

                //if( $data["DisputeTotal"] != '' && $data["DisputeDifference"] != '' && $data["DisputeMinutes"] != '' && $data["MinutesDifference"] != '' ){
                if( $data["DisputeID"] > 0 && !empty($data["DisputeAmount"]) ){

                    //Dispute::add_update_dispute(array( "DisputeID"=> $data["DisputeID"],  "InvoiceID"=>$id,"DisputeTotal"=>$data["DisputeTotal"],"DisputeDifference"=>$data["DisputeDifference"],"DisputeDifferencePer"=>$data["DisputeDifferencePer"],"DisputeMinutes"=>$data["DisputeMinutes"],"MinutesDifference"=>$data["MinutesDifference"],"MinutesDifferencePer"=>$data["MinutesDifferencePer"]));
                    Dispute::add_update_dispute(array( "DisputeID"=> $data["DisputeID"], "InvoiceType"=>Invoice::INVOICE_IN,"AccountID"=> $data["AccountID"], "InvoiceNo"=>$data["InvoiceNumber"],"DisputeAmount"=>$data["DisputeAmount"]));
                }
                return Response::json(["status" => "success", "message" => "Invoice in updated successfully. ".$message]);
            }else{
                return Response::json(["status" => "success", "message" => "Problem Updating Invoice"]);
            }
        }else{
            return Response::json(["status" => "success", "message" => "Problem Updating Invoice"]);
        }
    }
    public function  download_doc_file($id){
        $DocumentFile = Invoice::where(["InvoiceID"=>$id])->pluck('Attachment');
        $Invoice = Invoice::find($id);
        $CompanyID = $Invoice->CompanyID;
        if(file_exists($DocumentFile)){
            download_file($DocumentFile);
        }else{
            $FilePath =  AmazonS3::preSignedUrl($DocumentFile,$CompanyID);
            if(file_exists($FilePath))
            {
                download_file($FilePath);
            }
            elseif(is_amazon($CompanyID) == true)
            {
                header('Location: '.$FilePath);
            }
        }
        exit;
    }

    public function invoice_email($id) {
        $Invoice = Invoice::find($id);
        if(!empty($Invoice)) {
            $Account = Account::find($Invoice->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $companyID = User::get_companyID();
            $CompanyName = Company::getName();
            if (!empty($Currency)) {
               // $Subject = "New Invoice " . $Invoice->FullInvoiceNumber . ' from ' . $CompanyName . ' ('.$Account->AccountName.')';
			    $templateData	 	 = 	 EmailTemplate::getSystemEmailTemplate($Invoice->CompanyID, Invoice::EMAILTEMPLATE, $Account->LanguageID );
				$data['InvoiceURL']	 =   URL::to('/invoice/'.$Invoice->AccountID.'-'.$Invoice->InvoiceID.'/cview?email=#email');
			//	$Subject	 		 = 	 $templateData->Subject;
			//	$Message 	 		 = 	 $templateData->TemplateBody;		
				$Message	 		 =	 EmailsTemplates::SendinvoiceSingle($id,'body',$data);
				$Subject	 		 =	 EmailsTemplates::SendinvoiceSingle($id,"subject",$data);
				
				$response_api_extensions 	=    Get_Api_file_extentsions();
			    if(isset($response_api_extensions->headers)){ return	Redirect::to('/logout'); 	}	
			    $response_extensions		=	json_encode($response_api_extensions['allowed_extensions']); 
			    $max_file_size				=	get_max_file_size();	
				 
				if(!empty($Subject) && !empty($Message)){
					$from	 = $templateData->EmailFrom;
					return View::make('invoices.email', compact('Invoice', 'Account', 'Subject','Message','CompanyName','from','response_extensions','max_file_size'));
				}
				return Response::json(["status" => "failure", "message" => "Subject or message is empty"]);
	            
                
            }
        }
    }
    public function send($id){
        if($id){
            set_time_limit(600); // 10 min time limit.
            $CreatedBy = User::get_user_full_name();
            $data = Input::all(); //Log::info(print_r($data,true)); exit;
			$postdata = Input::all();
            $Invoice = Invoice::find($id);
            $Company = Company::find($Invoice->CompanyID);
            $CompanyName = $Company->CompanyName;
            //$InvoiceGenerationEmail = CompanySetting::getKeyVal('InvoiceGenerationEmail');
            $InvoiceCopy = Notification::getNotificationMail(Notification::InvoiceCopy,$Invoice->CompanyID);
            $InvoiceCopy = empty($InvoiceCopy)?$Company->Email:$InvoiceCopy;
            $emailtoCustomer = CompanyConfiguration::get('EMAIL_TO_CUSTOMER',$Invoice->CompanyID);
            if(intval($emailtoCustomer) == 1){
                $CustomerEmail = $data['Email'];
            }else{
                $CustomerEmail = $Company->Email;
            }
            $data['EmailTo'] = explode(",",$CustomerEmail);
            $data['InvoiceURL'] = URL::to('/invoice/'.$Invoice->AccountID.'-'.$Invoice->InvoiceID.'/cview'); 
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
			
			
		   $attachmentsinfo        =	$data['attachmentsinfo']; 
			if(!empty($attachmentsinfo) && count($attachmentsinfo)>0){
				$files_array = json_decode($attachmentsinfo,true);
			}
	
			if(!empty($files_array) && count($files_array)>0) {
				$FilesArray = array();
				foreach($files_array as $key=> $array_file_data){
					$file_name  = basename($array_file_data['filepath']); 
					$amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['EMAIL_ATTACHMENT'],'',$Invoice->CompanyID);
					$destinationPath = CompanyConfiguration::get('UPLOAD_PATH',$Invoice->CompanyID) . '/' . $amazonPath;
	
					if (!file_exists($destinationPath)) {
						mkdir($destinationPath, 0777, true);
					}
					copy($array_file_data['filepath'], $destinationPath . $file_name);
					if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath,$Invoice->CompanyID)) {
						return Response::json(array("status" => "failed", "message" => "Failed to upload file." ));
					}
					$FilesArray[] = array ("filename"=>$array_file_data['filename'],"filepath"=>$amazonPath . $file_name);
					@unlink($array_file_data['filepath']);
				}
				$data['AttachmentPaths']		=	$FilesArray;
			} 
		
			
            /*
             * Send to Customer
             * */
            //$status = sendMail('emails.invoices.send',$data);
            $status = 0;
            $body = '';
            $CustomerEmails = $data['EmailTo'];
            foreach($CustomerEmails as $singleemail){
                $singleemail = trim($singleemail);
                if (filter_var($singleemail, FILTER_VALIDATE_EMAIL)) {
					
						$data['EmailTo'] 		= 	$singleemail;
						$data['InvoiceURL']		=   URL::to('/invoice/'.$Invoice->AccountID.'-'.$Invoice->InvoiceID.'/cview?email='.$singleemail);
						$body					=	EmailsTemplates::ReplaceEmail($singleemail,$postdata['Message']);
						$data['Subject']		=	$postdata['Subject'];
                        $InvoiceBillingClass =	 Invoice::GetInvoiceBillingClass($Invoice);

                        $invoicePdfSend = CompanySetting::getKeyVal('invoicePdfSend');
                        if($invoicePdfSend!='Invalid Key' && $invoicePdfSend && !empty($Invoice->PDF) ){
                            $data['AttachmentPaths']= array([
                                "filename"=>pathinfo($Invoice->PDF, PATHINFO_BASENAME),
                                "filepath"=>$Invoice->PDF
                            ]);
                        }
						if(isset($postdata['email_from']) && !empty($postdata['email_from']))
						{
							$data['EmailFrom']	=	$postdata['email_from'];	
						}else{
							$data['EmailFrom']	=	EmailsTemplates::GetEmailTemplateFrom(Invoice::EMAILTEMPLATE);				
						}
						
						$status 				= 	$this->sendInvoiceMail($body,$data,0);
					
					//$body 				=   View::make('emails.invoices.send',compact('data'))->render();  // to store in email log
                }
            }
            if($status['status']==0){
                $status['status'] = 'failure';
            }else{
                $status['status'] = "success";
                if($Invoice->InvoiceStatus != Invoice::PAID && $Invoice->InvoiceStatus != Invoice::PARTIALLY_PAID && $Invoice->InvoiceStatus != Invoice::CANCEL){
                    $Invoice->update(['InvoiceStatus' => Invoice::SEND ]);
                }
                $invoiceloddata = array();
                $invoiceloddata['InvoiceID']= $Invoice->InvoiceID;
                $invoiceloddata['Note']= 'Sent By '.$CreatedBy;
                $invoiceloddata['created_at']= date("Y-m-d H:i:s");
                $invoiceloddata['InvoiceLogStatus']= InVoiceLog::SENT;
                InVoiceLog::insert($invoiceloddata);

                if($Invoice->RecurringInvoiceID > 0){
                    $RecurringInvoiceLogData = array();
                    $RecurringInvoiceLogData['RecurringInvoiceID']= $Invoice->RecurringInvoiceID;
                    $RecurringInvoiceLogData['Note'] = 'Invoice ' . $Invoice->FullInvoiceNumber.' '.RecurringInvoiceLog::$log_status[RecurringInvoiceLog::SENT] .' By '.$CreatedBy;
                    $RecurringInvoiceLogData['created_at']= date("Y-m-d H:i:s");
                    $RecurringInvoiceLogData['RecurringInvoiceLogStatus']= RecurringInvoiceLog::SENT;
                    RecurringInvoiceLog::insert($RecurringInvoiceLogData);
                }

                /*
                    Insert email log in account
                */
				//$data['Message'] = $body;
				$message_id 	=  isset($status['message_id'])?$status['message_id']:"";
                $logData = ['AccountID'=>$Invoice->AccountID,
                    'EmailFrom'=>$data['EmailFrom'],
                    'EmailTo'=>$CustomerEmail,
                    'Subject'=>$data['Subject'],
                    'Message'=>$body,
					"message_id"=>$message_id,
					"AttachmentPaths"=>isset($data["AttachmentPaths"])?$data["AttachmentPaths"]:array()
					];
                email_log($logData);
            }
            /*
             * Send to Staff
             * */
            $Account = Account::find($Invoice->AccountID);
            if(!empty($Account->Owner))
            {
                $AccountManager = User::find($Account->Owner);
                $InvoiceCopy .= ',' . $AccountManager->EmailAddress;
            }
            $sendTo = explode(",",$InvoiceCopy);
            //$sendTo[] = User::get_user_email();
            //$data['Subject'] .= ' ('.$Account->AccountName.')';//Added by Abubakar
            $data['EmailTo'] 		= 	$sendTo;
            $data['InvoiceURL']		= 	URL::to('/invoice/'.$Invoice->InvoiceID.'/invoice_preview');
			$body					=	$postdata['Message'];
			$data['Subject']		=	$postdata['Subject'];

            $invoicePdfSend = CompanySetting::getKeyVal('invoicePdfSend');
            if($invoicePdfSend!='Invalid Key' && $invoicePdfSend && !empty($Invoice->PDF) ){
                $data['AttachmentPaths']= array([
                        "filename"=>pathinfo($Invoice->PDF, PATHINFO_BASENAME),
                    "filepath"=>$Invoice->PDF
                ]);
            }

			if(isset($postdata['email_from']) && !empty($postdata['email_from']))
			{
				$data['EmailFrom']	=	$postdata['email_from'];	
			}else{
				$data['EmailFrom']	=	EmailsTemplates::GetEmailTemplateFrom(Invoice::EMAILTEMPLATE);				
			}
			
            //$StaffStatus = sendMail('emails.invoices.send',$data);
            $StaffStatus = $this->sendInvoiceMail($body,$data,0);
            if($StaffStatus['status']==0){
               $status['message'] .= ', Enable to send email to staff : ' . $StaffStatus['message'];
            }
            return Response::json(array("status" => $status['status'], "message" => "".$status['message']));
        }else{
            return Response::json(["status" => "failure", "message" => "Problem Sending Invoice"]);
        }
    }

    function sendInvoiceMail($view,$data,$type=1){ 
	
	   $status 		= 	array('status' => 0, 'message' => 'Something wrong with sending mail.');
    	if(isset($data['email_from'])){
			$data['EmailFrom'] = $data['email_from'];
		}
	    if(is_array($data['EmailTo']))
		{ 
            $status 			= 	sendMail($view,$data,$type);
        }
		else
		{ 
            if(!empty($data['EmailTo']))
			{
				$data['EmailTo'] 	= 	trim($data['EmailTo']);
				$status 			= 	sendMail($view,$data,0);
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
        $jobdata["created_at"] = date('Y-m-d H:i:s');
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
                //Stock History Calculations Start
                $this->StockHistoryCalculationByInvoiceStatus($data['InvoiceStatus'],$InvoiceIDs);
                //Stock History Calculations End

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
        $Invoice = Invoice::find($id);
        $CompanyID = $Invoice->CompanyID;
        $FilePath =  AmazonS3::preSignedUrl($OutputFilePath,$CompanyID);
        if(file_exists($FilePath)){
            download_file($FilePath);
        }elseif(is_amazon($CompanyID) == true){
            header('Location: '.$FilePath);
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
            $jobdata["created_at"] = date('Y-m-d H:i:s');
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
        $CompanyID = $Invoice->CompanyID;
        $PDFurl = '';
        log::info('CompanyID '.$CompanyID);
        $PDFurl =  AmazonS3::preSignedUrl($Invoice->PDF,$CompanyID);
        log::info('$PDFurl '.$PDFurl);
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="'.basename($PDFurl).'"');
        echo file_get_contents($PDFurl);
        exit;
    }
    public static function download_invoice($InvoiceID){
        $Invoice = Invoice::find($InvoiceID);
        $CompanyID = $Invoice->CompanyID;
        $FilePath =  AmazonS3::preSignedUrl($Invoice->PDF,$CompanyID);
        if(file_exists($FilePath)){
            download_file($FilePath);
        }elseif(is_amazon($CompanyID) == true){
            header('Location: '.$FilePath);
        }
        exit;
    }
    public static function download_attachment($InvoiceID){
        $Invoice = Invoice::find($InvoiceID);
        $CompanyID = $Invoice->CompanyID;
        $FilePath =  AmazonS3::preSignedUrl($Invoice->Attachment,$CompanyID);
        if(file_exists($FilePath)){
            download_file($FilePath);
        }elseif(is_amazon($CompanyID) == true){
            header('Location: '.$FilePath);
        }
        exit;
    }
    public function invoice_payment($id,$type)
    {
        $request = Input::all();
        Payment::multiLang_init();
        $stripeachprofiles=array();
        $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($type);
        $account_inv = explode('-', $id);
        if (isset($account_inv[0]) && intval($account_inv[0]) > 0 && (isset($account_inv[1]) && intval($account_inv[1]) > 0 || isset($request["Amount"]) && intval($request["Amount"]) > 0)) {
            $AccountID = intval($account_inv[0]);
            if(!isset($request["Amount"])){
                $InvoiceID = intval($account_inv[1]);
                $Invoice = Invoice::where(["InvoiceID" => $InvoiceID, "AccountID" => $AccountID])->first();
            }

            $Account = Account::where(['AccountID'=>$AccountID])->first();

            /* stripe ach gateway */
            if(!empty($type) && $PaymentGatewayID==PaymentGateway::StripeACH){
                $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($type);

                $data = AccountPaymentProfile::where(["tblAccountPaymentProfile.CompanyID"=>$Account->CompanyId])
                    ->where(["tblAccountPaymentProfile.AccountID"=>$AccountID])
                    ->where(["tblAccountPaymentProfile.PaymentGatewayID"=>$PaymentGatewayID])
                    ->where(["tblAccountPaymentProfile.Status"=>1])
                    ->get();
                if(!empty($data) && count($data)){
                    foreach($data as $profile){
                        $Options = json_decode($profile->Options);
                        if(!empty($Options->VerifyStatus) && $Options->VerifyStatus=='verified'){
                            $stripedata=array();
                            $stripedata['AccountPaymentProfileID'] = $profile->AccountPaymentProfileID;
                            $stripedata['Title'] = $profile->Title;
                            $stripedata['PaymentMethod'] = $type;
                            $stripedata['isDefault'] = $profile->isDefault;
                            $stripedata['created_at'] = $profile->created_at;
                            $CustomerProfileID = $Options->CustomerProfileID;
                            $verifystatus = $Options->VerifyStatus;
                            $BankAccountID = $Options->BankAccountID;
                            $stripedata['CustomerProfileID'] = $CustomerProfileID;
                            $stripedata['BankAccountID'] = $BankAccountID;
                            $stripedata['verifystatus'] = $verifystatus;
                            $stripeachprofiles[]=$stripedata;
                        }
                    }
                }
            }
            /* stripe ach gateway end */

            if(isset($Invoice) && count($Invoice) > 0){
                $CurrencyCode = Currency::getCurrency($Invoice->CurrencyID);
                $CurrencySymbol =  Currency::getCurrencySymbol($Invoice->CurrencyID);
                return View::make('invoices.invoice_payment', compact('Invoice','CurrencySymbol','Account','CurrencyCode','type','PaymentGatewayID','stripeachprofiles', 'request'));
            }else if (isset($request["Amount"])){
                $CurrencyCode = Currency::getCurrency($Account->CurrencyID);
                $CurrencySymbol =  Currency::getCurrencySymbol($Account->CurrencyId);
                $request["InvoiceID"] = (int)Invoice::where(array('FullInvoiceNumber'=>$request['InvoiceNo'],'AccountID'=>$Account->AccountID))->pluck('InvoiceID');
                return View::make('invoices.invoice_payment', compact('CurrencySymbol','Account','CurrencyCode','type','PaymentGatewayID','stripeachprofiles', 'request'));
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

        $payment_log = Payment::getPaymentByInvoice($Invoice->InvoiceID);

        if(!empty($Invoice)) {
            //$data['GrandTotal'] = $Invoice->GrandTotal;
            $Invoice = Invoice::find($Invoice->InvoiceID);
            $data['GrandTotal'] = $payment_log['final_payment'];
            $data['InvoiceNumber'] = $Invoice->FullInvoiceNumber;
            $authorize = new AuthorizeNet();
            $response = $authorize->pay_invoice($data);
            $Notes = '';
            if($response->response_code == 1) {
                $Notes = 'AuthorizeNet transaction_id ' . $response->transaction_id;
            }else{
                $Notes = isset($response->response->xml->messages->message->text) && $response->response->xml->messages->message->text != '' ? $response->response->xml->messages->message->text : $response->response_reason_text ;
            }
            if ($response->approved) {
                $paymentdata = array();
                $paymentdata['CompanyID'] = $Invoice->CompanyID;
                $paymentdata['AccountID'] = $Invoice->AccountID;
                $paymentdata['InvoiceNo'] = $Invoice->FullInvoiceNumber;
                $paymentdata['InvoiceID'] = (int)$Invoice->InvoiceID;
                $paymentdata['PaymentDate'] = date('Y-m-d H:i:s');
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
                $transactiondata['Amount'] = floatval($response->amount);
                $transactiondata['Status'] = TransactionLog::SUCCESS;
                $transactiondata['created_at'] = date('Y-m-d H:i:s');
                $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                $transactiondata['CreatedBy'] = 'customer';
                $transactiondata['ModifyBy'] = 'customer';
                $transactiondata['Response'] = json_encode($response);
                TransactionLog::insert($transactiondata);
                $Invoice->update(array('InvoiceStatus' => Invoice::PAID));

                $paymentdata['EmailTemplate'] 		= 	EmailTemplate::getSystemEmailTemplate($Invoice->CompanyId, Estimate::InvoicePaidNotificationTemplate, $account->LanguageID);
                $paymentdata['CompanyName'] 		= 	Company::getName($paymentdata['CompanyID']);
                $paymentdata['Invoice'] = $Invoice;
                Notification::sendEmailNotification(Notification::InvoicePaidByCustomer,$paymentdata);
                return Response::json(array("status" => "success", "message" => "Invoice paid successfully"));
            }else{
                $transactiondata = array();
                $transactiondata['CompanyID'] = $Invoice->CompanyID;
                $transactiondata['AccountID'] = $AccountID;
                $transactiondata['InvoiceID'] = $Invoice->InvoiceID;
                $transactiondata['Transaction'] = $response->transaction_id;
                $transactiondata['Notes'] = $Notes;
                $transactiondata['Amount'] = floatval(0);
                $transactiondata['Status'] = TransactionLog::FAILED;
                $transactiondata['created_at'] = date('Y-m-d H:i:s');
                $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                $transactiondata['CreatedBy'] = 'customer';
                $transactiondata['ModifyBy'] = 'customer';
                $transactiondata['Response'] = json_encode($response);
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
        if (isset($account_inv[0]) && intval($account_inv[0]) > 0 && isset($account_inv[1]) && intval($account_inv[1]) >= 0) {
            $AccountID = intval($account_inv[0]);
            $InvoiceID = intval($account_inv[1]);
            if($InvoiceID==0){
                return View::make('invoices.invoice_thanks');
            }
            $Invoice = Invoice::where(["InvoiceID" => $InvoiceID, "AccountID" => $AccountID])->first();
            if (count($Invoice) > 0) {
                return View::make('invoices.invoice_thanks', compact('Invoice'));
            }
        }
    }
    public function api_invoice_thanks($id){
        $data = Input::all();

        log::info('paypal log');
        log::info(print_r($data,true));
        /*
        $data['mc_gross'] = '10.00';
        $data['protection_eligibility'] = 'Eligible';
        $data['address_status'] = 'confirmed';
        $data['payer_id'] = 'RD3R6ZAKRY9FE';
        $data['tax'] = '0.00';
        $data['address_street'] = '1 Main St';
        $data['payment_date'] = '05:41:07 Aug 06, 2018 PDT';
        $data['payment_status'] = 'Completed';
        $data['charset'] = 'utf-8';
        $data['address_zip'] = '95131';
        $data['first_name'] = 'Test';
        $data['mc_fee'] = '0.59';
        $data['address_country_code'] = 'US';
        $data['address_name'] = 'Test User';
        $data['notify_version'] = '3.9';
        $data['custom'] = '5';
        $data['payer_status'] = 'verified';
        $data['business'] = 'vishal.jagani-facilitator@code-desk.com';
        $data['address_country'] = 'United States';
        $data['address_city'] = 'San Jose';
        $data['quantity'] = '1';
        $data['payer_email'] = 'devens_1212647640_per@yahoo.com';
        $data['verify_sign'] = 'A3a-JHspcfFmwpElPB6mqlkSmHIUAnb4hxngI8JtHz9XHxXJIbHnqRHR';
        $data['txn_id'] = '9GD55899ER875904A';
        $data['payment_type'] = 'instant';
        $data['last_name'] = 'User';
        $data['address_state'] = 'CA';
        $data['receiver_email'] = 'vishal.jagani-facilitator@code-desk.com';
        $data['payment_fee'] = '0.59';
        $data['receiver_id'] = 'P3KJJHVM8JVNL';
        $data['txn_type'] = 'web_accept';
        $data['item_name'] = 'Wavetel Ltd dev Local test account API Invoice';
        $data['mc_currency'] = 'USD';
        $data['item_number'] = '';
        $data['residence_country'] = 'US';
        $data['test_ipn'] = '1';
        $data['handling_amount'] = '0.00';
        $data['transaction_subject'] = '';
        $data['payment_gross'] = '10.00';
        $data['shipping'] = '0.00';
        $data['auth'] = 'AEh45IkItw..cmGcPtOfY0a8Vrz0c55nwDg7b5WRTwuMw-NO-pp.BB8A6SmBkh7wRlyKbZvKEUD6QjuLVJIipFg'; */


        $PaymentResponse =array();
        if(isset($data["Success"])){
            $PaymentResponse['PaymentMethod'] = $data["PaymentMethod"];
            $PaymentResponse['transaction_notes'] = 'PayPal transaction_id '.$data["Transaction"];
            $PaymentResponse['Amount'] = floatval($data["Amount"]);
            $PaymentResponse['Transaction'] = $data["Transaction"];
            $PaymentResponse['Response'] = $data["PaymentGatewayResponse"];
            $PaymentResponse['status'] = 'success';
        }elseif(!empty($data['tx'])){
            $PaymentResponse['PaymentMethod'] = 'Paypal';
            $PaymentResponse['transaction_notes'] = 'PayPal transaction_id '.$data['tx'];
            $PaymentResponse['Amount'] = floatval($data["amt"]);
            $PaymentResponse['Transaction'] = $data["tx"];
            $PaymentResponse['Response'] = '';
            $PaymentResponse['status'] = 'success';
        }

        $Alldata = array();
        $Alldata['PaymentResponse'] = json_encode($PaymentResponse);
        $Alldata['APIData'] =  Session::get('APIEncodeData');
        //log::info(print_r($PaymentResponse,true));
        //log::info(print_r($Alldata,true));
        /*
        if($PaymentResponse['status']=='failed'){
            if(!empty($PaymentResponse['transaction_notes'])){
                $message = $PaymentResponse['transaction_notes'];
            }else{
                $message = empty($PaymentResponse['message']) ? '' :$PaymentResponse['message'];
            }
            return Response::json(["status"=>"failed","message" => $message, "data"=>$PaymentResponse]);
        }else{
            return Response::json(["status"=>"success","message" => "Create Payment Successfully", "data"=>json_encode($Alldata)]);
        }*/


        log::info(print_r($Alldata,true));
        log::info('api_invoice_thanks');
        $RegistarionApiLogID = Session::get('RegistarionApiLogID');
        log::info('R LogID '.$RegistarionApiLogID);
        $RegistarionApiLogUpdate = array();
        if(!empty($RegistarionApiLogID)){
            $RegistarionApiLogUpdate['PaymentAmount'] = empty($PaymentResponse['Amount']) ? 0 : $PaymentResponse['Amount'];
            $RegistarionApiLogUpdate['PaymentResponse'] = json_encode($PaymentResponse);
            $RegistarionApiLogUpdate['PaymentStatus'] = 'success';
            DB::table('tblRegistarionApiLog')->where('RegistarionApiLogID', $RegistarionApiLogID)->update($RegistarionApiLogUpdate);
        }
        //log::info(json_decode($data['data'],true));
        //$customdata = json_encode(json_decode($Alldata,true));
        $customdata = json_encode($Alldata);
        //$customdata=$data['data'];
        return View::make('neonregistartion.api_invoice_creditcard_thanks', compact('data','customdata'));
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
            $jobdata["created_at"] = date('Y-m-d H:i:s');
            $jobdata["updated_at"] = date('Y-m-d H:i:s');
            $JobID = Job::insertGetId($jobdata);
            /*if(getenv('APP_OS') == 'Linux'){
                pclose(popen(CompanyConfiguration::get("PHPExePath") . " " . CompanyConfiguration::get("RMArtisanFileLocation") . "  invoicegenerator " . $CompanyID . " $CronJobID $UserID ". " &", "r"));
            }else{
                pclose(popen("start /B " . CompanyConfiguration::get("PHPExePath") . " " . CompanyConfiguration::get("RMArtisanFileLocation") . "  invoicegenerator " . $CompanyID . " $CronJobID $UserID ", "r"));
            }*/
            if($JobID>0) {
                return Response::json(array("status" => "success", "message" => "Invoice Generation Job Added in queue to process.You will be notified once job is completed. "));
            }
        }
        return Response::json(array("status" => "error", "message" => "Please Setup Invoice Generator in CronJob"));

    }
    public function ajax_getEmailTemplate($id){
      //  $filter =array('Type'=>EmailTemplate::INVOICE_TEMPLATE);
		$filter =array('StaticType'=>EmailTemplate::DYNAMICTEMPLATE);
        if($id == 1){
          $filter['UserID'] =   User::get_userID();
        }
        return EmailTemplate::getTemplateArray($filter);
    }

    public function getInvoicesIdByCriteria($data){
        $companyID = User::get_companyID();
        $criteria = json_decode($data['criteria'],true);
        $criteria['Overdue'] = $criteria['Overdue']== 'true'?1:0;
        $criteria['InvoiceStatus'] = is_array($criteria['InvoiceStatus'])?implode(',',$criteria['InvoiceStatus']):$criteria['InvoiceStatus'];

        // Account Manager Condition
        $userID = 0;
        if(User::is('AccountManager')) { // Account Manager
            $userID = User::get_userID();
        }

        $query = "call prc_getInvoice (".$companyID.",'".$criteria['AccountID']."','".$criteria['InvoiceNumber']."','".$criteria['IssueDateStart']."','".$criteria['IssueDateEnd']."','".$criteria['InvoiceType']."','".$criteria['InvoiceStatus']."',".$criteria['Overdue'].",'' ,'','','','".$criteria['CurrencyID']."' ";

        if(!empty($criteria['zerovalueinvoice'])){
            $query = $query.',2,0,1';
        }else{
            $query = $query.',2,0,0';
        }
        $query .= ",'',".$userID.",'')";
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
        // Account Manager Condition
        $userID = 0;
        if(User::is('AccountManager')) { // Account Manager
            $userID = User::get_userID();
        }
        $companyID = User::get_companyID();
        if(!empty($data['InvoiceIDs'])){
            $query = "call prc_getInvoice (".$companyID.",0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',0,'',0,1 ,".count($data['InvoiceIDs']).",'','',''";
            if(isset($data['MarkPaid']) && $data['MarkPaid'] == 1){
                $query = $query.',0,2,0';
            }else{
                $query = $query.',0,1,0';
            }
            if(!empty($data['InvoiceIDs'])){
                $query = $query.",'".$data['InvoiceIDs']."',".$userID.")";
            }
			else			
            $query .= ",'')";
            $excel_data  = DB::connection('sqlsrv2')->select($query);
            $excel_data = json_decode(json_encode($excel_data),true);

            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/InvoiceSageExport.csv';
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
            $criteria['InvoiceStatus'] = is_array($criteria['InvoiceStatus'])?implode(',',$criteria['InvoiceStatus']):$criteria['InvoiceStatus'];
            $criteria['Overdue'] = $criteria['Overdue']== 'true'?1:0;
            $query = "call prc_getInvoice (".$companyID.",'".intval($criteria['AccountID'])."','".$criteria['InvoiceNumber']."','".$criteria['IssueDateStart']."','".$criteria['IssueDateEnd']."','".$criteria['InvoiceType']."','".$criteria['InvoiceStatus']."',".$criteria['Overdue'].",'' ,'','','',' ".$criteria['CurrencyID']." '";
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
            $query .= ",'',".$userID.",'')";
            $excel_data  = DB::connection('sqlsrv2')->select($query);
            $excel_data = json_decode(json_encode($excel_data),true);

            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/InvoiceSageExport.csv';
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
        $result = array();
        $CompanyID = User::get_companyID();

        /*if(!isset($data["InvoiceID"]) && isset($data["InvoiceNumber"]) ){
            $CompanyID = User::get_companyID();
            $Invoice = Invoice::where(["CompanyID"=>$CompanyID, "InvoiceNumber" => trim($data['InvoiceNumber'])])->select(["InvoiceID","GrandTotal"])->first();

            $data["InvoiceID"] = $Invoice->InvoiceID;

            $result["GrandTotal"] = $Invoice->GrandTotal;

        }*/
        $InvoiceNumber = Invoice::where(["InvoiceID" => $data['InvoiceID']])->pluck("InvoiceNumber");

        $InvoiceDetail = InvoiceDetail::where(["InvoiceID" => $data['InvoiceID']])->select(["InvoiceDetailID","StartDate", "EndDate","Description", "TotalMinutes"])->first();

        $result["InvoiceID"] = $data["InvoiceID"];
        $result['InvoiceDetailID'] = $InvoiceDetail->InvoiceDetailID;

        $StartTime =  explode(' ',$InvoiceDetail->StartDate);
        $EndTime =  explode(' ',$InvoiceDetail->EndDate);

        $result['StartDate'] = $StartTime[0];
        $result['EndDate'] = $EndTime[0];
        $result['Description'] = $InvoiceDetail->Description;
        $result['StartTime'] = $StartTime[1];
        $result['EndTime'] = $EndTime[1];
        $result['TotalMinutes'] = $InvoiceDetail->TotalMinutes;

        //$Dispute = Dispute::where(["InvoiceID"=>$data['InvoiceID'],"Status"=>Dispute::PENDING])->select(["DisputeID","InvoiceID","DisputeTotal", "DisputeDifference", "DisputeDifferencePer", "DisputeMinutes","MinutesDifference", "MinutesDifferencePer"])->first();
        $Dispute = Dispute::where(["CompanyID"=>$CompanyID,  "InvoiceNo"=>$InvoiceNumber])->select(["DisputeID","DisputeAmount"])->first();

        if(isset($Dispute->DisputeID)){

            $result["DisputeID"] = $Dispute->DisputeID;
            $result["DisputeAmount"] = $Dispute->DisputeAmount;

            /*$result["DisputeTotal"] = $Dispute->DisputeTotal;
            $result["DisputeDifference"] = $Dispute->DisputeDifference;
            $result["DisputeDifferencePer"] = $Dispute->DisputeDifferencePer;
            $result["DisputeMinutes"] = $Dispute->DisputeMinutes;
            $result["MinutesDifference"] = $Dispute->MinutesDifference;
            $result["MinutesDifferencePer"] = $Dispute->MinutesDifferencePer;*/
        }
        return Response::json($result);

    }

    public function invoice_in_reconcile()
    {
        $data = Input::all();
        $companyID =  User::get_companyID();
       
        $rules = array(
            'AccountID' => 'required',
            'StartDate' => 'required',
            'EndDate' => 'required',
            'GrandTotal'=>'required|numeric',
          //  'TotalMinutes'=>'required|numeric',
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrvcdr');


        $validator = Validator::make($data, $rules);

        $validator->setPresenceVerifier($verifier);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if($data['StartDate'] > $data['EndDate']){
            return Response::json(array("status" => "failed", "message" => "Dates are invalid"));
        }

        $accountID = $data['AccountID'];
        $StartDate = $data['StartDate'].' '.$data['StartTime'];
        $EndDate = $data['EndDate'].' '.$data['EndTime'];

        $output = Dispute::reconcile($companyID,$accountID,$StartDate,$EndDate,$data["GrandTotal"],$data["TotalMinutes"]);
        $message = '';
        if(isset($data["DisputeID"]) && $data["DisputeID"] > 0 ) {
            $data['CompanyID'] = $companyID;
            $data['InvoiceType'] = Invoice::RECEIVED;
            $status = Dispute::sendDisputeEmailCustomer($data);
            $message = $status['message'];
            $output["DisputeID"]  = $data["DisputeID"];
        }

        return Response::json( array_merge($output, array("status" => "success", "message" => $message  )));
    }

    /** Paypal ipn url which will be triggered from paypal with payment status and response
     * @param $id
     * @return mixed
     */
    public function paypal_ipn($id)
    {

        //@TODO: need to merge all payment gateway payment insert entry.


        $account_inv = explode('-', $id);
        if (isset($account_inv[0]) && intval($account_inv[0]) > 0 && isset($account_inv[1]) && intval($account_inv[1]) >= 0) {
            $AccountID = intval($account_inv[0]);
            $InvoiceID = intval($account_inv[1]);

            if($InvoiceID!=0){
                $Invoice = Invoice::find($InvoiceID);
                $CompanyID = $Invoice->CompanyID;
            }else{
                if(isset($account_inv[2])){
                    $Invoice = Invoice::where(array('FullInvoiceNumber'=>$account_inv[2],'AccountID'=>$AccountID))->first();
                    if(!empty($Invoice) && count($Invoice)){
                        $CompanyID = $Invoice->CompanyID;
                        $InvoiceID = $Invoice->InvoiceID;
                    }
                }
            }
            if(!isset($CompanyID)){
                $account = Account::find($AccountID);
                $CompanyID = $account->CompanyId;
            }

            $paypal = new PaypalIpn($CompanyID);

            $data["Notes"]                  = $paypal->get_note();
            $data["Success"]                = $paypal->success();
            $data["PaymentMethod"]          = $paypal->method;
            $data["Amount"]                 = $paypal->get_response_var('mc_gross');
            $data["Transaction"]            = $paypal->get_response_var('txn_id');
            $data["PaymentGatewayResponse"] = $paypal->get_full_response();

            return $this->post_payment_process($AccountID,$InvoiceID,$data);
        }
    }

    /** Paypal ipn url which will be triggered from paypal with payment status and response
     * @param $id
     * @return mixed
     */
    public function sagepay_ipn()
    {

        //@TODO: need to merge all payment gateway payment insert entry.
        $CompanyID = 0; // need to change if possible

        //https://sagepay.co.za/integration/sage-pay-integration-documents/pay-now-gateway-technical-guide/
        $SagePay = new SagePay($CompanyID);
        $AccountnInvoice = $SagePay->getAccountInvoiceID();

        if ($AccountnInvoice != null) { // Extra2 = m5 (hidden field of sagepay form).

            $AccountID = intval($AccountnInvoice["AccountID"]);
            $InvoiceID = intval($AccountnInvoice["InvoiceID"]);

            $data["Notes"]                  = $SagePay->get_note();
            $data["Success"]                = $SagePay->success();
            $data["PaymentMethod"]          = $SagePay->method;
            $data["Amount"]                 = $SagePay->get_response_var('Amount');
            $data["Transaction"]            = $SagePay->get_response_var('RequestTrace');
            $data["PaymentGatewayResponse"] = $SagePay->get_full_response();

            return $this->post_payment_process($AccountID,$InvoiceID,$data);
        }
    }

    /**
     * Once payment is done call post payment process
     * to add payment and transaction entries.
     */
    public function post_payment_process($AccountID,$InvoiceID,$data){

        $Invoice = Invoice::where(["InvoiceID" => $InvoiceID, "AccountID" => $AccountID])->first();
        $transactionResponse = array();

        $transactionResponse['CreatedBy'] = 'Customer';
        $transactionResponse['InvoiceID'] = $InvoiceID;
        $transactionResponse['AccountID'] = $AccountID;
        $transactionResponse['transaction_notes'] = $data["Notes"];
        $transactionResponse['Response'] = $data["PaymentGatewayResponse"];
        $transactionResponse['Transaction'] = $data["Transaction"];
        $transactionResponse['PaymentMethod'] = $data["PaymentMethod"];

        if (isset($data["Success"]) && (count($Invoice) > 0) || intval($InvoiceID)==0 ) {

            $PaymentCount = Payment::where('Notes',$data["Notes"])->count();//@TODO: need to check this
            if($PaymentCount == 0) {
                $transactionResponse['Amount'] = $data["Amount"];
                Payment::paymentSuccess($transactionResponse);

                return Response::json(array("status" => "success", "message" => "Invoice paid successfully"));
            }else{
                \Illuminate\Support\Facades\Log::info("Invoice Already paid successfully.");
                return Response::json(array("status" => "success", "message" => "Invoice Already paid successfully"));
            }
        } else {
//            $transactionResponse['Amount'] = floatval($Invoice->RemaingAmount);
            Payment::paymentFail($transactionResponse);
            //$paypal->log();
            return Response::json(array("status" => "failed", "message" => "Failed to payment."));
        }

    }

    public function invoice_quickbookpost(){
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
        //$data['type'] = 'journal';
        if($data['type'] == 'journal'){
            $msgtype = 'Journal';
        }
        else{
            $msgtype = 'Invoice';
        }
        $CompanyID = User::get_companyID();
        $InvoiceIDs =array_filter(explode(',',$data['InvoiceIDs']),'intval');
        if (is_array($InvoiceIDs) && count($InvoiceIDs)) {
            $jobType = JobType::where(["Code" => 'QIP'])->first(["JobTypeID", "Title"]);
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
                return json_encode(["status" => "success", "message" => $msgtype." Post in quickbook Job Added in queue to process.You will be notified once job is completed."]);
            }else{
                return json_encode(array("status" => "failed", "message" => "Problem Creating ".$msgtype." Post in Quickbook ."));
            }
        }

    }
    public function invoice_quickbookexport(){
        $data = Input::all();
        $InvoiceIDs = $data['InvoiceIDs'];
        //$data['type'] = 'journal';
        $CompanyID = User::get_companyID();
        //$InvoiceIDs =array_filter(explode(',',$data['InvoiceIDs']),'intval');

        Log::useFiles(storage_path() . '/logs/quickbook_invoiceexport-' . $CompanyID . '-' . date('Y-m-d') . '.log');

        try {
            if (isset($InvoiceIDs)) {

                $InvoiceIDs = explode(',',$InvoiceIDs);
                $InvoiceAccounts = array();
                $InvoiceItems = array();
                if(count($InvoiceIDs) > 0){
                    foreach ($InvoiceIDs as $InvoiceID) {
                        $Invoice = Invoice::find($InvoiceID);
                        $AccountID = $Invoice->AccountID;
                        $InvoiceAccounts['AccountID'][] = $AccountID;
                    }
                }

                $invoiceOptions = array();
                $invoiceOptions['CompanyID'] = $CompanyID;
                $invoiceOptions['Invoices'] = $InvoiceIDs;

                $InvoiceObject = new Invoice();
                $InvoiceObject->ExportInvoices($invoiceOptions);
            }
        }
        catch (\Exception $e) {

            Log::info(' ========================== Exception occured =============================');
            Log::error($e);
            echo "<pre>";print_r($e);
            Log::info(' ========================== Exception updated in job and email sent =============================');

        }
    }

    public function journal_quickbookdexport(){
        $data = Input::all();
        $InvoiceIDs = $data['InvoiceIDs'];
        //$data['type'] = 'journal';
        $CompanyID = User::get_companyID();
        //$InvoiceIDs =array_filter(explode(',',$data['InvoiceIDs']),'intval');

        Log::useFiles(storage_path() . '/logs/qbdesktop_journalexport-' . $CompanyID . '-' . date('Y-m-d') . '.log');

        try {
            if (isset($InvoiceIDs)) {

                $InvoiceIDs = explode(',',$InvoiceIDs);

                $invoiceOptions = array();
                $invoiceOptions['CompanyID'] = $CompanyID;
                $invoiceOptions['Invoices'] = $InvoiceIDs;

                $InvoiceObject = new Invoice();
                $Invoices = $InvoiceObject->ExportJournals($invoiceOptions);
                if($Invoices['status'] == 'success')
                {
                    return Response::json(array("status" => "success", "message" => $Invoices['msg'],"redirect" => $Invoices['redirect']));
                }
                else{
                    return Response::json(array("status" => "failed", "message" => $Invoices['msg']));
                }
            }
        }
        catch (\Exception $e) {

            Log::info(' ========================== Exception occured =============================');
            Log::error($e);
            Log::info(' ========================== Exception updated in Journal Export =============================');
            return Response::json(array("status" => "failed", "message" => $e));

        }
    }

    public function journal_quickbookdexport_download(){
        $file = $_REQUEST['file'];
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            exit;
        }
    }

    public function paypal_cancel($id){

        echo "<center>Opps. Payment Canceled, Please try again.</center>";

    }

    public function stripe_payment(){
        $data = Input::all();
        $InvoiceID = $data['InvoiceID'];
        $AccountID = $data['AccountID'];
        $rules = array(
            'CardNumber' => 'required|digits_between:13,19',
            'ExpirationMonth' => 'required',
            'ExpirationYear' => 'required',
            'NameOnCard' => 'required',
            'CVVNumber' => 'required | numeric | digits_between:3,4',
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
        $data['CurrencyCode'] = Currency::getCurrency($Invoice->CurrencyID);
        if(empty($data['CurrencyCode'])){
            return Response::json(array("status" => "failed", "message" => "No invoice currency available"));
        }

        if(!empty($Invoice)) {

        $Invoice = Invoice::find($Invoice->InvoiceID);

        $payment_log = Payment::getPaymentByInvoice($Invoice->InvoiceID);

        $data['Total'] = $payment_log['final_payment'];
        $data['FullInvoiceNumber'] = $Invoice->FullInvoiceNumber;

        $stripedata = array();
        $stripedata['number'] = $data['CardNumber'];
        $stripedata['exp_month'] = $data['ExpirationMonth'];
        $stripedata['cvc'] = $data['CVVNumber'];
        $stripedata['exp_year'] = $data['ExpirationYear'];
        $stripedata['name'] = $data['NameOnCard'];

        $stripedata['amount'] = $data['Total'];
        $stripedata['currency'] = strtolower($data['CurrencyCode']);
        $stripedata['description'] = $data['FullInvoiceNumber'].' (Invoice) Payment';

        $stripepayment = new StripeBilling($Invoice->CompanyID);

        if(empty($stripepayment->status)){
            return Response::json(array("status" => "failed", "message" => "Stripe Payment not setup correctly"));
        }
        $StripeResponse = array();

        $StripeResponse = $stripepayment->create_charge($stripedata);

        if ($StripeResponse['status'] == 'Success') {

            // Add Payment
            $paymentdata = array();
            $paymentdata['CompanyID'] = $Invoice->CompanyID;
            $paymentdata['AccountID'] = $AccountID;
            $paymentdata['InvoiceNo'] = $Invoice->FullInvoiceNumber;
            $paymentdata['InvoiceID'] = (int)$Invoice->InvoiceID;
            $paymentdata['PaymentDate'] = date('Y-m-d H:i:s');
            $paymentdata['PaymentMethod'] = 'Stripe';
            $paymentdata['CurrencyID'] = $Invoice->CurrencyID;
            $paymentdata['PaymentType'] = 'Payment In';
            $paymentdata['Notes'] = $StripeResponse['note'];
            $paymentdata['Amount'] = $StripeResponse['amount'];
            $paymentdata['Status'] = 'Approved';
            $paymentdata['CreatedBy'] = 'Customer';
            $paymentdata['ModifyBy'] = 'Customer';
            $paymentdata['created_at'] = date('Y-m-d H:i:s');
            $paymentdata['updated_at'] = date('Y-m-d H:i:s');
            Payment::insert($paymentdata);

            \Illuminate\Support\Facades\Log::info("Payment done.");
            \Illuminate\Support\Facades\Log::info($paymentdata);

            // Add transaction
            $transactiondata = array();
            $transactiondata['CompanyID'] = $Invoice->CompanyID;
            $transactiondata['AccountID'] = $AccountID;
            $transactiondata['InvoiceID'] = (int)$Invoice->InvoiceID;
            $transactiondata['Transaction'] = $StripeResponse['id'];
            $transactiondata['Notes'] = $StripeResponse['note'];
            $transactiondata['Amount'] = $StripeResponse['amount'];
            $transactiondata['Status'] = TransactionLog::SUCCESS;
            $transactiondata['created_at'] = date('Y-m-d H:i:s');
            $transactiondata['updated_at'] = date('Y-m-d H:i:s');
            $transactiondata['CreatedBy'] = 'Customer';
            $transactiondata['ModifyBy'] = 'Customer';
            $transactiondata['Response'] = json_encode($StripeResponse['response']);

            TransactionLog::insert($transactiondata);


            $account = Account::find($AccountID);

            $Invoice->update(array('InvoiceStatus' => Invoice::PAID));
            $paymentdata['EmailTemplate'] 		= 	EmailTemplate::getSystemEmailTemplate($Invoice->CompanyId, Estimate::InvoicePaidNotificationTemplate, $account->LanguageID);
            $paymentdata['CompanyName'] 		= 	Company::getName($paymentdata['CompanyID']);
            $paymentdata['Invoice'] = $Invoice;
            Notification::sendEmailNotification(Notification::InvoicePaidByCustomer,$paymentdata);
            \Illuminate\Support\Facades\Log::info("Transaction done.");
            \Illuminate\Support\Facades\Log::info($transactiondata);

            return Response::json(array("status" => "success", "message" => "Invoice paid successfully"));

        } else {

            $transactiondata = array();
            $transactiondata['CompanyID'] = $Invoice->CompanyID;
            $transactiondata['AccountID'] = $AccountID;
            $transactiondata['InvoiceID'] = $Invoice->InvoiceID;
            $transactiondata['Transaction'] = '';
            $transactiondata['Notes'] = $StripeResponse['error'];
            $transactiondata['Amount'] = floatval(0);
            $transactiondata['Status'] = TransactionLog::FAILED;
            $transactiondata['created_at'] = date('Y-m-d H:i:s');
            $transactiondata['updated_at'] = date('Y-m-d H:i:s');
            $transactiondata['CreatedBy'] = 'customer';
            $transactiondata['ModifyBy'] = 'customer';
            $transactiondata['ModifyBy'] = 'customer';
            TransactionLog::insert($transactiondata);

            return Response::json(array("status" => "failed", "message" => $StripeResponse['error']));
        }

        }else{
            return Response::json(array("status" => "failed", "message" => "Invoice not found"));
        }
    }


    public function stripeach_payment(){
        $data = Input::all();

        $InvoiceID = $data['InvoiceID'];
        $AccountID = $data['AccountID'];

        $Invoice = Invoice::where('InvoiceStatus','!=',Invoice::PAID)->where(["InvoiceID" => $InvoiceID, "AccountID" => $AccountID])->first();
        $data['CurrencyCode'] = Currency::getCurrency($Invoice->CurrencyID);
        if(empty($data['CurrencyCode'])){
            return Response::json(array("status" => "failed", "message" => "No invoice currency available"));
        }

        if(!empty($Invoice)) {

            $Invoice = Invoice::find($Invoice->InvoiceID);

            $payment_log = Payment::getPaymentByInvoice($Invoice->InvoiceID);

            $data['Total'] = $payment_log['final_payment'];
            $data['FullInvoiceNumber'] = $Invoice->FullInvoiceNumber;

            $stripedata = array();

            $stripedata['amount'] = $data['Total'];
            $stripedata['currency'] = strtolower($data['CurrencyCode']);
            $stripedata['description'] = $data['FullInvoiceNumber'].' (Invoice) Payment';
            $stripedata['customerid'] = $data['CustomerProfileID'];

            $stripepayment = new StripeACH();

            if(empty($stripepayment->status)){
                return Response::json(array("status" => "failed", "message" => "Stripe ACH Payment not setup correctly"));
            }
            $StripeResponse = array();

            $StripeResponse = $stripepayment->createchargebycustomer($stripedata);

            if ($StripeResponse['status'] == 'Success') {

                // Add Payment
                $paymentdata = array();
                $paymentdata['CompanyID'] = $Invoice->CompanyID;
                $paymentdata['AccountID'] = $AccountID;
                $paymentdata['InvoiceNo'] = $Invoice->FullInvoiceNumber;
                $paymentdata['InvoiceID'] = (int)$Invoice->InvoiceID;
                $paymentdata['PaymentDate'] = date('Y-m-d H:i:s');
                $paymentdata['PaymentMethod'] = 'StripeACH';
                $paymentdata['CurrencyID'] = $Invoice->CurrencyID;
                $paymentdata['PaymentType'] = 'Payment In';
                $paymentdata['Notes'] = $StripeResponse['note'];
                $paymentdata['Amount'] = $StripeResponse['amount'];
                $paymentdata['Status'] = 'Approved';
                $paymentdata['CreatedBy'] = 'Customer';
                $paymentdata['ModifyBy'] = 'Customer';
                $paymentdata['created_at'] = date('Y-m-d H:i:s');
                $paymentdata['updated_at'] = date('Y-m-d H:i:s');
                Payment::insert($paymentdata);

                \Illuminate\Support\Facades\Log::info("Payment done.");
                \Illuminate\Support\Facades\Log::info($paymentdata);

                // Add transaction
                $transactiondata = array();
                $transactiondata['CompanyID'] = $Invoice->CompanyID;
                $transactiondata['AccountID'] = $AccountID;
                $transactiondata['InvoiceID'] = (int)$Invoice->InvoiceID;
                $transactiondata['Transaction'] = $StripeResponse['id'];
                $transactiondata['Notes'] = $StripeResponse['note'];
                $transactiondata['Amount'] = $StripeResponse['amount'];
                $transactiondata['Status'] = TransactionLog::SUCCESS;
                $transactiondata['created_at'] = date('Y-m-d H:i:s');
                $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                $transactiondata['CreatedBy'] = 'Customer';
                $transactiondata['ModifyBy'] = 'Customer';
                $transactiondata['Response'] = json_encode($StripeResponse['response']);

                TransactionLog::insert($transactiondata);
                $account = Account::find($AccountID);
                $Invoice->update(array('InvoiceStatus' => Invoice::PAID));
                $paymentdata['EmailTemplate'] 		= 	EmailTemplate::getSystemEmailTemplate($Invoice->CompanyId, Estimate::InvoicePaidNotificationTemplate, $account->LanguageID);
                $paymentdata['CompanyName'] 		= 	Company::getName($paymentdata['CompanyID']);
                $paymentdata['Invoice'] = $Invoice;
                Notification::sendEmailNotification(Notification::InvoicePaidByCustomer,$paymentdata);
                \Illuminate\Support\Facades\Log::info("Transaction done.");
                \Illuminate\Support\Facades\Log::info($transactiondata);

                return Response::json(array("status" => "success", "message" => "Invoice paid successfully"));

            } else {

                $transactiondata = array();
                $transactiondata['CompanyID'] = $Invoice->CompanyID;
                $transactiondata['AccountID'] = $AccountID;
                $transactiondata['InvoiceID'] = $Invoice->InvoiceID;
                $transactiondata['Transaction'] = '';
                $transactiondata['Notes'] = $StripeResponse['error'];
                $transactiondata['Amount'] = floatval(0);
                $transactiondata['Status'] = TransactionLog::FAILED;
                $transactiondata['created_at'] = date('Y-m-d H:i:s');
                $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                $transactiondata['CreatedBy'] = 'customer';
                $transactiondata['ModifyBy'] = 'customer';
                $transactiondata['ModifyBy'] = 'customer';
                TransactionLog::insert($transactiondata);

                return Response::json(array("status" => "failed", "message" => $StripeResponse['error']));
            }

        }else{
            return Response::json(array("status" => "failed", "message" => "Invoice not found"));
        }
    }


    public function get_unbill_report($id){
        $AccountBilling = AccountBilling::getBilling($id, 0);
        $account = Account::find($id);
        $lastInvoicePeriod = Invoice::join('tblInvoiceDetail','tblInvoiceDetail.InvoiceID','=','tblInvoice.InvoiceID')
            ->where(array('AccountID'=>$account->AccountID,'InvoiceType'=>Invoice::INVOICE_OUT,'ProductType'=>Product::USAGE))
            ->orderBy('IssueDate','DESC')->limit(1)
            ->first(['StartDate','EndDate']);
        $CustomerLastInvoiceDate = Account::getCustomerLastInvoiceDate($AccountBilling,$account);
        $VendorLastInvoiceDate = Account::getVendorLastInvoiceDate($AccountBilling,$account);
        $CurrencySymbol = Currency::getCurrencySymbol($account->CurrencyId);
        $CustomerEndDate = '';
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $CustomerNextBilling = $VendorNextBilling = array();
        $StartDate = $CustomerLastInvoiceDate;
        if (!empty($AccountBilling) && $AccountBilling->BillingCycleType != 'manual') {
            $EndDate = $CustomerEndDate = next_billing_date($AccountBilling->BillingCycleType, $AccountBilling->BillingCycleValue, strtotime($CustomerLastInvoiceDate));
            while ($EndDate < $today) {
                $query = DB::connection('neon_report')->table('tblHeader')
                    ->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeader.DateID')
                    ->where(array('AccountID' => $id))
                    ->where('date', '>=', $StartDate)
                    ->where('date', '<', $EndDate);
                $TotalAmount = (double)$query->sum('TotalCharges');
                $TotalMinutes = (double)$query->sum('TotalBilledDuration');
                $CustomerNextBilling[] = array(
                    'StartDate' => $StartDate,
                    'EndDate' => $EndDate,
                    'AccountID' => $id,
                    'ServiceID' => 0,
                    'TotalAmount' => $TotalAmount,
                    'TotalMinutes' => $TotalMinutes,
                );
                $StartDate = $EndDate;
                $EndDate = next_billing_date($AccountBilling->BillingCycleType, $AccountBilling->BillingCycleValue, strtotime($StartDate));
            }
        } 
		$EndDate = $today;
		$query = DB::connection('neon_report')->table('tblHeader')
			->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeader.DateID')
			->where(array('AccountID' => $id))
			->where('date', '>=', $StartDate)
			->where('date', '<=', $EndDate);
		$TotalAmount = (double)$query->sum('TotalCharges');
		$TotalMinutes = (double)$query->sum('TotalBilledDuration');
		if ($TotalAmount > 0) {
			$CustomerNextBilling[] = array(
				'StartDate' => $StartDate,
				'EndDate' => $EndDate,
				'AccountID' => $id,
				'ServiceID' => 0,
				'TotalAmount' => $TotalAmount,
				'TotalMinutes' => $TotalMinutes,
			);
		}
        if(strpos($VendorLastInvoiceDate, "23:59:59") !== false){
            $VendorLastInvoiceDate = date('Y-m-d',strtotime($VendorLastInvoiceDate)+1);
        }

        $StartDate = $VendorLastInvoiceDate;
        if (!empty($AccountBilling) && $AccountBilling->BillingCycleType != 'manual') {
            $EndDate = next_billing_date($AccountBilling->BillingCycleType, $AccountBilling->BillingCycleValue, strtotime($VendorLastInvoiceDate));
            while ($EndDate < $today) {
                $query = DB::connection('neon_report')->table('tblHeaderV')
                    ->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeaderV.DateID')
                    ->where(array('VAccountID' => $id))
                    ->where('date', '>=', $StartDate)
                    ->where('date', '<', $EndDate);
                $TotalAmount = (double)$query->sum('TotalCharges');
                $TotalMinutes = (double)$query->sum('TotalBilledDuration');
                $VendorNextBilling[] = array(
                    'StartDate' => $StartDate,
                    'EndDate' => $EndDate,
                    'AccountID' => $id,
                    'ServiceID' => 0,
                    'TotalAmount' => $TotalAmount,
                    'TotalMinutes' => $TotalMinutes,
                );
                $StartDate = $EndDate;
                $EndDate = next_billing_date($AccountBilling->BillingCycleType, $AccountBilling->BillingCycleValue, strtotime($StartDate));
            }
        } 
		$EndDate = $today;
		$query = DB::connection('neon_report')->table('tblHeaderV')
			->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeaderV.DateID')
			->where(array('VAccountID' => $id))
			->where('date', '>=', $StartDate)
			->where('date', '<=', $EndDate);
		$TotalAmount = (double)$query->sum('TotalCharges');
		$TotalMinutes = (double)$query->sum('TotalBilledDuration');
		if ($TotalAmount > 0) {
			$VendorNextBilling[] = array(
				'StartDate' => $StartDate,
				'EndDate' => $EndDate,
				'AccountID' => $id,
				'ServiceID' => 0,
				'TotalAmount' => $TotalAmount,
				'TotalMinutes' => $TotalMinutes,
			);
		}
        

        return View::make('invoices.unbilled_table', compact('VendorNextBilling','CustomerNextBilling','CurrencySymbol','CustomerEndDate','CustomerLastInvoiceDate','today','yesterday','lastInvoicePeriod'));

    }

    public function generate_manual_invoice(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $UserID = User::get_userID();
        $AccountID = $data['AccountID'];
        $AccountBilling = AccountBilling::getBilling($AccountID, 0);
        if ($AccountID > 0 && $AccountBilling->BillingCycleType == 'manual') {
            $rules = array(
                'PeriodFrom' => 'required',
                'PeriodTo' => 'required',
            );
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if($data['PeriodFrom'] > $data['PeriodTo']){
                return Response::json(array("status" => "failed", "message" => "Dates are invalid"));
            }
            $AlreadyBilled = Invoice::checkIfAccountUsageAlreadyBilled($CompanyID, $AccountID, $data['PeriodFrom'], $data['PeriodTo'], 0);
            if ($AlreadyBilled) {
                return Response::json(array("status" => "failed", "message" => "Account already billed for this period.Select different period"));
            } else {
                $CronJobCommandID = CronJobCommand::where(array('Command'=>'invoicegenerator','CompanyID'=>$CompanyID))->pluck('CronJobCommandID');
                $CronJobID = CronJob::where(array('CronJobCommandID'=>(int)$CronJobCommandID,'CompanyID'=>$CompanyID))->pluck('CronJobID');
                if($CronJobID > 0) {

                    $jobType = JobType::where(["Code" => 'BI'])->get(["JobTypeID", "Title"]);
                    $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
                    $jobdata["CompanyID"] = $CompanyID;
                    $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
                    $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
                    $jobdata["JobLoggedUserID"] = $UserID;
                    $jobdata["Title"] = "[Manual] " . (isset($jobType[0]->Title) ? $jobType[0]->Title : '') . ' Generate & Send';
                    $jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
                    $jobdata["CreatedBy"] = User::get_user_full_name($UserID);
                    $jobdata['Options'] = json_encode(array('CronJobID'=>$CronJobID,'ManualInvoice'=>1)+$data);
                    $jobdata["created_at"] = date('Y-m-d H:i:s');
                    $jobdata["updated_at"] = date('Y-m-d H:i:s');
                    $JobID = Job::insertGetId($jobdata);

                    if($JobID>0) {
                        return Response::json(array("status" => "success", "message" => "Invoice Generation Job Added in queue to process.You will be notified once job is completed. "));
                    }
                }
                return Response::json(array("status" => "error", "message" => "Please Setup Invoice Generator in CronJob"));

            }

        } else {
            return Response::json(array("status" => "failed", "message" => "Please select account or account should have manual billing."));
        }
    }

    public function sagepay_return() {
        $CompanyID =0;
        $SagePay = new SagePay($CompanyID);
        $AccountnInvoice = $SagePay->getAccountInvoiceID('m10');

        if(isset($AccountnInvoice["AccountID"]) && isset($AccountnInvoice["InvoiceID"])) {
            $TransactionLog = TransactionLog::where(["AccountID" => $AccountnInvoice["AccountID"], "InvoiceID" => $AccountnInvoice["InvoiceID"]])->orderby("created_at", "desc")->first();

            $TransactionLog = json_decode(json_encode($TransactionLog),true);
            if($AccountnInvoice["InvoiceID"]==0){
                return Redirect::to(url('/customer/payments'));
            }
            if($TransactionLog["Status"] == TransactionLog::SUCCESS ){

                $Amount = $TransactionLog["Amount"];
                $Transaction = $TransactionLog["Transaction"];

                echo "<center>" . "Payment done successfully, Your Transaction ID is ". $Transaction .", Amount Received ".  $Amount  . " </center>";

            } else {

                echo "<center>Payment failed, Go back and try again later</center>";

            }
        }


    }
    public function sagepay_declined() {

        echo "<center>Payment declined, Go back and try again later.</center>";

    }


    public function bulk_print_invoice(){
        $zipfiles = array();
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

        $invoiceIds=array_map('intval', explode(',', $data['InvoiceIDs']));

        if(!empty($invoiceIds)) {

            $Invoices = Invoice::find($invoiceIds);
            $CompanyID = User::get_companyID();
            $UPLOAD_PATH = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID). "/";
            $isAmazon = is_amazon($CompanyID);
            foreach ($Invoices as $invoice) {
                $path = AmazonS3::preSignedUrl($invoice->PDF,$CompanyID);

                if ( file_exists($path) ){
                    $zipfiles[$invoice->InvoiceID]=$path;
                }else if($isAmazon == true){

                    $filepath = $UPLOAD_PATH . basename($invoice->PDF);
                    $content = @file_get_contents($path);
                    if($content != false){
                        file_put_contents( $filepath, $content);
                        $zipfiles[$invoice->InvoiceID] = $filepath;
                    }
                }
            }

            if (!empty($zipfiles)) {

                if (count($zipfiles) == 1) {

                    $downloadInvoiceid = array_keys($zipfiles)[0];
                    return Response::json(array("status" => "success", "message" => " Download Starting ", "invoiceId" => $downloadInvoiceid, "filePath" => ""));

                } else {

                    $filename='invoice' . date("dmYHis") . '.zip';
                    $local_zip_file = $UPLOAD_PATH . $filename;

                    Zipper::make($local_zip_file)->add($zipfiles)->close();

                    if (file_exists($local_zip_file)) {
                        return Response::json(array("status" => "success", "message" => " Download Starting ", "invoiceId" => "", "filePath" => base64_encode($filename)));
                    }
                    else {
                        return Response::json(array("status" => "error", "message" => "Something wrong Please Try Again"));
                    }
                }

            }
        }
        else {
            return Response::json(array("status" => "error", "message" => "Please Select Invoice"));
        }
        exit;
    }

	public function invoice_sagepayexport(){
        $data = Input::all();
        $MarkPaid = $data['MarkPaid'];
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
        $InvoiceIDs = array_filter(explode(',', $data['InvoiceIDs']), 'intval');
        if (is_array($InvoiceIDs) && count($InvoiceIDs)) {
            $SageData = array();
            $SageData['CompanyID'] = $CompanyID;
            $SageData['Invoices'] = $InvoiceIDs;
            $SageData['MarkPaid'] = $MarkPaid;

            $SageDirectDebit = new SagePayDirectDebit($CompanyID);
            $Response = $SageDirectDebit->sagebatchfileexport($SageData);
            log::info('Response');
            log::info($Response);
            if(!empty($Response['file_path'])){
                $FilePath = $Response['file_path'];
                if(file_exists($FilePath)){
                    download_file($FilePath);
                }else{
                    header('Location: '.$FilePath);
                }
                exit;
            }
        }
        exit;
    }

    public function payinvoice_withcard($type){
        $data = Input::all();
        $InvoiceID = $data['InvoiceID'];
        $AccountID = $data['AccountID'];
        $Invoice = Invoice::where('InvoiceStatus','!=',Invoice::PAID)->where(["InvoiceID" => $InvoiceID, "AccountID" => $AccountID])->first();
        if(!empty($Invoice) && intval($InvoiceID)>0 && $data['isInvoicePay']) {
            $payment_log = Payment::getPaymentByInvoice($Invoice->InvoiceID);

            $data['GrandTotal'] = $payment_log['final_payment'];
            $data['InvoiceNumber'] = $Invoice->FullInvoiceNumber;
            $data['CompanyID'] = $Invoice->CompanyID;

            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($type);
            $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);

            $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $Invoice->CompanyID);
            $PaymentResponse = $PaymentIntegration->paymentWithCreditCard($data);
            return json_encode($PaymentResponse);
        }elseif(isset($data['GrandTotal']) && intval($data['GrandTotal'])>0 && !$data['isInvoicePay']){
            $account = Account::find($AccountID);
            if(!empty($Invoice)){
                $data['InvoiceNumber'] = $Invoice->FullInvoiceNumber;
            }else{
                $data['InvoiceNumber'] = '';
            }
            $data['CompanyID'] = $account->CompanyId;

            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($type);
            $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);
            $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $account->CompanyID);
            $PaymentResponse = $PaymentIntegration->paymentWithCreditCard($data);
            return json_encode($PaymentResponse);
        }else{
            return Response::json(array("status" => "failed", "message" => cus_lang('PAGE_INVOICE_MSG_INVOICE_NOT_FOUND')));
        }
    }

    //using for authorize echeck
    public function payinvoice_withbankdetail($type){
        $data = Input::all();
        $InvoiceID = $data['InvoiceID'];
        $AccountID = $data['AccountID'];
        $Invoice = Invoice::where('InvoiceStatus','!=',Invoice::PAID)->where(["InvoiceID" => $InvoiceID, "AccountID" => $AccountID])->first();
        if(!empty($Invoice) && intval($InvoiceID)>0 && $data['isInvoicePay']) {
            $payment_log = Payment::getPaymentByInvoice($Invoice->InvoiceID);

            $data['GrandTotal'] = $payment_log['final_payment'];
            $data['InvoiceNumber'] = $Invoice->FullInvoiceNumber;
            $data['CompanyID'] = $Invoice->CompanyID;

            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($type);
            $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);
            $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $Invoice->CompanyID);
            $PaymentResponse = $PaymentIntegration->paymentWithBankDetail($data);
            return json_encode($PaymentResponse);
        }elseif(isset($data['GrandTotal']) && intval($data['GrandTotal'])>0 && !$data['isInvoicePay']){
            return Response::json(array("status" => "failed", "message" =>"failed"));
            /*
            $account = Account::find($AccountID);
            if(!empty($Invoice)){
                $data['InvoiceNumber'] = $Invoice->FullInvoiceNumber;
            }else{
                $data['InvoiceNumber'] = '';
            }
            $data['CompanyID'] = $account->CompanyId;

            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($type);
            $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);
            $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $account->CompanyID);
            $PaymentResponse = $PaymentIntegration->paymentWithBankDetail($data);
            return json_encode($PaymentResponse);
            */
        }else{
            return Response::json(array("status" => "failed", "message" => cus_lang('PAGE_INVOICE_MSG_INVOICE_NOT_FOUND')));
        }
    }

    // not using
    public function payinvoice_withbank($type){
        $data = Input::all();
        $InvoiceID = $data['InvoiceID'];
        $AccountID = $data['AccountID'];
        $Invoice = Invoice::where('InvoiceStatus','!=',Invoice::PAID)->where(["InvoiceID" => $InvoiceID, "AccountID" => $AccountID])->first();
        if(!empty($Invoice) && intval($InvoiceID)>0) {
            $Invoice = Invoice::find($Invoice->InvoiceID);

            $payment_log = Payment::getPaymentByInvoice($Invoice->InvoiceID);

            $data['GrandTotal'] = $payment_log['final_payment'];
            $data['InvoiceNumber'] = $Invoice->FullInvoiceNumber;
            $data['CompanyID'] = $Invoice->CompanyID;

            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($type);
            $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);

            $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $Invoice->CompanyID);
            $PaymentResponse = $PaymentIntegration->paymentWithBankDetail($data);
            return json_encode($PaymentResponse);
        }elseif(isset($data['GrandTotal']) && intval($data['GrandTotal'])>0){
            $account = Account::find($AccountID);

            $data['InvoiceNumber'] = 0;
            $data['CompanyID'] = $account->CompanyID;

            $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($type);
            $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);

            $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $account->CompanyID);
            $PaymentResponse = $PaymentIntegration->paymentWithBankDetail($data);
            return json_encode($PaymentResponse);
        }else{
            return Response::json(array("status" => "failed", "message" => "Invoice not found"));
        }
    }

    /**
     * Only for guest auth wih profile
     * like stripeach from invoice page
    */
    public function payinvoice_withprofile($type){
        $data = Input::all();
        $InvoiceID = $data['InvoiceID'];
        $AccountID = $data['AccountID'];
        $Invoice = Invoice::where('InvoiceStatus','!=',Invoice::PAID)->where(["InvoiceID" => $InvoiceID, "AccountID" => $AccountID])->first();
        if(!empty($Invoice) && intval($InvoiceID)>0 && $data['isInvoicePay']) {
            $Invoice = Invoice::find($Invoice->InvoiceID);
            $payment_log = Payment::getPaymentByInvoice($Invoice->InvoiceID);
            $CustomerProfile = AccountPaymentProfile::find($data['AccountPaymentProfileID']);
            if (!empty($CustomerProfile)) {
                $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($type);
                $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);

                $PaymentData = array();
                $PaymentData['AccountID'] = $AccountID;
                $PaymentData['CompanyID'] = $Invoice->CompanyID;
                $PaymentData['CreatedBy'] = 'customer';
                $PaymentData['AccountPaymentProfileID'] = $data['AccountPaymentProfileID'];
                $PaymentData['InvoiceIDs'] = $InvoiceID;
                $PaymentData['InvoiceNumber'] = $Invoice->FullInvoiceNumber;
                $PaymentGateway = PaymentGateway::getName($CustomerProfile->PaymentGatewayID);
                $PaymentData['PaymentGateway'] = $PaymentGateway;
                $PaymentData['outstanginamount'] = $payment_log['final_payment'];

                $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $Invoice->CompanyID);
                $PaymentResponse = $PaymentIntegration->paymentWithProfile($PaymentData);
                return json_encode($PaymentResponse);

            }else{
                return json_encode(array("status" => "failed", "message" => cus_lang('PAGE_INVOICE_MSG_ACCOUNT_PROFILE_NOT_SET')));
            }
        }elseif(isset($data['GrandTotal']) && intval($data['GrandTotal'])>0 && !$data['isInvoicePay']){
            $account = Account::find($AccountID);

            $CustomerProfile = AccountPaymentProfile::find($data['AccountPaymentProfileID']);
            if (!empty($CustomerProfile)) {
                $PaymentGatewayID = PaymentGateway::getPaymentGatewayIDByName($type);
                $PaymentGatewayClass = PaymentGateway::getPaymentGatewayClass($PaymentGatewayID);

                $PaymentData = array();
                $PaymentData['AccountID'] = $AccountID;
                $PaymentData['CompanyID'] = $account->CompanyID;
                $PaymentData['CreatedBy'] = 'customer';
                $PaymentData['AccountPaymentProfileID'] = $data['AccountPaymentProfileID'];
                $PaymentData['InvoiceIDs'] = $InvoiceID;
                $PaymentData['InvoiceNumber'] = '';
                $PaymentGateway = PaymentGateway::getName($CustomerProfile->PaymentGatewayID);
                $PaymentData['PaymentGateway'] = $PaymentGateway;
                $PaymentData['outstanginamount'] = $data['GrandTotal'];
                $PaymentData['isInvoicePay'] = $data['isInvoicePay'];
                $PaymentData['custome_notes'] = $data['custome_notes'];

                $PaymentIntegration = new PaymentIntegration($PaymentGatewayClass, $account->CompanyID);
                $PaymentResponse = $PaymentIntegration->paymentWithProfile($PaymentData);
                return json_encode($PaymentResponse);

            }else{
                return json_encode(array("status" => "failed", "message" => cus_lang('PAGE_INVOICE_MSG_ACCOUNT_PROFILE_NOT_SET')));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => cus_lang('PAGE_INVOICE_MSG_INVOICE_NOT_FOUND')));
        }
    }
    /**
     * Xero Post
     *
    */
    public function invoice_xeropost(){
        $data = Input::All();

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
            $jobType = JobType::where(["Code" => 'XIP'])->first(["JobTypeID", "Title"]);
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
                return json_encode(["status" => "success", "message" => "Invoice Post in xero Job Added in queue to process.You will be notified once job is completed."]);
            }else{
                return json_encode(array("status" => "failed", "message" => "Problem Creating Invoice Post in Xero ."));
            }
        }
    }

    public function invoice_management_chart($id){

        $Invoice = Invoice::find($id);
        if (!empty($Invoice)) {
            $InvoiceDetail = InvoiceDetail::where(["InvoiceID" => $id])->get();
            $InvoiceUSAGEPeriod = InvoiceDetail::where(["InvoiceID" => $id,'ProductType'=>Product::USAGE])->first();
            $Account = Account::find($Invoice->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency) ? $Currency->Code : '';
            $CurrencySymbol = Currency::getCurrencySymbol($Account->CurrencyId);
            $InvoiceBillingClass =	 Invoice::GetInvoiceBillingClass($Invoice);
            $InvoiceTemplateID = BillingClass::getInvoiceTemplateID($InvoiceBillingClass);
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            $RoundChargesAmount = get_round_decimal_places($Invoice->AccountID);
            $companyID = $Account->CompanyId;
            $management_query = "call prc_InvoiceManagementReport ('" . $companyID . "','".intval($Invoice->AccountID) . "','".$InvoiceUSAGEPeriod->StartDate . "','".$InvoiceUSAGEPeriod->EndDate. "')";
            $ManagementReports = DataTableSql::of($management_query,'sqlsrvcdr')->getProcResult(array('LongestCalls','ExpensiveCalls','DialledNumber','DailySummary','UsageCategory'));
            $ManagementReports = json_decode(json_encode($ManagementReports['data']), true);
            return View::make('invoices.invoice_chart', compact('Invoice', 'InvoiceDetail', 'Account', 'InvoiceTemplate', 'CurrencyCode','ManagementReports','CurrencySymbol','RoundChargesAmount'));
        }
    }

    /**api paypal */
    /** Paypal ipn url which will be triggered from paypal with payment status and response
     * @param $id
     * @return mixed
     */
    public function api_paypal_ipn($id)
    {

        //@TODO: need to merge all payment gateway payment insert entry.

        $CompanyID=$id;
        $paypal = new PaypalIpn($CompanyID);

        $data["Notes"]                  = $paypal->get_note();
        $data["Success"]                = $paypal->success();
        $data["PaymentMethod"]          = $paypal->method;
        $data["Amount"]                 = $paypal->get_response_var('mc_gross');
        $data["Transaction"]            = $paypal->get_response_var('txn_id');
        $data["PaymentGatewayResponse"] = $paypal->get_full_response();

        log::info('api_paypal_ipn');
        log::info(print_r($data,true));
        return Response::json(array("status" => "success", "message" => "Invoice paid successfully","data"=>$data));
        /*

        $account_inv = explode('-', $id);
        if (isset($account_inv[0]) && intval($account_inv[0]) > 0 && isset($account_inv[1]) && intval($account_inv[1]) >= 0) {
            $AccountID = intval($account_inv[0]);
            $InvoiceID = intval($account_inv[1]);

            if($InvoiceID!=0){
                $Invoice = Invoice::find($InvoiceID);
                $CompanyID = $Invoice->CompanyID;
            }else{
                if(isset($account_inv[2])){
                    $Invoice = Invoice::where(array('FullInvoiceNumber'=>$account_inv[2],'AccountID'=>$AccountID))->first();
                    if(!empty($Invoice) && count($Invoice)){
                        $CompanyID = $Invoice->CompanyID;
                        $InvoiceID = $Invoice->InvoiceID;
                    }
                }
            }
            if(!isset($CompanyID)){
                $account = Account::find($AccountID);
                $CompanyID = $account->CompanyId;
            }

            $paypal = new PaypalIpn($CompanyID);

            $data["Notes"]                  = $paypal->get_note();
            $data["Success"]                = $paypal->success();
            $data["PaymentMethod"]          = $paypal->method;
            $data["Amount"]                 = $paypal->get_response_var('mc_gross');
            $data["Transaction"]            = $paypal->get_response_var('txn_id');
            $data["PaymentGatewayResponse"] = $paypal->get_full_response();

            return $this->post_payment_process($AccountID,$InvoiceID,$data);
        }
        */
    }
    public function api_invoice_creditcard_thanks(){
        $data = Input::all();
        log::info(print_r($data,true));
        log::info('api_invoice_thanks');
        //log::info(json_decode($data['data'],true));
        $customdata = json_encode(json_decode($data['data'],true));
        //$customdata=$data['data'];
        return View::make('neonregistartion.api_invoice_creditcard_thanks', compact('data','customdata'));
    }

    public function api_paypal_cancel($id){
        echo "<center>Opps. Payment Canceled, Please try again.</center>";
    }

    public function api_sagepay_ipn($CompanyID)
    {

        //https://sagepay.co.za/integration/sage-pay-integration-documents/pay-now-gateway-technical-guide/
        $SagePay = new SagePay($CompanyID);

        $data["Notes"]                  = $SagePay->get_note();
        $data["Success"]                = $SagePay->success();
        $data["PaymentMethod"]          = $SagePay->method;
        $data["Amount"]                 = $SagePay->get_response_var('Amount');
        $data["Transaction"]            = $SagePay->get_response_var('RequestTrace');
        $data["PaymentGatewayResponse"] = $SagePay->get_full_response();

        log::info('api_sagepay_ipn');
        log::info(print_r($data,true));
        return Response::json(array("status" => "success", "message" => "Invoice paid successfully","data"=>$data));

    }

    public function api_sagepay_return($id){
        $data = Input::all();

        log::info('sagepay log');
        log::info(print_r($data,true));


        $PaymentResponse =array();
        if(isset($data["Success"])){
            $PaymentResponse['PaymentMethod'] = $data["PaymentMethod"];
            $PaymentResponse['transaction_notes'] = 'SagePay transaction_id '.$data["Transaction"];
            $PaymentResponse['Amount'] = floatval($data["Amount"]);
            $PaymentResponse['Transaction'] = $data["Transaction"];
            $PaymentResponse['Response'] = $data["PaymentGatewayResponse"];
            $PaymentResponse['status'] = 'success';
        }elseif(!empty($data['tx'])){
            $PaymentResponse['PaymentMethod'] = 'SagePay';
            $PaymentResponse['transaction_notes'] = 'SagePay transaction_id '.$data['tx'];
            $PaymentResponse['Amount'] = floatval($data["amt"]);
            $PaymentResponse['Transaction'] = $data["tx"];
            $PaymentResponse['Response'] = '';
            $PaymentResponse['status'] = 'success';
        }

        $Alldata = array();
        $Alldata['PaymentResponse'] = json_encode($PaymentResponse);
        $Alldata['APIData'] =  Session::get('APIEncodeData');
        //log::info(print_r($PaymentResponse,true));
        log::info(print_r($Alldata,true));
        log::info('api_invoice_thanks');
        $RegistarionApiLogID = Session::get('RegistarionApiLogID');
        log::info('R LogID '.$RegistarionApiLogID);
        $RegistarionApiLogUpdate = array();
        if(!empty($RegistarionApiLogID)){
            $RegistarionApiLogUpdate['PaymentAmount'] = empty($PaymentResponse['Amount']) ? 0 : $PaymentResponse['Amount'];
            $RegistarionApiLogUpdate['PaymentResponse'] = json_encode($PaymentResponse);
            $RegistarionApiLogUpdate['PaymentStatus'] = 'success';
            DB::table('tblRegistarionApiLog')->where('RegistarionApiLogID', $RegistarionApiLogID)->update($RegistarionApiLogUpdate);
        }
        //log::info(json_decode($data['data'],true));
        //$customdata = json_encode(json_decode($Alldata,true));
        $customdata = json_encode($Alldata);
        //$customdata=$data['data'];
        return View::make('neonregistartion.api_invoice_creditcard_thanks', compact('data','customdata'));
    }

    public function api_sagepay_declined($id) {
        echo "<center>Payment declined, Go back and try again later.</center>";
    }

    public function StockHistoryCalculationByInvoiceStatus($InvoiceStatus,$InvoiceIDs){
        foreach($InvoiceIDs as $InvoiceID){
            $StockHistory=array();
            if($InvoiceStatus == Invoice::CANCEL){
                $Invoice=Invoice::where('InvoiceID',$InvoiceID)->first();
                $InvoiceDetailData=InvoiceDetail::where(['InvoiceID'=>$InvoiceID])->get();
                //ToDO:join two tables

                foreach($InvoiceDetailData as $InvoiceDetail) {
                    $temparray=array();
                    if($InvoiceDetail->ProductID >0 && $InvoiceDetail->Qty >0 ){
                        $companyID = $Invoice->CompanyID;
                        $reason='delete_prodstock';

                        $temparray['CompanyID']=$companyID;
                        $temparray['ProductID']=intval($InvoiceDetail->ProductID);
                        $temparray['InvoiceID']=$InvoiceID;
                        $temparray['Qty']=$InvoiceDetail->Qty;
                        $temparray['Reason']=$reason;
                        $temparray['InvoiceNumber']=$Invoice->FullInvoiceNumber;
                        $temparray['oldQty']=$InvoiceDetail->Qty;
                        $temparray['created_by']=User::get_user_full_name();

                        array_push($StockHistory,$temparray);
                    }
                }
                Log::info("===== StockHistory while Cancel Invoice ====");
                Log::info($StockHistory);
                if(!empty($StockHistory)){
                    $historyData=stockHistoryUpdateCalculations($StockHistory);
                }

            }else{
                $Invoice=Invoice::where('InvoiceID',$InvoiceID)->first();
                if(!empty($Invoice) && $Invoice->InvoiceStatus==Invoice::CANCEL){
                    $StockHistory=array();
                    $InvoiceDetailData=InvoiceDetail::where(['InvoiceID'=>$InvoiceID])->get();
                    foreach($InvoiceDetailData as $InvoiceDetail) {
                        $temparray=array();
                        if($InvoiceDetail->ProductID >0 && $InvoiceDetail->Qty >0 ){
                            $companyID = User::get_companyID();

                            $temparray['CompanyID']=$companyID;
                            $temparray['ProductID']=intval($InvoiceDetail->ProductID);
                            $temparray['InvoiceID']=$InvoiceID;
                            $temparray['Qty']=$InvoiceDetail->Qty;
                            $temparray['Reason']='';
                            $temparray['InvoiceNumber']=$Invoice->FullInvoiceNumber;
                            $temparray['created_by']=User::get_user_full_name();

                            array_push($StockHistory,$temparray);
                        }
                    }
                    Log::info("===== StockHistory while Change InvoiceStatus From Cancel ====");
                    Log::info($StockHistory);
                    if(!empty($StockHistory)){
                        $historyData=StockHistoryCalculations($StockHistory);
                    }

                }
            }
        }
    }

}