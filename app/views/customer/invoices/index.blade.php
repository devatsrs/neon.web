@extends('layout.customer.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="#"><i class="entypo-home"></i>Invoice</a>
    </li>
</ol>
<h3>Invoice</h3>

@include('includes.errors')
@include('includes.success')


<div class="row">
    <div class="col-md-12">
        <form id="invoice_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Filter
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Invoice Type</label>
                        <div class="col-sm-2">
                            {{Form::select('InvoiceType',Invoice::$invoice_type_customer,'',array("class"=>"selectboxit"))}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Invoice Number</label>
                        <div class="col-sm-2">
                            {{ Form::text('InvoiceNumber', '', array("class"=>"form-control")) }}
                        </div>
                         <label for="field-1" class="col-sm-2 control-label">Issue Date Start</label>
                        <div class="col-sm-2">
                              {{ Form::text('IssueDateStart', '', array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Issue Date End</label>
                        <div class="col-sm-2">
                              {{ Form::text('IssueDateEnd', '', array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}
                        </div>
                    </div>
                    <p style="text-align: right;">
                        <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                            <i class="entypo-search"></i>
                            Search
                        </button>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="row">
 <div  class="col-md-12">
        <div class="text-right">
            @if(is_authorize())
                <button type="button"  id="pay_now" class="pay_now create btn btn-primary" >Pay Now</button>
            @endif
        </div>
        <div class="input-group-btn pull-right" style="width:70px;">
            <form id="clear-bulk-rate-form" >
                <input type="hidden" name="CustomerRateIDs" value="">
            </form>
        </div><!-- /btn-group -->
 </div>
    <div class="clear"></div>
    </div>
<br>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="10%"><div class="pull-left"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></div>
            <div class="pull-right"> Sent/Receive</div></th>
        <th width="20%">Account Name</th>
        <th width="10%">Invoice Number</th>
        <th width="10%">Issue Date</th>
        <th width="10%">Grand Total</th>
        <th width="10%">Paid/OS</th>
        <th width="10%">Invoice Status</th>
        <th width="15%">Action</th>
    </tr>
    </thead>
    <tbody>


    </tbody>
</table>

<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");
        //show_loading_bar(40);
        var invoicestatus = JSON.parse('{{$invoice_status_json}}');
        var list_fields  = ['InvoiceType','AccountName ','InvoiceNumber','IssueDate','GrandTotal','PendingAmount','InvoiceStatus','InvoiceID','Description','Attachment','AccountID','OutstandingAmount','ItemInvoice','BillingEmail'];

        $searchFilter.InvoiceType = $("#invoice_filter [name='InvoiceType']").val();
        $searchFilter.InvoiceNumber = $("#invoice_filter [name='InvoiceNumber']").val();
        $searchFilter.IssueDateStart = $("#invoice_filter [name='IssueDateStart']").val();
        $searchFilter.IssueDateEnd = $("#invoice_filter [name='IssueDateEnd']").val();

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/customer/invoice/ajax_datagrid/type",
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[3, 'desc']],
             "fnServerParams": function(aoData) {
                aoData.push({"name":"InvoiceType","value":$searchFilter.InvoiceType},{"name":"InvoiceNumber","value":$searchFilter.InvoiceNumber},{"name":"IssueDateStart","value":$searchFilter.IssueDateStart},{"name":"IssueDateEnd","value":$searchFilter.IssueDateEnd});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"InvoiceType","value":$searchFilter.InvoiceType},{"name":"InvoiceNumber","value":$searchFilter.InvoiceNumber},{"name":"IssueDateStart","value":$searchFilter.IssueDateStart},{"name":"IssueDateEnd","value":$searchFilter.IssueDateEnd},{ "name": "Export", "value": 1});
            },
             "aoColumns":
                     [
                         {  "bSortable": false,
                             mRender: function ( id, type, full ) {
                                 var action , action = '<div class = "hiddenRowData" >';
                                 if (id != '{{Invoice::INVOICE_IN}}'){
                                     invoiceType = ' <button class=" btn btn-primary pull-right" title="Payment Sent"><i class="entypo-left-bold"></i>RCV</a>';
                                 }else{
                                     invoiceType = ' <button class=" btn btn-primary pull-right" title="Payment Received"><i class="entypo-right-bold"></i>SNT</a>';
                                 }
                                 if (full[0] != '{{Invoice::INVOICE_IN}}'){
                                     action += '<div class="pull-left"><input type="checkbox" class="checkbox rowcheckbox" value="'+full[7]+'" name="InvoiceID[]"></div>';
                                 }
                                 action += invoiceType;
                                 return action;
                             }

                         },  // 0 AccountName
                         {  "bSortable": true},  // 1 AccountName
                         {  "bSortable": true
                         },  // 2 IssueDate
                         {  "bSortable": true },  // 3 IssueDate
                         {  "bSortable": true },  // 4 GrandTotal
                         {  "bSortable": true },  // 4 GrandTotal
                         {  "bSortable": true,
                             mRender:function( id, type, full){
                                 return invoicestatus[full[6]];
                             }

                         },  // 5 InvoiceStatus
                         {
                             "bSortable": false,
                             mRender: function ( id, type, full ) {
                                 var action , edit_ , show_ , delete_,view_url,edit_url,download_url,invoice_preview,invoice_log;
                                 action = '<div class = "hiddenRowData" >';
                                 if (full[0] != '{{Invoice::INVOICE_IN}}'){
                                     invoice_preview = (baseurl + "/invoice/{id}/cview").replace("{id}",full[10] +'-'+id);
                                 }else{
                                     download_url = baseurl+'/invoice/download_doc_file/'+id;
                                 }

                                 for(var i = 0 ; i< list_fields.length; i++){
                                     action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                 }
                                 action += '</div>';
                                 if (full[0] == '{{Invoice::INVOICE_OUT}}'){
                                     action += ' <a href="'+invoice_preview+'" class="view-invoice-sent btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Print </a>';
                                 }else{
                                     action += ' <a></a>';
                                     action += ' <a class="view-invoice-in btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Print </a>';
                                 }

                                 return action;
                             }
                         },
                     ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/customer/invoice/ajax_datagrid/xlsx", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/customer/invoice/ajax_datagrid/csv", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
           "fnDrawCallback": function() {
                   //After Delete done
                   FnDeleteInvoiceTemplateSuccess = function(response){

                       if (response.status == 'success') {
                           $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                           ShowToastr("success",response.message);
                           data_table.fnFilter('', 0);
                       }else{
                           ShowToastr("error",response.message);
                       }
                   }
                   //onDelete Click
                   FnDeleteInvoiceTemplate = function(e){
                       result = confirm("Are you Sure?");
                       if(result){
                           var id  = $(this).attr("data-id");
                           showAjaxScript( baseurl + "/invoice/"+id+"/delete" ,"",FnDeleteInvoiceTemplateSuccess );
                       }
                       return false;
                   }
                   $(".delete-invoice").click(FnDeleteInvoiceTemplate); // Delete Note
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
           }

        });
        $("#invoice_filter").submit(function(e){
            e.preventDefault();
            $searchFilter.InvoiceType = $("#invoice_filter [name='InvoiceType']").val();
            $searchFilter.InvoiceNumber = $("#invoice_filter [name='InvoiceNumber']").val();
            $searchFilter.IssueDateStart = $("#invoice_filter [name='IssueDateStart']").val();
            $searchFilter.IssueDateEnd = $("#invoice_filter [name='IssueDateEnd']").val();
            data_table.fnFilter('', 0);
            return false;
        });
        $('table tbody').on('click', '.view-invoice-in', function (ev) {
            var cur_obj = $(this).parent().parent().parent().parent().find("div.hiddenRowData");

            for(var i = 0 ; i< list_fields.length; i++){
            $("#modal-invoice-in-view").find("[data-id='"+list_fields[i]+"']").html('');
                if(list_fields[i] == 'Attachment'){
                    if(cur_obj.find("input[name='"+list_fields[i]+"']").val() != ''){
                        var down_html = ' <a href="' + baseurl +'/invoice/download_doc_file/'+cur_obj.find("input[name='InvoiceID']").val() +'" class="edit-invoice btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>'
                        $("#modal-invoice-in-view").find("[data-id='"+list_fields[i]+"']").html(down_html);
                    }
                }else{
                    $("#modal-invoice-in-view").find("[data-id='"+list_fields[i]+"']").html(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                }
            }
            $('#modal-invoice-in-view').modal('show');
        });

        $("#selectall").click(function(ev) {
            var is_checked = $(this).is(':checked');
            $('#table-4 tbody tr').each(function(i, el) {
                if($(this).find('.rowcheckbox').hasClass('rowcheckbox')){
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                }
            });
        });
        $('#table-4 tbody').on('click', 'tr', function() {
            if($(this).find('.rowcheckbox').hasClass('rowcheckbox')){
            $(this).toggleClass('selected');
            if ($(this).hasClass('selected')) {
                $(this).find('.rowcheckbox').prop("checked", true);
            } else {
                $(this).find('.rowcheckbox').prop("checked", false);
            }
            }
        });
        $("#pay_now").click(function(ev) {
            ev.preventDefault();
            var InvoiceIDs = [];
            var accoutid ;
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                InvoiceID = $(this).val();
                     InvoiceIDs[i++] = InvoiceID;
             var tr_obj = $(this).parent().parent().parent().parent();
             if(!accoutid){
                accoutid = tr_obj.children().find('[name=AccountID]').val();
             }

            });
            if(InvoiceIDs.length){
                if (!confirm('Are you sure you want to pay selected invoices?')) {
                    return;
                }
                //console.log(InvoiceIDs);

                paynow_url = '/customer/PaymentMethodProfiles/paynow';
                showAjaxModal( paynow_url ,'pay_now_modal');
                $('#pay_now_modal').modal('show');


            }
            return false;
        });
});

