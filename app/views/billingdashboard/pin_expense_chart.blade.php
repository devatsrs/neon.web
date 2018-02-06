@if(isset($top_pincode_data) && count($top_pincode_data))
    <div class="tab-pane active" id="line-chart-2">
        <div id="pin-bar-chart" class="morrischart" style="height: 300px"></div>
    </div>
@else
     <center>
        @lang('routes.MESSAGE_DATA_NOT_AVAILABLE')
    </center>
@endif
<script type="text/javascript">
$(function() {

        @if(isset($top_pincode_data) && count($top_pincode_data))
                var line_chart_demo_3 = $("#pin-bar-chart");
                var sales_data_3 =  [];
                    <?php $i=0; ?>
                    @foreach($top_pincode_data as $key => $top_pincode_data_row)

                        @if(isset($top_pincode_data_row->PincodeValue) && isset($top_pincode_data_row->Pincode))

                            sales_data_3['{{$i++}}'] =
                            {
                             x: '{{$top_pincode_data_row->Pincode}}',
                             y: '{{$top_pincode_data_row->PincodeValue}}'
                             }
                        @endif
                    @endforeach
            Morris.Bar({
                        element: 'pin-bar-chart',
                        data: sales_data_3,
                        xkey: 'x',
                        ykeys: ['y'],
                        labels:['{{$report_label}}'],
                        barColors: ['#3399FF'],
                            hoverCallback:function (index, options, content, row) {
                                if('{{$report_label}}' ==  'Extension Cost' || '{{$report_label}}' ==  'Pin Cost') {
                                    return '<div class="morris-hover-row-label">' + row.x + '</div><div style="color: #3399FF" class="morris-hover-point">{{$report_label}}: {{$CurrencySymbol}}' + row.y + '</div>'
                                }else{
                                    return '<div class="morris-hover-row-label">' + row.x + '</div><div style="color: #3399FF" class="morris-hover-point">{{$report_label}}: ' + row.y + '</div>'
                                }
                            }

                    }).on('click', function(i, row){
                            if(sales_data_3[i].x) {
                                dataGrid(sales_data_3[i].x,'{{$data['Startdate']}}','{{$data['Enddate']}}','{{$data['PinExt']}}','{{$data['CurrencyID']}}');
                            }
                    });
    line_chart_demo_3.parent().attr('style', '');

        @endif
});
</script>