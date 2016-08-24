<?php

class Notification extends \Eloquent {

    protected $guarded = array();

    protected $table = 'tblNotification';
    protected  $primaryKey = "NotificationID";

    const InvoiceGeneration = 1;
    const ReRate=2;
    const WeeklyPaymentTransactionLog=3;
    const LowBalanceReminder=4;
    const PendingApprovalPayment=5;

    public static $type = [ Notification::InvoiceGeneration=>'Invoice Generation',
        Notification::ReRate=>'CDR Rate Log',
        Notification::WeeklyPaymentTransactionLog=>'Weekly Payment Transaction Log',
        Notification::LowBalanceReminder=>'Low Balance Reminder',
        Notification::PendingApprovalPayment=>'Pending Approval Payment'];

    public static function getNotificationMail($type){
        $CompanyID = User::get_companyID();
        $Notification = Notification::where(['CompanyID'=>$CompanyID,'NotificationType'=>$type])->pluck('EmailAddresses');
        return empty($Notification)?'':$Notification;
    }

}