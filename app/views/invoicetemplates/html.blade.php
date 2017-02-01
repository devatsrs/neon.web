@section('logo')
<table border="0" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="col-md-6" valign="top">
           <img src="{{$logo}}" alt="Company Logo" title="Company Logo" style="max-width: 250px">
        </td>
        <td class="col-md-6 text-right" valign="top">
           <strong>Invoice From:</strong>
           @if(isset($editable))
           <p><a href="#" id="InvoiceTemplateHeader" title="Invoice Header Content" data-inputclass="editable-textarea" class="invoice-editable form-control autogrow " style="height: auto" data-name="Header" data-type="textarea" data-placeholder="Press Ctrl + Enter to save" data-title="Header">{{($InvoiceTemplate->Header)}}</a></p>
           <h3><a href="#" style="display: none;" id="InvoiceTemplateName" data-placeholder="Press Ctrl + Enter to save" class="invoice-editable form-control" data-name="Name" data-type="text" title="Template Name">{{$InvoiceTemplate->Name}}</a></h3>
           @else
           <p>{{nl2br($InvoiceTemplate->Header)}}</p>
           @endif
        </td>
    </tr>
</table>
@stop
@section('invoice_from')
        <table border="0" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="col-md-6"  valign="top" >
                		<br>
                        <strong>Invoice To</strong>
                        <p>John Doe
                        <br />
                        Mr Nilson Otto
                        <br />
                        FoodMaster Ltd</p>
                </td>
                <td class="col-md-6 text-right"  valign="top" >
                        <p><b>Invoice No: </b>{{$InvoiceTemplate->InvoiceNumberPrefix.$InvoiceTemplate->InvoiceStartNumber}}<br>
                        <b>Invoice Date: </b>{{date('d-m-Y')}}<br>
                        <b>Due Date: </b>{{date('d-m-Y',strtotime('+5 days'))}}</p>
                 </td>
                </tr>
                <tr>

            </tr>
        </table>
@stop
@section('usage_invoice_duration')
@if($InvoiceTemplate->ShowBillingPeriod == 1)
            <table border="1" cellspacing="0" cellpadding="0" class="table table-bordered" style="width: 100%; ">
                <thead>
                <tr>
                    <th colspan="2" style="text-align: center; padding: 0;margin: 0" >Invoice Period</th>

                </tr>
                <tr>
                    <th style="text-align: center;">From</th>
                    <th style="text-align: center;">To</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="text-align: center;" >Fubruary 1,2006</td>
                    <td style="text-align: center;" >Fubruary 28,2006</td>
                </tr>
                </tbody>
            </table>
@endif
@stop
@section('usage_invoice_prevbal')

            @if($InvoiceTemplate->ShowPrevBal)
            <table class="table table-bordered" style="width: 100%; text-align: right;">
                <tbody>
                <tr>
                    <td style="text-align: left;">Previous Balance</td>
                    <td style="text-align: right;">19.39</td>
                </tr>
                <tr>
                    <td style="border-top: 1px solid black;text-align: left;">Payments</td>
                    <td style="border-top: 1px solid black; text-align: right;">24.08</td>
                </tr>
                <tr>
                    <td style="border-top: 1px solid black;text-align: left;">Charges for this period</td>
                    <td style="border-top: 1px solid black; text-align: right;">90.18</td>
                </tr>
                <tr>
                    <td style="border-top: 2px solid black;text-align: left;">Total Due (USD)</td>
                    <td style="border-top: 2px solid black; text-align: right;">84.49</td>
                </tr>
                </tbody>
            </table>
            @endif
@stop

@section('items')
<h4>Item</h4>
                  <table  border="1"  width="100%" cellpadding="0" cellspacing="0" class="bg_graycolor invoice_total col-md-12 table table-bordered">
                  <thead>
                  <tr>
                      <th style="text-align: center;">Title</th>
                      <th style="text-align: left;">Description</th>
                      <th style="text-align: center;">Quantity</th>
                      <th style="text-align: center;">Price</th>
                      <th style="text-align: center;">Line Total</th>
                      {{--<th style="text-align: center;">Tax</th>--}}
                      <th style="text-align: center;">Tax Amount</th>

                  </tr>
                  </thead>
                  <tbody>
                  <tr>
                      <td style="text-align: center;"  >Item 1</td>
                      <td style="text-align: left;" >Item Description</td>
                      <td style="text-align: center;" >2</td>
                      <td style="text-align: center;" >25</td>
                      <td style="text-align: center;" >50</td>
                      {{--<td style="text-align: center;" >VAT 20%</td>--}}
                      <td style="text-align: center;" >10</td>
                  </tr>
                  </tbody>
              </table>
@stop
@section('subscriptiontotal')
<table  border="1"  width="100%" cellpadding="0" cellspacing="0" class="bg_graycolor invoice_total col-md-12 table table-bordered">

    <tfoot>
    <tr>
        <td>Usage</td>
        <td class="text-right">50.00</td>
    </tr>
    <tr>
        <td>Subscription</td>
        <td class="text-right">10.00</td>
    </tr>
    </tfoot>
