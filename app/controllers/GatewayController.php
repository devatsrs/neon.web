<?php

class GatewayController extends \BaseController {

    public function ajax_datagrid($type) {
        $data = Input::all();

        $CompanyID = User::get_companyID();
		$GatewayID = (isset($data['Gateway']) && $data['Gateway']!='')?$data['Gateway']:0;
        $Gateway = CompanyGateway::
            select('Title','IP','Status','GatewayID','CompanyGatewayID','TimeZone','BillingTimeZone')
            ->where("CompanyID", $CompanyID);
		if($GatewayID>0){
			$Gateway->where("GatewayID", $GatewayID);
		}	
        if(isset($data['Export']) && $data['Export'] == 1) {
            $Gateway = CompanyGateway::select('Title','IP','Status','TimeZone','BillingTimeZone')
                ->where("CompanyID", $CompanyID);
            $excel_data = $Gateway->get();
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Gateway.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Gateway.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }

        return Datatables::of($Gateway)->make();
    }

    public function index($id=0)
    {
        $gateway 			= 	Gateway::getGatewayListID();
        $timezones 			= 	TimeZone::getTimeZoneDropdownList();
        $GatewayName        =   Gateway::getGatewayName($id);

       // $gateway['other'] 	= 	'other';
        return View::make('gateway.index', compact('gateway','GatewayName','timezones','id'));
    }

