<?php


class TrunkController extends BaseController {

    private $users;

    public function __construct() {

    }


    public function ajax_datagrid(){

       $companyID = User::get_companyID();
       if(isset($_GET['sSearch_0']) && $_GET['sSearch_0'] == ''){
           $trunks = Trunk::select(["Status","Trunk","RatePrefix","AreaPrefix","Prefix","TrunkID"])->where(["CompanyID" => $companyID,"Status"=>1]); // by Default Status 1
       }else{
           $trunks = Trunk::select(["Status","Trunk","RatePrefix","AreaPrefix","Prefix","TrunkID"])->where(["CompanyID" => $companyID]);
       }

       
       return Datatables::of($trunks)->make();
    }

    public function index() {
            return View::make('trunk.list', compact(''));

    }


    public function create() {

            return View::make('trunk.create', array(''));


    }
    public function store() {

        $data = Input::all();
        if(!empty($data)){
            $user_id = User::get_userID();
            $data['CompanyID'] = User::get_companyID();
            $data['Status'] = isset($data['Status']) ? 1 : 0;
            $data['RatePrefix'] = str_replace("_","",$data['RatePrefix']);
            $data['AreaPrefix'] = str_replace("_","",$data['AreaPrefix']);
            $data['Prefix']     = str_replace("_","",$data['Prefix']);
            Trunk::$rules['Trunk'] = 'required|unique:tblTrunk,Trunk,NULL,CompanyID,CompanyID,'.$data['CompanyID'];

            $validator = Validator::make($data, Trunk::$rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if($trunk = Trunk::create($data)){
                Cache::forget("trunks_defaults");
                return  Response::json(array("status" => "success", "message" => "Trunk Successfully Created",'LastID'=>$trunk->TrunkID,'newcreated'=>$trunk));
            } else {
                return  Response::json(array("status" => "failed", "message" => "Problem Creating Trunk."));
            }

        }



    }


    public function edit($id) {
            $trunk = Trunk::find($id);
            return View::make('trunk.edit')->with(["trunk" => $trunk]);

    }

    public function update($id) {

        $data = Input::all();
        $trunk = Trunk::find($id);
        $data['CompanyID'] = User::get_companyID();
        $data['Status'] = isset($data['Status']) ? 1 : 0;

        $data['RatePrefix'] = str_replace("_","",$data['RatePrefix']);
        $data['AreaPrefix'] = str_replace("_","",$data['AreaPrefix']);
        $data['Prefix']     = str_replace("_","",$data['Prefix']);


        Trunk::$rules["Trunk"] = 'required|unique:tblTrunk,Trunk,'.$id.',TrunkID,CompanyID,'.$data['CompanyID'];


        $validator = Validator::make($data, Trunk::$rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if($trunk->update($data)){
                Cache::forget("trunks_defaults");
              return  Response::json(array("status" => "success", "message" => "Trunk Successfully Updated"));
        } else {
            return  Response::json(array("status" => "failed", "message" => "Problem Updating Trunk."));
        }

    }

    public function exports($type){
            $companyID = User::get_companyID();
            $data = Input::all();
            if (isset($data['sSearch_0']) && ($data['sSearch_0'] == '' || $data['sSearch_0'] == '1')) {
                $trunks = Trunk::where(["CompanyID" => $companyID, "Status" => 1])->orderBy("TrunkID", "desc")->get(["Trunk", "RatePrefix", "AreaPrefix", "Prefix"]);
            } else {
                $trunks = Trunk::where(["CompanyID" => $companyID, "Status" => 0])->orderBy("TrunkID", "desc")->get(["Trunk", "RatePrefix", "AreaPrefix", "Prefix"]);
            }
            $trunks = json_decode(json_encode($trunks),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Trunks.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($trunks);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Trunks.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($trunks);
            }

    }
}
