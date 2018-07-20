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
            <strong>Edit Estimate</strong>
        </li>
    </ol>
    <h3>Edit Estimate</h3>

    @include('includes.errors')
    @include('includes.success')

    <form class="form-horizontal form-groups-bordered" method="post" id="estimate-from" role="form">
        <div class="pull-right">
            @if(User::checkCategoryPermission('Invoice','Send'))
                <a href="Javascript:;" class="send-estimate btn btn-sm btn-success btn-icon icon-left hidden-print">
                    Send
                    <i class="entypo-mail"></i>
                </a>
            @endif
            &nbsp;
            <a target="_blank" href="{{URL::to('/estimate/'.$Estimate->EstimateID.'/estimate_preview')}}" class="btn btn-sm btn-danger btn-icon icon-left hidden-print">
                Print
                <i class="entypo-doc-text"></i>
            </a>
            &nbsp;
            <button type="submit" class="btn save btn-primary btn-sm btn-icon icon-left hidden-print" data-loading-text="Loading...">
                Save
                <i class="entypo-floppy"></i>
            </button>
            <a href="{{URL::to('/estimates')}}" class="btn btn-danger btn-sm btn-icon icon-left">
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
                            {{Form::select('AccountID',$accounts,$Estimate->AccountID,array("class"=>"select2" ,"disabled"=>"disabled"))}}
                            {{Form::hidden('AccountID',$Estimate->AccountID)}}
                        </div>
                        <div class="clearfix margin-bottom "></div>
                        
                        <label for="field-1" class="col-sm-2 control-label">*Billing Class</label>
 				         <div class="col-sm-6">
                         {{Form::select('BillingClassID', $BillingClass,$EstimateBillingClass,array("class"=>"select2 small form-control1 small","id"=>"AccountBillingClassID","disabled"=>"disabled"));}}
                         </div>
			            <div class="clearfix margin-bottom "></div>
                        
                        <label for="field-1" class="col-sm-2 control-label">*Address</label>
                        <div class="col-sm-6">

                            {{Form::textarea('Address',$Estimate->Address,array( "ID"=>"Account_Address", "rows"=>4, "class"=>"form-control"))}}
                        </div>

                        <div class="clearfix margin-bottom "></div>

                    </div>
                    <div class="col-sm-6">
                        <label for="field-1" class="col-sm-7 control-label">*Estimate Number</label>
                        <div class="col-sm-5">
                            {{Form::text('EstimateNumber',$Estimate->EstimateNumber,array("class"=>"form-control","readonly"=>"readonly"))}}
                        </div>
                        <br /><br />
                        <label for="field-1" class="col-sm-7 control-label">*Date of issue</label>
                        <div class="col-sm-5">
                            {{Form::text('IssueDate',date('Y-m-d',strtotime($Estimate->IssueDate)),array("class"=>" form-control datepicker" , "data-startdate"=>date('Y-m-d',strtotime("-2 month")),  "data-date-format"=>"yyyy-mm-dd", "data-end-date"=>"+1w" ,"data-start-view"=>"2"))}}
                        </div>
                        <br /><br />
                        <label for="field-1" class="col-sm-7 control-label">PO Number</label>
                        <div class="col-sm-5">
                            {{Form::text('PONumber',$Estimate->PONumber,array("class"=>" form-control" ))}}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-12">
                        <div class="dataTables_wrapper">
                            <table id="EstimateTable" class="table table-bordered" style="margin-bottom: 0">
                                <thead>
                                <tr>
                                    <th  width="1%"><button type="button" id="add-row" class="btn btn-primary btn-xs ">+</button></th>
                                    <th  width="14%">Item/Subscription</th>
                                    <th width="15%">Description</th>
                                    <th width="10%">Unit Price</th>
                                    <th width="10%">Quantity</th>
                                    <!--<th width="10%" >Discount</th>-->
                                    <th width="15%">Tax 1</th>
                                    <th width="15%">Tax 2</th>
                                    <th class="hidden" width="10%" >Total Tax</th>
                                    <th width="10%">Line Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($EstimateDetail)>0)
                                    @foreach($EstimateDetail as $ProductRow)
                                        <tr>
                                            <td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td>
                                            <td>{{Form::SelectControl('item_and_Subscription',0,['Type'=>$ProductRow->ProductType,'ID'=>$ProductRow->ProductID],0,'EstimateDetail[ProductID][]')}}</td>
                                            <td>{{Form::textarea('EstimateDetail[Description][]',$ProductRow->Description,array("class"=>"form-control autogrow descriptions invoice_estimate_textarea","rows"=>1))}}</td>
                                            <td class="text-center">{{Form::text('EstimateDetail[Price][]', number_format($ProductRow->Price,$RoundChargesAmount),array("class"=>"form-control Price","data-mask"=>"fdecimal"))}}</td>
                                            <td class="text-center">{{Form::text('EstimateDetail[Qty][]',$ProductRow->Qty,array("class"=>"form-control Qty","data-min"=>"1", "data-mask"=>"decimal"))}}</td>
                                            <!--  <td class="text-center">{{Form::text('EstimateDetail[Discount][]',number_format($ProductRow->Discount,$RoundChargesAmount),array("class"=>"form-control Discount","data-min"=>"1", "data-mask"=>"fdecimal"))}}</td>-->
                                            <td>
                                                {{Form::SelectExt(
                                                    [
                                                    "name"=>"EstimateDetail[TaxRateID][]",
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
                                                    "name"=>"EstimateDetail[TaxRateID2][]",
                                                    "data"=>$taxes,
                                                    "selected"=>$ProductRow->TaxRateID2,
                                                    "value_key"=>"TaxRateID",
                                                    "title_key"=>"Title",
                                                    "data-title1"=>"data-amount",
                                                    "data-value1"=>"Amount",
                                                    "data-title2"=>"data-flatstatus",
                                                    "data-value2"=>"FlatStatus",
                                                    "class" =>"select2 small Taxentity TaxRateID2",
                                                ])}}
                                            </td>
                                            <td class="hidden">{{Form::text('EstimateDetail[TaxAmount][]',number_format($ProductRow->TaxAmount,$RoundChargesAmount),array("class"=>"form-control TaxAmount","readonly"=>"readonly", "data-mask"=>"fdecimal"))}}</td>
                                            <td>{{Form::text('EstimateDetail[LineTotal][]',number_format($ProductRow->LineTotal,$RoundChargesAmount),array("class"=>"form-control LineTotal","data-min"=>"1", "data-mask"=>"fdecimal","readonly"=>"readonly"))}}
                                                {{Form::hidden('EstimateDetail[EstimateDetailID][]',$ProductRow->EstimateDetailID,array("class"=>"EstimateDetailID"))}}
                                                {{Form::hidden('EstimateDetail[ProductType][]',$ProductRow->ProductType,array("class"=>"ProductType"))}}
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
                                <td>{{Form::textarea('Terms',$Estimate->Terms,array("class"=>" form-control" ,"rows"=>5))}}</td>
                            </tr>
                            <tr>
                                <td><label for="field-1" class=" control-label">Footer Note</label></td>
                            </tr>
                            <tr>
                                <td>{{Form::textarea('FooterTerm',$Estimate->FooterTerm,array("class"=>" form-control" ,"rows"=>5))}}</td>
                            </tr>
                            <tr>
                                <td><label for="field-1" class=" control-label">Note ( Will not be visible to customer )</label></td>
                            </tr>
                            <tr>
                                <td>{{Form::textarea('Note',$Estimate->Note,array("class"=>" form-control" ,"rows"=>5))}}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-1"></div>
                    <div class="col-md-5">
                        <table id="summary" class="table table-bordered">
                            <tfoot>
                            <!--<tr>
                                    <td >Sub Total</td>
                                    <td>{{Form::text('SubTotal',number_format($Estimate->SubTotal,$RoundChargesAmount),array("class"=>"form-control SubTotal text-right","readonly"=>"readonly"))}}</td>
                            </tr>-->
                            <!--<tr class="tax_rows_estimate">
                                    <td ><span class="product_tax_title">VAT</span> </td>
                                    <td>{{Form::text('TotalTax',number_format($Estimate->TotalTax,$RoundChargesAmount),array("class"=>"form-control TotalTax text-right","readonly"=>"readonly"))}}</td>
                            </tr>-->
                            <!--<tr>
                                    <td>Discount </td>
                                    <td>{{Form::text('TotalDiscount',number_format($Estimate->TotalDiscount,$RoundChargesAmount),array("class"=>"form-control TotalDiscount text-right","readonly"=>"readonly"))}}</td>
                            </tr>
