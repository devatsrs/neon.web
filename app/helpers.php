<?php
function json_validator_response($validator){


    if ($validator->fails()) {
        $errors = "";
        foreach ($validator->messages()->all() as $error){
            $errors .= $error."<br>";
        }
        return  Response::json(array("status" => "failed", "message" => $errors));
    }

}

function json_response_api($response,$datareturn=false,$isBrowser=true,$isDataEncode=true){
    $message = '';
    $status = '';
    $data = '';
    $isArray = false;
    if(is_array($response)){
        $isArray = true;
    }
    $parse_repose = array("status" => $status, "message" => $message);
    if(($isArray && $response['status'] =='failed') || (!$isArray && $response->status=='failed')) {
        $validator = $isArray?$response['message']:(array)$response->message;
        if (count($validator) > 0) {
            foreach ($validator as $index => $error) {
                if(is_array($error)){
                    $message .= array_pop($error) . "<br>";
                }
            }
        }
        $status = 'failed';
    }else {
        $message = $isArray?$response['message']:$response->message;
        $status = 'success';
        if (($isArray && isset($response['data'])) || isset($response->data)) {
            $result = $isArray ? $response['data'] : $response->data;
            $data = $result;
            if($datareturn) {
                if ($isDataEncode) {
                    $result = json_encode($result);
                }
                return $result;
            }
        }
    }
    $parse_repose['status'] =  $status;
    $parse_repose['message'] = $message;
    if(!empty($data)) {
        $parse_repose['data'] = $data;
    }
    if(($isArray && isset($response['redirect'])) || (!$isArray && isset($response->redirect))){
        $parse_repose['redirect'] =  $isArray ? $response['redirect'] : $response->redirect;
    }


    if($isBrowser){
        if(($isArray && isset($response['Code']) && $response['Code'] ==401) || (!$isArray && isset($response->Code) && $response->Code == 401)){
            \Illuminate\Support\Facades\Log::info("helpers.php json_response_api");
            \Illuminate\Support\Facades\Log::info(print_r($response,true));
            return  Response::json($parse_repose,401);
        }else {
            return Response::json($parse_repose);
        }
    }else{
        return $message;
    }
}

