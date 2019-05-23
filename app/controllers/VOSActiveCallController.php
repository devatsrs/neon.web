<?php

class VOSActiveCallController extends \BaseController {

    public function index()
    {
        $data = array();
        $VOSActiveCallsActilead = UserActivity::UserActivitySaved($data,'View','VOS Active Calls');
        return View::make('VOSActiveCall.index');
    }

    public function ajax_datagrid($type)
	{
        $data 							 = 		Input::all();

        $CompanyID 						 = 		User::get_companyID();
        $data['iDisplayStart'] 			+=		1;
        $data['CLI']				     =		$data['CLI']!= ''?$data['CLI']:'';
        $data['CLD']				     =		$data['CLD']!= ''?$data['CLD']:'';
        $data['MappingGateway']			 =	$data['MappingGateway']!= ''?$data['MappingGateway']:'';
        $data['RoutingGateway']			 =	$data['RoutingGateway']!= ''?$data['RoutingGateway']:'';


        $columns = array('CLI','CLD','MappingGateway','RoutingGateway','CallerPDD','CalleePDD','ConnectTime','Duration','Codec','CustomerIP','SupplierIPRTP');
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_getVOSActiveCalls(".$CompanyID.",'".$data['CLI']."','".$data['CLD']."','".$data['MappingGateway']."','".$data['RoutingGateway']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrvcdr')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/ActiveCall.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/ActiveCall.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
        }
        $query .=',0)';

        return DataTableSql::of($query,'sqlsrvcdr')->make();
    }


    public function GetCurrentCall(){
        $GatewayID = Gateway::getGatewayID(Gateway::GATEWAY_VOS5000);
        $CompanyID=User::get_companyID();
        $CompanyGateways = CompanyGateway::where(['GatewayID'=>$GatewayID,'Status'=>1])->get();
        $Message=array();
        $PostData=array();
        $PostData['accounts']="";
        foreach($CompanyGateways as $CompanyGateway){
            $CompanyGatewayID=$CompanyGateway->CompanyGatewayID;
            $CompanyGatewayTitle=$CompanyGateway->Title;

            $Res=VOS5000API::request('GetCurrentCall',$CompanyGatewayID,$CompanyGatewayTitle,$PostData);

            if(!empty($Res->infoCurrentCalls)){
                Log::info("Total Record=".count($Res->infoCurrentCalls));
                $ActiveCallData=array();
                try{

                    foreach($Res->infoCurrentCalls as $val){
                        $data=array();
                        //Log::info(print_r($val,true));die;
                        $data['CLI'] = $val->callerE164;
                        $data['CLD'] = $val->calleeE164;
                        $data['ConnectTime'] = date('Y-m-d H:i:s',$val->connectedTime/1000);
                        $data['MappingGateway'] = !empty($val->callerGatewayId)?$val->callerGatewayId:'';
                        $data['RoutingGateway'] = !empty($val->calleeGatewayId)?$val->calleeGatewayId:'';
                        $data['CallerPDD'] = $val->callerPdd;
                        $data['CalleePDD'] = $val->calleePdd;
                        $data['Duration'] = $val->keepTime;
                        $data['Codec'] = $val->callCodec;
                        $data['CustomerIP'] = $val->callerRtpIp;
                        $data['SupplierIPRTP'] = $val->calleeRtpIp;
                        $data['CompanyGatewayID'] = $CompanyGatewayID;
                        $data['CompanyID'] = $CompanyID;
                        $data['created_at'] = date('Y-m-d H:i:s');
                        $data['created_by'] = 'API';

                        //Log::info(print_r($data,true));die;
                        array_push($ActiveCallData,$data);
                    }

                    if(!empty($ActiveCallData) && count($ActiveCallData) > 0){
                        DB::connection('sqlsrvcdr')->beginTransaction();

                        ActiveCall::where('CompanyID',$CompanyID)->delete();
                        Log::info("Count Insert=".count($ActiveCallData));

                        foreach (array_chunk($ActiveCallData,1500) as $t) {
                            ActiveCall::insert($t);
                        }

                        DB::connection('sqlsrvcdr')->commit();
                        $VOSActiveCallsActilead = UserActivity::UserActivitySaved($data,'Import','VOS Active Calls');
                        return Response::json(['status'=>'success','message'=>"Successfully imported ".count($ActiveCallData)]);
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
                Log::info("==== infoCurrentCalls Not Found====");
                Log::info(print_r($Res,true));
                return Response::json(['status'=>'failed','message'=>"Something went wrong."]);
            }
        }

        return Response::json(['status'=>'failed','message'=>"No Any Active Company Gateway Found For ".Gateway::GATEWAY_VOS5000."."]);


    }


}