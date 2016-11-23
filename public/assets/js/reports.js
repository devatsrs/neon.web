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
        url: baseurl+'/analysis/getAnalysisData',
        dataType: 'json',
        data:submitdata,
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
function set_search_parameter(submit_form){
    var start_time = ' 00:00:00';
    var end_time = ' 00:00:00';
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
}
function loadBarChart(chart_type,submit_data){
    loading(".bar_chart_"+chart_type,1);
    $.ajax({
        type: 'GET',
        url: baseurl+'/analysis/getAnalysisBarData',
        dataType: 'json',
        data:submit_data,
        aysync: true,
        success: function(data) {
            loading(".bar_chart_"+chart_type,0);
            if(data.categories != '' && data.categories.split(',').length > 0) {
                $('.bar_chart_'+chart_type).highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: data.Title
                    },
                    xAxis: {
                        categories: data.categories.split(','),
                        title: {
                            text: null
                        }
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
                    series: [{
                        name: 'Call Count',
                        data: data.CallCount.split(',').map(parseFloat)
                    }, {
                        name: 'Call Cost',
                        data: data.CallCost.split(',').map(parseFloat)
                    }, {
                        name: 'Call Minutes',
                        data: data.CallMinutes.split(',').map(parseFloat)
                    }]
                });
            }else{
                $('.bar_chart_'+chart_type).html('No Data');
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
}
function loadTable(table_id,pageSize,$searchFilter){
    var TotalCall = 0;
    var TotalDuration = 0;
    var TotalCost = 0;
        data_table  = $(table_id).dataTable({
        "bDestroy": true,
        "bProcessing": true,
        "bServerSide": true,
        "bAutoWidth": false,
        "sAjaxSource": baseurl + "/analysis/ajax_datagrid/type",
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
                {"name": "CurrencyID","value": $searchFilter.CurrencyID}


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
                    chart_type_param = 'CompanyGatewayID='+full[6]+'&';
                }
                if($searchFilter.chart_type == 'account'){
                    delete $searchFilter.AccountID;
                    chart_type_param = 'AccountID='+full[6]+'&';
                }
                if($searchFilter.chart_type != 'destination') {
                    output = '<a href="{url}" target="_blank" >{name}</a>';
                    output = output.replace("{url}", cdr_url + '?' + chart_type_param + $.param($searchFilter));
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

    ],
        "oTableTools": {
        "aButtons": [
            {
                "sExtends": "download",
                "sButtonText": "EXCEL",
                "sUrl": baseurl + "/analysis/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                sButtonClass: "save-collection"
            },
            {
                "sExtends": "download",
                "sButtonText": "CSV",
                "sUrl": baseurl + "/analysis/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                sButtonClass: "save-collection"
            }
        ]
    },
    "fnDrawCallback": function () {
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
            $($(row).children().get(0)).html('<strong>Total</strong>')
            $($(row).children().get(1)).html('<strong>'+TotalCall+'</strong>');
            $($(row).children().get(2)).html('<strong>'+TotalDuration+'</strong>');
            if(TotalCost) {
                $($(row).children().get(3)).html('<strong>' + TotalCost.toFixed(toFixed) + '</strong>');
            }
        }else{
            $(table_id).find('tfoot').find('tr').html('');
        }
    }

    });
    return data_table;
}
function account_expense_chart(submit_data){
    loading("#account_expense_bar_chart",1);
    $.ajax({
        type: 'POST',
        url: baseurl+'/accounts/expense_chart',
        dataType: 'json',
        data:submit_data,
        aysync: true,
        success: function(data) {
            loading("#account_expense_bar_chart",0);
            if(data.categories != '' && data.categories.split(',').length > 0) {
                $('#expense_year_table').find('tbody').html(data.ExpenseYear);
                $('#expense_customer_table').html(data.CustomerActivity);
                $('#expense_vendor_table').html(data.VendorActivity);
                $('#account_expense_bar_chart').highcharts({
                    title: {
                        text: 'Account Activity',
                        x: -20 //center
                    },
                    xAxis: {
                        categories: data.categories.split(',')
                    },
                    yAxis: {
                        title: {
                            text: 'Amount('+CurrencySymbol+')'
                        },
                        plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }]
                    },
                    tooltip: {
                        valuePrefix: CurrencySymbol
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
                        name: 'Customer Activity',
                        data: data.customer.split(',').map(parseFloat)
                    }, {
                        name: 'Vendor Activity',
                        data: data.vendor.split(',').map(parseFloat)
                    }
                    ]
                });
            }else{
                $('#account_expense_bar_chart').html('No Data');
            }
        }
    });
}
