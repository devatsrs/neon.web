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
    public static $Code500 =["500","Unknown Exception while processing the API"];
    public static $Code401 =["401","Larvel Validation"];
    public static $Code1000 =["1000","Please provide the correct Account Reference"];
    public static $Code1001 =["1001","Please specified the Service End Date"];
    public static $Code1002 =["1002","End Date should be greater then start date"];
    public static $Code1003 =["1003","The value of ContractType must be between 1 and 4"];
    public static $Code1004 =["1004","The value of AutoRenewal must be between 0 or 1"];
    public static $Code1005 =["1005","Please provide the correct account subscription"];
    public static $Code1006 =["1006","Please provide the valid dynamic field"];
    public static $Code1007 =["1007","More then one service template, please provide the unique product reference"];
    public static $Code1008 =["1008","Please provide the unique number for purchased"];
    public static $Code1009 =["1009","More then one Inbound Tariff found against the Category"];
    public static $Code1010 =["1010","Reseller user can not create reseller"];
    public static $Code1011 =["1011","please setup different domain for your reseller"];
    public static $Code1012 =["1012","Please provide the valid currency"];
    public static $Code1013 =["1013","Please provide the valid country"];
    public static $Code1014 =["1014","Please provide the valid Language"];
    public static $Code1015 =["1015","NextInvoiceDate Should be greater than BillingStartDate"];
    public static $Code1016 =["1016","Please select the valid billing type"];
    public static $Code1017 =["1017","Please select the valid billing class"];
    public static $Code1018 =["1018","Account Name contains illegal character"];
    public static $Code1019 =["1019","Please provide the valid owner ID"];



}