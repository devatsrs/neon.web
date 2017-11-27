<?php

use Webpatser\Uuid\Uuid;
use \App\Lib\TempVendorRate;
use \App\Lib\VendorRateChangeLog;

class VendorRatesController extends \BaseController
{
    
    private $trunks, $countries , $rate_sheet_formates;
    public function __construct() {
        
         $this->countries = Country::getCountryDropdownIDList("All");
         $this->rate_sheet_formates = RateSheetFormate::getVendorRateSheetFormatesDropdownList();
        
         
    }

    public function search_ajax_datagrid($id) {

        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $data['Country']=$data['Country']!= 'All'?$data['Country']:'null';
        $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';
        $data['Description'] = $data['Description'] != ''?"'".$data['Description']."'":'null';

        $columns = array('VendorRateID','Code','Description','ConnectionFee','Interval1','IntervalN','Rate','EffectiveDate','EndDate','updated_at','updated_by','VendorRateID');

        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();

        $query = "call prc_GetVendorRates (".$companyID.",".$id.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";

        return DataTableSql::of($query)->make();
        
    }

    public function index($id) {
            $Account = Account::find($id);
            $trunks = VendorTrunk::getTrunkDropdownIDList($id);
            $trunk_keys = getDefaultTrunk($trunks);
            if(count($trunks) == 0){
                return  Redirect::to('vendor_rates/'.$id.'/settings')->with('info_message', 'Please enable trunk against vendor to manage rates');
            }
        $CurrencySymbol = Currency::getCurrencySymbol($Account->CurrencyId);
            $countries = $this->countries;
            return View::make('vendorrates.index', compact('id', 'trunks', 'trunk_keys', 'countries','Account','CurrencySymbol'));
    }

     
    
    public function upload($id) {
//            $uploadtemplate = VendorFileUploadTemplate::getTemplateIDList();
            $arrData = VendorFileUploadTemplate::where(['CompanyID'=>User::get_companyID()])->orderBy('Title')->get(['Title', 'VendorFileUploadTemplateID', 'Options'])->toArray();

            $uploadtemplate=[];
            $uploadtemplate[]=[
                "Title" => "Select",
                "VendorFileUploadTemplateID" => "",
                "start_row" => "",
                "end_row" => ""
            ];

            foreach($arrData as $val)
            {
                $arrUploadTmp=[];
                $arrUploadTmp["Title"]=$val["Title"];
                $arrUploadTmp["VendorFileUploadTemplateID"]=$val["VendorFileUploadTemplateID"];

                $options=json_decode($val["Options"], true);

                if(array_key_exists("skipRows", $options))
                {
                    $arrUploadTmp["start_row"]=$options["skipRows"]["start_row"];
                    $arrUploadTmp["end_row"]=$options["skipRows"]["end_row"];
                }
                else
                {
                    $arrUploadTmp["start_row"]="";
                    $arrUploadTmp["end_row"]="";
                }
                $uploadtemplate[]=$arrUploadTmp;
            }

            $Account = Account::find($id);
            $trunks = VendorTrunk::getTrunkDropdownIDList($id);
            $trunk_keys = getDefaultTrunk($trunks);
            $dialstring = DialString::getDialStringIDList();
            $currencies = Currency::getCurrencyDropdownIDList();
            if(count($trunks) == 0){
                return  Redirect::to('vendor_rates/'.$id.'/settings')->with('info_message', 'Please enable trunk against vendor to manage rates');
            }
            $rate_sheet_formates = $this->rate_sheet_formates;
            return View::make('vendorrates.upload', compact('id', 'trunks', 'trunk_keys','rate_sheet_formates','Account','uploadtemplate','dialstring','currencies'));
    }
    
