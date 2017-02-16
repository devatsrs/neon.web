@extends('layout.main')

@section('content')


<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Jobs</strong>
    </li>
</ol>
<h3>Jobs</h3>

@include('includes.errors')
@include('includes.success')
<div class="row">
    <div class="col-md-12">
        <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="post" id="job_filter">
            <div data-collapsed="0" class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        Filter
                    </div>
                    <div class="panel-options">
                        <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-1 control-label" for="field-1">Status</label>
                        <div class="col-sm-2">
                            {{ Form::select('Status',$jobstatus, '', array("class"=>"select2")) }}
                        </div>
                        <label class="col-sm-1 control-label" for="field-1">Type</label>
                        <div class="col-sm-2">
                            {{ Form::select('Type',$jobtype,'', array("class"=>"select2")) }}
                        </div>
                        <label class="col-sm-1 control-label" for="field-1">Created By</label>
                        <div class="col-sm-2">
                            {{ Form::select('JobLoggedUserID',$creatdby,'', array("class"=>"select2")) }}
                        </div>
                        <label class="col-sm-1 control-label" for="field-1">Account</label>
                        <div class="col-sm-2">
                            {{ Form::select('AccountID',$account,'', array("class"=>"select2")) }}
                        </div>
                    </div>
                    <p style="text-align: right;">
                        <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit">
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
            <th>Title</th>
            <th>Type</th>
            <th>Status</th>
            <th>Created Date</th>
            <th>Created By</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>

<script type="text/javascript">

    jQuery(document).ready(function($) {
    var $searchFilter = {};
        $("#job_filter").submit(function(e) {
        e.preventDefault();

        $searchFilter.Type = $("#job_filter [name='Type']").val();
        $searchFilter.Status = $("#job_filter [name='Status']").val();
        $searchFilter.AccountID = $("#job_filter [name='AccountID']").val();
        $searchFilter.JobLoggedUserID = $("#job_filter [name='JobLoggedUserID']").val();

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/jobs/ajax_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            //"sDom": 'T<"clear">lfrtip',
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "sPaginationType": "bootstrap",
            "fnServerParams": function(aoData) {
                aoData.push({"name":"Type","value":$searchFilter.Type},{"name":"Status","value":$searchFilter.Status},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"JobLoggedUserID","value":$searchFilter.JobLoggedUserID});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"Type","value":$searchFilter.Type},{"name":"Export","value":1},{"name":"Status","value":$searchFilter.Status},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"JobLoggedUserID","value":$searchFilter.JobLoggedUserID});
            },
            "aaSorting": [[3, 'desc']],
            "aoColumns":
                    [
                        {"bSortable": false },
                        {"bSortable": true },
                        {"bSortable": true },
                        {"bSortable": true },
                        {"bSortable": true  },
                        {
                            "bSortable": true,
                            mRender: function(id, type, full) {
                                var action, edit_, show_;

                                action = '<a  onclick=" return showJobAjaxModal(' + id + ');" href="javascript:;" title="View"   class="btn btn-default btn-sm"><i class="fa fa-eye"></i></a>';

                                Status = full[2].toLowerCase();

                                if(Status == 'in progress'){

                                    action += ' <button data-id="'+ id +'" title="Stop" class="job_terminate btn btn-red btn-sm" type="button" data-loading-text="Loading..."><i class="entypo-stop"></i></button>';

                                }else  if( Status == 'failed' ){

                                    action += ' <button data-id="'+ id +'" title="Start" class="job_restart btn btn-primary btn-sm" type="button" data-loading-text="Loading..."><i class="glyphicon glyphicon-repeat"></i></button>';
                                } else  if( Status == 'pending' ){

                                    action += ' <button data-id="'+ id +'" class="job_cancel btn btn-primary btn-sm btn-icon icon-left" type="button" data-loading-text="Loading..."><i class="glyphicon glyphicon-repeat"></i> Cancel</button>';
                                }

                                return action;
                            }
                        },
                        //{ "visible": false ,"bSortable": true },
                        //{ "visible": false  ,"bSortable": true}
                    ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/jobs/exports/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/jobs/exports/csv", //baseurl + "/generate_csv.php",
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
            //data_table.fnSetColumnVis(6, false);
            //data_table.fnSetColumnVis(7, false);
        });


        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });

        // Highlighted rows
        $("#table-2 tbody input[type=checkbox]").each(function(i, el) {
            var $this = $(el),
                    $p = $this.closest('tr');

            $(el).on('change', function() {
                var is_checked = $this.is(':checked');

                $p[is_checked ? 'addClass' : 'removeClass']('highlight');
            });
        });

        // Replace Checboxes
        $(".pagination a").click(function(ev) {
            replaceCheckboxes();
        });

        //Restart a job
        $('table tbody').on('click','.job_restart',function(ev){
            result = confirm("Are you Sure?");
            if(result){
                id = $(this).attr('data-id');
                submit_ajax(baseurl+'/jobs/'+id + '/restart');
                data_table.fnFilter('', 0);
            }
        });

       //Cancel a job
        $('table tbody').on('click','.job_cancel',function(ev){
            result = confirm("Are you Sure?");
            if(result){
                id = $(this).attr('data-id');
                submit_ajax(baseurl+'/jobs/'+id + '/cancel');
                data_table.fnFilter('', 0);
            }
        });


        //Terninate a job
        $('table tbody').on('click','.job_terminate',function(ev){
            var JobID = $(this).attr('data-id');

            $('#job_terminate_form').trigger('reset');
            $("#job_terminate_form [name='JobStatusID']").val('').trigger("change");

            $('#modal-Terminate').modal('show');
            $('#job_terminate_form').attr("action", baseurl+'/jobs/'+JobID + '/terminate');
        });


        $("#job_terminate_form").submit(function(e){
            e.preventDefault();
            url = $(this).attr("action");
            submit_ajax(url,$(this).serialize());
            data_table.fnFilter('', 0);

            return false;
        });

    });

</script>
@stop

@section('footer_ext')
@parent
<!-- Job Modal  (Ajax Modal)-->
<div class="modal fade" id="modal-job">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Job Content</h4>
            </div>
            <div class="modal-body">
                Content is loading...
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-Terminate">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="job_terminate_form" method="post" action="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Terminate Job</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Change Status To</label>
                            <div class="col-sm-9">
                                {{Form::select('JobStatusID',$jobstatus_for_terminate,'',array("class"=>"select2 small"))}}
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Message</label>
                            <div class="col-sm-9">
                                <textarea class="form-control message" rows="4" name="message"></textarea>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Terminate
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop