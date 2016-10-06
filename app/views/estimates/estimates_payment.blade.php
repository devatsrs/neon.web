@extends('layout.blank')
<script src="{{URL::to('/')}}/assets/js/jquery-1.11.0.min.js"></script>
<script src="{{URL::to('/')}}/assets/js/toastr.js"></script>
<script src="{{URL::to('/')}}/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
<script src="{{URL::to('/')}}/assets/js/select2/select2.min.js"></script>
<link rel="stylesheet" type="text/css" href="{{URL::to('/')}}/assets/js/select2/select2-bootstrap.css">
<link rel="stylesheet" type="text/css" href="{{URL::to('/')}}/assets/js/select2/select2.css">
@section('content')
<header class="x-title">
    <div class="payment-strip">
        <div class="x-content">
            <div class="x-row">
                <div class="x-span8">
                    <div>
                        <div class="due">@if($Invoice->InvoiceStatus == Invoice::PAID) Paid @else DUE @endif</div>
                    </div>
                    <div class="amount">
                        <span class="overdue">{{number_format($Invoice->GrandTotal,get_round_decimal_places($Invoice->AccountID))}} {{$CurrencyCode}}</span>
                    </div>
                </div>
                <div class="x-span4 pull-left" > <h1 class="text-center"><h1 class="text-center">Payment</h1></h1></div>
            </div>
        </div>
    </div>
    </header>
<div class="row">
<div class="col-md-4">&nbsp;</div>
<div class="col-md-4">
<form id="add-credit-card-form" method="post">
    <div class="modal-header">
        <h4 class="modal-title">Credit Card Detail </h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Name on card*</label>
                    <input type="text" name="NameOnCard" autocomplete="off" class="form-control" id="field-5" placeholder="">
                </div>
            </div>
            <!--<div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">First Name*</label>
                    <input type="text" name="FirstName" autocomplete="off" class="form-control" id="field-5" placeholder="">
                 </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Last Name*</label>
                    <input type="text" name="LastName" autocomplete="off" class="form-control" id="field-5" placeholder="">
                 </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Address*</label>
                    <input type="text" name="Address" autocomplete="off" class="form-control" id="field-5" placeholder="">
                 </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">City*</label>
                    <input type="text" name="FirstName" autocomplete="off" class="form-control" id="field-5" placeholder="">
                 </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Zip*</label>
                    <input type="text" name="FirstName" autocomplete="off" class="form-control" id="field-5" placeholder="">
                 </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Email*</label>
                    <input type="text" name="FirstName" autocomplete="off" class="form-control" id="field-5" placeholder="">
                 </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">State*</label>
                    <input type="text" name="FirstName" autocomplete="off" class="form-control" id="field-5" placeholder="">
                 </div>
            </div>-->
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Credit Card Number *</label>
                    <input type="text" name="CardNumber" autocomplete="off" class="form-control" id="field-5" placeholder="">
                    <input type="hidden" name="InvoiceID" value="{{$Invoice->InvoiceID}}" />
                    <input type="hidden" name="AccountID" value="{{$Invoice->AccountID}}" />
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Card Type*</label>
                    {{ Form::select('CardType',Payment::$credit_card_type,'', array("class"=>"select2 small")) }}
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">CVV Number*</label>
                    <input type="text" data-mask="decimal" name="CVVNumber" autocomplete="off" class="form-control" id="field-5" placeholder="">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <div class="col-md-4">
                        <label for="field-5" class="control-label">Expiry Date *</label>
                    </div>
                    <div class="col-md-4">
                        {{ Form::select('ExpirationMonth', getMonths(), date('m'), array("class"=>"select2 small")) }}
                    </div>
                    <div class="col-md-4">
                        {{ Form::select('ExpirationYear', getYears(), date('Y'), array("class"=>"select2 small")) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" id="card-pay"  class="save btn btn-green btn-sm btn-icon icon-left" data-loading-text="Loading...">
            <i class="entypo-floppy"></i>
            Pay
        </button>
        <a href="javascript:history.back()"><button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
            <i class="entypo-cancel"></i>
            Back
        </button>
        </a>
    </div>
</form>
</div>
</div>
<div class="col-md-4">&nbsp;</div>
<script>
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
$(document).ready(function() {
    @if($Invoice->InvoiceStatus == Invoice::PAID)
    $('#add-credit-card-form').find('[type="submit"]').attr('disabled', true);
    @endif

    $('#add-credit-card-form').submit(function(e) {
        e.preventDefault();
        $('#add-credit-card-form').find('[type="submit"]').attr('disabled', true);
        var update_new_url =update_new_url = '{{URL::to('/')}}/pay_invoice';
        $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);
                        window.location = '{{URL::to('/')}}/invoice_thanks/{{$Invoice->AccountID}}-{{$Invoice->InvoiceID}}';
                    }else{
                        $('#add-credit-card-form').find('[type="submit"]').attr('disabled', false);
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                error: function(error) {
                    $('#add-credit-card-form').find('[type="submit"]').attr('disabled', false);
                },
                // Form data
                data: $(this).serialize(),
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
        });
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