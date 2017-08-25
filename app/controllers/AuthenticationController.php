<?php

/**
 * Created by PhpStorm.
 * User: srs2
 * Date: 23/02/2016
 * Time: 12:33
 */
class AuthenticationController extends \BaseController
{
    public function __construct(){

    }
    /** Account Authentication Rule */
    public function authenticate($id){
        $pos = strpos($id, '-');
        if($pos){
            $pos = explode('-',$id);
            $AccountID = $pos[0];
            $ServiceID = $pos[1];
        }else{
            $AccountID = $id;
            $ServiceID = 0;
        }
        $account = Account::find($AccountID);
        $customerip=array();
        $vendorip=array();
        $allcustomerip=array();
        $allvendorip=array();
        $AccountAuthenticate = AccountAuthenticate::where(array('AccountID' => $AccountID, 'ServiceID' => $ServiceID))->first();

        /* Service IP Changes Code Start */
        if(!empty($AccountAuthenticate->CustomerAuthRule) && $AccountAuthenticate->CustomerAuthRule == 'IP'){
            if($ServiceID==0){
                $AllAuthenticates = AccountAuthenticate::where(array('AccountID' => $AccountID,'CustomerAuthRule'=>'IP'))->get();
            }else{
                $AllAuthenticates = AccountAuthenticate::where(array('ServiceID' => $ServiceID,'AccountID' => $AccountID,'CustomerAuthRule'=>'IP'))->get();
            }

            if(count($AllAuthenticates)>0){
                foreach ($AllAuthenticates as $AllAuthenticate){					
					$value = $AllAuthenticate->CustomerAuthValue;
                    if(!empty($value)){
                        $AccountIPLists = array_filter(explode(',',$value));						
                        foreach($AccountIPLists as $index=>$row2){							
                            $customerip['CustomerIP'] = $row2;
                            $customerip['ServiceName'] = Service::getServiceNameByID($AllAuthenticate->ServiceID);
							$allcustomerip[] = $customerip;
                        }                        
                    }
                }

            }

        }
        if(!empty($AccountAuthenticate->VendorAuthRule) && $AccountAuthenticate->VendorAuthRule == 'IP'){
            if($ServiceID==0){
                $AllAuthenticates = AccountAuthenticate::where(array('AccountID' => $AccountID,'VendorAuthRule'=>'IP'))->get();
            }else{
                $AllAuthenticates = AccountAuthenticate::where(array('ServiceID' => $ServiceID,'AccountID' => $AccountID,'VendorAuthRule'=>'IP'))->get();
            }
            if(count($AllAuthenticates)>0){
                foreach ($AllAuthenticates as $AllAuthenticate){					
					$value = $AllAuthenticate->VendorAuthValue;
                    if(!empty($value)){
                        $AccountIPLists = array_filter(explode(',',$value));						
                        foreach($AccountIPLists as $index=>$row2){							
                            $vendorip['VendorIP'] = $row2;
                            $vendorip['ServiceName'] = Service::getServiceNameByID($AllAuthenticate->ServiceID);
							$allvendorip[] = $vendorip;
                        }                        
                    }
                }

            }

        }
        /* Service IP Changes End */
        /*
        if(!empty($data['ServiceID'])) {
            $AccountAuthenticate = AccountAuthenticate::where(array('AccountID' => $AccountID, 'ServiceID' => $ServiceID))->first();
        }else{
            $AccountAuthenticate = AccountAuthenticate::where(array('AccountID' => $AccountID))->get();
        }*/
        $rate_table = RateTable::getRateTableList(array('CurrencyID'=>$account->CurrencyId));
        $AuthRule = 'CLI';
        return View::make('accounts.authenticate', compact('account','AccountAuthenticate','Clitables','rate_table','AuthRule','ServiceID','allcustomerip','allvendorip'));
    }
    public function authenticate_store(){
        $data = Input::all();
        $data['CompanyID'] = $CompanyID = User::get_companyID();
        if(!empty($data['ServiceID'])){
            $ServiceID = $data['ServiceID'];
        }else{
            $ServiceID = 0;
        }
        $rule = AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->first();
        if(isset($data['VendorAuthRuleText'])) {
            unset($data['VendorAuthRuleText']);
        }
        if(isset($data['CustomerAuthValueText'])) {
            unset($data['CustomerAuthValueText']);
        }

        if(empty($data['CustomerAuthRule']) || ($data['CustomerAuthRule'] != 'IP' && $data['CustomerAuthRule'] != 'CLI' && $data['CustomerAuthRule']!='Other')){
            $data['CustomerAuthValue']='';  //if rule other then ip,cli and other, reset the value.
        }elseif(!empty($data['CustomerAuthRule']) && $data['CustomerAuthRule'] == 'Other' && empty($data['CustomerAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Customer Other Value required"));
        }elseif(!empty($data['CustomerAuthRule']) && $data['CustomerAuthRule'] == 'IP' && empty($data['CustomerAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Customer IP is required"));
        }elseif(!empty($data['CustomerAuthRule']) && $data['CustomerAuthRule'] == 'CLI' && empty($data['CustomerAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Customer CLI is required"));
        }

        if(empty($data['VendorAuthRule']) || ($data['VendorAuthRule'] != 'IP'&& $data['VendorAuthRule'] != 'CLI' && $data['VendorAuthRule']!='Other')){
            $data['VendorAuthValue']=''; //if rule other then ip,cli and other, reset the value.
        }else if(!empty($data['VendorAuthRule']) && $data['VendorAuthRule'] == 'Other' && empty($data['VendorAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Vendor Other Value required"));
        }else if(!empty($data['VendorAuthRule']) && $data['VendorAuthRule'] == 'IP' && empty($data['VendorAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Vendor IP is required"));
        }else if(!empty($data['VendorAuthRule']) && $data['VendorAuthRule'] == 'CLI' && empty($data['VendorAuthValue'])){
            return Response::json(array("status" => "error", "message" => "Vendor CLI is required"));
        }

        if(isset($data['VendorAuthValue'])){
            if(!empty($data['VendorAuthRule'])){ //if rule changes and value not changed, reset the values.
                $data['VendorAuthValue'] = implode(',', array_unique(explode(',', $data['VendorAuthValue'])));
                if(!empty($rule)) {
                    if ($rule->VendorAuthRule != $data['VendorAuthRule'] && $rule->VendorAuthValue == $data['VendorAuthValue']) {
                        $data['VendorAuthValue'] = '';
                    }
                }
            }
        }
        if(isset($data['CustomerAuthValue'])){  //if rule changed and value not changed, reset the values.
            if(!empty($data['CustomerAuthRule'])){
                $data['CustomerAuthValue'] = implode(',', array_unique(explode(',', $data['CustomerAuthValue'])));
                if(!empty($rule)) {
                    if ($rule->CustomerAuthRule != $data['CustomerAuthRule'] && $rule->CustomerAuthValue == $data['CustomerAuthValue']) {
                        $data['CustomerAuthValue'] = '';
                    }
                }
            }
        }
        unset($data['vendoriptable_length']);
        unset($data['vendorclitable_length']);
        unset($data['customeriptable_length']);
        unset($data['customerclitable_length']);
        unset($data['CLIName']);
        unset($data['table-clitable_length']);
        if(AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->count()){
            AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->update($data);
            return Response::json(array("status" => "success", "message" => "Account Successfully Updated"));
        }else{
            AccountAuthenticate::insert($data);
            return Response::json(array("status" => "success", "message" => "Account Successfully Updated"));
        }
        return Response::json(array("status" => "failed", "message" => "Problem Updating Account."));
    }

    public function addipclis($id){
        $data = Input::all();		
        $data['AccountID'] = $id;
        $data['CompanyID'] = $CompanyID = User::get_companyID();
        if(!empty($data['ServiceID'])){
            $ServiceID = $data['ServiceID'];
        }else{
            $ServiceID = 0;
        }
        /*$message = '';
        $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';*/

        $status = AccountAuthenticate::validate_ipclis($data);
        /*$save = $status['data'];*/
        if($status['status']==0){
            return Response::json(array("status" => "error", "message" => $status['message']));
        }

		$customerip=array();
        $vendorip=array();
        $allcustomerip=array();
        $allvendorip=array();
		
		$type = $data['type']==1?'CLI':'IP';
		$isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';
		
        $object = AccountAuthenticate::where(['CompanyID'=>$data['CompanyID'],'AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID])->first();
        /* Service IP Changes Code Start */
		if($type=='IP'){
			if($isCustomerOrVendor=='Customer'){
                if($ServiceID==0){
                    $AllAuthenticates = AccountAuthenticate::where(array('AccountID' => $data['AccountID'],'CustomerAuthRule'=>'IP'))->get();
                }else{
                    $AllAuthenticates = AccountAuthenticate::where(array('ServiceID' => $ServiceID,'AccountID' => $data['AccountID'],'CustomerAuthRule'=>'IP'))->get();
                }
				
				if(count($AllAuthenticates)>0){
					foreach ($AllAuthenticates as $AllAuthenticate){					
						$value = $AllAuthenticate->CustomerAuthValue;
						if(!empty($value)){
							$AccountIPLists = array_filter(explode(',',$value));						
							foreach($AccountIPLists as $index=>$row2){							
								$customerip['IP'] = $row2;
								$customerip['ServiceName'] = Service::getServiceNameByID($AllAuthenticate->ServiceID);
								$allcustomerip[] = $customerip;
							}                        
						}
					}
				}	
				
				return Response::json(array("status" => "success","object"=>$object ,"ipobject"=>$allcustomerip, "message" => $status['message']));	
			}			
			if($isCustomerOrVendor=='Vendor'){
                if($ServiceID==0) {
                    $AllAuthenticates = AccountAuthenticate::where(array('AccountID' => $data['AccountID'], 'VendorAuthRule' => 'IP'))->get();
                }else{
                    $AllAuthenticates = AccountAuthenticate::where(array('ServiceID' =>$ServiceID ,'AccountID' => $data['AccountID'], 'VendorAuthRule' => 'IP'))->get();
                }
				
				if(count($AllAuthenticates)>0){
					foreach ($AllAuthenticates as $AllAuthenticate){					
						$value = $AllAuthenticate->VendorAuthValue;
						if(!empty($value)){
							$AccountIPLists = array_filter(explode(',',$value));						
							foreach($AccountIPLists as $index=>$row2){							
								$vendorip['IP'] = $row2;
								$vendorip['ServiceName'] = Service::getServiceNameByID($AllAuthenticate->ServiceID);
								$allvendorip[] = $vendorip;
							}                        
						}
					}
				}	

				return Response::json(array("status" => "success","object"=>$object,"ipobject"=>$allvendorip, "message" => $status['message']));		
			}		
		}
        /* Service IP Changes Code Start */
                
        return Response::json(array("status" => "success","object"=>$object, "message" => $status['message']));

        /*if((isset($save['CustomerAuthValue'])) || (isset($save['VendorAuthValue']))){
            if($isCustomerOrVendor=='Customer' && !empty($save['CustomerAuthValue'])) {
                 $status['toBeInsert']=explode(',',$save['CustomerAuthValue']);
            }elseif($isCustomerOrVendor=='Vendor' && !empty($save['VendorAuthValue'])){
                $status['toBeInsert']=explode(',',$save['VendorAuthValue']);
            }
            if(AccountAuthenticate::where(['CompanyID'=>$save['CompanyID'],'AccountID'=>$save['AccountID']])->count()>0){
                AccountAuthenticate::where(['CompanyID'=>$save['CompanyID'],'AccountID'=>$save['AccountID']])->update($save);
            }else{
                AccountAuthenticate::insert($save);
            }
            $object = AccountAuthenticate::where(['CompanyID'=>$save['CompanyID'],'AccountID'=>$save['AccountID']])->first();
            return Response::json(array("status" => "success","object"=>$object, "message" => $status['message']));
        }*/
    }

    public function deleteips($id){
        $data = Input::all();
        $companyID = User::get_companyID();
        $Date = '';
        $Confirm = 0;
        if(isset($data['dates'])){
            $Date = $data['dates'];
            $Confirm = 1;
        }
        if(!empty($data['ServiceID'])){
            $ServiceID = $data['ServiceID'];
        }else{
            $ServiceID = 0;
        }
        $data['AccountID'] = $id;
        $accountAuthenticate = AccountAuthenticate::where(array('CompanyID'=>$companyID,'AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->first();
        $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';
        $query = "call prc_unsetCDRUsageAccount ('" . $companyID . "','" . $data['ipclis'] . "','".$Date."',".$Confirm.",".$ServiceID.")";
        $postIps = explode(',',$data['ipclis']);
        unset($data['ipclis']);
        unset($data['isCustomerOrVendor']);
        unset($data['dates']);
        unset($data['ServiceID']);
        $ips = [];
        if(!empty($accountAuthenticate)){
            $oldAuthValues['CustomerAuthValue'] = $accountAuthenticate->CustomerAuthValue;
            $oldAuthValues['VendorAuthValue'] = $accountAuthenticate->VendorAuthValue;
            $recordFound = DB::Connection('sqlsrvcdr')->select($query);
            if($recordFound[0]->Status>0){
                return Response::json(array("status" => "check","check"=>1));
            }

            /* Service IP Changes Code Start */
            if($ServiceID==0){
                if($isCustomerOrVendor=='Customer') {
                    if (count($postIps) > 0) {
                        foreach ($postIps as $ips) {
                            $accounts = AccountAuthenticate::where(array('AccountID' => $data['AccountID'], 'CustomerAuthRule' => 'IP'))
                                ->where('CustomerAuthValue', 'like', '%' . $ips . '%')
                                ->first();
                            if (count($accounts) > 0) {
                                $data['CustomerAuthRule'] = 'IP';
                                $dbIPs = explode(',', $accounts->CustomerAuthValue);
                                $updateIPs = explode(',', $ips);
                                $ips = implode(',',array_diff($dbIPs, $updateIPs));
                                $data['CustomerAuthValue'] = ltrim($ips,',');
                                AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$accounts->ServiceID))->update($data);
                            }

                        }
                    }
                }

                if($isCustomerOrVendor=='Vendor') {
                    if (count($postIps) > 0) {
                        foreach ($postIps as $ips) {
                            $accounts = AccountAuthenticate::where(array('AccountID' => $data['AccountID'], 'VendorAuthRule' => 'IP'))
                                ->where('VendorAuthValue', 'like', '%' . $ips . '%')
                                ->first();
                            if (count($accounts) > 0) {
                                $data['VendorAuthRule'] = 'IP';
                                $dbIPs = explode(',', $accounts->VendorAuthValue);
                                $updateIPs = explode(',', $ips);
                                $ips = implode(',',array_diff($dbIPs, $updateIPs));
                                $data['VendorAuthValue'] = ltrim($ips,',');
                                AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$accounts->ServiceID))->update($data);
                            }

                        }
                    }
                }

            }else{

                if($isCustomerOrVendor=='Customer'){
                    $data['CustomerAuthRule'] = 'IP';
                    $dbIPs = explode(',', $accountAuthenticate->CustomerAuthValue);
                    $ips = implode(',',array_diff($dbIPs, $postIps));
                    $data['CustomerAuthValue'] = ltrim($ips,',');
                }else{
                    $data['VendorAuthRule'] = 'IP';
                    $dbIPs = explode(',', $accountAuthenticate->VendorAuthValue);
                    $ips = implode(',',array_diff($dbIPs, $postIps));
                    $data['VendorAuthValue'] = ltrim($ips,',');
                }
                AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->update($data);
                //$object = AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->first();
                //return Response::json(array("status" => "success","ipclis"=> explode(',',$ips),"object"=>$object,"message" => "Account Successfully Updated"));

            }

            // starts add audit log
            $accountAuthenticate = AccountAuthenticate::where(['CompanyID'=>$companyID,'AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID]);
            if($accountAuthenticate->count() > 0) {
                $accountAuthenticate = $accountAuthenticate->first();
                AccountAuthenticate::addAuditLog($accountAuthenticate,$oldAuthValues);
            }
            // ends add audit log

            $customerip=array();
            $vendorip=array();
            $allcustomerip=array();
            $allvendorip=array();

            /*return response start*/
            if($isCustomerOrVendor=='Customer'){
                if($ServiceID==0){
                    $AllAuthenticates = AccountAuthenticate::where(array('AccountID' => $data['AccountID'],'CustomerAuthRule'=>'IP'))->get();
                }else{
                    $AllAuthenticates = AccountAuthenticate::where(array('ServiceID' => $ServiceID,'AccountID' => $data['AccountID'],'CustomerAuthRule'=>'IP'))->get();
                }

                if(count($AllAuthenticates)>0){
                    foreach ($AllAuthenticates as $AllAuthenticate){
                        $value = $AllAuthenticate->CustomerAuthValue;
                        if(!empty($value)){
                            $AccountIPLists = array_filter(explode(',',$value));
                            foreach($AccountIPLists as $index=>$row2){
                                $customerip['IP'] = $row2;
                                $customerip['ServiceName'] = Service::getServiceNameByID($AllAuthenticate->ServiceID);
                                $allcustomerip[] = $customerip;
                            }
                        }
                    }
                }

                return Response::json(array("status" => "success","object"=>$allcustomerip ,"ipobject"=>$allcustomerip, "message" =>"Account Successfully Updated" ));
            }

            if($isCustomerOrVendor=='Vendor'){
                if($ServiceID==0) {
                    $AllAuthenticates = AccountAuthenticate::where(array('AccountID' => $data['AccountID'], 'VendorAuthRule' => 'IP'))->get();
                }else{
                    $AllAuthenticates = AccountAuthenticate::where(array('ServiceID' =>$ServiceID ,'AccountID' => $data['AccountID'], 'VendorAuthRule' => 'IP'))->get();
                }

                if(count($AllAuthenticates)>0){
                    foreach ($AllAuthenticates as $AllAuthenticate){
                        $value = $AllAuthenticate->VendorAuthValue;
                        if(!empty($value)){
                            $AccountIPLists = array_filter(explode(',',$value));
                            foreach($AccountIPLists as $index=>$row2){
                                $vendorip['IP'] = $row2;
                                $vendorip['ServiceName'] = Service::getServiceNameByID($AllAuthenticate->ServiceID);
                                $allvendorip[] = $vendorip;
                            }
                        }
                    }
                }

                return Response::json(array("status" => "success","object"=>$allvendorip,"ipobject"=>$allvendorip, "message" => "Account Successfully Updated"));
            }
            /* Service IP Changes Code end */
            /*return response start*/

        }else{
            return Response::json(array("status" => "error","message" => "No Ip exist."));
        }
    }

    public function deleteclis($id){
        $data = Input::all();
        $companyID = User::get_companyID();
        $Date = '';
        $Confirm = 0;
        if(isset($data['dates'])){
            $Date = $data['dates'];
            $Confirm = 1;
        }
        if(!empty($data['ServiceID'])){
            $ServiceID = $data['ServiceID'];
        }else{
            $ServiceID = 0;
        }
        $data['AccountID'] = $id;
        $accountAuthenticate = AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->first();
        $isCustomerOrVendor = $data['isCustomerOrVendor']==1?'Customer':'Vendor';
        $query = "call prc_unsetCDRUsageAccount ('" . $companyID . "','" . $data['ipclis'] . "','".$Date."',".$Confirm.",".$ServiceID.")";
        $postClis = explode(',',$data['ipclis']);
        unset($data['ipclis']);
        unset($data['isCustomerOrVendor']);
        unset($data['dates']);
        if(!empty($accountAuthenticate)){
            $recordFound = DB::Connection('sqlsrvcdr')->select($query);
            if($recordFound[0]->Status>0){
                return Response::json(array("status" => "check","check"=>1));
            }
            if($isCustomerOrVendor=='Customer'){
                $data['CustomerAuthRule'] = 'CLI';
                $dbCLIs = explode(',', $accountAuthenticate->CustomerAuthValue);
                $clis = implode(',',array_diff($dbCLIs, $postClis));
                $data['CustomerAuthValue'] = ltrim($clis,',');
            }else{
                $data['VendorAuthRule'] = 'CLI';
                $dbCLIs = explode(',', $accountAuthenticate->VendorAuthValue);
                $clis = implode(',',array_diff($dbCLIs, $postClis));
                $data['VendorAuthValue'] = ltrim($clis,',');
            }
            AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->update($data);
            $object = AccountAuthenticate::where(array('AccountID'=>$data['AccountID'],'ServiceID'=>$ServiceID))->first();
            return Response::json(array("status" => "success","ipclis"=> explode(',',$clis),"object"=>$object,"message" => "Account Successfully Updated"));
        }else{
            return Response::json(array("status" => "error","message" => "No Cli exist."));
        }
    }

    public function recordExist(){

    }

}