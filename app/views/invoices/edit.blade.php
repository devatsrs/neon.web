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
        <strong>Edit Invoice</strong>
    </li>
</ol>
<h3>Edit Invoice</h3>

@include('includes.errors')
@include('includes.success')

<form class="form-horizontal form-groups-bordered" method="post" id="invoice-from" role="form">
<div class="pull-right">
    @if(User::checkCategoryPermission('Invoice','Send'))
    <a href="Javascript:;" class="send-invoice btn btn-sm btn-success btn-icon icon-left hidden-print">
        Send Invoice
        <i class="entypo-mail"></i>
    </a>
    @endif
    &nbsp;
    <a target="_blank" href="{{URL::to('/invoice/'.$Invoice->InvoiceID.'/invoice_preview')}}" class="btn btn-sm btn-danger btn-icon icon-left hidden-print">
        Print Invoice
        <i class="entypo-doc-text"></i>
    </a>
    &nbsp;
    <button type="submit" class="btn save btn-primary btn-sm btn-icon icon-left hidden-print" data-loading-text="Loading...">
        Save Invoice
        <i class="entypo-floppy"></i>
    </button>
    <a href="{{URL::to('/invoice')}}" class="btn btn-danger btn-sm btn-icon icon-left">
                <i class="entypo-cancel"></i>
                Close
        </a>
</div>
<div class="clearfix"></div>
<br/>

