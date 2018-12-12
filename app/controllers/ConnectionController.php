<?php
use Illuminate\Support\Facades\Crypt;

class ConnectionController extends \BaseController {
    var $model = 'VendorConnection';

    public function index($id)
    {
        $companyID = User::get_companyID();
        $trunks = VendorTrunk::getTrunkDropdownIDList($id);
        if(count($trunks) == 0){
            return  Redirect::to('vendor_rates/'.$id.'/settings')->with('info_message', 'Please enable trunk against vendor to manage rates');
        }
        $Type=[''=>'Select']+VendorConnection::$Type_array;
        $DIDCategories=DIDCategory::getCategoryDropdownIDList($companyID);
        $CurrencyID=Account::getCurrencyIDByAccount($id);

        $TariffDID=RateTable::getDIDTariffDropDownList($companyID,VendorConnection::Type_DID,$CurrencyID);
        $TariffVoiceCall=RateTable::getDIDTariffDropDownList($companyID,VendorConnection::Type_VoiceCall,$CurrencyID);

        return View::make('vendorrates.connection.index', compact('id','trunks','Type','DIDCategories','TariffDID','TariffVoiceCall'));

    }

    public function search_ajax_datagrid($id,$type) {

        $data = Input::all();

        $data['iDisplayStart'] +=1;
        $data['TrunkID']=!empty($data['TrunkID'])?$data['TrunkID']:0;
        $data['IP'] = !empty($data['IP'])?$data['IP']:'';
        $data['ConnectionType'] = !empty($data['ConnectionType'])?$data['ConnectionType']:'';
        $data['Name'] = !empty($data['Name'])?$data['Name']:'';

        $columns = array('VendorConnectionID','Name','ConnectionType','IP','Active','created_at','DIDCategoryID','Tariff','TrunkID','CLIRule','CLDRule','CallPrefix','Port','Username');

        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();

        $query = "call prc_getVendorConnection (" . $companyID . "," . $id . "," . $data['TrunkID'] . ",'" . $data['IP'] . "','" . $data['ConnectionType'] . "','" . $data['Name'] . "'," . (ceil($data['iDisplayStart'] / $data['iDisplayLength'])) . " ," . $data['iDisplayLength'] . ",'" . $sort_column . "','" . $data['sSortDir_0'] . "'";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/VendorConnection.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/VendorConnection.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

        }

        $query .=',0)';

        //echo $query;die;
        //Log::info($query);

