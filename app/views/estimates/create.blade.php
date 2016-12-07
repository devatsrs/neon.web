@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <a href="{{URL::to('estimates')}}">Estimate</a>
    </li>
    <li class="active">
        <strong>Create Estimate</strong>
    </li>
</ol>
<h3>Create Estimate</h3>

@include('includes.errors')
@include('includes.success')

<form class="form-horizontal form-groups-bordered" action="{{URL::to('/estimate/store')}}" method="post" id="estimate-from" role="form">

<p class="text-right">
    <button type="submit" class="btn save btn-primary btn-icon btn-sm icon-left hidden-print" data-loading-text="Loading...">
        Save Estimate
        <i class="entypo-mail"></i>
   </button>
    <a href="{{URL::to('/estimates')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
    </a>

</p>
<div class="panel panel-primary" data-collapsed="0">

        <div class="panel-body">
            <div class="form-group">

                <div class="col-sm-6">
                <label for="field-1" class="col-sm-2 control-label">*Client</label>
                <div class="col-sm-6">
                        {{Form::select('AccountID',$accounts,'',array("class"=>"select2"))}}
                </div>
                 <div class="clearfix margin-bottom "></div>
                <label for="field-1" class="col-sm-2 control-label">*Address</label>
                <div class="col-sm-6">

                        {{Form::textarea('Address','',array( "ID"=>"Account_Address", "rows"=>4, "class"=>"form-control"))}}
                </div>

                <div class="clearfix margin-bottom "></div>

                </div>
                <div class="col-sm-6">
                    <label for="field-1" class="col-sm-7 control-label">*Estimate Number</label>
                    <div class="col-sm-5">
                        {{Form::text('EstimateNumber','',array("Placeholder"=>"AUTO", "class"=>"form-control"))}}
                    </div>
                    <br /><br />
                    <label for="field-1" class="col-sm-7 control-label">*Date of issue</label>
                    <div class="col-sm-5">
                        {{Form::text('IssueDate',date('Y-m-d'),array("class"=>" form-control datepicker" , "data-startdate"=>date('Y-m-d',strtotime("-2 month")),  "data-date-format"=>"yyyy-mm-dd", "data-end-date"=>"+1w" ,"data-start-view"=>"2"))}}
                    </div>
                    <br /><br />
                    <label for="field-1" class="col-sm-7 control-label">PO Number</label>
                    <div class="col-sm-5">
                        {{Form::text('PONumber','',array("class"=>" form-control" ))}}
                    </div>
                </div>
                </div>
               <div class="form-group">
                <div class="col-md-12">
                    <div class="dataTables_wrapper">
                        <table id="EstimateTable" class="table table-bordered" style="margin-bottom: 0">
                            <thead>
                                <tr>
                                    <th  width="1%" ><button type="button" id="add-row" class="btn btn-primary btn-xs ">+</button></th>
                                    <th  width="14%" >Item</th>
                                    <th width="15%" >Description</th>
                                    <th width="10%" class="text-center">Unit Price</th>
                                    <th width="10%"  class="text-center">Quantity</th>
                                   <!-- <th width="10%" >Discount</th>-->
                                    <th width="15%" >Tax 1</th>
                                    <th width="15%" >Tax 2</th>
                                    <th class="hidden" width="10%" >Total Tax</th>
                                    <th width="10%" class="text-right">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td>
                                    <td>{{Form::select('EstimateDetail[ProductID][]',$products,'',array("class"=>"select2 product_dropdown"))}}</td>
                                    <td>{{Form::textarea('EstimateDetail[Description][]','',array("class"=>"form-control autogrow descriptions invoice_estimate_textarea","rows"=>1))}}</td>
                                    <td class="text-center">{{Form::text('EstimateDetail[Price][]','',array("class"=>"form-control Price","data-mask"=>"fdecimal"))}}</td>
                                    <td class="text-center">{{Form::text('EstimateDetail[Qty][]',1,array("class"=>"form-control Qty","data-min"=>"1", "data-mask"=>"decimal"))}}</td>
                                    <td style="display:none;" class="text-center">{{Form::text('EstimateDetail[Discount][]',0,array("class"=>"form-control Discount","data-min"=>"1", "data-mask"=>"fdecimal"))}}</td>
                                    <td>{{Form::SelectExt(
                                            [
                                            "name"=>"EstimateDetail[TaxRateID][]",
                                            "data"=>$taxes,
                                            "selected"=>'',
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
                                        "name"=>"EstimateDetail[TaxRateID2][]",
                                        "data"=>$taxes,
                                        "selected"=>'',
                                        "value_key"=>"TaxRateID",
                                        "title_key"=>"Title",
                                        "data-title1"=>"data-amount",
                                        "data-value1"=>"Amount",
                                        "data-title2"=>"data-flatstatus",
                                        "data-value2"=>"FlatStatus",
                                        "class" =>"select2 small Taxentity TaxRateID2",
                                        ]
                                )}}</td>

                                    <td class="hidden">{{Form::text('EstimateDetail[TaxAmount][]','',array("class"=>"form-control TaxAmount","readonly"=>"readonly", "data-mask"=>"fdecimal"))}}</td>
                                    <td>{{Form::text('EstimateDetail[LineTotal][]',0,array("class"=>"form-control LineTotal","data-min"=>"1", "data-mask"=>"fdecimal","readonly"=>"readonly"))}}
                                    {{Form::hidden('EstimateDetail[ProductType][]',Product::ITEM,array("class"=>"ProductType"))}}
                                    </td>
                                </tr>



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
                            <td>{{Form::textarea('Terms','',array("class"=>" form-control" ,"rows"=>5))}}</td>
                        </tr>
                        <tr>
                            <td><label for="field-1" class=" control-label">Footer Note</label></td>
                        </tr>
                        <tr>
                            <td>{{Form::textarea('FooterTerm','',array("class"=>" form-control" ,"rows"=>5))}}</td>
                        </tr>
                        <tr>
                            <td><label for="field-1" class=" control-label">Note ( Will not be visible to customer )</label></td>
                        </tr>
                        <tr>
                            <td>{{Form::textarea('Note','',array("class"=>" form-control" ,"rows"=>5))}}</td>
                        </tr>
                    </table>

                </div>
                   <div class="col-md-1"></div>
                <div class="col-md-5">
                    <table class="table table-bordered">
                    <tfoot>
                            <tr>
                                    <td >Sub Total</td>
                                    <td>{{Form::text('SubTotal','',array("class"=>"form-control SubTotal text-right","readonly"=>"readonly"))}}</td>
                            </tr>
                            <tr class="tax_rows_estimate">
                                    <td ><span class="product_tax_title">VAT</span> </td>
                                    <td>{{Form::text('TotalTax','',array("class"=>"form-control TotalTax text-right","readonly"=>"readonly"))}}</td>
                            </tr>
                           <!-- <tr>
                                    <td>Discount </td>
                                    <td>{{Form::text('TotalDiscount','',array("class"=>"form-control TotalDiscount text-right","readonly"=>"readonly"))}}</td>
                            </tr>-->
                            <tr class="grand_total_estimate">
                                    <td >Estimate Total </td>
                                    <td>{{Form::text('GrandTotal','',array("class"=>"form-control GrandTotal text-right","readonly"=>"readonly"))}}</td>
                            </tr>
                            <tr class="estimate_tax_row">
                                <td>
                                <button title="Add new Tax" type="button" class="btn btn-primary btn-xs estimate_tax_add ">+</button>                
                                  &nbsp; Tax </td>
                                <td><div class="col-md-8"> {{Form::SelectExt(
                                    [
                                    "name"=>"EstimateTaxes[field][]",
                                    "data"=>$taxes,
                                    "selected"=>'',
                                    "value_key"=>"TaxRateID",
                                    "title_key"=>"Title",
                                    "data-title1"=>"data-amount",
                                    "data-value1"=>"Amount",
                                    "data-title2"=>"data-flatstatus",
                                    "data-value2"=>"FlatStatus",
                                    "class" =>"select2 small Taxentity EstimateTaxesFld  EstimateTaxesFldFirst",
                                    ]
                                    )}}</div>
                                  <div class="col-md-4"> {{Form::text('EstimateTaxes[value][]','',array("class"=>"form-control EstimateTaxesValue","readonly"=>"readonly"))}} </div></td>
                              </tr>
                              <tr class="gross_total_estimate">
                                <td >Grand Total </td>
                                <td>{{Form::text('GrandTotalEstimate','',array("class"=>"form-control GrandTotalEstimate text-right","readonly"=>"readonly"))}}</td>
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
    <input type="hidden" name="EstimateTemplateID" value="">
    <input  type="hidden" name="TotalTax" value="" > 
