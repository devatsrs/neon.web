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
.page_break{ page-break-after: always;}
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
                   <strong>Invoice From:</strong>
                   <p><strong>{{ nl2br($InvoiceTemplate->Header)}}</strong></p>

                </td>
            </tr>
        </table>
        <br />

        <table border="0" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="col-md-6"  valign="top" >
                        <br>
                        <strong>Invoice To</strong>
                        <p>{{$Account->AccountName}}</p>
                        <p>{{nl2br($Invoice->Address)}}</p>
                </td>
                <td class="col-md-6 text-right"  valign="top" >
                        <p><b>Invoice No: </b>{{$Invoice->FullInvoiceNumber}}</p>
                        <p><b>Invoice Date: </b>{{ date($InvoiceTemplate->DateFormat,strtotime($Invoice->IssueDate))}}</p>
                        <p><b>Due Date: </b>{{date('d-m-Y',strtotime($Invoice->IssueDate.' +'.$PaymentDueInDays.' days'))}}</p>
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
                @if($Invoice->TotalDiscount >0)
                <th style="text-align: center;">Discount</th>
                @endif
                <th style="text-align: center;">Line Total</th>
                {{--<th style="text-align: center;">Tax</th>--}}
               {{--<th class="hidden" style="text-align: center;">Tax Amount</th> --}}
            </tr>
            </thead>
            <tbody>
            @foreach($InvoiceDetail as $ProductRow)
                <?php if(!isset($TaxrateName)){ $TaxrateName = TaxRate::getTaxName($ProductRow->TaxRateID); } ?>
            @if($ProductRow->ProductType == Product::ITEM)
            <tr>
                <td class="text-center">{{Product::getProductName($ProductRow->ProductID,$ProductRow->ProductType)}}</td>
                <td class="text-left">{{$ProductRow->Description}}</td>
                <td class="text-center">{{$ProductRow->Qty}}</td>
                <td class="text-center">{{number_format($ProductRow->Price,$RoundChargesAmount)}}</td>
                @if($Invoice->TotalDiscount >0)
                <td class="text-center">{{$ProductRow->Discount}}</td>
                @endif
                <td class="text-center">{{number_format($ProductRow->LineTotal,$RoundChargesAmount)}}</td>
                {{--<td class="text-center">{{TaxRate::getTaxRate($ProductRow->TaxRateID)}}</td>--}}
                {{--<td class="hidden" class="text-center">{{number_format($ProductRow->TaxAmount,$RoundChargesAmount)}}</td>--}}
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
                                                <p><a class="form-control" style="height: auto">{{nl2br($Invoice->Terms)}}</a></p>
                                        </td>
                                        <td class="col-md-6"  valign="top" width="35%" >
                                                <table  border="1"  width="100%" cellpadding="0" cellspacing="0" class="bg_graycolor invoice_total col-md-12 table table-bordered">
                                                    <tfoot>
                                                        <tr>
                                                                <td class="text-right"><strong>Sub Total:</strong></td>
                                                                <td class="text-right">{{$CurrencySymbol}}{{number_format($Invoice->SubTotal,$RoundChargesAmount)}}</td>
                                                        </tr>                                                      
                                                        @if($Invoice->TotalDiscount >0)
                                                        <tr>
                                                                <td class="text-right"><strong>Discount</strong></td>
                                                                <td class="text-right">{{$CurrencySymbol}}{{number_format($Invoice->TotalDiscount,$RoundChargesAmount)}}</td>
                                                        </tr>
                                                        @endif
                                                        @if(count($InvoiceAllTaxRates))
                                                            @foreach($InvoiceAllTaxRates as $InvoiceTaxRate)
                                                                <tr>
                                                                    <td class="text-right"><strong>{{$InvoiceTaxRate->Title}}</strong></td>
                                                                    <td class="text-right">{{$CurrencySymbol}}{{number_format($InvoiceTaxRate->TaxAmount,$RoundChargesAmount)}}</td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                        
                                                        <tr>
                                                                <td class="text-right"><strong>Grand Total:</strong></td>
                                                                <td class="text-right">{{$CurrencySymbol}}{{number_format($Invoice->GrandTotal,$RoundChargesAmount)}} </td>
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