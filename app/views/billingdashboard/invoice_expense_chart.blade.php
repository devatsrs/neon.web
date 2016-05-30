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
                        barColors: ['#3399FF', '#333399', '#3366CC'],
                            hoverCallback:function (index, options, content, row) {
                                var StartDate =row.x.split('/')[1]+'-'+row.x.split('/')[0]+'-01';
                                var lastday = new Date(2008, row.x.split('/')[0], 0).getDate();
                                var EndDate =row.x.split('/')[1]+'-'+row.x.split('/')[0]+'-'+lastday;
                                return '<div class="morris-hover-row-label">'+row.x+'</div><div style="color: #3399FF" class="morris-hover-point"><a target="_blank" href="'+baseurl+'/payments?StartDate='+StartDate+'&EndDate='+EndDate+'&Status=Approved&CurrencyID={{Input::get('CurrencyID')}}">Payment Received: {{$CurrencySymbol}}'+row.y+'</a></div><div style="color: #333399" class="morris-hover-point"><a target="_blank" href="'+baseurl+'/invoice?StartDate='+StartDate+'&EndDate='+EndDate+'&zerovalueinvoice=1&InvoiceType=1&CurrencyID={{Input::get('CurrencyID')}}">Total Invoice: {{$CurrencySymbol}}'+row.z+'</a></div><div style="color: #3366CC" class="morris-hover-point"><a target="_blank" href="'+baseurl+'/invoice?StartDate='+StartDate+'&EndDate='+EndDate+'&InvoiceStatus=send,awaiting,partially_paid&zerovalueinvoice=1&CurrencyID={{Input::get('CurrencyID')}}&InvoiceType=1">Total Outstanding: {{$CurrencySymbol}}'+row.a+'</a></div>'
                            }

                    });
            line_chart_demo_2.parent().attr('style', '');
        @endif
});
</script>