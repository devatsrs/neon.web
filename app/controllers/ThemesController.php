<?php

class ThemesController extends \BaseController {
	

    public function ajax_datagrid()
	{
        $data 						 = 		Input::all();
        $data['iDisplayStart'] 		+=		1;
        $companyID 					 =  	User::get_companyID();
        $columns 					 =  	['ThemeID','DomainUrl','Title','Favicon','Logo','ThemeStatus'];   
        $sort_column 				 =  	$columns[$data['iSortCol_0']];		
		$Themes 					 = 		Themes::where(["CompanyID" => $companyID])->select($columns);
		
		 if(trim($data['searchText']) != '')
		 {			 
			 $Themes->where(function($Themes){
        	$Themes->where('DomainUrl', 'like', '%' . $_GET['searchText'] . '%')
              ->orWhere('Title', 'like', '%' . $_GET['searchText'] . '%')
			  ->orWhere('FooterText', 'like', '%' . $_GET['searchText'] . '%')
			  ->orWhere('FooterUrl', 'like', '%' . $_GET['searchText'] . '%')
			  ->orWhere('LoginMessage', 'like', '%' . $_GET['searchText'] . '%');			  
   			 });			
        }
		
		if(trim($data['ThemeStatus']) != '')
		{
            $Themes->where('ThemeStatus', 'like','%'.trim($data['ThemeStatus']).'%');
        }
		
		 //return Datatables::of($Themes)->make();
		 
		   return Datatables::of($Themes)
            ->edit_column('Logo',function($row){
                $path = AmazonS3::unSignedUrl($row->Logo);
                if (!is_numeric(strpos($path, "https://"))) {
                    $path = str_replace('/', '\\', $path);
                    if (copy($path, './uploads/' . basename($path))) {
                        $path = URL::to('/') . '/uploads/' . basename($path);
                    }
                }

                return $path;
            })->edit_column('Favicon',function($row){
                $path = AmazonS3::unSignedUrl($row->Favicon);
                if (!is_numeric(strpos($path, "https://"))) {
                    $path = str_replace('/', '\\', $path);
                    if (copy($path, './uploads/' . basename($path))) {
                        $path = URL::to('/') . '/uploads/' . basename($path);
                    }
                }

                return $path;
            })->make();
		
    }
	