    /**
     * Store a newly created resource in storage.
     * POST /Gateway
     *
     * @return Response
     */
    public function create()
    {
        $data = Input::all();
        $companyID = User::get_companyID();
        if (Input::hasFile('key')) {
            //$upload_path = CompanyConfiguration::get('UPLOAD_PATH');
            $key = Input::file('key');
            $ext = $key->getClientOriginalExtension();
            $file_name_without_ext = GUID::generate();
            $file_name = $file_name_without_ext . '.' . $key->getClientOriginalExtension();

            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['GATEWAY_KEY']) ;
            $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
            $key->move($destinationPath, $file_name);

            if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath)){
                return Response::json(array("status" => "failed", "message" => "Failed to upload."));
            }
            $file_name = $amazonPath.$file_name;
            $data['key'] = $file_name;
        }
        $data['CompanyID'] = $companyID;
        $rules = array(
            'Title' => 'required|unique:tblCompanyGateway,Title,NULL,CompanyGatewayID,CompanyID,'.$data['CompanyID'],
            'GatewayID'=>'required',
            'TimeZone'=>'required',
            'BillingTimeZone'=>'required',
        );
        $rules = $rules +GatewayAPI::getRules($data['GatewayID']);
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if($data['GatewayID'] == 'other'){
            unset($data['GatewayID']);
        }
        if(isset($data['password']) && !empty($data['password'])){
            $data['password'] = Crypt::encrypt($data['password']);
        }
        if(isset($data['dbpassword']) && !empty($data['dbpassword'])){
            $data['dbpassword'] = Crypt::encrypt($data['dbpassword']);
        }
        if(isset($data['sshpassword']) && !empty($data['sshpassword'])){
            $data['sshpassword'] = Crypt::encrypt($data['sshpassword']);
        }
        if(isset($data['api_password']) && !empty($data['api_password'])){
            $data['api_password'] = Crypt::encrypt($data['api_password']);
        }
        $today = date('Y-m-d');
        $data['CreatedBy'] = User::get_user_full_name();
        $data['created_at'] =  $today;
        if(count($data)>0){
            $data['Settings'] =  json_encode($data);
        }
        unset($data['Accounts']);

        if ($CompanyGateway = CompanyGateway::create($data)) {
            $CompanyGatewayID = $CompanyGateway->CompanyGatewayID;
            CompanyGateway::createCronJobsByCompanyGateway($CompanyGatewayID);
            return Response::json(array("status" => "success", "message" => "Gateway Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Gateway."));
        }
    }

    /**
     * Display the specified resource.
     * GET /Gateway/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * GET /Gateway/{id}/edit
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * PUT /Gateway/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        if( $id > 0 ) {

            $CompanyGateway = CompanyGateway::findOrFail($id);
            $data = Input::all();
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;
            //$file_key = Input::file('key');
            if (Input::hasFile('key')) {
                //$upload_path = CompanyConfiguration::get('UPLOAD_PATH');
                $key = Input::file('key');
                $ext = $key->getClientOriginalExtension();
                $file_name_without_ext = GUID::generate();
                $file_name = $file_name_without_ext . '.' . $key->getClientOriginalExtension();

                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['GATEWAY_KEY']) ;
                $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
                $key->move($destinationPath, $file_name);

                if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath,0)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $file_name = $amazonPath.$file_name;
                $data['key'] = $file_name;
                $settings = json_decode($CompanyGateway->Settings,true);
                //Delete old picture
                //echo "<pre>";print_R($settings);exit;
                if(!empty($settings['key'])){
                    AmazonS3::delete($settings['key'],$companyID);
                }
            }
            else
            {
                if(isset($data['oldkey']))
                    $data['key'] = $data['oldkey'];
                else
                    $data['key'] = '';
            }

            $rules = array(
                'Title' => 'required|unique:tblCompanyGateway,Title,'.$id.',CompanyGatewayID,CompanyID,'.$data['CompanyID'],
                'GatewayID'=>'required',
                'TimeZone'=>'required',
                'BillingTimeZone'=>'required',
            );
            $rules = $rules +GatewayAPI::getRules($data['GatewayID']);
            if(isset($data['password']) && !empty($data['password'])){
                $data['password'] = Crypt::encrypt($data['password']);
            }else {
                $settings = json_decode($CompanyGateway->Settings,true);
                if(isset($settings["password"])&& !empty($settings["password"])){
                    $data['password'] = $settings["password"];
                }
            }
            if(isset($data['dbpassword']) && !empty($data['dbpassword'])){
                $data['dbpassword'] = Crypt::encrypt($data['dbpassword']);
            }else {
                $settings = json_decode($CompanyGateway->Settings,true);
                if(isset($settings["dbpassword"])&& !empty($settings["dbpassword"])){
                    $data['dbpassword'] = $settings["dbpassword"];
                }
            }
            if(isset($data['sshpassword']) && !empty($data['sshpassword'])){
                $data['sshpassword'] = Crypt::encrypt($data['sshpassword']);
            }else {
                $settings = json_decode($CompanyGateway->Settings,true);
                if(isset($settings["sshpassword"])&& !empty($settings["sshpassword"])){
                    $data['sshpassword'] = $settings["sshpassword"];
                }
            }
            if(isset($data['api_password']) && !empty($data['api_password'])){
                $data['api_password'] = Crypt::encrypt($data['api_password']);
            }else {
                $settings = json_decode($CompanyGateway->Settings,true);
                if(isset($settings["api_password"])&& !empty($settings["api_password"])){
                    $data['api_password'] = $settings["api_password"];
                }
            }
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if($data['GatewayID'] == 'other'){
                unset($data['GatewayID']);
            }
            $today = date('Y-m-d');
            $data['CreatedBy'] = User::get_user_full_name();
            $data['created_at'] =  $today;

            $tag = '"CompanyGatewayID":"'.$id.'"';
            if($data['Status']==1){
                if(CronJob::where('Settings','LIKE', '%'.$tag.'%')->where(['CompanyID'=>$companyID])->count()==0){
                    CompanyGateway::createCronJobsByCompanyGateway($id);
                }
                CronJob::where('Settings','LIKE', '%'.$tag.'%')->where(['CompanyID'=>$companyID])->update(['Status'=>1]);
            }else if($data['Status']==0){
                CronJob::where('Settings','LIKE', '%'.$tag.'%')->where(['CompanyID'=>$companyID])->update(['Status'=>0]);
            }
            if(count($data)>0){
                $data['Settings'] =  json_encode($data);
            }
            unset($data['Accounts']);

            if ($CompanyGateway->update($data)) {
                return Response::json(array("status" => "success", "message" => "Gateway Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Gateway."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Gateway."));
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /Gateway/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function delete($id)
    {
        if( intval($id) > 0){
            //if(!CompanyGateway::checkForeignKeyById($id)) {
                try {
                    //$CompanyID = User::get_companyID();
                    //$result = DB::statement('prc_DeleteCompanyGatewayWithReferences('.$CompanyID.','.$id.')');
                    $result = CompanyGateway::find($id);
                    if (!empty($result) && $result->delete()) {
                    //if($result){
                        return Response::json(array("status" => "success", "message" => "Gateway Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Gateway."));
                    }
                } catch (Exception $ex) {
                    return Response::json(array("status" => "failed", "message" => "Gateway is in Use, You cant delete this Gateway."));
                }
           /* }else{
                    return Response::json(array("status" => "failed", "message" => "Gateway is in Use, You cant delete this Gateway."));
                }*/
        }else{
            return Response::json(array("status" => "failed", "message" => "Gateway is in Use, You cant delete this Gateway."));
        }
    }
    public function ajax_load_gateway_dropdown(){
        $data = Input::all();
        if(isset($data['GatewayID']) && intval($data['GatewayID']) > 0) {
            $gatewayconfig = Gateway::getGatewayConfig($data['GatewayID']);
            if(isset($data['CompanyGatewayID']) && intval($data['CompanyGatewayID']) > 0) {
                $CompanyGateway = CompanyGateway::findOrFail($data['CompanyGatewayID']);
                $gatewayconfigval = json_decode($CompanyGateway->Settings);
            }
            $GatewayName = Gateway::getGatewayName($data['GatewayID']);
            $Accounts   = Account::getAccountIDList();
            $Accounts   = array_diff($Accounts,array('Select'));
            //echo "<pre>";print_R($gatewayconfig);exit;
            return View::make('gateway.ajax_config_html', compact('gatewayconfig','gatewayconfigval','GatewayName','Accounts'));
        }
        return '';
    }
    public function test_connetion($id){
        $CompanyGateway =  CompanyGateway::find($id);
        $response = array();
        if(!empty($CompanyGateway)){
            $getGatewayName = Gateway::getGatewayName($CompanyGateway->GatewayID);
            $getGatewayName = in_array($getGatewayName,Gateway::$ftp_array)?'FTP':$getGatewayName;
            $response =  GatewayAPI::GatewayMethod($getGatewayName,$CompanyGateway->CompanyGatewayID,'testConnection');
        }
        if(isset($response['result']) && $response['result'] =='OK'){
            return Response::json(array("status" => "success", "message" => "Gateway settings is ok"));
        }else if(isset($response['faultCode']) && isset($response['faultString'])){
            return Response::json(array("status" => "failed", "message" => "Failed to connect Gateway.".$response['faultString']));
        }else{
            return Response::json(array("status" => "failed", "message" => "Failed to connect Gateway."));
        }
    }

    // check before delete if any cronjob set gateway or not
    public function ajax_existing_gateway_cronjob($id){
        $companyID = User::get_companyID();
        $tag = '"CompanyGatewayID":"'.$id.'"';
        $cronJobs = CronJob::where('Settings','LIKE', '%'.$tag.'%')->where(['CompanyID'=>$companyID])->select(['JobTitle','Status','created_by','CronJobID'])->get()->toArray();
        return View::make('gateway.ajax_gateway_cronjobs', compact('cronJobs'));
    }

    public function deleteCronJob($id){
        $data = Input::all();
        try{
            $cronjobs = explode(',',$data['cronjobs']);
            foreach($cronjobs as $cronjobID){
                $cronjob = CronJob::find($cronjobID);
                if($cronjob->Active){
                    $Process = new Process();
                    $Process->change_crontab_status(0);
                }
                $cronjob->delete();
                CronJobLog::where("CronJobID",$cronjobID)->delete();
            }
            return Response::json(array("status" => "success", "message" => "Cron Job Successfully Deleted"));
        }catch (Exception $ex){
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }
}