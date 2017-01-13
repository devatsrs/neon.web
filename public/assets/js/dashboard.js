function loadDashboard(){
    jQuery(document).ready(function ($) {

        /* get hourly data for today and display in first bar chart*/
        getHourlyChart();

        if(typeof hidecallmonitor =='undefined') {
            /* get destination data for today and display in pie three chart*/
            getReportData('destination');

            /* get prefix data for today and display in pie three chart*/
            getReportData('prefix');

            /* get trunk data for today and display in three chart*/
            getReportData('trunk');

            /* get trunk data for today and display in three chart*/
            getReportData('account');

            /* get gateway data for today and display in three chart*/
            getReportData('gateway');
        }


        /* get world map*/
        getWorldMap($dashsearchFilter);

        if(typeof retailmonitor != 'undefined' && retailmonitor == 1){
            /* get calls reports for retail*/
            getMostExpensiveCall({});

            /* get calls reports for retail*/
            getMostDailedCall({});

            /* get calls reports for retail*/
            getLogestDurationCall({});
        }

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
                    barWidth: getBarWidth(),
                    barSpacing: 1,
                    tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} ({{value}} Total Sales)',
                    tooltipValueLookups: {
                        names: data.costTitle.split(',')
                    }
                });
                $(".hourly-sales-cost").parent().find('h3').html('Sales '+data.TotalCost)
            }else{
                $(".hourly-sales-cost").html('<h3>No Data</h3>');
            }

            if(parseInt(data.TotalMinutes) > 0) {
                $(".hourly-sales-minutes").sparkline(data.TotalMinutesChart.split(','), {
                    type: 'bar',
                    barColor: '#ec3b83',
                    height: '55px',
                    width: '100%',
                    barWidth: getBarWidth(),
                    barSpacing: 1,
                    tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} ({{value}} Total Minutes)',
                    tooltipValueLookups: {
                        names: data.minutesTitle.split(',')
                    }
                });
                $(".hourly-sales-minutes").parent().find('h3').html('Minutes '+data.TotalMinutes)
            }else{
                $(".hourly-sales-minutes").html('<h3>No Data</h3>');
            }
        }
    });
}
function getBarWidth(){
    var barWidth;
    if($(window).width() >= 1920){
        barWidth = 14;
    }else if ($(window).width() >= 1680 && $(window).width() < 1920){
        barWidth = 10;
    }else if ($(window).width() >= 1024 && $(window).width() < 1680){
        barWidth = 8;
    }else{
        barWidth = 5;
    }
    return barWidth;
}
