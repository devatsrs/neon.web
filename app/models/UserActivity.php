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
        
        $created_by=User::get_user_full_name();
        $companyID=User::get_companyID();
        $TypeName='';
        if($Who=='Reseller' && $action!='View'){
            $TypeName    = $data['AccountID'];
        } 
        if(($Who=='Account' || $Who=='Contact' || $Who=='Lead') && $action!='View'){
             $TypeName  = @$data['FirstName'].' '.$data['LastName'];
        }else if(($Who=='Tickets') && $action!='View'){
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
        }   
        
        
        
         
        
       
        $data_array['TypeName']    = $TypeName;
        $data_array['CompanyId']    = $companyID;
        $data_array['created_by']   =  $created_by;
        $data_array["created_at"]   = date('Y-m-d H:i:s');
        $ActionValueJSON            = json_encode($data);
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