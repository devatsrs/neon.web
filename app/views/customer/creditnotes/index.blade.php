@extends('layout.customer.main')

@section('content')
    <ol class="breadcrumb bc-3">
        <li> <a href="#"><i class="entypo-home"></i>@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_TITLE')</a> </li>
    </ol>
    <h3>{{cus_lang('CUST_PANEL_PAGE_CREDITNOTE_TITLE')}}</h3>
    @include('includes.errors')
    @include('includes.success')
    <div class="row">
        <div class="col-md-12">
            <form id="creditnote_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title"> @lang('routes.CUST_PANEL_FILTER_TITLE') </div>
                        <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_FILTER_FIELD_TYPE')</label>
                            <div class="col-sm-2"> {{Form::select('CreditNotesStatus',CreditNotes::$creditnotes_status_customer,Input::get('CreditNotesStatus'),array("class"=>"select2 small"))}} </div>
                            <label for="field-1" class="col-sm-2 control-label">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_FILTER_FIELD_START_DATE')</label>
                            <div class="col-sm-2"> {{ Form::text('IssueDateStart', Input::get('StartDate'), array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }} </div>

                            <label for="field-1" class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_FILTER_FIELD_NUMBER')</label>
                            <div class="col-sm-2"> {{ Form::text('CreditNotesNumber', '', array("class"=>"form-control")) }} </div>
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
            <th width="10%">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_TBL_CREDITNOTE_NUMBER')</th>
            <th width="10%">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_TBL_ISSUE_DATE')</th>
            <th width="10%">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_TBL_GRAND_TOTAL')</th>
            <th width="10%">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_TBL_AVAILABLE_BALANCE')</th>
            <th width="10%">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_TBL_CREDITNOTE_STATUS')</th>
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
            var creditnote_status = JSON.parse('{{$creditnote_status_json}}');
            var list_fields  = ['CreditNotesNumber','IssueDate','GrandTotal','CreditNotesStatus','CreditNotesID','Description','Attachment','AccountID','BillingEmail','AvailableBalance'];

            $searchFilter.CreditNotesStatus = $("#creditnote_filter [name='CreditNotesStatus']").val();
            $searchFilter.CreditNotesNumber = $("#creditnote_filter [name='CreditNotesNumber']").val();
            $searchFilter.IssueDateStart = $("#creditnote_filter [name='IssueDateStart']").val();

            data_table = $("#table-4").dataTable({
                "oLanguage": {
                    "sUrl": baseurl + "/translate/datatable_Label"
                },
                "bDestroy": true,
                "bProcessing":true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/customer/creditnotes/ajax_datagrid/type",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[3, 'desc']],
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"CreditNotesStatus","value":$searchFilter.CreditNotesStatus},{"name":"CreditNotesNumber","value":$searchFilter.CreditNotesNumber},{"name":"IssueDateStart","value":$searchFilter.IssueDateStart},{"name":"IssueDateEnd","value":$searchFilter.IssueDateEnd},{"name":"zerovaluecreditnote","value":$searchFilter.zerovaluecreditnote});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"CreditNotesStatus","value":$searchFilter.CreditNotesStatus},{"name":"CreditNotesNumber","value":$searchFilter.CreditNotesNumber},{"name":"IssueDateStart","value":$searchFilter.IssueDateStart},{"name":"IssueDateEnd","value":$searchFilter.IssueDateEnd},{"name":"zerovaluecreditnote","value":$searchFilter.zerovaluecreditnote},{ "name": "Export", "value": 1});
                },
                "aoColumns":
                        [

                            {  "bSortable": true
                            },  // 2 IssueDate
                            {  "bSortable": true },  // 3 IssueDate
                            {
                                "bSortable": true,
                                mRender: function (id, type, full) { return "<span class='leftsideview'>"+full[2]+"</span>" }
                            },  // 4 GrandTotal
                            {
                                "bSortable": true,
                                mRender: function (id, type, full) { return "<span class='leftsideview'>"+full[10]+"</span>"}
                            },  // 4 GrandTotal
                            {  "bSortable": true,
                                mRender:function( id, type, full){
                                    return creditnote_status[full[3]];
                                }

                            },  // 5 InvoiceStatus
                            {
                                "bSortable": false,
                                mRender: function ( id, type, full ) {
                                    var action , invoice_preview;
                                    action = '<div class = "hiddenRowData" >';

                                    invoice_preview = (baseurl + "/creditnotes/{id}/cview").replace("{id}",full[7]+'-'+full[4]);

                                    for(var i = 0 ; i< list_fields.length; i++){
                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                    }
                                    action += '</div>';

                                    action += ' <a href="'+invoice_preview+'" target="_blank" title="@lang('routes.BUTTON_VIEW_CAPTION')" class="view-invoice-sent btn btn-default btn-sm"><i class="fa fa-eye"></i></a>';
                                    return action;
                                }
                            },
                        ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "@lang('routes.BUTTON_EXPORT_EXCEL_CAPTION')",
                            "sUrl": baseurl + "/customer/creditnotes/ajax_datagrid/xlsx", //baseurl + "/generate_xls.php",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "@lang('routes.BUTTON_EXPORT_CSV_CAPTION')",
                            "sUrl": baseurl + "/customer/creditnotes/ajax_datagrid/csv", //baseurl + "/generate_xls.php",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function() {
                    get_total_grand(); //get result total
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    if(typeof customer_alignment!="undefined" && customer_alignment=="right"){
                        $('.pull-right, .pull-left').addClass('flip');
                    }
                }

            });
            $("#creditnote_filter").submit(function(e){

                e.preventDefault();
                $searchFilter.CreditNotesStatus = $("#creditnote_filter [name='CreditNotesStatus']").val();
                $searchFilter.CreditNotesNumber = $("#creditnote_filter [name='CreditNotesNumber']").val();
                $searchFilter.IssueDateStart = $("#creditnote_filter [name='IssueDateStart']").val();

                data_table.fnFilter('', 0);
                return false;
            });
            function get_total_grand(){
                $.ajax({
                    url: baseurl + "/customer/creditnotes/ajax_datagrid_total",
                    type: 'GET',
                    dataType: 'json',
                    data:{
                        "CreditNotesStatus":$("#creditnote_filter [name='CreditNotesStatus']").val(),
                        "CreditNotesNumber":$("#creditnote_filter [name='CreditNotesNumber']").val(),
                        "IssueDateStart":$("#creditnote_filter [name='IssueDateStart']").val(),
                        "IssueDateEnd":$("#creditnote_filter [name='IssueDateEnd']").val(),
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
                            $('#table-4 tbody').append('<tr class="result_row"><td><strong>@lang('routes.TABLE_TOTAL')</strong></td><td align="right" colspan="1"></td><td class="leftsideview"><strong>$'+response1.total_grand+'</strong></td><td class="leftsideview"></td><td colspan="2"></td></tr>');
                        }
                    },
                });
            }
            $('table tbody').on('click', '.view-invoice-in', function (ev) {
                var cur_obj = $(this).parent().parent().find("div.hiddenRowData");
                InvoiceID = cur_obj.find("input[name='InvoiceID']").val();
                $.ajax({
                    url: baseurl + '/customer/creditnotes/getCreditNotesDetail',
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

            /*$('#table-4 tbody').on('click', 'tr', function() {
                if($(this).find('.rowcheckbox').hasClass('rowcheckbox')){
                    $(this).toggleClass('selected');
                    if ($(this).hasClass('selected')) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                    }
                }
            });*/
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
                        <h4 class="modal-title"> <a class="btn btn-primary print btn-sm btn-icon icon-left" href=""> <i class="entypo-print"></i> @lang('routes.CUST_PANEL_PAGE_CREDITNOTE_MODAL_PRINT_CREDITNOTE_TITLE') </a> </h4>
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
                        <h4 class="modal-title">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_MODAL_VIEW_CREDITNOTE_TITLE')</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="field-5" class="col-sm-2 control-label">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_MODAL_VIEW_CREDITNOTE_FIELD_ACCOUNT_NAME')<span id="currency"></span></label>
                            <div class="col-sm-4 control-label"> <span data-id="AccountName">{{Customer::get_accountName()}}</span> </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_MODAL_VIEW_CREDITNOTE_FIELD_START_DATE')</label>
                            <div class="col-sm-4 control-label"> <span data-id="StartDate"></span> <span data-id="StartTime"></span> </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_MODAL_VIEW_CREDITNOTE_FIELD_END_DATE')</label>
                            <div class="col-sm-4 control-label"> <span data-id="EndDate"></span> <span data-id="EndTime"></span> </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_MODAL_VIEW_CREDITNOTE_FIELD_ISSUE_DATE')</label>
                            <div class="col-sm-4 control-label"> <span data-id="IssueDate"></span> </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_MODAL_VIEW_CREDITNOTE_FIELD_CREDITNOTE_NUMBER')</label>
                            <div class="col-sm-4 control-label"> <span data-id="InvoiceNumber"></span> </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_MODAL_VIEW_CREDITNOTE_FIELD_GRAND_TOTAL')</label>
                            <div class="col-sm-4 control-label"> <span data-id="GrandTotal"></span> </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_CREDITNOTE_MODAL_VIEW_CREDITNOTE_FIELD_DESCRIPTION')</label>
                            <div class="col-sm-4 control-label"> <span data-id="Description"></span> </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> @lang('routes.BUTTON_CLOSE_CAPTION') </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop