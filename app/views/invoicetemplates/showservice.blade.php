@extends('layout.main')
<?php
    $editable = 1;
?>
<link rel="stylesheet" type="text/css" href="<?php echo URL::to('/'); ?>/assets/css/invoicetemplate/invoicestyle.css" />

@include('invoicetemplates.servicehtml')
@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <a href="{{URL::to('invoice_template')}}">  Invoice Template</a>
    </li>
    <li>
        <a><span>{{invoicetemplate_dropbox($InvoiceTemplate->InvoiceTemplateID)}}</span></a>
    </li>
    <li class="active">
        <strong>Edit {{$InvoiceTemplate->Name}}</strong>
    </li>
</ol>
<h3>Edit {{$InvoiceTemplate->Name}}</h3>

@include('includes.errors')
@include('includes.success')
<p style="text-align: right;">
    <a href="{{URL::to('/invoice_template')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
    @if(User::checkCategoryPermission('InvoiceTemplates','Edit') )
    <button type="submit" id="invoice_template-save"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>
    @endif
    <a  href="Javascript:void(0);" id="invoice_template-print"  class="btn btn-danger btn-sm btn-icon icon-left" >
        <i class="entypo-print"></i>
        Preview Template
    </a>

</p>
<br>
<div class="inovicebody">
<header class="clearfix">
    @yield('logo')
