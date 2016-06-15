@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('accounts')}}">Accounts</a>
    </li>
    <li class="active">
        <strong>Invoices & Expenses Account</strong>
    </li>
</ol>
@include('includes.errors')
@include('includes.success')
<div class="row">
    <div class="col-sm-12">
        <div class="invoice_expsense panel panel-primary panel-table">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>Inbound & Outbound</h3>
                </div>
                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                    <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <div id="invoice_expense_bar_chart">
                    @if(isset($InvoiceExpense) && count($InvoiceExpense))
                        <div class="tab-pane active" id="line-chart-2">
                            <div id="bar-chart" class="morrischart" style="height: 300px"></div>
                        </div>
                    @else
                        <center>
                            No Data Found
                        </center>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-3">
        <div class="invoice_expsense panel panel-primary panel-table">
            <div class="panel-body">
                <table class="table table-bordered datatable">
                    <thead>
                    <tr>
                        <th width="30%">Year</th>
                        <th width="30%">Inbound</th>
                        <th width="40%">Outbound</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($InvoiceExpenseYear as $year => $total)
                        <tr>
                            <td>{{$year}}</td>
                            <td>{{$CurrencySymbol}}{{$total['TotalSentAmount']}}</td>
                            <td>{{$CurrencySymbol}}{{$total['TotalReceivedAmount']}}</td>
                        </tr>
                    @endforeach

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
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script type="text/javascript">
    $(function() {
        Highcharts.theme = {
            colors: ['#3366cc', '#dc3912', '#ff9900', '#109618', '#66aa00', '#dd4477','#0099c6', '#990099', '#143DFF']
        };
        // Apply the theme
        Highcharts.setOptions(Highcharts.theme);
    @if(isset($InvoiceExpense) && count($InvoiceExpense))
        $('#bar-chart').highcharts({
            title: {
                text: 'Inbound & Outbound',
                x: -20 //center
            },
            xAxis: {
                categories: [{{implode(',',$cat)}}]
            },
            yAxis: {
                title: {
                    text: 'Amount({{$CurrencySymbol}})'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                valuePrefix: '{{$CurrencySymbol}}'
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
                name: 'Inbound',
                data: [{{implode(',',$inbound)}}]
            }, {
                name: 'Outbound',
                data: [{{implode(',',$outbound)}}]
            }
            ]
        });

        @endif
    });
</script>
@stop