function validator_response($validator){


    if ($validator->fails()) {
        $errors = "";
        foreach ($validator->messages()->all() as $error){
            $errors .= $error."<br>";
        }
        return  array("status" => "failed", "message" => $errors);
    }

}
function download_file($file = ''){
    if ($file != "") {
        if (is_file($file) && file_exists($file)) {
            $mime_types = array(
                '.xls' => 'application/excel',
                '.xlsx' => 'application/excel',
                '.csv' => 'text/csv',
                '.txt' => 'text/plain',
                '.pdf' => 'application/pdf',
                '.doc' => 'application/msword',
                '.docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                '.jpg'=>'image/jpeg',
                '.gif'=>'image/gif',
                '.png'=>'image/png'
            );
            $f = new Symfony\Component\HttpFoundation\File\File($file);
            $extension = $f->getExtension();
            $type = "application/octet-stream";
            if (!isset($mime_types[$extension])) {
                $mime_types[$extension] = $f->getMimeType();
            }
            if (isset($mime_types[$extension])) {
                $type = $mime_types[$extension];
                header('Content-Description: File Transfer');
                header('Content-disposition: attachment; filename="' . basename($file).'"');
                header("Content-Type: $type");
                header("Content-Transfer-Encoding: $type\n");
                header("Content-Length: " . filesize($file));
                ob_clean();
                flush();
                readfile($file);
                exit();
            } else {
                echo $file.'<BR>';
                echo "No Data Found";
                exit();
            }
        }else if (!filter_var($file, FILTER_VALIDATE_URL) === false) {
            header('Location: '.$file);
            exit;

        }
    }
}
function rename_upload_file($destinationPath,$full_name){
    $increment = 1;
    $name = pathinfo($full_name, PATHINFO_FILENAME);
    $extension = pathinfo($full_name, PATHINFO_EXTENSION);
    while(file_exists($destinationPath.$name. $increment . '.' . $extension)) {
        $increment++;
    }
    $basename = $name . $increment . '.' . $extension;
    return $basename;
}
function importrules_dropbox($id=0,$data=array()){
    $all_ImportRules = TicketImportRule::getImportRules($data);
    return Form::select('importrules', $all_ImportRules, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}
function customer_dropbox($id=0,$data=array()){
    $all_customers = Account::getAccountIDList($data);
    return Form::select('customers', $all_customers, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}
function upload_template_dropbox($id=0,$data=array()){
    $all_templates = FileUploadTemplate::getTemplateIDList($data);
    return Form::select('templates', $all_templates, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}

function customer_leads_dropbox($id=0,$data=array()){
    $accounts = Account::getAccountIDList($data);
    $leads = Lead::getLeadList($data);
    unset($leads['']);
    $all_customers = $accounts+$leads;
    return Form::select('customers', $all_customers, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}

function opportunites_dropbox($id=0,$data=array()){
    $all_opportunites = CRMBoard::getBoards(CRMBoard::OpportunityBoard,-1);
    return Form::select('crmboard', $all_opportunites, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}

function toandfro_dropdown($id,$type){
    $list = [];
    if($type=='recurringInvoice'){
        $list = RecurringInvoice::getRecurringInvoicesIDList();
    }
    return Form::select('drp_toandfro_jump', $list, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}

function rategenerators_dropbox($id=0,$data=array()){
    $all_rategenerators = RateGenerator::getRateGenerators();
    return Form::select('rategenerators', $all_rategenerators, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}

function businesshours_dropbox($id=0,$data=array()){
    $all_Businesshours = TicketBusinessHours::getBusinesshours(0);
    return Form::select('businesshours', $all_Businesshours, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}

function slapolicies_dropbox($id=0,$data=array()){
    $all_slapolicies = TicketSla::getSlapolicies();
    return Form::select('slapolicies', $all_slapolicies, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}

function rate_tables_dropbox($id=0,$data=array()){
    $all_getRateTables = RateTable::getRateTables();
    return Form::select('rategenerators', $all_getRateTables, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}

function contacts_dropbox($id=0,$data=array()){
    $all_contacts = Contact::getContacts($data);
    return Form::select('contacts', $all_contacts, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}
function recurring_invoice_log_dropbox($id=0,$data=array()){
    $all_getInvoice = RecurringInvoice::getRecurringInvoices($data);
    return Form::select('recurringinvoicelogs', $all_getInvoice, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}
function ticketgroup_dropbox($id=0,$data=array()){
    $all_ticketsgroups = TicketGroups::getTicketGroups_dropdown($data);
    return Form::select('ticketgroups', $all_ticketsgroups, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}

function accountservice_dropbox($AccountID,$ServiceID){
    $all_accsevices = accountservice::getAccountServiceIDList($AccountID);
    return Form::select('accountservice', $all_accsevices, $ServiceID ,array("id"=>"drp_accountservice_jump" ,"class"=>"selectboxit1 form-control1"));
}
function basecodedeck_dropbox($id=0,$data=array()){
    $all_basecodedecks = BaseCodeDeck::getCodedeckIDList();
    return Form::select('basecodedeck', $all_basecodedecks, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}
function discountplan_dropbox($id=0,$data=array()){
    $all_discountplans = DiscountPlan::getDiscountPlanIDList($data);
    return Form::select('discountplan', $all_discountplans, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}
function invoicetemplate_dropbox($id=0,$data=array()){
    $all_invoicetemplates = InvoiceTemplate::getInvoiceTemplateList();
    return Form::select('invoicetemplate', $all_invoicetemplates, $id ,array("id"=>"drp_invoicetemplate_jump" ,"class"=>"selectboxit1 form-control1"));
}

function sendMail($view,$data,$ViewType=1){
    
	if(empty($data['companyID']))
    {
        $companyID = User::get_companyID();
    }else{
        $companyID = $data['companyID'];
    }

	if($ViewType){
		$body 	=  html_entity_decode(View::make($view,compact('data'))->render()); 
	}
	else{
		$body  = $view;
	}

	
	if(SiteIntegration::CheckCategoryConfiguration(false,SiteIntegration::$EmailSlug,$companyID)){
		$status = 	SiteIntegration::SendMail($view,$data,$companyID,$body); 
	}
	else{ 
		$config = Company::select('SMTPServer','SMTPUsername','CompanyName','SMTPPassword','Port','IsSSL','EmailFrom')->where("CompanyID", '=', $companyID)->first();
		$status = 	PHPMAILERIntegtration::SendMail($view,$data,$config,$companyID,$body);
	}
	return $status;
}

function getMonths() {
    $months = array(''=>'Select');
    for ($x = 1; $x <= 12; $x++) {
        $x = str_pad($x, 2, '0', STR_PAD_LEFT);
        $months[$x] = date("F", mktime(0, 0, 0, $x, 10)) . " ($x)"; //January (01)
    }

    return $months;
}

function getYears() {
    $years = array(''=>'Select');
    $curYear = date("Y");
    $limit = 20;
    for ($x = $curYear; $x < $curYear + $limit; $x++) {
        $years[$x] = $x;
    }
    return $years;
}


Form::macro('SelectExt', function($arg = array())
{
    /*{
        "name" => $name  - Field Name
        "data" => $data,
        "selected" => $selected,
        "value_key" => "TaxRateID",
        "title_key" => "Title",

        "data-title1" => "Amount",
        "data-title2" => "Amount1",
        "data-title3" => "Amount2",

        "data-value1" => "Amount",
        "data-value2" => "Amount1",
        "data-value3" => "Amount2",

        "class" => "",
        "extra" => ""
    }*/

    $data = $arg['data'];
    if(count($data) > 0) {
        $output = '<select name="'.$arg['name'].'" class="'.$arg['class'].'"  ';

        if(isset($arg['extra'])) {
            $output .= $arg['extra'] ;
        }
        $output .=  ' >';
        foreach ($data as $row) {

            $output .= '<option value="' . $row[$arg["value_key"]] . '" ';
            if ($row[$arg["value_key"]] == $arg['selected']) {
                $output .= " selected ";
            }
            if(isset($arg["data-title1"])) {
                $output .= $arg["data-title1"] . '="' . $row[$arg["data-value1"]] . '"';
            }

            if(isset($arg["data-title2"])) {
                $output .= $arg["data-title2"] . '="' . $row[$arg["data-value2"]] . '"';
            }

            if(isset($arg["data-title3"])) {
                $output .= $arg["data-title3"] . '="' . $row[$arg["data-value3"]] . '"';
            }

            $output .=  '>';

            $output .= $row[$arg["title_key"]] . "</option>";
        }
        $output .= "</select>";
        return $output;
    }
});

Form::macro('selectItem', function($name, $data , $selected , $extraparams )
{
    /**
    <select name="InvoiceDetail[ProductID][]" class="selectboxit product_dropdown visible" style="display: none;">
     *  <option value="">Select a Product</option>
     * <optgroup label="Usage">
     * <option selected="selected" value="0">Usage</option><
    /optgroup>
     * <optgroup label="Subscription">
     * <option value="1">Internet Subscription</option>
     * <option value="2">Phone Billing Plan</option>
     * </optgroup>
     * <optgroup label="Item">
     * <option value="5">BILL TEMPLATE</option>
     * <option value="13">TEST ITEM1</option>
     * <option value="14">IP Phone 2</option>
     * <option value="15">Phone 3</option>
     * <option value="16">New Item</option>
     * </optgroup>
     * </select>
     */

    $output = '<select name="'.$name.'" class="'.$extraparams['class'].'">';
    foreach($data as $optgroup => $rows){
        if(empty($optgroup) ){
            $output .= '<option value="">'.$rows.'</option>';
        }else {
            $output .= '<optgroup label="' . $optgroup . '" >';
            foreach ($rows as $value  => $title) {
                $output .= '<option value="' . $value . '" ';
                if ($value == $selected &&  isset($extraparams['type']) && strtolower($extraparams['type']) == strtolower($optgroup) ) {
                    $output .= " selected ";
                }
                $output .= ">";
                $output .= $title . "</option>";
            }
            $output .= '</optgroup>';
        }
    }
    $output .= "</select>";
    return $output;
});

Form::macro('SelectControl', function($type,$compact=0,$selection='',$disable=0,$nameID='',$initialize=1) {
    $small = $compact==1?"small":'';
    $select2 = $initialize==1?"select2":'select22';//for manual initialize set 0.
    $isComposit = 0;
    $extraClass = '';
    $name = '';
    $modal = '';
    $data = [];
    if($type=='currency') {
        $name = 'CurrencyID';
        $modal = 'add-new-modal-currency';
        $data = Currency::getCurrencyDropdownIDList();
    }elseif($type=='invoice_template'){
        $name = 'InvoiceTemplateID';
        $modal = 'add-new-modal-invoice_template';
        $data = InvoiceTemplate::getInvoiceTemplateList();
    }elseif($type=='email_template'){
        $name = 'TemplateID';
        $modal = 'add-new-modal-template';
        $data = EmailTemplate::getTemplateArray();
    }elseif($type=='trunk'){
        $name = 'TrunkID';
        $modal = 'add-new-modal-trunk';
        $data = Trunk::getTrunkDropdownIDList();
    }elseif($type=='billing_class'){
        $name = 'BillingClassID';
        $modal = 'add-new-modal-billingclass';
        $data = BillingClass::getDropdownIDList();
    }elseif($type=='item'){
        $name = 'ProductID';
        $modal = 'add-edit-modal-product';
        $data = Product::getProductDropdownList();
    }elseif($type=='item_and_Subscription'){
        $name = 'ProductItemID';
        $modal = 'add-edit-modal-product-subscription';
        $data = [Product::ITEM=>Product::getProductDropdownList() ,Product::SUBSCRIPTION=> BillingSubscription::getSubscriptionsList()];
        $extraClass = 'product_dropdown';
        $isComposit = 1;
    }elseif($type=='service'){
        $name = 'ServiceID';
        $modal = 'add-new-modal-service';
        $data = Service::getDropdownIDList();
    }
    if(!empty($nameID)){
        $name= $nameID;
    }
    //select2add    :for Add button
    //data-modal    :for target open modal
    //data-active   :For current active drop-down
    //data-type     :For drop-down recognition while adding new item
    //small         :For compact drop-down
    //extraClass    :Any extra class add to drop-down
    //data-composite :For drop-down recognition while adding new item
    $arr = ['class' => $select2.' select2add '.$small.' '.$extraClass , 'data-modal' => $modal, 'data-active'=>0,'data-type'=>$type];
    if($disable==1){
        $arr['disabled'] = 'disabled';
    }
    if($isComposit==1){
        $arr['data-composite'] = 1;
       return compositDropdown($name, $data, $selection, $arr);
    }
    return Form::select($name, $data, $selection, $arr);
});

function compositDropdown($name,$data,$selection,$arr)
{
    $attr = '';
    foreach($arr as $index=>$att){
        $attr .= $index.'="'.$att.'" ';
    }
    $select = '<select name="'.$name.'" '.$attr.'>';
    foreach($data as $index=>$cate){
        $select .= ' <optgroup class="optgroup_'.Product::$TypetoProducts[$index].'" label="'.ucfirst(Product::$TypetoProducts[$index]).'">';
        foreach($cate as $key=>$val) {
            $selected = (!empty($selection) && $key==$selection['ID'] && $index==$selection['Type'])?'selected':'';
            $optgroup = !empty($key) ? $index . '-' : '';
            $select .= '    <option Item_Subscription_txt="'.ucfirst(Product::$TypetoProducts[$index]).'" Item_Subscription_type="'.$index.'" value="' . $optgroup . $key . '" '.$selected.'>';
            $select .= $val;
            $select .= '    </option>';
        }
        $select .= ' </optgroup>';
    }
    $select .= '</select>';
    return $select;
}


function is_amazon($CompanyID = 0){
	
  /*  $AMAZONS3_KEY  = getenv("AMAZONS3_KEY");
    $AMAZONS3_SECRET = getenv("AMAZONS3_SECRET");
    $AWS_REGION = getenv("AWS_REGION");*/

	$AmazonData			=	SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$AmazoneSlug,$CompanyID);
    $AMAZONS3_KEY  		= 	isset($AmazonData->AmazonKey)?$AmazonData->AmazonKey:'';
    $AMAZONS3_SECRET 	= 	isset($AmazonData->AmazonSecret)?$AmazonData->AmazonSecret:'';
    $AWS_REGION 		= 	isset($AmazonData->AmazonAwsRegion)?$AmazonData->AmazonAwsRegion:'';

    if(empty($AMAZONS3_KEY) || empty($AMAZONS3_SECRET) || empty($AWS_REGION) ){
        return false;
    }
    return true;
}

function is_authorize($CompanyID){
	return				SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$AuthorizeSlug,$CompanyID);
	
	/*$AuthorizeDbData 	= 	IntegrationConfiguration::where(array('CompanyId'=>User::get_companyID(),"IntegrationID"=>9))->first();
	if(count($AuthorizeDbData)>0){
		
		$AuthorizeData   				= 	isset($AuthorizeDbData->Settings)?json_decode($AuthorizeDbData->Settings):"";		
		$AUTHORIZENET_API_LOGIN_ID  	= 	isset($AuthorizeData->AuthorizeLoginID)?$AuthorizeData->AuthorizeLoginID:'';		
		$AUTHORIZENET_TRANSACTION_KEY  	= 	isset($AuthorizeData->AuthorizeTransactionKey)?$AuthorizeData->AuthorizeTransactionKey:'';
		
		if(empty($AUTHORIZENET_API_LOGIN_ID) || empty($AUTHORIZENET_TRANSACTION_KEY)){
			return false;
		}
		return true;		
	}
	else{
		return false;
	}	*/
}

function is_paypal($CompanyID){

    $paypal = new PaypalIpn($CompanyID);
    if($paypal->status){
        return true;
    }
    return false;
}

function is_pelecard($CompanyID){

    $pelecard = new PeleCard($CompanyID);
    if($pelecard->status){
        return true;
    }
    return false;
}

function is_sagepay($CompanyID){

    $sagepay = new SagePay($CompanyID);
    if($sagepay->status){
        return true;
    }
    return false;
}


function get_image_data($path){
    $type = pathinfo($path, PATHINFO_EXTENSION);
    try{
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }catch (Exception $e){
        return "";
    }

    return $base64;
}


function getFileContent($file_name, $data, $Sheet=''){
    $columns = [];
    $grid = [];
    $flag = 0;

    if(isset($data["start_row"]) && isset($data["end_row"])){
        NeonExcelIO::$start_row=$data["start_row"];
        NeonExcelIO::$end_row=$data["end_row"];
    }

    $NeonExcel = new NeonExcelIO($file_name, $data, $Sheet);
    $results = $NeonExcel->read(10);

    // Get columns

    $counter = 1;
    foreach ($results[0] as $index => $value) {
        $index = preg_replace('/\s+/', ' ', trim($index));//remove new lines (/r/n) etc...
        if (isset($data['option']['Firstrow']) && $data['option']['Firstrow'] == 'data') {
            $columns[$counter] = 'Col' . $counter;
        } else {
            if(!is_null($index))
            {
                $columns[$index] = $index;
            }
            else
            {
                $columns[""]="";
            }

        }
        $counter++;
    }

    // Get data into array.
    $columns = array_filter($columns);
    $grid_array = array();
    foreach ($results as $outindex => $datarow) {

        $i = 1;
        foreach ($datarow as $index => $singlerow) {

            if (strpos(strtolower($index), 'date') !== false) {

                $singlerow = str_replace('/', '-', $singlerow);
                $grid_array[$outindex][$index] = $singlerow;
            }

            if (isset($data['option']['Firstrow']) && $data['option']['Firstrow'] == 'data') {
                $grid_array[$outindex][$columns[$i++]] = $singlerow;
            }else{
                $grid_array[$outindex][$index] = $singlerow;
            }
        }
        unset($grid_array[$outindex][""]);

    }
    //print_r($grid_array);
    //exit;

    try {
    } catch (\Exception $ex) {
        Log::error($ex);
    }

    $grid['columns'] = $columns;
    $grid['rows'] = $grid_array;
    $grid['filename'] = $file_name;

    return $grid;
}

function getFileContentSheet2($file_name, $data, $Sheet=''){
    $columns = [];
    $grid = [];
    $flag = 0;

     if(isset($data["start_row_sheet2"]) && isset($data["end_row_sheet2"])){
        NeonExcelIO::$start_row=$data["start_row_sheet2"];
        NeonExcelIO::$end_row=$data["end_row_sheet2"];
    }

    $NeonExcel = new NeonExcelIO($file_name, $data, $Sheet);
    $results = $NeonExcel->read(10);

    // Get columns

    $counter = 1;
    foreach ($results[0] as $index => $value) {
        $index = preg_replace('/\s+/', ' ', trim($index));//remove new lines (/r/n) etc...
        if (isset($data['option']['Firstrow']) && $data['option']['Firstrow'] == 'data') {
            $columns[$counter] = 'Col' . $counter;
        } else {
            if(!is_null($index))
            {
                $columns[$index] = $index;
            }
            else
            {
                $columns[""]="";
            }

        }
        $counter++;
    }

    // Get data into array.
    $columns = array_filter($columns);
    $grid_array = array();
    foreach ($results as $outindex => $datarow) {

        $i = 1;
        foreach ($datarow as $index => $singlerow) {

            //$grid_array[$outindex][$index] = $singlerow;

            if (strpos(strtolower($index), 'date') !== false) {

                $singlerow = str_replace('/', '-', $singlerow);
                $grid_array[$outindex][$index] = $singlerow;
            }

            if (isset($data['option']['Firstrow']) && $data['option']['Firstrow'] == 'data') {
                $grid_array[$outindex][$columns[$i++]] = $singlerow;
            }
            else
            {
                $grid_array[$outindex][$index] = $singlerow;
            }
        }
        unset($grid_array[$outindex][""]);
    }
    //print_r($grid_array);
    //exit;

    try {
    } catch (\Exception $ex) {
        Log::error($ex);
    }

    $grid['columns'] = $columns;
    $grid['rows'] = $grid_array;
    $grid['filename'] = $file_name;

    return $grid;
}

function estimate_date_fomat($DateFormat){
    if(empty($DateFormat)){
        $DateFormat = 'd-m-Y';
    }
    return $DateFormat;
}
function invoice_date_fomat($DateFormat){
    if(empty($DateFormat)){
        $DateFormat = 'd-m-Y';
    }
    return $DateFormat;
}
function bulk_mail($type,$data){
    $message = '';
    $companyID = User::get_companyID();
    $fullPath = "";
    $sendmail = 1;
    $jobtext = 'Bulk mail';
    if(isset($data['sendMail']) && $data['sendMail'] ==0 ){
        $sendmail = 0;
    }
    if($sendmail==1) {
        if ($data['subject'] == "") {
            return Response::json(array("status" => "error", "message" => "Subject should not empty."));
        }
        if ($data['message'] == "") {
            return Response::json(array("status" => "error", "message" => "Message should not empty."));
        }

        if (Input::hasFile('attachment')) {
            $upload_path = CompanyConfiguration::get('UPLOAD_PATH');
            $Attachment = Input::file('attachment');
            $ext = $Attachment->getClientOriginalExtension();
            if (in_array(strtolower($ext), array("pdf", "jpg", "png", "gif", 'zip', 'xls', 'xlsx'))) {
                $file_name = GUID::generate() . '.' . $ext;
                if ($type == 'BLE') {
                    $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['BULK_LEAD_MAIL_ATTACHEMENT']);
                }
                if ($type == 'BAE') {
                    $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['BULK_ACCOUNT_MAIL_ATTACHEMENT']);
                }
                if ($type == 'IR') {
                    $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['BULK_INVOICE_MAIL_ATTACHEMENT']);
                }
                if ($type == 'DR') {
                    $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['BULK_DISPUTE_MAIL_ATTACHEMENT']);
                }
                $dir = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
                if (!file_exists($dir)) {
                    mkdir($dir, 777, TRUE);
                }
                $Attachment->move($dir, $file_name);
                if (!AmazonS3::upload($dir . '/' . $file_name, $amazonPath)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
                $data['attachment'] = $fullPath;
            } else {
                unset($data['attachment']);
            }
        }
        if ($data["template_option"] == 1) { //Create Template
            $companyID = User::get_companyID();
            $template = [];
            if ($data['email_template_privacy'] == 1) {
                $template['userID'] = user::get_userID();
            }
            $template['CompanyID'] = $companyID;
            $template['TemplateName'] = $data['template_name'];
            $template['Subject'] = $data['subject'];
            $template['TemplateBody'] = $data['message'];
            $template['Type'] = $data['Type'];
            $template['CreatedBy'] = User::get_user_full_name();
            $rules = [
                "TemplateName" => "required|unique:tblEmailTemplate,TemplateName,NULL,TemplateID",
                "Subject" => "required",
                "TemplateBody" => "required"
            ];
            $validator = Validator::make($template, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if (EmailTemplate::create($template)) {
                $message = " and template Successfully Created";
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Template."));
            }
        } elseif ($data["template_option"] == 2) { // Update
            if (!empty($data['email_template']) && $data['email_template'] > 0) {
                $id = $data['email_template'];
                $companyID = User::get_companyID();
                $template = [];
                if ($data['email_template_privacy'] == 1) {
                    $template['userID'] = user::get_userID();
                }
                $template['CompanyID'] = $companyID;
                $template['Subject'] = $data['subject'];
                $template['TemplateBody'] = $data['message'];
                $template['ModifiedBy'] = User::get_user_full_name();
                $EmailTemplate = EmailTemplate::find($id);

                $rules = [
                    "Subject" => "required",
                    "TemplateBody" => "required"
                ];
                $validator = Validator::make($template, $rules);

                if ($validator->fails()) {
                    return json_validator_response($validator);
                }
                if ($EmailTemplate->update($template)) {
                    $message = " and template Successfully Updated";
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Updating template."));
                }
            } else {
                return Response::json(array("status" => "error", "message" => "Please select an email template."));
            }
        }
    }
    unset($data['template_name']);
    unset($data['_wysihtml5_mode']);
    unset($data['email_template']);
    unset($data['template_option']);
    unset($data['email_template_privacy']);
    //Create Job
    $jobType = JobType::where(["Code" => $type])->get(["JobTypeID", "Title"]);
    $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
    $jobdata["CompanyID"] = $companyID;
    $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
    $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
    $jobdata["JobLoggedUserID"] = User::get_userID();
    $jobdata["Title"] =  (isset($jobType[0]->Title) ? $jobType[0]->Title : '');
    $jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
    $jobdata["Options"] = json_encode($data);
    $jobdata["OutputFilePath"] = $fullPath;
    $jobdata["CreatedBy"] = User::get_user_full_name();
    $jobdata["updated_at"] = date('Y-m-d H:i:s');
    $jobdata["created_at"] = date('Y-m-d H:i:s');
    $JobID = Job::insertGetId($jobdata);
    if($type=='CD'){
        $jobtext = 'ratesheet';
    }
    if($JobID){
        return json_encode(["status" => "success", "message" => $jobtext." Job Added in queue to process.You will be notified once job is completed. ".$message]);
    }else{
        return json_encode(array("status" => "failed", "message" => "Problem Creating Bulk Mail."));
    }
}


function formatDate($date,$dateformat='d-m-y',$smallDate = false) {
    $date = str_replace('/', '-', $date);

    if(!$smallDate){

        if(strpos($date,":" ) !== FALSE ) {
            $dateformat = $dateformat . " H:i:s";

            if (strpos(strtolower($date), "am") !== FALSE || strpos(strtolower($date), "pm") !== FALSE) {
                $dateformat = $dateformat . " A";
            }
        }
    }

    $_date_time = date_parse_from_format($dateformat, $date);

    if (isset($_date_time['warning_count']) &&  isset($_date_time['warnings']) && count($_date_time['warnings']) > 0 ) {

        $error  = $date . ': Date Format Error  ' . implode(",",(array)$_date_time['warnings']);
        //throw new Exception($error);
    }

    if (isset($_date_time['error_count']) && $_date_time['error_count'] > 0 && isset($_date_time['errors'])) {

        $error = $date . ': Date Format Error  ' . implode(",",(array)$_date_time['errors']);
        //throw new Exception($error);

    }

    $datetime = $_date_time['year'].'-'.$_date_time['month'].'-'.$_date_time['day'];

    if(is_numeric($_date_time['hour']) && is_numeric($_date_time['minute']) && is_numeric($_date_time['second'])){

        $datetime = $datetime . ' '. $_date_time['hour'].':'.$_date_time['minute'].':'.$_date_time['second'];
    }
    return $datetime;
}

function email_log($data){
    $status = array('status' => 0, 'message' => 'Something wrong with Saving log.');
    if(!isset($data['EmailTo']) && empty($data['EmailTo'])){
        $status['message'] = 'Email To not set in Account mail log';
        return $status;
    }
    if(!isset($data['AccountID']) && empty($data['AccountID'])){
        $status['message'] = 'AccountID not set in Account mail log';
        return $status;
    }
    if(!isset($data['Subject']) && empty($data['Subject'])){
        $status['message'] = 'Subject not set in Account mail log';
        return $status;
    }
    if(!isset($data['Message']) && empty($data['Message'])){
        $status['message'] = 'Message not set in Account mail log';
        return $status;
    }
	
	if(isset($data['AttachmentPaths']) && count($data['AttachmentPaths'])>0)
    {
        $data['AttachmentPaths'] = serialize($data['AttachmentPaths']);
    }
    else
    {
        $data['AttachmentPaths'] = serialize([]);
    }

    if(is_array($data['EmailTo'])){
        $data['EmailTo'] = implode(',',$data['EmailTo']);
    }

    if(empty($data['EmailFrom'])){
        $data['EmailFrom'] = User::get_user_email();
    }

    if(!isset($data['cc']) || !is_array($data['cc']))
    {
        $data['cc'] = array();
    }

    if(!isset($data['bcc']) || !is_array($data['bcc']))
    {
        $data['bcc'] = array();
    }
	
	if(!isset($data['message_id']))
	{
		$data['message_id'] = '';
	}

    $logData = ['EmailFrom'=>$data['EmailFrom'],
        'EmailTo'=>$data['EmailTo'],
        'Subject'=>$data['Subject'],
        'Message'=>$data['Message'],
        'AccountID'=>$data['AccountID'],
        'CompanyID'=>User::get_companyID(),
        'UserID'=>User::get_userID(),
        'CreatedBy'=>User::get_user_full_name(),
        'Cc'=>implode(",",$data['cc']),
        'Bcc'=>implode(",",$data['bcc']),
		"AttachmentPaths"=>$data['AttachmentPaths'],
		"MessageID"=>$data['message_id']];
    if(AccountEmailLog::Create($logData)){
        $status['status'] = 1;
    }
    return $status;
}

function getDefaultTrunk($truanks){
    $trunk_keys = array_keys($truanks);
    if(!empty($trunk_keys) && isset($trunk_keys[1]))
    {
        return $trunk_keys[1];
    }
    return '';
}

function call_api($post = array())
{

    //$LicenceVerifierURL = 'http://localhost/RMLicenceAPI/branches/master/public/validate_licence';
    $LicenceVerifierURL = 'http://api.licence.neon-soft.com/validate_licence';// //getenv('LICENCE_URL').'validate_licence';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $LicenceVerifierURL);
    curl_setopt($ch, CURLOPT_VERBOSE, '1');
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);//TRUE to automatically set the Referer: field in requests where it follows a Location: redirect.
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);//TRUE to force the connection to explicitly close when it has finished processing, and not be pooled for reuse.
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);//TRUE to force the use of a new connection instead of a cached one.


    //turning off the server and peer verification(TrustManager Concept).
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    // curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);

    //NVPRequest for submitting to server
    $nvpreq = "json=" . json_encode($post);

    \Illuminate\Support\Facades\Log::info("Licencing request... ");
    \Illuminate\Support\Facades\Log::info($nvpreq);
    //$nvpreq = http_build_query($post);

    ////setting the nvpreq as POST FIELD to curl
    curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

    //getting response from server
    $response = curl_exec($ch);

    // echo $response;
    return $response;
}

function excloded_resource($resource){
    $excloded = ['HomeController.home'=>'HomeController.home',
        'HomeController.dologin'=>'HomeController.dologin',
        'HomeController.dologout'=>'HomeController.dologout',
        'HomeController.process_redirect'=>'HomeController.process_redirect'];
    if(array_key_exists($resource,$excloded)){
        return true;
    }
}


function getDashBoards(){
    $DashBoards = [''=>'Select'];
    $DashBoards['/monitor'] = 'Monitor Dashboard';
    $DashBoards['/billingdashboard'] = 'Billing Dashboard';
    $DashBoards['/crmdashboard'] = 'CRM Dashboard';
    /*
    if(Company::isRMLicence()){
        $DashBoards['/dashboard'] = 'RM Dashboard';
        $DashBoards['/monitor'] = 'Monitor Dashboard';
    }
    if(Company::isBillingLicence()){
        $DashBoards['/salesdashboard'] = 'Sales Dashboard';
    }
    if(Company::isBillingLicence()){
        $DashBoards['/billingdashboard'] = 'Billing Dashboard';
    }*/

    return $DashBoards;
}

function getDashBoardController($key){
    /*$DashBoards['/dashboard'] = 'RmDashboard';
    $DashBoards['/salesdashboard'] = 'SalesDashboard';*/
    $DashBoards['/billingdashboard'] = 'BillingDashboard';
    $DashBoards['/monitor'] = 'MonitorDashboard';
    $DashBoards['/crmdashboard'] = 'CrmDashboard';
    return $DashBoards[$key];
}

function formatSmallDate($date,$dateformat='d-m-y') {
    return formatDate($date,$dateformat,true);
    /*if(ctype_digit($date) && strlen($date)==5){
        $UNIX_DATE = ($date - 25569) * 86400;
        $datetime = gmdate("Y-m-d", $UNIX_DATE);
    }else {
        $m_d_y='((?:[0]?[1-9]|[1][012])[-:\\/.](?:(?:[0-2]?\\d{1})|(?:[3][01]{1}))[-:\\/.](?:(?:\\d{1}\\d{1})))(?![\\d])'; // for	m-d-y when converted from british
        $d_m_y = '((?:(?:[0-2]?\\d{1})|(?:[3][01]{1}))[-:\\/.](?:[0]?[1-9]|[1][012])[-:\\/.](?:(?:\\d{1}\\d{1})))(?![\\d])';// for d-m-y british
        if ($c = preg_match_all("/" . $d_m_y . "/is", $date, $matches)) {
            $date_obj = \DateTime::createFromFormat('d-m-y', $date);
            if (!empty($date_obj)) {
                $datetime = $date_obj->format('Y-m-d');
            }
        }elseif($c = preg_match_all("/" . $m_d_y . "/is", $date, $matches)) {
            $date_obj = \DateTime::createFromFormat('m-d-y', $date);
            if (!empty($date_obj)) {
                $datetime = $date_obj->format('Y-m-d');
            }
        }
        if (!isset($datetime)|| empty($datetime)){
            $date_obj = date_create($date);
            if (is_object($date_obj)) {
                $datetime = date_format($date_obj, "Y-m-d");
            } else {
                $date_arr = date_parse($date);
                if (!empty($date_arr['year']) && !empty($date_arr['month']) && !empty($date_arr['day'])) {
                    $datetime = date("Y-m-d", mktime(0, 0, 0, $date_arr['month'], $date_arr['day'], $date_arr['year']));
                } else {
                    if (strpos($date, '.') !== false) {
                        $date = str_replace('.', '-', $date);
                    }
                    if (strpos($date, '/') !== false) {
                        $date = str_replace('/', '-', $date);
                    }
                    /*if (strpos($date, ' ') !== false) {
                        $date = str_replace(' ', '-', $date);
                    }*/
                   /* if ($dateformat == 'd-m-Y' && strpos($date, '/') !== false) {
                        $date = str_replace('/', '-', $date);
                        $datetime = date('Y-m-d', strtotime($date));
                    } else if ($dateformat == 'm-d-Y' && strpos($date, '-') !== false) {
                        $date = str_replace('-', '/', $date);
                        $datetime = date('Y-m-d', strtotime($date));
                    } else {
                        $datetime = date('Y-m-d', strtotime($date));
                    }
                }
            }
        }
    }

    if ($datetime == '1970-01-01') {
        $datetime = '';
    }
    return $datetime;*/
}

function SortBillingType($account=0){
    ksort(Company::$BillingCycleType);
    ksort(Company::$BillingCycleType2);
    if($account == 0) {
        return Company::$BillingCycleType;
    }else{
        return Company::$BillingCycleType2;
    }
}
function getUploadedFileRealPath($files)
{
    $realPaths = [];
    foreach ($files as $file) {
        $realPaths[] = '@' . $file->getRealPath() . ';filename=' . $file->getClientOriginalName();
    }
    return $realPaths;
}


function create_site_configration_cache(){
    $domain_url 					=   $_SERVER['HTTP_HOST'];
    $result 						= 	DB::table('tblCompanyThemes')->where(["DomainUrl" => $domain_url,'ThemeStatus'=>Themes::ACTIVE])->get();

    if($result){  //url found
        $cache['FavIcon'] 			=	empty($result[0]->Favicon)?URL::to('/').'/assets/images/favicon.ico':AmazonS3::unSignedImageUrl($result[0]->Favicon);
        $cache['Logo'] 	  			=	empty($result[0]->Logo)?URL::to('/').'/assets/images/logo@2x.png':AmazonS3::unSignedImageUrl($result[0]->Logo);
        $cache['Title']				=	$result[0]->Title;
        $cache['FooterText']		=	$result[0]->FooterText;
        $cache['FooterUrl']			=	$result[0]->FooterUrl;
        $cache['LoginMessage']		=	$result[0]->LoginMessage;
        $cache['CustomCss']			=	$result[0]->CustomCss;
    }else{
        $cache['FavIcon'] 			=	URL::to('/').'/assets/images/favicon.ico';
        $cache['Logo'] 	  			=	URL::to('/').'/assets/images/logo@2x.png';
        $cache['Title']				=	'Neon';
        $cache['FooterText']		=	'&copy; '.date('Y').' Code Desk';
        $cache['FooterUrl']			=	'http://www.code-desk.com';
        $cache['LoginMessage']		=	Lang::get("routes.CUST_PANEL_PAGE_LOGIN_LBL_LOGIN_MSG");
        $cache['CustomCss']			=	'';
    }

    Session::put('user_site_configrations', $cache);
}

//not in use
function addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}

function chart_reponse($alldata){

    $chartColor = array('#3366cc','#ff9900','#dc3912','#109618','#66aa00','#dd4477','#0099c6','#990099','#ec3b83','#f56954','#0A1EFF','#050FFF','#0000FF');
    $response['ChartColors'] = implode(',',$chartColor);

    if(empty($alldata['call_count'])) {
        $response['CallCountHtml'] = '<h4>'.Lang::get('routes.MESSAGE_DATA_NOT_AVAILABLE').'</h4>';
        $response['CallCount'] = '';
        $response['CallCountVal'] = '';
    }else{
        $response['CallCount'] = implode(',',$alldata['call_count']);
        $response['CallCountVal'] = implode(',',$alldata['call_count_val']);
        $response['CallCountHtml'] =  $alldata['call_count_html'];
    }
    if(empty($alldata['call_cost'])) {
        $response['CallCostHtml'] = '<h4>'.Lang::get('routes.MESSAGE_DATA_NOT_AVAILABLE').'</h4>';
        $response['CallCost'] = '';
        $response['CallCostVal'] = '';
    }else{
        $response['CallCost'] = implode(',',$alldata['call_cost']);
        $response['CallCostVal'] = implode(',',$alldata['call_cost_val']);
        $response['CallCostHtml'] = $alldata['call_cost_html'];
    }
    if(empty($alldata['call_minutes'])) {
        $response['CallMinutesHtml'] = '<h4>'.Lang::get('routes.MESSAGE_DATA_NOT_AVAILABLE').'</h4>';
        $response['CallMinutes'] = '';
        $response['CallMinutesVal'] = '';
    }else{
        $response['CallMinutes'] = implode(',',$alldata['call_minutes']);
        $response['CallMinutesVal'] = implode(',',$alldata['call_minutes_val']);
        $response['CallMinutesHtml'] = $alldata['call_minutes_html'];
    }
    return $response;
}
function get_report_type($date11,$date22){
    $date1 = new DateTime($date11);
    $date2 = new DateTime($date22);
    $interval = $date1->diff($date2);

    $report_type = 1;
    if($interval->y > 0 && $interval->y < 2){
        $report_type = 5;
    }else if($interval->y > 2) {
        $report_type = 6;
    }else if($interval->m >= 9 && $interval->m < 12) {
        $report_type = 4;
    }else if($interval->m >= 6 && $interval->m < 9) {
        $report_type = 4;
    }else if($interval->m >= 3 && $interval->m < 6) {
        $report_type = 3;
    }else if($interval->m >= 1 && $interval->m < 3) {
        $report_type = 3;
    }else if($interval->d >= 15 && $interval->d < 31) {
        $report_type = 2;
    }else if($interval->d > 0 && $interval->d < 15) {
        $report_type = 2;
    }
    return $report_type;
}
function get_report_title($report_type){
    $report_title = 'Call Analysis By Time';
    if ($report_type == 1) {
        $report_title = 'Hourly Call Analysis';
    } else if ($report_type == 2) {
        $report_title = 'Daily Call Analysis';
    } else if ($report_type == 3) {
        $report_title = 'Weekly Call Analysis';
    } else if ($report_type == 4) {
        $report_title = 'Monthly Call Analysis';
    } else if ($report_type == 5) {
        $report_title = 'Quarterly Call Analysis';
    } else if ($report_type == 6) {
        $report_title = 'Yearly Call Analysis';
    }
    return $report_title;
}

function get_random_number(){
    return md5(uniqid(rand(), true));
}

// sideabar submenu open when click on
function check_uri($parent_link=''){
    $Path 			  =    Route::currentRouteAction();
    $path_array 	  =    explode("Controller",$Path);
    $array_settings   =    array("Users","Trunk","CodeDecks","Gateway","Currencies","CurrencyConversion","DestinationGroup","DialString");
    $array_admin	  =	   array("Users","Role","Themes","AccountApproval","FileUploadTemplate","EmailTemplate","Notification","ServerInfo","Retention","NoticeBoard");
    $array_summary    =    array("Summary");
    $array_rates	  =	   array("RateTables","LCR","RateGenerators","VendorProfiling","AutoRateImport");
    $array_autoImport =	   array("AutoRateImport");
	$array_tickets	  =	   array("Tickets","TicketsFields","TicketsGroup","Dashboard","TicketsSla","TicketsBusinessHours","TicketImportRules");
    $array_template   =    array("");
    $array_dashboard  =    array("Dashboard");
	$array_crm 		  =    array("OpportunityBoard","Task","Dashboard");
    $array_billing    =    array("Dashboard",'Estimates','Invoices','RecurringInvoice','Dispute','BillingSubscription','Payments','AccountStatement','Products','InvoiceTemplates','TaxRates','CDR',"Discount","BillingClass","Services");
    $customer_billing    =    array('InvoicesCustomer','PaymentsCustomer','AccountStatementCustomer','PaymentProfileCustomer','CDRCustomer',"DashboardCustomer");
	
    if(count($path_array)>0)
    {
  		$controller = $path_array[0];
	   	if(in_array($controller,$array_billing) && $parent_link =='Billing')
        {
			if(Request::segment(1)!='monitor' && $path_array[1]!='@CrmDashboard' && $path_array[1]!='@TicketDashboard'){
            	return 'opened';
			} 
        }

        if(in_array($controller,$array_settings) && $parent_link =='Settings')
        {  if($controller=='Users' && isset($_REQUEST['sm'])){
            	return 'opened';
			}else if($controller!='Users'){
				return 'opened';
			}
        }

        if(in_array($controller,$array_admin) && $parent_link =='Admin')
        {
			if(!isset($_REQUEST['sm'])){
            	return 'opened';
			}
        }

        if(in_array($controller,$array_summary) && $parent_link =='Summary')
        {
            return 'opened';
        }

        if(in_array($controller,$array_rates) && $parent_link =='Rates')
        {
            return 'opened';
        }
        if(in_array($controller,$array_autoImport) && $parent_link =='AutoImport')
        {
            return 'opened';
        }

        if(in_array($controller,$array_crm) && $parent_link =='Crm')
        {
			if($path_array[1]!='@billingdashboard' && $path_array[1]!='@monitor_dashboard' && $path_array[1]!='@TicketDashboard'){
				return 'opened';
			}
        }

        if(in_array($controller,$array_dashboard) && $parent_link =='Dashboard')
        {
            return 'opened';
        }

        if(in_array($controller,$customer_billing) && $parent_link =='Customer_billing')
        {
            return 'opened';
        }
		
		 if(in_array($controller,$array_tickets) && $parent_link =='tickets' && $path_array[1]!='@CrmDashboard' && $path_array[1]!='@monitor_dashboard' && $path_array[1]!='@billingdashboard')
        {
            return 'opened';
        }
    }
}


function getimageicons($url){
    $file = new SplFileInfo($url);
    $ext  = $file->getExtension();
    $icons = [
        '7z'=>URL::to('/').'/assets/images/icons/7z.png',
        'bmp'=>URL::to('/').'/assets/images/icons/bmp.png',
        'csv'=>URL::to('/').'/assets/images/icons/csv.png',
        'doc'=>URL::to('/').'/assets/images/icons/doc.png',
        'docx'=>URL::to('/').'/assets/images/icons/docx.png',
        'gif'=>URL::to('/').'/assets/images/icons/gif.png',
        'ini'=>URL::to('/').'/assets/images/icons/ini.png',
        'jpg'=>URL::to('/').'/assets/images/icons/jpg.png',
        'msg'=>URL::to('/').'/assets/images/icons/msg.png',
        'odt'=>URL::to('/').'/assets/images/icons/odt.png',
        'pdf'=>URL::to('/').'/assets/images/icons/pdf.png',
        'png'=>URL::to('/').'/assets/images/icons/png.png',
        'ppt'=>URL::to('/').'/assets/images/icons/ppt.png',
        'pptx'=>URL::to('/').'/assets/images/icons/pptx.png',
        'rar'=>URL::to('/').'/assets/images/icons/rar.png',
        'rtf'=>URL::to('/').'/assets/images/icons/rtf.png',
        'txt'=>URL::to('/').'/assets/images/icons/txt.png',
        'xls'=>URL::to('/').'/assets/images/icons/xls.png',
        'xlsx'=>URL::to('/').'/assets/images/icons/xlsx.png',
        'zip'=>URL::to('/').'/assets/images/icons/zip.png'
    ];
    if(array_key_exists(strtolower($ext),$icons)){
        return $icons[strtolower($ext)];
    }else{
        return URL::to('/').'/assets/images/icons/file.png';
    }
}

function get_uploaded_files($session,$data){
    $files='';
    if (Session::has($session)){
        $files_array = Session::get($session);
        if(isset($files_array[$data['token_attachment']])) {
            $files = $files_array[$data['token_attachment']];
            unset($files_array[$data['token_attachment']]);
            Session::set($session, $files_array);
        }
    }
    return $files;
}

function get_max_file_size()
{
    $max_file_env   = CompanyConfiguration::get('MAX_UPLOAD_FILE_SIZE');
    $max_file_size   = !empty($max_file_env)?CompanyConfiguration::get('MAX_UPLOAD_FILE_SIZE'):ini_get('post_max_size');
    return $max_file_size;
}
function isJson($string) {
    try{
        json_decode($string);
        return true;
    
    }
    catch (Exception $ex)
       {
          return false;
       }

}

/**
 * Get Round up decimal places from company or account
 * @param $array
 */
function get_round_decimal_places($AccountID = 0) {

    if($AccountID>0){
		$RoundChargesAmount = AccountBilling::getRoundChargesAmount($AccountID);
	}else{
		$RoundCharges=CompanySetting::getKeyVal('RoundChargesAmount');
		if($RoundCharges!='Invalid Key'){
			$RoundChargesAmount = $RoundCharges;
		}
	}
	if(empty($RoundChargesAmount)){
		$RoundChargesAmount = 2;
	}
    return $RoundChargesAmount;
}


function ValidateSmtp($SMTPServer,$Port,$EmailFrom,$IsSSL,$SMTPUsername,$SMTPPassword,$address,$ToEmail){ 
    $mail 				= 	new PHPMailer;
    $mail->isSMTP();
	//$mail->SMTPDebug = 2;                  
    $mail->Host 		= 	$SMTPServer;
    $mail->SMTPAuth 	= 	true;
    $mail->Username 	= 	$SMTPUsername;
    $mail->Password 	= 	$SMTPPassword;
    $mail->SMTPSecure	= 	$IsSSL==1?'ssl':'tls';
    $mail->Port 		= 	$Port;
    $mail->From 		= 	$address;
    $mail->FromName 	= 	'Test Smtp server';
    $mail->Body 		= 	"Testing Smtp mail Settings";
    $mail->Subject 		= 	"Test Smtp Email";
    $mail->Timeout		=    25;
  /*if($mail->smtpConnect()){
		$mail->smtpClose();*/
	$mail->addAddress($ToEmail); 
   if ($mail->send()) {
	   return "Valid mail settings.";
	}else{ 
		return "Invalid mail settings.";
	}
 }
	 
function account_expense_table($Expense,$customer_vendor){
    $datacount = $colsplan=  0;
    $tableheader = $tablebody = '';
    if(!empty($Expense) && !isset($Expense[0]->datacount)) {
        foreach ($Expense as $ExpenseRow) {
            if ($datacount == 0) {
                $tableheader = '<tr>';
            }
            $tablebody .= '<tr>';
            foreach ($ExpenseRow as $yearmonth => $total) {
                if ($datacount == 0) {
                    if ($yearmonth != 'AreaPrefix') {
                        $tableheader .= "<th>$yearmonth</th>";
                    } else {
                        $tableheader .= "<th>Top Prefix</th>";
                    }
                    $colsplan++;
                }
                $tablebody .= "<td>$total</td>";
            }
            if ($datacount == 0) {
                $tableheader .= '</tr>';
            }
            $tablebody .= '</tr>';
            $datacount++;
        }
    }else{
        $tablebody = '<tr><td>No Data</td></tr>';
    }
    $tableheader = "<thead><tr><th colspan='".$colsplan."'>$customer_vendor Activity</th></tr>".$tableheader."</thead>";
    return $tablehtml = $tableheader."<tbody>".$tablebody."</tbody>";
}
function view_response_api($response){
    $message = '';
    $isArray = false;
    if(is_array($response)){
        $isArray = true;
    }
    //@TODO: there is no key with Code.
    if(($isArray && isset($response['Code']) && $response['Code'] ==401) || (!$isArray && isset($response->Code) && $response->Code == 401)) {
        //return Redirect::to('/logout');
        \Illuminate\Support\Facades\Log::info("helpers.php view_response_api");
        \Illuminate\Support\Facades\Log::info(print_r($response,true));
    }else if(($isArray && $response['status'] =='failed') || !$isArray && $response->status=='failed'){
        $Code = $isArray?$response['Code']:$response->Code;
        $validator = $isArray?$response['message']:(array)$response->message;
        if (count($validator) > 0) {
            foreach ($validator as $index => $error) {
                if(is_array($error)){
                    $message .= array_pop($error) . "<br>";
                }
            }
        }
        Log::info($message);
        if($Code > 0) {
            return App::abort($Code, $message);
        }
    }

}

function terminate_process($pid){

    $process = new Process();
    $process->setPid($pid);
    $status = $process->stop();
    return $status;

}
function run_process($command) {
    $process = new Process($command);
    return $status = $process->status();
}

function Get_Api_file_extentsions($ajax=false){

	 /*if (Session::has("api_response_extensions")){
		  $response_extensions['allowed_extensions'] =  Session::get('api_response_extensions'); 
		  if(is_array($response_extensions['allowed_extensions'])){
			  return $response_extensions;
		  }		 
	 } 	 */
	 $response     			=  NeonAPI::request('get_allowed_extensions',[],false);
	 $response_extensions 	=  [];
	 	
	if($response->status=='failed'){
		if($ajax==true){
			return $response;
		}else{
			
			if(isset($response->Code) && ($response->Code==400 || $response->Code==401)){
                \Illuminate\Support\Facades\Log::info("helpers.php Get_Api_file_extentsions 401 ");
                \Illuminate\Support\Facades\Log::info(print_r($response,true));
                //return Redirect::to('/logout');
			}
			if(isset($response->error) && $response->error=='token_expired'){
                \Illuminate\Support\Facades\Log::info("helpers.php Get_Api_file_extentsions token_expired");
                \Illuminate\Support\Facades\Log::info(print_r($response,true));
                //return Redirect::to('/login');
            }
		}
	}else{		
		$response_extensions 		 = 	json_response_api($response,true,true); 
		$response_extensions 		 = 	json_decode($response_extensions);		
		$array['allowed_extensions'] = 	$response_extensions;
		Session::put('api_response_extensions', $response_extensions);
		return $array;
	}
}

function getBillingDay($BillingStartDate,$BillingCycleType,$BillingCycleValue){
    $BillingDays = 0;
    switch ($BillingCycleType) {
        case 'weekly':
            $BillingDays = 7;
            break;
        case 'monthly':
            $BillingDays = date("t", $BillingStartDate);
            break;
        case 'daily':
            $BillingDays = 1;
            break;
        case 'in_specific_days':
            $BillingDays = intval($BillingCycleValue);
            break;
        case 'monthly_anniversary':

            $day = date("d",  strtotime($BillingCycleValue)); // Date of Anivarsary
            $month = date("m",  $BillingStartDate); // Month of Last Invoice date or Start Date
            $year = date("Y",  $BillingStartDate); // Year of Last Invoice date or Start Date

            $newDate = strtotime($year . '-' . $month . '-' . $day);

            if($day<=date("d",  $BillingStartDate)) {
                $NextInvoiceDate = date("Y-m-d", strtotime("+1 month", $newDate));
                $LastInvoiceDate = date("Y-m-d",$newDate);
            }else{
                $NextInvoiceDate = date("Y-m-d",$newDate);
                $LastInvoiceDate = date("Y-m-d", strtotime("-1 month", $newDate));
            }
            $date1 = new DateTime($LastInvoiceDate);
            $date2 = new DateTime($NextInvoiceDate);
            $interval = $date1->diff($date2);
            $BillingDays =  $interval->days;

            break;
        case 'fortnightly':
            $fortnightly_day = date("d", $BillingStartDate);
            if($fortnightly_day > 15){
                $NextInvoiceDate = date("Y-m-d", strtotime("first day of next month ",$BillingStartDate));
                $LastInvoiceDate = date("Y-m-16", $BillingStartDate);
            }else {
                $NextInvoiceDate = date("Y-m-16", $BillingStartDate);
                $LastInvoiceDate = date("Y-m-01", $BillingStartDate);
            }
            $date1 = new DateTime($LastInvoiceDate);
            $date2 = new DateTime($NextInvoiceDate);
            $interval = $date1->diff($date2);
            $BillingDays =  $interval->days;
            break;
        case 'quarterly':
            $quarterly_month = date("m", $BillingStartDate);
            if($quarterly_month < 4){
                $NextInvoiceDate = date("Y-m-d", strtotime("first day of april ",$BillingStartDate));
                $LastInvoiceDate = date("Y-m-d", strtotime("first day of january ",$BillingStartDate));
            }else if($quarterly_month > 3 && $quarterly_month < 7) {
                $NextInvoiceDate = date("Y-m-d", strtotime("first day of july ",$BillingStartDate));
                $LastInvoiceDate = date("Y-m-d", strtotime("first day of april ",$BillingStartDate));
            }else if($quarterly_month > 6 && $quarterly_month < 10) {
                $NextInvoiceDate = date("Y-m-d", strtotime("first day of october ",$BillingStartDate));
                $LastInvoiceDate = date("Y-m-d", strtotime("first day of july ",$BillingStartDate));
            }else if($quarterly_month > 9){
                $NextInvoiceDate = date("Y-01-01", strtotime('+1 year ',$BillingStartDate));
                $LastInvoiceDate = date("Y-m-d", strtotime("first day of october ",$BillingStartDate));
            }
            $date1 = new DateTime($LastInvoiceDate);
            $date2 = new DateTime($NextInvoiceDate);
            $interval = $date1->diff($date2);
            $BillingDays =  $interval->days;
            break;
    }
    return $BillingDays;
}
function next_billing_date($BillingCycleType,$BillingCycleValue,$BillingStartDate){
    $NextInvoiceDate = '';
    if(isset($BillingCycleType)) {
        switch ($BillingCycleType) {
            case 'weekly':
                if (!empty($BillingCycleValue)) {
                    $NextInvoiceDate = date("Y-m-d", strtotime("next " . $BillingCycleValue,$BillingStartDate));
                }else{
                    $NextInvoiceDate = date("Y-m-d", strtotime("next monday")); /** default value set to monday if not set in account **/
                }
                break;
            case 'monthly':
                $NextInvoiceDate = date("Y-m-d", strtotime("first day of next month ",$BillingStartDate));
                break;
            case 'daily':
                $NextInvoiceDate = date("Y-m-d", strtotime("+1 Days",$BillingStartDate));
                break;
            case 'in_specific_days':
                if (!empty($BillingCycleValue)) {
                    $NextInvoiceDate = date("Y-m-d", strtotime("+" . intval($BillingCycleValue)  . " Day",$BillingStartDate));
                }
                break;
            case 'monthly_anniversary':

                $day = date("d",  strtotime($BillingCycleValue)); // Date of Anivarsary
                $month = date("m",  $BillingStartDate); // Month of Last Invoice date or Start Date
                $year = date("Y",  $BillingStartDate); // Year of Last Invoice date or Start Date

                $newDate = strtotime($year . '-' . $month . '-' . $day);

                if($day<=date("d",  $BillingStartDate)) {
                    $NextInvoiceDate = date("Y-m-d", strtotime("+1 month", $newDate));
                }else{
                    $NextInvoiceDate = date("Y-m-d",$newDate);
                }

                break;
            case 'fortnightly':
                $fortnightly_day = date("d", $BillingStartDate);
                if($fortnightly_day > 15){
                    $NextInvoiceDate = date("Y-m-d", strtotime("first day of next month ",$BillingStartDate));
                }else{
                    $NextInvoiceDate = date("Y-m-16", $BillingStartDate);
                }
                break;
            case 'quarterly':
                $quarterly_month = date("m", $BillingStartDate);
                if($quarterly_month < 4){
                    $NextInvoiceDate = date("Y-m-d", strtotime("first day of april ",$BillingStartDate));
                }else if($quarterly_month > 3 && $quarterly_month < 7) {
                    $NextInvoiceDate = date("Y-m-d", strtotime("first day of july ",$BillingStartDate));
                }else if($quarterly_month > 6 && $quarterly_month < 10) {
                    $NextInvoiceDate = date("Y-m-d", strtotime("first day of october ",$BillingStartDate));
                }else if($quarterly_month > 9){
                    $NextInvoiceDate = date("Y-01-01", strtotime('+1 year ',$BillingStartDate));
                }
                break;
            case 'yearly':
                $NextInvoiceDate = date("Y-m-d", strtotime("+1 year", $BillingStartDate));
                break;
        }
        $Timezone = Company::getCompanyTimeZone(0);
        if(isset($Timezone) && $Timezone != ''){
            date_default_timezone_set($Timezone);
        }

    }
    return $NextInvoiceDate;
}
function tax_exists($TaxRateID, $array) {
    $result = -1;
    for($i=0; $i<sizeof($array); $i++) {
        if ($array[$i]['TaxRateID'] == $TaxRateID) {
            $result = $i;
            break;
        }
    }
    return $result;
}
function merge_tax($taxs) {
    $InvoiceTaxRates = array();
    foreach($taxs as $tax) {
        if($tax['TaxRateID']) {
            $index = tax_exists($tax['TaxRateID'], $InvoiceTaxRates);
            if ($index < 0) {
                $InvoiceTaxRates[] = $tax;
            } else {
                $InvoiceTaxRates[$index]['TaxAmount'] += $tax['TaxAmount'];
            }
        }
    }
    return $InvoiceTaxRates;
}
function getdaysdiff($date1,$date2){
    $date1 = new DateTime($date1);
    $date2 = new DateTime($date2);
    return $date2->diff($date1)->format("%R%a");
}

function ShortName($title,$length=8){
	if(strlen($title)>$length)
	{
		return substr($title,0,$length).'..';
	}else{
		return $title;
	}
}

function is_Stripe($CompanyID){
    return	SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$StripeSlug,$CompanyID);
}
function is_StripeACH($CompanyID){
    return	SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$StripeACHSlug,$CompanyID);
}
function is_SagePayDirectDebit($CompanyID){
    return	SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$SagePayDirectDebitSlug,$CompanyID);
}
function is_FideliPay($CompanyID){
    return	SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$FideliPaySlug,$CompanyID);
}
function is_Xero($CompanyID){
    return	SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$XeroSlug,$CompanyID);
}
function is_merchantwarrior($CompanyID){
    return	SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$MerchantWarriorSlug,$CompanyID);
}
function change_timezone($billing_timezone,$timezone,$date){
    if(!empty($timezone) && !empty($billing_timezone)) {
        date_default_timezone_set($billing_timezone);
        $strtotime = strtotime($date);
        date_default_timezone_set($timezone);
        $changed_date = date('Y-m-d H:i:s', $strtotime);
        date_default_timezone_set(Config::get('app.timezone'));
        return $changed_date;
    }
    return $date;
}

// not using
function getQuickBookAccountant($CompanyID){
    $ChartofAccounts = array();
    $Quickbook = new BillingAPI($CompanyID);
    $check_quickbook = $Quickbook->check_quickbook($CompanyID);
    if($check_quickbook){
        $ChartofAccounts = $Quickbook->getChartofAccounts();
        if(!empty($ChartofAccounts) && count($ChartofAccounts)>0){
            $ChartofAccounts = array(""=> "Select Chart of accounts")+$ChartofAccounts;
        }
    }
    return $ChartofAccounts;
}

	
	function email_log_data_Ticket($data,$view = '',$status){ 
	$EmailParent =	 0;
	if($data['TicketID']){
		//$EmailParent =	TicketsTable::where(["TicketID"=>$data['TicketID']])->pluck('AccountEmailLogID');
	}

	
    $status_return = array('status' => 0, 'message' => 'Something wrong with Saving log.');
    if(!isset($data['EmailTo']) && empty($data['EmailTo'])){
        $status_return['message'] = 'Email To not set in Account mail log';
        return $status_return;
    }
    
    if(!isset($data['Subject']) && empty($data['Subject'])){
        $status_return['message'] = 'Subject not set in Account mail log';
        return $status_return;
    }
    if(!isset($data['Message']) && empty($data['Message'])){
        $status_return['message'] = 'Message not set in Account mail log';
        return $status_return;
    }

    if(is_array($data['EmailTo'])){
        $data['EmailTo'] = implode(',',$data['EmailTo']);
    }

    if(!isset($data['cc']))
    {
        $data['cc'] = '';
    }

    if(!isset($data['bcc']))
    {
        $data['bcc'] = '';
    }

    if(isset($data['AttachmentPaths']) && count($data['AttachmentPaths'])>0)
    {
        $data['AttachmentPaths'] = serialize($data['AttachmentPaths']);
    }
    else
    {
        $data['AttachmentPaths'] = serialize([]);
    }

    if($view!='')
    {
        $body = htmlspecialchars_decode(View::make($view, compact('data'))->render());
    }
    else
    {
        $body = $data['Message'];
    } 
	if(!isset($status['message_id']))
	{
		$status['message_id'] = '';
	} 
	if(!isset($data['EmailCall']))
	{
		$data['EmailCall'] = Messages::Sent;
	}

	if(isset($data['EmailFrom']))
	{
		$data['EmailFrom'] = $data['EmailFrom'];
	}else{
		$data['EmailFrom'] = User::get_user_email();
	}
	
    $logData = ['EmailFrom'=>$data['EmailFrom'],
        'EmailTo'=>$data['EmailTo'],
        'Subject'=>$data['Subject'],
        'Message'=>$body,
        'CompanyID'=>User::get_companyID(),
        'UserID'=>User::get_userID(),
        'CreatedBy'=>User::get_user_full_name(),
		"created_at"=>date("Y-m-d H:i:s"),
        'Cc'=>$data['cc'],
        'Bcc'=>$data['bcc'],
        "AttachmentPaths"=>$data['AttachmentPaths'],
		"MessageID"=>$status['message_id'],
		"EmailParent"=>isset($data['EmailParent'])?$data['EmailParent']:$EmailParent,
		"EmailCall"=>$data['EmailCall'],
    ];
    $data =  AccountEmailLog::insertGetId($logData);
    return $data;
}
function generateGroupConcat($data,$columns){
    $query_row = '';
    if(count($columns)) {
        foreach ($columns as $key => $single_columns) {
            $column_name = $query_condition = '';

            foreach ($single_columns as $col_name => $col_val) {
                $query_condition .= " $col_name = '" . $col_val . "'";
                $query_condition .= " AND ";
                $column_name .= $col_val . "###";
            }
            $query_condition = rtrim($query_condition, ' AND ');
            foreach ($data['sum'] as $col_sum) {
                $query_row .= "SUM(IF( " . $query_condition . ", " . $col_sum . ", 0)) AS '" . $column_name . $col_sum . "'";
                $query_row .= ",";
            }
        }
    }else{
        foreach ($data['sum'] as $col_sum) {
            $query_row .= "SUM(" . $col_sum . ") AS '" . $col_sum . "'";
            $query_row .= ",";
        }
    }
    $query_row = rtrim($query_row,',');
    return $query_row;
}

function generateReportTable($data,$response)
{
    $row_count = count($data['row']);

    if(!empty($response['data'])) {
        $table = '<table class="table table-bordered">';

        $header_array = array_slice(array_keys($response['data'][0]), $row_count, count(array_keys($response['data'][0])));

        $table .= header_array_html($header_array, $data, $response);

        $table .= row_array_html($data, $response);
    }else{
        $table = 'No Data Found Or Select at least one Measure';
    }

    return $table;
}



function header_array_html($main_header,$data,$response){
    $index_col = 0;
    $row_count = count($data['row']);
    $table_header = $table_header_colgroup = '';
    $chartColor = array('#C5CAE9','#BBDEFB','#B3E5FC','#B2EBF2','#C8E6C9','#DCEDC8','#F0F4C3','#FFCCBC','#D7CCC8','#F5F5F5','#CFD8DC','#050FFF','#0000FF');
    if($row_count) {
        $table_header_colgroup .= '<colgroup span="' . $row_count . '" style="background-color:' . $chartColor[0] . '"></colgroup>';
    }
    $header_html = array();
    foreach ($main_header as $headers) {
        $headerrow = explode('###',$headers);
        $key_comb = '';
        if(count($headerrow)>1) {
            foreach ($headerrow as $key => $header) {
                $key_comb .= $header.'!';
                if (empty(${'count_' . $key_comb})) {
                    ${'count_' . $key_comb} = 0;
                }
                if (isset($data['column'][$key])) {
                    $col_name = $data['column'][$key];
                    $header_name = '';
                    if (isset($response['column']['names'][$col_name][$header])) {
                        $header_name = $response['column']['names'][$col_name][$header];
                    }
                    ${'count_' . $key_comb}++;
                    if (!isset($header_html[$key][$key_comb])) {
                        $header_html[$key][$key_comb]['name'] = $header_name;
                    }
                    $header_html[$key][$key_comb]['colspan'] = ${'count_' . $key_comb};
                }
            }
        }
    }
    //print_r($header_html);exit;
    if(count($data['column'])) {
        foreach ($data['column'] as $key => $col_name) {
            $table_header .= '<tr>';
            if ($key == 0) {
                foreach ($data['row'] as $rowkey => $blankrow_name) {
                    $table_header .= '<td rowspan="' . (count($data['column']) + 1) . '"></td>';
                }
            }
            foreach ($header_html[$key] as $row_val) {
                $color = '#FFF';
                if ($key == 0) {
                    if(isset($chartColor[$index_col + 1])) {
                        $color = $chartColor[$index_col + 1];
                    }
                    $table_header_colgroup .= '<colgroup span="' . $row_val['colspan'] . '" style="background-color:' . $color. '"></colgroup>';
                }
                $table_header .= '<th colspan="' . $row_val['colspan'] . '" scope="colgroup">' . $row_val['name'] . '</th>';
                $index_col++;
            }
            $table_header .= '</tr>';
        }
    }


    /** header columns */
    $i_count = 0;
    $table_header .= '<tr>';
    if(count($data['column']) == 0) {
        foreach ($data['row'] as $rowkey => $blankrow_name) {
            $table_header .= '<th scope="col"></th>';
        }
    }
    foreach ($response['data'] as $row) {
        foreach ($row as $col_name => $col_val) {
            $rowarray = explode('###', $col_name);
            if($row_count <= $i_count) {
                $table_header .= '<th scope="col">' . end($rowarray) . '</th>';
            }
            $i_count++;
        }
        break;
    }
    $table_header .= '</tr>';


    return $table_header_colgroup.$table_header;

}
function row_array_html($data,$response){
    /** display row and it's data */
    $table_data = '';
    $row_count = count($data['row']);
    $header_html = array();
    foreach ($response['data'] as $tablerow) {
        $key_comb = '';
        $key_index= 0;
        foreach ($tablerow as $col_name => $col_val) {
            $rowarray = explode('###', $col_name);
            if (count($rowarray) < 2) {
                $key_comb .= $col_val.'#';
                if (empty(${'count_' . $key_comb})) {
                    ${'count_' . $key_comb} = 0;
                }
                $header_name = Report::getName($col_name,$col_val);
                if (!isset($header_html[$key_index][$key_comb])) {
                    $header_html[$key_index][$key_comb]['name'] = $header_name;
                }
                ${'count_' . $key_comb}++;
                $header_html[$key_index][$key_comb]['rowspan'] = ${'count_' . $key_comb};
                $key_index++;
            }

        }
    }
    //print_r($header_html);exit;


    foreach ($response['data'] as $row) {
        $table_data .= '<tr>';
        $key_comb = '';
        $key_index= 0;
        $table_row = $table_col_row = '';
        foreach ($row as $col_name => $col_val) {
            if ($row_count <= $key_index) {
                $table_row .= '<td>' . $col_val . '</td>';
            } else {
                $key_comb .= $col_val.'#';
                if (empty(${'count_new_' . $key_comb})) {
                    ${'count_new_' . $key_comb} = $header_html[$key_index][$key_comb]['rowspan'];
                }
                if (${'count_new_' . $key_comb} == $header_html[$key_index][$key_comb]['rowspan']) {
                    $table_col_row .= '<td rowspan="' . $header_html[$key_index][$key_comb]['rowspan'] . '">' . $header_html[$key_index][$key_comb]['name'] . '</td>';
                }
                ${'count_new_' . $key_comb}--;
            }
            $key_index++;
        }
        $table_row = $table_col_row . $table_row;
        $table_data .= $table_row;
        $table_data .= '</tr>';
    }
    $table_data .= '</table>';

    return $table_data;

}
function table_array($data,$response,$all_data_list){
    $table_data = array();
    $col_seprator = '##';
    $row_seprator = '!!';

    if(count($data['row'])) {

        foreach ($response['distinct_row'] as $col_key => $data_key) {
            //$data_key_array = array_filter(explode($row_seprator,$data_key));
            $data_key_array = explode($row_seprator, $data_key);
            array_pop($data_key_array);
            for ($i = 1; $i <= count($data['row']); $i++) {
                $fincalcol_key = implode($row_seprator,array_slice($data_key_array,0,$i));
                if (empty(${'new_count_rowspan_' . $fincalcol_key . $row_seprator})) {
                    ${'new_count_rowspan_' . $fincalcol_key . $row_seprator} = 0;
                }
                ${'new_count_rowspan_' . $fincalcol_key . $row_seprator}++;
                //echo ${'new_count_rowspan_' . $fincalcol_key.$row_seprator}.PHP_EOL;
                //echo ' key ============'.$fincalcol_key.$row_seprator.'========'.PHP_EOL;
                $table_data['row'][$i-1][$fincalcol_key . $row_seprator]['rowspan'] = ${'new_count_rowspan_' . $fincalcol_key . $row_seprator};
                $table_data['data'][$fincalcol_key . $row_seprator] = array();
            }
        }
        ksort($table_data['row']);
        //print_r($table_data);exit;

    }

    foreach ($response['data'] as $row){
        $key_col_comb = $key_row_comb = '';
        $key_col_index = $key_row_index = 0;
        foreach ($row as $col_name => $col_val) {
            if(empty($response['name'][$col_name][$col_val])){
                $response['name'][$col_name][$col_val] = Report::getName($col_name,$col_val,$all_data_list);
            }

            if(in_array($col_name,$data['column'])){
                $key_col_comb .= $col_val.$col_seprator;
                if (empty(${'count_colspan_' . $key_col_comb})) {
                    ${'count_colspan_' . $key_col_comb} = 0;
                }
                $table_data['columns'][$key_col_index][$key_col_comb]['name'] =  $response['name'][$col_name][$col_val];//$col_name.'@'.$col_val
                ${'count_colspan_' . $key_col_comb}++;
                $table_data['columns'][$key_col_index][$key_col_comb]['colspan'] = ${'count_colspan_' . $key_col_comb};
                $key_col_index++;
            }

            if(in_array($col_name,$data['row'])){
                $key_row_comb .= $col_val.$row_seprator;
                if (empty(${'count_rowspan_' . $key_row_comb})) {
                    ${'count_rowspan_' . $key_row_comb} = 0;
                }
                $table_data['row'][$key_row_index][$key_row_comb]['name'] = $response['name'][$col_name][$col_val]; //$col_name.'@'.$col_val
                ${'count_rowspan_' . $key_row_comb}++;
                if(!isset($table_data['row'][$key_row_index][$key_row_comb]['rowspan'])) {
                    $table_data['row'][$key_row_index][$key_row_comb]['rowspan'] = ${'count_rowspan_' . $key_row_comb};
                }
                $key_row_index++;
            }

            if(in_array($col_name,$data['sum'])){
                if(empty($table_data['columns'][$key_col_index][$key_col_comb]['name']) || !in_array($col_name,$table_data['columns'][$key_col_index][$key_col_comb]['name'])) {
                    $table_data['columns'][$key_col_index][$key_col_comb]['name'][] = $col_name;
                }
                $table_data['columns'][$key_col_index][$key_col_comb]['colspan'] = 1;
            }
            $key_val_comb = $key_col_comb.$col_name;
            $table_data['data'][$key_row_comb][$key_val_comb] = $col_val;
        }
    }

    if(count($data['column'])) {
        foreach ($table_data['columns'][count($table_data['columns']) - 1] as $col_key => $column) {
            $fincalcol_key = $col_key;
            for ($i = count($table_data['columns']) - 2; $i >= 0; $i--) {
                $fincalcol_key = str_lreplace($col_seprator, '', $fincalcol_key);
                if (empty(${'new_count_colspan_' . $fincalcol_key . $col_seprator})) {
                    ${'new_count_colspan_' . $fincalcol_key . $col_seprator} = 0;
                }
                ${'new_count_colspan_' . $fincalcol_key . $col_seprator} = ${'new_count_colspan_' . $fincalcol_key . $col_seprator} + count($column['name']);
                //echo ${'new_count_colspan_' . $fincalcol_key.$col_seprator}.PHP_EOL;
                //echo ' key ============'.$fincalcol_key.$col_seprator.'========'.PHP_EOL;
                $table_data['columns'][$i][$fincalcol_key . $col_seprator]['colspan'] = ${'new_count_colspan_' . $fincalcol_key . $col_seprator};
            }
        }
        if(count($table_data['columns']) > 2){
            $columns_keys = array_keys($table_data['columns'][count($table_data['columns']) - 2]);
        }else{
            $columns_keys = array_keys($table_data['columns'][count($table_data['columns']) - 1]);
        }

        foreach ($table_data['data'] as $key => $table_row){
            $table_new_row = array();
            foreach ($columns_keys as $columns_key) {
                foreach ($data['sum'] as $sum_col) {
                    if (!isset($table_row[$columns_key.$sum_col])) {
                        $table_new_row[$columns_key.$sum_col] = '';
                    } else {
                        $table_new_row[$columns_key.$sum_col] = $table_row[$columns_key.$sum_col];
                    }
                }
            }
            $table_data['data'][$key] = $table_new_row;
        }
    }


    //echo '<pre>';print_r($table_data);exit;
    return $table_data;
}
function table_html($data,$table_data){
    $index_col = 1;
    $cube = $data['Cube'];
    $row_count = count($data['row']);
    $col_count = count($data['column']);
    $table_header = $table_header_colgroup = $table_row = $table_footer = '';
    $table_data['table_footer_sum'] = array();
    $setting_rename = isset($data['setting_rename'])?json_decode($data['setting_rename'],true):array();
    $chartColor = array('#C5CAE9','#BBDEFB','#B3E5FC','#B2EBF2','#C8E6C9','#DCEDC8','#F0F4C3','#FFCCBC','#D7CCC8','#F5F5F5','#CFD8DC');
    if($row_count) {
        $table_header_colgroup .= '<colgroup span="' . $row_count . '" style="background-color:' . $chartColor[0] . '"></colgroup>';
    }
    if(count($data['column'])) {
        foreach ($data['column'] as $key => $col_name) {
            $table_header .= '<tr>';
            if ($key == 0) {
                foreach ($data['row'] as $rowkey => $blankrow_name) {
                    $table_header .= '<td rowspan="' . (count($data['column']) + 1) . '">'.ucwords($blankrow_name).'</td>';
                }
            }

            foreach ($table_data['columns'][$key] as $row_val) {
                $color = '#FFF';
                if ($key == 0) {
                    if(!isset($chartColor[$index_col])) {
                        $index_col = 1;
                    }
                    if(isset($chartColor[$index_col])) {
                        $color = $chartColor[$index_col];
                    }
                    
                    $table_header_colgroup .= '<colgroup span="' . $row_val['colspan'] . '" style="background-color:' . $color. '"></colgroup>';
                }
                if(isset($row_val['name'])){
                    $table_header .= '<th colspan="' .$row_val['colspan'] . '" scope="colgroup"><strong>' . $row_val['name'] . '</strong></th>';
                }else{
                    $table_header .= '<th colspan="' .$row_val['colspan'] . '" scope="colgroup"><strong></strong></th>';
                }
                $index_col++;
            }

            $table_header .= '</tr>';
        }
    }
    if(count($data['sum'])) {
        $table_header .= '<tr>';
        if (count($data['column']) == 0) {
            foreach ($data['row'] as $rowkey => $blankrow_name) {
                $table_header .= '<td rowspan="' . (count($data['column']) + 1) . '"></td>';
            }
        }
        $key_count = count($data['column']);
        foreach ($table_data['columns'][$key_count] as $row_val) {
            foreach ($row_val['name'] as $row_name) {
                if(array_key_exists($row_name,Report::$measures[$cube])){
                    $table_header .= '<th colspan="' . 1 . '" scope="colgroup">' . (isset($setting_rename[$row_name])?$setting_rename[$row_name]:Report::$measures[$cube][$row_name])  . '</th>';
                }else{
                    $table_header .= '<th colspan="' . 1 . '" scope="colgroup">' . $row_name . '</th>';
                }
            }
        }
        $table_header .= '</tr>';
    }
    $table_header = $table_header_colgroup.$table_header;
    $table_td = '';
    //echo '<pre>';print_r($table_data);exit;
    foreach ($table_data['data'] as $datakey => $row) {
        $explode_row_array = explode('!!', $datakey);
        array_pop($explode_row_array);
        //$explode_row_array = array_filter(explode('!!', $datakey));
        $explode_row_count = count($explode_row_array);


        $key_index= 0;
        $table_single_row = $table_col_row = '';
        foreach ($row as $col_name => $col_val) {
            if (isset($table_data['table_footer_sum'][$col_name])) {
                $table_data['table_footer_sum'][$col_name] += $col_val;
            } else {
                $table_data['table_footer_sum'][$col_name] = $col_val;
            }

            $explode_array = explode('##', $col_name);
            array_pop($explode_array);
            //$explode_array = array_filter(explode('##', $col_name));
            $explode_count = count($explode_array);


            if ($row_count > 0 && $row_count >= $explode_row_count && $key_index == 0) {
                $table_col_row .= '<td rowspan="' . $table_data['row'][$explode_row_count-1][$datakey]['rowspan'] . '">' . $table_data['row'][$explode_row_count-1][$datakey]['name'] . '</td>';
            }
            if ($explode_row_count == $row_count && $explode_count >= $col_count) {
                $round_decimal = get_round_decimal_places();
                if(!is_apply_number_format($col_name)){
                    $round_decimal = 0;
                }
                if($key_index > 0 && $col_count == 0){
                    $table_single_row .= '<td class="col">' . (is_numeric($col_val) ?number_format($col_val,$round_decimal):$col_val) . '</td>';
                } else if($key_index == 0 && $col_count == 0 && $row_count == 0 ){
                    $table_single_row .= '<td class="col">' . (is_numeric($col_val) ?number_format($col_val,$round_decimal):$col_val) . '</td>';
                } else if($col_count > 0){
                    $table_single_row .= '<td class="col">' . (is_numeric($col_val) ?number_format($col_val,$round_decimal):$col_val) . '</td>';
                }

            }
            $key_index++;
        }
        $table_single_row = $table_col_row . $table_single_row;
        $table_td .= $table_single_row;
        if($explode_row_count == $row_count) {
            $table_row .='<tr>'.$table_td. '</tr>';
            $table_td = '';
        }

    }
    if(count($data['row'])) {
        $table_footer = '<tr>';
        foreach ($data['row'] as $rowkey => $blankrow_name) {
            $table_footer .= '<td rowspan="1" style="background-color: #66a9bd"></td>';
        }
        if(empty($data['column']) && count($data['column']) == 0){
            $row_col_count = count($data['row']);
        }else{
            $row_col_count = 0;
        }
        $footer_col_count = 0;
        foreach ($table_data['table_footer_sum'] as $foot_col_name => $foot_col_val) {
            if($footer_col_count >= $row_col_count) {
                $round_decimal = get_round_decimal_places();
                if(!is_apply_number_format($foot_col_name)){
                    $round_decimal = 0;
                }
                if(is_apply_total($foot_col_name)){
                    $table_footer .= '<td class="col" style="background-color: #91c5d4"><strong></strong></td>';
                }else{
                    $table_footer .= '<td class="col" style="background-color: #91c5d4"><strong>' . (is_numeric($foot_col_val) ?number_format($foot_col_val,$round_decimal):$foot_col_val) . '</strong></td>';
                }
            }
            $footer_col_count++;
        }
        $table_footer .= '</tr>';
    }
    return $table_header.$table_row.$table_footer;
}
function generateReportTable2($data,$response,$all_data_list)
{
    if(!empty($response['data'])) {
        $table = '<table class="table table-bordered">';

        $table_data =  table_array($data,$response,$all_data_list);

        $table .=  table_html($data,$table_data);

        $table .= '</table>';
    }else{
        $table = 'No Data Found Or Select at least one Measure';
    }

    return $table;
}

function str_lreplace($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);

    if($pos !== false)
    {
        $subject = substr_replace($subject, $replace, $pos, strlen($subject));
    }

    return $subject;
}

