<?php

class QuickBookController extends \BaseController {

    
    public function __construct() {
    
    }
    /**
     * Display a listing of the resource.
     * GET /accounts
     *
     * @return Response
     */
    public function index() {

        //QuickBook::disconnect();
        $CompanyID = User::get_companyID();

        $QuickBook = new BillingAPI($CompanyID);
        $quickbooks_CompanyInfo = $QuickBook->test_connection();

        if(!empty($quickbooks_CompanyInfo)){
            return View::make('quickbook.index', compact('quickbooks_CompanyInfo'));
        }else{
            return View::make('quickbook.connection', compact('quickbooks_CompanyInfo'));
        }

    }

    public function disconnect(){
        $CompanyID = User::get_companyID();
        $QuickBook = new BillingAPI($CompanyID);
        $QuickBook->quickbook_disconnect();
        return View::make('quickbook.disconnection', compact(''));
    }

    public function addCustomer(){
        //QuickBook::addCustomer();
    }

    public function quickbookoauth(){
        $CompanyID = User::get_companyID();
        $QuickBook = new BillingAPI($CompanyID);
        $QuickBook->quickbook_connect();
    }

    public function success(){
        return View::make('quickbook.success', compact(''));
    }

    public function getAllCustomer(){
        $CompanyID = User::get_companyID();
        $QuickBook = new BillingAPI($CompanyID);
        $customers = $QuickBook->getAllCustomer();
        echo "<pre>";
        print_r($customers);exit;
    }

    public function getAllItems(){
        $CompanyID = User::get_companyID();
        $QuickBook = new BillingAPI($CompanyID);
        $items = $QuickBook->getAllItems();
        echo "<pre>";
        print_r($items);exit;
    }

    public function createItem(){
        $CompanyID = User::get_companyID();
        $QuickBook = new BillingAPI($CompanyID);
        $response = $QuickBook->createItem();
        print_r($response);
        exit;
    }

    public function createJournal(){
        $CompanyID = User::get_companyID();
        $QuickBook = new BillingAPI($CompanyID);
        $response = $QuickBook->createJournal();
        print_r($response);
        exit;
    }
}
