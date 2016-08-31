@extends('layout.print')

@section('content')
<style>
*{
    font-family: Arial;
    font-size: 12px;
    line-height: normal;
}
p{ line-height: 20px;}
.text-left{ text-align: left}
.text-right{ text-align: right}
.text-center{ text-align: center}
table.invoice th{ padding:3px; background-color: #f5f5f6}
.bg_graycolor{background-color: #f5f5f6}
table.invoice td , table.invoice_total td{ padding:3px;}
.page_break{page-break-after: always;}
@media print {
    * {
        background-color: auto !important;
        background: auto !important;
        color: auto !important;
    }
    th,td{ padding: 1px; margin: 1px;}
}

</style>
<?php
$RoundChargesAmount = get_round_decimal_places($Account->AccountID);
$InvoiceTo =$InvoiceFrom = '';
$is_sub = false;
$subscriptiontotal = $useagetotal= 0;
$subscriptionarray= array();
foreach($InvoiceDetail as $ProductRow){
    $InvoiceFrom = date('F d,Y',strtotime($ProductRow->StartDate));
    $InvoiceTo = date('F d,Y',strtotime($ProductRow->EndDate));
    if($ProductRow->ProductType == Product::SUBSCRIPTION){
        $subscriptiontotal += $ProductRow->LineTotal;
        $is_sub = true;
    }
    if($ProductRow->ProductType == Product::USAGE){
        $useagetotal += $ProductRow->LineTotal;
    }
}
$InvoiceTaxRates = InvoiceTaxRate::where("InvoiceID",$Invoice->InvoiceID )->get();

?>
        <table border="0" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="col-md-6" valign="top">
                    @if(!empty($logo))
                   <img src="{{$logo}}" style="max-width: 250px">
                   @endif
                </td>
                <td class="col-md-6 text-right"  valign="top" >
                        <p><b>Invoice No: </b>{{$Invoice->FullInvoiceNumber}}</p>
                        <p><b>Invoice Date: </b>{{ date('d-m-Y',strtotime($Invoice->IssueDate))}}</p>
                </td>
            </tr>
        </table>
        <br />

        <table border="0" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="col-md-6" valign="top">
                    <br>
                   <strong>Invoice From:</strong>
                   <p><strong>{{ nl2br($InvoiceTemplate->Header)}}</strong></p>

                </td>
                <td class="col-md-6 text-right"  valign="top" >
                        <br>
                        <strong>Invoice To</strong>
                        <p>{{$Account->AccountName}}</p>
                        <p>{{nl2br($Invoice->Address)}}</p>
                </td>
            </tr>
        </table>
        <br /><br /><br /><br /><br /><br />
         <table width="100%" border="0">
            <tbody>
            <tr>
                <td width="40%">
                    <table border="1"  width="100%" cellpadding="0" cellspacing="0" class="invoice table table-bordered">
                        <thead>
                        <tr>
                            <th style="text-align: right;border-right: 0px solid black !important;"><br>&nbsp;Invoice</th><th style="text-align: left;border-left: 0px solid black !important;"><br>&nbsp;Period</th>
                        </tr>
                        <tr>
                            <th style="text-align: center;">From</th>
                            <th style="text-align: center;">To</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>{{$InvoiceFrom}}</td>
                            <td>{{$InvoiceTo}}</td>
                        </tr>
                        </tbody>
                    </table></td>
                <td width="25%"></td>
                <td width="35%">
                    <table class="table table-bordered" style="width: 100%; text-align: right;">
                        <tbody>
                        <tr>
                            <td style="border-top: 1px solid black;text-align: left;">Previous Balance</td>
                            <td style="border-top: 1px solid black; text-align: right;">{{number_format($Invoice->PreviousBalance,$RoundChargesAmount)}}</td>
                        </tr>
                        <tr>
                            <td style="border-top: 1px solid black;text-align: left;">Charges for this period</td>
                            <td style="border-top: 1px solid black; text-align: right;">{{number_format($Invoice->GrandTotal,$RoundChargesAmount)}}</td>
                        </tr>
                        <tr>
                            <td style="border-top: 2px solid black;text-align: left;">Total Due (USD)</td>
                            <td style="border-top: 2px solid black; text-align: right;">{{floatval(number_format($Invoice->TotalDue,$RoundChargesAmount))}}</td>
                        </tr>
                        </tbody>
                    </table></td>
            </tr>
            </tbody>
        </table>
<br /><br /><br />
    <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="table-responsive">
                                <table border="0" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="col-md-6" valign="top" width="65%">
                                                <p><a class="form-control" style="height: auto">{{nl2br($Invoice->Terms)}}</a></p>
                                        </td>
                                        <td class="col-md-6"  valign="top" width="35%" >
                                                <table  border="1"  width="100%" cellpadding="0" cellspacing="0" class="bg_graycolor invoice_total col-md-12 table table-bordered">
                                                    <tfoot>
                                                        <tr>
                                                                <td class="text-right" ><strong>Usage</strong></td>
                                                                <td class="text-right">{{number_format($useagetotal,$RoundChargesAmount)}}</td>
                                                        </tr>
                                                        <tr>
                                                                <td class="text-right"><strong>Subscription</strong></td>
                                                                <td class="text-right">{{number_format($subscriptiontotal,$RoundChargesAmount)}}</td>
                                                        </tr>
                                                        @if(count($InvoiceTaxRates))
                                                        @foreach($InvoiceTaxRates as $InvoiceTaxRate)
                                                        <tr>
                                                                <td class="text-right"><strong>{{$InvoiceTaxRate->Title}}</strong></td>
                                                                <td class="text-right">{{number_format($InvoiceTaxRate->TaxAmount,$RoundChargesAmount)}}</td>
                                                        </tr>
                                                        @endforeach
                                                        @endif
                                                        <tr>
                                                                <td class="text-right"><strong>Discount</strong></td>
                                                                <td class="text-right">{{number_format($Invoice->TotalDiscount,$RoundChargesAmount)}}</td>
                                                        </tr>
                                                        <tr>
                                                                <td class="text-right"><strong>Invoice Total</strong></td>
                                                                <td class="text-right">{{number_format($Invoice->GrandTotal,$RoundChargesAmount)}} {{$CurrencyCode}}</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                        </td>
                                    </tr>
                                </table>
                                </br>
                                </br>
                                </br>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<div class="page_break"> </div>
<h5>Usage Charges</h5>
<table border="1"  width="100%" cellpadding="0" cellspacing="0" class="invoice col-md-12 table table-bordered">
        <thead>
        <tr>
            <th style="text-align: center;">Title</th>
            <th style="text-align: center;">Description</th>
            <th style="text-align: center;">Price</th>
            <th style="text-align: center;">Quantity</th>
            <th style="text-align: center;">Date From</th>
            <th style="text-align: center;">Date To</th>
            <th style="text-align: center;">Line Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($InvoiceDetail as $ProductRow)
        @if($ProductRow->ProductType == Product::USAGE)
        <tr>
            <td >{{Product::getProductName($ProductRow->ProductID,$ProductRow->ProductType)}}</td>
            <td>{{$ProductRow->Description}}</td>
            <td>{{number_format($ProductRow->Price,$RoundChargesAmount)}}</td>
            <td>{{$ProductRow->Qty}}</td>
            <td>{{date('Y-m-d',strtotime($ProductRow->StartDate))}}</td>
            <td>{{date('Y-m-d',strtotime($ProductRow->EndDate))}}</td>
            <td>{{number_format($ProductRow->LineTotal,$RoundChargesAmount)}}</td>
        </tr>
        @endif
        @endforeach
        </tbody>
    </table>
    @if($is_sub == true)
    <br /> 
    <h5>Subscription Charges</h5>
    <table border="1"  width="100%" cellpadding="0" cellspacing="0" class="invoice col-md-12 table table-bordered">
            <thead>
            <tr>
                <th style="text-align: center;">Title</th>
                <th style="text-align: center;">Description</th>
                <th style="text-align: center;">Price</th>
                <th style="text-align: center;">Quantity</th>
                <th style="text-align: center;">Date From</th>
                <th style="text-align: center;">Date To</th>
                <th style="text-align: center;">Line Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($InvoiceDetail as $ProductRow)
            @if($ProductRow->ProductType == Product::SUBSCRIPTION)
            <tr>
                <td >{{Product::getProductName($ProductRow->ProductID,$ProductRow->ProductType)}}</td>
                <td>{{$ProductRow->Description}}</td>
                <td>{{number_format($ProductRow->Price,$RoundChargesAmount)}}</td>
                <td>{{$ProductRow->Qty}}</td>
                <td>{{date('Y-m-d',strtotime($ProductRow->StartDate))}}</td>
                <td>{{date('Y-m-d',strtotime($ProductRow->EndDate))}}</td>
                <td>{{number_format($ProductRow->LineTotal,$RoundChargesAmount)}}</td>
            </tr>
            @endif
            @endforeach
            </tbody>
        </table>
    @endif



 @stop