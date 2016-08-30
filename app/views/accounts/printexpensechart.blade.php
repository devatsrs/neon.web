@extends('layout.print')
@section('content')
    @include('includes.login-css')
    <style>
        *{
            font-family: Arial;
            font-size: 10px;
            line-height: normal;
        }
        p{ line-height: 20px;}
        .text-left{ text-align: left}
        .text-right{ text-align: right}
        .text-center{ text-align: center}
        table.invoice th{ padding:3px; background-color: #f5f5f6}
        .bg_graycolor{background-color: #f5f5f6}
        table.invoice td , table.invoice_total td{ padding:3px;}
        @media print {
            .page_break{page-break-after: always;}
            * {
                background-color: auto !important;
                background: auto !important;
                color: auto !important;
            }
            th,td{ padding: 1px; margin: 1px;}
        }
        .page_break{page-break-after: always;}
    </style>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-primary panel-table loading">
                <div class="panel-body">
                    <div id="account_expense_bar_chart" style="width: 1024px;height: 400px;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel loading panel-default dataTables_wrapper" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->

                <!-- panel body -->
                <div class="panel-body">

                    <table class="table table-bordered datatable" id="expense_customer_table">
                        {{$response['CustomerActivity']}}
                    </table>
                </div>
            </div>

        </div>
        <div class="col-sm-12">
            <div class="panel loading panel-default dataTables_wrapper" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->

                <!-- panel body -->
                <div class="panel-body">

                    <table class="table table-bordered datatable" id="expense_vendor_table">
                        {{$response['VendorActivity']}}
                    </table>
                </div>
            </div>

        </div>

    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="panel loading panel-default dataTables_wrapper" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                <!-- panel body -->
                <div class="panel-body">
                    <table class="table table-bordered datatable" id="expense_year_table">
                        <thead>
                        <tr>
                            <th width="30%">Year</th>
                            <th width="30%">Customer</th>
                            <th width="40%">Vendor</th>
                        </tr>
                        </thead>
                        <tbody>
                            {{$response['ExpenseYear']}}
                        </tbody>
                        <tfoot>
                        <tr>

                        </tr>
                        </tfoot>

                    </table>
                </div>
            </div>

        </div>
    </div>
    <script src="<?php echo URL::to('/'); ?>/assets/js/jquery-1.11.0.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script>
        var CurrencySymbol = '{{$CurrencySymbol}}';
        var categories = '{{$response['categories']}}';
        var customer = '{{$response['customer']}}';
        var vendor = '{{$response['vendor']}}';
        $(function() {
            Highcharts.theme = {
                colors: ['#3366cc', '#ff9900','#dc3912', '#109618', '#66aa00', '#dd4477','#0099c6', '#990099', '#143DFF']
            };
            // Apply the theme
            Highcharts.setOptions(Highcharts.theme);
        if(categories != '' && categories.split(',').length > 0) {

            $('#account_expense_bar_chart').highcharts({
                title: {
                    text: 'Account Activity',
                    x: -20 //center
                },
                xAxis: {
                    categories: categories.split(',')
                },
                yAxis: {
                    title: {
                        text: 'Amount('+CurrencySymbol+')'
                    },
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },
                tooltip: {
                    valuePrefix: CurrencySymbol
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'middle',
                    borderWidth: 0
                },
                credits: {
                    enabled: false
                },
                series: [{
                    name: 'Customer Activity',
                    data: customer.split(',').map(parseFloat)
                }, {
                    name: 'Vendor Activity',
                    data: vendor.split(',').map(parseFloat)
                }
                ]
            });
        }else{
            $('#account_expense_bar_chart').html('No Data');
        }
        });
    </script>
@stop