@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Notifications</a>
        </li>
    </ol>

    <h3>Notifications</h3>
    <div class="tab-content">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="clear"></div>
            <br>
            @if(User::checkCategoryPermission('Notification','Add'))
                <p style="text-align: right;">
                    <a href="{{URL::to('notification/create')}}" class=" btn btn-primary btn-sm btn-icon icon-left" id="add-notification">
                        <i class="entypo-plus"></i>
                        Add Notification
                    </a>
                </p>
            @endif
            <div class="row">
                <div class="col-md-12">
                    <form id="notification_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
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
                                    <label for="field-1" class="col-sm-1 control-label">Type</label>
                                    <div class="col-sm-3">
                                        {{Form::select('NotificationType',$notificationType,'',array("class"=>"select2 Notification_Type_dropdown"))}}
                                    </div>
                                </div>
                                <p style="text-align: right;">
                                    <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left" id="notification_submit">
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
                    <th width="20%">Type</th>
                    <th width="30%">Email Address</th>
                    <th width="10%">Status</th>
                    <th width="10%">Created Date</th>
                    <th width="10%">Created By</th>
                    <th width="20%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                var list_fields  = ["NotificationType","EmailAddresses","Status","created_at","CreatedBy","NotificationID"];
                var NotificationType = JSON.parse('{{json_encode(Notification::$type)}}');
                var $search = {};
                var update_new_url;
                var postdata;
                var notification_add_url = baseurl + "/notification/store";
                var notification_edit_url = baseurl + "/notification/{id}/update";
                var notification_delete_url = baseurl + "/notification/{id}/delete";
                var notification_datagrid_url = baseurl + "/notification/ajax_datagrid/type";
                jQuery(document).ready(function ($) {
                    data_table_char = $("#table-4").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": notification_datagrid_url,
                        "fnServerParams": function (aoData) {
                            aoData.push({"name": "NotificationType", "value": $search.NotificationType});

                            data_table_extra_params.length = 0;
                            data_table_extra_params.push({"name": "NotificationType", "value": $search.NotificationType},{"name":"Export","value":1});

                        },
                        "iDisplayLength": '{{Config::get('app.pageSize')}}',
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[0, 'asc']],
                        "aoColumns": [
                            {"bSortable": true,
                                mRender:function(id,type,full){
                                    return NotificationType[id];
                                }

                            },  // 0 Notification
                            {"bSortable": true},  // 1 Email Addresses
                            {
                                mRender: function (status, type, full) {
                                    if (status == 1)
                                        return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                                    else
                                        return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                                }
                            }, //2   Status
                            {"bSortable": true},  // 2 Created At
                            {"bSortable": true},  // 3 Created By
                            {                        // 9 Action
                                "bSortable": false,
                                mRender: function (id, type, full) {
                                    action = '<div class = "hiddenRowData" >';
                                    for (var i = 0; i < list_fields.length; i++) {
                                        action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + full[i] + '" / >';
                                    }
                                    action += '</div>';
                                    @if(User::checkCategoryPermission('Notification','Update'))
                                        action += ' <a href="' + notification_edit_url.replace("{id}", id) + '" class="edit-notification btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>'
                                    @endif
                                    @if(User::checkCategoryPermission('Notification','Delete'))
                                        action += ' <a href="' + notification_delete_url.replace("{id}", id) + '" class="delete-notification btn btn-danger btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Delete </a>'
                                    @endif
                                    return action;
                                }
                            }
                        ],
                        "oTableTools": {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "EXCEL",
                                    "sUrl": baseurl + "/notification/ajax_datagrid/xlsx",
                                    sButtonClass: "save-collection btn-sm"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/notification/ajax_datagrid/csv",
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
                    $("#notification_submit").click(function(e) {

                        e.preventDefault();
                        public_vars.$body = $("body");
                        $search.NotificationType = $('#notification_filter [name="NotificationType"]').val();
                        data_table_char.fnFilter('', 0);
                        return false;
                    });


                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                    $('#notification_submit').trigger('click');
                    //inst.myMethod('I am a method');
                    $('#add-notification').click(function(ev){
                        ev.preventDefault();
                        $('#notification-form').trigger("reset");
                        $('#modal-notification h4').html('Add Notification');
                        $("#notification-form [name='NotificationEmailAddresses']").val('');
                        $(".js-example-disabled").prop("disabled", false);
                        var selectBox = $("#notification-form [name='NotificationType']");
                        selectBox.val('').trigger("change");
                        selectBox.prop("disabled", false);
                        $('.tax').removeClass('hidden');

                        $('#notification-form').attr("action",notification_add_url);
                        $('#modal-notification').modal('show');
                    });
                    $('table tbody').on('click', '.edit-notification', function (ev) {
                        ev.preventDefault();
                        $('#notification-form').trigger("reset");
                        var edit_url  = $(this).attr("href");
                        $('#notification-form').attr("action",edit_url);
                        $('#modal-notification h4').html('Edit Notification');
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){
                            $("#notification-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            if(list_fields[i] == 'NotificationType'){
                                var selectBox = $("#notification-form [name='"+list_fields[i]+"']");
                                selectBox.val(cur_obj.find("input[name='"+list_fields[i]+"']").val()).trigger("change");
                                selectBox.prop("disabled", true);
                            }
                            if(list_fields[i] == 'Status') {
                                if (cur_obj.find("input[name='Status']").val() == 1) {
                                    $("#notification-form [name='"+list_fields[i]+"']").prop('checked', true)
                                } else {
                                    $("#notification-form [name='"+list_fields[i]+"']").prop('checked', false)
                                }
                            }
                        }
                        $('#modal-notification').modal('show');
                    });
                    $('table tbody').on('click', '.delete-notification', function (ev) {
                        ev.preventDefault();
                        result = confirm("Are you Sure?");
                        if(result){
                            var delete_url  = $(this).attr("href");
                            submit_ajax_datatable( delete_url,"",0,data_table_char);
                        }
                        return false;
                    });

                    $("#notification-form").submit(function(e){
                        e.preventDefault();
                        var _url  = $(this).attr("action");
                        submit_ajax_datatable(_url,$(this).serialize(),0,data_table_char);

                    });

                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });
                });

            </script>

            @include('includes.errors')
            @include('includes.success')

        </div>
    </div>
@stop
@section('footer_ext')
    @parent

    <div class="modal fade in" id="modal-notification">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="notification-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Additional Charges</h4>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Type</label>
                                {{Form::select('NotificationType',$notificationType,'',array("class"=>"select2 small product_dropdown"))}}
                                <input type="hidden" name="NotificationID" />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Email Addresses</label>
                                <input type="text" name="EmailAddresses" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Active</label>
                                <div class="clear">
                                    <p class="make-switch switch-small">
                                        <input type="checkbox" checked=""  name="Status" value="0">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
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
