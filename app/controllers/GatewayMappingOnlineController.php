<?php

class GatewayMappingOnlineController extends \BaseController {

    public function index()
    {
        $id=0;
        $companyID = User::get_companyID();

        $accounts = Account::getAccountIDList();
        $trunks = Trunk::getTrunkDropdownIDList($companyID);
        $trunks = $trunks + array('Other'=>'Other');
        $Services = Service::getDropdownIDList($companyID);
        $gateway = CompanyGateway::getCompanyGatewayIdList($companyID);

        return View::make('GatewayMappingOnline.index', compact('accounts','trunks','Services','gateway'));
    }

    public function ajax_datagrid($type)
	{
        $data 							 = 		Input::all();

        $CompanyID 						 = 		User::get_companyID();
        $UserActilead                    =       UserActivity::UserActivitySaved($data,'View','Gateway Mapping Online');
        $data['iDisplayStart'] 			+=		1;
        $data['GatewayName'] 				 = 		$data['GatewayName']?$data['GatewayName']:'';

        $data['CompanyGatewayID']				 =		$data['CompanyGatewayID']!= ''?$data['CompanyGatewayID']:0;

        $columns = array('GatewayName','TotalCurrentCalls','Asr','Acd','RemoteIP','CompanyGatewayID');
        $sort_column = $columns[$data['iSortCol_0']];


        $query = "call prc_getVOSGatewayMappingOnline(".$CompanyID.",'".$data['GatewayName']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',".$data['CompanyGatewayID']."";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $export_type['type'] = $type;
            $UserActilead = UserActivity::UserActivitySaved($export_type,'Export','GatewayMappingOnline');

            $excel_data  = DB::connection('sqlsrvcdr')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/GatewayMappingOnline.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/GatewayMappingOnline.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query,'sqlsrvcdr')->make();
    }


    public function GetGatewayMappingOnline(){

        $GatewayID = Gateway::getGatewayID(Gateway::GATEWAY_VOS5000);
        $CompanyID=User::get_companyID();
        $CompanyGateways = CompanyGateway::where(['GatewayID'=>$GatewayID,'Status'=>1])->get();
        $Message=array();
        $PostData=array();
        $PostData['accounts']="";
        foreach($CompanyGateways as $CompanyGateway){
            $CompanyGatewayID=$CompanyGateway->CompanyGatewayID;
            $CompanyGatewayTitle=$CompanyGateway->Title;

            $Res=VOS5000API::request('GetGatewayMappingOnline',$CompanyGatewayID,$CompanyGatewayTitle,$PostData);

            if(!empty($Res->infoGatewayMappingOnlines)){
                Log::info("Total Record=".count($Res->infoGatewayMappingOnlines));

                $VendorActiveCall=array();
                try{

                    foreach($Res->infoGatewayMappingOnlines as $val){
                        $data=array();
                        //Log::info(print_r($val->callerE164,true));die;
                        $data['GatewayName'] = $val->name;
                        //$data['CallPrefix'] = $val->prefix;
                        $data['TotalCurrentCalls'] = $val->currentCall;
                        $data['Asr'] = $val->asr;
                        $data['Acd'] = $val->acd;
                        $data['RemoteIP'] = $val->remoteIps;
                        $data['CompanyGatewayID'] = $CompanyGatewayID;
                        $data['CompanyID'] = $CompanyID;
                        $data['created_at'] = date('Y-m-d H:i:s');
                        $data['created_by'] = 'API';

                        array_push($VendorActiveCall,$data);
                    }

                    if(!empty($VendorActiveCall) && count($VendorActiveCall) > 0){
                        DB::connection('sqlsrvcdr')->beginTransaction();

                        VOSGatewayMappingOnline::where('CompanyID',$CompanyID)->delete();
                        Log::info("Count Insert=".count($VendorActiveCall));
                        //Log::info(print_r($VendorActiveCall,true));

                        foreach (array_chunk($VendorActiveCall,1500) as $vac) {
                            VOSGatewayMappingOnline::insert($vac);
                        }

                        DB::connection('sqlsrvcdr')->commit();
                        $UserActilead = UserActivity::UserActivitySaved($data,'Import','Vendor Active Calls');
                        return Response::json(['status'=>'success','message'=>"Successfully imported ".count($VendorActiveCall)]);
                    }else{
                        return Response::json(['status'=>'failed','message'=>"No Records Found."]);
                    }

                }catch(Exception $e){
                    DB::connection('sqlsrvcdr')->rollback();
                    Log::info("======== Exception Generated ========");
                    Log::info($e->getMessage());
                    return Response::json(['status'=>'failed','message'=>$e->getMessage()]);
                }

            }else{
                //return $Res;
                $Message[]=$Res;
            }
        }

        return Response::json(['status'=>'failed','message'=>"No Any Active Company Gateway Found For ".Gateway::GATEWAY_VOS5000."."]);

    }


}