<div class="panel panel-primary" data-collapsed="0">

        <div class="panel-body">
            <div class="form-group">

                <div class="col-sm-6">
                <label for="field-1" class="col-sm-2 control-label">*Client</label>
                <div class="col-sm-6">
                        {{Form::select('AccountID',$accounts,$Invoice->AccountID,array("class"=>"select2" ,"disabled"=>"disabled"))}}
                        {{Form::hidden('AccountID',$Invoice->AccountID)}}
                </div>
                 <div class="clearfix margin-bottom "></div>
                <label for="field-1" class="col-sm-2 control-label">*Address</label>
                <div class="col-sm-6">

                        {{Form::textarea('Address',$Invoice->Address,array( "ID"=>"Account_Address", "rows"=>4, "class"=>"form-control"))}}
                </div>

                <div class="clearfix margin-bottom "></div>

                </div>
                <div class="col-sm-6">
                    <label for="field-1" class="col-sm-7 control-label">*Invoice Number</label>
                    <div class="col-sm-5">
                        {{Form::text('InvoiceNumber',$Invoice->InvoiceNumber,array("class"=>"form-control","readonly"=>"readonly"))}}
                    </div>
                    <br /><br />
                    <label for="field-1" class="col-sm-7 control-label">*Date of issue</label>
                    <div class="col-sm-5">
                        {{Form::text('IssueDate',date('Y-m-d',strtotime($Invoice->IssueDate)),array("class"=>" form-control datepicker" , "data-startdate"=>date('Y-m-d',strtotime("-2 month")),  "data-date-format"=>"yyyy-mm-dd", "data-end-date"=>"+1w" ,"data-start-view"=>"2"))}}
                    </div>
                    <br /><br />
                    <label for="field-1" class="col-sm-7 control-label">PO Number</label>
                    <div class="col-sm-5">
                        {{Form::text('PONumber',$Invoice->PONumber,array("class"=>" form-control" ))}}
                    </div>
                </div>
                </div>
               <div class="form-group">
                <div class="col-sm-12">


                	<table id="InvoiceTable" class="table table-bordered" style="margin-bottom: 0">
                		<thead>
                			<tr>
                				<th  width="1%" ><button type="button" id="add-row" class="btn btn-primary btn-xs ">+</button></th>
                				<th  width="20%" >Item</th>
                                <th width="20%" >Description</th>
                                <th width="10%" class="text-center">Unit Price</th>
                                <th width="10%"  class="text-center">Quantity</th>
                                <th width="10%" >Discount</th>
                                <th width="10%" >Tax Rate </th>
                                <th width="10%" >Tax</th>
                                <th width="20%" class="text-right">Line Total</th>
                			</tr>
                		</thead>
                		<tbody>
                		    @if(count($InvoiceDetail)>0)
                		    @foreach($InvoiceDetail as $ProductRow)
                			<tr>
                			    <td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td>
                                <td>{{Form::select('InvoiceDetail[ProductID][]',$products,$ProductRow->ProductID,array("class"=>"select2 product_dropdown"))}}</td>
                                <td>{{Form::text('InvoiceDetail[Description][]',$ProductRow->Description,array("class"=>"form-control description"))}}</td>
                                <td class="text-center">{{Form::text('InvoiceDetail[Price][]', number_format($ProductRow->Price,$RoundChargesAmount),array("class"=>"form-control Price","data-mask"=>"fdecimal"))}}</td>
                                <td class="text-center">{{Form::text('InvoiceDetail[Qty][]',$ProductRow->Qty,array("class"=>"form-control Qty","data-min"=>"1", "data-mask"=>"decimal"))}}</td>
                                <td class="text-center">{{Form::text('InvoiceDetail[Discount][]',number_format($ProductRow->Discount,$RoundChargesAmount),array("class"=>"form-control Discount","data-min"=>"1", "data-mask"=>"fdecimal"))}}</td>
                                <td>{{Form::SelectExt(
                                        [
                                        "name"=>"InvoiceDetail[TaxRateID][]",
                                        "data"=>$taxes,
                                        "selected"=>$ProductRow->TaxRateID,
                                        "value_key"=>"TaxRateID",
                                        "title_key"=>"Title",
                                        "title_key"=>"Title",
                                        "data-title1"=>"data-amount",
                                        "data-value1"=>"Amount",
                                        "class" =>"selectboxit TaxRateID",
                                        ]
                                )}}</td>
                                <td>{{Form::text('InvoiceDetail[TaxAmount][]',number_format($ProductRow->TaxAmount,$RoundChargesAmount),array("class"=>"form-control TaxAmount","readonly"=>"readonly", "data-mask"=>"fdecimal"))}}</td>
                                <td>{{Form::text('InvoiceDetail[LineTotal][]',number_format($ProductRow->LineTotal,$RoundChargesAmount),array("class"=>"form-control LineTotal","data-min"=>"1", "data-mask"=>"fdecimal","readonly"=>"readonly"))}}
                                {{Form::hidden('InvoiceDetail[InvoiceDetailID][]',$ProductRow->InvoiceDetailID,array("class"=>"InvoiceDetailID"))}}
                                {{Form::hidden('InvoiceDetail[ProductType][]',$ProductRow->ProductType,array("class"=>"ProductType"))}}
                                </td>
                            </tr>
                            @endforeach
                            @endif
                		</tbody>

                	</table>

                </div>
            </div>
               <div class="form-group">
                <div class="col-sm-9">

                    <table  width="50%" >
                        <tr>
                            <td><label for="field-1" class=" control-label">*Terms</label></td>
                        </tr>
                        <tr>
                            <td>{{Form::textarea('Terms',$Invoice->Terms,array("class"=>" form-control" ,"rows"=>5))}}</td>
                        </tr>
                        <tr>
                            <td><label for="field-1" class=" control-label">Footer Note</label></td>
                        </tr>
                        <tr>
                            <td>{{Form::textarea('FooterTerm',$Invoice->FooterTerm,array("class"=>" form-control" ,"rows"=>5))}}</td>
                        </tr>
                        <tr>
                            <td><label for="field-1" class=" control-label">Note ( Will not be visible to customer )</label></td>
                        </tr>
                        <tr>
                            <td>{{Form::textarea('Note',$Invoice->Note,array("class"=>" form-control" ,"rows"=>5))}}</td>
                        </tr>
                    </table>

                </div>
                <div class="col-sm-3">
                    <table class="table table-bordered">
                    <tfoot>
                            <tr>
                                    <td >Sub Total</td>
                                    <td>{{Form::text('SubTotal',number_format($Invoice->SubTotal,$RoundChargesAmount),array("class"=>"form-control SubTotal text-right","readonly"=>"readonly"))}}</td>
                            </tr>
                            <tr>
                                    <td ><span class="product_tax_title">VAT</span> </td>
                                    <td>{{Form::text('TotalTax',number_format($Invoice->TotalTax,$RoundChargesAmount),array("class"=>"form-control TotalTax text-right","readonly"=>"readonly"))}}</td>
                            </tr>
                            <tr>
                                    <td>Discount </td>
                                    <td>{{Form::text('TotalDiscount',number_format($Invoice->TotalDiscount,$RoundChargesAmount),array("class"=>"form-control TotalDiscount text-right","readonly"=>"readonly"))}}</td>
                            </tr>
                            <tr>
                                    <td >Invoice Total </td>
                                    <td>{{Form::text('GrandTotal',number_format($Invoice->GrandTotal,$RoundChargesAmount),array("class"=>"form-control GrandTotal text-right","readonly"=>"readonly"))}}</td>
                            </tr>


               		</tfoot>
                    </table>

                </div>

               </div>

        </div>
