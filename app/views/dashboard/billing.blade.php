@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate class="form-horizontal form-groups-bordered validate" method="post" id="billing_filter">
                <div class="form-group">
                    <label for="field-1" class="control-label">Currency</label>
                    {{Form::select('CurrencyID',Currency::getCurrencyDropdownIDList(),$DefaultCurrencyID,array("class"=>"select2"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Date</label>
                    <div class="row">
                        <div class="col-sm-12">
                            {{ Form::select('date-span', array(6=>'6 Months',12=>'12 Months',0=>'Custome Date'), 1, array('id'=>'date-span','class'=>'select2 small')) }}
                        </div>
                        <div class="col-sm-12 tobehidden hidden" style="margin-top: 10px;">
                            <input value="{{$StartDateDefault}} - {{$DateEndDefault}}" type="text" id="Closingdate"
                                   data-format="YYYY-MM-DD" name="Closingdate" class="form-control daterange">
                        </div>
                    </div>
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
    <?php
    $url = URL::to('invoice');
    //http_build_query(['StartDate'=>isset($data['StartDate'])?$data['StartDate']:date('Y-m-d'),'EndDate'=>isset($data['EndDate'])?$data['EndDate']:date('Y-m-d')])
    ?>
    <br/>
    <?php if(User::checkCategoryPermission('BillingDashboardSummaryWidgets','View')){ ?>
        <div class="row">
        <div class="col-md-12">
            <div data-collapsed="0" class="panel panel-primary">
                <div id="invoice-widgets" class="panel-body">
                    @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardTotalOutstanding',$BillingDashboardWidgets))
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-blue"><a target="_blank" class="undefined"
                                                                  data-startdate="" data-enddate=""
                                                                  data-currency="" href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p> Total Outstanding</p></a></div>
                    </div>
                    @endif
                    @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPayableAmount',$BillingDashboardWidgets))
                        <div class="col-sm-3 col-xs-6">
                            <div class="tile-stats tile-orange"><a target="_blank" class="undefined" data-startdate=""
                                                                   data-enddate="" data-currency=""
                                                                   href="javascript:void(0)">
                                    <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                         data-duration="1500" data-delay="1200">0
                                    </div>
                                    <p>Total Payable</p></a></div>
                        </div>
                    @endif
                    @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardReceivableAmount',$BillingDashboardWidgets))
                        <div class="col-sm-3 col-xs-6">
                            <div class="tile-stats tile-red"><a target="_blank" class="undefined" data-startdate=""
                                                                data-enddate="" data-currency=""
                                                                href="javascript:void(0)">
                                    <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                         data-duration="1500" data-delay="1200">0
                                    </div>
                                    <p>Total Receivable</p></a></div>
                        </div>
                    @endif
                    @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardTotalInvoiceSent',$BillingDashboardWidgets))
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-green"><a target="_blank" class="undefined" data-startdate=""
                                                              data-enddate="" data-currency=""
                                                              href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Invoice Sent for selected period</p></a></div>
                    </div>
                    @endif
                    @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardTotalInvoiceReceived',$BillingDashboardWidgets))
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-plum"><a target="_blank" class="undefined" data-startdate=""
                                                              data-enddate="" data-currency=""
                                                              href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Invoice Received</p></a></div>
                    </div>
                    @endif
                        @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardDueAmount',$BillingDashboardWidgets))
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-orange"><a target="_blank" class="undefined"
                                                               data-startdate="" data-enddate=""
                                                               data-currency="0" href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Due Amount</p></a></div>
                    </div>
                    @endif
                        @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardOverDueAmount',$BillingDashboardWidgets))
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-red"><a target="_blank" class="undefined" data-startdate=""
                                                            data-enddate="" data-currency=""
                                                            href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Overdue Amount</p></a></div>
                    </div>
                    @endif
                        @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPaymentReceived',$BillingDashboardWidgets))
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-purple"><a target="_blank" class="undefined" data-startdate=""
                                                             data-enddate="" data-currency=""
                                                             href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Payment Received</p></a></div>
                    </div>
                        @endif
                    @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPaymentSent',$BillingDashboardWidgets))
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-cyan"><a target="_blank" class="undefined" data-startdate=""
                                                               data-enddate="" data-currency=""
                                                               href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Payment Sent</p></a></div>
                    </div>
                    @endif
                    @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardOutstanding',$BillingDashboardWidgets))
                        <div class="col-sm-3 col-xs-6">
                            <div class="tile-stats tile-brown">
                                <a target="_blank" class="undefined" data-startdate="" data-enddate="" data-currency="" href="javascript:void(0)">
                                    <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix="" data-duration="1500" data-delay="1200">0</div>
                                    <p>Outstanding For Selected Period</p>
                                </a>
                            </div>
                        </div>
                    @endif
                    @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPendingDispute',$BillingDashboardWidgets))
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-aqua"><a target="_blank" class="undefined" data-startdate=""
                                                             data-enddate="" data-currency=""
                                                             href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Pending Dispute</p></a></div>
                    </div>
                    @endif
                   @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPendingEstimate',$BillingDashboardWidgets))
                    <div class="col-sm-3 col-xs-6">
                        <div class="tile-stats tile-pink"><a target="_blank" class="undefined" data-startdate=""
                                                             data-enddate="" data-currency=""
                                                             href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Pending Eastimate</p></a></div>
                    </div>
                   @endif
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
    @if(((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPaybleWidget',$BillingDashboardWidgets))&&User::checkCategoryPermission('BillingDashboardPaybleWidget','View'))
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-primary panel-table">
                    <div class="panel-heading">
                        <div id="Sales_Manager" class="pull-right panel-box panel-options"> <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a> <a data-rel="close" href="#"><i class="entypo-cancel"></i></a></div>
                        <div class="panel-title forecase_title">
                            <h3>Payable & Receivable  </h3>
                            <div class="PayableReceivable"></div>
                        </div>
                    </div>
                    <div class="form_Sales panel-body white-bg">
                        <form novalidate class="form-horizontal form-groups-bordered"  id="PayableReceivableForm">
                            <div class="form-group form-group-border-none">
                                <div class="col-sm-8">
                                    <label for="Closingdate" class="col-sm-1 control-label managerLabel ">Date</label>
                                    <div class="col-sm-6"> <input value="{{$StartDateDefault1}} - {{$DateEndDefault}}" type="text" id="Duedate"  data-format="YYYY-MM-DD"  name="Duedate" class="small-date-input daterange">
                                        {{ Form::select('ListType',array("Daily"=>"Daily","Weekly"=>"Weekly","Monthly"=>"Monthly"),$GetDashboardPR,array("class"=>"select_gray","id"=>"ListType")) }}
                                        {{ Form::select('Type',array("0"=>"Exclude Unbill Amount","1"=>"Include Unbill Amount"),'Weekly',array("class"=>"select_gray","id"=>"ListType")) }}
                                        <button type="submit" id="submit_Sales" class="btn btn-sm btn-primary"><i class="entypo-search"></i></button></div>
                                </div>
                            </div>
                            <div class="text-center">
                                <div id="PayableReceivable1" style="min-width: 310px; height: 400px; margin: 0 auto" class="PayableReceivable1"></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if(((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardProfitWidget',$BillingDashboardWidgets))&&User::checkCategoryPermission('BillingDashboardProfitWidget','View'))
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-primary panel-table">
                    <div class="panel-heading">
                        <div id="Sales_Manager" class="pull-right panel-box panel-options"> <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a> <a data-rel="close" href="#"><i class="entypo-cancel"></i></a></div>
                        <div class="panel-title forecase_title">
                            <h3>Profit & Loss  </h3>
                            <div class="ProfitLoss"></div>
                        </div>
                    </div>
                    <div class="form_Sales panel-body white-bg">
                        <form novalidate class="form-horizontal form-groups-bordered"  id="ProfitLossForm">
                            <div class="form-group form-group-border-none">
                                <div class="col-sm-8">
                                    <label for="Closingdate" class="col-sm-1 control-label managerLabel ">Date</label>
                                    <div class="col-sm-6"> <input value="{{$StartDateDefault1}} - {{$DateEndDefault}}" type="text" id="Duedate"  data-format="YYYY-MM-DD"  name="Duedate" class="small-date-input daterange">
                                        {{ Form::select('ListType',array("Daily"=>"Daily","Weekly"=>"Weekly","Monthly"=>"Monthly"),$GetDashboardPL,array("class"=>"select_gray","id"=>"ListType")) }}
                                        <button type="submit" id="submit_Sales" class="btn btn-sm btn-primary"><i class="entypo-search"></i></button></div>
                                </div>
                            </div>
                            <div class="text-center">
                                <div id="ProfitLoss1" style="min-width: 310px; height: 400px; margin: 0 auto" class="ProfitLoss1"></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if(((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardInvoiceExpense',$BillingDashboardWidgets)) && User::checkCategoryPermission('BillingDashboardInvoiceExpenseWidgets','View'))
    <div class="row">
        <div class="col-md-12">
            <div class="invoice_expsense panel panel-primary panel-table">
                <form id="invoiceExpensefilter-form" name="filter-form">
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
    @endif
    @if(((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPincodeWidget',$BillingDashboardWidgets))&& User::checkCategoryPermission('BillingDashboardPincodeWidget','View'))
        <div class="row">
            <div class="col-sm-12">
                <div class="pin_expsense panel panel-primary panel-table">
                    <form id="filter-form" name="filter-form" style="display: inline">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h3>Top Pincodes</h3>
                        </div>
                        <div class="panel-options">

                                {{ Form::select('PinExt', array('pincode'=>'By Pincode','extension'=>'By Extension'), 1, array('id'=>'PinExt','class'=>'select_gray')) }}
                                {{ Form::select('Type', array(1=>'By Cost',2=>'By Duration'), 1, array('id'=>'Type','class'=>'select_gray')) }}
                                {{ Form::select('Limit', array(5=>5,10=>10,20=>20), 5, array('id'=>'pin_size','class'=>'select_gray')) }}

                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                            <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                        </div>

                    </div>
                    </form>
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
    @if(((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardMissingGatewayWidget',$BillingDashboardWidgets))&&User::checkCategoryPermission('BillingDashboardMissingGatewayWidget','View'))
    <div class="row">
        <div class="col-sm-6">
            <div class="panel panel-primary panel-table">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3>Missing Gateway Accounts ()</h3>

                    </div>

                    <div class="panel-options">
                        {{ Form::select('CompanyGatewayID', $company_gateway, 1, array('id'=>'company_gateway','class'=>'select_gray')) }}
                        <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                        <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a>
                        <a data-rel="close" href="#"><i class="entypo-cancel"></i></a>
                        <a data-rel="empty" href="#" title="Delete Missing Gateway Accounts"><i class="entypo-trash"></i></a>
                    </div>
                </div>
                <div class="panel-body" style="max-height: 450px; overflow-y: auto; overflow-x: hidden;">
                    <table id="missingAccounts" class="table table-responsive">
                        <thead>
                        <tr>
                            <th>Account Name</th>
                            <th>Gateway</th>
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
    <script src="{{ URL::asset('assets/js/highcharts.js') }}"></script>
    <script type="text/javascript">

        jQuery(document).ready(function ($) {

            $('#filter-button-toggle').show();

            var $searchFilter = {};
            var invoicestatus = {{$invoice_status_json}};
            $searchFilter.PaymentDate_StartDate = $('[name="Startdate"]').val();
            $searchFilter.PaymentDate_StartTime = '';
            $searchFilter.PaymentDate_EndDate = $('[name="Enddate"]').val();
            $searchFilter.PaymentDate_EndTime = '';
            $searchFilter.CurrencyID = $('[name="CurrencyID"]').val();
            $searchFilter.Type = 1;
            var TotalSum = 0;
            var TotalPaymentSum = 0;
            var TotalPendingSum = 0;
            var url = '{{$url}}';
            Highcharts.theme = {
                colors: ['#3366cc', '#ff9900' ,'#dc3912' , '#109618', '#66aa00', '#dd4477','#0099c6', '#990099', '#143DFF']
            };
            // Apply the theme
            Highcharts.setOptions(Highcharts.theme);
            $('#PayableReceivableForm').submit(function(e) {
                e.preventDefault();
                GetDashboardPR();
            });
            $('#ProfitLossForm').submit(function(e) {
                e.preventDefault();
                GetDashboardPL();
            });
            function getDrilDown(type) {
                if(type==1) {
                    $("#paymentTable").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/billing_dashboard/ajax_datagrid_Invoice_Expense/type",
                        "fnServerParams": function (aoData) {
                            aoData.push(
                                    {"name": "PaymentDate_StartDate", "value": $searchFilter.PaymentDate_StartDate},
                                    {"name": "PaymentDate_EndDate", "value": $searchFilter.PaymentDate_EndDate},
                                    {"name": "CurrencyID", "value": $searchFilter.CurrencyID},
                                    {"name": "Type", "value": $searchFilter.Type}
                            );
                            data_table_extra_params.length = 0;
                            data_table_extra_params.push(
                                    {"name": "PaymentDate_StartDate", "value": $searchFilter.PaymentDate_StartDate},
                                    {"name": "PaymentDate_EndDate", "value": $searchFilter.PaymentDate_EndDate},
                                    {"name": "CurrencyID", "value": $searchFilter.CurrencyID},
                                    {"name": "Type", "value": $searchFilter.Type},
                                    {"name": "Export", "value": 1}
                            );

                        },
                        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[4, 'desc']],
                        "aoColumns": [
                            {"bSortable": true}, //0   Account Name
                            {"bSortable": true}, //1   Invoice No
                            {"bSortable": true}, //2   Amount
                            {"bSortable": true}, //3   PaymentDate
                            {"bSortable": true}, //4   Created by
                            {"bSortable": true}, //5   Notes
                        ],
                        "oTableTools": {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "EXCEL",
                                    "sUrl": baseurl + "/billing_dashboard/ajax_datagrid_Invoice_Expense/xlsx", //baseurl + "/generate_xlsx.php",
                                    sButtonClass: "save-collection"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/billing_dashboard/ajax_datagrid_Invoice_Expense/csv", //baseurl + "/generate_csv.php",
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
                            if ($('#Recall_on_off').prop("checked")) {
                                $('.tohidden').addClass('hidden');
                                $('#selectall').addClass('hidden');
                            }
                        },
                        "fnServerData": function (sSource, aoData, fnCallback) {
                            /* Add some extra data to the sender */
                            $.getJSON(sSource, aoData, function (json) {
                                /* Do whatever additional processing you want on the callback, then tell DataTables */
                                TotalSum = json.Total.totalsum;
                                fnCallback(json)
                            });
                        },
                        "fnFooterCallback": function (row, data, start, end, display) {
                            if (end > 0) {
                                $(row).html('');
                                for (var i = 0; i < 2; i++) {
                                    var a = document.createElement('td');
                                    $(a).html('');
                                    $(row).append(a);
                                }
                                if (TotalSum) {
                                    $($(row).children().get(0)).attr('colspan', 2);
                                    $($(row).children().get(0)).html('<strong>Total</strong>');
                                    $($(row).children().get(1)).html('<strong>' + TotalSum + '</strong>');
                                }
                            } else {
                                $("#paymentTable").find('tfoot').find('tr').html('');
                            }
                        }

                    });

                }else {
                    $("#invoiceTable").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/billing_dashboard/ajax_datagrid_Invoice_Expense/type",
                        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[2, 'desc']],
                        "fnServerParams": function (aoData) {
                            aoData.push(
                                    {"name": "PaymentDate_StartDate", "value": $searchFilter.PaymentDate_StartDate},
                                    {"name": "PaymentDate_EndDate", "value": $searchFilter.PaymentDate_EndDate},
                                    {"name": "CurrencyID", "value": $searchFilter.CurrencyID},
                                    {"name": "Type", "value": $searchFilter.Type}
                            );
                            data_table_extra_params.length = 0;
                            data_table_extra_params.push(
                                    {"name": "PaymentDate_StartDate", "value": $searchFilter.PaymentDate_StartDate},
                                    {"name": "PaymentDate_EndDate", "value": $searchFilter.PaymentDate_EndDate},
                                    {"name": "CurrencyID", "value": $searchFilter.CurrencyID},
                                    {"name": "Type", "value": $searchFilter.Type},
                                    {"name": "Export", "value": 1}
                            );
                        },
                        "aoColumns": [
                            // 0 AccountName
                            {
                                "bSortable": true,

                                mRender: function (id, type, full) {
                                    var output, account_url;
                                    output = '<a href="{url}" target="_blank" >{account_name}';
                                    if (full[11] == '') {
                                        output += '<br> <span class="text-danger"><small>(Email not setup)</small></span>';
                                    }
                                    output += '</a>';
                                    account_url = baseurl + "/accounts/" + full[8] + "/show";
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
                                        account_url = baseurl + "/invoice/" + full[7] + "/invoice_preview";
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
                            {"bSortable": false},  // 6 PAID/OS
                            {
                                "bSortable": true,
                                mRender: function (id, type, full) {
                                    return invoicestatus[full[6]];
                                }

                            },  // 6 InvoiceStatus
                            /*{"bSortable": true},*/ //6   Overdue Aging
                        ],
                        "oTableTools": {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "EXCEL",
                                    "sUrl": baseurl + "/billing_dashboard/ajax_datagrid_Invoice_Expense/xlsx", //baseurl + "/generate_xls.php",
                                    sButtonClass: "save-collection btn-sm"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/billing_dashboard/ajax_datagrid_Invoice_Expense/csv", //baseurl + "/generate_xls.php",
                                    sButtonClass: "save-collection btn-sm"
                                }
                            ]
                        },
                        "fnDrawCallback": function () {
                            //get_total_grand(); //get result total
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
                        },
                        "fnServerData": function (sSource, aoData, fnCallback) {
                            /* Add some extra data to the sender */
                            $.getJSON(sSource, aoData, function (json) {
                                /* Do whatever additional processing you want on the callback, then tell DataTables */
                                TotalSum = json.Total.currencySymbol + json.Total.totalsum;
                                TotalPaymentSum = json.Total.currencySymbol + json.Total.totalpaymentsum;
                                TotalPendingSum = json.Total.currencySymbol + json.Total.totalpendingsum;
                                fnCallback(json)
                            });
                        },
                        "fnFooterCallback": function (row, data, start, end, display) {
                            if (end > 0) {
                                $(row).html('');
                                for (var i = 0; i < 3; i++) {
                                    var a = document.createElement('td');
                                    $(a).html('');
                                    $(row).append(a);
                                }
                                if (TotalSum) {
                                    $($(row).children().get(0)).attr('colspan', 4);
                                    $($(row).children().get(0)).html('<strong>Total</strong>');
                                    $($(row).children().get(1)).html('<strong>' + TotalSum + '</strong>');
                                    $($(row).children().get(2)).html('<strong>' + TotalPaymentSum + '/' + TotalPendingSum + '</strong>');
                                }
                            } else {
                                $("#invoiceTable").find('tfoot').find('tr').html('');
                            }
                        }

                    });
                }
            }
            $('#billing_filter [name="date-span"]').change(function(){
                $('.tobehidden').addClass('hidden');
                if($(this).val()==0){
                    $('.tobehidden').removeClass('hidden');
                }else if($(this).val()>0){
                    var date2 = new Date();
                    var today = new Date();
                    date2.setMonth(date2.getMonth() - $(this).val());
                    date2.setDate(1);
                    $('#billing_filter [name="Closingdate"]').val(date2.getFullYear()+'-'+(date2.getMonth()+1)+'-'+date2.getDate()+' - '+today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate());
                }
            });
            $('#billing_filter [name="date-span"]').trigger('change');

            $(document).on('click', '.paymentReceived,.totalInvoice,.totalOutstanding', function (e) {
                e.preventDefault();
                $searchFilter.PaymentDate_StartDate = $(this).attr('data-startdate');
                $searchFilter.PaymentDate_StartTime = '';
                $searchFilter.PaymentDate_EndDate = $(this).attr('data-enddate');
                $searchFilter.PaymentDate_EndTime = '';
                $searchFilter.CurrencyID = $(this).attr('data-currency');
                if ($(this).hasClass('paymentReceived')) {
                    $searchFilter.Type = 1;
                    //PaymentTable.fnFilter('', 0);
                    getDrilDown(1);
                    $('#modal-Payment').modal('show');
                } else if ($(this).hasClass('totalInvoice')) {
                    $searchFilter.Type = 2;
                    //invoiceTable.fnFilter('', 0);
                    getDrilDown(2);
                    $('#modal-invoice h4').text('Total Invoices');
                    $('#modal-invoice').modal('show');
                } else if ($(this).hasClass('totalOutstanding')) {
                    $searchFilter.Type = 3;
                    //invoiceTable.fnFilter('', 0);
                    getDrilDown(3);
                    $('#modal-invoice h4').text('Total Outstanding');
                    $('#modal-invoice').modal('show');
                } /*else if ($(this).hasClass('unpaid')) {
                    $searchFilter.Type = 4;
                    invoiceTable.fnFilter('', 0);
                    $('#modal-invoice h4').text('Unpaid Invoices');
                    $('#modal-invoice').modal('show');
                } else if ($(this).hasClass('overdue')) {
                    $searchFilter.Type = 5;
                    invoiceTable.fnFilter('', 0);
                    $('#modal-invoice h4').text('Overdue Invoices');
                    $('#modal-invoice').modal('show');
                } else if ($(this).hasClass('paid')) {
                    $searchFilter.Type = 6;
                    invoiceTable.fnFilter('', 0);
                    $('#modal-invoice h4').text('Paid Invoices');
                    $('#modal-invoice').modal('show');
                }else if ($(this).hasClass('partiallypaid')) {
                    $searchFilter.Type = 7;
                    invoiceTable.fnFilter('', 0);
                    $('#modal-invoice h4').text('Partially Paid Invoices');
                    $('#modal-invoice').modal('show');
                }else if ($(this).hasClass('Pendingdispute')) {

                }*/

            });
        });


        $('#invoiceExpensefilter-form [name="ListType"]').change(function(){
            invoiceExpense();
        });

        function reload_invoice_expense() {
            invoiceExpense();
            invoiceExpenseTotal();
            pin_report();
            missingAccounts();
        }


        function pin_title() {
            if ($("#filter-form [name='PinExt']").val() == 'pincode') {
                $('.pin_expsense').find('h3').html('Top Pincodes');
                $('.pin_expsense_report').find('h3').html('Top Pincodes Detail Report');
            }
            if ($("#filter-form [name='PinExt']").val() == 'extension') {
                $('.pin_expsense').find('h3').html('Top Extensions ');
                $('.pin_expsense_report').find('h3').html('Top Extensions Detail Report');

            }
        }
        function loadingUnload(table, bit) {
            var panel = jQuery(table).closest('.panel');
            if (bit == 1) {
                blockUI(panel);
                panel.addClass('reloading');
            } else {
                unblockUI(panel);
                panel.removeClass('reloading');
            }
        }

        function pin_report() {
            @if(((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPincodeWidget',$BillingDashboardWidgets)))
            $("#pin_grid_main").addClass('hidden');
            loadingUnload('#pin_expense_bar_chart', 1);
            data = $('#billing_filter').serialize() + '&' + $('#filter-form').serialize();
            pin_title();
            var get_url = baseurl + "/billing_dashboard/ajax_top_pincode";
            $.get(get_url, data, function (response) {
                loadingUnload('#pin_expense_bar_chart', 0);
                $(".save.btn").button('reset');
                $("#pin_expense_bar_chart").html(response);
            }, "html");
            @endif
        }
        $('body').on('click', '.panel > .panel-heading > .panel-options > a[data-rel="reload"]', function (e) {
            e.preventDefault();
            var id = $(this).parents('.panel-primary').find('table').attr('id');
            if (id == 'missingAccounts') {
                missingAccounts();
            }
        });
        $('body').on('click', '.panel > .panel-heading > .panel-options > a[data-rel="empty"]', function (e) {
            e.preventDefault();
            var id = $(this).parents('.panel-primary').find('table').attr('id');
            if (id == 'missingAccounts') {
                deleteMissingAccounts();
            }
        });
        $(function () {
            reload_invoice_expense();
            GetDashboardPR();
            GetDashboardPL();
            $("#filter-pin").hide();
            $('#billing_filter').submit(function (e) {
                e.preventDefault();
                reload_invoice_expense();
                return false;
            });
            $('#filter-form').submit(function (e) {
                e.preventDefault();
                pin_report();
                return false;
            });
            $("#pin_fiter").click(function () {
                $("#filter-pin").slideToggle();
            });
            $("#pin_size").change(function () {
                pin_report();
            });
            $("#Type").change(function () {
                pin_report();
            });
            $("#PinExt").change(function () {
                pin_report();
            })
            $("#company_gateway").change(function () {
                missingAccounts();
            });
        });

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

        function titleState(el) {

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
        }

            function invoiceExpense() {
                @if(((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardInvoiceExpense',$BillingDashboardWidgets)) && User::checkCategoryPermission('BillingDashboardInvoiceExpenseWidgets','View'))
                var get_url = baseurl + "/billing_dashboard/invoice_expense_chart";
                data = $('#billing_filter').serialize() + '&ListType=' + $('#invoiceExpensefilter-form [name="ListType"]').val();
                var CurrencyID = $('#billing_filter [name="CurrencyID"]').val();
                loadingUnload('#invoice_expense_bar_chart', 1);
                $.get(get_url, data, function (response) {
                    $(".search.btn").button('reset');
                    loadingUnload('#invoice_expense_bar_chart', 0);
                    $(".panel.invoice_expsense #invoice_expense_bar_chart").html(response);
                }, "html");
                @endif
            }
        function invoiceExpenseTotalwidgets(){
            @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardTotalOutstanding',$BillingDashboardWidgets) || in_array('BillingDashboardPayableAmount',$BillingDashboardWidgets) || in_array('BillingDashboardReceivableAmount',$BillingDashboardWidgets))
                var data = $('#billing_filter').serialize();
                var get_url = baseurl + "/billing_dashboard/invoice_expense_total_widget";
                $.get(get_url, data, function (response) {
                    var CurrencyID = $('#billing_filter [name="CurrencyID"]').val();
                    var option = [];
                    var widgets = '';
                    var startDate = '';
                    var enddate = '{{date('Y-m-d')}}';
                    if ($('#billing_filter [name="date-span"]').val() == 6) {
                        startDate = '{{date("Y-m-d",strtotime(''.date('Y-m-d').' -6 months'))}}';
                    } else if ($('#billing_filter [name="date-span"]').val() == 12) {
                        startDate = '{{date("Y-m-d",strtotime(''.date('Y-m-d').' -12 months'))}}';
                    } else{
                        startDate = $('#billing_filter [name="Closingdate"]').val();
                        var res = startDate.split(" - ");
                        console.log(res);
                        startDate = res[0]+' 00:00:01';
                        enddate = res[1]+' 23:59:59';
                    }

                    $(".search.btn").button('reset');

                    option["prefix"] = response.CurrencySymbol;
                    option["startdate"] = startDate;
                    option["enddate"] = enddate;
                    option["currency"] = CurrencyID;
                    option["count"] = '';
                    option["round"] = response.data.Round;

                    @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardTotalOutstanding',$BillingDashboardWidgets))
                    option["amount"] = response.data.TotalOutstanding;
                    option["end"] = response.data.TotalOutstanding;
                    option["tileclass"] = 'tile-blue';
                    option["type"] = 'Total Outstanding';
                    widgets += buildbox(option);
                    @endif
                    @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPayableAmount',$BillingDashboardWidgets))
                        option["amount"] = response.data.TotalPayable;
                        option["end"] = response.data.TotalPayable;
                        option["tileclass"] = 'tile-orange';
                        option["type"] = 'Total Payable';
                        widgets += buildbox(option);
                    @endif
                    @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardReceivableAmount',$BillingDashboardWidgets))
                        option["amount"] = response.data.TotalReceivable;
                        option["end"] = response.data.TotalReceivable;
                        option["tileclass"] = 'tile-red';
                        option["type"] = 'Total Receivable';
                        widgets += buildbox(option);
                    @endif
                    var ele = $('<div></div>');
                    ele.html(widgets);
                    var temp = ele.find('.col-xs-6');
                    $('#invoice-widgets').prepend(temp);
                    $("#invoice-widgets").find('.tile-stats').each(function (i, el) {
						titleState(el);
					});

                }, "json");
            @endif
        }

        function invoiceExpenseTotal(){
            var data = $('#billing_filter').serialize();
            var get_url = baseurl + "/billing_dashboard/invoice_expense_total";
            $.get(get_url, data, function (response) {
                invoiceExpenseTotalwidgets();
                var CurrencyID = $('#billing_filter [name="CurrencyID"]').val();
                var option = [];
                var widgets = '';
                var startDate = '';
                var enddate = '{{date('Y-m-d')}}';
                if ($('#billing_filter [name="date-span"]').val() == 6) {
                    startDate = '{{date("Y-m-d",strtotime(''.date('Y-m-d').' -6 months'))}}';
                } else if ($('#billing_filter [name="date-span"]').val() == 12) {
                    startDate = '{{date("Y-m-d",strtotime(''.date('Y-m-d').' -12 months'))}}';
                } else{
                    startDate = $('#billing_filter [name="Closingdate"]').val();
                    var res = startDate.split(" - ");
                    console.log(res);
                    startDate = res[0]+' 00:00:01';
                    enddate = res[1]+' 23:59:59';
                }

                $(".search.btn").button('reset');
                option["prefix"] = response.CurrencySymbol;
                option["startdate"] = startDate;
                option["enddate"] = enddate;
                option["currency"] = CurrencyID;
                option["count"] = '';

                @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardTotalInvoiceSent',$BillingDashboardWidgets))
                option["amount"] = response.data.TotalInvoiceOut;
                option["end"] = response.data.TotalInvoiceOut;
                option["tileclass"] = 'tile-plum';
                option["class"] = 'paid';
                option["type"] = 'Invoice Sent for selected period';
                /*option["count"] = response.data.CountTotalPaidInvoices;*/
                widgets += buildbox(option);
                @endif
                @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardTotalInvoiceReceived',$BillingDashboardWidgets))
                option["amount"] = response.data.TotalInvoiceIn;
                option["end"] = response.data.TotalInvoiceIn;
                option["tileclass"] = 'tile-green';
                option["class"] = 'paid';
                option["type"] = 'Invoice Received';
                /*option["count"] = response.data.CountTotalPaidInvoices;*/
                widgets += buildbox(option);
                @endif

                @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardDueAmount',$BillingDashboardWidgets))
                option["amount"] = response.data.TotalDueAmount;
                option["end"] = response.data.TotalDueAmount;
                option["tileclass"] = 'tile-orange';
                option["class"] = 'due';
                option["type"] = 'Due Amount';
                /*option["count"] = response.data.CountTotalUnpaidInvoices;*/
                widgets += buildbox(option);
                @endif
                @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardOverDueAmount',$BillingDashboardWidgets))
                option["amount"] = response.data.TotalOverdueAmount;
                option["end"] = response.data.TotalOverdueAmount;
                option["tileclass"] = 'tile-red';
                option["class"] = 'overdue';
                option["type"] = 'Overdue Amount';
                /*option["count"] = response.data.CountTotalOverdueInvoices;*/
                widgets += buildbox(option);
                @endif
                /*option["amount"] = response.data.TotalPartiallyPaidInvoices;
                option["end"] = response.data.TotalPartiallyPaidInvoices;
                option["tileclass"] = 'tile-cyan';
                option["class"] = 'partiallypaid';
                option["type"] = 'Partially Paid invoices';
                option["count"] = response.data.CountTotalPartiallyPaidInvoices;
                widgets += buildbox(option);*/
                @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPaymentReceived',$BillingDashboardWidgets))
                option["amount"] = response.data.TotalPaymentsIn;
                option["end"] = response.data.TotalPaymentsIn;
                option["tileclass"] = 'tile-purple';
                option["class"] = 'paymentReceived1';
                option["type"] = 'Payments Received';
                /*option["count"] = response.data.CountTotalPayment;*/
                widgets += buildbox(option);
                @endif
                @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPaymentSent',$BillingDashboardWidgets))
                option["amount"] = response.data.TotalPaymentsOut;
                option["end"] = response.data.TotalPaymentsOut;
                option["tileclass"] = 'tile-cyan';
                option["class"] = 'paymentsent';
                option["type"] = 'Payments Sent';
                /*option["count"] = response.data.CountTotalPayment;*/
                widgets += buildbox(option);
                @endif
                @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardOutstanding',$BillingDashboardWidgets))
                option["amount"] = response.data.Outstanding;
                option["end"] = response.data.Outstanding;
                option["tileclass"] = 'tile-brown';
                option["class"] = 'paymentsent';
                option["type"] = 'Outstanding For Selected Period';
                /*option["count"] = response.data.CountTotalPayment;*/
                widgets += buildbox(option);
                @endif
                @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPendingDispute',$BillingDashboardWidgets))
                option["amount"] = response.data.TotalDispute;
                option["end"] = response.data.TotalDispute;
                option["tileclass"] = 'tile-aqua';
                option["class"] = 'Pendingdispute';
                option["type"] = 'Pending Dispute';
                /*option["count"] = response.data.CountTotalDispute;*/
                widgets += buildbox(option);
                @endif
                @if((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPendingEstimate',$BillingDashboardWidgets))
                option["amount"] = response.data.TotalEstimate;
                option["end"] = response.data.TotalEstimate;
                option["tileclass"] = 'tile-pink';
                option["class"] = 'Pendingestimate';
                option["type"] = 'Pending Estimate';
                /*option["count"] = response.data.CountTotalDispute;*/
                widgets += buildbox(option);
                @endif
                $('#invoice-widgets').html(widgets);
                $("#invoice-widgets").find('.tile-stats').each(function (i, el) {
                    titleState(el);
                });
            }, "json");
        }

        function deleteMissingAccounts() {
            @if(((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardMissingGatewayWidget',$BillingDashboardWidgets))&&User::checkCategoryPermission('BillingDashboardMissingGatewayWidget','View'))
            var gateWayID = $("#company_gateway").val();
            if(gateWayID) {
                if(confirm('Are you sure you want to delete missing gateway accounts?')) {
                    var table = $('#missingAccounts');
                    loadingUnload(table, 1);
                    var url = baseurl + '/dashboard/delete_missing_accounts/' + gateWayID;
                    showAjaxScript(url, [], function (response) {
                        $(".btn").button('reset');
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        missingAccounts();
                        loadingUnload(table, 0);
                    });
                }
            }
            @endif
        }

        function missingAccounts() {
            @if(((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardMissingGatewayWidget',$BillingDashboardWidgets))&&User::checkCategoryPermission('BillingDashboardMissingGatewayWidget','View'))
            var table = $('#missingAccounts');
            loadingUnload(table, 1);
            var url = baseurl + '/dashboard/ajax_get_missing_accounts?CompanyGatewayID=' + $("#company_gateway").val();
            $.ajax({
                url: url,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    var accounts = response.missingAccounts;
                    html = '';
                    table.parents('.panel-primary').find('.panel-title h3').html('Missing Gateway Accounts (' + accounts.length + ')');
                    table.find('tbody').html('');
                    if (accounts.length > 0) {
                        for (i = 0; i < accounts.length; i++) {
                            html += '<tr>';
                            html += '      <td>' + accounts[i]["AccountName"] + '</td>';
                            html += '      <td>' + accounts[i]["Title"] + '</td>';
                            html += '</tr>';
                        }
                    } else {
                        html = '<td colspan="3">No Records found.</td>';
                    }
                    table.find('tbody').html(html);
                    loadingUnload(table, 0);
                },
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
            @endif
        }
        function dataGrid(Pincode, Startdate, Enddate, PinExt, CurrencyID) {
            $("#pin_grid_main").removeClass('hidden');
            if (PinExt == 'pincode') {
                $('.pin_expsense_report').find('h3').html('Pincode ' + Pincode + ' Detail Report');
            }
            if (PinExt == 'extension') {
                $('.pin_expsense_report').find('h3').html('Extension' + Pincode + ' Detail Report');

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
                            {"name": "Export", "value": 1}
                    );

                },
                "iDisplayLength": 10,
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
                            "sUrl": baseurl + "/billing_dashboard/ajaxgrid_top_pincode/xlsx", //baseurl + "/generate_xlsx.php",
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
        function GetDashboardPR(){
            @if(((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardPaybleWidget',$BillingDashboardWidgets))&&User::checkCategoryPermission('BillingDashboardPaybleWidget','View'))
                        loadingUnload(".PayableReceivable1",1);
            var CurrencyID  = $("#billing_filter [name='CurrencyID']").val();
            var Duedate     = $("#PayableReceivableForm [name='Duedate']").val();
            var ListType     = $("#PayableReceivableForm [name='ListType']").val();
            var Type     = $("#PayableReceivableForm [name='Type']").val();
            $.ajax({
                type: 'POST',
                url: baseurl+'/billing_dashboard/GetDashboardPR',
                dataType: 'json',
                data:{CurrencyID:CurrencyID,Duedate:Duedate,ListType:ListType,Type:Type},
                aysync: true,
                success: function(dataObj) {
                    $('#PayableReceivable1').html('');
                    loadingUnload(".PayableReceivable1",0);

                    if(dataObj.series != '' && dataObj.series.length > 0) {

                            var seriesdata =  [];
                            var categories =  [];
                        seriesdata = JSON.parse(JSON.stringify(dataObj.series));


                            $('#PayableReceivable1').highcharts({
                                chart: {
                                    type: 'column'
                                },
                                title: {
                                    text: ''
                                },
                                xAxis: {
                                    /*categories: dataObj.categories.split(','),*/
                                    title: {
                                        text: ""
                                    },
                                    type: 'category'
                                },
                                tooltip: {
                                    valueSuffix: ''
                                },
                                plotOptions: {
                                    bar: {
                                        dataLabels: {
                                            enabled: true
                                        }
                                    },
                                    column: {
                                        pointPadding: 0.2,
                                        borderWidth: 0
                                    }
                                },
                                legend: {
                                    layout: 'vertical',
                                    align: 'right',
                                    verticalAlign: 'top',
                                    x: -40,
                                    y: 80,
                                    floating: false,
                                    borderWidth: 1,
                                    backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                                    shadow: false
                                },
                                credits: {
                                    enabled: false
                                },

                                series: seriesdata,

                            });


                        }else{
                            $('.PayableReceivable1').html('<br><h4>No Data</h4>');
                            $('.PayableReceivable').html('');
                        }

                    ////////
                }
            });
            @endif
        }
        function GetDashboardPL(){
            @if(((count($BillingDashboardWidgets)==0) ||  in_array('BillingDashboardProfitWidget',$BillingDashboardWidgets))&&User::checkCategoryPermission('BillingDashboardProfitWidget','View'))
                        loadingUnload(".ProfitLoss1",1);
            var CurrencyID  = $("#billing_filter [name='CurrencyID']").val();
            var Duedate     = $("#ProfitLossForm [name='Duedate']").val();
            var ListType     = $("#ProfitLossForm [name='ListType']").val();
            $.ajax({
                type: 'POST',
                url: baseurl+'/billing_dashboard/GetDashboardPL',
                dataType: 'json',
                data:{CurrencyID:CurrencyID,Duedate:Duedate,ListType:ListType},
                aysync: true,
                success: function(dataObj) {
                    $('#ProfitLoss1').html('');
                    loadingUnload(".ProfitLoss1",0);

                    if(dataObj.series != '' && dataObj.series.length > 0) {

                        var seriesdata =  [];
                        var categories =  [];
                        seriesdata = JSON.parse(JSON.stringify(dataObj.series));


                        $('#ProfitLoss1').highcharts({
                            chart: {
                                type: 'column'
                            },
                            title: {
                                text: ''
                            },
                            xAxis: {
                                /*categories: dataObj.categories.split(','),*/
                                title: {
                                    text: ""
                                },
                                type: 'category'
                            },
                            tooltip: {
                                valueSuffix: ''
                            },
                            plotOptions: {
                                bar: {
                                    dataLabels: {
                                        enabled: true
                                    }
                                },
                                column: {
                                    pointPadding: 0.2,
                                    borderWidth: 0
                                },
                                series: {
                                    className: 'main-color',
                                    negativeColor: true
                                }
                            },

                            credits: {
                                enabled: false
                            },

                            series: seriesdata,

                        });


                    }else{
                        $('.ProfitLoss1').html('<br><h4>No Data</h4>');
                        $('.ProfitLoss').html('');
                    }

                    ////////
                }
            });
            @endif
        }
    </script>
    <style>

        .form_Sales, .form_Forecast{ margin-left:30px;}
        .forecase_title{padding-bottom:10px !important;}
        .form-group-border-none{border-bottom:none !important; padding-bottom:0px !important;}
        .small-date-input
        {
            width:150px;
        }
        .white-bg{background:#fff none repeat scroll 0 0 !important; }
        .managerLabel{
            padding-left:0;
            padding-right:0;
            width:38px;
        }
        .panel-heading{
            border:none !important;
        }
        #customer .panel-heading{
            border-bottom:1px solid transparent !important;
            border-color:#ebebeb !important;
        }
        #ProfitLoss1 .highcharts-point.highcharts-negative {
            fill: #f56954;
        }
    </style>
@stop

@section('footer_ext')
    @parent
    <div class="modal fade" id="modal-Payment">
        <div class="modal-dialog modal-lg">
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
                                <th width="15%">Account Name</th>
                                <th width="10%">Invoice No</th>
                                <th width="10%">Amount</th>
                                <th width="15%">Payment Date</th>
                                <th width="15%">CreatedBy</th>
                                <th width="35%">Notes</th>
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
                        <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-invoice">
        <div class="modal-dialog modal-lg">
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
                                <th width="20%">Account Name</th>
                                <th width="10%">Invoice Number</th>
                                <th width="15%">Issue Date</th>
                                <th width="20%">Period</th>
                                <th width="10%">Grand Total</th>
                                <th width="10%">Paid/OS</th>
                                <th width="10%">Status</th>
                                <!--<th width="5%">Overdue Aging</th>-->
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
                        <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop