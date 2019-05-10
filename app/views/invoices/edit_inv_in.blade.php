@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <a href="{{URL::to('invoice')}}">Invoice</a>
    </li>
    <li class="active">
        <strong>Edit Received Invoice</strong>
    </li>
</ol>
<h3>Edit Invoice Received </h3>

@include('includes.errors')
@include('includes.success')
<style>
    .popover{
        max-width:350px;
        width:350px;
    }
    .DiscountType{
         width:40%;
         text-align:left;
     }
    .DiscountType .select2-arrow{
        width:30% !important;
    }
    .DiscountAmount{
        width:40%;
        display:inline;
    }
</style>
<form class="form-horizontal form-groups-bordered"  method="post" id="invoice-from" role="form">
  <p class="text-right">
    <button type="submit" class="btn save btn-primary btn-icon btn-sm icon-left hidden-print" data-loading-text="Loading..."> Save<i class="entypo-floppy"></i> </button>
    <a href="{{URL::to('/invoice')}}" class="btn btn-danger btn-sm btn-icon icon-left"> <i class="entypo-cancel"></i> Close </a> </p>
  <div class="panel panel-primary" data-collapsed="0">
    <div class="panel-body">
      <div class="form-group">
        <div class="col-sm-6">
          <label for="field-2" class="col-sm-3 control-label">Account</label>
          <div class="col-sm-9">{{Form::select('AccountID',$accounts,$Invoice->AccountID,array("class"=>"select2" ,"disabled"=>"disabled"))}}
                            {{Form::hidden('AccountID',$Invoice->AccountID)}}  </div><br>

          <div class="clearfix margin-bottom "></div>
          <label for="field-1" class="col-sm-3 control-label">Invoice #</label>
          <div class="col-sm-9"> {{Form::text('InvoiceNumber',$Invoice->InvoiceNumber,array("class"=>"form-control","readonly"=>"readonly"))}} </div>
          <div class="clearfix margin-bottom "></div>
            <label for="field-1" class="col-sm-3 control-label">Status</label>
            <div class="col-sm-9">{{Form::select('InvoiceStatus',Invoice::getStatusDropDownPurchaseInvoice(),$Invoice->InvoiceStatus,array("class"=>"select2"))}} </div>
            <div class="clearfix margin-bottom "></div>
           <label for="field-1" class="col-sm-3 control-label">Description</label>
          <div class="col-sm-9"> {{Form::textarea('Address',$Invoice->Address,array( "ID"=>"Account_Address", "rows"=>4, "class"=>"form-control"))}} </div>
          <div class="clearfix margin-bottom "></div>
        </div>
        
        <div class="col-sm-6">
          <label for="field-1" class="col-sm-4 control-label">Date of issue</label>
          <div class="col-sm-8"> {{Form::text('IssueDate',date('d-m-Y',strtotime($Invoice->IssueDate)),array("class"=>" form-control datepicker" , "data-startdate"=>date('d-m-Y',strtotime("-2 month")),  "data-date-format"=>"dd-mm-yyyy", "data-end-date"=>"+1w" ,"data-start-view"=>"2"))}} </div>
          <div class="clearfix margin-bottom "></div>

          <label for="field-1" class="col-sm-4 control-label">Start Date</label>
          <div class="col-sm-8"> {{Form::text('StartDate',date('d-m-Y',strtotime($InvoiceDetailFirst->StartDate)),array("class"=>" form-control datepicker" , "data-startdate"=>date('Y-m-d',strtotime("-2 month")),  "data-date-format"=>"dd-mm-yyyy", "data-end-date"=>"+1w" ,"data-start-view"=>"2"))}} </div>
          <div class="clearfix margin-bottom "></div>
          <label for="field-1" class="col-sm-4 control-label">End Date</label>
          <div class="col-sm-8"> {{Form::text('EndDate',date('d-m-Y',strtotime($InvoiceDetailFirst->EndDate)),array("class"=>" form-control datepicker" , "data-startdate"=>date('Y-m-d',strtotime("-2 month")),  "data-date-format"=>"dd-mm-yyyy", "data-end-date"=>"+1w" ,"data-start-view"=>"2"))}} </div>
          <div class="clearfix margin-bottom "></div>
          <label for="field-1" class="col-sm-4 control-label">*Total Seconds</label>
          <div class="col-sm-8"> {{Form::text('TotalMinutes',$InvoiceDetailFirst->TotalMinutes,array("class"=>"form-control"))}} </div>
        </div>
      </div>
      <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Dispute Amount</label>
            <div class="col-sm-2">
              <input type="text" name="DisputeAmount" class="form-control" value="@if(!empty($Invoice->dispute->DisputeAmount)) {{$Invoice->dispute->DisputeAmount}} @endif"/>
            </div>
             <label class="col-sm-2 control-label" for="field-1">Reconcile</label>
            <div class="col-sm-6">
              <table class="reconcile_table table table-bordered datatable  hidden">
                <thead>
                <th></th>
                  <th>Total</th>
                  <th>Difference</th>
                  <th>Difference %</th>
                    </thead>
                <tbody>
                  <tr>
                    <th>Amount</th>
                    <td><span class="DisputeTotal"></span></td>
                    <td><span class="DisputeDifference"></span></td>
                    <td><span class="DisputeDifferencePer"></span></td>
                  </tr>
                  <tr>
                    <th>Seconds</th>
                    <td><span class="DisputeMinutes"></span></td>
                    <td><span class="MinutesDifference"></span></td>
                    <td><span class="MinutesDifferencePer"></span></td>
                  </tr>
                </tbody>
              </table>
              <button class="btn btn-primary reconcile btn-sm btn-icon icon-left" type="button"
                                        data-loading-text="Loading..."> <i class="entypo-pencil"></i> Reconcile </button>
              <button class="btn ignore btn-danger btn-sm btn-icon icon-left hidden" type="button"
                                        data-loading-text="Loading..."> <i class="entypo-pencil"></i> Ignore </button>
              <input type="hidden" name="DisputeID">
              {{--
              <input type="hidden" name="DisputeTotal">
              --}}
              {{--
              <input type="hidden" name="DisputeDifference">
              --}}
              {{--
              <input type="hidden" name="DisputeDifferencePer">
              --}}
              
              {{--
              <input type="hidden" name="DisputeMinutes">
              --}}
              {{--
              <input type="hidden" name="MinutesDifference">
              --}}
              {{--
              <input type="hidden" name="MinutesDifferencePer">
              --}} </div>
              
            <div class="col-sm-1 download"> </div>
            
            
          </div>
          <div class="clearfix"></div>
          <div class="form-group">
          <div class="col-sm-6">
            <label class="col-sm-6 control-label" for="field-1">Attachment(.pdf, .jpg, .png,
              .gif)</label>
              <input id="Attachment" name="Attachment" type="file"
                                       class="form-control file2 inline btn btn-primary"
                                       data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse"/>
              
              <!--<br><span class="file-input-name"></span>--> 
            </div>
          </div>
      <div class="form-group">
                    <div class="col-sm-12">
                        <div class="dataTables_wrapper">
                            <table id="InvoiceTable" class="table table-bordered" style="margin-bottom: 0">
                                <thead>
                                <tr>
                                    <th  width="1%"><button type="button" id="add-row" class="btn btn-primary btn-xs ">+</button></th>
                                    <th  width="14%">Item</th>
                                    <th width="15%">Description</th>
                                    <th width="10%">Unit Price</th>
                                    <th width="10%">Quantity</th>
                                    <th width="10%" >Discount</th>
                                    <th class="hidden" width="10%" >Total Tax</th>
                                    <th width="10%">Line Total</th>
                                </tr>
                                </thead>
                                <tbody>

                                @if(count($InvoiceDetail)>0)
                                    @foreach($InvoiceDetail as $ProductRow)
                                        <tr>
                                            <td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td>
                                            <td>{{Form::SelectControl('item_and_Subscription',0,['Type'=>$ProductRow->ProductType,'ID'=>$ProductRow->ProductID],0,'InvoiceDetail[ProductID][]')}}</td>
                                            <td>{{Form::textarea('InvoiceDetail[Description][]',$ProductRow->Description,array("class"=>"form-control autogrow invoice_estimate_textarea descriptions","rows"=>1))}}</td>
                                            <td class="text-center">{{Form::text('InvoiceDetail[Price][]', number_format($ProductRow->Price,$RoundChargesAmount),array("class"=>"form-control Price","data-mask"=>"fdecimal"))}}</td>
                                            <td class="text-center">{{Form::text('InvoiceDetail[Qty][]',$ProductRow->Qty,array("class"=>"form-control Qty"))}}</td>
                                            <td class="text-center hidden">{{Form::text('InvoiceDetail[Discount][]',number_format($ProductRow->Discount,$RoundChargesAmount),array("class"=>"form-control Discount","data-min"=>"1", "data-mask"=>"fdecimal"))}}</td>

                                            <td class="text-center">{{Form::text('InvoiceDetail[DiscountAmount][]', number_format($ProductRow->DiscountAmount,$RoundChargesAmount),array("class"=>"form-control DiscountAmount","data-min"=>"1", "data-mask"=>"fdecimal"))}}
                                                {{Form::Select("InvoiceDetail[DiscountType][]",array("Percentage"=>"%","Flat"=>"Flat"),$ProductRow->DiscountType,array("class"=>"select2 small DiscountType"))}}</td>
                                            <!--<td>{{Form::SelectExt(
                                                  [
                                                  "name"=>"InvoiceDetail[TaxRateID][]",
                                                  "data"=>$taxes,
                                                  "selected"=>$ProductRow->TaxRateID,
                                                  "value_key"=>"TaxRateID",
                                                  "title_key"=>"Title",
                                                  "data-title1"=>"data-amount",
                                                  "data-value1"=>"Amount",
                                                  "data-title2"=>"data-flatstatus",
                                                  "data-value2"=>"FlatStatus",
                                                  "class" =>"select2 small Taxentity TaxRateID",
                                                  ])}}
                                            </td>
                                            <td>{{Form::SelectExt(
                                                  [
                                                  "name"=>"InvoiceDetail[TaxRateID2][]",
                                                  "data"=>$taxes,
                                                  "selected"=>$ProductRow->TaxRateID2,
                                                  "value_key"=>"TaxRateID",
                                                  "title_key"=>"Title",
                                                  "data-title1"=>"data-amount",
                                                  "data-value1"=>"Amount",
                                                  "data-title2"=>"data-flatstatus",
                                                  "data-value2"=>"FlatStatus",
                                                  "class" =>"select2 small Taxentity TaxRateID2",
                                                  ]
                                                  )}}
                                            </td> -->
                                            <td class="hidden">{{Form::text('InvoiceDetail[TaxAmount][]',number_format($ProductRow->TaxAmount,$RoundChargesAmount),array("class"=>"form-control TaxAmount","readonly"=>"readonly", "data-mask"=>"fdecimal"))}}</td>
                                            <td>{{Form::text('InvoiceDetail[LineTotal][]',number_format($ProductRow->LineTotal,$RoundChargesAmount),array("class"=>"form-control LineTotal","data-min"=>"1", "data-mask"=>"fdecimal","readonly"=>"readonly"))}}
                                                {{Form::hidden('InvoiceDetail[InvoiceDetailID][]',$ProductRow->InvoiceDetailID,array("class"=>"InvoiceDetailID"))}}
                                                {{Form::hidden('InvoiceDetail[ProductType][]',$ProductRow->ProductType,array("class"=>"ProductType"))}} </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
      <div class="form-group">
        <div class="col-md-6">
          
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-5">
                    <table class="table table-bordered">
                            <tfoot>
                            <tr>
                                <td >Sub Total</td>
                                <td>{{Form::text('SubTotal',number_format($Invoice->SubTotal,$RoundChargesAmount),array("class"=>"form-control SubTotal text-right"))}}</td>
                            </tr>
                            <tr class="tax_rows_invoice">
                                <td ><span class="product_tax_title">VAT</span> </td>
                                <td>{{Form::text('TotalTax',number_format($Invoice->TotalTax,$RoundChargesAmount),array("class"=>"form-control TotalTax text-right"))}}</td>
                            </tr>
                            <!--<tr>
                            <td>Discount </td>
                            <td>{{Form::text('TotalDiscount',number_format($Invoice->TotalDiscount,$RoundChargesAmount),array("class"=>"form-control TotalDiscount text-right","readonly"=>"readonly"))}}</td>
                    </tr>-->
                            <tr class="grand_total_invoice">
                                <td >Invoice Total </td>
                                <td>{{Form::text('GrandTotal',number_format($Invoice->GrandTotal,$RoundChargesAmount),array("class"=>"form-control GrandTotal text-right"))}}</td>
                            </tr>
                            @if(count($InvoiceAllTax)>0)
                                @foreach($InvoiceAllTax as $key => $InvoiceAllTaxData)
                                    <tr class="  @if($key==0) invoice_tax_row @else all_tax_row @endif">
                                        @if($key==0)
                                            <td>  <button title="Add new Tax" type="button" class="btn btn-primary btn-xs invoice_tax_add ">+</button>   &nbsp; Tax </td>
                                        @else
                                            <td>
                                                <button title="Delete Tax" type="button" class="btn btn-danger btn-xs invoice_tax_remove ">X</button>
                                            </td>
                                        @endif
                                        <td><div class="col-md-8"> {{Form::SelectExt(
                                                                        [
                                                                        "name"=>"InvoiceTaxes[field][]",
                                                                        "data"=>$taxes,
                                                                        "selected"=>$InvoiceAllTaxData->TaxRateID,
                                                                        "value_key"=>"TaxRateID",
                                                                        "title_key"=>"Title",
                                                                        "data-title1"=>"data-amount",
                                                                        "data-value1"=>"Amount",
                                                                        "data-title2"=>"data-flatstatus",
                                                                        "data-value2"=>"FlatStatus",
                                                                        "class" =>"select2 small Taxentity InvoiceTaxesFld  InvoiceTaxesFldFirst",
                                                                        ]
                                                                        )}}
                                            </div>
                                            <div class="col-md-4"> {{Form::text('InvoiceTaxes[value][]',$InvoiceAllTaxData->TaxAmount,array("class"=>"form-control InvoiceTaxesValue"))}} </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="invoice_tax_row">
                                    <td>
                                        <button title="Add new Tax" type="button" class="btn btn-primary btn-xs invoice_tax_add ">+</button>
                                        &nbsp; Tax </td>
                                    <td>
                                        <div class="col-md-8"> {{Form::SelectExt(
                                                                [
                                                                "name"=>"InvoiceTaxes[field][]",
                                                                "data"=>$taxes,
                                                                "selected"=>'',
                                                                "value_key"=>"TaxRateID",
                                                                "title_key"=>"Title",
                                                                "data-title1"=>"data-amount",
                                                                "data-value1"=>"Amount",
                                                                "data-title2"=>"data-flatstatus",
                                                                "data-value2"=>"FlatStatus",
                                                                "class" =>"select2 small Taxentity InvoiceTaxesFld  InvoiceTaxesFldFirst",
                                                                ]
                                                                )}}
                                        </div>
                                        <div class="col-md-4"> {{Form::text('InvoiceTaxes[value][]','',array("class"=>"form-control InvoiceTaxesValue"))}} </div></td>
                                </tr>
                            @endif
                            <tr class="gross_total_invoice">
                                <td >Grand Total </td>
                                <td>{{Form::text('GrandTotalInvoice','',array("class"=>"form-control GrandTotalInvoice text-right"))}}</td>
                            </tr>
                            </tfoot>
                        </table>

                </div>

               </div>

        </div>
