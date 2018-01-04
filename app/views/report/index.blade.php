@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Report</a>
        </li>
    </ol>
    <h3>Report</h3>
    @include('includes.errors')
    @include('includes.success')
@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="report_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
 
                        <div class="form-group">
                            <label for="field-1" class="control-label">Name</label>
                            <input class="form-control" name="Name" type="text" >
                        </div>
                        <div class="form-group">
                            <br/>
                            <button type="submit" class="btn btn-primary btn-md btn-icon icon-left" id="report_submit">
                                <i class="entypo-search"></i>
                                Search
                            </button>
                        </div>


            </form>

        </div>
    </div>
@stop
    @if(User::checkCategoryPermission('Report','Add'))
        <p style="text-align: right;">
            <a href="{{URL::to('report/create')}}" class=" btn btn-primary btn-sm btn-icon icon-left" id="add-report">
                <i class="entypo-plus"></i>
                Add New
            </a>

            <a href="{{URL::to('report/schedule')}}" class=" btn btn-primary btn-sm btn-icon icon-left">
                <i class="fa fa-list"></i>
                Schedule
            </a>
        </p>
    @endif
    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
            <th width="60%">Name</th>
            <th width="40%">Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <script type="text/javascript">
        var list_fields_index  = ["Name","ReportID","ReportScheduleID","Status","Settings"];

        var $search = {};
        var report_edit_url = baseurl + "/report/edit/{id}";
        var report_delete_url = baseurl + "/report/delete/{id}";
        var report_export_url = baseurl + "/report/getdatagrid/{id}";
        var report_schedule_url = baseurl + "/report/schedule_update/{id}";
        var report_add_schedule_url = baseurl + "/report/add_schedule";
        var report_history_url = baseurl + "/report/schedule_history";
        var report_datagrid_url = baseurl + "/report/ajax_datagrid/type";
        jQuery(document).ready(function ($) {
            $('#filter-button-toggle').show();

            data_table = $("#table-4").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": report_datagrid_url,
                "fnServerParams": function (aoData) {
                    aoData.push({"name": "Name", "value": $search.Name});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name": "Name", "value": $search.Name},{"name":"Export","value":1});

                },
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[0, 'asc']],
                "aoColumns": [
                    {"bSortable": true},  // 1 Email Addresses
                    {                        // 9 Action
                        "bSortable": false,
                        sClass: "dropdown",
                        mRender: function (id, type, full) {
                            var action;
                            action = '<div class = "hiddenRowData pull-left" >';
                            for (var i = 0; i < list_fields_index.length; i++) {
                                if(list_fields_index[i] == 'Settings' && full[i] != null && IsJsonString(full[i])){
                                    var settings_json = JSON.parse(full[i]);
                                    $.each(settings_json, function(key, value) {
                                        action += '<input disabled type = "hidden"  name = "' +key + '"       value = "' + value + '" / >';
                                    });
                                }else {
                                    action += '<input disabled type = "hidden"  name = "' + list_fields_index[i] + '"       value = "' + full[i] + '" / >';
                                }
                            }
                            action += '</div>';
                            var Status = full[3];
                            var ReportScheduleID = full[2];
                            @if(User::checkCategoryPermission('Report','Update'))
                                action += ' <a href="' + report_edit_url.replace("{id}", id) + '" class="btn btn-default btn-sm tooltip-primary" data-original-title="Edit" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-pencil"></i>&nbsp;</a>';
                                action += ' <a href="' + report_edit_url.replace("{id}", id) + '?report=run" class="btn btn-default btn-sm tooltip-primary" data-original-title="Run" title="" data-placement="top" data-toggle="tooltip"><i class="fa fa-play"></i>&nbsp;</a>';
                                action += ' <a class="btn btn-default btn-sm tooltip-primary dropdown-toggle" data-original-title="Export" title="" data-placement="top" data-toggle="dropdown"><i class="fa fa-download"></i>&nbsp;</a>' +
                                        '<ul class="dropdown-menu dropdown-menu-left" role="menu" style="left:60px;top:35px;background-color: #000; border-color: #000; margin-top:0px; min-width: 0"> <li> <a href="' + report_export_url.replace("{id}", id) + '?Type={{Report::XLS}}" class="save-report-data"> <span>Excel</span> </a> </li><li> <a href="' + report_export_url.replace("{id}", id) + '?Type={{Report::PNG}}" class="save-report-data"> <span>PNG</span> </a> </li> <li> <a href="' + report_export_url.replace("{id}", id) + '?Type={{Report::PDF}}" class="save-report-data">  <span>PDF</span> </a> </li> </ul>';
                            if(ReportScheduleID) {
                                action += ' <a href="' + report_schedule_url.replace("{id}", ReportScheduleID) + '" class="schedule_report  btn btn-default btn-sm tooltip-primary" data-original-title="Scheduling" title="" data-placement="top" data-toggle="tooltip"><i class="fa fa-calendar-times-o"></i>&nbsp;</a>';
                            }else{
                                action += ' <a href="' + report_add_schedule_url + '" class="schedule_report  btn btn-default btn-sm tooltip-primary" data-original-title="Scheduling" title="" data-placement="top" data-toggle="tooltip"><i class="fa fa-calendar-times-o"></i>&nbsp;</a>';
                            }
                                action += ' <a href="' + report_history_url+'?ReportID=' +id+'" class="btn btn-default btn-sm tooltip-primary" data-original-title="History" title="" data-placement="top" data-toggle="tooltip"><i class="glyphicon glyphicon-time"></i>&nbsp;</a>';
                            @endif

                                    @if(User::checkCategoryPermission('Report','Delete'))
                            //if(full[2] == 0) {
                                action += ' <a href="' + report_delete_url.replace("{id}", id) + '" class="delete-report btn btn-danger btn-sm tooltip-primary" data-original-title="Delete" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-trash"></i></a>';
                            //}
                            @endif
                            @if(User::checkCategoryPermission('Report','Update'))
                                if(full[4]) {
                                    if (Status == 1) {
                                        action += '&nbsp;<button data-id="' + ReportScheduleID + '" data-status="' + Status + '" class="change_schedule btn btn-red btn-sm" type="button" title="Scheduling InActive" data-placement="left" data-toggle="tooltip"><i class="glyphicon glyphicon-ban-circle" ></i></button>';
                                    } else {
                                        action += '&nbsp;<button data-id="' + ReportScheduleID + '" data-status="' + Status + '" class="change_schedule btn btn-green btn-sm" type="button" title="Scheduling Active" data-placement="left" data-toggle="tooltip"><i class="entypo-check"></i></button>';
                                    }
                                }
                            @endif
                                return action;
                        }
                    }
                ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/report/ajax_datagrid/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/report/ajax_datagrid/csv",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function () {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }

            });
            $("#report_submit").click(function(e) {

                e.preventDefault();
                public_vars.$body = $("body");
                $search.Name = $('#report_filter [name="Name"]').val();
                data_table.fnFilter('', 0);
                return false;
            });


            // Replace Checboxes
            $(".pagination a").click(function (ev) {
                replaceCheckboxes();
            });

            $('table tbody').on('click', '.delete-report', function (ev) {
                ev.preventDefault();
                result = confirm("Are you Sure?");
                if(result){
                    var delete_url  = $(this).attr("href");
                    submit_ajax_datatable( delete_url,"",0,data_table);
                }
                return false;
            });

            $("#report-form").submit(function(e){
                e.preventDefault();
                var _url  = $(this).attr("action");
                submit_ajax_datatable(_url,$(this).serialize(),0,data_table);

            });

            // Replace Checboxes
            $(".pagination a").click(function (ev) {
                replaceCheckboxes();
            });

            $('table tbody').on('click','.change_schedule',function(ev){
                result = confirm("Are you Sure?");
                if(result){
                    status = ($(this).attr('data-status')==0)?1:0;
                    submit_ajax(baseurl+'/report/status_update/'+$(this).attr('data-id')+'?Status=' + status );
                }
            });


        });

    </script>
@include('report.schedule_modal')
@stop
