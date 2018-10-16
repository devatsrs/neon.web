<?php

class CreditNotesCustomerController extends \BaseController {

    public function ajax_datagrid($type) {
        $data = Input::all();
        //print_R($data);exit;
        $data['iDisplayStart'] +=1;
        $companyID = Customer::get_companyID();
        $columns = ['CreditNotesID','AccountName','CreditNotesNumber','IssueDate','GrandTotal','CreditNotesStatus','CreditNotesID'];
        $data['CreditNotesStatus'] = $data['CreditNotesStatus'] == 'All'?'':$data['CreditNotesStatus'];
        $data['AccountID'] = Customer::get_accountID();
        $data['IssueDateStart'] = empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd'] = empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_CustomerPanel_getCreditNotes (".$companyID.",".intval($data['AccountID']).",'".$data['CreditNotesNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."','".$data['CreditNotesStatus']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {

            $excel_data  = DB::connection('sqlsrv2')->select($query.',0,1)');
            $excel_data = json_decode(json_encode($excel_data),true);
           // echo $query;exit;
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID) .'/CreditNotes.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID) .'/CreditNotes.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

        }
        $query .=',0,0)';
        return DataTableSql::of($query,'sqlsrv2')->make();
    }

    public function ajax_datagrid_total()
    {
        $data 						 = 	Input::all();
        $data['iDisplayStart'] 		 =	0;
        $data['iDisplayStart'] 		+=	1;
        $data['iSortCol_0']			 =  0;
        $data['sSortDir_0']			 =  'desc';
        $companyID 					 =  Customer::get_companyID();
        $columns 					 =  ['CreditNotesID','AccountName','CreditNotesNumber','IssueDate','GrandTotal','PendingAmount','CreditNotesStatus','CreditNotesID'];
        $data['IssueDateStart'] = empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd'] = empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];
        $sort_column 				 =  $columns[$data['iSortCol_0']];
        $data['AccountID'] = Customer::get_accountID();
        $data['CreditNotesStatus'] = $data['CreditNotesStatus'] == 'All'?'':$data['CreditNotesStatus'];
        if(empty($data['CreditNotesNumber']))
        {
            $data['CreditNotesNumber'] = '';
        }
        if(empty($data['CreditNotesStatus']))
        {
            $data['CreditNotesStatus'] = '';
        }

        $query = "call prc_CustomerPanel_getCreditNotes (".$companyID.",".intval($data['AccountID']).",'".$data['CreditNotesNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."','".$data['CreditNotesStatus']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        $query .=',0,0)';

        $result   = DataTableSql::of($query,'sqlsrv2')->getProcResult(array('ResultCurrentPage','Total_grand_field'));
        $result2  = $result['data']['Total_grand_field'][0]->total_grand;
        $result4  = array(
            "total_grand"=>$result['data']['Total_grand_field'][0]->currency_symbol.$result['data']['Total_grand_field'][0]->total_grand
           // "os_pp"=>$result['data']['Total_grand_field'][0]->currency_symbol.$result['data']['Total_grand_field'][0]->TotalPayment.'/'.$result['data']['Total_grand_field'][0]->TotalPendingAmount,
        );

        return json_encode($result4,JSON_NUMERIC_CHECK);
    }

    /**
     * Display a listing of the resource.
     * GET /creditnotes
     *
     * @return Response
     */
    public function index()
    {
        CreditNotes::multiLang_init();
        $CompanyID 					 =  Customer::get_companyID();
        $creditnote_status_json = json_encode(CreditNotes::get_creditnotes_status());
        return View::make('customer.creditnotes.index',compact('creditnote_status_json','CompanyID'));
    }


    public function print_preview($id) {

        $CreditNotes = CreditNotes::find($id);
        $CreditNotesDetail = CreditNotesDetail::where(["CreditNotesID"=>$id])->get();
        $Account  = Account::find($CreditNotes->AccountID);
        $Currency = Currency::find($Account->CurrencyId);
        $CurrencyCode = !empty($Currency)?$Currency->Code:'';
        $CreditNotesTemplateID = AccountBilling::getCreditNotesTemplateID($CreditNotes->AccountID);
        $CreditNotesTemplate = CreditNotesTemplate::find($CreditNotesTemplateID);
        if(empty($CreditNotesTemplate->CompanyLogoUrl)){
            $logo = 'http://placehold.it/250x100';
        }else{
            $logo = AmazonS3::unSignedUrl($CreditNotesTemplate->CompanyLogoAS3Key);
        }
        return View::make('creditnotes.creditnote_view', compact('CreditNotes','CreditNotesDetail','Account','CreditNotesTemplate','CurrencyCode','logo'));
    }
    public function creditnote_preview($id) {

        $CreditNotes = CreditNotes::find($id);
        if(!empty($CreditNotes)) {
            $CreditNotesDetail = CreditNotesDetail::where(["CreditNotesID" => $id])->get();
            $Account = Account::find($CreditNotes->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency) ? $Currency->Code : '';
            $CreditNotesTemplateID = AccountBilling::getCreditNotesTemplateID($CreditNotes->AccountID);
            $CreditNotesTemplate = CreditNotesTemplate::find($CreditNotesTemplateID);
            if (empty($CreditNotesTemplate->CompanyLogoUrl)) {
                $logo = 'http://placehold.it/250x100';
            } else {
                $logo = AmazonS3::unSignedUrl($CreditNotesTemplate->CompanyLogoAS3Key);
            }
            return View::make('creditnotes.creditnote_cview', compact('CreditNotes', 'CreditNotesDetail', 'Account', 'CreditNotesTemplate', 'CurrencyCode', 'logo'));
        }
    }

    public function pdf_view($id) {

        $pdf_path = $this->generate_pdf($id);
        return Response::download($pdf_path);
    }

    public function  download_creditnote_file($id){
        $DocumentFile = CreditNotes::where(["CreditNotesID"=>$id])->pluck('Attachment');
        $FilePath =  AmazonS3::preSignedUrl($DocumentFile);
        if(file_exists($FilePath)){
            download_file($FilePath);
        }else if(is_amazon() == true){
            header('Location: '.$FilePath);
        }
        exit;
    }

    public function getCreditNotesDetail(){
        $data = Input::all();
        $CreditNotesDetail = CreditNotesDetail::where(["CreditNotesID" => $data['CreditNotesID']])->select(["CreditNotesDetailID","StartDate", "EndDate","Description"])->first();

        $result = array();
        $result['CreditNotesDetailID'] = $CreditNotesDetail->CreditNotesDetailID;
        $StartTime =  explode(' ',$CreditNotesDetail->StartDate);
        $EndTime =  explode(' ',$CreditNotesDetail->EndDate);
        $result['StartDate'] = $StartTime[0];
        $result['EndDate'] = $EndTime[0];
        $result['Description'] = $CreditNotesDetail->Description;
        $result['StartTime'] = $StartTime[1];
        $result['EndTime'] = $EndTime[1];
        //return json_encode($result);

        return Response::json(array('CreditNotesDetailID' => $result['CreditNotesDetailID'], 'StartDate' => $result['StartDate'],'EndDate'=>$result['EndDate'],'Description'=>$result['Description'],'StartTime'=>$result['StartTime'],'EndTime'=>$result['EndTime']));

    }

}