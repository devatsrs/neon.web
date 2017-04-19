@extends('layout.blank')
@include('invoicetemplates.itemhtml')
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
			<div id="client">
				<div class="to">INVOICE TO:</div>
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
				<th class="total">Line Total</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td class="desc">Item 1</td>
				<td class="desc">Item Description</td>
				<td class="desc">2</td>
				<td class="desc">25</td>
				<td class="total">50</td>
			</tr>
			<tr>
				<td class="desc">Item 2</td>
				<td class="desc">Item Description</td>
				<td class="desc">2</td>
				<td class="desc">25</td>
				<td class="total">50</td>
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
	</main>
	<div id="thanksadevertise">
		<div class="invoice-left">
			@yield('terms')
		</div>
	</div>
    <br/>
    <br/>
    <header class="clearfix">        
    </header>    
    <div class="row">
        <div class="col-sm-12 invoice-footer">
            @yield('footerterms')
        </div>
    </div>

</div>
 @stop