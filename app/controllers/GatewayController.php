<?php

class GatewayController extends \BaseController {

    public function ajax_datagrid($type) {
        $data = Input::all();

        $CompanyID = User::get_companyID();
        $Gateway = CompanyGateway::
            select('Title','IP','Status','GatewayID','CompanyGatewayID','TimeZone','BillingTimeZone')
            ->where("CompanyID", $CompanyID);
        if(isset($data['Export']) && $data['Export'] == 1) {
            $Gateway = CompanyGateway::select('Title','IP','Status','TimeZone','BillingTimeZone')
                ->where("CompanyID", $CompanyID);
            $excel_data = $Gateway->get();
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Gateway.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Gateway.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }

        return Datatables::of($Gateway)->make();
    }

    public function index()
    {
        $gateway = Gateway::getGatewayListID();
        $timezones = TimeZone::getTimeZoneDropdownList();
        $gateway['other'] = 'other';
        return View::make('gateway.index', compact('gateway','timezones'));

    }

    /**
     * Store a newly created resource in storage.
     * POST /Gateway
     *
     * @return Response
     */
    public function create()
    {
        $data = array();
        $datainput = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $data['Title'] =$datainput['Title'];
        $data['GatewayID'] =$datainput['GatewayID'];
        $data['Status'] =$datainput['Status'];
        $data['IP'] =$datainput['IP'];
        $data['TimeZone'] =$datainput['TimeZone'];
        //$data['BillingTime'] =$datainput['BillingTime'];
        $data['BillingTimeZone'] =$datainput['BillingTimeZone'];
        $rules = array(
            'Title' => 'required|unique:tblCompanyGateway,Title,NULL,CompanyGatewayID,CompanyID,'.$data['CompanyID'],
            'GatewayID'=>'required',
            'TimeZone'=>'required',
            'BillingTimeZone'=>'required',
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if($data['GatewayID'] == 'other'){
            unset($data['GatewayID']);
        }
        if(isset($datainput['password']) && !empty($datainput['password'])){
            $datainput['password'] = Crypt::encrypt($datainput['password']);
        }
        $today = date('Y-m-d');
        $data['CreatedBy'] = User::get_user_full_name();
        $data['created_at'] =  $today;
        unset($datainput['CompanyGatewayID']);
        unset($datainput['Title']);
        unset($datainput['GatewayID']);
        unset($datainput['Status']);
        unset($datainput['Status_name']);
        unset($datainput['IP']);
        if(count($datainput)>0){
            $data['Settings'] =  json_encode($datainput);
        }
        if (CompanyGateway::create($data)) {
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
            $data = array();
            $datainput = Input::all();
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;
            $data['Title'] =$datainput['Title'];
            $data['GatewayID'] =$datainput['GatewayID'];
            $data['Status'] =$datainput['Status'];
            $data['IP'] =$datainput['IP'];
            $data['TimeZone'] =$datainput['TimeZone'];
            //$data['BillingTime'] =$datainput['BillingTime'];
            $data['BillingTimeZone'] =$datainput['BillingTimeZone'];
            $rules = array(
                'Title' => 'required|unique:tblCompanyGateway,Title,'.$id.',CompanyGatewayID,CompanyID,'.$data['CompanyID'],
                'GatewayID'=>'required',
                'TimeZone'=>'required',
                'BillingTimeZone'=>'required',
            );
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
            if(isset($datainput['password']) && !empty($datainput['password'])){
                $datainput['password'] = Crypt::encrypt($datainput['password']);
            }else {
                $settings = json_decode($CompanyGateway->Settings,true);
                if(isset($settings["password"])&& !empty($settings["password"])){
                    $datainput['password'] = Crypt::decrypt($settings["password"]);
                }
            }
            unset($datainput['CompanyGatewayID']);
            unset($datainput['Title']);
            unset($datainput['GatewayID']);
            unset($datainput['Status']);
            unset($datainput['Status_name']);
            unset($datainput['IP']);
            if(count($datainput)>0){
                $data['Settings'] =  json_encode($datainput);
            }
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
            if(!CompanyGateway::checkForeignKeyById($id)) {
                try {
                    $result = CompanyGateway::find($id);
                    if (!empty($result) && $result->delete()) {
                        return Response::json(array("status" => "success", "message" => "Gateway Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Gateway."));
                    }
                } catch (Exception $ex) {
                    return Response::json(array("status" => "failed", "message" => "Gateway is in Use, You cant delete this Gateway."));
                }
            }else{
                    return Response::json(array("status" => "failed", "message" => "Gateway is in Use, You cant delete this Gateway."));
                }
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
            return View::make('gateway.ajax_config_html', compact('gatewayconfig','gatewayconfigval','GatewayName'));
        }
        return '';
    }
    public function test_connetion($id){
        $CompanyGateway =  CompanyGateway::find($id);
        $response = array();
        if(!empty($CompanyGateway)){
            $getGatewayName = Gateway::getGatewayName($CompanyGateway->GatewayID);
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


}