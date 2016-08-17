<?php

class IntegrationController extends \BaseController
{

    public function __construct()
	{
		
    }
    /**
     * Display a listing of the resource.
     * GET /integration
     *
     * @return Response
     */
    public function index()
	{
		$companyID  = User::get_companyID();
		$categories = Integration::where(["CompanyID" => $companyID,"ParentID"=>0])->orderBy('Title', 'asc')->get();
		return View::make('integration.index', compact('categories',"companyID"));
    }
	
	function Update(){
		$data 			 = 	Input::all();
		$companyID  	 = 	User::get_companyID();
		 $rules = array(
            'firstcategory' => 'required',
            'secondcategory' => 'required',         
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
		
		if($data['firstcategory']=='support')
		{ 

			if($data['secondcategory']=='freshdesk')
			{			
				$rules = array(
					'FreshdeskDomain'	 => 'required',
					'FreshdeskEmail'	 => 'required|email',
					'FreshdeskPassword'  => 'required',
					'Freshdeskkey'		 => 'required',
				);
		
				$validator = Validator::make($data, $rules);
		
				if ($validator->fails()) {
					return json_validator_response($validator);
				}
			}
			
			$FreshdeskData = array(
					"FreshdeskDomain"=>$data['FreshdeskDomain'],
					"FreshdeskEmail"=>$data['FreshdeskEmail'],
					"FreshdeskPassword"=>$data['FreshdeskPassword'],
					"Freshdeskkey"=>$data['Freshdeskkey'],
					"FreshdeskGroup"=>$data['FreshdeskGroup']
					
			);
			
		  $data['Status'] = isset($data['Status'])?1:0;	
		  if($data['Status']==1){ //disable all other support subcategories
				$status =	array("Status"=>0);
				IntegrationConfiguration::where(array('ParentIntegrationID'=>$data['firstcategoryid']))->update($status);
		   }
			
			$FreshDeskDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$data['secondcategoryid']))->first();
			
			if(count($FreshDeskDbData)>0)
			{
				$SaveData = array("Settings"=>json_encode($FreshdeskData),"updated_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
				IntegrationConfiguration::where(array('IntegrationConfigurationID'=>$FreshDeskDbData->IntegrationConfigurationID))->update($SaveData);	
				
			}
			else
			{	
				$SaveData = array("Settings"=>json_encode($FreshdeskData),"IntegrationID"=>$data['secondcategoryid'],"CompanyId"=>$companyID,"created_by"=> User::get_user_full_name(),"Status"=>$data['Status'],'ParentIntegrationID'=>$data['firstcategoryid']);
			 	IntegrationConfiguration::create($SaveData);
			}
			 return Response::json(array("status" => "success", "message" => "FreshDesk Settings Successfully Updated"));
		}
	}
}