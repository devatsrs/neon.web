<?php

/** SagePay
 * Created by PhpStorm.
 * User: deven
 * Date: 13/06/2017
 * Time: 6:25 PM
 */
class SagePay
{

    var $isLive ;
    var $ServiceKey ;
    var $SoftwareVendorKey ;
    var $ipn ;
    var $status ;
    var $item_title;
    var $method;


    //https://sagepay.co.za/integration/sage-pay-integration-documents/pay-now-gateway-technical-guide/

    function __Construct(){

        $this->method  = SiteIntegration::$SagePaySlug;

        $sagepay_obj	 = SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$SagePaySlug);

        if( !empty($sagepay_obj) ) {

			$this->ServiceKey 	            = 	$sagepay_obj->ServiceKey;
            $this->SoftwareVendorKey		= 	$sagepay_obj->SoftwareVendorKey;
            $this->isLive  				= 	$sagepay_obj->isLive;

            if(empty($this->ipn)){

                $post = \Illuminate\Support\Facades\Input::all();
                $this->ipn = $post;
            }
            $this->status = true;

        }else{

            if(empty($this->ipn)){

                $post = \Illuminate\Support\Facades\Input::all();
                $this->ipn = $post;
            }
            $this->status = false;
        }

    }

    public function status(){

        return $this->status;
    }


    public function success(){

        # Get Returned Variables - Adjust for Post Variable Names from your Gateway's Documentation

        if(isset($this->ipn["TransactionAccepted"]) && strtolower($this->ipn["TransactionAccepted"]) == "true"  ){

            return true;

        }
        # Unsuccessful

        \Illuminate\Support\Facades\Log::info("SagePay Transaction Unsuccessful");
        $this->log();

        return false;
    }

    public function pending(){

        # Get Returned Variables - Adjust for Post Variable Names from your Gateway's Documentation

        if(isset($this->ipn["TransactionAccepted"]) && strtolower($this->ipn["TransactionAccepted"]) == "PENDING"  ){

            return true;

        }
        # Unsuccessful

        \Illuminate\Support\Facades\Log::info("SagePay Transaction Unsuccessful");
        $this->log();

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


        $message = "SagePay Payment Note: \n\r";
        if(isset($this->ipn["RequestTrace"]) ) {
            $message .= sprintf('Txn Id : %s ', $this->ipn["RequestTrace"]) . " \n\r";
        }

       return $message;

    }

    /**
     * log the response
     */
    public function log(){

        \Illuminate\Support\Facades\Log::info("SagePay IPN");
        \Illuminate\Support\Facades\Log::info($this->ipn);

    }

    /**
     * paynow button show.
     */
    public function get_paynow_button($AccountID,$InvoiceID){

        $sagepay_url = "https://paynow.sagepay.co.za/site/paynow.aspx";

        $this->amount = number_format($this->amount,2,'.','') ;// paypal gives error if more than 2 decimal placesrequies 2 decimal points

        $custom_fields = json_encode(["AccountID"=>$AccountID,"InvoiceID",$InvoiceID]);

        $return_url = url('/sagepay_return');
        $cancel_url = url('/sagepay_declined');
        $notify_url = url('/sagepay_ipn');


        $form = '<form name="form" id="sagepayform" method="POST" action="' . $sagepay_url .'" target="_self" class="no-margin">
                  <input type="hidden" name="m1" value="' . $this->ServiceKey . '"> <!-- // Pay Now Service Key -->
                  <input type="hidden" name="m2" value="' . $this->SoftwareVendorKey . '"> <!-- // // Software Vendor Key -->
                  <input type="hidden" name="p2" value="' . $InvoiceID.':'.date('YmdHis') . '"> <!-- // // Unique ID for this transaction -->
                  <input type="hidden" name="p3"  value="' . $this->item_title  .  '"> <!-- // // Description of goods being purchased -->
                  <input type="hidden" name="p4" value="' . $this->amount  .  '"> <!-- // // Amount to be settled to the credit card -->
                  <input type="hidden" name="Budget" value="Y"> <!-- // // Budget facility being offered? -->
                  <input type="hidden" name="m4" value="' . $this->item_number  .  '"> <!-- // // This is an extra field -->
                  <input type="hidden" name="m5" value="' . $custom_fields  .  '"> <!-- // // This is an extra field -->
                  <input type="hidden" name="m6" value=""> <!-- // // This is an extra field -->
                  <input type="hidden" name="m9" value=""> <!-- // // Card holders email address -->
                  <input type="hidden" name="m10" value="m10"> <!-- // // M10 data -->

                  <input type="hidden" name="return_url" value="' . $return_url  .  '">
                  <input type="hidden" name="cancel_url" value="' . $cancel_url  .  '">
                  <input type="hidden" name="notify_url" value="' . $notify_url  .  '">


                  <button name="submit"  type="submit" class="pull-right  btn btn-sm btn-danger btn-icon icon-left hidden-print" style="display:none;"> <i class="entypo-credit-card"></i> Pay with SagePay</button>
                </form>';

        return $form;

    }

    public function get_full_response(){

        if(empty($this->ipn)){

            $post = \Illuminate\Support\Facades\Input::all();

            $this->ipn = $post;
        }
        return $this->ipn;
    }
}