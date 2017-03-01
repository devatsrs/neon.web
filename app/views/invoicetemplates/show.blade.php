@extends('layout.main')
<?php
    $editable = 1;
?>
@include('invoicetemplates.html')

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
<div class="invoice">
    <div class="row">
        <div class="col-md-12">
		@yield('logo')
		</div>

	</div>
	<div class="row">
        <div class="col-md-12">
        @yield('invoice_from')
        </div>
    </div>

	<br /><br /><br />
        @if(Input::get('Type') == 1 )
        <div class="row">
            <div class="col-sm-5">
                <div class="invoice-left">
                        @yield('usage_invoice_duration')
                    </div>
                </div>
            <div class="col-sm-2"></div>
            <div class="col-sm-5">
                <div class="invoice-right">
                     @yield('usage_invoice_prevbal')
                    </div>
                </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                @yield('subscriptiontotal')
            </div>
        </div>
        @else
            @yield('items')
        @endif


	<div class="margin"></div>
	<div class="row">

            <div class="col-sm-7">

                <div class="invoice-left">
                    </br>
                    </br>
                    @yield('terms')
                </div>

            </div>

            <div class="col-sm-5">

                <div class="invoice-right">
            	     @yield('total')
                </div>
            </div>
        </div>
        <br/>
        <br/>
        <br/>
        <br/>

                <hr>
                @if(Input::get('Type') == 1 )
                    @yield('sub_usage')
                @endif
                <div class="row">
                    <div class="col-sm-12">
                        @yield('footerterms')
                    </div>
                </div>
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

	</style>
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
        $('.invoice #InvoiceTemplateName').editable();
        $('.invoice #InvoiceStartNumber').editable();
        $('.invoice #InvoiceTemplateHeader').editable();
        $('.invoice #InvoiceTemplateFooter').editable();
        $('.invoice #InvoiceTemplateTerms').editable();
        $('.invoice #InvoiceTemplatePages').editable({
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
           $('.invoice-editable').editable('submit', {
               url: '<?php echo URL::to('/invoice_template/'.$InvoiceTemplate->InvoiceTemplateID .'/update'); ?>',
               ajaxOptions: {
                   dataType: 'json' //assuming json response
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