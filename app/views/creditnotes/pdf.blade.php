@extends('layout.print')

@section('content')
<link rel="stylesheet" type="text/css" href="<?php echo public_path("assets/css/invoicetemplate/invoicestyle.css"); ?>" />
<style type="text/css">
.invoice,
.invoice table,.invoice table td,.invoice table th,
.invoice ul li
{ font-size: 12px; }
#frontinvoice{margin-bottom:10px !important;  }
.EstiamteTotalTable{margin-bottom:0px !important;}
.EstiamteTotalTable tbody{font-size:2px !important;}

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
    <!-- logo and estimate from section start-->
    <header class="clearfix">
        <div id="logo">
            @if(!empty($logo))
                <img src="{{get_image_data($logo)}}" style="max-width: 250px">
            @endif
        </div>
        <div id="company">
            <h2 class="name"><b>Credit Note From</b></h2>
            <div>{{ nl2br($CreditNotesTemplate->Header)}}</div>
        </div>
    </header>
    <!-- logo and estimate from section end-->

    <main>
            <div id="details" class="clearfix">
                <div id="client">
                    <div class="to"><b>Credit Note To:</b></div>
                    <div>{{nl2br($CreditNotes->Address)}}</div>
                </div>
                <div id="invoice">
                    <h1>Credit Note No: {{$CreditNotesTemplate->CreditNotesNumberPrefix}}{{$CreditNotes->CreditNotesNumber}}</h1>
                    <div class="date">Credit Note Date: {{ date($CreditNotesTemplate->DateFormat,strtotime($CreditNotes->IssueDate))}}</div>
                    @if(!empty($MultiCurrencies))
                        @foreach($MultiCurrencies as $multiCurrency)
                            <div>Grand Total In {{$multiCurrency['Title']}} : {{$multiCurrency['Amount']}}</div>
                        @endforeach
                    @endif
                </div>
            </div>
            
            <!-- content of front page section start -->            
            <!--<div id="Service">
                <h1>Item</h1>
            </div>-->
            <div class="clearfix"></div>
          
                @if(count($CreditNotesDetailItems)>0)
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
                                <td class="rightalign">{{$ProductItemRow->Qty}}</td>
                                <td class="rightalign">{{number_format($ProductItemRow->Price,$RoundChargesAmount)}}</td>
                                <td class="total">{{number_format($ProductItemRow->LineTotal,$RoundChargesAmount)}}</td>
                            </tr>   
                @endforeach
                </tbody>
                <tfoot>      
                <?php $item_tax_total = 0; ?>
                 @if($grand_total_item > 0)
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="2">Sub Total</td>
                        <td class="subtotal">{{$CurrencySymbol}}{{number_format($grand_total_item,$RoundChargesAmount)}}</td>
                        <?php $item_tax_total = $grand_total_item; ?>
                    </tr>
                @endif
                @if(count($CreditNotesItemTaxRates) > 0) 
                    @foreach($CreditNotesItemTaxRates as $CreditNotesItemTaxRatesData)
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">{{$CreditNotesItemTaxRatesData->Title}}</td>
                            <td class="subtotal">{{$CurrencySymbol}}{{number_format($CreditNotesItemTaxRatesData->TaxAmount,$RoundChargesAmount)}}</td>
                        </tr> <?php $item_tax_total = $item_tax_total+$CreditNotesItemTaxRatesData->TaxAmount; ?>
                    @endforeach
                @endif  

                </tfoot>
                </table>
                @endif
                
                 @if(count($CreditNotesDetailItems)>0 && count($CreditNotesDetailISubscription)>0)                 
                 @endif
                 
                
                @if(count($CreditNotesDetailISubscription)>0)
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
                @foreach($CreditNotesDetailISubscription as $ProductRow)
                    <?php if(!isset($TaxrateName)){ $TaxrateName = TaxRate::getTaxName($ProductRow->TaxRateID); }
                        if ($ProductRow->TaxRateID!= 0) {
                            $tax = $taxes[$ProductRow->TaxRateID];
                            $amount = $tax['FlatStatus']==1?$tax['Amount']:(($ProductRow->LineTotal * $ProductRow->Qty * $tax['Amount'])/100 );
                            if(array_key_exists($ProductRow->TaxRateID, $inlineTaxes)){
                                $inlineTaxes[$ProductRow->TaxRateID] += $amount;
                            }else{
                                $inlineTaxes[$ProductRow->TaxRateID] = $amount;
                            }
                        }
                        if($ProductRow->TaxRateID2 != 0){
                            $tax = $taxes[$ProductRow->TaxRateID2];
                            $amount = $tax['FlatStatus']==1?$tax['Amount']:(($ProductRow->LineTotal * $ProductRow->Qty * $tax['Amount'])/100 );
                            if(array_key_exists($ProductRow->TaxRateID2, $inlineTaxes)){
                                $inlineTaxes[$ProductRow->TaxRateID2] += $amount;
                            }else{
                                $inlineTaxes[$ProductRow->TaxRateID2] = $amount;
                            }
                        }
                        $grand_total_subscription += $ProductRow->LineTotal;
                    ?>
                            <tr>
                                <td class="desc">{{Product::getProductName($ProductRow->ProductID,$ProductRow->ProductType)}}</td>
                                <td class="desc">{{nl2br($ProductRow->Description)}}</td>
                                <td class="rightalign">{{$ProductRow->Qty}}</td>
                                <td class="rightalign">{{number_format($ProductRow->Price,$RoundChargesAmount)}}</td>
                                <td class="total">{{number_format($ProductRow->LineTotal,$RoundChargesAmount)}}</td>
                            </tr>   
                @endforeach
                </tbody>
                <tfoot>   
                <?php  $subscription_tax_total = 0; ?>
                @if($grand_total_subscription > 0)
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="2">Sub Total</td>
                        <td class="subtotal">{{$CurrencySymbol}}{{number_format($grand_total_subscription,$RoundChargesAmount)}}</td>
                        <?php $subscription_tax_total = $grand_total_subscription; ?>
                    </tr>
                @endif    
                
                  @if(count($CreditNotesSubscriptionTaxRates) > 0)
                    @foreach($CreditNotesSubscriptionTaxRates as $CreditNotesSubscriptionTaxRatesData)
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">{{$CreditNotesSubscriptionTaxRatesData->Title}}</td>
                            <td class="subtotal">{{$CurrencySymbol}}{{number_format($CreditNotesSubscriptionTaxRatesData->TaxAmount,$RoundChargesAmount)}}</td>
                            <?php $subscription_tax_total = $subscription_tax_total+$CreditNotesSubscriptionTaxRatesData->TaxAmount; ?>
                        </tr>
                    @endforeach
                @endif

                </tfoot>
                 </table>
                @endif
                
                  <table class="EstiamteTotalTable" border="0" cellspacing="0" cellpadding="0" id="frontinvoice">
                  <thead>                                     
 		       </thead>
                 <tbody>
                 @foreach($CreditNotesDetail as $ProductRow)
                 <tr style="visibility:hidden;">
                    <td class="desc">{{Product::getProductName($ProductRow->ProductID,$ProductRow->ProductType)}}</td>
                    <td class="desc">{{$ProductRow->Description}}</td>
                    <td class="desc">{{$ProductRow->Qty}}</td>
                    <td class="desc">{{number_format($ProductRow->Price,$RoundChargesAmount)}}</td>
                    <td class="total">{{number_format($ProductRow->LineTotal,$RoundChargesAmount)}}</td>
                 </tr>   <?php break; ?>
                @endforeach
                 </tbody>
                <tfoot>                           

                
                @if(count($CreditNotesAllTaxRates))
                    @foreach($CreditNotesAllTaxRates as $CreditNotesTaxRate)
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">{{$CreditNotesTaxRate->Title}}</td>
                            <td class="subtotal">{{$CurrencySymbol}}{{number_format($CreditNotesTaxRate->TaxAmount,$RoundChargesAmount)}}</td>
                        </tr>
                    @endforeach
                @endif
                
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2"><b>Grand Total</b></td>
                    <td class="subtotal"><b>{{$CurrencySymbol}}{{number_format($CreditNotes->GrandTotal,$RoundChargesAmount)}}</b></td>
                </tr>
                
                </tfoot>
            </table>
            <!-- content of front page section end -->  
        </main> 
        
        <!-- adevrtisement and terms section start-->
        <div id="thanksadevertise">
            <div class="invoice-left">
                <p><a class="form-control" style="height: auto">{{nl2br($CreditNotes->Terms)}}</a></p>
            </div>
        </div>
        <!-- adevrtisement and terms section end -->
@stop