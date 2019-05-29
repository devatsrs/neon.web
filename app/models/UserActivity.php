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

        if($action=='Login'){
            $created_by="";
            $companyID="";
            unset($data['password']);
        }else{
            $created_by=User::get_user_full_name();
            $companyID=User::get_companyID();
        }
        
        $TypeName='';
        if(($Who=='Tickets') && $action!='View'){
             $TypeName  = @$data['Ticket']['default_subject'];
        }else if(($Who=='SendMail') && $action!='View'){
             $TypeName = @$data['Subject'];
        }else if(($Who=='Estimates') && $action=='Add' && $action =='Edit'){
            unset($data['Terms']);
            unset($data['FooterTerm']);
            $TypeName  = @$data['Estimatenumber'];
        }else if(($Who=='Creditnotes') && $action=='Add' && $action =='Edit'){
            unset($data['Terms']);
            unset($data['FooterTerm']);
            $TypeName  = @$data['CreditNotesNumber'];
        }else if(($Who=='Invoice') && $action=='Add' && $action =='Edit'){
            unset($data['Terms']);
            unset($data['FooterTerm']);
            $TypeName  = @$data['InvoiceNumber'];
        }     
        
        if(!empty($options)){
            $TypeName=$options;
        }

        if($action=='View' && isset($data['sEcho']) && $data['sEcho']=='2'){
            $action='Search';
        }
        
        $dataActionValue            = array_filter($data, function($value) { return $value !== ''; });
        $data_array['TypeName']     = $TypeName;
        $data_array['CompanyId']    = $companyID;
        $data_array['created_by']   =  $created_by;
        //$data_array["created_at"]   = date('Y-m-d H:i:s');
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