</header>

    <div id="details" class="clearfix">
        <div style="float:left;">
            <h2 class="name"><b>Invoice To:</b></h2><br/>
            <div style="padding-bottom:8px;">{{ Form::select('InvoiceToInfo', Invoice::$invoice_account_info, (!empty(Input::get('InvoiceToInfo'))?explode(',',Input::get('InvoiceFromInfo')):[]), array("class"=>"","data-allow-clear"=>"true","data-placeholder"=>"Select Account Info")) }}</div>
            <textarea class="invoice-to" style="min-width: 400px;" rows="7">@if(!empty($InvoiceTemplate->InvoiceTo)){{$InvoiceTemplate->InvoiceTo}} @else {AccountName} @endif</textarea>

        </div>

        <div id="invoice">
            <h1>Invoice No: {{$InvoiceTemplate->InvoiceNumberPrefix.$InvoiceTemplate->InvoiceStartNumber}}</h1>
            <div class="date">Invoice Date: {{date('d-m-Y')}}</div>
            <div class="date">Due Date: {{date('d-m-Y',strtotime('+5 days'))}}</div>
            @if($InvoiceTemplate->ShowBillingPeriod == 1)
                <div class="date">Invoice Period: {{date('d-m-Y',strtotime('-7 days'))}} - {{date('d-m-Y')}}</div>
            @endif
        </div>
    </div>
    <table border="0" cellspacing="0" cellpadding="0" id="frontinvoice">
        <thead>
        <tr>
            @if($InvoiceTemplate->GroupByService==0)
                <?php
                    $VisibleColumns = (array)json_decode($InvoiceTemplate->VisibleColumns);
                    $Description = $Usage = $Recurring = $Additional = false;
                    if(!empty($VisibleColumns)) {
                        if($VisibleColumns['Description'] == 1)
                            $Description = true;
                        if($VisibleColumns['Usage'] == 1)
                            $Usage = true;
                        if($VisibleColumns['Recurring'] == 1)
                            $Recurring = true;
                        if($VisibleColumns['Additional'] == 1)
                            $Additional = true;
                    }
                ?>
                <th class="desc">
                    <b>Description</b>
                    &nbsp;{{Form::checkbox('VisibleColumns[Description]', 1, $Description, array("class"=>""))}}
                    <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="if not ticked then this column will not appear on invoice." data-original-title="Description">?</span>
                </th>
                <th class="desc">
                    <b>Usage</b>
                    &nbsp;{{Form::checkbox('VisibleColumns[Usage]', 1, $Usage, array("class"=>""))}}
                    <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="if not ticked then this column will not appear on invoice." data-original-title="Usage">?</span>
                </th>
                <th class="desc">
                    <b>Recurring</b>
                    &nbsp;{{Form::checkbox('VisibleColumns[Recurring]', 1, $Recurring, array("class"=>""))}}
                    <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="if not ticked then this column will not appear on invoice." data-original-title="Recurring">?</span>
                </th>
                <th class="desc">
                    <b>Additional</b>
                    &nbsp;{{Form::checkbox('VisibleColumns[Additional]', 1, $Additional, array("class"=>""))}}
                    <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="if not ticked then this column will not appear on invoice." data-original-title="Additional">?</span>
                </th>
            @else
                @if($InvoiceTemplate->GroupByService==1)
                    <th class="desc"><b>Description</b></th>
                @endif
                <th class="desc"><b>Usage</b></th>
                <th class="desc"><b>Recurring</b></th>
                <th class="desc"><b>Additional</b></th>
            @endif
            <th class="total"><b>Total</b></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            @if($InvoiceTemplate->GroupByService==1)
            <td class="desc">Service - 1</td>
            @elseif($InvoiceTemplate->GroupByService==0)
            <td class="desc" id="desc_editable">{{$InvoiceTemplate->ItemDescription}}</td>
            @endif
            <td class="desc">$1,200.00</td>
            <td class="desc">$1,000.00</td>
            <td class="desc">$1,000.00</td>
            <td class="total">$3,200.00</td>
        </tr>
        @if($InvoiceTemplate->GroupByService==1)
        <tr>
            <td class="desc">Service - 2</td>
            <td class="desc">$1,200.00</td>
            <td class="desc">$1,000.00</td>
            <td class="desc">$1,000.00</td>
            <td class="total">$3,200.00</td>
        </tr>
        <tr>
            <td class="desc">Other Service</td>
            <td class="desc">$400.00</td>
            <td class="desc">$400.00</td>
            <td class="desc">$400.00</td>
            <td class="total">$1,200.00</td>
        </tr>
        @endif
        </tbody>
        <tfoot>
        <tr>
            @if($InvoiceTemplate->GroupByService==0 || $InvoiceTemplate->GroupByService==1)
                <td colspan="2"></td>
            @else
                <td></td>
            @endif
            <td colspan="2">Sub Total</td>
            <td class="subtotal">$5,200.00</td>
        </tr>
        <tr>
            @if($InvoiceTemplate->GroupByService==0 || $InvoiceTemplate->GroupByService==1)
                <td colspan="2"></td>
            @else
                <td></td>
            @endif
            <td colspan="2">Tax 25%</td>
            <td class="subtotal">$1,300.00</td>
        </tr>
        @if($InvoiceTemplate->ShowPrevBal)
            <tr>
                @if($InvoiceTemplate->GroupByService==0 || $InvoiceTemplate->GroupByService==1)
                    <td colspan="2"></td>
                @else
                    <td></td>
                @endif
                <td colspan="2">Brought Forward</td>
                <td class="subtotal">$0.00</td>
            </tr>
        @endif
        <tr>
            @if($InvoiceTemplate->GroupByService==0 || $InvoiceTemplate->GroupByService==1)
                <td colspan="2"></td>
            @else
                <td></td>
            @endif
            <td colspan="2"><b>Grand Total</b></td>
            <td class="subtotal"><b>$6,500.00</b></td>
        </tr>
        </tfoot>
    </table>

<div class="form-Group" id="txt-adv">
        <br />
        <br />
        <textarea class="form-control message" rows="18" id="field-3" name="TemplateBody">{{$InvoiceTemplate->Terms}}</textarea>
