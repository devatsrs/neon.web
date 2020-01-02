<?php

class AccountEmailLog extends \Eloquent {
    protected $guarded = array("AccountEmailLogID");
    protected $fillable = [];
    protected $table = "AccountEmailLog";
    protected $primaryKey = "AccountEmailLogID";
	
    const InvoicePaymentReminder=1;
    const LowBalanceReminder=2;
    const QosACDAlert =3;
    const QosASRAlert =4;
    const CallDurationAlert = 5;
    const CallCostAlert = 6;
    const CallOfficeAlert = 7;
    const CallBlackListAlert = 8;
    const VendorBalanceReport = 9;
	const TicketEmail = 10;
    const ReportEmail = 11;
    const BalanceWarning = 12;
    const AccountBalanceEmailReminder = 13;
    const ContractExpire = 14;
    const ContractManage = 15;
    const LowBalanceZeroReminder = 16;


    public static $type = [ AccountEmailLog::InvoicePaymentReminder=>'Invoice Payment Reminder',
        AccountEmailLog::LowBalanceReminder=>'Low Balance Reminder',
        AccountEmailLog::QosACDAlert=>'Qos ACD Alert',
        AccountEmailLog::QosASRAlert=>'Qos ASR Alert',
        AccountEmailLog::CallDurationAlert=>'Call Duration Alert',
        AccountEmailLog::CallCostAlert=>'Call Cost Alert',
        AccountEmailLog::CallOfficeAlert=>'Call Office Alert',
        AccountEmailLog::CallBlackListAlert=>'Call Black List Alert',
        AccountEmailLog::VendorBalanceReport=>'Vendor Balance Report',
        AccountEmailLog::TicketEmail=>'Ticket Email',
        AccountEmailLog::ReportEmail=>'Report Email',
        AccountEmailLog::BalanceWarning=>'Balance Warning',
        AccountEmailLog::AccountBalanceEmailReminder=>'Account Balance Email Reminder',
        AccountEmailLog::ContractExpire=>'Contract Expire',
        AccountEmailLog::ContractManage=>'Contract Manage'

    ];
    public static $type2 = [
        AccountEmailLog::LowBalanceReminder=>'Low Balance',
        AccountEmailLog::InvoicePaymentReminder=>'Payment Reminders',
        AccountEmailLog::LowBalanceZeroReminder=>'Low Balance Zero Reminder',
        //AccountEmailLog::InvoicePaymentReminder=>'Payment Reminders',

        
    ];

}