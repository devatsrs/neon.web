@extends('layout.main') @section('content')

<ol class="breadcrumb bc-3">
    <li><a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a></li>
    <li><a href="{{URL::to('/estimates')}}">Estimate</a></li>
    <li class="active"><strong>{{$estimatenumber}}</strong></li>
</ol>
<h3>View Estimate Log</h3>
<div class="float-right" >
    <a href="{{URL::to('/estimates')}}"  class="btn btn-primary btn-sm btn-icon icon-left" >
        <i class="entypo-floppy"></i>
        Back
    </a>


</div>
<div class="row">
    <div class="col-md-12">
        <form role="form" id="rate-table-search"  method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">

        </form>
    </div>
</div>
<table class="table table-bordered datatable" id="table-5">
    <thead>
        <tr>
            <th width="35%">Notes</th>
            <th width="20%">Status</th>
            <th width="20%">Date</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>


<script type="text/javascript">
    var $searchFilter = {};
    var list_fields  = ['Notes','EstimateLogStatus','created_at','EstimateID'];
    var data_table_invoice_log;
    var estimatelogstatus = {{json_encode(EstimateLog::$log_status)}};
    jQuery(document).ready(function($) {
            
            data_table_invoice_log = $("#table-5").dataTable({
                "bDestroy": true, // Destroy when resubmit form
                "bProcessing": true,
                "bServerSide": true,
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "sAjaxSource": baseurl + "/estimate/ajax_estimatelog_datagrid/{{$id}}/type",
                "fnServerParams": function(aoData) {
                    aoData.push();
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"Export","value":1});
                },
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                //  "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[2, "desc"]],
                "aoColumns":                        [

                            {}, //note
                            {
                                mRender: function(status, type, full) {
                                    return estimatelogstatus[status];
                                }
                            }, // Status
                            {} // Date

                        ],
                        "oTableTools":
                        {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "EXCEL",
                                    "sUrl": baseurl + "/estimate/ajax_estimatelog_datagrid/{{$id}}/xlsx",
                                    sButtonClass: "save-collection btn-sm"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/estimate/ajax_estimatelog_datagrid/{{$id}}/csv",
                                    sButtonClass: "save-collection btn-sm"
                                }
                            ]
                        },
                "fnDrawCallback": function() {

                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }
            });

        // Replace Checboxes
        $(".pagination a").click(function(ev) {
            replaceCheckboxes();
        });
    });

</script>
@stop
