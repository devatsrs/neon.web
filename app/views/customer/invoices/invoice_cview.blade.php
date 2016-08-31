@extends('layout.blank')

@section('content')
<style type="text/css">
.invoice,
.invoice table,.invoice table td,.invoice table th,
.invoice ul li
{ font-size: 12px; }
.invoice .table.table-bordered th {
    font-weight: bold;
    color: #a6a7aa;
}
.invoice h5{
font-size: 14px;
color: #a6a7aa;
font-weight: bold;
}
@media print {
    .page_break{page-break-after: always;}
    * {
        background-color: auto !important;
        background: auto !important;
        color: auto !important;
    }
    th,td{ padding: 1px; margin: 1px;}
}
.page_break{page-break-after: always;}
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
<div class="container">
           <div class="text-center"> <h1>Invoice</h1></div>
<div class="pull-right">
    {{--<a href="Javascript:;" class="send-invoice btn btn-sm btn-success btn-icon icon-left hidden-print">
        Send Invoice
        <i class="entypo-mail"></i>
    </a>
    &nbsp;--}}
    <a href="{{URL::to('/invoice/'.$Invoice->AccountID.'-'.$Invoice->InvoiceID.'/cprint')}}" class="print-invoice btn btn-sm btn-danger btn-icon icon-left hidden-print">
        Print Invoice
        <i class="entypo-doc-text"></i>
    </a>
    @if( !empty($Invoice->UsagePath))
    &nbsp;
    <a href="{{URL::to('/invoice/'.$Invoice->AccountID.'-'.$Invoice->InvoiceID.'/cdownload_usage')}}" class="btn btn-success btn-sm btn-icon icon-left">
            <i class="entypo-down"></i>
            Downlod Usage
        </a>
    @endif
</div>
<div class="clear clearfix"></div>
<hr>
<div class="invoice">

	<div class="row">

		<div class="col-sm-6 invoice-left">
            <img src="{{$logo}}" alt="Company Logo" title="Company Logo"  style="max-width: 250px">
		</div>

		<div class="col-sm-6 invoice-right">
		        <br>
				<p><strong>Invoice No: </strong>{{$Invoice->FullInvoiceNumber}}</p>
				<p><strong>Invoice Date: </strong>{{ date('d-m-Y',strtotime($Invoice->IssueDate))}}</p>
		</div>
		
	</div>
	

	<div class="row">
	
		<div class="col-sm-3 invoice-left">
           <br><br>
           <strong>Invoice From:</strong>
           <p>{{ nl2br($InvoiceTemplate->Header)}}</p>


		</div>
	

		<div class="col-md-9 invoice-right">
		    <br><br>
            <strong>Invoice To</strong>
			<p>{{$Account->AccountName}}</p>
			<p>{{nl2br($Invoice->Address)}}</p>

		</div>
		
	</div>
	<br /><br /><br /><br /><br /><br />
	<div class="row">
            <div class="col-sm-5">
                <div class="invoice-left">
                    <table class="table table-bordered" style="text-align: center;">
                        <thead>
                        <tr>
                            <th colspan="2" style="text-align: center;">Invoice Period</th>
                        </tr>
                        <tr>
                            <th style="text-align: center;">From</th>
                            <th style="text-align: center;">To</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td >{{$InvoiceFrom}}</td>
                            <td>{{$InvoiceTo}}</td>
                        </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            <div class="col-sm-2"></div>
            <div class="col-sm-5">
                <div class="invoice-right">
                    <table class="table">
                        <tbody>
                        <tr>
                            <td style="border-top: 1px solid black;text-align: left;">Previous Balance</td>
                            <td style="border-top: 1px solid black;text-align: right;">{{number_format($Invoice->PreviousBalance,$RoundChargesAmount)}}</td>
                        </tr>
                        <tr>
                            <td style="border-top: 1px solid black;text-align: left;">Charges for this period</td>
                            <td style="border-top: 1px solid black;text-align: right;">{{number_format($Invoice->GrandTotal,$RoundChargesAmount)}}</td>
                        </tr>
                        <tr>
                            <td style="border-top: 2px solid black;text-align: left;">Total Due (USD)</td>
                            <td style="border-top: 2px solid black;text-align: right;">{{floatval(number_format($Invoice->TotalDue,$RoundChargesAmount))}}</td>
                        </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
        </div>

	<div class="margin"></div>
	<div class="row">

            <div class="col-sm-7">

                <div class="invoice-left">
                    </br>
                    </br>
                    <p>{{nl2br($Invoice->Terms)}}</p>

                </div>

            </div>

            <div class="col-sm-5">

                <div class="invoice-right">

                        <table class="table table-bordered">
                            <tfoot>
                            <tr>
                                <td class="text-right"><strong>Usage</strong></td>
                                <td>{{number_format($useagetotal,$RoundChargesAmount)}}</td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>Subscription </strong></td>
                                <td>{{number_format($subscriptiontotal,$RoundChargesAmount)}}</td>
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
                                    <td class="text-right"><strong> Discount</strong></td>
                                    <td>{{number_format($Invoice->TotalDiscount,$RoundChargesAmount)}}</td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>Invoice Total </strong></td>
                                <td>{{number_format($Invoice->GrandTotal,$RoundChargesAmount)}} {{$CurrencyCode}}</td>
                            </tr>

                            </tfoot>
                        </table>

                </div>
            </div>
        </div>


	<div class="margin"></div>
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




</div>
<hr>
</div>
@stop