</script>
<style>
#table-4 .dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}
 #table-5_filter label{
    display:block !important;
}
</style>
@stop
@section('footer_ext')
@parent
<!-- Job Modal  (Ajax Modal)-->
<div class="modal fade custom-width" id="print-modal-invoice">
    <div class="modal-dialog" style="width: 60%;">
        <div class="modal-content">
            <form id="add-new-invoice_template-form" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                    <h4 class="modal-title">
                        <a class="btn btn-primary print btn-sm btn-icon icon-left" href="">
                            <i class="entypo-print"></i>
                            Print
                        </a>
                    </h4>
                </div>
                <div class="modal-body">
                        Content is loading...
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
<div class="modal fade custom-width" id="modal-invoice-in-view">
    <div class="modal-dialog" style="width: 60%;">
        <div class="modal-content">
        <form class="form-horizontal form-groups-bordered">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">View Invoice</h4>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label for="field-5" class="col-sm-2 control-label">Account Name<span id="currency"></span></label>
                        <div class="col-sm-4 control-label">
                        <span data-id="AccountName">abcs</span>
                        </div>
                     </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="field-1">Issue Date</label>
                        <div class="col-sm-4 control-label">
                            <span data-id="IssueDate"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="field-1">Invoice Number</label>
                        <div class="col-sm-4 control-label">
                            <span data-id="InvoiceNumber"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="field-1">Grand Total</label>
                        <div class="col-sm-4 control-label">
                            <span data-id="GrandTotal"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="field-1">Description</label>
                        <div class="col-sm-4 control-label">
                            <span data-id="Description"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="field-1">Attachment</label>
                        <div class="col-sm-4 control-label">
                            <span data-id="Attachment"></span>
                        </div>
                    </div>
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
<div class="modal fade custom-width" id="pay_now_modal">
    <div class="modal-dialog" style="width: 60%;">
        <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"> Pay Now
                    </h4>
                </div>
                <div class="modal-body">



                  </div>
                <div class="modal-footer">
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
        </div>
    </div>
</div>

@stop