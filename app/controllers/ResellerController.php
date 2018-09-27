<?php
use Illuminate\Support\Facades\Crypt;
class ResellerController extends BaseController {

    public function __construct() {

    }


    public function ajax_datagrid(){

       $data = Input::all();

       $companyID = User::get_companyID();
       //$data['Status'] = $data['Status']== 'true'?1:0;

       $resellers = Reseller::leftJoin('tblAccount','tblAccount.AccountID','=','tblReseller.AccountID')
                    ->select(["tblReseller.ResellerID","tblAccount.AccountName","tblReseller.Email",DB::raw("(select count(*) from tblAccount a where a.CompanyId=tblReseller.ChildCompanyID) as NumberOfAccount"),"tblReseller.AccountID","tblReseller.Status","tblReseller.AllowWhiteLabel","tblReseller.CompanyID","tblReseller.ChildCompanyID"])
                    ->where(["tblReseller.CompanyID" => $companyID]);
        if($data['Status']==1){
            $resellers->where(["tblReseller.Status" => 1]);
        }else{
            $resellers->where(["tblReseller.Status" => 0]);
        }

       if(!empty($data['AccountID'])){
           $resellers->where(["tblReseller.AccountID" => $data['AccountID']]);
        }
       
       return Datatables::of($resellers)->make();
    }

    public function index() {
        $CompanyID = User::get_companyID();
        $Products = Product::getProductDropdownList($CompanyID,BillingSubscription::Reseller);
        //$BillingSubscription = array(""=> "Select") + BillingSubscription::getSubscriptionsListByAppliedTo($CompanyID,BillingSubscription::Reseller);
        $BillingSubscription = array(""=> "Select") + BillingSubscription::getSubscriptionsList($CompanyID,BillingSubscription::Reseller);
        $Trunks = Trunk::getTrunkDropdownIDList($CompanyID);
        return View::make('reseller.index', compact('Products','BillingSubscription','Trunks'));

    }

    public function store() {
        $data = Input::all();

        $items = empty($data['reseller-item']) ? '' : array_filter($data['reseller-item']);
        $subscriptions = empty($data['reseller-subscription']) ? '' : array_filter($data['reseller-subscription']);
        //$trunks = empty($data['reseller-trunk']) ? '' : array_filter($data['reseller-trunk']);
        $trunks='';
        $is_product = 0;
        $is_subscription = 0;
        $is_trunk = 0;
        $productids = '';
        $subscriptionids = '';
        $trunkids = '';
        if(!empty($items)){
            $is_product  = 1;
            $productids=implode(',',$items);
        }
        if(!empty($subscriptions)){
            $is_subscription = 1;
            $subscriptionids=implode(',',$subscriptions);
        }
        if(!empty($trunks)){
            $is_trunk = 1;
            $trunkids=implode(',',$trunks);
        }

        if(!empty($data)){
            $user_id = User::get_userID();
            $CompanyID = User::get_companyID();
            $data['CompanyID'] = $CompanyID;
            $CurrentTime = date('Y-m-d H:i:s');
            $CreatedBy = User::get_user_full_name();
            if(empty($CreatedBy)){
                $CreatedBy = 'system';
            }
            //$data['Status'] = isset($data['Status']) ? 1 : 0;

            Reseller::$rules['AccountID'] = 'required|unique:tblReseller,AccountID';
            Reseller::$rules['Email'] = 'required|email';
            Reseller::$rules['Password'] ='required|min:3';


            $validator = Validator::make($data, Reseller::$rules, Reseller::$messages);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            //$data['Password'] = Hash::make($data['Password']);
            $data['Password'] = Crypt::encrypt($data['Password']);

            $Account = Account::find($data['AccountID']);
            $data['AllowWhiteLabel'] = isset($data['AllowWhiteLabel']) ? 1 : 0;
            $AccountID = $data['AccountID'];
            $Email = $data['Email'];
            $Password = $data['Password'];
            $AllowWhiteLabel = $data['AllowWhiteLabel'];
            $AccountName = $Account->AccountName;
            if(!empty($Account->FirstName) && !empty($Account->LastName)){
                $FirstName = empty($Account->FirstName) ? '' : $Account->FirstName;
                $LastName =  empty($Account->LastName)  ? '' : $Account->LastName;
            }else{
                $FirstName = $AccountName;
                $LastName = 'Reseller';
            }

            if(!empty($data['AllowWhiteLabel'])){
                if(empty($data['DomainUrl'])){
                    $data['DomainUrl'] = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'WEB_URL'])->pluck('Value');
                }
                if(!Reseller::IsAllowDomainUrl($data['DomainUrl'],'')){
                    return  Response::json(array("status" => "failed", "message" => "please setup different domain for your reseller."));
                }
            }

