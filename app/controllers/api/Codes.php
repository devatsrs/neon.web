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
    public static $Code500 = ["500","Internal Server Error"];
    public static $Code401 =["401","Unauthorized"];
    public static $Code402 =["402","Invalid parameters"];
    public static $Code403 = ["403","Forbidden"];
    public static $Code404 = ["404","Not Found"];
    public static $Code410 = ["410","Record already exists"];
    public static $Code409 = ["409","Conflict"];
    public static $Code400 = ["400","Bad Request"];


//    ErrorMessage: ""


    public static $Code1000 =["402","Invalid Account"];
    public static $Code1001 =["402","Please specified the Service End Date"];
    public static $Code1002 =["402","End Date should be greater then start date"];
    public static $Code1003 =["402","The value of ContractType must be between 1 and 5"];
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
    public static $Code1021 =["402","Please provide the valid product reference"];
    public static $Code1022 =["402","Provided date is not in correct format"];

    public static $Code1023 =["402","The value of isReseller must be between 0 or 1"];
    public static $Code1024 =["402","The value of isCustomer must be between 0 or 1"];
    public static $Code1025 =["402","The value of IsVendor must be between 0 or 1"];

    public static $Code1026 =["402","The value of BillingCycleID must be between 1 and 8"];
    public static $Code1027 =["402","Please provide the value for BillingCycleValue in case of BillingCycleType 2,5 and 7"];
    public static $Code1028 =["402","Please provide the ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] for BillingCycleValue for BillingCycleID 7"];
    public static $Code1029 =["410","The account name has already been taken"];
    public static $Code1030 =["410","The number has already been taken"];
    public static $Code1031 =["402","Invalid Package ID"];
    public static $Code1032 =["402","Please provide the valid Service"];
    public static $Code1033 =["402","Error while creating the payment account"];
    public static $Code1034 =["402","Please provide either Bank or Card Details"];
    public static $Code1035 =["402","Please provide the valid Reseller ID"];
    public static $Code1036 =["402","Please provide the valid card type"];
    public static $Code1037 =["402","Please provide the valid Account Holder type"];
    public static $Code1038 =["402","Please specified the Number Subscription Start Date"];
    public static $Code1039 =["402","Please specified the Number Subscription End Date"];
    public static $Code1040 =["402","Please specified the Package Subscription Start Date"];
    public static $Code1041 =["402","Please provide the valid CLI number"];
    public static $Code1042 =["402","CLI status against the account is already active"];
    public static $Code1043 =["402","Please provide the status value"];
    public static $Code1044 =["402","The value of Status must be between 0 or 1"];
    public static $Code1045 =["402","More then one inactive CLI found"];
    public static $Code1046 =["402","Country is not set against the product"];
    public static $Code1047 =["402","Please provide the valid serviceID"];
    public static $Code1048 =["402","Please provide the valid Rate Table ID"];
    public static $Code1049 =["402","Please provide the valid Discount Plan ID"];
    public static $Code1050 =["402","Please provide the valid Country ID"];
    public static $Code1051 =["402","Please provide the valid Type"];
    public static $Code1052 =["402","Please provide the valid Prefix"];
    public static $Code1053 =["402","Please provide the valid City"];
    public static $Code1054 =["402","Please provide the valid Tariff"];
    public static $Code1055 =["402","Please specified the Package Subscription End Date"];
    public static $Code1056 =["402","Valid Package exists between the dates"];
}