</div>


    <br/>
    <br/>
    <!--<div class="form-Group clearfix">
        <label class="col-sm-3" style="font-size: 1.4em;">Split Services on separate pages
            <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If ON each service will be displayed on separate page" data-original-title="Service Split">?</span></label>
        </label>
        <div class="col-sm-2">
            <p class="make-switch switch-small">
                <input id="ServiceSplit" name="ServiceSplit" type="checkbox"  @if($InvoiceTemplate->ServiceSplit == 1 )checked="" @endif value="1" >
            </p>
        </div>
    </div>-->
    @if($InvoiceTemplate->GroupByService==1)
    <header class="clearfix">
        <div id="Service" style="float:left;width:40%">
            <h1>Service 1</h1>
        </div>
    </header>
    @endif
    <main>
        <div class="ChargesTitle clearfix">
            <div style="float:left;">Usage</div>
            <div style="text-align:right;float:right;">$6.20</div>
        </div>
        <table border="0" cellspacing="0" cellpadding="0" id="backinvoice">
            <thead>
            <tr>
                <th class="leftalign">Title</th>
                <th class="leftalign">Description</th>
                <th class="rightalign">Price</th>
                <th class="rightalign">Qty</th>
                <th class="leftalign">Date From</th>
                <th class="leftalign">Date To</th>
                <th class="rightalign">Total</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="leftalign">Usage</td>
                <td class="leftalign">From 01-01-2017 To 31-01-2017</td>
                <td class="rightalign">1.24</td>
                <td class="rightalign">1</td>
                <td class="leftalign">01-01-2017</td>
                <td class="leftalign">31-01-2017</td>
                <td class="rightalign">1.24</td>
            </tr>
            </tbody>
        </table>

        <div class="ChargesTitle clearfix">
            <div style="float:left;">Recurring</div>
            <div style="text-align:right;float:right;">$99.87</div>
        </div>

        <table border="0" cellspacing="0" cellpadding="0" id="backinvoice">
            <thead>
            <tr>
                <th class="leftalign">Title</th>
                <th class="leftalign">Description</th>
                <th class="rightalign">Price</th>
                <th class="rightalign">Qty</th>
                <th class="leftalign">Date From</th>
                <th class="leftalign">Date To</th>
                <th class="rightalign">Total</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="leftalign">WT Premium - £5.99 - NO IPPHONE</td>
                <td class="leftalign">WT Premium - £5.99 - NO IPPHONE</td>
                <td class="rightalign">5.99</td>
                <td class="rightalign">12</td>
                <td class="leftalign">01-02-2017</td>
                <td class="leftalign">28-02-2017</td>
                <td class="rightalign">71.88</td>
            </tr>
            </tbody>
        </table>

        <div class="ChargesTitle clearfix">
            <div style="float:left;">Additional</div>
            <div style="text-align:right;float:right;">$32.00</div>
        </div>

        <table border="0" cellspacing="0" cellpadding="0" id="backinvoice">
            <thead>
            <tr>
                <th class="leftalign">Title</th>
                <th class="leftalign">Description</th>
                <th class="rightalign">Price</th>
                <th class="rightalign">Qty</th>
                <th class="leftalign">Date</th>
                <th class="rightalign">Total</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="leftalign">PBXSETUP</td>
                <td class="leftalign">SETUP COST PER USER</td>
                <td class="rightalign">10.00</td>
                <td class="rightalign">2</td>
                <td class="leftalign">28-02-2017</td>
                <td class="rightalign">20.00</td>
            </tr>
            </tbody>
        </table>
    </main>
    <div class="form-Group" id="txt-footer">
        </br>
        <h2>Footer</h2>

        <textarea class="form-control invoiceFooterTerm" rows="8" id="field-3" name="FooterTerm">{{$InvoiceTemplate->FooterTerm}}</textarea>
    </div>
    <!--<div class="row">
        <div class="col-sm-12 invoice-footer">
            @yield('footerterms')
        </div>
    </div>-->
