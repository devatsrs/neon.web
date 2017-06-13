<?php

class AccountStatementController extends \BaseController
{


    public function ajax_datagrid()
    {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['AccountID'] = $data['AccountID'] != '' ? $data['AccountID'] : 0;
        //$query = "prc_getSOA ".$CompanyID.",".$data['AccountID'].",0";
        $account = Account::find($data['AccountID']);
        $roundplaces = get_round_decimal_places($data['AccountID']);
        $CurencySymbol = Currency::getCurrencySymbol($account->CurrencyId);


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
        $id = 0;
        $companyID = User::get_companyID();
        $accounts = Account::getAccountIDList();
        $CompanyName = Company::getName();
        return View::make('accountstatement.index', compact('accounts', 'CompanyName'));
    }

    public function getPayment()
    {
        $data = Input::all();

        $result = Payment::where(["PaymentID" => $data['id']])->first()->toArray();

        if(isset($result["CurrencyID"]) && $result["CurrencyID"] > 0 ){

            $CurrencyCode = Currency::find($result["CurrencyID"])->pluck("Code");
            $result["Currency"] = $CurrencyCode;
        }
        echo json_encode($result);
    }

    public function exports($type)
    {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['AccountID'] = $data['AccountID'] != '' ? $data['AccountID'] : 0;

        $account = Account::find($data['AccountID']);
        $roundplaces = get_round_decimal_places($data['AccountID']);
        $query = "call prc_getSOA (" . $CompanyID . "," . $data['AccountID'] . ",'" . $data['StartDate'] . "','" . $data['EndDate'] . "',1)";
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

        AccountStatementController::generateExcel($output, $type);
    }

