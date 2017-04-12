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

    const InvoicePaidNotificationTemplate = 'InvoicePaidNotification';

    public static $type = [ Notification::InvoiceCopy=>'Invoice Copy',
        Notification::ReRate=>'CDR Rate Log',
        Notification::WeeklyPaymentTransactionLog=>'Weekly Payment Transaction Log',
        Notification::PendingApprovalPayment=>'Pending Approval Payment',
        Notification::RetentionDiskSpaceEmail=>'Retention Disk Space Email',
        Notification::BlockAccount=>'Block Account',
        Notification::InvoicePaidByCustomer=>'Invoice Paid By Customer'
    ];

    public static function getNotificationMail($type){
        $CompanyID = User::get_companyID();
        $Notification = Notification::where(['CompanyID'=>$CompanyID,'NotificationType'=>$type,'Status'=>1])->pluck('EmailAddresses');
        return empty($Notification)?'':$Notification;
    }

    public static function sendEmailNotification($type,$data){
        if($type==Notification::InvoicePaidByCustomer) {
            $body					=	EmailsTemplates::SendNotification('body',$data);
            $data['Subject']		=	EmailsTemplates::SendNotification("subject",$data);
        }
        $EmailTemplate = $data['EmailTemplate'];
        $data['EmailFrom']		=	$EmailTemplate->EmailFrom;
        $Emails = Notification::getNotificationMail(Notification::InvoicePaidByCustomer);
        $emailArray 			= 	explode(',', $Emails);
        foreach($emailArray as $singleemail) {
            $singleemail = trim($singleemail);
            if (filter_var($singleemail, FILTER_VALIDATE_EMAIL)) {
                if($EmailTemplate->Status){
                    $data['EmailTo'] 		= 	$singleemail;
                    $status 				= 	sendMail($body,$data,0);
                    Log::info($status['status']==1?'Email sent to '.$data['EmailTo'].' for Invoice Paid by Customer Notification':'Email sent failed to '.$data['EmailTo']);
                }
            }
        }
    }

}