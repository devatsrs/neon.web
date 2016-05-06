<?php

class DisputeController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 * GET /products
	 *
	 * @return Response

	 */

	public function ajax_datagrid($type) {

		$data 							 = 		Input::all();
		$CompanyID 						 = 		User::get_companyID();
		$data['iDisplayStart'] 			+=		1;
		$data['AccountID'] 				 = 		$data['AccountID']!= ''?$data['AccountID']:'NULL';
		$data['InvoiceNo']				 =		$data['InvoiceNo']!= ''?"'".$data['InvoiceNo']."'":'NULL';
		$data['Status'] 				 = 		$data['Status'] != ''?$data['Status']:'NULL';
		$data['p_disputestartdate'] 	 = 		$data['DisputeDate_StartDate']!=''?$data['DisputeDate_StartDate']:'NULL';
		$data['p_disputeenddate'] 	 	 = 		$data['DisputeDate_EndDate']!=''?$data['DisputeDate_EndDate']:'NULL';
		$data['p_disputestart']			 =		'NULL';
		$data['p_disputeend']			 =		'NULL';

		if($data['p_disputestartdate']!='' && $data['p_disputestartdate']!='NULL')
		{
			$data['p_disputestart']		=	"'".$data['p_disputestartdate']."'"; //.' '.$data['p_disputestartTime']."'";
		}
		if($data['p_disputeenddate']!='' && $data['p_disputeenddate']!='NULL')
		{
			$data['p_disputeend']			=	"'".$data['p_disputeenddate']."'"; //.' '.$data['p_disputeendtime']."'";
		}

		if($data['p_disputestart']!='NULL' && $data['p_disputeend']=='')
		{
			$data['p_disputeend'] 			= 	"'".date("Y-m-d H:i:s")."'";
		}

		$columns = array('AccountName','InvoiceNo','DisputeAmount','Status','created_at', 'CreatedBy','Notes','DisputeID');
		$sort_column = $columns[$data['iSortCol_0']];

		$query = "call prc_getDisputes (".$CompanyID.",".$data['AccountID'].",".$data['InvoiceNo'].",".$data['Status'].",".$data['p_disputestart'].",".$data['p_disputeend'].",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) ).",".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

		if(isset($data['Export']) && $data['Export'] == 1) {
			$excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
			$excel_data = json_decode(json_encode($excel_data),true);
			if($type=='csv'){
				$file_path = getenv('UPLOAD_PATH') .'/Dispute.csv';
				$NeonExcel = new NeonExcelIO($file_path);
				$NeonExcel->download_csv($excel_data);
			}elseif($type=='xlsx'){
				$file_path = getenv('UPLOAD_PATH') .'/Dispute.xls';
				$NeonExcel = new NeonExcelIO($file_path);
				$NeonExcel->download_excel($excel_data);
			}
		}
		$query .=',0)';
		return DataTableSql::of($query,'sqlsrv2')->make();
	}


	public function index()
	{
		$id=0;
		$companyID = User::get_companyID();
		$currency = Currency::getCurrencyDropdownList();
		$currency_ids = json_encode(Currency::getCurrencyDropdownIDList());
		$InvoiceNo = Invoice::where(array('CompanyID'=>$companyID,'InvoiceType'=>Invoice::INVOICE_IN))->get(['InvoiceNumber']);
		$InvoiceNoarray = array();
		foreach($InvoiceNo as $Invoicerow){
			$InvoiceNoarray[] = $Invoicerow->InvoiceNumber;
		}
		$invoice_nos = implode(',',$InvoiceNoarray);
		$accounts = Account::getAccountIDList();

		return View::make('disputes.index', compact('id','currency','status','accounts','invoice_nos','currency_ids'));

	}

	/**
	 * Show the form for creating a new resource.
	 * GET /products/create
	 *
	 * @return Response
	 */
	public function create(){

		$data = Input::all();

		$output = Dispute::add_update_dispute($data);

		return $output;

	}


	/**
	 * Update the specified resource in storage.
	 * PUT /products/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		if( $id > 0 ) {

			$data = Input::all();

			$output = Dispute::add_update_dispute($data);

			return $output;
		}else {

			return Response::json(array("status" => "failed", "message" => "Invalid DisputeID."));
		}
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /products/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function delete($id) {
		if( intval($id) > 0){

				try {
					$result = Dispute::find($id)->delete();
					if ($result) {
						return Response::json(array("status" => "success", "message" => "Dispute Successfully Deleted"));
					} else {
						return Response::json(array("status" => "failed", "message" => "Problem Deleting Dispute."));
					}
				} catch (Exception $ex) {
					return Response::json(array("status" => "failed", "message" => "Dispute is in Use, You cant delete this Dispute."));
				}

		}else{
			return Response::json(array("status" => "failed", "message" => "Dispute is in Use, You cant delete this Dispute."));
		}
	}

	//not in use
	public function reconcile()
	{
		$data = Input::all();
		$companyID =  User::get_companyID();

		$rules = array(
			'InvoiceNo'=>'required|numeric',
			'AccountID'=>'required|numeric',
		);

		$verifier = App::make('validation.presence');
		$verifier->setConnection('sqlsrvcdr');

		$validator = Validator::make($data, $rules);

		$validator->setPresenceVerifier($verifier);
		if ($validator->fails()) {
			return json_validator_response($validator);
		}

		$Invoice = InvoiceDetail::where("InvoiceNo",$data['InvoiceNo'])->select(['StartDate','EndDate','Price','TotalMinutes'])->first();

		$StartDate = $Invoice->StartDate;
		$EndDate = $Invoice->EndDate;
		$TotalMinutes = $Invoice->TotalMinutes;
		$GrandTotal = $Invoice->Price;
		$accountID = $data['AccountID'];

		$output = Dispute::reconcile($companyID,$accountID,$StartDate,$EndDate,$GrandTotal,$TotalMinutes);

		if(isset($data["DisputeID"]) && $data["DisputeID"] > 0 ){

			$output["DisputeID"]  = $data["DisputeID"];
		}

		return Response::json( array_merge($output, array("status" => "success", "message" => ""  )));
	}


	public function change_status(){

		$data = Input::all();

		$rules = array(
			'Notes'=>'required',
			'DisputeID'=>'required|numeric',
			'Status'=>'required|numeric',
		);
		$validator = Validator::make($data, $rules);
		if ($validator->fails()) {
			return json_validator_response($validator);
		}
		$Dispute = Dispute::findOrFail($data["DisputeID"]);
		$Dispute->Notes = date("Y-m-d H:i:s") .': '. User::get_user_full_name() . ' has Changed Status'. PHP_EOL. $data['Notes'] . PHP_EOL.  PHP_EOL .  $Dispute->Notes;
		$Dispute->Status = $data["Status"];

		if ($Dispute->update()) {
			return Response::json(array("status" => "success", "message" => "Dispute Status Successfully Updated"));
		} else {
			return Response::json(array("status" => "failed", "message" => "Failed Updating Dispute Status."));
		}

	}
}