</div>

<div class="pull-right">
    <input type="hidden" name="CurrencyID" value="">
    <input type="hidden" name="CurrencyCode" value="">
    <input type="hidden" name="InvoiceTemplateID" value="">
   <input  type="hidden" name="TotalTax" value="" > 
</div>
</form>
<div id="rowContainer"></div>
<script type="text/javascript">
var decimal_places = 2;
var invoice_id = '';
var invoice_tax_html = '<td><button title="Delete Tax" type="button" class="btn btn-danger btn-xs invoice_tax_remove ">X</button></td><td><div class="col-md-8">{{addslashes(Form::SelectExt(["name"=>"InvoiceTaxes[field][]","data"=>$taxes,"selected"=>'',"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select2 Taxentity small InvoiceTaxesFld"]))}}</div><div class="col-md-4">{{Form::text("InvoiceTaxes[value][]","",array("class"=>"form-control InvoiceTaxesValue","readonly"=>"readonly"))}}</div></td>';

var add_row_html = '<tr class="itemrow hidden"><td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td><td>{{addslashes(Form::SelectControl('item_and_Subscription',0,'',0,'InvoiceDetail[ProductID][]',0))}}</td><td>{{Form::textarea('InvoiceDetail[Description][]','',array("class"=>"form-control invoice_estimate_textarea autogrow descriptions","rows"=>1))}}</td><td class="text-center">{{Form::text('InvoiceDetail[Price][]',"0",array("class"=>"form-control Price","data-mask"=>"fdecimal"))}}</td><td class="text-center">{{Form::text('InvoiceDetail[Qty][]',1,array("class"=>"form-control Qty"))}}</td>'
add_row_html += '<td class="text-center hidden">{{Form::text('InvoiceDetail[Discount][]',0,array("class"=>"form-control Discount","data-min"=>"1", "data-mask"=>"fdecimal"))}}</td>';
add_row_html += '<td class="text-center">{{Form::text('InvoiceDetail[DiscountAmount][]',0,array("class"=>"form-control DiscountAmount","data-min"=>"1", "data-mask"=>"fdecimal"))}} {{Form::Select("InvoiceDetail[DiscountType][]",array("Percentage"=>"%","Flat"=>"Flat"),'%',array("class"=>"select22 small DiscountType"))}}</td>';
/*add_row_html += '<td>{{addslashes(Form::SelectExt(["name"=>"InvoiceDetail[TaxRateID][]","data"=>$taxes,"selected"=>'',"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select22 Taxentity small TaxRateID"]))}}</td>';
add_row_html += '<td>{{addslashes(Form::SelectExt(["name"=>"InvoiceDetail[TaxRateID2][]","data"=>$taxes,"selected"=>'',"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select22 Taxentity small TaxRateID2"]))}}</td>';*/
	 
     add_row_html += '<td class="hidden">{{Form::text('InvoiceDetail[TaxAmount][]',"0",array("class"=>"form-control  TaxAmount","readonly"=>"readonly", "data-mask"=>"fdecimal"))}}</td>';
     add_row_html += '<td>{{Form::text('InvoiceDetail[LineTotal][]',0,array("class"=>"form-control LineTotal","data-min"=>"1", "data-mask"=>"fdecimal","readonly"=>"readonly"))}}';
     add_row_html += '{{Form::hidden('InvoiceDetail[ProductType][]',Product::ITEM,array("class"=>"ProductType"))}}</td></tr>';

