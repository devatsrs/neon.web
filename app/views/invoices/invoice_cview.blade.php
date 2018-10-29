@extends('layout.blank')
<script src="{{URL::to('/')}}/assets/js/jquery-1.11.0.min.js"></script>
<script src="{{URL::to('/')}}/assets/js/toastr.js"></script>
<script src="{{URL::to('/')}}/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
<script src="{{URL::to('/')}}/assets/js/select2/select2.min.js"></script>
<link rel="stylesheet" type="text/css" href="{{URL::to('/')}}/assets/js/select2/select2-bootstrap.css">
<link rel="stylesheet" type="text/css" href="{{URL::to('/')}}/assets/js/select2/select2.css">

<!-- bootstarp.js is for action button-->
<script src="{{URL::to('/')}}/assets/js/bootstrap.js"></script>
@section('content')
<?php
    $PDFurl = "";
    $unsignPDFurl = "";
    if(!empty($Invoice->PDF)){
        /*if(is_amazon() == false){
            $unsignPDFurl = URL::to('/invoice/display_invoice/'.$Invoice->InvoiceID);
            $PDFurl = URL::to('/invoice/download_invoice/'.$Invoice->InvoiceID);
            $cdownload_usage =  URL::to('/invoice/'.$Invoice->AccountID.'-'.$Invoice->InvoiceID.'/cdownload_usage');
        }else{
            $PDFurl =  AmazonS3::preSignedUrl($Invoice->PDF);
            $unsignPDFurl =  AmazonS3::unSignedUrl($Invoice->PDF);
            if(!empty($Invoice->UsagePath)){
                $cdownload_usage =  AmazonS3::preSignedUrl($Invoice->UsagePath);
            }
        }*/

        $unsignPDFurl = URL::to('/invoice/display_invoice/'.$Invoice->InvoiceID);
        $PDFurl = URL::to('/invoice/download_invoice/'.$Invoice->InvoiceID);
        $cdownload_usage =  URL::to('/invoice/'.$Invoice->AccountID.'-'.$Invoice->InvoiceID.'/cdownload_usage');
    }
    ?>
