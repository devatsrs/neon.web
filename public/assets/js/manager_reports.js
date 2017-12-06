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

function set_search_parameter(submit_form){
    $searchFilter.StartDate = $(submit_form).find("input[name='StartDate']").val();
    $searchFilter.EndDate = $(submit_form).find("input[name='EndDate']").val();
    $searchFilter.CurrencyID = $(submit_form).find("[name='CurrencyID']").val();
    $searchFilter.Admin = $(submit_form).find("input[name='Admin']").val();
    $searchFilter.UserID = $(submit_form).find("[name='UsersID[]']").val();
    $searchFilter.RevenueListType = $("[name='RevenueListType']:checked").val();
    $searchFilter.MarginListType = $("[name='MarginListType']:checked").val();
    $searchFilter.AccountListType = $("[name='AccountListType']:checked").val();
    $searchFilter.ActiveAccount = $("[name='ActiveAccount']:checked").val();
    $searchFilter.ActiveLead = $("[name='ActiveLead']:checked").val();

}

function reloadCharts(table_id,pageSize,$searchFilter){
    loadLeads('#leads',10,$searchFilter);
    loadAccounts('#accounts',10,$searchFilter);
    loadAccountManagerRevenue('#AccountManagerRevenue',10,$searchFilter);
    loadAccountManagerMargin('#AccountManagerMargin',10,$searchFilter);
    loadRevenueChart($searchFilter);
    loadMarginChart($searchFilter);
    loadAccountRevenueMargin('#AccountMargin',10,$searchFilter);
}
function loadAccounts(table_id,pageSize,$searchFilter){
        data_table  = $(table_id).dataTable({
        "bDestroy": true,
        "bProcessing": true,
        "bServerSide": true,
        "bAutoWidth": false,
        "sAjaxSource": baseurl + "/analysis/get_account/type",
        "fnServerParams": function (aoData) {
            aoData.push(

                {"name": "StartDate", "value": $searchFilter.StartDate},
                {"name": "EndDate","value": $searchFilter.EndDate},
                {"name": "UserID","value": $searchFilter.UserID},
                {"name": "ActiveAccount","value": $searchFilter.ActiveAccount},
                {"name": "Admin","value": $searchFilter.Admin},
                {"name": "CurrencyID","value": $searchFilter.CurrencyID}


            );
            data_table_extra_params.length = 0;
            data_table_extra_params.push(
                {"name": "StartDate", "value": $searchFilter.StartDate},
                {"name": "EndDate","value": $searchFilter.EndDate},
                {"name": "UserID","value": $searchFilter.UserID},
                {"name": "ActiveAccount","value": $searchFilter.ActiveAccount},
                {"name": "Admin","value": $searchFilter.Admin},
                {"name": "CurrencyID","value": $searchFilter.CurrencyID},
                {"name":"Export","value":1});

        },
        "iDisplayLength": pageSize,
        "sPaginationType": "bootstrap",
        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
        "aaSorting": [[0, 'asc']],
        "aoColumns": [


            {  "bSortable": true },  // 3 StartDate
            {  "bSortable": true },  // 3 StartDate
            {  "bSortable": true }  // 3 StartDate

        ],
        "oTableTools": {
        "aButtons": [
            {
                "sExtends": "download",
                "sButtonText": "EXCEL",
                "sUrl": baseurl + "/analysis/get_account/xlsx", //baseurl + "/generate_xlsx.php",
                sButtonClass: "save-collection"
            },
            {
                "sExtends": "download",
                "sButtonText": "CSV",
                "sUrl": baseurl + "/analysis/get_account/csv", //baseurl + "/generate_csv.php",
                sButtonClass: "save-collection"
            }
        ]
    },
    "fnDrawCallback": function () {
        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });

    }
    });
    return data_table;
}
function loadLeads(table_id,pageSize,$searchFilter){
    data_table  = $(table_id).dataTable({
        "bDestroy": true,
        "bProcessing": true,
        "bServerSide": true,
        "bAutoWidth": false,
        "sAjaxSource": baseurl + "/analysis/get_leads/type",
        "fnServerParams": function (aoData) {
            aoData.push(

                {"name": "StartDate", "value": $searchFilter.StartDate},
                {"name": "EndDate","value": $searchFilter.EndDate},
                {"name": "UserID","value": $searchFilter.UserID},
                {"name": "ActiveLead","value": $searchFilter.ActiveLead},
                {"name": "Admin","value": $searchFilter.Admin},
                {"name": "CurrencyID","value": $searchFilter.CurrencyID}


            );
            data_table_extra_params.length = 0;
            data_table_extra_params.push(
                {"name": "StartDate", "value": $searchFilter.StartDate},
                {"name": "EndDate","value": $searchFilter.EndDate},
                {"name": "UserID","value": $searchFilter.UserID},
                {"name": "ActiveLead","value": $searchFilter.ActiveLead},
                {"name": "Admin","value": $searchFilter.Admin},
                {"name": "CurrencyID","value": $searchFilter.CurrencyID},
                {"name":"Export","value":1});

        },
        "iDisplayLength": pageSize,
        "sPaginationType": "bootstrap",
        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
        "aaSorting": [[0, 'asc']],
        "aoColumns": [

            {  "bSortable": true },  // 3 StartDate
            {  "bSortable": true },  // 3 StartDate
            {  "bSortable": true },  // 3 StartDate
            {  "bSortable": true }  // 3 StartDate

        ],
        "oTableTools": {
            "aButtons": [
                {
                    "sExtends": "download",
                    "sButtonText": "EXCEL",
                    "sUrl": baseurl + "/analysis/get_leads/xlsx", //baseurl + "/generate_xlsx.php",
                    sButtonClass: "save-collection"
                },
                {
                    "sExtends": "download",
                    "sButtonText": "CSV",
                    "sUrl": baseurl + "/analysis/get_leads/csv", //baseurl + "/generate_csv.php",
                    sButtonClass: "save-collection"
                }
            ]
        },
        "fnDrawCallback": function () {
            $(".dataTables_wrapper select").select2({
                minimumResultsForSearch: -1
            });

        }
    });
    return data_table;
}
function loadAccountManagerRevenue(table_id,pageSize,$searchFilter){
    var TotalCost = 0;
    data_table  = $(table_id).dataTable({
        "bDestroy": true,
        "bProcessing": true,
        "bServerSide": true,
        "bAutoWidth": false,
        "sAjaxSource": baseurl + "/analysis/get_account_manager_revenue/type",
        "fnServerParams": function (aoData) {
            aoData.push(

                {"name": "StartDate", "value": $searchFilter.StartDate},
                {"name": "EndDate","value": $searchFilter.EndDate},
                {"name": "UserID","value": $searchFilter.UserID},
                {"name": "RevenueListType","value": $searchFilter.RevenueListType},
                {"name": "Admin","value": $searchFilter.Admin},
                {"name": "CurrencyID","value": $searchFilter.CurrencyID}


            );
            data_table_extra_params.length = 0;
            data_table_extra_params.push(
                {"name": "StartDate", "value": $searchFilter.StartDate},
                {"name": "EndDate","value": $searchFilter.EndDate},
                {"name": "UserID","value": $searchFilter.UserID},
                {"name": "RevenueListType","value": $searchFilter.RevenueListType},
                {"name": "Admin","value": $searchFilter.Admin},
                {"name": "CurrencyID","value": $searchFilter.CurrencyID},
                {"name":"Export","value":1});

        },
        "paging": false,
        "bPaginate": false,
        "bInfo": false,
        "sDom": "<'row'<'col-xs-12 col-right'<'export-data'T>f>r>t",
        "aaSorting": [[0, 'asc']],
        "aoColumns": [
            {  "bSortable": true },  // 3 StartDate
            {  "bSortable": true },  // 3 StartDate
            {  "bSortable": true }  // 3 StartDate

        ],
        "oTableTools": {
            "aButtons": [
                {
                    "sExtends": "download",
                    "sButtonText": "EXCEL",
                    "sUrl": baseurl + "/analysis/get_account_manager_revenue/xlsx", //baseurl + "/generate_xlsx.php",
                    sButtonClass: "save-collection"
                },
                {
                    "sExtends": "download",
                    "sButtonText": "CSV",
                    "sUrl": baseurl + "/analysis/get_account_manager_revenue/csv", //baseurl + "/generate_csv.php",
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
                TotalCost = json.Total.TotalCost;
                fnCallback(json)
            });
        },
        "fnFooterCallback": function ( row, data, start, end, display ) {
            if (end > 0) {
                $(row).html('');
                for (var i = 0; i < 3; i++) {
                    var a = document.createElement('td');
                    $(a).html('');
                    $(row).append(a);
                }
                $($(row).children().get(0)).html('<strong>Total</strong>')
                if(TotalCost) {
                    $($(row).children().get(2)).html('<strong>' + TotalCost.toFixed(toFixed) + '</strong>');
                }
            }else{
                $(table_id).find('tfoot').find('tr').html('');
            }
        }
    });
    return data_table;

}