</table>
@stop

@section('total')
<table  border="1"  width="100%" cellpadding="0" cellspacing="0" class="bg_graycolor invoice_total col-md-12 table table-bordered">

    <tfoot>
    <tr>
        <td>Sub Total</td>
        <td>50.00</td>
    </tr>
    <tr>
        <td>TAX</td>
        <td>10.00</td>
    </tr>
    <tr>
        <td>Invoice Total </td>
        <td>60.00 USD</td>
    </tr>
    </tfoot>
</table>
 @stop
 @section('terms')

    @if(isset($editable))
        <p><a href="#" id="InvoiceTemplateTerms" class="invoice-editable form-control" style="height: auto" data-name="Terms" data-type="textarea" data-placeholder="Press Ctrl + Enter to save" data-title="Terms">{{$InvoiceTemplate->Terms}}</a></p>
    @else
        <p>{{nl2br($InvoiceTemplate->Terms)}}</p>
    @endif
 @stop

 @section('footerterms')
    <div id="pdf_footer" class="">
    <table>
        <tbody>
            <tr>
          <td>
     @if(isset($editable))
         <p><a href="#" id="InvoiceTemplateFooter" class="invoice-editable form-control" style="height: auto" data-name="FooterTerm" data-type="textarea" data-placeholder="Press Ctrl + Enter to save" data-title="FooterTerm">{{$InvoiceTemplate->FooterTerm}}</a></p>
     @else
          {{nl2br($InvoiceTemplate->FooterTerm)}}
     @endif
                </td>
             </tr>
           </tbody></table>
     </div>
  @stop

 @section('sub_usage')


        @if(isset($editable))
            <hr>
        @endif

        @if($InvoiceTemplate->InvoicePages == 'single_with_detail')
            <div class="page_break"> </div>
            <br/><br/><br/>
        @endif
     	<h4>Subscription Charges</h4>
            <table border="1"  width="100%" cellpadding="0" cellspacing="0" class="invoice col-md-12 table table-bordered">
                <thead>
                <tr>
                    <th style="text-align: center;">Title</th>
                    <th style="text-align: left;">Description</th>
                    <th style="text-align: center;">Price</th>
                    <th style="text-align: center;">Quantity</th>
                    <th style="text-align: center;">Date From</th>
                    <th style="text-align: center;">Date To</th>
                    <th style="text-align: center;">Line Total</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="text-align: center;"  >Fubruary 1,2006</td>
                    <td style="text-align: left;" >Usage From 22-06-2015 To 28-06-2015</td>
                    <td style="text-align: center;" >25</td>
                    <td style="text-align: center;" >1</td>
                    <td style="text-align: center;" >12-12-2015</td>
                    <td style="text-align: center;" >19-12-2015</td>
                    <td style="text-align: center;" >25</td>
                </tr>
                </tbody>
            </table>
            <br>
            <h4>Call Charges</h4>
            <table border="1"  width="100%" cellpadding="0" cellspacing="0" class="invoice col-md-12 table table-bordered">
                <thead>
                <tr>
                    <th style="text-align: center;">Title</th>
                    <th style="text-align: left;">Description</th>
                    <th style="text-align: center;">Price</th>
                    <th style="text-align: center;">Quantity</th>
                    <th style="text-align: center;">Date From</th>
                    <th style="text-align: center;">Date To</th>
                    <th style="text-align: center;">Line Total</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="text-align: center;"  >Fubruary 1,2006</td>
                    <td style="text-align: left;" >Usage From 22-06-2015 To 28-06-2015</td>
                    <td style="text-align: center;" >25</td>
                    <td style="text-align: center;" >1</td>
                    <td style="text-align: center;" >12-12-2015</td>
                    <td style="text-align: center;" >19-12-2015</td>
                    <td style="text-align: center;" >25</td>
                </tr>
                </tbody>
            </table>
            <br>
            <h4>Additional Charges</h4>
            <table border="1"  width="100%" cellpadding="0" cellspacing="0" class="invoice col-md-12 table table-bordered">
                <thead>
                <tr>
                    <th style="text-align: center;">Title</th>
                    <th style="text-align: left;">Description</th>
                    <th style="text-align: center;">Price</th>
                    <th style="text-align: center;">Quantity</th>
                    <th style="text-align: center;">Date</th>
                    <th style="text-align: center;">Line Total</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="text-align: center;"  >Yealink</td>
                    <td style="text-align: left;" >Yealink</td>
                    <td style="text-align: center;" >25</td>
                    <td style="text-align: center;" >1</td>
                    <td style="text-align: center;" >12-12-2015</td>
                    <td style="text-align: center;" >25</td>
                </tr>
                </tbody>
            </table>
            
<style>
#pdf_footer table {
	width:100%;
}
</style>
 @stop