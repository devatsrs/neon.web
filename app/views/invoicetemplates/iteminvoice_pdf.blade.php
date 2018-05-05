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
				<td class="desc">Item Testing 2</td>
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