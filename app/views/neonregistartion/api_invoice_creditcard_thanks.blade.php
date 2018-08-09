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
@stop