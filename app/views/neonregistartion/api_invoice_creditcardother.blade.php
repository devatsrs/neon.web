<div class="row">
<div class="col-md-4">&nbsp;</div>
<div class="col-md-4">
    <div class="modal-header">
        <h4 class="modal-title">
            @if($PaymentGateway=='Paypal')
                PayPal
            @else
                {{$PaymentGateway}}
            @endif
        </h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Amount</label>
                    <input type="text" name="Amount" autocomplete="off" class="form-control" id="field-5" placeholder="" value="{{$Amount}}" readonly>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        @if($PaymentGateway=='Paypal')
        <button type="submit" id="pay_paypal"  class="save btn btn-green btn-sm btn-icon icon-left" data-loading-text="{{cus_lang('BUTTON_LOADING_CAPTION')}}">
            <i class="entypo-floppy"></i>
                {{cus_lang('BUTTON_PAY_CAPTION')}}
        </button>
        @endif
        @if($PaymentGateway=='SagePay')
        <button type="submit" id="pay_SagePay"  class="save btn btn-green btn-sm btn-icon icon-left" data-loading-text="{{cus_lang('BUTTON_LOADING_CAPTION')}}">
            <i class="entypo-floppy"></i>
            {{cus_lang('BUTTON_PAY_CAPTION')}}
        </button>
        @endif
        <a href="#" id="paymentcancelbutton"><button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
            <i class="entypo-cancel"></i>
            {{cus_lang('BUTTON_BACK_CAPTION')}}
        </button>
        </a>
    </div>
    @if($PaymentGateway=='Paypal')
        {{$paypal_button}}
    @endif
    @if($PaymentGateway=='SagePay')
        {{$sagepay_button}}
    @endif

</div>
<div class="col-md-4">&nbsp;</div>
</div>
<form method="post" id="apiinvoicedone" class="hidden" action="{{$BackRequestUrl}}">
    <input type="text" name="status" value="failed">
    <input type="text" name="AccountID" value="0">
    <input type="text" name="AccountNumber" value="">
    <input type="text" name="PaymentStatus" value="failed">
    <input type="text" name="PaymentMessage" value="Payment Cancel">
    <input type="text" name="NeonStatus" value="failed">
    <input type="text" name="NeonMessage" value="Payment Cancel">
</form>

<script>

$(document).ready(function() {

    $('#pay_paypal').click( function(){
        $('#paypalform').submit();
    });

    $('#pay_SagePay').click( function(){
        $('#sagepayform').submit();
    });

    $('#paymentcancelbutton').on('click', function(){
        $('#apiinvoicedone').submit();
    });

});

</script>