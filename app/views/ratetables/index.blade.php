@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Rate Table</strong>
    </li>
</ol>
<h3>Rate Table</h3>
<p style="text-align: right;">
@if(User::checkCategoryPermission('RateTables','Add'))
    <a href="#" id="add-new-rate-table" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New RateTable
    </a>
@endif
</p>
<div class="row">
    <div class="col-md-12">
        <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="get" id="ratetable_filter">
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
                        <label class="col-sm-2 control-label" for="field-1">Trunk</label>
                        <div class="col-sm-3">
                            {{ Form::select('TrunkID', $trunks, $trunk_keys, array("class"=>"select2")) }}
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
<div class="cler row">
    <div class="col-md-12">
        <form role="form" id="form1" method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Rate Table
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-md-12">
                            <table class="table table-bordered datatable" id="table-4">
                                <thead>
                                    <tr>
                                        <th width="30%">Name</th>
                                        <th width="20%">Currency</th>
                                        <th width="28%">Codedeck</th>
                                        <th width="20%">last update</th>
                                         <th width="20%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>


                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($) {
    var $searchFilter = {};
    var update_new_url;
        $searchFilter.TrunkID = $("#ratetable_filter [name='TrunkID']").val();

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/rate_tables/ajax_datagrid",
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "oTableTools": {},
            "aaSorting": [[3, "desc"]],
            "fnServerParams": function(aoData) {
                aoData.push({"name":"TrunkID","value":$searchFilter.TrunkID});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"TrunkID","value":$searchFilter.TrunkID});
            },
            "fnRowCallback": function(nRow, aData) {
                $(nRow).attr("id", "host_row_" + aData[2]);
            },
            "aoColumns":
                    [
                        {},
                        {},
                        {},
                        {},
                        {
                            mRender: function(id, type, full) {
                                var action, view_, delete_;
                                view_ = "{{ URL::to('/rate_tables/{id}/view')}}";
                                delete_ = "{{ URL::to('/rate_tables/{id}/delete')}}";

                                view_ = view_.replace('{id}', id);
                                delete_ = delete_.replace('{id}', id);

                                action = '<a href="' + view_ + '" class="btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>View</a>';

                                <?php if(User::checkCategoryPermission('RateTables','Delete') ) { ?>
                                    action += ' <a href="' + delete_ + '" data-redirect="{{URL::to("/rate_tables")}}"  class="btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete</a>';
                                <?php } ?>
                                //action += status_link;
                                return action;
                            }
                        },
                    ],
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/rate_tables/exports/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/rate_tables/exports/csv",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    }, 
            "fnDrawCallback": function() {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });

                $(".btn.delete").click(function(e) {

                    response = confirm('Are you sure?');
                    //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
                    if (response) {
                        $.ajax({
                            url: $(this).attr("href"),
                            type: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                $(".btn.delete").button('reset');
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    data_table.fnFilter('', 0);
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                            },
                            // Form data
                            //data: {},
                            cache: false,
                            contentType: false,
                            processData: false
                        });


                    }
                    return false;

                });
                $(".btn.change_status").click(function(e) {
                    //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
                     $(this).button('loading');
                        $.ajax({
                            url: $(this).attr("href"),
                            type: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                $(this).button('reset');
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    data_table.fnFilter('', 0);
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                            },

                            // Form data
                            //data: {},
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                    return false;
                });
            }
        });

        $("#ratetable_filter").submit(function(e) {
            e.preventDefault();
            $searchFilter.TrunkID = $("#ratetable_filter [name='TrunkID']").val();
            data_table.fnFilter('', 0);
            return false;
         });
         $("#add-new-rate-table").click(function(ev) {
             $('#modal-add-new-rate-table').modal('show', {backdrop: 'static'});
         });
         $("#add-new-form").submit(function(ev){
            ev.preventDefault();
            update_new_url = baseurl + '/rate_tables/store';
            submit_ajax(update_new_url,$("#add-new-form").serialize());
         });
    });

</script>
@include('includes.errors')
@include('includes.success')
@stop
@section('footer_ext')
@parent
<div class="modal fade" id="modal-add-new-rate-table">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New RateTable</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">Codedeck</label>
                                {{Form::select('CodedeckId', $codedecks, '',array("class"=>"form-control select2"))}}
                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">Trunk</label>
                                {{Form::select('TrunkID', $trunks, $trunk_keys,array("class"=>"form-control select2"))}}
                            </div>
                        </div>
                         </div>
                       <div class="row">
                       <div class="col-md-6">
                           <div class="form-group ">
                               <label for="field-5" class="control-label">Currency</label>
                               {{ Form::select('CurrencyID', $currencylist,  '', array("class"=>"select2")) }}
                           </div>
                       </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">RateTable Name</label>
                                <input type="text" name="RateTableName" class="form-control" value="" />
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="codedeck-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
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