</div>

<div class="pull-right">
    <input type="hidden" name="CurrencyID" value="{{$CurrencyID}}">
    <input type="hidden" name="CurrencyCode" value="{{$CurrencySymbol}}">
    <input type="hidden" name="InvoiceTemplateID" value="{{$InvoiceTemplateID}}">
</div>
</form>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th colspan="3">Invoice Biography</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Created</td>
        <td>Created By {{$Invoice->CreatedBy}}</td>
        <td>{{$Invoice->created_at}}</td>
    </tr>
    @foreach($invoicelog as $invoicelogrw)
        <tr>
        <td>{{InVoiceLog::$log_status[$invoicelogrw->InvoiceLogStatus]}}</td>
        <td>{{$invoicelogrw->Note}}</td>
        <td>{{$invoicelogrw->created_at}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<script type="text/javascript">
var invoice_id = '{{$Invoice->InvoiceID}}';
var decimal_places = '{{$RoundChargesAmount}}';

var subscription_array = [{{implode(",",array_keys(BillingSubscription::getSubscriptionsArray(User::get_companyID(),$CurrencyID)))}}];

var add_row_html = '<tr><td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td><td>{{Form::select('InvoiceDetail[ProductID][]',$products,'',array("class"=>"select2 product_dropdown"))}}</td><td>{{Form::text('InvoiceDetail[Description][]','',array("class"=>"form-control description"))}}</td><td class="text-center">{{Form::text('InvoiceDetail[Price][]','',array("class"=>"form-control Price","data-mask"=>"fdecimal"))}}</td><td class="text-center">{{Form::text('InvoiceDetail[Qty][]',1,array("class"=>"form-control Qty","data-min"=>"1", "data-mask"=>"decimal"))}}</td>'
     add_row_html += '<td class="text-center">{{Form::text('InvoiceDetail[Discount][]',0,array("class"=>"form-control Discount","data-min"=>"1", "data-mask"=>"fdecimal"))}}</td>';
     add_row_html += '<td>{{Form::SelectExt(["name"=>"InvoiceDetail[TaxRateID][]","data"=>$taxes,"selected"=>$ProductRow->TaxRateID,"value_key"=>"TaxRateID","title_key"=>"Title","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","class" =>"selectboxit TaxRateID"])}}</td>';
     add_row_html += '<td>{{Form::text('InvoiceDetail[TaxAmount][]','',array("class"=>"form-control TaxAmount","readonly"=>"readonly", "data-mask"=>"fdecimal"))}}</td>';
     add_row_html += '<td>{{Form::text('InvoiceDetail[LineTotal][]',0,array("class"=>"form-control LineTotal","data-min"=>"1", "data-mask"=>"fdecimal","readonly"=>"readonly"))}}';
     add_row_html += '{{Form::hidden('InvoiceDetail[StartDate][]','',array("class"=>"StartDate"))}}{{Form::hidden('InvoiceDetail[EndDate][]','',array("class"=>"EndDate"))}}{{Form::hidden('InvoiceDetail[ProductType][]',$ProductRow->ProductType,array("class"=>"ProductType"))}}</td></tr>';

</script>
@include('invoices.script_invoice_add_edit')
@include('includes.ajax_submit_script', array('formID'=>'invoice-from' , 'url' => 'invoice/'.$id.'/update' ))
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

<div class="modal fade custom-width" id="print-modal-invoice">
    <div class="modal-dialog" style="width: 60%;">
        <div class="modal-content">
            <form id="add-new-invoice_template-form" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><a href="{{URL::to('/invoice/'.$Invoice->InvoiceID.'/print')}}" class="btn btn-primary print btn-sm btn-icon icon-left" >
                                                                    <i class="entypo-print"></i>
                                                                    Print
                                                                 </a></h4>
                </div>
                <div class="modal-body">



                  </div>
                <div class="modal-footer">
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade in" id="send-modal-invoice">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="send-invoice-form" method="post" class="form-horizontal form-groups-bordered">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Send Invoice By Email</h4>
                </div>
                <div class="modal-body">


                   </div>
                <div class="modal-footer">
                     <button type="submit" class="btn btn-primary send btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-mail"></i>
                        Send
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
