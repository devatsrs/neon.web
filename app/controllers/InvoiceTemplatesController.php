<?php

class InvoiceTemplatesController extends \BaseController {

    public function ajax_datagrid($type) {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $invoiceCompanies = InvoiceTemplate::where("CompanyID", $CompanyID);
        if(isset($data['Export']) && $data['Export'] == 1) {
            $invoiceCompanies = $invoiceCompanies->select('Name','updated_at','ModifiedBy', 'InvoiceStartNumber','InvoiceNumberPrefix','InvoicePages','LastInvoiceNumber','ShowZeroCall','ShowPrevBal','DateFormat','ShowBillingPeriod','EstimateStartNumber','LastEstimateNumber','EstimateNumberPrefix','CreditNotesStartNumber','LastCreditNotesNumber','CreditNotesNumberPrefix','CDRType','GroupByService','IgnoreCallCharge','ShowPaymentWidgetInvoice','DefaultTemplate','FooterDisplayOnlyFirstPage','ShowTaxesOnSeparatePage','ShowTotalInMultiCurrency')->get();
            $invoiceCompanies = json_decode(json_encode($invoiceCompanies),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Invoice Template.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($invoiceCompanies);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Invoice Template.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($invoiceCompanies);
            }
        }
        $invoiceCompanies = $invoiceCompanies->select('Name','updated_at','ModifiedBy', 'InvoiceTemplateID','InvoiceStartNumber','CompanyLogoUrl','InvoiceNumberPrefix','InvoicePages','LastInvoiceNumber','ShowZeroCall','ShowPrevBal','DateFormat','Type','ShowBillingPeriod','EstimateStartNumber','LastEstimateNumber','EstimateNumberPrefix','CreditNotesStartNumber','LastCreditNotesNumber','CreditNotesNumberPrefix','CDRType','GroupByService','ServiceSplit','IgnoreCallCharge','ShowPaymentWidgetInvoice','DefaultTemplate','FooterDisplayOnlyFirstPage','ShowTaxesOnSeparatePage','ShowTotalInMultiCurrency');
        return Datatables::of($invoiceCompanies)->make();
    }

    public function index() {

        $countries = Country::getCountryDropdownList();
        return View::make('invoicetemplates.index', compact('countries'));

    }

