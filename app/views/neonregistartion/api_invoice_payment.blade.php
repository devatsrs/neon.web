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
    @if($error==0)
        @if($PaymentGatewayID==PaymentGateway::AuthorizeNet || $PaymentGatewayID==PaymentGateway::Stripe || $PaymentGatewayID==PaymentGateway::FideliPay || $PaymentGatewayID==PaymentGateway::PeleCard || $PaymentGatewayID==PaymentGateway::MerchantWarrior)
            @include('neonregistartion.api_invoice_creditcard')
        @endif
        @if($PaymentGateway=='Paypal' || $PaymentGateway=='SagePay')
            @include('neonregistartion.api_invoice_creditcardother')
        @endif
    @else
    <form method="post" id="apiinvoicedone" class="hidden" action="{{$BackRequestUrl}}">
        <input type="text" name="status" value="failed">
        <input type="text" name="AccountID" value="0">
        <input type="text" name="AccountNumber" value="">
        <input type="text" name="PaymentStatus" value="failed">
        <input type="text" name="PaymentMessage" value="">
        <input type="text" name="NeonStatus" value="failed">
        <input type="text" name="NeonMessage" value="{{$errormessage}}">
    </form>
    @endif
<script>
    $(document).ready(function() {
        var Error='{{$error}}';
        if(Error==1) {
            $('#apiinvoicedone').submit();
        }
    });

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