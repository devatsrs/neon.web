<style>
    #packageselectcheckbox{
        padding: 15px 10px;
    }
</style>
<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Package
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
        <div id="packagetable_filter" method="get" action="#">
            <div class="panel panel-primary panel-collapse" data-collapsed="1">
                <div class="panel-heading">
                    <div class="panel-title">
                        Filter
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body" style="display: none;">
                    <table width="100%">
                        <tr>
                            <td><label for="field-1" class="col-sm-1 control-label">Package</label></td>
                            <td width="15%">{{ Form::select('PackageName', $Packages , '' , array("class"=>"select2")) }}</td>
                            <td><label for="field-1" class="col-sm-1 control-label">ContractID</label></td>
                            <td><input type="text" name="PackageContractID" class="form-control" value="" /></td>
                            <td><label for="field-1" class="col-sm-1 control-label">Start Date</label></td>
                            <td><input type="text" data-date-format="yyyy-mm-dd"  class="form-control datepicker" id="PackageStartDate" name="PackageStartDate"></td>
                            <td><label for="field-1" class="col-sm-1 control-label">End Date</label></td>
                            <td><input type="text" data-date-format="yyyy-mm-dd"  class="form-control datepicker" id="PackageEndDate" name="PackageEndDate"></td>
                            <td><label for="field-1" class="col-sm-1 control-label">Status</label></td>
                            <td><p class="make-switch switch-small">
                                    <input id="PackageStatus" name="PackageStatus" type="checkbox" value="1" checked="checked">
                                </p></td>
                        </tr>
                        <tr>
                            <td colspan="10" align="right">
                                <button class="btn btn-primary btn-sm btn-icon icon-left" style="align:right;" id="packagetable_submit">
                                    <i class="entypo-search"></i>
                                    Search
                                </button>
                            </td>
                        </tr>
                    </table>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="pull-right">
                    @if( User::checkCategoryPermission('AuthenticationRule','Add,Delete'))
                        <button type="button" class="btn btn-primary btn-sm  dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="false">Action <span class="caret"></span></button>
                        <ul class="dropdown-menu dropdown-menu-left" role="menu"
                            style="background-color: #000; border-color: #000; margin-top:0px;">
                            @if( User::checkCategoryPermission('AuthenticationRule','Add'))
                                <li>
                                    <a class="create" id="add-packagetable" href="javascript:;">
                                        <i class="entypo-plus"></i>
                                        Add
                                    </a>
                                </li>
                            @endif
                            @if( User::checkCategoryPermission('AuthenticationRule','Delete'))
                                <li>
                                    <a class="generate_rate create" id="bulk-delete-package" href="javascript:;"
                                       style="width:100%">
                                        <i class="entypo-trash"></i>
                                        Delete
                                    </a>
                                </li>
                            @endif
                            @if( User::checkCategoryPermission('AuthenticationRule','Add'))
                                <li class="hidden">
                                    <a class="generate_rate create" id="changeSelectedCLI" href="javascript:;">
                                        <i class="entypo-pencil"></i>
                                        Change RateTable
                                    </a>
                                </li>
                            @endif
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        </br>
        <table id="table-packagetable" class="table table-bordered datatable table-packagetable">
            <thead>
            <tr>
                <th width="5%"><input type="checkbox" id="packageSelectall" name="checkbox[]" class="" /></th>
                <th width="12%">Package</th>
                <th width="15%">Default Rate Table</th>
                <th width="15%">Discount Plan</th>
                <th width="15%">Special Rate Table</th>
                <th width="15%">Contract ID</th>
                <th width="10%">Start Date</th>
                <th width="10%">End Date</th>
                <th width="2%">Status</th>
                <th width="30%">Action</th>

            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

    </div>
