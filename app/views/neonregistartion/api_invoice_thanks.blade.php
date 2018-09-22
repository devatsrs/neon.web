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
                <h4 class="modal-title">Registering...</h4>
        </div>
    </div>
</div>
<div id="last_msg_loader" style="display: table; position: absolute; padding: 10px; text-align: center; left: 50%; top: auto; margin: 71px auto; z-index: 999;"></div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var data='{{$customdata}}';
        var baseurl = '{{URL::to('/')}}';
        $('div#last_msg_loader').html('<img src="'+baseurl+'/assets/images/bigLoader.gif">');
        var url =  baseurl + '/globalneonregistarion/createaccount';
        $.ajax({
            url: url,  //Server script to process data
            data: 'customdata='+data+'&CompanyID=1&CreditCard=0',
            dataType: 'json',
            success: function (response) {
                $('div#last_msg_loader').empty();
                if(response.status =='success'){
                    toastr.success(response.message, "Success", toastr_opts);
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            },
            type: 'POST'
        });

    });
</script>
@stop