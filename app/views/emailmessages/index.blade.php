@extends('layout.main')

@section('content')


<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Emails</strong>
    </li>
</ol>
<h3>Emails</h3>

@include('includes.errors')
@include('includes.success')
<div class="row">
    <div class="col-md-12">
        <form novalidate class="form-horizontal form-groups-bordered validate" method="post" id="job_filter">
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

        $searchFilter.AccountID = $("#job_filter [name='AccountID']").val();
        $searchFilter.JobLoggedUserID = $("#job_filter [name='JobLoggedUserID']").val();

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/emailmessages/ajax_datagrid",
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            //"sDom": 'T<"clear">lfrtip',
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "sPaginationType": "bootstrap",
            "fnServerParams": function(aoData) {
                aoData.push({"name":"AccountID","value":$searchFilter.AccountID},{"name":"JobLoggedUserID","value":$searchFilter.JobLoggedUserID});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"Export","value":1},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"JobLoggedUserID","value":$searchFilter.JobLoggedUserID});
            },
            "aaSorting": [[3, 'desc']],
            "aoColumns":
                    [
                        {"bSortable": false },
                        {"bSortable": true },                     
                        {"bSortable": true  },
                        {
                            "bSortable": true,
                            mRender: function(id, type, full) {
                                var action, edit_, show_;

                                action = '<a  onclick=" return showEmailMessageAjaxModal(' + id + ');" href="javascript:;"   class="btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>View </a>';
                        
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
                <h4 class="modal-title">Message Content</h4>
            </div>
            <div class="modal-body">
                Content is loading...
            </div>
        </div>
    </div>
</div>
@stop