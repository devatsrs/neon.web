<?php


class ServicesController extends BaseController {

    private $users;

    public function __construct() {

    }


    public function ajax_datagrid(){

       $data = Input::all();

       $companyID = User::get_companyID();
       $data['ServiceStatus'] = $data['ServiceStatus']== 'true'?1:0;

       $services = Service::leftJoin('tblCompanyGateway','tblService.CompanyGatewayID','=','tblCompanyGateway.CompanyGatewayID')
            ->select(["tblService.Status","tblService.ServiceName","tblService.ServiceType","tblCompanyGateway.Title","tblService.ServiceID","tblService.CompanyGatewayID"])
            ->where(["tblService.CompanyID" => $companyID]);
        if($data['ServiceStatus']==1){
            $services->where(["tblService.Status" => 1]);
        }else{
            $services->where(["tblService.Status" => 0]);
        }

       if(!empty($data['ServiceName'])){
           $services->where('tblService.ServiceName','like','%'.$data['ServiceName'].'%');
        }
       if(!empty($data['CompanyGatewayID'])){
           $services->where(["tblService.CompanyGatewayID" => $data['CompanyGatewayID']]);
        }

       /*
       $companyID = User::get_companyID();
       if(isset($_GET['sSearch_0']) && $_GET['sSearch_0'] == ''){
           $services = Service::leftJoin('tblCompanyGateway','tblService.CompanyGatewayID','=','tblCompanyGateway.CompanyGatewayID')
                        ->select(["tblService.Status","tblService.ServiceName","tblService.ServiceType","tblCompanyGateway.Title","tblService.ServiceID","tblService.CompanyGatewayID"])->where(["tblService.CompanyID" => $companyID,"tblService.Status"=>1]); // by Default Status 1
       }else{
           $services = Service::leftJoin('tblCompanyGateway','tblService.CompanyGatewayID','=','tblCompanyGateway.CompanyGatewayID')
               ->select(["tblService.Status","tblService.ServiceName","tblService.ServiceType","tblCompanyGateway.Title","tblService.ServiceID","tblService.CompanyGatewayID"])->where(["tblService.CompanyID" => $companyID]); // by Default Status 1
       }*/

       
       return Datatables::of($services)->make();
    }

    public function index() {
            return View::make('service.index', compact(''));

    }

    public function store() {

        $data = Input::all();
        if(!empty($data)){
            $user_id = User::get_userID();
            $data['CompanyID'] = User::get_companyID();
            $data['Status'] = isset($data['Status']) ? 1 : 0;

            Service::$rules['ServiceType'] = 'required';
            Service::$rules['ServiceName'] = 'required|unique:tblService,ServiceName,NULL,CompanyID,CompanyID,'.$data['CompanyID'];

            $validator = Validator::make($data, Service::$rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if($Service = Service::create($data)){
                return  Response::json(array("status" => "success", "message" => "Service Successfully Created",'LastID'=>$Service->ServiceID,'newcreated'=>$Service));
            } else {
                return  Response::json(array("status" => "failed", "message" => "Problem Creating Service."));
            }

        }



    }

    public function update($id) {

        $data = Input::all();
        $Service = Service::find($id);
        $data['CompanyID'] = User::get_companyID();
        $data['Status'] = isset($data['Status']) ? 1 : 0;

        Service::$rules["ServiceName"] = 'required|unique:tblService,ServiceName,'.$id.',ServiceID,CompanyID,'.$data['CompanyID'];


        $validator = Validator::make($data, Service::$rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if($Service->update($data)){
              return  Response::json(array("status" => "success", "message" => "Service Successfully Updated"));
        } else {
            return  Response::json(array("status" => "failed", "message" => "Problem Updating Service."));
        }

    }

    public function delete($id){
        if(Service::checkForeignKeyById($id)){
            try{
                $result = Service::where(array('ServiceID'=>$id))->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Service Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Service."));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Service is in Use, You can not delete this Service."));
        }

    }

    public function exports($type){
            $companyID = User::get_companyID();
            $data = Input::all();
            $data['ServiceStatus']=$data['ServiceStatus']=='true'?1:0;

            $query = Service::leftJoin('tblCompanyGateway','tblService.CompanyGatewayID','=','tblCompanyGateway.CompanyGatewayID')
                    ->select(["tblService.ServiceName","tblService.ServiceType","tblCompanyGateway.Title as Gateway","tblService.ServiceID",])
                    ->orderBy("tblService.ServiceName", "ASC")
                    ->where("tblService.CompanyID","=",$companyID);
            if(isset($data['ServiceStatus']) && $data['ServiceStatus'] == '1') {
                            $query->where("tblService.Status","=",1);
            }else{
                $query->where("tblService.Status","=",0);
            }
            $services = $query->get();

            $services = json_decode(json_encode($services),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Services.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($services);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Services.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($services);
            }

    }
}
