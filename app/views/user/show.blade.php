@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Users</a>
        </li>
    </ol>

    <h3>Users</h3>
    <div class="tab-content">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="clear"></div>
            <br>
            @if( User::can('UsersController.store') && User::can('UsersController.add') )
                <p style="text-align: right;">
                    <a href="#" id="add-new-user" class="btn btn-primary ">
                        <i class="entypo-plus"></i>
                        Add New User
                    </a>
                </p>
            @endif
            <div class="row">
                <div class="col-md-12">
                    <form id="user_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
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
                                    <label class="col-sm-2 control-label">Status</label>
                                    <div class="col-sm-2">
                                        <p class="make-switch switch-small">
                                            <input id="UserStatus" name="UserStatus" type="checkbox" value="1" checked="checked">
                                        </p>
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
                    <th width="10%">Status</th>
                    <th width="20%">First Name</th>
                    <th width="20%">Last Name</th>
                    <th width="20%">Email</th>
                    <th width="20%">Role</th>
                    <th width="10%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                var list_fields  = ['Status','FirstName','LastName','EmailAddress','AdminUser','UserID'];
                var $searchFilter = {};
                var update_new_url;
                var postdata;
                jQuery(document).ready(function ($) {
                    public_vars.$body = $("body");
                    $searchFilter.status = $("#user_filter [name='UserStatus']").prop("checked");
                    data_table = $("#table-4").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/users/ajax_datagrid",
                        "fnServerParams": function (aoData) {
                            aoData.push({ "name": "status", "value": $searchFilter.status });
                            data_table_extra_params.length = 0;
                            data_table_extra_params.push({ "name": "status", "value": $searchFilter.status },{ "name": "Export", "value": 1});

                        },
                        "iDisplayLength": '{{Config::get('app.pageSize')}}',
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[1, 'asc']],
                        "aoColumns": [
                            {"bVisible": false, "bSortable": true },  // 1 status
                            {  "bSortable": true },  // 2 first name
                            {  "bSortable": true },  // 3 last name
                            {  "bSortable": true },  // 4 email
                            {  "bSortable": true },  // 5 role
                            {                       //  5  Action
                                "bSortable": false,
                                mRender: function (id, type, full) {

                                    action = '<div class = "hiddenRowData" >';
                                    for(var i = 0 ; i< list_fields.length; i++){
                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                    }
                                    action += '</div>';
                                    if('{{User::can('UsersController.edit')}}') {
                                        action += ' <a data-name = "' + full[0] + '" data-id="' + full[5] + '" class="edit-user btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                                    }
                                    return action;
                                }
                            }
                        ],
                        "oTableTools": {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "Export Data",
                                    "sUrl": baseurl + "/users/ajax_datagrid", //baseurl + "/generate_xls.php",
                                    sButtonClass: "save-collection"
                                }
                            ]
                        },
                        "fnDrawCallback": function () {
                            $(".dataTables_wrapper select").select2({
                                minimumResultsForSearch: -1
                            });
                        }

                    });
                    $("#user_filter").submit(function(e){
                        e.preventDefault();
                        $searchFilter.status = $("#user_filter [name='UserStatus']").prop("checked");
                        data_table.fnFilter('', 0);
                        return false;
                    });


                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                    $('table tbody').on('click', '.edit-user', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $('#add-edit-user-form').trigger("reset");
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){

                            if(list_fields[i] == 'Status'){
                                if(cur_obj.find("input[name='"+list_fields[i]+"']").val() == 1){
                                    $('#add-edit-user-form [name="Status"]').prop('checked',true)
                                }else{
                                    $('#add-edit-user-form [name="Status"]').prop('checked',false)
                                }
                            }else if(list_fields[i] == 'AdminUser'){
                                if(cur_obj.find("input[name='"+list_fields[i]+"']").val() == 'Admin'){
                                    $('#add-edit-user-form [name="AdminUser"]').prop('checked',true)
                                }else{
                                    $('#add-edit-user-form [name="AdminUser"]').prop('checked',false)
                                }
                            }else{
                                $("#add-edit-user-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }
                        }
                        $('#add-edit-modal-user h4').html('Edit User');
                        $('#add-edit-modal-user').modal('show');
                    });


                    $('#add-new-user').click(function (ev) {
                        ev.preventDefault();
                        $('#add-edit-user-form').trigger("reset");
                        $("#add-edit-user-form [name='UserID']").val('');
                        $('#add-edit-modal-user h4').html('Add New User');
                        $('#add-edit-modal-user').modal('show');
                    });


                    $('#add-edit-user-form').submit(function(e){
                        e.preventDefault();
                        var UserID = $("#add-edit-user-form [name='UserID']").val()
                        if( typeof UserID != 'undefined' && UserID != ''){
                            update_new_url = baseurl + '/users/update/'+UserID;
                        }else{
                            update_new_url = baseurl + '/users/store';
                        }
                        $.ajax({
                            url: update_new_url,  //Server script to process data
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                if(response.status =='success'){
                                    toastr.success(response.message, "Success", toastr_opts);
                                    $('#add-edit-modal-user').modal('hide');
                                    data_table.fnFilter('', 0);
                                }else{
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                $("#product-update").button('reset');
                            },
                            // Form data
                            data: $('#add-edit-user-form').serialize(),
                            //Options to tell jQuery not to process data or worry about content-type.
                            cache: false
                        });
                    });
                });

                // Replace Checboxes
                $(".pagination a").click(function (ev) {
                    replaceCheckboxes();
                });

                $('body').on('click', '.btn.delete', function (e) {
                    e.preventDefault();

                    response = confirm('Are you sure?');
                    if( typeof $(this).attr("data-redirect")=='undefined'){
                        $(this).attr("data-redirect",'{{ URL::previous() }}')
                    }
                    redirect = $(this).attr("data-redirect");
                    if (response) {

                        $.ajax({
                            url: $(this).attr("href"),
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
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
            </script>

            @include('includes.errors')
            @include('includes.success')

        </div>
    </div>
@stop
@section('footer_ext')
    @parent

    <div class="modal fade" id="add-edit-modal-user">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-edit-user-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New product</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">First Name</label>
                                    <input type="text" name="FirstName" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Last Name</label>
                                    <input type="text" name="LastName" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Email</label>
                                    <input type="text" name="EmailAddress" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Password</label>
                                    <input type="password" name="password" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Active</label>
                                    <p class="make-switch switch-small">
                                        <input id="Status" name="Status" type="checkbox" value="1" checked >
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Admin User</label>
                                    <p class="make-switch switch-small">
                                        <input id="AdminUser" name="AdminUser" type="checkbox" value="1" checked >
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="UserID" />
                    <div class="modal-footer">
                        <button type="submit" id="product-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
