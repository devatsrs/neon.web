<?php

class AccountStatementCustomerController extends \BaseController {


    public function ajax_datagrid() {
        $data = Input::all();
        $CompanyID = Customer::get_companyID();
        $data['AccountID'] = Customer::get_accountID();
        $account = Account::find($data['AccountID']);
        $roundplaces = get_round_decimal_places($data['AccountID']);
        $CurencySymbol = Currency::getCurrencySymbol($account->CurrencyId);

        //$query = "prc_getSOA ".$CompanyID.",".$data['AccountID'].",0";
        $query = "call prc_getSOA (" . $CompanyID . "," . $data['AccountID'] . ",'" . $data['StartDate'] . "','" . $data['EndDate'] . "',0)";
        $result = DB::connection('sqlsrv2')->getPdo()->query($query);

        // ----------------
        //1. Invoice Sent
        //2. Paymnet Received
        //3. Invoice Received
        //4. Payment Sent
        // ----------------

        $InvoiceOutWithPaymentIn = $result->fetchAll(PDO::FETCH_ASSOC);
        $result->nextRowset();
        $InvoiceInWithPaymentOut = $result->fetchAll(PDO::FETCH_ASSOC);

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

        $result->nextRowset();
        $BroughtForwardOffset = $result->fetchAll(PDO::FETCH_ASSOC);

        $BroughtForwardOffset = !empty($BroughtForwardOffset[0]["BroughtForwardOffset"]) ? number_format(doubleval($BroughtForwardOffset[0]["BroughtForwardOffset"]), $roundplaces) : 0;

        $InvoiceOutAmountTotal = (doubleval(!empty($InvoiceOutAmountTotal[0]["InvoiceOutAmountTotal"]))) ? $InvoiceOutAmountTotal[0]["InvoiceOutAmountTotal"] : 0;

        $InvoiceOutDisputeAmountTotal = (doubleval(!empty($InvoiceOutDisputeAmountTotal[0]["InvoiceOutDisputeAmountTotal"]))) ? $InvoiceOutDisputeAmountTotal[0]["InvoiceOutDisputeAmountTotal"] : 0;

        $PaymentInAmountTotal = (doubleval(!empty($PaymentInAmountTotal[0]["PaymentInAmountTotal"]))) ? $PaymentInAmountTotal[0]["PaymentInAmountTotal"] : 0;

        $InvoiceInAmountTotal = (doubleval(!empty($InvoiceInAmountTotal[0]["InvoiceInAmountTotal"]))) ? $InvoiceInAmountTotal[0]["InvoiceInAmountTotal"] : 0;

        $InvoiceInDisputeAmountTotal = ((doubleval(!empty($InvoiceInDisputeAmountTotal[0]["InvoiceInDisputeAmountTotal"])))) ? $InvoiceInDisputeAmountTotal[0]["InvoiceInDisputeAmountTotal"] : 0;

        $PaymentOutAmountTotal = ((doubleval(!empty($PaymentOutAmountTotal[0]["PaymentOutAmountTotal"])))) ? $PaymentOutAmountTotal[0]["PaymentOutAmountTotal"] : 0;

        $CompanyBalance = number_format(($InvoiceInAmountTotal - $PaymentOutAmountTotal), $roundplaces);
        $AccountBalance = number_format(($InvoiceOutAmountTotal - $PaymentInAmountTotal), $roundplaces);

        //Balance after offset
        $OffsetBalance = number_format(($InvoiceOutAmountTotal - $PaymentInAmountTotal) - ($InvoiceInAmountTotal - $PaymentOutAmountTotal), $roundplaces);

        $InvoiceOutWithPaymentIn = $this->format_records($InvoiceOutWithPaymentIn,Invoice::INVOICE_OUT,$roundplaces); // format records to display without using any condition
        $InvoiceInWithPaymentOut = $this->format_records($InvoiceInWithPaymentOut,Invoice::INVOICE_IN,$roundplaces); // format records to display without using any condition

        $InvoiceOutWithPaymentIn = $this->merge_single_invoice_payments($InvoiceOutWithPaymentIn,Invoice::INVOICE_OUT);
        $InvoiceInWithPaymentOut = $this->merge_single_invoice_payments($InvoiceInWithPaymentOut,Invoice::INVOICE_IN);

        $soa_result = array_map(function ($InvoiceOutWithPaymentIn, $InvoiceInWithPaymentOut) {
            return array_merge((array)$InvoiceOutWithPaymentIn, (array)$InvoiceInWithPaymentOut);
        }, (array)$InvoiceOutWithPaymentIn, (array)$InvoiceInWithPaymentOut);

        $soa_result = $this->cleanup_duplicate_records($soa_result);

        $output = [
            'result' => $soa_result,
            'InvoiceOutAmountTotal' => number_format($InvoiceOutAmountTotal, $roundplaces),
            'PaymentInAmountTotal' => number_format($PaymentInAmountTotal, $roundplaces),
            'InvoiceInAmountTotal' => number_format($InvoiceInAmountTotal, $roundplaces),
            'PaymentOutAmountTotal' => number_format($PaymentOutAmountTotal, $roundplaces),
            'InvoiceOutDisputeAmountTotal' => number_format($InvoiceOutDisputeAmountTotal, $roundplaces),
            'InvoiceInDisputeAmountTotal' => number_format($InvoiceInDisputeAmountTotal, $roundplaces),
            'CompanyBalance' => $CompanyBalance,
            'AccountBalance' => $AccountBalance,
            'OffsetBalance' => $OffsetBalance,
            'BroughtForwardOffset' => $BroughtForwardOffset,
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
        $companyID = Customer::get_companyID();
        $AccountName = Customer::get_accountName();
        //$UserName = User::get_user_full_name();
        $CompanyName = Company::getName($companyID);
        return View::make('customer.accountstatement.index', compact('CompanyName','AccountName'));
	}

    public function getPayment(){
        $data = Input::all();

        $result = Payment::where(["PaymentID"=>$data['id']])->first();
        echo json_encode($result);
    }

    public function exports($type) {
        $data = Input::all();
        $CompanyID = Customer::get_companyID();
        $data['AccountID'] = Customer::get_accountID();
        $account = Account::find($data['AccountID']);
        $roundplaces = get_round_decimal_places($data['AccountID']);
        $query = "call prc_getSOA (".$CompanyID.",".$data['AccountID'].",'".$data['StartDate']."','".$data['EndDate']."',1)";
        $result = DB::connection('sqlsrv2')->getPdo()->query($query);

        $CurencySymbol = Currency::getCurrencySymbol($account->CurrencyId);

        // ----------------
        //1. Invoice Sent
        //2. Paymnet Received
        //3. Invoice Received
        //4. Payment Sent
        // ----------------

        $InvoiceOutWithPaymentIn = $result->fetchAll(PDO::FETCH_ASSOC);
        $result->nextRowset();
        $InvoiceInWithPaymentOut = $result->fetchAll(PDO::FETCH_ASSOC);

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

        $result->nextRowset();
        $BroughtForwardOffset = $result->fetchAll(PDO::FETCH_ASSOC);

        $BroughtForwardOffset = !empty($BroughtForwardOffset[0]["BroughtForwardOffset"]) ? number_format(doubleval($BroughtForwardOffset[0]["BroughtForwardOffset"]), $roundplaces) : 0;

        $InvoiceOutAmountTotal = (doubleval(!empty($InvoiceOutAmountTotal[0]["InvoiceOutAmountTotal"]))) ? $InvoiceOutAmountTotal[0]["InvoiceOutAmountTotal"] : 0;

        $InvoiceOutDisputeAmountTotal = (doubleval(!empty($InvoiceOutDisputeAmountTotal[0]["InvoiceOutDisputeAmountTotal"]))) ? $InvoiceOutDisputeAmountTotal[0]["InvoiceOutDisputeAmountTotal"] : 0;

        $PaymentInAmountTotal = (doubleval(!empty($PaymentInAmountTotal[0]["PaymentInAmountTotal"]))) ? $PaymentInAmountTotal[0]["PaymentInAmountTotal"] : 0;

        $InvoiceInAmountTotal = (doubleval(!empty($InvoiceInAmountTotal[0]["InvoiceInAmountTotal"]))) ? $InvoiceInAmountTotal[0]["InvoiceInAmountTotal"] : 0;

        $InvoiceInDisputeAmountTotal = ((doubleval(!empty($InvoiceInDisputeAmountTotal[0]["InvoiceInDisputeAmountTotal"])))) ? $InvoiceInDisputeAmountTotal[0]["InvoiceInDisputeAmountTotal"] : 0;

        $PaymentOutAmountTotal = ((doubleval(!empty($PaymentOutAmountTotal[0]["PaymentOutAmountTotal"])))) ? $PaymentOutAmountTotal[0]["PaymentOutAmountTotal"] : 0;

        $CompanyBalance = number_format(($InvoiceInAmountTotal - $PaymentOutAmountTotal), $roundplaces);
        $AccountBalance = number_format(($InvoiceOutAmountTotal - $PaymentInAmountTotal), $roundplaces);

        //Balance after offset
        $OffsetBalance = number_format(($InvoiceOutAmountTotal - $PaymentInAmountTotal) - ($InvoiceInAmountTotal - $PaymentOutAmountTotal), $roundplaces);


        $InvoiceOutWithPaymentIn = $this->format_records($InvoiceOutWithPaymentIn,Invoice::INVOICE_OUT,$roundplaces); // format records to display without using any condition
        $InvoiceInWithPaymentOut = $this->format_records($InvoiceInWithPaymentOut,Invoice::INVOICE_IN,$roundplaces); // format records to display without using any condition

        $soa_result = array_map(function ($InvoiceOutWithPaymentIn, $InvoiceInWithPaymentOut) {
            return array_merge((array)$InvoiceOutWithPaymentIn, (array)$InvoiceInWithPaymentOut);
        }, $InvoiceOutWithPaymentIn, $InvoiceInWithPaymentOut);

        $soa_result = $this->cleanup_duplicate_records($soa_result);


        $output = [
            'result' => $soa_result,
            'InvoiceOutAmountTotal' => number_format($InvoiceOutAmountTotal, $roundplaces),
            'PaymentInAmountTotal' => number_format($PaymentInAmountTotal, $roundplaces),
            'InvoiceInAmountTotal' => number_format($InvoiceInAmountTotal, $roundplaces),
            'PaymentOutAmountTotal' => number_format($PaymentOutAmountTotal, $roundplaces),
            'InvoiceOutDisputeAmountTotal' => number_format($InvoiceOutDisputeAmountTotal, $roundplaces),
            'InvoiceInDisputeAmountTotal' => number_format($InvoiceInDisputeAmountTotal, $roundplaces),
            'CompanyBalance' => $CompanyBalance,
            'AccountBalance' => $AccountBalance,
            'OffsetBalance' => $OffsetBalance,
            'BroughtForwardOffset' => $BroughtForwardOffset,
            'CurencySymbol' => $CurencySymbol,
            'roundplaces' => $roundplaces,
            'AccountName' => Account::getCompanyNameByID($data['AccountID']),
            'CompanyName' => Company::getName(),
        ];

        AccountStatementCustomerController::generateExcel($output, $type);
    }

    static function generateExcel($account_statement, $type)
    {
        Excel::create('Account Statement', function ($excel) use ($account_statement) {
            $excel->sheet('Account Statement', function ($sheet) use ($account_statement) {
                //$sheet->mergeCells('A4:D4');
                //$sheet->getCell('B4')->setValue('Wavetel Ltd INVOICE');


                $InvoiceOutAmountTotal = $account_statement['InvoiceOutAmountTotal'];
                $PaymentInAmountTotal = $account_statement['PaymentInAmountTotal'];
                $InvoiceInAmountTotal = $account_statement['InvoiceInAmountTotal'];
                $PaymentOutAmountTotal = $account_statement['PaymentOutAmountTotal'];
                $InvoiceOutDisputeAmountTotal = $account_statement['InvoiceOutDisputeAmountTotal'];
                $InvoiceInDisputeAmountTotal = $account_statement['InvoiceInDisputeAmountTotal'];
                $CompanyBalance = $account_statement['CompanyBalance'];
                $AccountBalance = $account_statement['AccountBalance'];
                $OffsetBalance = $account_statement['OffsetBalance'];

                $CurencySymbol = $account_statement['CurencySymbol'];
                $roundplaces = $account_statement['roundplaces'];


                $Alpha = range('A', 'R');

                //setting default space.
                $sheet->cell('E1', function ($cell) {
                    $cell->setValue(' ');
                });
                $sheet->cell('I1', function ($cell) {
                    $cell->setValue(' ');
                });
                $sheet->cell('N1', function ($cell) {
                    $cell->setValue(' ');
                });


                /**
                 * MAIN HEADING
                 */
                $sheet->mergeCells('A2:R2');
                AccountStatementCustomerController::insertExcelHeaderData($sheet, "A2",'INVOICE OFFSETTING',14 );

                /**
                 * SUB HEADINGS
                 */
                $sheet->mergeCells('A4:D4');
                $sheet->mergeCells('I4:L4');
                AccountStatementCustomerController::insertExcelHeaderData($sheet, "A4",$account_statement['CompanyName'] . ' INVOICE',12);
                AccountStatementCustomerController::insertExcelHeaderData($sheet, "I4",$account_statement['AccountName'] . ' INVOICE',12);


                /**
                 * SOA COLUMNS
                 */

                $StartRowIndex = $RowIndex = 5;
                $columnIndex = 0;

                //Invoice Out
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'INVOICE NO');
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'PERIOD COVERED');
                $InvoiceOutAmountIndex = $columnIndex;
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'AMOUNT');
                $InvoiceOutDisputeAmountIndex = $columnIndex;
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'PENDING DISPUTE');

                $columnIndex++;

                //Payment IN
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'DATE');
                $PaymentInAmountIndex = $columnIndex;
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, $account_statement['AccountName'] . ' PAYMENT');
                $PaymentInBalanceIndex = $columnIndex;
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'BALANCE');

                $columnIndex++;

                //Invoice In
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'INVOICE NO');
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'PERIOD COVERED');
                $InvoiceInAmountIndex = $columnIndex;
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'AMOUNT');
                $InvoiceInDisputeAmountIndex = $columnIndex;
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'PENDING DISPUTE');

                $columnIndex++;

                //Payment Out
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'DATE');
                $PaymentOutAmountIndex = $columnIndex;
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, $account_statement['CompanyName'] . ' PAYMENT');
                $PaymentOutBalanceIndex = $columnIndex;
                AccountStatementCustomerController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'BALANCE');

                $RowIndex++;


                if (count($account_statement['result']) > 0) {


                    // Loop through result
                    foreach ($account_statement['result'] as $rowData) {


                        if (!isset($rowData['InvoiceOut_InvoiceNo'])) {
                            $rowData['InvoiceOut_InvoiceNo'] = '';
                        }
                        if (!isset($rowData['InvoiceOut_PeriodCover'])) {
                            $rowData['InvoiceOut_PeriodCover'] = '';
                        }
                        if (!isset($rowData['InvoiceOut_Amount'])) {
                            $rowData['InvoiceOut_Amount'] = 0;
                        }
                        if (!isset($rowData['InvoiceOut_DisputeAmount'])) {
                            $rowData['InvoiceOut_DisputeAmount'] = 0;
                        }
                        //Payment In
                        if (!isset($rowData['PaymentIn_PeriodCover'])) {
                            $rowData['PaymentIn_PeriodCover'] = '';
                        }
                        if (!isset($rowData['PaymentIn_PaymentID'])) {
                            $rowData['PaymentIn_PaymentID'] = '';
                        }
                        if (!isset($rowData['PaymentIn_Amount'])) {
                            $rowData['PaymentIn_Amount'] = 0;
                        }
                        //Invoice In
                        if (!isset($rowData['InvoiceIn_InvoiceNo'])) {
                            $rowData['InvoiceIn_InvoiceNo'] = '';
                        }
                        if (!isset($rowData['InvoiceIn_PeriodCover'])) {
                            $rowData['InvoiceIn_PeriodCover'] = '';
                        }
                        if (!isset($rowData['InvoiceIn_Amount'])) {
                            $rowData['InvoiceIn_Amount'] = 0;
                        }
                        if (!isset($rowData['InvoiceIn_DisputeAmount'])) {
                            $rowData['InvoiceIn_DisputeAmount'] = 0;
                        }
                        //Payment Out
                        if (!isset($rowData['PaymentOut_PeriodCover'])) {
                            $rowData['PaymentOut_PeriodCover'] = '';
                        }
                        if (!isset($rowData['PaymentOut_PaymentID'])) {
                            $rowData['PaymentOut_PaymentID'] = '';
                        }
                        if (!isset($rowData['PaymentOut_Amount'])) {
                            $rowData['PaymentOut_Amount'] = 0;
                        }

                        $InvoiceOut_Amount = $rowData['InvoiceOut_Amount'];

                        $InvoiceIn_Amount = $rowData['InvoiceIn_Amount'];

                        $InvoiceIn_DisputeAmount = $rowData['InvoiceIn_DisputeAmount'];

                        $InvoiceOut_DisputeAmount = $rowData['InvoiceOut_DisputeAmount'];

                        $PaymentIn_Amount = $rowData['PaymentIn_Amount'];

                        $PaymentOut_Amount = $rowData['PaymentOut_Amount'];


                        $columnIndex = 0;

                        //INVOICE OUT
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++]   . $RowIndex, $rowData['InvoiceOut_InvoiceNo']);
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $rowData['InvoiceOut_PeriodCover']);
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $InvoiceOut_Amount);
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $InvoiceOut_DisputeAmount);

                        $columnIndex++;


                        //PAYMEMT IN
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++]   . $RowIndex, $rowData['PaymentIn_PeriodCover']);
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $PaymentIn_Amount);
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, ''); //Balance

                        $columnIndex++;

                        //INVOICE In
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++]   . $RowIndex, $rowData['InvoiceIn_InvoiceNo']);
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $rowData['InvoiceIn_PeriodCover']);
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $InvoiceIn_Amount);
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $InvoiceIn_DisputeAmount);

                        $columnIndex++;

                        //PAYMEMT OUT
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++]   . $RowIndex, $rowData['PaymentOut_PeriodCover']);
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $PaymentOut_Amount);
                        AccountStatementCustomerController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, ''); //Balance

                        $RowIndex++;

                    }
                }

                /**
                 * SUMMERY DATA START
                 */

                $SOADataStartRow = ($StartRowIndex+1);
                $SOADataEndRow = $RowIndex-1;

                //Invoice Out Amount Total
                $InvoiceOutAmountTotalFormula = '=SUM('.$Alpha[$InvoiceOutAmountIndex].$SOADataStartRow  . ':' . $Alpha[$InvoiceOutAmountIndex] . $SOADataEndRow . ')';
                AccountStatementCustomerController::insertExcelSummeryData($sheet, $Alpha[$InvoiceOutAmountIndex].$RowIndex, $InvoiceOutAmountTotalFormula);

                //Invoice Out Dispute Total
                $InvoiceOutDisputeAmountTotalFormula = '=SUM('.$Alpha[$InvoiceOutDisputeAmountIndex].$SOADataStartRow  . ':' . $Alpha[$InvoiceOutDisputeAmountIndex] . $SOADataEndRow . ')';
                AccountStatementCustomerController::insertExcelSummeryData($sheet, $Alpha[$InvoiceOutDisputeAmountIndex].$RowIndex, $InvoiceOutDisputeAmountTotalFormula);



                //Total Payment In Amount
                $PaymentInAmountTotalFormula = '=SUM('.$Alpha[$PaymentInAmountIndex].$SOADataStartRow  . ':' . $Alpha[$PaymentInAmountIndex] . $SOADataEndRow . ')';
                AccountStatementCustomerController::insertExcelSummeryData($sheet, $Alpha[$PaymentInAmountIndex].$RowIndex, $PaymentInAmountTotalFormula);

                //Total balance after Payment In
                $PaymentInBalanceFormula = '=('.$Alpha[$InvoiceOutAmountIndex].$RowIndex  . '-' . $Alpha[$PaymentInAmountIndex].$RowIndex . ')';
                AccountStatementCustomerController::insertExcelSummeryData($sheet, $Alpha[$PaymentInBalanceIndex].$RowIndex, $PaymentInBalanceFormula);
                $PaymentInBalanceIndexCell = $Alpha[$PaymentInBalanceIndex].$RowIndex;



                //Invoice In Amount Total
                $InvoiceInAmountTotalFormula = '=SUM('.$Alpha[$InvoiceInAmountIndex].$SOADataStartRow  . ':' . $Alpha[$InvoiceInAmountIndex] . $SOADataEndRow . ')';
                AccountStatementCustomerController::insertExcelSummeryData($sheet, $Alpha[$InvoiceInAmountIndex].$RowIndex, $InvoiceInAmountTotalFormula);

                //Invoice In Dispute Total
                $InvoiceInDisputeAmountTotalFormula = '=SUM('.$Alpha[$InvoiceInDisputeAmountIndex].$SOADataStartRow  . ':' . $Alpha[$InvoiceInDisputeAmountIndex] . $SOADataEndRow . ')';
                AccountStatementCustomerController::insertExcelSummeryData($sheet, $Alpha[$InvoiceInDisputeAmountIndex].$RowIndex, $InvoiceInDisputeAmountTotalFormula);



                //Total Payment Out Amount
                $PaymentOutAmountTotalFormula = '=SUM('.$Alpha[$PaymentOutAmountIndex].$SOADataStartRow  . ':' . $Alpha[$PaymentOutAmountIndex] . $SOADataEndRow . ')';
                AccountStatementCustomerController::insertExcelSummeryData($sheet, $Alpha[$PaymentOutAmountIndex].$RowIndex, $PaymentOutAmountTotalFormula);

                //Total balance after Payment Out
                $PaymentOutBalanceFormula = '=('.$Alpha[$InvoiceInAmountIndex].$RowIndex  . '-' . $Alpha[$PaymentOutAmountIndex].$RowIndex . ')';
                AccountStatementCustomerController::insertExcelSummeryData($sheet, $Alpha[$PaymentOutBalanceIndex].$RowIndex, $PaymentOutBalanceFormula);
                $PaymentOutBalanceIndexCell = $Alpha[$PaymentOutBalanceIndex].$RowIndex;
                $RowIndex++;

                /**
                 * SOA OFFSET SUMMERY DATA START
                 */
                $sheet->mergeCells($Alpha[2].$RowIndex. ':' . $Alpha[7] . $RowIndex);
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[1].$RowIndex, 'BALANCE AFTER OFFSET:',14);

                $BalanceAfterOffsetFormula = '=('.$PaymentInBalanceIndexCell  . '-' . $PaymentOutBalanceIndexCell . ')';
                AccountStatementController::insertExcelSummeryData($sheet, $Alpha[2].$RowIndex, $BalanceAfterOffsetFormula);

                // BALANCE BROUGHT FORWARD:
                $sheet->mergeCells($Alpha[10].$RowIndex. ':' . $Alpha[count($Alpha)-1] . $RowIndex);
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[9].$RowIndex, 'BALANCE BROUGHT FORWARD:',14);
                AccountStatementController::insertExcelSummeryData($sheet, $Alpha[10].$RowIndex, $account_statement['BroughtForwardOffset']);

            });
        })->download('xls');
    }

    static function generateExcel_OLD($account_statement){
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
                    AccountStatementCustomerController::formateCell($cell);
                    $cell->setValue($account_statement['firstCompany'].' INVOICE');
                    $cell->setFontSize(12);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('A5', function($cell){
                    AccountStatementCustomerController::formateCell($cell);
                    $cell->setValue('INVOICE NO');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('B5', function($cell){
                    AccountStatementCustomerController::formateCell($cell);
                    $cell->setValue('PERIOD COVERED');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('C5', function($cell){
                    AccountStatementCustomerController::formateCell($cell);
                    $cell->setValue('AMOUNT');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('E5', function($cell){
                    AccountStatementCustomerController::formateCell($cell);
                    $cell->setValue('DATE');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('F5', function($cell) use ($account_statement){
                    AccountStatementCustomerController::formateCell($cell);
                    $cell->setValue($account_statement['secondCompany'].' PAYMENT');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('G5', function($cell) {
                    AccountStatementCustomerController::formateCell($cell);
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
                                    AccountStatementCustomerController::formateCell($cell,false);
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
                                    AccountStatementCustomerController::formateCell($cell);
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
                    AccountStatementCustomerController::formateCell($cell);
                    $cell->setValue($account_statement['secondCompany'].' INVOICE');
                    $cell->setFontSize(12);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('I5', function($cell){
                    AccountStatementCustomerController::formateCell($cell);
                    $cell->setValue('INVOICE NO');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('J5', function($cell){
                    AccountStatementCustomerController::formateCell($cell);
                    $cell->setValue('PERIOD COVERED');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('K5', function($cell){
                    AccountStatementCustomerController::formateCell($cell);
                    $cell->setValue('AMOUNT');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('M5', function($cell){
                    AccountStatementCustomerController::formateCell($cell);
                    $cell->setValue('DATE');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('N5', function($cell)use($account_statement){
                    AccountStatementCustomerController::formateCell($cell);
                    $cell->setValue($account_statement['firstCompany'].' PAYMENT');
                    $cell->setFontSize(11);
                    $cell->setFontWeight('bold');
                });
                $sheet->cell('O5', function($cell) {
                    AccountStatementCustomerController::formateCell($cell);
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
                                    AccountStatementCustomerController::formateCell($cell,false);
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
                                    AccountStatementCustomerController::formateCell($cell);
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
                    AccountStatementCustomerController::formateCell($cell,false);
                    $formula = '=SUM(C'.$startrowtemp.':C'.($startRow-1).')';
                    $cell->setValue($formula);
                });
                //Sum up payments amount for company
                $sheet->cell('F' . $startRow, function ($cell) use ($startrowtemp,$startRow) {
                    AccountStatementCustomerController::formateCell($cell,false);
                    $formula = '=SUM(F'.$startrowtemp.':F'.($startRow-1).')';
                    $cell->setValue($formula);
                });
                //ballance for Company invoices and payments
                $sheet->cell('G' . $startRow, function ($cell) use ($startRow) {
                    AccountStatementCustomerController::formateCell($cell,false);
                    $formula = '= C'.($startRow).'-F'.($startRow);
                    $cell->setValue($formula);
                });

                //Sum up invoices amount for customer or vendor
                $sheet->cell('K' . $startRow, function ($cell) use ($startrowtemp,$startRow) {
                    AccountStatementCustomerController::formateCell($cell,false);
                    $formula = '=SUM(K'.$startrowtemp.':K'.($startRow-1).')';
                    $cell->setValue($formula);
                });
                //Sum up payments amount for customer or vendor
                $sheet->cell('N' . $startRow, function ($cell) use ($startrowtemp,$startRow) {
                    AccountStatementCustomerController::formateCell($cell,false);
                    $formula = '=SUM(N'.$startrowtemp.':N'.($startRow-1).')';
                    $cell->setValue($formula);
                });
                //ballance for customer or vendor invoices and payments
                $sheet->cell('O' . $startRow, function ($cell) use ($startRow) {
                    AccountStatementCustomerController::formateCell($cell,false);
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

// Excel functions
    public static function formateExcelCell(&$cell, $isCenter = true)
    {
        $cell->setFont(array(
            'family' => 'Arial',
            'size' => '11',
            'bold' => false
        ));
        if ($isCenter) {
            $cell->setAlignment('center');
        }
    }

// Excel functions
    public static function insertExcelCellData(&$sheet, $target_cell, $value)
    {

        $sheet->cell($target_cell, function ($cell) use ($value) {
            AccountStatementCustomerController::formateExcelCell($cell, false);
            $cell->setValue($value);
            $cell->setBackground('#EBF5F2');
        });

    }

    public static function insertExcelHeaderData(&$sheet, $target_cell, $value , $font_size = 11){
        $sheet->cell($target_cell, function($cell) use ($value, $font_size) {
            AccountStatementCustomerController::formateExcelCell($cell);
            $cell->setValue($value);
            $cell->setFontSize($font_size);
            $cell->setFontWeight('bold');
            $cell->setAlignment('center');
        });

    }

    public static function insertExcelSummeryData(&$sheet, $target_cell, $value , $font_size = 11){

        $sheet->cell($target_cell, function ($cell) use ($value , $font_size) {
            AccountStatementCustomerController::formateExcelCell($cell, false);
            $cell->setValue($value);
            $cell->setFontSize($font_size);
        });

    }
    /**
     * Cleanup duplicate Invoice against multiple payments against single Invoice
     */
    public function cleanup_duplicate_records($soa_result = array()){

        if(count($soa_result) > 0) {
            $InvoiceOut_InvoiceNo = array();
            foreach ($soa_result as $index => $soa_result_row) {

                if (!empty($soa_result[$index]["InvoiceOut_InvoiceNo"])) {

                    if (!in_array($soa_result[$index]["InvoiceOut_InvoiceNo"], $InvoiceOut_InvoiceNo)) {
                        $InvoiceOut_InvoiceNo[] = $soa_result[$index]["InvoiceOut_InvoiceNo"];

                    } else {
                        $soa_result[$index]["InvoiceOut_InvoiceNo"] = "";
                        $soa_result[$index]["InvoiceOut_PeriodCover"] = "";
                        $soa_result[$index]["InvoiceOut_Amount"] = "";
                    }
                }
            }
            $InvoiceIn_InvoiceNo = array();
            foreach ($soa_result as $index => $soa_result_row) {

                if (!empty($soa_result[$index]["InvoiceIn_InvoiceNo"])) {

                    if (!in_array($soa_result[$index]["InvoiceIn_InvoiceNo"], $InvoiceIn_InvoiceNo)) {
                        $InvoiceIn_InvoiceNo[] = $soa_result[$index]["InvoiceIn_InvoiceNo"];

                    } else {
                        $soa_result[$index]["InvoiceIn_InvoiceNo"] = "";
                        $soa_result[$index]["InvoiceIn_PeriodCover"] = "";
                        $soa_result[$index]["InvoiceIn_Amount"] = "0";
                    }
                }
            }
        }
        return $soa_result;
    }

    /**
     * Format Records tobe display and export
     * @param $soa_records
     * @param int $roundplaces
     * @return mixed
     */
    public function format_records($invoice_records,$type,$roundplaces=2)
    {
        if (count($invoice_records) > 0) {

            // Loop through result
            foreach ($invoice_records as $key => $rowData) {

                if($type == Invoice::INVOICE_OUT) {

                    if (!isset($rowData['InvoiceOut_InvoiceNo'])) {
                        $rowData['InvoiceOut_InvoiceNo'] = '';
                    }
                    if (!isset($rowData['InvoiceOut_PeriodCover'])) {
                        $rowData['InvoiceOut_PeriodCover'] = '';
                    }
                    if (!isset($rowData['InvoiceOut_Amount'])) {
                        $rowData['InvoiceOut_Amount'] = 0;
                    }
                    if (!isset($rowData['InvoiceOut_DisputeAmount'])) {
                        $rowData['InvoiceOut_DisputeAmount'] = 0;
                    }
                    //Payment In
                    if (!isset($rowData['PaymentIn_PeriodCover'])) {
                        $rowData['PaymentIn_PeriodCover'] = '';
                    }
                    if (!isset($rowData['PaymentIn_PaymentID'])) {
                        $rowData['PaymentIn_PaymentID'] = '';
                    }
                    if (!isset($rowData['PaymentIn_Amount'])) {
                        $rowData['PaymentIn_Amount'] = 0;
                    }

                    $InvoiceOut_Amount = number_format($rowData['InvoiceOut_Amount'], $roundplaces, '.', '');
                    $rowData['InvoiceOut_Amount'] = $InvoiceOut_Amount != 0 ? $InvoiceOut_Amount : '';

                    $PaymentIn_Amount = number_format($rowData['PaymentIn_Amount'], $roundplaces, '.', '');
                    $rowData['PaymentIn_Amount'] = $PaymentIn_Amount != 0 ? $PaymentIn_Amount : '';

                    $InvoiceOut_DisputeAmount = number_format($rowData['InvoiceOut_DisputeAmount'], $roundplaces, '.', '');
                    $rowData['InvoiceOut_DisputeAmount'] = $InvoiceOut_DisputeAmount != 0 ? $InvoiceOut_DisputeAmount : '';

                }
                else if($type == Invoice::INVOICE_IN) {

                    //Invoice In
                    if (!isset($rowData['InvoiceIn_InvoiceNo'])) {
                        $rowData['InvoiceIn_InvoiceNo'] = '';
                    }
                    if (!isset($rowData['InvoiceIn_PeriodCover'])) {
                        $rowData['InvoiceIn_PeriodCover'] = '';
                    }
                    if (!isset($rowData['InvoiceIn_Amount'])) {
                        $rowData['InvoiceIn_Amount'] = 0;
                    }
                    if (!isset($rowData['InvoiceIn_DisputeAmount'])) {
                        $rowData['InvoiceIn_DisputeAmount'] = 0;
                    }
                    //Payment Out
                    if (!isset($rowData['PaymentOut_PeriodCover'])) {
                        $rowData['PaymentOut_PeriodCover'] = '';
                    }
                    if (!isset($rowData['PaymentOut_PaymentID'])) {
                        $rowData['PaymentOut_PaymentID'] = '';
                    }
                    if (!isset($rowData['PaymentOut_Amount'])) {
                        $rowData['PaymentOut_Amount'] = 0;
                    }

                    $InvoiceIn_Amount = number_format($rowData['InvoiceIn_Amount'], $roundplaces, '.', '');
                    $rowData['InvoiceIn_Amount'] = $InvoiceIn_Amount != 0 ? $InvoiceIn_Amount : '';

                    $InvoiceIn_DisputeAmount = number_format($rowData['InvoiceIn_DisputeAmount'], $roundplaces, '.', '');
                    $rowData['InvoiceIn_DisputeAmount'] = $InvoiceIn_DisputeAmount != 0 ? $InvoiceIn_DisputeAmount : '';

                    $PaymentOut_Amount = number_format($rowData['PaymentOut_Amount'], $roundplaces, '.', '');
                    $rowData['PaymentOut_Amount'] = $PaymentOut_Amount != 0 ? $PaymentOut_Amount : '';

                }



                $invoice_records[$key] = $rowData;
            }
        }
        return $invoice_records;
    }

    /** Merge all payments against single Invoice.
     * @param $soa_records
     * @return mixed
     */
    public function merge_single_invoice_payments($invoice_records,$type) {

        if (count($invoice_records) > 0) {

            // Loop through result
            for ($key = 0 ; $key < count($invoice_records); $key++ ) {

                $next  = $key + 1 ;

                if($type == Invoice::INVOICE_OUT ) {
                    // check if same Invoice no.
                    while (!empty($invoice_records[$key]['InvoiceOut_InvoiceNo']) && isset($invoice_records[$next]['InvoiceOut_InvoiceNo'])
                        && $invoice_records[$key]['InvoiceOut_InvoiceNo'] == $invoice_records[$next]['InvoiceOut_InvoiceNo']
                    ) {
                        //$invoice_records[$key]['InvoiceOut_InvoiceNo'] = "";

                        $invoice_records[$key]['PaymentIn_PeriodCover'] .= '<br>' . $invoice_records[$next]['PaymentIn_PeriodCover'];

                        if(!empty($invoice_records[$next]['PaymentIn_Amount'])){

                            $invoice_records[$key]['PaymentIn_Amount'] .= ',' .$invoice_records[$next]['PaymentIn_Amount'] ;
                            $invoice_records[$key]['PaymentIn_PaymentID'] .= ',' . $invoice_records[$next]['PaymentIn_PaymentID'] ;
                            unset($invoice_records[$next]);
                        }
                        $next++;
                    }
                } else if ($type == Invoice::INVOICE_IN ) {

                    // check if same Invoice no.
                    while (!empty($invoice_records[$key]['InvoiceIn_InvoiceNo']) && isset($invoice_records[$next]['InvoiceIn_InvoiceNo'])
                        && $invoice_records[$key]['InvoiceIn_InvoiceNo'] == $invoice_records[$next]['InvoiceIn_InvoiceNo']
                    ) {
                        //$invoice_records[$key]['InvoiceIn_InvoiceNo'] = "";

                        $invoice_records[$key]['PaymentOut_PeriodCover'] .= '<br>' . $invoice_records[$next]['PaymentOut_PeriodCover'];
                        if(!empty($invoice_records[$next]['PaymentOut_Amount'])){

                            $invoice_records[$key]['PaymentOut_Amount'] .= ',' . $invoice_records[$next]['PaymentOut_Amount']  ;
                            $invoice_records[$key]['PaymentOut_PaymentID'] .=  ',' . $invoice_records[$next]['PaymentOut_PaymentID'] ;
                            unset($invoice_records[$next]);
                        }

                        $next++;

                    }
                }

                //$invoice_records[$key] = $rowData;
            }
        }
        return $invoice_records;
    }

}