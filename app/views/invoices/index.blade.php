@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="invoice_filter" method="get" class="form-horizontal form-groups-bordered validate">
                <div class="form-group">
                    <label for="field-1" class="control-label">Type</label>
                    {{Form::select('InvoiceType',Invoice::$invoice_type,Input::get('InvoiceType'),array("class"=>"select2 small"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Account</label>
                    {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Status</label>
                    {{ Form::select('InvoiceStatus', Invoice::get_invoice_status(), (!empty(Input::get('InvoiceStatus'))?explode(',',Input::get('InvoiceStatus')):[]), array("class"=>"select2","multiple","data-allow-clear"=>"true","data-placeholder"=>"Select Status")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Hide Zero Value</label><br/>
                    <p class="make-switch switch-small">
                        <input id="zerovalueinvoice" name="zerovalueinvoice" type="checkbox" @if($InvoiceHideZeroValue == 1) checked @endif>
                    </p>
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Invoice Number</label>
                    {{ Form::text('InvoiceNumber', '', array("class"=>"form-control")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Issue Date Start</label>
                        {{ Form::text('IssueDateStart', !empty(Input::get('StartDate'))?Input::get('StartDate'):$data['StartDateDefault'], array("class"=>"form-control small-date-input datepicker", "data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}<!-- Time formate Updated by Abubakar -->
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Issue Date End</label>
                        {{ Form::text('IssueDateEnd', !empty(Input::get('EndDate'))?Input::get('EndDate'):$data['IssueDateEndDefault'], array("class"=>"form-control small-date-input datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Currency</label>
                    {{Form::select('CurrencyID',Currency::getCurrencyDropdownIDList(),(!empty(Input::get('CurrencyID'))?Input::get('CurrencyID'):$DefaultCurrencyID),array("class"=>"select2"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Overdue</label><br/>
                    <p class="make-switch switch-small">
                        <input id="Overdue" name="Overdue" type="checkbox">
                    </p>
                </div>
                <div class="form-group">
                    <label class="control-label">Account Tag</label>
                    <input class="form-control tags" name="tag" type="text" >
                </div>
                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop


@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Invoice</strong> </li>
</ol>
<h3>Invoice</h3>
@include('includes.errors')
@include('includes.success')

<div class="tab-content">
  <div class="tab-pane active">
    <div class="row">
      <div class="col-md-12 action-buttons">

          @if(User::checkCategoryPermission('RecurringProfile','View'))
              <div class="input-group-btn">
                  <button href="{{URL::to('/recurringprofiles')}}" onclick="location.href=$(this).attr('href');" class="btn btn-primary tooltip-primary pull-right" data-original-title="Recurring Profile" title="" data-placement="top" data-toggle="tooltip" > Recurring Profiles </button>
              </div>
          @endif

          @if(User::checkCategoryPermission('Invoice','Add'))
              <div class="input-group-btn">
                  <button type="button" class="btn btn-primary dropdown-toggle pull-right" data-toggle="dropdown" aria-expanded="false"> Add Invoice <span class="caret"></span></button>
                  <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                      <li> <a id="add-new-invoice" href="{{URL::to("invoice/create")}}" style="width:100%"> Oneoff </a> </li>
                      <li> <a id="invoice-in" href="javascript:;"> Received</a> </li>
                  </ul>
              </div>
          @endif

          @if(User::checkCategoryPermission('Invoice','Generate'))
              <div class="input-group-btn">
                  <button type="button" class="btn btn-primary dropdown-toggle pull-right" data-toggle="dropdown" aria-expanded="false">Generate Invoice <span class="caret"></span></button>
                  <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                      <li> <a id="generate-new-invoice" href="javascript:;">Automatically</a> </li>
                      <li> <a id="manual_billing" class="manual_billing" href="javascript:;"style="width:100%">Manually </a> </li>

                  </ul>
              </div>
          @endif

        <div class="input-group-btn"> @if( User::checkCategoryPermission('Invoice','Edit,Send,Generate,Email'))
          <button type="button" class="btn btn-primary dropdown-toggle pull-right" data-toggle="dropdown"
                                    aria-expanded="false">Action <span class="caret"></span></button>
          <ul class="dropdown-menu dropdown-menu-left" role="menu"
                                style="background-color: #000; border-color: #000; margin-top:0px;">
            @if(User::checkCategoryPermission('Invoice','Send'))
            <li> <a class="generate_rate create" id="bulk-invoice-send" href="javascript:;"
                                           style="width:100%"> Send Invoice </a> </li>
            @endif

            <li> <a class="quickbookpost create" id="print_invoice" href="javascript:;"> Download Invoice </a> </li>

            @if(User::checkCategoryPermission('Invoice','Edit'))
            <li> <a class="generate_rate create" id="changeSelectedInvoice" href="javascript:;"> Change Status </a> </li>
            @endif
            @if(User::checkCategoryPermission('Invoice','Generate'))
            <li> <a class="generate_rate create" id="RegenSelectedInvoice" href="javascript:;"> Regenerate </a> </li>
            @endif
            @if(is_PayNowInvoice($CompanyID))
            @if(User::checkCategoryPermission('Invoice','Edit'))
            <li> <a class="pay_now create" id="pay_now" href="javascript:;"> Pay Now </a> </li>
            @endif
            @endif
            @if(User::checkCategoryPermission('Invoice','Email'))
            <li> <a class="pay_now create" id="bulk_email" href="javascript:;"> Bulk Email </a> </li>
            @endif
            @if(User::checkCategoryPermission('Invoice','Post') && !empty($check_quickbook))
            <li> <a class="quickbookpost create" id="quickbook_post" href="javascript:;"> QuickBook Journal Post </a> </li>
            <li> <a class="quickbookpost create" id="quickbook_post_invoice" href="javascript:;"> QuickBook Invoice Post </a> </li>
            @endif
            @if(User::checkCategoryPermission('Invoice','Post') && !empty($check_quickbook_desktop))
           <!-- <li> <a class="quickbookpost create" id="quickbook_invoice_export" href="javascript:;"> QuickBook Desktop Invoice Export (IIF)</a> </li>-->
            <li> <a class="quickbookpost create" id="quickbookd_journal_export" href="javascript:;"> QuickBook D Journal Export (IIF)</a> </li>
            @endif
            @if(User::checkCategoryPermission('Invoice','Post') && is_Xero($CompanyID))
            <li> <a class="xeropost create" id="xero_post" href="javascript:;"> Xero Post </a> </li>
            <li> <a class="xerojournal create" id="xero_journal" href="javascript:;"> Xero Journal </a> </li>
            @endif

            <li> <a class="create" id="sage-export" href="javascript:;"> Sage Export </a> </li>
            @if(is_SagePayDirectDebit($CompanyID))
            <li> <a class="sagepost create" id="sage-post" href="javascript:;"> Sage Pay Direct Debit Export </a> </li>
            @endif
          </ul>
          @endif
          <form id="clear-bulk-rate-form">
            <input type="hidden" name="CustomerRateIDs" value="">
          </form>
        </div>

        <!-- /btn-group -->
        <div class="clear"><br>
        </div>
      </div>
    </div>
    <table class="table table-bordered datatable" id="table-4">
      <thead>
        <tr>
          <th width="10%"> <div class="pull-left">
              <input type="checkbox" id="selectall" name="checkbox[]" class=""/>
            </div>
          </th>
          <th width="15%">Account</th>
          <th width="10%">Invoice Number</th>
          <th width="10%">Issue Date</th>
          <th width="13%">Period</th>
          <th width="6%">Grand Total</th>
          <th width="6%">Paid/OS</th>
          <th width="10%">Status</th>
          <th width="5%">Available Credit Notes</th>
          <th width="10%">Due Date</th>
          {{--<th width="10%">Due Days</th>--}}
          <th width="20%">Action</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>
<script type="text/javascript">
        var $searchFilter = {};
        var checked = '';
        var update_new_url;
        var postdata;
	    var editor_options 	  =  		{};
        jQuery(document).ready(function ($) {

            $('#filter-button-toggle').show();

            public_vars.$body = $("body");
            //show_loading_bar(40);
            var invoicestatus = {{$invoice_status_json}};
            var Invoice_Status_Url = "{{ URL::to('invoice/invoice_change_Status')}}";
            var list_fields = ['InvoiceType', 'AccountName ', 'InvoiceNumber', 'IssueDate', 'InvoicePeriod', 'GrandTotal2', 'PendingAmount', 'InvoiceStatus', 'DueDate', 'DueDays', 'InvoiceID', 'Description', 'Attachment', 'AccountID', 'OutstandingAmount', 'ItemInvoice', 'BillingEmail', 'GrandTotal'];
            $searchFilter.InvoiceType = $("#invoice_filter [name='InvoiceType']").val();
            $searchFilter.AccountID = $("#invoice_filter select[name='AccountID']").val();
            $searchFilter.InvoiceStatus = $("#invoice_filter select[name='InvoiceStatus']").val() != null ? $("#invoice_filter select[name='InvoiceStatus']").val() : '';
            $searchFilter.InvoiceNumber = $("#invoice_filter [name='InvoiceNumber']").val();
            $searchFilter.IssueDateStart = $("#invoice_filter [name='IssueDateStart']").val();
            $searchFilter.IssueDateEnd = $("#invoice_filter [name='IssueDateEnd']").val();
            $searchFilter.zerovalueinvoice = $("#invoice_filter [name='zerovalueinvoice']").prop("checked");
            $searchFilter.CurrencyID = $("#invoice_filter [name='CurrencyID']").val();
            $searchFilter.Overdue = $("#invoice_filter [name='Overdue']").prop("checked");
            $searchFilter.tag = $("#invoice_filter [name='tag']").val();

            data_table = $("#table-4").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/invoice/ajax_datagrid/type",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[3, 'desc']],
                "fnServerParams": function (aoData) {
                    aoData.push({"name": "InvoiceType", "value": $searchFilter.InvoiceType},
                            {"name": "AccountID","value": $searchFilter.AccountID},
                            {"name": "InvoiceNumber", "value": $searchFilter.InvoiceNumber},
                            {"name": "InvoiceStatus","value": $searchFilter.InvoiceStatus},
                            {"name":"IssueDateStart","value":$searchFilter.IssueDateStart},
                            {"name":"IssueDateEnd","value":$searchFilter.IssueDateEnd},
                            {"name": "zerovalueinvoice","value": $searchFilter.zerovalueinvoice},
                            {"name": "CurrencyID", "value": $searchFilter.CurrencyID},
                            {"name": "Overdue","value": $searchFilter.Overdue},
                            {"name": "tag","value": $searchFilter.tag});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name": "InvoiceType","value": $searchFilter.InvoiceType},
                            {"name": "AccountID", "value": $searchFilter.AccountID},
                            {"name": "InvoiceNumber","value": $searchFilter.InvoiceNumber},
                            {"name": "InvoiceStatus", "value": $searchFilter.InvoiceStatus},
                            {"name":"IssueDateStart","value":$searchFilter.IssueDateStart},
                            {"name":"IssueDateEnd","value":$searchFilter.IssueDateEnd},
                            {"name": "IssueDate","value": $searchFilter.IssueDate},
                            {"name": "Export", "value": 1},
                            {"name": "zerovalueinvoice","value": $searchFilter.zerovalueinvoice},
                            {"name": "CurrencyID", "value": $searchFilter.CurrencyID},
                            {"name": "Overdue","value": $searchFilter.Overdue},
                            {"name": "tag","value": $searchFilter.tag});
                },
                "aoColumns": [
                    {
                        "bSortable": false,
                        mRender: function (id, type, full) {
                            var action, action = '<div class = "hiddenRowData" >';
                            if (id != '{{Invoice::INVOICE_IN}}') {
                                invoiceType = ' <button class=" btn btn-primary pull-right" title="Invoice Sent"><i class="entypo-left-bold"></i>SNT</a>';
                            } else {
                                invoiceType = ' <button class=" btn btn-primary pull-right" title="Invoice Received"><i class="entypo-right-bold"></i>RCV</a>';
                            }
                            if (full[0] != '{{Invoice::INVOICE_IN}}') {
                                action += '<div class="pull-left"><input type="checkbox" class="checkbox rowcheckbox" value="' + full[10] + '" name="InvoiceID[]"></div>';
                            }
                            action += invoiceType;
                            return action;
                        }

                    },  // 0 AccountName
                    {
                        "bSortable": true,

                        mRender: function (id, type, full) {
                            var output, account_url;
                            output = '<a href="{url}" target="_blank" >{account_name}';
                            if (full[16] == '') {
                                output += '<br> <span class="text-danger"><small>(Email not setup)</small></span>';
                            }
                            output += '</a>';
                            account_url = baseurl + "/accounts/" + full[13] + "/show";
                            output = output.replace("{url}", account_url);
                            output = output.replace("{account_name}", id);
                            return output;
                        }

                    },  // 1 InvoiceNumber
                    {
                        "bSortable": true,

                        mRender: function (id, type, full) {

                            var output, account_url;
                            if (full[0] != '{{Invoice::INVOICE_IN}}') {
                                output = '<a href="{url}" target="_blank"> ' + id + '</a>';
                                account_url = baseurl + "/invoice/" + full[10] + "/invoice_preview";
                                output = output.replace("{url}", account_url);
                                output = output.replace("{account_name}", id);
                            } else {
                                output = id;
                            }
                            return output;
                        }

                    },  // 2 IssueDate
                    {"bSortable": true},  // 3 IssueDate
                    {"bSortable": true},  //4 Invoice period
                    {"bSortable": true},  // 5 GrandTotal
                    {"bSortable": true},  // 6 PAID/OS
                    {
                        "bSortable": true,
                        mRender: function (id, type, full) {
                            return invoicestatus[full[7]];
                        }

                    },  // 7 InvoiceStatus
                    {
                        "bSortable": false,
                        mRender: function (id, type, full) {
                                return full[18];
                        }

                    }, // 18 CreditNotes Amount
                    {
                        "bSortable": true,
                        mRender: function (id, type, full) {
                            var output;
                            if(full[9] != '' && full[9] != null)
                                output = full[8] + '<br/> <span style="color:red">(' + full[9] + ' Days)</span>';
                            else
                                output = full[8];
                            return output;
                        }
                    },  // 8 DueDate and DueDays
                    //{"bSortable": false},  // 9 DueDays
                    {
                        "bSortable": false,
                        mRender: function (id, type, full) {
                            var action, edit_, show_, delete_, view_url, edit_url, download_url, invoice_preview, invoice_log;
                            id = full[10];
                            action = '<div class = "hiddenRowData" >';
                            if (full[0] != '{{Invoice::INVOICE_IN}}') {
                                edit_url = (baseurl + "/invoice/{id}/edit").replace("{id}", id);
                                invoice_preview = (baseurl + "/invoice/{id}/invoice_preview").replace("{id}", id);
                                invoice_log = (baseurl + "/invoice_log/{id}").replace("{id}", id);
                            } else {
                                download_url = baseurl + '/invoice/download_doc_file/' + id;
                            }

                            for (var i = 0; i < list_fields.length; i++) {
                                action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null ? full[i] : '') + '" / >';
                            }
                            action += '</div>';

                            /*Multiple Dropdown*/
                            if (full[0] == '{{Invoice::INVOICE_IN}}') {
                                if ('{{User::checkCategoryPermission('Invoice','Edit')}}') {
                                    action += '<div class="btn-group">';
                                    action += '<a id="dLabel" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="#">Action<span class="caret"></span></a>';
                                    action += '<ul class="dropdown-menu multi-level dropdown-menu-left" role="menu" aria-labelledby="dropdownMenu">';
                                    action += ' <li><a class="edit-invoice-in icon-left"><i class="entypo-pencil"></i>Edit </a></li>';
                                    //action += ' <li><a class="view-invoice-in icon-left"><i class="entypo-pencil"></i>Print </a></li>';
                                    action += '</ul>';
                                    action += '</div>';
                                    if (full[12]) {
                                        action += ' <a class="btn btn-success btn-sm btn-icon icon-left download" href="' + (baseurl + "/invoice/download_atatchment/{id}").replace("{id}", id) + '"><i class="entypo-down"></i>Download</a>'
                                    }
                                }
                            } else {
                                action += '<div class="btn-group">';
                                action += '<a id="dLabel" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="#">Action<span class="caret"></span></a>';
                                action += '<ul class="dropdown-menu multi-level dropdown-menu-left" role="menu" aria-labelledby="dropdownMenu">';

                                if (full[15] == '{{Invoice::ITEM_INVOICE}}') {
                                    if ('{{User::checkCategoryPermission('Invoice','Edit')}}') {
                                        action += ' <li><a class="icon-left"  href="' + (baseurl + "/invoice/{id}/edit").replace("{id}", id) + '"><i class="entypo-pencil"></i>Edit </a></li>';
                                    }
                                }
                                if (edit_url) {
                                    //action += ' <a href="' + edit_url +'" class="edit-invoice btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>'
                                    action += '<li><a class="icon-left"  target="_blank" href="' + invoice_preview + '"><i class="fa fa-eye"></i>View </a></li>';
                                }
                                if (invoice_log) {
                                    action += '<li><a href="' + invoice_log + '" class="icon-left" target="_blank"><i class="entypo-list"></i>Log </a></li>';
                                }
                                if (full[0] != '{{Invoice::INVOICE_IN}}') {
                                    if ('{{User::checkCategoryPermission('Invoice','Send')}}') {
                                        action += '<li><a data-id="' + id + '" class="send-invoice icon-left"><i class="entypo-mail"></i>Send </a></li>';
                                    }
                                }
                                if (full[0] != '{{Invoice::INVOICE_IN}}' && (full[7] != '{{Invoice::PAID}}')) {
                                    if ('{{User::checkCategoryPermission('Invoice','Edit')}}') {
                                        action += '<li><a data-id="' + id + '" class="add-new-payment icon-left"><i class="entypo-credit-card"></i>Enter Payment</a></li>';
                                    }
                                }
                                action += '</ul>';
                                action += '</div>';
                            }

                            /*Multiple Dropdown*/
                            if (full[0] != '{{Invoice::INVOICE_IN}}') {
                                if ('{{User::checkCategoryPermission('Invoice','Edit')}}') {
                                    action += ' <div class="btn-group"><button href="#" class="btn generate btn-success btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Change Status <span class="caret"></span></button>'
                                    action += '<ul class="dropdown-menu dropdown-green" role="menu">';
                                    $.each(invoicestatus, function (index, value) {
                                        if (index != '') {
                                            action += '<li><a data-invoicestatus="' + index + '" data-invoiceid="' + id + '" href="' + Invoice_Status_Url + '" class="changestatus" >' + value + '</a></li>';
                                        }

                                    });
                                    action += '</ul>' +
                                    '</div>';
                                }
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
                            "sUrl": baseurl + "/invoice/ajax_datagrid/xlsx", //baseurl + "/generate_xls.php",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/invoice/ajax_datagrid/csv", //baseurl + "/generate_xls.php",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function () {
                    get_total_grand(); //get result total
                    $('#table-4 tbody tr').each(function (i, el) {
                        if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                            if (checked != '') {
                                $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                $(this).addClass('selected');
                                $('#selectallbutton').prop("checked", true);
                            } else {
                                $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                ;
                                $(this).removeClass('selected');
                            }
                        }
                    });
                    //After Delete done
                    FnDeleteInvoiceTemplateSuccess = function (response) {

                        if (response.status == 'success') {
                            $("#Note" + response.NoteID).parent().parent().fadeOut('fast');
                            ShowToastr("success", response.message);
                            data_table.fnFilter('', 0);
                        } else {
                            ShowToastr("error", response.message);
                        }
                    }
                    //onDelete Click
                    FnDeleteInvoiceTemplate = function (e) {
                        result = confirm("Are you Sure?");
                        if (result) {
                            var id = $(this).attr("data-id");
                            showAjaxScript(baseurl + "/invoice/" + id + "/delete", "", FnDeleteInvoiceTemplateSuccess);
                        }
                        return false;
                    }
                    $(".delete-invoice").click(FnDeleteInvoiceTemplate); // Delete Note
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    $('#selectallbutton').click(function (ev) {
                        if ($(this).is(':checked')) {
                            checked = 'checked=checked disabled';
                            $("#selectall").prop("checked", true).prop('disabled', true);
                            if (!$('#changeSelectedInvoice').hasClass('hidden')) {
                                $('#table-4 tbody tr').each(function (i, el) {
                                    if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {

                                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                        $(this).addClass('selected');
                                    }
                                });
                            }
                        } else {
                            checked = '';
                            $("#selectall").prop("checked", false).prop('disabled', false);
                            if (!$('#changeSelectedInvoice').hasClass('hidden')) {
                                $('#table-4 tbody tr').each(function (i, el) {
                                    if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {

                                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                        $(this).removeClass('selected');
                                    }
                                });
                            }
                        }
                    });
                }

            });

            $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');

            $("#invoice_filter").submit(function (e) {
                e.preventDefault();
                $searchFilter.InvoiceType = $("#invoice_filter [name='InvoiceType']").val();
                $searchFilter.AccountID = $("#invoice_filter select[name='AccountID']").val();
                $searchFilter.InvoiceNumber = $("#invoice_filter [name='InvoiceNumber']").val();
                $searchFilter.InvoiceStatus = $("#invoice_filter select[name='InvoiceStatus']").val() != null ? $("#invoice_filter select[name='InvoiceStatus']").val() : '';
                $searchFilter.IssueDateStart = $("#invoice_filter [name='IssueDateStart']").val();
                $searchFilter.IssueDateEnd = $("#invoice_filter [name='IssueDateEnd']").val();
                $searchFilter.zerovalueinvoice = $("#invoice_filter [name='zerovalueinvoice']").prop("checked");
                $searchFilter.CurrencyID = $("#invoice_filter [name='CurrencyID']").val();
                $searchFilter.Overdue = $("#invoice_filter [name='Overdue']").prop("checked");
                $searchFilter.tag = $("#invoice_filter [name='tag']").val();
                data_table.fnFilter('', 0);
                return false;
            });


            function get_total_grand() {
                $.ajax({
                    url: baseurl + "/invoice/ajax_datagrid_total",
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        "InvoiceType": $("#invoice_filter [name='InvoiceType']").val(),
                        "AccountID": $("#invoice_filter select[name='AccountID']").val(),
                        "InvoiceNumber": $("#invoice_filter [name='InvoiceNumber']").val(),
                        "InvoiceStatus": $("#invoice_filter select[name='InvoiceStatus']").val(),
                        "IssueDateStart":$("#invoice_filter [name='IssueDateStart']").val(),
                        "IssueDateEnd":$("#invoice_filter [name='IssueDateEnd']").val(),
                        "zerovalueinvoice": $("#invoice_filter [name='zerovalueinvoice']").prop("checked"),
                        "CurrencyID": $("#invoice_filter [name='CurrencyID']").val(),
                        "Overdue": $("#invoice_filter [name='Overdue']").prop("checked"),
                        "tag": $("#invoice_filter [name='tag']").val(),
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/invoice/ajax_datagrid/type",
                        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[3, 'desc']]
                    },
                    success: function (response1) {
                        //console.log("sum of result"+response1);
                        if (response1.total_grand != null) {
                            $('.result_row').remove();
                            $('.result_row').hide();
                            $('#table-4 tbody').append('<tr class="result_row"><td><strong>Total</strong></td><td align="right" colspan="4"></td><td><strong>' + response1.total_grand + '</strong></td><td><strong>' + response1.os_pp + '</strong></td><td colspan="2"></td></tr>');
                        }
                    }
                });
            }


            $('#invoice-in').click(function (ev) {
                ev.preventDefault();
                $('#add-invoice_in_template-form').trigger("reset");
                $('#modal-invoice-in h4').html('Add Invoice');
                $("#add-invoice_in_template-form [name='AccountID']").select2().select2('val', '');
                $("#add-invoice_in_template-form [name='InvoiceID']").val('');
                $('.file-input-name').text('');
                $('#modal-invoice-in').find('.download').html('');
                $('#modal-invoice-in').modal('show');
                reset_dispute();
            });

            $("#add-invoice_in_template-form [name='AccountID']").change(function () {
                $("#add-invoice_in_template-form [name='AccountName']").val($("#add-invoice_in_template-form [name='AccountID'] option:selected").text());

                var AccountID = $("#add-invoice_in_template-form [name='AccountID'] option:selected").val();
                if (AccountID > 0) {
                    var url = baseurl + '/payments/get_currency_invoice_numbers/' + AccountID;
                    $.get(url, function (response) {

                        if (typeof response.status != 'undefined' && response.status == 'success') {
                            $("#currency").text('(' + response.Currency_Symbol + ')');
                        }

                    });

                }


            });

            $(".btn.ignore").click(function (e) {

                reset_dispute();

            });

            $(".btn.reconcile").click(function (e) {


                e.preventDefault();
                var curnt_obj = $(this);
                curnt_obj.button('loading');


                var formData = $('#add-invoice_in_template-form').serializeArray();

                reconcile_url = baseurl + '/invoice/reconcile';
                ajax_json(reconcile_url, formData, function (response) {

                    $(".btn").button('reset');

                    if (response.status == 'success') {

                        console.log(response);
                        set_dispute(response);
                    }

                });


            });

            function set_dispute(response) {

                if (typeof response.DisputeID != 'undefined') {

                    $('#add-invoice_in_template-form').find("input[name=DisputeID]").val(response.DisputeID);

                } else {

                    $('#add-invoice_in_template-form').find("input[name=DisputeID]").val("");

                }

                if (typeof response.DisputeTotal == 'undefined') {

                    $(".reconcile_table").addClass("hidden");
                    $(".btn.ignore").addClass("hidden");


                } else {

                    $(".reconcile_table").removeClass("hidden");
                    $(".btn.ignore").removeClass("hidden");
                }

                if (typeof response.DisputeAmount != 'undefined') {

                    $('#add-invoice_in_template-form').find("input[name=DisputeAmount]").val(response.DisputeAmount);

                } else {

                    $('#add-invoice_in_template-form').find("input[name=DisputeAmount]").val(response.DisputeDifference);
                }


                $('#add-invoice_in_template-form').find("table .DisputeTotal").text(response.DisputeTotal);
                $('#add-invoice_in_template-form').find("table .DisputeDifference").text(response.DisputeDifference);
                $('#add-invoice_in_template-form').find("table .DisputeDifferencePer").text(response.DisputeDifferencePer);


                $('#add-invoice_in_template-form').find("table .DisputeMinutes").text(response.DisputeMinutes);
                $('#add-invoice_in_template-form').find("table .MinutesDifference").text(response.MinutesDifference);
                $('#add-invoice_in_template-form').find("table .MinutesDifferencePer").text(response.MinutesDifferencePer);


                /*$('#add-invoice_in_template-form').find("input[name=DisputeTotal]").val(response.DisputeTotal);
                 $('#add-invoice_in_template-form').find("input[name=DisputeDifference]").val(response.DisputeDifference);
                 $('#add-invoice_in_template-form').find("input[name=DisputeDifferencePer]").val(response.DisputeDifferencePer);
                 $('#add-invoice_in_template-form').find("input[name=DisputeMinutes]").val(response.DisputeMinutes);
                 $('#add-invoice_in_template-form').find("input[name=MinutesDifference]").val(response.MinutesDifference);
                 $('#add-invoice_in_template-form').find("input[name=MinutesDifferencePer]").val(response.MinutesDifferencePer);*/

            }

            function reset_dispute() {

                $('#add-invoice_in_template-form').find("table .DisputeTotal").text("");
                $('#add-invoice_in_template-form').find("table .DisputeDifference").text("");
                $('#add-invoice_in_template-form').find("table .DisputeDifferencePer").text("");


                $('#add-invoice_in_template-form').find("table .DisputeMinutes").text("");
                $('#add-invoice_in_template-form').find("table .MinutesDifference").text("");
                $('#add-invoice_in_template-form').find("table .MinutesDifferencePer").text("");


                $('#add-invoice_in_template-form').find("input[name=DisputeAmount]").val("")

                /*$('#add-invoice_in_template-form').find("input[name=DisputeTotal]").val("");
                 $('#add-invoice_in_template-form').find("input[name=DisputeDifference]").val("");
                 $('#add-invoice_in_template-form').find("input[name=DisputeDifferencePer]").val("");

                 $('#add-invoice_in_template-form').find("input[name=DisputeMinutes]").val("");
                 $('#add-invoice_in_template-form').find("input[name=MinutesDifference]").val("");
                 $('#add-invoice_in_template-form').find("input[name=MinutesDifferencePer]").val("");*/

                $(".reconcile_table").addClass("hidden");
                $(".btn.ignore").addClass("hidden");

            }

            $("#add-invoice_in_template-form").submit(function (e) {
                e.preventDefault();
                var formData = new FormData($('#add-invoice_in_template-form')[0]);
                var InvoiceID = $("#add-invoice_in_template-form [name='InvoiceID']").val();
                if (typeof InvoiceID != 'undefined' && InvoiceID != '') {
                    update_new_url = baseurl + '/invoice/update_invoice_in/' + InvoiceID;
                } else {
                    update_new_url = baseurl + '/invoice/add_invoice_in';
                }
                $.ajax({
                    url: update_new_url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#modal-invoice-in').modal('hide');
                            data_table.fnFilter('', 0);
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $("#saveinvoice").button('reset');
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });
            $('table tbody').on('click', '.edit-invoice-in', function (ev) {
                $('#add-invoice_in_template-form').trigger("reset");
                $('.file-input-name').text('');
                $('#modal-invoice-in h4').html('Edit Invoice');
                //var cur_obj = $(this).prev("div.hiddenRowData");
                var cur_obj = $(this).parent().parent().parent().parent().find("div.hiddenRowData");
                var downloadbutton = $(this).parent().parent().parent().parent().find("a.download").clone();
                downloadbutton.removeClass('');
                $('#modal-invoice-in').find('.download').html('');
                InvoiceID = cur_obj.find("input[name='InvoiceID']").val();
                $.ajax({
                    url: baseurl + '/invoice/getInvoiceDetail',
                    data: 'InvoiceID=' + InvoiceID,
                    dataType: 'json',
                    success: function (response) {
                        $("#add-invoice_in_template-form [name='StartDate']").val(response.StartDate);
                        $("#add-invoice_in_template-form [name='StartTime']").val(response.StartTime);
                        $("#add-invoice_in_template-form [name='EndDate']").val(response.EndDate);
                        $("#add-invoice_in_template-form [name='EndTime']").val(response.EndTime);
                        $("#add-invoice_in_template-form [name='Description']").val(response.Description);
                        $("#add-invoice_in_template-form [name='InvoiceDetailID']").val(response.InvoiceDetailID);
                        $("#add-invoice_in_template-form [name='TotalMinutes']").val(response.TotalMinutes);
                        $('#modal-invoice-in').find('.download').append(downloadbutton);
                        set_dispute(response);

                    },
                    type: 'POST'
                });
                for (var i = 0; i < list_fields.length; i++) {
                    if (list_fields[i] != 'Attachment') {
                        $("#add-invoice_in_template-form [name='" + list_fields[i] + "']").val(cur_obj.find("input[name='" + list_fields[i] + "']").val());
                        //$("#add-invoice_in_template-form .file-input-name").text(cur_obj.find("input[name='Attachment']").val());
                        if (list_fields[i] == 'AccountID') {
                            $("#add-invoice_in_template-form [name='" + list_fields[i] + "']").select2().select2('val', cur_obj.find("input[name='" + list_fields[i] + "']").val());
                        }
                    }
                }
                $('#modal-invoice-in').modal('show');
            });
            $('table tbody').on('click', '.view-invoice-in', function (ev) {
                //var cur_obj = $(this).prev().prev("div.hiddenRowData");
                var cur_obj = $(this).parent().parent().parent().parent().find("div.hiddenRowData");
                for (var i = 0; i < list_fields.length; i++) {
                    $("#modal-invoice-in-view").find("[data-id='" + list_fields[i] + "']").html('');
                    if (list_fields[i] == 'Attachment') {
                        if (cur_obj.find("input[name='" + list_fields[i] + "']").val() != '') {
                            var down_html = ' <a href="' + baseurl + '/invoice/download_doc_file/' + cur_obj.find("input[name='InvoiceID']").val() + '" class="edit-invoice btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>'
                            $("#modal-invoice-in-view").find("[data-id='" + list_fields[i] + "']").html(down_html);
                        }
                    } else {
                        $("#modal-invoice-in-view").find("[data-id='" + list_fields[i] + "']").html(cur_obj.find("input[name='" + list_fields[i] + "']").val());
                    }
                }
                $('#modal-invoice-in-view').modal('show');
            });


            // Replace Checboxes
            $(".pagination a").click(function (ev) {
                replaceCheckboxes();
            });

            $("#selectall").click(function (ev) {
                var is_checked = $(this).is(':checked');
                $('#table-4 tbody tr').each(function (i, el) {
                    if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
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
            $('#table-4 tbody').on('click', 'tr', function () {
                if (checked == '') {
                    if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                        $(this).toggleClass('selected');
                        if ($(this).hasClass('selected')) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                        }
                    }
                }
            });
            $("#changeSelectedInvoice").click(function (ev) {
                var criteria = '';
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                }
                var InvoiceIDs = [];
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    //console.log($(this).val());
                    InvoiceID = $(this).val();
                    if (typeof InvoiceID != 'undefined' && InvoiceID != null && InvoiceID != 'null') {
                        InvoiceIDs[i++] = InvoiceID;
                    }
                    if (InvoiceIDs.length) {
                        $("#selected-invoice-status-form").find("input[name='InvoiceIDs']").val(InvoiceIDs.join(","));
                        $("#selected-invoice-status-form").find("input[name='criteria']").val(criteria);
                        $('#selected-invoice-status').modal('show');
                        $("#selected-invoice-status-form [name='InvoiceStatus']").select2().select2('val', '');
                        $("#selected-invoice-status-form [name='CancelReason']").val('');
                        $('#statuscancel').hide();
                    }
                });
            });
            $("#RegenSelectedInvoice").click(function (ev) {
                var criteria = '';
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                }
                var InvoiceIDs = [];
                var i = 0;
                if (!confirm('Are you sure you want to regenerate selected invoices?')) {
                    return;
                }
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    InvoiceID = $(this).val();
                    if (typeof InvoiceID != 'undefined' && InvoiceID != null && InvoiceID != 'null') {
                        InvoiceIDs[i++] = InvoiceID;
                    }
                });
                if (InvoiceIDs.length) {
                    submit_ajax(baseurl + '/invoice/invoice_regen', 'InvoiceIDs=' + InvoiceIDs.join(",") + '&criteria=' + criteria)
                }
            });

            $("#pay_now").click(function (ev) {
                ev.preventDefault();
                var InvoiceIDs = [];
                var accoutid;
                var sec_accountid;
                var other_account = false;
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    InvoiceID = $(this).val();
                    InvoiceIDs[i++] = InvoiceID;
                    var tr_obj = $(this).parent().parent().parent().parent();
                    sec_accountid = tr_obj.children().find('[name=AccountID]').val();
                    if (!accoutid) {
                        accoutid = tr_obj.children().find('[name=AccountID]').val();
                    }
                    if (accoutid != sec_accountid) {
                        alert('Please select invoices from single account');
                        other_account = true
                        return;
                    }

                });
                if (InvoiceIDs.length && other_account == false) {
                    if (!confirm('Are you sure you want to pay selected invoices?')) {
                        return;
                    }
                    //console.log(InvoiceIDs);
                    $('#add-credit-card-form').find("[name=AccountID]").val(accoutid);
                    $('#add-bankaccount-form').find("[name=AccountID]").val(accoutid);

                    paynow_url = '/customer/PaymentMethodProfiles/paynow/' + accoutid;
                    //paynow_url = '/paymentprofile/' + accoutid;
                    showAjaxModal(paynow_url, 'pay_now_modal');
                    $('#pay_now_modal').modal('show');


                }
                return false;
            });
            $("#selected-invoice-status-form").submit(function (e) {
                e.preventDefault();
                var InvoiceStatus = $(this).find("select[name='InvoiceStatus']").val();

                if (InvoiceStatus != '') {
                    if (InvoiceStatus == '{{Invoice::CANCEL}}') {
                        var CancelReason = $(this).find("input[name='CancelReason']").val().trim();
                        if (CancelReason != '') {
                            formData = $("#selected-invoice-status-form").serialize();
                            update_new_url = baseurl + '/invoice/invoice_change_Status';
                            submit_ajax(update_new_url, formData)
                        }
                        else {
                            toastr.error("Please Enter Cancel Reason", "Error", toastr_opts);
                            $(this).find(".cancelbutton").button("reset");
                            return false;
                        }

                    } else {
                        formData = $("#selected-invoice-status-form").serialize();
                        update_new_url = baseurl + '/invoice/invoice_change_Status';
                        submit_ajax(update_new_url, formData)
                    }
                } else {
                    toastr.error("Please Select Invoices Status", "Error", toastr_opts);
                    $(this).find(".cancelbutton").button("reset");
                    return false;
                }

            });
            $('#selected-invoice-status-form [name="InvoiceStatus"]').change(function (e) {
                e.preventDefault();
                $('#statuscancel').hide();
                var status = $(this).val();
                if (status == '{{Invoice::CANCEL}}') {
                    $('#statuscancel').show();
                }
            });

            $("#invoice-status-cancel-form").submit(function (e) {
                e.preventDefault();
                if ($(this).find("input[name='CancelReason']").val().trim() != '') {
                    submit_ajax(Invoice_Status_Url, $(this).serialize())
                }
            });
            $('table tbody').on('click', '.changestatus', function (e) {
                e.preventDefault();
                var self = $(this);
                var text = self.text();
                if (!confirm('Are you sure you want to change the invoice status to ' + text + '?')) {
                    return;
                }
                if (self.attr('data-invoicestatus') == '{{Invoice::CANCEL}}') {
                    $("#invoice-status-cancel-form").find("input[name='CancelReason']").val('');
                    $("#invoice-status-cancel-form").find("input[name='InvoiceIDs']").val($(this).attr('data-invoiceid'));
                    $("#invoice-status-cancel-form").find("input[name='InvoiceStatus']").val($(this).attr('data-invoicestatus'));
                    $("#invoice-status-cancel").modal('show', {backdrop: 'static'});
                    return;
                }
                $(this).button('loading');
                $.ajax({
                    url: $(this).attr("href"),
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $(this).button('reset');
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            data_table.fnFilter('', 0);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },
                    data: 'InvoiceStatus=' + $(this).attr('data-invoicestatus') + '&InvoiceIDs=' + $(this).attr('data-invoiceid')

                });
                return false;
            });

            $('table tbody').on('click', '.send-invoice', function (ev) {
                //var cur_obj = $(this).prevAll("div.hiddenRowData");
                var cur_obj = $(this).parent().parent().parent().parent().find("div.hiddenRowData");
                InvoiceID = cur_obj.find("[name=InvoiceID]").val();
                send_url = ("/invoice/{id}/invoice_email").replace("{id}", InvoiceID);
                showAjaxModal(send_url, 'send-modal-invoice');
				
                $('#send-modal-invoice').modal('show');		
				emailFileList = [];	
            });

            $("#send-invoice-form").submit(function (e) {
                e.preventDefault();
                var post_data = $(this).serialize();
                var InvoiceID = $(this).find("[name=InvoiceID]").val();
                var _url = baseurl + '/invoice/' + InvoiceID + '/send';
                submit_ajax(_url, post_data);
				
            });
            $("#print_invoice").click(function (ev) {
                var criteria = '';
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                }
                var InvoiceIDs = [];
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    //console.log($(this).val());
                    InvoiceID = $(this).val();
                    if (typeof InvoiceID != 'undefined' && InvoiceID != null && InvoiceID != 'null') {
                        InvoiceIDs[i++] = InvoiceID;
                    }
                });
                console.log(InvoiceIDs);

                if (InvoiceIDs.length) {
                    if (!confirm('Are you sure you want to download selected invoices?')) {
                        return;
                    }
                    $.ajax({
                        url: baseurl + '/invoice/bulk_print_invoice',
                        data: 'InvoiceIDs=' + InvoiceIDs + '&criteria=' + criteria,
                        error: function () {
                            toastr.error("error", "Error", toastr_opts);
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status == 'success') {
                                if(response.filePath!=""){
                                    document.location =baseurl + "/download_file?file="+response.filePath;
                                }else if(response.invoiceId){
                                    document.location =baseurl + "/invoice/download_invoice/"+response.invoiceId;
                                }else{
                                    toastr.error("Something Worng Please try again.", "Error", toastr_opts);
                                }

                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        type: 'POST'
                    });
                }
                else
                {
                    toastr.error("Please Select One", "Error", toastr_opts);
                }

            });
            $("#bulk-invoice-send").click(function (ev) {
                var criteria = '';
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                }
                var InvoiceIDs = [];
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    //console.log($(this).val());
                    InvoiceID = $(this).val();
                    if (typeof InvoiceID != 'undefined' && InvoiceID != null && InvoiceID != 'null') {
                        InvoiceIDs[i++] = InvoiceID;
                    }
                });
                console.log(InvoiceIDs);

                if (InvoiceIDs.length) {
                    if (!confirm('Are you sure you want to send selected invoices?')) {
                        return;
                    }
                    $.ajax({
                        url: baseurl + '/invoice/bulk_send_invoice_mail',
                        data: 'InvoiceIDs=' + InvoiceIDs + '&criteria=' + criteria,
                        error: function () {
                            toastr.error("error", "Error", toastr_opts);
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        type: 'POST'
                    });

                }

            });
            $('#add-credit-card-form').submit(function (e) {
                e.preventDefault();
                update_new_url = baseurl + '/paymentprofile/create';
                submit_ajax(update_new_url, $('#add-credit-card-form').serialize())
            });

            $('#add-bankaccount-form').submit(function (e) {
                e.preventDefault();
                update_new_url = baseurl + '/paymentprofile/create';
                submit_ajax(update_new_url, $('#add-bankaccount-form').serialize())
            });

            $('#generate-new-invoice').click(function (e) {
                e.preventDefault();
                update_new_url = "{{URL::to("invoice/generate")}}";
                submit_ajax(update_new_url, $('#add-credit-card-form').serialize(), 1)
            });
            $('table tbody').on('click', '.add-new-payment', function (ev) {
                ev.preventDefault();
                $('#add-edit-payment-form').trigger("reset");

                $("#add-edit-payment-form [name='AccountID']").val('').trigger("change");
                $("#add-edit-payment-form [name='PaymentMethod']").val('').trigger("change");
                $("#add-edit-payment-form [name='PaymentType']").val('Payment In').trigger("change");
                $("#add-edit-payment-form [name='PaymentID']").val('');


                var cur_obj = $(this).parent().parent().parent().parent().find("div.hiddenRowData");
                //alert(cur_obj.find("input[name='AccountID']").val());exit;
                //console.log(cur_obj.find("input[name='AccountID']").val());
                if (cur_obj.find("input[name='InvoiceStatus']").val() == '{{Invoice::PARTIALLY_PAID}}') {
                    $("#add-edit-payment-form [name='Amount']").val(cur_obj.find("input[name='OutstandingAmount']").val());
                } else {
                    $("#add-edit-payment-form [name='Amount']").val(cur_obj.find("input[name='GrandTotal']").val());
                }
                $("#add-edit-payment-form [name='InvoiceNo']").val(cur_obj.find("input[name='InvoiceNumber']").val());
                for (var i = 0; i < list_fields.length; i++) {
                    if (list_fields[i] != 'Attachment') {
                        $("#add-edit-payment-form [name='" + list_fields[i] + "']").val(cur_obj.find("input[name='" + list_fields[i] + "']").val());
                        if (list_fields[i] == 'AccountID') {
                            $("#add-edit-payment-form [name='" + list_fields[i] + "']").select2().select2('val', cur_obj.find("input[name='" + list_fields[i] + "']").val());
                            $("#add-edit-payment-form [name='AccountID']").trigger('change');
                        }
                    }
                }
                $('#add-edit-modal-payment h4').html('Add New Payment');
                $('#add-edit-modal-payment').modal('show');


            });
            $("#add-edit-payment-form").submit(function (e) {
                e.preventDefault();
                update_new_url = baseurl + '/payments/create';
                submit_ajax(update_new_url, $("#add-edit-payment-form").serialize());
            });
            $("#add-edit-payment-form [name='AccountID']").change(function () {
                $("#add-edit-payment-form [name='AccountName']").val($("#add-edit-payment-form [name='AccountID'] option:selected").text());

                /*var url = baseurl + '/payments/getcurrency/'+$("#add-edit-payment-form [name='AccountID'] option:selected").val();
                 if($("#add-edit-payment-form [name='AccountID'] option:selected").val()>0) {
                 $.get(url, function (Currency) {
                 $("#AccountID_currency").text('(' + Currency + ')');
                 });
                 }*/

                var AccountID = $("#add-edit-payment-form [name='AccountID'] option:selected").val()
                if (AccountID > 0) {
                    var url = baseurl + '/payments/get_currency_invoice_numbers/' + AccountID;
                    $.get(url, function (response) {
                        if (typeof response.status != 'undefined' && response.status == 'success') {
                            $("#AccountID_currency").text('(' + response.Currency_Symbol + ')');
                        }
                    });

                }

            });
            $("#bulk_email").click(function () {
				document.getElementById('BulkMail-form').reset();
				$("#modal-BulkMail").find('.file-input-name').html("");
                $("#BulkMail-form [name='email_template']").val('').trigger("change");
                $("#BulkMail-form [name='template_option']").val('').trigger("change");
                $("#BulkMail-form").trigger('reset')
                $("#modal-BulkMail").modal('show');
            });
			$('#send-modal-invoice').on('shown.bs.modal', function (event) {
				//setTimeout(function(){ $("#send-modal-invoice").find(".select22").select2(); }, 250);
			});
            $('#modal-BulkMail').on('shown.bs.modal', function (event) {
                var modal = $(this);

                show_summernote(modal.find(".message"),editor_options);

            });

            $('#modal-BulkMail').on('hidden.bs.modal', function (event) {
                var modal = $(this);


            });
            $("#BulkMail-form [name=email_template]").change(function (e) {
                var templateID = $(this).val();
                if (templateID > 0) {
                    var url = baseurl + '/accounts/' + templateID + '/ajax_template';
                    $.get(url, function (data, status) {
                        if (Status = "success") {
                            var modal = $("#modal-BulkMail");

                            modal.find('.message').show();

                            var EmailTemplate = data['EmailTemplate'];
                            modal.find('[name="subject"]').val(EmailTemplate.Subject);
                            modal.find('.message').val(EmailTemplate.TemplateBody);

                            show_summernote(modal.find(".message"),editor_options);
                        } else {
                            toastr.error(status, "Error", toastr_opts);
                        }
                    });
                }
            });
            $("#BulkMail-form [name=template_option]").change(function (e) {
                if ($(this).val() == 1) {
                    $('#templatename').removeClass("hidden");
                } else {
                    $('#templatename').addClass("hidden");
                }
            });
            $("#BulkMail-form").submit(function (e) {
                e.preventDefault();
                var SelectedIDs = [];
                var i = 0;
                if ($("#BulkMail-form").find('[name="test"]').val() == 0) {
                    if (!$('#selectallbutton').is(':checked')) {
                        $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                            SelectedID = $(this).val();
                            SelectedIDs[i++] = SelectedID;
                        });
                    }
                    var criteria = JSON.stringify($searchFilter);
                    $("#BulkMail-form").find("input[name='criteria']").val(criteria);
                    $("#BulkMail-form").find("input[name='SelectedIDs']").val(SelectedIDs.join(","));

                    if ($("#BulkMail-form").find("input[name='SelectedIDs']").val() != "" && confirm("Are you sure to send mail to selected Accounts") != true) {
                        $(".btn").button('reset');
                        $(".savetest").button('reset');
                        $('#modal-BulkMail').modal('hide');
                        return false;
                    }
                }

                var formData = new FormData($('#BulkMail-form')[0]);
                var url = baseurl + "/accounts/bulk_mail"
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            $(".save").button('reset');
                            $(".savetest").button('reset');
                            $('#modal-BulkMail').modal('hide');
                            data_table.fnFilter('', 0);
                            reloadJobsDrodown(0);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                            $(".save").button('reset');
                            $(".savetest").button('reset');
                        }
                        $('.file-input-name').text('');
                        $('#attachment').val('');
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            $("#test").click(function (e) {
                e.preventDefault();
                $("#BulkMail-form").find('[name="test"]').val(1);
                $('#TestMail-form').find('[name="EmailAddress"]').val('');
                $('#modal-TestMail').modal({show: true});
            });
            $('.alerta').click(function (e) {
                e.preventDefault();
                var email = $('#TestMail-form').find('[name="EmailAddress"]').val();
                var accontID = $('.hiddenRowData').find('.rowcheckbox').val();
                if (email == '') {
                    toastr.error('Email field should not empty.', "Error", toastr_opts);
                    $(".alerta").button('reset');
                    return false;
                } else if (accontID == '') {
                    toastr.error('Please select sample invoice', "Error", toastr_opts);
                    $(".alerta").button('reset');
                    return false;
                }
                $('#BulkMail-form').find('[name="testEmail"]').val(email);
                $('#BulkMail-form').find('[name="SelectedIDs"]').val(accontID);
                $("#BulkMail-form").submit();
                $('#modal-TestMail').modal('hide');

            });

            $('#modal-TestMail').on('hidden.bs.modal', function (event) {
                var modal = $(this);
                modal.find('[name="test"]').val(0);
            });
            $('#BulkMail-form [name="email_template_privacy"]').change(function (e) {
                var privacyID = $(this).val();
                var url = baseurl + '/invoice/' + privacyID + '/ajax_getEmailTemplate';
                $.get(url, function (data, status) {
                    if (Status = "success") {
                        var modal = $("#modal-BulkMail");
                        var el = modal.find('#BulkMail-form [name=email_template]');
                        rebuildSelect2(el,data,'');
                    } else {
                        toastr.error(status, "Error", toastr_opts);
                    }
                });
            });

            $('#sage-export').click(function (e) {
                var MarkPaid = '0';
                if (confirm('Do you want to change the status of selected invoices to Paid?')) {
                    MarkPaid = '1';
                }
                var criteria = '';
                var InvoiceIDs = [];
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    //console.log($(this).val());
                    InvoiceID = $(this).val();
                    if (typeof InvoiceID != 'undefined' && InvoiceID != null && InvoiceID != 'null') {
                        InvoiceIDs[i++] = InvoiceID;
                    }
                });
                if (InvoiceIDs == '') {
                    criteria = JSON.stringify($searchFilter);
                }
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                    InvoiceIDs = '';
                }
                var url = baseurl + '/invoice/sageExport';
                var data = '?InvoiceIDs=' + InvoiceIDs + '&criteria=' + criteria + '&MarkPaid=' + MarkPaid;

                window.location.href = url + data;
                setTimeout(function () {
                    data_table.fnFilter('', 0);
                }, 1000);
            });

            $(document).on("click", ".available", function () {
                if ($(this).hasClass('prev')) {
                    return false;
                }
                if ($(this).hasClass('next')) {
                    return false;
                }

                $('.applyBtn').click();
            });



            $("#quickbook_post").click(function (ev) {
                var criteria = '';
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                }
                var InvoiceIDs = [];
                var i = 0;
                if (!confirm('Are you sure you want to post in quickbook selected invoices journal?')) {
                    return;
                }
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    InvoiceID = $(this).val();
                    if (typeof InvoiceID != 'undefined' && InvoiceID != null && InvoiceID != 'null') {
                        InvoiceIDs[i++] = InvoiceID;
                    }
                });
                if (InvoiceIDs.length) {
                    submit_ajax(baseurl + '/invoice/invoice_quickbookpost', 'InvoiceIDs=' + InvoiceIDs.join(",") + '&criteria=' + criteria + '&type=journal')
                }
            });

            $("#quickbook_post_invoice").click(function (ev) {
                var criteria = '';
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                }
                var InvoiceIDs = [];
                var i = 0;
                if (!confirm('Are you sure you want to post in quickbook selected invoices?')) {
                    return;
                }
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    InvoiceID = $(this).val();
                    if (typeof InvoiceID != 'undefined' && InvoiceID != null && InvoiceID != 'null') {
                        InvoiceIDs[i++] = InvoiceID;
                    }
                });
                if (InvoiceIDs.length) {
                    submit_ajax(baseurl + '/invoice/invoice_quickbookpost', 'InvoiceIDs=' + InvoiceIDs.join(",") + '&criteria=' + criteria + '&type=invoice')
                }
            });

            $("#quickbook_invoice_export").click(function (ev) {
                var criteria = '';
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                }
                var InvoiceIDs = [];
                var i = 0;
                if (!confirm('Are you sure you want to Export Quickbook selected invoices?')) {
                    return;
                }
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    InvoiceID = $(this).val();
                    if (typeof InvoiceID != 'undefined' && InvoiceID != null && InvoiceID != 'null') {
                        InvoiceIDs[i++] = InvoiceID;
                    }
                });
                if (InvoiceIDs.length) {
                    submit_ajax(baseurl + '/invoice/invoice_quickbookexport', 'InvoiceIDs=' + InvoiceIDs.join(",") + '&criteria=' + criteria )
                }
            });

            $("#quickbookd_journal_export").click(function (ev) {
                var criteria = '';
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                }
                var InvoiceIDs = [];
                var i = 0;
                if (!confirm('Are you sure you want to Export Quickbook selected Journals?')) {
                    return;
                }
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    InvoiceID = $(this).val();
                    if (typeof InvoiceID != 'undefined' && InvoiceID != null && InvoiceID != 'null') {
                        InvoiceIDs[i++] = InvoiceID;
                    }
                });
                if (InvoiceIDs.length) {
                    //submit_ajax(baseurl + '/invoice/journal_quickbookdexport', 'InvoiceIDs=' + InvoiceIDs.join(",") + '&criteria=' + criteria )
                    $.ajax({
                        url:baseurl + '/invoice/journal_quickbookdexport',
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            $(".btn").button('reset');
                            if (response.status == 'success') {
                                $('.modal').modal('hide');
                                toastr.success(response.message, "Success", toastr_opts);

                                if(typeof response.redirect != 'undefined' && response.redirect != ''){
                                    var url = baseurl + '/invoice/journal_quickbookdexport_download';
                                    var data = '?file=' + response.redirect ;
                                    window.location.href = url + data;
                                }
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        data: 'InvoiceIDs=' + InvoiceIDs.join(","),
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false
                    });
                }
            });

            $("#xero_post").click(function (ev) {
                var criteria = '';
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                }
                var InvoiceIDs = [];
                var i = 0;
                if (!confirm('Are you sure you want to post in xero selected invoices?')) {
                    return;
                }
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    InvoiceID = $(this).val();
                    if (typeof InvoiceID != 'undefined' && InvoiceID != null && InvoiceID != 'null') {
                        InvoiceIDs[i++] = InvoiceID;
                    }
                });
                if (InvoiceIDs.length) {
                    submit_ajax(baseurl + '/invoice/invoice_xeropost', 'InvoiceIDs=' + InvoiceIDs.join(",") + '&criteria=' + criteria + '&type=invoice')
                }
            });


            $("#xero_journal").click(function (ev){
                var criteria = '';
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                }
                var InvoiceIDs = [];
                var i = 0;
                if (!confirm('Are you sure you want to post as journal in xero selected invoices?')) {
                    return;
                }
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    InvoiceID = $(this).val();
                    if (typeof InvoiceID != 'undefined' && InvoiceID != null && InvoiceID != 'null') {
                        InvoiceIDs[i++] = InvoiceID;
                    }
                });
                if (InvoiceIDs.length) {
                    submit_ajax(baseurl + '/invoice/invoice_xeropost', 'InvoiceIDs=' + InvoiceIDs.join(",") + '&criteria=' + criteria + '&type=journal')
                }
            });

            $("#sage-post").click(function (ev) {
                var criteria = '';
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                }
                var InvoiceIDs = [];
                var i = 0;
                var MarkPaid = '0';
                if (confirm('Do you want to change the status of selected invoices to Paid?\n\nAccounts where Sage Pay Bank Account Details are not setup will not be exported.')) {
                    MarkPaid = '1';
                }
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    InvoiceID = $(this).val();
                    if (typeof InvoiceID != 'undefined' && InvoiceID != null && InvoiceID != 'null') {
                        InvoiceIDs[i++] = InvoiceID;
                    }
                });
                if (InvoiceIDs.length) {
                    var url = baseurl + '/invoice/invoice_sagepayexport';
                    var data = '?InvoiceIDs=' + InvoiceIDs.join(",") + '&criteria=' + criteria + '&MarkPaid=' + MarkPaid;

                    window.location.href = url + data;
                    setTimeout(function () {
                        data_table.fnFilter('', 0);
                    }, 1000);
                    //submit_ajax(baseurl + '/invoice/invoice_sagepayexport', 'InvoiceIDs=' + InvoiceIDs.join(",") + '&criteria=' + criteria + '&MarkPaid=' + MarkPaid)
                }
            });

        });

    </script>
