<?php
/**
 * Created by PhpStorm.
 * User: VASIM
 * Date: 22-02-2019
 * Time: 12:47 PM
 */

class ExactAuthentication extends \Eloquent {

    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblExactAuthentication';
    protected $primaryKey = "ExactAuthenticationID";

    public $timestamps = false;

    const ExactConfigKey        = "EXACT";
    const AUTH_URL              = 'https://start.exactonline.nl/api/oauth2/auth';
    const TOKEN_URL             = 'https://start.exactonline.nl/api/oauth2/token';
    const RESPONSE_TYPE_AUTH    = 'code'; // for auth request
    const RESPONSE_TYPE_TOKEN   = "authorization_code"; // for token request
    const RESPONSE_TYPE_GRANT   = 'refresh_token'; // for refresh token request

    const KEY_COST_COMPONENT_TERMINATION    = "CostComponentsTermination";
    const KEY_COST_COMPONENT_DID            = "CostComponentsAccess";
    const KEY_COST_COMPONENT_PKG            = "CostComponentsPackage";
    const KEY_PAYMENT_CONDITION             = "PaymentCondition";
    const KEY_ONE_OFF_INVOICE_COMPONENT     = "OneOffInvoiceComponent";
    const TEXT_COST_COMPONENT_TERMINATION   = "Cost Components - Termination";
    const TEXT_COST_COMPONENT_DID           = "Cost Components - Access";
    const TEXT_COST_COMPONENT_PKG           = "Cost Components - Package";
    const TEXT_PAYMENT_CONDITION            = "Payment Condition";
    const TEXT_ONE_OFF_INVOICE_COMPONENT    = "One-Off Invoice Component";

    const KEY_VAT_COUNTRY_NL            = "NL";
    const KEY_VAT_COUNTRY_EU            = "EU";
    const KEY_VAT_COUNTRY_NEU           = "NEU";
    const TEXT_VAT_COUNTRY_NL           = "NETHERLANDS";
    const TEXT_VAT_COUNTRY_EU           = "EU Countries";
    const TEXT_VAT_COUNTRY_NEU          = "Non EU Countries";

    public static $MappingTypes = [
        ExactAuthentication::KEY_COST_COMPONENT_TERMINATION => ExactAuthentication::TEXT_COST_COMPONENT_TERMINATION,
        ExactAuthentication::KEY_COST_COMPONENT_DID         => ExactAuthentication::TEXT_COST_COMPONENT_DID,
        ExactAuthentication::KEY_COST_COMPONENT_PKG         => ExactAuthentication::TEXT_COST_COMPONENT_PKG,
        ExactAuthentication::KEY_PAYMENT_CONDITION          => ExactAuthentication::TEXT_PAYMENT_CONDITION,
        ExactAuthentication::KEY_ONE_OFF_INVOICE_COMPONENT  => ExactAuthentication::TEXT_ONE_OFF_INVOICE_COMPONENT,
    ];

    public static $VATCountry = [
        ExactAuthentication::KEY_VAT_COUNTRY_NL     => ExactAuthentication::TEXT_VAT_COUNTRY_NL,
        ExactAuthentication::KEY_VAT_COUNTRY_EU     => ExactAuthentication::TEXT_VAT_COUNTRY_EU,
        ExactAuthentication::KEY_VAT_COUNTRY_NEU    => ExactAuthentication::TEXT_VAT_COUNTRY_NEU,
    ];

    public static $PaymentConditionComponents = array(
        "Prepaid"       => "Prepaid",
        "Postpaid"      => "Postpaid",
        "OnCredit"      => "OnCredit",
        "Creditcard"    => "Creditcard",
        "DirectDebit"   => "DirectDebit",
    );

    public static $OneOffInvoiceComponent = array(
        "One-Off"       => "One-Off",
        "Outpayment"    => "Outpayment",
        "topup"         => "TopUp",
    );

    public static function getAllMappingElements(){
        $products = [];
        $products[ExactAuthentication::KEY_COST_COMPONENT_TERMINATION]  = RateTableRate::$Components;
        $products[ExactAuthentication::KEY_COST_COMPONENT_DID]          = RateTableDIDRate::$Components;
        $products[ExactAuthentication::KEY_COST_COMPONENT_PKG]          = RateTablePKGRate::$Components;
        $products[ExactAuthentication::KEY_PAYMENT_CONDITION]           = ExactAuthentication::$PaymentConditionComponents;
        $products[ExactAuthentication::KEY_ONE_OFF_INVOICE_COMPONENT]   = ExactAuthentication::$OneOffInvoiceComponent;
        return $products;
    }

}