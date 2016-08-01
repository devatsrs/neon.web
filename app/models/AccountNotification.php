<?php

class AccountNotification extends \Eloquent {

    protected $guarded = array();

    protected $table = 'tblAccountNotification';
    protected  $primaryKey = "AccountNotificationID";

    const InvoiceGeneration = 1;
    const RateGeneration = 2;
    const ReRate=3;
    const WeeklyPaymentTransactionLog=4;
    const LowBalanceReminder=5;

    public static $type = [ AccountNotification::InvoiceGeneration=>'Invoice Generation',
                            AccountNotification::RateGeneration=>'Rate Generation',
                            AccountNotification::ReRate=>'Re Rate',
                            AccountNotification::WeeklyPaymentTransactionLog=>'Weekly Payment Transaction Log',
                            AccountNotification::LowBalanceReminder=>'Low Balance Reminder'];

}