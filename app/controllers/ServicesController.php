<?php


class ServicesController extends BaseController {

    private $users;

    public function __construct() {

    }


    public function ajax_datagrid(){

       $companyID = User::get_companyID();
       if(isset($_GET['sSearch_0']) && $_GET['sSearch_0'] == ''){
           $services = Service::select(["Status","ServiceName","ServiceType","ServiceID"])->where(["CompanyID" => $companyID,"Status"=>1]); // by Default Status 1
       }else{
           $services = Service::select(["Status","ServiceName","ServiceType","ServiceID"])->where(["CompanyID" => $companyID]);
       }

       
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
        if(Service::where(["ServiceID" => $id])->delete()){
            return Response::json(array("status" => "success", "message" => "Service Successfully Deleted"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Service."));
        }
    }

    public function exports($type){
            $companyID = User::get_companyID();
            $data = Input::all();
            if (isset($data['sSearch_0']) && ($data['sSearch_0'] == '' || $data['sSearch_0'] == '1')) {
                $services = Service::where(["CompanyID" => $companyID, "Status" => 1])->orderBy("ServiceID", "desc")->get(["ServiceID","ServiceName", "ServiceTYpe"]);
            } else {
                $services = Service::where(["CompanyID" => $companyID, "Status" => 0])->orderBy("ServiceID", "desc")->get(["ServiceID","ServiceName", "ServiceTYpe"]);
            }
            $services = json_decode(json_encode($services),true);
            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Services.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($services);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Services.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($services);
            }

    }
}
