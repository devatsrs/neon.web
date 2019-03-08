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

    const KEY_ITEM                      = 'Item';
    const KEY_COST_COMPONENT_VoiceCall  = "CostComponentsVoiceCall";
    const KEY_COST_COMPONENT_DID        = "CostComponentsAccess";
    const KEY_COST_COMPONENT_PKG        = "CostComponentsPackage";
    const KEY_VAT                       = "VAT";
    const TEXT_ITEM                     = 'Item';
    const TEXT_COST_COMPONENT_VoiceCall = "Cost Components - Termination";
    const TEXT_COST_COMPONENT_DID       = "Cost Components - Access";
    const TEXT_COST_COMPONENT_PKG       = "Cost Components - Package";
    const TEXT_VAT                      = "VAT";

    const KEY_VAT_COUNTRY_NL            = "NL";
    const KEY_VAT_COUNTRY_EU            = "EU";
    const KEY_VAT_COUNTRY_NEU           = "NEU";
    const TEXT_VAT_COUNTRY_NL           = "NETHERLANDS";
    const TEXT_VAT_COUNTRY_EU           = "EU Countries";
    const TEXT_VAT_COUNTRY_NEU          = "Non EU Countries";

    public static $MappingTypes = [
        ExactAuthentication::KEY_ITEM                       => ExactAuthentication::TEXT_ITEM,
        ExactAuthentication::KEY_COST_COMPONENT_VoiceCall   => ExactAuthentication::TEXT_COST_COMPONENT_VoiceCall,
        ExactAuthentication::KEY_COST_COMPONENT_DID         => ExactAuthentication::TEXT_COST_COMPONENT_DID,
        ExactAuthentication::KEY_COST_COMPONENT_PKG         => ExactAuthentication::TEXT_COST_COMPONENT_PKG,
        ExactAuthentication::KEY_VAT                        => ExactAuthentication::TEXT_VAT,
    ];

    public static $VATCountry = [
        ExactAuthentication::KEY_VAT_COUNTRY_NL     => ExactAuthentication::TEXT_VAT_COUNTRY_NL,
        ExactAuthentication::KEY_VAT_COUNTRY_EU     => ExactAuthentication::TEXT_VAT_COUNTRY_EU,
        ExactAuthentication::KEY_VAT_COUNTRY_NEU    => ExactAuthentication::TEXT_VAT_COUNTRY_NEU,
    ];

    public static function getAllMappingElements($CompanyID){
        $products = [];
        $items = Product::select(['ProductID','Name'])->where(['CompanyId'=>$CompanyID,'Active'=>1])->lists('Name','ProductID');
        $products[ExactAuthentication::KEY_ITEM]                        = $items;
        $products[ExactAuthentication::KEY_COST_COMPONENT_VoiceCall]    = RateTableRate::$Components;
        $products[ExactAuthentication::KEY_COST_COMPONENT_DID]          = RateTableDIDRate::$Components;
        $products[ExactAuthentication::KEY_COST_COMPONENT_PKG]          = RateTablePKGRate::$Components;
        return $products;
    }

}