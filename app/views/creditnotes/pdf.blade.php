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

<?php
$RoundChargesAmount = get_round_decimal_places($Account->AccountID);
$total_tax_item = 0;
$total_tax_subscription = 0;
$grand_total_item = 0;
$grand_total_subscription = 0;
$inlineTaxes        =   [];
?>

<div class="inovicebody">
  <!-- logo and invoice from section start-->
  <header class="clearfix">
    <div id="logo" class="pull-left flip">
      @if(!empty($logo))
        <img src="{{get_image_data($logo)}}" style="max-width: 250px">
      @endif
    </div>
    <div id="company" class="pull-right flip">
        <h2 class="name text-right flip"><b>@lang('routes.CUST_PANEL_PAGE_CREDITNOTES_PDF_LBL_CREDITNOTES_FROM')</b></h2>
        <div class="text-right flip">{{ nl2br($CreditNotesTemplate->Header)}}</div>
    </div>
  </header>
  <!-- logo and invoice from section end-->

  <main>
    @if(isset($arrSignature["UseDigitalSignature"]) && $arrSignature["UseDigitalSignature"]==true)
      <img src="{{get_image_data($arrSignature['signaturePath'].$arrSignature['DigitalSignature']->image)}}" class="signatureImage" />
    @endif

            <div id="details" class="clearfix">
                <div id="client" class="pull-left flip">
                    <div class="to"><b>@lang('routes.CUST_PANEL_PAGE_CREDITNOTES_PDF_LBL_CREDITNOTES_TO')</b></div>
                    <div>{{nl2br($CreditNotes->Address)}}</div>
                </div>
                <div id="invoice" class="pull-right flip">
                    <h1 class="text-right flip">@lang('routes.CUST_PANEL_PAGE_CREDITNOTES_PDF_LBL_CREDITNOTES_NO'): {{$CreditNotesTemplate->CreditNotesNumberPrefix}}{{$CreditNotes->CreditNotesNumber}}</h1>
                    <div class="date text-right flip">@lang('routes.CUST_PANEL_PAGE_CREDITNOTES_PDF_LBL_CREDITNOTES_DATE'): {{ date($CreditNotesTemplate->DateFormat,strtotime($CreditNotes->IssueDate))}}</div>
                    @if(!empty($MultiCurrencies))
                        @foreach($MultiCurrencies as $multiCurrency)
                            <div class="text-right flip">@lang('routes.CUST_PANEL_PAGE_CREDITNOTES_PDF_TBL_GRAND_TOTAL_IN') {{$multiCurrency['Title']}} : {{$multiCurrency['Amount']}}</div>
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
                <th class="desc"><b>@lang('routes.CUST_PANEL_PAGE_CREDITNOTES_PDF_TBL_TITLE')</b></th>
                <th class="desc"><b>@lang('routes.CUST_PANEL_PAGE_CREDITNOTES_PDF_TBL_DESCRIPTION')</b></th>
                <th class="rightalign"><b>@lang('routes.CUST_PANEL_PAGE_CREDITNOTES_PDF_TBL_QUANTITY')</b></th>
                <th class="rightalign"><b>@lang('routes.CUST_PANEL_PAGE_CREDITNOTES_PDF_TBL_PRICE')</b></th>
                <th class="total"><b>@lang('routes.CUST_PANEL_PAGE_CREDITNOTES_PDF_TBL_LINE_TOTAL')</b></th>
            </tr>
        </thead>
        
        <tbody>
            @foreach($CreditNotesDetailItems as $ProductItemRow)
                <?php if(!isset($TaxrateName)){ $TaxrateName = TaxRate::getTaxName($ProductItemRow->TaxRateID); }
                    if ($ProductItemRow->TaxRateID!= 0) {
                        $tax = $taxes[$ProductItemRow->TaxRateID];
                        $amount = $tax['FlatStatus']==1?$tax['Amount']:(($ProductItemRow->LineTotal * $ProductItemRow->Qty * $tax['Amount'])/100 );
                        if(array_key_exists($ProductItemRow->TaxRateID, $inlineTaxes)){
                            $inlineTaxes[$ProductItemRow->TaxRateID] += $amount;
                        }else{
                            $inlineTaxes[$ProductItemRow->TaxRateID] = $amount;
                        }
                    }
                    if($ProductItemRow->TaxRateID2 != 0){
                        $tax = $taxes[$ProductItemRow->TaxRateID2];
                        $amount = $tax['FlatStatus']==1?$tax['Amount']:(($ProductItemRow->LineTotal * $ProductItemRow->Qty * $tax['Amount'])/100 );
                        if(array_key_exists($ProductItemRow->TaxRateID2, $inlineTaxes)){
                            $inlineTaxes[$ProductItemRow->TaxRateID2] += $amount;
                        }else{
                            $inlineTaxes[$ProductItemRow->TaxRateID2] = $amount;
                        }
                    }
                    $grand_total_item += $ProductItemRow->LineTotal;
                
                ?>
            <tr>
                <td class="desc">{{Product::getProductName($ProductItemRow->ProductID,$ProductItemRow->ProductType)}}</td>
                <td class="desc">{{nl2br($ProductItemRow->Description)}}</td>
                <td class="rightalign leftsideview">{{$ProductItemRow->Qty}}</td>
                <td class="rightalign leftsideview">{{number_format($ProductItemRow->Price,$RoundChargesAmount)}}</td>
                <td class="total leftsideview">{{number_format($ProductItemRow->LineTotal,$RoundChargesAmount)}}</td>
            </tr> 
            {{--@endif--}}
        @endforeach       
        </tbody>
        <tfoot>
            <?php $item_tax_total = 0; ?>
                 @if($grand_total_item > 0)
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="2">@lang('routes.CUST_PANEL_PAGE_CREDITNOTES_PDF_TBL_SUB_TOTAL')</td>
                        <td class="subtotal leftsideview">{{$CurrencySymbol}}{{number_format($grand_total_item,$RoundChargesAmount)}}</td>
                        <?php $item_tax_total = $grand_total_item; ?>
                    </tr>
                @endif

        
            @if(count($CreditNotesAllTaxRates))
                @foreach($CreditNotesAllTaxRates as $CreditNotesTaxRate)
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="2">{{$CreditNotesTaxRate->Title}}</td>
                        <td class="subtotal leftsideview">{{$CurrencySymbol}}{{number_format($CreditNotesTaxRate->TaxAmount,$RoundChargesAmount)}}</td>
                    </tr>
                @endforeach
            @endif
        
				@if(count($CreditNotesItemTaxRates) > 0) 
                    @foreach($CreditNotesItemTaxRates as $CreditNotesItemTaxRatesData)
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">{{$CreditNotesItemTaxRatesData->Title}}</td>
                            <td class="subtotal leftsideview">{{$CurrencySymbol}}{{number_format($CreditNotesItemTaxRatesData->TaxAmount,$RoundChargesAmount)}}</td>
                        </tr> <?php $item_tax_total = $item_tax_total+$CreditNotesItemTaxRatesData->TaxAmount; ?>
                    @endforeach
                @endif
				
				<tr>
                    <td colspan="2"></td>
                    <td colspan="2"><b>@lang('routes.CUST_PANEL_PAGE_CREDITNOTES_PDF_TBL_GRAND_TOTAL')</b></td>
                    <td class="subtotal"><b>{{$CurrencySymbol}}{{number_format($CreditNotes->GrandTotal,$RoundChargesAmount)}}</b></td>
                </tr>	
        
        </tfoot>
      </table>
      <!-- content of front page section end -->  
    </main> 
    
    <!-- adevrtisement and terms section start-->
    <div id="thanksadevertise">
      <div class="invoice-left">
        <p><a class="form-control pull-left" style="height: auto">{{nl2br($CreditNotes->Terms)}}</a></p>
      </div>
    </div>
    <!-- adevrtisement and terms section end -->
@stop