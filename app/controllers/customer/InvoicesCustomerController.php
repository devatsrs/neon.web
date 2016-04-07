<?php

class InvoicesCustomerController extends \BaseController {

    public function ajax_datagrid($type) {
        $data = Input::all();
        $data['iDisplayStart'] +=1;
        $companyID = User::get_companyID();
        $columns = ['InvoiceID','AccountName','InvoiceNumber','IssueDate','GrandTotal','InvoiceStatus','InvoiceID'];
        $data['InvoiceType'] = $data['InvoiceType'] == 'All'?'':$data['InvoiceType'];
        $data['AccountID'] = User::get_userID();
        $data['InvoiceStatus'] = '';
        $data['IssueDateStart'] = empty($data['IssueDateStart'])?'0000-00-00 00:00:00':$data['IssueDateStart'];
        $data['IssueDateEnd'] = empty($data['IssueDateEnd'])?'0000-00-00 00:00:00':$data['IssueDateEnd'];
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getInvoice (".$companyID.",".intval($data['AccountID']).",'".$data['InvoiceNumber']."','".$data['IssueDateStart']."','".$data['IssueDateEnd']."',".intval($data['InvoiceType']).",'".$data['InvoiceStatus']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1,0,0,"")');
            $excel_data = json_decode(json_encode($excel_data),true);

            if($type=='csv'){
                $file_path = getenv('UPLOAD_PATH') .'/Invoice.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = getenv('UPLOAD_PATH') .'/Invoice.xlsx';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

            /*Excel::create('Invoice', function ($excel) use ($excel_data) {
                $excel->sheet('Invoice', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .=',0,0,0,"")';
        return DataTableSql::of($query,'sqlsrv2')->make();
    }
    /**
     * Display a listing of the resource.
     * GET /invoices
     *
     * @return Response
     */
    public function index()
    {
        $invoice_status_json = json_encode(Invoice::get_invoice_status());
        return View::make('customer.invoices.index',compact('invoice_status_json'));
    }


    public function print_preview($id) {

        $Invoice = Invoice::find($id);
        $InvoiceDetail = InvoiceDetail::where(["InvoiceID"=>$id])->get();
        $Account  = Account::find($Invoice->AccountID);
        $Currency = Currency::find($Account->CurrencyId);
        $CurrencyCode = !empty($Currency)?$Currency->Code:'';
        $InvoiceTemplate = InvoiceTemplate::find($Account->InvoiceTemplateID);
        if(empty($InvoiceTemplate->CompanyLogoUrl)){
            $logo = 'http://placehold.it/250x100';
        }else{
            $logo = AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key);
        }
        return View::make('invoices.invoice_view', compact('Invoice','InvoiceDetail','Account','InvoiceTemplate','CurrencyCode','logo'));
    }
    public function invoice_preview($id) {

        $Invoice = Invoice::find($id);
        if(!empty($Invoice)) {
            $InvoiceDetail = InvoiceDetail::where(["InvoiceID" => $id])->get();
            $Account = Account::find($Invoice->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency) ? $Currency->Code : '';
            $InvoiceTemplate = InvoiceTemplate::find($Account->InvoiceTemplateID);
            if (empty($InvoiceTemplate->CompanyLogoUrl)) {
                $logo = 'http://placehold.it/250x100';
            } else {
                $logo = AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key);
            }
            return View::make('invoices.invoice_cview', compact('Invoice', 'InvoiceDetail', 'Account', 'InvoiceTemplate', 'CurrencyCode', 'logo'));
        }
    }

    public function pdf_view($id) {
        \Debugbar::disable();
        $pdf_path = $this->generate_pdf($id);
        return Response::download($pdf_path);
    }

    /** This function is not in use now, We are creating PDF with RMService */
    public function generate_pdf($id){
        if($id>0) {
            $Invoice = Invoice::find($id);
            $InvoiceDetail = InvoiceDetail::where(["InvoiceID" => $id])->get();
            $Account = Account::find($Invoice->AccountID);
            $Currency = Currency::find($Account->CurrencyId);
            $CurrencyCode = !empty($Currency)?$Currency->Code:'';
            $InvoiceTemplate = InvoiceTemplate::find($Account->InvoiceTemplateID);
            if (empty($InvoiceTemplate->CompanyLogoUrl)) {
                $as3url = 'http://placehold.it/250x100';
            } else {
                $as3url = (AmazonS3::unSignedUrl($InvoiceTemplate->CompanyLogoAS3Key));
            }
            $logo = getenv('UPLOAD_PATH') . '/' . basename($as3url);
            file_put_contents($logo, file_get_contents($as3url));
            $usage_data = array();
            $file_name = 'Invoice--' . date('d-m-Y') . '.pdf';
            if($InvoiceTemplate->InvoicePages == 'single_with_detail') {
                foreach ($InvoiceDetail as $Detail) {
                    if (isset($Detail->StartDate) && isset($Detail->EndDate) && $Detail->StartDate != '1900-01-01' && $Detail->EndDate != '1900-01-01') {

                        $companyID = $Account->CompanyId;
                        $start_date = $Detail->StartDate;
                        $end_date = $Detail->EndDate;
                        $pr_name = 'call prc_getInvoiceUsage (';

                        $query = $pr_name . $companyID . ",'" . $Invoice->AccountID . "','" . $start_date . "','" . $end_date . "')";
                        DB::connection('sqlsrv2')->setFetchMode(PDO::FETCH_ASSOC);
                        $usage_data = DB::connection('sqlsrv2')->select($query);
                        $usage_data = json_decode(json_encode($usage_data), true);
                        $file_name =  'Invoice-From-' . Str::slug($start_date) . '-To-' . Str::slug($end_date) . '.pdf';
                        break;
                    }
                }
            }
            $body = View::make('invoices.pdf', compact('Invoice', 'InvoiceDetail', 'Account', 'InvoiceTemplate', 'usage_data', 'CurrencyCode', 'logo'))->render();
            $destination_dir = getenv('UPLOAD_PATH') . '/'. AmazonS3::generate_path(AmazonS3::$dir['INVOICE_UPLOAD'],$Account->CompanyId) ;
            if (!file_exists($destination_dir)) {
                mkdir($destination_dir, 0777, true);
            }
            $save_path = $destination_dir .  GUID::generate().'-'. $file_name;
            PDF::loadHTML($body)->setPaper('a4')->setOrientation('potrait')->save($save_path);
            //@unlink($logo);
            return $save_path;
        }
    }

    public function pay_now(){
        $data = Input::all();
        $id = User::get_userID();
        $account = Account::find($id);
        $CompanyID = User::get_companyID();
        $CreatedBy = User::get_user_full_name();
        $Invoiceids = $data['InvoiceIDs'];
        $AccountPaymentProfileID = $data['AccountPaymentProfileID'];
        return AccountPaymentProfile::paynow($CompanyID,$id,$Invoiceids,$CreatedBy,$AccountPaymentProfileID);

    }
}