</div>
<script type="text/javascript">
    var AccountID = '{{$account->AccountID}}';
    var ServiceID='{{$ServiceID}}';
    var AccountServiceID='{{$AccountServiceID}}';

    var update_new_url;
    var postdata;
    $('table tbody').on('click', '.package-discount-table', function (ev) {
        var $this   = $(this);
        var AccountServicePackageID   = $this.prevAll("div.hiddenRowData").find("input[name='AccountServicePackageID']").val();
        var PackageDiscountPlan   = $this.prevAll("div.hiddenRowData").find("input[name='PackageDiscountPlan']").val();
        var PackageId   = $this.prevAll("div.hiddenRowData").find("input[name='PackageId']").val();
        if (PackageDiscountPlan == 0) {
            alert("Please select the valid package discount plan");
            return;
        }


        getPackageDiscountPlan($this,AccountID,AccountServiceID,PackageId,AccountServicePackageID);
    });
    $('table tbody').on('click', '.history-packagetable', function (ev) {
        var $this   = $(this);
        var RateTableID   = $this.prevAll("div.hiddenRowData").find("input[name='RateTableID']").val();
        if (RateTableID == 0) {
            alert("Please add rate table against the package");
            return;
        }
        var PackageId   = $this.prevAll("div.hiddenRowData").find("input[name='PackageId']").val();
        var Code   = $this.prevAll("div.hiddenRowData").find("input[name='PackageName']").val();
        var accessURL = baseurl + "/rate_tables/" + RateTableID + "/view?Code=" + Code;
        // location.href = accessURL;

        getArchiveRateTableDIDRates1($this,RateTableID,'',PackageId,'5');
    });

    $('table tbody').on('click', '.special-package-clitable', function (ev) {
        var $this   = $(this);
        var SpecialPackageRateTableID   = $this.prevAll("div.hiddenRowData").find("input[name='SpecialPackageRateTableID']").val();
        if (SpecialPackageRateTableID == 0) {
            alert("Please add rate table against the package");
            return;
        }
        var PackageId   = $this.prevAll("div.hiddenRowData").find("input[name='PackageId']").val();
        getArchiveRateTableDIDRates1($this,SpecialPackageRateTableID,'',PackageId,'5');
    });

    function getServiceName(ServiceID) {
        if (ServiceID == 1) {
            return "Volume";
        } else if (ServiceID == 2) {
            return "Minutes";
        }else if (ServiceID == 3) {
            return "Fixed";
        }
    }

    function getPackageDiscountPlan($clickedButton,AccountID,AccountServiceID,PackageId,AccountServicePackageID) {
        data_table_packagetable = $("#table-packagetable").DataTable();
        // alert($("#table-packagetable").DataTable().rows().count());
        var tr = $clickedButton.closest('tr');
        var checkIfSame = $clickedButton.find('i').hasClass('entypo-plus-squared');
        // alert('Called 1' + data_table_packagetable + "1" + tr.id);
        //alert(data_table_packagetable.rows().count());
        var row = data_table_packagetable.row(tr);
        tr.find('.details-control i').toggleClass('entypo-plus-squared entypo-minus-squared');
        tr.find('.special-package-clitable i, .history-packagetable i,.package-discount-table i').removeClass('entypo-plus-squared entypo-minus-squared');
        row.child.hide();
        tr.removeClass('shown');

        if(!checkIfSame)
            $.ajax({
                url: baseurl + "/discount_plan/PackageDiscountPlan",
                type: 'POST',
                data: "PackageId=" + PackageId + "&AccountID=" + AccountID
                + "&AccountServiceID=" + AccountServiceID
                + "&AccountServicePackageID=" + AccountServicePackageID,
                dataType: 'json',
                cache: false,
                success: function (response) {
                    if (response.status == 'success') {
                        ArchiveRates = response.data;
                        // alert(ArchiveRates);
                        $clickedButton.find('i').addClass('entypo-plus-squared entypo-minus-squared');
                        //tr.find('.details-control i').toggleClass('entypo-plus-squared entypo-minus-squared');
                        var table = $('<table class="table table-bordered datatable dataTable no-footer" style="margin-left: 0.1%;width: 50% !important;"></table>');
                        var header = "<thead><tr><th>PackageName</th><th>Service</th><th>Components</th><th>Discount</th>";
                        header += "</tr></thead>";
                        table.append(header);
                        var tbody = $("<tbody></tbody>");
                        ArchiveRates.forEach(function (data) {
                            // alert(data);
                            // alert(data['Rate']);
                            var html = "";
                            html += "<tr class='no-selection'>";


                            html += "<td>" + (data['PackageName'] != null ? data['PackageName'] : '') + "</td>";
                            html += "<td>" + (data['Service'] != null ? getServiceName(data['Service'])  : '') + "</td>";
                            html += "<td>" + (data['Components'] != null ? data['Components'] : '') + "</td>";
                            html += "<td>" + (data['Discount'] != null ? data['Discount'] : '') + "</td>";
                            html += "</tr>";
                            table.append(html);

                        })
                        table.append(tbody);
                        row.child(table).show();
                        row.child().addClass('no-selection child-row');
                        tr.addClass('shown');
                        //alert(data['Rate']);
                    } else {
                        ArchiveRates = {};
                        toastr.error(response.message, "Error", toastr_opts);
                    }

                }
            });
    }

    function getArchiveRateTableDIDRates1($clickedButton,RateTableID,TerminationRateTable,PackageId,CityTariff) {
        data_table_packagetable = $("#table-packagetable").DataTable();
        // alert($("#table-packagetable").DataTable().rows().count());
        var tr = $clickedButton.closest('tr');
        var checkIfSame = $clickedButton.find('i').hasClass('entypo-plus-squared');
        // alert('Called 1' + data_table_packagetable + "1" + tr.id);
        //alert(data_table_packagetable.rows().count());
        var row = data_table_packagetable.row(tr);
        tr.find('.details-control i').toggleClass('entypo-plus-squared entypo-minus-squared');
        tr.find('.package-discount-table i,.special-package-clitable i, .history-packagetable i').removeClass('entypo-plus-squared entypo-minus-squared');
        row.child.hide();
        tr.removeClass('shown');

        if(!checkIfSame)
            $.ajax({
                url: baseurl + "/rate_tables/search_ajax_datagrid_rates_account_service",
                type: 'POST',
                data: "AccessRateTable=" + RateTableID + "&PackageID=" + PackageId,
                dataType: 'json',
                cache: false,
                success: function (response) {
                    if (response.status == 'success') {
                        ArchiveRates = response.data;
                        // alert(ArchiveRates);
                        $clickedButton.find('i').addClass('entypo-plus-squared entypo-minus-squared');
                       // tr.find('.details-control i').toggleClass('entypo-plus-squared entypo-minus-squared');
                        var table = $('<table class="table table-bordered datatable dataTable no-footer" style="margin-left: 0.1%;width: 50% !important;"></table>');
                        var header = "<thead><tr><th>Time Of Day</th><th>One Off Cost</th><th>Monthly Cost</th><th>Cost Per Minute</th><th>Recording Cost per Minute</th>" +
                                "<th>Effective Date</th><th>End Date</th>";
                        header += "</tr></thead>";
                        table.append(header);
                        var tbody = $("<tbody></tbody>");
                        ArchiveRates.forEach(function (data) {
                            // alert(data);
                            // alert(data['Rate']);
                            var html = "";
                            html += "<tr class='no-selection'>";


                            html += "<td>" + (data['TimeTitle'] != null ? data['TimeTitle'] : '') + "</td>";
                            html += "<td>" + (data['OneOffCost'] != null ? data['OneOffCost'] : '') + "</td>";
                            html += "<td>" + (data['MonthlyCost'] != null ? data['MonthlyCost'] : '') + "</td>";
                            html += "<td>" + (data['PackageCostPerMinute'] != null ? data['PackageCostPerMinute'] : '') + "</td>";
                            html += "<td>" + (data['RecordingCostPerMinute'] != null ? data['RecordingCostPerMinute'] : '') + "</td>";
                            html += "<td>" + (data['EffectiveDate'] != null ? data['EffectiveDate'] : '') + "</td>";
                            html += "<td>" + (data['EndDate'] != null ? data['EndDate'] : '') + "</td>";
                            html += "</tr>";
                            table.append(html);

                        })
                        table.append(tbody);
                        row.child(table).show();
                        row.child().addClass('no-selection child-row');
                        tr.addClass('shown');
                        //alert(data['Rate']);
                    } else {
                        ArchiveRates = {};
                        toastr.error(response.message, "Error", toastr_opts);
                    }

                }
            });
    }

    jQuery(document).ready(function ($) {
        var package_list_fields = ["AccountServicePackageID", "PackageName","RateTableName", "PackageDiscountPlan","SpecialRateTableName",'ContractID', 'PackageStartDate', 'PackageEndDate',
            'Status','PackageId','RateTableID','AccountPackageDiscountPlanID','SpecialPackageRateTableID'];
        public_vars.$body = $("body");
        var $searchcli = {};
        var data_table_packagetable;
        var packagetable_add_url = baseurl + "/packagetable/store";
        var packagetable_update_url = baseurl + "/packagetable/update";
        var packagetable_delete_url = baseurl + "/packagetable/delete/{id}";
        var packagetable_datagrid_url = baseurl + "/packagetable/ajax_package_datagrid/" + AccountID;


        $("#packagetable_submit").click(function (e) {
            e.preventDefault();
            $searchcli.PackageName = $("#packagetable_filter").find('[name="PackageName"]').val();
            $searchcli.PackageContractID = $("#packagetable_filter").find('[name="PackageContractID"]').val();
            $searchcli.PackageStartDate = $("#packagetable_filter").find('[name="PackageStartDate"]').val();
            $searchcli.PackageEndDate = $("#packagetable_filter").find('[name="PackageEndDate"]').val();
            $searchcli.PackageStatus = $("#packagetable_filter").find('[name="PackageStatus"]').is(":checked") ? 1 : 0;

            if((typeof $searchcli.PackageEndDate  != 'undefined' && $searchcli.PackageEndDate != '')
                    && (typeof $searchcli.PackageStartDate  != 'undefined' && $searchcli.PackageStartDate != '')
                    && ($searchcli.PackageEndDate  <  $searchcli.PackageStartDate)){
                toastr.error("End Date for Package must be greater then start date", "Error", toastr_opts);
                return false;
            }

            data_table_packagetable = $("#table-packagetable").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,

                "sAjaxSource": packagetable_datagrid_url,
                "fnServerParams": function (aoData) {
                    aoData.push({
                                "name": "AccountID",
                                "value": AccountID
                            },
                            {
                                "name": "ServiceID",
                                "value": ServiceID
                            },
                            {
                                "name": "AccountServiceID",
                                "value": AccountServiceID
                            },
                            {
                                "name": "PackageName",
                                "value": $searchcli.PackageName
                            },
                            {
                                "name": "PackageStartDate",
                                "value": $searchcli.PackageStartDate
                            },
                            {
                                "name": "PackageEndDate",
                                "value": $searchcli.PackageEndDate
                            },
                            {
                                "name": "PackageContractID",
                                "value": $searchcli.PackageContractID
                            },
                            {
                                "name": "PackageStatus",
                                "value": $searchcli.PackageStatus
                            }
                    );

                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({
                                "name": "AccountID",
                                "value": AccountID
                            },
                            {
                                "name": "ServiceID",
                                "value": ServiceID
                            },
                            {
                                "name": "AccountServiceID",
                                "value": AccountServiceID
                            },
                            {
                                "name": "PackageName",
                                "value": $searchcli.PackageName
                            },
                            {
                                "name": "PackageStartDate",
                                "value": $searchcli.PackageStartDate
                            },
                            {
                                "name": "PackageEndDate",
                                "value": $searchcli.PackageEndDate
                            },
                            {
                                "name": "PackageContractID",
                                "value": $searchcli.PackageContractID
                            },
                            {
                                "name": "PackageStatus",
                                "value": $searchcli.PackageStatus
                            }
                    );

                },
                "iDisplayLength": 10,
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#packageselectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[0, 'asc']],
                "aoColumns": [
                    {
                        "bSortable": false,
                        mRender: function (id, type, full) {
                            return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                        }
                    },
                    {"bSortable": true},    // 1 Package
                    {
                        mRender: function(id, type, full) {
                            action = '';
                            action +='<div id="hiddenRowData1" class = "hiddenRowData" >';
                            for (var i = 0; i < package_list_fields.length; i++) {
                                action += '<input disabled type = "hidden"  name = "' + package_list_fields[i] + '"  value = "' + (full[i] != null ? full[i] : '') + '" / >';
                            }
                            action +='</div>';
                            action += full[2];
                            action += '<a title="Default Package Rate Table" href="javascript:;" class="history-packagetable btn btn-default btn-sm1"><i class="fa fa-eye"></i></a>&nbsp;';
                            return action;
                        },
                        "className":      'details-control',
                        "orderable":      false,
                        "data": null,
                        "defaultContent": ''
                    },    // Rate table
                    {
                        mRender: function(id, type, full) {
                        action = '';
                        action +='<div id="hiddenRowData1" class = "hiddenRowData" >';
                        for (var i = 0; i < package_list_fields.length; i++) {
                            action += '<input disabled type = "hidden"  name = "' + package_list_fields[i] + '"  value = "' + (full[i] != null ? full[i] : '') + '" / >';
                        }
                        action +='</div>';
                        if(full[3] != undefined && full[3] != '' && full[3] != 0) {
                                action += full[3];
                                action += '<a title="Package Discount Table" href="javascript:;" class="package-discount-table btn btn-default btn-sm1"><i class="fa fa-eye"></i></a>&nbsp;';
                        }
                        return action;
                    },
                        "className":      'details-control',
                        "orderable":      false,
                        "data": null,
                        "defaultContent": ''
                    },//Contract ID
                    {
                        mRender: function(id, type, full) {
                        action = '';
                        action +='<div id="hiddenRowData1" class = "hiddenRowData" >';
                        for (var i = 0; i < package_list_fields.length; i++) {
                            action += '<input disabled type = "hidden"  name = "' + package_list_fields[i] + '"  value = "' + (full[i] != null ? full[i] : '') + '" / >';
                        }
                        action +='</div>';
                        if(full[12] != undefined && full[12] != '' && full[12] != 0) {
                            action += full[4];
                            action += '<a title="Special Package Rate Table" href="javascript:;" class="special-package-clitable btn btn-default btn-sm1"><i class="fa fa-eye"></i></a>&nbsp;';
                        }
                        return action;
                    },
                        "className":      'details-control',
                        "orderable":      false,
                        "data": null,
                        "defaultContent": ''
                    },//Contract ID
                    {"bSortable": true},//Start Date
                    {"bSortable": true},//End Date
                    {"bSortable": true},



                    {  "bSortable": false,  //Status
                        mRender: function ( id, type, full ) {
                            console.log(full);
                            var action , edit_ , show_ , delete_;
                            //console.log(id);
                            if(full[8] == 1){
                                action='<i class="entypo-check" style="font-size:22px;color:green"></i>';
                            }else{
                                action='<i class="entypo-cancel" style="font-size:22px;color:red"></i>';
                            }
                            return action;
                        }
                    },  //0  Status', '', '', '
                    {                        // 10 Action
                        "bSortable": false,
                        mRender: function (id, type, full) {
                            action = '<div class = "hiddenRowData" >';
                            for (var i = 0; i < package_list_fields.length; i++) {
                                action += '<input disabled type = "hidden"  name = "' + package_list_fields[i] + '"  value = "' + (full[i] != null ? full[i] : '') + '" / >';
                            }
                            action += '</div>';
                            action += ' <a href="javascript:;" title="Edit" class="edit-packagetable btn btn-default btn-sm1 tooltip-primary" data-original-title="Edit" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-pencil"></i></a>';
                            action += ' <a href="' + packagetable_delete_url.replace("{id}", full[0]) + '" class="delete-packagetable btn btn-danger btn-sm1 tooltip-primary" data-original-title="Delete" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading..."><i class="entypo-trash"></i></a>'

                            return action;
                        }
                    }
                ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "Export Data",
                            "sUrl": packagetable_datagrid_url,
                            sButtonClass: "save-collection"
                        }
                    ]
                },
                "fnDrawCallback": function () {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });

                    default_row_selected('table-packagetable','packageSelectall','packageselectallbutton');
                    select_all_top('packageselectallbutton','table-packagetable','packageSelectall');
                }
            });
            setTimeout(function () {

                $("#packageselectcheckbox").append('<input type="checkbox" id="packageselectallbutton" name="packagecheckboxselect[]" class="" title="Select All Found Records" />');
            }, 10);


        });



        selected_all('packageSelectall', 'table-packagetable');

        table_row_select('table-packagetable', 'selectallbutton');


        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });
        var start = 1;
        if (start == 1) {
            $('#packagetable_submit').trigger('click');
        }
        start = 2;

        $('#add-packagetable').click(function (ev) {
            ev.preventDefault();
            $('#packagetable-form').trigger("reset");
            $('#modal-packagetable h4').html('Add Package');
            $("#packagetable-form [name=PackageID]").select2().select2('val', "");
            $("#packagetable-form [name=PackageRateTableID]").select2().select2('val', "");
            $("#packagetable-form [name=SpecialPackageRateTableID]").select2().select2('val', "");
            $("#packagetable-form [name=AccountPackageDiscountPlanID]").select2().select2('val', "");
            $("#packagetable-form [name=PackageStartDate]").val("");
            $("#packagetable-form [name=PackageEndDate]").val("");


            $("#packagetable-form [name=ContractID]").val("");
            $("#packagetable-form [name=Status]").prop("checked", 1 == 1).trigger("change");

            $('#packagetable-form').attr("action", packagetable_add_url);
            $('#modal-packagetable').modal('show');
        });

        $('table tbody').on('click', '.edit-packagetable', function (ev) {
            ev.preventDefault();

            var fields = $(this).prev(".hiddenRowData");
            $('#packagetable-form').trigger("reset");
            $('#modal-packagetable h4').html('Edit Package RateTable');

            /* var package_list_fields = ["AccountServicePackageID", "PackageName","RateTableName", "PackageDiscountPlan",'ContractID', 'PackageStartDate', 'PackageEndDate',
             'Status','PackageId','RateTableID','PackageDiscountPlanID'];*/
            var AccountServicePackageID = fields.find("[name='AccountServicePackageID']").val();
            var ContractID = fields.find("[name='ContractID']").val();
            var PackageStartDate = fields.find("[name='PackageStartDate']").val();
            var PackageEndDate = fields.find("[name='PackageEndDate']").val();
            var Status = fields.find("[name='Status']").val();
            var PackageId = fields.find("[name='PackageId']").val();
            var RateTableID = fields.find("[name='RateTableID']").val();
            var SpecialPackageRateTableID = fields.find("[name='SpecialPackageRateTableID']").val();
            var AccountPackageDiscountPlanID = fields.find("[name='AccountPackageDiscountPlanID']").val();


            RateTableID = RateTableID == 0 ? '' : RateTableID;
            SpecialPackageRateTableID = SpecialPackageRateTableID == 0 ? '' : SpecialPackageRateTableID;
            AccountPackageDiscountPlanID = AccountPackageDiscountPlanID == 0 ? '' : AccountPackageDiscountPlanID;




            $("#packagetable-form [name=PackageID]").select2().select2('val', PackageId);
            $("#packagetable-form [name=PackageRateTableID]").select2().select2('val', RateTableID);
            $("#packagetable-form [name=SpecialPackageRateTableID]").select2().select2('val', SpecialPackageRateTableID);
            $("#packagetable-form [name=AccountPackageDiscountPlanID]").select2().select2('val', AccountPackageDiscountPlanID);
            $("#packagetable-form [name=PackageStartDate]").val(PackageStartDate);
            $("#packagetable-form [name=PackageEndDate]").val(PackageEndDate);
            $("#packagetable-form [name=AccountServicePackageID]").val(AccountServicePackageID);

            $("#packagetable-form [name=ContractID]").val(ContractID);
            $("#packagetable-form [name=Status]").prop("checked", Status == 1).trigger("change");
            $('#packagetable-form').attr("action", packagetable_update_url);
            $('#modal-packagetable').modal('show');
        });

        $('table tbody').on('click', '.delete-packagetable', function (ev) {
            ev.preventDefault();
            $(this).button('loading');
            result = confirm("Are you Sure?");
            if (result) {

                var delete_url = $(this).attr("href");
                var AuthRule = $('#packagetable-form').find('input[name=AuthRule]').val();
                var AccountID = $('#packagetable-form').find('input[name=AccountID]').val();
                delete_cli(delete_url,data_table_packagetable,"AuthRule="+AuthRule+'&AccountID='+AccountID+'&ServiceID='+ServiceID)
            } else
                $(this).button('reset');
            return false;
        });

        $("#packagetable-form").submit(function (e) {
            e.preventDefault();
            var _url = $(this).attr("action");

            submit_ajax_datatable(_url, $(this).serialize(), 0, data_table_packagetable);
        });

        $("#bulk-delete-package").click(function (ev) {
            var criteria = '';
            if ($('#selectallbutton').is(':checked')) {
                criteria = JSON.stringify($searchcli);
            }
            var AccountServicePackageIDs = [];
            if (!confirm('Are you sure you want to delete selected Package?')) {
                return;
            }
            $('#table-packagetable tr .rowcheckbox:checked').each(function (i, el) {
                AccountServicePackageID = $(this).val();
                if (typeof AccountServicePackageID != 'undefined' && AccountServicePackageID != null && AccountServicePackageID != 'null') {
                    AccountServicePackageIDs[i] = AccountServicePackageID;
                }
            });
            var AuthRule = $('#packagetable-form').find('input[name=AuthRule]').val();
            var AccountID = $('#packagetable-form').find('input[name=AccountID]').val();

            if (AccountServicePackageIDs.length) {
                delete_cli(packagetable_delete_url.replace("{id}", 0),data_table_packagetable,'AccountServicePackageIDs=' + AccountServicePackageIDs.join(",") + '&criteria=' + criteria+'&AuthRule='+AuthRule+'&AccountID='+AccountID+'&ServiceID=' + ServiceID+'&AccountServiceID='+AccountServiceID)
            }
        });
        $("#changeSelectedCLI").click(function (ev) {
            var criteria = '';
            if ($('#selectallbutton').is(':checked')) {
                criteria = JSON.stringify($searchcli);
            }
            var CLIRateTableIDs = [];
            $('#table-packagetable tr .rowcheckbox:checked').each(function (i, el) {
                CLIRateTableID = $(this).val();
                if (typeof CLIRateTableID != 'undefined' && CLIRateTableID != null && CLIRateTableID != 'null') {
                    CLIRateTableIDs[i] = CLIRateTableID;
                }
            });
            $('#packagetable-form').trigger("reset");
            $('#modal-packagetable h4').html('Update Package');
            $("#packagetable-form [name=RateTableID]").select2().select2('val', "");
            $("#packagetable-form [name=PackageID]").select2().select2('val', "");
            $("#packagetable-form [name=PackageRateTableID]").select2().select2('val', "");
            $('#packagetable-form').find('input[name=CLIRateTableIDs]').val(CLIRateTableIDs);
            $('#packagetable-form').find('input[name=criteria]').val(criteria);
            $('#packagetable-form').attr("action", packagetable_update_url);
            $('#packagetable-form').find('.edit_hide').hide();
            $('#modal-packagetable').modal('show');
        });

        $('#form-confirm-modal').submit(function (e) {
            e.preventDefault();
            var dates = $('#form-confirm-modal [name="Closingdate"]').val();
            var criteria = '';
            if ($('#selectallbutton').is(':checked')) {
                criteria = JSON.stringify($searchcli);
            }
            var CLIRateTableIDs = [];
            $('#table-packagetable tr .rowcheckbox:checked').each(function (i, el) {
                CLIRateTableID = $(this).val();
                if (typeof CLIRateTableID != 'undefined' && CLIRateTableID != null && CLIRateTableID != 'null') {
                    CLIRateTableIDs[i] = CLIRateTableID;
                }
            });
            var CLIRateTableID = $('#packagetable-form').find('input[name=CLIRateTableID]').val();
            delete_cli(packagetable_delete_url.replace("{id}", CLIRateTableID),'CLIRateTableIDs=' + CLIRateTableIDs.join(",") + '&criteria=' + criteria+'&dates=' + dates+'&ServiceID=' + ServiceID+'&AccountServiceID='+AccountServiceID)

        });
    });

    function delete_cli(action,data_table_packagetable,data) {

        $.ajax({
            url: action,
            type: 'POST',
            data: data,
            datatype: 'json',
            success: function (response) {
                $("#delete-clidate").button('reset');
                $(".btn").button('reset');
                // alert(response.status);
                // alert(data_table_packagetable);
                if (response.status == 'success') {
                    $('#confirm-modal').modal('hide');
                    //if (typeof data_table_packagetable != 'undefined') {
                    data_table_packagetable.fnFilter('', 0);
                    //}
                    $('.packageSelectall').prop("checked", false);
                    toastr.success(response.message, 'Success', toastr_opts);
                } else if (response.status == 'check') {
                    var CLIRateTableID = 0;
                    var p = new RegExp('(\\/)(\\d+)', ["i"]);
                    var m = p.exec(action);
                    if (m != null) {
                        CLIRateTableID = m[2];
                    }
                    $('#packagetable-form').find('input[name=CLIRateTableID]').val(CLIRateTableID);
                    $('#confirm-modal').modal('show');
                } else {
                    toastr.error(response.message, "Error", toastr_opts);
                }
            }
        });
    }