    public function view($id) {

        $InvoiceTemplate = InvoiceTemplate::find($id);
        $CompanyID = $InvoiceTemplate->CompanyID;
        $logo = 'http://placehold.it/250x100';
        if(!empty($InvoiceTemplate->CompanyLogoAS3Key)){
            $logo = AmazonS3::unSignedImageUrl($InvoiceTemplate->CompanyLogoAS3Key,$CompanyID);
        }

        $data = Input::all();
        $type = $data['Type'];
        if($type==1){
            return View::make('invoicetemplates.showservice', compact('InvoiceTemplate','logo'));
        }elseif($type==2){
            return View::make('invoicetemplates.showitem', compact('InvoiceTemplate','logo'));
        }elseif($type==3){

            /* Default Value */
            $test_detail='[{"Title":"Prefix","ValuesID":"1","UsageName":"Prefix","Status":true,"FieldOrder":1},{"Title":"CLI","ValuesID":"2","UsageName":"CLI","Status":true,"FieldOrder":2},{"Title":"CLD","ValuesID":"3","UsageName":"CLD","Status":true,"FieldOrder":3},{"Title":"ConnectTime","ValuesID":"4","UsageName":"Connect Time","Status":true,"FieldOrder":4},{"Title":"DisconnectTime","ValuesID":"4","UsageName":"Disconnect Time","Status":true,"FieldOrder":5},{"Title":"BillDuration","ValuesID":"6","UsageName":"Duration","Status":true,"FieldOrder":6},{"Title":"ChargedAmount","ValuesID":"7","UsageName":"Cost","Status":true,"FieldOrder":7},{"Title":"BillDurationMinutes","ValuesID":"8","UsageName":"DurationMinutes","Status":false,"FieldOrder":8},{"Title":"Country","ValuesID":"9","UsageName":"Country","Status":false,"FieldOrder":9},{"Title":"CallType","ValuesID":"10","UsageName":"CallType","Status":false,"FieldOrder":10},{"Title":"Description","ValuesID":"11","UsageName":"Description","Status":false,"FieldOrder":11}]';

            $detail_values  =  json_decode($test_detail,true);

            $test_summary='[{"Title":"Trunk","ValuesID":"1","UsageName":"Trunk","Status":true,"FieldOrder":1},{"Title":"AreaPrefix","ValuesID":"2","UsageName":"Prefix","Status":true,"FieldOrder":2},{"Title":"Country","ValuesID":"3","UsageName":"Country","Status":true,"FieldOrder":3},{"Title":"Description","ValuesID":"4","UsageName":"Description","Status":true,"FieldOrder":4},{"Title":"NoOfCalls","ValuesID":"5","UsageName":"No of calls","Status":true,"FieldOrder":5},{"Title":"Duration","ValuesID":"6","UsageName":"Duration","Status":true,"FieldOrder":6},{"Title":"BillDuration","ValuesID":"7","UsageName":"Billed Duration","Status":true,"FieldOrder":7},{"Title":"AvgRatePerMin","ValuesID":"8","UsageName":"Avg Rate/Min","Status":true,"FieldOrder":8},{"Title":"ChargedAmount","ValuesID":"7","UsageName":"Cost","Status":true,"FieldOrder":9}]';
            $summary_values  =  json_decode($test_summary,true);

            /* Default Value */
            if(!empty($InvoiceTemplate->UsageColumn)){

                $usageColumns = json_decode($InvoiceTemplate->UsageColumn,true);
                if(!empty($usageColumns['Detail'])){
                    $default_column=array_column($detail_values, 'Title');
                    $db_column=array_column($usageColumns['Detail'], 'Title');
                    $diff_arrr=array_diff($default_column ,$db_column);

                    foreach($diff_arrr as $val){
                        $key = array_search($val, array_column($detail_values, 'Title'));
                        if(array_key_exists($key,$detail_values)){
                            $usageColumns['Detail'][]=$detail_values[$key];
                        }
                    }
                    $detail_values = $usageColumns['Detail'];
                }

                if(!empty($usageColumns['Summary'])){

                    $summary_values = $usageColumns['Summary'];
                }
            }

            return View::make('invoicetemplates.showusagecdr', compact('InvoiceTemplate','logo','detail_values','summary_values'));
        }elseif($type==4){
            /* Default Value */
            $test_detail='[{"Title":"Longest Calls","ValuesID":"1","UsageName":"Longest Calls","Status":true,"FieldOrder":1},{"Title":"Most Expensive Calls","ValuesID":"2","UsageName":"Most Expensive Calls","Status":true,"FieldOrder":2},{"Title":"Frequently Called Numbers","ValuesID":"3","UsageName":"Frequently Called Numbers","Status":true,"FieldOrder":3},{"Title":"Daily Summary","ValuesID":"4","UsageName":"Daily Summary","Status":true,"FieldOrder":4},{"Title":"Usage by Category","ValuesID":"4","UsageName":"Usage by Category","Status":true,"FieldOrder":5}]';
            $detail_values  =  json_decode($test_detail,true);
            /* Default Value */
            if(!empty($InvoiceTemplate->ManagementReport)){
                $usageColumns = json_decode($InvoiceTemplate->ManagementReport,true);
                if(!empty($usageColumns)){
                    $detail_values = $usageColumns;
                }
            }
            return View::make('invoicetemplates.report', compact('InvoiceTemplate','logo','detail_values'));
        }

        //return View::make('invoicetemplates.show', compact('InvoiceTemplate','logo'));

    }


