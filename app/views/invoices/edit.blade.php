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
    <style>
        .popover{
            max-width:350px;
            width:350px;
        }
    </style>
    <form class="form-horizontal form-groups-bordered" method="post" id="invoice-from" role="form">
        <div class="pull-right"> @if(User::checkCategoryPermission('Invoice','Send')) <a href="Javascript:;" class="send-invoice btn btn-sm btn-success btn-icon icon-left hidden-print"> Send <i class="entypo-mail"></i> </a> @endif
            &nbsp; <a target="_blank" href="{{URL::to('/invoice/'.$Invoice->InvoiceID.'/invoice_preview')}}" class="btn btn-sm btn-danger btn-icon icon-left hidden-print"> Print Invoice <i class="entypo-doc-text"></i> </a> &nbsp;
            <button type="submit" class="btn save btn-primary btn-sm btn-icon icon-left hidden-print" data-loading-text="Loading..."> Save<i class="entypo-floppy"></i> </button>
            <a href="{{URL::to('/invoice')}}" class="btn btn-danger btn-sm btn-icon icon-left"> <i class="entypo-cancel"></i> Close </a> </div>
        <div class="clearfix"></div>
        <br/>
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-body">
                <div class="form-group">
                    <div class="col-sm-4">
                        <label for="field-1" class="col-sm-3 control-label">*Client</label>
                        <div class="col-sm-9"> {{Form::select('AccountID',$accounts,$Invoice->AccountID,array("class"=>"select2" ,"disabled"=>"disabled"))}}
                            {{Form::hidden('AccountID',$Invoice->AccountID)}} </div>
                        <div class="clearfix margin-bottom "></div>
                          <label for="field-1" class="col-sm-3 control-label">*Billing Class</label>
 				         <div class="col-sm-9">{{Form::select('BillingClassID', $BillingClass,$InvoiceBillingClass,array("class"=>"select2 small form-control1 small","id"=>"AccountBillingClassID","disabled"=>"disabled"));}}</div>
			            <div class="clearfix margin-bottom "></div>
                        <label for="field-1" class="col-sm-3 control-label">*Address</label>
                        <div class="col-sm-9"> {{Form::textarea('Address',$Invoice->Address,array( "ID"=>"Account_Address", "rows"=>4, "class"=>"form-control"))}} </div>
                        <div class="clearfix margin-bottom "></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="field-1" class="col-sm-3 control-label">Barcode <span id="barcode_tooltip" data-original-title="Barcode" data-content="Scan item barcode in order to add item to the invoice." data-placement="bottom" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></label>
                        <div class="col-sm-9"> {{Form::text('BarCode','',array( "ID"=>"BarCode", "class"=>"form-control", "onkeypress"=>"validateBarCodeInput(event)"))}} </div>
                        <div class="clearfix margin-bottom "></div>
                    </div>
                    <div class="col-sm-4">
                        <label for="field-1" class="col-sm-4 control-label">*Invoice Number</label>
                        <div class="col-sm-8"> {{Form::text('InvoiceNumber',$Invoice->InvoiceNumber,array("class"=>"form-control","readonly"=>"readonly"))}} </div>
                        <br />
                        <br />
                        <label for="field-1" class="col-sm-4 control-label">*Date of issue</label>
                        <div class="col-sm-8"> {{Form::text('IssueDate',date('Y-m-d',strtotime($Invoice->IssueDate)),array("class"=>" form-control datepicker" , "data-startdate"=>date('Y-m-d',strtotime("-2 month")),  "data-date-format"=>"yyyy-mm-dd", "data-end-date"=>"+1w" ,"data-start-view"=>"2"))}} </div>
                        <br />
                        <br />
                        <label for="field-1" class="col-sm-4 control-label">PO Number</label>
                        <div class="col-sm-8"> {{Form::text('PONumber',$Invoice->PONumber,array("class"=>" form-control" ))}} </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-12">
                        <div class="dataTables_wrapper">
                            <table id="InvoiceTable" class="table table-bordered" style="margin-bottom: 0">
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

                                @if(count($InvoiceDetail)>0)
                                    @foreach($InvoiceDetail as $ProductRow)
                                        <tr>
                                            <td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td>
                                            <td>{{Form::SelectControl('item_and_Subscription',0,['Type'=>$ProductRow->ProductType,'ID'=>$ProductRow->ProductID],0,'InvoiceDetail[ProductID][]')}}</td>
                                            <td>{{Form::textarea('InvoiceDetail[Description][]',$ProductRow->Description,array("class"=>"form-control autogrow invoice_estimate_textarea descriptions","rows"=>1))}}</td>
                                            <td class="text-center">{{Form::text('InvoiceDetail[Price][]', number_format($ProductRow->Price,$RoundChargesAmount),array("class"=>"form-control Price","data-mask"=>"fdecimal"))}}</td>
                                            <td class="text-center">{{Form::text('InvoiceDetail[Qty][]',$ProductRow->Qty,array("class"=>"form-control Qty"))}}</td>
                                            <td class="text-center hidden">{{Form::text('InvoiceDetail[Discount][]',number_format($ProductRow->Discount,$RoundChargesAmount),array("class"=>"form-control Discount","data-min"=>"1", "data-mask"=>"fdecimal"))}}</td>
                                            <td>{{Form::SelectExt(
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
                                            </td>
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
                        <table>
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
                    <div class="col-md-1"></div>
                    <div class="col-md-5">
                        <table class="table table-bordered">
                            <tfoot>
                            <tr>
                                <td >Sub Total</td>
                                <td>{{Form::text('SubTotal',number_format($Invoice->SubTotal,$RoundChargesAmount),array("class"=>"form-control SubTotal text-right","readonly"=>"readonly"))}}</td>
                            </tr>
                            <tr class="tax_rows_invoice">
                                <td ><span class="product_tax_title">VAT</span> </td>
                                <td>{{Form::text('TotalTax',number_format($Invoice->TotalTax,$RoundChargesAmount),array("class"=>"form-control TotalTax text-right","readonly"=>"readonly"))}}</td>
                            </tr>
                            <!--<tr>
                            <td>Discount </td>
                            <td>{{Form::text('TotalDiscount',number_format($Invoice->TotalDiscount,$RoundChargesAmount),array("class"=>"form-control TotalDiscount text-right","readonly"=>"readonly"))}}</td>
                    </tr>-->
                            <tr class="grand_total_invoice">
                                <td >Invoice Total </td>
                                <td>{{Form::text('GrandTotal',number_format($Invoice->GrandTotal,$RoundChargesAmount),array("class"=>"form-control GrandTotal text-right","readonly"=>"readonly"))}}</td>
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
                                            <div class="col-md-4"> {{Form::text('InvoiceTaxes[value][]',$InvoiceAllTaxData->TaxAmount,array("class"=>"form-control InvoiceTaxesValue","readonly"=>"readonly"))}} </div>
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
                                        <div class="col-md-4"> {{Form::text('InvoiceTaxes[value][]','',array("class"=>"form-control InvoiceTaxesValue","readonly"=>"readonly"))}} </div></td>
                                </tr>
                            @endif
                            <tr class="gross_total_invoice">
                                <td >Grand Total </td>
                                <td>{{Form::text('GrandTotalInvoice','',array("class"=>"form-control GrandTotalInvoice text-right","readonly"=>"readonly"))}}</td>
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
            <input type="hidden" name="InvoiceTemplateID" value="{{$InvoiceTemplateID}}">
            <input  type="hidden" name="TotalTax" value="" >
        </div>
    </form>
    <div id="rowContainer"></div>
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

        var invoice_tax_html = '<td><button title="Delete Tax" type="button" class="btn btn-danger btn-xs invoice_tax_remove ">X</button></td><td><div class="col-md-8">{{addslashes(Form::SelectExt(["name"=>"InvoiceTaxes[field][]","data"=>$taxes,"selected"=>'',"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select2 Taxentity small InvoiceTaxesFld"]))}}</div><div class="col-md-4">{{Form::text("InvoiceTaxes[value][]","",array("class"=>"form-control InvoiceTaxesValue","readonly"=>"readonly"))}}</div></td>';


        var add_row_html = '<tr class="itemrow hidden"><td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td><td>{{Form::SelectControl('item_and_Subscription',0,'',0,'InvoiceDetail[ProductID][]',0)}}</td><td>{{Form::textarea('InvoiceDetail[Description][]','',array("class"=>"form-control descriptions autogrow invoice_estimate_textarea","rows"=>1))}}</td><td class="text-center">{{Form::text('InvoiceDetail[Price][]',"0",array("class"=>"form-control Price","data-mask"=>"fdecimal"))}}</td><td class="text-center">{{Form::text('InvoiceDetail[Qty][]',1,array("class"=>"form-control Qty"))}}</td>'
        add_row_html += '<td class="text-center hidden">{{Form::text('InvoiceDetail[Discount][]',0,array("class"=>"form-control Discount","data-min"=>"1", "data-mask"=>"fdecimal"))}}</td>';
        add_row_html += '<td>{{addslashes(Form::SelectExt(["name"=>"InvoiceDetail[TaxRateID][]","data"=>$taxes,"selected"=>$ProductRow->TaxRateID,"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select22 Taxentity small TaxRateID"]))}}</td>';
        add_row_html += '<td>{{addslashes(Form::SelectExt(["name"=>"InvoiceDetail[TaxRateID2][]","data"=>$taxes,"selected"=>'',"value_key"=>"TaxRateID","title_key"=>"Title","data-title1"=>"data-amount","data-value1"=>"Amount","data-title2"=>"data-flatstatus","data-value2"=>"FlatStatus","class" =>"select22 Taxentity small TaxRateID2"]))}}</td>';
        add_row_html += '<td class="hidden">{{Form::text('InvoiceDetail[TaxAmount][]',"0",array("class"=>"form-control TaxAmount","readonly"=>"readonly", "data-mask"=>"fdecimal"))}}</td>';
        add_row_html += '<td>{{Form::text('InvoiceDetail[LineTotal][]',0,array("class"=>"form-control LineTotal","data-min"=>"1", "data-mask"=>"fdecimal","readonly"=>"readonly"))}}';
        add_row_html += '{{Form::hidden('InvoiceDetail[StartDate][]','',array("class"=>"StartDate"))}}{{Form::hidden('InvoiceDetail[EndDate][]','',array("class"=>"EndDate"))}}{{Form::hidden('InvoiceDetail[ProductType][]',$ProductRow->ProductType,array("class"=>"ProductType"))}}</td></tr>';

        $('#rowContainer').append(add_row_html);
    </script>
    @include('invoices.script_invoice_barcode_product')
    @include('invoices.script_invoice_add_edit')
    @include('composetmodels.productsubscriptionmodal')
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
                <form id="send-invoice-form" method="post" >
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
