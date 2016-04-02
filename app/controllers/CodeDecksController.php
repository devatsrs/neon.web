<?php

class CodeDecksController extends \BaseController {

    var $countries;

    public function __construct() {
        $this->countries = Country::getCountryDropdownIDList();
    }

    public function ajax_datagrid() {

        $companyID = User::get_companyID();
//       $userID = User::get_userID();
        $data = Input::all();


        $data['ft_country']=$data['ft_country']!= ''?$data['ft_country']:'0';
        $data['ft_code'] = $data['ft_code'] != ''?"'".$data['ft_code']."'":'null';
        $data['ft_description'] = $data['ft_description'] != ''?"'".$data['ft_description']."'":'null';




        $data['iDisplayStart'] +=1;
        $columns = array('RateID','Country','Code','Description','Interval1','IntervalN','RateID');
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_GetCodeDeck (".$companyID.",".$data['ft_codedeckid'].",".$data['ft_country'].",".$data['ft_code'].",".$data['ft_description'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."','0')";

        return DataTableSql::of($query)->make();


    }

    /**
     * Display a listing of codedecks
     *
     * @return Response
     */
    public function index() {

            return View::make('codedecks.basecodedeck');
            $companyID = User::get_companyID();
    }

    /**
     * Show the form for creating a new codedeck
     *
     * @return Response
     */
    public function create() {
            $countries = $this->countries;
            $codedecklist = BaseCodeDeck::getCodedeckIDList();
            return View::make('codedecks.create', compact('countries','codedecklist'));
    }

    /**
     * Store a newly created codedeck in storage.
     *
     * @return Response
     */
    public function store() {
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;

        $data['Code'] = str_replace("_", "", $data['Code']);

        $rules = CodeDeck::$rules;
        $rules['Code'] = 'required|unique:tblRate,Code,NULL,CompanyID,CompanyID,'.$data['CompanyID'].',codedeckid,'.$data['codedeckid'];
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if ($codedesk = CodeDeck::create($data)) {
            return Response::json(array("status" => "success", "message" => "Code Decks Successfully Created",'LastID'=>$codedesk->RateID));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Code Decks."));
        }
    }

    /**
     * Display the specified codedeck.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {
            $codedeck = CodeDeck::findOrFail($id);

            return View::make('codedecks.show', compact('codedeck'));
    }

    /**
     * Show the form for editing the specified codedeck.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {
            $codedeck = CodeDeck::find($id);
            $codedecklist = BaseCodeDeck::getCodedeckIDList();
            $countries = $this->countries;
            return View::make('codedecks.edit', compact('countries', 'codedeck','codedecklist'));

    }

    /**
     * Update the specified codedeck in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        $data = Input::all();
        $codedeck = CodeDeck::findOrFail($id);
        $companyID = User::get_companyID();
        $data['Code'] = str_replace("_", "", $data['Code']);
        $data['CompanyID'] = $companyID;

        $rules = CodeDeck::$rules;
        $rules['Code'] = 'required|unique:tblRate,Code,' . $id . ',RateID,CompanyID,'.$data['CompanyID'].',codedeckid,'.$data['codedeckid'];


        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        if ($codedeck->update($data)) {
            return Response::json(array("status" => "success", "message" => "Code Decks Successfully Updated"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Code Decks."));
        }
    }

    public function upload() {

        \Debugbar::disable();

        //   $total_records = $this->import("I:\bk\www\projects\aamir\rm\laravel\rm\public\uploads\fxHv86yN\Snq4Obmf0XlJNFz2.csv");
        //   exit;
        ini_set('max_execution_time', 0);
        $data = Input::all();
        $rules = array(
            'codedeckid' => 'required',
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if (Input::hasFile('excel')) {

            $id = User::get_companyID();
            $company_name = Account::getCompanyNameByID($id);
            $upload_path = Config::get('app.upload_path');
            $excel = Input::file('excel'); // ->move($destinationPath);
            $ext = $excel->getClientOriginalExtension();

            if (in_array($ext, array("csv", "xls", "xlsx"))) {
                $file_name = "Codedeck_". GUID::generate() . '.' . $ext;
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['CODEDECK_UPLOAD']) ;
                $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;
                $excel->move($destinationPath, $file_name);
                if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $fullPath = $amazonPath . $file_name;
                $data['full_path'] = $fullPath;

                try {
                    DB::beginTransaction();
                    unset($data['excel']); //remove unnecesarry object.
                    $result = Job::logJob("CDU", $data);
                    if ($result['status'] != "success") {
                        DB::rollback();
                        return Response::json(["status" => "failed", "message" => $result['message']]);
                    }
                    DB::commit();
                    return Response::json(["status" => "success", "message" => "File Uploaded, Job Added in queue to process. You will be informed once Job Done. "]);
                } catch (Exception $ex) {
                    DB::rollback();
                    return Response::json(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
                }

            } else {
                return Response::json(array("status" => "failed", "message" => "Allowed Extension .xls, .xlxs, .csv."));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "Please upload excel/csv file <5MB."));
        }
    }

    /**
     * Remove the specified codedeck from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {
        CodeDeck::destroy($id);

        return Redirect::route('codedecks.index');
    }


    public function exports() {
            $companyID = User::get_companyID();

            $data = Input::all();

            $data['ft_country']=$data['ft_country']!= ''?$data['ft_country']:'0';
            $data['ft_code'] = $data['ft_code'] != ''?"'".$data['ft_code']."'":'null';
            $data['ft_description'] = $data['ft_description'] != ''?"'".$data['ft_description']."'":'null';


            $query = " call prc_GetCodeDeck (".$companyID.",".$data['ft_codedeckid'].",".$data['ft_country'].",".$data['ft_code'].",".$data['ft_description'].",null,null,null,null,1)";

            DB::setFetchMode( PDO::FETCH_ASSOC );
            $codedecks  = DB::select($query);
            DB::setFetchMode( Config::get('database.fetch'));

            $file_path = getenv('UPLOAD_PATH') .'/Code Decks.xlsx';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($codedecks);
            /*Excel::create('Code Decks', function ($excel) use ($codedecks) {
                $excel->sheet('Code Decks', function ($sheet) use ($codedecks) {
                    $sheet->fromArray($codedecks);
                });
            })->download('xls');*/
    }

