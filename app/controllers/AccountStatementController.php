<?php

class AccountStatementController extends \BaseController {


    public function ajax_datagrid() {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['AccountID'] = $data['AccountID']!= ''?$data['AccountID']:0;
        //$query = "prc_getSOA ".$CompanyID.",".$data['AccountID'].",0";
        $account = Account::find($data['AccountID']);
        $roundplaces = $RoundChargesAmount = CompanySetting::getKeyVal('RoundChargesAmount');//Rounding Add by Abubakar
        if(!empty($account->RoundChargesAmount)){
            $roundplaces = $account->RoundChargesAmount;
        }
        $CurencySymbol = Currency::getCurrencySymbol($account->CurrencyId);


        $query = "call prc_getSOA (".$CompanyID.",".$data['AccountID'].",'".$data['StartDate']."','".$data['EndDate']."',0)";
        $result = DB::connection('sqlsrv2')->getPdo()->query($query);

        // ----------------
        //1. Invoice Sent
        //2. Paymnet Received
        //3. Invoice Received
        //4. Payment Sent
        // ----------------

        $InvoiceOut = $result->fetchAll(PDO::FETCH_ASSOC);
        $result->nextRowset();
        $PaymentIn = $result->fetchAll(PDO::FETCH_ASSOC);

        $result->nextRowset();
        $InvoiceIn = $result->fetchAll(PDO::FETCH_ASSOC);
        $result->nextRowset();
        $PaymentOut = $result->fetchAll(PDO::FETCH_ASSOC);

        //Totals
        $result->nextRowset();
        $InvoiceOutAmountTotal = $result->fetchAll(PDO::FETCH_ASSOC);

        $result->nextRowset();
        $InvoiceOutDisputeAmountTotal = $result->fetchAll(PDO::FETCH_ASSOC);

        $result->nextRowset();
        $PaymentInAmountTotal = $result->fetchAll(PDO::FETCH_ASSOC);

        $result->nextRowset();
        $InvoiceInAmountTotal = $result->fetchAll(PDO::FETCH_ASSOC);

        $result->nextRowset();
        $InvoiceInDisputeAmountTotal = $result->fetchAll(PDO::FETCH_ASSOC);

        $result->nextRowset();
        $PaymentOutAmountTotal = $result->fetchAll(PDO::FETCH_ASSOC);

        $InvoiceOutAmountTotal = ($InvoiceOutAmountTotal[0]["InvoiceOutAmountTotal"] > 0 )?$InvoiceOutAmountTotal[0]["InvoiceOutAmountTotal"]:0;

        $InvoiceOutDisputeAmountTotal =  ($InvoiceOutDisputeAmountTotal[0]["InvoiceOutDisputeAmountTotal"] > 0 )?$InvoiceOutDisputeAmountTotal[0]["InvoiceOutDisputeAmountTotal"]:0;

        $PaymentInAmountTotal = ($PaymentInAmountTotal[0]["PaymentInAmountTotal"] > 0 )?$PaymentInAmountTotal[0]["PaymentInAmountTotal"]:0;

        $InvoiceInAmountTotal = ($InvoiceInAmountTotal[0]["InvoiceInAmountTotal"] > 0 )?$InvoiceInAmountTotal[0]["InvoiceInAmountTotal"]:0;

        $InvoiceInDisputeAmountTotal = ($InvoiceInDisputeAmountTotal[0]["InvoiceInDisputeAmountTotal"] > 0 )?$InvoiceInDisputeAmountTotal[0]["InvoiceInDisputeAmountTotal"]:0;

        $PaymentOutAmountTotal = ($PaymentOutAmountTotal[0]["PaymentOutAmountTotal"] > 0 )?$PaymentOutAmountTotal[0]["PaymentOutAmountTotal"]:0;

      /*  var Account_balance = parseFloat(InvoiceOut_AmountTotal - PaymentIn_AmountTotal).toFixed(roundplaces);
        var Company_balance = parseFloat(InvoiceIn_AmountTotal - PaymentOut_AmountTotal).toFixed(roundplaces);*/

        $CompanyBalance = number_format(($InvoiceInAmountTotal - $PaymentOutAmountTotal),$roundplaces );
        $AccountBalance =  number_format(($InvoiceOutAmountTotal - $PaymentInAmountTotal),$roundplaces );

        //Balance after offset
        $OffsetBalance = number_format( ($InvoiceOutAmountTotal - $PaymentInAmountTotal)  - ( $InvoiceInAmountTotal - $PaymentOutAmountTotal ) , $roundplaces  ) ;


        $soa_result = array_map(function($InvoiceOut,$PaymentIn,$InvoiceIn,$PaymentOut){
                                            return array_merge((array)$InvoiceOut,(array)$PaymentIn,(array)$InvoiceIn,(array)$PaymentOut);
                                        }, $InvoiceOut,$PaymentIn,$InvoiceIn,$PaymentOut);


        /*$countinInvoices = count($inInvoices);
        $countoutInvoices = count($outInvoices);
        $looptarget = '';
        $first = 0;
        if($countinInvoices>$countoutInvoices){
            $looptarget = $inInvoices;
            $first = 1;
        }else{
            $looptarget = $outInvoices;
        }
        $targetArray = array();
        $vertual = array('InvoiceNo'=>'',
            'PeriodCover'=>'',
            'InvoiceAmount'=>'',
            'spacer'=>' ',
            'PaymentID'=>'',
            'payment'=>'',
            'PaymentDate'=>'',
            'ballence'=>'');
        foreach($looptarget as $index=>$data){
            if($first==1){
                if($index<$countoutInvoices){
                    $temp = $outInvoices[$index];
                }else{
                    $temp = $vertual;
                }
                $targetArray[] = array(
                    'InvoiceNo'=>$data['InvoiceNo'],
                    'PeriodCover'=>$data['PeriodCover'],
                    'InvoiceAmount'=>$data['InvoiceAmount'],
                    'spacer'=>$data['spacer'],
                    'PaymentID'=>$data['PaymentID'],
                    'payment'=>$data['payment'],
                    'PaymentDate'=>$data['PaymentDate'],
                    'ballence'=>$data['ballence'],
                    'InvoiceNos'=>$temp['InvoiceNo'],
                    'PeriodCovers'=>$temp['PeriodCover'],
                    'InvoiceAmounts'=>$temp['InvoiceAmount'],
                    'PaymentIDs'=>$temp['PaymentID'],
                    'payments'=>$temp['payment'],
                    'PaymentDates'=>$temp['PaymentDate'],
                    'ballences'=>$temp['ballence'],
                    'roundplaces'=>$roundplaces,
                    'CurencySymbol'=>$CurencySymbol
                );
            }else{
                if($index<$countinInvoices){
                    $temp = $inInvoices[$index];
                }else{

                    $temp = $vertual;
                }
                $targetArray[] = array(
                    'InvoiceNo'=>$temp['InvoiceNo'],
                    'PeriodCover'=>$temp['PeriodCover'],
                    'InvoiceAmount'=>$temp['InvoiceAmount'],
                    'InvoiceType'=>$temp['InvoiceType'],
                    'DisputeAmount'=>$data['DisputeAmount'],
                    'spacer'=>$temp['spacer'],
                    'PaymentID'=>$temp['PaymentID'],
                    'payment'=>$temp['payment'],
                    'PaymentDate'=>$temp['PaymentDate'],
                    'ballence'=>$temp['ballence'],
                    'InvoiceNos'=>$data['InvoiceNo'],
                    'PeriodCovers'=>$data['PeriodCover'],
                    'InvoiceAmounts'=>$data['InvoiceAmount'],
                    'PaymentIDs'=>$data['PaymentID'],
                    'payments'=>$data['payment'],
                    'PaymentDates'=>$data['PaymentDate'],
                    'ballences'=>$data['ballence'],
                    'roundplaces'=>$roundplaces,
                    'CurencySymbol'=>$CurencySymbol
                );
            }

        }*/

        $output = [
            'result' => $soa_result,
            'InvoiceOutAmountTotal' => number_format($InvoiceOutAmountTotal,$roundplaces),
            'PaymentInAmountTotal' => number_format($PaymentInAmountTotal,$roundplaces),
            'InvoiceInAmountTotal' => number_format($InvoiceInAmountTotal,$roundplaces),
            'PaymentOutAmountTotal' => number_format($PaymentOutAmountTotal,$roundplaces),
            'InvoiceOutDisputeAmountTotal' => number_format($InvoiceOutDisputeAmountTotal,$roundplaces),
            'InvoiceInDisputeAmountTotal' => number_format($InvoiceInDisputeAmountTotal,$roundplaces),
            'CompanyBalance' => $CompanyBalance,
            'AccountBalance' => $AccountBalance,
            'OffsetBalance' => $OffsetBalance,
            'CurencySymbol' => $CurencySymbol,
            'roundplaces' => $roundplaces,
        ];

        echo json_encode($output);
    }
	/**
	 * Display a listing of the resource.
	 * GET /payments
	 *
	 * @return Response
	 */
	public function index()
	{
        $id=0;
        $companyID = User::get_companyID();
        $accounts = Account::getAccountIDList();
        $CompanyName = Company::getName();
        return View::make('accountstatement.index', compact('accounts','CompanyName'));
	}

