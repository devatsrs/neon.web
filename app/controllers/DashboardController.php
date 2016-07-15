<?php
use Jenssegers\Agent\Agent;
class DashboardController extends BaseController {

    
    public function __construct() {

    }


    public function home() {

        if(Company::isBillingLicence(1)){
            return Redirect::to('billingdashboard');
        }
 
        return View::make('dashboard.index');
    }
    public function salesdashboard(){

            $companyID = User::get_companyID();
            $userID = '';
            $isAdmin = (User::is_admin() || User::is('RateManager')) ? 1 : 0;
            $data = Input::all();


            $start_date = $prev_end_date = $original_startdate = date('Y-m-d', strtotime('-1 week'));
            $end_date = $original_enddate = date('Y-m-d');
            $prev_start_date = date('Y-m-d', strtotime('-2 week'));
            $compare_with = 0;
            $Executive =0;
            if(User::is_admin()){
                $Executive= 1;
            }

            if (isset($data['Startdate'])) {
                $prev_end_date = $start_date = $original_startdate = $data['Startdate'];
            }
            if (isset($data['Enddate'])) {
                $end_date = $original_enddate = $data['Enddate'];
                (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
                $prev_start_date = date('Y-m-d', strtotime($start_date . ' -' . (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) . ' day'));
            }
            if (User::is('AccountManager')) {
                $userID = User::get_userID();
            } else if (isset($data['account_owners']) && $data['account_owners'] > 0) {
                $userID = $data['account_owners'];
                $isAdmin = 0;
            }
            $query = "call prc_salesDashboard ($companyID,'','$userID','$isAdmin','$start_date','$end_date','','','$Executive')";
            if (isset($data['compare_with'])) {
                $compare_with = 1;
                $query = "call prc_salesDashboard ($companyID,'','$userID','$isAdmin','$start_date','$end_date','$prev_start_date','$prev_end_date','$Executive')";
            }

            $dashboardData = DataTableSql::of($query, 'sqlsrv2')->getProcResult(array('TotalSales', 'TotalActiveAccount', 'TotalInActiveAccount', 'DaySales', 'AccountSales', 'AreaSales', 'TotalInActiveAccountList','SalesExecutive' ,'PreSalesExecutive','PrevTotalSales', 'PrevTotalActiveAccount', 'PrevTotalInActiveAccount', 'PrevDaySales', 'PrevAccountSales', 'PrevAreaSales', 'PrevTotalInActiveAccountList'));

            $count = $TotalCharges = $PrevTotalCharges = 0;
            if (isset($dashboardData['data']['TotalSales'])) {
                $TotalCharges = $dashboardData['data']['TotalSales'][0]->TotalCharges;

            }
            if (isset($dashboardData['data']['PrevTotalSales'][0]->PrevTotalCharges)) {
                $PrevTotalCharges = $dashboardData['data']['PrevTotalSales'][0]->PrevTotalCharges;
            }
            if (isset($dashboardData['data']['DaySales'])) {
                while (strtotime($start_date) < strtotime($end_date)) {
                    foreach ($dashboardData['data']['DaySales'] as $saledata) {
                        $sales_data[$count]['sale_date'] = $start_date;
                        if (date("Y-m-d", strtotime($saledata->sales_date)) == $start_date) {
                            $sales_data[$count]['sales'] = $saledata->TotalCharges;
                            break;
                        } else {
                            $sales_data[$count]['sales'] = 0;
                        }
                    }
                    $count++;
                    $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
                }
            }
            $count = 0;

            if (isset($dashboardData['data']['PrevDaySales'])) {
                $start_date = $original_startdate;
                while (strtotime($prev_start_date) < strtotime($prev_end_date)) {
                    foreach ($dashboardData['data']['PrevDaySales'] as $saledata) {
                        $prev_sales_data[$start_date]['sale_date'] = $prev_start_date;
                        if (date("Y-m-d", strtotime($saledata->sales_date)) == $prev_start_date) {
                            $prev_sales_data[$start_date]['sales'] = $saledata->prevTotalCharges;
                            break;
                        } else {
                            $prev_sales_data[$start_date]['sales'] = 0;
                        }
                    }
                    $count++;
                    $prev_start_date = date("Y-m-d", strtotime("+1 day", strtotime($prev_start_date)));
                    $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
                }
            }
            $count = 0;
            if (isset($dashboardData['data']['AreaSales'])) {
                foreach ($dashboardData['data']['AreaSales'] as $topdata) {
                    $top_data[$count]['sales'] = $topdata->TotalCharges;
                    $top_data[$count]['code'] = $topdata->Country;
                    $count++;
                }
            }
            $count = 0;
            if (isset($dashboardData['data']['PrevAreaSales'])) {
                foreach ($dashboardData['data']['PrevAreaSales'] as $topdata) {
                    $prev_top_data[$count]['sales'] = $topdata->prevTotalCharges;
                    $prev_top_data[$count]['code'] = $topdata->Country;
                    $count++;
                }
            }
            if (isset($dashboardData['data']['PrevAccountSales'])) {
                foreach ($dashboardData['data']['PrevAccountSales'] as $prevaccount) {
                    $prevsales[$prevaccount->GatewayAccountID] = $prevaccount->prevTotalCharges;
                }

            }
            if (isset($dashboardData['data']['PreSalesExecutive']) && $compare_with ==1) {
                foreach ($dashboardData['data']['PreSalesExecutive'] as $SalesExecutive) {
                    $prevSalesExecutive[$SalesExecutive->Owner] = $SalesExecutive->TotalCharges;
                }

            }
            $account_owners = User::getOwnerUsersbyRole();

            return View::make('dashboard.sales', compact('dashboardData', 'TotalDueCustomer', 'TotalDueVendor', 'sales_data', 'prev_sales_data', 'top_data', 'prev_top_data', 'compare_with', 'prevsales', 'original_startdate', 'original_enddate', 'account_owners', 'userID','prevSalesExecutive'));
    }

    public function billingdashboard(){

       $companyID = User::get_companyID();
       $DefaultCurrencyID = Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $company_gateway =  CompanyGateway::getCompanyGatewayIdList();
       return View::make('dashboard.billing',compact('DefaultCurrencyID','original_startdate','original_enddate','company_gateway'));

    }
    public function monitor_dashboard(){

        $companyID = User::get_companyID();
        $DefaultCurrencyID = Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $isAdmin = User::is_admin();
        $where['Status'] = 1;
        $where['VerificationStatus'] = Account::VERIFIED;
        $where['CompanyID']=User::get_companyID();
        if(User::is('AccountManager')){
            $where['Owner'] = User::get_userID();
        }
        $agent = new Agent();
        $isDesktop = $agent->isDesktop();
        $newAccountCount = Account::where($where)->where('created_at','>=',$original_startdate)->count();
        return View::make('dashboard.dashboard',compact('DefaultCurrencyID','original_startdate','original_enddate','isAdmin','newAccountCount','isDesktop'));

    }
	
	public function CrmDashboard(){ 
        $companyID 			= 	User::get_companyID();
        $DefaultCurrencyID 	= 	Company::where("CompanyID",$companyID)->pluck("CurrencyId");
		$Country 			= 	Country::getCountryDropdownIDList();
        $account 			= 	Account::getAccountIDList();
		$currency 			= 	Currency::getCurrencyDropdownIDList();
		$UserID 			= 	User::get_userID();
		$isAdmin 			= 	(User::is_admin() || User::is('RateManager')) ? 1 : 0;
		$users			 	= 	User::getUserIDList();
		$StartDateDefault 	= 	date("m/d/Y",strtotime(''.date('Y-m-d').' -1 months'));
		$DateEndDefault  	= 	date('m/d/Y');
		 return View::make('dashboard.crm', compact('companyID','DefaultCurrencyID','Country','account','currency','UserID','isAdmin','users','StartDateDefault','DateEndDefault'));	
	}
	
	public function GetUsersTasks(){
		
	    $data 					= 	Input::all();		
		$companyID			 	= 	User::get_companyID();
		$SearchDate				=	'';
		
		$where['taskClosed']	=	0;
		$task 					= 	Task::where($where)->select(['tblTask.Subject','tblTask.DueDate','tblCRMBoardColumn.BoardColumnName as Status','tblAccount.AccountName as Company','tblTask.Priority']);
		
		
		$UserID			=	(isset($data['UsersID']) && is_array($data['UsersID']))?implode(",",array_filter($data['UsersID'])):'';
		if(!empty($UserID)){
			$task->whereRaw('find_in_set(tblTask.UsersIDs,"'.$UserID.'")');
		}		

		if(isset($data['TaskTypeData']) && $data['TaskTypeData']!=''){
		 if($data['TaskTypeData'] == 'duetoday'){
			 $task->where("tblTask.DueDate","=",DB::raw(''.date('Y-m-d')).'');
		 }
		 else if($data['TaskTypeData'] == 'duesoon'){
			 $task->whereBetween('tblTask.DueDate',array(date("Y-m-d"),date("Y-m-d",strtotime(''.date('Y-m-d').' +1 months'))));						
		 }
		 else if($data['TaskTypeData'] == 'overdue'){
			$task->where("tblTask.DueDate","<",DB::raw(''.date('Y-m-d')).'');			
		 }
		 if($data['TaskTypeData'] != 'All'){
			$task->where("tblTask.DueDate","!=",DB::raw("'0000-00-00 00:00:00'")); 			 
		 }		 
		}
				
		$task->join('tblCRMBoardColumn', 'tblTask.BoardColumnID', '=', 'tblCRMBoardColumn.BoardColumnID');
		
		$task->join('tblAccount', 'tblTask.AccountIDs', '=', 'tblAccount.AccountID');
		
        $UserTasks 		 	 	= 	$task->orderBy('tblTask.DueDate', 'desc')->get();
		
        $jsondata['UserTasks']	=	$UserTasks;
		return json_encode($jsondata);
	}
	
	function GetPipleLineData(){
        $companyID 			= 	User::get_companyID();
        $userID 			= 	'';
        $isAdmin 			= 	(User::is_admin() || User::is('RateManager')) ? 1 : 0;
        $data 				= 	Input::all();
		$UserID				=	(isset($data['UsersID']) && is_array($data['UsersID']))?implode(",",array_filter($data['UsersID'])):'';
		$CurrencyID			=	(isset($data['CurrencyID']) && !empty($data['CurrencyID']))?$data['CurrencyID']:0;
		$array_return 		= 	array("TotalOpportunites"=>0,"TotalWorth"=>0);
		$array_status 		= 	array();
		$statusarray 		=	implode(",", array(Opportunity::Open,Opportunity::Won,Opportunity::Lost,Opportunity::Abandoned));
		$query  			= 	"call prc_GetCrmDashboardPipeLine (".$companyID.",'".$UserID."', '".$statusarray."','".$CurrencyID."')";
		$result 			= 	DB::select($query);
		
			foreach($result as $result_data){
				$array_status[$result_data->Status] = array("Worth"=>$result_data->TotalWorth,"Opportunites"=>$result_data->TotalOpportunites);
			}
			foreach(Opportunity::$status as $index => $status_text){		
				$array_return['CurrencyCode'] 	= 	isset($result_data->v_CurrencyCode_)?$result_data->v_CurrencyCode_:'';		
				$array_return['data'][$index] = isset($array_status[$index])?array("status"=>$status_text,"Worth"=>$array_status[$index]["Worth"],"Opportunites"=>$array_status[$index]["Opportunites"],"CurrencyCode"=>$array_return['CurrencyCode']): array("status"=>$status_text,"Worth"=>0,"Opportunites"=>0,"CurrencyCode"=>$array_return['CurrencyCode']);
				
				$array_return['TotalOpportunites'] 			=   $array_return['TotalOpportunites']+(isset($array_status[$index]["Opportunites"])?$array_status[$index]["Opportunites"]:0);
				
				$array_return['TotalWorth'] 	= 	$array_return['TotalWorth']+(isset($array_status[$index]['Worth'])?$array_status[$index]['Worth']:0);	
			}
		
		
		return json_encode($array_return);
	}
	
	public function GetForecastData(){ //crm dashboard
			
        $companyID 			= 	User::get_companyID();
        $userID 			= 	'';
        $isAdmin 			= 	(User::is_admin() || User::is('RateManager')) ? 1 : 0;
        $data 				= 	Input::all();		
		$rules = array(
            'Closingdate' =>      'required',                 
        );
		$message	 = array("Closingdate.required"=> "Close Date field is required.");
        $validator   = Validator::make($data, $rules,$message);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }		
		$UserID				=	(isset($data['UsersID']) && is_array($data['UsersID']))?implode(",",array_filter($data['UsersID'])):'';
		$CurrencyID			=	(isset($data['CurrencyID']) && !empty($data['CurrencyID']))?$data['CurrencyID']:0;
		$array_return 		= 	array();
		$Closingdate		=	explode(' - ',$data['Closingdate']);
		$StartDate			=   date("Y-m-d",strtotime($Closingdate[0]))." 00:00:00";
		$EndDate			=	date("Y-m-d",strtotime($Closingdate[1]))." 23:59:59";		
		$statusarray		=	(isset($data['Status']))?$data['Status']:'';
		$query  			= 	"call prc_GetCrmDashboardForecast (".$companyID.",'".$UserID."', '".$statusarray."','".$CurrencyID."','".$StartDate."','".$EndDate."')";
		$result 			= 	DB::select($query);
		$TotalWorth			=	0;
		Log::info($query);
		foreach($result as $result_data){
				$CurrencySign 			   = 	    isset($result_data->v_CurrencyCode_)?$result_data->v_CurrencyCode_:'';	
				if(isset($array_return['data'][$result_data->ClosingDate]))
				{
					$CcurrentDataWorth 			=	 $array_return['data'][$result_data->ClosingDate]['TotalWorth'];
					$CcurrentDataOpportunites 	=	 $array_return['data'][$result_data->ClosingDate]['Opportunites'];
					$CcurrentDataStatusStr 		=	 $array_return['data'][$result_data->ClosingDate]['StatusStr'];
					
					if(isset($CcurrentDataStatusStr[Opportunity::$status[$result_data->StatusSum]])){ 	
					
					 	$currentStatusdata = 	$CcurrentDataStatusStr[Opportunity::$status[$result_data->StatusSum]];
						$CcurrentDataStatusStr[Opportunity::$status[$result_data->StatusSum]] = array("Status"=>Opportunity::$status[$result_data->StatusSum],"worth"=>$currentStatusdata['worth']+$result_data->TotalWorth);						
					}else{
						$CcurrentDataStatusStr[Opportunity::$status[$result_data->StatusSum]]	=	array("Status"=>Opportunity::$status[$result_data->StatusSum],"worth"=>$result_data->TotalWorth);	
					}
					$array_return['data'][$result_data->ClosingDate]    = 		array("TotalWorth"=>$CcurrentDataWorth+$result_data->TotalWorth,"Opportunites"=>$CcurrentDataOpportunites+1,"ClosingDate"=>$result_data->ClosingDate,"CurrencyCode"=>$CurrencySign,'StatusStr'=>$CcurrentDataStatusStr );
				}
				else
				{ 	$StatusArray = array();
					$StatusArray[Opportunity::$status[$result_data->StatusSum]]  = array("Status"=>Opportunity::$status[$result_data->StatusSum],"worth"=>$result_data->TotalWorth);
					$array_return['data'][$result_data->ClosingDate]    = 		array("TotalWorth"=>$result_data->TotalWorth,"Opportunites"=>1,"ClosingDate"=>$result_data->ClosingDate,"CurrencyCode"=>$CurrencySign,'StatusStr'=>$StatusArray);			
				}				
				$TotalWorth 			   = 		$TotalWorth+$result_data->TotalWorth;
		}
		//Log::info($array_return);
		$array_final = array();
		if(isset($array_return['data'])){ 
			foreach($array_return['data'] as $key => $array_return_data){
				$ArrStatus			= 	$array_return_data['StatusStr'];
				$ArrChild			= 	array();
				foreach($ArrStatus as $ArrStatusData){
					$ArrChild[]	 = $ArrStatusData;
				}
				$array_return_data['StatusStr'] = 	$ArrChild;
				$array_final['data'][] 			= 	$array_return_data;
			}
		}
		
		if(count($array_final)>0){					
			$array_final['status'] 	   	   	   = 		'success';
			$array_final['CurrencyCode'] 	   = 		isset($result_data->v_CurrencyCode_)?$result_data->v_CurrencyCode_:'';
		}
		$array_final['TotalWorth'] 	  		   = 		$TotalWorth;
		return json_encode($array_final);
	}

