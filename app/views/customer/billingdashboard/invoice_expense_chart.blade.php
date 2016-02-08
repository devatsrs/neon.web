@if(isset($InvoiceExpense) && count($InvoiceExpense))
    <div class="tab-pane active" id="line-chart-2">
        <div id="bar-chart" class="morrischart" style="height: 300px"></div>
    </div>
@else
     <center>
    No Data Found
    </center>
@endif
<script type="text/javascript">
$(function() {

        @if(isset($InvoiceExpense) && count($InvoiceExpense))
                var line_chart_demo_2 = $("#bar-chart");
                var sales_data_2 =  [];
                    <?php $i=0; ?>
                    @foreach($InvoiceExpense as $key => $InvoiceExpenseRow)

                        @if(isset($InvoiceExpenseRow->MonthName) && isset($InvoiceExpenseRow->PaymentReceived) && isset($InvoiceExpenseRow->TotalInvoice) && isset($InvoiceExpenseRow->TotalOutstanding))

                         sales_data_2['{{$i++}}'] =
                            {
                             x: '{{$InvoiceExpenseRow->MonthName}}',
                             y: '{{$InvoiceExpenseRow->PaymentReceived}}',
                             z: '{{$InvoiceExpenseRow->TotalInvoice}}',
                             a: '{{$InvoiceExpenseRow->TotalOutstanding}}'
                             }
                        @endif
                    @endforeach
            Morris.Bar({
                        element: 'bar-chart',
                        data: sales_data_2,
                        xkey: 'x',
                        ykeys: ['y','z','a'],
                        labels:['Payment Received','Total Invoice','Total Outstanding'],
                        barColors: ['#3399FF', '#333399', '#3366CC']

                    });
            line_chart_demo_2.parent().attr('style', '');
        @endif
});
</script>