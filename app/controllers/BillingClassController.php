<?php

class BillingClassController extends \BaseController {


    public function index() {
        $reseller_owners = Reseller::getDropdownIDListAll();
        $CompanyID = User::get_companyID();
        return View::make('billingclass.index', compact('reseller_owners','CompanyID'));
    }
    public function create() {
        /*$emailTemplates = EmailTemplate::getTemplateArray();
        $SendInvoiceSetting = BillingClass::$SendInvoiceSetting;
        $timezones = TimeZone::getTimeZoneDropdownList();
        $billing_type = AccountApproval::$billing_type;
        $taxrates = TaxRate::getTaxRateDropdownIDList();
        $InvoiceTemplates = InvoiceTemplate::getInvoiceTemplateList();
        if(isset($taxrates[""])){unset($taxrates[""]);}
        $privacy = EmailTemplate::$privacy;
        $type = EmailTemplate::$Type;*/
        $CompanyID = User::get_companyID();
        $BillingClassList = BillingClass::getBillingClassListByCompanyID($CompanyID);
        $reseller_owners = Reseller::getDropdownIDListAll();
        return View::make('billingclass.create', compact('BillingClassList','reseller_owners','CompanyID'));
        //return View::make('billingclass.create', compact('emailTemplates','taxrates','billing_type','timezones','SendInvoiceSetting','InvoiceTemplates','privacy','type'));
    }
    public function edit($id) {

        $getdata['BillingClassID'] = $id;
        $response =  NeonAPI::request('billing_class/get/'.$id,$getdata,false,false,false);
        if(!empty($response) && $response->status == 'success' ){
            /*$emailTemplates = EmailTemplate::getTemplateArray();
            $SendInvoiceSetting = BillingClass::$SendInvoiceSetting;
            $timezones = TimeZone::getTimeZoneDropdownList();
            $billing_type = AccountApproval::$billing_type;
            $taxrates = TaxRate::getTaxRateDropdownIDList();
            $InvoiceTemplates = InvoiceTemplate::getInvoiceTemplateList();
            if(isset($taxrates[""])){unset($taxrates[""]);}
            $BillingClass = $response->data;
            $PaymentReminders = json_decode($response->data->PaymentReminderSettings);
            $LowBalanceReminder = json_decode($response->data->LowBalanceReminderSettings);
            $InvoiceReminders = json_decode($response->data->InvoiceReminderSettings);

            //$accounts = BillingClass::getAccounts($id);
            $privacy = EmailTemplate::$privacy;
            $type = EmailTemplate::$Type;*/
            $BillingClassList = BillingClass::getBillingClassListByCompanyID(User::get_companyID());
            //$BillingClassList = array();
            //print_r($response->data);
            $BillingClass = $response->data;
            $InvoiceReminders = json_decode($response->data->InvoiceReminderSettings);
            $LowBalanceReminder = json_decode($response->data->LowBalanceReminderSettings);
            $BalanceWarning = json_decode($response->data->BalanceWarningSettings);
            $PaymentReminders = json_decode($response->data->PaymentReminderSettings);
            $ZeroBalanceWarning = json_decode($response->data->ZeroBalanceWarningSettings);
            $CompanyID = $BillingClass->CompanyID;
            $IsGlobal = $BillingClass->IsGlobal;
            $PartnerID = '';
            $Count = Reseller::IsResellerByCompanyID($CompanyID);
            if($Count>0){
                $PartnerID = $CompanyID;
            }else{
                if($IsGlobal==1){
                    $PartnerID = -1;
                }
            }

            /*
            $CompanyID = User::get_companyID();
            if(!empty($BillingClass->ResellerID)){
                $CompanyID = $BillingClass->CompanyID;
            }*/
            $reseller_owners = Reseller::getDropdownIDListAll();
            return View::make('billingclass.edit', compact('BillingClassList','BillingClass','InvoiceReminders','PaymentReminders','LowBalanceReminder','BalanceWarning','accounts','reseller_owners','CompanyID','ZeroBalanceWarning','PartnerID'));
            //return View::make('billingclass.edit', compact('emailTemplates','taxrates','billing_type','timezones','SendInvoiceSetting','BillingClass','PaymentReminders','LowBalanceReminder','InvoiceTemplates','BillingClassList','InvoiceReminders','accounts','privacy','type'));
        }else{
            return view_response_api($response);
        }
    }
    public function getInvoicetemplate() {
        $response = array();

        $data       = Input::all();
        $trunks     = array();

        if(!empty($data['id'])) {
            $id = $data['id'];
            $ChildCompanyID = Reseller::where('ResellerID',$id)->pluck('ChildCompanyID');
            if($data['type']=='emailtemp'){
                $TemplateData     = EmailTemplate::getEmailTemplateDropdownIDList($ChildCompanyID);
            }else{
                $TemplateData     = InvoiceTemplate::getInvoiceTemplateDropdownIDList($ChildCompanyID);
            }
        }else{
            $getResellerCompany=User::get_companyID();
            if($data['type']=='emailtemp'){
                $TemplateData     = EmailTemplate::getEmailTemplateDropdownIDList($getResellerCompany);
            }else{
                $TemplateData     = InvoiceTemplate::getInvoiceTemplateDropdownIDList($getResellerCompany);
            }
        }

        $response['status']                     = 'success';
        $response['invoicetemplate']            = $TemplateData;
        return json_encode($response);
    }
    public function ajax_datagrid(){
        $getdata = Input::all();
        $CompanyID = $getdata['CompanyID'];
        $getdata['is_reseller']=0;
        $getdata['is_IsGlobal'] = 0;
        $Count = Reseller::IsResellerByCompanyID($CompanyID);
        if($Count>0){
            $getdata['is_reseller']=1;
        }else{
            $getdata['CompanyID'] = 0;
            if(isset($getdata['ResellerOwner']) && $getdata['ResellerOwner'] > 0){
                $getdata['CompanyID'] = $getdata['ResellerOwner'];
            }elseif($getdata['ResellerOwner']=='-1'){
                $getdata['is_IsGlobal'] = 1;
            }
        }
        /*$getdata['is_reseller']=0;
        if(is_reseller()){
            $getdata['is_reseller']=1;
        }*/

        $response =  NeonAPI::request('billing_class/datagrid',$getdata,false,false,false);
        if(isset($getdata['Export']) && $getdata['Export'] == 1 && !empty($response) && $response->status == 'success') {
            $excel_data = $response->data;
            $excel_data = json_decode(json_encode($excel_data), true);
            Excel::create('Billing Class', function ($excel) use ($excel_data) {
                $excel->sheet('Billing Class', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }
        return json_response_api($response,true,true,true);
    }

    public function store($isModal){
        $postdata = Input::all();
        $postdata['IsGlobal'] =0;
        if(!empty($postdata['ResellerOwner'])){
            if($postdata['ResellerOwner']=='-1'){
                $postdata['IsGlobal'] =1;
            }else{
                $postdata['CompanyID'] = $postdata['ResellerOwner'];
            }
        }
        unset($postdata['ResellerOwner']);
        $postdata['ParentBillingClassID']=0;
        /*
        $postdata['CompanyID'] = User::get_companyID();
        if (isset($postdata['ResellerOwner']) && !empty($postdata['ResellerOwner'])) {
            $postdata['CompanyID'] = Reseller::where('ResellerID',$postdata['ResellerOwner'])->pluck('ChildCompanyID');
        }*/

        $response =  NeonAPI::request('billing_class/store',$postdata,true,false,false);
        if(!empty($response) && $response->status == 'success'){
            if($isModal==1){
                return json_response_api($response);
            }

            $response->redirect =  URL::to('/billing_class/edit/' . $response->data->BillingClassID);
        }
        return json_response_api($response);
    }

    public function delete($id){
        $response =  NeonAPI::request('billing_class/delete/'.$id,array(),'delete',false,false);
        return json_response_api($response);
    }

    public function update($id){
        $postdata = Input::all();
        unset($postdata['ResellerOwner']);
        $response =  NeonAPI::request('billing_class/update/'.$id,$postdata,'put',false,false);
        return json_response_api($response);
    }
    public function billingclass_clone($id){
        $postdata = Input::all();
        $postdata['IsGlobal'] =0;
        $CompanyID = User::get_companyID();
        $postdata['CompanyID'] = $CompanyID;
        unset($postdata['ResellerOwner']);
        $postdata['ParentBillingClassID']=$id;

        $response =  NeonAPI::request('billing_class/store',$postdata,true,false,false);
        if(!empty($response) && $response->status == 'success'){
            AccountBilling::where('BillingClassID',$id)->update(['BillingClassID'=>$response->data->BillingClassID]);
            $response->redirect =  URL::to('/billing_class/edit/' . $response->data->BillingClassID);
        }
        return json_response_api($response);
    }
    public function getInfo($id) {
        $getdata['BillingClassID'] = $id;
        $response =  NeonAPI::request('billing_class/get/'.$id,$getdata,false,true,false);
        return Response::json($response);
    }

}