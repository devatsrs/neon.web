<?php

class BillingSubscriptionController extends \BaseController {

    var $model = 'BillingSubscription';

    public function ajax_datagrid($type) {
        $data = Input::all();                
        //$FdilterAdvance = $data['FilterAdvance']== 'true'?1:0;
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $columns = array("Name", "AnnuallyFee", "QuarterlyFee", "MonthlyFee", "WeeklyFee", "DailyFee", "Advance");
        $sort_column = $columns[$data['iSortCol_0']];
        if($data['FilterAdvance'] == ''){
            $data['FilterAdvance'] = 'null';
        }
        if($data['FilterAppliedTo'] == ''){
            $data['FilterAppliedTo'] = 'null';
        }
        $query = "call prc_getBillingSubscription (".$CompanyID.",".$data['FilterAdvance'].",'".$data['FilterName']."','".intval($data['FilterCurrencyID'])."',".$data['FilterAppliedTo'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $billexports = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Billing Subscription.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($billexports);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Billing Subscription.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($billexports);
            }
            /*Excel::create('Billing Subscription', function ($excel) use ($billexports) {
                $excel->sheet('Billing Subscription', function ($sheet) use ($billexports) {
                    $sheet->fromArray($billexports);
                });
            })->download('xls');*/
        }
        $query .=',0)';

        return DataTableSql::of($query,'sqlsrv2')->make();
    }

    public function index() {

        $currencies 			= 	Currency::getCurrencyDropdownIDList();
		$AdvanceSubscription 	= 	json_encode(BillingSubscription::$Advance);
        return View::make('billingsubscription.index', compact('currencies','AdvanceSubscription'));

    }

    public function create()
    {
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        unset($data['SubscriptionID']);
        unset($data['SubscriptionClone']);
        $data['CreatedBy'] = User::get_user_full_name();
        $data["AppliedTo"] = empty($data['AppliedTo']) ? BillingSubscription::Customer : $data['AppliedTo'];
        $rules = array(
            'CompanyID' => 'required',
            'Name' => 'required|unique:tblBillingSubscription,Name,NULL,SubscriptionID,CompanyID,'.$data['CompanyID'].',AppliedTo,'.$data['AppliedTo'],
            'AnnuallyFee' => 'required|numeric',
            'QuarterlyFee' => 'required|numeric',
            'MonthlyFee' => 'required|numeric',
            'WeeklyFee' => 'required|numeric',
            'DailyFee' => 'required|numeric',
            'CurrencyID' => 'required',
            'InvoiceLineDescription' => 'required',
            'ActivationFee' => 'required|numeric',
        );
        $data['Advance'] = isset($data['Advance']) ? 1 : 0;
        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if ($BillingSubscription = BillingSubscription::create($data)) {
            return Response::json(array("status" => "success", "message" => "Subscription Successfully Created",'LastID'=>$BillingSubscription->SubscriptionID, 'newcreated'=>$BillingSubscription));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Subscription."));
        }
    }


    public function update($id)
    {
        if($id >0 ) {
            $BillingSubscription = BillingSubscription::find($id);
            $data = Input::all();
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;
            unset($data['SubscriptionID']);
            unset($data['SubscriptionClone']);
            $data['ModifiedBy'] = User::get_user_full_name();
            $rules = array(
                'CompanyID' => 'required',
                'Name' => 'required|unique:tblBillingSubscription,Name,'.$id.',SubscriptionID,CompanyID,'.$data['CompanyID'].',AppliedTo,'.$data['AppliedTo'],
                'AnnuallyFee' => 'required|numeric',
                'QuarterlyFee' => 'required|numeric',
                'MonthlyFee' => 'required|numeric',
                'WeeklyFee' => 'required|numeric',
                'DailyFee' => 'required|numeric',
                'CurrencyID' => 'required',
                'InvoiceLineDescription' => 'required',
                'ActivationFee' => 'required|numeric',
            );
            $data['Advance'] = isset($data['Advance']) ? 1 : 0;
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($data, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if ($BillingSubscription->update($data)) {
                return Response::json(array("status" => "success", "message" => "Subscription Successfully Updated",'LastID'=>$id));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Subscription."));
            }
        }
    }

    public function delete($id)
    {
        if( intval($id) > 0){

            if(!BillingSubscription::checkForeignKeyById($id)){
                try{
                    $BillingSubscription = BillingSubscription::find($id);
                    AmazonS3::delete($BillingSubscription->CompanyLogoAS3Key);
                    $result = $BillingSubscription->delete();
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
	
	function getSubscriptionData_ajax($id){		
       $BillingSubscription = BillingSubscription::find($id);
	   Log::info($BillingSubscription);
		if(empty($BillingSubscription)){
			return Response::json(array("status" => "failed", "message" => "Subscription Not found." ));
		}else{
			return Response::json($BillingSubscription);
		}
	
	}

}