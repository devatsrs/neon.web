@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate class="form-horizontal form-groups-bordered validate" action="javascript:void(0);" method="get" id="fileuploadtemplate-search">
                <div class="form-group">
                    <label for="Title" class="control-label">Title</label>
                    <input class="form-control" name="Title" id="Title"  type="text" >
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Template Type</label>
                    {{ Form::select('Type', $fileUploadTemplates, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Upload Template</a>
        </li>
    </ol>

    <h3>Upload Template</h3>
    <div class="tab-content">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="clear"></div>
            <br>
            @if( User::is_admin())
                <p style="text-align: right;">
                    <a href="{{URL::to('/uploadtemplate/create')}}" class="btn btn-primary ">
                        <i class="entypo-plus"></i>
                        Add New
                    </a>
                </p>
            @endif
            <table class="table table-bordered datatable" id="table-4">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Template Type</th>
                    <th>Created at</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                var $searchFilter = {};
                var list_fields  = ['Title','Type','created_at','FileUploadTemplateID'];

                jQuery(document).ready(function ($) {
                    $('#filter-button-toggle').show();

                    $("#fileuploadtemplate-search").submit(function(e) {
                        return templateDataTable();
                    });

                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
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

                });

                function templateDataTable() {
                    $searchFilter.Title = Title = $("#fileuploadtemplate-search input[name='Title']").val();
                    $searchFilter.Type  = Type  = $("#fileuploadtemplate-search select[name='Type']").val();

                    data_table = $("#table-4").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/uploadtemplate/ajax_datagrid/type",
                        "fnServerParams": function (aoData) {
                            aoData.push({"name": "Title", "value": Title},{"name": "Type", "value": Type});
                            data_table_extra_params.length = 0;
                            data_table_extra_params.push({"name": "Title", "value": Title},{"name": "Type", "value": Type},{"name":"Export","value":1});
                        },
                        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[0, 'asc']],
                        "aoColumns": [
                            {  "bSortable": true },  // 1 Template Name (Title)
                            {  "bSortable": true },  // 2 Template Type
                            {  "bSortable": false },  // 3 Created at
                            {                       //  4  Action
                                "bSortable": false,
                                mRender: function (id, type, full) {
                                    var delete_ = "{{ URL::to('uploadtemplate/{id}/delete')}}";
                                    var edit = "{{ URL::to('uploadtemplate/{id}/edit')}}";
                                    delete_  = delete_ .replace( '{id}', id );
                                    edit  = edit .replace( '{id}', id );
                                    var action = '';
                                    <?php if(User::checkCategoryPermission('UploadFileTemplate','Edit') ){ ?>
                                            action += ' <a href="'+edit+'" title="Edit" class="edit-config btn btn-default btn-sm" data-name="Edit Template"><i class="entypo-pencil"></i>&nbsp;</a>';
                                    <?php } ?>
                                            <?php if(User::checkCategoryPermission('UploadFileTemplate','Delete') ){ ?>
                                            action += ' <a href="'+delete_+'" title="Delete" class="btn delete btn-danger btn-default btn-sm"><i class="entypo-trash"></i></a>';
                                    <?php } ?>
                                            return action;
                                }
                            }
                        ],
                        "oTableTools": {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "EXCEL",
                                    "sUrl": baseurl + "/uploadtemplate/ajax_datagrid/xlsx", //baseurl + "/generate_xls.php",
                                    sButtonClass: "save-collection btn-sm"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/uploadtemplate/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
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
                }
            </script>

            @include('includes.errors')
            @include('includes.success')

        </div>
    </div>
@stop
@section('footer_ext')
    @parent
@stop
