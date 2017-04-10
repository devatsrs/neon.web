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
        <!--
		<div id="logo">
			<img src="{{$logo}}" alt="Company Logo" title="Company Logo" style="max-width: 250px">        
		</div>
		<div style="float:right;display:none;">
			<p> {{ Form::select('InvoiceFromInfo', Invoice::$invoice_company_info, (!empty(Input::get('InvoiceFromInfo'))?explode(',',Input::get('InvoiceFromInfo')):[]), array("class"=>"select2","multiple","data-allow-clear"=>"true","data-placeholder"=>"Select Company Info")) }} </p>
		</div>
		<div id="company">
			<h2 class="name">INVOICE FROM</h2><br>
			<div>Wavetel Limited</div>
			<div>88-90 Goodmayes Road,Goodmayes</div>
			<div>Essex</div>
			<div>IG3 9UU</div>
			<div>VAT: 161 0708 39</div>
			<div><a href="mailto:company@example.com">company@example.com</a></div>
		</div>-->
	</header>
	<main>
		<div id="details" class="clearfix">
			<div style="float:left;display:none;">
				<p> {{ Form::select('InvoiceToInfo', Invoice::$invoice_account_info, (!empty(Input::get('InvoiceToInfo'))?explode(',',Input::get('InvoiceFromInfo')):[]), array("class"=>"select2","multiple","data-allow-clear"=>"true","data-placeholder"=>"Select Account Info")) }} </p>
			</div>
			<div id="client">
				<div class="to">INVOICE TO:</div>
                <p>{{nl2br($InvoiceTemplate->InvoiceTo)}}</p>
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
				<th class="desc">Tax Amount</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td class="desc">Item 1</td>
				<td class="desc">Item Description</td>
				<td class="desc">2</td>
				<td class="desc">25</td>
				<td class="desc">50</td>
				<td class="desc">10</td>
			</tr>
			<tr>
				<td class="desc">Item 2</td>
				<td class="desc">Item Description</td>
				<td class="desc">2</td>
				<td class="desc">25</td>
				<td class="desc">50</td>
				<td class="desc">10</td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="3"></td>
				<td colspan="2">SUB TOTAL</td>
				<td class="subtotal">$5,200.00</td>
			</tr>
			<tr>
				<td colspan="3"></td>
				<td colspan="2">TAX 25%</td>
				<td class="subtotal">$1,300.00</td>
			</tr>
			@if($InvoiceTemplate->ShowPrevBal)
                <tr>
                    <td colspan="3"></td>
                    <td colspan="2">BROUGHT FORWARD</td>
                    <td class="subtotal">$0.00</td>
                </tr>
            @endif
			<tr>
				<td colspan="3"></td>
				<td colspan="2">GRAND TOTAL</td>
				<td class="subtotal">$6,500.00</td>
			</tr>
			</tfoot>
		</table>
	</main>
	<div id="thanksadevertise">
		<div class="invoice-left">
			</br>
			</br>
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