    public function ajax_get_recent_due_sheets(){
        $companyID = User::get_companyID();
        $query = "call prc_GetDashboardDataRecentDueRateSheet (".$companyID.")";
        $dashboardData = DataTableSql::of($query)->getProcResult(array('TotalDueCustomer','TotalDueVendor'));
        $TotalDueCustomerarray = $dashboardData['data']['TotalDueCustomer'];
        $TotalDueVendorarray = $dashboardData['data']['TotalDueVendor'];
        $jsondata['TotalDueCustomerarray'] = $TotalDueCustomerarray;
        $jsondata['TotalDueVendorarray'] = $TotalDueVendorarray;
        return json_encode($jsondata);
    }

    public function ajax_get_recent_leads(){
        $companyID = User::get_companyID();
        $query = "call prc_GetDashboardRecentLeads (".$companyID.")";
        $LeadsResult = DataTableSql::of($query)->getProcResult(array('getRecentLeads'));
        $leads = [];
        $jsondata['leads'] = '';
        foreach ($LeadsResult['data']['getRecentLeads'] as $index=>$lead){
            $leads['AccountName'] = $lead->AccountName;
            $leads['Accounturl'] = URL::to('/leads/'.$lead->AccountID.'/show');
            $leads['Phone'] = $lead->Phone;
            $leads['Email'] = $lead->Email;
            $leads['created_by'] = $lead->created_by;
            $leads['daydiff'] = \Carbon\Carbon::createFromTimeStamp(strtotime($lead->created_at))->diffForHumans();
            $jsondata['leads'][] = $leads;
        }
        return json_encode($jsondata);
    }

