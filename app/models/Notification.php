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
    const BlockAccount=7;
    const InvoicePaidByCustomer=8;
    const AutoAddIP=9;
    const LowStockReminder=10;

    public static $type = [ Notification::InvoiceCopy=>'Invoice Copy',
        Notification::ReRate=>'CDR Rate Log',
        Notification::WeeklyPaymentTransactionLog=>'Weekly Payment Transaction Log',
        Notification::PendingApprovalPayment=>'Payment Verification',
        Notification::RetentionDiskSpaceEmail=>'Retention Disk Space Email',
        Notification::BlockAccount=>'Block Account',
        Notification::InvoicePaidByCustomer=>'Invoice Paid',
        Notification::AutoAddIP=>'Auto Add IP'
        //Notification::LowStockReminder=>'Low Stock Reminder'
    ];

    public static function getNotificationMail($type,$CompanyID=0){
        if(empty($CompanyID)){
            $CompanyID = User::get_companyID();
        }
        $Notification = Notification::where(['CompanyID'=>$CompanyID,'NotificationType'=>$type,'Status'=>1])->pluck('EmailAddresses');
        return empty($Notification)?'':$Notification;
    }

    public static function sendEmailNotification($type,$data){
        $CompanyID = 0;
        if(!empty($data['CompanyID'])){
            $CompanyID = $data['CompanyID'];
        }
        if($type==Notification::InvoicePaidByCustomer) {
            $body					=	EmailsTemplates::render('body',$data);
            $data['Subject']		=	EmailsTemplates::render("subject",$data);
        }
        $EmailTemplate = $data['EmailTemplate'];
        $data['EmailFrom']		=	$EmailTemplate->EmailFrom;
        $Emails = Notification::getNotificationMail(Notification::InvoicePaidByCustomer,$CompanyID);
        $emailArray 			= 	explode(',', $Emails);
        foreach($emailArray as $singleemail) {
            $singleemail = trim($singleemail);
            if (filter_var($singleemail, FILTER_VALIDATE_EMAIL)) {
                if($EmailTemplate->Status){
                    $data['EmailTo'] 		= 	$singleemail;
                    $data['companyID'] = $CompanyID;
                    $status 				= 	sendMail($body,$data,0);
                    Log::info($status['status']==1?'Email sent to '.$data['EmailTo'].' for Invoice Paid by Customer Notification':'Email sent failed to '.$data['EmailTo']);
                }
            }
        }
    }

}