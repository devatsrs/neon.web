<?php

class ResellerController extends BaseController {

    public function __construct() {

    }


    public function ajax_datagrid(){

       $data = Input::all();

       $companyID = User::get_companyID();
       //$data['Status'] = $data['Status']== 'true'?1:0;

       $resellers = Reseller::leftJoin('tblAccount','tblAccount.AccountID','=','tblReseller.AccountID')
                    ->select(["tblReseller.ResellerName","tblReseller.FirstName","tblReseller.LastName","tblReseller.Email","tblReseller.AccountID","tblReseller.Status","tblReseller.CompanyID","tblReseller.ChildCompanyID","tblReseller.ResellerID","tblAccount.AccountName"])
                    ->where(["tblReseller.CompanyID" => $companyID]);
        if($data['Status']==1){
            $resellers->where(["tblReseller.Status" => 1]);
        }else{
            $resellers->where(["tblReseller.Status" => 0]);
        }

       if(!empty($data['ResellerName'])){
           $resellers->where('ResellerName','like','%'.$data['ResellerName'].'%');
        }
       if(!empty($data['AccountID'])){
           $resellers->where(["tblReseller.AccountID" => $data['AccountID']]);
        }
       
       return Datatables::of($resellers)->make();
    }

    public function index() {
            return View::make('reseller.index', compact(''));

    }

    public function store() {

        $data = Input::all();
        if(!empty($data)){
            $user_id = User::get_userID();
            $CompanyID = User::get_companyID();
            $data['CompanyID'] = $CompanyID;
            $CurrentTime = date('Y-m-d H:i:s');
            $CreatedBy = User::get_user_full_name();
            //$data['Status'] = isset($data['Status']) ? 1 : 0;

            Reseller::$rules['AccountID'] = 'required';
            Reseller::$rules['Email'] = 'required';
            Reseller::$rules['FirstName'] = 'required|min:2';
            Reseller::$rules['LastName'] = 'required|min:2';
            Reseller::$rules['Password'] ='required|confirmed|min:3';
            Reseller::$rules['ResellerName'] = 'required|unique:tblReseller,ResellerName,NULL,CompanyID,CompanyID,'.$data['CompanyID'];

            $validator = Validator::make($data, Reseller::$rules, Reseller::$messages);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            $data['Password'] = Hash::make($data['Password']);

            try {

                $CompanyData = array();
                $CompanyData['CompanyName'] = $data['ResellerName'];
                $CompanyData['CustomerAccountPrefix'] = '22221';
                $CompanyData['FirstName'] = $data['FirstName'];
                $CompanyData['LastName'] = $data['LastName'];
                $CompanyData['Email'] = $data['Email'];
                $CompanyData['Status'] = '1';
                $CompanyData['TimeZone'] = 'Etc/GMT';
                $CompanyData['created_at'] = $CurrentTime;
                $CompanyData['created_by'] = $CreatedBy;

                DB::beginTransaction();

                if ($ChildCompany = Company::create($CompanyData)) {
                    $ChildCompanyID = $ChildCompany->CompanyID;

                    log::info('Child Company ID '.$ChildCompanyID);

                    $UserData = array();
                    $UserData['CompanyID'] = $ChildCompanyID; // new company id
                    $UserData['FirstName'] = $data['FirstName'];
                    $UserData['LastName'] = $data['LastName'];
                    $UserData['EmailAddress'] = $data['Email'];
                    $UserData['password'] = $data['Password'];
                    $UserData['AdminUser'] = '1';
                    $UserData['updated_at'] = $CurrentTime;
                    $UserData['created_at'] = $CurrentTime;
                    $UserData['created_by'] = $CreatedBy;
                    $UserData['Status'] = 1;
                    $FullName = $data['FirstName'] . ' ' . $data['LastName'];
                    $UserData['EmailFooter'] = 'From ,<br><br><b>' . $FullName . '</b><br><br>';
                    $UserData['JobNotification'] = '1';

                    User::create($UserData);

                    $EmailTemplateQuery = "Insert Into tblEmailTemplate(CompanyID,TemplateName,Subject,TemplateBody,created_at,CreatedBy,updated_at,`Type`,EmailFrom,StaticType,SystemType,Status,StatusDisabled,TicketTemplate)
            select '" . $ChildCompanyID . "' as `CompanyID`,TemplateName,Subject,TemplateBody,created_at,'System' as `CreatedBy`,updated_at,`Type`,'test@test.test' as `EmailFrom`,StaticType,SystemType,Status,StatusDisabled,TicketTemplate from tblEmailTemplate where StaticType=1 and CompanyID=" . $CompanyID;

                    DB::statement($EmailTemplateQuery);

                    $CompanyConfigurationQuery = "Insert Into tblCompanyConfiguration(`CompanyID`,`Key`,`Value`)
            select '" . $ChildCompanyID . "' as `CompanyID`,`Key`,`Value` from tblCompanyConfiguration where CompanyID =" . $CompanyID;

                    DB::statement($CompanyConfigurationQuery);

                    $CronjobCommandQuery = "
            Insert Into tblCronJobCommand(`CompanyID`,GatewayID,Title,Command,Settings,Status,created_at,created_by)
            select '" . $ChildCompanyID . "' as `CompanyID`,GatewayID,Title,Command,Settings,Status,created_at,created_by from tblCronJobCommand where CompanyID =" . $CompanyID;

                    DB::statement($CronjobCommandQuery);

                    $ResellerData = array();
                    $ResellerData['ResellerName'] = $data['ResellerName'];
                    $ResellerData['CompanyID'] = $CompanyID;
                    $ResellerData['ChildCompanyID'] = $ChildCompanyID;
                    $ResellerData['AccountID'] = $data['AccountID'];
                    $ResellerData['FirstName'] = $data['FirstName'];
                    $ResellerData['LastName'] = $data['LastName'];
                    $ResellerData['Email'] = $data['Email'];
                    $ResellerData['Password'] = $data['Password'];
                    //$ResellerData['Status'] = $data['Status'];
                    $ResellerData['created_at'] = $CurrentTime;
                    $ResellerData['created_by'] = $CreatedBy;
                    $ResellerData['updated_at'] = $CurrentTime;

                    $Reseller = Reseller::create($ResellerData);
                    DB::commit();
                    if ($Reseller) {
                        log::info('Reseller ID '.$Reseller->ResellerID);
                        return Response::json(array("status" => "success", "message" => "Reseller Successfully Created", 'LastID' => $Reseller->ResellerID, 'newcreated' => $Reseller));
                    }

                }else{
                    return Response::json(array("status" => "failed", "message" => "Problem Creating Reseller."));
                }
            }catch( Exception $e){
                try {
                    DB::rollback();
                } catch (\Exception $err) {
                    Log::error($err);
                }
                Log::error($e);
                return Response::json(array("status" => "failed", "message" => "Problem Creating Reseller."));
            }
        }

    }