    public function update($id)
    {
        if($id >0 ) {

            $InvoiceTemplates = InvoiceTemplate::find($id);
            $data = Input::all();
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;
            $data['ModifiedBy'] = User::get_user_full_name();
            if(!empty($data['EditPage']) && $data['EditPage']==1){
                $data['ShowZeroCall'] = isset($data['ShowZeroCall']) ? 1 : 0;
                $data['ShowPrevBal'] = isset($data['ShowPrevBal']) ? 1 : 0;
                $data['ShowBillingPeriod'] = isset($data['ShowBillingPeriod']) ? 1 : 0;
                $data['IgnoreCallCharge'] = isset($data['IgnoreCallCharge']) ? 1 : 0;
                $data['ShowPaymentWidgetInvoice'] = isset($data['ShowPaymentWidgetInvoice']) ? 1 : 0;
                $data['GroupByService'] = isset($data['GroupByService']) ? 1 : 0;
                $data['ServiceSplit'] = isset($data['ServiceSplit']) ? 1 : 0;
                $data['FooterDisplayOnlyFirstPage'] = isset($data['FooterDisplayOnlyFirstPage']) ? 1 : 0;
                $data['ShowTaxesOnSeparatePage'] = isset($data['ShowTaxesOnSeparatePage']) ? 1 : 0;
                $data['ShowTotalInMultiCurrency'] = isset($data['ShowTotalInMultiCurrency']) ? 1 : 0;
            }
            unset($data['EditPage']);
            unset($data['ServicePage']);
            if(!isset($data['DateFormat'])){
                $data['DateFormat'] = $InvoiceTemplates->DateFormat;
            }
            if(!isset($data['CDRType'])){
                $data['CDRType'] = $InvoiceTemplates->CDRType;
            }
            $rules = array(
                'CompanyID' => 'required',
                /*'Pages' => 'required',
                'Header' => 'required',
                'Footer' => 'required',
                'Footer' => 'required',
                'Terms' => 'required',*/
                'Name' => 'required|unique:tblInvoiceTemplate,Name,'.$id.',InvoiceTemplateID,CompanyID,'.$data['CompanyID'],
                'InvoiceStartNumber' => 'required',
                'DateFormat'=> 'required',
                'CDRType'=> 'required',
            );

            $messages = array(
                'CompanyID.required' =>'The companyid field is required',
                'Name.required' =>'name field is required',
                'InvoiceStartNumber.required' =>'invoice start number field is required',
                'CDRType.required' =>'cdr format field is required',
                'DateFormat.required' =>'date format field is required',
            );

            if(!isset($data['InvoiceStartNumber'])){
                //If saved from view.
                unset($rules['InvoiceStartNumber']);
            }
			if(!isset($data['EstimateStartNumber'])){
                //If saved from view.
                unset($rules['EstimateStartNumber']);
            }
            if(!isset($data['CreditNotesStartNumber'])){
                //If saved from view.
                unset($rules['CreditNotesStartNumber']);
            }
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($data, $rules,$messages);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            $file = Input::file('CompanyLogo');
            if (!empty($file))
            {
                $ext = $file->getClientOriginalExtension();
				
                if (!in_array(strtolower($ext) , array("jpg"))){
                    return Response::json(array("status" => "failed", "message" => "Please Upload only jpg file."));

                }
                $extension = '.'. Input::file('CompanyLogo')->getClientOriginalExtension();
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['INVOICE_COMPANY_LOGO']) ;
                $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;// storage_path(). '\\InvoiceLogos\\';

                //Create profile company_logo dir if not exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $fileName = strtolower(filter_var($data['Name'],FILTER_SANITIZE_URL)) .'_'. GUID::generate() .$extension;
                Input::file('CompanyLogo')->move($destinationPath, $fileName);
                if(!AmazonS3::upload($destinationPath.$fileName,$amazonPath)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $AmazonS3Key = $amazonPath . $fileName;
                $data['CompanyLogoAS3Key'] = $AmazonS3Key;
                $data['CompanyLogoUrl'] = AmazonS3::unSignedUrl($AmazonS3Key);
            }
            unset($data['CompanyLogo']);
            unset($data['Status_name']);

            if(isset($data['VisibleColumns'])) {
                $data['VisibleColumns'] = json_encode($data['VisibleColumns']);
            }
            if(isset($data['ItemDescription'])) {
                $data['ItemDescription'] = nl2br($data['ItemDescription']);
            }

            if ($InvoiceTemplates->update($data)) {
                return Response::json(array("status" => "success", "message" => "Invoice Template Successfully Updated",'LastID'=>$id));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Invoice Template."));
            }
        }
    }

