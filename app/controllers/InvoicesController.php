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
			"total_grand"=>$result['data']['Total_grand_field'][0]->currency_symbol.$result['data']['Total_grand_field'][0]->total_grand,
			"os_pp"=>$result['data']['Total_grand_field'][0]->currency_symbol.$result['data']['Total_grand_field'][0]->TotalPayment.' / '.$result['data']['Total_grand_field'][0]->TotalPendingAmount,
		);
		
		return json_encode($result4,JSON_NUMERIC_CHECK);		
	}

    public function ajax_datagrid($type) {
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $companyID = User::get_companyID();
        $columns = ['InvoiceID','AccountName','InvoiceNumber','IssueDate','InvoicePeriod','GrandTotal','PendingAmount','InvoiceStatus','InvoiceID'];
        $data['InvoiceType'] = $data['InvoiceType'] == 'All'?'':$data['InvoiceType'];
        $data['zerovalueinvoice'] = $data['zerovalueinvoice']== 'true'?1:0;
        $data['IssueDateStart'] = empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd'] = empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];
        $data['CurrencyID'] = empty($data['CurrencyID'])?'0':$data['CurrencyID'];
        $data['Overdue'] = $data['Overdue']== 'true'?1:0;
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getInvoice (".$companyID.",".intval($data['AccountID']).",'".$data['InvoiceNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."',".intval($data['InvoiceType']).",'".$data['InvoiceStatus']."',".$data['Overdue'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',".intval($data['CurrencyID'])."";
        if(isset($data['Export']) && $data['Export'] == 1) {
            if(isset($data['zerovalueinvoice']) && $data['zerovalueinvoice'] == 1){
                $excel_data  = DB::connection('sqlsrv2')->select($query.',1,0,1,"")');
            }else{
                $excel_data  = DB::connection('sqlsrv2')->select($query.',1,0,0,"")');
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
        //$emailTemplates = EmailTemplate::getTemplateArray(array('Type'=>EmailTemplate::INVOICE_TEMPLATE));
		$emailTemplates = EmailTemplate::getTemplateArray(array('StaticType'=>EmailTemplate::DYNAMICTEMPLATE));
        $templateoption = [''=>'Select',1=>'New Create',2=>'Update'];
        $data['StartDateDefault'] 	  	= 	'';
		$data['IssueDateEndDefault']  	= 	'';
        $InvoiceHideZeroValue = NeonCookie::getCookie('InvoiceHideZeroValue',1);
        $Quickbook = new BillingAPI();
        $check_quickbook = $Quickbook->check_quickbook();
		$bulk_type = 'invoices';
        //print_r($_COOKIE);exit;
        return View::make('invoices.index',compact('products','accounts','invoice_status_json','emailTemplates','templateoption','DefaultCurrencyID','data','invoice','InvoiceHideZeroValue','check_quickbook','bulk_type'));

    }

    /**
     * Show the form for creating a new resource.
     * GET /invoices/create
     *
     * @return Response
     */
    public function create()
    {

        $accounts 	= 	Account::getAccountIDList();
        $products 	= 	Product::getProductDropdownList();
        $taxes 		= 	TaxRate::getTaxRateDropdownIDListForInvoice();
		//echo "<pre>"; 		print_r($taxes);		echo "</pre>"; exit;
        //$gateway_product_ids = Product::getGatewayProductIDs();
		$BillingClass = BillingClass::getDropdownIDList(User::get_companyID());
        return View::make('invoices.create',compact('accounts','products','taxes','BillingClass'));

    }

    /**
     *
     * */
    public function edit($id){


        //$str = preg_replace('/^INV/', '', 'INV021000');;
        if($id > 0) {

            $Invoice = Invoice::find($id);
			$InvoiceBillingClass =	 Invoice::GetInvoiceBillingClass($Invoice);			
            $InvoiceDetail = InvoiceDetail::where(["InvoiceID"=>$id])->get();
            $accounts = Account::getAccountIDList();
            $products = Product::getProductDropdownList();
            //$gateway_product_ids = Product::getGatewayProductIDs();
            $Account = Account::where(["AccountID" => $Invoice->AccountID])->select(["AccountName","BillingEmail", "CurrencyId"])->first(); //"TaxRateID","RoundChargesAmount","InvoiceTemplateID"
            $CurrencyID = !empty($Invoice->CurrencyID)?$Invoice->CurrencyID:$Account->CurrencyId;
            $RoundChargesAmount = get_round_decimal_places($Invoice->AccountID);
            $InvoiceTemplateID = BillingClass::getInvoiceTemplateID($InvoiceBillingClass);
            $InvoiceNumberPrefix = ($InvoiceTemplateID>0)?InvoiceTemplate::find($InvoiceTemplateID)->InvoiceNumberPrefix:'';
            $Currency = Currency::find($CurrencyID);
            $CurrencyCode = !empty($Currency)?$Currency->Code:'';
            $CompanyName = Company::getName();
            $taxes =  TaxRate::getTaxRateDropdownIDListForInvoice();
            $invoicelog =  InVoiceLog::where(array('InvoiceID'=>$id))->get();
			$InvoiceAllTax =  InvoiceTaxRate::where(["InvoiceID"=>$id,"InvoiceTaxType"=>1])->get();
			$BillingClass = BillingClass::getDropdownIDList(User::get_companyID());
			
            return View::make('invoices.edit', compact( 'id', 'Invoice','InvoiceDetail','InvoiceTemplateID','InvoiceNumberPrefix',  'CurrencyCode','CurrencyID','RoundChargesAmount','accounts', 'products', 'taxes','CompanyName','Account','invoicelog','InvoiceAllTax','BillingClass','InvoiceBillingClass'));
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
                'AccountID' => 'required',
                'Address' => 'required',
				'BillingClassID'=> 'required',
                'InvoiceNumber' => 'required|unique:tblInvoice,InvoiceNumber,NULL,InvoiceID,CompanyID,'.$companyID,
                'IssueDate' => 'required',
                'CurrencyID' => 'required',
                'GrandTotal' => 'required',
                'InvoiceType' => 'required',
            );
			$message = ['BillingClassID.required'=>'Billing Class field is required'];
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($InvoiceData, $rules,$message);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
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
						$InvoiceAllTaxRates[$p]['TaxRateID'] 		= 	$InvoiceTaxes;
						$InvoiceAllTaxRates[$p]['Title'] 			= 	TaxRate::getTaxName($InvoiceTaxes);
						$InvoiceAllTaxRates[$p]["created_at"] 		= 	date("Y-m-d H:i:s");
						$InvoiceAllTaxRates[$p]["InvoiceTaxType"] 	= 	1;
						$InvoiceAllTaxRates[$p]["InvoiceID"] 		= 	$Invoice->InvoiceID; 
						$InvoiceAllTaxRates[$p]["TaxAmount"] 		= 	$data['InvoiceTaxes']['value'][$p];
					}
				}
				
                $InvoiceTaxRates 	 = 	merge_tax($InvoiceTaxRates);
				$InvoiceAllTaxRates  = 	merge_tax($InvoiceAllTaxRates);
				
                $invoiceloddata = array();
                $invoiceloddata['InvoiceID']= $Invoice->InvoiceID;
                $invoiceloddata['Note']= 'Created By '.$CreatedBy;
                $invoiceloddata['created_at']= date("Y-m-d H:i:s");
                $invoiceloddata['InvoiceLogStatus']= InVoiceLog::CREATED;
                InVoiceLog::insert($invoiceloddata);
                if(!empty($InvoiceTaxRates)) { //product tax
                    InvoiceTaxRate::insert($InvoiceTaxRates);
                }
				
				 if(!empty($InvoiceAllTaxRates)) { //Invoice tax
                    InvoiceTaxRate::insert($InvoiceAllTaxRates);
                } 
                if (!empty($InvoiceDetailData) && InvoiceDetail::insert($InvoiceDetailData)) { 
                    $pdf_path = Invoice::generate_pdf($Invoice->InvoiceID); 
                    if (empty($pdf_path)) {
                        $error['message'] = 'Failed to generate Invoice PDF File';
                        $error['status'] = 'failure';
                        return $error;
                    } else {
                        $Invoice->update(["PDF" => $pdf_path]);
                    }


                    DB::connection('sqlsrv2')->commit();

                    return Response::json(array("status" => "success", "message" => "Invoice Successfully Created",'LastID'=>$Invoice->InvoiceID,'redirect' => URL::to('/invoice/'.$Invoice->InvoiceID.'/edit')));
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
					$InvoiceDetailData = $InvoiceTaxRates = $InvoiceAllTaxRates = array();
                    //Delete all Invoice Data and then Recreate.
                    InvoiceDetail::where(["InvoiceID" => $Invoice->InvoiceID])->delete();
                    InvoiceTaxRate::where(["InvoiceID" => $Invoice->InvoiceID])->delete();
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
						$InvoiceAllTaxRates[$p]['TaxRateID'] 		= 	$InvoiceTaxes;
						$InvoiceAllTaxRates[$p]['Title'] 			= 	TaxRate::getTaxName($InvoiceTaxes);
						$InvoiceAllTaxRates[$p]["created_at"] 		= 	date("Y-m-d H:i:s");
						$InvoiceAllTaxRates[$p]["InvoiceTaxType"] 	= 	1;
						$InvoiceAllTaxRates[$p]["InvoiceID"] 		= 	$Invoice->InvoiceID; 
						$InvoiceAllTaxRates[$p]["TaxAmount"] 		= 	$data['InvoiceTaxes']['value'][$p];
					}
				}
						
                        $InvoiceTaxRates 	  =     merge_tax($InvoiceTaxRates);
						$InvoiceAllTaxRates   = 	merge_tax($InvoiceAllTaxRates);
						
                        if(!empty($InvoiceTaxRates)) { //product tax
                            InvoiceTaxRate::insert($InvoiceTaxRates);
                        }
						
						 if(!empty($InvoiceAllTaxRates)) { //Invoice tax
                 		   InvoiceTaxRate::insert($InvoiceAllTaxRates);
               		 }
						
                        if (InvoiceDetail::insert($InvoiceDetailData)) {
                            $pdf_path = Invoice::generate_pdf($Invoice->InvoiceID);
                            if (empty($pdf_path)) {
                                $error['message'] = 'Failed to generate Invoice PDF File';
                                $error['status'] = 'failure';
                                return $error;
                            } else {
                                $Invoice->update(["PDF" => $pdf_path]);
                            }

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

            $payment_log = Payment::getPaymentByInvoice($id);

            $paypal_button = "";
            $paypal = new PaypalIpn();
            if(!empty($paypal->status)){
                $paypal->item_title =  Company::getName($Invoice->CompanyID).  ' Invoice #'.$Invoice->FullInvoiceNumber;
                $paypal->item_number =  $Invoice->FullInvoiceNumber;
                $paypal->curreny_code =  $CurrencyCode;
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

                $paypal_button = $paypal->get_paynow_button($Invoice->InvoiceID,$Invoice->AccountID);
            }

            return View::make('invoices.invoice_cview', compact('Invoice', 'InvoiceDetail', 'Account', 'InvoiceTemplate', 'CurrencyCode', 'logo','CurrencySymbol','payment_log','paypal_button'));
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
            $upload_path = CompanyConfiguration::get('UPLOAD_PATH');
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['VENDOR_UPLOAD']);
            $destinationPath = $upload_path . '/' . $amazonPath;
            $Attachment = Input::file('Attachment');
            // ->move($destinationPath);
            $ext = $Attachment->getClientOriginalExtension();
            if (in_array(strtolower($ext), array("pdf", "jpg", "png", "gif"))) {
                $file_name = GUID::generate() . '.' . $Attachment->getClientOriginalExtension();
                $Attachment->move($destinationPath, $file_name);
                if (!AmazonS3::upload($destinationPath.$file_name, $amazonPath)) {
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
            $upload_path = CompanyConfiguration::get('UPLOAD_PATH');
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['VENDOR_UPLOAD']);
            $destinationPath = $upload_path . '/' . $amazonPath;
            $Attachment = Input::file('Attachment');
            // ->move($destinationPath);
            $ext = $Attachment->getClientOriginalExtension();
            if (in_array(strtolower($ext), array("pdf", "jpg", "png", "gif"))) {
                $file_name = GUID::generate() . '.' . $Attachment->getClientOriginalExtension();
                $Attachment->move($destinationPath, $file_name);
                if (!AmazonS3::upload($destinationPath.$file_name, $amazonPath)) {
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
               // $Subject = "New Invoice " . $Invoice->FullInvoiceNumber . ' from ' . $CompanyName . ' ('.$Account->AccountName.')';
			    $templateData	 	 = 	 EmailTemplate::where(["SystemType"=>Invoice::EMAILTEMPLATE])->first();
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
            $InvoiceCopy = Notification::getNotificationMail(Notification::InvoiceCopy);
            $InvoiceCopy = empty($InvoiceCopy)?$Company->Email:$InvoiceCopy;
            $emailtoCustomer = CompanyConfiguration::get('EMAIL_TO_CUSTOMER');
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
					$amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['EMAIL_ATTACHMENT']);
					$destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
	
					if (!file_exists($destinationPath)) {
						mkdir($destinationPath, 0777, true);
					}
					copy($array_file_data['filepath'], $destinationPath . $file_name);
					if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
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
        $PDFurl = '';
        if(is_amazon() == true){
            $PDFurl =  AmazonS3::preSignedUrl($Invoice->PDF);
        }else{
            $PDFurl = CompanyConfiguration::get('UPLOAD_PATH')."/".$Invoice->PDF;
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
    public static function download_attachment($InvoiceID){
        $Invoice = Invoice::find($InvoiceID);
        $FilePath =  AmazonS3::preSignedUrl($Invoice->Attachment);
        if(is_amazon() == true){
            header('Location: '.$FilePath);
        }else if(file_exists($FilePath)){
            download_file($FilePath);
        }
        exit;
    }
    public function invoice_payment($id,$type)
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
                return View::make('invoices.invoice_payment', compact('Invoice','CurrencySymbol','Account','CurrencyCode','type'));
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
                $paymentdata['EmailTemplate'] 		= 	EmailTemplate::where(["SystemType"=>EmailTemplate::InvoicePaidNotificationTemplate])->first();
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
        $query = "call prc_getInvoice (".$companyID.",'".$criteria['AccountID']."','".$criteria['InvoiceNumber']."','".$criteria['IssueDateStart']."','".$criteria['IssueDateEnd']."','".$criteria['InvoiceType']."','".$criteria['InvoiceStatus']."',".$criteria['Overdue'].",'' ,'','','','".$criteria['CurrencyID']."' ";

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
            $query = "call prc_getInvoice (".$companyID.",0,'','0000-00-00 00:00:00','0000-00-00 00:00:00',0,'',0,1 ,".count($data['InvoiceIDs']).",'','',''";
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
        if (isset($account_inv[0]) && intval($account_inv[0]) > 0 && isset($account_inv[1]) && intval($account_inv[1]) > 0) {
            $AccountID = intval($account_inv[0]);
            $InvoiceID = intval($account_inv[1]);
            $Invoice = Invoice::where(["InvoiceID" => $InvoiceID, "AccountID" => $AccountID])->first();
            $Account = Account::where(['AccountID' => $AccountID])->first();

            $paypal = new PaypalIpn();

            $Notes = $paypal->get_note();



            if ($paypal->success() && count($Invoice) > 0 ) {

                $PaymentCount = Payment::where('Notes',$Notes)->count();
                if($PaymentCount == 0) {
                    $Invoice = Invoice::find($Invoice->InvoiceID);

                    // Add Payment
                    $paymentdata = array();
                    $paymentdata['CompanyID'] = $Invoice->CompanyID;
                    $paymentdata['AccountID'] = $Invoice->AccountID;
                    $paymentdata['InvoiceNo'] = $Invoice->FullInvoiceNumber;
                    $paymentdata['InvoiceID'] = (int)$Invoice->InvoiceID;
                    $paymentdata['PaymentDate'] = date('Y-m-d H:i:s');
                    $paymentdata['PaymentMethod'] = 'PAYPAL_IPN';
                    $paymentdata['CurrencyID'] = $Account->CurrencyId;
                    $paymentdata['PaymentType'] = 'Payment In';
                    $paymentdata['Notes'] = $Notes;
                    $paymentdata['Amount'] = floatval($paypal->get_response_var('mc_gross'));
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
                    $transactiondata['CompanyID'] = $Account->CompanyId;
                    $transactiondata['AccountID'] = $AccountID;
                    $transactiondata['InvoiceID'] = $Invoice->InvoiceID;
                    $transactiondata['Transaction'] = $paypal->get_response_var('txn_id');
                    $transactiondata['Notes'] = $Notes;
                    $transactiondata['Amount'] = floatval($paypal->get_response_var('mc_gross'));
                    $transactiondata['Status'] = TransactionLog::SUCCESS;
                    $transactiondata['created_at'] = date('Y-m-d H:i:s');
                    $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                    $transactiondata['CreatedBy'] = 'Customer';
                    $transactiondata['ModifyBy'] = 'Customer';
                    $transactiondata['Reposnse'] = json_encode($paypal->get_full_response());

                    TransactionLog::insert($transactiondata);

                    $Invoice->update(array('InvoiceStatus' => Invoice::PAID));

                    \Illuminate\Support\Facades\Log::info("Transaction done.");
                    \Illuminate\Support\Facades\Log::info($transactiondata);

                    $paypal->log();
                    $paymentdata['EmailTemplate'] = EmailTemplate::where(["SystemType" => EmailTemplate::InvoicePaidNotificationTemplate])->first();
                    $paymentdata['CompanyName'] = Company::getName($paymentdata['CompanyID']);
                    $paymentdata['Invoice'] = $Invoice;
                    Notification::sendEmailNotification(Notification::InvoicePaidByCustomer, $paymentdata);
                    return Response::json(array("status" => "success", "message" => "Invoice paid successfully"));
                }else{
                    \Illuminate\Support\Facades\Log::info("Invoice Already paid successfully.");
                    return Response::json(array("status" => "success", "message" => "Invoice Already paid successfully"));
                }


            } else {


                $transactiondata = array();
                $transactiondata['CompanyID'] = $Invoice->CompanyID;
                $transactiondata['AccountID'] = $AccountID;
                $transactiondata['InvoiceID'] = $Invoice->InvoiceID;
                $transactiondata['Transaction'] = $paypal->get_response_var('txn_id');
                $transactiondata['Notes'] = $Notes;
                $transactiondata['Amount'] = floatval($Invoice->RemaingAmount);
                $transactiondata['Status'] = TransactionLog::FAILED;
                $transactiondata['created_at'] = date('Y-m-d H:i:s');
                $transactiondata['updated_at'] = date('Y-m-d H:i:s');
                $transactiondata['CreatedBy'] = 'customer';
                $transactiondata['ModifyBy'] = 'customer';
                $transactiondata['Reposnse'] = json_encode($paypal->get_full_response());
                TransactionLog::insert($transactiondata);

                $paypal->log();

                return Response::json(array("status" => "failed", "message" => "Failed to payment."));
            }
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
        $data['type'] = 'journal';
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
                return json_encode(["status" => "success", "message" => "Invoice Post in quickbook Job Added in queue to process.You will be notified once job is completed."]);
            }else{
                return json_encode(array("status" => "failed", "message" => "Problem Creating Invoice Post in Quickbook ."));
            }
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

        $stripepayment = new StripeBilling();

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

            $Invoice->update(array('InvoiceStatus' => Invoice::PAID));
            $paymentdata['EmailTemplate'] 		= 	EmailTemplate::where(["SystemType"=>EmailTemplate::InvoicePaidNotificationTemplate])->first();
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

}