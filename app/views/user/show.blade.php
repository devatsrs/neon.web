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
            "iDisplayLength": {{Config::get('app.pageSize')}},
            "sPaginationType": "bootstrap",
            //"sDom": 'T<"clear">lfrtip',
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[1, 'asc']],
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
                                var action, edit_, show_;
                                edit_ = "{{ URL::to('users/edit/{id}')}}";
                                edit_ = edit_.replace('{id}', id);
                                action =  '';
                                <?php if(User::checkCategoryPermission('Users','Edit')){ ?>
                                    action = '<a href="' + edit_ + '" class="btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                                <?php } ?>
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
            }

        });


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
    });

</script>
@stop            