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
                <center>Error loading Credit Note, Its need to regenerate.</center>
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