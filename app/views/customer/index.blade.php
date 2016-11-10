@extends('layout.customer.main')
@section('content')
    <br />
    <div class="row">
        <div class="col-sm-12">
            <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="post" id="billing_filter">
                <div data-collapsed="0" class="panel panel-primary">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Filter
                        </div>
                        <div class="panel-options">
                            <a data-rel="collapse" href="#">
                                <i class="entypo-down-open"></i>
                            </a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="col-sm-1 control-label" for="Startdate">Start date</label>
                            <div class="col-sm-2">
                                <input type="text" name="Startdate" class="form-control datepicker"   data-date-format="yyyy-mm-dd" value="{{$original_startdate}}" data-enddate="{{date('Y-m-d')}}" />
                            </div>
                            <label class="col-sm-1 control-label" for="field-1">End Date</label>
                            <div class="col-sm-2">
                                <input type="text" name="Enddate" class="form-control datepicker"   data-date-format="yyyy-mm-dd" value="{{$original_enddate}}" data-enddate="{{date('Y-m-d', strtotime('+1 day') )}}" />
                            </div>

                        </div>
                        <p style="text-align: right;">
                            <button class="btn search btn-primary btn-sm btn-icon icon-left" type="submit" data-loading-text="Loading...">
                                <i class="entypo-search"></i>Search
                            </button>
                        </p>
                    </div>
                </div>
            </form>
        </div>

    </div>
    <div class="row">
        <div class="col-md-12">
            <div data-collapsed="0" class="panel panel-primary">
                <div id="invoice-widgets" class="panel-body">
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-blue"><a target="_blank" class="undefined"
                                                             data-startdate="" data-enddate=""
                                                             data-currency="" href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p> Total Outstanding</p></a></div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-green"><a target="_blank" class="undefined" data-startdate=""
                                                              data-enddate="" data-currency=""
                                                              href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Invoice Received</p></a></div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-plum"><a target="_blank" class="undefined" data-startdate=""
                                                             data-enddate="" data-currency=""
                                                             href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Invoice Sent</p></a></div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-orange"><a target="_blank" class="undefined"
                                                               data-startdate="" data-enddate=""
                                                               data-currency="0" href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Due Amount</p></a></div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-red"><a target="_blank" class="undefined" data-startdate=""
                                                            data-enddate="" data-currency=""
                                                            href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Overdue Amount</p></a></div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-purple"><a target="_blank" class="undefined" data-startdate=""
                                                               data-enddate="" data-currency=""
                                                               href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Payment Sent</p></a></div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-cyan"><a target="_blank" class="undefined" data-startdate=""
                                                             data-enddate="" data-currency=""
                                                             href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Payment Received</p></a></div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-orange">
                            <a target="_blank" class="undefined" data-startdate="" data-enddate="" data-currency="" href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix="" data-duration="1500" data-delay="1200">0</div>
                                <p>OutStanding For Selected Period</p>
                            </a>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-aqua"><a target="_blank" class="undefined" data-startdate=""
                                                             data-enddate="" data-currency=""
                                                             href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Pending Dispute</p></a></div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-pink"><a target="_blank" class="undefined" data-startdate=""
                                                             data-enddate="" data-currency=""
                                                             href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Pending Eastimate</p></a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="invoice_expsense panel panel-primary panel-table">
                <form id="invoiceExpensefilter-form" name="filter-form" style="display: inline">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3>Invoices & Expenses</h3>
                    </div>

                    <div class="panel-options">
                        {{ Form::select('ListType',array("Weekly"=>"Weekly","Monthly"=>"Monthly","Yearly"=>"Yearly"),$monthfilter,array("class"=>"select_gray","id"=>"ListType")) }}
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                        <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                    </div>
                </div>
                </form>
                <div class="panel-body">
                    <div id="invoice_expense_bar_chart"></div>
                </div>
            </div>
        </div>

    </div>
    <input name="CurrencyID" type="hidden" value="{{$account->CurrencyId}}">
    @if(CompanySetting::getKeyVal('PincodeWidget') == 1)
        <div class="row">
            <div class="col-sm-12">
                <div class="pin_expsense panel panel-primary panel-table">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h3>Top Pincodes</h3>
                        </div>

                        <div class="panel-options">
                            <form id="filter-form" name="filter-form" style="display: inline" >
                                {{ Form::select('PinExt', array('pincode'=>'By Pincode','extension'=>'By Extension'), 1, array('id'=>'PinExt','class'=>'select_gray')) }}
                                {{ Form::select('Type', array(1=>'By Cost',2=>'By Duration'), 1, array('id'=>'Type','class'=>'select_gray')) }}
                                {{ Form::select('Limit', array(5=>5,10=>10,20=>20), 5, array('id'=>'pin_size','class'=>'select_gray')) }}
                                <input name="AccountID" type="hidden" value="{{Customer::get_accountID()}}">
                                <input name="CurrencyID" type="hidden" value="{{$account->CurrencyId}}">
                            </form>

                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                            <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                        </div>

                    </div>
                    <div class="panel-body">
                        <div id="pin_expense_bar_chart"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row hidden" id="pin_grid_main">
            <div class="col-sm-12">
                <div class="pin_expsense_report panel panel-primary" style="position: static;">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h3>Pincodes Detail Report</h3>
                        </div>

                        <div class="panel-options">
                            <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                            <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="table table-bordered datatable" id="pin_grid">
                            <thead>
                            <tr>
                                <th width="30%">Destination Number</th>
                                <th width="30%">Total Cost</th>
                                <th width="30%">Number of Times Dialed</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    @endif
    <script type="text/javascript">
        function reload_invoice_expense(){


            /*var get_url = baseurl + "/customer/invoice_expense_total";
            loadingUnload('#invoice_expense_total',1);
            $.get( get_url, data , function(response){
                loadingUnload('#invoice_expense_total',0);
                $(".search.btn").button('reset');
                $("#invoice_expense_total").html(response);
            }, "html" );*/
            invoiceExpense();
            invoiceExpenseTotal();
            pin_report();


        }

        function invoiceExpense(){
            var data = $('#billing_filter').serialize() + '&' + $('#invoiceExpensefilter-form').serialize();
            CurrencyID = $("[name=CurrencyID]").val();
            data = data+'&CurrencyID='+CurrencyID;
            var get_url = baseurl + "/customer/invoice_expense_chart";
            loadingUnload('#invoice_expense_bar_chart',1);
            $.get( get_url, data , function(response){
                $(".search.btn").button('reset');
                loadingUnload('#invoice_expense_bar_chart',0);
                $(".panel.invoice_expsense #invoice_expense_bar_chart").html(response);
            }, "html" );
        }

        function invoiceExpenseTotal(){
            var data = $('#billing_filter').serialize();
            CurrencyID = $("[name=CurrencyID]").val();
            data = data+'&CurrencyID='+CurrencyID;
            var get_url = baseurl + "/customer/invoice_expense_total";
            $.get(get_url, data, function (response) {
                var option = [];
                var widgets = '';
                var startDate = '';
                var enddate = '{{date('Y-m-d')}}';
                /*if ($('#billing_filter [name="date-span"]').val() == 6) {
                    startDate = '{{date("Y-m-d",strtotime(''.date('Y-m-d').' -6 months'))}}';
                } else if ($('#billing_filter [name="date-span"]').val() == 12) {
                    startDate = '{{date("Y-m-d",strtotime(''.date('Y-m-d').' -12 months'))}}';
                } else{
                    startDate = $('#billing_filter [name="Closingdate"]').val();
                    var res = startDate.split(" - ");
                    console.log(res);
                    startDate = res[0]+' 00:00:01';
                    enddate = res[1]+' 23:59:59';
                }*/

                $(".search.btn").button('reset');
                option["prefix"] = response.CurrencySymbol;
                option["startdate"] = startDate;
                option["enddate"] = enddate;
                option["currency"] = CurrencyID;
                option["amount"] = response.data.TotalOutstanding;
                option["end"] = response.data.TotalOutstanding;
                option["tileclass"] = 'tile-blue';
                option["class"] = 'outstanding';
                option["type"] = 'Total Outstanding';
                option["count"] = '';
                option["round"] = response.data.Round;
                widgets += buildbox(option);

                option["amount"] = response.data.TotalInvoiceIn;
                option["end"] = response.data.TotalInvoiceIn;
                option["tileclass"] = 'tile-green';
                option["class"] = 'paid';
                option["type"] = 'Invoice Sent';
                /*option["count"] = response.data.CountTotalPaidInvoices;*/
                widgets += buildbox(option);

                option["amount"] = response.data.TotalInvoiceOut;
                option["end"] = response.data.TotalInvoiceOut;
                option["tileclass"] = 'tile-plum';
                option["class"] = 'paid';
                option["type"] = 'Invoice Received';
                /*option["count"] = response.data.CountTotalPaidInvoices;*/
                widgets += buildbox(option);

                option["amount"] = response.data.TotalDueAmount;
                option["end"] = response.data.TotalDueAmount;
                option["tileclass"] = 'tile-orange';
                option["class"] = 'due';
                option["type"] = 'Due Amount';
                /*option["count"] = response.data.CountTotalUnpaidInvoices;*/
                widgets += buildbox(option);

                option["amount"] = response.data.TotalOverdueAmount;
                option["end"] = response.data.TotalOverdueAmount;
                option["tileclass"] = 'tile-red';
                option["class"] = 'overdue';
                option["type"] = 'Overdue Amount';
                /*option["count"] = response.data.CountTotalOverdueInvoices;*/
                widgets += buildbox(option);

                /*option["amount"] = response.data.TotalPartiallyPaidInvoices;
                 option["end"] = response.data.TotalPartiallyPaidInvoices;
                 option["tileclass"] = 'tile-cyan';
                 option["class"] = 'partiallypaid';
                 option["type"] = 'Partially Paid invoices';
                 option["count"] = response.data.CountTotalPartiallyPaidInvoices;
                 widgets += buildbox(option);*/

                option["amount"] = response.data.TotalPaymentsIn;
                option["end"] = response.data.TotalPaymentsIn;
                option["tileclass"] = 'tile-purple';
                option["class"] = 'paymentReceived1';
                option["type"] = 'Payments Sent';
                /*option["count"] = response.data.CountTotalPayment;*/
                widgets += buildbox(option);

                option["amount"] = response.data.TotalPaymentsOut;
                option["end"] = response.data.TotalPaymentsOut;
                option["tileclass"] = 'tile-cyan';
                option["class"] = 'paymentsent';
                option["type"] = 'Payments Received';
                /*option["count"] = response.data.CountTotalPayment;*/
                widgets += buildbox(option);

                option["amount"] = response.data.Outstanding;
                option["end"] = response.data.Outstanding;
                option["tileclass"] = 'tile-brown';
                option["class"] = 'paymentsent';
                option["type"] = 'OutStanding For Selected Period';
                /*option["count"] = response.data.CountTotalPayment;*/
                widgets += buildbox(option);

                option["amount"] = response.data.TotalDispute;
                option["end"] = response.data.TotalDispute;
                option["tileclass"] = 'tile-aqua';
                option["class"] = 'Pendingdispute';
                option["type"] = 'Pending Dispute';
                /*option["count"] = response.data.CountTotalDispute;*/
                widgets += buildbox(option);

                option["amount"] = response.data.TotalEstimate;
                option["end"] = response.data.TotalEstimate;
                option["tileclass"] = 'tile-pink';
                option["class"] = 'Pendingestimate';
                option["type"] = 'Pending Estimate';
                /*option["count"] = response.data.CountTotalDispute;*/
                widgets += buildbox(option);

                $('#invoice-widgets').html(widgets);

                titleState();
            }, "json");
        }

        $('#invoiceExpensefilter-form [name="ListType"]').change(function(){
            invoiceExpense();
        });

        function pin_title(){
            if($("#filter-form [name='PinExt']").val() == 'pincode'){
                $('.pin_expsense').find('h3').html('Top Pincodes');
                $('.pin_expsense_report').find('h3').html('Top Pincodes Detail Report');
            }
            if($("#filter-form [name='PinExt']").val() == 'extension'){
                $('.pin_expsense').find('h3').html('Top Extensions ');
                $('.pin_expsense_report').find('h3').html('Top Extensions Detail Report');

            }
        }
        function loadingUnload(table,bit){
            var panel = jQuery(table).closest('.panel');
            if(bit==1){
                blockUI(panel);
                panel.addClass('reloading');
            }else{
                unblockUI(panel);
                panel.removeClass('reloading');
            }
        }
        function pin_report() {
            @if(CompanySetting::getKeyVal('PincodeWidget') == 1)
            $("#pin_grid_main").addClass('hidden');
            loadingUnload('#pin_expense_bar_chart', 1);
            data = $('#billing_filter').serialize() + '&' + $('#filter-form').serialize() ;
            pin_title();
            var get_url = baseurl + "/billing_dashboard/ajax_top_pincode";
            $.get(get_url, data, function (response) {
                loadingUnload('#pin_expense_bar_chart', 0);
                $(".save.btn").button('reset');
                $("#pin_expense_bar_chart").html(response);
            }, "html");
            @endif
        }

        $(function() {
            reload_invoice_expense();

            $('#billing_filter').submit(function(e){
                e.preventDefault();
                reload_invoice_expense();
                return false;
            });
            $("#pin_size").change(function(){
                pin_report();
            });
            $("#Type").change(function(){
                pin_report();
            });
            $("#PinExt").change(function(){
                pin_report();
            });

            var $searchFilter = {};
            var invoicestatus = {{$invoice_status_json}};
            $searchFilter.PaymentDate_StartDate = $('[name="Startdate"]').val();
            $searchFilter.PaymentDate_StartTime = '';
            $searchFilter.PaymentDate_EndDate   = $('[name="Enddate"]').val();
            $searchFilter.PaymentDate_EndTime   = '';
            $searchFilter.CurrencyID 			= $('[name="CurrencyID"]').val();
            $searchFilter.Type = 1;
            var TotalSum=0;
            var TotalPaymentSum = 0;
            var TotalPendingSum = 0;
            PaymentTable = $("#paymentTable").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/customer/billing_dashboard/ajax_datagrid_Invoice_Expense/type",
                "fnServerParams": function (aoData) {
                    aoData.push(
                            {"name": "PaymentDate_StartDate","value": $searchFilter.PaymentDate_StartDate},
                            {"name": "PaymentDate_EndDate","value": $searchFilter.PaymentDate_EndDate},
                            {"name": "CurrencyID","value": $searchFilter.CurrencyID},
                            {"name": "Type","value": $searchFilter.Type}
                    );
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push(
                            {"name": "PaymentDate_StartDate","value": $searchFilter.PaymentDate_StartDate},
                            {"name": "PaymentDate_EndDate","value": $searchFilter.PaymentDate_EndDate},
                            {"name": "CurrencyID","value": $searchFilter.CurrencyID},
                            {"name": "Type","value": $searchFilter.Type},
                            {"name":"Export","value":1}
                    );

                },
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[4, 'desc']],
                "aoColumns": [
                    //1   CurrencyDescription
                    {
                        "bSortable": true, //Account
                        mRender: function (id, type, full) {
                            return full[1]
                        }
                    }, //1   CurrencyDescription
                    {
                        "bSortable": true, //Account
                        mRender: function (id, type, full) {
                            return full[10]
                        }
                    }, //1   CurrencyDescription
                    {
                        "bSortable": true, //Amount
                        mRender: function (id, type, full) {
                            /*var a = parseFloat(Math.round(full[3] * 100) / 100).toFixed(toFixed);
                             a = a.toString();*/
                            return full[16]
                        }
                    },
                    {
                        "bSortable": true, //paymentDate
                        mRender: function (id, type, full) {
                            return full[6]
                        }
                    },
                    {
                        "bSortable": true, //Created by
                        mRender: function (id, type, full) {
                            return full[8]
                        }
                    },
                    {
                        "bSortable": true, //Created by
                        mRender: function (id, type, full) {
                            return full[12]
                        }
                    },
                ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/customer/billing_dashboard/ajax_datagrid_Invoice_Expense/xlsx", //baseurl + "/generate_xlsx.php",
                            sButtonClass: "save-collection"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/customer/billing_dashboard/ajax_datagrid_Invoice_Expense/csv", //baseurl + "/generate_csv.php",
                            sButtonClass: "save-collection"
                        }
                    ]
                },
                "fnDrawCallback": function () {
                    //get_total_grand();
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    $("#table-4 tbody input[type=checkbox]").each(function (i, el) {
                        var $this = $(el),
                                $p = $this.closest('tr');

                        $(el).on('change', function () {
                            var is_checked = $this.is(':checked');

                            $p[is_checked ? 'addClass' : 'removeClass']('selected');
                        });
                    });

                    $('.tohidden').removeClass('hidden');
                    $('#selectall').removeClass('hidden');
                    if($('#Recall_on_off').prop("checked")){
                        $('.tohidden').addClass('hidden');
                        $('#selectall').addClass('hidden');
                    }
                },
                "fnServerData": function ( sSource, aoData, fnCallback ) {
                    /* Add some extra data to the sender */
                    $.getJSON( sSource, aoData, function (json) {
                        /* Do whatever additional processing you want on the callback, then tell DataTables */
                        TotalSum = json.Total.totalsum;
                        fnCallback(json)
                    });
                },
                "fnFooterCallback": function ( row, data, start, end, display ) {
                    if (end > 0) {
                        $(row).html('');
                        for (var i = 0; i < 2; i++) {
                            var a = document.createElement('td');
                            $(a).html('');
                            $(row).append(a);
                        }
                        if(TotalSum) {
                            $($(row).children().get(0)).attr('colspan',2)
                            $($(row).children().get(1)).html('<strong>' + TotalSum + '</strong>');
                        }
                    }else{
                        $("#paymentTable").find('tfoot').find('tr').html('');
                    }
                }

            });


            invoiceTable = $("#invoiceTable").dataTable({
                "bDestroy": true,
                "bProcessing":true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/customer/billing_dashboard/ajax_datagrid_Invoice_Expense/type",
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[2, 'desc']],
                "fnServerParams": function(aoData) {
                    aoData.push(
                            {"name": "PaymentDate_StartDate","value": $searchFilter.PaymentDate_StartDate},
                            {"name": "PaymentDate_EndDate","value": $searchFilter.PaymentDate_EndDate},
                            {"name": "CurrencyID","value": $searchFilter.CurrencyID},
                            {"name": "Type","value": $searchFilter.Type}
                    );
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push(
                            {"name": "PaymentDate_StartDate","value": $searchFilter.PaymentDate_StartDate},
                            {"name": "PaymentDate_EndDate","value": $searchFilter.PaymentDate_EndDate},
                            {"name": "CurrencyID","value": $searchFilter.CurrencyID},
                            {"name": "Type","value": $searchFilter.Type},
                            {"name":"Export","value":1}
                    );
                },
                "aoColumns":
                        [
                            // 0 AccountName
                            {  "bSortable": true,

                                mRender:function( id, type, full){
                                    var output , account_url;
                                    output = '<a href="{url}" target="_blank" >{account_name}';
                                    if(full[14] ==''){
                                        output+= '<br> <span class="text-danger"><small>(Email not setup)</small></span>';
                                    }
                                    output+= '</a>';
                                    account_url = baseurl + "/accounts/"+ full[11] + "/show";
                                    output = output.replace("{url}",account_url);
                                    output = output.replace("{account_name}",id);
                                    return output;
                                }

                            },  // 1 InvoiceNumber
                            {  "bSortable": true,

                                mRender:function( id, type, full){

                                    var output , account_url;
                                    if (full[0] != '{{Invoice::INVOICE_IN}}') {
                                        output = '<a href="{url}" target="_blank"> ' + id + '</a>';
                                        account_url = baseurl + "/invoice/" + full[8] + "/invoice_preview";
                                        output = output.replace("{url}", account_url);
                                        output = output.replace("{account_name}", id);
                                    }else{
                                        output = id;
                                    }
                                    return output;
                                }

                            },  // 2 IssueDate
                            {  "bSortable": true },  // 3 IssueDate
                            {  "bSortable": true },  //4 Invoice period
                            {  "bSortable": true },  // 5 GrandTotal
                            {  "bSortable": false },  // 6 PAID/OS
                            {  "bSortable": true,
                                mRender:function( id, type, full){
                                    return invoicestatus[full[6]];
                                }

                            },  // 6 InvoiceStatus
                        ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/customer/billing_dashboard/ajax_datagrid_Invoice_Expense/xlsx", //baseurl + "/generate_xls.php",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/customer/billing_dashboard/ajax_datagrid_Invoice_Expense/csv", //baseurl + "/generate_xls.php",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function() {
                    //get_total_grand(); //get result total
                    $('#table-4 tbody tr').each(function(i, el) {
                        if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
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
                    $('#selectallbutton').click(function(ev) {
                        if($(this).is(':checked')){
                            checked = 'checked=checked disabled';
                            $("#selectall").prop("checked", true).prop('disabled', true);
                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                $('#table-4 tbody tr').each(function(i, el) {
                                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {

                                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                        $(this).addClass('selected');
                                    }
                                });
                            }
                        }else{
                            checked = '';
                            $("#selectall").prop("checked", false).prop('disabled', false);
                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                $('#table-4 tbody tr').each(function(i, el) {
                                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {

                                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                        $(this).removeClass('selected');
                                    }
                                });
                            }
                        }
                    });
                },
                "fnServerData": function ( sSource, aoData, fnCallback ) {
                    /* Add some extra data to the sender */
                    $.getJSON( sSource, aoData, function (json) {
                        /* Do whatever additional processing you want on the callback, then tell DataTables */
                        TotalSum = json.Total.totalsum;
                        TotalPaymentSum = json.Total.totalpaymentsum;
                        TotalPendingSum = json.Total.totalpendingsum;
                        fnCallback(json)
                    });
                },
                "fnFooterCallback": function ( row, data, start, end, display ) {
                    if (end > 0) {
                        $(row).html('');
                        for (var i = 0; i < 3; i++) {
                            var a = document.createElement('td');
                            $(a).html('');
                            $(row).append(a);
                        }
                        if(TotalSum) {
                            $($(row).children().get(0)).attr('colspan',4)
                            $($(row).children().get(1)).html('<strong>' + TotalSum + '</strong>');
                            $($(row).children().get(2)).html('<strong>' + TotalPaymentSum +'/' + TotalPendingSum + '</strong>');
                        }
                    }else{
                        $("#invoiceTable").find('tfoot').find('tr').html('');
                    }
                }

            });


            $(document).on('click','.paymentReceived,.totalInvoice,.totalOutstanding',function(e){
                e.preventDefault();
                $searchFilter.PaymentDate_StartDate = $(this).attr('data-startdate');
                $searchFilter.PaymentDate_StartTime = '';
                $searchFilter.PaymentDate_EndDate   = $(this).attr('data-enddate');
                $searchFilter.PaymentDate_EndTime   = '';
                $searchFilter.CurrencyID 			= $(this).attr('data-currency');
                if($(this).hasClass('paymentReceived')) {
                    $searchFilter.Type = 1;
                    //PaymentTable.fnClearTable();
                    PaymentTable.fnFilter('', 0);
                    $('#modal-Payment').modal('show');
                }else if($(this).hasClass('totalInvoice')){
                    $searchFilter.Type = 2;
                    //invoiceTable.fnClearTable();
                    invoiceTable.fnFilter('', 0);
                    $('#modal-invoice').modal('show');
                }else if($(this).hasClass('totalOutstanding')){
                    $searchFilter.Type = 3;
                    //invoiceTable.fnClearTable();
                    invoiceTable.fnFilter('', 0);
                    $('#modal-invoice').modal('show');
                }

            });
        });
        function dataGrid(Pincode,Startdate,Enddate,PinExt,CurrencyID){
            $("#pin_grid_main").removeClass('hidden');
            if(PinExt == 'pincode'){
                $('.pin_expsense_report').find('h3').html('Pincode '+Pincode+' Detail Report');
            }
            if(PinExt == 'extension'){
                $('.pin_expsense_report').find('h3').html('Extension'+Pincode+' Detail Report');

            }
            data_table = $("#pin_grid").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/billing_dashboard/ajaxgrid_top_pincode/type",
                "fnServerParams": function (aoData) {
                    aoData.push(
                            {"name": "Pincode", "value": Pincode},
                            {"name": "Startdate", "value": Startdate},
                            {"name": "Enddate", "value": Enddate},
                            {"name": "PinExt", "value": PinExt},
                            {"name": "CurrencyID", "value": CurrencyID}
                    );

                    data_table_extra_params.length = 0;
                    data_table_extra_params.push(
                            {"name": "Pincode", "value": Pincode},
                            {"name": "Startdate", "value": Startdate},
                            {"name": "Enddate", "value": Enddate},
                            {"name": "PinExt", "value": PinExt},
                            {"name": "CurrencyID", "value": CurrencyID},
                            {"name":"Export","value":1}
                    );

                },
                "iDisplayLength": '10',
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[0, 'asc']],
                "aoColumns": [
                    {"bSortable": true},  // 1 Destination Number
                    {"bSortable": true},  // 2 Total Cost
                    {"bSortable": true}  // 3 Number of Times Dialed
                ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/billing_dashboard/ajaxgrid_top_pincode/xlsx", //baseurl + "/generate_xls.php",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/billing_dashboard/ajaxgrid_top_pincode/csv", //baseurl + "/generate_csv.php",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function () {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }

            });
        }

        function buildbox(option) {
            html = '<div class="col-sm-3 col-xs-6">';
            html += ' <div class="tile-stats ' + option['tileclass'] + '">';
            //html += '  <a class="' + option['class'] + '" data-startdate="' + option['startdate'] + '" data-enddate="' + option['enddate'] + '" data-currency="' + option['currency'] + '" href="javascript:void(0)">';
            html += '   <div class="num" data-start="0" data-end="' + option['end'] + '" data-prefix="' + option['prefix'] + '" data-postfix="" data-duration="1500" data-delay="1200" data-round="'+option['round']+'">' + option['amount'] + '</div>';
            html += '    <p>' + option['count'] + ' ' + option['type'] + '</p>';
            //html += '  </a>';
            html += ' </div>';
            html += '</div>';
            return html;
        }

        function titleState() {
            $("#invoice-widgets").find('.tile-stats').each(function (i, el) {
                var $this = $(el),
                        $num = $this.find('.num'),
                        start = attrDefault($num, 'start', 0),
                        end = attrDefault($num, 'end', 0),
                        prefix = attrDefault($num, 'prefix', ''),
                        postfix = attrDefault($num, 'postfix', ''),
                        duration = attrDefault($num, 'duration', 1000),
                        delay = attrDefault($num, 'delay', 1000);
                round = attrDefault($num, 'round', 0);

                if (start < end) {
                    if (typeof scrollMonitor == 'undefined') {
                        $num.html(prefix + end + postfix);
                    }
                    else {
                        var tile_stats = scrollMonitor.create(el);

                        tile_stats.fullyEnterViewport(function () {

                            var o = {curr: start};

                            TweenLite.to(o, duration / 1000, {
                                curr: end, ease: Power1.easeInOut, delay: delay / 1000, onUpdate: function () {
                                    $num.html(prefix + o.curr.toFixed(2) + postfix);
                                }
                            });

                            tile_stats.destroy()
                        });
                    }
                }

                if($num.text().indexOf(prefix)==-1){
                    $num.prepend(prefix);
                }
            });
        }

    </script>
@stop

@section('footer_ext')
    @parent
    <div class="modal fade" id="modal-Payment">
        <div class="modal-dialog" style="width: 60%;">
            <div class="modal-content">
                <form id="BulkMail-form" method="post" action="" enctype="multipart/form-data">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Payment Received</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered datatable" id="paymentTable">
                            <thead>
                            <tr>
                                <th>Account Name</th>
                                <th>Invoice No</th>
                                <th>Amount</th>
                                <th>Payment Date</th>
                                <th>CreatedBy</th>
                                <th>Notes</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                            <tr></tr>
                            </tfoot>
                        </table>
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

    <div class="modal fade" id="modal-invoice">
        <div class="modal-dialog" style="width: 60%;">
            <div class="modal-content">
                <form id="TestMail-form" method="post" action="">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Total Invoices</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered datatable" id="invoiceTable">
                            <thead>
                            <tr>
                                <th>Account Name</th>
                                <th>Invoice Number</th>
                                <th>Issue Date</th>
                                <th>Period</th>
                                <th>Grand Total</th>
                                <th>Paid/OS</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>


                            </tbody>
                            <tfoot>
                            <tr></tr>
                            </tfoot>
                        </table>
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
@stop