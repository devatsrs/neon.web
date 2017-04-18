@extends('layout.print')

@include('invoicetemplates.itemhtml')
@section('content')
<link rel="stylesheet" type="text/css" href="<?php echo URL::to('/'); ?>/assets/css/invoicetemplate/invoicestyle.css" />
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
					<div class="to">INVOICE TO:</div>
					<div>{{nl2br(Invoice::getInvoiceTo($InvoiceTemplate->InvoiceTo))}}</div>
					<!--<h2 class="name">Bhavin Prajapati</h2>
					<div class="address">Rajkot</div>
					<div class="address">Rajkot - 360003</div>
					<div class="address">Gujarat, India</div>
					<div class="email"><a href="mailto:john@example.com">john@example.com</a></div>-->
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
			
			<!-- content of front page section start -->			
			<div id="Service">
				<h1>Item</h1>
			</div>
			<div class="clearfix"></div>
			<table border="0" cellspacing="0" cellpadding="0" id="frontinvoice">
				<thead>
				<tr>
					<th class="desc">Title</th>
					<th class="desc">Description</th>
					<th class="desc">Quantity</th>
					<th class="desc">Price</th>
					<th class="desc">Line Total</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td class="desc">Item 1</td>
					<td class="desc">Item Description</td>
					<td class="desc">2</td>
					<td class="desc">25</td>
					<td class="desc">50</td>
				</tr>
				<tr>
					<td class="desc">Item 1</td>
					<td class="desc">Item Description</td>
					<td class="desc">2</td>
					<td class="desc">25</td>
					<td class="desc">50</td>
				</tr>
				</tbody>
				<tfoot>
				<tr>
					<td colspan="2"></td>
					<td colspan="2">SUB TOTAL</td>
					<td class="subtotal">$5,200.00</td>
				</tr>
				<tr>
					<td colspan="2"></td>
					<td colspan="2">TAX 25%</td>
					<td class="subtotal">$1,300.00</td>
				</tr>
				@if($InvoiceTemplate->ShowPrevBal)
					<tr>
						<td colspan="2"></td>
						<td colspan="2">BROUGHT FORWARD</td>
						<td class="subtotal">$0.00</td>
					</tr>
				@endif
				<tr>
					<td colspan="2"></td>
					<td colspan="2">GRAND TOTAL</td>
					<td class="subtotal">$6,500.00</td>
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