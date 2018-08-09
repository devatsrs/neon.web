<div class="row">
<div class="col-md-4">&nbsp;</div>
<div class="col-md-4">
    <div class="modal-header">
        <h4 class="modal-title">PayPal</h4>
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
        <button type="submit" id="pay_paypal"  class="save btn btn-green btn-sm btn-icon icon-left" data-loading-text="{{cus_lang('BUTTON_LOADING_CAPTION')}}">
            <i class="entypo-floppy"></i>
                {{cus_lang('BUTTON_PAY_CAPTION')}}
        </button>
        <a href="javascript:history.back()"><button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
            <i class="entypo-cancel"></i>
            {{cus_lang('BUTTON_BACK_CAPTION')}}
        </button>
        </a>
    </div>
    {{$paypal_button}}
</div>
<div class="col-md-4">&nbsp;</div>
</div>

<script>

$(document).ready(function() {

    $('#pay_paypal').click( function(){
        $('#paypalform').submit();
    });
});

</script>