</script>
<style>
    .btn-default.btn-icon.btn-sm {
        padding-right: 36px;
    }
    .btn-sm1{
        padding:2px 5px;font-size:12px;line-height:1.5;border-radius:3px
    }
</style>
@section('footer_ext')
    @parent

    <div class="modal fade in" id="modal-packagetable">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="packagetable-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Package</h4>
                    </div>
                    <div class="modal-body">


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Package*</label>
                                    {{ Form::select('PackageID', $Packages , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Default Package Rate Table*</label>
                                    {{ Form::select('PackageRateTableID', $package_rate_table , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Package Discount Plan</label>
                                    {{ Form::select('AccountPackageDiscountPlanID', $DiscountPlanPACKAGE , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Special Package Rate Table</label>
                                    {{ Form::select('SpecialPackageRateTableID', $package_rate_table , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Start Date*</label>
                                    <input type="text" data-date-format="yyyy-mm-dd"  class="form-control datepicker" id="PackageStartDate" name="PackageStartDate">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">End Date*</label>
                                    <input type="text" data-date-format="yyyy-mm-dd"  class="form-control datepicker" id="PackageEndDate" name="PackageEndDate">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">ContractID</label>
                                    <input type="text"  class="form-control" id="ContractID" name="ContractID">
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-12">
                                <label>Status</label>
                            </div>
                            <div class="col-md-12">
                                <p class="make-switch switch-small">
                                    <input name="Status" checked type="checkbox" value="1" >
                                </p>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="AccountID" value="{{$account->AccountID}}">
                    <input type="hidden" name="ServiceID" value="{{$ServiceID}}">
                    <input type="hidden" name="AccountServiceID" value="{{$AccountServiceID}}">
                    <input type="hidden" name="CLIRateTableIDs" value="">
                    <input type="hidden" name="AccountServicePackageID" value="">
                    <input type="hidden" name="AuthRule" value="{{$AuthRule or ''}}">
                    <input type="hidden" name="criteria" value="">
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
    <div class="modal fade" id="confirm-modal" >
        <div class="modal-dialog">
            <div class="modal-content">
                <form role="form" id="form-confirm-modal" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Delete Package</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label col-sm-3">Date</label>
                                    <div class="col-sm-9">
                                        <input type="text" value="{{date('Y-m-d')}}" name="Closingdate" id="Closingdate" class="form-control datepicker" data-date-format="yyyy-mm-dd" placeholder="">
                                    </div>
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">This is the date when you deleted Package against this account from the switch</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" data-loading-text = "Loading..."  class="btn btn-primary btn-sm btn-icon icon-left" id="delete-clidate">
                            <i class="entypo-floppy"></i>
                            Delete
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