function custom_implode($array){
    return implode('!!',$array).'!!';
}

function get_ticket_status_date_array($result_data) {

    $sla_timer = true;
    $status				=	TicketsTable::getTicketStatusByID($result_data->Status);

    if (in_array($result_data->Status, array_keys( TicketsTable::getTicketStatusWithSLAOff() ) )) {  // SLATimer=off
        $sla_timer = false;
    }
    $due = $overdue = false ;
    if ($sla_timer) {

        $the_date = $result_data->DueDate;

        if(\Carbon\Carbon::createFromTimeStamp(strtotime($the_date))->isFuture()) {
            $due = true ;

            // round up minutes 1 hours 59 minutes to 2 hours
            if(\Carbon\Carbon::createFromTimeStamp(strtotime($the_date))->minute >= 1){
                $the_date = \Carbon\Carbon::createFromTimeStamp(strtotime($the_date))->addHour(1);
            }


        }else {
            $overdue = true;
        }


    } else {
        $the_date = TicketLog::where(['TicketID'=>$result_data->TicketID])->orderby("TicketLogID","DESC")->pluck("created_at");
    }


    $response = [ "the_date" => $the_date,
        "hunam_readable" =>  \Carbon\Carbon::createFromTimeStamp(strtotime($the_date))->diffForHumans(null, true),
        "sla_timer" => $sla_timer,
        "due" => $due,
        "overdue" => $overdue,
        "status" => $status,
    ];

    return $response;

}
function get_ticket_response_due_label($result_data) {

    $output = $overdue = "";

    if($result_data->Read==0) {
        return '<div class="label label-primary ticket-label ticket-new ">NEW</div>';

    } else if (TicketfieldsValues::isClosed($result_data->Status)) {
        return '<div class="label label-danger ticket-label ticket-closed ">'.strtoupper(TicketfieldsValues::$Status_Closed).'</div>';

    } else if (TicketfieldsValues::isResolved($result_data->Status)) {  //closed or resolved
        return '<div class="label label-danger ticket-label ticket-resolved ">'.strtoupper(TicketfieldsValues::$Status_Resolved).'</div>';
    }else {

        $TicketStatusOnHold = TicketsTable::getTicketStatusWithSLAOff();

        if (in_array($result_data->Status,array_keys($TicketStatusOnHold))) {  // SLATimer=off
            $output = '<div class="label label-warning ticket-label ticket-sla-off ">'.strtoupper($TicketStatusOnHold[$result_data->Status]).'</div>';
        }else {

            if($result_data->CustomerResponse > $result_data->AgentResponse ){
                $output = "<div class='label label-info ticket-label ticket-customer-replied '>CUSTOMER REPLIED</div>";

            }else if( $result_data->CustomerResponse < $result_data->AgentResponse ){
                $output = "<div class='label label-info ticket-label ticket-agent-replied'>AGENT REPLIED</div>";

            }
            if( \Carbon\Carbon::createFromTimeStamp(strtotime($result_data->DueDate))->isPast() ) {
                $overdue = ' <div class="label label-danger ticket-label ticket-overdue ">OVERDUE</div>';
            }
        }


    }

    return $output . $overdue;

}

