@extends('layout.print')

@include('invoicetemplates.itemhtml')
@section('content')
<link rel="stylesheet" type="text/css" href="<?php echo public_path('assets/css/invoicetemplate/invoicestyle.css'); ?>" />
<style type="text/css">
.invoice,
.invoice table,.invoice table td,.invoice table th,
.invoice ul li
{ font-size: 12px; }

#pdf_header, #pdf_footer{
    /*position: fixed;*/
}

@media print {
    .page_break{page-break-after: always;}

}
.page_break{page-break-after: always;}
</style>
    
	<div class="inovicebody">
		<!-- logo and invoice from section start-->
		<header class="clearfix">
			@yield('logo')
		</header>
		<!-- logo and invoice from section end-->
		<main>
			<div id="details" class="clearfix">
				<div id="client">
					<div class="to"><b>Invoice To:</b></div>
					<div>{{nl2br(Invoice::getInvoiceTo($InvoiceTemplate->InvoiceTo))}}</div>
					<!--<h2 class="name">Bhavin Prajapati</h2>
					<div class="address">Rajkot</div>
					<div class="address">Rajkot - 360003</div>
					<div class="address">Gujarat, India</div>
					<div class="email"><a href="mailto:john@example.com">john@example.com</a></div>-->
				</div>
				<div id="invoice">
					<h1>Invoice No: {{$InvoiceTemplate->InvoiceNumberPrefix.$InvoiceTemplate->InvoiceStartNumber}}</h1>
					<div class="date">Invoice Date: {{date($InvoiceTemplate->DateFormat)}}</div>
					<div class="date">Due Date: {{date($InvoiceTemplate->DateFormat,strtotime('+5 days'))}}</div>
					@if($InvoiceTemplate->ShowBillingPeriod == 1)
						<div class="date">Invoice Period: {{date($InvoiceTemplate->DateFormat,strtotime('-7 days'))}} - {{date($InvoiceTemplate->DateFormat)}}</div>
					@endif
				</div>
			</div>
			
			<!-- content of front page section start -->			
			<!--<div id="Service">
				<h1>Item</h1>
			</div>-->
			<div class="clearfix"></div>
			<table border="0" cellspacing="0" cellpadding="0" id="frontinvoice">
				<thead>
				<tr>
					<th class="desc"><b>Title</b></th>
					<th class="desc"><b>Description</b></th>
					<th class="rightalign"><b>Quantity</b></th>
					<th class="rightalign"><b>Price</b></th>
					<th class="total"><b>Line Total</b></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td class="desc">Item Testing 1</td>
					<td class="desc">Item Testing Description</td>
					<td class="rightalign">2</td>
					<td class="rightalign">25</td>
					<td class="total">50</td>
				</tr>
				<tr>
					<td class="desc">Item Testing 1</td>
					<td class="desc">Item Testing Description</td>
					<td class="rightalign">2</td>
					<td class="rightalign">25</td>
					<td class="total">50</td>
				</tr>
				</tbody>
				<tfoot>
				<tr>
					<td colspan="2"></td>
					<td colspan="2">Sub Total</td>
					<td class="subtotal">$5,200.00</td>
				</tr>
				<tr>
					<td colspan="2"></td>
					<td colspan="2">Tax 25%</td>
					<td class="subtotal">$1,300.00</td>
				</tr>
				@if($InvoiceTemplate->ShowPrevBal)
					<tr>
						<td colspan="2"></td>
						<td colspan="2">Brought Forward</td>
						<td class="subtotal">$0.00</td>
					</tr>
				@endif
				<tr>
					<td colspan="2"></td>
					<td colspan="2"><b>Grand Total</b></td>
					<td class="subtotal"><b>$6,500.00</b></td>
				</tr>
				</tfoot>
			</table>
			<!-- content of front page section end -->	
		</main>
		<!-- adevrtisement and terms section start-->
		<div id="thanksadevertise">
			<div class="invoice-left">
				@yield('terms')
			</div>
		</div>
		<!-- adevrtisement and terms section end -->

 @stop