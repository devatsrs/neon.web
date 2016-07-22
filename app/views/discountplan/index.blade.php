@extends('layout.main')
@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Discount Plan</strong>
        </li>
    </ol>
    <h3>Discount Plan</h3>

    @include('includes.errors')
    @include('includes.success')
    <p style="text-align: right;">
        <a  id="add-button" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add Discount Plan</a>
    </p>

    <div id="table_filter" method="get" action="#" >
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
                    <label for="field-1" class="col-sm-1 control-label">Name</label>
                    <div class="col-sm-2">
                        <input type="text" name="Name" class="form-control" value="" />
                    </div>
                </div>
                <p style="text-align: right;">
                    <button class="btn btn-primary btn-sm btn-icon icon-left" id="filter_submit">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </p>
            </div>
        </div>
    </div>
    <table id="table-list" class="table table-bordered datatable">
        <thead>
        <tr>
            <th width="20%">Name</th>
            <th width="15%">Modified By</th>
            <th width="15%">Modified Date</th>
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
        var DestinationGroupID;

        jQuery(document).ready(function ($) {
            var list_fields  = ["Name","UpdatedBy","updated_at","DiscountPlanID","DestinationGroupSetID","CurrencyID","Description"];
            //public_vars.$body = $("body");
            var $search = {};
            var add_url = baseurl + "/discount_plan/store";
            var edit_url = baseurl + "/discount_plan/update/{id}";
            var view_url = baseurl + "/discount_plan/show/{id}";
            var delete_url = baseurl + "/discount_plan/delete/{id}";
            var datagrid_url = baseurl + "/discount_plan/ajax_datagrid";

            $("#filter_submit").click(function(e) {
                e.preventDefault();

                $search.Name = $("#table_filter").find('[name="Name"]').val();
                $search.CodedeckID = $("#table_filter").find('[name="CodedeckID"]').val();
                data_table = $("#table-list").dataTable({
                    "bDestroy": true,
                    "bProcessing":true,
                    "bServerSide": true,
                    "sAjaxSource": datagrid_url,
                    "iDisplayLength": '{{Config::get('app.pageSize')}}',
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[0, 'asc']],
                    "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "CodedeckID", "value": $search.CodedeckID}

                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "CodedeckID", "value": $search.CodedeckID},
                                {"name": "Export", "value": 1}
                        );

                    },
                    "aoColumns": [
                        {  "bSortable": true },  // 0 Name
                        {  "bSortable": true },  // 2 UpdatedBy
                        {  "bSortable": true },  // 2 updated_at
                        {  "bSortable": false,
                            mRender: function ( id, type, full ) {
                                action = '<div class = "hiddenRowData" >';
                                for(var i = 0 ; i< list_fields.length; i++){
                                    action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                }
                                action += '</div>';
                                action += ' <a href="' + edit_url.replace("{id}",id) +'" class="edit-button btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>'
                                action += ' <a href="' + view_url.replace("{id}",id) +'" class="btn btn-default btn-sm btn-icon icon-left"><i class="fa fa-eye"></i>View</a>'
                                action += ' <a href="' + delete_url.replace("{id}",id) +'" class="delete-button btn btn-danger btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Delete </a>'
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
                $('#modal-list h4').html('Add Discount Plan');
                $("#modal-form [name=DiscountPlanID]").val("");
                $("#modal-form [name=DestinationGroupSetID]").select2().select2('val',"");
                $("#modal-form [name=CurrencyID]").select2().select2('val',"");

                $('#modal-form').attr("action",add_url);
                $('#modal-list').modal('show');
            });
            $('table tbody').on('click', '.edit-button', function (ev) {
                ev.preventDefault();
                $('#modal-form').trigger("reset");
                var edit_url  = $(this).attr("href");
                $('#modal-form').attr("action",edit_url);
                $('#modal-list h4').html('Edit Discount Plan');
                var cur_obj = $(this).prev("div.hiddenRowData");
                for(var i = 0 ; i< list_fields.length; i++){
                    $("#modal-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    if(list_fields[i] == 'DestinationGroupSetID'){
                        $("#modal-form [name='"+list_fields[i]+"']").select2().select2('val',cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    }else if(list_fields[i] == 'CurrencyID'){
                        $("#modal-form [name='"+list_fields[i]+"']").select2().select2('val',cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    }
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
    <div class="modal fade custom-width in " id="modal-list">
        <div class="modal-dialog" style="width: 60%;">
            <div class="modal-content">
                <form id="modal-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add Discount Plan</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Discount Plan Name*</label>
                                <input type="text" name="Name" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Description</label>
                                <input type="text" name="Description" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Destination Group Set*</label>
                                {{Form::select('DestinationGroupSetID', $DestinationGroupSets, '' ,array("id"=>"DestinationGroupSetID","class"=>"form-control select2"))}}

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Currency*</label>
                                {{Form::select('CurrencyID', $currencies, '' ,array("class"=>"form-control select2"))}}
                            </div>
                        </div>
                        </div>
                    </div>
                    <input type="hidden" name="DiscountPlanID">
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
