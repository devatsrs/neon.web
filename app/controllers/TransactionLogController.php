<?php

class TransactionLogController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 * GET /page
	 *
	 * @return Response
	 */
	public function log($id)
	{
        $invoice = Invoice::find($id);
		return View::make('transactionlog.index', compact('invoice','id'));
	}

    public function ajax_datagrid($id,$type) {
        $data = Input::all();
        $data['iDisplayStart'] +=1;


        $columns = array('InvoiceNumber','Transaction','Notes','Amount','Status','created_at','InvoiceID');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();

        $query = "call prc_GetInvoiceTransactionLog (".$companyID.",0,".$id.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        //echo $query;exit;
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Invoice Transaction Log.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Invoice Transaction Log.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

            /*Excel::create('Invoice Transaction Log', function ($excel) use ($excel_data) {
                $excel->sheet('Invoice Transaction Log', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .=',0)';

        return DataTableSql::of($query,'sqlsrv2')->make();
    }
    public function ajax_invoice_datagrid($id,$type) {
        $data = Input::all();
        $data['iDisplayStart'] +=1;


        //$columns = array('InvoiceNumber','Transaction','Notes','Amount','Status','created_at','InvoiceID');
		$columns = array('InvoiceNumber','Notes','Status','created_at','InvoiceID');
        $sort_column = $columns[$data['iSortCol_0']];
        $companyID = User::get_companyID();

        $query = "call prc_GetInvoiceLog (".$companyID.",".$id.",".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        //echo $query;exit;
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Invoice Log.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Invoice Log.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

            /*Excel::create('Invoice Log', function ($excel) use ($excel_data) {
                $excel->sheet('Invoice Log', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .=',0)';

        return DataTableSql::of($query,'sqlsrv2')->make();
    }

}