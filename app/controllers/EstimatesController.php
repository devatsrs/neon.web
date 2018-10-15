<?php

class EstimatesController extends \BaseController {
	
	public function ajax_datagrid_total()
	{		
        $data 						 = 	Input::all();
		$data['iDisplayStart'] 		 =	0;
        $data['iDisplayStart'] 		+=	1;
		$data['iSortCol_0']			 =  0;
		$data['sSortDir_0']			 =  strtoupper('desc');
        $companyID 					 =  User::get_companyID();
        $columns 					 =  ['EstimateID','AccountName','EstimateNumber','IssueDate','GrandTotal','PendingAmount','EstimateStatus','EstimateID'];
        $data['IssueDateStart'] 	 =  empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd']        =  empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];
        $sort_column 				 =  $columns[$data['iSortCol_0']];
		
       $query = "call prc_getEstimate (".$companyID.",".intval($data['AccountID']).",'".$data['EstimateNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."','".$data['EstimateStatus']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',".intval($data['CurrencyID'])."";
		
        if(isset($data['Export']) && $data['Export'] == 1)
		{
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            Excel::create('Estimate', function ($excel) use ($excel_data)
			{
                $excel->sheet('Estimate', function ($sheet) use ($excel_data)
				{
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
		
    
        $query 	.=	',0)';
    	
		$result   = DataTableSql::of($query,'sqlsrv2')->getProcResult(array('ResultCurrentPage','Total_grand_field'));
		$result2  = $result['data']['Total_grand_field'][0]->total_grand;
		$result4  = array(
			"total_grand"=>$result['data']['Total_grand_field'][0]->currency_symbol.$result['data']['Total_grand_field'][0]->total_grand
		);
		
		return json_encode($result4,JSON_NUMERIC_CHECK);	
	
	}

    public function ajax_datagrid($type)
	{
        $data 						 = 	Input::all();
        $data['iDisplayStart'] 		+=	1;
        $companyID 					 =  User::get_companyID();
        $columns 					 =  ['EstimateID','AccountName','EstimateNumber','EstimateID','GrandTotal','EstimateStatus','EstimateID','converted'];   
        $data['IssueDateStart'] 	 =  empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd']        =  empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];
        $sort_column 				 =  $columns[$data['iSortCol_0']];
        $data['CurrencyID'] = empty($data['CurrencyID'])?'0':$data['CurrencyID'];
		
        $query = "call prc_getEstimate (".$companyID.",".intval($data['AccountID']).",'".$data['EstimateNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."','".$data['EstimateStatus']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".strtoupper($data['sSortDir_0'])."',".intval($data['CurrencyID'])."";
		
        if(isset($data['Export']) && $data['Export'] == 1)
		{
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
			
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Estimate.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Estimate.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
           /* Excel::create('Estimate', function ($excel) use ($excel_data)
			{
                $excel->sheet('Estimate', function ($sheet) use ($excel_data)
				{
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
		

        $query .=',0)';
        //echo $query;exit;
        $result =  DataTableSql::of($query,'sqlsrv2')->make();
		return $result;
    }
	
    /**
     * Display a listing of the resource.
     * GET /estimates
     *
     * @return Response
     */
    public function index()
    {
        $companyID 				= 	User::get_companyID();
        $DefaultCurrencyID    	=   Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $accounts 				= 	Account::getAccountIDList();		
        $estimate_status_json 	= 	json_encode(Estimate::get_estimate_status());	
        return View::make('estimates.index',compact('accounts','estimate_status_json','DefaultCurrencyID'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /estimates/create
     *
     * @return Response
     */
    public function create()
    {
        $CompanyID = User::get_companyID();
        $accounts = Account::getAccountIDList();
        $products = Product::getProductDropdownList($CompanyID);
        $taxes 	  = TaxRate::getTaxRateDropdownIDListForInvoice(0,$CompanyID);
		$BillingClass = BillingClass::getDropdownIDList($CompanyID);
        //$gateway_product_ids = Product::getGatewayProductIDs();
        $itemtypes 	= 	ItemType::getItemTypeDropdownList($CompanyID);
        return View::make('estimates.create',compact('accounts','itemtypes','products','taxes','BillingClass'));

    }

    /**
     *
     * */
    public function edit($id)
	{
        //$str = preg_replace('/^INV/', '', 'INV021000');;
        if($id > 0)
		{
			
            $Estimate 					= 	 Estimate::find($id);
            $CompanyID = $Estimate->CompanyID;
			$EstimateBillingClass 		=	 Estimate::GetEstimateBillingClass($Estimate);
            $EstimateDetail 			=	 EstimateDetail::where(["EstimateID"=>$id])->get();
            $accounts 					= 	 Account::getAccountIDList();
            $products 					= 	 Product::getProductDropdownList($CompanyID);
            $Account 					= 	 Account::where(["AccountID" => $Estimate->AccountID])->select(["AccountName","BillingEmail","CurrencyId"])->first(); //"TaxRateID","RoundChargesAmount","InvoiceTemplateID"
            $CurrencyID 				= 	 !empty($Estimate->CurrencyID)?$Estimate->CurrencyID:$Account->CurrencyId;
            $RoundChargesAmount 		= 	 get_round_decimal_places($Estimate->AccountID);
            $EstimateTemplateID 		=	 BillingClass::getInvoiceTemplateID($EstimateBillingClass);
            $EstimateNumberPrefix 		= 	 ($EstimateTemplateID>0)?InvoiceTemplate::find($EstimateTemplateID)->EstimateNumberPrefix:'';
            $Currency 					= 	 Currency::find($CurrencyID);
            $CurrencyCode 				= 	 !empty($Currency)?$Currency->Code:'';
            $CompanyName 				= 	 Company::getName($CompanyID);
            $taxes 						= 	 TaxRate::getTaxRateDropdownIDListForInvoice(0,$CompanyID);
			$EstimateAllTax 			= 	 DB::connection('sqlsrv2')->table('tblEstimateTaxRate')->where(["EstimateID"=>$id,"EstimateTaxType"=>1])->get();
			$BillingClass				=    BillingClass::getDropdownIDList($CompanyID);
            $itemtypes 	= 	ItemType::getItemTypeDropdownList($CompanyID);
            return View::make('estimates.edit', compact( 'id','itemtypes', 'Estimate','EstimateDetail','EstimateTemplateID','EstimateNumberPrefix',  'CurrencyCode','CurrencyID','RoundChargesAmount','accounts', 'products', 'taxes','CompanyName','Account','EstimateAllTax','BillingClass','EstimateBillingClass'));
        }
    }

    /**
     * Store Invoice
     */
    public function store()
	{
        $data = Input::all(); 
        if($data)
		{  
            $companyID 						=   User::get_companyID();
            $CreatedBy 						= 	User::get_user_full_name();
            $isAutoEstimateNumber		    =   true;			
			
			$EstimateData 					= 	array();
            if(!empty($data["EstimateNumber"]))
			{
                $isAutoEstimateNumber 			=  false;
				$EstimateData["EstimateNumber"] =  $data["EstimateNumber"];
            }
			 if(isset($data['BillingClassID']) && $data['BillingClassID']>0){  
			 $InvoiceTemplateID  			= 	BillingClass::getInvoiceTemplateID($data['BillingClassID']);
			$EstimateData["EstimateNumber"] = 	$LastEstimateNumber = ($isAutoEstimateNumber)?InvoiceTemplate::getNextEstimateNumber($InvoiceTemplateID):$data["EstimateNumber"];
			
			 }
            
            $EstimateData["CompanyID"] 		= 	$companyID;
            $EstimateData["AccountID"] 		= 	intval($data["AccountID"]);
            $EstimateData["Address"] 		= 	$data["Address"];           
            $EstimateData["IssueDate"] 		= 	$data["IssueDate"];
            $EstimateData["PONumber"] 		= 	$data["PONumber"];
            $EstimateData["SubTotal"] 		= 	str_replace(",","",isset($data["SubTotalOnOffCharge"])?$data["SubTotalOnOffCharge"]:0)+str_replace(",","",isset($data["SubTotalSubscription"])?$data["SubTotalSubscription"]:0);
            //$EstimateData["TotalDiscount"] 	= 	str_replace(",","",$data["TotalDiscount"]);
			$EstimateData["TotalDiscount"] 	= 	0;
            $EstimateData["TotalTax"] 		= 	str_replace(",","",$data["TotalTax"]);
            $EstimateData["GrandTotal"] 	= 	floatval(str_replace(",","",$data["GrandTotalEstimate"]));
            $EstimateData["CurrencyID"] 	= 	$data["CurrencyID"];
            $EstimateData["EstimateStatus"] = 	Estimate::DRAFT;
            $EstimateData["Note"] 			= 	$data["Note"];
            $EstimateData["Terms"] 			= 	$data["Terms"];
            $EstimateData["FooterTerm"] 	=	$data["FooterTerm"];
            $EstimateData["CreatedBy"] 		= 	$CreatedBy;
			$EstimateData['EstimateTotal'] 	=  str_replace(",","",$data["GrandTotal"]);
			//$EstimateData["converted"] 		= 	'N';
			$EstimateData['BillingClassID'] =  $data["BillingClassID"];  
            ///////////
            $rules = array(
                'CompanyID' => 'required',
                'AccountID' => 'required|integer|min:1',
                'Address' => 'required',
				'BillingClassID'=> 'required',
                'EstimateNumber' => 'required|unique:tblEstimate,EstimateNumber,NULL,EstimateID,CompanyID,'.$companyID,
                'IssueDate' => 'required',
                'CurrencyID' => 'required',
                'GrandTotal' => 'required',
            );
            $message = ['BillingClassID.required'=>'Billing Class field is required','AccountID'=>'Client field is required','AccountID.min'=>'Client field is required'];
			
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($EstimateData, $rules,$message);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails())
			{
                return json_validator_response($validator);
            }

            if(empty($data["EstimateDetail"])) {
                return json_encode(["status"=>"failed","message"=>"Please select atleast one item."]);
            }

            try
			{	
                DB::connection('sqlsrv2')->beginTransaction();
                $Estimate = Estimate::create($EstimateData);
                //Store Last Estimate Number.
                
				if($isAutoEstimateNumber) {
                    InvoiceTemplate::find($InvoiceTemplateID)->update(array("LastEstimateNumber" => $LastEstimateNumber ));
                }
				
                $EstimateDetailData = $EstimateItemTaxRates = $EstimateSubscriptionTaxRates = $EstimateAllTaxRates = array();

                foreach($data["EstimateDetail"] as $field => $detail)
				{
                    $i	=	0;
                    foreach($detail as $value)
					{
                        if(in_array($field,["Price","Discount","TaxAmount","LineTotal"]))
						{
                            $EstimateDetailData[$i][$field] = str_replace(",","",$value);
                        }
                        else if($field == "ProductID")
                        {
                            if(!empty($value)) {
                                $pid = explode('-',$value);
                                $EstimateDetailData[$i][$field] = $pid[1];
                            } else {
                                $EstimateDetailData[$i][$field] = "";
                            }
                        }
						else
						{
                            $EstimateDetailData[$i][$field] = $value;
                        }
						
                        $EstimateDetailData[$i]["EstimateID"] 	= 	$Estimate->EstimateID;
                        $EstimateDetailData[$i]["created_at"] 	= 	date("Y-m-d H:i:s");
                        $EstimateDetailData[$i]["CreatedBy"] 	= 	$CreatedBy;
						$EstimateDetailData[$i]["Discount"] 	= 	0;
						
                       /* if($field == 'TaxRateID'){
                            $EstimateTaxRates[$i][$field] = $value;
                            $EstimateTaxRates[$i]['Title'] = TaxRate::getTaxName($value);
                            $EstimateTaxRates[$i]["created_at"] = date("Y-m-d H:i:s");
                            $EstimateTaxRates[$i]["EstimateID"] = $Estimate->EstimateID;
                        }
                        if($field == 'TaxAmount'){
                            $EstimateTaxRates[$i][$field] = str_replace(",","",$value);
                        }*/
                        if(empty($EstimateDetailData[$i]['ProductID']))
						{
                            unset($EstimateDetailData[$i]);
                        }
                        $i++;
                    }
                }
				
				//product tax
            	if(isset($data['ProductTax']) && is_array($data['ProductTax']))
				{					
					if(isset($data['ProductTax']['item']) && is_array($data['ProductTax']['item']))
					{
						foreach($data['ProductTax']['item'] as $j => $taxdata){
							$EstimateItemTaxRates[$j]['TaxRateID'] 		= 	$j;
							$EstimateItemTaxRates[$j]['Title'] 			= 	TaxRate::getTaxName($j);
							$EstimateItemTaxRates[$j]["created_at"] 	= 	date("Y-m-d H:i:s");
							$EstimateItemTaxRates[$j]["EstimateID"] 	= 	$Estimate->EstimateID;
							$EstimateItemTaxRates[$j]["TaxAmount"] 		= 	$taxdata;
						}
					}
					
					if(isset($data['ProductTax']['subscription']) && is_array($data['ProductTax']['subscription']))
					{
						foreach($data['ProductTax']['subscription'] as $j => $taxdata){
							$EstimateSubscriptionTaxRates[$j]['TaxRateID'] 			= 	$j;
							$EstimateSubscriptionTaxRates[$j]['Title'] 				= 	TaxRate::getTaxName($j);
							$EstimateSubscriptionTaxRates[$j]["created_at"] 		= 	date("Y-m-d H:i:s");
							$EstimateSubscriptionTaxRates[$j]["EstimateID"] 		= 	$Estimate->EstimateID;
							$EstimateSubscriptionTaxRates[$j]["TaxAmount"] 			= 	$taxdata;
							$EstimateSubscriptionTaxRates[$j]["EstimateTaxType"] 	= 	2;
						}
					}
				}
				
				//estimate tax
				if(isset($data['EstimateTaxes']) && is_array($data['EstimateTaxes'])){
					foreach($data['EstimateTaxes']['field'] as  $p =>  $EstimateTaxes){
                        if(!empty($EstimateTaxes)) {
                            $EstimateAllTaxRates[$p]['TaxRateID'] = $EstimateTaxes;
                            $EstimateAllTaxRates[$p]['Title'] = TaxRate::getTaxName($EstimateTaxes);
                            $EstimateAllTaxRates[$p]["created_at"] = date("Y-m-d H:i:s");
                            $EstimateAllTaxRates[$p]["EstimateTaxType"] = 1;
                            $EstimateAllTaxRates[$p]["EstimateID"] = $Estimate->EstimateID;
                            $EstimateAllTaxRates[$p]["TaxAmount"] = $data['EstimateTaxes']['value'][$p];
                        }
					}
				}
				
                /*$EstimateItemTaxRates 	 		 = 	merge_tax($EstimateItemTaxRates);
				$EstimateSubscriptionTaxRates 	 = 	merge_tax($EstimateSubscriptionTaxRates);
				$EstimateAllTaxRates 			 = 	merge_tax($EstimateAllTaxRates);*/
				
				
                $EstimateLogData = array();
                $EstimateLogData['EstimateID']= $Estimate->EstimateID;
                $EstimateLogData['Note']= 'Created By '.$CreatedBy;
                $EstimateLogData['created_at']= date("Y-m-d H:i:s");
                $EstimateLogData['EstimateLogStatus']= EstimateLog::CREATED;
                EstimateLog::insert($EstimateLogData);
				
                /*if(!empty($EstimateItemTaxRates)) { //product item tax
                    DB::connection('sqlsrv2')->table('tblEstimateTaxRate')->insert($EstimateItemTaxRates);
                }
				
				if(!empty($EstimateSubscriptionTaxRates)) { //product subscription tax
                    DB::connection('sqlsrv2')->table('tblEstimateTaxRate')->insert($EstimateSubscriptionTaxRates);
                }*/
				
				 if(!empty($EstimateAllTaxRates)) { //estimate tax
                     EstimateTaxRate::insert($EstimateAllTaxRates);
                }
//Log::info(print_r($EstimateDetailData,true));
                if (!empty($EstimateDetailData) && EstimateDetail::insert($EstimateDetailData))
				{
                    $InvoiceTaxRates1=EstimateTaxRate::getEstimateTaxRateByProductDetail($Estimate->EstimateID);
                    if(!empty($InvoiceTaxRates1)) { //Invoice tax
                        EstimateTaxRate::insert($InvoiceTaxRates1);
                    }
                    $pdf_path = Estimate::generate_pdf($Estimate->EstimateID);
					
                    if (empty($pdf_path))
					{
                        $error['message'] = 'Failed to generate Estimate PDF File';
                        $error['status']  = 'failure';
                        return $error;
                    }
					else
					{
                        $Estimate->update(["PDF" => $pdf_path]);
                    }


                    DB::connection('sqlsrv2')->commit();

                    return Response::json(array("status" => "success", "message" => "Estimate Successfully Created",'LastID'=>$Estimate->EstimateID,'redirect' => URL::to('/estimate/'.$Estimate->EstimateID.'/edit')));
                }
				else
				{
                    DB::connection('sqlsrv2')->rollback();
                    return Response::json(array("status" => "failed", "message" => "Problem Creating Estimate."));
                }
            }
			catch (Exception $e)
			{
                DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Problem Creating Estimate. \n" . $e->getMessage()));
            }
        }
    }

    /**
     * Store Estimate
     */
    public function update($id)
	{
        $data = Input::all();
        if(!empty($data) && $id > 0)
		{
            $Estimate 						= 	Estimate::find($id);
            $companyID 						= 	User::get_companyID();
            $CreatedBy 						= 	User::get_user_full_name();
            $EstimateData 					=	array();
            $EstimateData["CompanyID"] 		= 	$companyID;
            $EstimateData["AccountID"] 		= 	$data["AccountID"];
            $EstimateData["Address"] 		= 	$data["Address"];
            $EstimateData["EstimateNumber"] = 	$data["EstimateNumber"];
            $EstimateData["IssueDate"] 		= 	$data["IssueDate"];
            $EstimateData["PONumber"] 		= 	$data["PONumber"];
            $EstimateData["SubTotal"] 		= 	str_replace(",","",isset($data["SubTotalOnOffCharge"])?$data["SubTotalOnOffCharge"]:0)+str_replace(",","",isset($data["SubTotalSubscription"])?$data["SubTotalSubscription"]:0);
            //$EstimateData["TotalDiscount"] 	= 	str_replace(",","",$data["TotalDiscount"]);
			$EstimateData["TotalDiscount"] 	= 	0;
            $EstimateData["TotalTax"] 		= 	str_replace(",","",$data["TotalTax"]);
            $EstimateData["GrandTotal"] 	= 	floatval(str_replace(",","",$data["GrandTotalEstimate"]));
            $EstimateData["CurrencyID"] 	= 	$data["CurrencyID"];
            $EstimateData["Note"] 			= 	$data["Note"];
            $EstimateData["Terms"] 			= 	$data["Terms"];
            $EstimateData["FooterTerm"] 	= 	$data["FooterTerm"];
            $EstimateData["ModifiedBy"] 	= 	$CreatedBy;
			$EstimateData['EstimateTotal'] 	=   str_replace(",","",$data["GrandTotal"]);
            ///////////

            $rules = array(
                'CompanyID' => 'required',
                'AccountID' => 'required',
                'Address' => 'required',
                'EstimateNumber' => 'required|unique:tblEstimate,EstimateNumber,'.$id.',EstimateID,CompanyID,'.$companyID,
                'IssueDate' => 'required',
                'CurrencyID' => 'required',
                'GrandTotal' => 'required',
            );
			
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');
			
            $validator = Validator::make($EstimateData, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails())
			{
                return json_validator_response($validator);
            }

            if(empty($data["EstimateDetail"])) {
                return json_encode(["status"=>"failed","message"=>"Please select atleast one item."]);
            }

            try
			{
                DB::connection('sqlsrv2')->beginTransaction();
                if(isset($Estimate->EstimateID))
				{
                    $Extralognote = '';
                    if($Estimate->GrandTotal != $EstimateData['GrandTotal'])
					{
                        $Extralognote = ' Total '.$Estimate->GrandTotal.' To '.$EstimateData['GrandTotal'];
                    }
					
                    $Estimate->update($EstimateData);
					
                     $EstimateDetailData = $EstimateItemTaxRates = $EstimateSubscriptionTaxRates = $EstimateAllTaxRates = array();
					
                    //Delete all Estimate Data and then Recreate.
                    EstimateDetail::where(["EstimateID" => $Estimate->EstimateID])->delete();
                    DB::connection('sqlsrv2')->table('tblEstimateTaxRate')->where(["EstimateID" => $Estimate->EstimateID])->delete();
                    if (isset($data["EstimateDetail"]))
					{
                        foreach ($data["EstimateDetail"] as $field => $detail)
						{
                            $i = 0;
                            foreach ($detail as $value)
							{
                                if( in_array($field,["Price","Discount","TaxAmount","LineTotal"]))
								{
                                    $EstimateDetailData[$i][$field] = str_replace(",","",$value);
                                }
                                else if($field == "ProductID")
                                {
                                    if(!empty($value)) {
                                        $pid = explode('-',$value);
                                        $EstimateDetailData[$i][$field] = $pid[1];
                                    } else {
                                        $EstimateDetailData[$i][$field] = "";
                                    }
                                }
								else
								{
                                    $EstimateDetailData[$i][$field] = $value;
                                }
								
                                $EstimateDetailData[$i]["EstimateID"]  	= 	$Estimate->EstimateID;
                                $EstimateDetailData[$i]["created_at"]  	= 	date("Y-m-d H:i:s");
                                $EstimateDetailData[$i]["updated_at"]  	= 	date("Y-m-d H:i:s");
                                $EstimateDetailData[$i]["CreatedBy"]   	= 	$CreatedBy;
                                $EstimateDetailData[$i]["ModifiedBy"]  	= 	$CreatedBy;
								$EstimateDetailData[$i]["Discount"] 	= 	0;
                                
                                /*if($field == 'TaxRateID'){
                                    $EstimateTaxRates[$i][$field] = $value;
                                    $EstimateTaxRates[$i]['Title'] = TaxRate::getTaxName($value);
                                    $EstimateTaxRates[$i]["created_at"] = date("Y-m-d H:i:s");
                                    $EstimateTaxRates[$i]["EstimateID"] = $Estimate->EstimateID;
                                }
                                if($field == 'TaxAmount'){
                                    $EstimateTaxRates[$i][$field] = str_replace(",","",$value);
                                }*/
								if(isset($EstimateDetailData[$i]["EstimateDetailID"]))
								{
                                    unset($EstimateDetailData[$i]["EstimateDetailID"]);
                                }
								
                                if(empty($EstimateDetailData[$i]['ProductID']))
								{
                                    unset($EstimateDetailData[$i]);
                                }
                                $i++;
                            }
                        }
						
						//product tax
						if(isset($data['ProductTax']) && is_array($data['ProductTax']))
						{					
							if(isset($data['ProductTax']['item']) && is_array($data['ProductTax']['item']))
							{
								foreach($data['ProductTax']['item'] as $j => $taxdata){
									$EstimateItemTaxRates[$j]['TaxRateID'] 		= 	$j;
									$EstimateItemTaxRates[$j]['Title'] 			= 	TaxRate::getTaxName($j);
									$EstimateItemTaxRates[$j]["created_at"] 	= 	date("Y-m-d H:i:s");
									$EstimateItemTaxRates[$j]["EstimateID"] 	= 	$Estimate->EstimateID;
									$EstimateItemTaxRates[$j]["TaxAmount"] 		= 	$taxdata;
								}
							}
							
							if(isset($data['ProductTax']['subscription']) && is_array($data['ProductTax']['subscription']))
							{
								foreach($data['ProductTax']['subscription'] as $j => $taxdata){
									$EstimateSubscriptionTaxRates[$j]['TaxRateID'] 			= 	$j;
									$EstimateSubscriptionTaxRates[$j]['Title'] 				= 	TaxRate::getTaxName($j);
									$EstimateSubscriptionTaxRates[$j]["created_at"] 		= 	date("Y-m-d H:i:s");
									$EstimateSubscriptionTaxRates[$j]["EstimateID"] 		= 	$Estimate->EstimateID;
									$EstimateSubscriptionTaxRates[$j]["TaxAmount"] 			= 	$taxdata;
									$EstimateSubscriptionTaxRates[$j]["EstimateTaxType"] 	= 	2;
								}
							}
						}
						
							//estimate tax
						if(isset($data['EstimateTaxes']) && is_array($data['EstimateTaxes'])){
							foreach($data['EstimateTaxes']['field'] as  $p =>  $EstimateTaxes){
                                if(!empty($EstimateTaxes)) {
                                    $EstimateAllTaxRates[$p]['TaxRateID'] = $EstimateTaxes;
                                    $EstimateAllTaxRates[$p]['Title'] = TaxRate::getTaxName($EstimateTaxes);
                                    $EstimateAllTaxRates[$p]["created_at"] = date("Y-m-d H:i:s");
                                    $EstimateAllTaxRates[$p]["EstimateTaxType"] = 1;
                                    $EstimateAllTaxRates[$p]["EstimateID"] = $Estimate->EstimateID;
                                    $EstimateAllTaxRates[$p]["TaxAmount"] = $data['EstimateTaxes']['value'][$p];
                                }
							}
						}

                        /*$EstimateItemTaxRates 	 		 = 	merge_tax($EstimateItemTaxRates);
						$EstimateSubscriptionTaxRates 	 = 	merge_tax($EstimateSubscriptionTaxRates);
						$EstimateAllTaxRates 			 =  merge_tax($EstimateAllTaxRates);*/
						
                         /*if(!empty($EstimateItemTaxRates)) { //product item tax
							DB::connection('sqlsrv2')->table('tblEstimateTaxRate')->insert($EstimateItemTaxRates);
						}
						
						if(!empty($EstimateSubscriptionTaxRates)) { //product subscription tax
							DB::connection('sqlsrv2')->table('tblEstimateTaxRate')->insert($EstimateSubscriptionTaxRates);
						}*/
						
						if(!empty($EstimateAllTaxRates)) {
                            EstimateTaxRate::insert($EstimateAllTaxRates);
                        }

                        if (!empty($EstimateDetailData) && EstimateDetail::insert($EstimateDetailData))
						{
                            $InvoiceTaxRates1=EstimateTaxRate::getEstimateTaxRateByProductDetail($Estimate->EstimateID);
                            if(!empty($InvoiceTaxRates1)) { //Invoice tax
                                EstimateTaxRate::insert($InvoiceTaxRates1);
                            }
                            $pdf_path = Estimate::generate_pdf($Estimate->EstimateID);
							
                            if (empty($pdf_path))
							{
                                $error['message'] = 'Failed to generate Estimate PDF File';
                                $error['status'] = 'failure';
                                return $error;
                            }
							else
							{
                                $Estimate->update(["PDF" => $pdf_path]);
                            }

                            DB::connection('sqlsrv2')->commit();
                            return Response::json(array("status" => "success", "message" => "Estimate Successfully Updated", 'LastID' => $Estimate->EstimateID));
                        }
                        else
                        {
                            DB::connection('sqlsrv2')->rollback();
                            return Response::json(array("status" => "failed", "message" => "Problem Updating Estimate."));
                        }
                    }
					else
					{
                        return Response::json(array("status" => "success", "message" => "Estimate Successfully Updated, There is no product in Estimate", 'LastID' => $Estimate->EstimateID));
                    }
                }
            }
			catch (Exception $e)
			{
				DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Problem Updating Estimate. \n " . $e->getMessage()));
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
                            $TaxRates = TaxRate::where(array('CompanyID' => User::get_companyID(), "TaxType" => TaxRate::TAX_ALL))->select(['TaxRateID', 'Title', 'Amount'])->first();
                            if(!empty($TaxRates)){
                                $TaxRates->toArray();
                            }
                            //$AccountTaxRate = explode(",", $AccountBilling->TaxRateId);
							//$AccountTaxRate = explode(",",AccountBilling::getTaxRate($AccountID));
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
                            //$AccountTaxRate = explode(",",AccountBilling::getTaxRate($AccountID));
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
		
                $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
                /* for item invoice generate - invoice to address as invoice template */
				
				if(isset($InvoiceTemplateID) && $InvoiceTemplateID > 0) {
                	$message = $InvoiceTemplate->InvoiceTo;
                	$replace_array = Invoice::create_accountdetails($Account);
	                $text = Invoice::getInvoiceToByAccount($message,$replace_array);
    	            $EstimateToAddress = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
				    $Terms = $InvoiceTemplate->Terms;
    	            $FooterTerm = $InvoiceTemplate->FooterTerm;
					$EstimateTemplateID = $InvoiceTemplateID;
				}
				else
				{
					$InvoiceToAddress 	= 	'';
				    $Terms 				= 	'';
    	            $FooterTerm 		= 	'';
				}
				$BillingClassID     =   AccountBilling::getBillingClassID($data['account_id']);				
                $return = ['Terms','FooterTerm','Currency','CurrencyId','Address','EstimateTemplateID','AccountTaxRate','EstimateToAddress','BillingClassID'];
            
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
				$EstimateTemplateID = $InvoiceTemplateID;
				$AccountTaxRate  = BillingClass::getTaxRateType($data['BillingClassID'],TaxRate::TAX_ALL);
				$return = ['Terms','FooterTerm','EstimateTemplateID','InvoiceToAddress','AccountTaxRate'];
			}else{
			return Response::json(array("status" => "failed", "message" => "You cannot create estimate as no Invoice Template assigned to this account." ));
		   }
            return Response::json(compact($return));
        }
    }

    public function delete($id)
    {
        if( $id > 0)
		{
            try
			{
                DB::connection('sqlsrv2')->beginTransaction();
                EstimateDetail::where(["EstimateID"=>$id])->delete();
                EstimateTaxRate::where(["EstimateID"=>$id])->delete();
                EstimateLog::where(["EstimateID"=>$id])->delete();
                Estimate::find($id)->delete();
                DB::connection('sqlsrv2')->commit();
                return Response::json(array("status" => "success", "message" => "Estimate Successfully Deleted"));

            }
			catch (Exception $e)
			{
                DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Estimate is in Use, You cant delete this Currently. \n" . $e->getMessage() ));
            }

        }
    }
	
	public function delete_bulk()
    { 	
		 $data = Input::all();
		 
		 $EstimateIDs 				=	 array_filter(explode(',',$data['del_ids']),'intval');
		 
         if(count($EstimateIDs)>0)
		 {				 
            try
			{
                DB::connection('sqlsrv2')->beginTransaction();
				EstimateDetail::whereIn('EstimateID',$EstimateIDs)->delete();
                EstimateTaxRate::whereIn("EstimateID",$EstimateIDs)->delete();
                EstimateLog::whereIn("EstimateID",$EstimateIDs)->delete();
				Estimate::whereIn('EstimateID',$EstimateIDs)->delete();
                DB::connection('sqlsrv2')->commit();
                return Response::json(array("status" => "success", "message" => "Estimate(s) Successfully Deleted"));
            }
			catch (Exception $e)
			{
                DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Estimate(s) is in Use, You cant delete this Currrently. \n" . $e->getMessage() ));
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
    public function estimate_preview($id)
	{

        $Estimate = Estimate::find($id);
        if(!empty($Estimate))
		{
            $EstimateDetail 	= 	EstimateDetail::where(["EstimateID" => $id])->get();
            $Account 			= 	Account::find($Estimate->AccountID);
            $Currency 			= 	Currency::find($Account->CurrencyId);
            $CurrencyCode 		= 	!empty($Currency) ? $Currency->Code : '';
			$CurrencySymbol 	= 	Currency::getCurrencySymbol($Account->CurrencyId);
            $estimate_status 	= 	 Estimate::get_estimate_status();
            $EstimateStatus =   $estimate_status[$Estimate->EstimateStatus];
            $EstimateComments =   EstimateLog::get_comments_count($id);
            return View::make('estimates.estimates_preview', compact('Estimate', 'EstimateDetail', 'Account', 'EstimateTemplate', 'CurrencyCode', 'logo','CurrencySymbol','EstimateStatus','EstimateComments'));
        }
    }

    public function estimate_cview($id)
    {

        $Estimate = Estimate::find($id);
        if(!empty($Estimate))
        {
            $EstimateDetail 	= 	EstimateDetail::where(["EstimateID" => $id])->get();
            $Account 			= 	Account::find($Estimate->AccountID);
            $Currency 			= 	Currency::find($Account->CurrencyId);
            $CurrencyCode 		= 	!empty($Currency) ? $Currency->Code : '';
            $CurrencySymbol 	= 	Currency::getCurrencySymbol($Account->CurrencyId);
            $estimate_status 	= 	 Estimate::get_customer_estimate_status($Estimate->CompanyID);
            $EstimateStatus =   $estimate_status[$Estimate->EstimateStatus];
            $EstimateComments =   EstimateLog::get_comments_count($id);
            return View::make('estimates.estimates_cview', compact('Estimate', 'EstimateDetail', 'Account', 'EstimateTemplate', 'CurrencyCode', 'logo','CurrencySymbol','EstimateStatus','EstimateComments'));
        }
    }

    // not in use
    public function pdf_view($id)
	{


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

    public function cview($id) 
	{
        $account_inv = explode('-',$id);
        if(isset($account_inv[0]) && intval($account_inv[0]) > 0 && isset($account_inv[1]) && intval($account_inv[1]) > 0  ) {
            $AccountID = intval($account_inv[0]);
            $EstimateID = intval($account_inv[1]);
            $Estimate = Estimate::where(["EstimateID"=>$EstimateID,"AccountID"=>$AccountID])->first();
            if(count($Estimate)>0)
			{
				
                $estimateloddata = array();
                $estimateloddata['Note']= 'Viewed By Unknown';
                if(!empty($_GET['email']))
				{
                    $estimateloddata['Note']= 'Viewed By '. $_GET['email'];
                }

                $estimateloddata['EstimateID']= $Estimate->EstimateID;
                $estimateloddata['created_at']= date("Y-m-d H:i:s");
                $estimateloddata['EstimateLogStatus']= EstimateLog::VIEWED;
                EstimateLog::insert($estimateloddata);
				
                return self::estimate_cview($EstimateID);
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

    //Generate Item Based Estimate PDF
    public function generate_pdf($id){
        if($id>0) {
            $Estimate 		=	 Estimate::find($id);
            $EstimateDetail = 	 EstimateDetail::where(["EstimateID" => $id])->get();
            $Account = Account::find($Estimate->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency)?$Currency->Code:'';
            $InvoiceTemplateID = Estimate::GetEstimateInvoiceTemplateID($Estimate);
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            if (empty($InvoiceTemplate->CompanyLogoUrl)) {
                $as3url = 'http://placehold.it/250x100';
            } else {
                $as3url = (AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key));
            }
            $logo_path = CompanyConfiguration::get('UPLOAD_PATH') . '/logo/' . $Account->CompanyId;
            @mkdir($logo_path, 0777, true);
            RemoteSSH::run("chmod -R 777 " . $logo_path);
            $logo = $logo_path  . '/'  . basename($as3url);
            file_put_contents($logo, file_get_contents($as3url));
            $usage_data = array();
            $file_name = 'Estimate--' . date('d-m-Y') . '.pdf';
            if($InvoiceTemplate->InvoicePages == 'single_with_detail') {
                foreach ($EstimateDetail as $Detail) {
                    if (isset($Detail->StartDate) && isset($Detail->EndDate) && $Detail->StartDate != '1900-01-01' && $Detail->EndDate != '1900-01-01') {

                        $companyID = $Account->CompanyId;
                        $start_date = $Detail->StartDate;
                        $end_date = $Detail->EndDate;
                        $pr_name = 'call prc_getInvoiceUsage (';

                        $query = $pr_name . $companyID . ",'" . $Estimate->AccountID . "','" . $start_date . "','" . $end_date . "')";
                        DB::connection('sqlsrv2')->setFetchMode(PDO::FETCH_ASSOC);
                        $usage_data = DB::connection('sqlsrv2')->select($query);
                        $usage_data = json_decode(json_encode($usage_data), true);
                        $file_name =  'Estimate-From-' . Str::slug($start_date) . '-To-' . Str::slug($end_date) . '.pdf';
                        break;
                    }
                }
            }
			$print_type = 'Estimate';
            $body = View::make('estimates.pdf', compact('Estimate', 'EstimateDetail', 'Account', 'InvoiceTemplate', 'usage_data', 'CurrencyCode', 'logo','print_type'))->render();
            $destination_dir = CompanyConfiguration::get('UPLOAD_PATH') . '/'. AmazonS3::generate_path(AmazonS3::$dir['ESTIMATE_UPLOAD'],$Account->CompanyId) ;
            if (!file_exists($destination_dir)) {
                mkdir($destination_dir, 0777, true);
            }
            $save_path = $destination_dir .  GUID::generate().'-'. $file_name;
            PDF::loadHTML($body)->setPaper('a4')->setOrientation('potrait')->save($save_path);
            //@unlink($logo);
            return $save_path;
        }
    }

   

   
   
    public function  download_doc_file($id){
        $DocumentFile = Estimate::where(["EstimateID"=>$id])->pluck('Attachment');
        $Estimate = Estimate::find($id);
        $CompanyID = $Estimate->CompanyID;
        if(file_exists($DocumentFile)){
            download_file($DocumentFile);
        }else{
            $FilePath =  AmazonS3::preSignedUrl($DocumentFile,$CompanyID);
            if(file_exists($FilePath)){
                download_file($FilePath);
            }elseif(is_amazon($CompanyID) == true){
                header('Location: '.$FilePath);
            }
        }
        exit;
    }

    public function estimate_email($id)
	{
        $Estimate = Estimate::find($id);
        if(!empty($Estimate))
		{
            $Account 	 		= 	Account::find($Estimate->AccountID);
            $InvoiceTemplateID  =   Estimate::GetEstimateInvoiceTemplateID($Estimate);
            $Currency 	 		= 	Currency::find($Account->CurrencyId);
            $CompanyName 		= 	Company::getName();
            
			if (!empty($Currency))
			{
                $companyID = User::get_companyID();
                $templateData	 = 	EmailTemplate::getSystemEmailTemplate($companyID, Estimate::EMAILTEMPLATE, $Account->LanguageID);
				//$Subject	 	 = 	$templateData->Subject;
				//$Message 		 = 	$templateData->TemplateBody;		 		
				$data['EstimateURL']	=   URL::to('/estimate/'.$Estimate->AccountID.'-'.$Estimate->EstimateID.'/cview?email=#email');
				$Message				=	EmailsTemplates::SendEstimateSingle(Estimate::EMAILTEMPLATE,$Estimate->EstimateID,'body',$data);
				$Subject				=	EmailsTemplates::SendEstimateSingle(Estimate::EMAILTEMPLATE,$Estimate->EstimateID,"subject",$data);
				
				$response_api_extensions 	=    Get_Api_file_extentsions();
			    if(isset($response_api_extensions->headers)){ return	Redirect::to('/logout'); 	}	
			    $response_extensions		=	json_encode($response_api_extensions['allowed_extensions']); 
			    $max_file_size				=	get_max_file_size();	
				
				if(!empty($Subject) && !empty($Message)){
					$from	 = $templateData->EmailFrom;	
					return View::make('estimates.email', compact('Estimate', 'Account', 'Subject','Message','CompanyName','from','response_extensions','max_file_size'));
				}
				
				return Response::json(["status" => "failure", "message" => "Subject or message is empty"]);
            }
        }
    }
    public function send($id)
	{
        if($id)
		{
            set_time_limit(600); // 10 min time limit.
			
            $CreatedBy 					= 	User::get_user_full_name();
            $data 						= 	Input::all();
			$postdata 					= 	Input::all(); 
            $Estimate 					= 	Estimate::find($id);
            $Company 					= 	Company::find($Estimate->CompanyID);
            $CompanyName 				= 	$Company->CompanyName;
            $EstimateGenerationEmail 	= 	CompanySetting::getKeyVal('EstimateGenerationEmail');
            $EstimateGenerationEmail 	= 	($EstimateGenerationEmail =='Invalid Key')?$Company->Email:$EstimateGenerationEmail;
            $emailtoCustomer 			= 	CompanyConfiguration::get('EMAIL_TO_CUSTOMER');

			if(intval($emailtoCustomer) == 1)
			{
                $CustomerEmail = $data['Email'];
            }
			else
			{
                $CustomerEmail = $Company->Email;
            }
			
            $data['EmailTo'] 			= 	explode(",",$CustomerEmail);
            $data['EstimateURL'] 		= 	URL::to('/estimate/'.$Estimate->AccountID.'-'.$Estimate->EstimateID.'/cview');
            $data['AccountName'] 		= 	Account::find($Estimate->AccountID)->AccountName;
            $data['CompanyName'] 		= 	$CompanyName;
			
            $rules = array(
                'AccountName' => 'required',
                'EstimateURL' => 'required',
                'Subject'=>'required',
                'EmailTo'=>'required',
                'Message'=>'required',
                'CompanyName'=>'required',
            );
            
			$validator = Validator::make($data, $rules);
            
			if ($validator->fails())
			{
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
					$amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['EMAIL_ATTACHMENT'],'',$Estimate->CompanyID);
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
            $status 			= 	 0;
            $CustomerEmails 	=	 $data['EmailTo'];
			
            foreach($CustomerEmails as $singleemail)
			{
                $singleemail = trim($singleemail);
                if (filter_var($singleemail, FILTER_VALIDATE_EMAIL))
				{
                    $data['EmailTo'] 		= 	$singleemail;
                    $data['EstimateURL']	= 	URL::to('/estimate/'.$Estimate->AccountID.'-'.$Estimate->EstimateID.'/cview?email='.$singleemail);
					$body					=	EmailsTemplates::ReplaceEmail($singleemail,$postdata['Message']);
					$data['Subject']		=	$postdata['Subject'];
					//$body					=	EmailsTemplates::SendEstimateSingle(Estimate::EMAILTEMPLATE,$Estimate->EstimateID,'body',$data,$postdata);
					//$data['Subject']		=	EmailsTemplates::SendEstimateSingle(Estimate::EMAILTEMPLATE,$Estimate->EstimateID,"subject",$data,$postdata);
					
					if(isset($postdata['email_from']) && !empty($postdata['email_from']))
					{
						$data['EmailFrom']		=	$postdata['email_from'];	
					}else{
						$data['EmailFrom']		=	EmailsTemplates::GetEmailTemplateFrom(Estimate::EMAILTEMPLATE);
					}
                    $status 				= 		$this->sendEstimateMail($body,$data,0);
                }
            }
			
            if($status['status']==0)
			{
                $status['status'] = 'failure';
            }
			else
			{
                $status['status'] 					= "success";
                $Estimate->update(['EstimateStatus' => Estimate::SEND ]);

                $estimateloddata = array();
                $estimateloddata['EstimateID']= $Estimate->EstimateID;
                $estimateloddata['Note']= 'Sent By '.$CreatedBy;
                $estimateloddata['created_at']= date("Y-m-d H:i:s");
                $estimateloddata['EstimateLogStatus']= EstimateLog::SENT;
                EstimateLog::insert($estimateloddata);

                /*
                    Insert email log in account
                */
				$message_id 	=  isset($status['message_id'])?$status['message_id']:"";
                $logData = ['AccountID'=>$Estimate->AccountID,
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
            $Account = Account::find($Estimate->AccountID);
            if(!empty($Account->Owner))
            {
                $AccountManager 			 = 	User::find($Account->Owner);
                $EstimateGenerationEmail 	.= 	',' . $AccountManager->EmailAddress;
            }
			
            $sendTo 				= 	explode(",",$EstimateGenerationEmail);            
            $data['Subject'] 	   .= 	' ('.$Account->AccountName.')';//Added by Abubakar
            $data['EmailTo'] 		= 	$sendTo;
            $data['EstimateURL']	= 	URL::to('/estimate/'.$Estimate->EstimateID.'/estimate_preview');
			$body					=	EmailsTemplates::SendEstimateSingle(Estimate::EMAILTEMPLATE,$Estimate->EstimateID,'body',$data,$postdata);
			$data['Subject']		=	EmailsTemplates::SendEstimateSingle(Estimate::EMAILTEMPLATE,$Estimate->EstimateID,"subject",$data,$postdata);
			
			if(isset($postdata['email_from']) && !empty($postdata['email_from']))
			{
				$data['EmailFrom']		=	$postdata['email_from'];	
			}else{
				$data['EmailFrom']		=	EmailsTemplates::GetEmailTemplateFrom(Estimate::EMAILTEMPLATE);		
			}
			
			$StaffStatus 			= 	$this->sendEstimateMail($body,$data,0);
            
			if($StaffStatus['status']==0)
			{
                $status['message'] .= ', Enable to send email to staff : ' . $StaffStatus['message'];
            }

            return Response::json(array("status" => $status['status'], "message" => "".$status['message']));
        }
		else
		{
            return Response::json(["status" => "failure", "message" => "Problem Sending Estimate"]);
        }
    }

    function sendEstimateMail($view,$data,$type=1)
	{ 
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
				$status 			= 	sendMail($view,$data,$type);
            }
        }
        return $status;
    }
	
	function convert_estimate()
	{
		
        $data 				= 	 Input::all();
        $username 			=	 User::get_user_full_name();
        $estimate_status 	= 	 Estimate::get_estimate_status();
		$companyID 			=    User::get_companyID();
        
		$Estimate_data 		= 	Estimate::find($data['eid']);
		//if($Estimate_data->converted=='N')
		{
					 $query	  = 	"call prc_Convert_Invoices_to_Estimates (".$companyID.",'','','0000-00-00 00:00:00','0000-00-00 00:00:00','','".$data['eid']."',0)";  
						$results  = 	DB::connection('sqlsrv2')->select($query);
						$inv_id   = 	$results[0]->InvoiceID;
						$pdf_path = 	Invoice::generate_pdf($inv_id);
						Invoice::where(["InvoiceID" =>$inv_id])->update(["PDF" => $pdf_path]);
                        $Invoice = Invoice::find($inv_id);
                        //$BillingClassID = AccountBilling::getBillingClassID($Invoice->AccountID);
						$InvoiceTemplateID = Invoice::GetInvoiceTemplateID($Invoice);
                        InvoiceTemplate::where(array('InvoiceTemplateID'=>$InvoiceTemplateID))->update(array("LastInvoiceNumber" => $Invoice->InvoiceNumber));
		}
			
		return Response::json(array("status" => "success", "message" => "Estimate Successfully Updated"));			
	}

    
	public function estimate_change_Status()
	{
        $data 				= 	 Input::all();
        $username 			=	 User::get_user_full_name();
        $estimate_status 	= 	 Estimate::get_estimate_status();
		$companyID 			=    User::get_companyID();		
		$Estimate_data 		= 	 Estimate::find($data['EstimateIDs']);
		
		//if($Estimate_data->converted=='N')
		{
			if (Estimate::where('EstimateID',$data['EstimateIDs'])->update([ 'ModifiedBy'=>$username,'EstimateStatus' => $data['EstimateStatus']]))
			{
				return Response::json(array("status" => "success", "message" => "Estimate Successfully Updated"));
			}
			else
			{
				return Response::json(array("status" => "failed", "message" => "Problem Updating Estimate."));
			}
		}
		
    }

	
	public function estimate_change_Status_Bulk()
	{
		$data 						=  Input::all();
        $username 					=  User::get_user_full_name();
		$companyID 					=  User::get_companyID();
        $estimate_status 			=  Estimate::get_estimate_status();
        $EstimateIDs 				=  implode(',',$data['EstimateIDs']);
		$error						=  0;
		$data['IssueDateStart'] 	=  empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd']       =  empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];

		
        
			//convert all with criteria
			if($data['AllChecked']==1)
			{
				$query = "call prc_Convert_Invoices_to_Estimates (".$companyID.",'".$data['AccountID']."','".$data['EstimateNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."','".$data['EstimateStatus']."','',1)";		
				$results  = DB::connection('sqlsrv2')->select($query);
				
				foreach($results as $results_data)
				{
					$inv_id   = $results_data->InvoiceID;
					$pdf_path = Invoice::generate_pdf($inv_id);
				  	Invoice::where(["InvoiceID" =>$inv_id])->update(["PDF" => $pdf_path]);

                    $Invoice = Invoice::find($inv_id);
                	$InvoiceTemplateID = Invoice::GetInvoiceTemplateID($Invoice);
                    InvoiceTemplate::where(array('InvoiceTemplateID'=>$InvoiceTemplateID))->update(array("LastInvoiceNumber" => $Invoice->InvoiceNumber));
				}				
			}
			else
			{	
				//convert selected
				foreach($data['EstimateIDs'] as $EstimateIDs_data)
				{
					$Estimate_data = Estimate::find($EstimateIDs_data);
					if($Estimate_data->converted=='N')
					{
						 $query = "call prc_Convert_Invoices_to_Estimates (".$companyID.",'".$data['AccountID']."','".$data['EstimateNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."','".$data['EstimateStatus']."','".$EstimateIDs_data."',0)";
						 $results  = DB::connection('sqlsrv2')->select($query);
						$inv_id   = $results[0]->InvoiceID;
						$pdf_path = Invoice::generate_pdf($inv_id);
						Invoice::where(["InvoiceID" =>$inv_id])->update(["PDF" => $pdf_path]);
                        $Invoice = Invoice::find($inv_id);
                        $InvoiceTemplateID = Invoice::GetInvoiceTemplateID($Invoice);
                        InvoiceTemplate::where(array('InvoiceTemplateID'=>$InvoiceTemplateID))->update(array("LastInvoiceNumber" => $Invoice->InvoiceNumber));
					}
				}
				
			}
			
			if($error)
			{
				return Response::json(array("status" => "failed", "message" => "Problem Updating Estimate(s)."));
			}
			else
			{				
				return Response::json(array("status" => "success", "message" => "Estimate(s) Successfully Updated"));
			}
       
    }

    /*
     * Download Output File
     * */
    public function downloadUsageFile($id){
        //if( User::checkPermission('Job') && intval($id) > 0 ) {
        $OutputFilePath = Estimate::where("EstimateID", $id)->pluck("UsagePath");
        $FilePath 		= AmazonS3::preSignedUrl($OutputFilePath);
        if(file_exists($FilePath)){
            download_file($FilePath);
        }elseif(is_amazon() == true){
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
            $AccountID  = intval($account_inv[0]);
            $EstimateID = intval($account_inv[1]);
            $this->downloadUsageFile($EstimateID);
        }
    }
    
    public static function display_estimate($EstimateID)
	{
        $Estimate = Estimate::find($EstimateID);
        $PDFurl = '';
		
        if(is_amazon($Estimate->CompanyID) == true)
		{
            $PDFurl =  AmazonS3::preSignedUrl($Estimate->PDF);
        }
		else
		{
            $PDFurl = CompanyConfiguration::get('UPLOAD_PATH')."/".$Estimate->PDF;
        }
		
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="'.basename($PDFurl).'"');
        echo file_get_contents($PDFurl);
        exit;
    }
	
    public static function download_estimate($EstimateID)
	{
        $Estimate 	=  Estimate::find($EstimateID);
        $FilePath 	=  AmazonS3::preSignedUrl($Estimate->PDF);

        if(file_exists($FilePath))
        {
            download_file($FilePath);
        }
		elseif(is_amazon() == true)
        {
            header('Location: '.$FilePath);
        }
        exit;
    }
  

 
    
    
    public function ajax_getEmailTemplate($id){
      //  $filter =array('Type'=>EmailTemplate::ESTIMATE_TEMPLATE);
		$filter =array('StaticType'=>EmailTemplate::DYNAMICTEMPLATE);
        if($id == 1){
          $filter['UserID'] =   User::get_userID();
        }
        return EmailTemplate::getTemplateArray($filter);
    }

    public function estimate_comment($id)
    {
        $Estimate = Estimate::find($id);
        if(!empty($Estimate))
        {
            $EstimateComments = EstimateLog::get_comments($id);
            $Comment='';
            return View::make('estimates.comment', compact('Estimate', 'Comment','EstimateComments'));

        }
    }


    /**
        call when customer accept estimate
     */
    function customer_accept_estimate()
    {

        $data 				= 	 Input::all();
        if($data['Type']==2){
            $modifyby = 'customer';
        }else{
            $modifyby = User::get_user_full_name();
        }
        $Estimate 		= 	Estimate::find($data['eid']);

        if (Estimate::where('EstimateID',$data['eid'])->update([ 'ModifiedBy'=>$modifyby,'EstimateStatus' => 'accepted']))
        {

            $estimateloddata = array();
            if($data['Type']==2) {
                $estimateloddata['Note'] = 'Accepted By Unknown';
                if (!empty($data['Email'])) {
                    $estimateloddata['Note'] = 'Accepted By ' . $data['Email'];
                }
            }else{
                $estimateloddata['Note'] = 'Accepted By ' . $modifyby;
            }

            $estimateloddata['EstimateID']= $data['eid'];
            $estimateloddata['created_at']= date("Y-m-d H:i:s");
            $estimateloddata['EstimateLogStatus']= EstimateLog::ACCEPTED;
            EstimateLog::insert($estimateloddata);

            if($data['Type']==2){
                $Account = Account::find($Estimate->AccountID);
                $CustomerName = $Account->AccountName;
                //$InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($Estimate->AccountID);
				$InvoiceTemplateID = Estimate::GetEstimateInvoiceTemplateID($Estimate);
                $CompanyID = $Estimate->CompanyID;
                $CreatedBy = $Estimate->CreatedBy;
                $Company 					= 	Company::find($CompanyID);
                $CompanyName 				= 	$Company->CompanyName;
                $estimatenumber = Estimate::getFullEstimateNumber($Estimate,$InvoiceTemplateID);
                $emaildata['companyID'] = $CompanyID;
                $emaildata['CompanyName'] 		= 	$CompanyName;
                $emaildata['AccountName'] 		= 	$CreatedBy;
                $emaildata['Message'] 		= 	$estimatenumber.' Estimate '.$estimateloddata['Note'];
                $emaildata['Subject'] 		= 	$estimatenumber.' Estimate Accepted ('.$CustomerName.')';
                $Email = User::getEmailByUserName($CompanyID,$CreatedBy);
                if(!empty($Email)){
                    $emaildata['EmailTo'] = $Email;
                    $status = $this->sendEstimateMail('emails.estimates.estimatestatus',$emaildata);
                }
            }

            return Response::json(array("status" => "success", "message" => "Estimate Successfully Accepted"));

        }
        else
        {
            return Response::json(array("status" => "failed", "message" => "Problem Accepting Estimate."));
        }
	}

    public function estimate_reject_Status()
    {
        $data = Input::all();

        $Estimate 		= 	Estimate::find($data['EstimateIDs']);

        if($data['Type']==2){
            $modifyby = 'customer';
        }else{
            $modifyby = User::get_user_full_name();
        }

        if (Estimate::where('EstimateID',$data['EstimateIDs'])->update([ 'ModifiedBy'=>$modifyby,'EstimateStatus' => $data['EstimateStatus']]))
        {

            $estimateloddata = array();
            if($data['Type']==2) {
                $estimateloddata['Note'] = 'Rejected By Unknown';
                if (!empty($data['Email'])) {
                    $estimateloddata['Note'] = 'Rejected By ' . $data['Email'];
                }
            }else{
                $estimateloddata['Note'] = 'Rejected By ' . $modifyby;
            }

            $estimateloddata['EstimateID']= $data['EstimateIDs'];
            $estimateloddata['created_at']= date("Y-m-d H:i:s");
            $estimateloddata['EstimateLogStatus']= EstimateLog::REJECTED;
            EstimateLog::insert($estimateloddata);

            if($data['Type']==2){
                $Account = Account::find($Estimate->AccountID);
                $CustomerName = $Account->AccountName;
                //$InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($Estimate->AccountID);
				$InvoiceTemplateID = Estimate::GetEstimateInvoiceTemplateID($Estimate);
                $CompanyID = $Estimate->CompanyID;
                $CreatedBy = $Estimate->CreatedBy;
                $Company 					= 	Company::find($CompanyID);
                $CompanyName 				= 	$Company->CompanyName;
                $estimatenumber = Estimate::getFullEstimateNumber($Estimate,$InvoiceTemplateID);
                $emaildata['companyID'] = $CompanyID;
                $emaildata['CompanyName'] 		= 	$CompanyName;
                $emaildata['AccountName'] 		= 	$CreatedBy;
                $emaildata['Message'] 		= 	$estimatenumber.' Estimate '.$estimateloddata['Note'];
                $emaildata['Subject'] 		= 	$estimatenumber.' Estimate Rejected ('.$CustomerName.')';
                $Email = User::getEmailByUserName($CompanyID,$CreatedBy);
                if(!empty($Email)){
                    $emaildata['EmailTo'] = $Email;
                    $status = $this->sendEstimateMail('emails.estimates.estimatestatus',$emaildata);
                }
            }

            return Response::json(array("status" => "success", "message" => "Estimate Successfully Updated"));

        }
        else
        {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Estimate."));
        }
    }

    public function create_comment($id){
        $data = Input::all();
        $emaildata = array();
        $Estimate = Estimate::find($id);
        if($data['Comment']){
            $estimateloddata = array();
            if($data['Type']==2) {
                if (!empty($data['Email'])) {
                    $Email = $data['Email'];
                }else{
                    $Email = 'Unknown';
                }
            }else{
                $Email = User::get_user_full_name();
            }
            $Comment = $data['Comment'].' By '.$Email;
            $estimateloddata['Note'] = $Comment;
            $estimateloddata['EstimateID']= $id;
            $estimateloddata['created_at']= date("Y-m-d H:i:s");
            $estimateloddata['EstimateLogStatus']= EstimateLog::COMMENT;
            EstimateLog::insert($estimateloddata);

            $Account = Account::find($Estimate->AccountID);
            $CustomerName = $Account->AccountName;
            //$InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($Estimate->AccountID);
			$InvoiceTemplateID = Estimate::GetEstimateInvoiceTemplateID($Estimate);
            $CompanyID = $Estimate->CompanyID;
            $CreatedBy = $Estimate->CreatedBy;
            $Company 					= 	Company::find($CompanyID);
            $CompanyName 				= 	$Company->CompanyName;
            $estimatenumber = Estimate::getFullEstimateNumber($Estimate,$InvoiceTemplateID);
            $emailtoCustomer 			= 	CompanyConfiguration::get('EMAIL_TO_CUSTOMER');

            if($data['Type']==2){

                $emaildata['companyID'] = $CompanyID;
                $emaildata['CompanyName'] 		= 	$CompanyName;
                $emaildata['AccountName'] 		= 	$CreatedBy;
                $emaildata['Message'] 		= 	$Comment;
                $emaildata['EstimateNumber'] 		= 	$estimatenumber;
                $emaildata['Subject'] 		= 	'Comment added to Estimate '.$estimatenumber.' ('.$CustomerName.')';
                $Email = User::getEmailByUserName($CompanyID,$CreatedBy);
				//$emaildata['user'] 		= 	$CustomerName;

                if(!empty($Email)){
                    $emaildata['EmailTo'] = $Email;
                    $status = $this->sendEstimateMail('emails.estimates.comment',$emaildata);
                }
            }elseif($data['Type']==1){
                $CustomerEmail = $Account->BillingEmail;
                if(intval($emailtoCustomer) == 1 && isset($CustomerEmail) && $CustomerEmail != '')
                {
                    $data['EmailTo'] 			= 	explode(",",$CustomerEmail);
                    $data['EstimateURL'] 		= 	URL::to('/estimate/'.$Estimate->AccountID.'-'.$Estimate->EstimateID.'/cview');
                    $data['AccountName'] 		= 	Account::find($Estimate->AccountID)->AccountName;
                    $data['Subject'] 			= 	'Comment added to Estimate '.$estimatenumber;
                    $data['Message'] 			= 	$Comment;
                    $data['EstimateNumber'] 	= 	Estimate::getFullEstimateNumber($Estimate,$InvoiceTemplateID);
                    $data['CompanyName'] 		= 	$CompanyName;
                    $CustomerEmails 			=	$data['EmailTo'];

                    foreach($CustomerEmails as $singleemail)
                    {
                        $singleemail = trim($singleemail);
                        if (filter_var($singleemail, FILTER_VALIDATE_EMAIL))
                        {
							if(EmailsTemplates::CheckEmailTemplateStatus(Estimate::EMAILTEMPLATECOMMENT)){
                            $data['EmailTo'] 		= 	$singleemail;
                            $EstimateURL			= 	URL::to('/estimate/'.$Estimate->AccountID.'-'.$Estimate->EstimateID.'/cview?email='.$singleemail);
							$body					=	EmailsTemplates::SendEstimateSingle(Estimate::EMAILTEMPLATECOMMENT,$Estimate->EstimateID,'body',$data);
							$data['Subject']		=	EmailsTemplates::SendEstimateSingle(Estimate::EMAILTEMPLATECOMMENT,$Estimate->EstimateID,"subject",$data);
							$data['EmailFrom']		=	EmailsTemplates::GetEmailTemplateFrom(Estimate::EMAILTEMPLATECOMMENT);		
                            $status 				= 	$this->sendEstimateMail($body,$data,0);
							}
                        }
                    }

                }

            }

            return Response::json(array("status" => "success", "message" => "Estimate Comment Successfully Created"));
        }else{
            return Response::json(array("status" => "failed", "message" => "Problem Creating Estimate Comment Successfully"));
        }

    }

    /**
        Estimate Log
     */

    public function estimatelog($id)
    {
        $estimate = Estimate::find($id);
        //$InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($estimate->AccountID);
		$InvoiceTemplateID = Estimate::GetEstimateInvoiceTemplateID($estimate);
        $estimatenumber = Estimate::getFullEstimateNumber($estimate,$InvoiceTemplateID);
        return View::make('estimates.estimatelog', compact('estimate','id','estimatenumber'));
    }


    public function ajax_estimatelog_datagrid($id,$type) {
        $data = Input::all();
        $data['iDisplayStart'] +=1;


        //$columns = array('InvoiceNumber','Transaction','Notes','Amount','Status','created_at','InvoiceID');
        $columns = array('Notes','EstimateLogStatus','created_at','EstimateID');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();

        $query = "call prc_GetEstimateLog (".$companyID.",".$id.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        //echo $query;exit;
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Estimate Log.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Estimate Log.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query,'sqlsrv2')->make();
    }
}