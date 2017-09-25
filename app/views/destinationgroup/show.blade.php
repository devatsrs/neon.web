@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <div id="table_filter" method="get" action="#" >
                <div class="form-group">
                    <label for="field-1" class="control-label">Name</label>
                    <input type="text" name="Name" class="form-control" value="" />
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left" id="filter_submit">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop


@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('destination_group_set')}}">Destination Group Set </a>
        </li>
        <li class="active">
            <strong>Destination Group ({{$name}})</strong>
        </li>
    </ol>
    <h3>Destination Group</h3>
    <p style="text-align: right;">
        @if(User::checkCategoryPermission('DestinationGroup','Edit'))
        @if($discountplanapplied ==0)
        <a  id="add-button" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New</a>
        @endif
        @endif
        <a href="{{URL::to('/destination_group_set')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </p>
    @include('includes.errors')
    @include('includes.success')


    <table id="table-list" class="table table-bordered datatable">
        <thead>
        <tr>
            <th width="20%">Name</th>
            <th width="40%">Code</th>
            <th width="10%">Created By</th>
            <th width="10%">Created</th>
            <th width="20%">Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <script type="text/javascript">
        /**
         * JQuery Plugin for dataTable
         * */
        var data_table_list;
        var update_new_url;
        var postdata;
        var add_url = baseurl + "/destination_group/store";
        var edit_url = baseurl + "/destination_group/update_name/{id}";
        var view_url = baseurl + "/destination_group/show/{id}";
        var delete_url = baseurl + "/destination_group/delete/{id}";
        var datagrid_url = baseurl + "/destination_group/ajax_datagrid";
        var checked='';

        jQuery(document).ready(function ($) {

            $('#filter-button-toggle').show();

            var list_fields  = ["Name","Code","CreatedBy","created_at","DestinationGroupID","DestinationGroupSetID"];
            //public_vars.$body = $("body");
            var $search = {};


            $("#filter_submit").click(function(e) {
                e.preventDefault();

                $search.Name = $("#table_filter").find('[name="Name"]').val();
                data_table = $("#table-list").dataTable({
                    "bDestroy": true,
                    "bProcessing":true,
                    "bServerSide": true,
                    "sAjaxSource": datagrid_url,
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[0, 'asc']],
                    "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "DestinationGroupSetID", "value": '{{$DestinationGroupSetID}}'}

                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "DestinationGroupSetID", "value": '{{$DestinationGroupSetID}}'},
                                {"name": "Export", "value": 1}
                        );

                    },
                    "aoColumns": [
                        {  "bSortable": true },  // 0 Name
                        {  "bSortable": true },  // 0 Code
                        {  "bSortable": true },  // 0 Created By
                        {  "bSortable": true },  // 0 Created
                        {  "bSortable": false,
                            mRender: function ( id, type, full ) {
                                action = '<div class = "hiddenRowData" >';
                                for(var i = 0 ; i< list_fields.length; i++){
                                    action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                }
                                action += '</div>';
                                @if(User::checkCategoryPermission('DestinationGroup','Edit'))
                                action += ' <a href="' + edit_url.replace("{id}",id) +'" title="Edit" class="edit-button btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>'
                                @endif
                                action += ' <a href="' + view_url.replace("{id}",id) +'" title="View" class="view-button btn btn-default btn-sm"><i class="fa fa-eye"></i></a>'
                                @if($discountplanapplied ==0)
                                @if(User::checkCategoryPermission('DestinationGroup','Delete'))
                                action += ' <a href="' + delete_url.replace("{id}",id) +'" title="Delete" class="delete-button btn btn-danger btn-sm"><i class="entypo-trash"></i></a>'
                                @endif
                                @endif
                                return action;
                            }
                        },  // 0 Created


                    ],
                    "oTableTools": {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "Export Data",
                                "sUrl": datagrid_url,
                                sButtonClass: "save-collection"
                            }
                        ]
                    },
                    "fnDrawCallback": function() {
                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                    }

                });
            });




            $('#filter_submit').trigger('click');
            //inst.myMethod('I am a method');
            $('#add-button').click(function(ev){
                ev.preventDefault();
                $('#modal-form').trigger("reset");
                $('#modal-list .panel-title').html('Add Destination Group');
                $("#modal-form [name=DestinationGroupID]").val("");
                $("#modal-form [name=DestinationGroupSetID]").val("{{$DestinationGroupSetID}}");
                $('#modal-form').attr("action",add_url);
                $('#modal-list').modal('show');

            });
            $('table tbody').on('click', '.edit-button', function (ev) {
                ev.preventDefault();
                $('#modal-form').trigger("reset");
                var edit_url  = $(this).attr("href");
                $('#modal-form').attr("action",edit_url);
                $('#modal-list .panel-title').html('Edit Destination Group');
                var cur_obj = $(this).prev("div.hiddenRowData");
                for(var i = 0 ; i< list_fields.length; i++){
                    $("#modal-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());

                }
                $('#modal-list').modal('show');
            });
            $('table tbody').on('click', '.delete-button', function (ev) {
                ev.preventDefault();
                result = confirm("Are you Sure?");
                if(result){
                    var delete_url  = $(this).attr("href");
                    submit_ajax_datatable( delete_url,"",0,data_table);
                }
                return false;
            });

            $("#modal-form").submit(function(e){
                e.preventDefault();
                var _url  = $(this).attr("action");
                submit_ajax_datatable(_url,$(this).serialize(),0,data_table);
            });

        });
    </script>


@stop
@section('footer_ext')
    @parent
    <div class="modal fade in" id="modal-list">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="modal-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add Destination Group</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Destination Group Name</label>
                                    <input type="text" name="Name" class="form-control" value="" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="DestinationGroupID">
                    <input type="hidden" name="DestinationGroupSetID">
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
