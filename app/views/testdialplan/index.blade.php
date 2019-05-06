@extends('layout.main')

@section('filter')

    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate class="form-horizontal form-groups-bordered validate" method="post" id="testdialplan_form">
                {{--<div class="form-group">
                    <label for="Search" class="control-label">Search</label>
                    <input class="form-control" name="Search" id="Search"  type="text" >
                </div>--}}
                <div class="form-group A">
                    <label for="field-1" class="control-label">Phone Number</label>
                    <input type="text" value="" placeholder="Phone Number" id="field-1" required="" class="form-control" name="DestinationCode">
                </div>

                <div class="form-group  S">
                    <label class="control-label" for="field-1">Routing Profile</label>
                    {{Form::select('routingprofile', [null=>'All Available Routing Profiles'] + $routingprofile + ['DefaultLCR'=>'Default LCR'], (isset($RoutingProfileToCustomer->RoutingProfileID)?$RoutingProfileToCustomer->RoutingProfileID:'' ) ,array("class"=>"select2 small form-control1"));}}
                </div>
                <div class="form-group S">
                    <label class="control-label" for="field-1">Country</label>
                    {{Form::select('countryList', $countryList ,'',array("class"=>"select2 small form-control1"))}}
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Date and Time</label>
                    <div class="row">
                        <div class="col-sm-6">
                            <input type="text" name="StartDate"  class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d')}}"/>
                        </div>
                        <div class="col-md-6 select_hour">
                            <input type="text" name="StartHour" data-minute-step="30"   data-show-meridian="false" data-default-time="00:00" value="00:00"  data-template="dropdown" class="form-control timepicker">
                        </div>
                    </div>
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
            <strong>Test Dial Plan</strong>
        </li>
    </ol>
    <h3>Test Dial Plan</h3><br>

    @include('includes.errors')
    @include('includes.success')

    
    <div class="tab-content">
        
        <table class="table table-bordered datatable" id="table-4" style="display:none;">
                <thead>
                <tr>
                    
                    <th></th>
                    <th>Destination</th>
                    <th>Vendor</th>
                    <th>Connection</th>
                    <th>Rate</th>
                    <th>Routing Category</th>
                    <th>Routing Category Order</th>
                    <th>Preference</th>
                    <th>IP</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
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
$('#filter-button-toggle').show();
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
            $("#testdialplan_form").submit(function (e) {
                $('#table-4').show();
                $searchFilter.DestinationCode = $("#testdialplan_form [name='DestinationCode']").val();
                $searchFilter.routingprofile = $("#testdialplan_form select[name='routingprofile']").val();
                $searchFilter.countryList = $("#testdialplan_form select[name='countryList']").val();
                $searchFilter.StartDate = $("#testdialplan_form [name='StartDate']").val();
                $searchFilter.StartHour = $("#testdialplan_form [name='StartHour']").val();

                
                data_table = $("#table-4").dataTable({
                    "bDestroy": true,
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": baseurl + "/testdialplan/ajax_datagrid",
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[1, 'asc']],
                    "fnServerParams": function (aoData) {
                        aoData.push({"name": "DestinationCode", "value": $searchFilter.DestinationCode}, {"name": "routingprofile","value": $searchFilter.routingprofile},{"name": "countryList", "value": $searchFilter.countryList},
                                {"name": "StartDate", "value": $searchFilter.StartDate},{"name": "StartHour", "value": $searchFilter.StartHour});
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push({"name": "DestinationCode","value": $searchFilter.DestinationCode}, {"name": "routingprofile", "value": $searchFilter.routingprofile},{"name": "countryList", "value": $searchFilter.countryList},
                                {"name": "StartDate", "value": $searchFilter.StartDate},{"name": "StartHour", "value": $searchFilter.StartHour});
                    },
                    "aoColumns": [
                        {"bSortable": false, mRender: function (id, type, full) { return id;}},
                        {"bSortable": false, mRender: function (id, type, full) { return full[1];}},
                        {"bSortable": false, mRender: function (id, type, full) {return full[3]; }},
                        {"bSortable": false, mRender: function (id, type, full) { return full[4];}},
                        {"bSortable": false, mRender: function (id, type, full) { return full[12]; }},
                        {"bSortable": false, mRender: function (id, type, full) { return full[14];}},
                            
                            {"bSortable": false, mRender: function (id, type, full) { return full[16];}},
                                {"bSortable": false, mRender: function (id, type, full) { return full[15];}},
                        {"bSortable": false, mRender: function (id, type, full) { return full[6];}},
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
                    },
                    "fnRowCallback": function (nRow, aData, iDisplayIndex) {
                        $("td:nth-child(1)", nRow).html(iDisplayIndex + 1);
                        return nRow;
                    }
                });
                $("#selectcheckbox").append('');
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
                "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
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
        .RoutingMode{
            width: 200px;
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

@stop