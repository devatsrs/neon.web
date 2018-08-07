@extends('layout.blank')
@section('content')
    <script src="<?php echo URL::to('/'); ?>/assets/js/jquery-1.11.0.min.js"></script>
    <script src="<?php echo URL::to('/'); ?>/assets/js/bootstrap.js"></script>
    @include('includes.errors')
    @include('includes.success')
    @include('includes.login-css')
    <?php
    $PDFurl 		= 	"";
    $unsignPDFurl 	= 	"";
	
    if(!empty($Estimate->PDF))
	{
        /*if(is_amazon() == false)
		{
            $unsignPDFurl 		= 	URL::to('/estimate/display_estimate/'.$Estimate->EstimateID);
            $PDFurl 			= 	URL::to('/estimate/download_estimate/'.$Estimate->EstimateID);
            $cdownload_usage 	=   URL::to('/estimate/'.$Estimate->AccountID.'-'.$Estimate->EstimateID.'/cdownload_usage');
        }
		else
		{
            $PDFurl 		 	 =  AmazonS3::preSignedUrl($Estimate->PDF);
            $unsignPDFurl		 =  AmazonS3::unSignedUrl($Estimate->PDF);
        
		    if(!empty($Estimate->UsagePath))
			{
                $cdownload_usage =  AmazonS3::preSignedUrl($Estimate->UsagePath);
    	    }
        }*/

        $unsignPDFurl 		= 	URL::to('/estimate/display_estimate/'.$Estimate->EstimateID);
        $PDFurl 			= 	URL::to('/estimate/download_estimate/'.$Estimate->EstimateID);
        $cdownload_usage 	=   URL::to('/estimate/'.$Estimate->AccountID.'-'.$Estimate->EstimateID.'/cdownload_usage');
    }
    ?>
<header class="x-title">
    <div class="payment-strip">
        <div class="x-content">
            <div class="x-row">
                <div class="x-span8">                   
                    <div class="amount">                        
                         <span class="overdue">{{$CurrencySymbol}}{{number_format($Estimate->GrandTotal,get_round_decimal_places($Estimate->AccountID))}}</span>
                         &nbsp;&nbsp;<span class="overdue">{{$EstimateStatus}}</span>
                    </div>
                </div>
                <div class="x-span4 pull-left" > <h1 class="text-center">Estimate</h1></div>
                <div class="x-span8 pull-right" style="margin-top:5px;">

                <a id="comment-estimate" class="pull-right  btn btn-sm btn-info btn-icon icon-left hidden-print">
                    Comment @if($EstimateComments>0)({{$EstimateComments}})@endif
                    <i class="fa fa-comment-o"></i>
                </a><div class="pull-right"> &nbsp;</div>
                <a id="reject-estimate" class="pull-right  btn btn-sm btn-danger btn-icon icon-left hidden-print">
                    Reject
                    <i class="fa fa-times"></i>
                </a><div class="pull-right"> &nbsp;</div>
                <a id="accept-estimate" class="pull-right  btn btn-sm btn-success btn-icon icon-left hidden-print">
                    Accept
                    <i class="fa fa-check"></i>
                </a><div class="pull-right"> &nbsp;</div>
                @if( !empty($Estimate->UsagePath))

                <a href="{{$cdownload_usage}}" class="btn pull-right btn-success btn-sm btn-icon icon-left">
                        <i class="entypo-down"></i>
                        Downlod Usage
                </a><div class="pull-right"> &nbsp;</div>
                @endif
                <a href="{{$PDFurl}}" class="print-invoice pull-right  btn btn-sm btn-primary btn-icon icon-left hidden-print">
                    Print
                    <i class="entypo-doc-text"></i>
                </a>


                </div>
            </div>
        </div>
    </div>
    </header>
<div class="container">




<hr>
        <div class="invoice" id="Invoicepdf">

            @if( !empty($PDFurl))
            <div>
                <iframe src="{{$unsignPDFurl}}" frameborder="1" scrolling="auto" height="100%" width="100%" ></iframe>
            </div>
            @else
                <center>Error loading Estimate, Its need to regenerate.</center>
            @endif

        </div>
</div>
    <div class="modal fade in" id="comment-modal-estimate">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="comment-estimate-form" method="post" class="form-horizontal form-groups-bordered">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Comments</h4>
                    </div>
                    <div class="modal-body"> </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary send btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Add Comment </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var base_url_estimate 		= 	"{{ URL::to('estimate')}}";
        var estimate_id = '{{$Estimate->EstimateID}}';
        $("#accept-estimate").click(function(ev) {
            if (!confirm('Are you sure you want to Accept estimate ?')) {
                return false;
            }
            var ajaxurl_convert = base_url_estimate+"/"+estimate_id+"/convert_estimate";
            $("#accept-estimate").button('loading');
            $.ajax({
                url: ajaxurl_convert,
                type: 'POST',
                dataType: 'json',
                data:{'eid':estimate_id,'convert':1},
                success: function(response) {
                    $("#accept-estimate").button('reset');
                    alert(response.message);
                    window.location.reload();
                }
            });
        });

        $("#reject-estimate").click(function(ev) {
            if (!confirm('Are you sure you want to Reject estimate ?')) {
                return false;
            }
            var email = '';

            var ajaxurl_convert = base_url_estimate+"/estimate_reject_Status";
            $("#reject-estimate").button('loading');
            $.ajax({
                url: ajaxurl_convert,
                type: 'POST',
                dataType: 'json',
                data:{'EstimateIDs':estimate_id,'EstimateStatus':'rejected','Email':email,'Type':'1'},
                success: function(response) {
                    $("#reject-estimate").button('reset');
                    alert(response.message);
                    window.location.reload();
                }
            });

        });

        $("#comment-estimate").click(function(ev) {

            $('#send-modal-estimate').find(".modal-body").html("Loading Content...");
            var ajaxurl = base_url_estimate + "/"+estimate_id+"/estimate_comment";
            showAjaxModalnew(ajaxurl,'comment-modal-estimate');
            $("#comment-estimate-form")[0].reset();
            $('#comment-modal-estimate').modal('show');
        });

        $("#comment-estimate-form").submit(function(e){
            e.preventDefault();
            var email = '';
            var Comment = $(this).find("[name=Comment]").val();
            if(Comment != ''){
                var EstimateID = $(this).find("[name=EstimateID]").val();
                var ajaxurl_comment = base_url_estimate+"/"+EstimateID+"/create_comment";
                $("#comment-estimate-form").find('[type=submit]').button('loading');
                $.ajax({
                    url: ajaxurl_comment,
                    type: 'POST',
                    dataType: 'json',
                    data:{'EstimateID':EstimateID,'Comment':Comment,'Email':email,'Type':'1'},
                    success: function(response) {
                        $("#comment-estimate-form").find('[type=submit]').button('reset');
                        $('#comment-modal-estimate').modal('hide');
                        $("#comment-estimate-form")[0].reset();
                        alert(response.message);
                    }
                });
            }else{
                alert('Please Add Comment');
                return false;
            }

        });

    });

    function showAjaxModalnew(ajaxurl, modalID)
    {
        modalID = '#' + modalID;
        $(modalID).modal('show', {backdrop: 'static'});

        $(modalID + ' .modal-body').html("Content is loading...");
        $.ajax({
            url: ajaxurl,
            success: function(response)
            {
                $(modalID + ' .modal-body').html(response);
            }
        });
    }

</script>
@stop