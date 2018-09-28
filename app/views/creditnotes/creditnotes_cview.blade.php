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

    if(!empty($CreditNotes->PDF))
	{
        /*if(is_amazon() == false)
		{
            $unsignPDFurl 		= 	URL::to('/creditnotes/display_creditnotes/'.$CreditNotes->CreditNotesID);
            $PDFurl 			= 	URL::to('/creditnotes/download_creditnotes/'.$CreditNotes->CreditNotesID);
            $cdownload_usage 	=   URL::to('/creditnotes/'.$CreditNotes->AccountID.'-'.$CreditNotes->CreditNotesID.'/cdownload_usage');
        }
		else
		{
            $PDFurl 		 	 =  AmazonS3::preSignedUrl($CreditNotes->PDF);
            $unsignPDFurl		 =  AmazonS3::unSignedUrl($CreditNotes->PDF);
        
		    if(!empty($CreditNotes->UsagePath))
			{
                $cdownload_usage =  AmazonS3::preSignedUrl($CreditNotes->UsagePath);
    	    }
        }*/
        $unsignPDFurl 		= 	URL::to('/creditnotes/display_creditnotes/'.$CreditNotes->CreditNotesID);
        $PDFurl 			= 	URL::to('/creditnotes/download_creditnotes/'.$CreditNotes->CreditNotesID);
        $cdownload_usage 	=   URL::to('/creditnotes/'.$CreditNotes->AccountID.'-'.$CreditNotes->CreditNotesID.'/cdownload_usage');
    }
    ?>
<header class="x-title">
    <div class="payment-strip">
        <div class="x-content">
            <div class="x-row">
                <div class="x-span8">                   
                    <div class="amount">                        
                         <span class="overdue">{{$CurrencySymbol}}{{number_format($CreditNotes->GrandTotal,get_round_decimal_places($CreditNotes->AccountID))}}</span>
                         &nbsp;&nbsp;<span class="overdue">{{$CreditNotesStatus}}</span>
                    </div>
                </div>
                <div class="x-span4 pull-left" > <h1 class="text-center">Credit Note</h1></div>
                <div class="x-span8 pull-right" style="margin-top:5px;">

                @if( !empty($CreditNotes->UsagePath))

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
                <center>Error loading Credit Notes, Its need to regenerate.</center>
            @endif

        </div>
</div>

    <div class="modal fade in" id="comment-modal-creditnotes">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="comment-creditnotes-form" method="post" class="form-horizontal form-groups-bordered">
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
            var base_url_creditnotes 		= 	"{{ URL::to('creditnotes')}}";
            var creditnotes_id = '{{$CreditNotes->CreditNotesID}}';
            $("#accept-creditnotes").click(function(ev) {
                if (!confirm('Are you sure you want to Accept creditnotes ?')) {
                    return false;
                }
                var email = '';
                @if(isset($_GET['email']))
                   email = '{{$_GET['email']}}';
                @endif

                var ajaxurl_convert = base_url_creditnotes+"/"+creditnotes_id+"/customer_accept_creditnotes";

                $.ajax({
                    url: ajaxurl_convert,
                    type: 'POST',
                    dataType: 'json',
                    data:{'eid':creditnotes_id,'convert':1,'Email':email,'Type':2},
                    success: function(response) {
                        alert(response.message);
                        window.location.reload();
                    }
                });
            });

            $("#reject-creditnotes").click(function(ev) {
                if (!confirm('Are you sure you want to Reject creditnotes ?')) {
                    return false;
                }
                var email = '';
                @if(isset($_GET['email']))
                    email = '{{$_GET['email']}}';
                @endif

                var ajaxurl_convert = base_url_creditnotes+"/creditnotes_reject_Status";

                $.ajax({
                    url: ajaxurl_convert,
                    type: 'POST',
                    dataType: 'json',
                    data:{'CreditNotesIDs':creditnotes_id,'CreditNotesStatus':'rejected','Email':email,'Type':'2'},
                    success: function(response) {
                        alert(response.message);
                        window.location.reload();
                    }
                });

            });

            $("#comment-creditnotes").click(function(ev) {
                $('#send-modal-creditnotes').find(".modal-body").html("Loading Content...");
                var ajaxurl = base_url_creditnotes + "/"+creditnotes_id+"/creditnotes_comment";
                showAjaxModalnew(ajaxurl,'comment-modal-creditnotes');
                $("#comment-creditnotes-form")[0].reset();
                $('#comment-modal-creditnotes').modal('show');
            });

            $("#comment-creditnotes-form").submit(function(e){
                e.preventDefault();
                var email = '';
                @if(isset($_GET['email']))
                        email = '{{$_GET['email']}}';
                @endif
                var Comment = $(this).find("[name=Comment]").val();
                if(Comment != ''){
                var CreditNotesID = $(this).find("[name=CreditNotesID]").val();
                var ajaxurl_comment = base_url_creditnotes+"/"+CreditNotesID+"/create_comment";

                $.ajax({
                    url: ajaxurl_comment,
                    type: 'POST',
                    dataType: 'json',
                    data:{'CreditNotesID':CreditNotesID,'Comment':Comment,'Email':email,'Type':'2'},
                    success: function(response) {
                        $('#comment-modal-creditnotes').modal('hide');
                        $("#comment-creditnotes-form")[0].reset();
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