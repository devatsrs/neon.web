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
                    <h3>Invoices & Expenses</h3>
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
                        <th width="30%">Invoice Sent</th>
                        <th width="40%">Invoice Received</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($InvoiceExpenseYear as $year => $total)
                        <tr>
                            <td>{{$year}}</td>
                            <td>{{$total['TotalSentAmount']}}</td>
                            <td>{{$total['TotalReceivedAmount']}}</td>
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
<script type="text/javascript">
    $(function() {
    @if(isset($InvoiceExpense) && count($InvoiceExpense))
        var line_chart_demo_2 = $("#bar-chart");
        var sales_data_2 =  [];
        <?php $i=0; ?>
        @foreach($InvoiceExpense as $key => $InvoiceExpenseRow)

            @if(isset($InvoiceExpenseRow->TotalSentAmount) && isset($InvoiceExpenseRow->TotalReceivedAmount))

                sales_data_2[{{$i++}}] =
                {
                    x: '{{$InvoiceExpenseRow->Year.' - '.$InvoiceExpenseRow->Month}}',
                    y: {{$InvoiceExpenseRow->TotalSentAmount}},
                    z: {{$InvoiceExpenseRow->TotalReceivedAmount}}
                }
            @endif
        @endforeach
        Morris.Bar({
            element: 'bar-chart',
            data: sales_data_2,
            xkey: 'x',
            ykeys: ['y','z'],
            labels:['Invoice Sent','Invoice Received'],
            barColors: ['#3399FF', '#333399']


        });
        line_chart_demo_2.parent().attr('style', '');
        @endif
    });
</script>
@stop