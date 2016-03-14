@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Vendor file upload template</a>
        </li>
    </ol>

    <h3>Vendor Template</h3>
    <div class="tab-content">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="clear"></div>
            <br>
            @if( User::can('VendorFileUploadTemplateController.create') && User::can('VendorFileUploadTemplateController.store'))
                <p style="text-align: right;">
                    <a href="{{URL::to('/uploadtemplate/create')}}" class="btn btn-primary ">
                        <i class="entypo-plus"></i>
                        Add New template
                    </a>
                </p>
            @endif
            <table class="table table-bordered datatable" id="table-4">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Created at</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    data_table = $("#table-4").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/uploadtemplate/ajax_datagrid",
                        "iDisplayLength": '{{Config::get('app.pageSize')}}',
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[0, 'asc']],
                        "aoColumns": [
                            {  "bSortable": true },  // 1 Title
                            {  "bSortable": true },  // 2 Created at
                            {                       //  5  Action
                                "bSortable": false,
                                mRender: function (id, type, full) {
                                    var delete_ = "{{ URL::to('uploadtemplate/{id}/delete')}}";
                                    var edit = "{{ URL::to('uploadtemplate/{id}/edit')}}";
                                    delete_  = delete_ .replace( '{id}', id );
                                    edit  = edit .replace( '{id}', id );
                                    var action = '';
                                    if('{{User::can('VendorFileUploadTemplateController.edit')}}' && '{{User::can('VendorFileUploadTemplateController.update')}}') {
                                        action += '<a href="'+edit+'" class="edit-config btn btn-default btn-sm btn-icon icon-left" data-name="Edit Template"><i class="entypo-pencil"></i>Edit </a>';
                                    }
                                    if('{{User::can('VendorFileUploadTemplateController.delete')}}'){
                                        action += ' <a href="'+delete_+'" class="btn delete btn-danger btn-default btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete </a>';
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
                                    "sUrl": baseurl + "/uploadtemplate/ajax_datagrid", //baseurl + "/generate_xls.php",
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
                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                });

                // Replace Checboxes
                $(".pagination a").click(function (ev) {
                    replaceCheckboxes();
                });

                $('body').on('click', '.btn.delete', function (e) {
                    e.preventDefault();

                    response = confirm('Are you sure?');

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
@stop
