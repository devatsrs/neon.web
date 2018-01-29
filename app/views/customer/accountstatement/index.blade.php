@extends('layout.customer.main')

@section('content')
<style>
    table th {
        background-color: #fff !important;
        font-weight: bold;
        color: #333 !important;
    }
    table td {
        background-color: #fff !important;
    }
</style>
    <ol class="breadcrumb bc-3">
        <li>
            <a href="#"><i class="entypo-home"></i>@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TITLE')</a>
        </li>
    </ol>

    <h3>@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TITLE')</h3>
    <div class="tab-content">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="row">
                <div class="col-md-12">
                    <form role="form" id="account-statement-search" method="post"  action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                        <div class="panel panel-primary" data-collapsed="0">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    @lang('routes.BUTTON_SEARCH_CAPTION')
                                </div>

                                <div class="panel-options">
                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                </div>
                            </div>

                            <div class="panel-body">
                                <div class="form-group">
                                    <label class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_FILTER_FIELD_START_DATE')</label>
                                    <div class="col-sm-2">
                                        <input type="text" name="StartDate" class="form-control datepicker" data-date-format="yyyy-mm-dd" id="field-5" placeholder="" value="{{date("Y-m-d",strtotime("-7 days"))}}" >
                                    </div>

                                    <label class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_FILTER_FIELD_END_DATE')</label>
                                    <div class="col-sm-2">
                                        <input type="text" name="EndDate" class="form-control datepicker" data-date-format="yyyy-mm-dd" id="field-5" placeholder="" value="{{date("Y-m-d")}}">
                                    </div>
                                </div>

                                <p class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                                        <i class="entypo-search"></i>
                                        @lang('routes.BUTTON_SEARCH_CAPTION')
                                    </button>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="clear"></div>

            <div class="row">
                <div class="col-md-12">
                    <div style="width:95px;" class="input-group-btn pull-right">
                        <div class="export-data">
                            <div class="DTTT btn-group">
                                <a class="btn btn-white save-collection" style="display: none;" id="ToolTables_table-4_0">
                                    <undefined>@lang('routes.BUTTON_EXPORT_EXCEL_CAPTION')</undefined>
                                </a>
                            </div>
                        </div>
                    </div><!-- /btn-group -->
                </div>
                <div class="clear"></div>
                <div id="table-4_processing" class="dataTables_processing" style="display: none;">@lang('routes.DATATABLE_PROCESSING')</div>
            </div>
            <div style="width: 100%; overflow-x:auto ">
                <table class="table table-bordered datatable" id="table-4">
                    <thead>
                    <tr>
                        <th colspan="4" style="text-align: center;">{{$CompanyName}} @lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_INVOICE')</th>
                        <th colspan="4" style="text-align: center;">@if(isset($AccountName)) {{$AccountName}} @endif @lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_PAYMENT')</th>
                        <th colspan="5" style="text-align: center;">@if(isset($AccountName)) {{$AccountName}} @endif @lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_INVOICE')</th>
                        <th colspan="2" style="text-align: center;">{{$CompanyName}} @lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_PAYMENT')</th>
                    </tr>
                    <tr >
                        <th style="text-align: center;" width="5%">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_NO')</th>
                        <th style="text-align: center;" width="8%" >@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_PERIOD')</th>
                        <th style="text-align: center;" width="6%">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_AMOUNT')</th>
                        <th style="text-align: center;" width="6%">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_PENDING_DISPUTE')</th>
                        <th style="text-align: center;" width="1%"></th>
                        <th style="text-align: center;" width="8%">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_DATE')</th>
                        <th style="text-align: center;" width="6%">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_AMOUNT')</th>
                        <th style="text-align: center;" width="1%"></th>
                        <th style="text-align: center;" width="6%">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_NO')</th>
                        <th style="text-align: center;" width="8%">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_PERIOD')</th>
                        <th style="text-align: center;" width="6%">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_AMOUNT')</th>
                        <th style="text-align: center;" width="6%">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_PENDING_DISPUTE')</th>
                        <th style="text-align: center;" width="1%"></th>
                        <th style="text-align: center;" width="9%">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_DATE')</th>
                        <th style="text-align: center;" width="6%">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_AMOUNT')</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr></tr>
                    </tbody>
                </table>
            </div>
            <iframe id="RemotingIFrame" name="RemotingIFrame" style="border: 0px none; width: 0px; height: 0px;">
                <html>
                <head></head>
                <body>
                <form method="post" action="">

                </form>
                </body>
                </html>
            </iframe>
            <script type="text/javascript">
                $(document).ready(function($){
                    $('#account-statement-search').submit(function(e){
                        e.preventDefault();
                        var InvoiceInAmount = 0;
                        var InvoiceOutAmount = 0;
                        var PaymentsInAmount = 0;
                        var PaymentsOutAmount = 0;
                        var Ballance = 0;
                        var check1= '';
                        var check2='';
                        $('#table-4_processing').show();
                        $('#ToolTables_table-4_0').hide();
                        $.ajax({
                            url: baseurl+'/customer/account_statement/ajax_datagrid',
                            data: {
                                StartDate: $("#account-statement-search [name='StartDate']").val(),
                                EndDate: $("#account-statement-search [name='EndDate']").val()
                            },
                            error: function() {
                                toastr.error("error", "Error", toastr_opts);
                            },
                            dataType: 'json',
                            success: function(data) {

                                var InvoiceOutAmountTotal  = data.InvoiceOutAmountTotal;
                                var PaymentInAmountTotal  = data.PaymentInAmountTotal;
                                var InvoiceInAmountTotal  = data.InvoiceInAmountTotal;
                                var PaymentOutAmountTotal  = data.PaymentOutAmountTotal;
                                var InvoiceOutDisputeAmountTotal  = data.InvoiceOutDisputeAmountTotal;
                                var InvoiceInDisputeAmountTotal  = data.InvoiceInDisputeAmountTotal;
                                var CompanyBalance = data.CompanyBalance;
                                var AccountBalance = data.AccountBalance;
                                var OffsetBalance = data.OffsetBalance;
                                var BroughtForwardOffset = data.BroughtForwardOffset;

                                var CurencySymbol  = data.CurencySymbol;
                                var roundplaces  = data.roundplaces;

                                var result = data.result;

                                if(result.length<1){
                                    $('#table-4 > tbody ').html('<tr class="odd"><td valign="top" colspan="15" class="dataTables_empty">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_NO_DATA')</td></tr>');
                                    $('#table-4_processing').hide();
                                    //return false;
                                }
                                $('#table-4 > tbody > tr').remove();
                                $('#table-4 > tbody').append('<tr></tr>');

                                for (i = 0; i < result.length; i++) {

                                    //console.log(result[i]);

                                    //Invoice Out
                                    if( typeof result[i]['InvoiceOut_InvoiceNo'] == 'undefined' ) {
                                        result[i]['InvoiceOut_InvoiceNo'] = '';
                                    }
                                    if( typeof result[i]['InvoiceOut_PeriodCover'] == 'undefined' ) {
                                        result[i]['InvoiceOut_PeriodCover'] = '';
                                    }
                                    if( typeof result[i]['InvoiceOut_Amount'] == 'undefined' ) {
                                        result[i]['InvoiceOut_Amount'] = '';
                                    }
                                    if( typeof result[i]['InvoiceOut_DisputeAmount'] == 'undefined' ) {
                                        result[i]['InvoiceOut_DisputeAmount'] = '';
                                    }
                                    //Payment In
                                    if( typeof result[i]['PaymentIn_PeriodCover'] == 'undefined' ) {
                                        result[i]['PaymentIn_PeriodCover'] = '';
                                    }
                                    if( typeof result[i]['PaymentIn_PaymentID'] == 'undefined' ) {
                                        result[i]['PaymentIn_PaymentID'] = '';
                                    }
                                    if( typeof result[i]['PaymentIn_Amount'] == 'undefined' ) {
                                        result[i]['PaymentIn_Amount'] = '';
                                    }
                                    //Invoice In
                                    if( typeof result[i]['InvoiceIn_InvoiceNo'] == 'undefined' ) {
                                        result[i]['InvoiceIn_InvoiceNo'] = '';
                                    }
                                    if( typeof result[i]['InvoiceIn_PeriodCover'] == 'undefined' ) {
                                        result[i]['InvoiceIn_PeriodCover'] = '';
                                    }
                                    if( typeof result[i]['InvoiceIn_Amount'] == 'undefined' ) {
                                        result[i]['InvoiceIn_Amount'] = '';
                                    }
                                    if( typeof result[i]['InvoiceIn_DisputeAmount'] == 'undefined' ) {
                                        result[i]['InvoiceIn_DisputeAmount'] = '';
                                    }
                                    //Payment Out
                                    if( typeof result[i]['PaymentOut_PeriodCover'] == 'undefined' ) {
                                        result[i]['PaymentOut_PeriodCover'] = '';
                                    }
                                    if( typeof result[i]['PaymentOut_PaymentID'] == 'undefined' ) {
                                        result[i]['PaymentOut_PaymentID'] = '';
                                    }
                                    if( typeof result[i]['PaymentOut_Amount'] == 'undefined' ) {
                                        result[i]['PaymentOut_Amount'] = '';
                                    }



                                    if( result[i]['PaymentIn_PaymentID'] !='' ) {
                                        result[i]['PaymentIn_PaymentID'] = '<a class="paymentsModel leftsideview" id="'+result[i]['PaymentIn_PaymentID']+'" href="javascript:;" onClick="paymentsModel(this);">'+result[i]['PaymentIn_Amount']+'</a>';
                                    } else {
                                        result[i]['PaymentIn_PaymentID'] = '';
                                    }
                                    if( result[i]['PaymentOut_PaymentID'] !='' ) {
                                        result[i]['PaymentOut_PaymentID'] = '<a class="paymentsModel leftsideview" id="' + result[i]['PaymentOut_PaymentID'] + '" href="javascript:;" onClick="paymentsModel(this);">'+result[i]['PaymentOut_Amount']+'</a>';
                                    } else {
                                        result[i]['PaymentOut_PaymentID'] = '';
                                    }


                                    if( result[i]['InvoiceIn_DisputeID'] !='' ) {
                                        result[i]['InvoiceIn_DisputeID'] = '<a style="color:#cc2424;font-weight: bold" class="DisputeModel leftsideview" id="' + result[i]['InvoiceIn_DisputeID'] + '" href="javascript:;" onClick="disputesModel(this);">'+result[i]['InvoiceIn_DisputeAmount']+'</a>';
                                    } else {
                                        result[i]['InvoiceIn_DisputeID'] = '';
                                    }
                                    if( result[i]['InvoiceOut_DisputeID'] !='' ) {
                                        result[i]['InvoiceOut_DisputeID'] = '<a style="color:#cc2424;font-weight: bold" class="DisputeModel leftsideview" id="' + result[i]['InvoiceOut_DisputeID'] + '" href="javascript:;" onClick="disputesModel(this);">'+result[i]['InvoiceOut_DisputeAmount']+'</a>';
                                    } else {
                                        result[i]['InvoiceOut_DisputeID'] = '';
                                    }


                                    newRow = "<tr>" +
                                                // Invoice Out
                                            "<td align='center'>"+result[i]['InvoiceOut_InvoiceNo']+"</td>" +
                                            "<td align='center'>"+result[i]['InvoiceOut_PeriodCover']+"</td>" +
                                            "<td align='right' class='leftsideview'>"+ result[i]['InvoiceOut_Amount'] +"</td>" +
                                            "<td align='right' class='leftsideview'>"+ result[i]['InvoiceOut_DisputeID'] +"</td>" +
                                            "<td> </td>" +

                                                // Payment In
                                            "<td align='center'>"+result[i]['PaymentIn_PeriodCover']+"</td>" +
                                            "<td align='right' class='leftsideview'>"+result[i]['PaymentIn_PaymentID']+"</td>" +
                                            "<td> </td>" +

                                                // Invoice In
                                            "<td align='center'>"+result[i]['InvoiceIn_InvoiceNo']+"</td>" +
                                            "<td align='center'>"+result[i]['InvoiceIn_PeriodCover']+"</td>" +
                                            "<td align='right' class='leftsideview'>"+ result[i]['InvoiceIn_Amount'] +"</td>" +
                                            "<td align='right' class='leftsideview'>"+ result[i]['InvoiceIn_DisputeID'] +"</td>" +

                                            "<td> </td>" +

                                                //Payment Out
                                            "<td align='center'>"+ result[i]['PaymentOut_PeriodCover'] +"</td>" +
                                            "<td align='right' class='leftsideview'>"+ result[i]['PaymentOut_PaymentID'] +"</td>" +
                                            "</tr>";


                                    $('#table-4 > tbody > tr:last').after(newRow);

                                }

                                newRow =
                                        '<tr>' +
                                        '<th>@lang('routes.TABLE_TOTAL')</th>' +
                                        '<th></th>' +
                                        '<th style="text-align: right;" class="leftsideview">'+ CurencySymbol+ InvoiceOutAmountTotal +'</th>' +
                                        '<th style="color:#cc2424 !important;text-align: right;" class="leftsideview">' + CurencySymbol + InvoiceOutDisputeAmountTotal +'</th>' +
                                        '<th></th>' +
                                        '<th></th>' +
                                        '<th style="text-align: right;" class="leftsideview">'+ CurencySymbol+ PaymentInAmountTotal+'</th>' +
                                        '<th></th>' +
                                        '<th></th>' +
                                        '<th></th>' +
                                        '<th style="text-align: right;" class="leftsideview">'+ CurencySymbol + InvoiceInAmountTotal +'</th>' +
                                        '<th style="color:#cc2424 !important;text-align: right;" class="leftsideview">' + CurencySymbol + InvoiceInDisputeAmountTotal +'</th>' +
                                        '<th></th>' +
                                        '<th></th>' +
                                        '<th style="text-align: right;" class="leftsideview">'+ CurencySymbol + PaymentOutAmountTotal +'</th>' +
                                        '</tr>'+

                                        '<tr><th colspan="15"></th></tr>'+

                                        '<tr><th colspan="2" style="text-align: right;text-transform: uppercase" >@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_BALANCE_AFTER_OFFSET')</th><th class="leftsideview">' + CurencySymbol + OffsetBalance +'</th><th></th><th></th><th></th><th></th><th></th><th colspan="2" style="text-align: right;text-transform: uppercase">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_TBL_BALANCE_BROUGHT_FORWARD') </th><th class="leftsideview">' + CurencySymbol + BroughtForwardOffset +'</th><th></th><th></th><th></th><th></th>' +
                                        '</tr>' ;

                                $('#table-4 > tbody > tr:last').after(newRow);
                                $('#table-4_processing').hide();
                                $('#ToolTables_table-4_0').show();
                            },
                            type: 'GET'
                        });

                    });
                    $('#ToolTables_table-4_0').click(function(){
                        var StartDate = $("#account-statement-search [name='StartDate']").val();
                        var EndDate =  $("#account-statement-search [name='EndDate']").val();
                        var url = baseurl + "/customer/account_statement/exports/xlsx?&StartDate="+StartDate+"&EndDate="+EndDate;
                        $( "#RemotingIFrame" ).contents().find("form").attr('action',url);
                        window.open(url, "RemotingIFrame");
                    });
                });
                function paymentsModel(self){
                    id = $(self).attr('id');
                    if(id=='null' || id==''){
                        return false;
                    }
                    $.ajax({
                        url: baseurl + '/customer/account_statement/payment',
                        data: {
                            id: id
                        },
                        error: function () {
                            toastr.error("error", "Error", toastr_opts);
                        },
                        dataType: 'json',
                        success: function (data) {
                            $("#view-modal-payment [name='InvoiceNo']").text(data['InvoiceNo']);
                            $("#view-modal-payment [name='PaymentDate']").text(data['PaymentDate']);
                            $("#view-modal-payment [name='PaymentMethod']").text(data['PaymentMethod']);
                            $("#view-modal-payment [name='PaymentType']").text(data['PaymentType']);
                            $("#view-modal-payment [name='Currency']").text(data['Currency']);
                            $("#view-modal-payment [name='Amount']").text(parseFloat(Math.round(data['Amount'] * 100) / 100).toFixed(2));
                            $("#view-modal-payment [name='Notes']").text(data['Notes']);
                            $('#view-modal-payment').modal('show');
                        },
                        type: 'GET'
                    });

                }

                function disputesModel(self){

                    var DisputeID = $(self).attr("ID");
                    showAjaxModal("/disputes/"+DisputeID+"/view", "view-modal-dispute");
                }
            </script>
            <style>
                .dataTables_filter label{
                    display:none !important;
                }
                .dataTables_wrapper .export-data{
                    right: 30px !important;
                }
            </style>

            @include('includes.errors')
            @include('includes.success')

        </div>
    </div>