    public function getPayment(){
        $data = Input::all();

        $result = Payment::where(["PaymentID"=>$data['id']])->first();
        echo json_encode($result);
    }

    public function exports($type) {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['AccountID'] = $data['AccountID']!= ''?$data['AccountID']:0;
        
        $account = Account::find($data['AccountID']);
        $roundplaces = $RoundChargesAmount = CompanySetting::getKeyVal('RoundChargesAmount');//Rounding Add by Abubakar
        if(!empty($account->RoundChargesAmount)){
            $roundplaces = $account->RoundChargesAmount;
        }
        $query = "call prc_getSOA (".$CompanyID.",".$data['AccountID'].",'".$data['StartDate']."','".$data['EndDate']."',1)";
        $result = DB::connection('sqlsrv2')->getPdo()->query($query);
        $inInvoices = $result->fetchAll(PDO::FETCH_ASSOC);
        $result->nextRowset();
        $outInvoices = $result->fetchAll(PDO::FETCH_ASSOC);

        $account_statement['inInvoices'] = $inInvoices;
        $account_statement['outInvoices'] = $outInvoices;
        $account_statement['firstCompany'] = Company::getName();
        $account_statement['secondCompany'] = Account::getCompanyNameByID($data['AccountID']);
        $account_statement['roundplaces'] = $roundplaces;
        if(count($account_statement['inInvoices']) || count($account_statement['outInvoices'])){
            AccountStatementController::generateExcel($account_statement,$type);
        }
        AccountStatementController::generateExcel($account_statement,$type);
    }

