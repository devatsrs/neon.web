@extends('layout.customer.main')

@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="#"><i class="entypo-home"></i>@lang('routes.CUST_PANEL_PAGE_INVOICE_TITLE')</a> </li>
</ol>
<h3>{{cus_lang('CUST_PANEL_PAGE_INVOICE_TITLE')}}</h3>
@include('includes.errors')
@include('includes.success')
<div class="row">
  <div class="col-md-12">
    <form id="invoice_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title"> @lang('routes.CUST_PANEL_FILTER_TITLE') </div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body">
          <div class="form-group">
            <label for="field-1" class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_INVOICE_FILTER_FIELD_TYPE')</label>
            <div class="col-sm-2"> {{Form::select('InvoiceType',Invoice::$invoice_type_customer,Input::get('InvoiceType'),array("class"=>"select2 small"))}} </div>
            <label for="field-1" class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_INVOICE_FILTER_FIELD_START_DATE')</label>
            <div class="col-sm-2"> {{ Form::text('IssueDateStart', Input::get('StartDate'), array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }} </div>
            <label for="field-1" class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_INVOICE_FILTER_FIELD_END_DATE')</label>
            <div class="col-sm-2"> {{ Form::text('IssueDateEnd', Input::get('EndDate'), array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }} </div>
            <label for="field-1" class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_INVOICE_FILTER_FIELD_HIDE_ZERO')</label>
            <div class="col-sm-2">
              <p class="make-switch switch-small"  data-on-label="@lang('routes.BUTTON_ON_CAPTION')" data-off-label="@lang('routes.BUTTON_OFF_CAPTION')"  >
                <input id="zerovalueinvoice" name="zerovalueinvoice" type="checkbox" checked>
              </p>
            </div>
          </div>
          <div class="form-group">
            <label for="field-1" class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_INVOICE_FILTER_FIELD_NUMBER')</label>
            <div class="col-sm-2"> {{ Form::text('InvoiceNumber', '', array("class"=>"form-control")) }} </div>
            <label for="field-1" class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_INVOICE_FILTER_FIELD_OVERDUE')</label>
            <div class="col-sm-2">
              <p class="make-switch switch-small"  data-on-label="@lang('routes.BUTTON_ON_CAPTION')" data-off-label="@lang('routes.BUTTON_OFF_CAPTION')" >
                <input id="Overdue" name="Overdue"  type="checkbox">
              </p>
            </div>
          </div>
          <p class="pull-right">
            <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left"> <i class="entypo-search"></i> @lang('routes.BUTTON_SEARCH_CAPTION') </button>
          </p>
        </div>
      </div>
    </form>
  </div>
</div>
<div class="row">
  <div  class="col-md-12">
    <div class="text-right"> @if(is_PayNowInvoice($CompanyID))
      <button type="button"  id="pay_now" class="pay_now create btn btn-primary" >@lang('routes.CUST_PANEL_PAGE_INVOICE_BUTTON_PAY_NOW')</button>

      @endif </div>
    <div class="input-group-btn pull-right" style="width:70px;">
      <form id="clear-bulk-rate-form" >
        <input type="hidden" name="CustomerRateIDs" value="">
      </form>
    </div>
    <!-- /btn-group --> 
  </div>
  <div class="clear"></div>
</div>
<br>
<table class="table table-bordered datatable" id="table-4">
  <thead>
    <tr>
      <th width="10%"> @if(is_PayNowInvoice($CompanyID))
        <div class="pull-left">
          <input type="checkbox" id="selectall" name="checkbox[]" class="" />
        </div>
        @endif
        <div class="pull-right"></div></th>
        <th width="20%">@lang('routes.CUST_PANEL_PAGE_INVOICE_TBL_AC_NAME')</th>
        <th width="10%">@lang('routes.CUST_PANEL_PAGE_INVOICE_TBL_INVOICE_NUMBER')</th>
        <th width="10%">@lang('routes.CUST_PANEL_PAGE_INVOICE_TBL_ISSUE_DATE')</th>
        <th width="10%">@lang('routes.CUST_PANEL_PAGE_INVOICE_TBL_GRAND_TOTAL')</th>
        <th width="10%">@lang('routes.CUST_PANEL_PAGE_INVOICE_TBL_PAID_OS')</th>
        <th width="10%">@lang('routes.CUST_PANEL_PAGE_INVOICE_TBL_INVOICE_STATUS')</th>
        <th width="15%">@lang('routes.TABLE_COLUMN_ACTION')</th>
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
        var InvoiceID = '';
        var invoicestatus = JSON.parse('{{$invoice_status_json}}');
        var list_fields  = ['InvoiceType','AccountName ','InvoiceNumber','IssueDate','GrandTotal','PendingAmount','InvoiceStatus','InvoiceID','Description','Attachment','AccountID','OutstandingAmount','ItemInvoice','BillingEmail'];

        $searchFilter.InvoiceType = $("#invoice_filter [name='InvoiceType']").val();
        $searchFilter.InvoiceNumber = $("#invoice_filter [name='InvoiceNumber']").val();
        $searchFilter.IssueDateStart = $("#invoice_filter [name='IssueDateStart']").val();
        $searchFilter.IssueDateEnd = $("#invoice_filter [name='IssueDateEnd']").val();
        $searchFilter.zerovalueinvoice = $("#invoice_filter [name='zerovalueinvoice']").prop("checked");
        $searchFilter.Overdue = $("#invoice_filter [name='Overdue']").prop("checked");

        data_table = $("#table-4").dataTable({
            "oLanguage": {
                "sUrl": baseurl + "/translate/datatable_Label"
            },
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/customer/invoice/ajax_datagrid/type",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[3, 'desc']],
             "fnServerParams": function(aoData) {
                aoData.push({"name":"InvoiceType","value":$searchFilter.InvoiceType},{"name":"InvoiceNumber","value":$searchFilter.InvoiceNumber},{"name":"IssueDateStart","value":$searchFilter.IssueDateStart},{"name":"IssueDateEnd","value":$searchFilter.IssueDateEnd},{"name":"zerovalueinvoice","value":$searchFilter.zerovalueinvoice},{"name":"Overdue","value":$searchFilter.Overdue});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"InvoiceType","value":$searchFilter.InvoiceType},{"name":"InvoiceNumber","value":$searchFilter.InvoiceNumber},{"name":"IssueDateStart","value":$searchFilter.IssueDateStart},{"name":"IssueDateEnd","value":$searchFilter.IssueDateEnd},{"name":"zerovalueinvoice","value":$searchFilter.zerovalueinvoice},{"name":"Overdue","value":$searchFilter.Overdue},{ "name": "Export", "value": 1});
            },
             "aoColumns":
                     [
                         {  "bSortable": false,
                             mRender: function ( id, type, full ) {
                                 var action , action = '<div class = "hiddenRowData" >';
                                 if (id != '{{Invoice::INVOICE_IN}}'){
                                     invoiceType = ' <button class=" btn btn-primary pull-right" title="@lang('routes.CUST_PANEL_PAGE_ANALYSIS_INVOICE_BUTTON_INVOICE_RECEIVED')"><i class="entypo-left-bold"></i>RCV</a>';
                                 }else{
                                     invoiceType = ' <button class=" btn btn-primary pull-right" title="@lang('routes.CUST_PANEL_PAGE_ANALYSIS_INVOICE_BUTTON_INVOICE_SENT')"><i class="entypo-right-bold"></i>SNT</a>';
                                 }
                                 if (full[0] != '{{Invoice::INVOICE_IN}}'){
                                     if('{{is_PayNowInvoice($CompanyID)}}'){
                                        action += '<div class="pull-left"><input type="checkbox" class="checkbox rowcheckbox" value="'+full[7]+'" name="InvoiceID[]"></div>';
                                     }
                                 }
                                 action += invoiceType;
                                 return action;
                             }

                         },  // 0 AccountName
                         {  "bSortable": true},  // 1 AccountName
                         {  "bSortable": true
                         },  // 2 IssueDate
                         {  "bSortable": true },  // 3 IssueDate
                         {
                             "bSortable": true,
                             mRender: function (id, type, full) { return "<span class='leftsideview'>"+full[4]+"</span>" }
                         },  // 4 GrandTotal
                         {
                             "bSortable": true,
                             mRender: function (id, type, full) { return "<span class='leftsideview'>"+full[5]+"</span>"}
                         },  // 4 GrandTotal
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
                                     action += ' <a href="'+invoice_preview+'" target="_blank" title="@lang('routes.BUTTON_VIEW_CAPTION')" class="view-invoice-sent btn btn-default btn-sm"><i class="fa fa-eye"></i></a>';
                                 }else{
                                     action += ' <a></a>';
                                     action += ' <a title="@lang('routes.BUTTON_VIEW_CAPTION')" class="view-invoice-in btn btn-default btn-sm"><i class="fa fa-eye"></i></a>';
                                 }

                                 return action;
                             }
                         },
                     ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "@lang('routes.BUTTON_EXPORT_EXCEL_CAPTION')",
                        "sUrl": baseurl + "/customer/invoice/ajax_datagrid/xlsx", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "@lang('routes.BUTTON_EXPORT_CSV_CAPTION')",
                        "sUrl": baseurl + "/customer/invoice/ajax_datagrid/csv", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
           "fnDrawCallback": function() {
               get_total_grand(); //get result total
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
                       result = confirm("@lang("routes.MESSAGE_ARE_YOU_SURE")");
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
                   if(typeof customer_alignment!="undefined" && customer_alignment=="right"){
                       $('.pull-right, .pull-left').addClass('flip');
                   }
           }

        });
        $("#invoice_filter").submit(function(e){

            e.preventDefault();
            $searchFilter.InvoiceType = $("#invoice_filter [name='InvoiceType']").val();
            $searchFilter.InvoiceNumber = $("#invoice_filter [name='InvoiceNumber']").val();
            $searchFilter.IssueDateStart = $("#invoice_filter [name='IssueDateStart']").val();
            $searchFilter.IssueDateEnd = $("#invoice_filter [name='IssueDateEnd']").val();
            $searchFilter.zerovalueinvoice = $("#invoice_filter [name='zerovalueinvoice']").prop("checked");
            $searchFilter.Overdue = $("#invoice_filter [name='Overdue']").prop("checked");

            data_table.fnFilter('', 0);
            return false;
        });
        function get_total_grand(){
            $.ajax({
                url: baseurl + "/customer/invoice/ajax_datagrid_total",
                type: 'GET',
                dataType: 'json',
                data:{
                    "InvoiceType":$("#invoice_filter [name='InvoiceType']").val(),
                    "InvoiceNumber":$("#invoice_filter [name='InvoiceNumber']").val(),
                    "IssueDateStart":$("#invoice_filter [name='IssueDateStart']").val(),
                    "IssueDateEnd":$("#invoice_filter [name='IssueDateEnd']").val(),
                    "zerovalueinvoice":$("#invoice_filter [name='zerovalueinvoice']").prop("checked"),
                    "Overdue" : $("#invoice_filter [name='Overdue']").prop("checked"),
                    "bDestroy": true,
                    "bProcessing":true,
                    "bServerSide":true,
                    "sAjaxSource": baseurl + "/customer/invoice/ajax_datagrid/type",
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[3, 'desc']],},
                success: function(response1) {
                    console.log("sum of result"+response1);
                    if(response1.total_grand!=null)
                    {
                        $('.result_row').remove();
                        $('.result_row').hide();
                        $('#table-4 tbody').append('<tr class="result_row"><td><strong>@lang('routes.TABLE_TOTAL')</strong></td><td align="right" colspan="3"></td><td class="leftsideview"><strong>'+response1.total_grand+'</strong></td><td class="leftsideview"><strong>'+response1.os_pp+'</strong></td><td colspan="2"></td></tr>');
                    }
                },
            });
        }
        $('table tbody').on('click', '.view-invoice-in', function (ev) {
            var cur_obj = $(this).parent().parent().find("div.hiddenRowData");
            InvoiceID = cur_obj.find("input[name='InvoiceID']").val();
            $.ajax({
                url: baseurl + '/customer/invoice/getInvoiceDetail',
                data: 'InvoiceID='+InvoiceID,
                dataType: 'json',
                success: function (response) {
                    $("#modal-invoice-in-view").find("[data-id='StartDate']").html(response.StartDate);
                    $("#modal-invoice-in-view").find("[data-id='StartTime']").html(response.StartTime);
                    $("#modal-invoice-in-view").find("[data-id='EndDate']").html(response.EndDate);
                    $("#modal-invoice-in-view").find("[data-id='EndTime']").html(response.EndTime);
                    $("#modal-invoice-in-view").find("[data-id='Description']").html(response.Description);
                },
                type: 'POST'
            });
            for(var i = 0 ; i< list_fields.length; i++){
            $("#modal-invoice-in-view").find("[data-id='"+list_fields[i]+"']").html('');
                if(list_fields[i] == 'Attachment'){
                    if(cur_obj.find("input[name='"+list_fields[i]+"']").val() != ''){
                        var down_html = ' <a href="' + baseurl +'/customer/invoice/download_invoice_file/'+cur_obj.find("input[name='InvoiceID']").val() +'" class="edit-invoice btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>@lang('routes.BUTTON_DOWNLOAD_CAPTION')</a>';
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
                if (!confirm("@lang('routes.CUST_PANEL_PAGE_INVOICE_BUTTON_PAY_NOW_CONFIRM_MSG')")) {
                    return;
                }
                //console.log(InvoiceIDs);

                paynow_url = '/customer/PaymentMethodProfiles/paynow/' + accoutid;
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
    right: 30px;
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
          <h4 class="modal-title"> <a class="btn btn-primary print btn-sm btn-icon icon-left" href=""> <i class="entypo-print"></i> @lang('routes.CUST_PANEL_PAGE_INVOICE_MODAL_PRINT_INVOICE_TITLE') </a> </h4>
        </div>
        <div class="modal-body"> @lang('routes.MESSAGE_CONTENT_LOADION') </div>
        <div class="modal-footer">
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> @lang('routes.BUTTON_CLOSE_CAPTION') </button>
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
          <h4 class="modal-title">@lang('routes.CUST_PANEL_PAGE_INVOICE_MODAL_VIEW_INVOICE_TITLE')</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="field-5" class="col-sm-2 control-label">@lang('routes.CUST_PANEL_PAGE_INVOICE_MODAL_VIEW_INVOICE_FIELD_ACCOUNT_NAME')<span id="currency"></span></label>
            <div class="col-sm-4 control-label"> <span data-id="AccountName">{{Customer::get_accountName()}}</span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_INVOICE_MODAL_VIEW_INVOICE_FIELD_START_DATE')</label>
            <div class="col-sm-4 control-label"> <span data-id="StartDate"></span> <span data-id="StartTime"></span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_INVOICE_MODAL_VIEW_INVOICE_FIELD_END_DATE')</label>
            <div class="col-sm-4 control-label"> <span data-id="EndDate"></span> <span data-id="EndTime"></span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_INVOICE_MODAL_VIEW_INVOICE_FIELD_ISSUE_DATE')</label>
            <div class="col-sm-4 control-label"> <span data-id="IssueDate"></span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_INVOICE_MODAL_VIEW_INVOICE_FIELD_INVOICE_NUMBER')</label>
            <div class="col-sm-4 control-label"> <span data-id="InvoiceNumber"></span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_INVOICE_MODAL_VIEW_INVOICE_FIELD_GRAND_TOTAL')</label>
            <div class="col-sm-4 control-label"> <span data-id="GrandTotal"></span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_INVOICE_MODAL_VIEW_INVOICE_FIELD_DESCRIPTION')</label>
            <div class="col-sm-4 control-label"> <span data-id="Description"></span> </div>
          </div>
          <!--<div class="form-group">
                        <label class="col-sm-2 control-label" for="field-1">Attachment</label>
                        <div class="col-sm-4 control-label">
                            <span data-id="Attachment"></span>
                        </div>
                    </div>--> 
        </div>
        <div class="modal-footer">
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> @lang('routes.BUTTON_CLOSE_CAPTION') </button>
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
        <h4 class="modal-title"> @lang('routes.CUST_PANEL_PAGE_INVOICE_MODAL_PAY_NOW_TITLE') </h4>
      </div>
      <div class="modal-body"> </div>
      <div class="modal-footer">
        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> @lang('routes.BUTTON_CLOSE_CAPTION') </button>
      </div>
    </div>
  </div>
</div>
@stop