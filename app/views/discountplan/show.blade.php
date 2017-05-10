@extends('layout.main')
@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('discount_plan')}}">Discount Plan </a>
        </li>
        <li>
            <a><span>{{discountplan_dropbox($id)}}</span></a>
        </li>
        <li class="active">
            <strong>Discount ({{$name}})</strong>
        </li>
    </ol>
    <h3>Discount</h3>

    @include('includes.errors')
    @include('includes.success')
    <p style="text-align: right;">
        @if(User::checkCategoryPermission('DiscountPlan','Edit'))
        @if($discountplanapplied == 0)
        <a  id="add-button" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New</a>
        @endif
        @endif
        <a href="{{URL::to('/discount_plan')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
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
            <th width="15%">Destination Group</th>
            <th width="15%">Threshold</th>
            <th width="15%">Discount</th>
            <th width="15%">Unlimited</th>
            <th width="10%">Modified By</th>
            <th width="10%">Modified Date</th>
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
            var list_fields  = ["Name","Threshold","Discount","UnlimitedText","UpdatedBy","updated_at","DiscountID","DiscountPlanID","DestinationGroupID","DiscountSchemeID","Service","Unlimited"];
            //public_vars.$body = $("body");
            var $search = {};
            var add_url = baseurl + "/discount/store";
            var edit_url = baseurl + "/discount/update/{id}";
            var delete_url = baseurl + "/discount/delete/{id}";
            var datagrid_url = baseurl + "/discount/ajax_datagrid";

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
                    "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[0, 'asc']],
                    "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "DiscountPlanID", "value": '{{$id}}'}


                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "DiscountPlanID", "value": '{{$id}}'},
                                {"name": "Export", "value": 1}
                        );

                    },
                    "aoColumns": [
                        {  "bSortable": true },  // 0 Name
                        {  "bSortable": true },  // 1 Threshold
                        {  "bSortable": true },  // 2 Discount
                        {  "bSortable": true },  // 3 UnlimitedText
                        {  "bSortable": true },  // 4 UpdatedBy
                        {  "bSortable": true },  // 5 updated_at
                        {  "bSortable": false,
                            mRender: function ( id, type, full ) {
                                action = '<div class = "hiddenRowData" >';
                                for(var i = 0 ; i< list_fields.length; i++){
                                    action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                }
                                action += '</div>';
                                @if($discountplanapplied == 0)
                                    @if(User::checkCategoryPermission('DiscountPlan','Edit'))
                                        action += ' <a href="' + edit_url.replace("{id}",id) +'" title="Edit" class="edit-button btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>'
                                    @endif
                                    @if(User::checkCategoryPermission('DiscountPlan','Delete'))
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
                $('#modal-list h4').html('Add Discount');
                $("#modal-form [name=DiscountID]").val("");
                $("#modal-form [name=DiscountSchemeID]").val("");
                $("#modal-form [name=DestinationGroupID]").select2().select2('val',"");
                $("#modal-form [name=Service]").select2().select2('val',"");

                $('#modal-form').attr("action",add_url);
                $('#modal-list').modal('show');
            });
            $('table tbody').on('click', '.edit-button', function (ev) {
                ev.preventDefault();
                $('#modal-form').trigger("reset");
                var edit_url  = $(this).attr("href");
                $('#modal-form').attr("action",edit_url);
                $('#modal-list h4').html('Edit Discount');
                var cur_obj = $(this).prev("div.hiddenRowData");
                for(var i = 0 ; i< list_fields.length; i++){
                    $("#modal-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    if(list_fields[i] == 'DestinationGroupID'){
                        $("#modal-form [name='"+list_fields[i]+"']").select2().select2('val',cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    }else if(list_fields[i] == 'Service'){
                        $("#modal-form [name='"+list_fields[i]+"']").select2().select2('val',cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    }else if(list_fields[i] == 'Unlimited') {
                        if (cur_obj.find("[name='Unlimited']").val() == 1) {
                            $('#modal-form [name="Unlimited"]').prop('checked', true)
                        } else {
                            $('#modal-form [name="Unlimited"]').prop('checked', false)
                        }
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
                if($('#modal-form [name="Unlimited"]').prop("checked") == false){
                    if($('#modal-form [name="Threshold"]').val() == 0){
                        setTimeout(function(){
                            $(".btn").button('reset');
                        },10);
                        toastr.error("Please Enter Value Greater then zero", "Error", toastr_opts);
                        return false;
                    }
                }
                var _url  = $(this).attr("action");
                submit_ajax_datatable(_url,$(this).serialize(),0,data_table);
            });

            $('#modal-form [name="Unlimited"]').on( "change",function(e){
                if($('#modal-form [name="Unlimited"]').prop("checked") == true){
                    $('#modal-form [name="Threshold"]').val(0);
                    $('#modal-form [name="Threshold"]').attr('readonly',true);
                }else {
                    $('#modal-form [name="Threshold"]').attr('readonly',false);
                }
            });

        });
    </script>

@stop
@section('footer_ext')
    @parent
    <div class="modal fade custom-width in " id="modal-list">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="modal-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add Discount</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Destination Group*</label>
                                    {{Form::select('DestinationGroupID', $DestinationGroup, '' ,array("id"=>"DestinationGroupID","class"=>"form-control select2"))}}

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Service*</label>
                                    {{Form::select('Service',DiscountPlan::$discount_service, '' ,array("class"=>"form-control select2"))}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Threshold*</label>
                                    <input type="text" name="Threshold" class="form-control" value="" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Discount(%)*</label>
                                    <input type="text" name="Discount" class="form-control" value="" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Unlimited</label>
                                    <div class="clear">
                                        <p class="make-switch switch-small">
                                            <input id="Unlimited" name="Unlimited" type="checkbox" value="1" >
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="DiscountID">
                    <input type="hidden" name="DiscountSchemeID">
                    <input type="hidden" name="DiscountPlanID" value="{{$id}}">
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
