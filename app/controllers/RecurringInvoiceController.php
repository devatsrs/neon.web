<?php

class RecurringInvoiceController extends \BaseController {

    public function ajax_datagrid($type)
	{
        $data 						 = 	Input::all();
        //print_r($data);exit();
        $data['iDisplayStart'] 		+=	1;
        $data['Status'] = $data['Status']==''?2:$data['Status'];
        $companyID 					 =  User::get_companyID();
        $columns 					 =  ['RecurringInvoiceID','Title','AccountName','LastInvoicedDate','NextInvoiceDate','GrandTotal','Status'];
        $sort_column 				 =  $columns[$data['iSortCol_0']];

        $query = "call prc_getRecurringInvoices (".$companyID.",".intval($data['AccountID']).",".$data['Status'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".strtoupper($data['sSortDir_0'])."'";
		//$query = "call prc_getRecurringInvoices('1', '449', '3', '0', '1', '50', '', 'asc', '0')";
        if(isset($data['Export']) && $data['Export'] == 1)
		{
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
			
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/RecurringInvoice.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/RecurringInvoice.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
           /* Excel::create('RecurringInvoice', function ($excel) use ($excel_data)
			{
                $excel->sheet('RecurringInvoice', function ($sheet) use ($excel_data)
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
     * GET /RecurringInvoice
     *
     * @return Response
     */
    public function index()
    {
        $companyID 				= 	User::get_companyID();
        $accounts 				= 	Account::getAccountIDList();
        $recurringinvoices_status_json 	= 	json_encode(RecurringInvoice::get_recurringinvoices_status());
        return View::make('recurringinvoices.index',compact('accounts','recurringinvoices_status_json'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /recurringinvoices/create
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
        return View::make('recurringinvoices.create',compact('accounts','products','taxes','BillingClass'));

    }

    /**
     *
     * */
    public function edit($id)
	{
        //$str = preg_replace('/^INV/', '', 'INV021000');;
        if($id > 0)
		{
            $RecurringInvoice 					= 	 RecurringInvoice::find($id);
            $CompanyID = $RecurringInvoice->CompanyID;
            $RecurringInvoiceDetail 			=	 RecurringInvoiceDetail::where(["RecurringInvoiceID"=>$id])->get();
            $accounts 					= 	 Account::getAccountIDList();
            $products 					= 	 Product::getProductDropdownList($CompanyID);
            $Account 					= 	 Account::where(["AccountID" => $RecurringInvoice->AccountID])->select(["AccountName","BillingEmail","CurrencyId"])->first(); //"TaxRateID","RoundChargesAmount","InvoiceTemplateID"
            $CurrencyID 				= 	 !empty($RecurringInvoice->CurrencyID)?$RecurringInvoice->CurrencyID:$Account->CurrencyId;
            $RoundChargesAmount 		= 	 get_round_decimal_places($RecurringInvoice->AccountID);
            $RecurringInvoiceTemplateID 		=	 AccountBilling::getInvoiceTemplateID($RecurringInvoice->AccountID);
            $RecurringInvoiceNumberPrefix 		= 	 ($RecurringInvoiceTemplateID>0)?InvoiceTemplate::find($RecurringInvoiceTemplateID)->RecurringInvoiceNumberPrefix:'';
            $Currency 					= 	 Currency::find($CurrencyID);
            $CurrencyCode 				= 	 !empty($Currency)?$Currency->Code:'';
            $CompanyName 				= 	 Company::getName($CompanyID);
            $taxes 						= 	 TaxRate::getTaxRateDropdownIDListForInvoice(0,$CompanyID);
			$RecurringInvoiceAllTax 			= 	 DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->where(["RecurringInvoiceID"=>$id,"RecurringInvoiceTaxType"=>1])->orderby('RecurringInvoiceTaxRateID')->get();
            $BillingClass = BillingClass::getDropdownIDList($CompanyID);
            $RecurringInvoiceReminder = json_decode($RecurringInvoice->RecurringSetting);
			
            return View::make('recurringinvoices.edit', compact( 'id', 'RecurringInvoice','RecurringInvoiceDetail','RecurringInvoiceTemplateID','RecurringInvoiceNumberPrefix',  'CurrencyCode','CurrencyID','RoundChargesAmount','accounts', 'products', 'taxes','CompanyName','Account','RecurringInvoiceAllTax','BillingClass','RecurringInvoiceReminder'));
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
            $isAutoRecurringInvoiceNumber		    =   true;
			
            if(!empty($data["RecurringInvoiceNumber"]))
			{
                $isAutoRecurringInvoiceNumber = false;
            }
			
            $RecurringInvoiceData 					= 	array();
            $RecurringInvoiceData["CompanyID"] 		= 	$companyID;
            $RecurringInvoiceData["AccountID"] 		= 	intval($data["AccountID"]);
            $RecurringInvoiceData["Address"] 		= 	$data["Address"];
            $RecurringInvoiceData["PONumber"] 		= 	$data["PONumber"];
            $RecurringInvoiceData["SubTotal"] 		= 	str_replace(",","",$data["SubTotal"]);
			$RecurringInvoiceData["TotalDiscount"] 	= 	0;
            $RecurringInvoiceData["TotalTax"] 		= 	str_replace(",","",$data["TotalTax"]);
            $RecurringInvoiceData["GrandTotal"] 	= 	floatval(str_replace(",","",$data["GrandTotalRecurringInvoice"]));
            $RecurringInvoiceData["CurrencyID"] 	= 	$data["CurrencyID"];
            $RecurringInvoiceData["Status"]         = 	RecurringInvoice::ACTIVE;
            $RecurringInvoiceData["Note"] 			= 	$data["Note"];
            $RecurringInvoiceData["Terms"] 			= 	$data["Terms"];
            $RecurringInvoiceData["FooterTerm"] 	=	$data["FooterTerm"];
            $RecurringInvoiceData["CreatedBy"] 		= 	$CreatedBy;
			$RecurringInvoiceData['RecurringInvoiceTotal'] 	 = str_replace(",","",$data["GrandTotal"]);
            $RecurringInvoiceData['BillingClassID'] = $data['BillingClassID'];
            $RecurringInvoiceData['Title'] = $data['Title'];
            $RecurringInvoiceData['Occurrence'] = $data['Occurrence'];
            $RecurringInvoiceData['LastInvoicedDate'] =  $data['NextInvoiceDate'];
            $RecurringInvoiceData['NextInvoiceDate'] =  $data['NextInvoiceDate'];
            $RecurringInvoiceData['BillingCycleType']       = $data['BillingCycleType'];
            $RecurringInvoiceData['RecurringInvoiceDetail'] = isset($data["RecurringInvoiceDetail"])?$data["RecurringInvoiceDetail"]:'';

            ///////////
            $rules = array(
                'CompanyID' => 'required',
                'Title' => 'required|unique:tblRecurringInvoice,Title,NULL,RecurringInvoiceID,CompanyID,'.$companyID,
                'BillingClassID'=> 'required',
                'Occurrence'=>'required',
                'AccountID' => 'required',
                'Address' => 'required',
                'CurrencyID' => 'required',
                'GrandTotal' => 'required',
                'BillingCycleType' => 'required',
                'RecurringInvoiceDetail' => 'required'
            );

            //$BillingCycleValue = '';

            if(isset($data['BillingCycleValue'])) {
                $rules['BillingCycleValue'] = 'required';
                $RecurringInvoiceData["BillingCycleValue"] 	    =	$data["BillingCycleValue"];
                //$BillingCycleValue = $data["BillingCycleValue"];
            }
            $message = ['BillingClassID.required'=>'Billing Class field is required',
                        'CurrencyID.required'=>'Currency Field is required.',
                        'RecurringInvoiceDetail.required'=>'Recurring Invoice Details fields are required',
                        'BillingCycleType.required'=>'Frequency field is required'];
			
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($RecurringInvoiceData, $rules,$message);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            unset($RecurringInvoiceData['RecurringInvoiceDetail']);
            //$RecurringInvoiceData['NextInvoiceDate'] = next_billing_date($data['BillingCycleType'], $BillingCycleValue, $data['InvoiceStartDate']);

			try
			{
                DB::connection('sqlsrv2')->beginTransaction();

                $RecurringInvoice = RecurringInvoice::create($RecurringInvoiceData);

                $RecurringInvoiceDetailData = $RecurringInvoiceTaxRates = $RecurringInvoiceAllTaxRates = array();

                foreach($data["RecurringInvoiceDetail"] as $field => $detail) {
                    $i	=	0;
                    foreach($detail as $value) {
                        if(in_array($field,["Price","Discount","TaxAmount","LineTotal"])) {
                            $RecurringInvoiceDetailData[$i][$field] = str_replace(",","",$value);
                        } else {
                            $RecurringInvoiceDetailData[$i][$field] = $value;
                        }

                        $RecurringInvoiceDetailData[$i]["RecurringInvoiceID"] 	= 	$RecurringInvoice->RecurringInvoiceID;
                        $RecurringInvoiceDetailData[$i]["created_at"] 	= 	date("Y-m-d H:i:s");
                        $RecurringInvoiceDetailData[$i]["CreatedBy"] 	= 	$CreatedBy;
						$RecurringInvoiceDetailData[$i]["Discount"] 	= 	0;
                        $i++;
                    }
                }

				//product tax
            	if(isset($data['Tax']) && is_array($data['Tax'])){
					foreach($data['Tax'] as $j => $taxdata){
						$RecurringInvoiceTaxRates[$j]['TaxRateID'] 		= 	$j;
						$RecurringInvoiceTaxRates[$j]['Title'] 			= 	TaxRate::getTaxName($j);
						$RecurringInvoiceTaxRates[$j]["created_at"] 	= 	date("Y-m-d H:i:s");
						$RecurringInvoiceTaxRates[$j]["RecurringInvoiceID"] 	= 	$RecurringInvoice->RecurringInvoiceID;
						$RecurringInvoiceTaxRates[$j]["TaxAmount"] 		= 	$taxdata;
					}
				}
				
				//RecurringInvoice tax
				if(isset($data['RecurringInvoiceTaxes']) && is_array($data['RecurringInvoiceTaxes'])){
					foreach($data['RecurringInvoiceTaxes']['field'] as  $p =>  $RecurringInvoiceTaxes){
                        if(!empty($RecurringInvoiceTaxes)) {
                            $RecurringInvoiceAllTaxRates[$p]['TaxRateID'] = $RecurringInvoiceTaxes;
                            $RecurringInvoiceAllTaxRates[$p]['Title'] = TaxRate::getTaxName($RecurringInvoiceTaxes);
                            $RecurringInvoiceAllTaxRates[$p]["created_at"] = date("Y-m-d H:i:s");
                            $RecurringInvoiceAllTaxRates[$p]["RecurringInvoiceTaxType"] = 1;
                            $RecurringInvoiceAllTaxRates[$p]["RecurringInvoiceID"] = $RecurringInvoice->RecurringInvoiceID;
                            $RecurringInvoiceAllTaxRates[$p]["TaxAmount"] = $data['RecurringInvoiceTaxes']['value'][$p];
                        }
					}
				}
				
                //$RecurringInvoiceTaxRates 	 = merge_tax($RecurringInvoiceTaxRates);
				//$RecurringInvoiceAllTaxRates = merge_tax($RecurringInvoiceAllTaxRates);

                $RecurringInvoiceLogData = array();
                $RecurringInvoiceLogData['RecurringInvoiceID']= $RecurringInvoice->RecurringInvoiceID;
                $RecurringInvoiceLogData['Note']= 'Created By '.$CreatedBy;
                $RecurringInvoiceLogData['created_at']= date("Y-m-d H:i:s");
                $RecurringInvoiceLogData['RecurringInvoiceLogStatus']= RecurringInvoiceLog::CREATED;
                RecurringInvoiceLog::insert($RecurringInvoiceLogData);
                /*if(!empty($RecurringInvoiceTaxRates)) { //product tax
                    DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->insert($RecurringInvoiceTaxRates);
                }*/
				
				 if(!empty($RecurringInvoiceAllTaxRates)) { //RecurringInvoice tax
                    DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->insert($RecurringInvoiceAllTaxRates);
                }

                if (!empty($RecurringInvoiceDetailData) && RecurringInvoiceDetail::insert($RecurringInvoiceDetailData))
				{
                    $InvoiceTaxRates1=RecurringTaxRate::getRecurringInvoiceTaxRateByProductDetail($RecurringInvoice->RecurringInvoiceID);
                    if(!empty($InvoiceTaxRates1)) { //Invoice tax
                        RecurringTaxRate::insert($InvoiceTaxRates1);
                    }

                    DB::connection('sqlsrv2')->commit();
                    return Response::json(array("status" => "success", "message" => "Recurring Profile Successfully Created",'LastID'=>$RecurringInvoice->RecurringInvoiceID,'redirect' => URL::to('/recurringprofiles/'.$RecurringInvoice->RecurringInvoiceID.'/edit')));
                }
				else
				{
                    DB::connection('sqlsrv2')->rollback();
                    return Response::json(array("status" => "failed", "message" => "Problem Creating Recurring Profile."));
                }
            }
			catch (Exception $e)
			{
                DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Problem Creating Recurring Profile. \n" . $e->getMessage()));
            }
        }
    }

    /**
     * Store RecurringInvoice
     */
    public function update($id)
	{
        $data = Input::all();
		
        if(!empty($data) && $id > 0)
		{
            $RecurringInvoice 						= 	RecurringInvoice::find($id);
            $companyID 						= 	User::get_companyID();
            $CreatedBy 						= 	User::get_user_full_name();
            $RecurringInvoiceData 					=	array();
            $RecurringInvoiceData["CompanyID"] 		= 	$companyID;
            $RecurringInvoiceData["AccountID"] 		= 	intval($data["AccountID"]);
            $RecurringInvoiceData["Address"] 		= 	$data["Address"];
            $RecurringInvoiceData["PONumber"] 		= 	$data["PONumber"];
            $RecurringInvoiceData["SubTotal"] 		= 	str_replace(",","",$data["SubTotal"]);
            $RecurringInvoiceData["TotalDiscount"] 	= 	0;
            $RecurringInvoiceData["TotalTax"] 		= 	str_replace(",","",$data["TotalTax"]);
            $RecurringInvoiceData["GrandTotal"] 	= 	floatval(str_replace(",","",$data["GrandTotalRecurringInvoice"]));
            $RecurringInvoiceData["CurrencyID"] 	= 	$data["CurrencyID"];
            $RecurringInvoiceData["Note"] 			= 	$data["Note"];
            $RecurringInvoiceData["Terms"] 			= 	$data["Terms"];
            $RecurringInvoiceData["FooterTerm"] 	=	$data["FooterTerm"];
            $RecurringInvoiceData["CreatedBy"] 		= 	$CreatedBy;
            $RecurringInvoiceData['RecurringInvoiceTotal'] 	 = str_replace(",","",$data["GrandTotal"]);
            $RecurringInvoiceData['BillingClassID'] = $data['BillingClassID'];
            $RecurringInvoiceData['Title'] = $data['Title'];
            $RecurringInvoiceData['Occurrence'] = $data['Occurrence'];
            $RecurringInvoiceData['BillingCycleType'] = $data['BillingCycleType'];
            $RecurringInvoiceData['NextInvoiceDate'] = $data['NextInvoiceDate'];
            $RecurringInvoiceData['RecurringInvoiceDetail'] = isset($data["RecurringInvoiceDetail"])?$data["RecurringInvoiceDetail"]:'';
            ///////////

            $rules = array(
                'CompanyID' => 'required',
                'Title'=>'required|unique:tblRecurringInvoice,Title,'.$id.',RecurringInvoiceID,CompanyID,'.$companyID,
                'BillingClassID'=> 'required',
                'Occurrence'=>'required',
                'AccountID' => 'required',
                'Address' => 'required',
                'CurrencyID' => 'required',
                'GrandTotal' => 'required',
                'BillingCycleType' => 'required',
                'RecurringInvoiceDetail' => 'required',
                'NextInvoiceDate'=> 'required'
            );

            $BillingCycleValue = '';

            if(isset($data['BillingCycleValue'])) {
                $rules['BillingCycleValue'] = 'required';
                $RecurringInvoiceData['BillingCycleValue'] = $data['BillingCycleValue'];
                $BillingCycleValue = $data["BillingCycleValue"];
            }

            $message = ['BillingClassID.required'=>'Billing Class field is required',
                'CurrencyID.required'=>'Currency Field is required',
                'RecurringInvoiceDetail.required'=>'Recurring Invoice Details fields are required',
                'BillingCycleType.required'=>'Frequency field is required'];
			
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');
			
            $validator = Validator::make($RecurringInvoiceData, $rules,$message);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails())
			{
                return json_validator_response($validator);
            }
            unset($RecurringInvoiceData['RecurringInvoiceDetail']);
            try
			{
                DB::connection('sqlsrv2')->beginTransaction();
                if(isset($RecurringInvoice->RecurringInvoiceID))
				{
                    $Extralognote = '';
                    if($RecurringInvoice->GrandTotal != $RecurringInvoiceData['GrandTotal'])
					{
                        $Extralognote = ' Total '.$RecurringInvoice->GrandTotal.' To '.$RecurringInvoiceData['GrandTotal'];
                    }

                    if($RecurringInvoice->BillingCycleType != $data['BillingCycleType']) {
                        $RecurringInvoiceData['NextInvoiceDate'] = next_billing_date($data['BillingCycleType'], $BillingCycleValue,strtotime($RecurringInvoice->LastInvoicedDate));
                    }
                    if(empty($BillingCycleValue)) {
                        $RecurringInvoiceData["BillingCycleValue"] = '';
                    }

                    $RecurringInvoice->update($RecurringInvoiceData);

                    $RecurringInvoiceDetailData 	= $RecurringInvoiceTaxRates = $RecurringInvoiceAllTaxRates = array();

                    //Delete all RecurringInvoice Data and then Recreate.
                    RecurringInvoiceDetail::where(["RecurringInvoiceID" => $RecurringInvoice->RecurringInvoiceID])->delete();
                    DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->where(["RecurringInvoiceID" => $RecurringInvoice->RecurringInvoiceID])->delete();
                    if (isset($data["RecurringInvoiceDetail"])) {
                        unset($data["RecurringInvoiceDetail"]["RecurringInvoiceDetailID"]);
                        foreach ($data["RecurringInvoiceDetail"] as $field => $detail) {
                            $i = 0;
                            foreach ($detail as $value) {
                                if( in_array($field,["Price","Discount","TaxAmount","LineTotal"])) {
                                    $RecurringInvoiceDetailData[$i][$field] = str_replace(",","",$value);
                                } else {
                                    $RecurringInvoiceDetailData[$i][$field] = $value;
                                }

                                $RecurringInvoiceDetailData[$i]["RecurringInvoiceID"]  	= 	$RecurringInvoice->RecurringInvoiceID;
                                $RecurringInvoiceDetailData[$i]["created_at"]  	= 	date("Y-m-d H:i:s");
                                $RecurringInvoiceDetailData[$i]["updated_at"]  	= 	date("Y-m-d H:i:s");
                                $RecurringInvoiceDetailData[$i]["CreatedBy"]   	= 	$CreatedBy;
                                $RecurringInvoiceDetailData[$i]["ModifiedBy"]  	= 	$CreatedBy;
								$RecurringInvoiceDetailData[$i]["Discount"] 	= 	0;
                                $i++;
                            }
                        }

						//product tax
						if(isset($data['Tax']) && is_array($data['Tax'])){
							foreach($data['Tax'] as $j => $taxdata){
							$RecurringInvoiceTaxRates[$j]['TaxRateID'] 		= 	$j;
							$RecurringInvoiceTaxRates[$j]['Title'] 			= 	TaxRate::getTaxName($j);
							$RecurringInvoiceTaxRates[$j]["created_at"] 	= 	date("Y-m-d H:i:s");
							$RecurringInvoiceTaxRates[$j]["RecurringInvoiceID"] 	= 	$RecurringInvoice->RecurringInvoiceID;
							$RecurringInvoiceTaxRates[$j]["TaxAmount"] 		= 	$taxdata;
							}
						}

							//RecurringInvoice tax
						if(isset($data['RecurringInvoiceTaxes']) && is_array($data['RecurringInvoiceTaxes'])){
							foreach($data['RecurringInvoiceTaxes']['field'] as  $p =>  $RecurringInvoiceTaxes){
                                if(!empty($RecurringInvoiceTaxes)) {
                                    $RecurringInvoiceAllTaxRates[$p]['TaxRateID'] = $RecurringInvoiceTaxes;
                                    $RecurringInvoiceAllTaxRates[$p]['Title'] = TaxRate::getTaxName($RecurringInvoiceTaxes);
                                    $RecurringInvoiceAllTaxRates[$p]["created_at"] = date("Y-m-d H:i:s");
                                    $RecurringInvoiceAllTaxRates[$p]["RecurringInvoiceTaxType"] = 1;
                                    $RecurringInvoiceAllTaxRates[$p]["RecurringInvoiceID"] = $RecurringInvoice->RecurringInvoiceID;
                                    $RecurringInvoiceAllTaxRates[$p]["TaxAmount"] = $data['RecurringInvoiceTaxes']['value'][$p];
                                }
							}
						}

                        //$RecurringInvoiceTaxRates 	 = merge_tax($RecurringInvoiceTaxRates);
						//$RecurringInvoiceAllTaxRates = merge_tax($RecurringInvoiceAllTaxRates);

                        $RecurringInvoiceLogData = array();
                        $RecurringInvoiceLogData['RecurringInvoiceID']= $RecurringInvoice->RecurringInvoiceID;
                        $RecurringInvoiceLogData['Note']= 'Updated By '.$CreatedBy;
                        $RecurringInvoiceLogData['created_at']= date("Y-m-d H:i:s");
                        $RecurringInvoiceLogData['RecurringInvoiceLogStatus']= RecurringInvoiceLog::UPDATED;
                        RecurringInvoiceLog::insert($RecurringInvoiceLogData);

                        /*if(!empty($RecurringInvoiceTaxRates)) {
                            DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->insert($RecurringInvoiceTaxRates);
                        }*/

						if(!empty($RecurringInvoiceAllTaxRates)) {
                            DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->insert($RecurringInvoiceAllTaxRates);
                        }
                        if (RecurringInvoiceDetail::insert($RecurringInvoiceDetailData)) {
                            $InvoiceTaxRates1=RecurringTaxRate::getRecurringInvoiceTaxRateByProductDetail($RecurringInvoice->RecurringInvoiceID);
                            if(!empty($InvoiceTaxRates1)) { //Invoice tax
                                RecurringTaxRate::insert($InvoiceTaxRates1);
                            }
                            DB::connection('sqlsrv2')->commit();
                            return Response::json(array("status" => "success", "message" => "Recurring Profile Successfully Updated", 'LastID' => $RecurringInvoice->RecurringInvoiceID));
                        }
                    }
					else
					{
                        return Response::json(array("status" => "success", "message" => "Recurring Profile Successfully Updated, There is no product in Recurring Profile", 'LastID' => $RecurringInvoice->RecurringInvoiceID));
                    }
                }
            }
			catch (Exception $e)
			{
				DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Problem Updating Recurring Profile. \n " . $e->getMessage()));
            }
        }
    }

    public function delete(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $where=['AccountID'=>'','Status'=>'2','selectedIDs'=>''];
        if(isset($data['criteria']) && !empty($data['criteria'])){
            $criteria= json_decode($data['criteria'],true);
            if(!empty($criteria['AccountID'])){
                $where['AccountID']= $criteria['AccountID'];
            }
            $where['Status'] = $criteria['Status']==''?2:$criteria['Status'];
        }else{
            $where['selectedIDs']= $data['selectedIDs'];
        }
        $sql = "call prc_DeleteRecurringInvoices (".$companyID.",".intval($where['AccountID']).",".$where['Status'].",'".$where['selectedIDs']."')";

        try {
            DB::connection('sqlsrv2')->beginTransaction();
            DB::connection('sqlsrv2')->statement($sql);
            DB::connection('sqlsrv2')->commit();
            return Response::json(array("status" => "success", "message" => "Recurring Invoice Successfully Deleted"));
        } catch (Exception $e) {
            DB::connection('sqlsrv2')->rollback();
            return Response::json(array("status" => "failed", "message" => "Recurring Invoice is in Use, You cant delete this Currently. \n" . $e->getMessage() ));
        }
    }

    public function startstop($status){
        $data = Input::all();
        $companyID = User::get_companyID();
        $where=['AccountID'=>'','Status'=>'2','selectedIDs'=>''];
        if(isset($data['criteria']) && !empty($data['criteria'])){
            $criteria= json_decode($data['criteria'],true);
            if(!empty($criteria['AccountID'])){
                $where['AccountID']= $criteria['AccountID'];
            }
            $where['Status'] = $criteria['Status']==''?2:$criteria['Status'];
        }else{
            $where['selectedIDs']= $data['selectedIDs'];
        }

        if($status == 0 ){
            $StartStop = RecurringInvoiceLog::STOP;
        }else {
            $StartStop = RecurringInvoiceLog::START;
        }
        $sql = "call prc_StartStopRecurringInvoices (".$companyID.",".intval($where['AccountID']).",".$where['Status'].",'".$where['selectedIDs']."',".$status.",'".User::get_user_full_name()."',".$StartStop.")";
        try {
            DB::connection('sqlsrv2')->statement($sql);
            return Response::json(array("status" => "success", "message" => "Recurring Invoice Successfully Updated"));
        } catch (Exception $e) {
            return Response::json(array("status" => "failed", "message" =>$e->getMessage()));
        }
    }

    public function sendInvoice(){
        $data = Input::all();
        $date = Date("Y-m-d H:i:s");
        $companyID = User::get_companyID();
        $isSingle = 0;
        $where=['AccountID'=>'','Status'=>'2','selectedIDs'=>''];
        if(isset($data['criteria']) && !empty($data['criteria'])){
            $criteria= json_decode($data['criteria'],true);
            if(!empty($criteria['AccountID'])){
                $where['AccountID']= $criteria['AccountID'];
            }
            $where['Status'] = $criteria['Status']==''?2:$criteria['Status'];
        }else{
            $where['selectedIDs']= $data['selectedIDs'];
            if(count(explode(',',$data['selectedIDs']))==1){
                $isSingle = 1;
            }
        }

        if($isSingle==1){
            $processID = GUID::generate();
            $sql = "call prc_CreateInvoiceFromRecurringInvoice (".$companyID.",".intval($where['AccountID']).",".$where['Status'].",'".trim($where['selectedIDs'])."','".User::get_user_full_name()."',".RecurringInvoiceLog::GENERATE.",'".$processID."','".$date."')";
            $result = DB::connection('sqlsrv2')->select($sql);
            if(!empty($result[0]->message)){
                return Response::json(array("status" => "failed", "message" => $result[0]->message));
            }else {
                $invoiceID = Invoice::where(['ProcessID' => $processID])->pluck('InvoiceID');
                if(!empty($invoiceID)) {
                    //Update recurring invoice status
                    $recurringInvoice = RecurringInvoice::find($data['selectedIDs']);
                    $RecurringInvoiceData['NextInvoiceDate'] = next_billing_date($recurringInvoice->BillingCycleType, $recurringInvoice->BillingCycleValue , strtotime($recurringInvoice->NextInvoiceDate));
                    $RecurringInvoiceData['LastInvoicedDate'] = $date;
                    $recurringInvoice->update($RecurringInvoiceData);
                    //generate pdf for new created invoice
                    $pdf_path = Invoice::generate_pdf($invoiceID);
                    if (empty($pdf_path)) {
                        return Response::json(array("status" => "failed", "message" => 'Failed to generate Invoice PDF File'));
                    } else {
                        Invoice::where(['InvoiceID'=>$invoiceID])->update(["PDF" => $pdf_path]);
                        $InvoiceTemplateID = BillingClass::where('BillingClassID',$recurringInvoice->BillingClassID)->pluck('InvoiceTemplateID');
                        $Invoice = Invoice::find($invoiceID);
                        InvoiceTemplate::where(array('InvoiceTemplateID'=>$InvoiceTemplateID))->update(array("LastInvoiceNumber" => $Invoice->InvoiceNumber));
                    }
                }
                return Response::json(array("status" => "success", "message" => '', 'invoiceID' => $invoiceID));
            }
        }else if((isset($data['criteria']) && !empty($data['criteria']))|| !empty($data['selectedIDs'])){
            $data['RecurringInvoice'] = 1;
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
            $jobdata["created_at"] = $date;
            $jobdata["updated_at"] = $date;
            $JobID = Job::insertGetId($jobdata);
            if($JobID){
                return Response::json(array("status" => "success", "message" => "Bulk Invoice Send Job Added in queue to process.You will be notified once job is completed. "));
            }else{
                return Response::json(array("status" => "failed", "message" => "Problem Creating Job Bulk Invoice Send."));
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

    /**
    Calculate total on Product Change
     */
    public function calculate_total(){
        $data = Input::all();
        $response = array();
        $error = "";
        $AccountID = intval($data['account_id']);
        $BillingClassID = intval($data['BillingClassID']);
        $InvoiceTemplateID = BillingClass::getInvoiceTemplateID($BillingClassID);
        $Account = Account::find($AccountID);
        if (!empty($Account)) {
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            if (isset($InvoiceTemplate->InvoiceTemplateID) && $InvoiceTemplate->InvoiceTemplateID > 0) {
                $decimal_places = get_round_decimal_places($AccountID);

                $companyID = User::get_companyID();
                $data['CompanyID'] = $companyID;

                $Product = Product::find($data['product_id']);
                if (!empty($Product)) {
                    $ProductAmount = number_format($Product->Amount, $decimal_places,".","");
                    $ProductDescription = $Product->Description;
                    $SubTotal = number_format($ProductAmount * $data['qty'], $decimal_places,".","");
                    $response = [
                        "status" => "success",
                        "product_description" => $ProductDescription,
                        "product_amount" => $ProductAmount,
                         "product_total_tax_rate" => 0,
                        "sub_total" => $SubTotal,
                        "decimal_places" => $decimal_places,
                    ];
                } else {
                    $error = "No Product Found.";
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

    /**
     * Get Account Information
     */
    public function getAccountInfo()
    {
        $data = Input::all();
        if (isset($data['account_id']) && $data['account_id'] > 0 )
		{
            $fields 			=	["CurrencyId","AccountID","Address1","Address2","Address3","City","PostCode","Country"];
            $Account 			= 	Account::where(["AccountID"=>$data['account_id']])->select($fields)->first();
            $Currency 			= 	Currency::where(["CurrencyId"=>$Account->CurrencyId])->pluck("Code");
            $CurrencyId 		= 	$Account->CurrencyId;
            $Address 			= 	Account::getFullAddress($Account);
            $BillingClassID     =   AccountBilling::getBillingClassID($data['account_id']);
            $return 			=	['Currency','CurrencyId','Address','BillingClassID'];
            return Response::json(compact($return));
        }
    }


    public function getBillingClassInfo()
    {
        $data = Input::all();

        $invoiceTemplateID = BillingClass::getInvoiceTemplateID($data['BillingClassID']);
        $InvoiceToAddress = '';
        if (!empty($invoiceTemplateID) && $invoiceTemplateID > 0 ) {
            $InvoiceTemplate = InvoiceTemplate::find($invoiceTemplateID);
            $Terms = $InvoiceTemplate->Terms;
            $FooterTerm = $InvoiceTemplate->FooterTerm;
            $TaxRate = BillingClass::getTaxRateType($data['BillingClassID'],TaxRate::TAX_ALL);


            if(!empty($data['AccountID'])){
                $Account = Account::find($data['AccountID']);
                $message = $InvoiceTemplate->InvoiceTo;
                $replace_array = Invoice::create_accountdetails($Account);
                $text = Invoice::getInvoiceToByAccount($message,$replace_array);
                $InvoiceToAddress = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
            }
            $return = ['Terms','FooterTerm','TaxRate','InvoiceToAddress'];

            return Response::json(compact($return));
        }else{
            return Response::json(array("status" => "failed", "message" => "You cannot create invoice as no Invoice Template assigned to this billing Class." ));
        }
    }

    /**
        Recurring Invoice Log
     */

    public function recurringinvoicelog($id,$type='')
    {
        $recurringinvoice = RecurringInvoice::find($id);
        return View::make('recurringinvoices.recurringinvoicelog', compact('recurringinvoice','id','type'));
    }


    public function ajax_recurringinvoicelog_datagrid($id,$type) {
        $data = Input::all();
        $data['LogType'] = empty($data['LogType'])?0:$data['LogType'];
        $data['iDisplayStart'] +=1;
        //$columns = array('InvoiceNumber','Transaction','Notes','Amount','Status','created_at','InvoiceID');
        $columns = array('Notes','RecurringInvoiceLogStatus','created_at','RecurringInvoiceID');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();

        $query = "call prc_GetRecurringInvoiceLog (".$companyID.",".$id.",".$data['LogType'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        //echo $query;exit;
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/RecurringInvoice Log.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/RecurringInvoice Log.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query,'sqlsrv2')->make();
    }
}