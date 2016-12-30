<?php

class RecurringInvoiceController extends \BaseController {

    public function ajax_datagrid($type)
	{
        $data 						 = 	Input::all();
        //print_r($data);exit();
        $data['iDisplayStart'] 		+=	1;
        $data['Status'] = $data['Status']==''?2:$data['Status'];
        $companyID 					 =  User::get_companyID();
        $columns 					 =  ['RecurringInvoiceID','Title','AccountName','LastInvoiceNumber','LastInvoicedDate','GrandTotal','Status'];
        $sort_column 				 =  $columns[$data['iSortCol_0']];

        $query = "call prc_getRecurringInvoices (".$companyID.",".intval($data['AccountID']).",".intval($data['CurrencyID']).",".$data['Status'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".strtoupper($data['sSortDir_0'])."'";
		//$query = "call prc_getRecurringInvoices('1', '449', '3', '0', '1', '50', '', 'asc', '0')";
        if(isset($data['Export']) && $data['Export'] == 1)
		{
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
			
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/RecurringInvoice.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/RecurringInvoice.xls';
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
        $DefaultCurrencyID    	=   Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $accounts 				= 	Account::getAccountIDList();
        $recurringinvoices_status_json 	= 	json_encode(RecurringInvoice::get_recurringinvoices_status());
        return View::make('recurringinvoices.index',compact('accounts','recurringinvoices_status_json','DefaultCurrencyID'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /recurringinvoices/create
     *
     * @return Response
     */
    public function create()
    {

        $accounts = Account::getAccountIDList();
        $products = Product::getProductDropdownList();
        $taxes 	  = TaxRate::getTaxRateDropdownIDListForInvoice();
        $invoiceTemplate = InvoiceTemplate::getInvoiceTemplateList();
        //$gateway_product_ids = Product::getGatewayProductIDs();
        return View::make('recurringinvoices.create',compact('accounts','products','taxes','invoiceTemplate'));

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
            $RecurringInvoiceDetail 			=	 RecurringInvoiceDetail::where(["RecurringInvoiceID"=>$id])->get();
            $accounts 					= 	 Account::getAccountIDList();
            $products 					= 	 Product::getProductDropdownList();
            $Account 					= 	 Account::where(["AccountID" => $RecurringInvoice->AccountID])->select(["AccountName","BillingEmail","CurrencyId"])->first(); //"TaxRateID","RoundChargesAmount","InvoiceTemplateID"
            $CurrencyID 				= 	 !empty($RecurringInvoice->CurrencyID)?$RecurringInvoice->CurrencyID:$Account->CurrencyId;
            $RoundChargesAmount 		= 	 get_round_decimal_places($RecurringInvoice->AccountID);
            $RecurringInvoiceTemplateID 		=	 AccountBilling::getInvoiceTemplateID($RecurringInvoice->AccountID);
            $RecurringInvoiceNumberPrefix 		= 	 ($RecurringInvoiceTemplateID>0)?InvoiceTemplate::find($RecurringInvoiceTemplateID)->RecurringInvoiceNumberPrefix:'';
            $Currency 					= 	 Currency::find($CurrencyID);
            $CurrencyCode 				= 	 !empty($Currency)?$Currency->Code:'';
            $CompanyName 				= 	 Company::getName();
            $taxes 						= 	 TaxRate::getTaxRateDropdownIDListForInvoice();
			$RecurringInvoiceAllTax 			= 	 DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->where(["RecurringInvoiceID"=>$id,"RecurringInvoiceTaxType"=>1])->orderby('RecurringInvoiceTaxRateID')->get();
            $invoiceTemplate = InvoiceTemplate::getInvoiceTemplateList();
            $RecurringInvoiceReminder = json_decode($RecurringInvoice->RecurringSetting);
			
            return View::make('recurringinvoices.edit', compact( 'id', 'RecurringInvoice','RecurringInvoiceDetail','RecurringInvoiceTemplateID','RecurringInvoiceNumberPrefix',  'CurrencyCode','CurrencyID','RoundChargesAmount','accounts', 'products', 'taxes','CompanyName','Account','RecurringInvoiceAllTax','invoiceTemplate','RecurringInvoiceReminder'));
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
            //$RecurringInvoiceData["RecurringInvoiceNumber"] = 	$LastRecurringInvoiceNumber = ($isAutoRecurringInvoiceNumber)?InvoiceTemplate::getAccountNextInvoiceNumber($data["AccountID"]):$data["RecurringInvoiceNumber"];
            //$RecurringInvoiceData["IssueDate"] 		= 	$data["IssueDate"];
            //$RecurringInvoiceData["PONumber"] 		= 	$data["PONumber"];
            $RecurringInvoiceData["SubTotal"] 		= 	str_replace(",","",$data["SubTotal"]);
            //$RecurringInvoiceData["TotalDiscount"] 	= 	str_replace(",","",$data["TotalDiscount"]);
			$RecurringInvoiceData["TotalDiscount"] 	= 	0;
            $RecurringInvoiceData["TotalTax"] 		= 	str_replace(",","",$data["TotalTax"]);
            $RecurringInvoiceData["GrandTotal"] 	= 	floatval(str_replace(",","",$data["GrandTotalRecurringInvoice"]));
            $RecurringInvoiceData["CurrencyID"] 	= 	$data["CurrencyID"];
            $RecurringInvoiceData["Status"] = 	RecurringInvoice::STOP;
            $RecurringInvoiceData["Note"] 			= 	$data["Note"];
            $RecurringInvoiceData["Terms"] 			= 	$data["Terms"];
            $RecurringInvoiceData["FooterTerm"] 	=	$data["FooterTerm"];
            $RecurringInvoiceData["StartDate"] 	    =	$data["StartDate"];
            $RecurringInvoiceData["EndDate"] 	    =	$data["EndDate"];
            $RecurringInvoiceData["CreatedBy"] 		= 	$CreatedBy;
			$RecurringInvoiceData['RecurringInvoiceTotal'] 	 = str_replace(",","",$data["GrandTotal"]);
            $RecurringInvoiceData['RecurringSetting'] = json_encode($data['RecurringInvoice']);
            $RecurringInvoiceData['InvoiceTemplateID'] = $data['InvoiceTemplateID'];
            $RecurringInvoiceData['Title'] = $data['Title'];
            $RecurringInvoiceData['Interval'] = isset($data['RecurringInvoice']['Interval'])?$data['RecurringInvoice']['Interval']:'';
			//$RecurringInvoiceData["converted"] 		= 	'N';

            ///////////
            $rules = array(
                'CompanyID' => 'required',
                'Title' => 'required|unique:tblRecurringInvoice,Title,NULL,RecurringInvoiceID,CompanyID,'.$companyID,
                'InvoiceTemplateID'=> 'required',
                'Interval'=>'required',
                'AccountID' => 'required',
                'Address' => 'required',
                //'RecurringInvoiceNumber' => 'required|unique:tblRecurringInvoice,RecurringInvoiceNumber,NULL,RecurringInvoiceID,CompanyID,'.$companyID,
                //'IssueDate' => 'required',
                'CurrencyID' => 'required',
                'GrandTotal' => 'required',
                'StartDate' => 'required',
                'EndDate' => 'required'
            );

            $message = ['InvoiceTemplateID.required'=>'Invoice Template field is required',
                        'CurrencyID.required'=>'Currency Field is required'];
			
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($RecurringInvoiceData, $rules,$message);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails())
			{
                return json_validator_response($validator);
            }
            unset($RecurringInvoiceData['Interval']);
			try
			{
                DB::connection('sqlsrv2')->beginTransaction();
                $RecurringInvoice = RecurringInvoice::create($RecurringInvoiceData);
                //Store Last RecurringInvoice Number.
                
				/*if($isAutoRecurringInvoiceNumber) {
                    InvoiceTemplate::find(AccountBilling::getInvoiceTemplateID($data["AccountID"]))->update(array("LastRecurringInvoiceNumber" => $LastRecurringInvoiceNumber ));
                }*/
				
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
						$RecurringInvoiceAllTaxRates[$p]['TaxRateID'] 		= 	$RecurringInvoiceTaxes;
						$RecurringInvoiceAllTaxRates[$p]['Title'] 			= 	TaxRate::getTaxName($RecurringInvoiceTaxes);
						$RecurringInvoiceAllTaxRates[$p]["created_at"] 		= 	date("Y-m-d H:i:s");
						$RecurringInvoiceAllTaxRates[$p]["RecurringInvoiceTaxType"] = 	1;
						$RecurringInvoiceAllTaxRates[$p]["RecurringInvoiceID"] 		= 	$RecurringInvoice->RecurringInvoiceID;
						$RecurringInvoiceAllTaxRates[$p]["TaxAmount"] 		= 	$data['RecurringInvoiceTaxes']['value'][$p];
					}
				}
				
                $RecurringInvoiceTaxRates 	 = merge_tax($RecurringInvoiceTaxRates);
				$RecurringInvoiceAllTaxRates = merge_tax($RecurringInvoiceAllTaxRates);

                $RecurringInvoiceLogData = array();
                $RecurringInvoiceLogData['RecurringInvoiceID']= $RecurringInvoice->RecurringInvoiceID;
                $RecurringInvoiceLogData['Note']= 'Created By '.$CreatedBy;
                $RecurringInvoiceLogData['created_at']= date("Y-m-d H:i:s");
                $RecurringInvoiceLogData['RecurringInvoiceLogStatus']= RecurringInvoiceLog::CREATED;
                RecurringInvoiceLog::insert($RecurringInvoiceLogData);
                if(!empty($RecurringInvoiceTaxRates)) { //product tax
                    DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->insert($RecurringInvoiceTaxRates);
                }
				
				 if(!empty($RecurringInvoiceAllTaxRates)) { //RecurringInvoice tax
                    DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->insert($RecurringInvoiceAllTaxRates);
                }

                if (!empty($RecurringInvoiceDetailData) && RecurringInvoiceDetail::insert($RecurringInvoiceDetailData))
				{
                    DB::connection('sqlsrv2')->commit();
                    return Response::json(array("status" => "success", "message" => "RecurringInvoice Successfully Created",'LastID'=>$RecurringInvoice->RecurringInvoiceID,'redirect' => URL::to('/recurringinvoices/'.$RecurringInvoice->RecurringInvoiceID.'/edit')));
                }
				else
				{
                    DB::connection('sqlsrv2')->rollback();
                    return Response::json(array("status" => "failed", "message" => "Problem Creating RecurringInvoice."));
                }
            }
			catch (Exception $e)
			{
                DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Problem Creating RecurringInvoice. \n" . $e->getMessage()));
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
            $RecurringInvoiceData["AccountID"] 		= 	$data["AccountID"];
            $RecurringInvoiceData["Address"] 		= 	$data["Address"];
            //$RecurringInvoiceData["RecurringInvoiceNumber"] = 	$data["RecurringInvoiceNumber"];
            //$RecurringInvoiceData["IssueDate"] 		= 	$data["IssueDate"];
            //$RecurringInvoiceData["PONumber"] 		= 	$data["PONumber"];
            $RecurringInvoiceData["SubTotal"] 		= 	str_replace(",","",$data["SubTotal"]);
            //$RecurringInvoiceData["TotalDiscount"] 	= 	str_replace(",","",$data["TotalDiscount"]);
			$RecurringInvoiceData["TotalDiscount"] 	= 	0;
            $RecurringInvoiceData["TotalTax"] 		= 	str_replace(",","",$data["TotalTax"]);
            $RecurringInvoiceData["GrandTotal"] 	= 	floatval(str_replace(",","",$data["GrandTotalRecurringInvoice"]));
            $RecurringInvoiceData["CurrencyID"] 	= 	$data["CurrencyID"];
            $RecurringInvoiceData["Note"] 			= 	$data["Note"];
            $RecurringInvoiceData["Terms"] 			= 	$data["Terms"];
            $RecurringInvoiceData["FooterTerm"] 	= 	$data["FooterTerm"];
            $RecurringInvoiceData["StartDate"] 	    =	$data["StartDate"];
            $RecurringInvoiceData["EndDate"] 	    =	$data["EndDate"];
            $RecurringInvoiceData["ModifiedBy"] 	= 	$CreatedBy;
			$RecurringInvoiceData['RecurringInvoiceTotal'] 	=   str_replace(",","",$data["GrandTotal"]);
            $RecurringInvoiceData['RecurringSetting'] = json_encode($data['RecurringInvoice']);
            $RecurringInvoiceData['InvoiceTemplateID'] = $data['InvoiceTemplateID'];
            $RecurringInvoiceData['Title'] = $data['Title'];
            $RecurringInvoiceData['Interval'] = isset($data['RecurringInvoice']['Interval'])?$data['RecurringInvoice']['Interval']:'';
            ///////////

            $rules = array(
                'CompanyID' => 'required',
                'Title'=>'required|unique:tblRecurringInvoice,Title,'.$id.',RecurringInvoiceID,CompanyID,'.$companyID,
                'InvoiceTemplateID'=> 'required',
                'Interval'=>'required',
                'AccountID' => 'required',
                'Address' => 'required',
                'CurrencyID' => 'required',
                'GrandTotal' => 'required',
                'StartDate' => 'required',
                'EndDate' => 'required'
            );

            $message = ['InvoiceTemplateID.required'=>'Invoice Template field is required',
                'CurrencyID.required'=>'Currency Field is required'];
			
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');
			
            $validator = Validator::make($RecurringInvoiceData, $rules,$message);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails())
			{
                return json_validator_response($validator);
            }
            unset($RecurringInvoiceData['Interval']);
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
					
					
                    $RecurringInvoice->update($RecurringInvoiceData);

                    $RecurringInvoiceDetailData 	= $RecurringInvoiceTaxRates = $RecurringInvoiceAllTaxRates = array();

                    //Delete all RecurringInvoice Data and then Recreate.
                    RecurringInvoiceDetail::where(["RecurringInvoiceID" => $RecurringInvoice->RecurringInvoiceID])->delete();
                    DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->where(["RecurringInvoiceID" => $RecurringInvoice->RecurringInvoiceID])->delete();
                    if (isset($data["RecurringInvoiceDetail"])) {
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
								$RecurringInvoiceAllTaxRates[$p]['TaxRateID'] 		= 	$RecurringInvoiceTaxes;
								$RecurringInvoiceAllTaxRates[$p]['Title'] 			= 	TaxRate::getTaxName($RecurringInvoiceTaxes);
								$RecurringInvoiceAllTaxRates[$p]["created_at"] 		= 	date("Y-m-d H:i:s");
								$RecurringInvoiceAllTaxRates[$p]["RecurringInvoiceTaxType"] = 	1;
								$RecurringInvoiceAllTaxRates[$p]["RecurringInvoiceID"] 		= 	$RecurringInvoice->RecurringInvoiceID;
								$RecurringInvoiceAllTaxRates[$p]["TaxAmount"] 		= 	$data['RecurringInvoiceTaxes']['value'][$p];
							}
						}

                        $RecurringInvoiceTaxRates 	 = merge_tax($RecurringInvoiceTaxRates);
						$RecurringInvoiceAllTaxRates = merge_tax($RecurringInvoiceAllTaxRates);

                        $RecurringInvoiceLogData = array();
                        $RecurringInvoiceLogData['RecurringInvoiceID']= $RecurringInvoice->RecurringInvoiceID;
                        $RecurringInvoiceLogData['Note']= 'Created By '.$CreatedBy;
                        $RecurringInvoiceLogData['created_at']= date("Y-m-d H:i:s");
                        $RecurringInvoiceLogData['RecurringInvoiceLogStatus']= RecurringInvoiceLog::UPDATED;
                        RecurringInvoiceLog::insert($RecurringInvoiceLogData);

                        if(!empty($RecurringInvoiceTaxRates)) {
                            DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->insert($RecurringInvoiceTaxRates);
                        }

						if(!empty($RecurringInvoiceAllTaxRates)) {
                            DB::connection('sqlsrv2')->table('tblRecurringInvoiceTaxRate')->insert($RecurringInvoiceAllTaxRates);
                        }

                        if (RecurringInvoiceDetail::insert($RecurringInvoiceDetailData)) {
                            DB::connection('sqlsrv2')->commit();
                            return Response::json(array("status" => "success", "message" => "RecurringInvoice Successfully Updated", 'LastID' => $RecurringInvoice->RecurringInvoiceID));
                        }
                    }
					else
					{
                        return Response::json(array("status" => "success", "message" => "RecurringInvoice Successfully Updated, There is no product in RecurringInvoice", 'LastID' => $RecurringInvoice->RecurringInvoiceID));
                    }
                }
            }
			catch (Exception $e)
			{
				DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Problem Updating RecurringInvoice. \n " . $e->getMessage()));
            }
        }
    }

    public function delete(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $where=['AccountID'=>'','CurrencyID'=>'','Status'=>'2','selectedIDs'=>''];
        if(isset($data['criteria']) && !empty($data['criteria'])){
            $criteria= json_decode($data['criteria'],true);
            if(!empty($criteria['AccountID'])){
                $where['AccountID']= $criteria['AccountID'];
            }
            $where['Status'] = $criteria['Status']==''?2:$criteria['Status'];
            if(!empty($criteria['CurrencyID'])){
                $where['CurrencyID']= $criteria['CurrencyID'];
            }
        }else{
            $where['selectedIDs']= $data['selectedIDs'];
        }
        $sql = "call prc_DeleteRecurringInvoices (".$companyID.",".intval($where['AccountID']).",".intval($where['CurrencyID']).",'".$where['Status']."','".$where['selectedIDs']."')";

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
        $where=['AccountID'=>'','CurrencyID'=>'','Status'=>'2','selectedIDs'=>''];
        if(isset($data['criteria']) && !empty($data['criteria'])){
            $criteria= json_decode($data['criteria'],true);
            if(!empty($criteria['AccountID'])){
                $where['AccountID']= $criteria['AccountID'];
            }
            $where['Status'] = $criteria['Status']==''?2:$criteria['Status'];
            if(!empty($criteria['CurrencyID'])){
                $where['CurrencyID']= $criteria['CurrencyID'];
            }
        }else{
            $where['selectedIDs']= $data['selectedIDs'];
        }

        if($status == 0 ){
            $StartStop = RecurringInvoiceLog::STOP;
        }else {
            $StartStop = RecurringInvoiceLog::START;
        }
        $sql = "call prc_StartStopRecurringInvoices (".$companyID.",".intval($where['AccountID']).",".intval($where['CurrencyID']).",'".$where['Status']."','".$where['selectedIDs']."',".$status.",'".User::get_user_full_name()."',".$StartStop.")";
        try {
            DB::connection('sqlsrv2')->statement($sql);
            return Response::json(array("status" => "success", "message" => "Recurring Invoice Successfully Updated"));
        } catch (Exception $e) {
            return Response::json(array("status" => "failed", "message" =>$e->getMessage()));
        }
    }

    public function sendInvoice(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $isSelected = 0;
        $where=['AccountID'=>'','CurrencyID'=>'','Status'=>'','selectedIDs'=>''];
        if(isset($data['criteria']) && !empty($data['criteria'])){
            $criteria= json_decode($data['criteria'],true);
            if(!empty($criteria['AccountID'])){
                $where['AccountID']= $criteria['AccountID'];
            }
            $where['Status'] = $criteria['Status']==''?2:$criteria['Status'];
            if(!empty($criteria['CurrencyID'])){
                $where['CurrencyID']= $criteria['CurrencyID'];
            }
        }else{
            $where['selectedIDs']= $data['selectedIDs'];
            if(strlen($data['selectedIDs'])==1){
                $isSelected = 1;
            }
        }

        $processID = GUID::generate();

        $sql = "call prc_CreateInvoiceFromRecurringInvoice (".$companyID.",".intval($where['AccountID']).",".intval($where['CurrencyID']).",'".$where['Status']."','".$where['selectedIDs']."','".User::get_user_full_name()."',".RecurringInvoiceLog::GENERATE.",'".$processID."')";
        //$processID = 'B0FB6E02-30AF-7CE1-A145-3501C5B9EB3A';

        try {
            DB::connection('sqlsrv2')->statement($sql);
            if($isSelected==1){
                $invoices = Invoice::where(['ProcessID'=>$processID])->get();
                $Invoice = clone $invoices[0];
                $PDFPath = Invoice::generate_pdf($Invoice->InvoiceID);
                $invoices[0]->update(['PDF'=>$PDFPath]);
                $Account = Account::find($Invoice->AccountID);
                $Currency = Currency::find($Account->CurrencyId);
                $CompanyName = Company::getName();
                if (!empty($Currency)) {
                    $Subject = "New Invoice " . $Invoice->FullInvoiceNumber . ' from ' . $CompanyName . ' ('.$Account->AccountName.')';
                    $RoundChargesAmount = get_round_decimal_places($Invoice->AccountID);

                    $data = [
                        'CompanyName' => $CompanyName,
                        'GrandTotal'       => number_format($Invoice->GrandTotal,$RoundChargesAmount),
                        'CurrencyCode'     =>$Currency->Code
                    ];
                    $Message = Invoice::getInvoiceEmailTemplate($data);
                    return View::make('invoices.email', compact('Invoice', 'Account', 'Subject','Message','CompanyName'));
                }
            }else {
                $invoices = Invoice::where(['ProcessID'=>$processID])->select(['InvoiceID'])->lists('InvoiceID');
                $invoicesIDs = implode(',',$invoices);
                if(!empty($invoicesIDs)){
                    $data['InvoiceIDs'] = $invoicesIDs;
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
                    $jobdata["created_at"] = date('Y-m-d H:i:s');
                    $jobdata["updated_at"] = date('Y-m-d H:i:s');
                    $JobID = Job::insertGetId($jobdata);
                    if($JobID){
                        return Response::json(array("status" => "success", "message" => "Bulk Invoice Send Job Added in queue to process.You will be notified once job is completed. "));
                    }else{
                        return Response::json(array("status" => "success", "message" => "Problem Creating Job Bulk Invoice Send."));
                    }
                }
            }
        } catch (Exception $e) {
            return Response::json(array("status" => "failed", "message" =>$e->getMessage()));
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
            $AccountBilling = AccountBilling::getBilling($AccountID);
            if (!empty($Account)) {
                $InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($AccountID);
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
							$AccountTaxRate = explode(",",AccountBilling::getTaxRate($AccountID));

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
        if (isset($data['account_id']) && $data['account_id'] > 0 )
		{
            $fields 			=	["CurrencyId","AccountID","Address1","Address2","Address3","City","PostCode","Country"];
            $Account 			= 	Account::where(["AccountID"=>$data['account_id']])->select($fields)->first();
            $Currency 			= 	Currency::where(["CurrencyId"=>$Account->CurrencyId])->pluck("Code");
            $InvoiceTemplateID  = 	AccountBilling::getInvoiceTemplateID($Account->AccountID);
            $CurrencyId 		= 	$Account->CurrencyId;
            $Address 			= 	Account::getFullAddress($Account);			
            $Terms 				= 	$FooterTerm = '';
			//$AccountTaxRate 	= 	explode(",",AccountBilling::getTaxRate($Account->AccountID));
			$AccountTaxRate 	= 	AccountBilling::getTaxRateType($Account->AccountID,TaxRate::TAX_ALL);
			 
            if(isset($InvoiceTemplateID) && $InvoiceTemplateID > 0) {
                $InvoiceTemplate	= 	InvoiceTemplate::find($InvoiceTemplateID);
                $Terms 				= 	$InvoiceTemplate->Terms;
                $FooterTerm 		= 	$InvoiceTemplate->FooterTerm;
                $return 			=	['Terms','FooterTerm','Currency','CurrencyId','Address','InvoiceTemplateID','AccountTaxRate'];
            } else {
                return Response::json(array("status" => "failed", "message" => "You cannot create RecurringInvoice as no Invoice Template assigned to this account." ));
            }			
            return Response::json(compact($return));
        }
    }

    /**
        Recurring Invoice Log
     */

    public function recurringinvoicelog($id,$type='')
    {
        $recurringinvoice = RecurringInvoice::find($id);
        $InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($recurringinvoice->AccountID);
        //$recurringinvoicenumber = RecurringInvoice::getFullRecurringInvoiceNumber($recurringinvoice,$InvoiceTemplateID);
        $recurringinvoicenumber = 0;
        return View::make('recurringinvoices.recurringinvoicelog', compact('recurringinvoice','id','recurringinvoicenumber','type'));
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
                $file_path = getenv('UPLOAD_PATH') .'/RecurringInvoice Log.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/RecurringInvoice Log.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query,'sqlsrv2')->make();
    }
}