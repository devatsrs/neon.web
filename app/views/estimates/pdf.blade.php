@extends('layout.print')

@section('content')
<style>
*{
    font-family: Arial;
    font-size: 12px;
    line-height: normal;
}
p{ line-height: 20px;}
.text-left{ text-align: left}
.text-right{ text-align: right}
.text-center{ text-align: center}
table.invoice th{ padding:3px; background-color: #f5f5f6}
.bg_graycolor{background-color: #f5f5f6}
table.invoice td , table.invoice_total td{ padding:3px;}
.page_break{ padding: 10px 0; page-break-after: always;}
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
  margin-bottom: 30px;
}

</style>
<?php
$RoundChargesAmount = get_round_decimal_places($Account->AccountID);
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
                <th style="text-align: center;">Description</th>
                <th style="text-align: center;">Quantity</th>
                <th style="text-align: center;">Price</th>
                @if($Estimate->TotalDiscount >0)
                <th style="text-align: center;">Discount</th>
                @endif
                <th style="text-align: center;">Line Total</th>
                {{--<th style="text-align: center;">Tax</th>--}}
                <th style="text-align: center;">Tax Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($EstimateDetail as $ProductRow)
         	  <?php if(!isset($TaxrateName)){ $TaxrateName = TaxRate::getTaxName($ProductRow->TaxRateID); } ?>
            @if($ProductRow->ProductType == Product::ITEM)
            <tr>
                <td class="text-center">{{Product::getProductName($ProductRow->ProductID,$ProductRow->ProductType)}}</td>
                <td class="text-center">{{$ProductRow->Description}}</td>
                <td class="text-center">{{$ProductRow->Qty}}</td>
                <td class="text-center">{{number_format($ProductRow->Price,$RoundChargesAmount)}}</td>
                @if($Estimate->TotalDiscount >0)
                <td class="text-center">{{$ProductRow->Discount}}</td>
                @endif
                <td class="text-center">{{number_format($ProductRow->LineTotal,$RoundChargesAmount)}}</td>
                {{--<td class="text-center">{{TaxRate::getTaxRate($ProductRow->TaxRateID)}}</td>--}}
                <td class="text-center">{{number_format($ProductRow->TaxAmount,$RoundChargesAmount)}}</td>
            </tr>
            @endif
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
                                                        <tr>
                                                                <td class="text-right"><strong>SubTotal</strong></td>
                                                                <td class="text-right">{{$CurrencySymbol}}{{number_format($Estimate->SubTotal,$RoundChargesAmount)}}</td>
                                                        </tr>

                                                        @if(count($EstimateTaxRates))
                                                            @foreach($EstimateTaxRates as $EstimateTaxRate)
                                                                <tr>
                                                                    <td class="text-right"><strong>{{$EstimateTaxRate->Title}}</strong></td>
                                                                    <td class="text-right">{{$CurrencySymbol}}{{number_format($EstimateTaxRate->TaxAmount,$RoundChargesAmount)}}</td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                        @if($Estimate->TotalDiscount >0)
                                                        <tr>
                                                                <td class="text-right"><strong>Discount</strong></td>
                                                                <td class="text-right">{{$CurrencySymbol}}{{number_format($Estimate->TotalDiscount,$RoundChargesAmount)}}</td>
                                                        </tr>
                                                        @endif
                                                        <tr>
                                                                <td class="text-right"><strong>Estimate Total</strong></td>
                                                                <td class="text-right">{{$CurrencySymbol}}{{number_format($Estimate->EstimateTotal,$RoundChargesAmount)}} </td>
                                                        </tr>
                                                            @if(count($EstimateAllTaxRates))
                                                            @foreach($EstimateAllTaxRates as $EstimateTaxRate)
                                                                <tr>
                                                                    <td class="text-right"><strong>{{$EstimateTaxRate->Title}}</strong></td>
                                                                    <td class="text-right">{{$CurrencySymbol}}{{number_format($EstimateTaxRate->TaxAmount,$RoundChargesAmount)}}</td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                        
                                                        <tr>
                                                                <td class="text-right"><strong>Gross Total</strong></td>
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