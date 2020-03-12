@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="package_filter" method="get" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Name</label>
                    {{ Form::text('PackageName', '', array("class"=>"form-control")) }}
                </div>
                {{--<div class="form-group">--}}
                    {{--<label for="field-1" class="control-label">Currency</label>--}}
                    {{--{{ Form::select('CurrencyId', Currency::getCurrencyDropdownIDList(),'', array("class"=>"select2 small")) }}--}}
                    {{--<input id="PackageRefresh" type="hidden" value="1">--}}
                    {{--<input id="editRateTableId" type="hidden" value="">--}}
                {{--</div>--}}
                <div class="form-group">
                    <label for="field-1" class="control-label">Status</label>
                        {{ Form::select('Status', [""=>"Both",1=>"Active",0=>"Inactive"], "", array("class"=>"form-control select2 small")) }}
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
            <strong>Packages</strong>
        </li>
    </ol>
    <h3>Packages</h3>
    <p class="text-right">
        <a href="#" data-action="showAddModal" data-type="package" data-modal="add-new-modal-package" class="btn btn-primary add-new">
            <i class="entypo-plus"></i>
            Add New Package
        </a>
        <a href="#" id="bulkDelete" data-action="showDeleteBulkActionModal" data-type="bulkAction" data-modal="add-new-BulkAction-modal-service" class="btn btn-danger">
            <i class="entypo-trash"></i>
            Bulk Delete
        </a>
    </p>

    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
            <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
            <th>PackageID</th>
            <th>Name</th>
            <th>Rate Table</th>
            {{--<th>Currency</th>--}}
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>


        </tbody>
    </table>

    <script type="text/javascript">
        var checkBoxArray =[];
        var $searchFilter = {};
        jQuery(document).ready(function ($) {
            $('#filter-button-toggle').show();

            $searchFilter.PackageName = $("#package_filter [name='PackageName']").val();
            $searchFilter.CurrencyId = $("#package_filter [name='CurrencyId']").val();
            $searchFilter.Status = $("#package_filter [name='Status']").val();

            data_table = $("#table-4").dataTable({

                "bProcessing":true,
                "bServerSide":true,
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "sAjaxSource": baseurl + "/package/ajax_datagrid",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "aaSorting"   : [[5, 'desc']],
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"PackageName","value":$searchFilter.PackageName});
                    aoData.push({"name":"CurrencyId","value":$searchFilter.CurrencyId});
                    aoData.push({"name":"status","value":$searchFilter.Status});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"PackageName","value":$searchFilter.PackageName},{ "name": "Export", "value": 1});
                    data_table_extra_params.push({"name":"CurrencyId","value":$searchFilter.CurrencyId},{ "name": "Export", "value": 1});
                    data_table_extra_params.push({"name":"Status","value":$searchFilter.Status},{ "name": "Export", "value": 1});
                },
                "aoColumns":
                        [
                            {"bSortable": false,
                                mRender: function(id, type, full) {
                                    // checkbox for bulk action
                                    return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                                }
                            },
                            {"bSortable": false,
                                mRender: function(id, type, full) {
                                    
                                    return full[0];
                                }
                            },
                            {"bSortable": false,
                                mRender: function(id, type, full) {
                                    
                                    return full[1];
                                }
                            }, //Name
                            {"bSortable": false,
                                mRender: function(id, type, full) {
                                    
                                    return full[2];
                                }
                            }, //Type
//                            { "bSortable": true }, //Gateway
                            {
                                "bSortable": false,
                                mRender: function (id, type, full) {

                                    var output = full[6] ;
                                        if(output==1){
                                            action='<i class="entypo-check" style="font-size:22px;color:green"></i>';
                                        }else{
                                            action='<i class="entypo-cancel" style="font-size:22px;color:red"></i>';
                                        }
                                        return action;
                                    }


                            },
                            {
                                "bSortable": true,
                                mRender: function ( id, type, full ) {
                                    var action , edit_ , show_, delete_ ;
                                    action = '<div class = "hiddenRowData" >';
                                    action += '<input type = "hidden"  name ="PackageName" value= "' + (full[1] != null ? full[1] : '') + '" / >';
                                    action += '<input type = "hidden"  name ="RateTable" value= "' + (full[2] != null ? full[2] : '') + '" / >';
                                    action += '<input type = "hidden"  name ="Currency" value= "' + (full[3] != null ? full[3] : '') + '" / >';
                                    action += '<input type = "hidden"  name ="RateTableId" value= "' + (full[4] != null ? full[4] : '') + '" / >';
                                    action += '<input type = "hidden"  name ="CurrencyId" value= "' + (full[5] != null ? full[5] : '') + '" / >';
                                    action += '<input type = "hidden"  name ="status" value= "' + (full[6] != null ? full[6] : '') + '" / >';
                                    action += '</div>';
                                    action += ' <a data-name = "'+full[1]+'" data-id="'+ full[0] +'" title="Edit" class="edit-package btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                                    action += ' <a data-id="'+ full[0] +'" title="Delete" class="delete-package btn btn-danger btn-sm"><i class="entypo-trash"></i></a>';
                                    return action;
                                }
                            }
                        ],
                "oTableTools":
                {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/package/exports/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/package/exports/csv",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function() {
                    $(".delete-package.btn").click(function(ev) {
                        response = confirm('Are you sure?');
                        if (response) {
                            var clear_url;
                            var id  = $(this).attr("data-id");
                            clear_url = baseurl + "/package/delete/"+id;
                            $(this).button('loading');
                            //get
                            $.get(clear_url, function (response) {
                                if (response.status == 'success') {
                                    $(this).button('reset');
                                    checkBoxArray = [];
                                    toastr.success(response.message, "Success", toastr_opts);
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                data_table.fnFilter('', 0);
                            });
                        }
                        return false;


                    });
                }
            });
            $("#bulkDelete").click(function(ev) {
                if(checkBoxArray.length < 1){
                    $("input.rowcheckbox:checkbox:checked").each(function() {
                        checkBoxArray.push($(this).val());
                    });
                }
                if(checkBoxArray.length > 0) {
                    response = confirm('Are you sure?');
                    if (response) {
                        var package_bulkdelete_url = baseurl + "/package/bulk-delete";
                        $.ajax({
                            url: package_bulkdelete_url,
                            type: 'POST',
                                data: "PackageIds=" + checkBoxArray,
                            dataType: 'json',
                            cache: false,
                            success: function (response) {
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    checkBoxArray = [];
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                data_table.fnFilter('', 0);
                            }
                        });
                    }
                } else {
                    toastr.error("Please select a row first.", "Error", toastr_opts);
                }
                return false;
            });

            $("#package_filter").submit(function(e) {
                e.preventDefault();

                $searchFilter.PackageName = $("#package_filter [name='PackageName']").val();
                $searchFilter.CurrencyId = $("#package_filter [name='CurrencyId']").val();
                $searchFilter.Status = $("#package_filter [name='Status']").val();

                data_table.fnFilter('', 0);
                return false;
            });

            $(".dataTables_wrapper select").select2({
                minimumResultsForSearch: -1
            });

            // Highlighted rows
            $("#table-4 tbody input[type=checkbox]").each(function (i, el) {
                var $this = $(el),
                        $p = $this.closest('tr');

                $(el).on('change', function () {
                    var is_checked = $this.is(':checked');
                    $p[is_checked ? 'addClass' : 'removeClass']('highlight');
                });
            });

            $("#selectall").click(function (ev) {
                var is_checked = $(this).is(':checked');

                if(checkBoxArray != null || checkBoxArray == undefined)
                    checkBoxArray = [];

                $('#table-4 tbody tr').each(function (i, el) {
                    var txtValue = $(this).find('.rowcheckbox').prop("checked", true).val();

                    if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                        if (is_checked) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                            $(this).addClass('selected');
                            if(txtValue)
                                checkBoxArray.push(txtValue);

                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                            $(this).removeClass('selected');
                            checkBoxArray = [];
                        }
                    }
                });
            });
            // select single record which row is clicked
            $('#table-4 tbody').on('click', 'tr', function () {

                var txtValue = $(this).find('.rowcheckbox').prop("checked", true).val();
                var checked = $(this).is(':checked')
                if (checked == '') {
                    if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                        $(this).toggleClass('selected');
                        if ($(this).hasClass('selected')) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                            checkBoxArray.push(txtValue);
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                            checkBoxArray.pop(txtValue);

                        }
                    }
                }
            });


            // Highlighted rows
            $("#table-4 tbody input[type=checkbox]").each(function (i, el) {
                var $this = $(el),
                        $p = $this.closest('tr');

                $(el).on('change', function () {
                    var is_checked = $this.is(':checked');

                    $p[is_checked ? 'addClass' : 'removeClass']('highlight');
                });
            });

            // Replace Checboxes
            $(".pagination a").click(function (ev) {
                replaceCheckboxes();
            });

            $('.add-new').on('click',function(){
                $('.package-id').css('display','none');
            })

            $('table tbody').on('click','.edit-package',function(ev){
                ev.preventDefault();
                ev.stopPropagation();
                $('#add-new-package-form').trigger("reset");
                $('.package-id').css('display','block');

                PackageName = $(this).prev("div.hiddenRowData").find("input[name='PackageName']").val();
                RateTableId = $(this).prev("div.hiddenRowData").find("input[name='RateTableId']").val();
                CurrencyId  = $(this).prev("div.hiddenRowData").find("input[name='CurrencyId']").val();
                Status  = $(this).prev("div.hiddenRowData").find("input[name='status']").val();

                $("#add-new-package-form [name='Name']").val(PackageName);
                $("#add-new-package-form [name='PackageId']").val($(this).attr('data-id'));
                $('.package-text').val($(this).attr('data-id'));
                $("#add-new-package-form [name='RateTableId']").val(RateTableId).trigger("change");
                $("#add-new-package-form [name='CurrencyId']").val(CurrencyId).trigger("change");
                $("#add-new-package-form [name='status']").val(Status).prop('checked', Status == 1);
                $('#add-new-modal-package h4').html('Edit Package');
                $("#editRateTableId").val(RateTableId);
                $('#add-new-modal-package').modal('show');
            });

       });

    </script>
    @include('package.packagemodal')
@stop