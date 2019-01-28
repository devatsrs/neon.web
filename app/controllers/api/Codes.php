<?php namespace app\controllers\api;

/**
 * Created by PhpStorm.
 * User: Aamar Nazir
 * Date: 26/01/2019
 * Time: 3:29 AM
 */
class Codes
{
    public static $Code200 = ['200' ,'Success'];
    public static $Code500 = ["400","Bad Request"];
    public static $Code401 =["401","Unauthorized"];
    public static $Code402 =["402","Invalid parameters"];
    public static $Code403 = ["403","Forbidden"];
    public static $Code404 = ["404","Not Found"];
    public static $Code410 = ["410","Record already exists"];
    public static $Code409 = ["409","Conflict"];


//    ErrorMessage: ""


    public static $Code1000 =["402","Invalid Account"];
    public static $Code1001 =["402","Please specified the Service End Date"];
    public static $Code1002 =["402","End Date should be greater then start date"];
    public static $Code1003 =["402","The value of ContractType must be between 1 and 4"];
    public static $Code1004 =["402","The value of AutoRenewal must be between 0 or 1"];
    public static $Code1005 =["402","Please provide the correct account subscription"];
    public static $Code1006 =["402","Please provide the valid dynamic field"];
    public static $Code1007 =["409","More then one service template, please provide the unique product reference"];
    public static $Code1008 =["410","Please provide the unique number for purchased"];
    public static $Code1009 =["409","More then one Inbound Tariff found against the Category"];
    public static $Code1010 =["402","Please provide correct owner id when creating reseller"];
    public static $Code1011 =["402","please setup different domain for your reseller"];
    public static $Code1012 =["402","Please provide the valid currency"];
    public static $Code1013 =["402","Please provide the valid country"];
    public static $Code1014 =["402","Please provide the valid Language"];
    public static $Code1015 =["402","NextInvoiceDate Should be greater than BillingStartDate"];
    public static $Code1016 =["402","Please select the valid billing type"];
    public static $Code1017 =["402","Please select the valid billing class"];
    public static $Code1018 =["402","Account Name contains illegal character"];
    public static $Code1019 =["402","Please provide the valid owner ID"];
    public static $Code1020 =["402","Please enter the valid payment method"];
    public static $Code1021 =["402","Please provide the valid template reference"];
    public static $Code1022 =["402","Provided date is not in correct format"];

    public static $Code1023 =["402","The value of isReseller must be between 0 or 1"];
    public static $Code1024 =["402","The value of isCustomer must be between 0 or 1"];
    public static $Code1025 =["402","The value of IsVendor must be between 0 or 1"];

    public static $Code1026 =["402","The value of BillingCycleID must be between 1 and 8"];
    public static $Code1027 =["402","Please provide the value for BillingCycleValue in case of BillingCycleType 2,5 and 7"];
    public static $Code1028 =["402","Please provide the ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] for BillingCycleValue for BillingCycleID 7"];
    public static $Code1029 =["410","The account name has already been taken"];
    public static $Code1030 =["410","The number has already been taken"];



}