    public function delete($id){
        if( intval($id) > 0){

            if(!CodeDeck::checkForeignKeyById($id)){
                try{
                    $result = CodeDeck::find($id)->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "CodeDeck Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting CodeDeck."));
                    }
                }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "CodeDeck is in Use, You cant delete this CodeDeck."));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "CodeDeck is in Use, You cant delete this CodeDeck."));
            }
        }
    }
    public function download_sample_excel_file(){
            $filePath = public_path() .'/uploads/sample_upload/CodeDeckUploadSample.csv';
            download_file($filePath);

    }
    public  function update_selected(){

            $data = Input::all();
            $companyID = User::get_companyID();
            $rateids='';
            if(empty($data['CodeDecks']) && !empty($data['criteria'])){
                $criteria = json_decode($data['criteria'],true);
                if(!empty($criteria['ft_codedeckid'])){

                    $criteria['ft_country']=$criteria['ft_country']!= ''?$criteria['ft_country']:'0';
                    $criteria['ft_code'] = $criteria['ft_code'] != ''?"'".$criteria['ft_code']."'":'null';
                    $criteria['ft_description'] = $criteria['ft_description'] != ''?"'".$criteria['ft_description']."'":'null';

                    $query = "call prc_GetCodeDeck (".$companyID.",".$criteria['ft_codedeckid'].",".$criteria['ft_country'].",".$criteria['ft_code'].",".$criteria['ft_description'].",null,null,null,null,2)";
                    $exceldatas  = DB::select($query);
                    $exceldatas = json_decode(json_encode($exceldatas),true);
                    foreach($exceldatas as $exceldata){
                        $rateids.= $exceldata['RateID'].',';
                    }
                    $rateids = rtrim($rateids,',');
                    $rateids = array_filter(explode(',',$rateids),'intval') ;
                }else{
                    return Response::json(array("status" => "failed", "message" => "Problem Updating CodeDeck."));
                }
            }else{
                $rateids = array_filter(explode(',',$data['CodeDecks']),'intval') ;
            }
            //$companyID = User::get_companyID();
            $rules = array(
                'CountryID' => 'required',
                'Description' => 'required',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if(is_array($rateids) && !empty($rateids)){
                $result = CodeDeck::whereIn('RateID',$rateids)->where('CompanyID',$companyID)->update(array('CountryID'=>$data['CountryID'],'Description'=>$data['Description'],'Interval1'=>$data['Interval1'],'IntervalN'=>$data['IntervalN']));
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "CodeDeck Successfully Updated"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Updating CodeDeck."));
                }
            }


    }
    public  function delete_selected(){
            try {
                $data = Input::all();
                $rateids = array_filter(explode(',',$data['CodeDecks']),'intval') ;
                $companyID = User::get_companyID();
                if(is_array($rateids) && !empty($rateids)){
                    $result = CodeDeck::whereIn('RateID',$rateids)->where('CompanyID',$companyID)->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "CodeDeck Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting CodeDeck."));
                    }
                }
            } catch (Exception $ex) {
                return Response::json(array("status" => "failed", "message" => "CodeDeck is in Use, You cant delete this CodeDeck."));
            }

    }
    public function delete_all(){
            try {
                $data = Input::all();
                $companyID = User::get_companyID();
                $query = CodeDeck::where('CompanyID',$companyID);
                if($data['ft_country']){
                    $query->where('CountryID',$data['ft_country']);
                }
                if($data['ft_code']){
                    $query->where('Code','like',str_replace('*','%',$data['ft_code']));
                }
                if($data['ft_description']){
                    $query->where('Description','like',str_replace('*','%',$data['ft_description']));
                }
                if($data['ft_codedeckid']){
                    $query->where('CodeDeckId',$data['ft_codedeckid']);
                }
                $result = $query->delete();
                if ($result) {
                    return Response::json(array("status" => "success", "message" => "CodeDeck Successfully Deleted"));
                } else {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting CodeDeck."));
                }

            } catch (Exception $ex) {
                return Response::json(array("status" => "failed", "message" => "CodeDeck is in Use, You cant delete this CodeDeck."));
            }

    }
    public function cretecodedeck(){
            $data = Input::all();
            $data['CompanyID'] = User::get_companyID();

            $rules = array(
                'CodedeckName' => 'required|unique:tblCodeDeck,CodeDeckName,NULL,CompanyID,CompanyID,'.$data['CompanyID'],
                'CompanyID' => 'required',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            $data['CreatedBy'] = User::get_user_full_name();

            if ($codedesk = BaseCodeDeck::create($data)) {
                return Response::json(array("status" => "success", "message" => "Code Decks Successfully Created",'LastID'=>$codedesk->CodeDeckId));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Code Decks."));
            }

    }
    public function basecodedeck($id){
            $countries = $this->countries;
            $CodeDeckName = BaseCodeDeck::getCodeDeckName($id);
            return View::make('codedecks.index', compact('countries','id','codedecklist','CodeDeckName'));

    }
    public function base_datagrid(){
        $CompanyID = User::get_companyID();
        $rate_tables = BaseCodeDeck::where(["CompanyId" => $CompanyID])->select(["CodeDeckName","updated_at","ModifiedBy","CodeDeckId"]);
        return Datatables::of($rate_tables)->make();
    }
    public function updatecodedeck($id){
            $data = Input::all();
            $codedeck = BaseCodeDeck::findOrFail($id);
            $data['CompanyID'] = User::get_companyID();

            $rules = array(
                'CodedeckName' => 'required|unique:tblCodeDeck,CodeDeckName,'.$id.',CodeDeckId,CompanyID,'.$data['CompanyID'],
                'CompanyID' => 'required',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            $data['ModifiedBy'] = User::get_user_full_name();

            if ($codedeck->update($data)) {
                return Response::json(array("status" => "success", "message" => "Code Decks Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Code Decks."));
            }

    }
    public function base_delete($id){
        if( intval($id) > 0){

            if(!BaseCodeDeck::checkForeignKeyById($id)){
                try{
                    $result = BaseCodeDeck::find($id)->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "CodeDeck Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting CodeDeck."));
                    }
                }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "CodeDeck is in Use, You cant delete this CodeDeck."));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "CodeDeck is in Use, You cant delete this CodeDeck."));
            }
        }
    }
    public function base_exports() {
            $CompanyID = User::get_companyID();
            $codedecks = BaseCodeDeck::where(["CompanyId" => $CompanyID])->get(["CodeDeckName","updated_at","ModifiedBy"]);

            Excel::create('Code Decks', function ($excel) use ($codedecks) {
                $excel->sheet('Code Decks', function ($sheet) use ($codedecks) {
                    $sheet->fromArray($codedecks);
                });
            })->download('xls');
    }
}