</div>
	<style>
	    .invoice-editable:focus {
	        background: #FFFEBD;
	    }
	    #invoice_template-save:focus{
            background: #0058FA;
	    }
	    .editable-container.editable-inline{
	        width: 100%;
	    }

        .popover-primary {
            padding: .2em .6em .3em;
        }


	    .invoice-right .editable-inline .control-group.form-group{width: 100%;}
	    .invoice-left .editable-inline .control-group.form-group{width: 90%;}

        .invoice-right  .editable-container .form-control ,.invoice-right .editable-input{width: 90%;}
	    .invoice-left  .editable-container .form-control ,.invoice-left .editable-input{width: 100%;}

        .invoice-footer .editable-inline .control-group.form-group{width: 90%;}
        .invoice-footer .editable-container .form-control ,.invoice-footer .editable-input{width: 100%;}

	</style>
    <!--<link rel="stylesheet" href="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.css">
    <script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/wysihtml5-0.4.0pre.min.js"></script>
    <script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.js"></script>-->
	<script type="text/javascript">
	$(document).ready(function() {
        //toggle `popup` / `inline` mode
        $.fn.editable.defaults.mode = 'inline';
        $.fn.editable.defaults.ajaxOptions = {type: "PUT"};
        $.fn.editable.defaults.showbuttons = false;
        $.fn.editableform.template = '<form class="form-inline editableform" enctype="multipart/form-data">'+
        '<div class="control-group">' +
        '<div><div class="editable-input"></div><div class="editable-buttons"></div></div>'+
        '<div class="editable-error-block"></div>' +
        '</div>' +
        '</form>';

        //make username editable
        $('.inovicebody #InvoiceTemplateName').editable();
        $('.inovicebody #InvoiceStartNumber').editable();
        $('.inovicebody #InvoiceTemplateHeader').editable();
        $('.inovicebody #InvoiceTemplateFooter').editable();
        $('.inovicebody #InvoiceTemplateTerms').editable();
        $('.inovicebody #InvoiceTemplatePages').editable({
            prepend: 'Pages',
            value: '{{$InvoiceTemplate->Pages}}',
            source: [
                        { value: 'single', text: 'A single page with totals only' },
                        { value: 'single_with_detail', text: 'First page with totals + usage details attached on additional pages' }
                    ]
        });

        $('#invoice_template-print').click(function() {
                    document.getElementById("invoice_iframe").contentDocument.location.reload(true);
                    $('#print-modal-invoice_template').modal('show');
        });

        /*$('#print-modal-invoice_template .print.btn').click(function() {
            window.frames[0].focus();
            window.frames[0].print();
        });*/

        $('#invoice_template-save').click(function() {
            var invoiceto = $('.invoice-to').val();
            var Header = $('#InvoiceTemplateHeader').text();
            var Name = $('#InvoiceTemplateName').text();
            var Terms = $('.message').val();
            var FooterTerm = $('.invoiceFooterTerm').val();
            var ServiceSplit =$("#ServiceSplit").prop("checked");
            var ItemDescription = $('#desc_editable').html();

            var VisibleColumns = {};
            VisibleColumns['Description']   = $("input[name='VisibleColumns[Description]']").is(':checked') ? 1 : 0;
            VisibleColumns['Usage']         = $("input[name='VisibleColumns[Usage]']").is(':checked') ? 1 : 0;
            VisibleColumns['Recurring']     = $("input[name='VisibleColumns[Recurring]']").is(':checked') ? 1 : 0;
            VisibleColumns['Additional']    = $("input[name='VisibleColumns[Additional]']").is(':checked') ? 1 : 0;

           $('.invoice-editable').editable('submit', {
               url: '<?php echo URL::to('/invoice_template/'.$InvoiceTemplate->InvoiceTemplateID .'/update'); ?>',
               ajaxOptions: {
                   dataType: 'json', //assuming json response
                   data:{
                         'InvoiceTo':invoiceto,
                         'Header':Header,
                         'Name':Name,
                         'Terms':Terms,
                         'FooterTerm':FooterTerm,
                         'ServicePage':1,
                         'ServiceSplit':ServiceSplit,
                         'ItemDescription':ItemDescription,
                         'VisibleColumns':VisibleColumns
                        }
               },


               success: function(response, config) {

                    $("#invoice_template-update").button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
               },
               error: function(errors) {
                   var msg = '';
                   if(errors && errors.responseText) { //ajax error, errors = xhr object
                       msg = errors.responseText;
                   } else { //validation error (client-side or server-side)
                       $.each(errors, function(k, v) { msg += k+": "+v+"<br>"; });
                   }
                   $('#msg').removeClass('alert-success').addClass('alert-error').html(msg).show();
               }
           });
        });

        $("select[name=InvoiceToInfo]").change( function (e) {
            var str = $('.invoice-to').val();
            str += $(this).val();
            $('.invoice-to').val(str);
        });
        $( window ).on( "load", function() {
            var modal = $('#txt-adv');
            show_summerinvoicetemplate(modal.find(".message"));
            /*modal.find('.message').wysihtml5({
                "font-styles": false,
                "emphasis": true,
                "leadoptions": false,
                "Crm": false,
                "lists": true,
                "html": true,
                "link": true,
                "image": true,
                "color": false,
                parser: function (html) {
                    return html;
                }
            });*/

            var modal1 = $('#txt-footer');
            show_summerinvoicetemplate(modal1.find(".invoiceFooterTerm"));
            /*modal1.find('.invoiceFooterTerm').wysihtml5({
                "font-styles": false,
                "emphasis": true,
                "leadoptions": false,
                "Crm": false,
                "lists": true,
                "html": true,
                "link": true,
                "image": true,
                "color": false,
                parser: function (html) {
                    return html;
                }
            });*/
        });
        $('#drp_invoicetemplate_jump').on('change',function(){
            var val = $(this).val();
            if(val!="") {
                var InvoiceTemplateID = '{{$InvoiceTemplate->InvoiceTemplateID}}';
                var url ='/invoice_template/'+ val + '/view?Type=1';
                window.location.href = baseurl + url;
            }
        });

        $('#desc_editable').bind('click', function(){
            $(this).attr('contentEditable',true);
        });
        $('#desc_editable').on('focusout', function(){
            $('#desc_editable').attr('contentEditable',false);
        });
    });
	</script>
    <style>
        #drp_invoicetemplate_jump{
            border: 0px solid #fff;
            background-color: rgba(255,255,255,0);
            padding: 0px;
        }
        #drp_invoicetemplate_jump option{
            -webkit-appearance: none;
            -moz-appearance: none;
            border: 0px;
        }

    </style>
@stop
@section('footer_ext')
@parent
<div class="modal fade custom-width" id="print-modal-invoice_template">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="add-new-invoice_template-form" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">                     <a href="{{URL::to('invoice_template/'.$InvoiceTemplate->InvoiceTemplateID.'/pdf_download?Type='.Input::get('Type'))}}" type="button" class="btn btn-primary print btn-sm btn-icon icon-left" >
                                                                    <i class="entypo-print"></i>
                                                                    Print
                                                                 </a>
                    </h4>
                </div>
                <div class="modal-body">

                        <iframe  id="invoice_iframe"   frameborder="0" scrolling="no" style="position: relative; height: 1050px; width: 100%;overflow-y: auto; overflow-x: hidden;" width="100%" height="100%" src="{{ URL::to('/invoice_template/'.$InvoiceTemplate->InvoiceTemplateID .'/print?Type='.Input::get('Type')); }}"></iframe>

                  </div>
                <div class="modal-footer">
                     <a href="{{URL::to('invoice_template/'.$InvoiceTemplate->InvoiceTemplateID.'/pdf_download?Type='.Input::get('Type'))}}" type="button" class="btn btn-primary print btn-sm btn-icon icon-left" >
                        <i class="entypo-print"></i>
                        Print
                     </a>
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