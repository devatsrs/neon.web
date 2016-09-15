<?php

class Notification extends \Eloquent {

    protected $guarded = array();

    protected $table = 'tblNotification';
    protected  $primaryKey = "NotificationID";

    const InvoiceCopy = 1;
    const ReRate=2;
    const WeeklyPaymentTransactionLog=3;
    const LowBalanceReminder=4;
    const PendingApprovalPayment=5;
    const RetentionDiskSpaceEmail=6;
    const PaymentReminder=7;

    public static $type = [ Notification::InvoiceCopy=>'Invoice Copy',
        Notification::ReRate=>'CDR Rate Log',
        Notification::WeeklyPaymentTransactionLog=>'Weekly Payment Transaction Log',
        Notification::LowBalanceReminder=>'Low Balance Reminder',
        Notification::PendingApprovalPayment=>'Pending Approval Payment',
        Notification::RetentionDiskSpaceEmail=>'Retention Disk Space Email',
        Notification::PaymentReminder=>'Payment Reminder'
    ];

    public static $has_settings = [
        self::PaymentReminder
    ];

    const INVOICE_BASE=1;
    const PAYMENT_BASE=2;

    public static $paymentreminder_type = [
        Notification::INVOICE_BASE=>'Invoice',
        Notification::PAYMENT_BASE=>'Payment'

    ];

    public static function getNotificationMail($type){
        $CompanyID = User::get_companyID();
        $Notification = Notification::where(['CompanyID'=>$CompanyID,'NotificationType'=>$type,'Status'=>1])->pluck('EmailAddresses');
        return empty($Notification)?'':$Notification;
    }

    public static function validateNotification($Notification){
        $data = Input::all();
        $settings = $data['Settings'];
        $valid = array('valid'=>0,'message'=>'Some thing wrong with cron model validation','data'=>'');
        $message = '';
        if($Notification->NotificationType == self::PaymentReminder){
            $message = self::validatePaymentReminder($settings);
        }
        if(!empty($message)){
            $valid['message'] = $message;
        }else{
            $valid['valid'] = 1;
        }
        return $valid;
    }

    public static function validatePaymentReminder($settings){
        $verify_comma_separated = '/^\d+(?:,\d+)*$/';
        $message = '';
        if (!empty($settings['NotifyDueInvoice']) && !preg_match($verify_comma_separated, $settings['NotifyDueInvoice']) ) {
            $message = Response::json(array("status" => "failed", "message" => "Please enter correct comma separated value in Notify Due Invoice"));
        }else if (!empty($settings['NotifyUnpaidInvoice']) && !preg_match($verify_comma_separated, $settings['NotifyUnpaidInvoice']) ){
            $message = Response::json(array("status" => "failed", "message" => "Please enter correct comma separated value in Notify Unpaid Invoice"));
        }else if (!empty($settings['SuspendWarning']) && !preg_match($verify_comma_separated, $settings['SuspendWarning']) ){
            $message = Response::json(array("status" => "failed", "message" => "Please enter correct comma separated value in Account Suspend Warning"));
        }else if (!empty($settings['NotifyAccountClose']) && !preg_match($verify_comma_separated, $settings['NotifyAccountClose']) ){
            $message = Response::json(array("status" => "failed", "message" => "Please enter correct comma separated value in Account Close"));
        }else if(!empty($settings['NotifyDueInvoice']) && empty($settings['DueInvoice'])){
            $message = Response::json(array("status" => "failed", "message" => "Please select Notify Due Invoice Template"));
        }else if(!empty($settings['NotifyUnpaidInvoice']) && empty($settings['UnpaidInvoice'])){
            $message = Response::json(array("status" => "failed", "message" => "Please select Notify Unpaid Invoice Template"));
        }else if(!empty($settings['SuspendWarning']) && empty($settings['AccountCloseWarning'])){
            $message = Response::json(array("status" => "failed", "message" => "Please select Account Suspend Warning Template"));
        }else if(!empty($settings['NotifyAccountClose']) && empty($settings['AccountCloseSoon'])){
            $message = Response::json(array("status" => "failed", "message" => "Please select Account Close Template"));
        }
        return $message;
    }

}