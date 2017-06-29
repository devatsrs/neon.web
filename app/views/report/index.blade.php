@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Report</a>
        </li>
    </ol>
    <h3>Report</h3>
    @include('includes.errors')
    @include('includes.success')

    <div class="row">
        <div class="col-md-12">
            <form id="report_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
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
                            <div class="col-sm-3">
                                <input class="form-control" name="Name" type="text" >
                            </div>
                        </div>
                        <p style="text-align: right;">
                            <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left" id="report_submit">
                                <i class="entypo-search"></i>
                                Search
                            </button>
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @if(User::checkCategoryPermission('Report','Add'))
        <p style="text-align: right;">
            <a href="{{URL::to('report/create')}}" class=" btn btn-primary btn-sm btn-icon icon-left" id="add-report">
                <i class="entypo-plus"></i>
                Add New
            </a>
        </p>
    @endif
    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
            <th width="60%">Name</th>
            <th width="40%">Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <script type="text/javascript">
        var list_fields_index  = ["Name","ReportID"];

        var $search = {};
        var report_edit_url = baseurl + "/report/edit/{id}";
        var report_delete_url = baseurl + "/report/delete/{id}";
        var report_datagrid_url = baseurl + "/report/ajax_datagrid/type";
        jQuery(document).ready(function ($) {
            data_table_char = $("#table-4").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": report_datagrid_url,
                "fnServerParams": function (aoData) {
                    aoData.push({"name": "Name", "value": $search.Name});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name": "Name", "value": $search.Name},{"name":"Export","value":1});

                },
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[0, 'asc']],
                "aoColumns": [
                    {"bSortable": true},  // 1 Email Addresses
                    {                        // 9 Action
                        "bSortable": false,
                        mRender: function (id, type, full) {
                            var action;
                            action = '<div class = "hiddenRowData pull-left" >';
                            for (var i = 0; i < list_fields_index.length; i++) {
                                action += '<input disabled type = "hidden"  name = "' + list_fields_index[i] + '"       value = "' + full[i] + '" / >';
                            }
                            action += '</div>';
                            @if(User::checkCategoryPermission('Report','Update'))
                                action += ' <a href="' + report_edit_url.replace("{id}", id) + '" class="btn btn-default btn-sm tooltip-primary" data-original-title="Edit" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-pencil"></i>&nbsp;</a>';
                            @endif
                                    @if(User::checkCategoryPermission('Report','Delete'))
                                action += ' <a href="' + report_delete_url.replace("{id}", id) + '" class="delete-report btn btn-danger btn-sm tooltip-primary" data-original-title="Delete" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-trash"></i></a>';
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
                            "sUrl": baseurl + "/report/ajax_datagrid/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/report/ajax_datagrid/csv",
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
            $("#report_submit").click(function(e) {

                e.preventDefault();
                public_vars.$body = $("body");
                $search.Name = $('#report_filter [name="Name"]').val();
                data_table_char.fnFilter('', 0);
                return false;
            });


            // Replace Checboxes
            $(".pagination a").click(function (ev) {
                replaceCheckboxes();
            });

            $('table tbody').on('click', '.delete-report', function (ev) {
                ev.preventDefault();
                result = confirm("Are you Sure?");
                if(result){
                    var delete_url  = $(this).attr("href");
                    submit_ajax_datatable( delete_url,"",0,data_table_char);
                }
                return false;
            });

            $("#report-form").submit(function(e){
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

@stop
