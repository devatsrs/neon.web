<?php

class InvoiceTemplatesController extends \BaseController {

    public function ajax_datagrid($type) {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $invoiceCompanies = InvoiceTemplate::where("CompanyID", $CompanyID);
        if(isset($data['Export']) && $data['Export'] == 1) {
            $invoiceCompanies = $invoiceCompanies->select('Name','updated_at','ModifiedBy', 'InvoiceStartNumber','InvoiceNumberPrefix','InvoicePages','LastInvoiceNumber','ShowZeroCall','ShowPrevBal','DateFormat','ShowBillingPeriod','EstimateStartNumber','LastEstimateNumber','EstimateNumberPrefix')->get();
            $invoiceCompanies = json_decode(json_encode($invoiceCompanies),true);
            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Invoice Template.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($invoiceCompanies);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Invoice Template.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($invoiceCompanies);
            }
        }
        $invoiceCompanies = $invoiceCompanies->select('Name','updated_at','ModifiedBy', 'InvoiceTemplateID','InvoiceStartNumber','CompanyLogoUrl','InvoiceNumberPrefix','InvoicePages','LastInvoiceNumber','ShowZeroCall','ShowPrevBal','DateFormat','Type','ShowBillingPeriod','EstimateStartNumber','LastEstimateNumber','EstimateNumberPrefix');
        return Datatables::of($invoiceCompanies)->make();
    }

    public function index() {

        $countries = Country::getCountryDropdownList();
        return View::make('invoicetemplates.index', compact('countries'));

    }

    public function view($id) {

        $InvoiceTemplate = InvoiceTemplate::find($id);
        $logo = 'http://placehold.it/250x100';
        if(!empty($InvoiceTemplate->CompanyLogoAS3Key)){
            $logo = AmazonS3::unSignedImageUrl($InvoiceTemplate->CompanyLogoAS3Key);    
        }

        return View::make('invoicetemplates.show', compact('InvoiceTemplate','logo'));

    }


    public function update($id)
    {
        if($id >0 ) {

            $InvoiceTemplates = InvoiceTemplate::find($id);
            $data = Input::all();
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;
            $data['ModifiedBy'] = User::get_user_full_name();
            $data['ShowZeroCall'] = isset($data['ShowZeroCall']) ? 1 : 0;
            $data['ShowPrevBal'] = isset($data['ShowPrevBal']) ? 1 : 0;
            $data['ShowBillingPeriod'] = isset($data['ShowBillingPeriod']) ? 1 : 0;
            if(!isset($data['DateFormat'])){
                $data['DateFormat'] = $InvoiceTemplates->DateFormat;
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
            );

            if(!isset($data['InvoiceStartNumber'])){
                //If saved from view.
                unset($rules['InvoiceStartNumber']);
            }
			if(!isset($data['EstimateStartNumber'])){
                //If saved from view.
                unset($rules['EstimateStartNumber']);
            }
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($data, $rules);
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
                $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;// storage_path(). '\\InvoiceLogos\\';

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
        unset($data['InvoiceTemplateID']);
        $rules = array(
            'CompanyID' => 'required',
            'Name' => 'required|unique:tblInvoiceTemplate,Name,NULL,InvoiceTemplateID,CompanyID,'.$data['CompanyID'],
            'InvoiceStartNumber' => 'required',
            'DateFormat'=> 'required',
        );
        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $validator = Validator::make($data, $rules);
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
            $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;// storage_path(). '\\InvoiceLogos\\';

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
		$data['Header']		= InvoiceTemplate::$HeaderDefault;
		$data['FooterTerm'] = InvoiceTemplate::$TermsDefault;
		$data['Terms']  	= InvoiceTemplate::$FooterDefault;
        if ($invoiceCompany = InvoiceTemplate::create($data)) {
            if(isset($data['CompanyLogoAS3Key']) && !empty($data['CompanyLogoAS3Key'])){
                $data['CompanyLogoUrl'] = URL::to("/invoice_templates/".$invoiceCompany->InvoiceTemplateID) ."/get_logo";
            }

            return Response::json(array("status" => "success", "message" => "Invoice Template Successfully Created",'LastID'=>$invoiceCompany->InvoiceTemplateID));
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
                return Response::json(array("status" => "failed", "message" => "Invoice Template is in Use, You cant delete this Invoice Template."));
            }
        }
    }

    public function print_preview($id) {

        \Debugbar::disable();
        $InvoiceTemplate = InvoiceTemplate::find($id);
        $logo = 'http://placehold.it/250x100';
        if(!empty($InvoiceTemplate->CompanyLogoAS3Key)){
            $logo = AmazonS3::unSignedImageUrl($InvoiceTemplate->CompanyLogoAS3Key);    
        }

        return View::make('invoicetemplates.invoice_pdf', compact('InvoiceTemplate','logo'));

        /*$pdf = PDF::loadView('invoicetemplates.invoice_pdf', compact('InvoiceTemplate'));
        return $pdf->download('rm_invoice_template.pdf');*/

    }

    public function pdf_download($id) {

        \Debugbar::disable();
        $pdf_path = $this->generate_pdf($id);
        return Response::download($pdf_path);

    }

    public function generate_pdf($id){
        if($id>0) {
            set_time_limit(600); // 10 min time limit.
            $InvoiceTemplate = InvoiceTemplate::find($id);
            if (empty($InvoiceTemplate->CompanyLogoUrl)) {
                $as3url =  URL::to('/').'/assets/images/250x100.png';
            } else {
                $as3url = (AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key));
            }
            
            if(!empty($InvoiceTemplate->CompanyLogoAS3Key)){
                $logo_path = getenv('UPLOAD_PATH') . '/logo/' . User::get_companyID();
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
            $body = View::make('invoicetemplates.pdf', compact('InvoiceTemplate', 'logo','print_type'))->render();
            $body = htmlspecialchars_decode($body);

            $footer = View::make('invoicetemplates.pdffooter', compact('InvoiceTemplate','print_type'))->render();
            $footer = htmlspecialchars_decode($footer);

            $header = View::make('invoicetemplates.pdfheader', compact('InvoiceTemplate','print_type'))->render();
            $header = htmlspecialchars_decode($header);

            $destination_dir = getenv('TEMP_PATH') . '/' . AmazonS3::generate_path( AmazonS3::$dir['INVOICE_UPLOAD'], $InvoiceTemplate->CompanyID);
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
        if(!empty($logo)){
            $logo = AmazonS3::unSignedImageUrl($logo);
        }
        return $logo;

    }

}