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
    $isArray = false;
    if(is_array($response)){
        $isArray = true;
    }

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
            if($datareturn) {
                $result = $isArray ? $response['data'] : $response->data;
                if ($isDataEncode) {
                    $result = json_encode($result);
                }
                return $result;
            }
        }
    }
    $parse_repose = array("status" => $status, "message" => $message);
    if(($isArray && isset($response['redirect'])) || (!$isArray && isset($response->redirect))){
        $parse_repose['redirect'] =  $isArray ? $response['redirect'] : $response->redirect;
    }


    if($isBrowser){
        if(($isArray && isset($response['Code']) && $response['Code'] ==401) || (!$isArray && isset($response->Code) && $response->Code == 401)){

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
function customer_dropbox($id=0,$data=array()){
    $all_customers = account::getAccountIDList($data);
    return Form::select('customers', $all_customers, $id ,array("id"=>"drp_customers_jump" ,"class"=>"selectboxit1 form-control1"));
}

function opportunites_dropbox($id=0,$data=array()){
    $all_opportunites = CRMBoard::getBoards(CRMBoard::OpportunityBoard,-1);
    return Form::select('crmboard', $all_opportunites, $id ,array("id"=>"drp_customers_jump" ,"class"=>"selectboxit1 form-control1"));
}


function sendMail($view,$data){
    
	if(empty($data['companyID']))
    {
        $companyID = User::get_companyID();
    }else{
        $companyID = $data['companyID'];
    }
	$data   =   $data;
	$body 	=   View::make($view,compact('data'))->render(); 
	
	if(SiteIntegration::CheckCategoryConfiguration(false,SiteIntegration::$EmailSlug)){
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

function is_amazon(){
	
  /*  $AMAZONS3_KEY  = getenv("AMAZONS3_KEY");
    $AMAZONS3_SECRET = getenv("AMAZONS3_SECRET");
    $AWS_REGION = getenv("AWS_REGION");*/

	$AmazonData			=	SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$AmazoneSlug);
    $AMAZONS3_KEY  		= 	isset($AmazonData->AmazonKey)?$AmazonData->AmazonKey:'';
    $AMAZONS3_SECRET 	= 	isset($AmazonData->AmazonSecret)?$AmazonData->AmazonSecret:'';
    $AWS_REGION 		= 	isset($AmazonData->AmazonAwsRegion)?$AmazonData->AmazonAwsRegion:'';

    if(empty($AMAZONS3_KEY) || empty($AMAZONS3_SECRET) || empty($AWS_REGION) ){
        return false;
    }
    return true;
}

function is_authorize(){
	return				SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$AuthorizeSlug);
	
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

function is_paypal(){

    $paypal = new PaypalIpn();
    if($paypal->status){
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


function getFileContent($file_name,$data){
    $columns = [];
    $grid = [];
    $flag = 0;

    $NeonExcel = new NeonExcelIO($file_name, $data);
    $results = $NeonExcel->read(10);

    /*
    if (!empty($data['Delimiter'])) {
        Config::set('excel::csv.delimiter', $data['Delimiter']);
    }
    if (!empty($data['Enclosure'])) {
        Config::set('excel::csv.enclosure', $data['Enclosure']);
    }
    if (!empty($data['Escape'])) {
        Config::set('excel::csv.line_ending', $data['Escape']);
    }
    if(!empty($data['Firstrow'])){
        $data['option']['Firstrow'] = $data['Firstrow'];
    }

    if (!empty($data['option']['Firstrow'])) {
        if ($data['option']['Firstrow'] == 'data') {
            $flag = 1;
        }
    }
    $isExcel = in_array(pathinfo($file_name, PATHINFO_EXTENSION),['xls','xlsx'])?true:false;
    $results = Excel::selectSheetsByIndex(0)->load($file_name, function ($reader) use ($flag,$isExcel) {
        if ($flag == 1) {
            $reader->noHeading();
        }
    })->take(10)->toArray();*/
    $counter = 1;
    //$results[0] = array_filter($results[0]);
    foreach ($results[0] as $index => $value) {
        if (isset($data['Firstrow']) && $data['Firstrow'] == 'data') {
            $columns[$counter] = 'Col' . $counter;
        } else {
            $columns[$index] = $index;
        }
        $counter++;
    }
    foreach ($results as $outindex => $datarow) {
        //$datarow = array_filter($datarow);
        //$results[$outindex] =  array_filter($datarow);
        foreach ($datarow as $index => $singlerow) {
            $results[$outindex][$index] = $singlerow;
            if (strpos(strtolower($index), 'date') !== false) {
                $singlerow = str_replace('/', '-', $singlerow);
                $results[$outindex][$index] = $singlerow;
            }
        }
    }
    try {
    } catch (\Exception $ex) {
        Log::error($ex);
    }

    $grid['columns'] = $columns;
    $grid['rows'] = $results;
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
            $upload_path = Config::get('app.upload_path');
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
                $dir = getenv('UPLOAD_PATH') . '/' . $amazonPath;
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
            if ($data['email_template'] > 0) {
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

    if(is_array($data['EmailTo'])){
        $data['EmailTo'] = implode(',',$data['EmailTo']);
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

    $logData = ['EmailFrom'=>User::get_user_email(),
        'EmailTo'=>$data['EmailTo'],
        'Subject'=>$data['Subject'],
        'Message'=>$data['Message'],
        'AccountID'=>$data['AccountID'],
        'CompanyID'=>User::get_companyID(),
        'UserID'=>User::get_userID(),
        'CreatedBy'=>User::get_user_full_name(),
        'Cc'=>implode(",",$data['cc']),
        'Bcc'=>implode(",",$data['bcc']),
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

    if(ctype_digit($date) && strlen($date)==5){
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
                    if ($dateformat == 'd-m-Y' && strpos($date, '/') !== false) {
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
    return $datetime;
}

function SortBillingType(){
    ksort(Company::$BillingCycleType);
    return Company::$BillingCycleType;
}
function parse_reponse($response){
    $response = json_decode($response);
    if($response->status_code == 200){
        return $response;
    }elseif($response->status_code == 401 && $response->message == 'Token has expired'){
        Session::flush();
        Auth::logout();
        return Redirect::to('/login')->with('message', 'Your are now logged out!');
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
        $cache['LoginMessage']		=	'Dear user, log in to access RM!';
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
        $response['CallCountHtml'] = '<h4>No Data</h4>';
        $response['CallCount'] = '';
        $response['CallCountVal'] = '';
    }else{
        $response['CallCount'] = implode(',',$alldata['call_count']);
        $response['CallCountVal'] = implode(',',$alldata['call_count_val']);
        $response['CallCountHtml'] =  $alldata['call_count_html'];
    }
    if(empty($alldata['call_cost'])) {
        $response['CallCostHtml'] = '<h4>No Data</h4>';
        $response['CallCost'] = '';
        $response['CallCostVal'] = '';
    }else{
        $response['CallCost'] = implode(',',$alldata['call_cost']);
        $response['CallCostVal'] = implode(',',$alldata['call_cost_val']);
        $response['CallCostHtml'] = $alldata['call_cost_html'];
    }
    if(empty($alldata['call_minutes'])) {
        $response['CallMinutesHtml'] = '<h4>No Data</h4>';
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
    $array_admin	  =	   array("Users","Role","Themes","AccountApproval","VendorFileUploadTemplate","EmailTemplate","Notification","ServerInfo","Retention");
    $array_summary    =    array("Summary");
    $array_rates	  =	   array("RateTables","LCR","RateGenerators","VendorProfiling");
	$array_tickets	  =	   array("Tickets");
    $array_template   =    array("");
    $array_dashboard  =    array("Dashboard");
	$array_crm 		  =    array("OpportunityBoard","Task","Dashboard");
    $array_billing    =    array("Dashboard",'Estimates','Invoices','Dispute','BillingSubscription','Payments','AccountStatement','Products','InvoiceTemplates','TaxRates','CDR',"Discount","BillingClass");
    $customer_billing    =    array('InvoicesCustomer','PaymentsCustomer','AccountStatementCustomer','PaymentProfileCustomer','CDRCustomer',"DashboardCustomer");

    if(count($path_array)>0)
    {
         $controller = $path_array[0];
	   	if(in_array($controller,$array_billing) && $parent_link =='Billing')
        {
			if(Request::segment(1)!='monitor' && $path_array[1]!='@CrmDashboard'){
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

        if(in_array($controller,$array_crm) && $parent_link =='Crm')
        {
			if($path_array[1]!='@billingdashboard'){
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
    $max_file_env   = getenv('MAX_UPLOAD_FILE_SIZE');
    $max_file_size   = !empty($max_file_env)?getenv('MAX_UPLOAD_FILE_SIZE'):ini_get('post_max_size');
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

    $RoundChargesAmount = 2;
    if($AccountID>0){
        $RoundChargesAmount = AccountBilling::getRoundChargesAmount($AccountID);
    }
    if ( empty($RoundChargesAmount) ) {
        $RoundChargesAmount = 2;
    }
    return $RoundChargesAmount;
}


function ValidateSmtp($SMTPServer,$Port,$EmailFrom,$IsSSL,$SMTPUsername,$SMTPPassword,$address,$ToEmail){
    $mail 				= 	new PHPMailer;
    $mail->isSMTP();
    $mail->Host 		= 	$SMTPServer;
    $mail->SMTPAuth 	= 	true;
    $mail->Username 	= 	$SMTPUsername;
    $mail->Password 	= 	$SMTPPassword;
    $mail->SMTPSecure	= 	$IsSSL==1?'SSL':'TLS';
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
    if(($isArray && isset($response['Code']) && $response['Code'] ==401) || (!$isArray && isset($response->Code) && $response->Code == 401)) {
        return Redirect::to('/logout');
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

	 if (Session::has("api_response_extensions")){
		  $response_extensions['allowed_extensions'] =  Session::get('api_response_extensions');
		 return $response_extensions;
	 } 	 
	 $response     			=  NeonAPI::request('get_allowed_extensions',[],false);
	 $response_extensions 	=  [];
	
	if($response->status=='failed'){
		if($ajax==true){
			return $response;
		}else{
			
			if(isset($response->Code) && ($response->Code==400 || $response->Code==401)){
					return Redirect::to('/logout'); 	
			}		
			if(isset($response->error) && $response->error=='token_expired'){ return Redirect::to('/login');}	
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

function is_Stripe(){
    return	SiteIntegration::CheckIntegrationConfiguration(false,SiteIntegration::$StripeSlug);
}