    static function generateExcel($account_statement,$type){
        Excel::create('Account Statement', function ($excel) use ($account_statement) {
            $excel->sheet('Account Statement', function ($sheet) use ($account_statement) {
                //$sheet->mergeCells('A4:D4');
                //$sheet->getCell('B4')->setValue('Wavetel Ltd INVOICE');
                $firstoffset = 0;
                $secondoffset = 0;

                //setting default space.
                $sheet->cell('D1', function($cell){$cell->setValue(' ');});
                $sheet->cell('H1', function($cell){$cell->setValue(' ');});
                $sheet->cell('L1', function($cell){$cell->setValue(' ');});

                $sheet->mergeCells('A2:P2');
                $sheet->cell('A2', function($cell){
                    $cell->setValue('INVOICE OFFSETTING');
                    $cell->setAlignment('center');
                    $cell->setFontSize(14);
                    $cell->setFontWeight('bold');
                });
                $sheet->mergeCells('A4:D4');
                $sheet->cell('A4', function($cell)use($account_statement){
                    AccountStatementController::formateCell($cell);
                    $cell->setValue($account_statement['firstCompany'].' INVOICE');
                    $cell->setFontSize(12);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('A5', function($cell){
                    AccountStatementController::formateCell($cell);
                    $cell->setValue('INVOICE NO');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('B5', function($cell){
                    AccountStatementController::formateCell($cell);
                    $cell->setValue('PERIOD COVERED');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('C5', function($cell){
                    AccountStatementController::formateCell($cell);
                    $cell->setValue('AMOUNT');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('E5', function($cell){
                    AccountStatementController::formateCell($cell);
                    $cell->setValue('DATE');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('F5', function($cell) use ($account_statement){
                    AccountStatementController::formateCell($cell);
                    $cell->setValue($account_statement['secondCompany'].' PAYMENT');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('G5', function($cell) {
                    AccountStatementController::formateCell($cell);
                    $cell->setValue('BALANCE');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $startrowtemp = '';
                if(count($account_statement['inInvoices'])>0){
                    // start coordinate
                    list ($startColumn, $startRow) = PHPExcel_Cell::coordinateFromString('A6');
                    $startrowtemp = $startRow;
                    $check = '';
                    $invoiceNo = '';
                    $count = 1;
                    $valid = 1;
                    // Loop through $source
                    foreach ($account_statement['inInvoices'] as $rowData) {
                        $currentColumn = $startColumn;
                        $count = 1;
                        if(($check!=$rowData['InvoiceNo']) or ($rowData['InvoiceNo']=='')){
                            $check = $rowData['InvoiceNo'];
                            $valid = 1;
                        }else{
                            $valid = 0;
                        }

                        foreach($rowData as $cellValue) {
                            if(is_numeric($cellValue)){
                                $sheet->cell($currentColumn . $startRow, function($cell) use($cellValue,$valid,$count,$account_statement) {
                                    AccountStatementController::formateCell($cell,false);
                                    if($count == 6){
                                        if($valid==1){
                                            $cell->setValue($cellValue);
                                        }else{
                                            $cell->setValue('');
                                        }
                                    }else{
                                        $cellValue = '=ROUND('.$cellValue.','.$account_statement['roundplaces'].')';
                                        $cell->setValue($cellValue);
                                    }

                                    $cell->setBackground('#EBF5F2');
                                });
                            }else{
                                $sheet->cell($currentColumn . $startRow, function($cell) use($cellValue,$currentColumn,$valid,$count) {
                                    AccountStatementController::formateCell($cell);
                                    if($currentColumn!='D'){
                                        $cell->setBackground('#EBF5F2');
                                    }
                                    if($count == 5){
                                        if($valid==1){
                                            $cell->setValue($cellValue);
                                        }else{
                                            $cell->setValue('');
                                        }
                                    }else{
                                        $cell->setValue($cellValue);
                                    }
                                });
                            }

                            ++$currentColumn;
                            $count++;
                        }
                        ++$startRow;
                    }
                    $firstoffset = $startRow;
                }

                $sheet->mergeCells('I4:L4');
                $sheet->cell('I4', function($cell) use($account_statement){
                    AccountStatementController::formateCell($cell);
                    $cell->setValue($account_statement['secondCompany'].' INVOICE');
                    $cell->setFontSize(12);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('I5', function($cell){
                    AccountStatementController::formateCell($cell);
                    $cell->setValue('INVOICE NO');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('J5', function($cell){
                    AccountStatementController::formateCell($cell);
                    $cell->setValue('PERIOD COVERED');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('K5', function($cell){
                    AccountStatementController::formateCell($cell);
                    $cell->setValue('AMOUNT');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('M5', function($cell){
                    AccountStatementController::formateCell($cell);
                    $cell->setValue('DATE');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('N5', function($cell)use($account_statement){
                    AccountStatementController::formateCell($cell);
                    $cell->setValue($account_statement['firstCompany'].' PAYMENT');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('O5', function($cell) {
                    AccountStatementController::formateCell($cell);
                    $cell->setValue('BALANCE');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                if(count($account_statement['outInvoices'])>0) {
                    list ($startColumn, $startRow) = PHPExcel_Cell::coordinateFromString('I6');
                    $startrowtemp = $startRow;
                    $check = '';
                    $invoiceNo = '';
                    $count = 1;
                    $valid = 1;
                    foreach ($account_statement['outInvoices'] as $rowData) {
                        $currentColumn = $startColumn;
                        $count = 1;
                        if(($check!=$rowData['InvoiceNo']) or ($rowData['InvoiceNo']=='')){
                            $check = $rowData['InvoiceNo'];
                            $valid = 1;
                        }else{
                            $valid = 0;
                        }
                        foreach ($rowData as $cellValue) {
                            if (is_numeric($cellValue)) {
                                $sheet->cell($currentColumn . $startRow, function ($cell) use ($cellValue,$valid,$count,$account_statement) {
                                    AccountStatementController::formateCell($cell,false);
                                    if($count == 6){
                                        if($valid==1){
                                            $cell->setValue($cellValue);
                                        }else{
                                            $cell->setValue('');
                                        }
                                    }else{
                                        $cellValue = '=ROUND('.$cellValue.','.$account_statement['roundplaces'].')';
                                        $cell->setValue($cellValue);
                                    }
                                });
                            } else {
                                $sheet->cell($currentColumn . $startRow, function ($cell) use ($cellValue,$currentColumn,$valid,$count) {
                                    AccountStatementController::formateCell($cell);
                                    if($currentColumn!='L'){
                                        $cell->setBackground('#EBF5F2');
                                    }
                                    if($count == 5){
                                        if($valid==1){
                                            $cell->setValue($cellValue);
                                        }else{
                                            $cell->setValue('');
                                        }
                                    }else{
                                        $cell->setValue($cellValue);
                                    }
                                });
                            }
                            ++$currentColumn;
                        }
                        ++$startRow;
                    }
                    $secondoffset = $startRow;
                }
                if($firstoffset>$secondoffset){
                    $startRow = $firstoffset;
                }else{
                    $startRow = $secondoffset;
                }

                //Sum up invoices amount for company
                $startRow++;
                $sheet->cell('C' . $startRow, function ($cell) use ($startrowtemp,$startRow) {
                    AccountStatementController::formateCell($cell,false);
                    $formula = '=SUM(C'.$startrowtemp.':C'.($startRow-1).')';
                    $cell->setValue($formula);
                });
                //Sum up payments amount for company
                $sheet->cell('F' . $startRow, function ($cell) use ($startrowtemp,$startRow) {
                    AccountStatementController::formateCell($cell,false);
                    $formula = '=SUM(F'.$startrowtemp.':F'.($startRow-1).')';
                    $cell->setValue($formula);
                });
                //ballance for Company invoices and payments
                $sheet->cell('G' . $startRow, function ($cell) use ($startRow) {
                    AccountStatementController::formateCell($cell,false);
                    $formula = '= C'.($startRow).'-F'.($startRow);
                    $cell->setValue($formula);
                });

                //Sum up invoices amount for customer or vendor
                $sheet->cell('K' . $startRow, function ($cell) use ($startrowtemp,$startRow) {
                    AccountStatementController::formateCell($cell,false);
                    $formula = '=SUM(K'.$startrowtemp.':K'.($startRow-1).')';
                    $cell->setValue($formula);
                });
                //Sum up payments amount for customer or vendor
                $sheet->cell('N' . $startRow, function ($cell) use ($startrowtemp,$startRow) {
                    AccountStatementController::formateCell($cell,false);
                    $formula = '=SUM(N'.$startrowtemp.':N'.($startRow-1).')';
                    $cell->setValue($formula);
                });
                //ballance for customer or vendor invoices and payments
                $sheet->cell('O' . $startRow, function ($cell) use ($startRow) {
                    AccountStatementController::formateCell($cell,false);
                    $formula = '= K'.($startRow).'-N'.($startRow);
                    $cell->setValue($formula);
                });

                $lastRow = $startRow+4;
                $sheet->mergeCells('B'.$lastRow.':O'.$lastRow);
                $sheet->cell('A' . $lastRow, function ($cell) {
                    $cell->setFont(array(
                        'family'     => 'Arial',
                        'size'       => '14',
                        'bold'       =>  true
                    ));
                    $cell->setValue('BALANCE AFTER OFFSET:');
                });
                $sheet->cell('B' . $lastRow, function ($cell) use ($startRow) {
                    $total = '=G'.$startRow.'-'.'O'.$startRow;
                    //$total = ($inPayment_Amount['inAmount']-$inPayment_Amount['inpayments'])-($outPayment_Amount['outAmount']-$outPayment_Amount['outpayments']);
                    $cell->setValue($total);
                });
            });
        })->download('xls');
    }

    static function formateCell(&$cell,$isCenter=true){
        $cell->setFont(array(
            'family'     => 'Arial',
            'size'       => '11',
            'bold'       =>  false
        ));
        if($isCenter) {
            $cell->setAlignment('center');
        }
    }

}