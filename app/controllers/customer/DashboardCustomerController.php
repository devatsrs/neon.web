<?php
use Jenssegers\Agent\Agent;
class DashboardCustomerController extends BaseController {

    
    public function __construct() {

    }
    public function home() {
        $CustomerID = Customer::get_accountID();
        $account = Account::find($CustomerID);
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        return View::make('customer.index',compact('account','original_startdate','original_enddate'));
    }
    public function invoice_expense_chart(){
        $data = Input::all();
        $CurrencyID = "";
        $CustomerID = Customer::get_accountID();
        $CurrencySymbol = '';
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
            $CurrencySymbol = Currency::getCurrencySymbol($CurrencyID);
        }
        $companyID = User::get_companyID();
        $query = "call prc_getDashboardinvoiceExpense ('". $companyID  . "',  '". $CurrencyID  . "','".$CustomerID."')";
        $InvoiceExpenseResult = DataTableSql::of($query, 'sqlsrv2')->getProcResult(array('InvoiceExpense'));
        $InvoiceExpense = $InvoiceExpenseResult['data']['InvoiceExpense'];
        return View::make('customer.billingdashboard.invoice_expense_chart', compact('InvoiceExpense','CurrencySymbol'));

    }

    public function invoice_expense_total(){

        $data = Input::all();
        $CustomerID = Customer::get_accountID();
        $CurrencyID = "";
        $CurrencySymbol = $CurrencyCode = "";
        if(isset($data["CurrencyID"]) && !empty($data["CurrencyID"])){
            $CurrencyID = $data["CurrencyID"];
            $CurrencyCode = Currency::getCurrency($CurrencyID);
            $CurrencySymbol = Currency::getCurrencySymbol($CurrencyID);
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
    public function monitor_dashboard(){

        $companyID = User::get_companyID();
        $DefaultCurrencyID = Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $original_startdate = date('Y-m-d', strtotime('-1 week'));
        $original_enddate = date('Y-m-d');
        $isAdmin = 1;
        $agent = new Agent();
        $isDesktop = $agent->isDesktop();
        $User = User::find(Customer::get_currentUser()->Owner);
        $AccountManager = $User->FirstName.' '.$User->LastName;
        $AccountManagerEmail = $User->EmailAddress;
        return View::make('customer.dashboard',compact('DefaultCurrencyID','original_startdate','original_enddate','isAdmin','newAccountCount','isDesktop','AccountManager','AccountManagerEmail'));

    }

}