    static function generateExcel($account_statement, $type)
    {
        Excel::create('Account Statement', function ($excel) use ($account_statement) {
            $excel->sheet('Account Statement', function ($sheet) use ($account_statement) {
                //$sheet->mergeCells('A4:D4');
                //$sheet->getCell('B4')->setValue('Wavetel Ltd INVOICE');


                /**
                 * Not used as formula will replace this value.

                $InvoiceOutAmountTotal = $account_statement['InvoiceOutAmountTotal'];
                $PaymentInAmountTotal = $account_statement['PaymentInAmountTotal'];
                $InvoiceInAmountTotal = $account_statement['InvoiceInAmountTotal'];
                $PaymentOutAmountTotal = $account_statement['PaymentOutAmountTotal'];
                $InvoiceOutDisputeAmountTotal = $account_statement['InvoiceOutDisputeAmountTotal'];
                $InvoiceInDisputeAmountTotal = $account_statement['InvoiceInDisputeAmountTotal'];
                $CompanyBalance = $account_statement['CompanyBalance'];
                $AccountBalance = $account_statement['AccountBalance'];
                $OffsetBalance = $account_statement['OffsetBalance'];
                */

                //$CurencySymbol = $account_statement['CurencySymbol'];
                //$roundplaces = $account_statement['roundplaces'];


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
                AccountStatementController::insertExcelHeaderData($sheet, "A2",'INVOICE OFFSETTING',14 );

                /**
                 * SUB HEADINGS
                 */
                $sheet->mergeCells('A4:D4');
                $sheet->mergeCells('I4:L4');
                AccountStatementController::insertExcelHeaderData($sheet, "A4",$account_statement['CompanyName'] . ' INVOICE',12);
                AccountStatementController::insertExcelHeaderData($sheet, "I4",$account_statement['AccountName'] . ' INVOICE',12);


                /**
                 * SOA COLUMNS
                 */

                $StartRowIndex = $RowIndex = 5;
                $columnIndex = 0;

                //Invoice Out
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'INVOICE NO');
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'PERIOD COVERED');
                $InvoiceOutAmountIndex = $columnIndex;
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'AMOUNT');
                $InvoiceOutDisputeAmountIndex = $columnIndex;
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'PENDING DISPUTE');

                $columnIndex++;

                //Payment IN
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'DATE');
                $PaymentInAmountIndex = $columnIndex;
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, $account_statement['AccountName'] . ' PAYMENT');
                $PaymentInBalanceIndex = $columnIndex;
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'BALANCE');

                $columnIndex++;

                //Invoice In
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'INVOICE NO');
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'PERIOD COVERED');
                $InvoiceInAmountIndex = $columnIndex;
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'AMOUNT');
                $InvoiceInDisputeAmountIndex = $columnIndex;
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'PENDING DISPUTE');

                $columnIndex++;

                //Payment Out
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'DATE');
                $PaymentOutAmountIndex = $columnIndex;
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, $account_statement['CompanyName'] . ' PAYMENT');
                $PaymentOutBalanceIndex = $columnIndex;
                AccountStatementController::insertExcelHeaderData($sheet, $Alpha[$columnIndex++] . $RowIndex, 'BALANCE');

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
                            $rowData['PaymentIn_Amount'] = '';
                        }
                        //Invoice In
                        if (!isset($rowData['InvoiceIn_InvoiceNo'])) {
                            $rowData['InvoiceIn_InvoiceNo'] = '';
                        }
                        if (!isset($rowData['InvoiceIn_PeriodCover'])) {
                            $rowData['InvoiceIn_PeriodCover'] = '';
                        }
                        if (!isset($rowData['InvoiceIn_Amount'])) {
                            $rowData['InvoiceIn_Amount'] = '';
                        }
                        if (!isset($rowData['InvoiceIn_DisputeAmount'])) {
                            $rowData['InvoiceIn_DisputeAmount'] = '';
                        }
                        //Payment Out
                        if (!isset($rowData['PaymentOut_PeriodCover'])) {
                            $rowData['PaymentOut_PeriodCover'] = '';
                        }
                        if (!isset($rowData['PaymentOut_PaymentID'])) {
                            $rowData['PaymentOut_PaymentID'] = '';
                        }
                        if (!isset($rowData['PaymentOut_Amount'])) {
                            $rowData['PaymentOut_Amount'] = '';
                        }

                        $InvoiceOut_Amount = $rowData['InvoiceOut_Amount'];

                        $InvoiceIn_Amount = $rowData['InvoiceIn_Amount'];

                        $InvoiceIn_DisputeAmount = $rowData['InvoiceIn_DisputeAmount'];

                        $InvoiceOut_DisputeAmount = $rowData['InvoiceOut_DisputeAmount'];

                        $PaymentIn_Amount = $rowData['PaymentIn_Amount'];

                        $PaymentOut_Amount = $rowData['PaymentOut_Amount'];


                        $columnIndex = 0;

                        //INVOICE OUT
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++]   . $RowIndex, $rowData['InvoiceOut_InvoiceNo']);
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $rowData['InvoiceOut_PeriodCover']);
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $InvoiceOut_Amount);
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $InvoiceOut_DisputeAmount);

                        $columnIndex++;


                        //PAYMEMT IN
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++]   . $RowIndex, $rowData['PaymentIn_PeriodCover']);
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $PaymentIn_Amount);
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, ''); //Balance

                        $columnIndex++;

                        //INVOICE In
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++]   . $RowIndex, $rowData['InvoiceIn_InvoiceNo']);
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $rowData['InvoiceIn_PeriodCover']);
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $InvoiceIn_Amount);
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $InvoiceIn_DisputeAmount);

                        $columnIndex++;

                        //PAYMEMT OUT
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++]   . $RowIndex, $rowData['PaymentOut_PeriodCover']);
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, $PaymentOut_Amount);
                        AccountStatementController::insertExcelCellData($sheet, $Alpha[$columnIndex++] . $RowIndex, ''); //Balance

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
                AccountStatementController::insertExcelSummeryData($sheet, $Alpha[$InvoiceOutAmountIndex].$RowIndex, $InvoiceOutAmountTotalFormula);

                //Invoice Out Dispute Total
                $InvoiceOutDisputeAmountTotalFormula = '=SUM('.$Alpha[$InvoiceOutDisputeAmountIndex].$SOADataStartRow  . ':' . $Alpha[$InvoiceOutDisputeAmountIndex] . $SOADataEndRow . ')';
                AccountStatementController::insertExcelSummeryData($sheet, $Alpha[$InvoiceOutDisputeAmountIndex].$RowIndex, $InvoiceOutDisputeAmountTotalFormula);



                //Total Payment In Amount
                $PaymentInAmountTotalFormula = '=SUM('.$Alpha[$PaymentInAmountIndex].$SOADataStartRow  . ':' . $Alpha[$PaymentInAmountIndex] . $SOADataEndRow . ')';
                AccountStatementController::insertExcelSummeryData($sheet, $Alpha[$PaymentInAmountIndex].$RowIndex, $PaymentInAmountTotalFormula);

                //Total balance after Payment In
                $PaymentInBalanceFormula = '=('.$Alpha[$InvoiceOutAmountIndex].$RowIndex  . '-' . $Alpha[$PaymentInAmountIndex].$RowIndex . ')';
                AccountStatementController::insertExcelSummeryData($sheet, $Alpha[$PaymentInBalanceIndex].$RowIndex, $PaymentInBalanceFormula);
                $PaymentInBalanceIndexCell = $Alpha[$PaymentInBalanceIndex].$RowIndex;



                //Invoice In Amount Total
                $InvoiceInAmountTotalFormula = '=SUM('.$Alpha[$InvoiceInAmountIndex].$SOADataStartRow  . ':' . $Alpha[$InvoiceInAmountIndex] . $SOADataEndRow . ')';
                AccountStatementController::insertExcelSummeryData($sheet, $Alpha[$InvoiceInAmountIndex].$RowIndex, $InvoiceInAmountTotalFormula);

                //Invoice In Dispute Total
                $InvoiceInDisputeAmountTotalFormula = '=SUM('.$Alpha[$InvoiceInDisputeAmountIndex].$SOADataStartRow  . ':' . $Alpha[$InvoiceInDisputeAmountIndex] . $SOADataEndRow . ')';
                AccountStatementController::insertExcelSummeryData($sheet, $Alpha[$InvoiceInDisputeAmountIndex].$RowIndex, $InvoiceInDisputeAmountTotalFormula);



                //Total Payment Out Amount
                $PaymentOutAmountTotalFormula = '=SUM('.$Alpha[$PaymentOutAmountIndex].$SOADataStartRow  . ':' . $Alpha[$PaymentOutAmountIndex] . $SOADataEndRow . ')';
                AccountStatementController::insertExcelSummeryData($sheet, $Alpha[$PaymentOutAmountIndex].$RowIndex, $PaymentOutAmountTotalFormula);

                //Total balance after Payment Out
                $PaymentOutBalanceFormula = '=('.$Alpha[$InvoiceInAmountIndex].$RowIndex  . '-' . $Alpha[$PaymentOutAmountIndex].$RowIndex . ')';
                AccountStatementController::insertExcelSummeryData($sheet, $Alpha[$PaymentOutBalanceIndex].$RowIndex, $PaymentOutBalanceFormula);
                $PaymentOutBalanceIndexCell = $Alpha[$PaymentOutBalanceIndex].$RowIndex;
                $RowIndex++;

                /**
                 * SOA OFFSET SUMMERY DATA START
                 */
                $RowIndex = $RowIndex + 3; // give some space

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
            AccountStatementController::formateExcelCell($cell, false);
            $cell->setValue($value);
            $cell->setBackground('#EBF5F2');
        });

    }

    public static function insertExcelHeaderData(&$sheet, $target_cell, $value , $font_size = 11){
        $sheet->cell($target_cell, function($cell) use ($value, $font_size) {
            AccountStatementController::formateExcelCell($cell);
            $cell->setValue($value);
            $cell->setFontSize($font_size);
            $cell->setFontWeight('bold');
            $cell->setAlignment('center');
        });

    }

    public static function insertExcelSummeryData(&$sheet, $target_cell, $value , $font_size = 11){

        $sheet->cell($target_cell, function ($cell) use ($value , $font_size) {
            AccountStatementController::formateExcelCell($cell, false);
            $cell->setValue($value);
            $cell->setFontSize($font_size);
        });


    }

    /**
     * Cleanup duplicate Invoice against multiple payments against single Invoice
     * compare invoice number and its period covered if both are duplicate then clear the record with blank
     * some times invoice number can be duplicate but not period covered is different.
     */
    public function cleanup_duplicate_records($soa_result = array()){

        if(count($soa_result) > 0) {

            $InvoiceOut_PeriodCovered = array();
            foreach ($soa_result as $index => $soa_result_row) {


                if (isset($soa_result[$index]["InvoiceOut_InvoiceNo"]) &&  !empty($soa_result[$index]["InvoiceOut_InvoiceNo"])) {

                    $InvoiceNo = $soa_result[$index]["InvoiceOut_InvoiceNo"];

                    $PeriodCover = $soa_result[$index]["InvoiceOut_PeriodCover"];

                    $add_record = false;

                    if ( !isset($InvoiceOut_PeriodCovered[$InvoiceNo]) || (is_array($InvoiceOut_PeriodCovered[$InvoiceNo]) && !in_array($PeriodCover, $InvoiceOut_PeriodCovered[$InvoiceNo]))){

                        $add_record = true;
                    }

                    if($add_record) {
                        $InvoiceOut_PeriodCovered[$InvoiceNo][] = $PeriodCover;


                    } else {

                        $soa_result[$index]["InvoiceOut_InvoiceNo"] = "";
                        $soa_result[$index]["InvoiceOut_PeriodCover"] = "";
                        $soa_result[$index]["InvoiceOut_Amount"] = "";
                    }
                }
            }

            $InvoiceIn_PeriodCovered = array();

            foreach ($soa_result as $index => $soa_result_row) {


                if (isset($soa_result[$index]["InvoiceIn_InvoiceNo"]) &&  !empty($soa_result[$index]["InvoiceIn_InvoiceNo"])) {

                    $InvoiceNo = $soa_result[$index]["InvoiceIn_InvoiceNo"];
                    $PeriodCover = $soa_result[$index]["InvoiceIn_PeriodCover"];

                    $add_record = false;

                    if ( !isset($InvoiceIn_PeriodCovered[$InvoiceNo]) || (  is_array($InvoiceIn_PeriodCovered[$InvoiceNo]) && !in_array($PeriodCover, $InvoiceIn_PeriodCovered[$InvoiceNo]))){

                        $add_record = true;
                    }

                    if($add_record) {

                        $InvoiceIn_PeriodCovered[$InvoiceNo][] = $PeriodCover;

                    } else {

                        $soa_result[$index]["InvoiceIn_InvoiceNo"] = "";
                        $soa_result[$index]["InvoiceIn_PeriodCover"] = "";
                        $soa_result[$index]["InvoiceIn_Amount"] = "0";
                    }

                }
            }

            $soa_result = $this->remove_duplicate_payments($soa_result, '' );

        }

        return $soa_result;
    }

    /**
     * remove duplicate payments , due to same invoice number.
     */
    public function remove_duplicate_payments( $soa_result = array(), $type ) {
        /**
         * Remove duplicate payments in and out.
         */

        if(count($soa_result) > 0) {

            $PaymentIn_PeriodCovered = array();

            if(empty($type) || $type == Invoice::INVOICE_OUT ) {

                foreach ($soa_result as $index => $soa_result_row) {

                    if (isset($soa_result[$index]["PaymentIn_PaymentID"]) && !empty($soa_result[$index]["PaymentIn_PaymentID"])) {

                        $PaymentID = $soa_result[$index]["PaymentIn_PaymentID"];
                        $PeriodCover = $soa_result[$index]["PaymentIn_PeriodCover"];

                        $add_record = false;

                        if (!isset($PaymentIn_PeriodCovered[$PaymentID]) || (is_array($PaymentIn_PeriodCovered[$PaymentID]) && !in_array($PeriodCover, $PaymentIn_PeriodCovered[$PaymentID]))) {

                            $add_record = true;
                        }

                        if ($add_record) {

                            $PaymentIn_PeriodCovered[$PaymentID][] = $PeriodCover;

                        } else {

                            $soa_result[$index]["PaymentIn_PaymentID"] = "";
                            $soa_result[$index]["PaymentIn_PeriodCover"] = "";
                            $soa_result[$index]["PaymentIn_Amount"] = "";
                        }

                    }
                }
                if( $type == Invoice::INVOICE_OUT ) {

                    return $soa_result;
                }

            }

            if(empty($type) || $type == Invoice::INVOICE_IN) {

                $PaymentOut_PeriodCovered = array();

                foreach ($soa_result as $index => $soa_result_row) {


                    if (isset($soa_result[$index]["PaymentOut_PaymentID"]) && !empty($soa_result[$index]["PaymentOut_PaymentID"])) {

                        $PaymentID = $soa_result[$index]["PaymentOut_PaymentID"];
                        $PeriodCover = $soa_result[$index]["PaymentOut_PeriodCover"];

                        $add_record = false;

                        if (!isset($PaymentOut_PeriodCovered[$PaymentID]) || (is_array($PaymentOut_PeriodCovered[$PaymentID]) && !in_array($PeriodCover, $PaymentOut_PeriodCovered[$PaymentID]))) {

                            $add_record = true;
                        }

                        if ($add_record) {

                            $PaymentOut_PeriodCovered[$PaymentID][] = $PeriodCover;

                        } else {

                            $soa_result[$index]["PaymentOut_PaymentID"] = "";
                            $soa_result[$index]["PaymentOut_PeriodCover"] = "";
                            $soa_result[$index]["PaymentOut_Amount"] = "";
                        }

                    }
                }

                if( $type == Invoice::INVOICE_IN ) {

                    return $soa_result;
                }

            }


        }

        if(empty($type)){

            return $soa_result;

        }

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

        /**
         * Remove duplicate payments.
         */
        $invoice_records = $this->remove_duplicate_payments($invoice_records,$type);


        if (count($invoice_records) > 0) {

            // Loop through result
            for ($key = 0 ; $key < count($invoice_records); $key++ ) {

                $next  = $key + 1 ;

                if($type == Invoice::INVOICE_OUT ) {
                    // check if same Invoice no.
                    while (!empty($invoice_records[$key]['InvoiceOut_InvoiceNo']) && isset($invoice_records[$next]['InvoiceOut_InvoiceNo'])
                        && $invoice_records[$key]['InvoiceOut_InvoiceNo'] == $invoice_records[$next]['InvoiceOut_InvoiceNo']
                        && !empty($invoice_records[$next]['PaymentIn_PeriodCover'])

                    ) {

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
                        && !empty($invoice_records[$next]['PaymentOut_PeriodCover'])
                    ) {

                        $invoice_records[$key]['PaymentOut_PeriodCover'] .= '<br>' . $invoice_records[$next]['PaymentOut_PeriodCover'];

                        if(!empty($invoice_records[$next]['PaymentOut_Amount'])){

                            $invoice_records[$key]['PaymentOut_Amount'] .= ',' . $invoice_records[$next]['PaymentOut_Amount']  ;
                            $invoice_records[$key]['PaymentOut_PaymentID'] .=  ',' . $invoice_records[$next]['PaymentOut_PaymentID'] ;
                            unset($invoice_records[$next]);
                        }
                        
                        $next++;

                    }
                }

            }
        }
        return $invoice_records;
    }
}