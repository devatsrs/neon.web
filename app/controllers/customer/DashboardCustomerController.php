<?php

class DashboardCustomerController extends BaseController {

    
    public function __construct() {

    }
    public function home() {
        $CustomerID = Customer::get_accountID();
        $account = Account::find($CustomerID);
        return View::make('customer.index',compact('account'));
    }
    public function invoice_expense_chart(){
        $data = Input::all();
        $CurrencyID = "";
        $CustomerID = Customer::get_accountID();
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
        }
        $companyID = User::get_companyID();
        $query = "call prc_getDashboardinvoiceExpense ('". $companyID  . "',  '". $CurrencyID  . "','".$CustomerID."')";
        $InvoiceExpenseResult = DataTableSql::of($query, 'sqlsrv2')->getProcResult(array('InvoiceExpense'));
        $InvoiceExpense = $InvoiceExpenseResult['data']['InvoiceExpense'];
        return View::make('customer.billingdashboard.invoice_expense_chart', compact('InvoiceExpense'));

    }

    public function invoice_expense_total(){

        $data = Input::all();
        $CustomerID = Customer::get_accountID();
        $CurrencyID = "";
        $CurrencySymbol = $CurrencyCode = "";
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
            $CurrencyCode = Currency::getCurrency($CurrencyID);
            $CurrencySymbol = Currency::getCurrencySymbol($CurrencyCode);
        }
        $companyID = User::get_companyID();

        $query = "call prc_getDashboardinvoiceExpense ('". $companyID  . "',  '". $CurrencyID  . "','".$CustomerID."')";
        $InvoiceExpenseResult = DataTableSql::of($query, 'sqlsrv2')->getProcResult(array('InvoiceExpense'));

        $InvoiceExpense = $InvoiceExpenseResult['data']['InvoiceExpense'];
        $TotalOutstanding = 0;
        if(count($InvoiceExpense)) {

            foreach ($InvoiceExpense as $row) {
                if(isset($row->TotalOutstanding)) {
                    $TotalOutstanding += $row->TotalOutstanding;
                }
            }
        }

        return View::make('customer.billingdashboard.invoice_expense_total', compact( 'CurrencyCode', 'CurrencySymbol','TotalOutstanding'));

    }

}
