@extends('layout.print')

@section('content')
<link rel="stylesheet" type="text/css" href="<?php echo public_path("assets/css/invoicetemplate/invoicestyle.css"); ?>" />
@if(isset($language->is_rtl) && $language->is_rtl=="Y")
  <link rel="stylesheet" type="text/css" href="<?php echo public_path("assets/css/bootstrap-rtl.min.css"); ?>" />
  <style type="text/css">
    .leftsideview{
      direction: ltr;
    }
    #details{
      border-right: 3px solid #000000;
      padding-right: 6px;
      padding-left: 0px;
      border-left: 0px;
    }
  </style>
@endif
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
tr {
  page-break-inside: avoid;
}

thead {
  display: table-row-group
}

tfoot {
  display: table-row-group
}
@if(isset($arrSignature["UseDigitalSignature"]) && $arrSignature["UseDigitalSignature"]==true)
  img.signatureImage {
    position: absolute;
    z-index: 99999;
    top: {{isset($arrSignature["DigitalSignature"]->positionTop)?$arrSignature["DigitalSignature"]->positionTop:0}}px;
    left: {{isset($arrSignature["DigitalSignature"]->positionLeft)?$arrSignature["DigitalSignature"]->positionLeft:0}}px;
  }
@endif

</style>

<div class="inovicebody">
  <!-- logo and invoice from section start-->
  <header class="clearfix">
    <div id="logo" class="pull-left flip">
      @if(!empty($logo))
        <img src="{{get_image_data($logo)}}" style="max-width: 250px">
      @endif
    </div>
    <div id="company" class="pull-right flip">
      @if($print_type == "Proforma")
      <h2 class="name text-right flip"><b>@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_LBL_PROFORMA_FROM')</b></h2>
      @elseif($print_type == "Credit Note")
        <h2 class="name text-right flip"><b>@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_LBL_CREDIT_NOTE_FROM')</b></h2>
      @else
        <h2 class="name text-right flip"><b>@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_LBL_INVOICE_FROM')</b></h2>
      @endif
      <div class="text-right flip">{{ nl2br(Account::getAddress($Account)) }}</div>
    </div>
  </header>
  <!-- logo and invoice from section end-->

  <main>
    @if(isset($arrSignature["UseDigitalSignature"]) && $arrSignature["UseDigitalSignature"]==true)
      <img src="{{get_image_data($arrSignature['signaturePath'].$arrSignature['DigitalSignature']->image)}}" class="signatureImage" />
    @endif

      <div id="details" class="clearfix">
        <div id="client" class="pull-left flip">

          @if($print_type == "Proforma")
            <div class="to"><b>@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_LBL_PROFORMA_TO')</b></div>
          @elseif($print_type == "Credit Note")
            <div class="to"><b>@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_LBL_CREDIT_NOTE_TO')</b></div>
          @else
            <div class="to"><b>@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_LBL_INVOICE_TO')</b></div>
          @endif
          <div>{{nl2br($Invoice->Address)}}</div>
        </div>
        <div id="invoice" class="pull-right flip">
          @if($print_type == "Proforma")
            <h3 class="text-right flip">@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_LBL_PROFORMA_NO') {{$Invoice->FullInvoiceNumber}}</h3>
          @elseif($print_type == "Credit Note")
            <h3 class="text-right flip">@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_LBL_CREDIT_NOTE_NO') {{$Invoice->FullInvoiceNumber}}</h3>
          @else
            <h1 class="text-right flip">@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_LBL_INVOICE_NO') {{$Invoice->FullInvoiceNumber}}</h1>
          @endif
          <div class="date text-right flip">@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_LBL_INVOICE_DATE') {{ date(invoice_date_fomat($Reseller->InvoiceDateFormat),strtotime($Invoice->IssueDate))}}</div>
          <div class="date text-right flip">@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_LBL_DUE_DATE') {{date(invoice_date_fomat($Reseller->InvoiceDateFormat),strtotime($Invoice->IssueDate.' +'.$PaymentDueInDays.' days'))}}</div>
          @if(!empty($MultiCurrencies))
            @foreach($MultiCurrencies as $multiCurrency)
              <div class="text-right flip">@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_TBL_GRAND_TOTAL_IN') {{$multiCurrency['Title']}} : {{$multiCurrency['Amount']}}</div>
            @endforeach
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
          <th class="desc"><b>@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_TBL_TITLE')</b></th>
          <th class="desc"><b>@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_TBL_DESCRIPTION')</b></th>
          <th class="rightalign"><b>@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_TBL_QUANTITY')</b></th>
          <th class="rightalign"><b>@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_TBL_PRICE')</b></th>
          <th class="rightalign"><b>@lang("routes.CUST_PANEL_PAGE_INVOICE_PDF_TBL_DISCOUNT")</b></th>
          <th class="total"><b>@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_TBL_LINE_TOTAL')</b></th>
        </tr>
        </thead>

        <tbody>
        @foreach($InvoiceDetail as $ProductRow)
          <?php if(!isset($TaxrateName)){ $TaxrateName = TaxRate::getTaxName($ProductRow->TaxRateID); } ?>
            {{---@if($ProductRow->ProductType == Product::ITEM)--}}
              <tr>
                <td class="desc">{{Product::getProductName($ProductRow->ProductID,$ProductRow->ProductType)}}</td>
                <td class="desc">{{nl2br($ProductRow->Description)}}</td>
                <td class="rightalign leftsideview">{{$ProductRow->Qty}}</td>
                <td class="rightalign leftsideview">{{number_format($ProductRow->Price,$RoundChargesAmount)}}</td>
                <td class="rightalign leftsideview">
                  @if(!empty($ProductRow->DiscountAmount) && !empty($ProductRow->DiscountType))
                    {{number_format($ProductRow->DiscountAmount,$RoundChargesAmount)}}@if($ProductRow->DiscountType=='Percentage') % @endif
                  @endif
                </td>
                <td class="total leftsideview">{{number_format($ProductRow->LineTotal,$RoundChargesAmount)}}</td>
              </tr>
            {{--@endif--}}
        @endforeach
        </tbody>
        <tfoot>
        <tr>
          <td colspan="3"></td>
          <td colspan="2">@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_TBL_SUB_TOTAL')</td>
          <td class="subtotal leftsideview">{{$CurrencySymbol}}{{number_format($Invoice->SubTotal,$RoundChargesAmount)}}</td>
        </tr>

        @if(count($InvoiceAllTaxRates))
          @foreach($InvoiceAllTaxRates as $InvoiceTaxRate)
            <tr>
              <td colspan="3"></td>
              <td colspan="2">{{$InvoiceTaxRate->Title}}</td>
              <td class="subtotal leftsideview">{{$CurrencySymbol}}{{number_format($InvoiceTaxRate->TaxAmount,$RoundChargesAmount)}}</td>
            </tr>
          @endforeach
                @endif

        <tr>
          <td colspan="3"></td>
          <td colspan="2"><b>@lang('routes.CUST_PANEL_PAGE_INVOICE_PDF_TBL_GRAND_TOTAL')</b></td>
          <td class="subtotal leftsideview"><b>{{$CurrencySymbol}}{{number_format($Invoice->GrandTotal,$RoundChargesAmount)}}</b></td>
        </tr>

        </tfoot>
      </table>
      <!-- content of front page section end -->
    </main>

    <!-- adevrtisement and terms section start-->
    <div id="thanksadevertise">
      <div class="invoice-left">
        <p><a class="form-control pull-left" style="height: auto">{{nl2br($Invoice->Terms)}}</a></p>
      </div>
    </div>
    <!-- adevrtisement and terms section end -->
@stop