    public function process_upload($id) {
        ini_set('max_execution_time', 0);
        if (Input::hasFile('excel')) {
            
            $data = Input::all();
            if (!isset($data['Trunk']) || empty($data['Trunk'])) {
                 return json_encode(["status" => "failed", "message" =>'Please Select a Trunk' ]);
            }else if (!isset($data['uploadtemplate']) || empty($data['uploadtemplate'])) {
                return json_encode(["status" => "failed", "message" =>'Please Select an upload template' ]);
            }

            $company_name = Account::getCompanyNameByID($id);
            $upload_path = CompanyConfiguration::get('UPLOAD_PATH');
            $destinationPath = $upload_path . sprintf("/%s/", $company_name);
            $excel = Input::file('excel');
             // ->move($destinationPath);
            $ext = $excel->getClientOriginalExtension();
            if (in_array(strtolower($ext), array("csv", "xls", "xlsx"))) {
                $file_name = GUID::generate() . '.' . $excel->getClientOriginalExtension();
                $excel->move($destinationPath, $file_name);
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['VENDOR_UPLOAD']) ;
                if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
                $data['full_path'] = $fullPath;
                $data["AccountID"] = $id;
                $data['codedeckid'] = VendorTrunk::where(["AccountID" => $id,'TrunkID'=>$data['Trunk']])->pluck("CodeDeckId");
                if (!isset($data['codedeckid']) || empty($data['codedeckid'])) {
                    return json_encode(["status" => "failed", "message" =>'Please Update a Codedeck in Setting' ]);
                }
                //Inserting Job Log
                try {
                    DB::beginTransaction();
                    unset($data['excel']);
                     //remove unnecesarry object
                    $result = Job::logJob("VU", $data);
                    
                    if ($result['status'] != "success") {
                        DB::rollback();
                        return json_encode(["status" => "failed", "message" => $result['message']]);
                    }
                    DB::commit();
                    return json_encode(["status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed. "]);
                }
                catch(Exception $ex) {
                    DB::rollback();
                    return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage() ]);
                }
            } else {
                echo json_encode(array("status" => "failed", "message" => "Please upload excel/csv file only."));
            }
        } else {
            echo json_encode(array("status" => "failed", "message" => "Please upload excel/csv file <5MB."));
        }
    }
    
    public function download($id) {
            $Account = Account::find($id);
            $trunks = VendorTrunk::getTrunkDropdownIDList($id);
            if(count($trunks) == 0){
                return  Redirect::to('vendor_rates/'.$id.'/settings')->with('info_message', 'Please enable trunk against vendor to manage rates');
            }
            $rate_sheet_formates = $this->rate_sheet_formates;
            $downloadtype = [''=>'Select','xlsx'=>'EXCEL','csv'=>'CSV'];
            return View::make('vendorrates.download', compact('id', 'trunks', 'rate_sheet_formates','Account','downloadtype'));
    }
    
    public function process_download($id) {
        if (Request::ajax()) {
            
            $data = Input::all();
            
            $rules = array( 'isMerge' => 'required', 'Trunks' => 'required', 'Format' => 'required','filetype' => 'required' );
            if (!isset($data['isMerge'])) {
                $data['isMerge'] = 0;
            }


            $validator = Validator::make($data, $rules);
            
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if(!empty($data['filetype'])){
                $data['downloadtype'] = $data['filetype'];
                unset($data['filetype']);
            }

            
            //Inserting Job Log
            try {
                DB::beginTransaction();
                $data["AccountID"] = $id;
                $result = Job::logJob("VD", $data);
                
                if ($result['status'] != "success") {
                    DB::rollback();
                    return json_encode(["status" => "failed", "message" => $result['message']]);
                }
                DB::commit();
                return json_encode(["status" => "success", "message" => "File is added to queue for processing. You will be notified once file creation is completed. "]);
            }
            catch(Exception $ex) {
                DB::rollback();
                return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage() ]);
            }
        } else {
            echo json_encode(array("status" => "failed", "message" => "Access not allowed"));
        }
    }
    
    public function history($id) {
            $Account = Account::find($id);
            $trunks = VendorTrunk::getTrunkDropdownIDList($id);
            if(count($trunks) == 0){
                return  Redirect::to('vendor_rates/'.$id.'/settings')->with('info_message', 'Please enable trunk against vendor to manage rates');
            }
            return View::make('vendorrates.history', compact('id','Account'));
    }

    public function history_ajax_datagrid($id) {
        $companyID = User::get_companyID();
        
        $RateSheetHistory = RateSheetHistory::join('tblJob','tblJob.JobID','=','tblRateSheetHistory.JobID')
                                            ->leftjoin('tblJobFile','tblJob.JobID','=','tblJobFile.JobID')
                                            ->where(["tblJob.CompanyID" => $companyID, "tblJob.AccountID" => $id])
                                   ->whereRaw("(tblRateSheetHistory.Type = 'VU' OR tblRateSheetHistory.Type = 'VD') ")
                                   ->select(array('tblJob.Title', 
                                                'tblRateSheetHistory.created_at as created_date','tblRateSheetHistory.CreatedBy',
                                                'tblRateSheetHistory.RateSheetHistoryID','tblRateSheetHistory.Type','tblJob.JobID','tblJob.OutputFilePath'));
        
        return Datatables::of($RateSheetHistory)->make();
    }
    public function show_history($id,$RateSheetHistoryID) {
        
        $history = RateSheetHistory::join('tblJob','tblJob.JobID','=','tblRateSheetHistory.JobID')
                                   ->where(["tblRateSheetHistory.RateSheetHistoryID" => $RateSheetHistoryID])
                                   ->select(
                                            'tblRateSheetHistory.Title',
                                            'tblRateSheetHistory.Description',
                                            'tblRateSheetHistory.Type',
                                            'tblJob.AccountID',
                                            'tblJob.Options',
                                            'tblJob.JobStatusMessage',
                                            'tblJob.OutputFilePath',
                                            'tblRateSheetHistory.created_at as created',
                                            'tblRateSheetHistory.JobID'
                                           )
                                    ->first();
        $job_file ='';
        if(isset($history->JobID)){
            $job_file = DB::table('tblJobFile')
                ->where("tblJobFile.JobID" , $history->JobID)
                ->first();
        }

        return View::make('vendorrates.show_history',compact('id','history','job_file'));
    }
    public function history_exports($id,$type) {
            $companyID = User::get_companyID();

            $RateSheetHistory = RateSheetHistory::join('tblJob', 'tblJob.JobID', '=', 'tblRateSheetHistory.JobID')
                ->where(["tblJob.CompanyID" => $companyID, "tblJob.AccountID" => $id])
                ->whereRaw("tblRateSheetHistory.Type = 'VU' OR tblRateSheetHistory.Type = 'VD' ")
                ->orderBy("tblRateSheetHistory.RateSheetHistoryID", "DESC")
                ->get(array('tblJob.Title', 'tblRateSheetHistory.created_at as created_date',
                ));

            $excel_data = json_decode(json_encode($RateSheetHistory),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Vendor Rates History.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Vendor Rates History.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*
            Excel::create('Vendor Rates History', function ($excel) use ($RateSheetHistory) {
                $excel->sheet('Vendor Rates History', function ($sheet) use ($RateSheetHistory) {
                    $sheet->fromArray($RateSheetHistory);
                });
            })->download('xls');*/
    }
    public function exports($id,$type) {
            $data = Input::all();
            $data['iDisplayStart'] +=1;
            $data['Country']=$data['Country']!= 'All'?$data['Country']:'null';
            $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';
            $data['Description'] = $data['Description'] != ''?"'".$data['Description']."'":'null';

            $columns = array('VendorRateID','Code','Description','Rate','EffectiveDate','updated_at','updated_by','VendorRateID');
            $sort_column = $columns[$data['iSortCol_0']];
            $companyID = User::get_companyID();

            $query = "call prc_GetVendorRates (".$companyID.",".$id.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",'".$data['Effective']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',1)";

            DB::setFetchMode( PDO::FETCH_ASSOC );
            $vendor_rates  = DB::select($query);
            DB::setFetchMode( Config::get('database.fetch'));

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Vendor Rates.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($vendor_rates);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Vendor Rates.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($vendor_rates);
            }


            /*Excel::create('Vendor Rates', function ($excel) use ($vendor_rates) {
                $excel->sheet('Vendor Rates', function ($sheet) use ($vendor_rates) {
                    $sheet->fromArray($vendor_rates);
                });
            })->download('xls');*/
    }

    public function uploaded_excel_file_download($id,$JobID){
            $filePath = JobFile::where(["JobID" => $JobID])->pluck("FilePath");
            Excel::load($filePath, function ($writer) {
                $writer->setFileName(basename($writer->getFileName()));
            })->download();
    }
    public function downloaded_excel_file_download($id,$JobID){
            $filePath = JobFile::where(["JobID" => $JobID])->pluck("FilePath");
            Excel::load($filePath, function ($writer) {
                $writer->setFileName(basename($writer->getFileName()));
            })->download();
    }
    public function download_sample_excel_file(){
            $filePath =  public_path() .'/uploads/sample_upload/VendorRateUploadSample.csv';
            download_file($filePath);

    }

    // Delete single and selected vendor rate
    public function bulk_clear_rate($id){
        $data = Input::all();
        $rules = array('VendorRateID' => 'required', 'Trunk' => 'required',);

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
            //return Redirect::back()->withInput(Input::all())->withErrors($validator);
        }

        $CompanyID = User::get_companyID();

        $results = DB::statement("call prc_VendorBulkRateDelete ('".$CompanyID."','".$id."','".$data['Trunk']."','".$data['VendorRateID']."',NULL,NULL,NULL,NULL,2)");
        if ($results) {
            return Response::json(array("status" => "success", "message" => "Vendors Rate Successfully Deleted"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Vendor Rate."));
        }
        /*
        $VendorIDs = explode(",", $data['VendorRateID']);
        if (VendorRate::whereIn('VendorRateID',$VendorIDs)->delete()) {
            return Response::json(array("status" => "success", "message" => "Vendor Rates Successfully Deleted."));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Vendor Rates."));
        }*/

    }
    public function bulk_update($id){
        $data = Input::all();

        $rules = array(
            'VendorRateID' => 'required',
            'EffectiveDate' => 'required',
            'Rate' => 'required|numeric',
            'Interval1' => 'required|numeric',
            'IntervalN' => 'required|numeric',
        );

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            //return Redirect::back()->withInput(Input::all())->withErrors($validator);
            return json_validator_response($validator);
        }
        $username = User::get_user_full_name();
        $VendorIDs = explode(",", $data['VendorRateID']);
        //'Interval1'=> $data['Interval1'],'IntervalN'=> $data['IntervalN'],
        if (VendorRate::whereIn('VendorRateID',$VendorIDs)->update(['Interval1'=> $data['Interval1'],'IntervalN'=> $data['IntervalN'], 'updated_by'=>$username,'ConnectionFee'=>floatval($data['ConnectionFee']), 'EffectiveDate' => $data['EffectiveDate'],'Rate'=>$data['Rate']])) {
            return Response::json(array("status" => "success", "message" => "Vendor Rates Successfully Updated."));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Vendor Rates."));
        }

    }
    public function  bulk_update_new($id){
        $data = Input::all();
        $company_id = User::get_companyID();
        $data['vendor'] = $id;
        $rules = array('EffectiveDate' => 'required','Rate' => 'required|numeric', 'Trunk' => 'required|numeric');//'Interval1' => 'required|numeric','IntervalN' => 'required|numeric'
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $data['Country'] = $data['Country'] == 'All'?'NULL':$data['Country'];
        $data['Code'] =  $data['Code'] == ''?'NULL':"'".$data['Code']."'";
        $data['Description'] = $data['Description'] == ''?'NULL':"'".$data['Description']."'";
        $username = User::get_user_full_name();
        //Inserting Job Log
        $results = DB::statement("call prc_VendorBulkRateUpdate ('".$data['vendor']."',".$data['Trunk'].",".$data['Code'].",".$data['Description'].",".$data['Country'].",".$company_id.",".$data['Rate'].",'".$data['EffectiveDate']."','".floatval($data['ConnectionFee'])."',".intval($data['Interval1']).",".intval($data['IntervalN']).",'".$username."','".$data['Effective']."',1)");

        if ($results) {
            return Response::json(array("status" => "success", "message" => "Vendors Rate Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Vendor Rate."));
        }
    }

    // Delete All vendor rate by criteria
    public function clear_all_vendorrate($id){
        $data = Input::all();
        $rules = array('Trunk' => 'required',);

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $CompanyID = User::get_companyID();
        if(!empty($data['criteria'])){
            $criteria = json_decode($data['criteria'],true);
            $criteria['Country'] = $criteria['Country'] == 'All'?'NULL':$criteria['Country'];
            $criteria['Code'] =  $criteria['Code'] == ''?'NULL':"'".$criteria['Code']."'";
            $criteria['Description'] = $criteria['Description'] == ''?'NULL':"'".$criteria['Description']."'";
            $username = User::get_user_full_name();
            $results = DB::statement("call prc_VendorBulkRateDelete ('".$CompanyID."','".$id."','".$data['Trunk']."',NULL,".$criteria['Code'].",".$criteria['Description'].",".$criteria['Country'].",'".$criteria['Effective']."',1)");
            if ($results) {
                return Response::json(array("status" => "success", "message" => "Vendor Rates Successfully Deleted."));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Deleting Vendor Rate."));
            }
        }
    }
    public function settings($id){
            $codedecklist = BaseCodeDeck::getCodedeckIDList();
            $trunks = Trunk::getTrunkCacheObj();
            $vendor_trunks = VendorTrunk::getTrunksByTrunkAsKey($id);
            $Account = Account::find($id);
            $companygateway = CompanyGateway::getCompanyGatewayIdList();
            unset($companygateway['']);
            return View::make('vendorrates.setting', compact('id','codedecklist','Account','trunks','vendor_trunks','companygateway'));
    }
    public function  update_settings($id){
            $post_data = Input::all();
            if (!empty($post_data)) {

                $companyID = User::get_companyID();
                foreach ($post_data['VendorTrunk'] as $trunk => $data) {

                    if (isset($data['Status']) && $data['Status'] == 1) {

                        $VendorTrunk = new VendorTrunk();

                        $data['AccountID'] = $id;
                        $data['CompanyID'] = $companyID;
                        $data['TrunkID'] = $trunk;

                        //$data['Status'] = $data['Status'];
                        $data['CreatedBy'] = User::get_user_full_name();
                        $data['ModifiedBy'] = !empty($data['VendorTrunkID']) ? User::get_user_full_name() : '';
                        $data['UseInBilling'] = isset($data['UseInBilling']) ? 1 : 0;

                        $rules = array("CodeDeckId"=>"required","AccountID" => "required", "CompanyID" => "required", "TrunkID" => "required","Status" => "required");
                        $validator = Validator::make($data, $rules);

                        if ($validator->fails()) {
                            return Redirect::back()->withInput(Input::all())->withErrors($validator);
                        }


                        if (isset($data['VendorTrunkID']) && $data['VendorTrunkID'] > 0) {
                            $VendorTrunkID = $data['VendorTrunkID'];
                            unset($data['VendorTrunkID']);
                            VendorTrunk::find($VendorTrunkID)->update($data);
                        } else {
                            unset($data['VendorTrunkID']);
                            if ($VendorTrunk->insert($data)) {

                            } else {
                                return Redirect::back()->with('error_message', "Problem Creating Vendor Trunk for " . $trunk . " Trunk");
                            }
                        }
                    } else {

                        if (isset($data['VendorTrunkID']) && $data['VendorTrunkID'] > 0) {
                            $VendorTrunkID = $data['VendorTrunkID'];
                            VendorTrunk::find($VendorTrunkID)->update(['Status' => 0]);
                        }
                    }
                }
                //Success
                return Redirect::back()->with('success_message', "Vendor Trunk Saved");
            }
    }

    //Delete vendor rate when codedeck change in setting page
    public function  delete_vendorrates($id){
            $data = Input::all();
            $rules = array('Trunkid' => 'required');
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if(isset($data['action']) && $data['action']=='check_count'){
                return VendorRate::where(["AccountID" =>$id ,'TrunkID'=>$data['Trunkid']])->count();
            }

            $CompanyID = User::get_companyID();
            $results = DB::statement("call prc_VendorBulkRateDelete ('".$CompanyID."','".$id."','".$data['Trunkid']."',NULL,NULL,NULL,NULL,NULL,2)");
            if ($results) {
                return Response::json(array("status" => "success", "message" => "Vendor Rates Successfully Deleted."));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Deleting Vendor Rate."));
            }
    }
    public function vendor_preference($id){
            $Account = Account::find($id);
            $trunks = VendorTrunk::getTrunkDropdownIDList($id);
            $trunk_keys = getDefaultTrunk($trunks);
            if(count($trunks) == 0){
                return  Redirect::to('vendor_rates/'.$id.'/settings')->with('info_message', 'Please enable trunk against vendor to manage rates');
            }
            $countries = $this->countries;
            return View::make('vendorrates.preference', compact('id', 'trunks', 'trunk_keys', 'countries','Account'));
    }
    public function search_ajax_datagrid_preference($id,$type) {


        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $data['Country']=$data['Country']!= 'All'?$data['Country']:'null';
        $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';
        $data['Description'] = $data['Description'] != ''?"'".$data['Description']."'":'null';


        $columns = array('RateID','Code','Preference','Description','VendorPreferenceID');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();

        $query = "call prc_GetVendorPreference (".$companyID.",".$id.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Vendor Preference.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Vendor Preference.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

        }
        $query .=',0)';
        return DataTableSql::of($query)->make();

    }
    public function bulk_update_preference($id){
        Log::info("vendor bulk update start : ". $id);
        $data = Input::all();
        if(empty($data['Preference'])){
            $data['Preference']='0';
         //   return Response::json(array("status" => "failed", "message" => "Please Insert Preference."));
        }
        $company_id = User::get_companyID();
        $username = User::get_user_full_name();

        if($data['Action'] == 'bulk'){
            $data['Country'] = $data['Country']!= 'All'?$data['Country']:'null';
            $data['Code'] = $data['Code'] != ''?"'".$data['Code']."'":'null';
            $data['Description'] = $data['Description'] != ''?"'".$data['Description']."'":'null';
            /*$exceldatas  = DB::select("call prc_GetVendorPreference( ".$company_id.",".$id.",".$data['Trunk'].",".$data['Country'].",".$data['Code'].",".$data['Description'].",0,0,'','',2)");
            $exceldatas = json_decode(json_encode($exceldatas),true);
            $RateID='';
            foreach($exceldatas as $exceldata){
                $RateID.= $exceldata['RateID'].',';
            }
            $RateID = rtrim($RateID,',');*/
            try{
                Log::info("prc_VendorPreferenceUpdateBySelectedRateId (".$company_id.",'".$id."','',".$data['Trunk'].",".$data['Preference'].",'".$username."',".$data['Country'].",".$data['Code'].",".$data['Description'].",1)");
                DB::statement("call prc_VendorPreferenceUpdateBySelectedRateId (".$company_id.",'".$id."','',".$data['Trunk'].",".$data['Preference'].",'".$username."',".$data['Country'].",".$data['Code'].",".$data['Description'].",1)");
                Log::info("vendor bulk update end");
                return Response::json(array("status" => "success", "message" => "Vendor Preference Updated Successfully"));
            }catch ( Exception $ex ){
                return Response::json(array("status" => "failed", "message" => "Error Updating Vendor Preference."));
            }
        }else{
            $RateID = $data['RateID'];
            if(!empty($RateID)){
                try{
                    Log::info("prc_VendorPreferenceUpdateBySelectedRateId (".$company_id.",'".$id."','".$RateID."',".$data['Trunk'].",".$data['Preference'].",'".$username."',null,null,null,0)");
                    DB::statement("call prc_VendorPreferenceUpdateBySelectedRateId (".$company_id.",'".$id."','".$RateID."',".$data['Trunk'].",".$data['Preference'].",'".$username."',null,null,null,0)");
                    Log::info("vendor bulk update end");
                    return Response::json(array("status" => "success", "message" => "Vendor Preference Updated Successfully"));
                }catch ( Exception $ex ){
                    return Response::json(array("status" => "failed", "message" => "Error Updating Vendor Preference."));
                }

            }else{

                return Response::json(array("status" => "failed", "message" => "Problem Updating Vendor Preference."));
            }
        }

    }

    function ajaxfilegrid(){
        try {
            $data = Input::all();
            $data['Delimiter'] = $data['option']['Delimiter'];
            $data['Enclosure'] = $data['option']['Enclosure'];
            $data['Escape'] = $data['option']['Escape'];
            $data['Firstrow'] = $data['option']['Firstrow'];
            $file_name = $data['TempFileName'];
            $grid = getFileContent($file_name, $data);
            $grid['filename'] = $data['TemplateFile'];
            $grid['tempfilename'] = $data['TempFileName'];
            if ($data['uploadtemplate'] > 0) {
                $VendorFileUploadTemplate = VendorFileUploadTemplate::find($data['uploadtemplate']);
                $grid['VendorFileUploadTemplate'] = json_decode(json_encode($VendorFileUploadTemplate), true);
                //$grid['VendorFileUploadTemplate']['Options'] = json_decode($VendorFileUploadTemplate->Options,true);
            }
            $grid['VendorFileUploadTemplate']['Options'] = array();
            $grid['VendorFileUploadTemplate']['Options']['option'] = $data['option'];
            $grid['VendorFileUploadTemplate']['Options']['selection'] = $data['selection'];
            return Response::json(array("status" => "success", "data" => $grid));
        } catch (Exception $ex) {
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }

    public function storeTemplate($id) {
        $data = Input::all();
        $CompanyID = User::get_companyID();

        $rules['selection.Code'] = 'required';
        $rules['selection.Description'] = 'required';
        $rules['selection.Rate'] = 'required';
        //$rules['selection.EffectiveDate'] = 'required';
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if(isset($data['selection']['FromCurrency']) && !empty($data['selection']['FromCurrency'])) {
            $CompanyCurrency = Company::find($CompanyID)->CurrencyId;

            $error = array();
            if(!($CompanyCurrency && !empty($CompanyCurrency))) {
                $error['status'] = "failed";
                $error['message'] = "You have not setup your base currency, please select it under company page if you want to convert rates.<br/>";
            } else {
                $ACID = Account::find($id)->CurrencyId;
                $CompanyConversionRate = CurrencyConversion::where(['CurrencyID' => $CompanyCurrency, 'CompanyID' => $CompanyID])->count();
                $FileConversionRate = CurrencyConversion::where(['CurrencyID' => $data['selection']['FromCurrency'], 'CompanyID' => $CompanyID])->count();
                $AccountConversionRate = CurrencyConversion::where(['CurrencyID' => $ACID, 'CompanyID' => $CompanyID])->count();

                $error['message'] = "";
                $CurrencyCode = array();
                if(empty($CompanyConversionRate)) {
                    $CurrencyCode[] = Currency::find($CompanyCurrency)->Code;
                }
                if(empty($FileConversionRate)) {
                    $CurrencyCode[] = Currency::find($data['selection']['FromCurrency'])->Code;
                }
                if(empty($AccountConversionRate)) {
                    $CurrencyCode[] = Currency::find($ACID)->Code;
                }

                if(count($CurrencyCode) > 0) {
                    $CurrencyCode = array_unique($CurrencyCode);
                    $error['status'] = "failed";

                    foreach ($CurrencyCode as $Code) {
                        $error['message'] .= "You have not setup your currency (".$Code.") conversion rate, please set it up under setting -> exchange rate.<br/>";
                    }
                }

            }

            if(isset($error['status']) && $error['status'] == 'failed') {
                return json_encode($error);
            }
        }
        $data['codedeckid'] = VendorTrunk::where(["AccountID" => $id, 'TrunkID' => $data['Trunk']])->pluck("CodeDeckId");
        if (!isset($data['codedeckid']) || empty($data['codedeckid'])) {
            return json_encode(["status" => "failed", "message" => 'Please Update a Codedeck in Setting']);
        }
        $file_name = basename($data['TemplateFile']);

        $temp_path = CompanyConfiguration::get('TEMP_PATH').'/' ;

        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['VENDOR_UPLOAD']);
 
        $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;

        copy($temp_path . $file_name, $destinationPath . $file_name);
        if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
            return Response::json(array("status" => "failed", "message" => "Failed to upload vendor rates file."));
        }
        $option["skipRows"] = array( "start_row"=>$data["start_row"], "end_row"=>$data["end_row"] );
        if(!empty($data['TemplateName'])){
            $save = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . $file_name];
            $save['created_by'] = User::get_user_full_name();
            $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"] = $data['selection'];//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
            $save['Options'] = json_encode($option);
            if (isset($data['uploadtemplate']) && $data['uploadtemplate'] > 0) {
                $template = VendorFileUploadTemplate::find($data['uploadtemplate']);
                $template->update($save);
            } else {
                $template = VendorFileUploadTemplate::create($save);
            }
            $data['uploadtemplate'] = $template->VendorFileUploadTemplateID;
        }
        $save = array();
        $option["option"]=  $data['option'];
        $option["selection"] = $data['selection'];
        $save['Options'] = json_encode($option);
        $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
        $save['full_path'] = $fullPath;
        $save["AccountID"] = $id;
        $save['codedeckid'] = $data['codedeckid'];
        if(isset($data['uploadtemplate'])) {
            $save['uploadtemplate'] = $data['uploadtemplate'];
        }
        $save['Trunk'] = $data['Trunk'];
        $save['checkbox_replace_all'] = $data['checkbox_replace_all'];
        $save['checkbox_rates_with_effected_from'] = $data['checkbox_rates_with_effected_from'];
        $save['checkbox_add_new_codes_to_code_decks'] = $data['checkbox_add_new_codes_to_code_decks'];
        $save['checkbox_review_rates'] = $data['checkbox_review_rates'];
        $save['radio_list_option'] = $data['radio_list_option'];
        if(!empty($data['ProcessID'])) {
            $save['ProcessID'] = $data['ProcessID'];
        }

        //Inserting Job Log
        try {
            DB::beginTransaction();
            //remove unnecesarry object
            $result = Job::logJob("VU", $save);
            if ($result['status'] != "success") {
                DB::rollback();
                return json_encode(["status" => "failed", "message" => $result['message']]);
            }
            DB::commit();
            @unlink($temp_path . $file_name);
            return json_encode(["status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed. "]);
        } catch (Exception $ex) {
            DB::rollback();
            return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
        }
    }

    public function check_upload() {
        try {
            $data = Input::all();
            if (!isset($data['Trunk']) || empty($data['Trunk'])) {
                return json_encode(["status" => "failed", "message" => 'Please Select a Trunk']);
            } else if (Input::hasFile('excel')) {
                $upload_path = CompanyConfiguration::get('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array(strtolower($ext), array("csv", "xls", "xlsx"))) {
                    $file_name_without_ext = GUID::generate();
                    $file_name = $file_name_without_ext . '.' . $excel->getClientOriginalExtension();
                    $excel->move($upload_path, $file_name);
                    $file_name = $upload_path . '/' . $file_name;
                } else {
                    return Response::json(array("status" => "failed", "message" => "Please select excel or csv file."));
                }
            } else if (isset($data['TemplateFile'])) {
                $file_name = $data['TemplateFile'];
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select a file."));
            }
            if (!empty($file_name)) {

                if ($data['uploadtemplate'] > 0) {
                    $VendorFileUploadTemplate = VendorFileUploadTemplate::find($data['uploadtemplate']);
                    $options = json_decode($VendorFileUploadTemplate->Options, true);
                    $data['Delimiter'] = $options['option']['Delimiter'];
                    $data['Enclosure'] = $options['option']['Enclosure'];
                    $data['Escape'] = $options['option']['Escape'];
                    $data['Firstrow'] = $options['option']['Firstrow'];
                }

                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;//$upload_path.'\\'.'temp.'.$ext;
                $grid['filename'] = $file_name;

                $grid['start_row'] = $data["start_row"];
                $grid['end_row'] = $data["end_row"];

                if (!empty($VendorFileUploadTemplate)) {
                    $grid['VendorFileUploadTemplate'] = json_decode(json_encode($VendorFileUploadTemplate), true);
                    $grid['VendorFileUploadTemplate']['Options'] = json_decode($VendorFileUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "data" => $grid));
            }
        }catch(Exception $ex) {
		Log::info($ex);
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }
    /** search grid used for vendor*/
    public  function search_vendor_grid($id){
        $CompanyID = User::get_companyID();
        $data = Input::all();
        $UserID = 0;
        $SelectedCodes = 0;
        $isCountry = 1;
        $countries = 0;
        $isall = 0;
        $criteria =0;

        if (User::is('AccountManager')) {
            $UserID = User::get_userID();
        }
        if (isset($data['OwnerFilter']) && $data['OwnerFilter'] != 0) {
            $UserID = $data['OwnerFilter'];
        }

        //block by contry
        if(isset($data['block_by']) && $data['block_by']=='country')
        {
            $isCountry=1;
            if(in_array(0,explode(',',$data['Country']))){
                $isall = 1;
            }elseif(!empty($data['Country'])){
                $isall = 0;
                $countries = $data['Country'];
            }

        }

        //block by code
        if(isset($data['block_by']) && $data['block_by']=='code')
        {
            $isCountry=0;
            $isall = 0;
            // by critearia
            if(!empty($data['criteria']) && $data['criteria']==1){
                if(!empty($data['Code']) || !empty($data['Country'])){
                    if(!empty($data['Code'])){
                        $criteria = 1;
                        $SelectedCodes = $data['Code'];
                    }else{
                        $criteria = 2;
                        if(!empty($data['Country'])){
                            $isall = 0;
                            $countries = $data['Country'];
                        }
                    }
                }else{
                    $criteria = 3;
                }

            }elseif(!empty($data['SelectedCodes'])){
                //by code
                $SelectedCodes = $data['SelectedCodes'];
                $criteria = 0;
            }

        }

        if($data['action'] == 'block'){
            $data['action'] = 0;
        }else{
            $data['action'] = 1;
        }

        $query = "call prc_GetBlockUnblockVendor (".$CompanyID.",".$UserID.",".$data['Trunk'].",'".$countries."','".$SelectedCodes."',".$isCountry.",".$data['action'].",".$isall.",".$criteria.")";
        //$accounts = DataTableSql::of($query)->getProcResult(array('AccountID','AccountName'));
        //return $accounts->make();
        return DataTableSql::of($query)->make();
    }

    public function vendordownloadtype($id,$type){
        if($type==RateSheetFormate::RATESHEET_FORMAT_VOS32 || $type==RateSheetFormate::RATESHEET_FORMAT_VOS20){
            $downloadtype = '<option value="">Select</option><option value="txt">TXT</option>';
        }else{
            $downloadtype = '<option value="">Select</option><option value="xlsx">EXCEL</option><option value="csv">CSV</option>';
        }
        return $downloadtype;
    }

    public function reviewRates($id) {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $ProcessID = Uuid::generate();
        $bacth_insert_limit = 250;
        $counter = 0;
        $p_forbidden = 0;
        $p_preference = 0;
        $DialStringId = 0;
        $dialcode_separator = 'null';
        //$TEMP_PATH = CompanyConfiguration::get($CompanyID,'TEMP_PATH').'/';

        $rules['selection.Code'] = 'required';
        $rules['selection.Description'] = 'required';
        $rules['selection.Rate'] = 'required';
        //$rules['selection.EffectiveDate'] = 'required';
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if(isset($data['selection']['FromCurrency']) && !empty($data['selection']['FromCurrency'])) {
            $CompanyCurrency = Company::find($CompanyID)->CurrencyId;

            $error = array();
            if(!($CompanyCurrency && !empty($CompanyCurrency))) {
                $error['status'] = "failed";
                $error['message'] = "You have not setup your base currency, please select it under company page if you want to convert rates.<br/>";
            } else {
                $ACID = Account::find($id)->CurrencyId;
                $CompanyConversionRate = CurrencyConversion::where(['CurrencyID' => $CompanyCurrency, 'CompanyID' => $CompanyID])->count();
                $FileConversionRate = CurrencyConversion::where(['CurrencyID' => $data['selection']['FromCurrency'], 'CompanyID' => $CompanyID])->count();
                $AccountConversionRate = CurrencyConversion::where(['CurrencyID' => $ACID, 'CompanyID' => $CompanyID])->count();

                $error['message'] = "";
                $CurrencyCode = array();
                if(empty($CompanyConversionRate)) {
                    $CurrencyCode[] = Currency::find($CompanyCurrency)->Code;
                }
                if(empty($FileConversionRate)) {
                    $CurrencyCode[] = Currency::find($data['selection']['FromCurrency'])->Code;
                }
                if(empty($AccountConversionRate)) {
                    $CurrencyCode[] = Currency::find($ACID)->Code;
                }

                if(count($CurrencyCode) > 0) {
                    $CurrencyCode = array_unique($CurrencyCode);
                    $error['status'] = "failed";

                    foreach ($CurrencyCode as $Code) {
                        $error['message'] .= "You have not setup your currency (".$Code.") conversion rate, please set it up under setting -> exchange rate.<br/>";
                    }
                }

            }

            if(isset($error['status']) && $error['status'] == 'failed') {
                return json_encode($error);
            }
        }
        $data['codedeckid'] = VendorTrunk::where(["AccountID" => $id, 'TrunkID' => $data['Trunk']])->pluck("CodeDeckId");
        if (!isset($data['codedeckid']) || empty($data['codedeckid'])) {
            return json_encode(["status" => "failed", "message" => 'Please Update a Codedeck in Setting']);
        }
        $file_name = basename($data['TemplateFile']);

        $temp_path = CompanyConfiguration::get('TEMP_PATH').'/' ;

        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['VENDOR_UPLOAD']);

        $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;

        copy($temp_path . $file_name, $destinationPath . $file_name);
        if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
            return Response::json(array("status" => "failed", "message" => "Failed to upload vendor rates file."));
        }
        $option["skipRows"] = array( "start_row"=>$data["start_row"], "end_row"=>$data["end_row"] );
        if(!empty($data['TemplateName'])){
            $save = ['CompanyID' => $CompanyID, 'Title' => $data['TemplateName'], 'TemplateFile' => $amazonPath . $file_name];
            $save['created_by'] = User::get_user_full_name();
            $option["option"] = $data['option'];  //['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
            $option["selection"] = $data['selection'];//['Code'=>$data['Code'],'Description'=>$data['Description'],'Rate'=>$data['Rate'],'EffectiveDate'=>$data['EffectiveDate'],'Action'=>$data['Action'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN'],'ConnectionFee'=>$data['ConnectionFee']];
            $save['Options'] = json_encode($option);
            if (isset($data['uploadtemplate']) && $data['uploadtemplate'] > 0) {
                $template = VendorFileUploadTemplate::find($data['uploadtemplate']);
                $template->update($save);
            } else {
                $template = VendorFileUploadTemplate::create($save);
            }
            $data['uploadtemplate'] = $template->VendorFileUploadTemplateID;
        }

        $save = array();
        $option["option"]=  $data['option'];
        $option["selection"] = $data['selection'];
        $save['Options'] = json_encode($option);
        $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
        $save['full_path'] = $fullPath;
        $save["AccountID"] = $id;
        $save['codedeckid'] = $data['codedeckid'];
        if(isset($data['uploadtemplate'])) {
            $save['uploadtemplate'] = $data['uploadtemplate'];
        }
        $save['Trunk'] = $data['Trunk'];
        $save['checkbox_replace_all'] = $data['checkbox_replace_all'];
        $save['checkbox_rates_with_effected_from'] = $data['checkbox_rates_with_effected_from'];
        $save['checkbox_add_new_codes_to_code_decks'] = $data['checkbox_add_new_codes_to_code_decks'];
        $save['checkbox_review_rates'] = $data['checkbox_review_rates'];
        $save['radio_list_option'] = $data['radio_list_option'];

        $jobdata = array();
        $joboptions = json_decode(json_encode($save));
        if (count($joboptions) > 0) {
            if(isset($joboptions->uploadtemplate) && !empty($joboptions->uploadtemplate)){
                $uploadtemplate = VendorFileUploadTemplate::find($joboptions->uploadtemplate);
                $templateoptions = json_decode($uploadtemplate->Options);
            }else{
                $templateoptions = json_decode($joboptions->Options);
            }
            $csvoption = $templateoptions->option;
            $attrselection = $templateoptions->selection;

            // check dialstring mapping or not
            if(isset($attrselection->DialString) && !empty($attrselection->DialString))
            {
                $DialStringId = $attrselection->DialString;
            }else{
                $DialStringId = 0;
            }
            if(isset($attrselection->Forbidden) && !empty($attrselection->Forbidden)){
                $p_forbidden = 1;
            }
            if(isset($attrselection->Preference) && !empty($attrselection->Preference)){
                $p_preference = 1;
            }

            if(isset($attrselection->DialCodeSeparator)){
                if($attrselection->DialCodeSeparator == ''){
                    $dialcode_separator = 'null';
                }else{
                    $dialcode_separator = $attrselection->DialCodeSeparator;
                }
            }else{
                $dialcode_separator = 'null';
            }

            if (isset($attrselection->FromCurrency) && !empty($attrselection->FromCurrency)) {
                $CurrencyConversion = 1;
                $CurrencyID = $attrselection->FromCurrency;
            }else{
                $CurrencyConversion = 0;
                $CurrencyID = 0;
            }

            if ($fullPath) {
                $path = AmazonS3::unSignedUrl($fullPath,$CompanyID);
                if (strpos($path, "https://") !== false) {
                    $file = $temp_path . basename($path);
                    file_put_contents($file, file_get_contents($path));
                    $FilePath = $file;
                } else {
                    $FilePath = $path;
                }
            };

            if(isset($templateoptions->skipRows))
            {
                $skiptRows=$templateoptions->skipRows;
                NeonExcelIO::$start_row=$skiptRows->start_row;
                NeonExcelIO::$end_row=$skiptRows->end_row;
                $lineno = 1+$skiptRows->start_row;
            }else if ($csvoption->Firstrow == 'data') {
                $lineno = 1;
            }else{
                $lineno = 2;
            }

            $NeonExcel = new NeonExcelIO($FilePath, (array) $csvoption);
            $results = $NeonExcel->read();
             

            $error = array();
            $batch_insert_array = [];

            foreach ($results as $index=>$temp_row) {

                if ($csvoption->Firstrow == 'data') {
                    array_unshift($temp_row, null);
                    unset($temp_row[0]);
                }

                $tempvendordata = array();
                $tempvendordata['codedeckid'] = $joboptions->codedeckid;
                $tempvendordata['ProcessId'] = (string) $ProcessID;

                //check empty row
                $checkemptyrow = array_filter(array_values($temp_row));
                if(!empty($checkemptyrow)){
                    if (isset($attrselection->CountryCode) && !empty($attrselection->CountryCode) && !empty($temp_row[$attrselection->CountryCode])) {
                        $tempvendordata['CountryCode'] = trim($temp_row[$attrselection->CountryCode]);
                    }else{
                        $tempvendordata['CountryCode'] = '';
                    }

                    if (isset($attrselection->Code) && !empty($attrselection->Code) && trim($temp_row[$attrselection->Code]) != '') {
                        $tempvendordata['Code'] = trim($temp_row[$attrselection->Code]);
                    }else if (isset($attrselection->CountryCode) && !empty($attrselection->CountryCode) && !empty($temp_row[$attrselection->CountryCode])) {
                        $tempvendordata['Code'] = "";  // if code is blank but country code is not blank than mark code as blank., it will be merged with countr code later ie 91 - 1 -> 911
                    } else {
                        $error[] = 'Code is blank at line no:'.$lineno;
                    }

                    if (isset($attrselection->Description) && !empty($attrselection->Description) && !empty($temp_row[$attrselection->Description])) {
                        $tempvendordata['Description'] = $temp_row[$attrselection->Description];
                    }else{
                        $error[] = 'Description is blank at line no:'.$lineno;
                    }
                    if (isset($attrselection->Rate) && !empty($attrselection->Rate) && is_numeric(trim($temp_row[$attrselection->Rate]))  ) {
                        if(is_numeric(trim($temp_row[$attrselection->Rate]))) {
                            $tempvendordata['Rate'] = trim($temp_row[$attrselection->Rate]);
                        }else{
                            $error[] = 'Rate is not numeric at line no:'.$lineno;
                        }
                    }else{
                        $error[] = 'Rate is blank at line no:'.$lineno;
                    }
					if (isset($attrselection->Action) && !empty($attrselection->Action)) {
                        if(empty($temp_row[$attrselection->Action])){
                            $tempvendordata['Change'] = 'I';
                        }else{
                            $action_value = $temp_row[$attrselection->Action];
                            if (isset($attrselection->ActionDelete) && !empty($attrselection->ActionDelete) && trim(strtolower($action_value)) == trim(strtolower($attrselection->ActionDelete)) ) {
                                $tempvendordata['Change'] = 'D';
                            }else if (isset($attrselection->ActionUpdate) && !empty($attrselection->ActionUpdate) && trim(strtolower($action_value)) == trim(strtolower($attrselection->ActionUpdate))) {
                                $tempvendordata['Change'] = 'U';
                            }else if (isset($attrselection->ActionInsert) && !empty($attrselection->ActionInsert) && trim(strtolower($action_value)) == trim(strtolower($attrselection->ActionInsert))) {
                                $tempvendordata['Change'] = 'I';
                            }else{
                                $tempvendordata['Change'] = 'I';
                            }
                        }

                    }else{
                        $tempvendordata['Change'] = 'I';
                    }
					
                    if (isset($attrselection->EffectiveDate) && !empty($attrselection->EffectiveDate) && !empty($temp_row[$attrselection->EffectiveDate])) {
                        try {
                            $tempvendordata['EffectiveDate'] = formatSmallDate(str_replace( '/','-',$temp_row[$attrselection->EffectiveDate]), $attrselection->DateFormat);
                        }catch (\Exception $e){
                            $error[] = 'Date format is Wrong  at line no:'.$lineno;
                        }
                    }elseif(empty($attrselection->EffectiveDate)){
                        $tempvendordata['EffectiveDate'] = date('Y-m-d');
                    }elseif($tempvendordata['Change'] != 'D') {
                        $error[] = 'EffectiveDate is blank at line no:'.$lineno;
                    }
                    if (isset($attrselection->EndDate) && !empty($attrselection->EndDate) && !empty($temp_row[$attrselection->EndDate])) {
                        try {
                            $tempvendordata['EndDate'] = formatSmallDate(str_replace( '/','-',$temp_row[$attrselection->EndDate]), $attrselection->DateFormat);
                        }catch (\Exception $e){
                            $error[] = 'Date format is Wrong  at line no:'.$lineno;
                        }
                    }

                     

                    if (isset($attrselection->ConnectionFee) && !empty($attrselection->ConnectionFee)) {
                        $tempvendordata['ConnectionFee'] = trim($temp_row[$attrselection->ConnectionFee]);
                    }
                    if (isset($attrselection->Interval1) && !empty($attrselection->Interval1)) {
                        $tempvendordata['Interval1'] = trim($temp_row[$attrselection->Interval1]);
                    }
                    if (isset($attrselection->IntervalN) && !empty($attrselection->IntervalN)) {
                        $tempvendordata['IntervalN'] = trim($temp_row[$attrselection->IntervalN]);
                    }
                    if (isset($attrselection->Preference) && !empty($attrselection->Preference)) {
                        $tempvendordata['Preference'] = trim($temp_row[$attrselection->Preference]);
                    }
                    if (isset($attrselection->Forbidden) && !empty($attrselection->Forbidden)) {
                        $Forbidden = trim($temp_row[$attrselection->Forbidden]);
                        if($Forbidden=='0'){
                            $tempvendordata['Forbidden'] = 'UB';
                        }elseif($Forbidden=='1'){
                            $tempvendordata['Forbidden'] = 'B';
                        }else{
                            $tempvendordata['Forbidden'] = '';
                        }
                    }
                    if(!empty($DialStringId)){
                        if (isset($attrselection->DialStringPrefix) && !empty($attrselection->DialStringPrefix)) {
                            $tempvendordata['DialStringPrefix'] = trim($temp_row[$attrselection->DialStringPrefix]);
                        } else {
                            $tempvendordata['DialStringPrefix'] = '';
                        }
                    }
                    if(isset($tempvendordata['Code']) && isset($tempvendordata['Description']) && isset($tempvendordata['Rate']) && ( isset($tempvendordata['EffectiveDate']) || $tempvendordata['Change'] == 'D') ){
                        $batch_insert_array[] = $tempvendordata;
                        $counter++;
                    }
                }

                if($counter==$bacth_insert_limit){
                    Log::info('Batch insert start');
                    Log::info('global counter'.$lineno);
                    Log::info('insertion start');
                    TempVendorRate::insert($batch_insert_array);
                    Log::info('insertion end');
                    $batch_insert_array = [];
                    $counter = 0;
                }
                $lineno++;
            } // loop over

            if(!empty($batch_insert_array)){
                Log::info('Batch insert start');
                Log::info('global counter'.$lineno);
                Log::info('insertion start');
                Log::info('last batch insert ' . count($batch_insert_array));
                TempVendorRate::insert($batch_insert_array);
                Log::info('insertion end');
            }

            $JobStatusMessage = array();
            $duplicatecode=0;

            Log::info("start CALL  prc_WSReviewVendorRate ('" . $save['AccountID'] . "','" . $save['Trunk'] . "'," . $save['checkbox_replace_all'] . ",'" . $save['checkbox_rates_with_effected_from'] . "','" . $ProcessID . "','" . $save['checkbox_add_new_codes_to_code_decks'] . "','" . $CompanyID . "','".$p_forbidden."','".$p_preference."','".$DialStringId."','".$dialcode_separator."',".$CurrencyID.",".$save['radio_list_option'].")");

            try{
                DB::beginTransaction();
                $JobStatusMessage = DB::select("CALL  prc_WSReviewVendorRate ('" . $save['AccountID'] . "','" . $save['Trunk'] . "'," . $save['checkbox_replace_all'] . ",'" . $save['checkbox_rates_with_effected_from'] . "','" . $ProcessID . "','" . $save['checkbox_add_new_codes_to_code_decks'] . "','" . $CompanyID . "','".$p_forbidden."','".$p_preference."','".$DialStringId."','".$dialcode_separator."',".$CurrencyID.",".$save['radio_list_option'].")");
                Log::info("end CALL  prc_WSReviewVendorRate ('" . $save['AccountID'] . "','" . $save['Trunk'] . "'," . $save['checkbox_replace_all'] . ",'" . $save['checkbox_rates_with_effected_from'] . "','" . $ProcessID . "','" . $save['checkbox_add_new_codes_to_code_decks'] . "','" . $CompanyID . "','".$p_forbidden."','".$p_preference."','".$DialStringId."','".$dialcode_separator."',".$CurrencyID.",".$save['radio_list_option'].")");
                DB::commit();

                $JobStatusMessage = array_reverse(json_decode(json_encode($JobStatusMessage),true));
                Log::info($JobStatusMessage);
                Log::info(count($JobStatusMessage));

                if(!empty($error) || count($JobStatusMessage) > 1){
                    $prc_error = array();
                    foreach ($JobStatusMessage as $JobStatusMessage1) {
                        $prc_error[] = $JobStatusMessage1['Message'];
                        if(strpos($JobStatusMessage1['Message'], 'DUPLICATE CODE') !==false || strpos($JobStatusMessage1['Message'], 'No PREFIX FOUND') !==false){
                            $duplicatecode = 1;
                        }
                    }

                    // if duplicate code exit job will fail
                    if($duplicatecode == 1){
                        $error = array_merge($prc_error,$error);
                        unset($error[0]);
                        $jobdata['message'] = implode('<br>',fix_jobstatus_meassage($error));
                        $jobdata['JobStatusID'] = DB::table('tblJobStatus')->where('Code','F')->pluck('JobStatusID');
                    }else{
                        $error = array_merge($prc_error,$error);
                        $jobdata['message'] = implode('<br>',fix_jobstatus_meassage($error));
                        $jobdata['JobStatusID'] = DB::table('tblJobStatus')->where('Code','PF')->pluck('JobStatusID');
                    }
                    $jobdata['status'] = "failed";

                }elseif(empty($JobStatusMessage)){
                    $jobdata['status'] = "success";
                    $jobdata['ProcessID'] = (string) $ProcessID;
                    $jobdata['message'] = "Review Rates Successfully!";
                    $jobdata['JobStatusID'] = DB::table('tblJobStatus')->where('Code','S')->pluck('JobStatusID');
                }

            }catch ( Exception $err ){
                DB::rollback();
                $jobdata['JobStatusID'] = DB::table('tblJobStatus')->where('Code', 'F')->pluck('JobStatusID');
                $jobdata['message'] = 'Exception: ' . $err->getMessage();
                $jobdata['status'] = "failed";
                Log::error($err);
            }
        }

        return json_encode($jobdata);
    }

    public function getReviewRates() {
        $data = Input::all();
        $data['iDisplayStart'] +=1;

        $columns = array('Code','Description','Rate','EffectiveDate','EndDate','ConnectionFee','Interval1','IntervalN');
        $sort_column = $columns[$data['iSortCol_0']];

        $data['Code'] = !empty($data['Code']) ? $data['Code'] : NULL;
        $data['Description'] = !empty($data['Description']) ? $data['Description'] : NULL;

        $query = "call prc_getReviewVendorRates ('".$data['ProcessID']."','".$data['Action']."','".$data['Code']."','".$data['Description']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',0)";
        Log::info($query);

        return DataTableSql::of($query)->make();
    }

    public function reviewRatesExports($id,$type) {
        $data = Input::all();
        $data['iDisplayStart'] +=1;

        $data['Code'] = !empty($data['Code']) ? $data['Code'] : NULL;
        $data['Description'] = !empty($data['Description']) ? $data['Description'] : NULL;

        $columns = array('Code','Description','Rate','EffectiveDate','EndDate','ConnectionFee','Interval1','IntervalN');
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_getReviewVendorRates ('".$data['ProcessID']."','".$data['Action']."','".$data['Code']."','".$data['Description']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."',1)";
        Log::info($query);

        DB::setFetchMode( PDO::FETCH_ASSOC );
        $review_vendor_rates = DB::select($query);
        DB::setFetchMode( Config::get('database.fetch'));

        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Review Vendor Rates.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($review_vendor_rates);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Review Vendor Rates.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($review_vendor_rates);
        }

        /*Excel::create('Vendor Rates', function ($excel) use ($vendor_rates) {
            $excel->sheet('Vendor Rates', function ($sheet) use ($vendor_rates) {
                $sheet->fromArray($vendor_rates);
            });
        })->download('xls');*/
    }

    public function updateTempVendorRates($AccountID) {
        $data = Input::all();

        $ProcessID = $data['ProcessID'];
        $TrunkID   = 0;

        if($data['Action'] == 'New') {
            $TempRateIDs = array_filter(explode(',',$data['TempRateIDs']),'intval');
        } else if($data['Action'] == 'Deleted') {
            $TempRateIDs = array_filter(explode(',',$data['VendorRateIDs']),'intval');
            $TrunkID     = $data['TrunkID'];
        }

        if (is_array($TempRateIDs) && count($TempRateIDs) || !empty($data['criteria'])) {
            $criteria = !empty($data['criteria']) && (int) $data['criteria'] == 1 ? 1 : 0;
            $Action = '';
            $Interval1 = $IntervalN = 0;
            $EndDate = date('Y-m-d H:i:s');

            if($data['Action'] == 'New') {
                if (!empty($data['updateInterval1']) || !empty($data['updateIntervalN'])) {
                    if (!empty($data['updateInterval1']) && empty((int)$data['Interval1'])) {
                        return json_encode(array("status" => "Error", "message" => "Please enter Interval1 value."));
                    } else if (!empty($data['updateInterval1']) && !empty((int)$data['Interval1'])) {
                        $Interval1 = (int)$data['Interval1'] > 0 ? (int)$data['Interval1'] : 0;
                    }
                    if (!empty($data['updateIntervalN']) && empty((int)$data['IntervalN'])) {
                        return json_encode(array("status" => "Error", "message" => "Please enter IntervalN value."));
                    } else if (!empty($data['updateIntervalN']) && !empty((int)$data['IntervalN'])) {
                        $IntervalN = (int)$data['IntervalN'] > 0 ? (int)$data['IntervalN'] : 0;
                    }
                    $Action = $data['Action'];
                } else {
                    return json_encode(array("status" => "Error", "message" => "Please select atlease 1 checkbox."));
                }
            } else if($data['Action'] == 'Deleted') {
                if (!empty($data['EndDate'])) {
                    $EndDate = $data['EndDate'];
                } else {
                    return json_encode(array("status" => "Error", "message" => "Please Enter End Date."));
                }
                $Action = $data['Action'];
            }

            $TempRateIDs = implode(',',$TempRateIDs);

            try {
                DB::statement("call prc_WSReviewVendorRateUpdate ('".$AccountID."','".$TrunkID."','".$TempRateIDs."','".$ProcessID."','".$criteria."','".$Action."','".$Interval1."','".$IntervalN."','".$EndDate."')");
                return json_encode(["status" => "success", "message" => "Rates successfully updated."]);
            } catch (Exception $e) {
                return json_encode(array("status" => "failed", "message" => $e->getMessage()));
            }
        }else{
            return json_encode(array("status" => "failed", "message" => "Please select vendor rates."));
        }
    }

}