        return DataTableSql::of($query)->make();

    }

    /**
     * Store a newly created resource in storage.
     * POST /taxrates
     *
     * @return Response
     */
    public function create($id)
    {
        if($id > 0) {
            $data=array();
            $Input = Input::all();
            $companyID = User::get_companyID();

            unset($data['VendorConnectionID']);


            $rules=array();
            if($Input['ConnectionType']==VendorConnection::Type_DID){
                $data=$Input['did'];
                $rules = array(
                    'ConnectionType' => 'required',
                    'Name' => 'required',
                    'CompanyID' => 'required',
                    'DIDCategoryID' => 'required',
                    'Tariff' => 'required',

                );
            }else if($Input['ConnectionType']==VendorConnection::Type_VoiceCall){
                $data=$Input['voice'];
                $rules = array(
                    'ConnectionType' => 'required',
                    'Name' => 'required',
                    'CompanyID' => 'required',
                    'TrunkID' => 'required',
                    'Tariff' => 'required',

                );
            }else{
                $data=$Input['voice'];
                $rules = array(
                    'ConnectionType' => 'required',
                    'Name' => 'required',
                    'CompanyID' => 'required',

                );
            }

            $data['CompanyID'] = $companyID;
            $data["created_by"] = User::get_user_full_name();
            $data['Active'] = isset($data['Active']) ? 1 : 0;
            $data['PrefixCDR'] = isset($data['PrefixCDR']) ? 1 : 0;
            $data['ConnectionType'] =$Input['ConnectionType'];
            $data['Name'] =$Input['Name'];
            $data['AccountId']=$id;

            if(!empty($data['Password'])){
                $data['Password'] = Crypt::encrypt($data['Password']);
            }

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            //check Duplicate
            $checkduplicate=VendorConnection::where(['ConnectionType'=>$data['ConnectionType'],'Name'=>$data['Name']])->get()->count();
            if($checkduplicate > 0){
                return Response::json(array("status" => "failed", "message" => "Type with this Name Already Exists."));
            }

            if ($VendorConnection = VendorConnection::create($data)) {
                return Response::json(array("status" => "success", "message" => "Vendor Connection Successfully Created", 'LastID' => $VendorConnection->VendorConnectionID));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Vendor Connection."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Problem Creating Vendor Connection."));
        }
    }

    /**
     * Display the specified resource.
     * GET /taxrates/{id}
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
     * GET /taxrates/{id}/edit
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
     * PUT /taxrates/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id,$conID)
    {
        if( $id && $conID > 0 ) {
            $data=array();
            $Input = Input::all();
            $companyID = User::get_companyID();
            $VendorConnection=VendorConnection::findOrFail($conID);
            unset($data['VendorConnectionID']);

            $rules=array();
            if($VendorConnection->ConnectionType==VendorConnection::Type_DID){
                $data=$Input['did'];
                $rules = array(
                    'Name' => 'required',
                    'CompanyID' => 'required',
                    'DIDCategoryID' => 'required',
                    'Tariff' => 'required',

                );
            }else if($VendorConnection->ConnectionType==VendorConnection::Type_VoiceCall){
                $data=$Input['voice'];
                $rules = array(
                    'Name' => 'required',
                    'CompanyID' => 'required',
                    'TrunkID' => 'required',
                    'Tariff' => 'required',

                );
            }

            $data['CompanyID'] = $companyID;
            $data["updated_by"] = User::get_user_full_name();
            $data['Active'] = isset($data['Active']) ? 1 : 0;
            $data['PrefixCDR'] = isset($data['PrefixCDR']) ? 1 : 0;
            $data['Name'] =$Input['Name'];
            $data['AccountId']=$id;

            if(!empty($data['Password'])){
                //$data['password'] = Hash::make($data['password']);
                $data['Password'] = Crypt::encrypt($data['Password']);
            }else{
                unset($data['Password']);
            }


            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if ($VendorConnection->update($data)) {
                return Response::json(array("status" => "success", "message" => "Vendor Connection Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Vendor Connection."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Vendor Connection."));
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /taxrates/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function delete($id)
    {
        if( intval($id) > 0){

            try{
                $result = VendorConnection::find($id)->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Vendor Connection Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Vendor Connection."));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Vendor Connection is in Use, You cant delete this Vendor Connection."));
            }

        }
    }

   public function updatestatus($id,$status){
       if( intval($id) > 0 && $status >=0){
           try{
               $result = VendorConnection::findorfail($id);
               $oldStatus=$result->Active;
               if($oldStatus!=$status){
                   if ($result->update(['Active'=>$status])) {
                       return Response::json(array("status" => "success", "message" => "Status Successfully Updated"));
                   } else {
                       return Response::json(array("status" => "failed", "message" => "Problem Updating Status."));
                   }
               }else{
                   return Response::json(array("status" => "failed", "message" => "Nothing to update."));
               }

           }catch (Exception $ex){
               return Response::json(array("status" => "failed", "message" => "Something went wrong."));
           }
       }else{
           return Response::json(array("status" => "failed", "message" => "Something went wrong."));
       }
   }

    public function bulk_update_connection($id){
        Log::info("vendor Connection bulk update-delete start : ". $id);
        $data = Input::all();
        if(isset($data['Active'])){
            $data['Active']=1;
        }else{
            $data['Active']=0;
        }
        $company_id = User::get_companyID();
        $username = User::get_user_full_name();
        $is_delete=0;
        if(isset($data['isDelete']) && $data['isDelete']==1){
            $is_delete=1;
        }
        Log::info("***** isDelete=".$is_delete);

        if($data['Action'] == 'bulk'){
            $data['TrunkID']=!empty($data['TrunkID'])?$data['TrunkID']:0;
            $data['IP'] = !empty($data['IP'])?$data['IP']:'';
            $data['ConnectionType'] = !empty($data['ConnectionType'])?$data['ConnectionType']:'';
            $data['Name'] = !empty($data['Name'])?$data['Name']:'';

            try{
                $query = "call prc_VendorConnectionUpdateBySelectedConnectionId (".$company_id.",".$id.",'',".$data['Active'].",".$data['TrunkID'].",'".$data['IP']."','".$data['ConnectionType']."','".$data['Name']."','".$username."',1,".$is_delete.")";
                //echo "==".$query;die;
                Log::info($query);
                DB::statement($query);
                Log::info("vendor Connection bulk update-delete end");
                if($is_delete==1){
                    return Response::json(array("status" => "success", "message" => "Vendor Connection Deleted Successfully"));
                }else{
                    return Response::json(array("status" => "success", "message" => "Vendor Connection Updated Successfully"));
                }

            }catch ( Exception $ex ){
                if($is_delete==1){
                    return Response::json(array("status" => "failed", "message" => "Error Deleting Vendor Connection."));
                }else{
                    return Response::json(array("status" => "failed", "message" => "Error Updating Vendor Connection."));
                }

            }
        }else{
            $ConnectionID= $data['ConnectionID'];
            if(!empty($ConnectionID)){
                try{
                    $query = "call prc_VendorConnectionUpdateBySelectedConnectionId (".$company_id.",".$id.",'".$ConnectionID."',".$data['Active'].",'','','','','".$username."',0,".$is_delete.")";
                   //echo "==".$query;die;
                    Log::info($query);
                    DB::statement($query);
                    Log::info("vendor Connection bulk update-delete end");

                    if($is_delete==1){
                        return Response::json(array("status" => "success", "message" => "Vendor Connection deleted Successfully"));
                    }else{
                        return Response::json(array("status" => "success", "message" => "Vendor Connection Updated Successfully"));
                    }

                }catch ( Exception $ex ){
                    if($is_delete==1){
                        return Response::json(array("status" => "failed", "message" => "Error deleting Vendor Connection."));
                    }else{
                        return Response::json(array("status" => "failed", "message" => "Error Updating Vendor Connection."));
                    }

                }

            }else{

                if($is_delete==1){
                    return Response::json(array("status" => "failed", "message" => "Problem deleting Vendor Connection."));
                }else{
                    return Response::json(array("status" => "failed", "message" => "Problem Updating Vendor Connection."));
                }

            }
        }

    }

}