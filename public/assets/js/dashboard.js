function loadDashboard(){
    jQuery(document).ready(function ($) {

        /* get hourly data for today and display in first bar chart*/
        getHourlyChart();

        /* get destination data for today and display in pie three chart*/
        getReportData('destination');

        /* get prefix data for today and display in pie three chart*/
        getReportData('prefix');

        /* get trunk data for today and display in three chart*/
        getReportData('trunk');

        /* get gateway data for today and display in three chart*/
        getReportData('gateway');

    });
}
function loading(table,bit){
    var panel = jQuery(table).closest('.loading');
    if(bit==1){
        blockUI(panel);
        panel.addClass('reloading');
    }else{
        unblockUI(panel);
        panel.removeClass('reloading');
    }
}
function getReportData(chart_type){
    loading("."+chart_type+"-call-count-pie-chart",1);
    loading("."+chart_type+"-call-cost-pie-chart",1);
    loading("."+chart_type+"-call-minutes-pie-chart",1);
    $.ajax({
        type: 'GET',
        url: baseurl+'/getReportData',
        dataType: 'json',
        data:$('#hidden_form').serialize()+'&chart_type='+chart_type,
        aysync: true,
        success: function(data) {
            loading("."+chart_type+"-call-count-pie-chart",0);
            loading("."+chart_type+"-call-cost-pie-chart",0);
            loading("."+chart_type+"-call-minutes-pie-chart",0);
            $("."+chart_type+"-call-count-pie-chart").sparkline(data.CallCountVal.split(','), {
                type: 'pie',
                width: '200',
                height: '200',
                sliceColors: data.ChartColors.split(','),
                tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} Total Calls({{value}})',
                tooltipValueLookups: {
                    names: data.CallCount.split(',')
                }

            });
            $("."+chart_type+"-call-count-pie-chart").parent().parent().find('.call_count_desc').html(data.CallCountHtml);
            $("."+chart_type+"-call-cost-pie-chart").sparkline(data.CallCostVal.split(','), {
                type: 'pie',
                width: '200',
                height: '200',
                sliceColors: data.ChartColors.split(','),
                tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} Total Sales({{value}})',
                tooltipValueLookups: {
                    names: data.CallCost.split(',')
                }

            });
            $("."+chart_type+"-call-cost-pie-chart").parent().parent().find('.call_cost_desc').html(data.CallCostHtml);
            $("."+chart_type+"-call-minutes-pie-chart").sparkline(data.CallMinutesVal.split(','), {
                type: 'pie',
                width: '200',
                height: '200',
                sliceColors: data.ChartColors.split(','),
                tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} Total Minutes({{value}})',
                tooltipValueLookups: {
                    names: data.CallMinutes.split(',')
                }

            });
            $("."+chart_type+"-call-minutes-pie-chart").parent().parent().find('.call_minutes_desc').html(data.CallMinutesHtml);

        }
    });
}
function getHourlyChart(){
    loading('.hourly-sales-cost',1);
    loading('.hourly-sales-minutes',1);

    $.ajax({
        type: 'GET',
        url: baseurl+'/getHourlyData',
        dataType: 'json',
        data:$('#hidden_form').serialize(),
        aysync: true,
        success: function(data) {
            loading('.hourly-sales-cost',0);
            loading('.hourly-sales-minutes',0);
            if(data.TotalCost > 0) {
                $(".hourly-sales-cost").sparkline(data.TotalCostChart.split(','), {
                    type: 'bar',
                    barColor: '#ffa812',
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
                $(".hourly-sales-cost").parent().find('h3').html('Sales '+data.TotalCost)
            }else{
                $(".hourly-sales-cost").html('<h3>NO DATA!!</h3>');
            }

            if(parseInt(data.TotalMinutes) > 0) {
                $(".hourly-sales-minutes").sparkline(data.TotalMinutesChart.split(','), {
                    type: 'bar',
                    barColor: '#ec3b83',
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
                $(".hourly-sales-minutes").parent().find('h3').html('Minutes '+data.TotalMinutes)
            }else{
                $(".hourly-sales-minutes").html('<h3>NO DATA!!</h3>');
            }
        }
    });
}