<header class="x-title">
  <div class="payment-strip">
    <div class="x-content">
      <div class="x-row">
        <div class="x-span8">
          <div>
            <div class="due">@if($Invoice->InvoiceStatus == Invoice::PAID) @lang('routes.CUST_PANEL_PAGE_INVOICE_CVIEW_LBL_PAID') @else @lang('routes.CUST_PANEL_PAGE_INVOICE_CVIEW_LBL_DUE') @endif</div>
          </div>
          <div class="amount"> <span class="overdue"><?php if($Invoice->InvoiceStatus==Invoice::PAID){echo $CurrencySymbol.number_format($payment_log['paid_amount'],get_round_decimal_places($Invoice->AccountID));}elseif($Invoice->InvoiceStatus!=Invoice::PAID && $payment_log['paid_amount']>0){echo $CurrencySymbol.number_format($payment_log['due_amount'],get_round_decimal_places($Invoice->AccountID));}else{echo $CurrencySymbol.number_format($payment_log['total'],get_round_decimal_places($Invoice->AccountID));}  ?></span> </div>
        </div>
        <div class="x-span4 pull-left" >
          <h1 class="text-center">@lang('routes.CUST_PANEL_PAGE_INVOICE_TITLE')</h1>
        </div>
        <div class="x-span8 pull-right" style="margin-top:5px;">
          <?php
            /**
             * helper function
             * PaymentGatewayBase::get_cview_link();
             */

            ?>

              <div class="pull-right"> &nbsp;</div>
                  @if($Invoice->InvoiceStatus != Invoice::PAID && (getInvoicePayments($Invoice->CompanyID)) && $payment_log['final_payment'] > 0)
                  <div class="input-group-btn pull-right" style="width: 70px;">
                      <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="padding:4px 10px;"> @lang('routes.BUTTON_PAY_NOW_CAPTION') <span class="caret"></span></button>
                      <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                          @if(empty($ShowAllPaymentMethod))
                              @if(($PaymentMethod == 'AuthorizeNet') && (is_authorize($Invoice->CompanyID)  ) )
                                  <li> <a class="generate_rate create" href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/AuthorizeNet');}}" id="pay_AuthorizeNet" href="javascript:;"style="width:100%"> AuthorizeNet </a> </li>
                              @endif
                              @if(($PaymentMethod == 'Stripe') && (is_Stripe($Invoice->CompanyID)  ) )
                                  <li> <a class="generate_rate create" href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/Stripe');}}" id="pay_Stripe" href="javascript:;"> Stripe </a> </li>
                              @endif
                              @if($PaymentMethod == 'FideliPay') && is_FideliPay($Invoice->CompanyID))
                                  <li> <a class="generate_rate create" href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/FideliPay');}}" id="pay_FideliPay" href="javascript:;"> FideliPay </a> </li>
                              @endif
                              @if(($PaymentMethod == 'StripeACH') && (is_StripeACH($Invoice->CompanyID) && $StripeACHCount==1 ) )
                                  <li> <a class="generate_rate create"  href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/StripeACH');}}" id="pay_StripeACH" href="javascript:;"> StripeACH </a> </li>
                              @endif
                              @if(($PaymentMethod == 'Paypal') && (is_paypal($Invoice->CompanyID)  ) )
                                  <li> <a class="pay_now create" id="pay_paypal" href="javascript:;"> Paypal </a> </li>
                              @endif
                              @if(($PaymentMethod == 'SagePay') && (is_sagepay($Invoice->CompanyID)  ) )
                                  <li> <a class="pay_now create" id="pay_SagePay" href="javascript:;"> SagePay </a> </li>
                              @endif
                              @if(($PaymentMethod == 'PeleCard') && (is_pelecard($Invoice->CompanyID)  ) )
                                  <li> <a class="generate_rate create" href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/PeleCard');}}" id="pay_PeleCard" href="javascript:;"> PeleCard </a> </li>
                              @endif
                             @if(($PaymentMethod == 'MerchantWarrior') && (is_merchantwarrior($Invoice->CompanyID)  ) )
                                  <li> <a class="generate_rate create" href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/MerchantWarrior');}}" id="pay_MerchantWarrior" href="javascript:;"> MerchantWarrior </a> </li>
                              @endif
                             @if(($PaymentMethod == 'AuthorizeNetEcheck') && (is_authorize($Invoice->CompanyID)  ) )
                                  <li> <a class="generate_rate create" href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/AuthorizeNetEcheck');}}" id="pay_AuthorizeNetEcheck" href="javascript:;"> AuthorizeNet Echeck </a> </li>
                              @endif

                          @else
                              @if(is_authorize($Invoice->CompanyID))
                              <li> <a class="generate_rate create" href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/AuthorizeNet');}}" id="pay_AuthorizeNet" href="javascript:;"style="width:100%"> AuthorizeNet </a> </li>
                              @endif
                              @if(is_Stripe($Invoice->CompanyID))
                              <li> <a class="generate_rate create" href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/Stripe');}}" id="pay_Stripe" href="javascript:;"> Stripe </a> </li>
                              @endif
                              @if(is_FideliPay($Invoice->CompanyID))
                              <li> <a class="generate_rate create" href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/FideliPay');}}" id="pay_FideliPay" href="javascript:;"> FideliPay </a> </li>
                              @endif
                              @if(is_StripeACH($Invoice->CompanyID) && $StripeACHCount==1)
                              <li> <a class="generate_rate create"  href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/StripeACH');}}" id="pay_StripeACH" href="javascript:;"> StripeACH </a> </li>
                              @endif
                              @if(is_paypal($Invoice->CompanyID))
                              <li> <a class="pay_now create" id="pay_paypal" href="javascript:;"> Paypal </a> </li>
                              @endif
                              @if(is_sagepay($Invoice->CompanyID))
                              <li> <a class="pay_now create" id="pay_SagePay" href="javascript:;"> SagePay </a> </li>
                              @endif
                              @if(is_pelecard($Invoice->CompanyID))
                              <li> <a class="generate_rate create" href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/PeleCard');}}" id="pay_PeleCard" href="javascript:;"> PeleCard </a> </li>
                              @endif
                              @if(is_merchantwarrior($Invoice->CompanyID))
                              <li> <a class="generate_rate create" href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/MerchantWarrior');}}" id="pay_MerchantWarrior" href="javascript:;"> MerchantWarrior </a> </li>
                              @endif
                              @if(is_authorize($Invoice->CompanyID))
                              <li> <a class="generate_rate create" href="{{URL::to('invoice_payment/'. $Invoice->AccountID.'-'.$Invoice->InvoiceID.'/AuthorizeNetEcheck');}}" id="pay_AuthorizeNetEcheck" href="javascript:;"> AuthorizeNet Echeck </a> </li>
                              @endif
                          @endif
                      </ul>
                  </div>
                  <div class="pull-right"> &nbsp;</div>
                  @endif
              @if( (!empty($InvoiceTemplate->ManagementReport)) && InvoiceDetail::where(["InvoiceID" => $Invoice->InvoiceID,'ProductType'=>Product::USAGE])->count() && InvoiceDetail::where(["InvoiceID" => $Invoice->InvoiceID,'ProductType'=>Product::USAGE])->sum('LineTotal') != 0)
                  <a href="{{URL::to('/invoice/'.$Invoice->InvoiceID.'/invoice_chart/')}}" class="btn pull-right btn-success tooltip-primary" data-original-title="Management Reports" title="Management Reports" data-placement="top" data-toggle="tooltip"> <i class="entypo-chart-bar"></i></a>
                  <div class="pull-right"> &nbsp;</div>
              @endif
          @if( !empty($Invoice->UsagePath)) <a href="{{$cdownload_usage}}" class="btn pull-right btn-success btn-sm btn-icon icon-left"> <i class="entypo-down"></i> @lang('routes.CUST_PANEL_PAGE_INVOICE_CVIEW_BUTTON_DOWNLOAD_USAGE') </a>
          <div class="pull-right"> &nbsp;</div>
          @endif

              <a href="{{$PDFurl}}" class="print-invoice pull-right  btn btn-sm btn-danger btn-icon icon-left hidden-print"> @lang('routes.CUST_PANEL_PAGE_INVOICE_CVIEW_BUTTON_PRINT_INVOICE') <i class="entypo-doc-text"></i> </a>
              @if(($Invoice->InvoiceStatus != Invoice::PAID) && (is_paypal($Invoice->CompanyID)  ) )
              {{$paypal_button}}
              @endif
              @if(($Invoice->InvoiceStatus != Invoice::PAID) && (is_sagepay($Invoice->CompanyID)  ) )
              {{$sagepay_button}}
              @endif

        </div>
      </div>
    </div>
  </div>