-->                         <tr class="grand_total_estimate">
                                <td >Estimate Total </td>
                                <td>{{Form::text('GrandTotal',number_format($Estimate->GrandTotal,$RoundChargesAmount),array("class"=>"form-control GrandTotal text-right","readonly"=>"readonly"))}}</td>
                            </tr>
                            @if(count($EstimateAllTax)>0)
                                @foreach($EstimateAllTax as $key => $EstimateAllTaxData)

                                    <tr class="  @if($key==0) estimate_tax_row @else all_tax_row @endif">
                                        @if($key==0)
                                            <td>  <button title="Add new Tax" type="button" class="btn btn-primary btn-xs estimate_tax_add ">+</button>   &nbsp; Tax </td>
                                        @else
                                            <td>
                                                <button title="Delete Tax" type="button" class="btn btn-danger btn-xs estimate_tax_remove ">X</button>
                                            </td>
                                        @endif
                                        <td>
                                            <div class="col-md-8"> {{Form::SelectExt(
                                                                    [
                                                                    "name"=>"EstimateTaxes[field][]",
                                                                    "data"=>$taxes,
                                                                    "selected"=>$EstimateAllTaxData->TaxRateID,
                                                                    "value_key"=>"TaxRateID",
                                                                    "title_key"=>"Title",
                                                                    "data-title1"=>"data-amount",
                                                                    "data-value1"=>"Amount",
                                                                    "data-title2"=>"data-flatstatus",
                                                                    "data-value2"=>"FlatStatus",
                                                                    "class" =>"select2 small Taxentity EstimateTaxesFld  EstimateTaxesFldFirst",
                                                                    ]
                                                                    )}}
                                            </div>
                                            <div class="col-md-4"> {{Form::text('EstimateTaxes[value][]',$EstimateAllTaxData->TaxAmount,array("class"=>"form-control EstimateTaxesValue","readonly"=>"readonly"))}} </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="estimate_tax_row">
                                    <td>
                                        <button title="Add new Tax" type="button" class="btn btn-primary btn-xs estimate_tax_add ">+</button>
                                        &nbsp; Tax </td>
                                    <td>
                                        <div class="col-md-8"> {{Form::SelectExt(
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
                                                                )}}
                                        </div>
                                        <div class="col-md-4"> {{Form::text('EstimateTaxes[value][]','',array("class"=>"form-control EstimateTaxesValue","readonly"=>"readonly"))}} </div></td>
                                </tr>
                            @endif
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
            <input type="hidden" name="CurrencyID" value="{{$CurrencyID}}">
            <input type="hidden" name="CurrencyCode" value="{{$CurrencyCode}}">
            <input type="hidden" name="InvoiceTemplateID" value="{{$EstimateTemplateID}}">
            <input type="hidden" name="TotalTax" value="" >
        </div>
    </form>
    <div id="rowContainer"></div>
    <script type="text/javascript">
        var estimate_id = '{{$Estimate->EstimateID}}';
        var decimal_places = '{{$RoundChargesAmount}}';

        var subscription_array = [{{implode(",",array_keys(BillingSubscription::getSubscriptionsArray(User::get_companyID(),$CurrencyID)))}}];

        var estimate_tax_html = '<td><button title="Delete Tax" type="button" class="btn btn-danger btn-xs estimate_tax_remove ">X</button></td><td><div class="col-md-8">{{addslashes(Form::SelectExt(["name"=>"EstimateTaxes[field][]","data"=>$taxes,"selected"=>'',"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select2 Taxentity small EstimateTaxesFld"]))}}</div><div class="col-md-4">{{Form::text("EstimateTaxes[value][]","",array("class"=>"form-control EstimateTaxesValue","readonly"=>"readonly"))}}</div></td>';

        var add_row_html = '<tr class="itemrow hidden"><td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td><td>{{Form::SelectControl('item_and_Subscription',0,'',0,'EstimateDetail[ProductID][]',0)}}</td><td>{{Form::textarea('EstimateDetail[Description][]','',array("class"=>"form-control invoice_estimate_textarea autogrow descriptions","rows"=>1))}}</td><td class="text-center">{{Form::text('EstimateDetail[Price][]',"0",array("class"=>"form-control Price","data-mask"=>"fdecimal"))}}</td><td class="text-center">{{Form::text('EstimateDetail[Qty][]',1,array("class"=>"form-control Qty","data-min"=>"1", "data-mask"=>"decimal"))}}</td>'
        add_row_html += '<td class="text-center hidden">{{Form::text('EstimateDetail[Discount][]',0,array("class"=>"form-control Discount","data-min"=>"1", "data-mask"=>"fdecimal"))}}</td>';
        add_row_html += '<td>{{addslashes(Form::SelectExt(["name"=>"EstimateDetail[TaxRateID][]","data"=>$taxes,"selected"=>'',"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select22 Taxentity small TaxRateID"]))}}</td>';
        add_row_html += '<td>{{addslashes(Form::SelectExt(["name"=>"EstimateDetail[TaxRateID2][]","data"=>$taxes,"selected"=>'',"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select22 Taxentity small TaxRateID2"]))}}</td>';

        add_row_html += '<td class="hidden">{{Form::text('EstimateDetail[TaxAmount][]',"0",array("class"=>"form-control TaxAmount","readonly"=>"readonly", "data-mask"=>"fdecimal"))}}</td>';
        add_row_html += '<td>{{Form::text('EstimateDetail[LineTotal][]',0,array("class"=>"form-control LineTotal","data-min"=>"1", "data-mask"=>"fdecimal","readonly"=>"readonly"))}}';
        add_row_html += '{{Form::hidden('EstimateDetail[ProductType][]',Product::ITEM,array("class"=>"ProductType"))}}</td></tr>';
        $('#rowContainer').append(add_row_html);
    </script>
    @include('estimates.script_estimate_add_edit')
    @include('composetmodels.productsubscriptionmodal')
    @include('includes.ajax_submit_script', array('formID'=>'estimate-from' , 'url' => 'estimate/'.$id.'/update' ))
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
                        <h4 class="modal-title"><a href="{{URL::to('/estimate/'.$Estimate->EstimateID.'/print')}}" class="btn btn-primary print btn-sm btn-icon icon-left" >
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

    <div class="modal fade in" id="send-modal-estimate">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="send-estimate-form" method="post" >
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Send Estimate By Email</h4>
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
