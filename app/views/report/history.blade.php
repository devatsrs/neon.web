@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('report')}}">Report</a>
    </li>
    <li>
        <a href="{{URL::to('report/schedule')}}">Report Schedule</a>
    </li>
</ol>
<h3>Report History</h3>

<div class="row">
    <div class="col-md-12">
        <form id="history_filter" method=""  action="" class="form-horizontal form-groups-bordered validate" novalidate>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Filter
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-1 control-label">Search</label>
                        <div class="col-sm-2">
                            <input class="form-control" name="Search"  type="text" >
                        </div>
                        <label class="col-sm-1 control-label">Report Name</label>
                        <div class="col-sm-2">
                            {{ Form::select('ReportID', $Reports, (!empty(Input::get('ReportID'))?Input::get('ReportID'):''), array("class"=>"form-control select2 small")) }}
                        </div>
                        <label class="col-sm-1 control-label">Schedule Name</label>
                        <div class="col-sm-2">
                            {{ Form::select('ReportScheduleID', $ReportSchedules, (!empty(Input::get('ReportScheduleID'))?Input::get('ReportScheduleID'):''), array("class"=>"form-control select2 small")) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-1 control-label">Start Date</label>
                        <div class="col-sm-2">
                            {{ Form::text('StartDate', !empty(Input::get('StartDate'))?Input::get('StartDate'):$data['StartDateDefault'], array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}<!-- Time formate Updated by Abubakar -->
                        </div>
                        <div class="col-sm-2  small-date-input">
                            <input type="text" name="StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00:00" data-show-seconds="true" data-template="dropdown" placeholder="00:00:00" class="form-control timepicker">
                        </div>
                        <label for="field-1" class="col-sm-1 control-label">End Date</label>
                        <div class="col-sm-2">
                            {{ Form::text('EndDate', !empty(Input::get('EndDate'))?Input::get('ToDate'):$data['EndDateDefault'], array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}
                        </div>
                        <div class="col-sm-2  small-date-input">
                            <input type="text" name="EndTime" data-minute-step="5" data-show-meridian="false" data-default-time="23:59:59" value="23:59:59" data-show-seconds="true" placeholder="00:00:00" data-template="dropdown" class="form-control timepicker">
                        </div>
                    </div>
                    <p style="text-align: right;">
                        <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                            <i class="entypo-search"></i>
                            Search
                        </button>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="clear"></div>

<table class="table table-bordered datatable" id="table-4">
    <thead>
        <tr>
            <th width="30%">Name</th>
            <th width="30%">Created Date</th>
            <th width="30%">Action</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>
<script src="{{ URL::asset('assets/js/dashboard.js') }}"></script>
<script type="text/javascript">
    var $searchFilter = {};
    var list_fields_index  = ["Name","send_at","Subject","Message","AttachmentPaths"];
    jQuery(document).ready(function($) {

        $searchFilter.Search = $("#history_filter [name='Search']").val();
        $searchFilter.StartDate = $("#history_filter [name='StartDate']").val();
        $searchFilter.StartTime = $("#history_filter [name='StartTime']").val();
        $searchFilter.EndDate = $("#history_filter [name='EndDate']").val();
        $searchFilter.EndTime = $("#history_filter [name='EndTime']").val();
        $searchFilter.ReportID = $("#history_filter [name='ReportID']").val();
        $searchFilter.ReportScheduleID = $("#history_filter [name='ReportScheduleID']").val();

        data_table = $("#table-4").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/report/schedule_history/type",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "fnServerParams": function(aoData) {
                aoData.push(
                        {"name":"Search","value":$searchFilter.Search},
                        {"name":"StartDate","value":$searchFilter.StartDate},
                        {"name":"StartTime","value":$searchFilter.StartTime},
                        {"name":"EndDate","value":$searchFilter.EndDate},
                        {"name":"EndTime","value":$searchFilter.EndTime},
                        {"name":"ReportID","value":$searchFilter.ReportID},
                        {"name":"ReportScheduleID","value":$searchFilter.ReportScheduleID}
                );
                data_table_extra_params.length = 0;
                data_table_extra_params.push(
                        {"name":"Search","value":$searchFilter.Search},
                        {"name":"StartDate","value":$searchFilter.StartDate},
                        {"name":"StartTime","value":$searchFilter.StartTime},
                        {"name":"EndDate","value":$searchFilter.EndDate},
                        {"name":"EndTime","value":$searchFilter.EndTime},
                        {"name":"ReportID","value":$searchFilter.ReportID},
                        {"name":"ReportScheduleID","value":$searchFilter.ReportScheduleID},
                        {"name":"Export","value":1}
                );
            },
            "aaSorting": [[2, 'desc']],
            "aoColumns":
                    [
                        {"bSortable": true},  // 1 Title
                        {"bSortable": true},  // 1 Title
                        {                        // 9 Action
                            "bSortable": false,
                            mRender: function (id, type, full) {
                                action = '<div class = "hiddenRowData" >';
                                for (var i = 0; i < list_fields_index.length; i++) {
                                    action += '<div class="hidden" name="' + list_fields_index[i] + '">' + full[i] + '</div>';
                                }
                                action += '</div>';

                                action += ' <a class="view-report btn btn-default btn-sm tooltip-primary" data-original-title="View" title="" data-placement="top" data-toggle="tooltip"><i class="fa fa-eye"></i></a>'
                                var str = full[4];
                                if(str) {
                                    var str_array = str.split(',');
                                    for (var i = 0; i < str_array.length; i++) {
                                        console.log(str_array[i])
                                        action += ' <a href="'+baseurl+'/report/schedule_download/'+full[5]+'-'+i+'" class="btn btn-green btn-sm tooltip-primary" data-original-title="View" title="" data-placement="top" data-toggle="tooltip"><i class="fa fa-download"></i></a>'
                                    }
                                }
                                        return action;
                            }
                        }
                    ],
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/report/schedule_history/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/report/schedule_history/csv",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    }
        });

        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });

        // Replace Checboxes
        $(".pagination a").click(function(ev) {
            replaceCheckboxes();
        });


        $("#history_filter").submit(function(e) {
            e.preventDefault();
            $searchFilter.Search = $("#history_filter [name='Search']").val();
            $searchFilter.StartDate = $("#history_filter [name='StartDate']").val();
            $searchFilter.StartTime = $("#history_filter [name='StartTime']").val();
            $searchFilter.EndDate = $("#history_filter [name='EndDate']").val();
            $searchFilter.EndTime = $("#history_filter [name='EndTime']").val();
            $searchFilter.Status = $("#history_filter [name='Status']").val();
            $searchFilter.ReportID = $("#history_filter [name='ReportID']").val();
            $searchFilter.ReportScheduleID = $("#history_filter [name='ReportScheduleID']").val();

            data_table.fnFilter('', 0);
            return false;
        });

    });

</script>
@include('report.report-log')
    @stop