    /**
     * Display a listing of the resource.
     * GET /estimates
     *
     * @return Response
     */
    public function index()
    {
        $companyID 				= 		User::get_companyID();
        $data 					= 		Input::all();
        $Themes 				= 		DB::table('tblCompanyThemes')->where(["CompanyID" => $companyID])->orderBy('ThemeID', 'desc')->get();
		$themes_status_json 	= 		json_encode(Themes::get_theme_status());
        return View::make('themes.index',compact('Themes','themes_status_json'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /invoices/create
     *
     * @return Response
     */
    public function create()
    {
		$theme_status_json 		= 	Themes::get_theme_status();		
        return View::make('themes.create',compact('theme_status_json'));
    }

    /**
     *
     * */
    public function edit($id)
	{
        if($id > 0)
		{
	        $theme_status_json 		= 	 Themes::get_theme_status();
            $Theme 					= 	 Themes::find($id);
            return View::make('themes.edit', compact('Theme','theme_status_json'));
        }
    }

    /**
     * Store Invoice
     */
    public function store()
	{
        $data 					= 		Input::all();		
		$companyID 				= 		User::get_companyID();
		$company_name 			= 		Account::getCompanyNameByID($companyID);
		
        if($data)
		{
            $companyID 						=   User::get_companyID();
            $CreatedBy 						= 	User::get_user_full_name();           
            $themeData 						= 	array();
            $themeData["CompanyID"] 		= 	$companyID;
            $themeData["DomainUrl"] 		= 	$data["DomainUrl"];
            $themeData["Title"] 			= 	$data["Title"];
            $themeData["FooterText"] 		= 	$data["FooterText"];
            $themeData["FooterUrl"] 		= 	$data["FooterUrl"];			
			$themeData["LoginMessage"] 		= 	$data["LoginMessage"];
            $themeData["CustomCss"] 		= 	$data["CustomCss"];			
            $themeData["ThemeStatus"] 		= 	$data["ThemeStatus"];
            $themeData["CreatedBy"] 		= 	$CreatedBy;
			$themeData["created_at"] 		= 	date('Y-m-d H:i:s');

            ///////////
            $rules = array(
                'CompanyID' => 'required',
                'DomainUrl' => 'required|unique:tblCompanyThemes,DomainUrl|url', 
				'FooterUrl' => 'url',               
            );
			
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv');

            $validator = Validator::make($themeData, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails())
			{
                return json_validator_response($validator);
            }
			
			if (Input::hasFile('Logo'))
			{
				$upload_path 	  = 	getenv('TEMP_PATH');
				$Attachment		  = 	Input::file('Logo');
				$ext 			  = 	$Attachment->getClientOriginalExtension();			
				
				if (in_array($ext, array("jpg")))
				{
					$amazonPath		 	=	AmazonS3::generate_upload_path(AmazonS3::$dir['THEMES_IMAGES']);
					$destinationPath 	= 	getenv("UPLOAD_PATH") . '/' . $amazonPath;					
					$filename 		 	= 	rename_upload_file($destinationPath,$Attachment->getClientOriginalName());
					$fullPath 		 	= 	$destinationPath .$filename;										
        	        $Attachment->move($destinationPath, $filename);
					
					 if(!AmazonS3::upload($destinationPath.$filename,$amazonPath))
					 {
                    	return Response::json(array("status" => "failed", "message" => "Failed to upload."));
              		 }
					 
					$data['logo_path']   = $fullPath; 
				
					$data['logo']	 	 = $amazonPath . $filename; 
					
					list($width_log,$height_log) =  getimagesize($data['logo_path']);
					
					if($width_log >200 || $height_log>58)
					{
						unlink($data['logo_path']);
						return Response::json(array("status" => "failed", "message" => "Logo image max size is 200 x 58"));			
					}
				}
				else
				{
					return Response::json(array("status" => "failed", "message" => "Invalid Logo file extension."));				
				}	
			}			
			else
			{
				return Response::json(array("status" => "failed", "message" => "Please Select Logo."));				
			}
			
			/*Favicon upload start*/						
			if (Input::hasFile('Favicon'))
			{
				
				//////////
				
				///////////
				$upload_path 	  = 	getenv('TEMP_PATH');
				$Attachment		  = 	Input::file('Favicon');
				$ext 			  = 	$Attachment->getClientOriginalExtension();			
				$destinationPath  = 	$upload_path . sprintf("\\%s\\", $company_name);
				if (in_array($ext, array("ico")))
				{
					
					////////
					
					$amazonPath		 	=	AmazonS3::generate_upload_path(AmazonS3::$dir['THEMES_IMAGES']);
					$destinationPath 	= 	getenv("UPLOAD_PATH") . '/' . $amazonPath;					
					$filename 		 	= 	rename_upload_file($destinationPath,$Attachment->getClientOriginalName());
					$fullPath 		 	= 	$destinationPath .$filename;										
        	        $Attachment->move($destinationPath, $filename);
					
					 if(!AmazonS3::upload($destinationPath.$filename,$amazonPath))
					 {
                    	return Response::json(array("status" => "failed", "message" => "Failed to upload."));
              		 }
					 
					$data['Favicon_path']   = $fullPath; 
				
					$data['Favicon']	 	 = $amazonPath . $filename; 
			
					list($width_log,$height_log) =  getimagesize($data['Favicon_path']);
					
					if($width_log >32 || $height_log>32)
					{
						unlink($data['Favicon_path']);
						return Response::json(array("status" => "failed", "message" => "Favicon image max size is 32 x 32"));			
					}
				}
				else
				{
					return Response::json(array("status" => "failed", "message" => "Invalid Favicon file extension."));				
				}	
			}			
			else
			{
				return Response::json(array("status" => "failed", "message" => "Please Select Favicon."));				
			}
			/*Favicon upload end*/	
	
            $themeData['Logo'] 		= 	$data['logo'];
			$themeData['Favicon'] 	= 	$data['Favicon'];
			
			try
			{
                if ($theme = Themes::create($themeData))
				{
					   return  Response::json(array("status" => "success", "message" => "Theme Successfully Created",'LastID'=>$theme->ThemeID,'redirect' => URL::to('/themes')));
				}
				else
				{
                    return Response::json(array("status" => "failed", "message" => "Problem Creating Theme."));
                }				
            }
			catch (Exception $e)
			{
                return Response::json(array("status" => "failed", "message" => "Problem Creating Theme. \n" . $e->getMessage()));
            }
        }
    }

    /**
     * Store Estimate
     */
    public function update($id)
	{
        $data 					= 		Input::all();
		$companyID 				= 		User::get_companyID();
		$company_name 			= 		Account::getCompanyNameByID($companyID);
		
        if(!empty($data) && $id > 0)
		{
            $Themes 						= 	Themes::find($id);
            $CreatedBy 						= 	User::get_user_full_name();
            $companyID 						=   User::get_companyID();
            $themeData 						= 	array();
            $themeData["DomainUrl"] 		= 	$data["DomainUrl"];
            $themeData["Title"] 			= 	$data["Title"];
            $themeData["FooterText"] 		= 	$data["FooterText"];
            $themeData["FooterUrl"] 		= 	$data["FooterUrl"];			
			$themeData["LoginMessage"] 		= 	$data["LoginMessage"];
            $themeData["CustomCss"] 		= 	$data["CustomCss"];			
            $themeData["ThemeStatus"] 		= 	$data["ThemeStatus"];
            $themeData["ModifiedBy"] 		= 	$CreatedBy;
			$themeData["updated_at"] 		= 	date('Y-m-d H:i:s');
            ///////////

            $rules = array(
                'DomainUrl' => 'required|unique:tblCompanyThemes,DomainUrl,'.$id.',ThemeID|url',
                'FooterUrl' => 'url',
            );
			
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv');
            $validator = Validator::make($themeData, $rules);
            $validator->setPresenceVerifier($verifier);
			
            if ($validator->fails())
			{
                return json_validator_response($validator);
            }
			
			//default value for logo and favicon
			$data['logo'] 		= 	$Themes->Logo;
			$data['Favicon'] 	= 	$Themes->Favicon;
			
			if (Input::hasFile('Logo'))
			{
				$upload_path 	  = 	getenv('TEMP_PATH');
				$Attachment		  = 	Input::file('Logo');
				$ext 			  = 	$Attachment->getClientOriginalExtension();			
				
				if (in_array($ext, array("jpg")))
				{
					$amazonPath		 	=	AmazonS3::generate_upload_path(AmazonS3::$dir['THEMES_IMAGES']);
					$destinationPath 	= 	getenv("UPLOAD_PATH") . '/' . $amazonPath;					
					$filename 		 	= 	rename_upload_file($destinationPath,$Attachment->getClientOriginalName());
					$fullPath 		 	= 	$destinationPath .$filename;										
        	        $Attachment->move($destinationPath, $filename);
					
					 if(!AmazonS3::upload($destinationPath.$filename,$amazonPath))
					 {
                    	return Response::json(array("status" => "failed", "message" => "Failed to upload."));
              		 }
					 
					$data['logo_path']   = $fullPath; 
				
					$data['logo']	 	 = $amazonPath . $filename; 
					
					list($width_log,$height_log) =  getimagesize($data['logo_path']);
					
					if($width_log >200 || $height_log>58)
					{
						unlink($data['logo_path']);
						return Response::json(array("status" => "failed", "message" => "Logo image max size is 200 x 58"));			
					}
				}
				else
				{
					return Response::json(array("status" => "failed", "message" => "Invalid Logo file extension."));				
				}	
			}			
			else
			{
				if($Themes->Logo=='')
				{
					return Response::json(array("status" => "failed", "message" => "Please Select Logo."));				
				}
			}
			
			/*Favicon upload start*/						
			if (Input::hasFile('Favicon'))
			{
				
				//////////
				
				///////////
				$upload_path 	  = 	getenv('TEMP_PATH');
				$Attachment		  = 	Input::file('Favicon');
				$ext 			  = 	$Attachment->getClientOriginalExtension();			
				$destinationPath  = 	$upload_path . sprintf("\\%s\\", $company_name);
				if (in_array($ext, array("ico")))
				{
					
					$amazonPath		 	=	AmazonS3::generate_upload_path(AmazonS3::$dir['THEMES_IMAGES']);
					$destinationPath 	= 	getenv("UPLOAD_PATH") . '/' . $amazonPath;					
					$filename 		 	= 	rename_upload_file($destinationPath,$Attachment->getClientOriginalName());
					$fullPath 		 	= 	$destinationPath .$filename;										
        	        $Attachment->move($destinationPath, $filename);
					
					 if(!AmazonS3::upload($destinationPath.$filename,$amazonPath))
					 {
                    	return Response::json(array("status" => "failed", "message" => "Failed to upload."));
              		 }
					 
					$data['Favicon_path']   = $fullPath; 
				
					$data['Favicon']	 	 = $amazonPath . $filename; 
			
					list($width_log,$height_log) =  getimagesize($data['Favicon_path']);
					
					if($width_log >32 || $height_log>32)
					{
						unlink($data['Favicon_path']);
						return Response::json(array("status" => "failed", "message" => "Favicon image max size is 32 x 32"));			
					}
				}
				else
				{
					return Response::json(array("status" => "failed", "message" => "Invalid Favicon file extension."));				
				}	
			}			
			else
			{
				if($Themes->Favicon=='')
				{
					return Response::json(array("status" => "failed", "message" => "Please Select Favicon."));				
				}
			}
			/*Favicon upload end*/	
	
            $themeData['Logo'] 		= 	$data['logo'];
			$themeData['Favicon'] 	= 	$data['Favicon'];

            try
			{               
                if(isset($Themes->ThemeID))
				{
                    $Themes->update($themeData);
					return Response::json(array("status" => "success", "message" => "Theme Successfully Updated", 'LastID' => $Themes->ThemeID,'redirect' => URL::to('/themes')));

                }
            }
			catch (Exception $e)
			{
                return Response::json(array("status" => "failed", "message" => "Problem Updating Theme. \n " . $e->getMessage()));
            }
        }
    }



    public function delete($id)
    {
        if( $id > 0)
		{
            try
			{
                Themes::find($id)->delete();
                return Response::json(array("status" => "success", "message" => "Theme Successfully Deleted"));
            }
			catch (Exception $e)
			{
                return Response::json(array("status" => "failed", "message" => "Theme is in Use, You cant delete this Currrently. \n" . $e->getMessage() ));
            }

        }
    }
	
	public function delete_bulk()
    { 	
		 $data = Input::all();
		 
		 $ThemesIDs 				=	 array_filter(explode(',',$data['del_ids']),'intval');
		 
         if(count($ThemesIDs)>0)
		 {				 
            try
			{
				Themes::whereIn('ThemeID',$ThemesIDs)->delete();
                return Response::json(array("status" => "success", "message" => "Theme(s) Successfully Deleted"));
            }
			catch (Exception $e)
			{
                return Response::json(array("status" => "failed", "message" => "Theme(s) is in Use, You cant delete this Currrently. \n" . $e->getMessage() ));
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
        $InvoiceTemplate = InvoiceTemplate::find($Account->InvoiceTemplateID);
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
            return View::make('estimates.estimates_cview', compact('Estimate', 'EstimateDetail', 'Account', 'EstimateTemplate', 'CurrencyCode', 'logo','CurrencySymbol'));
        }
    }

    // not in use
    public function pdf_view($id)
	{
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
				
                return self::estimate_preview($EstimateID);
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
            $InvoiceTemplate = InvoiceTemplate::find($Account->InvoiceTemplateID);
            if (empty($InvoiceTemplate->CompanyLogoUrl)) {
                $as3url = 'http://placehold.it/250x100';
            } else {
                $as3url = (AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key));
            }
            $logo = getenv('UPLOAD_PATH') . '/' . basename($as3url);
            file_put_contents($logo, file_get_contents($as3url));
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
            $body = View::make('invoices.pdf', compact('Invoice', 'InvoiceDetail', 'Account', 'InvoiceTemplate', 'usage_data', 'CurrencyCode', 'logo'))->render();
            $destination_dir = getenv('UPLOAD_PATH') . '/'. AmazonS3::generate_path(AmazonS3::$dir['INVOICE_UPLOAD'],$Account->CompanyId) ;
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
        $DocumentFile = Invoice::where(["InvoiceID"=>$id])->pluck('Attachment');
        if(file_exists($DocumentFile)){
            download_file($DocumentFile);
        }else{
            $FilePath =  AmazonS3::preSignedUrl($DocumentFile);
            header('Location: '.$FilePath);
        }
        exit;
    }

    public function estimate_email($id)
	{
        $Estimate = Estimate::find($id);
        if(!empty($Estimate))
		{
            $Account 	 	= 	Account::find($Estimate->AccountID);
            $Currency 	 	= 	Currency::find($Account->CurrencyId);
            $CompanyName 	= 	Company::getName();
            
			if (!empty($Currency))
			{
                $Subject = "New Estimate " . Estimate::getFullEstimateNumber($Estimate,$Account). ' from ' . $CompanyName . ' ('.$Account->AccountName.')';
                $RoundChargesAmount = 2;
				
                if($Account->RoundChargesAmount > 0)
				{
                    $RoundChargesAmount = $Account->RoundChargesAmount;
                }

                $data = [
                    'CompanyName' => $CompanyName,
                    'GrandTotal'       => number_format($Estimate->GrandTotal,$RoundChargesAmount),
                    'CurrencyCode'     =>$Currency->Code
                ];
                $Message = Estimate::getEstimateEmailTemplate($data);
                return View::make('estimates.email', compact('Estimate', 'Account', 'Subject','Message','CompanyName'));
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
            $Estimate 					= 	Estimate::find($id);
            $Company 					= 	Company::find($Estimate->CompanyID);
            $CompanyName 				= 	$Company->CompanyName;
            $EstimateGenerationEmail 	= 	CompanySetting::getKeyVal('EstimateGenerationEmail');
            $EstimateGenerationEmail 	= 	($EstimateGenerationEmail =='Invalid Key')?$Company->Email:$EstimateGenerationEmail;
            $emailtoCustomer 			= 	getenv('EmailToCustomer');
            
			if(intval($emailtoCustomer) == 1)
			{
                $CustomerEmail = $data['Email'];
            }
			else
			{
                $CustomerEmail = $Company->Email;
            }
			
            $data['EmailTo'] 			= 	explode(",",$CustomerEmail);
            $data['EstimateURL'] 		= 	"URL::to('/estimate/'.$Estimate->AccountID.'-'.$Estimate->EstimateID.'/cview'";
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
                    $status 				= 	$this->sendEstimateMail('emails.estimates.send',$data);
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
                /*
                    Insert email log in account
                */
                $logData = ['AccountID'=>$Estimate->AccountID,
                    'EmailTo'=>$CustomerEmail,
                    'Subject'=>$data['Subject'],
                    'Message'=>$data['Message']];
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
            $StaffStatus 			= 	$this->sendEstimateMail('emails.estimates.send',$data);
            
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

    function sendEstimateMail($view,$data)
	{ 
        $status 		= 	array('status' => 0, 'message' => 'Something wrong with sending mail.');
        $companyID 		= 	User::get_companyID();
        $mail 			= 	setMailConfig($companyID);
        $body 			= 	View::make($view,compact('data'))->render();

        if(getenv('APP_ENV') != 'Production')
		{
            $data['Subject'] = 'Test Mail '.$data['Subject'];
        }
		
        $mail->Body 	= $body;
        $mail->Subject 	= $data['Subject'];
		
        if(is_array($data['EmailTo']))
		{
            foreach((array)$data['EmailTo'] as $email_address)
			{
                if(!empty($email_address))
				{
                    $email_address = trim($email_address);					
                    $mail->addAddress($email_address);
					
                    if (!$mail->send())
					{
                        $mail->clearAllRecipients();
                        $status['status']   = 	0;
                        $status['message'] .= 	$mail->ErrorInfo . ' ( Email Address: ' . $email_address . ')';
                    }
					else
					{
                        $status['status'] 	= 	1;
                        $status['message'] 	= 	'Email has been sent';
                    }
                }
            }
        }
		else
		{ 
            if(!empty($data['EmailTo']))
			{
                $email_address = trim($data['EmailTo']);
                $mail->addAddress($email_address);
                if (!$mail->send())
				{
                    $mail->clearAllRecipients();
                    $status['status'] = 0;
                    $status['message'] .= $mail->ErrorInfo . ' ( Email Address: ' . $data['EmailTo'] . ')';
                }
				else
				{
                    $status['status'] = 1;
                    $status['message'] = 'Email has been sent';
                }
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
		}
			
		return Response::json(array("status" => "success", "message" => "Estimate Successfully Updated"));			
	}

    
	public function themes_change_Status()
	{
        $data 				= 	 Input::all();
        $username 			=	 User::get_user_full_name();
        $theme_status 	    =  	 Themes::get_theme_status();
		$companyID 			=    User::get_companyID();		
		$Themes_data 		= 	 Themes::find($data['ThemeIDs']);
		
		if($Themes_data->ThemeID)
		{
			if (Themes::where('ThemeID',$data['ThemeIDs'])->update([ 'ModifiedBy'=>$username,'updated_at'=>date('Y-m-d H:i:s'),'ThemeStatus' => $data['ThemeStatus']]))
			{
				
				return Response::json(array("status" => "success", "message" => "Theme Successfully Updated"));
			}
			else
			{
				return Response::json(array("status" => "failed", "message" => "Problem Updating Theme."));
			}
		}
		else
		{
			return Response::json(array("status" => "failed", "message" => "Problem Updating Theme."));
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
    
    public static function display_estimate($EstimateID)
	{
		echo "here";
        $Estimate = Estimate::find($EstimateID);
        $PDFurl = '';
		
        if(is_amazon() == true)
		{
            $PDFurl =  AmazonS3::preSignedUrl($Estimate->PDF);
        }
		else
		{
            $PDFurl = Config::get('app.upload_path')."/".$Estimate->PDF;
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
		
        if(is_amazon() == true)
		{
            header('Location: '.$FilePath);
        }
		else if(file_exists($FilePath))
		{
            download_file($FilePath); 
        }
        exit;
    }
  

 
    
    
    public function ajax_getEmailTemplate($id){
        $filter =array('Type'=>EmailTemplate::ESTIMATE_TEMPLATE);
        if($id == 1){
          $filter['UserID'] =   User::get_userID();
        }
        return EmailTemplate::getTemplateArray($filter);
    }

}