<style>
        #table-4 .dataTables_filter label {
            display: none !important;
        }

        .dataTables_wrapper .export-data {
            right: 30px !important;
        }

        #table-5_filter label {
            display: block !important;
        }

        #selectcheckbox {
            padding: 15px 10px;
        }
    </style>
@include('accounts.bulk_email')
@include('invoices.manualmodal')
@stop
@section('footer_ext')
    @parent 
<!-- Job Modal  (Ajax Modal)-->
<div class="modal fade custom-width" id="print-modal-invoice">
  <div class="modal-dialog" style="width: 60%;">
    <div class="modal-content">
      <form id="add-new-invoice_template-form" method="post" class="form-horizontal form-groups-bordered"
                      enctype="multipart/form-data">
        <div class="modal-header">
          <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
          <h4 class="modal-title"> <a class="btn btn-primary print btn-sm btn-icon icon-left" href=""> <i class="entypo-print"></i> Print </a> </h4>
        </div>
        <div class="modal-body"> Content is loading... </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade custom-width" id="modal-invoice-in">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="add-invoice_in_template-form" method="post" class="form-horizontal form-groups-bordered">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Add Invoice</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="field-5" class="col-sm-2 control-label">Account Name</label>
            <div class="col-sm-4"> {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}
              <input type="hidden" name="InvoiceID">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Start Date</label>
            <div class="col-sm-2">
              <input type="text" name="StartDate" class="form-control datepicker"
                                       data-date-format="yyyy-mm-dd" value="" data-enddate="{{date('Y-m-d')}}"/>
            </div>
            <div class="col-sm-2">
              <input type="text" name="StartTime" value="00:00:00" data-minute-step="5" data-show-meridian="false"
                                       data-default-time="00:00 AM" data-show-seconds="true" data-template="dropdown"
                                       class="form-control timepicker">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">End Date</label>
            <div class="col-sm-2">
              <input type="text" name="EndDate" class="form-control datepicker"
                                       data-date-format="yyyy-mm-dd" value="" data-enddate="{{date('Y-m-d')}}"/>
            </div>
            <div class="col-sm-2">
              <input type="text" name="EndTime" value="23:59:59" data-minute-step="5" data-show-meridian="false"
                                       data-default-time="00:00 AM" data-show-seconds="true" data-template="dropdown"
                                       class="form-control timepicker">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Issue Date</label>
            <div class="col-sm-4">
              <input type="text" name="IssueDate" class="form-control datepicker"
                                       data-date-format="yyyy-mm-dd" value="" data-enddate="{{date('Y-m-d')}}"/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Invoice Number</label>
            <div class="col-sm-4">
              <input type="text" name="InvoiceNumber" class="form-control" value=""/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Grand Total<span
                                        id="currency"></span></label>
            <div class="col-sm-4">
              <input type="text" name="GrandTotal" class="form-control" value=""/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Total Seconds</label>
            <div class="col-sm-4">
              <input type="text" name="TotalMinutes" class="form-control" value=""/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Dispute Amount</label>
            <div class="col-sm-4">
              <input type="text" name="DisputeAmount" class="form-control" value=""/>
            </div>
          </div>
          <div class="form-group ">
            <label class="col-sm-2 control-label" for="field-1">Reconcile</label>
            <div class="col-sm-4">
              <table class="reconcile_table table table-bordered datatable  hidden">
                <thead>
                <th></th>
                  <th>Total</th>
                  <th>Difference</th>
                  <th>Difference %</th>
                    </thead>
                <tbody>
                  <tr>
                    <th>Amount</th>
                    <td><span class="DisputeTotal"></span></td>
                    <td><span class="DisputeDifference"></span></td>
                    <td><span class="DisputeDifferencePer"></span></td>
                  </tr>
                  <tr>
                    <th>Seconds</th>
                    <td><span class="DisputeMinutes"></span></td>
                    <td><span class="MinutesDifference"></span></td>
                    <td><span class="MinutesDifferencePer"></span></td>
                  </tr>
                </tbody>
              </table>
              <button class="btn btn-primary reconcile btn-sm btn-icon icon-left" type="button"
                                        data-loading-text="Loading..."> <i class="entypo-pencil"></i> Reconcile </button>
              <button class="btn ignore btn-danger btn-sm btn-icon icon-left hidden" type="button"
                                        data-loading-text="Loading..."> <i class="entypo-pencil"></i> Ignore </button>
              <input type="hidden" name="DisputeID">
              {{--
              <input type="hidden" name="DisputeTotal">
              --}}
              {{--
              <input type="hidden" name="DisputeDifference">
              --}}
              {{--
              <input type="hidden" name="DisputeDifferencePer">
              --}}
              
              {{--
              <input type="hidden" name="DisputeMinutes">
              --}}
              {{--
              <input type="hidden" name="MinutesDifference">
              --}}
              {{--
              <input type="hidden" name="MinutesDifferencePer">
              --}} </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Description</label>
            <div class="col-sm-4">
              <input type="text" name="Description" class="form-control" value=""/>
              <input type="hidden" name="InvoiceDetailID" class="form-control" value=""/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Attachment(.pdf, .jpg, .png,
              .gif)</label>
            <div class="col-sm-4">
              <input id="Attachment" name="Attachment" type="file"
                                       class="form-control file2 inline btn btn-primary"
                                       data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse"/>
              
              <!--<br><span class="file-input-name"></span>--> 
            </div>
            <div class="col-sm-1 download"> </div>
          </div>
        </div>
        <div class="modal-footer">
          <button id="saveinvoice" class="btn btn-primary btn-sm btn-icon icon-left" type="submit"
                                data-loading-text="Loading..."> <i class="entypo-pencil"></i> Save</button>
          <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
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
            <label for="field-5" class="col-sm-2 control-label">Account Name</label>
            <div class="col-sm-4 control-label"> <span data-id="AccountName">abcs</span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Issue Date</label>
            <div class="col-sm-4 control-label"> <span data-id="IssueDate"></span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Invoice Number</label>
            <div class="col-sm-4 control-label"> <span data-id="InvoiceNumber"></span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Grand Total<span
                                        id="currency"></span></label>
            <div class="col-sm-4 control-label"> <span data-id="GrandTotal"></span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Description</label>
            <div class="col-sm-4 control-label"> <span data-id="Description"></span> </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="field-1">Attachment</label>
            <div class="col-sm-4 control-label"> <span data-id="Attachment"></span> </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade in" id="send-modal-invoice">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="send-invoice-form" method="post" action="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Send Invoice By Email</h4>
        </div>
        <div class="modal-body"> </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left"
                                data-loading-text="Loading..."> <i class="entypo-mail"></i> Send </button>
          <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade in" id="selected-invoice-status">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="selected-invoice-status-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Change Selected Invoice Status</h4>
        </div>
        <div class="modal-body">
          <div id="text-boxes" class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label">Invoice Status</label>
                {{ Form::select('InvoiceStatus', Invoice::get_invoice_status(), '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Status")) }} </div>
            </div>
            <div class="col-md-6" id="statuscancel">
              <div class="form-group">
                <label for="field-5" class="control-label">Cancel Reason</label>
                <input type="text" name="CancelReason" class="form-control" value=""/>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left cancelbutton"
                                data-loading-text="Loading...">
          <i class="entypo-floppy"></i>
          <input type="hidden" name="InvoiceIDs" value="">
          <input type="hidden" name="criteria"/>
          Save
          </button>
          <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade in" id="invoice-status-cancel">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="invoice-status-cancel-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Provide Cancel Reason</h4>
        </div>
        <div class="modal-body">
          <div id="text-boxes" class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label">Cancel Reason</label>
                <input type="text" name="CancelReason" class="form-control" value=""/>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left"
                                data-loading-text="Loading...">
          <i class="entypo-floppy"></i>
          <input type="hidden" name="InvoiceIDs" value="">
          <input type="hidden" name="InvoiceStatus" value="">
          Save
          </button>
          <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade custom-width" id="pay_now_modal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"> Pay Now </h4>
      </div>
      <div class="modal-body"> </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="add-modal-card" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="add-credit-card-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Add New Card</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Title</label>
                <input type="text" name="Title" class="form-control" id="field-5" placeholder="">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Name on card*</label>
                <input type="text" name="NameOnCard" autocomplete="off" class="form-control"
                                           id="field-5" placeholder="">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Credit Card Number *</label>
                <input type="text" name="CardNumber" autocomplete="off" class="form-control"
                                           id="field-5" placeholder="">
                <input type="hidden" name="cardID"/>
                <input type="hidden" name="AccountID"/>
                <input type="hidden" name="PaymentGatewayID"/>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Card Type*</label>
                {{ Form::select('CardType',Payment::$credit_card_type,'', array("class"=>"select2 small")) }} </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">CVV Number*</label>
                <input type="text" data-mask="decimal" name="CVVNumber" autocomplete="off"
                                           class="form-control" id="field-5" placeholder="">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <div class="col-md-4">
                  <label for="field-5" class="control-label">Expiry Date *</label>
                </div>
                <div class="col-md-4"> {{ Form::select('ExpirationMonth', getMonths(), date('m'), array("class"=>"select2 small")) }} </div>
                <div class="col-md-4"> {{ Form::select('ExpirationYear', getYears(), date('Y'), array("class"=>"select2 small")) }} </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="card-update" class="save btn btn-primary btn-sm btn-icon icon-left"
                                data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="add-modal-bankaccount" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-bankaccount-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Bank Account</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Title</label>
                                <input type="text" name="Title" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Account Holder Name*</label>
                                <input type="text" name="AccountHolderName" autocomplete="off" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Account Number *</label>
                                <input type="text" name="AccountNumber" autocomplete="off" class="form-control" id="field-5" placeholder="">
                                <input type="hidden" name="cardID"/>
                                <input type="hidden" name="AccountID"/>
                                <input type="hidden" name="PaymentGatewayID"/>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Routing Number*</label>
                                <input type="text" name="RoutingNumber" autocomplete="off" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Account Holder Type*</label>
                                {{ Form::select('AccountHolderType',Payment::$account_holder_type,'', array("class"=>"select2 small")) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="bankaccount-update" class="save btn btn-primary btn-sm btn-icon icon-left"
                            data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
                    <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="add-edit-modal-payment">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="add-edit-payment-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Add New payment Request</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Account Name * <span
                                                id="AccountID_currency"></span></label>
                {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}
                <input type="hidden" name="AccountName"/>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Payment Date *</label>
                <input type="text" name="PaymentDate" class="form-control datepicker"
                                           data-enddate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" id="field-5"
                                           placeholder="">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Payment Method *</label>
                {{ Form::select('PaymentMethod',Payment::$method, '', array("class"=>"select2 small")) }} </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Action *</label>
                {{ Form::select('PaymentType', Payment::$action, '', array("class"=>"select2 small","id"=>"PaymentTypeAuto")) }} </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Amount *</label>
                <input type="text" name="Amount" class="form-control" id="field-5" placeholder="">
                <input type="hidden" name="PaymentID">
                <input type="hidden" name="InvoiceID">
                <input type="hidden" name="OutstandingAmount">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Invoice</label>
                <input type="text" id="InvoiceAuto" name="InvoiceNo" class="form-control"
                                           id="field-5" placeholder="">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Notes</label>
                <textarea name="Notes" class="form-control" id="field-5" placeholder=""></textarea>
                <input type="hidden" name="PaymentID">
              </div>
            </div>
            @if(User::is_admin() OR User::is('AccountManager'))
            <div class="col-md-12">
              <div class="form-group">
                <label for="PaymentProof" class="col-sm-2 control-label">Upload (.pdf, .jpg,
                  .png, .gif)</label>
                <div class="col-sm-6">
                  <input id="PaymentProof" name="PaymentProof" type="file"
                                                   class="form-control file2 inline btn btn-primary" data-label="
                            <i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse"/>
                </div>
              </div>
            </div>
            @endif </div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="payment-update" class="save btn btn-primary btn-sm btn-icon icon-left"
                                data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop