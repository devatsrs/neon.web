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
            <h2 class="name">INVOICE TO:</h2><br/>
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
            <th class="desc">DESCRIPTION</th>
            <th class="desc">Usage</th>
            <th class="desc">Recurring</th>
            <th class="desc">Additional</th>
            <th class="total">TOTAL</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="desc">Service - 1</td>
            <td class="desc">$1,200.00</td>
            <td class="desc">$1,000.00</td>
            <td class="desc">$1,000.00</td>
            <td class="total">$3,200.00</td>
        </tr>
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
        </tbody>
        <tfoot>
        <tr>
            <td colspan="2"></td>
            <td colspan="2">SUB TOTAL</td>
            <td class="subtotal">$5,200.00</td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="2">TAX 25%</td>
            <td class="subtotal">$1,300.00</td>
        </tr>
        @if($InvoiceTemplate->ShowPrevBal)
            <tr>
                <td colspan="2"></td>
                <td colspan="2">BROUGHT FORWARD</td>
                <td class="subtotal">$0.00</td>
            </tr>
        @endif
        <tr>
            <td colspan="2"></td>
            <td colspan="2">GRAND TOTAL</td>
            <td class="subtotal">$6,500.00</td>
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
    <div class="form-Group clearfix">
        <label class="col-sm-3" style="font-size: 1.4em;">Service Split on Separate page</label>
        <div class="col-sm-2">
            <p class="make-switch switch-small">
                <input id="ServiceSplit" name="ServiceSplit" type="checkbox"  @if($InvoiceTemplate->ServiceSplit == 1 )checked="" @endif value="1" >
            </p>
        </div>
    </div>
    <header class="clearfix">
        <div id="Service">
            <h1>Service 1</h1>
        </div>
    </header>
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


	    .invoice-right .editable-inline .control-group.form-group{width: 100%;}
	    .invoice-left .editable-inline .control-group.form-group{width: 90%;}

        .invoice-right  .editable-container .form-control ,.invoice-right .editable-input{width: 90%;}
	    .invoice-left  .editable-container .form-control ,.invoice-left .editable-input{width: 100%;}

        .invoice-footer .editable-inline .control-group.form-group{width: 90%;}
        .invoice-footer .editable-container .form-control ,.invoice-footer .editable-input{width: 100%;}

	</style>
    <link rel="stylesheet" href="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.css">
    <script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/wysihtml5-0.4.0pre.min.js"></script>
    <script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.js"></script>
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
                         'ServiceSplit':ServiceSplit
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
            modal.find('.message').wysihtml5({
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
            });

            var modal1 = $('#txt-footer');
            modal1.find('.invoiceFooterTerm').wysihtml5({
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
            });
        });

    });
	</script>
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