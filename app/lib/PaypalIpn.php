<?php

/** Paypal Standard
 * Created by PhpStorm.
 * User: deven
 * Date: 29/09/2016
 * Time: 5:27 PM
 */
class PaypalIpn
{

    var $is_live ;
    var $business_email ;
    var $ipn;
    var $item_title;
    var $item_number;
    var $amount;
    var $curreny_code;
    var $logo_url;
    var $status;
    var $method;
    var $custom;


    function __Construct($CompanyID){

        $this->method  = "PAYPAL_IPN";
        //$is_paypal 	 = CompanyConfiguration::get($paypal_key);
		$is_paypal	 = SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$paypalSlug,$CompanyID);

        if( !empty($is_paypal) ) {

           /* $this->paypal_business_email = CompanyConfiguration::getJsonKey($paypal_key,"paypal_business_email");
            $this->is_live  = CompanyConfiguration::getJsonKey($paypal_key,"is_live");
            $this->logo_url = CompanyConfiguration::getJsonKey($paypal_key,"logo_url");*/
			
			$this->paypal_business_email 	= 	$is_paypal->PaypalEmail;
            $this->is_live  				= 	$is_paypal->PaypalLive;
            $this->logo_url 				= 	$is_paypal->PaypalLogoUrl;
			

            if(empty($this->ipn)){

                $post = \Illuminate\Support\Facades\Input::all();
                $this->ipn = $post;
            }
            $this->status = true;

            return true;

        }else{

            if(empty($this->ipn)){

                $post = \Illuminate\Support\Facades\Input::all();
                $this->ipn = $post;
            }
            $this->status = false;
            return false;
        }

    }



    public function success(){

        /* Post
         * Array
            (
                [mc_gross] => 35.98
                [protection_eligibility] => Eligible
                [address_status] => confirmed
                [item_number1] => 1878
                [payer_id] => RD3R6ZAKRY9FE
                [tax] => 0.00
                [address_street] => 1 Main St
                [payment_date] => 07:07:29 Sep 11, 2010 PDT
                [payment_status] => Completed
                [charset] => windows-1252
                [address_zip] => 95131
                [mc_shipping] => 0.00
                [mc_handling] => 0.00
                [first_name] => Test
                [mc_fee] => 1.60
                [address_country_code] => US
                [address_name] => Test User
                [notify_version] => 3.0
                [custom] => session_id=7bce002bf5e5a1dd286d367c2cae49a7|total_item=2|total_amount=35.98
                [payer_status] => verified
                [business] => devens_1224939565_biz@yahoo.com
                [address_country] => United States
                [num_cart_items] => 1
                [mc_handling1] => 0.00
                [address_city] => San Jose
                [payer_email] => devens_1212647640_per@yahoo.com
                [verify_sign] => Azz3kByIjBt0E6SRXklhqpnfFwW3AMj9JaX8zEEUnLHq1.fQvhOk2yVH
                [mc_shipping1] => 0.00
                [tax1] => 0.00
                [txn_id] => 8Y6300504T3331016
                [payment_type] => instant
                [last_name] => User
                [receiver_email] => devens_1224939565_biz@yahoo.com
                [item_name1] => Tioga Comp III Tyres  Fat And Thin
                [address_state] => CA
                [payment_fee] =>
                [quantity1] => 1
                [receiver_id] => M8SL3QQJY9RTJ
                [txn_type] => cart
                [mc_currency] => GBP
                [mc_gross_1] => 35.98
                [residence_country] => US
                [test_ipn] => 1
                [transaction_subject] => session_id=7bce002bf5e5a1dd286d367c2cae49a7|total_item=2|total_amount=35.98
                [payment_gross] =>
                [merchant_return_link] => Return to Deven Sitapara\'s Test Store
            )
*/

        if(isset($this->ipn["payment_status"]) && strtolower($this->ipn["payment_status"]) == 'completed'  ){

            return true;

        } else if($this->is_live == 0 && isset($this->ipn["payment_status"]) && strtolower($this->ipn["payment_status"]) == 'pending'  ){


            return true;

        }

        return false;
    }

    public function get_response_var($field){

        if(empty($this->ipn)){

            $post = \Illuminate\Support\Facades\Input::all();

            $this->ipn = $post;
        }

        if(isset($this->ipn[$field]) ){

            return $this->ipn[$field];
        }

        return null;

    }

    /**
     * Generate paypal response note.
     * @return null
     */
    public function get_note(){


        $message = "Paypal Payment Note: \n\r";
        if(isset($this->ipn["txn_id"]) ) {
            $message .= sprintf('Txn Id : %s ', $this->ipn["txn_id"]) . " \n\r";
        }
        if(isset($this->ipn["address_name"]) ) {
            $message .= sprintf('Payer name : %s ', $this->ipn["address_name"]) . " \n\r";
        }
        if(isset($this->ipn["address_country"]) ) {
            $message .= sprintf('Country : %s ', $this->ipn["address_country"]) . " \n\r";
        }
        if(isset($this->ipn["payer_email"]) ) {
            $message .= sprintf('Payer : %s ', $this->ipn["payer_email"]) . " \n\r";
        }

       return $message;

    }

    /**
     * log the response
     */
    public function log(){

        \Illuminate\Support\Facades\Log::info("Paypal IPN");
        \Illuminate\Support\Facades\Log::info($this->ipn);

    }

    /**
     * paynow button show.
     */
    public function get_paynow_button($AccountID,$InvoiceID, $paypal_success_url="", $paypal_cancel_url=""){

        if(empty($paypal_success_url)){
            $paypal_success_url = url('/invoice_thanks/'.$AccountID . '-' . $InvoiceID );
        }
        if(empty($paypal_cancel_url)){
            $paypal_cancel_url = url('/paypal_cancel/'.$AccountID . '-' . $InvoiceID );
        }

        $paypal_ipn_url = url('/paypal_ipn/'.$AccountID . '-' . $InvoiceID );

        if (!$this->is_live) {
//            $paypal_email =  'devens_1224939565_biz@yahoo.com';  //devens_1224939565_biz@yahoo.com
            $paypal_email =  'vishal.jagani-facilitator@code-desk.com';  //devens_1224939565_biz@yahoo.com
            $paypal_url  = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            $paypal_url  = 'https://www.paypal.com/cgi-bin/webscr';
            $paypal_email =  $this->paypal_business_email;

        }
        $this->amount = number_format($this->amount,2,'.','') ;// paypal gives error if more than 2 decimal placesrequies 2 decimal points

        $form = '<form method="post" id="pyapalform" action="' . $paypal_url  .  '" target="_self" class="no-margin" >
        <input type="hidden" name="business" value="' . $paypal_email  .  '"/>
        <input type="hidden" name="return" value="' . $paypal_success_url  .  '" />
        <input type="hidden" name="cancel_url" value="' . $paypal_cancel_url  .  '" />
        <input type="hidden" name="notify_url" value="' . $paypal_ipn_url  .  '" />
        <input type="hidden" name="item_name" value="' . $this->item_title  .  '"/>
        <input type="hidden" name="item_number" value="' . $this->item_number  .  '" />
        <input type="hidden" name="quantity" value="1"/>
        <input type="hidden" name="amount" value="' . $this->amount  .  '"/>
        <input type="hidden" name="custom" value=""/>
        <input type="hidden" name="currency_code" value="' . $this->curreny_code  .  '"/>
        <input type="hidden" name="image_url" value="'. $this->logo_url .'"/>
        <input type="hidden" name="rm" value="2"/>
        <input type="hidden" name="cmd" value="_xclick"/>
        <button type="submit" class="pull-right  btn btn-sm btn-danger btn-icon icon-left hidden-print" style="display:none;"> <i class="entypo-credit-card"></i> Pay Now With Paypal</button>
        </form>';

        return $form;

    }

    public function get_full_response(){

        if(empty($this->ipn)){

            $post = \Illuminate\Support\Facades\Input::all();

            $this->ipn = $post;

            $this->log();
        }
        return $this->ipn;
    }

    /**
     * paynow button show in registration widget.
     */
    public function get_api_paynow_button($CompanyID, $paypal_success_url="", $paypal_cancel_url=""){

        if(empty($paypal_success_url)){
            $paypal_success_url = url('/api_accountcreation/'.$CompanyID);
        }
        if(empty($paypal_cancel_url)){
            $paypal_cancel_url = url('/api_paypal_cancel/'.$CompanyID);
        }

        $paypal_ipn_url = url('/api_paypal_ipn/'.$CompanyID);

        if (!$this->is_live) {
//            $paypal_email =  'devens_1224939565_biz@yahoo.com';  //devens_1224939565_biz@yahoo.com
            $paypal_email =  'vishal.jagani-facilitator@code-desk.com';  //devens_1224939565_biz@yahoo.com
            $paypal_url  = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            $paypal_url  = 'https://www.paypal.com/cgi-bin/webscr';
            $paypal_email =  $this->paypal_business_email;

        }
        $this->amount = number_format($this->amount,2,'.','') ;// paypal gives error if more than 2 decimal placesrequies 2 decimal points
        $form = '<form method="post" id="paypalform" action="' . $paypal_url  .  '" target="_self" class="no-margin" >
        <input type="hidden" name="business" value="' . $paypal_email  .  '"/>
        <input type="hidden" name="return" value="' . $paypal_success_url  .  '" />
        <input type="hidden" name="cancel_url" value="' . $paypal_cancel_url  .  '" />
        <input type="hidden" name="notify_url" value="' . $paypal_ipn_url  .  '" />
        <input type="hidden" name="item_name" value="' . $this->item_title  .  '"/>
        <input type="hidden" name="item_number" value="' . $this->item_number  .  '" />
        <input type="hidden" name="quantity" value="1"/>
        <input type="hidden" name="amount" value="' . $this->amount  .  '"/>
        <input type="hidden" name="custom" value=""/>
        <input type="hidden" name="currency_code" value="' . $this->curreny_code  .  '"/>
        <input type="hidden" name="image_url" value="'. $this->logo_url .'"/>
        <input type="hidden" name="rm" value="2"/>
        <input type="hidden" name="cmd" value="_xclick"/>
        <button type="submit" class="pull-right  btn btn-sm btn-danger btn-icon icon-left hidden-print" style="display:none;"> <i class="entypo-credit-card"></i> Pay Now With Paypal</button>
        </form>';

        return $form;

    }
}