    public function update($id) {

        $data = Input::all();

        $Reseller = Reseller::find($id);
        $data['CompanyID'] = User::get_companyID();
        $data['Status'] = isset($data['Status']) ? 1 : 0;
        $CurrentTime = date('Y-m-d H:i:s');
        $CreatedBy = User::get_user_full_name();

        Reseller::$rules['Email'] = 'required';
        Reseller::$rules['FirstName'] = 'required|min:2';
        Reseller::$rules['LastName'] = 'required|min:2';
        Reseller::$rules["ResellerName"] = 'required|unique:tblReseller,ResellerName,'.$id.',ResellerID,CompanyID,'.$data['CompanyID'];

        if(!empty($data['Password']) || !empty($data['Password_confirmation'])){
            Reseller::$rules['Password'] ='required|confirmed|min:3';
        }

        if(!empty($data['Password'])){
            $data['Password'] = Hash::make($data['Password']);
        }else{
            unset($data['Password']);
        }

        $validator = Validator::make($data, Reseller::$rules, Reseller::$messages);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $updatedata = array();
        $ResellerData['ResellerName'] = $data['ResellerName'];
        $ResellerData['FirstName'] = $data['FirstName'];
        $ResellerData['LastName'] = $data['LastName'];
        $ResellerData['Email'] = $data['Email'];
        if(isset($data['Password'])){
            $ResellerData['Password'] = $data['Password'];
        }
        $ResellerData['updated_at'] = $CurrentTime;
        $ResellerData['updated_by'] = $CreatedBy;

        $UserData = array();
        $UserData['FirstName'] = $data['FirstName'];
        $UserData['LastName'] = $data['LastName'];
        $UserData['EmailAddress'] = $data['Email'];
        if(isset($data['Password'])){
            $UserData['Password'] = $data['Password'];
        }
        $UserData['updated_at'] = $CurrentTime;
        $UserData['updated_by'] = $CreatedBy;

        try{
            DB::beginTransaction();

            $User = User::where(['CompanyID'=>$Reseller->ChildCompanyID,'Status'=>1])->first();
            $User->update($UserData);
            $Result = $Reseller->update($ResellerData);
            DB::commit();
            if($Result){
                return  Response::json(array("status" => "success", "message" => "Reseller Successfully Updated"));
            } else {
                return  Response::json(array("status" => "failed", "message" => "Problem Updating Reseller."));
            }

        }catch( Exception $e){
            try {
                DB::rollback();
            } catch (\Exception $err) {
                Log::error($err);
            }
            Log::error($e);
            return Response::json(array("status" => "failed", "message" => "Problem Creating Reseller."));
        }

    }

    public function delete($id){
        return Response::json(array("status" => "failed", "message" => "Reseller is in Use, You can not delete this Reseller."));
        if(Reseller::checkForeignKeyById($id)){
            try{
                $result = Reseller::where(array('ResellerID'=>$id))->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "Reseller Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Reseller."));
                }
            }catch (Exception $ex){
                return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Reseller is in Use, You can not delete this Reseller."));
        }

    }

    public function exports($type){
            $companyID = User::get_companyID();
            $data = Input::all();
            //$data['ServiceStatus']=$data['ServiceStatus']=='true'?1:0;

            $resellers = Reseller::leftJoin('tblAccount','tblAccount.AccountID','=','tblReseller.AccountID')
                ->select(["tblReseller.ResellerName","tblReseller.FirstName","tblReseller.LastName","tblReseller.Email","tblReseller.AccountID","tblReseller.Status","tblReseller.CompanyID","tblReseller.ChildCompanyID","tblReseller.ResellerID","tblAccount.AccountName"])
                ->where(["tblReseller.CompanyID" => $companyID]);
            if($data['Status']==1){
                $resellers->where(["tblReseller.Status" => 1]);
            }else{
                $resellers->where(["tblReseller.Status" => 0]);
            }

            if(!empty($data['ResellerName'])){
                $resellers->where('ResellerName','like','%'.$data['ResellerName'].'%');
            }
            if(!empty($data['AccountID'])){
                $resellers->where(["tblReseller.AccountID" => $data['AccountID']]);
            }

           $resellers = $resellers->get();

            $resellers = json_decode(json_encode($resellers),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID) .'/Resellers.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($resellers);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID) .'/Resellers.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($resellers);
            }

    }

    public function view($id) {

    }
}