    public function create()
    {
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $data['ModifiedBy'] = User::get_user_full_name();
        $data['ShowZeroCall'] = isset($data['ShowZeroCall']) ? 1 : 0;
        $data['ShowPrevBal'] = isset($data['ShowPrevBal']) ? 1 : 0;
        $data['ShowBillingPeriod'] = isset($data['ShowBillingPeriod']) ? 1 : 0;
        $data['IgnoreCallCharge'] = isset($data['IgnoreCallCharge']) ? 1 : 0;
        $data['ShowPaymentWidgetInvoice'] = isset($data['ShowPaymentWidgetInvoice']) ? 1 : 0;
        $data['GroupByService'] = isset($data['GroupByService']) ? 1 : 0;
        $data['ServiceSplit'] = isset($data['ServiceSplit']) ? 1 : 0;
        $data['FooterDisplayOnlyFirstPage'] = isset($data['FooterDisplayOnlyFirstPage']) ? 1 : 0;
        $data['ShowTaxesOnSeparatePage'] = isset($data['ShowTaxesOnSeparatePage']) ? 1 : 0;
        $data['ShowTotalInMultiCurrency'] = isset($data['ShowTotalInMultiCurrency']) ? 1 : 0;
        unset($data['InvoiceTemplateID']);
        unset($data['EditPage']);
        $rules = array(
            'CompanyID' => 'required',
            'Name' => 'required|unique:tblInvoiceTemplate,Name,NULL,InvoiceTemplateID,CompanyID,'.$data['CompanyID'],
            'InvoiceStartNumber' => 'required',
            'CDRType'=> 'required',
            'DateFormat'=> 'required',
        );

        $messages = array(
            'CompanyID.required' =>'The companyid field is required',
            'Name.required' =>'name field is required',
            'InvoiceStartNumber.required' =>'invoice start number field is required',
            'CDRType.required' =>'cdr format field is required',
            'DateFormat.required' =>'date format field is required',
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $validator = Validator::make($data, $rules,$messages);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $file = Input::file('CompanyLogo');
        if (!empty($file))
        {
            $ext = $file->getClientOriginalExtension();

            if (!in_array(strtolower($ext) , array("jpg"))){
                return Response::json(array("status" => "failed", "message" => "Please Upload only jpg file."));
            }
            $extension = '.'. Input::file('CompanyLogo')->getClientOriginalExtension();
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['INVOICE_COMPANY_LOGO']) ;
            $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;// storage_path(). '\\InvoiceLogos\\';

            //Create profile company_logo dir if not exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $fileName = strtolower(filter_var($data['Name'],FILTER_SANITIZE_URL)) .'_'. GUID::generate() .$extension;
            Input::file('CompanyLogo')->move($destinationPath, $fileName);
            if(!AmazonS3::upload($destinationPath.$fileName,$amazonPath)){
                return Response::json(array("status" => "failed", "message" => "Failed to upload."));
            }
            $AmazonS3Key = $amazonPath . $fileName;
            $data['CompanyLogoAS3Key'] = $AmazonS3Key;
            $data['CompanyLogoUrl'] = AmazonS3::unSignedUrl($AmazonS3Key);
            //@unlink($destinationPath.$fileName); // Remove temp local file.
        }
        unset($data['CompanyLogo']);
        unset($data['Status_name']);

        if(isset($data['VisibleColumns'])) {
            $data['VisibleColumns'] = json_encode($data['VisibleColumns']);
        } else {
            $data['VisibleColumns'] = '{"Description":"1","Usage":"1","Recurring":"1","Additional":"1"}';
        }

		$data['Header']		= InvoiceTemplate::$HeaderDefault;
		$data['FooterTerm'] = InvoiceTemplate::$TermsDefault;
		$data['Terms']  	= InvoiceTemplate::$FooterDefault;
        if ($invoiceCompany = InvoiceTemplate::create($data)) {
            if(isset($data['CompanyLogoAS3Key']) && !empty($data['CompanyLogoAS3Key'])){
                $data['CompanyLogoUrl'] = URL::to("/invoice_templates/".$invoiceCompany->InvoiceTemplateID) ."/get_logo";
            }

            return Response::json(array("status" => "success", "message" => "Invoice Template Successfully Created",'newcreated'=>$invoiceCompany,'LastID'=>$invoiceCompany->InvoiceTemplateID));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Invoice Template."));
        }
    }


