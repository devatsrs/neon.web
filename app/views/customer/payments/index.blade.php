@extends('layout.customer.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="#"><i class="entypo-home"></i>@lang('routes.CUST_PANEL_PAGE_PAYMENTS_TITLE')</a>
        </li>
    </ol>

    <h3>@lang('routes.CUST_PANEL_PAGE_PAYMENTS_TITLE')</h3>
    <div class="tab-content">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="row">
                <div class="col-md-12">
                    <form role="form" id="payment-table-search" method="post"  action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                        <div class="panel panel-primary" data-collapsed="0">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    @lang('routes.CUST_PANEL_FILTER_TITLE')
                                </div>

                                <div class="panel-options">
                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                </div>
                            </div>

                            <div class="panel-body">
                                <div class="form-group">

                                    <label class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_INVOICE_NO')</label>
                                    <div class="col-sm-2">
                                        <input type="text" name="InvoiceNo" class="form-control" id="field-1" placeholder="" value="{{Input::get('InvoiceNo')}}" />

                                    </div>
                                    <label for="field-1" class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_ACTION')</label>
                                    <div class="col-sm-2">
                                        {{ Form::select('type', $action, Input::get('Type'), array("class"=>"select2 small","data-allow-clear"=>"true","data-placeholder"=>"Select Type")) }}
                                    </div>

                                    <label for="field-1" class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_PAYMENT_METHOD')</label>
                                    <div class="col-sm-2">
                                        {{ Form::select('paymentmethod', $method, Input::get('paymentmethod') , array("class"=>"select2 small","data-allow-clear"=>"true","data-placeholder"=>"Select Type")) }}
                                    </div>
                                </div>

                                <!--payment date start -->
                                <div class="form-group">
                                    <label class="col-sm-1 control-label small_label" for="PaymentDate_StartDate">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_START_DATE')</label>
                                    <div class="col-sm-2 col-sm-e2">
                                        <input autocomplete="off" type="text" name="PaymentDate_StartDate" id="PaymentDate_StartDate" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="{{Input::get('StartDate')}}" data-enddate="{{date('Y-m-d')}}" />
                                    </div>
                                    <div class="col-sm-2 col-sm-e2">
                                        <input type="text" name="PaymentDate_StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00:00" data-show-seconds="true" data-template="dropdown" placeholder="00:00:00" class="form-control timepicker">
                                    </div>
                                    <label  class="col-sm-1 control-label small_label" for="PaymentDate_EndDate">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_FILTER_FIELD_END_DATE')</label>
                                    <div class="col-sm-2 col-sm-e2">
                                        <input autocomplete="off" type="text" name="PaymentDate_EndDate" id="PaymentDate_EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{Input::get('EndDate')}}" data-enddate="{{date('Y-m-d')}}" />
                                    </div>
                                    <div class="col-sm-2 col-sm-e2">
                                        <input type="text" name="PaymentDate_EndTime" data-minute-step="5" data-show-meridian="false" data-default-time="23:59:59" value="23:59:59" data-show-seconds="true" placeholder="00:00:00" data-template="dropdown" class="form-control timepicker">
                                    </div>
                                </div>
                                <!--payment date end -->

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
            <br>
            @if(isset($AccountDetailsID) && $AccountDetailsID==1)
            <p class="pull-right add-payment-btn">
                <a href="#" id="add-new-payment" class="btn btn-primary ">
                    <i class="entypo-plus"></i>
                    @lang('routes.CUST_PANEL_PAGE_PAYMENTS_BUTTON_ADD_NEW')
                </a>
            </p>
            @endif
            <table class="table table-bordered datatable" id="table-4">
                <thead>
                <tr>
                    <th width="15%">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_TBL_INVOICE_NO')</th>
                    <th width="20%">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_TBL_AMOUNT')</th>
                    <th width="15%">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_TBL_TYPE')</th>
                    <th width="20%">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_TBL_PAYMENT_DATE')</th>
                    <th width="15%">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_TBL_STATUS')</th>
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
                    $("#payment-table-search").submit(function(e) {
                        e.preventDefault();
                        public_vars.$body = $("body");
                        //show_loading_bar(40);
                        $searchFilter.AccountID = $("#payment-table-search select[name='AccountID']").val();
                        $searchFilter.InvoiceNo = $("#payment-table-search [name='InvoiceNo']").val();
                        $searchFilter.type = $("#payment-table-search select[name='type']").val();
                        $searchFilter.paymentmethod = $("#payment-table-search select[name='paymentmethod']").val();
                        $searchFilter.PaymentDate_StartDate = $("#payment-table-search input[name='PaymentDate_StartDate']").val();
                        $searchFilter.PaymentDate_StartTime = $("#payment-table-search input[name='PaymentDate_StartTime']").val();
                        $searchFilter.PaymentDate_EndDate   = $("#payment-table-search input[name='PaymentDate_EndDate']").val();
                        $searchFilter.PaymentDate_EndTime   = $("#payment-table-search input[name='PaymentDate_EndTime']").val();
                        data_table = $("#table-4").dataTable({
                            "oLanguage": {
                                "sUrl": baseurl + "/translate/datatable_Label"
                            },
                            "bDestroy": true,
                            "bProcessing": true,
                            "bServerSide": true,
                            "sAjaxSource": baseurl + "/customer/payments/ajax_datagrid/type",
                            "fnServerParams": function (aoData) {
                                aoData.push({"name": "AccountID", "value": $searchFilter.AccountID},
                                        {"name": "InvoiceNo","value": $searchFilter.InvoiceNo},
                                        {"name": "type","value": $searchFilter.type},
                                        {"name": "paymentmethod","value": $searchFilter.paymentmethod},
                                        {"name": "PaymentDate_StartDate","value": $searchFilter.PaymentDate_StartDate},
                                        {"name": "PaymentDate_StartTime","value": $searchFilter.PaymentDate_StartTime},
                                        {"name": "PaymentDate_EndDate","value": $searchFilter.PaymentDate_EndDate},
                                        {"name": "PaymentDate_EndTime","value": $searchFilter.PaymentDate_EndTime}

                                );
                                data_table_extra_params.length = 0;
                                data_table_extra_params.push({"name": "AccountID", "value": $searchFilter.AccountID},
                                        {"name": "InvoiceNo","value": $searchFilter.InvoiceNo},
                                        {"name": "type","value": $searchFilter.type},
                                        {"name": "paymentmethod","value": $searchFilter.paymentmethod},
                                        {"name": "PaymentDate_StartDate","value": $searchFilter.PaymentDate_StartDate},
                                        {"name": "PaymentDate_StartTime","value": $searchFilter.PaymentDate_StartTime},
                                        {"name": "PaymentDate_EndDate","value": $searchFilter.PaymentDate_EndDate},
                                        {"name": "PaymentDate_EndTime","value": $searchFilter.PaymentDate_EndTime},
                                        {"name":"Export","value":1}
                                );

                            },
                            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                            "sPaginationType": "bootstrap",
                            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                            "aaSorting": [[3, 'desc']],
                            "aoColumns": [
                                {
                                    "bSortable": true, //Account
                                    mRender: function (id, type, full) {
                                        return full[10]
                                    }
                                }, //1   CurrencyDescription
                                {
                                    "bSortable": true, //Amount
                                    mRender: function (id, type, full) {
                                        var a = parseFloat(Math.round(full[3] * 100) / 100).toFixed(2);
                                        a = a.toString();

                                        return "<span class='leftsideview'>"+full[16]+"</span>"
                                    }
                                },
                                {
                                    "bSortable": true, //Type
                                    mRender: function (id, type, full) {
                                        return full[4]
                                    }
                                },

                                {
                                    "bSortable": true, //paymentDate
                                    mRender: function (id, type, full) {
                                        return full[6]
                                    }
                                },
                                {
                                    "bSortable": true, //status
                                    mRender: function (id, type, full) {
                                        return full[7]
                                    }
                                },
                                {                       //3  Action

                                    "bSortable": false,
                                    mRender: function (id, type, full) {
                                        var action, edit_, show_, delete_;
                                        var Approve_Payment = "{{ URL::to('payments/{id}/payment_approve_reject/approve')}}";
                                        var Reject_Payment = "{{ URL::to('payments/{id}/payment_approve_reject/reject')}}";
                                        Approve_Payment = Approve_Payment.replace('{id}', full[0]);
                                        Reject_Payment = Reject_Payment.replace('{id}', full[0]);
                                        action = '<div class = "hiddenRowData" >';
                                        action += '<input type = "hidden"  name = "PaymentID" value = "' + full[0] + '" / >';
                                        action += '<input type = "hidden"  name = "AccountName" value = "' + full[1] + '" / >';
                                        action += '<input type = "hidden"  name = "AccountID" value = "' + full[2] + '" / >';
                                        action += '<input type = "hidden"  name = "Amount" value = "' + full[16] + '" / >';
                                        action += '<input type = "hidden"  name = "PaymentType" value = "' + full[4] + '" / >';
                                        action += '<input type = "hidden"  name = "Currency" value = "' + full[5] + '" / >';
                                        action += '<input type = "hidden"  name = "PaymentDate" value = "' + full[6] + '" / >';
                                        action += '<input type = "hidden"  name = "Status" value = "' + full[7] + '" / >';
                                        action += '<input type = "hidden"  name = "CreatedBy" value = "' + full[8] + '" / >';
                                        action += '<input type = "hidden"  name = "InvoiceNo" value = "' + full[10] + '" / >';
                                        action += '<input type = "hidden"  name = "PaymentMethod" value = "' + full[11] + '" / >';
                                        action += '<input type = "hidden"  name = "Notes" value = "' + full[12] + '" / >';
                                        action += '</div>';
                                        action += ' <a data-name = "' + full[0] + '" data-id="' + full[0] + '" class="edit-payment btn btn-default btn-sm btn-icon icon-left"><i class="fa fa-eye"></i></i>@lang('routes.BUTTON_VIEW_CAPTION') </a>'
                                        <?php if(User::is('BillingAdmin')){?>
                                        if(full[7] != "Approved"){

                                            action += ' <div class="btn-group"><button href="#" class="btn generate btn-success btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')">@lang('routes.BUTTON_APPROVE_CAPTION')/@lang('routes.BUTTON_REJECT_CAPTION') <span class="caret"></span></button>'
                                            action += '<ul class="dropdown-menu dropdown-green" role="menu"><li><a href="' + Approve_Payment+ '" class="approvepayment" >@lang('routes.BUTTON_APPROVE_CAPTION')</a></li><li><a href="' + Reject_Payment + '" class="rejectpayment">@lang('routes.BUTTON_REJECT_CAPTION')</a></li></ul></div>';
                                        }
                                        <?php } ?>
                                        if(full[9]!= null){
                                            var Download = "@lang('routes.BUTTON_DOWNLOAD_CAPTION')";
                                            action += '<span class="col-md-offset-1"><a class="btn btn-success btn-sm btn-icon icon-left"  href="{{URL::to('customer/payments/download_doc')}}/'+full[0]+'" title="" ><i class="entypo-down"></i>'+Download+'</a></span>'
                                        }
                                        return action;
                                    }
                                }
                            ],
                            "oTableTools": {
                                "aButtons": [
                                    {
                                        "sExtends": "download",
                                        "sButtonText": "@lang('routes.BUTTON_EXPORT_EXCEL_CAPTION')",
                                        "sUrl": baseurl + "/customer/payments/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                                        sButtonClass: "save-collection btn-sm"
                                    },
                                    {
                                        "sExtends": "download",
                                        "sButtonText": "@lang('routes.BUTTON_EXPORT_CSV_CAPTION')",
                                        "sUrl": baseurl + "/customer/payments/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                                        sButtonClass: "save-collection btn-sm"
                                    }
                                ]
                            },
                            "fnDrawCallback": function () {
                                get_total_grand(); //get result total
                                $(".dataTables_wrapper select").select2({
                                    minimumResultsForSearch: -1
                                });
                            }

                        });


                        // Replace Checboxes
                        $(".pagination a").click(function (ev) {
                            replaceCheckboxes();
                        });
                    });

                    $('table tbody').on('click', '.edit-payment', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $('#view-modal-payment').trigger("reset");

                        Code = $(this).prev("div.hiddenRowData").find("input[name='Code']").val();
                        Description = $(this).prev("div.hiddenRowData").find("input[name='Description']").val();

                        $('#view-modal-payment').trigger("reset");

                        PaymentID = $(this).prev("div.hiddenRowData").find("input[name='PaymentID']").val();
                        AccountName = $(this).prev("div.hiddenRowData").find("input[name='AccountName']").val();
                        AccountID = $(this).prev("div.hiddenRowData").find("input[name='AccountID']").val();
                        Amount = $(this).prev("div.hiddenRowData").find("input[name='Amount']").val();
                        PaymentType = $(this).prev("div.hiddenRowData").find("input[name='PaymentType']").val();
                        Currency = $(this).prev("div.hiddenRowData").find("input[name='Currency']").val();
                        PaymentDate = $(this).prev("div.hiddenRowData").find("input[name='PaymentDate']").val();
                        Status = $(this).prev("div.hiddenRowData").find("input[name='Status']").val();
                        CreatedBy = $(this).prev("div.hiddenRowData").find("input[name='CreatedBy']").val();
                        InvoiceNo = $(this).prev("div.hiddenRowData").find("input[name='InvoiceNo']").val();
                        PaymentMethod = $(this).prev("div.hiddenRowData").find("input[name='PaymentMethod']").val();
                        Status = $(this).prev("div.hiddenRowData").find("input[name='Status']").val();
                        Notes = $(this).prev("div.hiddenRowData").find("input[name='Notes']").val();


                        $("#view-modal-payment [name='PaymentID']").text(PaymentID);
                        $("#view-modal-payment [name='AccountName']").text(AccountName);
                        $("#view-modal-payment [name='InvoiceNo']").text(InvoiceNo);
                        $("#view-modal-payment [name='PaymentDate']").text(PaymentDate);
                        $("#view-modal-payment [name='PaymentMethod']").text(PaymentMethod);
                        $("#view-modal-payment [name='PaymentType']").text(PaymentType);
                        $("#view-modal-payment [name='Currency']").text(Currency);
                        $("#view-modal-payment [name='Amount']").text(Amount);
                        $("#view-modal-payment [name='Notes']").text(Notes);

                        $('#view-modal-payment h4').html('View Payment');
                        $('#view-modal-payment').modal('show');
                    });

                    $('#add-new-payment').click(function (ev) {
                        ev.preventDefault();
                        $('#add-edit-payment-form').trigger("reset");
                        $("#add-edit-payment-form [name='PaymentMethod']").val('').trigger("change");
                        $("#add-edit-payment-form [name='PaymentType']").val('').trigger("change");
                        $("#add-edit-payment-form [name='PaymentID']").val('');
                        $('#add-edit-modal-payment h4').html("@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_ADD_PAYMENT_TITLE')");
                        $('#add-edit-modal-payment').modal('show');
                    });

                    $('#add-edit-payment-form').submit(function(e){
                        e.preventDefault();
                        var PaymentID = $("#add-edit-payment-form [name='PaymentID']").val();
                        var PaymentMethod = $("#add-edit-payment-form [name='PaymentMethod']").val();

                        if(PaymentMethod.indexOf("online_")==0){
                            PaymentMethod = PaymentMethod.replace("online_", "");
                            var Amount = $("#add-edit-payment-form [name='Amount']").val();
                            var InvoiceNo = $("#add-edit-payment-form [name='InvoiceNo']").val();
                            var custome_notes = $("#add-edit-payment-form [name='Notes']").val();
                            if(PaymentMethod=="Paypal"  ){
                                $('#pyapalform [name=notify_url]').val(baseurl + "/paypal_ipn/{{$Account->AccountID}}-0-"+InvoiceNo)
                                $('#pyapalform [name=amount]').val(Amount)
                                $('#pyapalform').submit();
                                return false;
                            }
                            if(PaymentMethod=="SagePay"){
                                $('#sagepayform [name=p4]').val(Amount)
                                $('#sagepayform').submit();
                                return false;
                            }

                            $("#frm_online_payment [name=Amount]").val(Amount);
                            $("#frm_online_payment [name=InvoiceNo]").val(InvoiceNo);
                            $("#frm_online_payment [name=custome_notes]").val(custome_notes);
                            $("#frm_online_payment").attr("action", baseurl + "/invoice_payment/{{$Account->AccountID}}-0/"+PaymentMethod).submit();

                            return false;
                        }

                        if( typeof PaymentID != 'undefined' && PaymentID != ''){
                            update_new_url = baseurl + '/customer/payments/update/'+PaymentID;
                        }else{
                            update_new_url = baseurl + '/customer/payments/create';
                        }
                        ajax_Add_update(update_new_url);
                    });

                    /*$("#add-edit-payment-form [name='PaymentMethod']").change(function(){
                        var PaymentMethod = $("#add-edit-payment-form [name='PaymentMethod']").val();

                        if(PaymentMethod.indexOf("online_")==0){
                            if(PaymentMethod=="online_Paypal" || PaymentMethod=="online_SagePay" ){
                                $("#add-edit-payment-form [name='Notes']").parents(".col-md-12").eq(0).hide();
                            }else{
                                $("#add-edit-payment-form [name='Notes']").parents(".col-md-12").eq(0).show();
                            }
                        }
                    });*/
                    $("#payment-table-search").submit();

                });

                function ajax_Add_update(fullurl){
                    var data = new FormData($('#add-edit-payment-form')[0]);
                    //show_loading_bar(0);

                    $.ajax({
                        url:fullurl, //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        beforeSend: function(){
                            /*$('.btn.upload').button('loading');
                             show_loading_bar({
                             pct: 50,
                             delay: 5
                             });*/

                        },
                        afterSend: function(){
                            console.log("Afer Send");
                        },

                        success: function(response) {
                            $("#payment-update").button('reset');
                            $(".btn").button('reset');
                            $('#modal-payment').modal('hide');

                            if (response.status == 'success') {
                                $('#add-edit-modal-payment').modal('hide');
                                toastr.success(response.message, "Success", toastr_opts);
                                if( typeof data_table !=  'undefined'){
                                    data_table.fnFilter('', 0);
                                }
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                            $('.btn.upload').button('reset');
                        },
                        data: data,
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                    var $label = $('#add-edit-payment-form [for="PaymentProof"]');
                    $label.parents('.form-group').find('a,span').remove();
                    $label.parents('.form-group').find('div').append('<input id="PaymentProof" name="PaymentProof" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class=\'glyphicon glyphicon-circle-arrow-up\'></i>&nbsp;   Browse" />');
                    var $this = $('#PaymentProof');
                    var label = attrDefault($this, 'label', 'Browse');
                    $this.bootstrapFileInput(label);
                }

                function get_total_grand(){
                    $.ajax({
                        url: baseurl + "/customer/payments/ajax_datagrid_total",
                        type: 'GET',
                        dataType: 'json',
                        data:{
                            "AccountID":$("#payment-table-search select[name='AccountID']").val(),
                            "InvoiceNo" : $("#payment-table-search [name='InvoiceNo']").val(),
                            "type" : $("#payment-table-search select[name='type']").val(),
                            "paymentmethod" : $("#payment-table-search select[name='paymentmethod']").val(),
                            "PaymentDate_StartDate" : $("#payment-table-search input[name='PaymentDate_StartDate']").val(),
                            "PaymentDate_StartTime" : $("#payment-table-search input[name='PaymentDate_StartTime']").val(),
                            "PaymentDate_EndDate" : $("#payment-table-search input[name='PaymentDate_EndDate']").val(),
                            "PaymentDate_EndTime" : $("#payment-table-search input[name='PaymentDate_EndTime']").val(),
                            "bDestroy": true,
                            "bProcessing":true,
                            "bServerSide":true,
                            "sAjaxSource": baseurl + "/customer/payments/ajax_datagrid/type",
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
                                $('#table-4 tbody').append('<tr class="result_row"><td><strong>{{strtoupper(cus_lang("TABLE_TOTAL"))}}</strong></td><td><strong>'+response1.total_grand+'</strong></td><td align="right" colspan="3"></td><td></td><td colspan="2"></td></tr>');
                            }
                        }
                    });
                }

                // Replace Checboxes
                $(".pagination a").click(function (ev) {
                    replaceCheckboxes();
                });


            </script>
            <style>
                .dataTables_filter label{
                    display:none !important;
                }
                .dataTables_wrapper .export-data{
                    right: 30px;
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
                    <h4 class="modal-title">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_VIEW_PAYMENT_TITLE')</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_VIEW_PAYMENT_FIELD_AC_NAME')</label>
                                <div class="col-sm-12" name="AccountName"></div>
                            </div>
                        </div>
                        <!--<div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Currency</label>
                                <div class="col-sm-12" name="Currency"></div>
                            </div>
                        </div>-->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_VIEW_PAYMENT_FIELD_INVOICE')</label>
                                <div class="col-sm-12" name="InvoiceNo"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_VIEW_PAYMENT_FIELD_PAYMENT_DATE')</label>
                                <div class="col-sm-12" name="PaymentDate"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_VIEW_PAYMENT_FIELD_PAYMENT_METHOD')</label>
                                <div class="col-sm-12" name="PaymentMethod"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_VIEW_PAYMENT_FIELD_ACTION')</label>
                                <div class="col-sm-12" name="PaymentType"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_VIEW_PAYMENT_FIELD_AMOUNT')</label>
                                <div class="col-sm-12" name="Amount"></div>
                                <input type="hidden" name="PaymentID" >
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_VIEW_PAYMENT_FIELD_NOTES')</label>
                                <div class="col-sm-12" name="Notes"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="modal fade in" id="payment-status">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="payment-status-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_PAYMENT_NOTES_TITLE')</h4>
                </div>
                <div class="modal-body">
                <div id="text-boxes" class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_PAYMENT_NOTES_FIELD_NOTES')</label>
                            <input type="text" name="Notes" class="form-control"  value="" />
                        </div>
                    </div>
                </div>
                </div>
                <div class="modal-footer">
                     <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')">
                        <i class="entypo-floppy"></i>
                        <input type="hidden" name="URL" value="">
                         @lang('routes.BUTTON_SAVE_CAPTION')
                     </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        @lang('routes.BUTTON_CLOSE_CAPTION')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
    <div class="modal fade" id="add-edit-modal-payment">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-edit-payment-form" method="post">
                    <!--<div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New payment Request</h4>
                    </div>-->
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 hidden">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_EDIT_PAYMENT_FIELD_PAYMENT_DATE')</label>
                                    <input type="text" name="PaymentDate" class="form-control datepicker" data-date-format="yyyy-mm-dd" id="field-5" placeholder="" value="{{date('Y-m-d')}}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_EDIT_PAYMENT_FIELD_PAYMENT_METHOD')</label>
                                    {{ Form::select('PaymentMethod', $method, '', array("class"=>"select2 small", "required")) }}
                                </div>
                            </div>
                            {{--
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_EDIT_PAYMENT_FIELD_ACTION')</label>
                                    {{ Form::select('PaymentType', $action, '', array("class"=>"select2 small")) }}
                                </div>
                            </div>
                            --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_EDIT_PAYMENT_FIELD_AMOUNT')</label>
                                    <input type="text" name="Amount" class="form-control" id="field-5" placeholder="" pattern="[0-9.]*" required>
                                    <input type="hidden" name="PaymentID" >
                                    <input type="hidden" name="CustomerPaymentType" value="Payment In" >
                                    <input type="hidden" name="Currency" value="{{$currency}}" >
                                    <input type="hidden" name="AccountID" value="{{$Account->AccountID}}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_EDIT_PAYMENT_FIELD_INVOICE')</label>
                                    <input type="text" name="InvoiceNo" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group hidden">
                                    <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_EDIT_PAYMENT_FIELD_NOTES')</label>
                                    <textarea name="Notes" class="form-control" id="field-5" placeholder=""></textarea>
                                    <input type="hidden" name="PaymentID" >
                                </div>
                            </div>

                            <div class="col-md-12 hidden">
                                <div class="form-group">
                                    <label for="PaymentProof" class="col-sm-2 control-label">@lang('routes.CUST_PANEL_PAGE_PAYMENTS_MODAL_EDIT_PAYMENT_FIELD_PROOF_UPLOAD_EXTENSION')</label>
                                    <div class="col-sm-6">
                                        <input id="PaymentProof" name="PaymentProof" type="file" class="form-control file2 inline btn btn-primary" data-label="
                                            <i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="payment-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')">
                            <i class="entypo-floppy"></i>
                            @lang('routes.BUTTON_SAVE_CAPTION')
                        </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            @lang('routes.BUTTON_CLOSE_CAPTION')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <form action="" id="frm_online_payment" method="post">
        <input type="hidden" name="Amount" class="form-control">
        <input type="hidden" name="InvoiceNo" class="form-control">
        <input type="hidden" name="custome_notes" class="form-control">
    </form>
@if(is_paypal($Account->CompanyId) )
    {{$paypal_button}}
@endif
@if(is_sagepay($Account->CompanyId))
    {{$sagepay_button}}
@endif
@stop
