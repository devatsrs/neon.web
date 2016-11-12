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

        $QuickBook = new BillingAPI();
        $quickbooks_CompanyInfo = $QuickBook->test_connection();

        if(!empty($quickbooks_CompanyInfo)){
            return View::make('quickbook.index', compact('quickbooks_CompanyInfo'));
        }else{
            return View::make('quickbook.connection', compact('quickbooks_CompanyInfo'));
        }

    }

    public function disconnect(){
        $QuickBook = new BillingAPI();
        $QuickBook->quickbook_disconnect();
        return View::make('quickbook.disconnection', compact(''));
    }

    public function addCustomer(){
        //QuickBook::addCustomer();
    }

    public function quickbookoauth(){
        $QuickBook = new BillingAPI();
        $QuickBook->quickbook_connect();
    }

    public function success(){
        return View::make('quickbook.success', compact(''));
    }

    public function getAllCustomer(){
        $QuickBook = new BillingAPI();
        $customers = $QuickBook->getAllCustomer();
        echo "<pre>";
        print_r($customers);exit;
    }

    public function getAllItems(){
        $QuickBook = new BillingAPI();
        $items = $QuickBook->getAllItems();
        echo "<pre>";
        print_r($items);exit;
    }

    public function createItem(){
        $QuickBook = new BillingAPI();
        $response = $QuickBook->createItem();
        print_r($response);
        exit;
    }

    public function createJournal(){
        $QuickBook = new BillingAPI();
        $response = $QuickBook->createJournal();
        print_r($response);
        exit;
    }
}