@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="view-modal-payment">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_VIEW_PAYMENT_TITLE')</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!--<div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Currency</label>
                                <div class="col-sm-12" name="Currency"></div>
                            </div>
                        </div>-->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_VIEW_PAYMENT_FIELD_INVOICE')</label>
                                <div class="col-sm-12" name="InvoiceNo"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_VIEW_PAYMENT_FIELD_PAYMENT_DATE')</label>
                                <div class="col-sm-12" name="PaymentDate"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_VIEW_PAYMENT_FIELD_PAYMENT_METHOD')</label>
                                <div class="col-sm-12" name="PaymentMethod"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_VIEW_PAYMENT_FIELD_ACTION')</label>
                                <div class="col-sm-12" name="PaymentType"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_VIEW_PAYMENT_FIELD_AMOUNT')</label>
                                <div class="col-sm-12" name="Amount"></div>
                                <input type="hidden" name="PaymentID" >
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_VIEW_PAYMENT_FIELD_NOTES')</label>
                                <div class="col-sm-12" name="Notes"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="view-modal-dispute">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">@lang('routes.CUST_PANEL_PAGE_ACCOUNT_STATEMENT_MODAL_DISPUTE_TITLE')</h4>
                </div>
                <div class="modal-body">


                </div>
                <div class="modal-footer">
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> @lang('routes.BUTTON_CLOSE_CAPTION') </button>
                </div>

            </div>
        </div>
    </div>
@stop
