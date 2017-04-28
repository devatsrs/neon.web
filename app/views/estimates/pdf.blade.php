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
            <h2 class="name">ESTIMATE FROM</h2>
            <div>{{ nl2br($EstimateTemplate->Header)}}</div>
        </div>
    </header>
    <!-- logo and estimate from section end-->

    <main>
            <div id="details" class="clearfix">
                <div id="client">
                    <div class="to">ESTIMATE TO:</div>
                    <div>{{nl2br($Estimate->Address)}}</div>
                </div>
                <div id="invoice">
                    <h1>Estimate No: {{$EstimateTemplate->EstimateNumberPrefix}}{{$Estimate->EstimateNumber}}</h1>
                    <div class="date">Estimate Date: {{ date($EstimateTemplate->DateFormat,strtotime($Estimate->IssueDate))}}</div>
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
                    <th class="desc">Title</th>
                    <th class="desc">Description</th>
                    <th class="desc">Quantity</th>
                    <th class="desc">Price</th>
                    <th class="total">Line Total</th>
                </tr>
                </thead>
                
                <tbody>
                @foreach($EstimateDetail as $ProductRow)
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
                        if($ProductRow->ProductType == Product::ITEM){
                            $grand_total_item += $ProductRow->LineTotal;
                        }elseif($ProductRow->ProductType == Product::SUBSCRIPTION){
                            $grand_total_subscription += $ProductRow->LineTotal;
                        }
                    ?>
                        {{--@if($ProductRow->ProductType == Product::ITEM)--}}
                            <tr>
                                <td class="desc">{{Product::getProductName($ProductRow->ProductID,$ProductRow->ProductType)}}</td>
                                <td class="desc">{{$ProductRow->Description}}</td>
                                <td class="desc">{{$ProductRow->Qty}}</td>
                                <td class="desc">{{number_format($ProductRow->Price,$RoundChargesAmount)}}</td>
                                <td class="total">{{number_format($ProductRow->LineTotal,$RoundChargesAmount)}}</td>
                            </tr>   
                        {{-- @endif --}}
                @endforeach
                </tbody>
                <tfoot>
                @if($grand_total_item > 0)
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="2">ONE OFF SUB TOTAL</td>
                        <td class="subtotal">{{$CurrencySymbol}}{{number_format($grand_total_item,$RoundChargesAmount)}}</td>
                    </tr>
                @endif
                @if($grand_total_subscription > 0)
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="2">RECURRING SUB TOTAL</td>
                        <td class="subtotal">{{$CurrencySymbol}}{{number_format($grand_total_subscription,$RoundChargesAmount)}}</td>
                    </tr>
                @endif
                @if(count($inlineTaxes) > 0)
                    @foreach($inlineTaxes as $index=>$value)
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">{{$taxes[$index]['Title']}}</td>
                            <td class="subtotal">{{$CurrencySymbol}}{{number_format($value,$RoundChargesAmount)}}</td>
                        </tr>
                    @endforeach
                @endif
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2">ESTIMATE TOTAL</td>
                    <td class="subtotal">{{$CurrencySymbol}}{{number_format($Estimate->SubTotal,$RoundChargesAmount)}}</td>
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
                    <td colspan="2">GRAND TOTAL</td>
                    <td class="subtotal">{{$CurrencySymbol}}{{number_format($Estimate->GrandTotal,$RoundChargesAmount)}}</td>
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