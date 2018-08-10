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
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <div class="modal-header">            
                <h4 class="modal-title">Thank You For Payment.</h4>            
        </div>
        <div class="modal-body">
            <div id="table-4_processing" class="dataTables_processing" style="visibility: hidden;">Neon Account Creating...</div>
        </div>
    </div>
</div>
<form method="post" id="apiinvoicecreate" class="hidden">
    <input type="text" name="customdata" value="{{htmlspecialchars($customdata)}}">
    <input type="text" name="CreditCard" value="1">
    <input type="text" name="CompanyID" value="1">
</form>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        setTimeout(function(){
            $('#apiinvoicecreate').submit();
        }, 10);
        $("#apiinvoicecreate").submit(function (e) {
            e.preventDefault();
            $('.dataTables_processing').css("visibility","visible");
            var baseurl = '{{URL::to('/')}}';
            var url =  baseurl + '/globalneonregistarion/createaccount';
            var post_data = $(this).serialize();
            $.ajax({
                url:url, //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    $('.dataTables_processing').css("visibility","hidden");
                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                data: post_data,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
            });

        });

    });
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