@extends('layout.print')

@section('content')
<style>
*{
    font-family: Arial;
    font-size: 10px;
    line-height: normal;
}
p{ line-height: 20px;}
.text-left{ text-align: left}
.text-right{ text-align: right}
.text-center{ text-align: center}
table.invoice th{ padding:3px; background-color: #f5f5f6}
.bg_graycolor{background-color: #f5f5f6}
table.invoice td , table.invoice_total td{ padding:3px;}
.page_break{page-break-after: always;}
@media print {
    * {
        background-color: auto !important;
        background: auto !important;
        color: auto !important;
    }
    th,td{ padding: 1px; margin: 1px;}
}
table{
  width: 100%;
  border-spacing: 0;
  margin-bottom: 0;
}

.gray_td{color:#4a4a4a;}

tr {
    page-break-inside: avoid;
}

thead {
    display: table-header-group
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
$inlineTaxes		=	[];
?>
<br/><br/><br/>
        <table border="0" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="col-md-6" valign="top">
                    @if(!empty($logo))
                   <img src="{{get_image_data($logo)}}" style="max-width: 250px">
                   @endif
                </td>
                <td class="col-md-6 text-right" valign="top">
                    <br>
                   <strong>Estimate From:</strong>
                   <p><strong>{{ nl2br($EstimateTemplate->Header)}}</strong></p>

                </td>
            </tr>
        </table>
        <br />

        <table border="0" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="col-md-6"  valign="top" >
                        <br>
                        <strong>Estimate To</strong>
                        <p>{{$Account->AccountName}}</p>
                        <p>{{nl2br($Estimate->Address)}}</p>
                </td>
                <td class="col-md-6 text-right"  valign="top" >
                        <p><b>Estimate No: </b>{{$EstimateTemplate->EstimateNumberPrefix}}{{$Estimate->EstimateNumber}}</p>
                        <p><b>Estimate Date: </b>{{ date($EstimateTemplate->DateFormat,strtotime($Estimate->IssueDate))}}</p>                       
                </td>
            </tr>
        </table>
     <br />
    <h5>Items</h5>
    <table border="1"  width="100%" cellpadding="0" cellspacing="0" class="invoice col-md-12 table table-bordered">
            <thead>
            <tr>
                <th style="text-align: center;">Title</th>
                <th style="text-align: left;">Description</th>
                <th style="text-align: center;">Quantity</th>
                <th style="text-align: center;">Price</th>
                @if($Estimate->TotalDiscount >0)
                <th style="text-align: center;">Discount</th>
                @endif
                <th style="text-align: center;">Line Total</th>
                {{--<th style="text-align: center;">Tax</th>--}}
               {{--<th class="hidden" style="text-align: center;">Tax Amount</th> --}}
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
              }elseif($ProductRow->TaxRateID2 != 0 && array_key_exists($ProductRow->TaxRateID2, $inlineTaxes)){
                  $tax = $taxes[$ProductRow->TaxRateID2];
                  $amount = $tax['FlatStatus']==1?$tax['Amount']:(($ProductRow->LineTotal * $ProductRow->Qty * $tax['Amount'])/100 );
                  if($ProductRow->TaxRateID2!=0){
                      $inlineTaxes[$ProductRow->TaxRateID] += $amount;
                  }else{
                      $inlineTaxes[$ProductRow->TaxRateID] = $amount;
                  }
              }
              if($ProductRow->ProductType == Product::ITEM){
                  $grand_total_item += number_format($ProductRow->LineTotal,$RoundChargesAmount);
              }elseif($ProductRow->ProductType == Product::SUBSCRIPTION){
                  $grand_total_subscription += number_format($ProductRow->LineTotal,$RoundChargesAmount);
              }
              ?>
            <!--if($ProductRow->ProductType == Product::ITEM)-->
            <tr>
                <td class="text-center">{{Product::getProductName($ProductRow->ProductID,$ProductRow->ProductType)}}</td>
                <td class="text-left">{{$ProductRow->Description}}</td>
                <td class="text-center">{{$ProductRow->Qty}}</td>
                <td class="text-center">{{number_format($ProductRow->Price,$RoundChargesAmount)}}</td>
                @if($Estimate->TotalDiscount >0)
                <td class="text-center">{{$ProductRow->Discount}}</td>
                @endif
                <td class="text-center">{{number_format($ProductRow->LineTotal,$RoundChargesAmount)}}</td>
                {{--<td class="text-center">{{TaxRate::getTaxRate($ProductRow->TaxRateID)}}</td>--}}
                {{--<td class="hidden" class="text-center">{{number_format($ProductRow->TaxAmount,$RoundChargesAmount)}}</td>--}}
            </tr>
            <!--endif-->
            @endforeach
        </tbody>
    </table>
    <br /><br /><br />

    <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="table-responsive">
                                <table border="0" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="col-md-5" valign="top" width="60%">
                                                <p><a class="form-control" style="height: auto">{{nl2br($Estimate->Terms)}}</a></p>
                                        </td>
                                        <td class="col-md-6"  valign="top" width="35%" >
                                                <table  border="1"  width="100%" cellpadding="0" cellspacing="0" class="bg_graycolor invoice_total col-md-12 table table-bordered">
                                                    <tfoot>
                                                        @if($grand_total_item > 0)
                                                            <tr>
                                                                <td class="text-right"><strong>One off Sub Total:</strong></td>
                                                                <td class="text-right">{{$CurrencySymbol}}{{number_format($grand_total_item,$RoundChargesAmount)}}</td>
                                                            </tr>
                                                        @endif
                                                        @if($grand_total_subscription > 0)
                                                            <tr>
                                                                <td class="text-right"><strong>Recurring Sub Total:</strong></td>
                                                                <td class="text-right">{{$CurrencySymbol}}{{number_format($grand_total_subscription,$RoundChargesAmount)}}</td>
                                                            </tr>
                                                        @endif
                                                        @if(count($inlineTaxes) > 0)
                                                            @foreach($inlineTaxes as $index=>$value)
                                                                <tr>
                                                                    <td class="text-right"><strong>{{$taxes[$index]['Title']}}</strong></td>
                                                                    <td class="text-right">{{$CurrencySymbol}}{{number_format($value,$RoundChargesAmount)}}</td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                        <tr>
                                                                <td class="text-right"><strong>Estimate Total :</strong></td>
                                                                <td class="text-right">{{$CurrencySymbol}}{{number_format($Estimate->SubTotal,$RoundChargesAmount)}}</td>
                                                        </tr>
                                                        @if($Estimate->TotalDiscount >0)
                                                        <tr>
                                                                <td class="text-right"><strong>Discount</strong></td>
                                                                <td class="text-right">{{$CurrencySymbol}}{{number_format($Estimate->TotalDiscount,$RoundChargesAmount)}}</td>
                                                        </tr>
                                                        @endif                                                        
                                                            @if(count($EstimateAllTaxRates))
                                                            @foreach($EstimateAllTaxRates as $EstimateTaxRate)
                                                                <tr>
                                                                    <td class="text-right"><strong>{{$EstimateTaxRate->Title}}</strong></td>
                                                                    <td class="text-right">{{$CurrencySymbol}}{{number_format($EstimateTaxRate->TaxAmount,$RoundChargesAmount)}}</td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                        
                                                        <tr>
                                                                <td class="text-right"><strong>Grand Total:</strong></td>
                                                                <td class="text-right">{{$CurrencySymbol}}{{number_format($Estimate->GrandTotal,$RoundChargesAmount)}} </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                        </td>
                                    </tr>
                                </table>
                                </br>
                                </br>
                                </br>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <br />

 @stop