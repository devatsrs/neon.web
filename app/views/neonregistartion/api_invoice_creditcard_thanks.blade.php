@extends('layout.blank')
<meta http-equiv="Content-Security-Policy" content="default-src *; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://staging.neon-soft.com/api_invoice_thanks/1">
<script src="{{URL::to('/')}}/assets/js/jquery-1.11.0.min.js"></script>
<script src="{{URL::to('/')}}/assets/js/toastr.js"></script>
<script src="{{URL::to('/')}}/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
<script src="{{URL::to('/')}}/assets/js/select2/select2.min.js"></script>
<link rel="stylesheet" type="text/css" href="{{URL::to('/')}}/assets/js/select2/select2-bootstrap.css">
<link rel="stylesheet" type="text/css" href="{{URL::to('/')}}/assets/js/select2/select2.css">
<link rel="stylesheet" type="text/css" href="{{URL::to('/')}}/assets/js/dataTables.bootstrap.js">
<link rel="stylesheet" type="text/css" href="{{URL::to('/')}}/assets/js/jquery.dataTables.min.js">
@section('content')
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <div class="modal-header">            
                <h4 class="modal-title">Registering...</h4>
        </div>
    </div>
</div>
<div id="last_msg_loader" style="display: table; position: absolute; padding: 10px; text-align: center; left: 50%; top: auto; margin: 71px auto; z-index: 999;"></div>
<form method="post" id="apiinvoicecreate" class="hidden">
    <input type="text" name="customdata" value="{{htmlspecialchars($customdata)}}">
    <input type="text" name="CreditCard" value="1">
    <input type="text" name="CompanyID" value="1">
</form>
<form method="post" id="apiinvoicedone" class="hidden">
    <input type="text" name="status">
    <input type="text" name="AccountID">
    <input type="text" name="AccountNumber">
    <input type="text" name="PaymentStatus">
    <input type="text" name="PaymentMessage">
    <input type="text" name="NeonStatus">
    <input type="text" name="NeonMessage">
</form>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        setTimeout(function(){
            $('#apiinvoicecreate').submit();
        }, 10);
        $("#apiinvoicecreate").submit(function (e) {
            e.preventDefault();
            var baseurl = '{{URL::to('/')}}';
            $('div#last_msg_loader').html('<img src="'+baseurl+'/assets/images/bigLoader.gif">');
            var url =  baseurl + '/globalneonregistarion/createaccount';
            var post_data = $(this).serialize();
            $.ajax({
                url:url, //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    $('div#last_msg_loader').empty();
                    if(response.status =='success'){
                        //toastr.success(response.message, "Success", toastr_opts);
                    }else{
                        //toastr.error(response.message, "Error", toastr_opts);

                    }
                    $.when(testfn(response)).then(fnsubmit());

                },
                data: post_data,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
            });

        });

    });
    function testfn(response){
        $("#apiinvoicedone [name='status']").val(response.status);
        $("#apiinvoicedone [name='AccountID']").val(response.AccountID);
        $("#apiinvoicedone [name='AccountNumber']").val(response.AccountNumber);
        $("#apiinvoicedone [name='PaymentStatus']").val(response.PaymentStatus);
        $("#apiinvoicedone [name='PaymentMessage']").val(response.PaymentMessage);
        $("#apiinvoicedone [name='NeonStatus']").val(response.NeonStatus);
        $("#apiinvoicedone [name='NeonMessage']").val(response.NeonMessage);
        $('#apiinvoicedone').attr('action', response.ApiRequestUrl);
    }
    function fnsubmit(){
        $('#apiinvoicedone').submit();
    }
</script>
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