    public function ajax_get_jobs(){
        $companyID = User::get_companyID();
        $userID = User::get_userID();
        $isAdmin = (User::is_admin() || User::is('RateManager'))?1:0;
        $query = "call prc_GetDashboardDataJobs (".$companyID.','.$userID.','.$isAdmin.")";
        $JobResult = DataTableSql::of($query)->getProcResult(array('AllHeaderJobs','CountJobs'));
        $Jobs = [];
        $jsondata['Jobs'] = '';
        if(!empty($JobResult['data']['AllHeaderJobs'])) {
            foreach ($JobResult['data']['AllHeaderJobs'] as $index => $job) {
                $Jobs['JobID'] = $job->JobID;
                $Jobs['Title'] = $job->Title;
                $Jobs['Status'] = $job->Status;
                $Jobs['CreatedBy'] = $job->CreatedBy;
                $Jobs['daydiff'] = \Carbon\Carbon::createFromTimeStamp(strtotime($job->created_at))->diffForHumans();
                $jsondata['Jobs'][] = $Jobs;
            }
        }
        $jsondata['JobsCount'] = $JobResult['data']['CountJobs'];
        return json_encode($jsondata);
    }

    public function ajax_get_processed_files(){
        $companyID = User::get_companyID();
        $userID = User::get_userID();
        $isAdmin = (User::is_admin() || User::is('RateManager'))?1:0;
        $query = "call prc_GetDashboardProcessedFiles (".$companyID.','.$userID.','.$isAdmin.")";
        $fileResult = DataTableSql::of($query)->getProcResult(array('RecentJobFiles'));
        $jobFiles = [];
        $jsondata['jobFiles'] = '';
        if(!empty($fileResult['data']['RecentJobFiles'])) {
            foreach ($fileResult['data']['RecentJobFiles'] as $index => $jobFile) {
                $jobFiles['JobID'] = $jobFile->JobID;
                $jobFiles['Title'] = $jobFile->Title;
                $jobFiles['Status'] = $jobFile->Status;
                $jobFiles['CreatedBy'] = $jobFile->CreatedBy;
                $jobFiles['daydiff'] = \Carbon\Carbon::createFromTimeStamp(strtotime($jobFile->created_at))->diffForHumans();
                $jsondata['jobFiles'][] = $jobFiles;
            }
        }
        return json_encode($jsondata);
    }

