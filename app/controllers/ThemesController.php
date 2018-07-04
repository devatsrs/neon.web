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
			 // ->orWhere('FooterUrl', 'like', '%' . $_GET['searchText'] . '%')
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
				if($row->Logo!='')
				{
					$path = AmazonS3::unSignedImageUrl($row->Logo);
					/*if (!is_numeric(strpos($path, "https://"))) {
						$path = str_replace('/', '\\', $path);
						if (copy($path, './uploads/' . basename($path))) {
							$path = URL::to('/') . '/uploads/' . basename($path);
						}
					}*/
				}
				else
				{
					$path = '';					
				}

                return $path;
            })->edit_column('Favicon',function($row){
				if($row->Favicon!='')
				{
					$path = AmazonS3::unSignedImageUrl($row->Favicon);
					/*if (!is_numeric(strpos($path, "https://"))) {
						$path = str_replace('/', '\\', $path);
						if (copy($path, './uploads/' . basename($path))) {
							$path = URL::to('/') . '/uploads/' . basename($path);
						}
					}*/
				}
				else
				{
					$path = '';	
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
		$ResellerAllowWhiteLabel = Reseller::is_AllowWhiteLabel();
		if(is_reseller() && empty($ResellerAllowWhiteLabel)){
			return Redirect::to('reseller/profile');
		}
        $companyID 				= 		User::get_companyID();
        $data 					= 		Input::all();
        $Themes 				= 		DB::table('tblCompanyThemes')->where(["CompanyID" => $companyID])->orderBy('ThemeID', 'desc')->get();
		$ThemesCount            = DB::table('tblCompanyThemes')->where(["CompanyID" => $companyID])->count();
		$themes_status_json 	= 		json_encode(Themes::get_theme_status());
        return View::make('themes.index',compact('Themes','themes_status_json','ThemesCount'));
    }

    /**
     * Show the form for creating a new resource.
     * GET /invoices/create
     *
     * @return Response
     */
    public function create()
    {
		$CompanyID 				= 	User::get_companyID();
		$ResellerAllowWhiteLabel = Reseller::is_AllowWhiteLabel();
		if(is_reseller() && empty($ResellerAllowWhiteLabel)){
			return Redirect::to('reseller/profile');
		}

		$Domain_Url = CompanyConfiguration::getValueConfigurationByKey('WEB_URL',$CompanyID);
		$sourceUrl = parse_url($Domain_Url);
		$sourceUrl = $sourceUrl['host'];
		$theme_status_json 		= 	Themes::get_theme_status();		
        return View::make('themes.create',compact('theme_status_json','sourceUrl'));
    }

    /**
     *
     * */
    public function edit($id)
	{
        if($id > 0)
		{
			$CompanyID 				= 	User::get_companyID();
	        $theme_status_json 		= 	Themes::get_theme_status();
            $Theme 					= 	Themes::find($id);
			$Domain_Url = CompanyConfiguration::getValueConfigurationByKey('WEB_URL',$CompanyID);
			$sourceUrl = parse_url($Domain_Url);
			$sourceUrl = $sourceUrl['host'];
		    return View::make('themes.edit', compact('Theme','theme_status_json','FilePath_logo','FilePath_fav','sourceUrl'));
        }
    }

    /**
     * Store Invoice
     */
    public function store()
	{
        $data 					= 		Input::all();		
		$companyID 				= 		User::get_companyID();
		$CreatedBy 						= 	User::get_user_full_name();
		$company_name 			= 		Account::getCompanyNameByID($companyID);
		
        if($data)
		{

            $themeData 						= 	array();
            $themeData["CompanyID"] 		= 	$companyID;
            $themeData["DomainUrl"] 		= 	$data["DomainUrl"];
            $themeData["Title"] 			= 	$data["Title"];
            $themeData["FooterText"] 		= 	$data["FooterText"];
           // $themeData["FooterUrl"] 		= 	$data["FooterUrl"];			
			$themeData["LoginMessage"] 		= 	$data["LoginMessage"];
            $themeData["CustomCss"] 		= 	$data["CustomCss"];			
            $themeData["ThemeStatus"] 		= 	empty($data["ThemeStatus"])?Themes::INACTIVE:$data["ThemeStatus"];
            $themeData["CreatedBy"] 		= 	$CreatedBy;
			$themeData["created_at"] 		= 	date('Y-m-d H:i:s');

            ///////////
            $rules = array(
                'CompanyID' => 'required',
                'DomainUrl' => 'required|unique:tblCompanyThemes,DomainUrl', 
				//'FooterUrl' => 'url',               
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
				$upload_path 	  = 	CompanyConfiguration::get('TEMP_PATH');
				$Attachment		  = 	Input::file('Logo');
				$ext 			  = 	$Attachment->getClientOriginalExtension();	
				if (in_array(strtolower($ext), array("jpg","png")))
				{		
					list($width_log,$height_log) =  getimagesize($Attachment->getRealPath());
					

					if($width_log >200)
					{
						return Response::json(array("status" => "failed", "message" => "Logo max width is 200"));			
					}					

					$amazonPath		 	=	AmazonS3::generate_upload_path(AmazonS3::$dir['THEMES_IMAGES'],'',$companyID);
					$destinationPath 	= 	CompanyConfiguration::get('UPLOAD_PATH',$companyID) . '/' . $amazonPath;
					$filename 		 	= 	rename_upload_file($destinationPath,$Attachment->getClientOriginalName());
					$fullPath 		 	= 	$destinationPath .$filename;										
        	        $Attachment->move($destinationPath, $filename);
					
					 if(!AmazonS3::upload($destinationPath.$filename,$amazonPath))
					 {
                    	return Response::json(array("status" => "failed", "message" => "Failed to upload."));
              		 }
					 
					$data['logo_path']   = $fullPath; 
				
					$data['logo']	 	 = $amazonPath . $filename; 
					
					
				}
				else
				{
					return Response::json(array("status" => "failed", "message" => "Invalid Logo file extension."));				
				}	
			}			
			else
			{
				$data['logo'] = '';
				//return Response::json(array("status" => "failed", "message" => "Please Select Logo."));				
			}
			
			/*Favicon upload start*/						
			if (Input::hasFile('Favicon'))
			{
				
				///////////
				$upload_path 	  = 	CompanyConfiguration::get('TEMP_PATH');
				$Attachment		  = 	Input::file('Favicon');
				$ext 			  = 	$Attachment->getClientOriginalExtension();	
				
				if (in_array(strtolower($ext), array("ico")))
				{
					list($width_fav,$height_fav) =  getimagesize($Attachment->getRealPath());
					
					if($width_fav >32 || $height_fav>32)
					{
						return Response::json(array("status" => "failed", "message" => "Favicon image max size is 32 x 32"));			
					}
					
					$amazonPath		 	=	AmazonS3::generate_upload_path(AmazonS3::$dir['THEMES_IMAGES'],'',$companyID);
					$destinationPath 	= 	CompanyConfiguration::get('UPLOAD_PATH',$companyID) . '/' . $amazonPath;
					$filename 		 	= 	rename_upload_file($destinationPath,$Attachment->getClientOriginalName());
					$fullPath 		 	= 	$destinationPath .$filename;										
        	        $Attachment->move($destinationPath, $filename);
					
					 if(!AmazonS3::upload($destinationPath.$filename,$amazonPath))
					 {
                    	return Response::json(array("status" => "failed", "message" => "Failed to upload."));
              		 }
					 
					$data['Favicon_path']   = $fullPath; 
				
					$data['Favicon']	 	 = $amazonPath . $filename; 				
				}
				else
				{
					return Response::json(array("status" => "failed", "message" => "Invalid Favicon file extension."));				
				}	
			}			
			else
			{
				$data['Favicon'] = '';
				//return Response::json(array("status" => "failed", "message" => "Please Select Favicon."));				
			}
			/*Favicon upload end*/	
	
            $themeData['Logo'] 		= 	$data['logo'];
			$themeData['Favicon'] 	= 	$data['Favicon'];
			
			try
			{
                if ($theme = Themes::create($themeData))
				{
					$domainUrl_key = preg_replace('/[^A-Za-z0-9\-]/', '', $themeData["DomainUrl"]);
					$domainUrl_key = preg_replace('/-+/', '_',$domainUrl_key);

					Translation::add_system_name("THEMES_".$domainUrl_key."_FOOTERTEXT", $themeData["FooterText"]);
					Translation::add_system_name("THEMES_".$domainUrl_key."_LOGIN_MSG", $themeData["LoginMessage"]);
					Translation::add_system_name("THEMES_".$domainUrl_key."_TITLE", $themeData["Title"]);

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
		$CreatedBy 						= 	User::get_user_full_name();
		$company_name 			= 		Account::getCompanyNameByID($companyID);
		
        if(!empty($data) && $id > 0)
		{
            $Themes 						= 	Themes::find($id);

            $themeData 						= 	array();
            $themeData["DomainUrl"] 		= 	$data["DomainUrl"];
            $themeData["Title"] 			= 	$data["Title"];
            $themeData["FooterText"] 		= 	$data["FooterText"];
            //$themeData["FooterUrl"] 		= 	$data["FooterUrl"];			
			$themeData["LoginMessage"] 		= 	$data["LoginMessage"];
            $themeData["CustomCss"] 		= 	$data["CustomCss"];			
            $themeData["ThemeStatus"] 		= 	empty($data["ThemeStatus"])?Themes::INACTIVE:$data["ThemeStatus"];
            $themeData["ModifiedBy"] 		= 	$CreatedBy;
			$themeData["updated_at"] 		= 	date('Y-m-d H:i:s');
            ///////////

            $rules = array(
                'DomainUrl' => 'required|unique:tblCompanyThemes,DomainUrl,'.$id.',ThemeID',
               // 'FooterUrl' => 'url',
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
				$upload_path 	  = 	CompanyConfiguration::get('TEMP_PATH');
				$Attachment		  = 	Input::file('Logo');
				$ext 			  = 	$Attachment->getClientOriginalExtension();			
				
				if (in_array(strtolower($ext), array("jpg","png")))
				{
					list($width_log,$height_log) =  getimagesize($Attachment->getRealPath());
					
					if($width_log >200)
					{
						return Response::json(array("status" => "failed", "message" => "Logo max width is 200"));			
					}

					$amazonPath		 	=	AmazonS3::generate_upload_path(AmazonS3::$dir['THEMES_IMAGES'],'',$companyID);
					$destinationPath 	= 	CompanyConfiguration::get('UPLOAD_PATH',$companyID) . '/' . $amazonPath;
					$filename 		 	= 	rename_upload_file($destinationPath,$Attachment->getClientOriginalName());
					$fullPath 		 	= 	$destinationPath .$filename;										
        	        $Attachment->move($destinationPath, $filename);
					
					 if(!AmazonS3::upload($destinationPath.$filename,$amazonPath))
					 {
                    	return Response::json(array("status" => "failed", "message" => "Failed to upload."));
              		 }
					 
					$data['logo_path']   = $fullPath; 
				
					$data['logo']	 	 = $amazonPath . $filename; 
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
				 $data['logo'] = '';
				 //	return Response::json(array("status" => "failed", "message" => "Please Select Logo."));				
				}
			}
			
			/*Favicon upload start*/						
			if (Input::hasFile('Favicon'))
			{
				
				//////////
				
				///////////
				$upload_path 	  = 	CompanyConfiguration::get('TEMP_PATH');
				$Attachment		  = 	Input::file('Favicon');
				$ext 			  = 	$Attachment->getClientOriginalExtension();			

				if (in_array(strtolower($ext), array("ico")))
				{
					list($width_log,$height_log) =  getimagesize($Attachment->getRealPath());
					
					if($width_log >32 || $height_log>32)
					{
						return Response::json(array("status" => "failed", "message" => "Favicon image max size is 32 x 32"));			
					}
					
					$amazonPath		 	=	AmazonS3::generate_upload_path(AmazonS3::$dir['THEMES_IMAGES'],'',$companyID);
					$destinationPath 	= 	CompanyConfiguration::get('UPLOAD_PATH',$companyID) . '/' . $amazonPath;
					$filename 		 	= 	rename_upload_file($destinationPath,$Attachment->getClientOriginalName());
					$fullPath 		 	= 	$destinationPath .$filename;										
        	        $Attachment->move($destinationPath, $filename);
					
					 if(!AmazonS3::upload($destinationPath.$filename,$amazonPath))
					 {
                    	return Response::json(array("status" => "failed", "message" => "Failed to upload."));
              		 }
					 
					$data['Favicon_path']   = $fullPath; 
				
					$data['Favicon']	 	 = $amazonPath . $filename; 					
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
					$data['Favicon']  = '';
					//return Response::json(array("status" => "failed", "message" => "Please Select Favicon."));				
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

					$domainUrl_key = preg_replace('/[^A-Za-z0-9\-]/', '', $themeData["DomainUrl"]);
					$domainUrl_key = preg_replace('/-+/', '_',$domainUrl_key);

					Translation::update_label(Translation::$default_lang_ISOcode, "THEMES_".$domainUrl_key."_FOOTERTEXT", $themeData["FooterText"]);
					Translation::update_label(Translation::$default_lang_ISOcode, "THEMES_".$domainUrl_key."_LOGIN_MSG", $themeData["LoginMessage"]);
					Translation::update_label(Translation::$default_lang_ISOcode, "THEMES_".$domainUrl_key."_TITLE", $themeData["Title"]);

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
                $theme=Themes::find($id);
				$domainUrl_key=$theme->DomainUrl;
				$theme->delete();

				$arr_language=Translation::getLanguageDropdownWithFlagList();

				$domainUrl_key = preg_replace('/[^A-Za-z0-9\-]/', '', $domainUrl_key);
				$domainUrl_key = preg_replace('/-+/', '_',$domainUrl_key);

				foreach($arr_language as $lang_iso=>$value){
					Translation::delete_label($lang_iso, "THEMES_".$domainUrl_key."_FOOTERTEXT");
					Translation::delete_label($lang_iso, "THEMES_".$domainUrl_key."_LOGIN_MSG");
					Translation::delete_label($lang_iso, "THEMES_".$domainUrl_key."_TITLE");
				}

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
				$arr_themes = Themes::whereIn('ThemeID',$ThemesIDs)->get();
				Themes::whereIn('ThemeID',$ThemesIDs)->delete();

				$arr_language=Translation::getLanguageDropdownWithFlagList();

				foreach($arr_themes as $theme){
					$domainUrl_key = preg_replace('/[^A-Za-z0-9\-]/', '', $theme->DomainUrl);
					$domainUrl_key = preg_replace('/-+/', '_',$domainUrl_key);

					foreach($arr_language as $lang_iso=>$value){
						Translation::delete_label($lang_iso, "THEMES_".$domainUrl_key."_FOOTERTEXT");
						Translation::delete_label($lang_iso, "THEMES_".$domainUrl_key."_LOGIN_MSG");
						Translation::delete_label($lang_iso, "THEMES_".$domainUrl_key."_TITLE");
					}
				}

                return Response::json(array("status" => "success", "message" => "Theme(s) Successfully Deleted"));
            }
			catch (Exception $e)
			{
                return Response::json(array("status" => "failed", "message" => "Theme(s) is in Use, You cant delete this Currrently. \n" . $e->getMessage() ));
            }
        }
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
}