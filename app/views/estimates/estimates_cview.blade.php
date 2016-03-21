@extends('layout.blank')
@section('content')
    <?php
    $PDFurl 		= 	"";
    $unsignPDFurl 	= 	"";
	
    if(!empty($Estimate->PDF))
	{
        if(is_amazon() == false)
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
        }
    }
    ?>
<header class="x-title">
    <div class="payment-strip">
        <div class="x-content">
            <div class="x-row">
                <div class="x-span8">                   
                    <div class="amount">
                        <span class="overdue">{{number_format($Estimate->GrandTotal,$Account->RoundChargesAmount)}} <em class="currency overdue">{{$CurrencyCode}}</em></span>
                    </div>
                </div>
                <div class="x-span4 pull-left" > <h1 class="text-center">Estimate</h1></div>
                <div class="x-span8 pull-right" style="margin-top:5px;">
                @if( !empty($Estimate->UsagePath))

                <a href="{{$cdownload_usage}}" class="btn pull-right btn-success btn-sm btn-icon icon-left">
                        <i class="entypo-down"></i>
                        Downlod Usage
                </a><div class="pull-right"> &nbsp;</div>
                @endif
                <a href="{{$PDFurl}}" class="print-invoice pull-right  btn btn-sm btn-danger btn-icon icon-left hidden-print">
                    Print Estimate
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

@stop