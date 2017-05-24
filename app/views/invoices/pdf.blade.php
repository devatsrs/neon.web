@extends('layout.print')

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

<?php
$RoundChargesAmount = get_round_decimal_places($Account->AccountID);
?>

<div class="inovicebody">
  <!-- logo and invoice from section start-->
  <header class="clearfix">
    <div id="logo">
      @if(!empty($logo))
        <img src="{{get_image_data($logo)}}" style="max-width: 250px">
      @endif
    </div>
    <div id="company">
      <h2 class="name"><b>Invoice From</b></h2>
      <div>{{ nl2br($InvoiceTemplate->Header)}}</div>
    </div>
  </header>
  <!-- logo and invoice from section end-->

  <main>
      <div id="details" class="clearfix">
        <div id="client">
          <div class="to"><b>Invoice To:</b></div>
          <div>{{nl2br($Invoice->Address)}}</div>
        </div>
        <div id="invoice">
          <h1>Invoice No: {{$Invoice->FullInvoiceNumber}}</h1>
          <div class="date">Invoice Date: {{ date($InvoiceTemplate->DateFormat,strtotime($Invoice->IssueDate))}}</div>
          <div class="date">Due Date: {{date('d-m-Y',strtotime($Invoice->IssueDate.' +'.$PaymentDueInDays.' days'))}}</div>         
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
        @foreach($InvoiceDetail as $ProductRow)
          <?php if(!isset($TaxrateName)){ $TaxrateName = TaxRate::getTaxName($ProductRow->TaxRateID); } ?>
            {{---@if($ProductRow->ProductType == Product::ITEM)--}}
              <tr>
                <td class="desc">{{Product::getProductName($ProductRow->ProductID,$ProductRow->ProductType)}}</td>
                <td class="desc">{{nl2br($ProductRow->Description)}}</td>
                <td class="rightalign">{{$ProductRow->Qty}}</td>
                <td class="rightalign">{{number_format($ProductRow->Price,$RoundChargesAmount)}}</td>
                <td class="total">{{number_format($ProductRow->LineTotal,$RoundChargesAmount)}}</td>
              </tr> 
            {{--@endif--}}
        @endforeach       
        </tbody>
        <tfoot>
        <tr>
          <td colspan="2"></td>
          <td colspan="2">Sub Total</td>
          <td class="subtotal">{{$CurrencySymbol}}{{number_format($Invoice->SubTotal,$RoundChargesAmount)}}</td>
        </tr>
        
        @if(count($InvoiceAllTaxRates))
          @foreach($InvoiceAllTaxRates as $InvoiceTaxRate)
            <tr>
              <td colspan="2"></td>
              <td colspan="2">{{$InvoiceTaxRate->Title}}</td>
              <td class="subtotal">{{$CurrencySymbol}}{{number_format($InvoiceTaxRate->TaxAmount,$RoundChargesAmount)}}</td>
            </tr>
          @endforeach
                @endif
        
        <tr>
          <td colspan="2"></td>
          <td colspan="2"><b>Grand Total</b></td>
          <td class="subtotal"><b>{{$CurrencySymbol}}{{number_format($Invoice->GrandTotal,$RoundChargesAmount)}}</b></td>
        </tr>
        
        </tfoot>
      </table>
      <!-- content of front page section end -->  
    </main> 
    
    <!-- adevrtisement and terms section start-->
    <div id="thanksadevertise">
      <div class="invoice-left">
        <p><a class="form-control" style="height: auto">{{nl2br($Invoice->Terms)}}</a></p>
      </div>
    </div>
    <!-- adevrtisement and terms section end -->
@stop