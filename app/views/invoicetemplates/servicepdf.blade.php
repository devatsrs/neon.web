@extends('layout.print')

@include('invoicetemplates.servicehtml')
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
    * {
        background-color: auto !important;
        background: auto !important;
        color: auto !important;
    }
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
					<div>{{nl2br($InvoiceTemplate->InvoiceTo)}}</div>
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
			
			<table border="0" cellspacing="0" cellpadding="0" id="frontinvoice">
				<thead>
					<tr>
						<th class="desc">DESCRIPTION</th>
						<th class="desc">Usage</th>
						<th class="desc">Recurring</th>
						<th class="desc">Additional</th>
						<th class="total">TOTAL</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="desc">Service - 1</td>
						<td class="desc">$1,200.00</td>
						<td class="desc">$1,000.00</td>
						<td class="desc">$1,000.00</td>
						<td class="total">$3,200.00</td>
					</tr>
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
		
		<!-- service section start -->		
		<div class="page_break"> </div>
        <br/>        
		<header class="clearfix">
			<div id="Service">
				<h1>Service 1</h1>
			</div>
		</header>
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
		
		<!-- service section end -->
	

 @stop