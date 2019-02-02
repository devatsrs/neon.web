<?php


class PackageController extends BaseController {

    private $users;

    public function __construct() {

    }


    public function ajax_datagrid(){

        $data = Input::all();

        $packages = Package::leftJoin('tblRateTable','tblPackage.RateTableId','=','tblRateTable.RateTableId')
            ->leftJoin('tblCurrency','tblPackage.CurrencyId','=','tblCurrency.CurrencyId')
            ->select([
                "tblPackage.PackageId",
                "tblPackage.Name",
                "tblRateTable.RateTableName",
                "tblCurrency.Code",
                "tblPackage.RateTableId",
                "tblPackage.CurrencyId"
            ]);


        if(!empty($data['PackageName'])){
            $packages->where('tblPackage.Name','like','%'.$data['PackageName'].'%');
        }

        if(!empty($data['CurrencyId'])){
            $packages->where(["tblCurrency.CurrencyId" => $data['CurrencyId']]);
        }

        return Datatables::of($packages)->make();
    }

    public function index() {
        $rateTables = RateTable::lists("RateTableName", "RateTableId");
        $rateTables = array('' => "Select") + $rateTables;
        $CompanyID  = User::get_companyID();
        $defaultCurrencyId = Company::getCompanyField($CompanyID, "CurrencyId");

        return View::make('package.index', compact('rateTables', 'defaultCurrencyId','currencyDropdown'));
    }

    public function store() {

        $data = Input::all();
        if(!empty($data)){

            Package::$rules['Name'] = 'required|unique:tblPackage,Name';

            $validator = Validator::make($data, Package::$rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if($Package = Package::create($data)){
                return  Response::json(array("status" => "success", "message" => "Package Successfully Created",'LastID'=>$Package->PackageId,'newcreated'=>$Package));
            } else {
                return  Response::json(array("status" => "failed", "message" => "Problem Creating Package."));
            }

        }
    }

    public function update($id) {

        $data = Input::all();
        $Package = Package::find($id);
        Package::$rules["Name"] = 'required|unique:tblPackage,Name,'.$id.',PackageId';


        $validator = Validator::make($data, Package::$rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if($Package->update($data)){
            return  Response::json(array("status" => "success", "message" => "Package Successfully Updated"));
        } else {
            return  Response::json(array("status" => "failed", "message" => "Problem Updating Package."));
        }
    }

    public function delete($id){
        try{
            $result = Package::where(array('PackageId'=>$id))->delete();
            if ($result) {
                return Response::json(array("status" => "success", "message" => "Package Successfully Deleted"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Deleting Package."));
            }
        }catch (Exception $ex){
            return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
        }

    }


    public function bulkDelete(){
        $data = Input::all();
        $validator = Validator::make($data, ['PackageIds' => 'required']);

        if ($validator->fails())
            return json_validator_response($validator);

        $bulkIds = explode(",",$data['PackageIds']);
        $result = Package::whereIn('PackageId', $bulkIds)->delete();
        if ($result) {
            return Response::json(array("status" => "success", "message" => "Packages Successfully Deleted"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Packages."));
        }
    }

    public function exports($type){

        $data = Input::all();

        $query = Package::leftJoin('tblRateTable','tblPackage.RateTableId','=','tblRateTable.RateTableId')
            ->leftJoin('tblCurrency','tblPackage.CurrencyId','=','tblCurrency.CurrencyId')
            ->select([
                "tblPackage.Name",
                "tblRateTable.RateTableName",
                "tblCurrency.Code as Currency"
            ]);


        if(!empty($data['PackageName'])){
            $query->where('tblPackage.Name','like','%'.$data['PackageName'].'%');
        }

        if(!empty($data['CurrencyId'])){
            $query->where(["tblCurrency.CurrencyId" => $data['CurrencyId']]);
        }

        $packages = $query->get();

        $packages = json_decode(json_encode($packages),true);
        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Packages.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($packages);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Packages.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($packages);
        }

    }


    public function getRateTableFromCurrencyId($id){
            $rateTables = RateTable::where('CurrencyID', $id)
                ->where('Type', 3)
                ->where('AppliedTo', "!=", 2)
                ->lists("RateTableName", "RateTableId");
            if ($rateTables != false) {
                $rateTables = array('' => "Select") + $rateTables;
                return Response::json($rateTables);
            } else {
                return Response::json([''=>'Select']);
            }
    }
}