function SowCustomerAgentRepliedDate($result_data)
{
    if(!empty($result_data->AgentRepliedDate) && !empty($result_data->CustomerRepliedDate))
    {
        if($result_data->AgentRepliedDate>$result_data->CustomerRepliedDate)
        {
            return ", Agent responded: ".\Carbon\Carbon::createFromTimeStamp(strtotime($result_data->AgentRepliedDate))->diffForHumans();
        }

        if($result_data->AgentRepliedDate<$result_data->CustomerRepliedDate)
        {
            return ", Customer responded: ".\Carbon\Carbon::createFromTimeStamp(strtotime($result_data->CustomerRepliedDate))->diffForHumans();
        }
    }
    elseif(empty($result_data->AgentRepliedDate) || empty($result_data->CustomerRepliedDate))
    {
        if(empty($result_data->CustomerRepliedDate) && !empty($result_data->AgentRepliedDate))
        {
            return ", Agent responded: ".\Carbon\Carbon::createFromTimeStamp(strtotime($result_data->AgentRepliedDate))->diffForHumans();
        }

        if(empty($result_data->AgentRepliedDate) && !empty($result_data->CustomerRepliedDate))
        {
            return ", Customer responded: ".\Carbon\Carbon::createFromTimeStamp(strtotime($result_data->CustomerRepliedDate))->diffForHumans();
        }

    }

}