</div>
</form>
<script type="text/javascript">
var decimal_places = 2;
var estimate_id = '';

var estimate_tax_html = '<td><button title="Delete Tax" type="button" class="btn btn-danger btn-xs estimate_tax_remove ">X</button></td><td><div class="col-md-8">{{Form::SelectExt(["name"=>"EstimateTaxes[field][]","data"=>$taxes,"selected"=>'',"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select2 Taxentity small EstimateTaxesFld"])}}</div><div class="col-md-4">{{Form::text("EstimateTaxes[value][]","",array("class"=>"form-control EstimateTaxesValue","readonly"=>"readonly"))}}</div></td>';

var add_row_html = '<tr><td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td><td>{{Form::select('EstimateDetail[ProductID][]',$products,'',array("class"=>"select2 product_dropdown"))}}</td><td>{{Form::textarea('EstimateDetail[Description][]','',array("class"=>"form-control invoice_estimate_textarea autogrow descriptions","rows"=>1))}}</td><td class="text-center">{{Form::text('EstimateDetail[Price][]',"0",array("class"=>"form-control Price","data-mask"=>"fdecimal"))}}</td><td class="text-center">{{Form::text('EstimateDetail[Qty][]',1,array("class"=>"form-control Qty","data-min"=>"1", "data-mask"=>"decimal"))}}</td>'
    // add_row_html += '<td class="text-center">{{Form::text('EstimateDetail[Discount][]',0,array("class"=>"form-control Discount","data-min"=>"1", "data-mask"=>"fdecimal"))}}</td>';
     add_row_html += '<td>{{Form::SelectExt(["name"=>"EstimateDetail[TaxRateID][]","data"=>$taxes,"selected"=>'',"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select2 Taxentity small TaxRateID"])}}</td>';
	   add_row_html += '<td>{{Form::SelectExt(["name"=>"EstimateDetail[TaxRateID2][]","data"=>$taxes,"selected"=>'',"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select2 Taxentity small TaxRateID2"])}}</td>';
	 
     add_row_html += '<td class="hidden">{{Form::text('EstimateDetail[TaxAmount][]',"0",array("class"=>"form-control TaxAmount","readonly"=>"readonly", "data-mask"=>"fdecimal"))}}</td>';
     add_row_html += '<td>{{Form::text('EstimateDetail[LineTotal][]',0,array("class"=>"form-control LineTotal","data-min"=>"1", "data-mask"=>"fdecimal","readonly"=>"readonly"))}}';
     add_row_html += '{{Form::hidden('EstimateDetail[ProductType][]',Product::ITEM,array("class"=>"ProductType"))}}</td></tr>';



function ajax_form_success(response){
    if(typeof response.redirect != 'undefined' && response.redirect != ''){
        window.location = response.redirect;
    }
}
</script>
@include('estimates.script_estimate_add_edit')
@include('includes.ajax_submit_script', array('formID'=>'estimate-from' , 'url' => 'estimate/store','update_url'=>'estimate/{id}/update' ))
@stop
@section('footer_ext')
@parent
<div class="modal fade" id="add-new-modal-estimate-duration">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-estimate-duration-form" class="form-horizontal form-groups-bordered" method="post">
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
                    <button type="submit" id="estimate-duration-select"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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