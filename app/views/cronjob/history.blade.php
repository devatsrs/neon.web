@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('cronjob_monitor')}}">Cron Job</a>
    </li>
    <li class="active">
        <strong>{{$JobTitle}}</strong>
    </li>
</ol>
<h3>Cron Job History</h3>

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
                        <label class="col-sm-1 control-label">Status</label>
                        <div class="col-sm-2">
                            {{ Form::select('Status', [""=>"Both",1=>"Success",2=>"Failed"], '', array("class"=>"form-control select2 small")) }}
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
            <th style="display: none;">Title</th>
            <th>Status</th>
            <th>Message</th>
            <th>Created Date</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>
<script type="text/javascript">

    jQuery(document).ready(function($) {
        var $searchFilter = {};
        $searchFilter.Search = $("#history_filter [name='Search']").val();
        $searchFilter.StartDate = $("#history_filter [name='StartDate']").val();
        $searchFilter.StartTime = $("#history_filter [name='StartTime']").val();
        $searchFilter.EndDate = $("#history_filter [name='EndDate']").val();
        $searchFilter.EndTime = $("#history_filter [name='EndTime']").val();
        $searchFilter.Status = $("#history_filter [name='Status']").val();
        data_table = $("#table-4").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/cronjobs/history_ajax_datagrid/{{$id}}/type",
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
                        {"name":"Status","value":$searchFilter.Status});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"Search","value":$searchFilter.Search},
                        {"name":"StartDate","value":$searchFilter.StartDate},
                        {"name":"StartTime","value":$searchFilter.StartTime},
                        {"name":"EndDate","value":$searchFilter.EndDate},
                        {"name":"EndTime","value":$searchFilter.EndTime},
                        {"name":"Status","value":$searchFilter.Status},
                        {"name":"Export","value":1});
            },
            "aaSorting": [[3, 'desc']],
            "aoColumns":
                    [
                        {
                            "bVisible": false

                        }, //0 tblJob.Title
                        {
                            mRender: function(status, type, full) {
                                               if (status == '{{CronJob::CRON_SUCCESS}}')
                                                   return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                                               else
                                                   return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                                           }
                        }, //1 status
                        {}, //1 tblRateSheetHistory.created_at
                        {  // 2 tblJob.JobID

                        },
                    ],
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/cronjobs/history_ajax_datagrid/{{$id}}/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/cronjobs/history_ajax_datagrid/{{$id}}/csv",
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

            data_table.fnFilter('', 0);
            return false;
        });

    });

</script>
@stop            

@section('footer_ext')
@parent
<!-- Job Modal  (Ajax Modal)-->
<div class="modal fade" id="modal-customer-rate-history">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Detail</h4>
            </div>
            <div class="modal-body">
                Content is loading...
            </div>
        </div>
    </div>
</div>
@stop