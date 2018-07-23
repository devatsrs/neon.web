<?php
class Alert extends \Eloquent {
    protected $guarded = array("AlertID");
    protected $table = "tblAlert";
    protected $primaryKey = "AlertID";

    const GROUP_QOS = 'qos';
    const GROUP_CALL = 'call';

    public static $qos_alert_type = array(''=>'Select','ACD'=>'ACD','ASR'=>'ASR');
    public static $call_monitor_alert_type = array(''=>'Select','block_destination'=>'Blacklisted Destination','call_duration'=>'Longest Call','call_cost'=>'Expensive Calls','call_after_office'=>'Call After Business Hour','vendor_balance_report'=>'Vendor Stats','account_balance'=>'Account Balance','Low_stock_reminder'=>'Low Stock Reminder');
    public static $call_monitor_customer_alert_type = array(''=>'Select','call_duration'=>'Longest Call','call_cost'=>'Expensive Calls','call_after_office'=>'Call After Business Hour','account_balance'=>'Account Balance');
    public static $call_blacklist_alert_type = array(''=>'Select','block_destination'=>'Blacklisted Destination');

    public static function multiLang_init(){
        Alert::$call_monitor_customer_alert_type = array(''=>cus_lang("DROPDOWN_OPTION_SELECT"),'call_duration'=>cus_lang("PAGE_NOTIFICATIONS_FIELD_CALL_MONITOR_CUSTOMER_ALERT_TYPE_DDL_LONGEST_CALL"),'call_cost'=>cus_lang("PAGE_NOTIFICATIONS_FIELD_CALL_MONITOR_CUSTOMER_ALERT_TYPE_DDL_EXPENSIVE_CALLS"),'call_after_office'=>cus_lang("PAGE_NOTIFICATIONS_FIELD_CALL_MONITOR_CUSTOMER_ALERT_TYPE_DDL_CALL_AFTER_BUSINESS_HOUR"),'account_balance'=>'Account Balance');
    }

    public static $rules = array(
        'AlertType'=>'required',
        'Name'=>'required',
    );

    protected $fillable = array(
        'CompanyID','Name','AlertType','Status','LowValue','HighValue','AlertGroup',
        'Settings','created_at','updated_at','UpdatedBy','CreatedBy','CreatedByCustomer'
    );

    public static function getDropdownIDList($CompanyID){
        $DropdownIDList = Alert::where(array("CompanyID"=>$CompanyID))->lists('Name', 'AlertID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }

}