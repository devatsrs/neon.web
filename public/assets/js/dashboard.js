function loadDashboard(){
    jQuery(document).ready(function ($) {

        /* get hourly data for today and display in first bar chart*/
        $.ajax({
            type: 'GET',
            url: baseurl+'/getHourlyData',
            dataType: 'json',
            data:$('#hidden_form').serialize(),
            aysync: true,
            success: function(data) {
                if(data.TotalCost > 0) {
                    $(".hourly-sales-cost").sparkline(data.TotalCostChart.split(','), {
                        type: 'bar',
                        barColor: '#333399',
                        height: '55px',
                        width: '100%',
                        barWidth: 14,
                        barSpacing: 1,
                        tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} ({{value}} Total Sales)',
                        tooltipValueLookups: {
                            names: {
                                0: '0 Hour',
                                1: '1 Hour',
                                2: '2 Hour',
                                3: '3 Hour',
                                4: '4 Hour',
                                5: '5 Hour',
                                6: '6 Hour',
                                7: '7 Hour',
                                8: '8 Hour',
                                9: '9 Hour',
                                10: '10 Hour',
                                11: '11 Hour',
                                12: '12 Hour',
                                13: '13 Hour',
                                14: '14 Hour',
                                15: '15 Hour',
                                16: '16 Hour',
                                17: '17 Hour',
                                18: '18 Hour',
                                19: '19 Hour',
                                20: '20 Hour',
                                21: '21 Hour',
                                22: '22 Hour',
                                23: '23 Hour'
                            }
                        }
                    });
                    $(".hourly-sales-cost").parent().find('h3').html('Total Sales '+data.TotalCost)
                }else{
                    $(".hourly-sales-cost").html('<h3>NO DATA!!</h3>');
                }

                if(parseInt(data.TotalMinutes) > 0) {
                    $(".hourly-sales-minutes").sparkline(data.TotalMinutesChart.split(','), {
                        type: 'bar',
                        barColor: '#3399FF',
                        height: '55px',
                        width: '100%',
                        barWidth: 14,
                        barSpacing: 1,
                        tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} ({{value}} Total Minutes)',
                        tooltipValueLookups: {
                            names: {
                                0: '0 Hour',
                                1: '1 Hour',
                                2: '2 Hour',
                                3: '3 Hour',
                                4: '4 Hour',
                                5: '5 Hour',
                                6: '6 Hour',
                                7: '7 Hour',
                                8: '8 Hour',
                                9: '9 Hour',
                                10: '10 Hour',
                                11: '11 Hour',
                                12: '12 Hour',
                                13: '13 Hour',
                                14: '14 Hour',
                                15: '15 Hour',
                                16: '16 Hour',
                                17: '17 Hour',
                                18: '18 Hour',
                                19: '19 Hour',
                                20: '20 Hour',
                                21: '21 Hour',
                                22: '22 Hour',
                                23: '23 Hour'
                            }
                        }
                    });
                    $(".hourly-sales-minutes").parent().find('h3').html('Total Minutes '+data.TotalMinutes)
                }else{
                    $(".hourly-sales-minutes").html('<h3>NO DATA!!</h3>');
                }
            }
        });
        /* get trunk data for today and display in pie chart*/
        $.ajax({
            type: 'GET',
            url: baseurl+'/getTrunkData',
            dataType: 'json',
            data:$('#hidden_form').serialize(),
            aysync: true,
            success: function(data) {
                $(".trunk-pie-chart").sparkline(data.TrunkReport.split(','), {
                    type: 'pie',
                    width: '115',
                    height: '115',
                    sliceColors: ['#3399FF', '#333399','#3366CC'],
                    tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} Total Sales({{value}})',
                    tooltipValueLookups: {
                        names: {
                            0: data.FirstTrunk,
                            1: data.SecondTrunk,
                            2: 'Other'
                        }
                    }

                });
                $(".trunk-pie-chart").parent().find('.trunk_desc').html(data.TrunkHtml);

            }
        });
        /* get gateway data for today and display in pie two chart*/
        $.ajax({
            type: 'GET',
            url: baseurl+'/getGatewayData',
            dataType: 'json',
            data:$('#hidden_form').serialize(),
            aysync: true,
            success: function(data) {
                $(".gateway-pie-chart").sparkline(data.GatewayCost.split(','), {
                    type: 'pie',
                    width: '115',
                    height: '115',
                    sliceColors: data.GatewayColors.split(','),
                    tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} Total Sales({{value}})',
                    tooltipValueLookups: {
                        names: data.GatewayNames.split(',')
                    }

                });
                $(".gateway-pie-chart").parent().find('.gateway_desc').html(data.GatewayHtml);

            }
        });

        /* get prefix data for today and display in pie three chart*/
        $.ajax({
            type: 'GET',
            url: baseurl+'/getPrefixData',
            dataType: 'json',
            data:$('#hidden_form').serialize(),
            aysync: true,
            success: function(data) {
                $(".prefix-call-count-pie-chart").sparkline(data.PrefixCallCountVal.split(','), {
                    type: 'pie',
                    width: '200',
                    height: '200',
                    sliceColors: data.PrefixColors.split(','),
                    tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} Total Calls({{value}})',
                    tooltipValueLookups: {
                        names: data.PrefixCallCount.split(',')
                    }

                });
                $(".prefix-call-count-pie-chart").parent().parent().find('.call_count_desc').html(data.PrefixCallCountHtml);
                $(".prefix-call-cost-pie-chart").sparkline(data.PrefixCallCostVal.split(','), {
                    type: 'pie',
                    width: '200',
                    height: '200',
                    sliceColors: data.PrefixColors.split(','),
                    tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} Total Sales({{value}})',
                    tooltipValueLookups: {
                        names: data.PrefixCallCost.split(',')
                    }

                });
                $(".prefix-call-cost-pie-chart").parent().parent().find('.call_cost_desc').html(data.PrefixCallCostHtml);
                $(".prefix-call-minutes-pie-chart").sparkline(data.PrefixCallMinutesVal.split(','), {
                    type: 'pie',
                    width: '200',
                    height: '200',
                    sliceColors: data.PrefixColors.split(','),
                    tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} Total Minutes({{value}})',
                    tooltipValueLookups: {
                        names: data.PrefixCallMinutes.split(',')
                    }

                });
                $(".prefix-call-minutes-pie-chart").parent().parent().find('.call_minutes_desc').html(data.PrefixCallMinutesHtml);

            }
        });
    });
}