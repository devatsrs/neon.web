<?php

class AccountSubscriptionController extends \BaseController {

public function main() {	
		$data 				=  Input::all();        
		$id					=  $data['id'];
		$companyID 			=  User::get_companyID();
		$SelectedAccount    =  Account::find($id);	 
		$accounts	 		=  Account::getAccountIDList();
		$services 			=  Service::getDropdownIDList($companyID);
	    return View::make('accountsubscription.main', compact('accounts','services','SelectedAccount','services'));

    }	

    public function ajax_datagrid($id){
        $data = Input::all();        
        $id=$data['account_id'];
        $select = ["tblAccountSubscription.SequenceNo","tblBillingSubscription.Name", "InvoiceDescription", "Qty" ,"tblAccountSubscription.StartDate",DB::raw("IF(tblAccountSubscription.EndDate = '0000-00-00','',tblAccountSubscription.EndDate) as EndDate"),"tblAccountSubscription.ActivationFee","tblAccountSubscription.DailyFee","tblAccountSubscription.WeeklyFee","tblAccountSubscription.MonthlyFee","tblAccountSubscription.QuarterlyFee","tblAccountSubscription.AnnuallyFee","tblAccountSubscription.AccountSubscriptionID","tblAccountSubscription.SubscriptionID","tblAccountSubscription.ExemptTax"];
        $subscriptions = AccountSubscription::join('tblBillingSubscription', 'tblAccountSubscription.SubscriptionID', '=', 'tblBillingSubscription.SubscriptionID')->where("tblAccountSubscription.AccountID",$id);        
        if(!empty($data['SubscriptionName'])){
            $subscriptions->where('tblBillingSubscription.Name','Like','%'.trim($data['SubscriptionName']).'%');
        }
        if(!empty($data['SubscriptionInvoiceDescription'])){
            $subscriptions->where('tblAccountSubscription.InvoiceDescription','Like','%'.trim($data['SubscriptionInvoiceDescription']).'%');
        }
        if(!empty($data['ServiceID'])){
            $subscriptions->where('tblAccountSubscription.ServiceID','=',$data['ServiceID']);
        }else{
            $subscriptions->where('tblAccountSubscription.ServiceID','=',0);
        }
        if(!empty($data['SubscriptionActive']) && $data['SubscriptionActive'] == 'true'){
            $subscriptions->where(function($query){
                $query->where('tblAccountSubscription.EndDate','>=',date('Y-m-d'));
                $query->orwhere('tblAccountSubscription.EndDate','=','0000-00-00');
            });

        }elseif(!empty($data['SubscriptionActive']) && $data['SubscriptionActive'] == 'false'){
            $subscriptions->where('tblAccountSubscription.EndDate','<',date('Y-m-d'));
            $subscriptions->where('tblAccountSubscription.EndDate','<>','0000-00-00') ;
        }
        $subscriptions->select($select);

        return Datatables::of($subscriptions)->make();
    }


	public function ajax_datagrid_page(){
        $data 						 = 	Input::all(); //Log::info(print_r($data,true));
        $data['iDisplayStart'] 		+=	1;
        $companyID 					 =  User::get_companyID(); 
        $columns 					 =  ['SequenceNo','AccountName','ServiceName','Name','Qty','StartDate','EndDate','ActivationFee','DailyFee','WeeklyFee','MonthlyFee','QuarterlyFee','AnnuallyFee'];   
        $sort_column 				 =  $columns[$data['iSortCol_0']];
        $data['AccountID'] 			 =  empty($data['AccountID'])?'0':$data['AccountID'];
		if($data['Active'] == 'true'){
			$data['Active']	=	1;
		}else{
			$data['Active'] =   0;
		}
		$data['ServiceID'] 			 =  empty($data['ServiceID'])?'null':$data['ServiceID'];
        $query = "call prc_GetAccountSubscriptions (".$companyID.",".intval($data['AccountID']).",".intval($data['ServiceID']).",'".$data['Name']."','".$data['Active']."','".date('Y-m-d')."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".strtoupper($data['sSortDir_0'])."'";
		
        if(isset($data['Export']) && $data['Export'] == 1)
		{
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
			
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/accountsubscription.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/subscription.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }         
        }
		

