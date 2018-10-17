<div class="row">
<div class="col-md-4">&nbsp;</div>
<div class="col-md-4">
<form id="add-bank-detail-form" method="post">
    <div class="modal-header">
        <h4 class="modal-title">Echeck Bank Detail </h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Bank Account Name</label>
                    <input type="text" name="BankAccountName" autocomplete="off" class="form-control" id="field-5" placeholder="">
                    <input type="hidden" name="type" class="form-control" id="field-5" placeholder="" value="{{$type}}">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Bank Name</label>
                    <input type="text" name="BankName" autocomplete="off" class="form-control" id="field-5" placeholder="">
                    @if(isset($Invoice))
                        <input type="hidden" name="InvoiceID" value="{{$Invoice->InvoiceID}}" />
                        <input type="hidden" name="AccountID" value="{{$Invoice->AccountID}}" />
                        <input type="hidden" name="isInvoicePay" value="1" />
                    @elseif(isset($request["Amount"]))
                        <input type="hidden" name="InvoiceID" value="{{$request["InvoiceID"]}}" />
                        <input type="hidden" name="AccountID" value="{{$Account->AccountID}}" />
                        <input type="hidden" name="GrandTotal" value="{{$request["Amount"]}}" />
                        <input type="hidden" name="isInvoicePay" value="0" />
                        <input type="hidden" name="custome_notes" value="{{$request["custome_notes"]}}" />
                    @endif
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Account Number</label>
                    <input type="text" name="AccountNumber" autocomplete="off" class="form-control" id="field-5" placeholder="">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Routing Number</label>
                    <input type="text" name="RoutingNumber" autocomplete="off" class="form-control" id="field-5" placeholder="">
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" id="card-pay"  class="save btn btn-green btn-sm btn-icon icon-left" data-loading-text="{{cus_lang('BUTTON_LOADING_CAPTION')}}">
            <i class="entypo-floppy"></i>
                {{cus_lang('BUTTON_PAY_CAPTION')}}
        </button>
        <a href="javascript:history.back()"><button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
            <i class="entypo-cancel"></i>
            {{cus_lang('BUTTON_BACK_CAPTION')}}
        </button>
        </a>
    </div>
</form>
</div>
<div class="col-md-4">&nbsp;</div>
</div>

<script>

$(document).ready(function() {
    @if(isset($Invoice) && $Invoice->InvoiceStatus == Invoice::PAID)
    $('#add-bank-detail-form').find('[type="submit"]').attr('disabled', true);
    @endif

    $('#add-bank-detail-form').submit(function(e) {
        e.preventDefault();
        $('#add-bank-detail-form').find('[type="submit"]').attr('disabled', true);
        var update_new_url;
        var type = '{{$type}}';
        update_new_url = '{{URL::to('/')}}/payinvoice_withbankdetail/'+type;
        $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);
                        @if(isset($Invoice))
                            window.location = '{{URL::to('/')}}/invoice_thanks/{{$Invoice->AccountID}}-{{$Invoice->InvoiceID}}';
                        @elseif(isset($request["Amount"]))
                            window.location = '{{URL::to('/customer/payments')}}';
                        @endif
                    }else{
                        $('#add-bank-detail-form').find('[type="submit"]').attr('disabled', false);
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                error: function(error) {
                    $('#add-bank-detail-form').find('[type="submit"]').attr('disabled', false);
                },
                // Form data
                data: $(this).serialize(),
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
        });
    });
});

</script>