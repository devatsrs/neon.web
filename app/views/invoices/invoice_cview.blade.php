@extends('layout.blank')
@section('content')
    <?php
    $PDFurl = "";
    $unsignPDFurl = "";
    if(!empty($Invoice->PDF)){
        if(is_amazon() == false){
            $unsignPDFurl = URL::to('/invoice/display_invoice/'.$Invoice->InvoiceID);
            $PDFurl = URL::to('/invoice/download_invoice/'.$Invoice->InvoiceID);
            $cdownload_usage =  URL::to('/invoice/'.$Invoice->AccountID.'-'.$Invoice->InvoiceID.'/cdownload_usage');
        }else{
            $PDFurl =  AmazonS3::preSignedUrl($Invoice->PDF);
            $unsignPDFurl =  AmazonS3::unSignedUrl($Invoice->PDF);
            if(!empty($Invoice->UsagePath)){
                $cdownload_usage =  AmazonS3::preSignedUrl($Invoice->UsagePath);
            }
        }
    }
    ?>
<header class="x-title">
    <div class="payment-strip">
        <div class="x-content">
            <div class="x-row">
                <div class="x-span8">
                    <div>
                        <div class="due">@if($Invoice->InvoiceStatus == Invoice::PAID) Paid @else DUE @endif</div>
                    </div>
                    <div class="amount">
                        <span class="overdue"><em class="currency overdue">{{$CurrencySymbol}}</em>{{number_format($Invoice->GrandTotal,$Account->RoundChargesAmount)}}</span>
                    </div>
                </div>
                <div class="x-span4 pull-left" > <h1 class="text-center">Invoice</h1></div>
                <div class="x-span8 pull-right" style="margin-top:5px;">
                @if(($Invoice->InvoiceStatus != Invoice::PAID) && is_authorize())
                <a href="{{URL::to('invoice_payment', $Invoice->AccountID.'-'.$Invoice->InvoiceID);}}" class="print-invoice pull-right  btn btn-sm btn-danger btn-icon icon-left hidden-print">
                    <i class="entypo-credit-card"></i>
                Pay Now
                </a><div class="pull-right"> &nbsp;</div>
                @endif
                @if( !empty($Invoice->UsagePath))

                <a href="{{$cdownload_usage}}" class="btn pull-right btn-success btn-sm btn-icon icon-left">
                        <i class="entypo-down"></i>
                        Downlod Usage
                </a><div class="pull-right"> &nbsp;</div>
                @endif
                <a href="{{$PDFurl}}" class="print-invoice pull-right  btn btn-sm btn-danger btn-icon icon-left hidden-print">
                    Print Invoice
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
                <center>Error loading Invoice, Its need to regenerate.</center>
            @endif

        </div>
</div>

@stop