        $query .=',0)'; Log::info($query);
       // echo $query;exit;
        $result =  DataTableSql::of($query,'sqlsrv2')->make();
		return $result;
    }

	/**
	 * Store a newly created resource in storage.
	 * POST /accountsubscription
	 *
	 * @return Response
	 */
	public function store($id)
	{
		$data = Input::all();
        $data["AccountID"] = $id;
        $data["CreatedBy"] = User::get_user_full_name();
        $data['ExemptTax'] = isset($data['ExemptTax']) ? 1 : 0;
        AccountSubscription::$rules['SubscriptionID'] = 'required|unique:tblAccountSubscription,AccountSubscriptionID,NULL,SubscriptionID,'.$data['SubscriptionID'].',AccountID,'.$data["AccountID"];

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $rules = array(
            'AccountID'         =>      'required',
            'SubscriptionID'    =>  'required',
            'StartDate'               =>'required',
			'MonthlyFee' => 'required|numeric',
            'WeeklyFee' => 'required|numeric',
            'DailyFee' => 'required|numeric',
			 'ActivationFee' => 'required|numeric',
			 'Qty' => 'required|numeric',
			 
            //'EndDate'               =>'required'
        );
        if(!empty($data['EndDate'])) {
            $rules['StartDate'] = 'required|date|before:EndDate';
            $rules['EndDate'] = 'required|date';
        }
        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        unset($data['Status_name']);
        if(empty($data['SequenceNo'])){
            $SequenceNo = AccountSubscription::where(['AccountID'=>$data["AccountID"]])->max('SequenceNo');
            $SequenceNo = $SequenceNo +1;
            $data['SequenceNo'] = $SequenceNo;
        }
        if ($AccountSubscription = AccountSubscription::create($data)) {
            return Response::json(array("status" => "success", "message" => "Subscription Successfully Created",'LastID'=>$AccountSubscription->AccountSubscriptionID));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Subscription."));
        }
	}

	public function update($AccountID,$AccountSubscriptionID)
	{
        if( $AccountID  > 0  && $AccountSubscriptionID > 0 ) {
            $data = Input::all();
            $AccountSubscriptionID = $data['AccountSubscriptionID'];
            $AccountSubscription = AccountSubscription::find($AccountSubscriptionID);
            $data["AccountID"] = $AccountID;
            $data["ModifiedBy"] = User::get_user_full_name();
            $data['ExemptTax'] = isset($data['ExemptTax']) ? 1 : 0;
            AccountSubscription::$rules['SubscriptionID'] = 'required|unique:tblAccountSubscription,AccountSubscriptionID,NULL,SubscriptionID,' . $data['SubscriptionID'] . ',AccountID,' . $data["AccountID"];

            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $rules = array(
                'AccountID' => 'required',
                'SubscriptionID' => 'required',
                'StartDate' => 'required',
				'MonthlyFee' => 'required|numeric',
            'WeeklyFee' => 'required|numeric',
            'DailyFee' => 'required|numeric',
			 'ActivationFee' => 'required|numeric',
			 'Qty' => 'required|numeric',
			 
                //'EndDate' => 'required'
            );
            if(!empty($data['EndDate'])) {
                $rules['StartDate'] = 'required|date|before:EndDate';
                $rules['EndDate'] = 'required|date';
            }
            $validator = Validator::make($data, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            unset($data['Status_name']);
            if ($AccountSubscription->update($data)) {
                return Response::json(array("status" => "success", "message" => "Subscription Successfully Created", 'LastID' => $AccountSubscription->AccountSubscriptionID));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Subscription."));
            }
        }
	}


	public function delete($AccountID,$AccountSubscriptionID)
	{
        if( intval($AccountSubscriptionID) > 0){

            if(!AccountSubscription::checkForeignKeyById($AccountSubscriptionID)){
                try{
                    $AccountSubscription = AccountSubscription::find($AccountSubscriptionID);
                    $result = $AccountSubscription->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "Subscription Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Subscription."));
                    }
                }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting. Exception:". $ex->getMessage()));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Subscription is in Use, You can not delete this Subscription."));
            }
        }
	}
	
	function GetAccountServices($id){
	    $data = Input::all();
        $select = ["tblService.ServiceID","tblService.ServiceName"];
        $services = AccountService::join('tblService', 'tblAccountService.ServiceID', '=', 'tblService.ServiceID')->where("tblAccountService.AccountID",$id);
        $services->where(function($query){ $query->where('tblAccountService.Status','=','1'); });
        $services->select($select);
		$ServicesDataDb =  $services->get();
		$servicesArray = array();
		
		//
		foreach($ServicesDataDb as $ServicesData){				
			$servicesArray[$ServicesData->ServiceName] =	$ServicesData->ServiceID; 						
		} 
		return $servicesArray;
	}
	
	function GetAccountSubscriptions($id){
		$account = Account::find($id);
		$subscriptions =  BillingSubscription::getSubscriptionsArray($account->CompanyId,$account->CurrencyId);	
		return $subscriptions;
	}

}