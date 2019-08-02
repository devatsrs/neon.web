<style>
    #selectcheckbox{
        padding: 15px 10px;
    }
</style>
<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Number
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
        <div id="clitable_filter" method="get" action="#">
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
                            <td><label for="field-1" class="col-sm-1 control-label">Number</label></td>
                            <td width="10%"><input type="text" name="CLIName" class="form-control" value="" /></td>
                            <td><label for="field-1" class="col-sm-1 control-label">ContractID</label></td>
                            <td width="10%"><input type="text" name="NumberContractID" class="form-control" value="" /></td>
                            <td><label for="field-1" class="col-sm-1 control-label">Start Date</label></td>
                            <td width="10%"><input type="text" data-date-format="yyyy-mm-dd"  class="form-control datepicker" id="NumberStartDate" name="NumberStartDate"></td>
                            <td><label for="field-1" class="col-sm-1 control-label">End Date</label></td>
                            <td width="10%"><input type="text" data-date-format="yyyy-mm-dd"  class="form-control datepicker" id="NumberEndDate" name="NumberEndDate"></td>
                            <td><label for="field-1" class="col-sm-1 control-label">Status</label></td>
                            <td width="12%">{{ Form::select('CLIStatus', [""=>"All",1=>"Active",0=>"Inactive"], '', array("class"=>"form-control select2 small")) }}</td>
                        </tr>
                        <tr>
                            <td colspan="10" align="right">
                                <button class="btn btn-primary btn-sm btn-icon icon-left" id="clitable_submit">
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
                                    <a class="create" id="add-clitable" href="javascript:;">
                                        <i class="entypo-plus"></i>
                                        Add
                                    </a>
                                </li>
                            @endif
                            @if( User::checkCategoryPermission('AuthenticationRule','Delete'))
                                <li>
                                    <a class="generate_rate create" id="bulk-delete-cli" href="javascript:;"
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
        <table id="table-clitable" class="table table-bordered datatable table-clitable">
            <thead>
            <tr>
                <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
                <th width="12%">Number</th>
                <th width="12%">Package</th>
                <th width="15%">Default Access Rate Table</th>
                <th width="15%">Access Discount Plan</th>
                <th width="15%">Default Termination Rate Table</th>
                <th width="15%">Termination Discount Plan</th>
                <th width="15%">Special Access Rate Table</th>
                <th width="15%">Special Termination Rate Table</th>
                <th width="15%">Contract ID</th>
                <th width="8%">Type</th>
                <th width="8%">Country</th>
                <th width="8%">Prefix</th>

                <th width="8%">City</th>
                <th width="8%">Tariff</th>
                <th width="8%">StartDate</th>
                <th width="8%">EndDate</th>

                <th width="5%">Status</th>
                <th width="20%">Action</th>

            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

    </div>
</div>
<style>
    .btn-default.btn-icon.btn-sm {
        padding-right: 36px;
    }
    .btn-sm1{
        padding:2px 3px;font-size:12px;line-height:1.5;border-radius:3px
    }
