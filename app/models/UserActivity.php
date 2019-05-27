<?php

class UserActivity extends \Eloquent {

    protected $guarded = array();

    protected $table = 'tblUserActivity';

    protected  $primaryKey = "UserActivityID";


    public static $rules = array(
        'CompanyId' =>  'required'
    );
    
    public static  function UserActivitySaved($data,$action,$Who="",$options=""){
        $data_array=array();
        $actionData = array('View','Search','Export','Send','Bulk Send','Upload','Recall','Bulk Delete','Delete','Bulk Edit');
        if($action=='Login'){
            $created_by="";
            $companyID="";
        }else{
            $created_by=User::get_user_full_name();
            $companyID=User::get_companyID();
        }
        
        $TypeName='';
        if($Who=='Reseller' && $action!='View'){
           // $TypeName    = $data['AccountID'];
        } 
        if(($Who=='Tickets') && $action!='View'){
             $TypeName  = @$data['Ticket']['default_subject'];
        }else if(($Who=='SendMail') && $action!='View'){
             $TypeName = @$data['Subject'];
        }else if(($Who=='Company') && $action!='View'){
            $TypeName  = @$data['CompanyName'];
        }else if(($Who=='Notification') && $action!='View'){
            $TypeName  = @$data['NotificationType'];
        }else if(($Who=='Alert') && $action!='View'){
            $TypeName  = @$data['Name'];
        }else if(($Who=='Accountapproval') && $action!='View'){
            $TypeName  = @$data['Key'];
        }else if(($Who=='Retention') && $action!='View' && $action!='Export'){
            $TypeName  = @$data['TableData']['CDR'];
        }else if(($Who=='Email_Template') && $action!='View' && $action!='Export'){
            $TypeName  = @$data['TemplateName'];
        }else if(($Who=='Noticeboard') && $action!='View' && $action!='Export'){
            $TypeName  = @$data['Title'];
        }else if(($Who=='Translation') && $action!='View' && $action!='Export'){
            $TypeName  = @$data['system_name'];
        }else if(($Who=='Dynamiclink') && $action!='View' && $action!='Export'){
            $TypeName  = @$data['Title'];
        }else if(($Who=='Trunks') && $action!='View' && $action!='Export'){
            $TypeName  = @$data['Trunk'];
        }else if(($Who=='Codedecks') && $action!='View' && $action!='Export'){
            $TypeName  = @$data['CodedeckName'];
        }else if(($Who=='DialStrings') && $action!='View' && $action!='Export'){
            $TypeName  = @$data['Name'];
        }else if(($Who=='DialStringsCode') && $action!='View' && $action!='Export' && $action!='Search'){
            $TypeName  = @$data['DialString'];
        }else if(($Who=='Currency') && $action!='View' && $action!='Export' && $action!='Search'){
            $TypeName  = @$data['Code'];
        }else if(($Who=='Currency Conversion') && $action!='View' && $action!='Export' && $action!='Search'){
            $TypeName  = 'Currency Conversion';
        }else if(($Who=='Timezones') && $action!='View' && $action!='Export' && $action!='Search'){
            $TypeName  = @$data['Title'];
        }else if(($Who=='VOS Active Calls') && $action!='View' && $action!='Export' && $action!='Export'&& $action!='Search'){
            $TypeName  = @$data['Title'];
        }else if(($Who=='Task') && $action!='View' && $action!='Export' && $action!='Export'&& $action!='Search'){
            $TypeName  = @$data['Subject'];
        }else if(($Who=='Opportunity') && $action!='View' && $action!='Export' && $action!='Export'&& $action!='Search' && $action!='Sort'){
            $TypeName  = @$data['OpportunityName'];
        }else if(($Who=='Opportunity Boards') && $action!='View' && $action!='Export' && $action!='Export'&& $action!='Search'){
            $TypeName  = @$data['BoardName'];
        }else if(($Who=='Opportunity Board Column') && $action!='View' && $action!='Export' && $action!='Export'&& $action!='Search'){
            $TypeName  = @$data['BoardColumnName'];
        }else if(($Who=='Estimates') && $action!='View' && $action!='Export' && $action!='Export'&& $action!='Search'){
            unset($data['Terms']);
            unset($data['FooterTerm']);
            $TypeName  = @$data['Estimatenumber'];
        }else if(($Who=='Creditnotes') && $action!='View' && $action!='Export' && $action!='Search' && $action!='Send' && $action!='Bulk Send'){
            unset($data['Terms']);
            unset($data['FooterTerm']);
            $TypeName  = @$data['CreditNotesNumber'];
        }else if(($Who=='Payments') && !in_array($action, $actionData) ){
            $TypeName  = @$data['PaymentMethod '];
        }else if(($Who=='Disputes') && !in_array($action, $actionData) ){
            $TypeName  = @$data['AccountID'];
        }else if(($Who=='Services') && !in_array($action, $actionData) ){
            $TypeName  = @$data['ServiceName'];
        }else if(($Who=='Billing Subscription') && !in_array($action, $actionData) ){
            $TypeName  = @$data['Name'];
        }else if(($Who=='Discount Plan') && !in_array($action, $actionData) ){
            $TypeName  = @$data['Name'];
        }else if(($Who=='Products') && !in_array($action, $actionData) ){
            $TypeName  = @$data['Name'];
        }else if(($Who=='Item Types') && !in_array($action, $actionData) ){
            $TypeName  = @$data['Title'];
        }else if(($Who=='Dynamic Fields') && !in_array($action, $actionData) ){
            $TypeName  = @$data['FieldName'];
        }else if(($Who=='Taxrate') && !in_array($action, $actionData) ){
            $TypeName  = @$data['Title'];
        }else if(($Who=='Billing Class') && !in_array($action, $actionData) ){
            $TypeName  = @$data['Name'];
        }else if(($Who=='Invoice') && !in_array($action, $actionData) ){
            unset($data['Terms']);
            unset($data['FooterTerm']);
            $TypeName  = @$data['InvoiceNumber'];
        }     
        
        if(!empty($options)){
            $TypeName=$options;
        }
        
        $dataActionValue            = array_filter($data, function($value) { return $value !== ''; });
        $data_array['TypeName']     = $TypeName;
        $data_array['CompanyId']    = $companyID;
        $data_array['created_by']   =  $created_by;
        $data_array["created_at"]   = date('Y-m-d H:i:s');
        $ActionValueJSON            = json_encode($dataActionValue);
        $data_array["ActionValue"]  = $ActionValueJSON;
        $data_array["Action"]       = $action;
        $data_array["Type"]          = $Who;
        try{
            $UserActilead               = UserActivity::create($data_array);
        }catch (\Exception $e) {
            echo  $e->getMessage();
            die();
        }
    }

}