<?php

class AutoImportController extends \BaseController {

	public function __construct(){

	 }

	public function index()
	{
		$companyID = User::get_companyID();
		$trunks = Trunk::getTrunkDropdownIDList();
		$trunk_keys = getDefaultTrunk($trunks);
		$RateGenerators = RateGenerator::where(["Status" => 1, "CompanyID" => $companyID])->lists("RateGeneratorName", "RateGeneratorId");
		$jobTypes  = JobType::getJobTypeIDListByWhere();
		$jobStatus = JobStatus::getJobStatusIDList();
		$uploadtemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplate::TEMPLATE_VENDOR_RATE);
		return View::make('autoimport.index', compact('trunks','jobStatus','jobTypes','trunk_keys','uploadtemplate'));

	}





	public function ajax_datagrid($type)
	{
		$CompanyID = User::get_companyID();
		$data = Input::all();
		$data['iDisplayStart'] +=1;
		$columns = array('Type','Header Info','Created','JobId','Status');
		$sort_column = $columns[$data['iSortCol_0']];
		$jobType = !empty($data["jobType"]) ? $data["jobType"] : 0;
		$jobStatus = !empty($data["jobStatus"]) ? $data["jobStatus"] : 0;
		$search = !empty($data['Search']) ? $data['Search'] : '';
		$query = "call prc_getAutoImportMail (".$jobType.",".$jobStatus.",".$CompanyID.",'".$search."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."' ";

		if( isset($data['Export']) && $data['Export'] == 1) {
			$excel_data  = DB::select($query.',1)');
			$excel_data = json_decode(json_encode($excel_data),true);
			foreach($excel_data as $rowno => $rows){
				foreach($rows as $colno => $colval){
					$excel_data[$rowno][$colno] = str_replace( "<br>" , "\n" ,$colval );
				}
			}

			if($type=='csv'){
				$file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/AutoImport.csv';
				$NeonExcel = new NeonExcelIO($file_path);
				$NeonExcel->download_csv($excel_data);
			}elseif($type=='xlsx'){
				$file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/AutoImport.xls';
				$NeonExcel = new NeonExcelIO($file_path);
				$NeonExcel->download_excel($excel_data);
			}
		}
		$query .=',0)';

		\Illuminate\Support\Facades\Log::info($query);

		return DataTableSql::of($query)->make();

	}


	public function GetemailReadById($id){
		$CompanyID = 1;
		$result = AutoImport::getEmailById($id);
		$upload_path = CompanyConfiguration::get($CompanyID,'UPLOAD_PATH');
		$amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['AUTOIMPORT_UPLOAD'],'',$CompanyID);
		$path = AmazonS3::unSignedUrl($amazonPath, $CompanyID);
		//echo $fullPath = $upload_path . "/". $amazonPath;
		return Response::json(array("data" => $result[0], "path" => $path));
		//return Response::json($result[0]);
	}





}