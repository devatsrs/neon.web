@extends('layout.blank')
@include('invoicetemplates.servicehtml')
@section('content')
<link rel="stylesheet" type="text/css" href="<?php echo URL::to('/'); ?>/assets/css/invoicetemplate/invoicestyle.css" />
<style type="text/css">
.invoice,
.invoice table,.invoice table td,.invoice table th,
.invoice ul li
{ font-size: 12px; }
.page_break{page-break-after: always;}
#pdf_header, #pdf_footer{
    /*position: fixed;*/
}
</style>
<div class="inovicebody" style="max-height: 100%;overflow-x: hidden;overflow-y: auto;">
    <header class="clearfix">
        @yield('logo')
	</header>
	<main>
		<div id="details" class="clearfix">
			<div style="float:left;display:none;">
				<p> {{ Form::select('InvoiceToInfo', Invoice::$invoice_account_info, (!empty(Input::get('InvoiceToInfo'))?explode(',',Input::get('InvoiceFromInfo')):[]), array("class"=>"select2","multiple","data-allow-clear"=>"true","data-placeholder"=>"Select Account Info")) }} </p>
			</div>
			<div id="client">
				<div class="to"><b>Invoice To:</b></div>
                <div>{{nl2br(Invoice::getInvoiceTo($InvoiceTemplate->InvoiceTo))}}</div>
			</div>
			<div id="invoice">
				<h1>Invoice No: {{$InvoiceTemplate->InvoiceNumberPrefix.$InvoiceTemplate->InvoiceStartNumber}}</h1>
				<div class="date">Invoice Date: {{date('d-m-Y')}}</div>
				<div class="date">Due Date: {{date('d-m-Y',strtotime('+5 days'))}}</div>
                @if($InvoiceTemplate->ShowBillingPeriod == 1)
                    <div class="date">Invoice Period: {{date('d-m-Y',strtotime('-7 days'))}} - {{date('d-m-Y')}}</div>
                @endif
			</div>
		</div>
		<table border="0" cellspacing="0" cellpadding="0" id="frontinvoice">
			<thead>
			<tr>
                @if($InvoiceTemplate->GroupByService==1)
				<th class="desc"><b>Description</b></th>
                @endif
				<th class="desc"><b>Usage</b></th>
				<th class="desc"><b>Recurring</b></th>
				<th class="desc"><b>Additional</b></th>
				<th class="total"><b>Total</b></th>
			</tr>
			</thead>
			<tbody>
			<tr>
                @if($InvoiceTemplate->GroupByService==1)
				<td class="desc">Service - 1</td>
                @endif
                <td class="desc">$1,200.00</td>
                <td class="desc">$1,000.00</td>
                <td class="desc">$1,000.00</td>
				<td class="total">$3,200.00</td>
			</tr>
            @if($InvoiceTemplate->GroupByService==1)
			<tr>
				<td class="desc">Service - 2</td>
                <td class="desc">$1,200.00</td>
                <td class="desc">$1,000.00</td>
                <td class="desc">$1,000.00</td>
				<td class="total">$3,200.00</td>
			</tr>
			<tr>
				<td class="desc">Other Service</td>
                <td class="desc">$400.00</td>
                <td class="desc">$400.00</td>
                <td class="desc">$400.00</td>
				<td class="total">$1,200.00</td>
			</tr>
            @endif
			</tbody>
			<tfoot>
			<tr>
                @if($InvoiceTemplate->GroupByService==1)
				<td colspan="2"></td>
                @else
                <td></td>
                @endif
				<td colspan="2">Sub Total</td>
				<td class="subtotal">$5,200.00</td>
			</tr>
			<tr>
                @if($InvoiceTemplate->GroupByService==1)
                    <td colspan="2"></td>
                @else
                    <td></td>
                @endif
				<td colspan="2">Tax 25%</td>
				<td class="subtotal">$1,300.00</td>
			</tr>
            @if($InvoiceTemplate->ShowPrevBal)
                <tr>
                    @if($InvoiceTemplate->GroupByService==1)
                        <td colspan="2"></td>
                    @else
                        <td></td>
                    @endif
                    <td colspan="2">Brought Forward</td>
                    <td class="subtotal">$0.00</td>
                </tr>
            @endif
			<tr>
                @if($InvoiceTemplate->GroupByService==1)
                    <td colspan="2"></td>
                @else
                    <td></td>
                @endif
				<td colspan="2"><b>Grand Total</b></td>
				<td class="subtotal">$6,500.00</td>
			</tr>
			</tfoot>
		</table>
	</main>
	<div id="thanksadevertise">
		<div class="invoice-left">
			@yield('terms')
		</div>
	</div>
    <br/>
    <br/>
    @if($InvoiceTemplate->GroupByService==1)
    <header class="clearfix">
        <div id="Service">
            <h1>Service 1</h1>
        </div>
    </header>
    @endif
    <main>
        <div class="ChargesTitle clearfix">
            <div style="float:left;">Usage</div>
            <div style="text-align:right;float:right;">$6.20</div>
        </div>
        <table border="0" cellspacing="0" cellpadding="0" id="backinvoice">
            <thead>
            <tr>
                <th class="leftalign">Title</th>
                <th class="leftalign">Description</th>
                <th class="rightalign">Price</th>
                <th class="rightalign">Qty</th>
                <th class="leftalign">Date From</th>
                <th class="leftalign">Date To</th>
                <th class="rightalign">Total</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="leftalign">Usage</td>
                <td class="leftalign">From 01-01-2017 To 31-01-2017</td>
                <td class="rightalign">1.24</td>
                <td class="rightalign">1</td>
                <td class="leftalign">01-01-2017</td>
                <td class="leftalign">31-01-2017</td>
                <td class="rightalign">1.24</td>
            </tr>
            </tbody>
        </table>

        <div class="ChargesTitle clearfix">
            <div style="float:left;">Recurring</div>
            <div style="text-align:right;float:right;">$99.87</div>
        </div>

        <table border="0" cellspacing="0" cellpadding="0" id="backinvoice">
            <thead>
            <tr>
                <th class="leftalign">Title</th>
                <th class="leftalign">Description</th>
                <th class="rightalign">Price</th>
                <th class="rightalign">Qty</th>
                <th class="leftalign">Date From</th>
                <th class="leftalign">Date To</th>
                <th class="rightalign">Total</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="leftalign">WT Premium - £5.99 - NO IPPHONE</td>
                <td class="leftalign">WT Premium - £5.99 - NO IPPHONE</td>
                <td class="rightalign">5.99</td>
                <td class="rightalign">12</td>
                <td class="leftalign">01-02-2017</td>
                <td class="leftalign">28-02-2017</td>
                <td class="rightalign">71.88</td>
            </tr>
            </tbody>
        </table>

        <div class="ChargesTitle clearfix">
            <div style="float:left;">Additional</div>
            <div style="text-align:right;float:right;">$32.00</div>
        </div>

        <table border="0" cellspacing="0" cellpadding="0" id="backinvoice">
            <thead>
            <tr>
                <th class="leftalign">Title</th>
                <th class="leftalign">Description</th>
                <th class="rightalign">Price</th>
                <th class="rightalign">Qty</th>
                <th class="leftalign">Date</th>
                <th class="rightalign">Total</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="leftalign">PBXSETUP</td>
                <td class="leftalign">SETUP COST PER USER</td>
                <td class="rightalign">10.00</td>
                <td class="rightalign">2</td>
                <td class="leftalign">28-02-2017</td>
                <td class="rightalign">20.00</td>
            </tr>
            </tbody>
        </table>
    </main>
    <div class="row">
        <div class="col-sm-12 invoice-footer">
            @yield('footerterms')
        </div>
    </div>

</div>
 @stop