    public function delete($id)
    {
        if( intval($id) > 0){

            if(!InvoiceTemplate::checkForeignKeyById($id)){
                try{
                    $InvoiceTemplate = InvoiceTemplate::find($id);
                    AmazonS3::delete($InvoiceTemplate->CompanyLogoAS3Key);
                    $result = $InvoiceTemplate->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "Invoice Template Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Invoice Template."));
                    }
                }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Invoice Template is in Use, You can not delete this Invoice Template."));
            }
        }
    }

    public function print_preview($id) {


        $InvoiceTemplate = InvoiceTemplate::find($id);
        $CompanyID = $InvoiceTemplate->CompanyID;
        $logo = 'http://placehold.it/250x100';
        if(!empty($InvoiceTemplate->CompanyLogoAS3Key)){
            $logo = AmazonS3::unSignedImageUrl($InvoiceTemplate->CompanyLogoAS3Key,$CompanyID);
        }

        $data = Input::all();
        $type = $data['Type'];
        if($type==1){
            return View::make('invoicetemplates.serviceinvoice_pdf', compact('InvoiceTemplate','logo'));
        }elseif($type==2){
            return View::make('invoicetemplates.iteminvoice_pdf', compact('InvoiceTemplate','logo'));
        }


        /*$pdf = PDF::loadView('invoicetemplates.invoice_pdf', compact('InvoiceTemplate'));
        return $pdf->download('rm_invoice_template.pdf');*/

    }

    public function pdf_download($id) {

        $pdf_path = $this->generate_pdf($id);
        return Response::download($pdf_path);

    }

    public function generate_pdf($id){
        $data = Input::all();
        $type = $data['Type'];

        if($id>0 && $type>0) {
            set_time_limit(600); // 10 min time limit.
            $InvoiceTemplate = InvoiceTemplate::find($id);

            if (empty($InvoiceTemplate->CompanyLogoUrl)) {
                $as3url =  URL::to('/').'/assets/images/250x100.png';
            } else {
                $as3url = (AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key));
            }
            
            if(!empty($InvoiceTemplate->CompanyLogoAS3Key)){
                $logo_path = CompanyConfiguration::get('UPLOAD_PATH') . '/logo/' . User::get_companyID();
                @mkdir($logo_path, 0777, true);
                RemoteSSH::run("chmod -R 777 " . $logo_path);
                $logo = $logo_path  . '/'  . basename($as3url);
                @file_put_contents($logo, file_get_contents($as3url));
            }else{
                $logo ='';
            }


			$print_type = 'Invoice Template';
            $file_name = 'Invoice--' . date('d-m-Y') . '.pdf';
            $htmlfile_name = 'Invoice--' . date('d-m-Y') . '.html';
            if($type==1){
                $body = View::make('invoicetemplates.servicepdf', compact('InvoiceTemplate', 'logo','print_type'))->render();
                $body = htmlspecialchars_decode($body);
            }
            if($type==2){
                $body = View::make('invoicetemplates.itempdf', compact('InvoiceTemplate', 'logo','print_type'))->render();
                $body = htmlspecialchars_decode($body);
            }
            if($type==3){
                $body = View::make('invoicetemplates.usagepdf', compact('InvoiceTemplate', 'logo','print_type'))->render();
                $body = htmlspecialchars_decode($body);
            }

            $footer = View::make('invoicetemplates.newpdffooter', compact('InvoiceTemplate','print_type'))->render();
            $footer = htmlspecialchars_decode($footer);

            $header = View::make('invoicetemplates.newpdfheader', compact('InvoiceTemplate','print_type'))->render();
            $header = htmlspecialchars_decode($header);

            $destination_dir = CompanyConfiguration::get('TEMP_PATH') . '/' . AmazonS3::generate_path( AmazonS3::$dir['INVOICE_UPLOAD'], $InvoiceTemplate->CompanyID);
            Log::info('invoicetemplate '.$destination_dir);
            if (!file_exists($destination_dir)) {
                mkdir($destination_dir, 0777, true);
            }
            RemoteSSH::run("chmod -R 777 " . $destination_dir);
            $file_name = \Nathanmac\GUID\Facades\GUID::generate() .'-'. $file_name;
            $htmlfile_name = \Nathanmac\GUID\Facades\GUID::generate() .'-'. $htmlfile_name;
            $local_file = $destination_dir .  $file_name;
            $local_htmlfile = $destination_dir .  $htmlfile_name;
            file_put_contents($local_htmlfile,$body);

            $footer_name = 'footer-'. \Nathanmac\GUID\Facades\GUID::generate() .'.html';
            $footer_html = $destination_dir.$footer_name;
            file_put_contents($footer_html,$footer);

            $header_name = 'header-'. \Nathanmac\GUID\Facades\GUID::generate() .'.html';
            $header_html = $destination_dir.$header_name;
            file_put_contents($header_html,$header);

            $output= "";
            if(getenv('APP_OS') == 'Linux'){
                exec (base_path(). '/wkhtmltox/bin/wkhtmltopdf --header-spacing 3 --footer-spacing 1 --header-html "'.$header_html.'" --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);
                Log::info(base_path(). '/wkhtmltox/bin/wkhtmltopdf --header-spacing 3 --footer-spacing 1 --header-html "'.$header_html.'" --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);

            }else{
                exec (base_path().'/wkhtmltopdf/bin/wkhtmltopdf.exe --header-spacing 3 --footer-spacing 1 --header-html "'.$header_html.'" --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);
                Log::info (base_path().'/wkhtmltopdf/bin/wkhtmltopdf.exe --header-spacing 3 --footer-spacing 1 --header-html "'.$header_html.'" --footer-html "'.$footer_html.'" "'.$local_htmlfile.'" "'.$local_file.'"',$output);
            }

            Log::info($output);

            @unlink($local_htmlfile);
            @unlink($footer_html);
            @unlink($header_html);
            $save_path = $destination_dir . $file_name;

            //PDF::loadHTML($body)->setPaper('a4')->setOrientation('potrait')->save($save_path);
            if(file_exists($logo)){
                @unlink($logo);
            }

            return $save_path;
        }
    }


    public function get_logo($id){
        $logo = InvoiceTemplate::where("InvoiceTemplateID",$id)->pluck('CompanyLogoAS3Key');
        $CompanyID = InvoiceTemplate::where("InvoiceTemplateID",$id)->pluck('CompanyID');
        if(!empty($logo)){
            $logo = AmazonS3::unSignedImageUrl($logo,$CompanyID);
        }
        return $logo;

    }

    function Save_Single_Field()
    {
        $postdata = Input::all();
        if (!empty($postdata['InvoiceTemplateID']) && $postdata['InvoiceTemplateID'] > 0 && isset($postdata['reportchoicesdata'])) {
            $InvoiceTemplates = InvoiceTemplate::find($postdata['InvoiceTemplateID']);
            if ($InvoiceTemplates->update(array('ManagementReport' => $postdata['reportchoicesdata']))) {
                return Response::json(array("status" => "success", "message" => "Invoice Template Usage Column Successfully Updated"));
            }
        } else if (!empty($postdata['InvoiceTemplateID']) && $postdata['InvoiceTemplateID'] > 0) {
            $AllData = array();
            $UsageSummary = json_decode($postdata['summarychoices'], true);
            $AllData['Summary'] = $UsageSummary;
            $UsageDetail = json_decode($postdata['detailchoices'],true);
            $AllData['Detail'] = $UsageDetail;
            $usagedata = json_encode($AllData);

            $InvoiceTemplateID = $postdata['InvoiceTemplateID'];

            $InvoiceTemplates = InvoiceTemplate::find($InvoiceTemplateID);


            if ($InvoiceTemplates->update(array('UsageColumn' => $usagedata))) {

                return Response::json(array("status" => "success", "message" => "Invoice Template Usage Column Successfully Updated"));

            }

        }

        return Response::json(array("status" => "failed", "message" => "Problem Updating Invoice Template."));
    }
}