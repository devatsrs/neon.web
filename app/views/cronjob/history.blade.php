@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('cronjobs')}}">Cron Job</a>
    </li>
    <li class="active">
        <strong>Cron Job History</strong>
    </li>
</ol>
<h3>Cron Job History</h3>

<table class="table table-bordered datatable" id="table-4">
    <thead>
        <tr>
            <th>Title</th>
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

        data_table = $("#table-4").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/cronjobs/history_ajax_datagrid/{{$id}}/type",
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "fnServerParams": function(aoData) {
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"Export","value":1});
            },
            "aaSorting": [[3, 'desc']],
            "aoColumns":
                    [
                        {}, //0 tblJob.Title
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