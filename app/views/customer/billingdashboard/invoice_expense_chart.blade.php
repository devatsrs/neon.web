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
                        labels:['Payment Deposited','Total Invoice','Total Outstanding'],
                        barColors: ['#3399FF', '#333399', '#3366CC'],
                            hoverCallback:function (index, options, content, row) {
                                var StartDate =row.x.split('/')[1]+'-'+row.x.split('/')[0]+'-01';
                                var lastday = new Date(2008, row.x.split('/')[0], 0).getDate();
                                var EndDate =row.x.split('/')[1]+'-'+row.x.split('/')[0]+'-'+lastday;
                                var Currency = $('[name="CurrencyID"]').val();
                                return '<div class="morris-hover-row-label">'+
                                        row.x+
                                        '</div>' +
                                        '<div  class="morris-hover-point">' +
                                        '   <a  style="color: #3399FF" class="paymentReceived" data-startdate="'+StartDate+'" data-enddate="'+EndDate+'" data-currency="'+Currency+'" href="javascript:void(0)">Payment Received: {{$CurrencySymbol}}'+row.y+'</a>' +
                                        '</div>' +
                                        '<div  class="morris-hover-point">' +
                                        '   <a style="color: #333399" class="totalInvoice" data-startdate="'+StartDate+'" data-enddate="'+EndDate+'" data-currency="'+Currency+'" href="javascript:void(0)">Total Invoice: {{$CurrencySymbol}}'+row.z+'</a>' +
                                        '</div>' +
                                        '<div  class="morris-hover-point">' +
                                        '   <a style="color: #3366CC" class="totalOutstanding" data-startdate="'+StartDate+'" data-enddate="'+EndDate+'" data-currency="'+Currency+'" href="javascript:void(0)">Total Outstanding: {{$CurrencySymbol}}'+row.a+'</a>' +
                                        '</div>';
                                //return '<div class="morris-hover-row-label">'+row.x+'</div><div  class="morris-hover-point"><a  style="color: #3399FF" target="_blank" href="'+baseurl+'/customer/payments?Type=Payment In&StartDate='+StartDate+'&EndDate='+EndDate+'">Payment Deposited: {{$CurrencySymbol}}'+row.y+'</a></div><div  class="morris-hover-point"><a style="color: #333399" target="_blank" href="'+baseurl+'/customer/invoice?StartDate='+StartDate+'&EndDate='+EndDate+'&InvoiceType=1">Total Invoice: {{$CurrencySymbol}}'+row.z+'</a></div><div  class="morris-hover-point"><a style="color: #3366CC" target="_blank" href="'+baseurl+'/customer/invoice?StartDate='+StartDate+'&EndDate='+EndDate+'&InvoiceType=1">Total Outstanding: {{$CurrencySymbol}}'+row.a+'</a></div>'
                            }

                    });
            line_chart_demo_2.parent().attr('style', '');
        @endif
});
</script>