    public function ajax_get_recent_accounts(){
        $companyID = User::get_companyID();
        $userID = User::get_userID();
        $AccountManager = 0;
        if (User::is('AccountManager')) { // Account Manager
            $AccountManager = 1;
        }
        $query = "call prc_GetDashboardRecentAccounts (".$companyID.','.$userID.','.$AccountManager.")";
        $accountResult = DataTableSql::of($query)->getProcResult(array('getRecentAccounts'));
        $accounts = [];
        $jsondata['accounts'] = '';
        if(!empty($accountResult['data']['getRecentAccounts'])) {
            foreach ($accountResult['data']['getRecentAccounts'] as $index => $account) {
                $accounts['AccountName'] = $account->AccountName;
                $accounts['Accounturl'] = URL::to('/accounts/'.$account->AccountID.'/show');
                $accounts['Phone'] = $account->Phone;
                $accounts['Email'] = ($account->Email==null || $account->Email=='null')?'':$account->Email;
                $accounts['created_by'] = $account->created_by;
                $accounts['daydiff'] = \Carbon\Carbon::createFromTimeStamp(strtotime($account->created_at))->diffForHumans();
                $jsondata['accounts'][] = $accounts;
            }
        }
        return json_encode($jsondata);
    }

    public function ajax_get_missing_accounts(){
        $companyID = User::get_companyID();
        $query = "call prc_getMissingAccounts (".$companyID.",".intval(Input::get('CompanyGatewayID')).")";
        $missingAccounts = DataTableSql::of($query, 'sqlsrv2')->getProcResult(array('getMissingAccounts'));
        $jsondata['missingAccounts']=$missingAccounts['data']['getMissingAccounts'];
        return json_encode($jsondata);
    }

}