</header>
<div class="container">
  <hr>
  <div class="invoice" id="Invoicepdf"> @if( !empty($PDFurl))
    <div>
      <iframe src="{{$unsignPDFurl}}" title="{{getenv('COMPANY_NAME')}}" frameborder="1" scrolling="auto" height="100%" width="100%" ></iframe>
    </div>
    @else
    <center>
        @lang('routes.CUST_PANEL_PAGE_INVOICE_CVIEW_MSG_ERROR_LOADING_INVOICE')
    </center>
    @endif </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('#pay_paypal').click( function(){
            $('#pyapalform').submit();
        });
        $('#pay_SagePay').click( function(){
            $('#sagepayform').submit();
        });
        $('#pay_PeleCard').click( function(){
            $('#pelecardform').submit();
        });
    });

    if ($.isFunction($.fn.select2))
    {
        $("select.select2").each(function(i, el)
        {
            var $this = $(el),
                    opts = {
                        allowClear: attrDefault($this, 'allowClear', false)
                    };
            if($this.hasClass('small')){
                opts['minimumResultsForSearch'] = attrDefault($this, 'allowClear', Infinity);
                opts['dropdownCssClass'] = attrDefault($this, 'allowClear', 'no-search')
            }
            $this.select2(opts);
            if($this.hasClass('small')){
                $this.select2('container').find('.select2-search').addClass ('hidden') ;
            }
            //$this.select2("open");
        }).promise().done(function(){
            $('.select2').css('visibility','visible');
        });


        if ($.isFunction($.fn.perfectScrollbar))
        {
            $(".select2-results").niceScroll({
                cursorcolor: '#d4d4d4',
                cursorborder: '1px solid #ccc',
                railpadding: {right: 3}
            });
        }
    }
    // Element Attribute Helper
    function attrDefault($el, data_var, default_val)
    {
        if (typeof $el.data(data_var) != 'undefined')
        {
            return $el.data(data_var);
        }

        return default_val;
    }
</script>
@stop