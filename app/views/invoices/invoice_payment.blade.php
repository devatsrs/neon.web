@extends('layout.blank')
<script src="{{URL::to('/')}}/assets/js/jquery-1.11.0.min.js"></script>
<script src="{{URL::to('/')}}/assets/js/toastr.js"></script>
<script src="{{URL::to('/')}}/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
<script src="{{URL::to('/')}}/assets/js/select2/select2.min.js"></script>
<link rel="stylesheet" type="text/css" href="{{URL::to('/')}}/assets/js/select2/select2-bootstrap.css">
<link rel="stylesheet" type="text/css" href="{{URL::to('/')}}/assets/js/select2/select2.css">
<link rel="stylesheet" type="text/css" href="{{URL::to('/')}}/assets/js/dataTables.bootstrap.js">
<link rel="stylesheet" type="text/css" href="{{URL::to('/')}}/assets/js/jquery.dataTables.min.js">
@section('content')
<header class="x-title">
    <div class="payment-strip">
        <div class="x-content">
            <div class="x-row">
                <div class="x-span8">
                    @if(isset($Invoice))
                        <div>
                            <div class="due">@if($Invoice->InvoiceStatus == Invoice::PAID) {{cus_lang('CUST_PANEL_PAGE_INVOICE_CVIEW_LBL_PAID')}} @else {{cus_lang('CUST_PANEL_PAGE_INVOICE_CVIEW_LBL_DUE')}} @endif</div>
                        </div>
                        <div class="amount">
                            <span class="overdue">{{$CurrencySymbol}}{{number_format($Invoice->GrandTotal,get_round_decimal_places($Invoice->AccountID))}}</span>
                        </div>
                    @elseif(isset($request["Amount"]))
                        <div class="amount">
                            <span class="overdue">{{$CurrencySymbol}}{{number_format($request["Amount"],get_round_decimal_places($Account->AccountID))}}</span>
                        </div>
                    @endif
                </div>
                <div class="x-span4 pull-left" > <h1 class="text-center"><h1 class="text-center">Payment</h1></h1></div>
            </div>
        </div>
    </div>
    </header>
    @if($PaymentGatewayID==PaymentGateway::AuthorizeNet || $PaymentGatewayID==PaymentGateway::Stripe || $PaymentGatewayID==PaymentGateway::FideliPay || $PaymentGatewayID==PaymentGateway::PeleCard || $PaymentGatewayID==PaymentGateway::MerchantWarrior))
        @include('invoices.invoice_creditcard')
    @endif
    @if($PaymentGatewayID==PaymentGateway::StripeACH)
        @include('invoices.invoice_bankaccount')
    @endif
    @if($PaymentGatewayID==PaymentGateway::AuthorizeNetEcheck)
        @include('invoices.invoice_authorizebankaccount')
    @endif

<script>
    var PaymentGatewayID='{{$PaymentGatewayID}}';
toastr_opts = {
    "closeButton": true,
    "debug": false,
    "positionClass": "toast-top-right",
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

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