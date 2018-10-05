<?php

class CreditNotesController extends \BaseController {

    public function ajax_datagrid_total()
    {
        $data 						 = 	Input::all();
        $data['iDisplayStart'] 		 =	0;
        $data['iDisplayStart'] 		+=	1;
        $data['iSortCol_0']			 =  0;
        $data['sSortDir_0']			 =  strtoupper('desc');
        $companyID 					 =  User::get_companyID();
        $columns 					 =  ['CreditNotesID','AccountName','CreditNotesNumber','IssueDate','GrandTotal','PendingAmount','CreditNotesStatus','CreditNotesID'];
        $data['IssueDateStart'] 	 =  empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd']        =  empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];
        $sort_column 				 =  $columns[$data['iSortCol_0']];

        $query = "call prc_getCreditNotes (".$companyID.",".intval($data['AccountID']).",'".$data['CreditNotesNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."','".$data['CreditNotesStatus']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',".intval($data['CurrencyID'])."";

        if(isset($data['Export']) && $data['Export'] == 1)
        {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            Excel::create('CreditNotes', function ($excel) use ($excel_data)
            {
                $excel->sheet('CreditNotes', function ($sheet) use ($excel_data)
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
        $columns 					 =  ['CreditNotesID','AccountName','CreditNotesNumber','CreditNotesID','GrandTotal','CreditNotesStatus','CreditNotesID','converted'];
        $data['IssueDateStart'] 	 =  empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd']        =  empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];
        $sort_column 				 =  $columns[$data['iSortCol_0']];
        $data['CurrencyID'] = empty($data['CurrencyID'])?'0':$data['CurrencyID'];
        if($data['CreditNotesStatus'] == "")
        {
            $data['CreditNotesStatus'] = 'open';
        }

        $query = "call prc_getCreditNotes (".$companyID.",".intval($data['AccountID']).",'".$data['CreditNotesNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."','".$data['CreditNotesStatus']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".strtoupper($data['sSortDir_0'])."',".intval($data['CurrencyID'])."";

        if(isset($data['Export']) && $data['Export'] == 1)
        {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');

            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/CreditNotes.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/CreditNotes.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /* Excel::create('CreditNotes', function ($excel) use ($excel_data)
             {
                 $excel->sheet('CreditNotes', function ($sheet) use ($excel_data)
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

    public function apply_creditnote_datagrid($AccountID)
    {
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['iDisplayStart'] 		+=	1;
        if(empty($data['InvoiceNumber']))
        {
            $data['InvoiceNumber'] = '';
        }
        $AccountInvoices = "call prc_getCreditNoteInvoices ('" . $AccountID . "','".$data['InvoiceNumber'] . "',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].")";

        return DataTableSql::of($AccountInvoices,'sqlsrv2')->make();

        /*if($data['InvoiceNumber'] == 0) {

            $AccountInvoices = Invoice::select(["tblInvoice.InvoiceID", "tblInvoice.FullInvoiceNumber", "tblInvoice.IssueDate", "tblInvoice.GrandTotal", DB::raw("(select IFNULL(SUM(Amount),0) from tblPayment where tblPayment.InvoiceID=tblInvoice.InvoiceID and tblPayment.Recall=0) as paidsum")])
                ->where('tblInvoice.AccountID', $AccountID)
                ->where('tblInvoice.GrandTotal','<>', 0)
                ->whereIn('tblInvoice.InvoiceStatus', array('partially_paid','send','awaiting'));
                //->groupBy('tblInvoice.InvoiceID');
        }
        else{

            $AccountInvoices = Invoice::select(["tblInvoice.InvoiceID", "tblInvoice.FullInvoiceNumber", "tblInvoice.IssueDate", "tblInvoice.GrandTotal", DB::raw("(select IFNULL(SUM(Amount),0) from tblPayment where tblPayment.InvoiceID=tblInvoice.InvoiceID and tblPayment.Recall=0) as paidsum")])
                ->where('tblInvoice.AccountID', $AccountID)
                ->where('tblInvoice.GrandTotal','<>', 0)
                ->where('tblInvoice.InvoiceNumber', $data['InvoiceNumber'])
                ->whereIn('tblInvoice.InvoiceStatus', array('partially_paid','send','awaiting'));

        }
        return Datatables::of($AccountInvoices)->make();*/
    }

    /**
     * Display a listing of the resource.
     * GET /creditnotes
     *
     * @return Response
     */
    public function index()
    {
        $CompanyID = User::get_companyID();
        $accounts = Account::getAccountIDList();
        $DefaultCurrencyID    	=   Company::where("CompanyID",$CompanyID)->pluck("CurrencyId");
        $creditnotes_status_json = json_encode(CreditNotes::get_creditnotes_status());
        return View::make('creditnotes.index',compact('accounts','creditnotes_status_json','DefaultCurrencyID','CompanyID'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /creditnotes/create
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

        return View::make('creditnotes.create',compact('accounts','products','taxes','BillingClass','DynamicFields','itemtypes'));

    }

    /**
     *
     * */
    public function edit($id)
    {
        //$str = preg_replace('/^INV/', '', 'INV021000');;
        if($id > 0)
        {
            $CreditNotes 				= 	 CreditNotes::find($id);
            //check if creditnotes are closed or not
            if($CreditNotes->CreditNotesStatus != 'close') {
                $CompanyID = $CreditNotes->CompanyID;
                $CreditNotesBillingClass = CreditNotes::GetCreditNotesBillingClass($CreditNotes);
                $CreditNotesDetail = CreditNotesDetail::where(["CreditNotesID" => $id])->get();
                $accounts = Account::getAccountIDList();
                $products = Product::getProductDropdownList($CompanyID);
                $Account = Account::where(["AccountID" => $CreditNotes->AccountID])->select(["AccountName", "BillingEmail", "CurrencyId"])->first(); //"TaxRateID","RoundChargesAmount","InvoiceTemplateID"
                $CurrencyID = !empty($CreditNotes->CurrencyID) ? $CreditNotes->CurrencyID : $Account->CurrencyId;
                $RoundChargesAmount = get_round_decimal_places($CreditNotes->AccountID);
                $InvoiceTemplateID = BillingClass::getInvoiceTemplateID($CreditNotesBillingClass);
                $CreditNotesNumberPrefix = ($InvoiceTemplateID > 0) ? InvoiceTemplate::find($InvoiceTemplateID)->CreditNotesNumberPrefix : '';
                $Currency = Currency::find($CurrencyID);
                $CurrencyCode = !empty($Currency) ? $Currency->Code : '';
                $CompanyName = Company::getName($CompanyID);
                $taxes = TaxRate::getTaxRateDropdownIDListForInvoice(0, $CompanyID);
                $CreditNotesAllTax = DB::connection('sqlsrv2')->table('tblCreditNotesTaxRate')->where(["CreditNotesID" => $id, "CreditNotesTaxType" => 1])->get();
                $BillingClass = BillingClass::getDropdownIDList($CompanyID);
                $itemtypes = ItemType::getItemTypeDropdownList($CompanyID);
                return View::make('creditnotes.edit', compact('id', 'itemtypes', 'CreditNotes', 'CreditNotesDetail', 'InvoiceTemplateID', 'CreditNotesNumberPrefix', 'CurrencyCode', 'CurrencyID', 'RoundChargesAmount', 'accounts', 'products', 'taxes', 'CompanyName', 'Account', 'CreditNotesAllTax', 'BillingClass', 'CreditNotesBillingClass'));
            }
            else{
                //if creditnotes are closed
                return Redirect::to(url('/creditnotes'));
            }
        }
    }

    public function apply_creditnotes($AccountID,$id)
    {
        if($AccountID > 0)
        {
            $Invoices 	    =   Invoice::GetInvoiceByAccount($AccountID);
            $AccountName    =   Account::find($AccountID)->AccountName;
            $invoicenumbers =   array("Select Invoices");
            foreach($Invoices as $invoice)
            {
                $invoicenumbers[$invoice->FullInvoiceNumber] = $invoice->FullInvoiceNumber;
            }
            $CreditNotes    = 	CreditNotes::find($id);
            $CompanyID      =   $CreditNotes->CompanyID;
            $CreditNotesID  =   $CreditNotes->CreditNotesID;

            return View::make('creditnotes.apply_creditnotes', compact( 'AccountID','AccountName','CompanyID','CreditNotesID','Invoices','invoicenumbers','CreditNotes'));
        }
    }

    public function store_creditnotes()
    {
        $data = Input::all();
        //echo"<pre>"; print_R($data);exit;
        $CurrencyId = Account::where(['AccountID'=>$data['AccountID']])->pluck('CurrencyId');
        if(!isset($data['payment']))
        {
            return Response::json(array("status" => "failed", "message" => "No Invoices Found"));
        }

        if(!array_filter($data['payment'])) {
            return Response::json(array("status" => "failed", "message" => "Enter Amount For atleast one Invoice"));
        }
        else{
            try {
                DB::connection('sqlsrv2')->beginTransaction();
                $totalamount = 0;
                //for loop of each invoices
                for ($i = 0; $i < count($data['invoice_id']); $i++) {
                    if ($data['payment'][$i] != "") {
                        $paymentdata = array();
                        $paymentdata['CompanyID'] = $data['CompanyID'];
                        $paymentdata['AccountID'] = $data['AccountID'];
                        $paymentdata['InvoiceNo'] = $data['invoice_number'][$i];
                        $paymentdata['PaymentDate'] = date('Y-m-d H:i:s');
                        $paymentdata['PaymentMethod'] = 'CREDIT NOTE';
                        $paymentdata['PaymentType'] = 'Payment In';
                        $paymentdata['Notes'] = 'Paid By Credit Note No. '.$data['CreditNoteNumber'];
                        $paymentdata['Amount'] = $data['payment'][$i];
                        $paymentdata['Status'] = 'Approved';
                        $paymentdata['created_at'] = date("Y-m-d H:i:s");
                        $paymentdata['updated_at'] = date("Y-m-d H:i:s");
                        $paymentdata['InvoiceID'] = $data['invoice_id'][$i];
                        $paymentdata['CreditNotesID'] = $data['CreditNotesID'];
                        $paymentdata['CurrencyId'] = $CurrencyId;
                        $creditnote_id = $data['CreditNotesID'];

                        //check specific invoice grand total is greter then creditnotes
                        $InvoiceAmount = Invoice::find($data['invoice_id'][$i])->GrandTotal;
                        $TotalPayAmount = DB::connection('sqlsrv2')->table('tblPayment')->where('InvoiceID', $data['invoice_id'][$i])->sum('Amount');
                        $totalpaidamount = $TotalPayAmount + $data['payment'][$i];
                        if($InvoiceAmount >= $totalpaidamount)
                        {
                            $payment_insert = Payment::insert($paymentdata);
                            $totalamount += $data['payment'][$i];

                            //update invoice status as paid or partially paid
                            $InvoiceData = array();
                            if($InvoiceAmount == $totalpaidamount)
                            {
                                $InvoiceData["InvoiceStatus"] = 'paid';
                                Invoice::find($data['invoice_id'][$i])->update($InvoiceData);
                            }
                            else{
                                $InvoiceData["InvoiceStatus"] = 'partially_paid';
                                Invoice::find($data['invoice_id'][$i])->update($InvoiceData);
                            }

                            $creditnotesloddata = array();
                            $creditnotesloddata['CreditNotesID']= $creditnote_id;
                            $creditnotesloddata['Note']= 'Paid For Invoice No : '.$data['invoice_number'][$i].' Amount : '.$data['payment'][$i];
                            $creditnotesloddata['created_at']= date("Y-m-d H:i:s");
                            $creditnotesloddata['CreditNotesLogStatus']= CreditNotesLog::PAID;
                            CreditNotesLog::insert($creditnotesloddata);
                        }
                        else{
                            return Response::json(array("status" => "failed", "message" => "CreditNote Amount is higher then Invoice Amount."));
                        }
                    }
                }
                if ($payment_insert == 1) {
                    $GrandTotal = CreditNotes::find($creditnote_id)->GrandTotal;
                    $PaidAmount = CreditNotes::find($creditnote_id)->PaidAmount;
                    $Available_Balance = $GrandTotal - $PaidAmount;
                    //check if total credit amount is less then available balance or not
                    if($Available_Balance >= $totalamount) {
                        $CreditNotesData['PaidAmount'] = $PaidAmount + $totalamount;
                        if (CreditNotes::find($creditnote_id)->update($CreditNotesData)) {
                            DB::connection('sqlsrv2')->commit();
                            $redirect_url = URL::previous();
                            return Response::json(array("status" => "success", "message" => "Credit Note Applied", "redirect" => $redirect_url));
                        }
                    }
                    else{
                        return Response::json(array("status" => "failed", "message" => "Not Enough Credit Available."));
                    }
                }
                else{
                    return Response::json(array("status" => "failed", "message" => "Failed to Apply Credit Note."));
                }
            }
            catch (Exception $e){
                Log::info($e);
                DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Problem Applying Credit Note. \n" . $e->getMessage()));
            }
        }
    }

    /**
     * Store CreditNotes
     */
    public function store(){
        $data = Input::all();
        //echo"<pre>"; print_R($data);exit;
        //unset($data['BarCode']);
        if($data){
            $companyID = User::get_companyID();
            $CreatedBy = User::get_user_full_name();

            //$CurrencyId = Account::where("AccountID",intval($data["AccountID"]))->pluck('CurrencyId');
            $isAutoCreditNotesNumber = true;
            $CreditNotesData = array();
            if(!empty($data["CreditNotesNumber"])){
                $isAutoCreditNotesNumber = false;
                $CreditNotesData["CreditNotesNumber"] =  $data["CreditNotesNumber"];
            }

            if(isset($data['BillingClassID']) && $data['BillingClassID']>0){
                $InvoiceTemplateID  = 	BillingClass::getInvoiceTemplateID($data['BillingClassID']);
                $CreditNotesData["CreditNotesNumber"] = $LastCreditNotesNumber = ($isAutoCreditNotesNumber)?InvoiceTemplate::getNextCreditNotesNumber($InvoiceTemplateID):$data["CreditNotesNumber"];
            }

            $CreditNotesData["CompanyID"] = $companyID;
            $CreditNotesData["AccountID"] = intval($data["AccountID"]);
            $CreditNotesData["Address"] = $data["Address"];

            $CreditNotesData["IssueDate"] = $data["IssueDate"];
            //$CreditNotesData["PONumber"] = $data["PONumber"];
            $CreditNotesData["SubTotal"] = str_replace(",","",$data["SubTotal"]);
            //$CreditNotesData["TotalDiscount"] = str_replace(",","",$data["TotalDiscount"]);
            $CreditNotesData["TotalDiscount"] = 0;
            $CreditNotesData["TotalTax"] = str_replace(",","",$data["TotalTax"]);
            $CreditNotesData["GrandTotal"] = floatval(str_replace(",","",$data["GrandTotalCreditNotes"]));
            //$CreditNotesData["GrandTotal"] = floatval(str_replace(",","",$data["GrandTotal"]));
            $CreditNotesData["CurrencyID"] = $data["CurrencyID"];
            $CreditNotesData["CreditNotesType"] = CreditNotes::INVOICE_OUT;
            $CreditNotesData["CreditNotesStatus"] = CreditNotes::OPEN;
            // $CreditNotesData["ItemCreditNotes"] = CreditNotes::ITEM_INVOICE;
            $CreditNotesData["Note"] = $data["Note"];
            $CreditNotesData["Terms"] = $data["Terms"];
            $CreditNotesData["FooterTerm"] = $data["FooterTerm"];
            $CreditNotesData["CreatedBy"] = $CreatedBy;
            $CreditNotesData['CreditNotesTotal'] = str_replace(",","",$data["GrandTotal"]);
            $CreditNotesData['BillingClassID'] =$data["BillingClassID"];

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
                'CreditNotesNumber' => 'required|unique:tblCreditNotes,CreditNotesNumber,NULL,CreditNotesID,CompanyID,'.$companyID,
                'IssueDate' => 'required',
                'CurrencyID' => 'required',
                'GrandTotal' => 'required',
                //'CreditNotesType' => 'required',
            );
            $message = ['BillingClassID.required'=>'Billing Class field is required','AccountID'=>'Client field is required','AccountID.min'=>'Client field is required'];
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($CreditNotesData, $rules,$message);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if(empty($data["CreditNotesDetail"])) {
                return json_encode(["status"=>"failed","message"=>"Please select atleast one item."]);
            }

            try{
                $CreditNotesData["FullCreditNotesNumber"] = ($isAutoCreditNotesNumber)?InvoiceTemplate::find($InvoiceTemplateID)->CreditNotesNumberPrefix.$LastCreditNotesNumber:$LastCreditNotesNumber;
                DB::connection('sqlsrv2')->beginTransaction();
                $CreditNotes = CreditNotes::create($CreditNotesData);
                //Store Last CreditNotes Number.
                if($isAutoCreditNotesNumber) {
                    InvoiceTemplate::find($InvoiceTemplateID)->update(array("LastCreditNotesNumber" => $LastCreditNotesNumber ));
                }

                $CreditNotesDetailData = $CreditNotesTaxRates = $CreditNotesAllTaxRates = array();

                foreach($data["CreditNotesDetail"] as $field => $detail){
                    $i=0;
                    foreach($detail as $value){
                        if( in_array($field,["Price","Discount","TaxAmount","LineTotal"])){
                            $CreditNotesDetailData[$i][$field] = str_replace(",","",$value);
                        }else if($field == "ProductID"){
                            if(!empty($value)) {
                                $pid = explode('-', $value);
                                $CreditNotesDetailData[$i][$field] = $pid[1];
                            } else {
                                $CreditNotesDetailData[$i][$field] = "";
                            }
                        }else{
                            $CreditNotesDetailData[$i][$field] = $value;
                        }
                        $CreditNotesDetailData[$i]["Discount"] 	= 	0;
                        $CreditNotesDetailData[$i]["CreditNotesID"] = $CreditNotes->CreditNotesID;
                        $CreditNotesDetailData[$i]["created_at"] = date("Y-m-d H:i:s");
                        $CreditNotesDetailData[$i]["CreatedBy"] = $CreatedBy;

                        if(empty($CreditNotesDetailData[$i]['ProductID'])){
                            unset($CreditNotesDetailData[$i]);
                        }
                        $i++;
                    }
                }

                //product tax
                if(isset($data['Tax']) && is_array($data['Tax'])){
                    foreach($data['Tax'] as $j => $taxdata){
                        $CreditNotesTaxRates[$j]['TaxRateID'] 	= 	$j;
                        $CreditNotesTaxRates[$j]['Title'] 		= 	TaxRate::getTaxName($j);
                        $CreditNotesTaxRates[$j]["created_at"] 	= 	date("Y-m-d H:i:s");
                        $CreditNotesTaxRates[$j]["CreditNotesID"] 	= 	$CreditNotes->CreditNotesID;
                        $CreditNotesTaxRates[$j]["TaxAmount"] 	= 	$taxdata;
                    }
                }

                //CreditNotes tax
                if(isset($data['CreditNotesTaxes']) && is_array($data['CreditNotesTaxes'])){
                    foreach($data['CreditNotesTaxes']['field'] as  $p =>  $CreditNotesTaxes){
                        if(!empty($CreditNotesTaxes)) {
                            $CreditNotesAllTaxRates[$p]['TaxRateID'] = $CreditNotesTaxes;
                            $CreditNotesAllTaxRates[$p]['Title'] = TaxRate::getTaxName($CreditNotesTaxes);
                            $CreditNotesAllTaxRates[$p]["created_at"] = date("Y-m-d H:i:s");
                            $CreditNotesAllTaxRates[$p]["CreditNotesTaxType"] = 1;
                            $CreditNotesAllTaxRates[$p]["CreditNotesID"] = $CreditNotes->CreditNotesID;
                            $CreditNotesAllTaxRates[$p]["TaxAmount"] = $data['CreditNotesTaxes']['value'][$p];
                        }
                    }
                }

                //$CreditNotesTaxRates 	 = 	merge_tax($CreditNotesTaxRates);
               // $CreditNotesAllTaxRates  = 	merge_tax($CreditNotesAllTaxRates);

                $creditnotesloddata = array();
                $creditnotesloddata['CreditNotesID']= $CreditNotes->CreditNotesID;
                $creditnotesloddata['Note']= 'Created By '.$CreatedBy;
                $creditnotesloddata['created_at']= date("Y-m-d H:i:s");
                $creditnotesloddata['CreditNotesLogStatus']= CreditNotesLog::CREATED;
                CreditNotesLog::insert($creditnotesloddata);
                /*if(!empty($CreditNotesTaxRates)) { //product tax
                    CreditNotesTaxRate::insert($CreditNotesTaxRates);
                }*/

                if(!empty($CreditNotesAllTaxRates)) { //CreditNotes tax
                    CreditNotesTaxRate::insert($CreditNotesAllTaxRates);
                }
                if (!empty($CreditNotesDetailData) && CreditNotesDetail::insert($CreditNotesDetailData)) {
                    $CreditNotesTaxRates1=CreditNotesTaxRate::getCreditNotesTaxRateByProductDetail($CreditNotes->CreditNotesID);
                    if(!empty($CreditNotesTaxRates1)) { //Invoice tax
                        CreditNotesTaxRate::insert($CreditNotesTaxRates1);
                    }

                    $pdf_path = CreditNotes::generate_pdf($CreditNotes->CreditNotesID);
                    if (empty($pdf_path)) {
                        $error['message'] = 'Failed to generate Credit Notes PDF File';
                        $error['status'] = 'failure';
                        return $error;
                    } else {
                        $CreditNotes->update(["PDF" => $pdf_path]);
                    }

                    DB::connection('sqlsrv2')->commit();
                    $SuccessMsg="Credit Notes Successfully Created.";
                    $message='';
                    /* if(!empty($historyData)){
                         foreach($historyData as $msg){
                             $message.=$msg;
                             $message.="\n\r";
                         }
                     }*/
                    return Response::json(array("status" => "success","warning"=>$message, "message" => $SuccessMsg,'LastID'=>$CreditNotes->CreditNotesID,'redirect' => URL::to('/creditnotes/'.$CreditNotes->CreditNotesID.'/edit')));
                } else {
                    DB::connection('sqlsrv2')->rollback();
                    return Response::json(array("status" => "failed", "message" => "Problem Creating Credit Notes."));
                }
            }catch (Exception $e){
                Log::info($e);
                DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Problem Creating Credit notes. \n" . $e->getMessage()));
            }

        }

    }

    /**
     * Store CreditNotes
     */
    public function update($id){
        $data = Input::all();
        //unset($data['BarCode']);
        if(!empty($data) && $id > 0){
            $CreditNotes = CreditNotes::find($id);
            $companyID = User::get_companyID();
            $CreatedBy = User::get_user_full_name();
            $FullCreditNotesNumber=$CreditNotes->FullCreditNotesNumber;
            $OldProductsarr=CreditNotesDetail::where(['CreditNotesID'=>$CreditNotes->CreditNotesID])->get(['ProductID','Qty','ProductType','CreditNotesDetailID'])->toArray();

            $CreditNotesData = array();
            $CreditNotesData["CompanyID"] = $companyID;
            $CreditNotesData["AccountID"] = $data["AccountID"];
            $CreditNotesData["Address"] = $data["Address"];
            $CreditNotesData["CreditNotesNumber"] = $data["CreditNotesNumber"];
            $CreditNotesData["IssueDate"] = $data["IssueDate"];
            //$CreditNotesData["PONumber"] = $data["PONumber"];
            $CreditNotesData["SubTotal"] = str_replace(",","",$data["SubTotal"]);
            //$CreditNotesData["TotalDiscount"] = str_replace(",","",$data["TotalDiscount"]);
            $CreditNotesData["TotalDiscount"] = 0;
            $CreditNotesData["TotalTax"] = str_replace(",","",$data["TotalTax"]);
            $CreditNotesData["GrandTotal"] = floatval(str_replace(",","",$data["GrandTotalCreditNotes"]));
            $CreditNotesData["CurrencyID"] = $data["CurrencyID"];
            $CreditNotesData["Note"] = $data["Note"];
            $CreditNotesData["Terms"] = $data["Terms"];
            $CreditNotesData["FooterTerm"] = $data["FooterTerm"];
            $CreditNotesData["ModifiedBy"] = $CreatedBy;
            $CreditNotesData['CreditNotesTotal'] = str_replace(",","",$data["GrandTotal"]);
            //$CreditNotesData["CreditNotesType"] = CreditNotes::INVOICE_OUT;

            ///////////
            $rules = array(
                'CompanyID' => 'required',
                'AccountID' => 'required',
                'Address' => 'required',
                'CreditNotesNumber' => 'required|unique:tblCreditNotes,CreditNotesNumber,'.$id.',CreditNotesID,CompanyID,'.$companyID,
                'IssueDate' => 'required',
                'CurrencyID' => 'required',
                'GrandTotal' => 'required',
                //'CreditNotesType' => 'required',
            );
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');
            $validator = Validator::make($CreditNotesData, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if(empty($data["CreditNotesDetail"])) {
                return json_encode(["status"=>"failed","message"=>"Please select atleast one item."]);
            }

            try{

                DB::connection('sqlsrv2')->beginTransaction();
                if(isset($CreditNotes->CreditNotesID)) {

                    $Extralognote = '';
                    if($CreditNotes->GrandTotal != $CreditNotesData['GrandTotal']){
                        $Extralognote = ' Total '.$CreditNotes->GrandTotal.' To '.$CreditNotesData['GrandTotal'];
                    }
                    $creditnotesloddata = array();
                    $creditnotesloddata['CreditNotesID']= $CreditNotes->CreditNotesID;
                    $creditnotesloddata['Note']= 'Updated By '.$CreatedBy.$Extralognote;
                    $creditnotesloddata['created_at']= date("Y-m-d H:i:s");
                    $creditnotesloddata['CreditNotesLogStatus']= CreditNotesLog::UPDATED;
                    $CreditNotes->update($CreditNotesData);
                    CreditNotesLog::insert($creditnotesloddata);
                    $CreditNotesDetailData = $StockHistoryData = $CreditNotesTaxRates = $CreditNotesAllTaxRates = array();
                    //Delete all CreditNotes Data and then Recreate.
                    CreditNotesDetail::where(["CreditNotesID" => $CreditNotes->CreditNotesID])->delete();
                    CreditNotesTaxRate::where(["CreditNotesID" => $CreditNotes->CreditNotesID])->delete();
                    if (isset($data["CreditNotesDetail"])) {
                        foreach ($data["CreditNotesDetail"] as $field => $detail) {
                            $i = 0;
                            foreach ($detail as $value) {
                                if( in_array($field,["Price","Discount","TaxAmount","LineTotal"])){
                                    $CreditNotesDetailData[$i][$field] = str_replace(",","",$value);
                                }else if($field == "ProductID"){
                                    if(!empty($value)) {
                                        $pid = explode('-', $value);
                                        $CreditNotesDetailData[$i][$field] = $pid[1];
                                        $StockHistoryData[$i][$field] = $pid[1];
                                        $StockHistoryData[$i]["CreditNotesID"] = $CreditNotes->CreditNotesID;

                                        /**
                                         *  1. if product is new not in old and exists in new
                                         *  2. existinsg product checke update qty with old
                                         */

                                    } else {
                                        $CreditNotesDetailData[$i][$field] = "";
                                        $StockHistoryData[$i][$field] = "";
                                    }
                                }else{
                                    $CreditNotesDetailData[$i][$field] = $value;
                                    $StockHistoryData[$i][$field] = $value;
                                }
                                $CreditNotesDetailData[$i]["Discount"] 	= 	0;
                                $CreditNotesDetailData[$i]["CreditNotesID"] = $CreditNotes->CreditNotesID;
                                $CreditNotesDetailData[$i]["created_at"] = date("Y-m-d H:i:s");
                                $CreditNotesDetailData[$i]["updated_at"] = date("Y-m-d H:i:s");
                                $CreditNotesDetailData[$i]["CreatedBy"] = $CreatedBy;
                                $CreditNotesDetailData[$i]["ModifiedBy"] = $CreatedBy;
                                if(isset($CreditNotesDetailData[$i]["CreditNotesDetailID"])){
                                    unset($CreditNotesDetailData[$i]["CreditNotesDetailID"]);
                                }
                                if(empty($CreditNotesDetailData[$i]['ProductID'])){
                                    unset($CreditNotesDetailData[$i]);
                                }
                                /*if($field == 'TaxRateID'){
									$txname = TaxRate::getTaxName($value);
                                    $CreditNotesTaxRates[$txname][$j][$field] = $value;
                                    $CreditNotesTaxRates[$txname][$j]['Title'] = TaxRate::getTaxName($value);
                                    $CreditNotesTaxRates[$txname][$j]["created_at"] = date("Y-m-d H:i:s");
                                    $CreditNotesTaxRates[$txname][$j]["CreditNotesID"] = $CreditNotes->CreditNotesID;
                                }
								if($field == 'TaxRateID2'){
									$txname = TaxRate::getTaxName($value);
                                    $CreditNotesTaxRates[$txname][$j][$field] = $value;
                                    $CreditNotesTaxRates[$txname][$j]['Title'] = TaxRate::getTaxName($value);
                                    $CreditNotesTaxRates[$txname][$j]["created_at"] = date("Y-m-d H:i:s");
                                    $CreditNotesTaxRates[$txname][$j]["CreditNotesID"] = $CreditNotes->CreditNotesID;
                                }
                                if($field == 'TaxAmount'){
                                    $CreditNotesTaxRates[$txname][$field] = str_replace(",","",$value);
                                }*/
                                $i++;
                            }
                        }

                        if(isset($data['Tax']) && is_array($data['Tax'])){
                            foreach($data['Tax'] as $j => $taxdata)
                            {
                                $CreditNotesTaxRates[$j]['TaxRateID'] 	= 	$j;
                                $CreditNotesTaxRates[$j]['Title'] 		= 	TaxRate::getTaxName($j);
                                $CreditNotesTaxRates[$j]["created_at"] 	= 	date("Y-m-d H:i:s");
                                $CreditNotesTaxRates[$j]["CreditNotesID"] 	= 	$CreditNotes->CreditNotesID;
                                $CreditNotesTaxRates[$j]["TaxAmount"] 	= 	$taxdata;
                            }
                        }

                        if(isset($data['CreditNotesTaxes']) && is_array($data['CreditNotesTaxes'])){
                            foreach($data['CreditNotesTaxes']['field'] as  $p =>  $CreditNotesTaxes){
                                if(!empty($CreditNotesTaxes)) {
                                    $CreditNotesAllTaxRates[$p]['TaxRateID'] = $CreditNotesTaxes;
                                    $CreditNotesAllTaxRates[$p]['Title'] = TaxRate::getTaxName($CreditNotesTaxes);
                                    $CreditNotesAllTaxRates[$p]["created_at"] = date("Y-m-d H:i:s");
                                    $CreditNotesAllTaxRates[$p]["CreditNotesTaxType"] = 1;
                                    $CreditNotesAllTaxRates[$p]["CreditNotesID"] = $CreditNotes->CreditNotesID;
                                    $CreditNotesAllTaxRates[$p]["TaxAmount"] = $data['CreditNotesTaxes']['value'][$p];
                                }
                            }
                        }

                       // $CreditNotesTaxRates 	  =     merge_tax($CreditNotesTaxRates);
                        //$CreditNotesAllTaxRates   = 	merge_tax($CreditNotesAllTaxRates);

                      /*  if(!empty($CreditNotesTaxRates)) { //product tax
                            CreditNotesTaxRate::insert($CreditNotesTaxRates);
                        }*/

                        if(!empty($CreditNotesAllTaxRates)) { //CreditNotes tax
                            CreditNotesTaxRate::insert($CreditNotesAllTaxRates);
                        }

                        if (!empty($CreditNotesDetailData) && CreditNotesDetail::insert($CreditNotesDetailData)) {
                            $CreditNotesTaxRates1=CreditNotesTaxRate::getCreditNotesTaxRateByProductDetail($CreditNotes->CreditNotesID);
                            if(!empty($CreditNotesTaxRates1)) { //Invoice tax
                                CreditNotesTaxRate::insert($CreditNotesTaxRates1);
                            }

                            $pdf_path = CreditNotes::generate_pdf($CreditNotes->CreditNotesID);
                            if (empty($pdf_path)) {
                                $error['message'] = 'Failed to generate CreditNotes PDF File';
                                $error['status'] = 'failure';
                                return $error;
                            } else {
                                $CreditNotes->update(["PDF" => $pdf_path]);
                            }

                            //StockHistory Maintain
                            $MultiProductSumQtyArr=array();
                            $OldProductsarr=sumofQtyIfSameProduct($OldProductsarr);
                            $MultiProductSumQtyArr=sumofQtyIfSameProduct($CreditNotesDetailData);

                            $StockHistory=array();
                            $temparray=array();

                            //For Create New If not Exist
                            foreach($MultiProductSumQtyArr as $CreditNotesHistory){
                                $prodType=intval($CreditNotesHistory['ProductType']);
                                if($prodType==1) {
                                    $ProdID = intval($CreditNotesHistory['ProductID']);
                                    $CreditNotesID = intval($CreditNotesHistory['CreditNotesID']);
                                    $Qty = intval($CreditNotesHistory['Qty']);
                                    $key_of_arr = searchArrayByProductID($ProdID, $OldProductsarr);
                                    if(intval($key_of_arr) < 0){
                                        //Create New
                                        $temparray['CompanyID']=$companyID;
                                        $temparray['ProductID']=$ProdID;
                                        $temparray['CreditNotesID']=$CreditNotesID;
                                        $temparray['Qty']=$Qty;
                                        $temparray['Reason']='';
                                        $temparray['CreditNotesNumber']=$FullCreditNotesNumber;
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
                                    $CreditNotesDetailID=$OldProduct['CreditNotesDetailID'];
                                    $CreditNotesNo=CreditNotes::where('CreditNotesID',$id)->pluck('FullCreditNotesNumber');
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
                                    $temparrayUpdate['CreditNotesID']=$id;
                                    $temparrayUpdate['CreditNotesNumber']=$CreditNotesNo;
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
                            return Response::json(array("status" => "success","warning"=>$message, "message" => "CreditNotes Successfully Updated", 'LastID' => $CreditNotes->CreditNotesID));
                        } else {
                            DB::connection('sqlsrv2')->rollback();
                            return Response::json(array("status" => "failed", "message" => "Problem Updating CreditNotes."));
                        }
                    }else{
                        return Response::json(array("status" => "success", "message" => "CreditNotes Successfully Updated, There is no product in CreditNotes", 'LastID' => $CreditNotes->CreditNotesID));
                    }
                }
            }catch (Exception $e){
                DB::connection('sqlsrv2')->rollback();
                return Response::json(array("status" => "failed", "message" => "Problem Updating CreditNotes. \n " . $e->getMessage()));
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

                            $ProductDescription = $Subscription->CreditNotesLineDescription;

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

                        $error = "No CreditNotes Template Assigned to Account";
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

            $Terms = $FooterTerm = $CreditNotesToAddress ='';

            $AccountTaxRate = AccountBilling::getTaxRateType($Account->AccountID,TaxRate::TAX_ALL);
            //\Illuminate\Support\Facades\Log::error(print_r($TaxRates, true));

            // if(isset($InvoiceTemplateID) && $InvoiceTemplateID > 0) {
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            /* for item invoice generate - invoice to address as invoice template */

            if(isset($InvoiceTemplateID) && $InvoiceTemplateID > 0) {
                $message = $InvoiceTemplate->InvoiceTo;
                $replace_array = Invoice::create_accountdetails($Account);
                $text = Invoice::getInvoiceToByAccount($message,$replace_array);
                $CreditNotesToAddress = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
                $Terms = $InvoiceTemplate->Terms;
                $FooterTerm = $InvoiceTemplate->FooterTerm;
            }
            else{
                $CreditNotesToAddress 	= 	'';
                $Terms 				= 	'';
                $FooterTerm 		= 	'';
            }
            $BillingClassID     =   AccountBilling::getBillingClassID($data['account_id']);

            $return = ['Terms','FooterTerm','Currency','CurrencyId','Address','InvoiceTemplateID','AccountTaxRate','CreditNotesToAddress','BillingClassID'];
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
            $Terms = $FooterTerm = $CreditNotesToAddress ='';
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            /* for item invoice generate - invoice to address as invoice template */

            if(isset($InvoiceTemplateID) && $InvoiceTemplateID > 0) {
                $message = $InvoiceTemplate->InvoiceTo;
                $replace_array = Invoice::create_accountdetails($Account);
                $text = Invoice::getInvoiceToByAccount($message,$replace_array);
                $CreditNotesToAddress = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
                $Terms = $InvoiceTemplate->Terms;
                $FooterTerm = $InvoiceTemplate->FooterTerm;
                $AccountTaxRate  = BillingClass::getTaxRateType($data['BillingClassID'],TaxRate::TAX_ALL);
                $return = ['Terms','FooterTerm','InvoiceTemplateID','CreditNotesToAddress','AccountTaxRate'];
            }else{
                return Response::json(array("status" => "failed", "message" => "You can not create Invoice for this Account. as It has no Invoice Template assigned" ));
            }
            return Response::json(compact($return));
        }
    }

    public function delete($id)
    {
        if( $id > 0){
            $CreditNotesUsed = Payment::where('CreditNotesID','=',$id)->count();
            if($CreditNotesUsed == 0)
            {
                try{
                    DB::connection('sqlsrv2')->beginTransaction();
                    CreditNotesTaxRate::where(["CreditNotesID"=>$id])->delete();
                    CreditNotesDetail::where(["CreditNotesID"=>$id])->delete();
                    CreditNotes::find($id)->delete();
                    DB::connection('sqlsrv2')->commit();
                    return Response::json(array("status" => "success", "message" => "CreditNotes Successfully Deleted"));

                }catch (Exception $e){
                    DB::connection('sqlsrv2')->rollback();
                    return Response::json(array("status" => "failed", "message" => "CreditNotes Delete Failed \n" . $e->getMessage() ));
                }
            }
            else{
                return Response::json(array("status" => "failed", "message" => "Invoice Already Paid Using this CreditNotes" ));
            }
        }
    }


    public function print_preview($id) {
        //not in use.

        $CreditNotes = CreditNotes::find($id);
        $CreditNotesDetail = CreditNotesDetail::where(["CreditNotesID"=>$id])->get();
        $Account  = Account::find($CreditNotes->AccountID);
        $Currency = Currency::find($Account->CurrencyId);
        $CurrencyCode = !empty($Currency)?$Currency->Code:'';
        $InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($CreditNotes->AccountID);
        $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
        if(empty($InvoiceTemplate->CompanyLogoUrl)){
            $logo = 'http://placehold.it/250x100';
        }else{
            $logo = AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key);
        }
        return View::make('creditnotes.creditnotes_view', compact('CreditNotes','CreditNotesDetail','Account','InvoiceTemplate','CurrencyCode','logo'));
    }
    public function creditnotes_preview($id)
    {
        $CreditNotes = CreditNotes::find($id);
        if(!empty($CreditNotes))
        {
            $CreditNotesDetail 	= 	CreditNotesDetail::where(["CreditNotesID" => $id])->get();
            $Account 			= 	Account::find($CreditNotes->AccountID);
            $Currency 			= 	Currency::find($Account->CurrencyId);
            $CurrencyCode 		= 	!empty($Currency) ? $Currency->Code : '';
            $CurrencySymbol 	= 	Currency::getCurrencySymbol($Account->CurrencyId);
            $creditnotes_status = 	 CreditNotes::get_creditnotes_status();
            $CreditNotesStatus  =   $creditnotes_status[$CreditNotes->CreditNotesStatus];
            $InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($CreditNotes->AccountID);
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            if(empty($InvoiceTemplate->CompanyLogoUrl)){
                $logo = 'http://placehold.it/250x100';
            }else{
                $logo = AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key);
            }
            //$CreditNotesComments =   CreditNotesLog::get_comments_count($id);
            return View::make('creditnotes.creditnotes_cview', compact('CreditNotes', 'CreditNotesDetail', 'Account', 'CreditNotesTemplate', 'CurrencyCode', 'logo','CurrencySymbol','CreditNotesStatus'));
        }
    }

    // not in use
    public function pdf_view($id) {


        // check if CreditNotes has usege or Subscription then download PDF directly.
        $hasUsageInCreditNotes =  CreditNotesDetail::where("CreditNotesID",$id)
            ->Where(function($query)
            {
                $query->where("ProductType",Product::USAGE)
                    ->orWhere("ProductType",Product::SUBSCRIPTION);
            })->count();
        if($hasUsageInCreditNotes > 0){
            $PDF = CreditNotes::where("CreditNotesID",$id)->pluck("PDF");
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
            $CreditNotesID = intval($account_inv[1]);
            $CreditNotes = CreditNotes::where(["CreditNotesID"=>$CreditNotesID,"AccountID"=>$AccountID])->first();
            if(count($CreditNotes)>0) {
                $creditnotesloddata = array();
                $creditnotesloddata['Note']= 'Viewed By Unknown';
                if(!empty($_GET['email'])){
                    $creditnotesloddata['Note']= 'Viewed By '. $_GET['email'];
                }

                $creditnotesloddata['CreditNotesID']= $CreditNotes->CreditNotesID;
                $creditnotesloddata['created_at']= date("Y-m-d H:i:s");
                $creditnotesloddata['CreditNotesLogStatus']= CreditNotesLog::VIEWED;
                CreditNotesLog::insert($creditnotesloddata);

                return self::creditnotes_preview($CreditNotesID);
            }
        }
        echo "Something Went wrong";
    }

    // not in use
    public function cpdf_view($id){
        $account_inv = explode('-',$id);
        if(isset($account_inv[0]) && $account_inv[0] > 0 && isset($account_inv[1]) && $account_inv[1] > 0  ) {
            $AccountID = intval($account_inv[0]);
            $CreditNotesID = intval($account_inv[1]);
            $CreditNotes = CreditNotes::where(["CreditNotesID" => $CreditNotesID, "AccountID" => $AccountID])->first();
            if (count($CreditNotes) > 0) {
                return $this->pdf_view($CreditNotesID);
            }
        }
//        echo "Something Went wrong";
    }

    //Generate Item Based CreditNotes PDF - not using
    public function generate_pdf($id){
        if($id>0) {
            $CreditNotes = CreditNotes::find($id);
            $CreditNotesDetail = CreditNotesDetail::where(["CreditNotesID" => $id])->get();
            $Account = Account::find($CreditNotes->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency)?$Currency->Code:'';
            $InvoiceTemplateID = CreditNotes::GetInvoiceTemplateID($CreditNotes);
            //$InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($CreditNotes->AccountID);
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            if (empty($InvoiceTemplate->CompanyLogoUrl)) {
                $as3url =  public_path("/assets/images/250x100.png");
            } else {
                $as3url = (AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key));
            }
            $logo_path = CompanyConfiguration::get('UPLOAD_PATH') . '/logo/' . $Account->CompanyId;
            @mkdir($logo_path, 0777, true);
            RemoteSSH::run("chmod -R 777 " . $logo_path);
            $logo = $logo_path  . '/'  . basename($as3url);
            file_put_contents($logo, file_get_contents($as3url));
            chmod($logo,0777);
            $usage_data = array();
            $file_name = 'CreditNotes--' . date('d-m-Y') . '.pdf';
            if($InvoiceTemplate->CreditNotesPages == 'single_with_detail') {
                foreach ($CreditNotesDetail as $Detail) {
                    if (isset($Detail->StartDate) && isset($Detail->EndDate) && $Detail->StartDate != '1900-01-01' && $Detail->EndDate != '1900-01-01') {

                        $companyID = $Account->CompanyId;
                        $start_date = $Detail->StartDate;
                        $end_date = $Detail->EndDate;
                        $pr_name = 'call prc_getCreditNotesUsage (';

                        $query = $pr_name . $companyID . ",'" . $CreditNotes->AccountID . "','" . $start_date . "','" . $end_date . "')";
                        DB::connection('sqlsrv2')->setFetchMode(PDO::FETCH_ASSOC);
                        $usage_data = DB::connection('sqlsrv2')->select($query);
                        $usage_data = json_decode(json_encode($usage_data), true);
                        $file_name =  'CreditNotes-From-' . Str::slug($start_date) . '-To-' . Str::slug($end_date) . '.pdf';
                        break;
                    }
                }
            }
            $print_type = 'CreditNotes';
            $body = View::make('creditnotes.pdf', compact('CreditNotes', 'CreditNotesDetail', 'Account', 'InvoiceTemplate', 'usage_data', 'CurrencyCode', 'logo','print_type'))->render();
            $destination_dir = CompanyConfiguration::get('UPLOAD_PATH') . '/'. AmazonS3::generate_path(AmazonS3::$dir['CREDITNOTES_UPLOAD'],$Account->CompanyId) ;
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

    public function bulk_creditnotes(){
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
            return json_encode(["status" => "success", "message" => "Bulk CreditNotes Job Added in queue to process.You will be notified once job is completed. "]);
        }else{
            return json_encode(array("status" => "failed", "message" => "Problem Creating Bulk CreditNotes."));
        }

    }

    public function add_creditnotes_in(){
        $data = Input::all();

        $CompanyID = User::get_companyID();
        $rules = array(
            'AccountID' => 'required',
            'IssueDate' => 'required',
            'StartDate' => 'required',
            'EndDate' => 'required',
            'GrandTotal'=>'required|numeric',
            'CreditNotesNumber'=>'required|unique:tblCreditNotes,CreditNotesNumber',
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

        $CreditNotesData = array();
        $CreditNotesData["CompanyID"] = $CompanyID;
        $CreditNotesData["AccountID"] = $data["AccountID"];
        $CreditNotesData["Address"] = $Address;
        $CreditNotesData["CreditNotesNumber"] = $data["CreditNotesNumber"];
        $CreditNotesData["FullCreditNotesNumber"] = $data["CreditNotesNumber"];
        $CreditNotesData["IssueDate"] = $data["IssueDate"];
        $CreditNotesData["GrandTotal"] = floatval(str_replace(",","",$data["GrandTotal"]));
        $CreditNotesData["CurrencyID"] = $Account->CurrencyId;
        $CreditNotesData["CreditNotesType"] = CreditNotes::INVOICE_IN;
        if(isset($fullPath)) {
            $CreditNotesData["Attachment"] = $fullPath;
        }
        $CreditNotesData["CreatedBy"] = $CreatedBy;
        if($CreditNotes = CreditNotes::create($CreditNotesData)) {
            $CreditNotesDetailData =array();
            $CreditNotesDetailData['CreditNotesID'] = $CreditNotes->CreditNotesID;
            $CreditNotesDetailData['StartDate'] = $data['StartDate'];
            $CreditNotesDetailData['EndDate'] = $data['EndDate'];
            $CreditNotesDetailData['TotalMinutes'] = $data['TotalMinutes'];
            $CreditNotesDetailData['Price'] = floatval(str_replace(",","",$data["GrandTotal"]));
            $CreditNotesDetailData['Qty'] = 1;
            $CreditNotesDetailData['ProductType'] = Product::INVOICE_PERIOD;
            $CreditNotesDetailData['LineTotal'] = floatval(str_replace(",","",$data["GrandTotal"]));
            $CreditNotesDetailData["created_at"] = date("Y-m-d H:i:s");
            $CreditNotesDetailData['Description'] = 'CreditNotes In';
            $CreditNotesDetailData['ProductID'] = 0;
            $CreditNotesDetailData["CreatedBy"] = $CreatedBy;
            CreditNotesDetail::insert($CreditNotesDetailData);

            //if( $data["DisputeTotal"] != '' && $data["DisputeDifference"] != '' && $data["DisputeMinutes"] != '' && $data["MinutesDifference"] != '' ){
            if( !empty($data["DisputeAmount"])  ){

                //Dispute::add_update_dispute(array( "DisputeID"=> $data["DisputeID"],  "CreditNotesID"=>$CreditNotes->CreditNotesID,"DisputeTotal"=>$data["DisputeTotal"],"DisputeDifference"=>$data["DisputeDifference"],"DisputeDifferencePer"=>$data["DisputeDifferencePer"],"DisputeMinutes"=>$data["DisputeMinutes"],"MinutesDifference"=>$data["MinutesDifference"],"MinutesDifferencePer"=>$data["MinutesDifferencePer"]));
                Dispute::add_update_dispute(array( "DisputeID"=> $data["DisputeID"],"CreditNotesType"=>CreditNotes::INVOICE_IN,  "AccountID"=> $data["AccountID"], "CreditNotesNo"=>$data["CreditNotesNumber"],"DisputeAmount"=>$data["DisputeAmount"],"sendEmail"=>1));

            }

            return Response::json(["status" => "success", "message" => "CreditNotes in Created successfully. ".$message]);

        }else{
            return Response::json(["status" => "success", "message" => "Problem Updating CreditNotes"]);
        }

    }
    public function update_creditnotes_in($id){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $rules = array(
            'AccountID' => 'required',
            'IssueDate' => 'required',
            'GrandTotal'=>'required|numeric',
            'CreditNotesNumber' => 'required|unique:tblCreditNotes,CreditNotesNumber,'.$id.',CreditNotesID,CompanyID,'.$CompanyID,
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

        $CreditNotesData = array();
        $CreditNotesData["CompanyID"] = $CompanyID;
        $CreditNotesData["AccountID"] = $data["AccountID"];
        $CreditNotesData["Address"] = $Address;
        $CreditNotesData["CreditNotesNumber"] = $data["CreditNotesNumber"];
        $CreditNotesData["FullCreditNotesNumber"] = $data["CreditNotesNumber"];
        $CreditNotesData["IssueDate"] = $data["IssueDate"];
        $CreditNotesData["GrandTotal"] = floatval(str_replace(",","",$data["GrandTotal"]));
        $CreditNotesData["CurrencyID"] = $Account->CurrencyId;
        $CreditNotesData["CreditNotesType"] = CreditNotes::INVOICE_IN;
        if(isset($fullPath)) {
            $CreditNotesData["Attachment"] = $fullPath;
        }
        $CreditNotesData["ModifiedBy"] = $CreatedBy;

        $CreditNotesDetailData =array();
        $CreditNotesDetailData['StartDate'] = $data['StartDate'].' '.$data['StartTime'];
        $CreditNotesDetailData['EndDate'] = $data['EndDate'].' '.$data['EndTime'];
        $CreditNotesDetailData['Price'] = floatval(str_replace(",","",$data["GrandTotal"]));
        $CreditNotesDetailData['TotalMinutes'] = floatval(str_replace(",","",$data["TotalMinutes"]));
        $CreditNotesDetailData['LineTotal'] = floatval(str_replace(",","",$data["GrandTotal"]));
        $CreditNotesDetailData["updated_at"] = date("Y-m-d H:i:s");
        $CreditNotesDetailData['Description'] = $data['Description'];
        $CreditNotesDetailData["ModifiedBy"] = $CreatedBy;
        if(CreditNotes::find($id)->update($CreditNotesData)) {
            if(CreditNotesDetail::find($data['CreditNotesDetailID'])->update($CreditNotesDetailData)) {

                //if( $data["DisputeTotal"] != '' && $data["DisputeDifference"] != '' && $data["DisputeMinutes"] != '' && $data["MinutesDifference"] != '' ){
                if( $data["DisputeID"] > 0 && !empty($data["DisputeAmount"]) ){

                    //Dispute::add_update_dispute(array( "DisputeID"=> $data["DisputeID"],  "CreditNotesID"=>$id,"DisputeTotal"=>$data["DisputeTotal"],"DisputeDifference"=>$data["DisputeDifference"],"DisputeDifferencePer"=>$data["DisputeDifferencePer"],"DisputeMinutes"=>$data["DisputeMinutes"],"MinutesDifference"=>$data["MinutesDifference"],"MinutesDifferencePer"=>$data["MinutesDifferencePer"]));
                    Dispute::add_update_dispute(array( "DisputeID"=> $data["DisputeID"], "CreditNotesType"=>CreditNotes::INVOICE_IN,"AccountID"=> $data["AccountID"], "CreditNotesNo"=>$data["CreditNotesNumber"],"DisputeAmount"=>$data["DisputeAmount"]));
                }
                return Response::json(["status" => "success", "message" => "CreditNotes in updated successfully. ".$message]);
            }else{
                return Response::json(["status" => "success", "message" => "Problem Updating CreditNotes"]);
            }
        }else{
            return Response::json(["status" => "success", "message" => "Problem Updating CreditNotes"]);
        }
    }
    public function  download_doc_file($id){
        $DocumentFile = CreditNotes::where(["CreditNotesID"=>$id])->pluck('Attachment');
        $CreditNotes = CreditNotes::find($id);
        $CompanyID = $CreditNotes->CompanyID;
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

    public function creditnotes_email($id)
    {
        $CreditNotes = CreditNotes::find($id);
        if(!empty($CreditNotes))
        {
            $Account 	 		= 	Account::find($CreditNotes->AccountID);
            $InvoiceTemplateID  =   CreditNotes::GetInvoiceTemplateID($CreditNotes);
            $Currency 	 		= 	Currency::find($Account->CurrencyId);
            $CompanyName 		= 	Company::getName();

            if (!empty($Currency))
            {
                $companyID = User::get_companyID();
                $templateData	 = 	EmailTemplate::getSystemEmailTemplate($companyID, CreditNotes::EMAILTEMPLATE, $Account->LanguageID);
                //$Subject	 	 = 	$templateData->Subject;
                //$Message 		 = 	$templateData->TemplateBody;
                $data['CreditNotesURL']	=   URL::to('/creditnotes/'.$CreditNotes->AccountID.'-'.$CreditNotes->CreditNotesID.'/cview?email=#email');
                $Message				=	EmailsTemplates::SendCreditNotesSingle(CreditNotes::EMAILTEMPLATE,$CreditNotes->CreditNotesID,'body',$data);
                $Subject				=	EmailsTemplates::SendCreditNotesSingle(CreditNotes::EMAILTEMPLATE,$CreditNotes->CreditNotesID,"subject",$data);

                $response_api_extensions 	=    Get_Api_file_extentsions();
                if(isset($response_api_extensions->headers)){ return	Redirect::to('/logout'); 	}
                $response_extensions		=	json_encode($response_api_extensions['allowed_extensions']);
                $max_file_size				=	get_max_file_size();

                if(!empty($Subject) && !empty($Message)){
                    $from	 = $templateData->EmailFrom;
                    return View::make('creditnotes.email', compact('CreditNotes', 'Account', 'Subject','Message','CompanyName','from','response_extensions','max_file_size'));
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
            $CreditNotes 					= 	CreditNotes::find($id);
            $Company 					= 	Company::find($CreditNotes->CompanyID);
            $CompanyName 				= 	$Company->CompanyName;
            $CreditNotesGenerationEmail 	= 	CompanySetting::getKeyVal('CreditNotesGenerationEmail');
            $CreditNotesGenerationEmail 	= 	($CreditNotesGenerationEmail =='Invalid Key')?$Company->Email:$CreditNotesGenerationEmail;
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
            $data['CreditNotesURL'] 		= 	URL::to('/creditnotes/'.$CreditNotes->AccountID.'-'.$CreditNotes->CreditNotesID.'/cview');
            $data['AccountName'] 		= 	Account::find($CreditNotes->AccountID)->AccountName;
            $data['CompanyName'] 		= 	$CompanyName;

            $rules = array(
                'AccountName' => 'required',
                'CreditNotesURL' => 'required',
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
                    $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['EMAIL_ATTACHMENT'],'',$CreditNotes->CompanyID);
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
                    $data['CreditNotesURL']	= 	URL::to('/creditnotes/'.$CreditNotes->AccountID.'-'.$CreditNotes->CreditNotesID.'/cview?email='.$singleemail);
                    $body					=	EmailsTemplates::ReplaceEmail($singleemail,$postdata['Message']);
                    $data['Subject']		=	$postdata['Subject'];
                    //$body					=	EmailsTemplates::SendCreditNotesSingle(CreditNotes::EMAILTEMPLATE,$CreditNotes->CreditNotesID,'body',$data,$postdata);
                    //$data['Subject']		=	EmailsTemplates::SendCreditNotesSingle(CreditNotes::EMAILTEMPLATE,$CreditNotes->CreditNotesID,"subject",$data,$postdata);

                    if(isset($postdata['email_from']) && !empty($postdata['email_from']))
                    {
                        $data['EmailFrom']		=	$postdata['email_from'];
                    }else{
                        $data['EmailFrom']		=	EmailsTemplates::GetEmailTemplateFrom(CreditNotes::EMAILTEMPLATE);
                    }
                    $status 				= 		$this->sendCreditNotesMail($body,$data,0);
                }
            }

            if($status['status']==0)
            {
                $status['status'] = 'failure';
            }
            else
            {
                $status['status'] 					= "success";
                //$CreditNotes->update(['CreditNotesStatus' => CreditNotes::SEND ]);

                $creditnotesloddata = array();
                $creditnotesloddata['CreditNotesID']= $CreditNotes->CreditNotesID;
                $creditnotesloddata['Note']= 'Sent By '.$CreatedBy;
                $creditnotesloddata['created_at']= date("Y-m-d H:i:s");
                //$creditnotesloddata['CreditNotesLogStatus']= CreditNotesLog::SENT;
                CreditNotesLog::insert($creditnotesloddata);

                /*
                    Insert email log in account
                */
                $message_id 	=  isset($status['message_id'])?$status['message_id']:"";
                $logData = ['AccountID'=>$CreditNotes->AccountID,
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
            $Account = Account::find($CreditNotes->AccountID);
            if(!empty($Account->Owner))
            {
                $AccountManager 			 = 	User::find($Account->Owner);
                $CreditNotesGenerationEmail 	.= 	',' . $AccountManager->EmailAddress;
            }

            $sendTo 				= 	explode(",",$CreditNotesGenerationEmail);
            $data['Subject'] 	   .= 	' ('.$Account->AccountName.')';//Added by Abubakar
            $data['EmailTo'] 		= 	$sendTo;
            $data['CreditNotesURL']	= 	URL::to('/creditnotes/'.$CreditNotes->CreditNotesID.'/creditnotes_preview');
            $body					=	EmailsTemplates::SendCreditNotesSingle(CreditNotes::EMAILTEMPLATE,$CreditNotes->CreditNotesID,'body',$data,$postdata);
            $data['Subject']		=	EmailsTemplates::SendCreditNotesSingle(CreditNotes::EMAILTEMPLATE,$CreditNotes->CreditNotesID,"subject",$data,$postdata);

            if(isset($postdata['email_from']) && !empty($postdata['email_from']))
            {
                $data['EmailFrom']		=	$postdata['email_from'];
            }else{
                $data['EmailFrom']		=	EmailsTemplates::GetEmailTemplateFrom(CreditNotes::EMAILTEMPLATE);
            }

            $StaffStatus 			= 	$this->sendCreditNotesMail($body,$data,0);

            if($StaffStatus['status']==0)
            {
                $status['message'] .= ', Enable to send email to staff : ' . $StaffStatus['message'];
            }

            return Response::json(array("status" => $status['status'], "message" => "".$status['message']));
        }
        else
        {
            return Response::json(["status" => "failure", "message" => "Problem Sending CreditNotes"]);
        }
    }

    function sendCreditNotesMail($view,$data,$type=1){

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
    public function bulk_send_creditnote_mail(){
        $data = Input::all();
        $companyID = User::get_companyID();
        unset($data['criteria']);

        $jobType = JobType::where(["Code" => 'BCS'])->get(["JobTypeID", "Title"]);
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
            return Response::json(array("status" => "success", "message" => "Bulk Credit Notes Send Job Added in queue to process.You will be notified once job is completed. "));
        }else{
            return Response::json(array("status" => "success", "message" => "Problem Creating Job Bulk CreditNotes Send."));
        }
    }
    public function creditnotes_change_Status(){
        $data = Input::all();
        $username = User::get_user_full_name();
        $creditnotes_status = CreditNotes::get_creditnotes_status();
        if(!empty($data['criteria']))
        {
            $creditnotesid = $this->getCreditNotessIdByCriteria($data);
            $CreditNotesIDs =array_filter(explode(',',$creditnotesid),'intval');

        }else{
            $CreditNotesIDs =array_filter(explode(',',$data['CreditNotesIDs']),'intval');
        }
        if (is_array($CreditNotesIDs) && count($CreditNotesIDs)) {

            if (CreditNotes::whereIn('CreditNotesID',$CreditNotesIDs)->update([ 'ModifiedBy'=>$username,'CreditNotesStatus' => $data['CreditNotesStatus']])) {
                $Extralognote = '';
                /* if($data['CreditNotesStatus'] == CreditNotes::CLOSE){
                     $Extralognote = ' Cancel Reason: '.$data['CancelReason'];
                 }*/
                foreach($CreditNotesIDs as $CreditNotesID) {
                    $creditnotesloddata = array();
                    $creditnotesloddata['CreditNotesID'] = $CreditNotesID;
                    $creditnotesloddata['Note'] = $creditnotes_status[$data['CreditNotesStatus']].' By ' . $username.$Extralognote;
                    $creditnotesloddata['created_at'] = date("Y-m-d H:i:s");
                    $creditnotesloddata['CreditNotesLogStatus'] = CreditNotesLog::UPDATED;
                    CreditNotesLog::insert($creditnotesloddata);
                }

                return Response::json(array("status" => "success", "message" => "CreditNotes Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating CreditNotes."));
            }
        }

    }

    /*
     * Download Output File
     * */
    public function downloadUsageFile($id){
        //if( User::checkPermission('Job') && intval($id) > 0 ) {
        $OutputFilePath = CreditNotes::where("CreditNotesID", $id)->pluck("UsagePath");
        $CreditNotes = CreditNotes::find($id);
        $CompanyID = $CreditNotes->CompanyID;
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
            $CreditNotesID = intval($account_inv[1]);
            $this->downloadUsageFile($CreditNotesID);
        }
    }
    public function creditnotes_regen(){
        $data = Input::all();
        if(!empty($data['criteria'])){
            $creditnotesid = $this->getCreditNotessIdByCriteria($data);
            $creditnotesid = rtrim($creditnotesid,',');
            $data['CreditNotesIDs'] = $creditnotesid;
            unset($data['criteria']);
        }
        else{
            unset($data['criteria']);
        }
        $CompanyID = User::get_companyID();
        $CreditNotesIDs =array_filter(explode(',',$data['CreditNotesIDs']),'intval');
        if (is_array($CreditNotesIDs) && count($CreditNotesIDs)) {
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
                return json_encode(["status" => "success", "message" => "CreditNotes Regeneration Job Added in queue to process.You will be notified once job is completed."]);
            }else{
                return json_encode(array("status" => "failed", "message" => "Problem Creating Bulk CreditNotes."));
            }
        }

    }
    public static function display_creditnotes($CreditNotesID){
        $CreditNotes = CreditNotes::find($CreditNotesID);
        $CompanyID = $CreditNotes->CompanyID;
        $PDFurl = '';
        log::info('CompanyID '.$CompanyID);
        $PDFurl =  AmazonS3::preSignedUrl($CreditNotes->PDF,$CompanyID);
        log::info('$PDFurl '.$PDFurl);
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="'.basename($PDFurl).'"');
        echo file_get_contents($PDFurl);
        exit;
    }
    public static function download_creditnotes($CreditNotesID){
        $CreditNotes = CreditNotes::find($CreditNotesID);
        $CompanyID = $CreditNotes->CompanyID;
        $FilePath =  AmazonS3::preSignedUrl($CreditNotes->PDF,$CompanyID);
        if(file_exists($FilePath)){
            download_file($FilePath);
        }elseif(is_amazon($CompanyID) == true){
            header('Location: '.$FilePath);
        }
        exit;
    }
    public static function download_attachment($CreditNotesID){
        $CreditNotes = CreditNotes::find($CreditNotesID);
        $CompanyID = $CreditNotes->CompanyID;
        $FilePath =  AmazonS3::preSignedUrl($CreditNotes->Attachment,$CompanyID);
        if(file_exists($FilePath)){
            download_file($FilePath);
        }elseif(is_amazon($CompanyID) == true){
            header('Location: '.$FilePath);
        }
        exit;
    }

    public function creditnotes_thanks($id)
    {
        $account_inv = explode('-', $id);
        if (isset($account_inv[0]) && intval($account_inv[0]) > 0 && isset($account_inv[1]) && intval($account_inv[1]) >= 0) {
            $AccountID = intval($account_inv[0]);
            $CreditNotesID = intval($account_inv[1]);
            if($CreditNotesID==0){
                return View::make('creditnotes.creditnotes_thanks');
            }
            $CreditNotes = CreditNotes::where(["CreditNotesID" => $CreditNotesID, "AccountID" => $AccountID])->first();
            if (count($CreditNotes) > 0) {
                return View::make('creditnotes.creditnotes_thanks', compact('CreditNotes'));
            }
        }
    }
    public function generate(){
        $CompanyID = User::get_companyID();
        $UserID = User::get_userID();
        $CronJobCommandID = CronJobCommand::where(array('Command'=>'creditnotesgenerator','CompanyID'=>$CompanyID))->pluck('CronJobCommandID');
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
                pclose(popen(CompanyConfiguration::get("PHPExePath") . " " . CompanyConfiguration::get("RMArtisanFileLocation") . "  creditnotesgenerator " . $CompanyID . " $CronJobID $UserID ". " &", "r"));
            }else{
                pclose(popen("start /B " . CompanyConfiguration::get("PHPExePath") . " " . CompanyConfiguration::get("RMArtisanFileLocation") . "  creditnotesgenerator " . $CompanyID . " $CronJobID $UserID ", "r"));
            }*/
            if($JobID>0) {
                return Response::json(array("status" => "success", "message" => "CreditNotes Generation Job Added in queue to process.You will be notified once job is completed. "));
            }
        }
        return Response::json(array("status" => "error", "message" => "Please Setup CreditNotes Generator in CronJob"));

    }
    public function ajax_getEmailTemplate($id){
        //  $filter =array('Type'=>EmailTemplate::INVOICE_TEMPLATE);
        $filter =array('StaticType'=>EmailTemplate::DYNAMICTEMPLATE);
        if($id == 1){
            $filter['UserID'] =   User::get_userID();
        }
        return EmailTemplate::getTemplateArray($filter);
    }

    public function getCreditNotessIdByCriteria($data){
        $companyID = User::get_companyID();
        $criteria = json_decode($data['criteria'],true);
        $criteria['Overdue'] = $criteria['Overdue']== 'true'?1:0;
        $criteria['CreditNotesStatus'] = is_array($criteria['CreditNotesStatus'])?implode(',',$criteria['CreditNotesStatus']):$criteria['CreditNotesStatus'];

        // Account Manager Condition
        $userID = 0;
        if(User::is('AccountManager')) { // Account Manager
            $userID = User::get_userID();
        }

        $query = "call prc_getCreditNotes (".$companyID.",'".$criteria['AccountID']."','".$criteria['CreditNotesNumber']."','".$criteria['IssueDateStart']."','".$criteria['IssueDateEnd']."','".$criteria['CreditNotesType']."','".$criteria['CreditNotesStatus']."',".$criteria['Overdue'].",'' ,'','','','".$criteria['CurrencyID']."' ";

        if(!empty($criteria['zerovaluecreditnotes'])){
            $query = $query.',2,0,1';
        }else{
            $query = $query.',2,0,0';
        }
        $query .= ",'',".$userID.")";
        $exceldatas  = DB::connection('sqlsrv2')->select($query);
        $exceldatas = json_decode(json_encode($exceldatas),true);
        $creditnotesid='';
        foreach($exceldatas as $exceldata){
            $creditnotesid.= $exceldata['CreditNotesID'].',';
        }
        return $creditnotesid;
    }

    public function bulk_print_creditnotes(){
        $zipfiles = array();
        $data = Input::all();
        if(!empty($data['criteria'])){
            $creditnotesid = $this->getCreditNotessIdByCriteria($data);
            $creditnotesid = rtrim($creditnotesid,',');
            $data['CreditNotesIDs'] = $creditnotesid;
            unset($data['criteria']);
        }
        else{
            unset($data['criteria']);
        }

        $creditnotesIds=array_map('intval', explode(',', $data['CreditNotesIDs']));

        if(!empty($creditnotesIds)) {

            $CreditNotess = CreditNotes::find($creditnotesIds);
            $CompanyID = User::get_companyID();
            $UPLOAD_PATH = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID). "/";
            $isAmazon = is_amazon($CompanyID);
            foreach ($CreditNotess as $creditnotes) {
                $path = AmazonS3::preSignedUrl($creditnotes->PDF,$CompanyID);

                if ( file_exists($path) ){
                    $zipfiles[$creditnotes->CreditNotesID]=$path;
                }else if($isAmazon == true){

                    $filepath = $UPLOAD_PATH . basename($creditnotes->PDF);
                    $content = @file_get_contents($path);
                    if($content != false){
                        file_put_contents( $filepath, $content);
                        $zipfiles[$creditnotes->CreditNotesID] = $filepath;
                    }
                }
            }

            if (!empty($zipfiles)) {

                if (count($zipfiles) == 1) {

                    $downloadCreditNotesid = array_keys($zipfiles)[0];
                    return Response::json(array("status" => "success", "message" => " Download Starting ", "creditnotesId" => $downloadCreditNotesid, "filePath" => ""));

                } else {

                    $filename='creditnotes' . date("dmYHis") . '.zip';
                    $local_zip_file = $UPLOAD_PATH . $filename;

                    Zipper::make($local_zip_file)->add($zipfiles)->close();

                    if (file_exists($local_zip_file)) {
                        return Response::json(array("status" => "success", "message" => " Download Starting ", "creditnotesId" => "", "filePath" => base64_encode($filename)));
                    }
                    else {
                        return Response::json(array("status" => "error", "message" => "Something wrong Please Try Again"));
                    }
                }

            }
        }
        else {
            return Response::json(array("status" => "error", "message" => "Please Select CreditNotes"));
        }
        exit;
    }

    public function creditnotes_management_chart($id){

        $CreditNotes = CreditNotes::find($id);
        if (!empty($CreditNotes)) {
            $CreditNotesDetail = CreditNotesDetail::where(["CreditNotesID" => $id])->get();
            $CreditNotesUSAGEPeriod = CreditNotesDetail::where(["CreditNotesID" => $id,'ProductType'=>Product::USAGE])->first();
            $Account = Account::find($CreditNotes->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency) ? $Currency->Code : '';
            $CurrencySymbol = Currency::getCurrencySymbol($Account->CurrencyId);
            $CreditNotesBillingClass =	 CreditNotes::GetCreditNotesBillingClass($CreditNotes);
            $InvoiceTemplateID = BillingClass::getInvoiceTemplateID($CreditNotesBillingClass);
            $InvoiceTemplate = InvoiceTemplate::find($InvoiceTemplateID);
            $RoundChargesAmount = get_round_decimal_places($CreditNotes->AccountID);
            $companyID = $Account->CompanyId;
            $management_query = "call prc_CreditNotesManagementReport ('" . $companyID . "','".intval($CreditNotes->AccountID) . "','".$CreditNotesUSAGEPeriod->StartDate . "','".$CreditNotesUSAGEPeriod->EndDate. "')";
            $ManagementReports = DataTableSql::of($management_query,'sqlsrvcdr')->getProcResult(array('LongestCalls','ExpensiveCalls','DialledNumber','DailySummary','UsageCategory'));
            $ManagementReports = json_decode(json_encode($ManagementReports['data']), true);
            return View::make('creditnotes.creditnotes_chart', compact('CreditNotes', 'CreditNotesDetail', 'Account', 'InvoiceTemplate', 'CurrencyCode','ManagementReports','CurrencySymbol','RoundChargesAmount'));
        }
    }

    public function creditnoteslog($id)
    {
        $creditnotes = CreditNotes::find($id);
        //$InvoiceTemplateID = AccountBilling::getInvoiceTemplateID($creditnotes->AccountID);
        $InvoiceTemplateID = CreditNotes::GetInvoiceTemplateID($creditnotes);
        $creditnotesnumber = CreditNotes::getFullCreditNotesNumber($creditnotes,$InvoiceTemplateID);
        return View::make('creditnotes.creditnoteslog', compact('creditnotes','id','creditnotesnumber'));
    }

    public function ajax_creditnoteslog_datagrid($id,$type) {
        $data = Input::all();
        $data['iDisplayStart'] +=1;

        //$columns = array('InvoiceNumber','Transaction','Notes','Amount','Status','created_at','InvoiceID');
        $columns = array('Notes','CreditNotesLogStatus','created_at','CreditNotesID');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();

        $query = "call prc_GetCreditNotesLog (".$companyID.",".$id.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        //echo $query;exit;
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/CreditNotes Log.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/CreditNotes Log.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query,'sqlsrv2')->make();
    }
}