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
    public static $Code403 = ["403","Not found"];
    public static $Code410 = ["410","Record already exists"];
    public static $Code409 = ["409","Conflict"];


//    ErrorMessage: ""



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



}