<?php


class PackageController extends BaseController {

    private $users;

    public function __construct() {

    }


    public function ajax_datagrid(){
        $data = Input::all();

        $CompanyID = User::get_companyID();
        $packages = Package::leftJoin('tblRateTable','tblPackage.RateTableId','=','tblRateTable.RateTableId')
            ->leftJoin('tblCurrency','tblPackage.CurrencyId','=','tblCurrency.CurrencyId')
            ->select([
                "tblPackage.PackageId",
                "tblPackage.Name",
                "tblRateTable.RateTableName",
                "tblCurrency.Code",
                "tblPackage.RateTableId",
                "tblPackage.CurrencyId"
            ])->where("tblPackage.CompanyID", $CompanyID);


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

            $CompanyID = User::get_companyID();
            $data['CompanyID'] = $CompanyID;
            Package::$rules["Name"] = 'required|unique:tblPackage,Name,NULL,PackageId,CompanyID,'.$CompanyID;

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
        $CompanyID = User::get_companyID();
        $Package = Package::where("CompanyID", $CompanyID)->find($id);
        Package::$rules["Name"] = 'required|unique:tblPackage,Name,'.$id.',PackageId,CompanyID,'.$CompanyID;


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
            $CompanyID = User::get_companyID();
            $packageExist = AccountServicePackage::where("PackageId", $id)->get();
            if($packageExist->count() < 1) {
                $result = Package::where("CompanyID", $CompanyID)
                    ->where(array('PackageId' => $id))->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Package Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Package."));
                }
            } else {
                return Response::json(array("status" => "failed", "message" => "Package is assigned to an account."));
            }
        }catch (Exception $ex){
            return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
        }

    }


    public function bulkDelete(){
        $data = Input::all();
        $validator = Validator::make($data, ['PackageIds' => 'required']);
        $CompanyID = User::get_companyID();

        if ($validator->fails())
            return json_validator_response($validator);

        $bulkIds = explode(",",$data['PackageIds']);

        $packageExist = AccountServicePackage::whereIn('PackageId', $bulkIds)->lists('PackageId');

        $bulkIds = array_diff($bulkIds, $packageExist);
        if(!empty($bulkIds)) {
            $result = Package::where("CompanyID", $CompanyID)
                ->whereIn('PackageId', $bulkIds)->delete();
            if ($result) {
                return Response::json(array("status" => "success", "message" => "Packages Successfully Deleted"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Deleting Packages."));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "Selected Packages are Assigned to Account."));
        }
    }

    public function exports($type){

        $data = Input::all();

        $CompanyID = User::get_companyID();
        $query = Package::leftJoin('tblRateTable','tblPackage.RateTableId','=','tblRateTable.RateTableId')
            ->leftJoin('tblCurrency','tblPackage.CurrencyId','=','tblCurrency.CurrencyId')
            ->select([
                "tblPackage.Name",
                "tblRateTable.RateTableName",
                "tblCurrency.Code as Currency"
            ])->where("tblPackage.CompanyID", $CompanyID);

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
        $rateTypeID = RateType::getRateTypeIDBySlug("package");
        $rateTables = RateTable::where('CurrencyID', $id)
            ->where('Type', $rateTypeID)
            ->where('AppliedTo', "!=", RateTable::APPLIED_TO_VENDOR)
            ->lists("RateTableName", "RateTableId");
        if ($rateTables != false) {
            $rateTables = array('' => "Select") + $rateTables;
            return Response::json($rateTables);
        } else {
            return Response::json([''=>'Select']);
        }
    }
}