$('#rowContainer').append(add_row_html);

function ajax_form_success(response){
    if(typeof response.redirect != 'undefined' && response.redirect != ''){
        window.location = response.redirect;
    }
}

$(".btn.ignore").click(function (e) {

                reset_dispute();

            });

$(".btn.reconcile").click(function (e) {


                e.preventDefault();
                var curnt_obj = $(this);
                curnt_obj.button('loading');


                var formData = $('#invoice-from').serializeArray();

                reconcile_url = baseurl + '/invoice/reconcile';
                ajax_json(reconcile_url, formData, function (response) {

                    $(".btn").button('reset');

                    if (response.status == 'success') {

                        console.log(response);
                        set_dispute(response);
                    }

                });


            });
function set_dispute(response) {
if (typeof response.DisputeTotal == 'undefined') {

                    $(".reconcile_table").addClass("hidden");
                    $(".btn.ignore").addClass("hidden");


                } else {

                    $(".reconcile_table").removeClass("hidden");
                    $(".btn.ignore").removeClass("hidden");
                }

                if (typeof response.DisputeAmount != 'undefined') {

                    $('#invoice-from').find("input[name=DisputeAmount]").val(response.DisputeAmount);

                } else {

                    $('#invoice-from').find("input[name=DisputeAmount]").val(response.DisputeDifference);
                }


                $('#invoice-from').find("table .DisputeTotal").text(response.DisputeTotal);
                $('#invoice-from').find("table .DisputeDifference").text(response.DisputeDifference);
                $('#invoice-form').find("table .DisputeDifferencePer").text(response.DisputeDifferencePer);


                $('#invoice-from').find("table .DisputeMinutes").text(response.DisputeMinutes);
                $('#invoice-from').find("table .MinutesDifference").text(response.MinutesDifference);
                $('#invoice-from').find("table .MinutesDifferencePer").text(response.MinutesDifferencePer);


                /*$('#invoice-form').find("input[name=DisputeTotal]").val(response.DisputeTotal);
                 $('#invoice-form').find("input[name=DisputeDifference]").val(response.DisputeDifference);
                 $('#invoice-form').find("input[name=DisputeDifferencePer]").val(response.DisputeDifferencePer);
                 $('#invoice-form').find("input[name=DisputeMinutes]").val(response.DisputeMinutes);
                 $('#invoice-form').find("input[name=MinutesDifference]").val(response.MinutesDifference);
                 $('#invoice-form').find("input[name=MinutesDifferencePer]").val(response.MinutesDifferencePer);*/

            }

            function reset_dispute() {

                $('#invoice-form').find("table .DisputeTotal").text("");
                $('#invoice-form').find("table .DisputeDifference").text("");
                $('#invoice-form').find("table .DisputeDifferencePer").text("");


                $('#invoice-form').find("table .DisputeMinutes").text("");
                $('#invoice-form').find("table .MinutesDifference").text("");
                $('#invoice-form').find("table .MinutesDifferencePer").text("");


                $('#invoice-form').find("input[name=DisputeAmount]").val("")

                /*$('#invoice-form').find("input[name=DisputeTotal]").val("");
                 $('#invoice-form').find("input[name=DisputeDifference]").val("");
                 $('#invoice-form').find("input[name=DisputeDifferencePer]").val("");

                 $('#invoice-form').find("input[name=DisputeMinutes]").val("");
                 $('#invoice-form').find("input[name=MinutesDifference]").val("");
                 $('#invoice-form').find("input[name=MinutesDifferencePer]").val("");*/

                $(".reconcile_table").addClass("hidden");
                $(".btn.ignore").addClass("hidden");

            }

