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
            <h2 class="name"><b>Estimate From</b></h2>
            <div>{{ nl2br($EstimateTemplate->Header)}}</div>
        </div>
    </header>
    <!-- logo and estimate from section end-->

    <main>
            <div id="details" class="clearfix">
                <div id="client">
                    <div class="to"><b>Estimate To:</b></div>
                    <div>{{nl2br($Estimate->Address)}}</div>
                </div>
                <div id="invoice">
                    <h1>Estimate No: {{$EstimateTemplate->EstimateNumberPrefix}}{{$Estimate->EstimateNumber}}</h1>
                    <div class="date">Estimate Date: {{ date($EstimateTemplate->DateFormat,strtotime($Estimate->IssueDate))}}</div>
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
          
                @if(count($EstimateDetailItems)>0)
                  <table border="0" cellspacing="0" cellpadding="0" id="frontinvoice">
                <thead>
                <tr>
                    <th colspan="5" class="desc"><b>ONE OFF Fees</b></th>
                </tr>
                <tr>
                    <th class="desc"><b>Title</b></th>
                    <th class="desc"><b>Description</b></th>
                    <th class="rightalign"><b>Quantity</b></th>
                    <th class="rightalign"><b>Price</b></th>
                    <th class="total"><b>Line Total</b></th>
                </tr>
                </thead>
                
                <tbody>
                @foreach($EstimateDetailItems as $ProductItemRow)
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
                @if(count($EstimateItemTaxRates) > 0) 
                    @foreach($EstimateItemTaxRates as $EstimateItemTaxRatesData)
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">{{$EstimateItemTaxRatesData->Title}}</td>
                            <td class="subtotal">{{$CurrencySymbol}}{{number_format($EstimateItemTaxRatesData->TaxAmount,$RoundChargesAmount)}}</td>
                        </tr> <?php $item_tax_total = $item_tax_total+$EstimateItemTaxRatesData->TaxAmount; ?>
                    @endforeach
                @endif  
                @if($item_tax_total>0)
               		 <tr>
                            <td colspan="2"></td>
                            <td colspan="2"><strong>ONE OFF TOTAL</strong></td>
                            <td class="subtotal">{{$CurrencySymbol}}{{number_format($item_tax_total,$RoundChargesAmount)}}</td>
                        </tr>
                @endif  
                </tfoot>
                </table>
                @endif
                
                 @if(count($EstimateDetailItems)>0 && count($EstimateDetailISubscription)>0)                 
                 @endif
                 
                
                @if(count($EstimateDetailISubscription)>0)
                  <table border="0" cellspacing="0" cellpadding="0" id="frontinvoice">
                <thead>
                <tr>
                    <th colspan="5" class="desc"><b>RECURRING Fees</b></th>
                </tr>
                <tr>
                    <th class="desc"><b>Title</b></th>
                    <th class="desc"><b>Description</b></th>
                    <th class="rightalign"><b>Quantity</b></th>
                    <th class="rightalign"><b>Price</b></th>
                    <th class="total"><b>Line Total</b></th>
                </tr>
                </thead>
                
                <tbody>
                @foreach($EstimateDetailISubscription as $ProductRow)
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
                
                  @if(count($EstimateSubscriptionTaxRates) > 0)
                    @foreach($EstimateSubscriptionTaxRates as $EstimateSubscriptionTaxRatesData)
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">{{$EstimateSubscriptionTaxRatesData->Title}}</td>
                            <td class="subtotal">{{$CurrencySymbol}}{{number_format($EstimateSubscriptionTaxRatesData->TaxAmount,$RoundChargesAmount)}}</td>
                            <?php $subscription_tax_total = $subscription_tax_total+$EstimateSubscriptionTaxRatesData->TaxAmount; ?>
                        </tr>
                    @endforeach
                @endif
                
                @if($subscription_tax_total>0)
               		 <tr>
                            <td colspan="2"></td>
                            <td colspan="2"><strong>Recurring Total	</strong></td>
                            <td class="subtotal">{{$CurrencySymbol}}{{number_format($subscription_tax_total,$RoundChargesAmount)}}</td>
                        </tr>
                @endif  
                </tfoot>
                 </table>
                @endif
                
                  <table class="EstiamteTotalTable" border="0" cellspacing="0" cellpadding="0" id="frontinvoice">
                  <thead>                                     
 		       </thead>
                 <tbody>
                 @foreach($EstimateDetail as $ProductRow)
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
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2">Estimate Total</td>
                    <td class="subtotal">{{$CurrencySymbol}}{{number_format($Estimate->EstimateTotal,$RoundChargesAmount)}}</td>
                </tr>
                
                @if(count($EstimateAllTaxRates))
                    @foreach($EstimateAllTaxRates as $EstimateTaxRate)
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">{{$EstimateTaxRate->Title}}</td>
                            <td class="subtotal">{{$CurrencySymbol}}{{number_format($EstimateTaxRate->TaxAmount,$RoundChargesAmount)}}</td>
                        </tr>
                    @endforeach
                @endif
                
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2"><b>Grand Total</b></td>
                    <td class="subtotal"><b>{{$CurrencySymbol}}{{number_format($Estimate->GrandTotal,$RoundChargesAmount)}}</b></td>
                </tr>
                
                </tfoot>
            </table>
            <!-- content of front page section end -->  
        </main> 
        
        <!-- adevrtisement and terms section start-->
        <div id="thanksadevertise">
            <div class="invoice-left">
                <p><a class="form-control" style="height: auto">{{nl2br($Estimate->Terms)}}</a></p>
            </div>
        </div>
        <!-- adevrtisement and terms section end -->
@stop