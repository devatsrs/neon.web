<?php

class SippyVendorDestiController extends \BaseController {

	public function __construct(){

	 }
	/* AutoInbox Setting  Start */
	public function index()
	{
		$trunks = Trunk::getTrunkDropdownIDList();
		$all_accounts = Account::getAccountIDList(['IsVendor'=>1]);
		$DestinationSet=DestinationSet::getDestinationSet();
		return View::make('sippyvendordesti.index', compact('trunks','all_accounts','DestinationSet'));

	}
	public function Store()
	{
		$data = Input::all();
		//print_r($data);die;
		$companyID = User::get_companyID();

		$data ["CompanyID"] = $companyID;
		$data["created_by"] = User::get_user_full_name();

		unset($data['SippyVendorDestiMapID']);
		unset($data['ProductClone']);

		$rules = array(
			'CompanyID' => 'required',
			'VendorID' => 'required',
			'TrunkID' => 'required',
			'CodeRule' => 'required',
			'DestinationSetID' => 'required',
		);

		$verifier = App::make('validation.presence');
		$verifier->setConnection('sqlsrv2');

		$validator = Validator::make($data, $rules);
		$validator->setPresenceVerifier($verifier);

		if ($validator->fails()) {
			return json_validator_response($validator);
		}
		$checkduplicate=SippyVendorDestiMap::where(['VendorID'=>$data['VendorID'],'CodeRule'=>$data['CodeRule'],'DestinationSetID'=>$data['DestinationSetID']])->get()->count();
		if($checkduplicate > 0){
			return Response::json(array("status" => "failed", "message" => "Vendor Destination Setting Already Exists With this Vendor,CodeRule and DestinationSet."));
		}

		if ($Result = SippyVendorDestiMap::create($data)) {
			return Response::json(array("status" => "success", "message" => "Vendor Destination Setting Successfully Created",'newcreated'=>$Result));
		} else {
			return Response::json(array("status" => "failed", "message" => "Problem Creating Vendor Destination Setting."));
		}


	}


	/* Search Grid for Setting (RateTable and Account )*/
	public function ajax_datagrid($type)
	{
		$CompanyID = User::get_companyID();
		$data = Input::all();
		$data['iDisplayStart'] +=1;

		$columns = array('AccountName','CodeRule','TrunkID','DestinationSetID','created_at','updated_by');

		$sort_column = $columns[$data['iSortCol_0']];
		$trunkId = ( !empty($data['TrunkID']) && $data['TrunkID'] != 'undefined' ) ? $data['TrunkID'] : 0;
		$CodeRule = !empty($data['CodeRule']) ? $data['CodeRule'] : '';
		$VendorID = !empty($data['VendorID']) ? $data['VendorID'] : 0;

		$query = "call prc_getSippyVendorDesti (".$CompanyID.",".$VendorID.",".$trunkId.",'".$CodeRule."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."' ";

		if( isset($data['Export']) && $data['Export'] == 1) {
			$excel_data  = DB::select($query.',1)');
			$excel_data = json_decode(json_encode($excel_data),true);
			foreach($excel_data as $rowno => $rows){
				foreach($rows as $colno => $colval){
					$excel_data[$rowno][$colno] = str_replace( "<br>" , "\n" ,$colval );
				}
			}

			if($type=='csv'){
				$file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/VendorDestination.csv';
				$NeonExcel = new NeonExcelIO($file_path);
				$NeonExcel->download_csv($excel_data);
			}elseif($type=='xlsx'){
				$file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/VendorDestination.xls';
				$NeonExcel = new NeonExcelIO($file_path);
				$NeonExcel->download_excel($excel_data);
			}
		}
		$query .=',0)';
		//echo $query;die;
//		\Illuminate\Support\Facades\Log::info($query);
		return DataTableSql::of($query)->make();

	}


	/* Use in Account Setting page*/
	public function update($id) {
		if( $id > 0 ) {
			$data = Input::all();
			$SippyVendorDestiMap = SippyVendorDestiMap::findOrFail($id);

			$companyID = User::get_companyID();
			$data ["CompanyID"] = $companyID;
			$data["updated_by"] = User::get_user_full_name();

			unset($data['ProductClone']);

			$rules = array(
				'CompanyID' => 'required',
				'VendorID' => 'required',
			);
			$verifier = App::make('validation.presence');
			$verifier->setConnection('sqlsrv2');

			$validator = Validator::make($data, $rules);
			$validator->setPresenceVerifier($verifier);

			if ($validator->fails()) {
				return json_validator_response($validator);
			}

			if ($SippyVendorDestiMap->update($data)) {
				return Response::json(array("status" => "success", "message" => "Vendor Destination Setting Successfully Updated"));
			} else {
				return Response::json(array("status" => "failed", "message" => "Problem Updating Vendor Destination Setting."));
			}
		}else {
			return Response::json(array("status" => "failed", "message" => "Problem Updating Vendor Destination Setting."));
		}
	}

	/* Use in Account Setting and RateTable Setting */
	public function delete($id) {

		if( intval($id) > 0){
			try {
				$result = SippyVendorDestiMap::find($id)->delete();
				if ($result) {
					return Response::json(array("status" => "success", "message" => "Vendor Destination Setting Successfully Deleted"));
				} else {
					return Response::json(array("status" => "failed", "message" => "Problem Deleting Vendor Destination Setting."));
				}
			} catch (Exception $ex) {
				return Response::json(array("status" => "failed", "message" => "Problem Deleting Vendor Destination Setting.."));
			}

		}else{
			return Response::json(array("status" => "failed", "message" => "Problem Deleting Vendor Destination Setting."));
		}

	}


}