function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;

}

function generate_manual_datatable_response($ColName){
    $response_data = array();
    Invoice::multiLang_init();
    switch ($ColName) {
        case 'InvoiceType':
            foreach (Invoice::$invoice_type as $row_key => $row_title) {
                if (!empty($row_key) && $row_key != 'All') {
                    $response_data[] = array($row_key, $row_title);
                }
            }
            break;
        case 'InvoiceStatus':
            $invoice_status = Invoice::get_invoice_status();
            foreach ($invoice_status as $row_key => $row_title) {
                if (!empty($row_key) && $row_key != 'All') {
                    $response_data[] = array($row_key, $row_title);
                }
            }
            break;
        case 'ProductType':
            $product_type = Product::$AllProductTypes;
            foreach ($product_type as $row_key => $row_title) {
                if (!empty($row_key) && $row_key != 'All') {
                    $response_data[] = array($row_key, $row_title);
                }
            }
            break;
        case 'PaymentMethod':
            $method = Payment::$method;
            foreach ($method as $row_key => $row_title) {
                if (!empty($row_key) && $row_key != 'All') {
                    $response_data[] = array($row_key, $row_title);
                }
            }
            break;
        case 'PaymentType':
            $action = Payment::$action;
            foreach ($action as $row_key => $row_title) {
                if (!empty($row_key) && $row_key != 'All') {
                    $response_data[] = array($row_key, $row_title);
                }
            }
            break;
        case 'Owner':
            $action = User::getOwnerUsersbyRole();
            foreach ($action as $row_key => $row_title) {
                if (!empty($row_key) && $row_key != 'All') {
                    $response_data[] = array($row_key, $row_title);
                }
            }
            break;
    }
    $manual_response = '{"sEcho":1,"iTotalRecords":'.count($response_data).',"iTotalDisplayRecords":'.count($response_data).',"aaData":'.json_encode($response_data).',"sColumns":["value","name"],"Total":{"totalcount":'.count($response_data).'}}';
    return $manual_response;

}

