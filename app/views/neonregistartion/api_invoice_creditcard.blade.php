<div class="row">
<div class="col-md-4">&nbsp;</div>
<div class="col-md-4">
<form id="add-credit-card-form" method="post">
    <div class="modal-header">
        <h4 class="modal-title">{{cus_lang('CUST_PANEL_PAGE_CREDIT_CARD_HEADING')}} </h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">

                <div class="form-group">
                    <label for="field-5" class="control-label">{{cus_lang('CUST_PANEL_PAGE_CREDIT_CARD_FIELD_NAME_ON_CARD')}}</label>
                    <input type="text" name="NameOnCard" autocomplete="off" class="form-control" id="field-5" placeholder="">
                    <input type="hidden" name="type" class="form-control" id="field-5" placeholder="" value="">
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
                    <label for="field-5" class="control-label">{{cus_lang('CUST_PANEL_PAGE_CREDIT_CARD_FIELD_CREDIT_CARD_NUMBER')}}</label>
                    <input type="text" name="CardNumber" autocomplete="off" class="form-control" id="field-5" placeholder="">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">{{cus_lang('CUST_PANEL_PAGE_CREDIT_CARD_FIELD_CARD_TYPE')}}</label>
                    {{ Form::select('CardType',Payment::$credit_card_type,'', array("class"=>"select2 small")) }}
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">{{cus_lang('CUST_PANEL_PAGE_CREDIT_CARD_FIELD_CVV_NUMBER')}}</label>
                    <input type="text" data-mask="decimal" name="CVVNumber" autocomplete="off" class="form-control" id="field-5" placeholder="">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <div class="col-md-4">
                        <label for="field-5" class="control-label">{{cus_lang('CUST_PANEL_PAGE_CREDIT_CARD_FIELD_EXPIRY_DATE')}}</label>
                    </div>
                    <div class="col-md-4">
                        {{ Form::select('ExpirationMonth', getMonths(), date('m'), array("class"=>"select2 small")) }}
                    </div>
                    <div class="col-md-4">
                        {{ Form::select('ExpirationYear', getYears(), date('Y'), array("class"=>"select2 small")) }}
                    </div>
                </div>
            </div>
            @if($PaymentGatewayID==PaymentGateway::PeleCard)
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="field-5" class="control-label">{{cus_lang('CUST_PANEL_PAGE_CREDIT_CARD_FIELD_PELECARDID')}}</label>
                        <input type="text" name="PeleCardID" autocomplete="off" class="form-control" placeholder="">
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <input type="hidden" name="Amount" value="{{$Amount}}">
        <input type="hidden" name="CompanyID" value="{{$CompanyID}}">
        <input type="hidden" name="PaymentGatewayID" value="{{$PaymentGatewayID}}">
        <input type="hidden" name="CustomData" value="{{htmlentities($CustomData)}}">
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
    <form method="post" id="apiinvoicethanks" class="hidden">
        <input type="text" name="data">
    </form>
</div>
<div class="col-md-4">&nbsp;</div>
</div>

<script>

$(document).ready(function() {

    $('#add-credit-card-form').submit(function(e) {
        e.preventDefault();
        $('#add-credit-card-form').find('[type="submit"]').attr('disabled', true);
        var update_new_url;
        var baseurl = '{{URL::to('/')}}';
        update_new_url =  baseurl + '/globalneonregistarion/createpayment';
        var invoiceurl = baseurl + '/api_invoice_creditcard_thanks';
        $.ajax({
            url: update_new_url,  //Server script to process data
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                if(response.status =='success'){
                    toastr.success(response.message, "Success", toastr_opts);
                    $('#apiinvoicethanks input[name=data]').val(response.data);
                    $('#apiinvoicethanks').attr('action',invoiceurl);
                    $('#apiinvoicethanks').submit();

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

</script>