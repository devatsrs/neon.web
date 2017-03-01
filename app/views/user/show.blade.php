@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{ URL::to('/dashboard') }}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Users</strong>
    </li>
</ol>
<h3>Users</h3>
<p class="text-right">
@if( User::checkCategoryPermission('Users','Add'))
    <a href="{{ URL::to('/users/add') }}" class="btn btn-primary">
        <i class="entypo-plus"></i>
        Add New
    </a>
@endif    
</p>
<div class="form-group">
    <label class="control-label">Status</label>
    <p class="make-switch switch-small mar-left-5 mar-top-5" >
        <input name="Status" id="UserStatus" type="checkbox" checked>
    </p>
</div>
<table class="table table-bordered datatable" id="table-4">
    <thead>
        <tr>
            <th>Status</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>




<script type="text/javascript">

    jQuery(document).ready(function($) {
        data_table = $("#table-4").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/users/ajax_datagrid/type",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            //"sDom": 'T<"clear">lfrtip',
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[1, 'asc']],
            "fnServerParams": function(aoData) {
                data_table_extra_params.length = 0;
                data_table_extra_params.push(
                        {"name":"Export","value":1}
                );
            },
            "aoColumns":
                    [
                        {"bVisible": false, "bSortable": false },
                        {"bSortable": true },
                        {"bSortable": true },
                        {"bSortable": true },
                        {"bSortable": true },
                        {
                            "bSortable": true,
                            mRender: function(id, type, full) {
                                id = full[6];
                                var action, edit_, show_;
                                edit_ = "{{ URL::to('users/edit/{id}')}}";
                                edit_ = edit_.replace('{id}', id);
                                action =  '';
                                if (full[5] == "1") {
                                    active_ = "{{ URL::to('/users/{id}/job_notification/0')}}";
                                    notification_link = ' <button href="' + active_ + '" title="Job Notification" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If enabled, system will notify you by email about Job status." data-original-title="Notification"  class="btn change_notification btn-success btn-sm popover-primary" data-loading-text="Loading..."><i class="glyphicon glyphicon-time"></i></button>';
                                } else {
                                    active_ = "{{ URL::to('/users/{id}/job_notification/1')}}";
                                    notification_link = ' <button href="' + active_ + '"  title="Job Notification"  data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If enabled, system will notify you by email about Job status." data-original-title="Notification"  class="btn change_notification btn-danger btn-sm popover-primary" data-loading-text="Loading..."><i class="glyphicon glyphicon-time"></i></button>';
                                }
                                <?php if(User::checkCategoryPermission('Users','Edit')){ ?>
                                    action = '<a href="' + edit_ + '" title="Edit" class="btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                                <?php } ?>
                                notification_link = notification_link.replace('{id}', id);
                                action += notification_link;
                                return action;
                            }
                        },
                    ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/users/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/users/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
            "fnDrawCallback": function() {
                $('#table-4 .popover-primary').each(function(i, el) {
                    var $this = $(el),
                            placement = attrDefault($this, 'placement', 'right'),
                            trigger = attrDefault($this, 'trigger', 'click'),
                            popover_class = $this.hasClass('popover-secondary') ? 'popover-secondary' : ($this.hasClass('popover-primary') ? 'popover-primary' : ($this.hasClass('popover-default') ? 'popover-default' : ''));

                    $this.popover({
                        placement: placement,
                        trigger: trigger
                    });

                    $this.on('shown.bs.popover', function(ev)
                    {
                        var $popover = $this.next();

                        $popover.addClass(popover_class);
                    });
                });
            }

        });
        data_table.fnFilter(1, 0);

        $('#UserStatus').change(function() {
            if ($(this).is(":checked")) {
                data_table.fnFilter(1, 0);  // 1st value 2nd column index
            } else {
                data_table.fnFilter(0, 0);
            }
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

        $(document).on('click','.change_notification',function (e) {
            $(this).button('loading');
            $.ajax({
                url: $(this).attr("href"),
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    $(this).button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        if ($('#UserStatus').is(":checked")) {
                            data_table.fnFilter(1, 0);  // 1st value 2nd column index
                        } else {
                            data_table.fnFilter(0, 0);
                        }
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


    });

</script>
@stop            