function report_col_name($column){
    $Accountschema = Report::$dimension['summary']['Customer'];
    if(in_array($column,$Accountschema) && $column != 'AccountID'){
        $column = 'tblAccount.'.$column;
    }
    return $column;
}

function report_join($data){
    $account_join = false;
    $Accountschema = Report::$dimension['summary']['Customer'];
    foreach ($data['column'] as $column) {
        if (in_array($column, array_keys($Accountschema)) && $column != 'AccountID') {
            $account_join = true;
        }
        if ($column == 'Owner') {
            $account_join = true;
        }
    }
    foreach ($data['row'] as $column) {
        if (in_array($column,array_keys($Accountschema)) && $column != 'AccountID' ) {
            $account_join = true;
        }
        if ($column == 'Owner' ) {
            $account_join = true;
        }
    }

    return $account_join;
}
function getInvoicePayments($CompanyID){
    if(is_authorize($CompanyID) || is_Stripe($CompanyID) || is_StripeACH($CompanyID) || is_paypal($CompanyID) || is_sagepay($CompanyID) || is_FideliPay($CompanyID) || is_pelecard($CompanyID)){
        return true;
    }
    return false;
}

function is_PayNowInvoice($CompanyID){
    if(is_authorize($CompanyID) || is_Stripe($CompanyID) || is_StripeACH($CompanyID) || is_FideliPay($CompanyID) || is_pelecard($CompanyID)){
        return true;
    }
    return false;
}
function fix_jobstatus_meassage($message){
    if(count($message)>100) {
        $message = array_slice($message, 0, 100);
        $message[] = '...';
    }
    return $message;
}