            try {

                $CompanyData = array();
                $CompanyData['CompanyName'] = $AccountName;
                $CompanyData['CustomerAccountPrefix'] = '22221';
                $CompanyData['FirstName'] = $FirstName;
                $CompanyData['LastName'] = $LastName;
                $CompanyData['Email'] = $data['Email'];
                $CompanyData['Status'] = '1';
                $CompanyData['TimeZone'] = 'Etc/GMT';
                $CompanyData['created_at'] = $CurrentTime;
                $CompanyData['created_by'] = $CreatedBy;

                DB::beginTransaction();

                if ($ChildCompany = Company::create($CompanyData)) {
                    $ChildCompanyID = $ChildCompany->CompanyID;

                    log::info('Child Company ID '.$ChildCompanyID);

                    $JobStatusMessage = DB::select("CALL  prc_insertResellerData ($CompanyID,$ChildCompanyID,'".$AccountName."','".$FirstName."','".$LastName."',$AccountID,'".$Email."','".$Password."',$is_product,'".$productids."',$is_subscription,'".$subscriptionids."',$is_trunk,'".$trunkids."',$AllowWhiteLabel)");
                    Log::info("CALL  prc_insertResellerData ($CompanyID,$ChildCompanyID,'".$AccountName."','".$FirstName."','".$LastName."',$AccountID,'".$Email."','".$Password."',$is_product,'".$productids."',$is_subscription,'".$subscriptionids."',$is_trunk,'".$trunkids."')");
                    Log::info($JobStatusMessage);

                    if(count($JobStatusMessage)){
                        throw  new \Exception($JobStatusMessage[0]->Message);
                    }else{
                        if(!empty($data['DomainUrl'])){
                            $DomainUrl = rtrim($data['DomainUrl'],"/");
                            CompanyConfiguration::where(['Key'=>'WEB_URL','CompanyID'=>$ChildCompany->CompanyID])->update(['Value'=>$DomainUrl]);
                        }else{
                            $ResellerDomain = CompanyConfiguration::where(['CompanyID'=>$CompanyID,'Key'=>'WEB_URL'])->pluck('Value');
                            CompanyConfiguration::where(['Key'=>'WEB_URL','CompanyID'=>$ChildCompany->CompanyID])->update(['Value'=>$ResellerDomain]);
                        }
                        CompanyGateway::createDefaultCronJobs($ChildCompanyID);
                        DB::commit();
                        return Response::json(array("status" => "success", "message" => "Reseller Successfully Created" ));
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
        $data['AllowWhiteLabel'] = isset($data['AllowWhiteLabel']) ? 1 : 0;
        $CurrentTime = date('Y-m-d H:i:s');
        $CreatedBy = User::get_user_full_name();

        Reseller::$rules['Email'] = 'required|email';
        //Reseller::$rules['FirstName'] = 'required|min:2';
        //Reseller::$rules['LastName'] = 'required|min:2';
        //Reseller::$rules["ResellerName"] = 'required|unique:tblReseller,ResellerName,'.$id.',ResellerID,CompanyID,'.$data['CompanyID'];

        $Account = Account::find($data['UpdateAccountID']);
        $AccountName = $Account->AccountName;
        if(!empty($Account->FirstName) && !empty($Account->LastName)){
            $FirstName = empty($Account->FirstName) ? '' : $Account->FirstName;
            $LastName =  empty($Account->LastName)  ? '' : $Account->LastName;
        }else{
            $FirstName = $AccountName;
            $LastName = 'Reseller';
        }

        if(!empty($data['Password'])){
            Reseller::$rules['Password'] ='required|min:3';
        }

        if(!empty($data['Password'])){
           // $data['Password'] = Hash::make($data['Password']);
            $data['Password'] = Crypt::encrypt($data['Password']);
        }else{
            unset($data['Password']);
        }

        $validator = Validator::make($data, Reseller::$rules, Reseller::$messages);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if(!empty($data['AllowWhiteLabel'])){
            if(empty($data['DomainUrl'])){
                $data['DomainUrl'] = CompanyConfiguration::where(['CompanyID'=>$data['CompanyID'],'Key'=>'WEB_URL'])->pluck('Value');
            }
            if(!Reseller::IsAllowDomainUrl($data['DomainUrl'],$id)){
                return  Response::json(array("status" => "failed", "message" => "please setup different domain for your reseller."));
            }
        }

        $updatedata = array();
        $ResellerData['ResellerName'] = $AccountName;
        $ResellerData['FirstName'] = $FirstName;
        $ResellerData['LastName'] = $LastName;
        $ResellerData['Email'] = $data['Email'];
        $ResellerData['AllowWhiteLabel'] = $data['AllowWhiteLabel'];
        if(isset($data['Password'])){
            $ResellerData['Password'] = $data['Password'];
        }
        $ResellerData['updated_at'] = $CurrentTime;
        $ResellerData['updated_by'] = $CreatedBy;

        $UserData = array();
        $UserData['EmailAddress'] = $data['Email'];
        if(isset($data['Password'])){
            $UserData['Password'] = $data['Password'];
        }
        $UserData['updated_at'] = $CurrentTime;
        $UserData['updated_by'] = $CreatedBy;

        try{
            DB::beginTransaction();

            $User = User::where(['CompanyID'=>$Reseller->ChildCompanyID,'Status'=>1])->first();
            if(empty($User->FirstName) && empty($User->LastName)){
                $UserData['FirstName'] = $FirstName;
                $UserData['LastName'] = $LastName;
            }
            $User->update($UserData);
            $Result = $Reseller->update($ResellerData);
            DB::commit();
            if($Result){
                if(!empty($data['DomainUrl'])){
                    $DomainUrl = rtrim($data['DomainUrl'],"/");
                    CompanyConfiguration::where(['Key'=>'WEB_URL','CompanyID'=>$Reseller->ChildCompanyID])->update(['Value'=>$DomainUrl]);
                }else{
                    $ResellerDomain = CompanyConfiguration::where(['CompanyID'=>$Reseller->CompanyID,'Key'=>'WEB_URL'])->pluck('Value');
                    CompanyConfiguration::where(['Key'=>'WEB_URL','CompanyID'=>$Reseller->ChildCompanyID])->update(['Value'=>$ResellerDomain]);
                }
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
                //->select([DB::raw("tblAccount.AccountName as `Reseller Account`, tblReseller.Email as `User Name`")])
                ->select(DB::raw("tblAccount.AccountName as `Reseller Account`, tblReseller.Email as `User Name`"),DB::raw("(select count(*) from tblAccount a where a.CompanyId=tblReseller.ChildCompanyID and a.IsCustomer=1) as NumberOfAccount"),DB::raw("(CASE WHEN(tblReseller.AllowWhiteLabel != 0) THEN 'yes' ELSE 'no' END) as AllowWhiteLabel"))
                //->select(["tblAccount.AccountName as `Reseller Account`, tblReseller.Email as `User Name`"])
                ->where(["tblReseller.CompanyID" => $companyID]);
            if($data['Status']==1){
                $resellers->where(["tblReseller.Status" => 1]);
            }else{
                $resellers->where(["tblReseller.Status" => 0]);
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

    public function bulkcopydata(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $items = empty($data['reseller-item']) ? '' : array_filter($data['reseller-item']);
        $subscriptions = empty($data['reseller-subscription']) ? '' : array_filter($data['reseller-subscription']);
        //$trunks = empty($data['reseller-trunk']) ? '' : array_filter($data['reseller-trunk']);
        $trunks='';

        $is_product = 0;
        $is_subscription = 0;
        $is_trunk = 0;
        $productids = '';
        $subscriptionids = '';
        $trunkids = '';
        if(!empty($items)){
            $is_product  = 1;
            $productids=implode(',',$items);
        }
        if(!empty($subscriptions)){
            $is_subscription = 1;
            $subscriptionids=implode(',',$subscriptions);
        }
        if(!empty($trunks)){
            $is_trunk = 1;
            $trunkids=implode(',',$trunks);
        }

        if(empty($items) && empty($subscriptions) && empty($trunks)){
            return Response::json(array("status" => "failed", "message" => "Please Select Copy Data."));
        }

        if(!empty($data['criteria'])){
            $resellerid = $this->getResellerIdByCriteria($data);
            $resellerid = rtrim($resellerid,',');
            $data['ResellerIDs'] = $resellerid;
            unset($data['criteria']);
        }
        else{
            unset($data['criteria']);
        }
        $ResellerIDs = $data['ResellerIDs'];
        if(!empty($ResellerIDs)) {
            try {

                DB::beginTransaction();


                $JobStatusMessage = DB::select("CALL  prc_copyResellerData ($CompanyID,'".$ResellerIDs."',$is_product,'".$productids."',$is_subscription,'".$subscriptionids."',$is_trunk,'".$trunkids."')");
                Log::info("CALL  prc_copyResellerData ($CompanyID,'".$ResellerIDs."',$is_product,'".$productids."',$is_subscription,'".$subscriptionids."',$is_trunk,'".$trunkids."')");
                Log::info($JobStatusMessage);

                if(count($JobStatusMessage)){
                    throw  new \Exception($JobStatusMessage[0]->Message);
                }else{
                    DB::commit();
                    return Response::json(array("status" => "success", "message" => "Reseller Data Copied" ));
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


        }else{
            return Response::json(array("status" => "failed", "message" => "Please Select Reseller Account."));
        }


    }

    public function getResellerIdByCriteria($data){
        $companyID = User::get_companyID();

        $ResellerIDs = '';

        $criteria = json_decode($data['criteria'],true);

        $resellers = Reseller::where(["tblReseller.CompanyID" => $companyID]);
        if($criteria['Status']==1){
            $resellers->where(["tblReseller.Status" => 1]);
        }else{
            $resellers->where(["tblReseller.Status" => 0]);
        }
        if(!empty($criteria['AccountID'])){
            $resellers->where(["tblReseller.AccountID" => $criteria['AccountID']]);
        }
        $Resellerdata = $resellers->get();
        if(!empty($Resellerdata)){
            foreach($Resellerdata as $rdata){
                $ResellerIDs.= $rdata->ResellerID.',';
            }
        }

        return $ResellerIDs;
    }

    public function getdomainurl($ResellerID){
        $DomainUrl = Reseller::ResellerDomainUrl($ResellerID);
        return Response::json(array("DomainUrl" => $DomainUrl));
    }

    public function view($id) {

    }
}