<?php
class Alert extends \Eloquent {
    protected $guarded = array("AlertID");
    protected $fillable = [];
    protected $table = "tblAlert";
    protected $primaryKey = "AlertID";

    const GROUP_QOS = 'qos';
    const GROUP_CALL = 'call';

    public static $qos_alert_type = array(''=>'Select','ACD'=>'ACD','ASR'=>'ASR');
    public static $call_monitor_alert_type = array(''=>'Select','block_destination'=>'Blacklisted Destination','call_duration'=>'Longest Call','call_cost'=>'Expensive calls','call_after_office'=>'Call After Business Hour');
    public static $call_monitor_customer_alert_type = array(''=>'Select','call_duration'=>'Longest Call','call_cost'=>'Expensive calls','call_after_office'=>'Call After Business Hour');


}