function is_reseller(){
    if(Session::get('reseller')==1){
        return true;
    }else{
        return false;
    }
}	

function is_apply_number_format($col_name){
    $flag = true;
    $col_array = array('TotalBilledDuration','BilledDuration','NoOfCalls','NoOfFailCalls','TotalDuration','TotalDuration2','UsageDetailID','billed_duration','duration','VendorCDRID');
    foreach($col_array as $col){
        if (strpos($col_name, $col) !== false) {
            $flag = false;
        }
    }
    return $flag;
}

function is_apply_total($col_name){
    $flag = false;
    $col_array = array('ACD','ASR','MarginPercentage');
    foreach($col_array as $col){
        if (strpos($col_name, $col) !== false) {
            $flag = true;
        }
    }
    return $flag;
}

function report_tables_dropbox($id=0,$CompanyID){
    $all_getRateTables = Report::getDropdownIDList($CompanyID);
    return Form::select('rategenerators', $all_getRateTables, $id ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));
}


function get_account_view_url($AccountID) {

    return URL::to('accounts/'.$AccountID.'/show');
}

function get_contact_view_url($ContactID) {

    return URL::to('contacts/'.$ContactID.'/show');
}

function get_user_edit_url($UserID) {

    return URL::to('users/edit/'.$UserID);
}

function check_apply_limit($setting_ag){
    $response_array = array(
        'applylimit' => false,
        'limit' => 0,
        'order' => 'ASC',
    );
    foreach((array)$setting_ag as $col => $column_action) {
        if ($column_action == 'top5' || $column_action == 'top10' || $column_action == 'bottom5' || $column_action == 'bottom10') {
            $response_array['applylimit'] = true;
        }
        if ($column_action == 'top5' || $column_action == 'bottom5'){
            $response_array['limit'] = 5 ;
        }else if($column_action == 'top10' || $column_action == 'bottom10'){
            $response_array['limit'] = 10 ;
        }
        if ($column_action == 'top5' || $column_action == 'top10'){
            $response_array['order'] = 'DESC';
        }
    }
    return $response_array;
}

function get_col_full_name($setting_ag,$Table,$colname){
    $aggregator = 'SUM';
    $aggregator2 = '';
    if(!empty($Table)) {
        $Table = $Table . ".";
    }
    if(!empty($setting_ag[$colname]) && $setting_ag[$colname] ==  'count_distinct' ){
        $aggregator2 = 'distinct';
        $aggregator = 'count';
    }else if(!empty($setting_ag[$colname])){
        $aggregator = $setting_ag[$colname];
    }

    return $aggregator."(".$aggregator2." ".$Table. $colname . ") as " . $colname;
}

function get_measure_name($colname,$Table){
    $measure_name = '';
    $col_TotalCharges = 'TotalCharges';
    $col_TotalCost = 'TotalCost';
    if (strpos($Table, 'Vendor') !== false) {
        $col_TotalCharges = 'TotalSales';
        $col_TotalCost = 'TotalCharges';
    }
    if($colname == 'Margin'){
        $measure_name = "COALESCE(SUM(".$Table.".".$col_TotalCharges."),0) - COALESCE(SUM(".$Table.".".$col_TotalCost."),0)";
    }else if($colname == 'MarginPercentage'){
        $measure_name = "(COALESCE(SUM(".$Table.".".$col_TotalCharges."),0) - COALESCE(SUM(".$Table.".".$col_TotalCost."),0)) / SUM(".$Table.".".$col_TotalCharges.")*100 ";
    }else if($colname == 'ACD'){
        $measure_name = "IF(SUM(".$Table.".NoOfCalls)>0,fnDurationmmss(COALESCE(SUM(".$Table.".TotalBilledDuration),0)/SUM(".$Table.".NoOfCalls)),0) ";
    }else if($colname == 'ASR'){
        $measure_name = "SUM(".$Table.".NoOfCalls)/(SUM(".$Table.".NoOfCalls)+SUM(".$Table.".NoOfFailCalls))*100 ";
    }else if($colname == 'BilledDuration'){
        $measure_name = "ROUND(COALESCE(SUM(".$Table.".TotalBilledDuration),0)/ 60,0) ";
    }else if($colname == 'TotalDuration2'){
        $measure_name = "ROUND(COALESCE(SUM(".$Table.".TotalDuration),0)/ 60,0)";
    }else if($colname == 'UsageDetailID'){
        $measure_name = "COUNT(".$Table.".UsageDetailID) ";
    }else if($colname == 'VendorCDRID'){
        $measure_name = "COUNT(".$Table.".VendorCDRID) ";
    }else if($colname == 'duration2'){
        $measure_name = "ROUND(COALESCE(SUM(".$Table.".duration),0)/ 60,0) ";
    }else if($colname == 'duration1'){
        $measure_name ="ROUND(COALESCE(SUM(".$Table.".billed_duration),0)/ 60,0) ";
    }else if($colname == 'avgrate'){
        $measure_name ="ROUND((COALESCE(SUM(".$Table.".cost),0)/COALESCE(SUM(".$Table.".billed_duration)))*60,6) ";
    }else if($colname == 'avgratesummary'){
        $measure_name ="ROUND((COALESCE(SUM(".$Table.".TotalCharges),0)/COALESCE(SUM(".$Table.".TotalBilledDuration)))*60,6) ";
    }else if($colname == 'avgratevendorcdr'){
        $measure_name ="ROUND((COALESCE(SUM(".$Table.".buying_cost),0)/COALESCE(SUM(".$Table.".billed_duration)))*60,6) ";
    }
    return $measure_name;
}

function set_cus_language($language){

    NeonCookie::deleteCookie("customer_language");
    NeonCookie::deleteCookie("customer_alignment");

    App::setLocale($language);
    NeonCookie::setCookie('customer_language',$language,365);

    if( DB::table('tblLanguage')->where(['ISOCode'=>$language, "is_rtl"=>"y"])->count()){
        NeonCookie::setCookie('customer_alignment',"right",365);
    }else{
        NeonCookie::setCookie('customer_alignment',"left",365);
    }
}

