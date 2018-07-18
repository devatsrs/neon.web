@extends('layout.main')

@section('content')
    <style>

        #table-4_processing {
            top: 500px !important;
        }

    </style>
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Vendor Profiling</strong>
        </li>
    </ol>
    <h3>Vendor Profiling</h3>

    @include('includes.errors')
    @include('includes.success')

    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab1" data-toggle="tab">Bulk Vendor Update</a></li>
        <li><a href="#tab2" data-toggle="tab">Vendor Blocking</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab1">
            <div class="row">
                <div class="col-md-6">
                    <h4>Vendor Active</h4>

                    <form id="active-vendor-form" method="post">
                        <div class="form-group">
                            <div class="col-sm-6">
                                <input type="text" name="txtleftbulkvendor" class="form-control"
                                       placeholder="Vendor Active Search" value="">
                            </div>
                            <div class="col-sm-10 scroll">
                                <table class="clear table table-bordered datatable controle vendoractive">
                                    <thead>
                                    <tr>
                                        <th width="10%">
                                            <div class="checkbox">
                                                <input type="checkbox" name="checkbox[]" class="selectall">
                                            </div>
                                        </th>
                                        <th width="90%">Vendors</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(count($active_vendor))
                                        @foreach($active_vendor as $index=>$active)
                                            <tr class="draggable" search="{{strtolower($active)}}">
                                                <td>
                                                    <div class="checkbox">
                                                        {{Form::checkbox("AccountID[]" , $index ) }}
                                                    </div>
                                                </td>
                                                <td>{{$active}}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-sm-10">
                                <p style="text-align: right;">
                                    <button type="submit" id="vendor-deactive"
                                            class="save btn btn-primary btn-sm btn-icon icon-left"
                                            data-loading-text="Loading...">
                                        <i class="entypo-floppy"></i>
                                        Deactivte
                                    </button>
                                    <input value="deactivate" name="action" type="hidden">
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-6">
                    <h4>Vendor DeActive</h4>

                    <form id="deactive-vendor-form" method="post">
                        <div class="form-group">
                            <div class="col-sm-6">
                                <input type="text" name="txtrightbulkvendor" class="form-control"
                                       placeholder="Vendor Deactive Search" value="">
                            </div>
                            <div class="col-sm-10 scroll">
                                <table class="clear table table-bordered datatable controle vendordeactive">
                                    <thead>
                                    <tr>
                                        <th width="10%">
                                            <div class="checkbox">
                                                <input type="checkbox" name="checkbox[]" class="selectall">
                                            </div>
                                        </th>
                                        <th width="90%">Vendors</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(count($inactive_vendor))
                                        @foreach($inactive_vendor as $index=>$active)
                                            <tr search="{{strtolower($active)}}">
                                                <td>
                                                    <div class="checkbox">
                                                        {{Form::checkbox("AccountID[]" , $index ) }}
                                                    </div>
                                                </td>
                                                <td>{{$active}}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-sm-10">
                                <p style="text-align: right;">
                                    <button type="submit" id="role-update"
                                            class="save btn btn-primary btn-sm btn-icon icon-left"
                                            data-loading-text="Loading...">
                                        <i class="entypo-floppy"></i>
                                        Activate
                                    </button>
                                    <input value="activate" name="action" type="hidden">
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="tab-pane " id="tab2">
            <form id="block_by_country_form" method="post" class="form-horizontal form-groups-bordered validate"
                  novalidate="novalidate">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Block by Country
                        </div>
                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-1 control-label">Country</label>
                            <div class="col-sm-2">
                                {{ Form::select('Country', $countries, '' , array("class"=>"select2","multiple")) }}
                                <input type="hidden" name="Code" value=""/>
                            </div>
                            <label class="col-sm-1 control-label">Trunk</label>
                            <div class="col-sm-2">
                                {{ Form::select('Trunk', $trunks, $trunk_keys, array("class"=>"select2")) }}
                            </div>
                            <label class="col-sm-1 control-label">Timezone</label>
                            <div class="col-sm-2">
                                {{ Form::select('Timezones', $Timezones, '', array("class"=>"select2")) }}
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div style="text-align: right;padding:10px 0 ">
                <button id="blockSelectedCountry" class="btn btn-primary btn-sm btn-icon icon-left"
                        data-loading-text="Loading...">
                    <i class="entypo-floppy"></i>
                    Block
                </button>
                <button id="unblockSelectedCountry" class="btn btn-danger btn-sm btn-icon icon-left"
                        data-loading-text="Loading...">
                    <i class="entypo-cancel"></i>
                    Unblock
                </button>
            </div>
            <form id="block_by_code_form" method="post" class="form-horizontal form-groups-bordered validate"
                  novalidate="novalidate">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Block by Code
                        </div>
                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-1 control-label">Code</label>
                            <div class="col-sm-2">
                                <input type="text" value="" placeholder="Code" id="field-1" class="form-control" name="Code">
                            </div>
                            <label for="field-1" class="col-sm-1 control-label">Country</label>
                            <div class="col-sm-2">
                                {{ Form::select('Country', $countriesCode, '' , array("class"=>"select2","multiple")) }}
                            </div>
                            <label class="col-sm-1 control-label">Trunk</label>
                            <div class="col-sm-2">
                                {{ Form::select('Trunk', $trunks, $trunk_keys, array("class"=>"select2")) }}
                            </div>
                            <label class="col-sm-1 control-label">Timezone</label>
                            <div class="col-sm-2">
                                {{ Form::select('Timezones', $Timezones, '', array("class"=>"select2")) }}
                            </div>
                        </div>

                        <p style="text-align: right">
                            <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                                <i class="entypo-search"></i>
                                Search
                            </button>
                        </p>
                    </div>
                </div>
            </form>
            <div style="text-align: right;padding:10px 0 ">
                <button id="blockSelectedCode" class="btn btn-primary btn-sm btn-icon icon-left"
                        data-loading-text="Loading...">
                    <i class="entypo-floppy"></i>
                    Block
                </button>
                <button id="unblockSelectedCode" class="btn btn-danger btn-sm btn-icon icon-left"
                        data-loading-text="Loading...">
                    <i class="entypo-cancel"></i>
                    Unblock
                </button>
            </div>
            <table class="table table-bordered datatable" id="table-4">
                <thead>
                <tr>
                    <th>
                        <div class="checkbox ">
                            <input type="checkbox" id="selectall" name="checkbox[]" class="">
                        </div>
                    </th>
                    <th>Code</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        var action_click;
        var block_by;
        var $searchFilter = {};
        var checked = '';
        $searchFilter.SelectedCodes = '';
        var code_check = 0;
        var first_call = true;
        $(function () {
            //load_vendorGrid();

            $('.nav-tabs li a').click(function (e) {
                e.preventDefault();
            });
            $('input[type="text"]').on('keyup', function () {
                var s = $(this).val();
                var table = $(this).parents('form').find('table');
                $(table).find('tbody tr:hidden').show();
                $(table).find('tbody tr').each(function () {
                    if (this.getAttribute("search").indexOf(s.toLowerCase()) != 0) {
                        $(this).hide();
                    }
                });
            });//key up.
            $('.selectall').on('click', function () {
                var self = $(this);
                var is_checked = $(self).is(':checked');
                self.parents('table').find('tbody tr').each(function (i, el) {
                    if (is_checked) {
                        if ($(this).is(':visible')) {
                            $(this).find('input[type="checkbox"]').prop("checked", true);
                            $(this).addClass('selected');
                        }
                    } else {
                        $(this).find('input[type="checkbox"]').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                });
            });
            $(document).on('click', '#active-vendor-form .table tbody tr,#deactive-vendor-form .table tbody tr', function () {
                var self = $(this);
                if (self.hasClass('selected')) {
                    $(this).find('input[type="checkbox"]').prop("checked", false);
                    $(this).removeClass('selected');
                } else {
                    $(this).find('input[type="checkbox"]').prop("checked", true);
                    $(this).addClass('selected');
                }
            });
            $('#deactive-vendor-form').on('submit', function (e) {
                e.preventDefault();
                var ajax_full_url = '{{Url::to('/active_deactivate_vendor')}}';
                action_click = 'activate';
                submit_ajax(ajax_full_url, $('#deactive-vendor-form').serialize());

                return false;
            });
            $('#active-vendor-form').on('submit', function (e) {
                e.preventDefault();
                action_click = 'deactivate';
                var ajax_full_url = '{{Url::to('/active_deactivate_vendor')}}';
                submit_ajax(ajax_full_url, $('#active-vendor-form').serialize());
                return false;
            });
            $('#bulk-update-vendor-code-form').on('submit', function (e) {
                e.preventDefault();
                var ajax_full_url = '{{Url::to('/vendor_profiling/block_unblockcode')}}';
                var Codes = [];
                var criteria = '';
                var criteriaCountry = '';
                if (code_check == 1) { //bit for indicating Code Blocking unblocking
                    var ajax_data = $('#block_by_code_form').serialize() + '&' + $('#bulk-update-vendor-code-form').serialize();
                    if ($('#selectallbutton').is(':checked')) {
                        criteriaCountry = $searchFilter.Country;
                        ajax_data += '&criteria=1&criteriaCountry=' + criteriaCountry;
                    } else {
                        $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                            //console.log($(this).val());
                            Code = $(this).val();
                            if (Code !== null && Code !== 'null') {
                                Codes[i++] = Code;
                            }

                        });
                    }
                    if (Codes.length > 0) {
                        ajax_data += '&Codes=' + Codes.join(",");
                    }
                } else {//Country blocking unblocking
                    var ajax_data = $('#block_by_country_form').serialize() + '&' + $('#bulk-update-vendor-code-form').serialize();
                }
                if (action_click == 'block' || action_click == 'unblock') {
                    ajax_data += '&action=' + action_click;
                }
                if (block_by == 'code' || block_by == 'country') {
                    ajax_data += '&block_by=' + block_by;
                }
                submit_ajax(ajax_full_url, ajax_data);
                return false;
            });

            $(document).ajaxSuccess(function (event, response, ajaxOptions, responsedata) {
                if (responsedata.status == 'success') {
                    if (action_click == 'deactivate') {
                        var selected_vendor = $("#active-vendor-form .table tbody").find("tr.selected");
                        var appedtable = $("#deactive-vendor-form .table tbody");
                        filgrid($("#active-vendor-form .table"), responsedata.active_vendor);
                        filgrid($("#deactive-vendor-form .table"), responsedata.inactive_vendor);
                        //selected_vendor.appendTo(appedtable);
                        //sort_table($("#deactive-vendor-form .table tbody"));
                        //sort_table($("#active-vendor-form .table tbody"));

                    }
                    if (action_click == 'activate') {
                        var selected_vendor = $("#deactive-vendor-form .table tbody").find("tr.selected");
                        var appedtable = $("#active-vendor-form .table tbody");
                        filgrid($("#active-vendor-form .table"), responsedata.active_vendor);
                        filgrid($("#deactive-vendor-form .table"), responsedata.inactive_vendor);
                        //selected_vendor.appendTo(appedtable);
                        //sort_table($("#deactive-vendor-form .table tbody"));
                        //sort_table($("#active-vendor-form .table tbody"));
                    }

                }
            });
            $("#block_by_code_form").submit(function (e) {
                $searchFilter.Trunk = $("#block_by_code_form select[name='Trunk']").val();
                $searchFilter.Timezones = $("#block_by_code_form select[name='Timezones']").val();
                $searchFilter.Country = $("#block_by_code_form select[name='Country']").val();
                $searchFilter.Code = $("#block_by_code_form [name='Code']").val();
                if (typeof $searchFilter.Trunk == 'undefined' || $searchFilter.Trunk == '') {
                    toastr.error("Please Select a Trunk", "Error", toastr_opts);
                    return false;
                }

                data_table = $("#table-4").dataTable({
                    "bDestroy": true,
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": baseurl + "/vendor_profiling/ajax_datagrid",
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[1, 'asc']],
                    "fnServerParams": function (aoData) {
                        aoData.push({"name": "Trunk", "value": $searchFilter.Trunk}, {"name": "Country","value": $searchFilter.Country}, {"name": "Code", "value": $searchFilter.Code}, {"name": "Timezones", "value": $searchFilter.Timezones});
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push({"name": "Trunk","value": $searchFilter.Trunk}, {"name": "Country", "value": $searchFilter.Country}, {"name": "Code","value": $searchFilter.Code}, {"name": "Timezones", "value": $searchFilter.Timezones});
                    },
                    "aoColumns": [
                        {
                            "bSortable": false,
                            mRender: function (id, type, full) {
                                var action = '<div class = "hiddenRowData" >';
                                action += '<div class="pull-left"><input type="checkbox" class="checkbox rowcheckbox" value="' + full[1] + '" name="Code[]"></div>';
                                action += '</div>';
                                return action;
                            }

                        },
                        {"bSortable": true}  // 1 Code


                    ],
                    "oTableTools": {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "Export Data",
                                "sUrl": baseurl + "/invoice/ajax_datagrid", //baseurl + "/generate_xls.php",
                                sButtonClass: "save-collection"
                            }
                        ]
                    },
                    "fnDrawCallback": function () {
                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                        $('#table-4 tbody tr').each(function (i, el) {
                            if (checked != '') {
                                $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                $(this).addClass('selected');
                                $('#selectallbutton').prop("checked", true);
                            } else {
                                $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                ;
                                $(this).removeClass('selected');
                            }
                        });

                        $('#selectallbutton').click(function (ev) {
                            if ($(this).is(':checked')) {
                                checked = 'checked=checked disabled';
                                $("#selectall").prop("checked", true).prop('disabled', true);
                                if (!$('#changeSelectedInvoice').hasClass('hidden')) {
                                    $('#table-4 tbody tr').each(function (i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                        $(this).addClass('selected');
                                    });
                                }
                            } else {
                                checked = '';
                                $("#selectall").prop("checked", false).prop('disabled', false);
                                if (!$('#changeSelectedInvoice').hasClass('hidden')) {
                                    $('#table-4 tbody tr').each(function (i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                        $(this).removeClass('selected');
                                    });
                                }
                            }
                        });
                    }

                });
                $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
                return false;
            });
            //Select Row on click
            $('#table-4 tbody').on('click', 'tr', function () {
                if (checked == '') {
                    $(this).toggleClass('selected');
                    if ($(this).hasClass("selected")) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                    }
                }
            });
            //Select Row on click
            $('#table-5 tbody').on('click', 'tr', function () {
                $(this).toggleClass('selected');
                if ($(this).hasClass("selected")) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false);
                }
            });

            // Select all
            $("#selectall").click(function (ev) {
                var is_checked = $(this).is(':checked');
                $('#table-4 tbody tr').each(function (i, el) {
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                });
            });

            $("#blockSelectedCode,#unblockSelectedCode").click(function () {
                var id = $(this).attr('id');
                if (typeof $searchFilter.Trunk == 'undefined' || $searchFilter.Trunk == '') {
                    toastr.error("Please Search Code First", "Error", toastr_opts);
                    return false;
                } else if ($searchFilter.Country == '0') {
                    toastr.error("Please Search Code First", "Error", toastr_opts);
                    return false;
                }
                $('#bulk-update-vendor-code-form').find('[name="countries"]').val('');
                if ($('#table-4 tr .rowcheckbox:checked').length == 0) {
                    toastr.error("Please Select at least one Code.", "Error", toastr_opts);
                    return true;
                } else {
                    var Codes = [];
                    if (!$('#selectallbutton').is(':checked')) {
                        $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                            var parent = $(this).parents('tr');
                            RateCodetext = parent.find('td').eq(1).text();
                            Code = $(this).val();
                            if (Code !== null && Code !== 'null') {
                                Codes[i++] = Code;
                            }
                        });
                    }
                    if (Codes.length > 0) {
                        var x = Codes.join(",");
                        x = x.charAt(0) == ',' ? x.substr(1) : x;
                        $searchFilter.SelectedCodes = x;
                    }
                }
                if (id == 'blockSelectedCode') {
                    action_click = 'block';
                    text = 'Bulk Vendor Block';
                } else {
                    action_click = 'unblock';
                    text = 'Bulk Vendor unBlock';
                }
                code_check = 1;
                block_by = 'code';
                var modal = $("#modal-bulkaccount");
                modal.find('.modal-header h4').text(text);
                modal.modal('show');
                //popupgrid();
                initCustomerGrid('table-5');
            });

            $("#blockSelectedCountry,#unblockSelectedCountry").click(function () {
                var id = $(this).attr('id');
                $searchFilter.Trunk = $("#block_by_country_form select[name='Trunk']").val();
                $searchFilter.Timezones = $("#block_by_country_form select[name='Timezones']").val();
                $searchFilter.Country = $("#block_by_country_form select[name='Country']").val();
                $searchFilter.Code = '';
                $searchFilter.SelectedCodes = '';
                if (typeof $searchFilter.Trunk == 'undefined' || $searchFilter.Trunk == '') {
                    toastr.error("Please Select Trunk First", "Error", toastr_opts);
                    return false;
                }
                if ($searchFilter.Country == null || $searchFilter.Country == '') {
                    toastr.error("Please Select at least one Country", "Error", toastr_opts);
                    return false;
                }
                $('#bulk-update-vendor-code-form').find('[name="countries"]').val($searchFilter.Country);
                if (id == 'blockSelectedCountry') {
                    action_click = 'block';
                    text = 'Bulk Vendor Block';
                } else {
                    action_click = 'unblock';
                    text = 'Bulk Vendor unBlock';
                }
                code_check = 0;
                block_by = 'country';
                var modal = $("#modal-bulkaccount");
                modal.find('.modal-header h4').text(text);
                modal.modal('show');
                //popupgrid();
                initCustomerGrid('table-5');
            });
        });

        function sort_table(table) {
            $(table).find("tr").sort(function (a, b) {
                var keyA = $('td', a).text();
                var keyB = $('td', b).text();
                if ($($sort).hasClass('asc')) {
                    return (keyA > keyB) ? 1 : 0;
                } else {
                    return (keyA > keyB) ? 1 : 0;
                }
            });
        }
        function initCustomerGrid(tableID, OwnerFilter) {
            first_call = true;
            var criteria = 0;
            if ($('#selectallbutton').is(':checked')) {
                criteria = 1;
            }
            $searchFilter.OwnerFilter = "";
            var data_table_new = $("#" + tableID).dataTable({
                "bDestroy": true, // Destroy when resubmit form
                "sDom": "<'row'<'col-xs-12 border_left'f>r>t",
                "bProcessing": false,
                "bServerSide": false,
                "bPaginate": false,
                "fnServerParams": function (aoData) {
                    aoData.push(
                            {"name": "Trunk", "value": $searchFilter.Trunk},
                            {"name": "Timezones", "value": $searchFilter.Timezones},
                            {"name": "OwnerFilter", "value": $searchFilter.OwnerFilter},
                            {"name": "Country", "value": $searchFilter.Country},
                            {"name": "Code", "value": $searchFilter.Code},
                            {"name": "SelectedCodes", "value": $searchFilter.SelectedCodes},
                            {"name": "action", "value": action_click},
                            {"name": "criteria", "value": criteria},
                            {"name": "block_by", "value": block_by}
                    );
                },
                "sAjaxSource": baseurl + "/vendor_rates/0/search_vendor_grid",
                "aoColumns": [
                    {
                        "bSortable": false, //Code
                        mRender: function (id, type, full) {
                            return '<div class="checkbox "><input type="checkbox" name="AccountID[]" value="' + id + '" class="rowcheckbox" ></div>';
                        }
                    },
                    {}
                ],

                "fnDrawCallback": function () {
                    $(".selectallcust").click(function (ev) {
                        var is_checked = $(this).is(':checked');
                        $('#' + tableID + ' tbody tr').each(function (i, el) {
                            if (is_checked) {
                                $(this).find('.rowcheckbox').prop("checked", true);
                                $(this).addClass('selected');
                            } else {
                                $(this).find('.rowcheckbox').prop("checked", false);
                                $(this).removeClass('selected');
                            }
                        });
                    });
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }

            });
        }

        function filgrid(table, data) {
            var table = $(table);
            $(table).find('tbody > tr').remove();
            $(table).find('tbody').append('<tr search=""></tr>');
            $.each(data, function (key, val) {
                newRow = '<tr class="draggable" search="' + val.AccountName.toLowerCase() + '">';
                newRow += '  <td>';
                newRow += '    <div class="checkbox ">';
                newRow += '      <input type="checkbox" value="' + val.AccountID + '" name="AccountID[]" >';
                newRow += '    </div>';
                newRow += '  </td>';
                newRow += '  <td>' + val.AccountName + '</td>';
                newRow += '  </tr>';
                $(table).find('tbody>tr:last').after(newRow);
            });
        }

    </script>
    <style>
        .controle {
            width: 100%;
        }

        .scroll {
            height: 400px;
            overflow: auto;
        }

        .disabledTab {
            pointer-events: none;
        }

        .dataTables_filter label {
            display: none !important;
        }

        .dataTables_wrapper .export-data {
            display: none !important;
        }

        .border_left .dataTables_filter {
            border-left: 1px solid #eeeeee !important;
            border-top-left-radius: 3px;
        }

        #table-5_filter label {
            display: block !important;
        }

        #selectcheckbox {
            padding: 15px 10px;
        }
    </style>
@stop


@section('footer_ext')
    @parent


    <div class="modal fade " id="modal-bulkaccount">
        <div class="modal-dialog ">
            <div class="modal-content">

                <form id="bulk-update-vendor-code-form" method="post">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Bulk Vendor Block</h4>
                    </div>

                    <div class="modal-body">
                        <div id="search_static_val">
                        </div>
                        <input type="hidden" name="countries"/>

                        <div style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
                            <table class="table table-bordered datatable" id="table-5" style="margin-top:10px;">
                                <thead>
                                <tr>
                                    <th><input type="checkbox" class="selectallcust" name="AccountID[]"/></th>
                                    <th>Vendor Name</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="submit-bulk-data-new"
                                class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
                        </button>
                        <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop