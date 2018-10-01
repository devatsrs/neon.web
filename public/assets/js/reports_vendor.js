var char_title = [];
var ajax_running = 0;
Date.prototype.addDays = function(days)
{
    var dat = new Date(this.valueOf());
    dat.setDate(dat.getDate() + days);
    return dat;
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
function getAnalysisData(chart_type,submitdata){
    loading("."+chart_type+"-call-count-pie-chart",1);
    loading("."+chart_type+"-call-cost-pie-chart",1);
    loading("."+chart_type+"-call-minutes-pie-chart",1);
    $.ajax({
        type: 'GET',
        url: baseurl+'/vendor_analysis/getAnalysisData',
        dataType: 'json',
        data:submitdata,
        aysync: true,
        success: function(result) {
            loading("."+chart_type+"-call-count-pie-chart",0);
            loading("."+chart_type+"-call-cost-pie-chart",0);
            loading("."+chart_type+"-call-minutes-pie-chart",0);
            var HTML_LBL=result.html;
            var data=result.data;

            $("."+chart_type+"-call-count-pie-chart").sparkline(data.CallCountVal.split(','), {
                type: 'pie',
                width: '200',
                height: '200',
                sliceColors: data.ChartColors.split(','),
                tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} '+HTML_LBL.total_calls+'({{value}})',
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
                tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} '+HTML_LBL.total_sales+'({{value}})',
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
                tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}} '+HTML_LBL.total_minutes+'({{value}})',
                tooltipValueLookups: {
                    names: data.CallMinutes.split(',')
                }

            });
            $("."+chart_type+"-call-minutes-pie-chart").parent().parent().find('.call_minutes_desc').html(data.CallMinutesHtml);

        }
    });
}
function set_search_parameter(submit_form){
    var start_time = ' 00:00:00';
    var end_time = ' 23:59:59';
    if($(submit_form).find("[name='StartHour']").attr('disabled') !== 'disabled'){
        if($(submit_form).find("[name='StartHour']").val() != ''){
            start_time = ' '+$(submit_form).find("[name='StartHour']").val()+':00';
        }
        if($(submit_form).find("[name='EndHour']").val() != ''){
            end_time = ' '+$(submit_form).find("[name='EndHour']").val()+':00';
        }
    }
    $searchFilter.StartDate = $(submit_form).find("input[name='StartDate']").val()+start_time;
    $searchFilter.EndDate = $(submit_form).find("input[name='EndDate']").val()+end_time;
    $searchFilter.UserID = $(submit_form).find("[name='UserID']").val();
    $searchFilter.Admin   = $(submit_form).find("input[name='Admin']").val();
    $searchFilter.chart_type   = $(submit_form).find("input[name='chart_type']").val();
    $searchFilter.AccountID = $(submit_form).find("[name='AccountID']").val();
    $searchFilter.CompanyGatewayID = $(submit_form).find("[name='CompanyGatewayID']").val();
    $searchFilter.CountryID = $(submit_form).find("[name='CountryID']").val();
    $searchFilter.Prefix = $(submit_form).find("input[name='Prefix']").val();
    $searchFilter.TrunkID = $(submit_form).find("[name='TrunkID']").val();
    $searchFilter.CurrencyID = $(submit_form).find("[name='CurrencyID']").val();
    $searchFilter.TimeZone = $(submit_form).find("[name='TimeZone']").val();
    $searchFilter.tag = $(submit_form).find("[name='tag']").val();
}
function loadBarChart(chart_type,submit_data){
    loading(".bar_chart",1);
    var seriesdata = [];
    var searchdates = {};
    $.ajax({
        type: 'GET',
        url: baseurl+'/vendor_analysis/getAnalysisBarData',
        dataType: 'json',
        data:submit_data,
        aysync: true,
        success: function(data) {
            loading(".bar_chart",0);
            if(data.series != '' && data.series.length > 0) {
				char_title.length = 0;
				char_title.push(data.Title);
                seriesdata = JSON.parse(JSON.stringify(data.series));
                $('.bar_chart').highcharts({
                    chart: {
                        type: 'column',
                        events: {
                            drilldown: function (e) {
                                if (!e.seriesOptions) {
                                    var chart = this;
									if(ajax_running == 0){
                                        ajax_running = 1;
                                        getChartData(submit_data,chart,e,searchdates);
                                    }                                    
                                }
                            },
							drillup: function (e) {
								var chart = this;
								char_title.pop();
								chart.setTitle({ text: char_title[char_title.length -1]});																
							}
                        }
                    },
                    title: {
                        text: data.Title
                    },
                    xAxis: {
                        type: 'category'
                        /*categories: data.categories.split(','),
                         title: {
                         text: null
                         }*/
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Calls',
                            align: 'high'
                        },
                        labels: {
                            overflow: 'justify'
                        }
                    },
                    tooltip: {
                        valueSuffix: ''
                    },
                    plotOptions: {
                        bar: {
                            dataLabels: {
                                enabled: true
                            }
                        },
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0
                        }
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'top',
                        x: -40,
                        y: 80,
                        floating: true,
                        borderWidth: 1,
                        backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                        shadow: true
                    },
                    credits: {
                        enabled: false
                    },
                    series: seriesdata,
                    drilldown: {
                        drillUpButton: {
                            relativeTo: 'spacingBox',
                            position: {
                                y: 0,
                                x: -30
                            }
                        },
                        series: []
                    }
                });
            }else{
                $('.bar_chart').html(MSG_DATA_NOT_AVAILABLE);
            }
        }
    });
}

function reloadCharts(table_id,pageSize,$searchFilter){

    /* get destination data for today and display in pie three chart*/
    getAnalysisData($searchFilter.chart_type,$searchFilter);

    /* get data by time in bar chart*/
    loadBarChart($searchFilter.chart_type,$searchFilter);

    /* load grid data table*/
    loadTable(table_id,pageSize,$searchFilter);

    /* get world map*/
    getWorldMap($searchFilter);
}
function loadTable(table_id,pageSize,$searchFilter){
    var TotalCall = 0;
    var TotalDuration = 0;
    var TotalCost = 0;
    var TotalMargin = 0;
        data_table  = $(table_id).dataTable({
        "oLanguage": {
            "sUrl": baseurl + "/translate/datatable_Label"
        },
        "bDestroy": true,
        "bProcessing": true,
        "bServerSide": true,
        "bAutoWidth": false,
        "sAjaxSource": baseurl + "/vendor_analysis/ajax_datagrid/type",
        "fnServerParams": function (aoData) {
            aoData.push(

                {"name": "StartDate", "value": $searchFilter.StartDate},
                {"name": "EndDate","value": $searchFilter.EndDate},
                {"name": "UserID","value": $searchFilter.UserID},
                {"name": "Admin","value": $searchFilter.Admin},
                {"name": "chart_type","value": $searchFilter.chart_type},
                {"name": "AccountID","value": $searchFilter.AccountID},
                {"name": "CompanyGatewayID","value": $searchFilter.CompanyGatewayID},
                {"name": "CountryID","value": $searchFilter.CountryID},
                {"name": "Prefix","value": $searchFilter.Prefix},
                {"name": "TrunkID","value": $searchFilter.TrunkID},
                {"name": "TimeZone","value": $searchFilter.TimeZone},
                {"name": "CurrencyID","value": $searchFilter.CurrencyID},
                {"name": "tag","value": $searchFilter.tag}


            );
            data_table_extra_params.length = 0;
            data_table_extra_params.push(
                {"name": "StartDate", "value": $searchFilter.StartDate},
                {"name": "EndDate","value": $searchFilter.EndDate},
                {"name": "UserID","value": $searchFilter.UserID},
                {"name": "Admin","value": $searchFilter.Admin},
                {"name": "chart_type","value": $searchFilter.chart_type},
                {"name": "AccountID","value": $searchFilter.AccountID},
                {"name": "CompanyGatewayID","value": $searchFilter.CompanyGatewayID},
                {"name": "CountryID","value": $searchFilter.CountryID},
                {"name": "Prefix","value": $searchFilter.Prefix},
                {"name": "TrunkID","value": $searchFilter.TrunkID},
                {"name": "TimeZone","value": $searchFilter.TimeZone},
                {"name": "CurrencyID","value": $searchFilter.CurrencyID},
                {"name": "tag","value": $searchFilter.tag},
                {"name":"Export","value":1});

        },
        "iDisplayLength": pageSize,
        "sPaginationType": "bootstrap",
        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
        "aaSorting": [[1, 'desc']],
        "aoColumns": [
        {  "bSortable": true ,
            mRender:function( id, type, full){
                var output,chart_type_param;

                if($searchFilter.chart_type == 'trunk' || $searchFilter.chart_type == 'prefix'){
                    chart_type_param = $searchFilter.chart_type+'='+id+'&';
                }
                if($searchFilter.chart_type == 'gateway'){
                    delete $searchFilter.CompanyGatewayID;
                    chart_type_param = 'CompanyGatewayID='+full[8]+'&';
                }
                if($searchFilter.chart_type == 'account'){
                    delete $searchFilter.AccountID;
                    chart_type_param = 'AccountID='+full[8]+'&';
                }
                if($searchFilter.chart_type != 'destination' && $searchFilter.chart_type != 'description') {
                    jQuery.each($searchFilter, function(index, item) {
                        var cdr_query_parama = ['prefix','StartDate','EndDate','AccountID','CompanyGatewayID','Prefix','TrunkID','CurrencyID'];
                        if(jQuery.inArray(index, cdr_query_parama) !== -1){
                            chart_type_param += index+'='+item+'&'
                        }
                    });
                    output = '<a href="{url}" target="_blank" >{name}</a>';
                    output = output.replace("{url}", cdr_url + '?' + chart_type_param.slice(0, -1));
                    output = output.replace("{name}", id);
                }else{
                    output = id;
                }
                return output;
            }
        },  // 3 StartDate
        {  "bSortable": true },  // 3 StartDate
        {  "bSortable": true },  // 3 StartDate
        {  "bSortable": true },  // 3 StartDate
        {  "bSortable": true },  // 3 StartDate
        {  "bSortable": true },  // 3 StartDate
        {  "bSortable": true },  // 3 StartDate
        {  "bSortable": true }  // 3 StartDate

    ],
        "oTableTools": {
        "aButtons": [
            {
                "sExtends": "download",
                "sButtonText": BUTTON_EXPORT_EXCEL_CAPTION,
                "sUrl": baseurl + "/vendor_analysis/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                sButtonClass: "save-collection"
            },
            {
                "sExtends": "download",
                "sButtonText": BUTTON_EXPORT_CSV_CAPTION,
                "sUrl": baseurl + "/vendor_analysis/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                sButtonClass: "save-collection"
            }
        ]
    },
    "fnDrawCallback": function () {
        checkrmargindisplay(table_id);
        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });
    },
    "fnServerData": function ( sSource, aoData, fnCallback ) {
        /* Add some extra data to the sender */
        $.getJSON( sSource, aoData, function (json) {
            /* Do whatever additional processing you want on the callback, then tell DataTables */
            TotalCall = json.Total.TotalCall;
            TotalDuration = json.Total.TotalDuration;
            TotalCost = json.Total.TotalCost;
            TotalMargin = json.Total.TotalMargin;
            fnCallback(json)
        });
    },
    "fnFooterCallback": function ( row, data, start, end, display ) {
        if (end > 0) {
            $(row).html('');
            for (var i = 0; i < data[0].length; i++) {
                var a = document.createElement('td');
                $(a).html('');
                $(row).append(a);
            }
            $($(row).children().get(0)).html('<strong>'+TABLE_TOTAL+'</strong>')
            $($(row).children().get(1)).html('<strong>'+TotalCall+'</strong>');
            $($(row).children().get(2)).html('<strong>'+TotalDuration+'</strong>');
            if(TotalCost) {
                $($(row).children().get(3)).html('<strong>' + TotalCost + '</strong>');
            }
            if(TotalMargin) {
                $($(row).children().get(6)).html('<strong>' + TotalMargin + '</strong>');
            }

        }else{
            $(table_id).find('tfoot').find('tr').html('');
        }
    }

    });
    return data_table;
}
function getChartData(submit_data,chart,e,searchdates){
    // Show the loading label
    chart.showLoading('Loading ...');
    getDates(submit_data,e,searchdates);
    $.ajax({
        type: 'GET',
        url: baseurl+'/vendor_analysis/getAnalysisBarData',
        dataType: 'json',
        data:submit_data,
        aysync: true,
        success: function(data) {
			ajax_running = 0;
            if(data.series != '') {
				chart.setTitle({ text: data.Title});
				char_title.push(data.Title);
				char_title.push(data.Title);
				char_title.push(data.Title);
                var drilldowns = JSON.parse(JSON.stringify(data.series));
                var series_1 = drilldowns[0];
                var series_2 = drilldowns[1];
                var series_3 = drilldowns[2];
                chart.addSingleSeriesAsDrilldown(e.point, series_1);
                chart.addSingleSeriesAsDrilldown(e.point, series_2);
                chart.addSingleSeriesAsDrilldown(e.point, series_3);
                chart.hideLoading();
                chart.applyDrilldown();
            }
        }
    });

}
function w2date(year, wn){
    var Day10 = new Date( year,0,10,12,0,0),
        Day4 = new Date( year,0,4,12,0,0),
        weekmSec = Day4.getTime() - Day10.getDay() * 86400000;  // 7 days in milli sec
    return new Date(weekmSec + ((wn - 1)  * 7 ) * 86400000);
}
function getDates(submit_data,row,searchdates) {
    var start_time = ' 00:00:00',end_time = ' 23:59:59';
    var StartDate,EndDate,minDate,maxDate;
    var MonthsName = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var arr = row.point.name.toString().split('-');
    if(typeof searchdates.StartDate == 'undefined') {
        searchdates.StartDate = submit_data.StartDate;
        searchdates.EndDate = submit_data.EndDate;
    }

    if ($("[name='StartHour']").attr('disabled') !== 'disabled') {
        if ($("[name='StartHour']").val() != '') {
            start_time = ' ' + $("[name='StartHour']").val() + ':00';
        }
        if ($("[name='EndHour']").val() != '') {
            end_time = ' ' + $("[name='EndHour']").val() + ':00';
        }
    }

    minDate = new Date(searchdates.StartDate);
    maxDate = new Date(searchdates.EndDate);

    if (arr.length && arr.length == 3 && arr[0] !='Q') {
        console.log(' daily '+arr[0]);
        date = new Date(row.point.name);
        date2 = new Date(row.point.name);
    } else if (arr.length == 2 && $.inArray(arr[0],MonthsName) == -1) {
        console.log(' week '+arr[0]);
        var enddate = w2date(arr[1], parseInt(arr[0]) + parseInt(1));
        date = w2date(arr[1], arr[0]);
        date2 = enddate.addDays(-1);
    } else if ($.inArray(arr[0],MonthsName) != -1) {
        console.log(' month '+arr[0]);
        date = new Date(arr[0] + " 01, " + arr[1]);
        date2 = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    } else if (arr.length == 3 && arr[0] =='Q') {
        console.log(' quarter '+arr[0]);
        if(arr[1]== 1){
            date = new Date(arr[2] + '-01-01');
            date2 = new Date(arr[2] + '-03-31');
        }else if(arr[1]== 2){
            date = new Date(arr[2] + '-04-01');
            date2 = new Date(arr[2] + '-06-30');
        }else if(arr[1]== 3){
            date = new Date(arr[2] + '-07-01');
            date2 = new Date(arr[2] + '-09-30');
        }else if(arr[1]== 4){
            date = new Date(arr[2] + '-10-01');
            date2 = new Date(arr[2] + '-12-31');
        }
    } else if (arr.length == 1) {
        console.log(' year '+arr[0]);
        date = new Date(arr[0] + '-01-01');
        date2 = new Date(arr[0] + '-12-31');
    }
    if (minDate > date) {
        date = minDate;
    }
    if (maxDate < date2) {
        date2 = maxDate;
    }
    StartDate = date.getFullYear() + '-' + ( date.getMonth() + 1) + '-' + date.getDate();
    EndDate = date2.getFullYear() + '-' + ( date2.getMonth() + 1) + '-' + date2.getDate();
    console.log(' start date 1 ' + submit_data.StartDate);
    console.log(' end date 1 ' + submit_data.EndDate);
    submit_data.StartDate = StartDate + start_time;
    submit_data.EndDate = EndDate + end_time;
    console.log(' start date 2 ' + submit_data.StartDate);
    console.log(' end date 2 ' + submit_data.EndDate);
}

function checkrmargindisplay(table_id){
    if(customer_login == 1){
        $(table_id+" td:nth-child(7)").hide();
        $(table_id+" th:nth-child(7)").hide();
        $(table_id+" td:nth-child(8)").hide();
        $(table_id+" th:nth-child(8)").hide();
    }else{
        $(table_id+" td:nth-child(7)").show();
        $(table_id+" th:nth-child(7)").show();
        $(table_id+" td:nth-child(8)").show();
        $(table_id+" th:nth-child(8)").show();
    }
}