function ddl_language($id="",$name="",$defaultVal="",$class="",$valuetype="isocode",$selectOne=""){
    $return = '<select id="'.$id.'" name="'.$name.'" class="ddl_language '.$class.'">';
                if($selectOne!=""){
                    $return .= '<option data-flag="" value="" >Select</option>';
                }
                foreach(Translation::getLanguageDropdownWithFlagList() as $key=>$value){
                    $selected="";
                    if($valuetype=="isocode"){
                        $opt_value=$key;
                    }else if($valuetype=="id"){
                        $opt_value=$value["languageId"];
                    }
                    if($defaultVal==$opt_value){
                        $selected="selected";
                    }
                    $return .= '<option data-flag="'.$value["languageFlag"].'" value="'.$opt_value.'" '.$selected.' >'.$value["languageName"].'</option>';
                }
    $return .= '</select>';

    return $return;
}

function cus_lang($key=""){
    return trans('routes.'.strtoupper($key));
}

function getSql($model)
{
    $replace = function ($sql, $bindings)
    {
        $needle = '?';
        foreach ($bindings as $replace){
            $pos = strpos($sql, $needle);
            if ($pos !== false) {
                if (gettype($replace) === "string") {
                    $replace = ' "'.addslashes($replace).'" ';
                }
                $sql = substr_replace($sql, $replace, $pos, strlen($needle));
            }
        }
        return $sql;
    };
    $sql = $replace($model->toSql(), $model->getBindings());

    return $sql;
}

function js_labels(){
    $arrJsLabel= array();
    $arrJsLabel["MSG_DATA_NOT_AVAILABLE"]=cus_lang("MESSAGE_DATA_NOT_AVAILABLE");
    $arrJsLabel["TABLE_TOTAL"]=cus_lang("TABLE_TOTAL");
    $arrJsLabel["BUTTON_EXPORT_CSV_CAPTION"]=cus_lang("BUTTON_EXPORT_CSV_CAPTION");
    $arrJsLabel["BUTTON_EXPORT_EXCEL_CAPTION"]=cus_lang("BUTTON_EXPORT_EXCEL_CAPTION");

    $arrJsLabel["HTTP_STATUS_400_MSG"]=cus_lang("HTTP_STATUS_400_MSG");
    $arrJsLabel["HTTP_STATUS_403_MSG"]=cus_lang("HTTP_STATUS_403_MSG");
    $arrJsLabel["HTTP_STATUS_404_MSG"]=cus_lang("HTTP_STATUS_404_MSG");
    $arrJsLabel["HTTP_STATUS_408_MSG"]=cus_lang("HTTP_STATUS_408_MSG");
    $arrJsLabel["HTTP_STATUS_410_MSG"]=cus_lang("HTTP_STATUS_410_MSG");
    $arrJsLabel["HTTP_STATUS_500_MSG"]=cus_lang("HTTP_STATUS_500_MSG");
    $arrJsLabel["HTTP_STATUS_503_MSG"]=cus_lang("HTTP_STATUS_503_MSG");
    $arrJsLabel["HTTP_STATUS_504_MSG"]=cus_lang("HTTP_STATUS_504_MSG");


    $html="";
    foreach($arrJsLabel as $key=>$val){
        $html.=" var ".$key." = '".$val."';  \n\r";
    }
    return $html;
}
function cleanarray($data = [],$unset=[]){
    foreach($unset as $item){
        if(array_key_exists($item,$data)){
            unset($data[$item]);
        }
    }
    return $data;
}
function emailHeaderDecode($emailHtml) {
    if(is_string($emailHtml)){

        $matches = null;

        /* Repair instances where two encodings are together and separated by a space (strip the spaces) */
        $emailHtml = preg_replace('/(=\?[^ ?]+\?[BQbq]\?[^ ?]+\?=)\s+(=\?[^ ?]+\?[BQbq]\?[^ ?]+\?=)/', "$1$2", $emailHtml);

        /* Now see if any encodings exist and match them */
        if (!preg_match_all('/=\?([^ ?]+)\?([BQbq])\?([^ ?]+)\?=/', $emailHtml, $matches, PREG_SET_ORDER)) {
            return $emailHtml;
        }
        foreach ($matches as $header_match) {
            list($match, $charset, $encoding, $data) = $header_match;
            $encoding = strtoupper($encoding);
            switch ($encoding) {
                case 'B':
                    $data = base64_decode($data);
                    break;
                case 'Q':
                    $data = quoted_printable_decode(str_replace("_", " ", $data));
                    break;
            }
            // This part needs to handle every charset
            switch (strtoupper($charset)) {
                case "UTF-8":
                    break;
            }
            $emailHtml = str_replace($match, $data, $emailHtml);
        }
    }
    return $emailHtml;
}

function filterArrayRemoveNewLines($arr) { // remove new lines (/r/n) etc...
    //return preg_replace('/s+/', ' ', trim($arr));
    foreach ($arr as $key => $value) {
        $oldkey = $key;
        /*$key = str_replace("\r", '', $key);
        $key = str_replace("\n", '', $key);*/
        $key = preg_replace('/\s+/', ' ',$key);
        $arr[$key] = $value;
        if($key != $oldkey)
            unset($arr[$oldkey]);
    }
    return $arr;
}

function array_key_exists_wildcard ( $arr, $search ) {
    $search = str_replace( '*', '###star_needle###', $search );
    $search = preg_quote( $search, '/' ); # This is important!
    $search = str_replace( '###star_needle###', '.*?', $search );
    $search = '/^' . $search . '$/i';

    return preg_grep( $search, array_keys( $arr ) );
}

function searchArrayByProductID($id, $array) {
    foreach ($array as $key => $val) {
        if ($val['ProductID'] == $id) {
            return $key;
        }
    }
    return -1;
}

function getArrayByProductID($id,$array){
    foreach ($array as $key => $val) {
        if ($val['ProductID'] == $id) {
            return array('ProductID'=>$val['ProductID'],'Qty'=>$val['Qty']);
        }
    }
    return null;
}

function array_group_by($array, $key) {
    $return = array();
    foreach($array as $val) {
        $return[$val[$key]][] = $val;
    }
    return $return;
}

function sumofQtyIfSameProduct($inpuarr){
    $mainarr=array_group_by($inpuarr,'ProductID');
    $resarr=array();
    foreach($mainarr as $valmain){
        $cnt=1;
        $arr=array();
        foreach ($valmain as $key => $val) {
            if ($cnt == 1) {
                $arr += $val;
            } else {
                if ($val['Qty']) {
                    $arr['Qty'] = $arr['Qty'] + $val['Qty'];
                }
            }
            $cnt++;
        }
        array_push($resarr, $arr);
    }
    return $resarr;
}

function StockHistoryCalculations($data=array()){
    $Error=array();
    $TempStockData=array();
    $StockData=array();
    foreach($data as $stockarr){
        if($stockarr['CompanyID'] > 0 && $stockarr['ProductID'] > 0 && $stockarr['Qty'] >0){
            $getProduct = Product::where(['ProductID'=>$stockarr['ProductID'],'EnableStock'=>1])->first();

            if(!empty($getProduct)){
                $pname=$getProduct['Name'];
                $pcode=$getProduct['Code'];
                $getPrevProductHistory = StockHistory::where('CompanyID', $stockarr['CompanyID'])->where('ProductID', $stockarr['ProductID'])->orderby('StockHistoryID', 'desc')->first();
                if (!empty($getPrevProductHistory)) {
                    $pstock = intval($getPrevProductHistory['Stock']);
                    $remainStock = $pstock - $stockarr['Qty'];
                    $low_stock_level=intval($getProduct['LowStockLevel']);
                    if ($remainStock < 0 || $remainStock <= $low_stock_level) {
                        $Error[] = "Invoiced qty is more then available qty: Item {" . $pcode."}";
                    }
                    $invoicemsg="";
                    if($stockarr['InvoiceNumber']!=''){
                        $invoicemsg='{'.$stockarr['InvoiceNumber'].'}';
                    }
                    $reason="Invoice Generated ".$invoicemsg." - qty ".$stockarr['Qty'];

                    $TempStockData['CompanyID']=$stockarr['CompanyID'];
                    $TempStockData['ProductID']=$stockarr['ProductID'];
                    $TempStockData['InvoiceID']=$stockarr['InvoiceID'];
                    $TempStockData['InvoiceNumber']=$stockarr['InvoiceNumber'];
                    $TempStockData['Stock']=$remainStock;
                    $TempStockData['Quantity']=$stockarr['Qty'];
                    $TempStockData['Reason']=$reason;
                    $TempStockData['created_at']=date('Y-m-d H:i:s');
                    $TempStockData['created_by']=$stockarr['created_by'];

                    $StockData[]=$TempStockData;
                    //array_push($StockData,$TempStockData);

                }
            }
        }
    }

    if(!empty($StockData)){
        StockHistory::insert($StockData);
        foreach($StockData as $updatestock){
            $getProduct = Product::where('ProductID',$updatestock['ProductID'])->first();
            if(!empty($getProduct)){
                $getProduct->update(['Quantity'=>$updatestock['Stock']]);
            }
        }
    }
    return $Error;
}

function stockHistoryUpdateCalculations($data=array()){
    $Error=array();
    $StockData=array();
    foreach($data as $stockarr){
        $TempStockData=array();
        if($stockarr['CompanyID'] > 0 && $stockarr['ProductID'] > 0 && $stockarr['Qty'] > 0){
            $getStockHistory = StockHistory::where('ProductID', $stockarr['ProductID'])->orderby('StockHistoryID', 'desc')->first();
            if(!empty($getStockHistory)){
                $low_stock_alert=0;
                $hQuantity=intval($getStockHistory['Quantity']);
                $hStock=intval($getStockHistory['Stock']);
                $InvoiceNo=$getStockHistory['InvoiceNumber'];
                if($stockarr['Reason']=='delete_prodstock'){
                    //if Delete Stock
                    $getProduct = Product::where(['ProductID'=>$stockarr['ProductID'],'EnableStock'=>1])->first();
                    if (!empty($getProduct)) {
                        $pname = $getProduct['Name'];
                        $low_stock_level = intval($getProduct['LowStockLevel']);
                        $updatedStock = $hStock + $stockarr['oldQty'];
                        $reason=$pname.' Deleted. Item Quantity '.$stockarr['Qty'].' Revert back to Stock.';

                        $TempStockData['CompanyID']=$stockarr['CompanyID'];
                        $TempStockData['ProductID']=$stockarr['ProductID'];
                        $TempStockData['InvoiceID']=$stockarr['InvoiceID'];
                        $TempStockData['InvoiceNumber']=$stockarr['InvoiceNumber'];
                        $TempStockData['Stock']=$updatedStock;
                        $TempStockData['Quantity']=$stockarr['Qty'];
                        $TempStockData['Reason']=$reason;
                        $TempStockData['created_at']=date('Y-m-d H:i:s');
                        $TempStockData['created_by']=$stockarr['created_by'];

                        $StockData[]=$TempStockData;

                    }
                }else{
                    if($stockarr['oldQty']!=$stockarr['Qty']) {
                        $getProduct = Product::where(['ProductID'=>$stockarr['ProductID'],'EnableStock'=>1])->first();
                        if (!empty($getProduct)) {
                            $pstock = $getProduct['Quantity'];
                            $pname = $getProduct['Name'];
                            $pcode = $getProduct['Code'];
                            $low_stock_level = intval($getProduct['LowStockLevel']);
                            if ($stockarr['Qty'] > $stockarr['oldQty']) {
                                $diffQuantity = $stockarr['Qty'] - $stockarr['oldQty'];
                                $updatedStock = $hStock - $diffQuantity;
                            } else {
                                $diffQuantity = $stockarr['oldQty'] - $stockarr['Qty'];
                                $updatedStock = $hStock + $diffQuantity;
                            }
                            if ($updatedStock <= $low_stock_level) {
                                $Error[] = "Invoiced qty is more then available qty: Item {" . $pcode."}";
                            }

                            $invoicemsg="";
                            if($InvoiceNo!=''){
                                $invoicemsg='{'.$InvoiceNo.'}';
                            }
                            $reason="Invoice Updated ".$invoicemsg." - qty ".$stockarr['Qty'];

                            $TempStockData['CompanyID']=$stockarr['CompanyID'];
                            $TempStockData['ProductID']=$stockarr['ProductID'];
                            $TempStockData['InvoiceID']=$stockarr['InvoiceID'];
                            $TempStockData['InvoiceNumber']=$stockarr['InvoiceNumber'];
                            $TempStockData['Stock']=$updatedStock;
                            $TempStockData['Quantity']=$diffQuantity;
                            $TempStockData['Reason']=$reason;
                            $TempStockData['created_at']=date('Y-m-d H:i:s');
                            $TempStockData['created_by']=$stockarr['created_by'];

                            $StockData[]=$TempStockData;
                        }
                    }
                }
            }
        }
    }

    if(!empty($StockData)){
        StockHistory::insert($StockData);
        foreach($StockData as $updatestock){
            $getProduct = Product::where('ProductID',$updatestock['ProductID'])->first();
            if(!empty($getProduct)){
                $getProduct->update(['Quantity'=>$updatestock['Stock']]);
            }
        }
    }
    return $Error;
}

function getRandomNumber($digits=5){
    $rand_no= rand(pow(10, $digits-1), pow(10, $digits)-1);
    return $rand_no;
}

function getLanguageValue($val){
    $name=$val;
    $langs = Translation::get_language_labels('en');
    $json_file = json_decode($langs->Translation, true);
    $key=array_search($val,$json_file);
    if(!empty($key)){
        $name=cus_lang($key);
    }
    return $name;
}

function getCompanyDecimalPlaces($CompanyID=0, $value=""){
    $RoundChargesAmount = CompanySetting::getKeyVal('RoundChargesAmount', $CompanyID);
    $RoundChargesAmount=($RoundChargesAmount !='Invalid Key')?$RoundChargesAmount:2;

    if(!empty($value) && is_numeric($value)){
        $formatedValue=number_format($value, $RoundChargesAmount);
        if($formatedValue){
            return $formatedValue;
        }
        return $value;
    }else{
        return $RoundChargesAmount;
    }
}

function terminateMysqlProcess($pid){
    $cmd="KILL ".$pid;
    DB::connection('sqlsrv2')->select($cmd);

}

function getItemType($id){
    return ItemType::where('ItemTypeID',$id)->pluck('title');
}