function loadAccountManagerMargin(table_id,pageSize,$searchFilter){
    var TotalCost = 0;
    var TotalMargin = 0;
    data_table  = $(table_id).dataTable({
        "bDestroy": true,
        "bProcessing": true,
        "bServerSide": true,
        "bAutoWidth": false,
        "sAjaxSource": baseurl + "/analysis/get_account_manager_margin/type",
        "fnServerParams": function (aoData) {
            aoData.push(

                {"name": "StartDate", "value": $searchFilter.StartDate},
                {"name": "EndDate","value": $searchFilter.EndDate},
                {"name": "UserID","value": $searchFilter.UserID},
                {"name": "MarginListType","value": $searchFilter.MarginListType},
                {"name": "Admin","value": $searchFilter.Admin},
                {"name": "CurrencyID","value": $searchFilter.CurrencyID}


            );
            data_table_extra_params.length = 0;
            data_table_extra_params.push(
                {"name": "StartDate", "value": $searchFilter.StartDate},
                {"name": "EndDate","value": $searchFilter.EndDate},
                {"name": "UserID","value": $searchFilter.UserID},
                {"name": "MarginListType","value": $searchFilter.MarginListType},
                {"name": "Admin","value": $searchFilter.Admin},
                {"name": "CurrencyID","value": $searchFilter.CurrencyID},
                {"name":"Export","value":1});

        },
        "paging": false,
        "bPaginate": false,
        "bInfo": false,
        "sDom": "<'row'<'col-xs-12 col-right'<'export-data'T>f>r>t",
        "aaSorting": [[0, 'asc']],
        "aoColumns": [
            {  "bSortable": true },  // 3 StartDate
            {  "bSortable": true },  // 3 StartDate
            {  "bSortable": true ,"bVisible": false},  // 3 StartDate
            {  "bSortable": true },  // 3 StartDate
            {  "bSortable": true }  // 3 StartDate
        ],
        "oTableTools": {
            "aButtons": [
                {
                    "sExtends": "download",
                    "sButtonText": "EXCEL",
                    "sUrl": baseurl + "/analysis/get_account_manager_margin/xlsx", //baseurl + "/generate_xlsx.php",
                    sButtonClass: "save-collection"
                },
                {
                    "sExtends": "download",
                    "sButtonText": "CSV",
                    "sUrl": baseurl + "/analysis/get_account_manager_margin/csv", //baseurl + "/generate_csv.php",
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
                TotalCost = json.Total.TotalCost;
                TotalMargin = json.Total.TotalMargin;
                fnCallback(json)
            });
        },
        "fnFooterCallback": function ( row, data, start, end, display ) {
            if (end > 0) {
                $(row).html('');
                for (var i = 0; i < 4; i++) {
                    var a = document.createElement('td');
                    $(a).html('');
                    $(row).append(a);
                }
                $($(row).children().get(0)).html('<strong>Total</strong>')
                if(TotalMargin) {
                    $($(row).children().get(2)).html('<strong>' + TotalMargin.toFixed(toFixed) + '</strong>');
                }
            }else{
                $(table_id).find('tfoot').find('tr').html('');
            }
        }
    });
    return data_table;
}
function loadAccountRevenueMargin(table_id,pageSize,$searchFilter){
    var TotalCost = 0;
    var TotalMargin = 0;
    data_table  = $(table_id).dataTable({
        "bDestroy": true,
        "bProcessing": true,
        "bServerSide": true,
        "bAutoWidth": false,
        "sAjaxSource": baseurl + "/analysis/account_revenue_margin/type",
        "fnServerParams": function (aoData) {
            aoData.push(

                {"name": "StartDate", "value": $searchFilter.StartDate},
                {"name": "EndDate","value": $searchFilter.EndDate},
                {"name": "UserID","value": $searchFilter.UserID},
                {"name": "AccountListType","value": $searchFilter.AccountListType},
                {"name": "Admin","value": $searchFilter.Admin},
                {"name": "CurrencyID","value": $searchFilter.CurrencyID}


            );
            data_table_extra_params.length = 0;
            data_table_extra_params.push(
                {"name": "StartDate", "value": $searchFilter.StartDate},
                {"name": "EndDate","value": $searchFilter.EndDate},
                {"name": "UserID","value": $searchFilter.UserID},
                {"name": "AccountListType","value": $searchFilter.AccountListType},
                {"name": "Admin","value": $searchFilter.Admin},
                {"name": "CurrencyID","value": $searchFilter.CurrencyID},
                {"name":"Export","value":1});

        },
        "paging": false,
        "bPaginate": false,
        "bInfo": false,
        "sDom": "<'row'<'col-xs-12 col-right'<'export-data'T>f>r>t",
        "aaSorting": [[0, 'asc']],
        "aoColumns": [
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
                    "sButtonText": "EXCEL",
                    "sUrl": baseurl + "/analysis/account_revenue_margin/xlsx", //baseurl + "/generate_xlsx.php",
                    sButtonClass: "save-collection"
                },
                {
                    "sExtends": "download",
                    "sButtonText": "CSV",
                    "sUrl": baseurl + "/analysis/account_revenue_margin/csv", //baseurl + "/generate_csv.php",
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
                TotalCost = json.Total.TotalCost;
                TotalMargin = json.Total.TotalMargin;
                fnCallback(json)
            });
        },
        "fnFooterCallback": function ( row, data, start, end, display ) {
            if (end > 0) {
                $(row).html('');
                for (var i = 0; i < 6; i++) {
                    var a = document.createElement('td');
                    $(a).html('');
                    $(row).append(a);
                }
                $($(row).children().get(0)).html('<strong>Total</strong>')
                if(TotalCost) {
                    $($(row).children().get(3)).html('<strong>' + TotalCost.toFixed(toFixed) + '</strong>');
                }
                if(TotalMargin) {
                    $($(row).children().get(4)).html('<strong>' + TotalMargin.toFixed(toFixed) + '</strong>');
                }
            }else{
                $(table_id).find('tfoot').find('tr').html('');
            }
        }
    });
    return data_table;
}
function loadRevenueChart(submit_data){
    loading(".bar_chart_revenue",1);
    var seriesdata = [];
    var searchdates = {};
    $.ajax({
        type: 'GET',
        url: baseurl+'/analysis/get_account_manager_revenue_report',
        dataType: 'json',
        data:submit_data,
        aysync: true,
        success: function(data) {
            loading(".bar_chart_revenue",0);
            if(data.series != '' && data.series.length > 0) {
                char_title.length = 0;
                char_title.push(data.Title);
                seriesdata = JSON.parse(JSON.stringify(data.series));
                $('.bar_chart_revenue').highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: ''
                    },
                    xAxis: {
                        /*categories: dataObj.categories.split(','),*/
                        title: {
                            text: ""
                        },
                        type: 'category'
                    },
                    tooltip: {
                        valueSuffix: ''
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'top',
                        x: -40,
                        y: 80,
                        floating: false,
                        borderWidth: 1,
                        backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                        shadow: false
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
                        },
                        series: {
                            className: 'main-color',
                            negativeColor: true
                        }
                    },

                    credits: {
                        enabled: false
                    },

                    series: seriesdata,
                });
            }else{
                $('.bar_chart_revenue').html('No Data');
            }
        }
    });
}
function loadMarginChart(submit_data){
    loading(".bar_chart_margin",1);
    var seriesdata = [];
    var searchdates = {};
    $.ajax({
        type: 'GET',
        url: baseurl+'/analysis/get_account_manager_margin_report',
        dataType: 'json',
        data:submit_data,
        aysync: true,
        success: function(data) {
            loading(".bar_chart_margin",0);
            if(data.series != '' && data.series.length > 0) {
                char_title.length = 0;
                char_title.push(data.Title);
                seriesdata = JSON.parse(JSON.stringify(data.series));
                $('.bar_chart_margin').highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: ''
                    },
                    xAxis: {
                        /*categories: dataObj.categories.split(','),*/
                        title: {
                            text: ""
                        },
                        type: 'category'
                    },
                    tooltip: {
                        valueSuffix: ''
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'top',
                        x: -40,
                        y: 80,
                        floating: false,
                        borderWidth: 1,
                        backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                        shadow: false
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
                        },
                        series: {
                            className: 'main-color',
                            negativeColor: true
                        }
                    },

                    credits: {
                        enabled: false
                    },

                    series: seriesdata,

                });
            }else{
                $('.bar_chart_margin').html('No Data');
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


