@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <a href="{{URL::to('recurringinvoices')}}">Recurring Invoice</a>
    </li>
    <li class="active">
        <strong>Edit Recurring Invoice</strong>
    </li>
</ol>
<h3>Edit Recurring Invoice</h3>

@include('includes.errors')
@include('includes.success')

<form class="form-horizontal form-groups-bordered" method="post" id="recurringinvoice-from" role="form">
<div class="pull-right">
    @if(User::checkCategoryPermission('Invoice','Send'))
    <a href="Javascript:;" class="send-recurringinvoice btn btn-sm btn-success btn-icon icon-left hidden-print">
        Send Recurring Invoice
        <i class="entypo-mail"></i>
    </a>
    @endif
    &nbsp;
    <button type="submit" class="btn save btn-primary btn-sm btn-icon icon-left hidden-print" data-loading-text="Loading...">
        Save Recurring Invoice
        <i class="entypo-floppy"></i>
    </button>
    <a href="{{URL::to('/recurringinvoices')}}" class="btn btn-danger btn-sm btn-icon icon-left">
                <i class="entypo-cancel"></i>
                Close
        </a>
</div>
<div class="clearfix"></div>
<br/>

<div class="panel panel-primary" data-collapsed="0">

        <div class="panel-body">
            <div class="form-group">

                <div class="col-sm-4">
                    <label for="field-1" class="col-sm-2 control-label">*Client</label>
                    <div class="col-sm-10">
                        {{Form::select('AccountID',$accounts,$RecurringInvoice->AccountID,array("class"=>"select2" ,"disabled"=>"disabled"))}}
                        {{Form::hidden('AccountID',$RecurringInvoice->AccountID)}}
                    </div>
                    <div class="clearfix margin-bottom "></div>
                    <label for="field-1" class="col-sm-2 control-label">*Address</label>
                    <div class="col-sm-10">

                        {{Form::textarea('Address',$RecurringInvoice->Address,array( "ID"=>"Account_Address", "rows"=>4, "class"=>"form-control"))}}
                    </div>

                    <div class="clearfix margin-bottom "></div>

                </div>

                <div class="col-md-5">
                    <label for="field-1" class="col-sm-2 control-label">*Title</label>

                    <div class="col-sm-10">
                        {{Form::text('Title',$RecurringInvoice->Title,array("Placeholder"=>"", "class"=>"form-control"))}}
                    </div>
                    <br><br><br>
                    <label for="field-1" class="col-sm-2 control-label">*Invoice Template</label>
                    <div class="col-sm-10">
                        {{Form::select('InvoiceTemplateID',$invoiceTemplate,$RecurringInvoice->InvoiceTemplateID,array( "class"=>"select2 small"))}}
                    </div>
                    <br><br><br>
                    <label for="field-5" class="col-sm-2 control-label clear">Period</label>
                    <div class="col-sm-4">
                        {{Form::select('RecurringInvoice[Time]',array(""=>"Select","DAILY"=>"Daily",'MONTHLY'=>'Monthly','YEARLY'=>'Yearly'),(isset($RecurringInvoiceReminder->Time)?$RecurringInvoiceReminder->Time:''),array( "class"=>"select2 small"))}}
                    </div>

                    <label for="field-5" class="col-sm-2 control-label">Interval</label>
                    <div class="col-sm-4">
                        {{Form::select('RecurringInvoice[Interval]',array(),(isset($RecurringInvoiceReminder->Interval)?$RecurringInvoiceReminder->Interval:''),array( "class"=>"select2 small"))}}
                    </div>
                </div>

                <div class="no-padding-left no-padding-right col-md-3">
                    <label for="field-1" class="col-sm-5 control-label">*Start Date</label>

                    <div class="col-sm-7">
                        {{Form::text('StartDate',$RecurringInvoice->StartDate,array("class"=>" form-control datepicker" , "data-startdate"=>date('Y-m-d',strtotime("-2 month")),  "data-date-format"=>"yyyy-mm-dd", "data-end-date"=>"+1w" ,"data-start-view"=>"2"))}}
                    </div>
                    <br/><br/><br/>
                    <label for="field-1" class="col-sm-5 control-label">*End Date</label>

                    <div class="col-sm-7">
                        {{Form::text('EndDate',$RecurringInvoice->EndDate,array("class"=>" form-control datepicker" , "data-startdate"=>date('Y-m-d',strtotime("-2 month")),  "data-date-format"=>"yyyy-mm-dd", "data-end-date"=>"+1w" ,"data-start-view"=>"2"))}}
                    </div>
                    <br/><br/><br/>
                </div>
                </div>
               <div class="form-group">
                <div class="col-sm-12">
                    <div class="dataTables_wrapper">
                        <table id="RecurringInvoiceTable" class="table table-bordered" style="margin-bottom: 0">
                            <thead>
                                <tr>
                                    <th  width="1%" ><button type="button" id="add-row" class="btn btn-primary btn-xs ">+</button></th>
                                    <th  width="14%" >Item</th>
                                    <th width="15%" >Description</th>
                                    <th width="10%" class="text-center">Unit Price</th>
                                    <th width="10%"  class="text-center">Quantity</th>
                                    <!--<th width="10%" >Discount</th>-->
                                    <th width="15%" >Tax 1</th>
                                    <th width="15%" >Tax 2</th>
                                    <th width="10%" >Total Tax</th>
                                    <th width="10%" class="text-right">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($RecurringInvoiceDetail)>0)
                                @foreach($RecurringInvoiceDetail as $ProductRow)
                                <tr>
                                    <td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td>
                                    <td>{{Form::select('RecurringInvoiceDetail[ProductID][]',$products,$ProductRow->ProductID,array("class"=>"select2 product_dropdown"))}}</td>
                                    <td>{{Form::textarea('RecurringInvoiceDetail[Description][]',$ProductRow->Description,array("class"=>"form-control autogrow descriptions invoice_recurringinvoice_textarea","rows"=>1))}}</td>
                                    <td class="text-center">{{Form::text('RecurringInvoiceDetail[Price][]', number_format($ProductRow->Price,$RoundChargesAmount),array("class"=>"form-control Price","data-mask"=>"fdecimal"))}}</td>
                                    <td class="text-center">{{Form::text('RecurringInvoiceDetail[Qty][]',$ProductRow->Qty,array("class"=>"form-control Qty","data-min"=>"1", "data-mask"=>"decimal"))}}</td>
                                  <!--  <td class="text-center">{{Form::text('RecurringInvoiceDetail[Discount][]',number_format($ProductRow->Discount,$RoundChargesAmount),array("class"=>"form-control Discount","data-min"=>"1", "data-mask"=>"fdecimal"))}}</td>-->
                                    <td>{{Form::SelectExt(
                                            [
                                            "name"=>"RecurringInvoiceDetail[TaxRateID][]",
                                            "data"=>$taxes,
                                            "selected"=>$ProductRow->TaxRateID,
                                            "value_key"=>"TaxRateID",
                                            "title_key"=>"Title",
                                            "data-title1"=>"data-amount",
                                            "data-value1"=>"Amount",
                                            "data-title2"=>"data-flatstatus",
                                            "data-value2"=>"FlatStatus",
                                            "class" =>"select2 small Taxentity TaxRateID",
                                            ]
                                    )}}</td>
                                    
                                    <td>{{Form::SelectExt(
                                        [
                                        "name"=>"RecurringInvoiceDetail[TaxRateID2][]",
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
                                )}}</td>
                                
                                    <td>{{Form::text('RecurringInvoiceDetail[TaxAmount][]',number_format($ProductRow->TaxAmount,$RoundChargesAmount),array("class"=>"form-control TaxAmount","readonly"=>"readonly", "data-mask"=>"fdecimal"))}}</td>
                                    <td>{{Form::text('RecurringInvoiceDetail[LineTotal][]',number_format($ProductRow->LineTotal,$RoundChargesAmount),array("class"=>"form-control LineTotal","data-min"=>"1", "data-mask"=>"fdecimal","readonly"=>"readonly"))}}
                                    {{Form::hidden('RecurringInvoiceDetail[RecurringInvoiceDetailID][]',$ProductRow->RecurringInvoiceDetailID,array("class"=>"RecurringInvoiceDetailID"))}}
                                    {{Form::hidden('RecurringInvoiceDetail[ProductType][]',$ProductRow->ProductType,array("class"=>"ProductType"))}}
                                    </td>
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
                        <table>
                        <tr>
                            <td><label for="field-1" class=" control-label">*Terms</label></td>
                        </tr>
                        <tr>
                            <td>{{Form::textarea('Terms',$RecurringInvoice->Terms,array("class"=>" form-control" ,"rows"=>5))}}</td>
                        </tr>
                        <tr>
                            <td><label for="field-1" class=" control-label">Footer Note</label></td>
                        </tr>
                        <tr>
                            <td>{{Form::textarea('FooterTerm',$RecurringInvoice->FooterTerm,array("class"=>" form-control" ,"rows"=>5))}}</td>
                        </tr>
                        <tr>
                            <td><label for="field-1" class=" control-label">Note ( Will not be visible to customer )</label></td>
                        </tr>
                        <tr>
                            <td>{{Form::textarea('Note',$RecurringInvoice->Note,array("class"=>" form-control" ,"rows"=>5))}}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-1"></div>
                <div class="col-md-5">
                    <table class="table table-bordered">
                    <tfoot>
                            <tr>
                                    <td >Sub Total</td>
                                    <td>{{Form::text('SubTotal',number_format($RecurringInvoice->SubTotal,$RoundChargesAmount),array("class"=>"form-control SubTotal text-right","readonly"=>"readonly"))}}</td>
                            </tr>
                            <tr class="tax_rows_recurringinvoice">
                                    <td ><span class="product_tax_title">VAT</span> </td>
                                    <td>{{Form::text('TotalTax',number_format($RecurringInvoice->TotalTax,$RoundChargesAmount),array("class"=>"form-control TotalTax text-right","readonly"=>"readonly"))}}</td>
                            </tr>
                            <!--<tr>
                                    <td>Discount </td>
                                    <td>{{Form::text('TotalDiscount',number_format($RecurringInvoice->TotalDiscount,$RoundChargesAmount),array("class"=>"form-control TotalDiscount text-right","readonly"=>"readonly"))}}</td>
                            </tr>
-->                            <tr class="grand_total_recurringinvoice">
                                    <td >Recurring Invoice Total </td>
                                    <td>{{Form::text('GrandTotal',number_format($RecurringInvoice->GrandTotal,$RoundChargesAmount),array("class"=>"form-control GrandTotal text-right","readonly"=>"readonly"))}}</td>
                            </tr>
                            <?php if(count($RecurringInvoiceAllTax)>0){
				  foreach($RecurringInvoiceAllTax as $key => $RecurringInvoiceAllTaxData){
				   ?>
              <tr class="  @if($key==0) recurringinvoice_tax_row @else all_tax_row @endif">
                @if($key==0)                
                <td>  <button title="Add new Tax" type="button" class="btn btn-primary btn-xs recurringinvoice_tax_add ">+</button>   &nbsp; Tax </td>
                @else
                <td>
                 <button title="Delete Tax" type="button" class="btn btn-danger btn-xs recurringinvoice_tax_remove ">X</button>
                 </td>
                @endif  
                <td><div class="col-md-8"> {{Form::SelectExt(
                    [
                    "name"=>"RecurringInvoiceTaxes[field][]",
                    "data"=>$taxes,
                    "selected"=>$RecurringInvoiceAllTaxData->TaxRateID,
                    "value_key"=>"TaxRateID",
                    "title_key"=>"Title",
                    "data-title1"=>"data-amount",
                    "data-value1"=>"Amount",
                    "data-title2"=>"data-flatstatus",
                    "data-value2"=>"FlatStatus",
                    "class" =>"select2 small Taxentity RecurringInvoiceTaxesFld  RecurringInvoiceTaxesFldFirst",
                    ]
                    )}}</div>
                  <div class="col-md-4"> {{Form::text('RecurringInvoiceTaxes[value][]',$RecurringInvoiceAllTaxData->TaxAmount,array("class"=>"form-control RecurringInvoiceTaxesValue","readonly"=>"readonly"))}} </div></td>
              </tr>
              <?php } } ?>
              <tr class="gross_total_recurringinvoice">
                <td >Grand Total </td>
                <td>{{Form::text('GrandTotalRecurringInvoice','',array("class"=>"form-control GrandTotalRecurringInvoice text-right","readonly"=>"readonly"))}}</td>
              </tr>


               		</tfoot>
                    </table>

                </div>

               </div>

        </div>
</div>

<div class="pull-right">
    <input type="hidden" name="CurrencyID" value="{{$CurrencyID}}">
    <input type="hidden" name="CurrencyCode" value="{{$CurrencyCode}}">
    <input type="hidden" name="InvoiceTemplateID" value="{{$RecurringInvoiceTemplateID}}">
    <input type="hidden" name="TotalTax" value="" > 
</div>
</form>

<script type="text/javascript">
var recurringinvoice_id = '{{$RecurringInvoice->RecurringInvoiceID}}';
var decimal_places = '{{$RoundChargesAmount}}';
var interval = '{{(isset($RecurringInvoiceReminder->Interval)?$RecurringInvoiceReminder->Interval:'')}}';

var subscription_array = [{{implode(",",array_keys(BillingSubscription::getSubscriptionsArray(User::get_companyID(),$CurrencyID)))}}];

var recurringinvoice_tax_html = '<td><button title="Delete Tax" type="button" class="btn btn-danger btn-xs recurringinvoice_tax_remove ">X</button></td><td><div class="col-md-8">{{Form::SelectExt(["name"=>"RecurringInvoiceTaxes[field][]","data"=>$taxes,"selected"=>'',"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select2 Taxentity small RecurringInvoiceTaxesFld"])}}</div><div class="col-md-4">{{Form::text("RecurringInvoiceTaxes[value][]","",array("class"=>"form-control RecurringInvoiceTaxesValue","readonly"=>"readonly"))}}</div></td>';

var add_row_html = '<tr><td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td><td>{{Form::select('RecurringInvoiceDetail[ProductID][]',$products,'',array("class"=>"select2 product_dropdown"))}}</td><td>{{Form::textarea('RecurringInvoiceDetail[Description][]','',array("class"=>"form-control autogrow descriptions invoice_recurringinvoice_textarea","rows"=>1))}}</td><td class="text-center">{{Form::text('RecurringInvoiceDetail[Price][]',"0",array("class"=>"form-control Price","data-mask"=>"fdecimal"))}}</td><td class="text-center">{{Form::text('RecurringInvoiceDetail[Qty][]',1,array("class"=>"form-control Qty","data-min"=>"1", "data-mask"=>"decimal"))}}</td>'
     //add_row_html += '<td class="text-center">{{Form::text('RecurringInvoiceDetail[Discount][]',0,array("class"=>"form-control Discount","data-min"=>"1", "data-mask"=>"fdecimal"))}}</td>';
     add_row_html += '<td>{{Form::SelectExt(["name"=>"RecurringInvoiceDetail[TaxRateID][]","data"=>$taxes,"selected"=>$ProductRow->TaxRateID,"value_key"=>"TaxRateID","title_key"=>"Title","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select2 small Taxentity TaxRateID"])}}</td>';
	    add_row_html += '<td>{{Form::SelectExt(["name"=>"RecurringInvoiceDetail[TaxRateID2][]","data"=>$taxes,"selected"=>'',"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select2 Taxentity small TaxRateID2"])}}</td>';
     add_row_html += '<td>{{Form::text('RecurringInvoiceDetail[TaxAmount][]',"0",array("class"=>"form-control TaxAmount","readonly"=>"readonly", "data-mask"=>"fdecimal"))}}</td>';
     add_row_html += '<td>{{Form::text('RecurringInvoiceDetail[LineTotal][]',0,array("class"=>"form-control LineTotal","data-min"=>"1", "data-mask"=>"fdecimal","readonly"=>"readonly"))}}';
     add_row_html += '{{Form::hidden('RecurringInvoiceDetail[StartDate][]','',array("class"=>"StartDate"))}}{{Form::hidden('RecurringInvoiceDetail[EndDate][]','',array("class"=>"EndDate"))}}{{Form::hidden('RecurringInvoiceDetail[ProductType][]',$ProductRow->ProductType,array("class"=>"ProductType"))}}</td></tr>';
</script>
@include('recurringinvoices.script_recurringinvoice_add_edit')
@include('includes.ajax_submit_script', array('formID'=>'recurringinvoice-from' , 'url' => 'recurringinvoices/'.$id.'/update' ))
<script>
    $(document).ready(function(){
        $("#recurringinvoice-from [name='RecurringInvoice[Time]']").change();
        $("#recurringinvoice-from [name='RecurringInvoice[Interval]']").val(interval).trigger('change');
    })
</script>
@stop
@section('footer_ext')
@parent

<div class="modal fade in" id="send-modal-recurringinvoice">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="send-recurringinvoice-form" method="post" class="form-horizontal form-groups-bordered">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Send Recurring Invoice By Email</h4>
                </div>
                <div class="modal-body">


                   </div>
                <input type="hidden" name="RecurringInvoice" value="1" >
                <input type="hidden" name="RecurringInvoiceID" value="{{$RecurringInvoice->RecurringInvoiceID}}" >
                <input type="hidden" name="selectedIDs" value="{{$RecurringInvoice->RecurringInvoiceID}}" >
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