</script>
@include('invoices.script_invoice_barcode_product')
@include('invoices.script_invoice_add_edit')
@include('composetmodels.productsubscriptionmodal')
@include('includes.ajax_submit_script', array('formID'=>'invoice-from' , 'url' => 'invoice/'.$id.'/updatein' ))
@stop
@section('footer_ext')
@parent
<div class="modal fade" id="add-new-modal-invoice-duration">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-invoice-duration-form" class="form-horizontal form-groups-bordered" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Select Duration</h4>
                </div>
                <div class="modal-body">
                         <div class="form-group">
                            <label class="col-sm-2 control-label" for="field-1">Time From</label>
                            <div class="col-sm-6">
                                {{Form::text('start_date','',array("class"=>" form-control datepicker" ,"data-enddate"=>date('Y-m-d',strtotime(" -1 day")), "data-date-format"=>"yyyy-mm-dd"))}}
                            </div>
                            <div class="col-sm-4">
                                <input type="text" name="start_time" data-minute-step="5" data-show-meridian="false" data-default-time="00:00 AM" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
                            </div>
                         </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="field-1">Time To</label>
                            <div class="col-sm-6">
                                {{Form::text('end_date','',array("class"=>" form-control datepicker" , "data-enddate"=>date('Y-m-d'), "data-date-format"=>"yyyy-mm-dd"))}}
                            </div>
                            <div class="col-sm-4">
                                <input type="text" name="end_time" data-minute-step="5" data-show-meridian="false" data-default-time="00:00 AM" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
                            </div>
                         </div>
                 </div>
                <div class="modal-footer">
                    <button type="submit" id="invoice-duration-select"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Select
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
