<?php

class Notification extends \Eloquent {

    protected $guarded = array();

    protected $table = 'tblNotification';
    protected  $primaryKey = "NotificationID";

    const InvoiceGeneration = 1;
    const RateGeneration = 2;
    const ReRate=3;
    const WeeklyPaymentTransactionLog=4;
    const LowBalanceReminder=5;

    public static $type = [ Notification::InvoiceGeneration=>'Invoice Generation',
                            Notification::RateGeneration=>'Rate Generation',
                            Notification::ReRate=>'Re Rate',
                            Notification::WeeklyPaymentTransactionLog=>'Weekly Payment Transaction Log',
                            Notification::LowBalanceReminder=>'Low Balance Reminder'];

}