</style>
<script type="text/javascript">
    var AccountID = '{{$account->AccountID}}';
    var ServiceID='{{$ServiceID}}';
    var AccountServiceID='{{$AccountServiceID}}';

    var update_new_url;
    var postdata;


    $('table tbody').on('click', '.history-Termination-clitable', function (ev) {

        var $this   = $(this);

        var AccessRateTable   = $this.prevAll("div.hiddenRowData").find("input[name='RateTableID']").val();
        var TerminationRateTable = $this.prevAll("div.hiddenRowData").find("input[name='TerminationRateTableID']").val();
        if (TerminationRateTable == 0) {
            alert("Please add termination rate table against the Number");
            return;
        }
        var Type = $this.prevAll("div.hiddenRowData").find("input[name='Type']").val();
        var Country = $this.prevAll("div.hiddenRowData").find("input[name='CountryID']").val();
        var City = $this.prevAll("div.hiddenRowData").find("input[name='City']").val();
        var Tariff = $this.prevAll("div.hiddenRowData").find("input[name='Tariff']").val();
        var accessURL = baseurl + "/rate_tables/" + TerminationRateTable + "/view?AccessType=" + Type;
        accessURL += "&Country=" +"0";
        //accessURL += "&City=" +City;
        //accessURL += "&Tariff=" +Tariff;

        location.href = accessURL;
        /* var TerminationRateTable = $this.prevAll("div.hiddenRowData").find("input[name='TerminationRateTableID']").val();
         var Type = $this.prevAll("div.hiddenRowData").find("input[name='Type']").val();


         getArchiveRateTableDIDRates12($this,AccessRateTable,TerminationRateTable,Type,Country,City,Tariff);*/
    });
    $('table tbody').on('click', '.history-clitable', function (ev) {
        var $this   = $(this);

        var AccessRateTable   = $this.prevAll("div.hiddenRowData").find("input[name='RateTableID']").val();
        if (AccessRateTable == 0) {
            alert("Please add Access rate table against the Number");
            return;
        }
        var Type = $this.prevAll("div.hiddenRowData").find("input[name='Type']").val();
        var Country = $this.prevAll("div.hiddenRowData").find("input[name='CountryID']").val();
        var City = $this.prevAll("div.hiddenRowData").find("input[name='City']").val();
        var Tariff = $this.prevAll("div.hiddenRowData").find("input[name='Tariff']").val();
        var Prefix = $this.prevAll("div.hiddenRowData").find("input[name='Prefix']").val();
        var accessURL = baseurl + "/rate_tables/" + AccessRateTable + "/view?AccessType=" + Type;
        accessURL += "&Country=" +Country;
        accessURL += "&City=" +City;
        accessURL += "&Tariff=" +Tariff;
        // location.href = accessURL;
        var TerminationRateTable = $this.prevAll("div.hiddenRowData").find("input[name='TerminationRateTableID']").val();
        var Type = $this.prevAll("div.hiddenRowData").find("input[name='Type']").val();


        getArchiveRateTableDIDRates12($this,AccessRateTable,TerminationRateTable,Type,Country,City,Tariff, Prefix);
    });


    $('table tbody').on('click', '.access-discount-plan', function (ev) {
        var $this   = $(this);

        var CLIRateTableID   = $this.prevAll("div.hiddenRowData").find("input[name='CLIRateTableID']").val();
        var AccessDicountPlan   = $this.prevAll("div.hiddenRowData").find("input[name='AccessDicountPlan']").val();
        var AccessDicountPlanCLI   = $this.prevAll("div.hiddenRowData").find("input[name='CLI']").val();
        if (AccessDicountPlan == '' || AccessDicountPlan == null) {
            alert("Please Provide the Access Discount Plan");
            return;
        }

        getAccessDiscountPlan($this,AccountID,AccountServiceID,AccessDicountPlanCLI,CLIRateTableID);
    });
   // $('#Termination-discount-plan').click(function (ev) {
    $('table tbody').on('click', '.Termination-discount-plan', function (ev) {
        var $this   = $(this);
        var CLIRateTableID   = $this.prevAll("div.hiddenRowData").find("input[name='CLIRateTableID']").val();
        var TerminationDicountPlan   = $this.prevAll("div.hiddenRowData").find("input[name='TerminationDiscountPlan']").val();
        var TerminationDicountPlanCLI   = $this.prevAll("div.hiddenRowData").find("input[name='CLI']").val();

        if (TerminationDicountPlan == '' || TerminationDicountPlan == null) {
            alert("Please Provide the Termination Discount Plan");
            return;
        }

        getTerminationDiscountPlan($this,AccountID,AccountServiceID,TerminationDicountPlanCLI,TerminationDicountPlan,CLIRateTableID);
    });


    $('table tbody').on('click', '.Display-Code-list', function (ev) {
        var $this = $(this);
       // var TerminationDiscountPlan   = $this.find("DisplayErrorCode");
        var TerminationDiscountPlan   = document.getElementById("DisplayTerminationCode");
        var TerminationDiscountPlanText = TerminationDiscountPlan.innerText;
        $("#DisplayCode-form [name=TerminationDiscountPlanName]").val(TerminationDiscountPlanText);
        $('#modal-DisplayCode').modal('show');
        $('#DisplayCode_submit').trigger('click');
    });
    $('table tbody').on('click', '.special-Access-clitable', function (ev) {
        var $this = $(this);
        var SpecialRateTableID   = $this.prevAll("div.hiddenRowData").find("input[name='SpecialRateTableID']").val();
        if (SpecialRateTableID == 0) {
            alert("Please add Access rate table against the Number");
            return;
        }
        var Type = $this.prevAll("div.hiddenRowData").find("input[name='Type']").val();
        var Country = $this.prevAll("div.hiddenRowData").find("input[name='CountryID']").val();
        var City = $this.prevAll("div.hiddenRowData").find("input[name='City']").val();
        var Tariff = $this.prevAll("div.hiddenRowData").find("input[name='Tariff']").val();
        var Prefix = $this.prevAll("div.hiddenRowData").find("input[name='Prefix']").val();


        getArchiveRateTableDIDRates12($this,SpecialRateTableID,'',Type,Country,City,Tariff,Prefix);
    });

    $('table tbody').on('click', '.special-Termination-clitable', function (ev) {

        var $this   = $(this);
        var SpecialTerminationRateTableID = $this.prevAll("div.hiddenRowData").find("input[name='SpecialTerminationRateTableID']").val();
        if (SpecialTerminationRateTableID == 0) {
            alert("Please add special termination rate table against the Number");
            return;
        }
        var Type = $this.prevAll("div.hiddenRowData").find("input[name='Type']").val();
        var Country = $this.prevAll("div.hiddenRowData").find("input[name='CountryID']").val();
        var City = $this.prevAll("div.hiddenRowData").find("input[name='City']").val();
        var Tariff = $this.prevAll("div.hiddenRowData").find("input[name='Tariff']").val();
        var accessURL = baseurl + "/rate_tables/" + SpecialTerminationRateTableID + "/view?AccessType=" + Type;
        accessURL += "&Country=" +"0";

        location.href = accessURL;
    });
    //
    function getTerminationDiscountPlan($clickedButton,AccountID,AccountServiceID,TerminationDicountPlanCLI,TerminationDiscountPlan,CLIRateTableID) {
        data_table_clitable = $("#table-clitable").DataTable();
        // alert($("#table-clitable").DataTable().rows().count());
        var tr = $clickedButton.closest('tr');
        var checkIfSame = $clickedButton.find('i').hasClass('entypo-plus-squared');
        // alert('Called 1' + data_table_clitable + "1" + tr.id);
        //alert(data_table_clitable.rows().count());
        var row = data_table_clitable.row(tr);
        tr.find('.details-control i').toggleClass('entypo-minus-squared entypo-plus-squared');
        //access-discount-plan Termination-discount-plan
        tr.find('.special-Termination-clitable i,.history-Termination-clitable i,.special-Access-clitable i, .history-clitable i,.access-discount-plan i, .Termination-discount-plan i').removeClass('entypo-plus-squared entypo-minus-squared');
        row.child.hide();
        tr.removeClass('shown');

        if(!checkIfSame) {

            // if(!checkIfSame)
            $.ajax({
                url: baseurl + "/discount_plan/TerminationDiscountPlan",
                type: 'POST',
                data: "TerminationDicountPlan=" + TerminationDicountPlanCLI + "&AccountID=" + AccountID
                + "&AccountServiceID=" + AccountServiceID
                + "&CLIRateTableID=" + CLIRateTableID,
                dataType: 'json',
                cache: false,
                success: function (response) {

                    if (response.status == 'success') {
                        ArchiveRates = response.data;
                        // alert(ArchiveRates);
                        $clickedButton.find('i').addClass('entypo-plus-squared entypo-minus-squared');
                        //tr.find('.details-control i').toggleClass('entypo-plus-squared entypo-minus-squared');
                        var table = $('<table class="table table-bordered datatable dataTable no-footer" style="margin-left: 0.1%;width: 50% !important;"></table>');
                        var header = "<thead><tr>";
                        header += "<th>Country</th><th>Type</th><th>Code</th><th>Service</th><th>Components</th><th>Discount</th>";
                        header += "</tr></thead>";
                        table.append(header);
                        var tbody = $("<tbody></tbody>");
                        ArchiveRates.forEach(function (data) {
                            // alert(data);
                            // alert(data['Rate']);
                            var html = "";
                            html += "<tr class='no-selection'>";
                            // var VolumeDiscount = JSON.parse(data['VolumeDiscount']);
                            // alert(Object.keys(VolumeDiscount).length);
                            // VolumeDiscount.forEach(function (data1) {
                            //     alert(data1.ToMin);
                            // });


                            html += "<td>" + (data['CountryName'] != null ? data['CountryName'] : '') + "</td>";
                            html += "<td>" + (data['Type'] != null ? data['Type'] : '') + "</td>";
                            html += "<td>" + (data['Code'] != null ? data['Code'] : '') +
                                    '<div class="hide DisplayTerminationCode" id="DisplayTerminationCode">'+TerminationDiscountPlan+'</div>'+
                                    '<a title="Display-Code-list" data-original-title="Display-Code-list" href="javascript:;" class="Display-Code-list btn btn-info btn-sm1"><i class="fa fa-eye"></i></a>' +
                                    "</td>";
                            html += "<td>" + (data['Service'] != null ? getServiceName(data['Service'])  : '') + "</td>";
                            html += "<td>" + (data['Components'] != null ? data['Components'] : '') + "</td>";
                            html += "<td>" + (data['Discount'] != null ? data['Discount'] : '') + "</td>";

                            html += "</tr>";
                            table.append(html);

                        });
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
    }
    function getServiceName(ServiceID) {
        if (ServiceID == 1) {
            return "Volume";
        } else if (ServiceID == 2) {
            return "Minutes";
        }else if (ServiceID == 3) {
            return "Fixed";
        }
    }
    function getAccessDiscountPlan($clickedButton,AccountID,AccountServiceID,AccessDicountPlan,CLIRateTableID) {

        data_table_clitable = $("#table-clitable").DataTable();
        // alert($("#table-clitable").DataTable().rows().count());
        var tr = $clickedButton.closest('tr');
        var checkIfSame = $clickedButton.find('i').hasClass('entypo-plus-squared');
        // alert('Called 1' + data_table_clitable + "1" + tr.id);
        //alert(data_table_clitable.rows().count());
        var row = data_table_clitable.row(tr);
        tr.find('.details-control i').toggleClass('entypo-minus-squared entypo-plus-squared');
        //access-discount-plan Termination-discount-plan
        tr.find('.special-Termination-clitable i,.history-Termination-clitable i,.special-Access-clitable i, .history-clitable i,.access-discount-plan i, .Termination-discount-plan i').removeClass('entypo-plus-squared entypo-minus-squared');
        row.child.hide();
        tr.removeClass('shown');

        if(!checkIfSame) {
            $.ajax({
                url: baseurl + "/discount_plan/AccessDiscountPlan",
                type: 'POST',
                data: "AccessDicountPlan=" + AccessDicountPlan + "&AccountID=" + AccountID
                + "&AccountServiceID=" + AccountServiceID
                + "&CLIRateTableID=" + CLIRateTableID,
                dataType: 'json',
                cache: false,
                success: function (response) {
                    $clickedButton.removeAttr('disabled');
                    if (response.status == 'success') {
                        ArchiveRates = response.data;
                        // alert(ArchiveRates);
                        $clickedButton.find('i').addClass('entypo-plus-squared entypo-minus-squared');
                        //tr.find('.details-control i').toggleClass('entypo-plus-squared entypo-minus-squared');
                        var table = $('<table class="table table-bordered datatable dataTable no-footer" style="margin-left: 0.1%;width: 50% !important;"></table>');
                        var header = "<thead><tr>";
                        header += "<th>Country</th><th>Type</th><th>Prefix</th>" +
                                "<th>City</th><th>Tariff</th><th>Service</th><th>Components</th><th>Discount</th>";
                        header += "</tr></thead>";
                        table.append(header);
                        var tbody = $("<tbody></tbody>");
                        ArchiveRates.forEach(function (data) {
                            // alert(data);
                            // alert(data['Rate']);
                            var html = "";
                            html += "<tr class='no-selection'>";
                            // var VolumeDiscount = JSON.parse(data['VolumeDiscount']);
                            // alert(Object.keys(VolumeDiscount).length);
                            // VolumeDiscount.forEach(function (data1) {
                            //     alert(data1.ToMin);
                            // });


                            html += "<td>" + (data['CountryName'] != null ? data['CountryName'] : '') + "</td>";
                            html += "<td>" + (data['Type'] != null ? data['Type'] : '') + "</td>";
                            html += "<td>" + (data['Prefix'] != null ? data['Prefix'] : '') + "</td>";
                            html += "<td>" + (data['City'] != null ? data['City'] : '') + "</td>";
                            html += "<td>" + (data['Tariff'] != null ? data['Tariff'] : '') + "</td>";
                            html += "<td>" + (data['Service'] != null ? getServiceName(data['Service'])  : '') + "</td>";
                            html += "<td>" + (data['Components'] != null ? data['Components'] : '') + "</td>";
                            html += "<td>" + (data['Discount'] != null ? data['Discount'] : '') + "</td>";

                            html += "</tr>";
                            table.append(html);

                        });
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
    }

    function getArchiveRateTableDIDRates12($clickedButton,AccessRateTable,TerminationRateTable,Type,Country,City,Tariff, Prefix) {
        data_table_clitable = $("#table-clitable").DataTable();
        // alert($("#table-clitable").DataTable().rows().count());
        var tr = $clickedButton.closest('tr');
        var checkIfSame = $clickedButton.find('i').hasClass('entypo-plus-squared');
        // alert('Called 1' + data_table_clitable + "1" + tr.id);
        //alert(data_table_clitable.rows().count());
        var row = data_table_clitable.row(tr);
        tr.find('.details-control i').toggleClass('entypo-minus-squared entypo-plus-squared');
        tr.find('.special-Termination-clitable i,.history-Termination-clitable i,.special-Access-clitable i, .history-clitable i,.access-discount-plan i, .Termination-discount-plan i').removeClass('entypo-plus-squared entypo-minus-squared');
        row.child.hide();
        tr.removeClass('shown');

        if(!checkIfSame)
            $.ajax({
                url: baseurl + "/rate_tables/search_ajax_datagrid_rates_account_service",
                type: 'POST',
                data: "AccessRateTable=" + AccessRateTable + "&TerminationRateTable=" + TerminationRateTable
                + "&Type=" + Type + "&Country=" + Country + "&City=" + City+ "&Tariff=" + Tariff + "&Prefix=" +Prefix,
                dataType: 'json',
                cache: false,
                success: function (response) {

                    if (response.status == 'success') {
                        ArchiveRates = response.data;
                        // alert(ArchiveRates);
                        $clickedButton.find('i').addClass('entypo-plus-squared entypo-minus-squared');
                       // tr.find('.details-control i').toggleClass('entypo-plus-squared entypo-minus-squared');
                        var table = $('<table class="table table-bordered datatable dataTable no-footer" style="margin-left: 0.1%;width: 50% !important;"></table>');
                        var header = "<thead><tr><th>Origination</th><th>Prefix</th><th>Time of Day</th>";
                        header += "<th>One-Off Cost</th><th>Monthly Cost</th><th>Cost Per Call</th><th>Cost Per Minute</th>" +
                                "<th>Surcharge Per Call</th><th>Surcharge Per Minute</th><th>Outpayment Per Call</th>" +
                                "<th>Outpayment Per Minute</th><th>Surcharges</th><th>Chargeback</th>" +
                                "<th>Collection Cost Amount</th><th>Collection Cost (%)</th><th>Registration Cost</th><th class='sorting_desc'>Effective Date</th><th>End Date</th>";
                        header += "</tr></thead>";
                        table.append(header);
                        var tbody = $("<tbody></tbody>");
                        ArchiveRates.forEach(function (data) {
                            // alert(data);
                            // alert(data['Rate']);
                            var html = "";
                            html += "<tr class='no-selection'>";

                            html += "<td>" + (data['OriginationCode'] != null ? data['OriginationCode'] : '') + "</td>";
                            html += "<td>" + (data['Prefix'] != null ? data['Prefix'] : '') + "</td>";
                            html += "<td>" + (data['TimeTitle'] != null ? data['TimeTitle'] : '') + "</td>";
                            html += "<td>" + (data['OneOffCost'] != null ? data['OneOffCost'] : '') + "</td>";
                            html += "<td>" + (data['MonthlyCost'] != null ? data['MonthlyCost'] : '') + "</td>";
                            html += "<td>" + (data['CostPerCall'] != null ? data['CostPerCall'] : '') + "</td>";
                            html += "<td>" + (data['CostPerMinute'] != null ? data['CostPerMinute'] : '') + "</td>";
                            html += "<td>" + (data['SurchargePerCall'] != null ? data['SurchargePerCall'] : '') + "</td>";
                            html += "<td>" + (data['SurchargePerMinute'] != null ? data['SurchargePerMinute'] : '') + "</td>";
                            html += "<td>" + (data['OutpaymentPerCall'] != null ? data['OutpaymentPerCall'] : '') + "</td>";
                            html += "<td>" + (data['OutpaymentPerMinute'] != null ? data['OutpaymentPerMinute'] : '') + "</td>";
                            html += "<td>" + (data['Surcharges'] != null ? data['Surcharges'] : '') + "</td>";
                            html += "<td>" + (data['Chargeback'] != null ? data['Chargeback'] : '') + "</td>";
                            html += "<td>" + (data['CollectionCostAmount'] != null ? data['CollectionCostAmount'] : '') + "</td>";
                            html += "<td>" + (data['CollectionCostPercentage'] != null ? data['CollectionCostPercentage'] : '') + "</td>";
                            html += "<td>" + (data['RegistrationCostPerNumber'] != null ? data['RegistrationCostPerNumber'] : '') + "</td>";
                            html += "<td>" + (data['EffectiveDate'] != null ? data['EffectiveDate'] : '') + "</td>";
                            html += "<td>" + (data['EndDate'] != null ? data['EndDate'] : '') + "</td>";
                            html += "</tr>";
                            table.append(html);

                        });
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
        var cli_list_fields = ["CLIRateTableID", "CLI",'PackageName' ,"AccessRateTable", 'AccessDicountPlan', 'TerminationRateTable',
            'TerminationDiscountPlan','SpecialRateTable','SpecialTerminationRateTable','ContractID', 'Type','Country', 'PrefixWithoutCountry', 'City','Tariff','NumberStartDate','NumberEndDate', 'Status',
            'RateTableID','AccessDiscountPlanID','TerminationRateTableID','TerminationDiscountPlanID','CountryID','SpecialRateTableID','SpecialTerminationRateTableID','Prefix','AccountServicePackageID'];
        public_vars.$body = $("body");
        var $searchcli = {};
        var data_table_clitable;
        var clitable_add_url = baseurl + "/clitable/store";
        var clitable_update_url = baseurl + "/clitable/update";
        var clitable_delete_url = baseurl + "/clitable/delete/{id}";
        var clitable_datagrid_url = baseurl + "/clitable/ajax_datagrid/" + AccountID;



        $("#clitable_submit").click(function (e) {
            e.preventDefault();

            $searchcli.CLIName = $("#clitable_filter").find('[name="CLIName"]').val();
            $searchcli.NumberContractID = $("#clitable_filter").find('[name="NumberContractID"]').val();
            $searchcli.NumberEndDate = $("#clitable_filter").find('[name="NumberEndDate"]').val();
            $searchcli.NumberStartDate = $("#clitable_filter").find('[name="NumberStartDate"]').val();
            $searchcli.AccountServiceOrderID = $("#clitable_filter").find('[name="AccountServiceOrderID"]').val();
            $searchcli.CLIStatus = $("#clitable_filter").find('[name="CLIStatus"]').val();

            if((typeof $searchcli.NumberEndDate  != 'undefined' && $searchcli.NumberEndDate != '')
                    && (typeof $searchcli.NumberStartDate  != 'undefined' && $searchcli.NumberStartDate != '')
                    && ($searchcli.NumberEndDate  <  $searchcli.NumberStartDate)){
                toastr.error("End Date for Number must be greater then start date", "Error", toastr_opts);
                return false;
            }
            data_table_clitable = $("#table-clitable").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "scrollX": true,
                "initComplete": function(settings, json) { // to hide extra row which is displaying due to scrollX
                    $('.dataTables_scrollBody thead tr').css({visibility:'collapse'});
                },
                "sAjaxSource": clitable_datagrid_url,
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
                                "name": "NumberContractID",
                                "value": $searchcli.NumberContractID
                            },
                            {
                                "name": "NumberStartDate",
                                "value": $searchcli.NumberStartDate
                            },
                            {
                                "name": "NumberEndDate",
                                "value": $searchcli.NumberEndDate
                            },
                            {
                                "name": "CLIName",
                                "value": $searchcli.CLIName
                            },
                            {
                                "name": "CLIStatus",
                                "value": $searchcli.CLIStatus
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
                                "name": "CLIName",
                                "value": $searchcli.CLIName
                            },
                            {
                                "name": "NumberContractID",
                                "value": $searchcli.NumberContractID
                            },
                            {
                                "name": "NumberStartDate",
                                "value": $searchcli.NumberStartDate
                            },
                            {
                                "name": "NumberEndDate",
                                "value": $searchcli.NumberEndDate
                            },
                            {
                                "name": "CLIStatus",
                                "value": $searchcli.CLIStatus
                            }
                    );

                },
                "iDisplayLength": 10,
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[0, 'asc']],
                "aoColumns": [
                    {
                        "bSortable": false,
                        mRender: function (id, type, full) {
                            return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                        }
                    },
                    {"bSortable": true},    // 1 Number
                    {"bSortable": true},
                    {
                        mRender: function(id, type, full) {
                            action = '';
                            action +='<div id="hiddenRowData1" class = "hiddenRowData" >';
                            for (var i = 0; i < cli_list_fields.length; i++) {
                                action += '<input disabled type = "hidden"  name = "' + cli_list_fields[i] + '"  value = "' + (full[i] != null ? full[i] : '') + '" / >';
                            }
                            action +='</div>';
                            action += "<div class='text-overflow: ellipsis;'>" + full[3] + "</div>";
                            action += '<a title="Access Table" href="javascript:;" class="history-clitable btn btn-default btn-sm1"><i class="fa fa-eye"></i></a>&nbsp;';
                            return action;
                        },
                        "className":      'details-control',
                        "orderable":      false,

                        "data": null,
                        "defaultContent": ''
                    },    // 2 Number Rate Table
                    {
                        mRender: function(id, type, full) {
                        action = '';
                        action +='<div id="hiddenRowData1" class = "hiddenRowData" >';
                        for (var i = 0; i < cli_list_fields.length; i++) {
                            action += '<input disabled type = "hidden"  name = "' + cli_list_fields[i] + '"  value = "' + (full[i] != null ? full[i] : '') + '" / >';
                        }
                        action +='</div>';

                            if(full[18] != undefined && full[19] != '' && full[19] != 0) {
                                action += "<div class='text-overflow: ellipsis;'>" + full[4] + "</div>";
                                //action += full[3];
                                action += '<a title="Access Discount Table" href="javascript:;" class="access-discount-plan btn btn-default btn-sm1"><i class="fa fa-eye"></i></a>&nbsp;';
                            }

                        return action;
                    },
                        "className":      'details-control',
                        "orderable":      false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {
                        mRender: function(id, type, full) {
                        action = '';
                        action +='<div id="hiddenRowData1" class = "hiddenRowData" >';
                        for (var i = 0; i < cli_list_fields.length; i++) {
                            action += '<input disabled type = "hidden"  name = "' + cli_list_fields[i] + '"  value = "' + (full[i] != null ? full[i] : '') + '" / >';
                        }
                        action +='</div>';
                        action += "<div class='text-overflow: ellipsis;'>" + full[5] + "</div>";
                         //action += full[4];
                                action += '<a title="Termination Table" href="javascript:;" class="history-Termination-clitable btn btn-default btn-sm1"><i class="fa fa-eye"></i></a>&nbsp;';

                        return action;
                    },
                        "className":      'details-control',
                        "orderable":      false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {
                        mRender: function(id, type, full) {
                        action = '';
                        action +='<div id="hiddenRowData1" class = "hiddenRowData" >';
                        for (var i = 0; i < cli_list_fields.length; i++) {
                            action += '<input disabled type = "hidden"  name = "' + cli_list_fields[i] + '"  value = "' + (full[i] != null ? full[i] : '') + '" / >';
                        }
                        action +='</div>';

                            if(full[20] != undefined && full[21] != '' && full[21] != 0) {
                                action += "<div class='text-overflow: ellipsis;'>" + full[6] + "</div>";
                                //action += full[5];
                                action += '<a title="Termination Discount Plan" href="javascript:;" class="Termination-discount-plan btn btn-default btn-sm1"><i class="fa fa-eye"></i></a>&nbsp;';
                            }

                        return action;
                    },
                        "className":      'details-control',
                        "orderable":      false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {
                        mRender: function(id, type, full) {

                        action = '';
                        action +='<div id="hiddenRowData1" class = "hiddenRowData" >';
                        for (var i = 0; i < cli_list_fields.length; i++) {
                            action += '<input disabled type = "hidden"  name = "' + cli_list_fields[i] + '"  value = "' + (full[i] != null ? full[i] : '') + '" / >';
                        }
                        action +='</div>';

                        if(full[22] != undefined && full[23] != '' && full[23] != 0) {
                            action += "<div class='text-overflow: ellipsis;'>" + full[7] + "</div>";
                            //action += full[6];
                                action += '<a title="Special Access Table" href="javascript:;" class="special-Access-clitable btn btn-default btn-sm1"><i class="fa fa-eye"></i></a>&nbsp;';
                        }

                        return action;
                    },
                        "className":      'details-control',
                        "orderable":      false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {
                        mRender: function(id, type, full) {
                        action = '';
                        action +='<div id="hiddenRowData1" class = "hiddenRowData" >';
                        for (var i = 0; i < cli_list_fields.length; i++) {
                            action += '<input disabled type = "hidden"  name = "' + cli_list_fields[i] + '"  value = "' + (full[i] != null ? full[i] : '') + '" / >';
                        }
                        action +='</div>';

                        if(full[23] != undefined && full[24] != '' && full[24] != 0) {
                            action += "<div class='text-overflow: ellipsis;'>" + full[8] + "</div>";
                           // action += full[7];
                                action += '<a title="Special Termination Table" href="javascript:;" class="special-Termination-clitable btn btn-default btn-sm1"><i class="fa fa-eye"></i></a>&nbsp;';
                        }

                        return action;
                    },
                        "className":      'details-control',
                        "orderable":      false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {"bSortable": true},
                    {"bSortable": true},
                    {"bSortable": true},
                    {"bSortable": true},    // 5 Type
                    {"bSortable": true},    // 6 Prefix
                    {"bSortable": true},    // 7 City Tariff
                    {"bSortable": true},    // 7 City Tariff
                    {"bSortable": true},    // 7 City Tariff


                    {  "bSortable": false,  //Status
                        mRender: function ( id, type, full ) {
                            console.log(full);
                            var action , edit_ , show_ , delete_;
                            //console.log(id);
                            if(full[17] == 1){
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
                            for (var i = 0; i < cli_list_fields.length; i++) {
                                action += '<input disabled type = "hidden"  name = "' + cli_list_fields[i] + '"  value = "' + (full[i] != null ? full[i] : '') + '" / >';
                            }
                            action += '</div>';

                            action += ' <a href="javascript:;" title="Edit" class="edit-clitable btn btn-default btn-sm1 tooltip-primary" data-original-title="Edit" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-pencil"></i></a>&nbsp;';
                            action += ' <a href="' + clitable_delete_url.replace("{id}", full[0]) + '" class="delete-clitable btn btn-danger btn-sm1 tooltip-primary" data-original-title="Delete" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading..."><i class="entypo-trash"></i></a>&nbsp;'

                            return action;
                        }
                    }
                ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "Export Data",
                            "sUrl": clitable_datagrid_url,
                            sButtonClass: "save-collection"
                        }
                    ]
                },
                "fnDrawCallback": function () {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    $('.dataTables_scrollBody thead tr').css({visibility:'collapse'});
                    default_row_selected('table-clitable','selectall','selectallbutton');
                    select_all_top('selectallbutton','table-clitable','selectall');
                }
            });
            setTimeout(function () {
                $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
            }, 10);


        });



        selected_all('selectall', 'table-clitable');

        table_row_select('table-clitable', 'selectallbutton');


        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });
        var start = 1;
        if (start == 1) {
            $('#clitable_submit').trigger('click');
        }
        start = 2;


        $('#add-clitable').click(function (ev) {
            ev.preventDefault();
            $('#clitable-form').trigger("reset");
            $('#modal-clitable h4').html('Add Number');
            $("#clitable-form .edit_show [name=CLI]").val("");
            $("#clitable-form [name=RateTableID]").select2().select2('val', "");
            $("#clitable-form [name=SpecialRateTableID]").select2().select2('val', "");
            $("#clitable-form [name=NumberStartDate]").val("");
            $("#clitable-form [name=NumberEndDate]").val("");
            $("#clitable-form [name=ContractID]").val("");
            $("#clitable-form [name=City]").select2().select2('val', "");
            $("#clitable-form [name=Tariff]").select2().select2('val', "");
            $("#clitable-form [name=PrefixWithoutCountry]").select2().select2('val', "");
            $("#clitable-form [name=NoType]").select2().select2('val', "");
            $("#clitable-form [name=Status]").prop("checked", 1 == 1).trigger("change");
            $("#clitable-form [name=TerminationRateTableID]").select2().select2('val', "");
            $("#clitable-form [name=SpecialTerminationRateTableID]").select2().select2('val', "");
            $("#clitable-form [name=AccessDiscountPlanID]").select2().select2('val', "");
            $("#clitable-form [name=TerminationDiscountPlanID]").select2().select2('val', "");
            $("#clitable-form [name=CountryID]").select2().select2('val', "");
            $('#clitable-form input[name=CLIRateTableID]').val("");
            $('#clitable-form input[name=CLIRateTableIDs]').val("");
            $('#clitable-form input[name=criteria]').val("");
            $('#clitable-form').attr("action", clitable_add_url);
            $('#clitable-form .edit_hide').show().find(".cli-field").attr("name", "CLI");
            $('#clitable-form .edit_show').hide().find(".cli-field").attr("name", "");
            $('#modal-clitable').modal('show');
        });

        $('table tbody').on('click', '.edit-clitable', function (ev) {
            ev.preventDefault();

            var fields = $(this).prev(".hiddenRowData");
            $('#clitable-form').trigger("reset");
            $('#modal-clitable h4').html('Edit Number RateTable');

            /* var cli_list_fields = ["CLIRateTableID", "CLI", "AccessRateTable", 'AccessDicountPlan', 'TerminationRateTable',
             'TerminationDiscountPlan','ContractID', 'Type','Country', 'Prefix', 'City','Tariff','NumberStartDate','NumberEndDate', 'Status',
             'RateTableID','AccessDiscountPlanID','TerminationRateTableID','TerminationDiscountPlanID','CountryID'];*/
            var CLI = fields.find("[name='CLI']").val();
            var RateTableID = fields.find("[name='RateTableID']").val();
            var SpecialRateTableID = fields.find("[name='SpecialRateTableID']").val();
            var City = fields.find("[name='City']").val();
            var Tariff = fields.find("[name='Tariff']").val();
            var PrefixWithoutCountry = fields.find("[name='PrefixWithoutCountry']").val();
            var NoType = fields.find("[name='Type']").val();
            var Status = fields.find("[name='Status']").val();
            var NumberStartDate = fields.find("[name='NumberStartDate']").val();
            var NumberEndDate = fields.find("[name='NumberEndDate']").val();
            var ContractID = fields.find("[name='ContractID']").val();
            var TerminationRateTableID = fields.find("[name='TerminationRateTableID']").val();
            var SpecialTerminationRateTableID = fields.find("[name='SpecialTerminationRateTableID']").val();
            var AccessDiscountPlanID = fields.find("[name='AccessDiscountPlanID']").val();
            var TerminationDiscountPlanID = fields.find("[name='TerminationDiscountPlanID']").val();
            var CountryID = fields.find("[name='CountryID']").val();
            var AccountServicePackageID = fields.find("[name='AccountServicePackageID']").val();
            var CLIRateTableID = fields.find("[name='CLIRateTableID']").val();

            RateTableID = RateTableID == 0 ? '' : RateTableID;
            SpecialRateTableID = SpecialRateTableID == 0 ? '' : SpecialRateTableID;
            TerminationRateTableID = TerminationRateTableID == 0 ? '' : TerminationRateTableID;
            SpecialTerminationRateTableID = SpecialTerminationRateTableID == 0 ? '' : SpecialTerminationRateTableID;
            AccessDiscountPlanID = AccessDiscountPlanID == 0 ? '' : AccessDiscountPlanID;
            TerminationDiscountPlanID = TerminationDiscountPlanID == 0 ? '' : TerminationDiscountPlanID;
            CountryID = CountryID == 0 ? '' : CountryID;


            $('#clitable-form .edit_hide').hide().find(".cli-field").attr("name", "");
            $('#clitable-form .edit_show').show().find(".cli-field").attr("name", "CLI");
            $("#clitable-form .edit_show [name=CLI]").val(CLI);
            $("#clitable-form [name=RateTableID]").select2().select2('val', RateTableID);
            $("#clitable-form [name=SpecialRateTableID]").select2().select2('val', SpecialRateTableID);
            $("#clitable-form [name=NumberStartDate]").val(NumberStartDate);
            $("#clitable-form [name=NumberEndDate]").val(NumberEndDate);
            $("#clitable-form [name=ContractID]").val(ContractID);
            $("#clitable-form [name=City]").select2().select2('val', City);
            $("#clitable-form [name=Tariff]").select2().select2('val', Tariff);
            $("#clitable-form [name=PrefixWithoutCountry]").select2().select2('val', PrefixWithoutCountry);
            $("#clitable-form [name=NoType]").select2().select2('val', NoType);
            $("#clitable-form [name=Status]").prop("checked", Status == 1).trigger("change");
            $("#clitable-form [name=TerminationRateTableID]").select2().select2('val', TerminationRateTableID);
            $("#clitable-form [name=SpecialTerminationRateTableID]").select2().select2('val', SpecialTerminationRateTableID);
            $("#clitable-form [name=AccessDiscountPlanID]").select2().select2('val', AccessDiscountPlanID);
            $("#clitable-form [name=TerminationDiscountPlanID]").select2().select2('val', TerminationDiscountPlanID);
            $("#clitable-form [name=AccountServicePackageID]").select2().select2('val', AccountServicePackageID);
            $("#clitable-form [name=CountryID]").select2().select2('val', CountryID);
            $('#clitable-form input[name=CLIRateTableID]').val(CLIRateTableID);
            $('#clitable-form input[name=CLIRateTableIDs]').val("");
            $('#clitable-form input[name=criteria]').val("");
            $('#clitable-form').attr("action", clitable_update_url);
            $('#modal-clitable').modal('show');
        });

        $('table tbody').on('click', '.delete-clitable', function (ev) {
            ev.preventDefault();
            $(this).button('loading');
            result = confirm("Are you Sure?");
            if (result) {

                var delete_url = $(this).attr("href");
                var AuthRule = $('#clitable-form').find('input[name=AuthRule]').val();
                var AccountID = $('#clitable-form').find('input[name=AccountID]').val();
                delete_cli(delete_url,data_table_clitable,"AuthRule="+AuthRule+'&AccountID='+AccountID+'&ServiceID='+ServiceID)
            } else
                $(this).button('reset');
            return false;
        });

        function submit_ajax_datatable1(fullurl,data,refreshjob,data_table_reload){
            $.ajax({
                url:fullurl, //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $(".btn").button('reset');
                    data_table_reload.fnFilter('', 0);
                    if (response.status == 'success') {
                        $('.modal').modal('hide');
                        toastr.success(response.message, "Success", toastr_opts);
                        if( typeof data_table_reload !=  'undefined'){
                            data_table_reload.fnFilter('', 0);

                        }
                        if(refreshjob){
                            reloadJobsDrodown(0);
                            reloadMsgDrodown(0);
                        }
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                data: data,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
            });
            // alert($('.dataTables_scrollBody thead tr').css());
            // $('.dataTables_scrollBody thead tr').css({visibility:'collapse'});
            // data_table_clitable.initComplete('','');
        }
        $("#clitable-form").submit(function (e) {
            e.preventDefault();
            var _url = $(this).attr("action");
            submit_ajax_datatable1(_url, $(this).serialize(), 0, data_table_clitable);

            //data_table_clitable = $("#table-clitable").DataTable();
            //data_table_clitable.initComplete('','');
        });

        $("#bulk-delete-cli").click(function (ev) {
            var criteria = '';
            if ($('#selectallbutton').is(':checked')) {
                criteria = JSON.stringify($searchcli);
            }
            var CLIRateTableIDs = [];
            if (!confirm('Are you sure you want to delete selected Number?')) {
                return;
            }
            $('#table-clitable tr .rowcheckbox:checked').each(function (i, el) {
                CLIRateTableID = $(this).val();
                if (typeof CLIRateTableID != 'undefined' && CLIRateTableID != null && CLIRateTableID != 'null') {
                    CLIRateTableIDs[i] = CLIRateTableID;
                }
            });
            var AuthRule = $('#clitable-form').find('input[name=AuthRule]').val();
            var AccountID = $('#clitable-form').find('input[name=AccountID]').val();

            if (CLIRateTableIDs.length) {
                delete_cli(clitable_delete_url.replace("{id}", 0),data_table_clitable,'CLIRateTableIDs=' + CLIRateTableIDs.join(",") + '&criteria=' + criteria+'&AuthRule='+AuthRule+'&AccountID='+AccountID+'&ServiceID=' + ServiceID+'&AccountServiceID='+AccountServiceID)
            }
        });
        $("#changeSelectedCLI").click(function (ev) {
            var criteria = '';
            if ($('#selectallbutton').is(':checked')) {
                criteria = JSON.stringify($searchcli);
            }
            var CLIRateTableIDs = [];
            $('#table-clitable tr .rowcheckbox:checked').each(function (i, el) {
                CLIRateTableID = $(this).val();
                if (typeof CLIRateTableID != 'undefined' && CLIRateTableID != null && CLIRateTableID != 'null') {
                    CLIRateTableIDs[i] = CLIRateTableID;
                }
            });
            $('#clitable-form').trigger("reset");
            $('#modal-clitable h4').html('Update Number');
            $("#clitable-form [name=RateTableID]").select2().select2('val', "");
            $("#clitable-form [name=SpecialRateTableID]").select2().select2('val', "");
            $("#clitable-form [name=PackageID]").select2().select2('val', "");
            $("#clitable-form [name=PackageRateTableID]").select2().select2('val', "");
            $('#clitable-form').find('input[name=CLIRateTableIDs]').val(CLIRateTableIDs);
            $('#clitable-form').find('input[name=criteria]').val(criteria);
            $('#clitable-form').attr("action", clitable_update_url);
            $('#clitable-form').find('.edit_hide').hide();
            $('#modal-clitable').modal('show');
        });

        $('#form-confirm-modal').submit(function (e) {
            e.preventDefault();
            var dates = $('#form-confirm-modal [name="Closingdate"]').val();
            var criteria = '';
            if ($('#selectallbutton').is(':checked')) {
                criteria = JSON.stringify($searchcli);
            }
            var CLIRateTableIDs = [];
            $('#table-clitable tr .rowcheckbox:checked').each(function (i, el) {
                CLIRateTableID = $(this).val();
                if (typeof CLIRateTableID != 'undefined' && CLIRateTableID != null && CLIRateTableID != 'null') {
                    CLIRateTableIDs[i] = CLIRateTableID;
                }
            });
            var CLIRateTableID = $('#clitable-form').find('input[name=CLIRateTableID]').val();
            delete_cli(clitable_delete_url.replace("{id}", CLIRateTableID),'CLIRateTableIDs=' + CLIRateTableIDs.join(",") + '&criteria=' + criteria+'&dates=' + dates+'&ServiceID=' + ServiceID+'&AccountServiceID='+AccountServiceID)

        });
    });

    function delete_cli(action,data_table_clitable,data) {

        $.ajax({
            url: action,
            type: 'POST',
            data: data,
            datatype: 'json',
            success: function (response) {
                $("#delete-clidate").button('reset');
                $(".btn").button('reset');
                // alert(response.status);
                // alert(data_table_clitable);
                if (response.status == 'success') {
                    $('#confirm-modal').modal('hide');
                    //if (typeof data_table_clitable != 'undefined') {
                    data_table_clitable.fnFilter('', 0);
                    //}
                    $('.selectall').prop("checked", false);
                    toastr.success(response.message, 'Success', toastr_opts);
                } else if (response.status == 'check') {
                    var CLIRateTableID = 0;
                    var p = new RegExp('(\\/)(\\d+)', ["i"]);
                    var m = p.exec(action);
                    if (m != null) {
                        CLIRateTableID = m[2];
                    }
                    $('#clitable-form').find('input[name=CLIRateTableID]').val(CLIRateTableID);
                    $('#confirm-modal').modal('show');
                } else {
                    toastr.error(response.message, "Error", toastr_opts);
                }
            }
        });
    }
</script>

@section('footer_ext')
    @parent

    <div class="modal fade in" id="modal-clitable">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="clitable-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Number</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row edit_hide">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-3215" class="control-label">Number</label>
                                    <textarea name="CLI" class="form-control cli-field autogrow"></textarea>

                                </div>
                            </div>
                        </div>
                        <div class="row edit_show">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-3216" class="control-label">Number*</label>
                                    <input name="CLI" class="form-control cli-field">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Default Access RateTable*</label>
                                    {{ Form::select('RateTableID', $rate_table , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Access Discount Plan</label>
                                    {{ Form::select('AccessDiscountPlanID', $DiscountPlanDID , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Default Termination Rate Table*</label>
                                    {{ Form::select('TerminationRateTableID', $termination_rate_table , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Termination Discount plan</label>
                                    {{ Form::select('TerminationDiscountPlanID', $DiscountPlanVOICECALL , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Special Access RateTable</label>
                                    {{ Form::select('SpecialRateTableID', $rate_table , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Special Termination Rate Table</label>
                                    {{ Form::select('SpecialTerminationRateTableID', $termination_rate_table , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Package</label>
                                    {{ Form::select('AccountServicePackageID', AccountService::getAccountServicePackage($account->AccountID) , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Country*</label>
                                    {{ Form::select('CountryID', $countries , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-2315" class="control-label">Type*</label>
                                    {{ Form::select('NoType', $AccessType , '' , array("class"=>"select2")) }}

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-115" class="control-label">Prefix*</label>
                                    {{ Form::select('PrefixWithoutCountry', $Prefix , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-115" class="control-label">City
                                        <span class="label hidden label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Select Rate Table to rate Inboud Calls based on origination no" data-original-title="Rate Table">?</span>
                                    </label>
                                    {{ Form::select('City', $City , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-115" class="control-label">Tariff
                                        <span class="label hidden label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Select Rate Table to rate Inboud Calls based on origination no" data-original-title="Rate Table">?</span>
                                    </label>
                                    {{ Form::select('Tariff', $Tariff , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">Start Date*</label>
                                    <input type="text" data-date-format="yyyy-mm-dd"  class="form-control datepicker" id="StartDate" name="NumberStartDate">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-225" class="control-label">End Date*</label>
                                    <input type="text" data-date-format="yyyy-mm-dd"  class="form-control datepicker" id="StartDate" name="NumberEndDate">
                                </div>
                            </div>
                        </div>



                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-115" class="control-label">Contract ID</label>
                                    <input type="text" name="ContractID" value="" class="form-control" id="ContractID" name="ContractID" placeholder="">
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
                    <input type="hidden" name="AccountID" value="{{ $account->AccountID }}">
                    <input type="hidden" name="ServiceID" value="{{$ServiceID}}">
                    <input type="hidden" name="AccountServiceID" value="{{$AccountServiceID}}">
                    <input type="hidden" name="CLIRateTableIDs" value="">
                    <input type="hidden" name="CLIRateTableID" value="">
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
                        <h4 class="modal-title">Delete Number</h4>
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
                                    <div class="col-sm-9">This is the date when you deleted Number against this account from the switch</div>
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

    @include('accounts.DisplayCodes')
@stop


