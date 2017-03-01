@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Cron Job</strong>
    </li>
</ol>
<h3>Cron Job</h3>

@include('includes.errors')
@include('includes.success')
<p style="text-align: right;">
@if( User::checkCategoryPermission('CronJob','Add') )
    <a href="#" id="add-new-config" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New
    </a>
@endif
</p>

<div class="row">
    <div class="col-md-12">
        <form id="cronjob_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
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
                        <label for="field-1" class="col-sm-2 control-label">Status</label>
                        <div class="col-sm-2">
                            {{ Form::select('Active', [""=>"Both",CronJob::ACTIVE=>"Active",CronJob::INACTIVE=>"Inactive"], CronJob::ACTIVE, array("class"=>"form-control select2 small")) }}
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


<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="20%">Job Title</th>
        <th width="20%">Name</th>
        <th width="20%">Status</th>
        <th width="20%">Action</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");

        //show_loading_bar(40);
        $('#cronjob_filter').submit(function(e) {
            e.preventDefault();
            $searchFilter.Active = $('#cronjob_filter [name="Active"]').val();
            data_table = $("#table-4").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/cronjobs/ajax_datagrid/type",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "fnServerParams": function (aoData) {
                    aoData.push({"name": "Active", "value": $searchFilter.Active});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name": "Active", "value": $searchFilter.Active},
                            {"name": "Export", "value": 1});
                },
                "aaSorting": [[0, 'asc']],
                "aoColumns": [
                    {"bSortable": true},//0 title
                    {"bSortable": true},  //1   name
                    {
                        mRender: function (status, type, full) {
                            if (status == 1)
                                return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                            else
                                return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                        }
                    }, //2   Status
                    {                       //3
                        "bSortable": false,
                        mRender: function (CronJobID, type, full) {

                            var action = '';


                            action = '<div class = "hiddenRowData" >';
                            action += '<input type = "hidden"  name = "JobTitle" value = "' + (full[0] !== null ? full[0] : '') + '" / >';
                            action += '<input type = "hidden"  name = "Title" value = "' + (full[1] !== null ? full[1] : '') + '" / >';
                            action += '<input type = "hidden"  name = "Status" value = "' + (full[2] !== null ? full[2] : '') + '" / >';
                            action += '<input type = "hidden"  name = "CronJobID" value = "' + CronJobID + '" / >';
                            action += '<input type = "hidden"  name = "CronJobCommandID" value = "' + (full[4] !== null ? full[4] : '') + '" / >';
                            action += '<div id="cron_set" style="display: none" >' + (full[5] !== null ? full[5] : '') + '</div>'
                            //action += '<input type = "hidden"  name = "SippyUsageInterval" value = "' + setiting.SippyUsageInterval+ '" / >';
                            action += '</div>';
                            var history_url = baseurl + "/cronjobs/history/" + CronJobID;

                            <?php if(User::checkCategoryPermission('CronJob','Edit') ){ ?>
                            action += ' <a data-name = "' + full[1] + '" data-id="' + CronJobID + '" title="Edit" class="edit-config btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                            <?php } ?>
                            <?php if(User::checkCategoryPermission('CronJob','Delete')){ ?>
                            action += ' <a data-id="' + CronJobID + '" title="Delete" class="delete-config btn delete btn-danger btn-sm"><i class="entypo-trash"></i></a>';
                            <?php } ?>
                            action += ' <a href="' + history_url + '" title="History" class=" btn btn-default btn-sm"><i class="entypo-list"></i>History </a>';

                            return action;
                        }
                    },

                ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/cronjobs/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/cronjobs/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
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
        });


        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

    $('#cronjob_filter').submit();

    });
</script>
<style>
.dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}
</style>
@stop

@section('footer_ext')
@parent
    @include('cronjob.cronjob_edit_popup')
@stop

