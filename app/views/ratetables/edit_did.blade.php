@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form role="form" id="rate-table-search" action="javascript:void(0);"  method="post" class="form-horizontal form-groups-bordered validate" novalidate>
                @if($RateApprovalProcess == 1 && $rateTable->AppliedTo != RateTable::APPLIED_TO_VENDOR)
                    <div class="form-group">
                        <label class="control-label">Status</label>
                        {{ Form::select('ApprovedStatus1', RateTable::$DDRateStatus1, RateTable::RATE_STATUS_APPROVED, array("class"=>"select2")) }}
                    </div>
                    <div class="form-group" id="ApprovedStatus2-Box" style="display: none;">
                        <label class="control-label">Status</label>
                        {{ Form::select('ApprovedStatus2', RateTable::$DDRateStatus2, RateTable::RATE_STATUS_APPROVED, array("class"=>"select2")) }}
                    </div>
                @endif
                <input type="hidden" name="ApprovedStatus" value="{{RateTable::RATE_STATUS_APPROVED}}" />

                <div class="form-group">
                    <label class="control-label">Access Type</label>
                    {{ Form::select('AccessType', array('' => "All") + $AccessType, Helper::getFormValue('AccessType') , array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label class="control-label">Country</label>
                    {{ Form::select('Country', $countries, Helper::getFormValue('Country') , array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label class="control-label">Origination</label>
                    <input type="text" name="OriginationCode" class="form-control" placeholder="" />
                </div>
                <div class="form-group">
                    <label class="control-label">Prefix</label>
                    <input type="text" name="Code" class="form-control" id="field-1" placeholder="" />
                    <input type="hidden" name="TrunkID" value="{{$trunkID}}" >
                </div>
                <div class="form-group">
                    <label class="control-label">City</label>
                    {{ Form::select('City', array('' => "All") + $City, Helper::getFormValue('City') , array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label class="control-label">Tariff</label>
                    {{ Form::select('Tariff', array('' => "All") + $Tariff, Helper::getFormValue('Tariff') , array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label class="control-label">Time Of Day</label>
                    {{ Form::select('Timezones', $Timezones, '', array("class"=>"select2")) }}
                </div>

                <div class="form-group filter_naa">
                    <label class="control-label">Discontinued Prefix</label>
                    <p class="make-switch switch-small">
                        {{Form::checkbox('DiscontinuedRates', '1', false, array("id"=>"DiscontinuedRates"))}}
                    </p>
                </div>

                <div class="form-group EffectiveBox">
                    <label class="control-label">Effective</label>
                    <select name="Effective" class="select2" data-allow-clear="true" data-placeholder="Select Effective">
                        <option value="All">All</option>
                        <option value="Now">Now</option>
                        <option value="Future">Future</option>
                    </select>
                </div>

                <input name="ResellerPage" type="hidden" value="{{!empty($ResellerPage) ? $ResellerPage : 0}}" >

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
    <li><a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a></li>
    <li><a href="{{URL::to('/rate_tables')}}">Rate Table</a></li>
    <li>
        <a><span>{{rate_tables_dropbox($id,['ResellerPage'=>$ResellerPage])}}</span></a>
    </li>
    <li class="active"><strong>{{$rateTable->RateTableName}}</strong></li>
</ol>
<h3>View Rate Table</h3>

<div class="row" style="margin-bottom: 10px;">
    @if(empty($ResellerPage))
        <div  class="col-md-12">
            <div class="input-group-btn pull-right hidden dropdown" style="width:70px;">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                    {{--@if($isBandTable)--}}
                        @if(User::checkCategoryPermission('RateTables','Add') )
                            <li><a href="javascript:void(0)" id="add-new-rate"><i class="entypo-plus"></i><span>Add New</span></a></li>
                        @endif
                    {{--@endif--}}
                    @if(User::checkCategoryPermission('RateTables','Edit') )
                        <li><a href="javascript:void(0)" id="change-bulk-rate"><i class="entypo-pencil"></i><span>Change Selected</span></a></li>
                    @endif
                    @if(User::checkCategoryPermission('RateTables','Delete') )
                        <li><a href="javascript:void(0)" id="clear-bulk-rate"><i class="entypo-trash"></i><span>Delete Selected</span></a></li>
                    @endif
                    @if(User::checkCategoryPermission('RateTables','ApprovalProcess') )
                        @if($RateApprovalProcess == 1 && $rateTable->AppliedTo != RateTable::APPLIED_TO_VENDOR)
                            <li><a href="javascript:void(0)" id="approve-bulk-rate"><i class="entypo-check"></i><span>Approve Selected</span></a></li>
                            <li><a href="javascript:void(0)" id="disapprove-bulk-rate"><i class="entypo-cancel"></i><span>Reject Selected</span></a></li>
                        @endif
                    @endif
                </ul>
            </div><!-- /btn-group -->

            {{--@if(User::checkCategoryPermission('VendorRates','History'))--}}
            <button class="btn btn-primary pull-right" onclick="location.href='{{ URL::to('/rate_upload/'.$id.'/'.RateUpload::ratetable) }}'">
                <i class="fa fa-upload"></i> Upload Rates
            </button>
            {{--@endif--}}
        </div>
    @endif
</div>
<form id="clear-bulk-rate-form" >
    <input type="hidden" name="RateTableDIDRateID" />
    <input type="hidden" name="criteria" />
    <input type="hidden" name="TimezonesID" value="">
</form>

{{--<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs bordered">
            <!-- available classes "bordered", "right-aligned" -->
            <li class="active  "><a href="{{URL::to('/rate_tables/'.$id.'/view')}}"> <span
                        class="hidden-xs">Rate</span>
                </a></li>
            <li><a href="{{URL::to('/rate_tables/'.$id.'/upload')}}"> <span
                            class="hidden-xs">Upload Rates</span>
                </a></li>
        </ul>
    </div>
</div>--}}


<table class="table table-bordered datatable" id="table-4">
    <thead>
        <tr>
            <th width="3%">
                <div class="checkbox ">
                    <input type="checkbox" id="selectall" name="checkbox[]" />
                </div>
            </th>
            <th width="10%">Access Type</th>
            <th width="10%">Country</th>
            <th width="4%">Origination</th>
            <th width="4%">Prefix</th>
            <th width="10%">City</th>
            <th width="10%">Tariff</th>
            <th width="10%">Time of Day</th>
            <th width="3%">One-Off Cost</th>
            <th width="3%">Monthly Cost</th>
            <th width="5%">Cost Per Call</th>
            <th width="5%">Cost Per Minute</th>
            <th width="5%">Surcharge Per Call</th>
            <th width="5%">Surcharge Per Minute</th>
            <th width="5%">Outpayment Per Call</th>
            <th width="5%">Outpayment Per Minute</th>
            <th width="5%">Surcharges</th>
            <th width="5%">Chargeback</th>
            <th width="5%">Collection Cost</th>
            <th width="5%">Collection Cost (%)</th>
            <th width="5%">Registration Cost</th>
            <th width="8%">Effective Date</th>
            <th width="9%" style="display: none;">End Date</th>
            <th width="8%">Modified By/Date</th>
            @if($RateApprovalProcess == 1 && $rateTable->AppliedTo != RateTable::APPLIED_TO_VENDOR)
            <th width="8%">Status Changed By/Date</th>
            @endif
            <th width="20%" > Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>





    <script type="text/javascript">
        var $searchFilter = {};
        var checked='';
        var codedeckid = '{{$id}}';
        var list_fields  = ['ID','AccessType','Country','OriginationCode','Code','City','Tariff','TimezoneTitle','OneOffCost','MonthlyCost','CostPerCall','CostPerMinute','SurchargePerCall','SurchargePerMinute','OutpaymentPerCall','OutpaymentPerMinute','Surcharges','Chargeback','CollectionCostAmount','CollectionCostPercentage','RegistrationCostPerNumber','EffectiveDate','EndDate','updated_at','ModifiedBy','RateTableDIDRateID','OriginationRateID','RateID','ApprovedStatus','ApprovedBy','ApprovedDate','OneOffCostCurrency','MonthlyCostCurrency', 'CostPerCallCurrency', 'CostPerMinuteCurrency', 'SurchargePerCallCurrency', 'SurchargePerMinuteCurrency', 'OutpaymentPerCallCurrency', 'OutpaymentPerMinuteCurrency', 'SurchargesCurrency', 'ChargebackCurrency', 'CollectionCostAmountCurrency', 'RegistrationCostPerNumberCurrency','OneOffCostCurrencySymbol','MonthlyCostCurrencySymbol', 'CostPerCallCurrencySymbol', 'CostPerMinuteCurrencySymbol', 'SurchargePerCallCurrencySymbol', 'SurchargePerMinuteCurrencySymbol', 'OutpaymentPerCallCurrencySymbol', 'OutpaymentPerMinuteCurrencySymbol', 'SurchargesCurrencySymbol', 'ChargebackCurrencySymbol', 'CollectionCostAmountCurrencySymbol', 'RegistrationCostPerNumberCurrencySymbol','TimezonesID'];
        jQuery(document).ready(function($) {

        $('#filter-button-toggle').show();

        var ratetablepageview = getCookie('ratetablepageview');
        if(ratetablepageview=='AdvanceView'){
            $('#btn-basic-view').removeClass('active');
            $('#btn-advance-view').addClass('active');
        } else {
            $('#btn-advance-view').removeClass('active');
            $('#btn-basic-view').addClass('active');
        }

        /*$("select[name='City'],select[name='Tariff']").on('change', function() {
            var form = $(this).closest('form').attr('id');
            var name = $(this).attr('name');
            var val = $(this).val();

            if(val != '') {
                if(name == 'City') {
                    $("#" + form + " select[name='Tariff']").val('').trigger('change');
                } else {
                    $("#" + form + " select[name='City']").val('').trigger('change');
                }
            }
        });*/

        $("#rate-table-search").submit(function(e) {
            return rateDataTable();
        });
        $("#rate-table-search").trigger('submit');

        $('#table-4 tbody').on('click', 'tr', function() {
            if(!$(this).hasClass('no-selection')) {
                if (checked == '') {
                    $(this).toggleClass('selected');
                    if ($(this).hasClass('selected')) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                    }
                }
            }
        });

        //Clear Rate Button
        $(document).off('click.clear-rate','.btn.clear-rate-table,#clear-bulk-rate');
        $(document).on('click.clear-rate','.btn.clear-rate-table,#clear-bulk-rate',function(ev) {

            var RateTableDIDRateIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                RateTableDIDRateID = $(this).val();
                RateTableDIDRateIDs[i++] = RateTableDIDRateID;
            });

            var $clickedButton = $(this);
            if(typeof $clickedButton.attr('disabled') !== typeof undefined && $clickedButton.attr('disabled') !== false) {
                return false;
            }

            if(RateTableDIDRateIDs.length || $(this).hasClass('clear-rate-table')) {
                response = confirm('Are you sure?');
                if (response) {
                    var TimezonesID     = $searchFilter.Timezones;
                    $("#clear-bulk-rate-form").find("input[name='TimezonesID']").val(TimezonesID);

                    if($(this).hasClass('clear-rate-table')) {
                        var RateTableDIDRateID = $(this).parent().find('.hiddenRowData input[name="RateTableDIDRateID"]').val();
                        $("#clear-bulk-rate-form").find("input[name='RateTableDIDRateID']").val(RateTableDIDRateID);
                        $("#clear-bulk-rate-form").find("input[name='criteria']").val('');
                    }

                    if($(this).attr('id') == 'clear-bulk-rate') {
                        var criteria='';
                        if($('#selectallbutton').is(':checked')){
                            criteria = JSON.stringify($searchFilter);
                            $("#clear-bulk-rate-form").find("input[name='RateTableDIDRateID']").val('');
                            $("#clear-bulk-rate-form").find("input[name='criteria']").val(criteria);
                        }else{
                            var RateTableDIDRateIDs = [];
                            var i = 0;
                            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                                RateTableDIDRateID = $(this).val();
                                RateTableDIDRateIDs[i++] = RateTableDIDRateID;
                            });
                            $("#clear-bulk-rate-form").find("input[name='RateTableDIDRateID']").val(RateTableDIDRateIDs.join(","))
                            $("#clear-bulk-rate-form").find("input[name='criteria']").val('');
                        }
                    }

                    $clickedButton.attr('disabled','disabled');
                    if($clickedButton.attr('id') == 'clear-bulk-rate') {
                        $clickedButton.html('<i class="entypo-trash"></i><span>Deleting...</span>');
                    }

                    var formData = new FormData($('#clear-bulk-rate-form')[0]);
                    formData.append('ApprovedStatus',$searchFilter.ApprovedStatus);

                    $.ajax({
                        url: baseurl + '/rate_tables/{{$id}}/clear_did_rate', //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            $clickedButton.removeAttr('disabled');
                            if($clickedButton.attr('id') == 'clear-bulk-rate') {
                                $clickedButton.html('<i class="entypo-trash"></i><span>Delete Selected</span>');
                            }
                            $(".save.btn").button('reset');

                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                                //data_table.fnFilter('', 0);
                                rateDataTable();
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        // Form data
                        data: formData,
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                    return false;
                }
                return false;
            } else {
                return false;
            }
        });

        //Bulk Edit Button
        $(document).off('click.change-bulk-rate','#change-bulk-rate');
        $(document).on('click.change-bulk-rate','#change-bulk-rate',function(ev) {

            var RateTableDIDRateIDs = [];
            var RateIDs = [];
            var TimezonesID = $searchFilter.Timezones;

            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                //console.log($(this).val());
                RateTableDIDRateID = $(this).val();
                RateTableDIDRateIDs[i] = RateTableDIDRateID;
                RateID = $(this).parents("tr").find("div.hiddenRowData").find("input[name='RateID']").val();
                RateIDs[i] = RateID;
                i++;
            });
            date = new Date();
            var month = date.getMonth()+1;
            var day = date.getDate();
            currentDate = date.getFullYear() + '-' +   (month<10 ? '0' : '') + month + '-' +     (day<10 ? '0' : '') + day;
            $("#bulk-edit-rate-table-form")[0].reset();
            $("#bulk-edit-rate-table-form").find("input[name='RateID']").val(RateIDs.join(","));
            $("#bulk-edit-rate-table-form").find("input[name='Interval1']").val(1);
            $("#bulk-edit-rate-table-form").find("input[name='IntervalN']").val(1);
            $("#bulk-edit-rate-table-form").find("input[name='EffectiveDate']").val(currentDate);
            $("#bulk-edit-rate-table-form").find("input[name='TimezonesID']").val(TimezonesID);

            var criteria = '';
            if ($('#selectallbutton').is(':checked')) {
                criteria = JSON.stringify($searchFilter);
                $("#bulk-edit-rate-table-form").find("input[name='RateTableDIDRateID']").val('');
                $("#bulk-edit-rate-table-form").find("input[name='criteria']").val(criteria);
            } else {
                $("#bulk-edit-rate-table-form").find("input[name='RateTableDIDRateID']").val(RateTableDIDRateIDs.join(","));
                $("#bulk-edit-rate-table-form").find("input[name='criteria']").val('');
            }

            if(RateIDs.length){
                jQuery('#modal-bulk-rate-table').modal('show', {backdrop: 'static'});
            }

        });

        //Bulk Form and Edit Single Form Submit
        $("#bulk-edit-rate-table-form,#edit-rate-table-form").submit(function() {
            var formData = new FormData($(this)[0]);
            formData.append('ApprovedStatus',$searchFilter.ApprovedStatus);
            $.ajax({
                url: baseurl + '/rate_tables/{{$id}}/update_rate_table_did_rate', //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $(".save.btn").button('reset');

                    if (response.status == 'success') {
                        $('#modal-bulk-rate-table').modal('hide');
                        $('#modal-rate-table').modal('hide');
                        toastr.success(response.message, "Success", toastr_opts);
                        //data_table.fnFilter('', 0);
                        rateDataTable();
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
            return false;
        });

        //Bulk Approve Button
        $(document).off('click.approve-bulk-rate','#approve-bulk-rate,#disapprove-bulk-rate');
        $(document).on('click.approve-bulk-rate','#approve-bulk-rate,#disapprove-bulk-rate',function(ev) {
            var $this = $(this);
            if(!$this.hasClass('processing')) {
                var button_id = $(this).attr('id');
                var button_text = $(this).html();
                var RateTableDIDRateIDs = [];
                var TimezonesID = $searchFilter.Timezones;
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    RateTableDIDRateID = $(this).val();
                    RateTableDIDRateIDs[i] = RateTableDIDRateID;
                    i++;
                });

                var formdata = new FormData();
                formdata.append('TimezonesID', TimezonesID);
                if(button_id == 'approve-bulk-rate') {
                    formdata.append('ApprovedStatus', 1);
                } else {
                    formdata.append('ApprovedStatus', 2);
                }
                var criteria = '';
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                    formdata.append('RateTableDIDRateID', '');
                    formdata.append('criteria', criteria);
                } else {
                    formdata.append('RateTableDIDRateID', RateTableDIDRateIDs.join(","));
                    formdata.append('criteria', '');
                }

                if (RateTableDIDRateIDs.length) {
                    $this.text('Processing...').addClass('processing');
                    $.ajax({
                        url: baseurl + '/rate_tables/{{$id}}/approve_rate_table_did_rate', //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            $this.html(button_text).removeClass('processing');
                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                                rateDataTable();
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        // Form data
                        data: formdata,
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                }
            }
            return false;
        });

        // Replace Checboxes
        $(".pagination a").click(function(ev) {
            replaceCheckboxes();
        });
        $("#add-new-rate").click(function(e){
            e.preventDefault();
            $("#new-rate-form")[0].reset();
            $("#new-rate-form .rateid_list").select2("val","");
            $("#new-rate-form .select_controls").select2("val","");
            //$("#new-rate-form [name='RateID']").select2().select2('val','');
            $("#modal-add-new").modal('show');
        });
        $("#new-rate-form").submit(function(e){
            e.preventDefault();
            fullurl = baseurl+'/rate_tables/{{$id}}/add_newrate';

            $.ajax({
                url:fullurl, //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $(".btn").button('reset');
                    if (response.status == 'success') {
                        $('.modal').modal('hide');
                        toastr.success(response.message, "Success", toastr_opts);
                        if( typeof data_table !=  'undefined'){
                            rateDataTable();
                        }
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                data: $("#new-rate-form").serialize(),
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
            });

            //submit_ajax(fullurl,$("#new-rate-form").serialize());
        return false;
        });
        $('.rateid_list').select2({
            placeholder: 'Enter a Prefix',
            minimumInputLength: 1,
            ajax: {
                dataType: 'json',
                url: baseurl+'/rate_tables/getCodeByAjax',
                data: function (term, page) {
                    return {
                        q: term,
                        page: codedeckid
                    };
                },
                quietMillis: 500,
                error: function (data) {
                    return false;
                },
                results: function (data, page) {
                    return {
                        results: data
                    };
                }
            }
        });

        // to show/hide child row (Code list) when Group By Description
        $('#table-4 tbody').on('click', 'td div.details-control', function () {
            var tr = $(this).closest('tr');
            var row = data_table.row(tr);

            if (row.child.isShown()) {
                $(this).find('i').toggleClass('entypo-plus-squared entypo-minus-squared');
                row.child.hide();
                tr.removeClass('shown');
            } else {
                $(this).find('i').toggleClass('entypo-plus-squared entypo-minus-squared');
                var hiddenRowData = tr.find('.hiddenRowData');
                var OriginationCode = hiddenRowData.find('input[name="OriginationCode"]').val();
                var Code = hiddenRowData.find('input[name="Code"]').val();
                var OriginationCode = OriginationCode.split(',');
                var Code = Code.split(',');
                var table = $('<table class="table table-bordered datatable dataTable no-footer" style="margin-left: 4%;width: 92% !important;"></table>');
                table.append("<thead><tr><th style='width:10%'>Origination</th><th>Prefix</th></tr></thead>");
                //table.append("<thead><tr><th>Code</th></tr></thead>");
                var tbody = $("<tbody></tbody>");
                for (var i = 0; i < Code.length; i++) {
                    table.append("<tr class='no-selection'><td>" + OriginationCode[i] + "</td><td>" + Code[i] + "</td></tr>");
                    //table.append("<tr class='no-selection'><td>" + Code[i] + "</td></tr>");
                }
                table.append(tbody);
                row.child(table).show();
                tr.addClass('shown');
            }
        });

        $("#DiscontinuedRates").on('change', function (event, state) {
            if($("#DiscontinuedRates").is(':checked')) {
                $(".EffectiveBox").hide();
            } else {
                $(".EffectiveBox").show();
            }
        });

        $(document).on('click', '.btn-history', function() {
            var $this   = $(this);
            var RateID   = $this.prevAll("div.hiddenRowData").find("input[name='RateID']").val();
            var OriginationRateID = $this.prevAll("div.hiddenRowData").find("input[name='OriginationRateID']").val();
            var TimezonesID = $this.prevAll("div.hiddenRowData").find("input[name='TimezonesID']").val();
            var City = $this.prevAll("div.hiddenRowData").find("input[name='City']").val();
            var Tariff = $this.prevAll("div.hiddenRowData").find("input[name='Tariff']").val();
            getArchiveRateTableDIDRates($this,RateID,OriginationRateID,TimezonesID,City,Tariff);
        });

        $(".numbercheck").keypress(function (e) {
            //allow only float value, numbers and one dot(.) only
            if ((e.which != 46 || $(this).val().indexOf('.') != -1) && e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                //display error message
                return false;
            }
        });
        $(document).on('click','.view-switcher', function() {
            var id = $(this).attr('id');
            if(!$(this).hasClass('active')) {
                if (id == 'btn-basic-view') {
                    setCookie('ratetablepageview','BasicView','30');
                    $('#btn-advance-view').removeClass('active');
                    $('#btn-basic-view').addClass('active');
                } else {
                    setCookie('ratetablepageview','AdvanceView','30');
                    $('#btn-basic-view').removeClass('active');
                    $('#btn-advance-view').addClass('active');
                }
                rateDataTable();
            }
        });

        $(document).on('change','#rate-table-search select[name="ApprovedStatus1"],#rate-table-search select[name="ApprovedStatus2"]',function(ev) {
            var Status;
            if($('#rate-table-search select[name="ApprovedStatus1"]').val() == {{RateTable::RATE_STATUS_APPROVED}}) {
                Status = $('#rate-table-search select[name="ApprovedStatus1"]').val();
                $('#ApprovedStatus2-Box').hide();
                $('.filter_naa').show();
            } else {
                Status = $('#rate-table-search select[name="ApprovedStatus2"]').val();
                $('#ApprovedStatus2-Box').show();
                $('.filter_naa').hide();
            }
            $('#rate-table-search [name="ApprovedStatus"]').val(Status);
        });
    });

    function rateDataTable() {
        var bVisible = false;
        ratetablepageview = getCookie('ratetablepageview');
        if(ratetablepageview == 'AdvanceView') {
            bVisible = true;
        } else {
            bVisible = false;
        }

        $searchFilter.OriginationCode = $("#rate-table-search input[name='OriginationCode']").val();
        $searchFilter.OriginationDescription = $("#rate-table-search input[name='OriginationDescription']").val();
        $searchFilter.Code = $("#rate-table-search input[name='Code']").val();
        $searchFilter.Description = $("#rate-table-search input[name='Description']").val();
        $searchFilter.AccessType = $("#rate-table-search select[name='AccessType']").val();
        $searchFilter.City = $("#rate-table-search select[name='City']").val();
        $searchFilter.Tariff = $("#rate-table-search select[name='Tariff']").val();
        $searchFilter.Country = $("#rate-table-search select[name='Country']").val();
        $searchFilter.TrunkID = $("#rate-table-search [name='TrunkID']").val();
        $searchFilter.Effective = Effective = $("#rate-table-search [name='Effective']").val();
        $searchFilter.DiscontinuedRates = DiscontinuedRates = $("#rate-table-search input[name='DiscontinuedRates']").is(':checked') ? 1 : 0;
        $searchFilter.Timezones = Timezones = $("#rate-table-search select[name='Timezones']").val();
        $searchFilter.ApprovedStatus = ApprovedStatus = $("#rate-table-search [name='ApprovedStatus']").val();
        $searchFilter.ratetablepageview = ratetablepageview;

        $searchFilter.ResellerPage = ResellerPage = $('#rate-table-search [name="ResellerPage"]').val();

        data_table = $("#table-4").DataTable({
            "bDestroy": true, // Destroy when resubmit form
            "bProcessing": true,
            "bServerSide": true,
            "scrollX": true,
            "initComplete": function(settings, json) { // to hide extra row which is displaying due to scrollX
                $('.dataTables_scrollBody thead tr').css({visibility:'collapse'});
            },
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "sAjaxSource": baseurl + "/rate_tables/{{$id}}/search_ajax_datagrid",
            "fnServerParams": function(aoData) {
                aoData.push({"name": "OriginationCode", "value": $searchFilter.OriginationCode}, {"name": "OriginationDescription", "value": $searchFilter.OriginationDescription}, {"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country},{"name": "TrunkID", "value": $searchFilter.TrunkID},{"name": "Effective", "value": $searchFilter.Effective}, {"name": "DiscontinuedRates", "value": DiscontinuedRates},{"name": "Timezones", "value": Timezones},{"name": "ApprovedStatus", "value": ApprovedStatus},{"name": "ratetablepageview", "value": ratetablepageview},{"name": "City", "value": $searchFilter.City},{"name": "Tariff", "value": $searchFilter.Tariff},{"name": "AccessType", "value": $searchFilter.AccessType},{"name": "ResellerPage", "value": ResellerPage});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name": "OriginationCode", "value": $searchFilter.OriginationCode}, {"name": "OriginationDescription", "value": $searchFilter.OriginationDescription}, {"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country},{"name": "TrunkID", "value": $searchFilter.TrunkID},{"name": "Effective", "value": $searchFilter.Effective}, {"name": "DiscontinuedRates", "value": DiscontinuedRates},{"name": "Timezones", "value": Timezones},{"name": "ApprovedStatus", "value": ApprovedStatus},{"name": "ratetablepageview", "value": ratetablepageview},{"name": "City", "value": $searchFilter.City},{"name": "Tariff", "value": $searchFilter.Tariff},{"name": "AccessType", "value": $searchFilter.AccessType},{"name": "ResellerPage", "value": ResellerPage});
            },
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            //  "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[4, "asc"]],
            "aoColumns":
                    [
                        {"bSortable": false,
                            mRender: function(id, type, full) {
                                var html = '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';

                                @if($RateApprovalProcess == 1 && $rateTable->AppliedTo != RateTable::APPLIED_TO_VENDOR)
                                if (full[28] == {{RateTable::RATE_STATUS_REJECTED}}) {
                                    html += '<i class="entypo-cancel" title="Rejected" style="color: red; "></i>';
                                } else if (full[28] == {{RateTable::RATE_STATUS_APPROVED}}) {
                                    html += '<i class="entypo-check" title="Approved" style="color: green; "></i>';
                                } else if (full[28] == {{RateTable::RATE_STATUS_AWAITING}}) {
                                    html += '<i class="fa fa-hourglass-1" title="Awaiting Approval" style="color: grey; "></i>';
                                } else if (full[28] == {{RateTable::RATE_STATUS_DELETE}}) {
                                    html += '<i class="fa fa-trash" title="Awaiting Approval Delete" style="color: red; "></i>';
                                }
                                @endif

                                return html;
                            }
                        }, //0Checkbox
                        {}, //1 AccessType
                        {}, //2 Country
                        {
                            mRender: function(id, type, full) {
                                return full[3];
                            },
                            "className":      'details-control',
                            "orderable":      false,
                            "data": null,
                            "defaultContent": ''
                        }, //3 Origination Code
                        {}, //4 Destination Code
                        {}, //5 City
                        {}, //6 Tariff
                        {}, //7 Timezones Title
                        {
                            mRender: function(col, type, full) {
                                if(col != null && col != '') return full[43] + col; else return '';
                            }
                        }, //8 OneOffCost,
                        {
                            mRender: function(col, type, full) {
                                if(col != null && col != '') return full[44] + col; else return '';
                            }
                        }, //9 MonthlyCost,
                        {
                            mRender: function(col, type, full) {
                                if(col != null && col != '') return full[45] + col; else return '';
                            }
                        }, //10 CostPerCall,
                        {
                            mRender: function(col, type, full) {
                                if(col != null && col != '') return full[46] + col; else return '';
                            }
                        }, //11 CostPerMinute,
                        {
                            mRender: function(col, type, full) {
                                if(col != null && col != '') return full[47] + col; else return '';
                            }
                        }, //12 SurchargePerCall,
                        {
                            mRender: function(col, type, full) {
                                if(col != null && col != '') return full[48] + col; else return '';
                            }
                        }, //13 SurchargePerMinute,
                        {
                            mRender: function(col, type, full) {
                                if(col != null && col != '') return full[49] + col; else return '';
                            }
                        }, //14 OutpaymentPerCall,
                        {
                            mRender: function(col, type, full) {
                                if(col != null && col != '') return full[50] + col; else return '';
                            }
                        }, //15 OutpaymentPerMinute,
                        {
                            mRender: function(col, type, full) {
                                if(col != null && col != '') return full[51] + col; else return '';
                            }
                        }, //16 Surcharges,
                        {
                            mRender: function(col, type, full) {
                                if(col != null && col != '') return full[52] + col; else return '';
                            }
                        }, //17 Chargeback,
                        {
                            mRender: function(col, type, full) {
                                if(col != null && col != '') return full[53] + col; else return '';
                            }
                        }, //18 CollectionCostAmount,
                        {}, //19 CollectionCostPercentage,
                        {
                            mRender: function(col, type, full) {
                                if(col != null && col != '') return full[54] + col; else return '';
                            }
                        }, //20 RegistrationCostPerNumber,
                        {}, //21 Effective Date
                        {
                            "bVisible" : false
                        }, //22 End Date
                        {
                            "bVisible" : bVisible,
                            mRender: function(id, type, full) {
                                full[23] = full[23] != null ? full[23] : '';
                                full[24] = full[24] != null ? full[24] : '';
                                if(full[23] != '' && full[24] != '')
                                    return full[24] + '<br/>' + full[23]; // modified by/modified date
                                else
                                    return '';
                            }
                        }, //24/23 modified by/modified date
                        @if($RateApprovalProcess == 1 && $rateTable->AppliedTo != RateTable::APPLIED_TO_VENDOR)
                        {
                            "bVisible" : bVisible,
                            mRender: function(id, type, full) {
                                full[29] = full[29] != null ? full[29] : '';
                                full[30] = full[30] != null ? full[30] : '';
                                if(full[29] != '' && full[30] != '')
                                    return full[29] + '<br/>' + full[30]; // Approved Status Changed By/Approved Date
                                else
                                    return '';
                            }
                        }, //29/30 Approved Status Changed By/Approved Date
                        @endif
                        {
                            "bSortable" : false,
                            mRender: function(id, type, full) {
                                $('#actionheader').attr('width','10%');
                                var action, edit_, delete_;
                                action = '<div class = "hiddenRowData" >';
                                for (var i = 0; i < list_fields.length; i++) {
                                    action += '<input type = "hidden"  name = "' + list_fields[i] + '" value = "' + (full[i] != null ? full[i] : '') + '" / >';
                                }
                                action += '</div>';

                                clerRate_ = "{{ URL::to('/rate_tables/{id}/clear_did_rate')}}";
                                clerRate_ = clerRate_.replace('{id}', full[25]);

                                <?php if(User::checkCategoryPermission('RateTables', 'Edit')) { ?>
                                if (DiscontinuedRates == 0) {
                                    @if(empty($ResellerPage))
                                        // if approved rates then show Edit button else hide it
                                        if(full[28] == {{RateTable::RATE_STATUS_AWAITING}} || parseInt("{{$RateApprovalProcess}}") != 1 || {{$rateTable->AppliedTo}} == {{RateTable::APPLIED_TO_VENDOR}}) {
                                            action += ' <button href="Javascript:;"  title="Edit" class="edit-rate-table btn btn-default btn-xs"><i class="entypo-pencil"></i>&nbsp;</button>';
                                        }
                                    @endif
                                }
                                <?php } ?>

                                // if approved rates then show history button else hide it
                                if($searchFilter.ApprovedStatus == {{RateTable::RATE_STATUS_APPROVED}}) {
                                    action += ' <button href="Javascript:;" title="History" class="btn btn-default btn-xs btn-history details-control"><i class="entypo-back-in-time"></i>&nbsp;</button>';
                                }

                                if (full[25] != null && full[25] != 0) {
                                    <?php if(User::checkCategoryPermission('RateTables', 'Delete')) { ?>
                                    if (DiscontinuedRates == 0) {
                                        @if(empty($ResellerPage))
                                            action += ' <button title="Delete" href="' + clerRate_ + '"  class="btn clear-rate-table btn-danger btn-xs" data-loading-text="Loading..."><i class="entypo-trash"></i></button>';
                                        @endif
                                    }
                                    <?php } ?>
                                }

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
                        "sUrl": baseurl + "/rate_tables/{{$id}}/rate_exports/xlsx",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/rate_tables/{{$id}}/rate_exports/csv",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
            "fnDrawCallback": function() {
                $(".dropdown").removeClass("hidden");
                var toggle = '<header>';
                toggle += '<span class="list-style-buttons">';
                if(ratetablepageview=='AdvanceView'){
                    toggle += '<a href="javascript:void(0)" title="Basic View" class="btn btn-primary view-switcher" id="btn-basic-view"><i class="fa fa-list-alt"></i></a>';
                    toggle += '<a href="javascript:void(0)" title="Advance View" class="btn btn-primary view-switcher active" id="btn-advance-view"><i class="fa fa-list"></i></a>';
                }else{
                    toggle += '<a href="javascript:void(0)" title="Basic View" class="btn btn-primary view-switcher active" id="btn-basic-view"><i class="fa fa-list-alt"></i></a>';
                    toggle += '<a href="javascript:void(0)" title="Advance View" class="btn btn-primary view-switcher" id="btn-advance-view"><i class="fa fa-list"></i></a>';
                }
                toggle +='</span>';
                toggle += '</header>';
                $('.change-view').html(toggle);

                $(".btn.clear").click(function(e) {

                    response = confirm('Are you sure?');
                    //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
                    if (response) {
                        $.ajax({
                            url: $(this).attr("href"),
                            type: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                $(".btn.delete").button('reset');
                                if (response.status == 'success') {
                                    //data_table.fnFilter('', 0);
                                    rateDataTable();
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
                })

                $("#selectall").off('click');
                $("#selectall").click(function(ev) {
                    var is_checked = $(this).is(':checked');
                    $('#table-4 tbody tr').each(function(i, el) {
                        if (is_checked) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                            $(this).addClass('selected');
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                            $(this).removeClass('selected');
                        }
                    });
                });

                //Edit Button
                $(".edit-rate-table.btn").off('click');
                $(".edit-rate-table.btn").click(function(ev) {
                    ev.stopPropagation();

                    $("#edit-rate-table-form  [name=OriginationRateID]").select2("val", "");
                    var cur_obj = $(this).prevAll("div.hiddenRowData");
                    for(var i = 0 ; i< list_fields.length; i++){
                        $("#edit-rate-table-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val()).trigger('change');
                    }
                    // these 2 controls divided from one so, we need to do hard code here
                    /*if($("#edit-rate-table-form [name='City'] option[value='"+cur_obj.find("input[name='CityTariff']").val()+"']").length != 0)
                        $("#edit-rate-table-form [name='City']").val(cur_obj.find("input[name='CityTariff']").val()).trigger('change');
                    if($("#edit-rate-table-form [name='Tariff'] option[value='"+cur_obj.find("input[name='CityTariff']").val()+"']").length != 0)
                        $("#edit-rate-table-form [name='Tariff']").val(cur_obj.find("input[name='CityTariff']").val()).trigger('change');*/

                    var OriginationRateID = cur_obj.find("input[name=OriginationRateID]").val();
                    if(OriginationRateID == null || OriginationRateID == '') {
                        $('#box-edit-OriginationRateID').show();
                    } else {
                        $('#box-edit-OriginationRateID').hide();
                    }
                    var TimezonesID = $searchFilter.Timezones;
                    $("#edit-rate-table-form").find("input[name='TimezonesID']").val(TimezonesID);
                    jQuery('#modal-rate-table').modal('show', {backdrop: 'static'});
                });

                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });

                //select all button
                $('#table-4 tbody tr').each(function(i, el) {
                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                        if (checked != '') {
                            $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                            $(this).addClass('selected');
                            $('#selectallbutton').prop("checked", true);
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                            ;
                            $(this).removeClass('selected');
                        }
                    }
                });

                $("#selectallbutton").off('click');
                $('#selectallbutton').click(function(ev) {
                    if($(this).is(':checked')){
                        checked = 'checked=checked disabled';
                        $("#selectall").prop("checked", true).prop('disabled', true);
                        if(!$('#changeSelectedInvoice').hasClass('hidden')){
                            $('#table-4 tbody tr').each(function(i, el) {
                                if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                                    $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                    $(this).addClass('selected');
                                }
                            });
                        }
                    }else{
                        checked = '';
                        $("#selectall").prop("checked", false).prop('disabled', false);
                        if(!$('#changeSelectedInvoice').hasClass('hidden')){
                            $('#table-4 tbody tr').each(function(i, el) {
                                if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                                    $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                    $(this).removeClass('selected');
                                }
                            });
                        }
                    }
                });

                // if approved rates then show Bulk update button else hide it
                if(parseInt("{{$RateApprovalProcess}}") != 1 || {{$rateTable->AppliedTo}} == {{RateTable::APPLIED_TO_VENDOR}} || ($searchFilter.ApprovedStatus!= '' && $searchFilter.ApprovedStatus == {{RateTable::RATE_STATUS_AWAITING}})) {
                    if (Effective == 'All' || DiscontinuedRates == 1) {//if(Effective == 'All' || DiscontinuedRates == 1) {
                        $('#change-bulk-rate').hide();
                    } else {
                        $('#change-bulk-rate').show();
                    }
                } else {
                    $('#change-bulk-rate').hide();
                }
                if($searchFilter.ApprovedStatus == {{RateTable::RATE_STATUS_APPROVED}}) {
                    $('#approve-bulk-rate,#disapprove-bulk-rate').hide();
                } else {
                    $('#approve-bulk-rate,#disapprove-bulk-rate').show();
                }

                if(DiscontinuedRates == 1) {
                    $('#clear-bulk-rate').hide();
                } else {
                    $('#clear-bulk-rate').show();
                }
            }
        });

        $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
        return false;
    }

    function getArchiveRateTableDIDRates($clickedButton,RateID,OriginationRateID,TimezonesID,City,Tariff) {
        //var Codes = new Array();
        var ArchiveRates;
        /*$("#table-4 tr td:nth-child(2)").each(function(){
            Codes.push($(this).html());
        });*/

        var tr  = $clickedButton.closest('tr');
        var row = data_table.row(tr);

        if (row.child.isShown()) {
            tr.find('.details-control i').toggleClass('entypo-plus-squared entypo-minus-squared');
            row.child.hide();
            tr.removeClass('shown');
        } else {
            tr.find('.details-control i').toggleClass('entypo-plus-squared entypo-minus-squared');
            $clickedButton.attr('disabled','disabled');

            $.ajax({
                url: baseurl + "/rate_tables/{{$id}}/search_ajax_datagrid_archive_rates",
                type: 'POST',
                data: "RateID=" + RateID + "&OriginationRateID=" + OriginationRateID + "&TimezonesID=" + TimezonesID + "&City=" + City + "&Tariff=" + Tariff,
                dataType: 'json',
                cache: false,
                success: function (response) {
                    $clickedButton.removeAttr('disabled');

                    if (response.status == 'success') {
                        ArchiveRates = response.data;
                        //$('.details-control').show();
                    } else {
                        ArchiveRates = {};
                        toastr.error(response.message, "Error", toastr_opts);
                    }

                    $clickedButton.find('i').toggleClass('entypo-plus-squared entypo-minus-squared');
                    var hiddenRowData = tr.find('.hiddenRowData');
                    var Code = hiddenRowData.find('input[name="Code"]').val();
                    var table = $('<table class="table table-bordered datatable dataTable no-footer" style="margin-left: 4%;width: 92% !important;"></table>');

                    var header = "<thead><tr><th>Access Type</th><th>Country</th><th>Origination</th>";
                    header += "<th>Prefix</th>";
                    header += "<th>City</th><th>Tariff</th><th>One-Off Cost</th><th>Monthly Cost</th><th>Cost Per Call</th><th>Cost Per Minute</th><th>Surcharge Per Call</th><th>Surcharge Per Minute</th><th>Outpayment Per Call</th><th>Outpayment Per Minute</th><th>Surcharges</th><th>Chargeback</th><th>Collection Cost</th><th>Collection Cost (%)</th><th>Registration Cost</th><th class='sorting_desc'>Effective Date</th><th>End Date</th>";
                    if(ratetablepageview == 'AdvanceView') {
                        header += "<th>Modified By/Date</th>";
                    }
                    @if($RateApprovalProcess == 1 && $rateTable->AppliedTo != RateTable::APPLIED_TO_VENDOR)
                    header += "<th>Status Changed By/Date</th><th>Status</th>";
                    @endif
                    header += "</tr></thead>";

                    table.append(header);
                    //table.append("<thead><tr><th>Code</th><th>Description</th><th>One-Off Cost</th><th>Monthly Cost</th><th>Cost Per Call</th><th>Cost Per Minute</th><th>Surcharge Per Call</th><th>Surcharge Per Minute</th><th>Outpayment Per Call</th><th>Outpayment Per Minute</th><th>Surcharges</th><th>Chargeback</th><th>Collection Cost</th><th>Collection Cost (%)</th><th>Registration Cost</th><th class='sorting_desc'>Effective Date</th><th>End Date</th><th>Modified Date</th><th>Modified By</th></tr></thead>");
                    var tbody = $("<tbody></tbody>");

                    ArchiveRates.forEach(function (data) {
                        //if (data['Code'] == Code) {
                            data['OriginationCode'] = data['OriginationCode'] != null ? data['OriginationCode'] : '';
                            data['OriginationDescription'] = data['OriginationDescription'] != null ? data['OriginationDescription'] : '';
                            var html = "";
                            html += "<tr class='no-selection'>";
                            html += "<td>" + (data['AccessType'] != null?data['AccessType']:'') + "</td>";
                            html += "<td>" + (data['Country'] != null?data['Country']:'') + "</td>";
                            html += "<td>" + data['OriginationCode'] + "</td>";
                            html += "<td>" + data['Code'] + "</td>";
                            html += "<td>" + data['City'] + "</td>";
                            html += "<td>" + data['Tariff'] + "</td>";
                            html += "<td>" + (data['OneOffCost'] != null?data['OneOffCost']:'') + "</td>";
                            html += "<td>" + (data['MonthlyCost'] != null?data['MonthlyCost']:'') + "</td>";
                            html += "<td>" + (data['CostPerCall'] != null?data['CostPerCall']:'') + "</td>";
                            html += "<td>" + (data['CostPerMinute'] != null?data['CostPerMinute']:'') + "</td>";
                            html += "<td>" + (data['SurchargePerCall'] != null?data['SurchargePerCall']:'') + "</td>";
                            html += "<td>" + (data['SurchargePerMinute'] != null?data['SurchargePerMinute']:'') + "</td>";
                            html += "<td>" + (data['OutpaymentPerCall'] != null?data['OutpaymentPerCall']:'') + "</td>";
                            html += "<td>" + (data['OutpaymentPerMinute'] != null?data['OutpaymentPerMinute']:'') + "</td>";
                            html += "<td>" + (data['Surcharges'] != null?data['Surcharges']:'') + "</td>";
                            html += "<td>" + (data['Chargeback'] != null?data['Chargeback']:'') + "</td>";
                            html += "<td>" + (data['CollectionCostAmount'] != null?data['CollectionCostAmount']:'') + "</td>";
                            html += "<td>" + (data['CollectionCostPercentage'] != null?data['CollectionCostPercentage']:'') + "</td>";
                            html += "<td>" + (data['RegistrationCostPerNumber'] != null?data['RegistrationCostPerNumber']:'') + "</td>";
                            html += "<td>" + data['EffectiveDate'] + "</td>";
                            html += "<td>" + data['EndDate'] + "</td>";
                            if(ratetablepageview == 'AdvanceView') {
                                html += "<td>" + data['ModifiedBy'] + '<br/>' + data['ModifiedDate'] + "</td>";
                            }

                            @if($RateApprovalProcess == 1 && $rateTable->AppliedTo != RateTable::APPLIED_TO_VENDOR)
                                html += "<td>" + (data['ApprovedBy'] != null?data['ApprovedBy'] + '<br/>':'') + (data['ApprovedDate'] != null?data['ApprovedDate']:'') + "</td>";
                                if (data['ApprovedStatus'] == 2)
                                    html += '<td><i class="entypo-cancel" title="Rejected" style="color: red; "></i></td>';
                                else if(data['ApprovedStatus'] == 1)
                                    html += '<td><i class="entypo-check" title="Approved" style="color: green; "></i></td>';
                                else if(data['ApprovedStatus'] == 0)
                                    html += '<td><i class="fa fa-hourglass-1" title="Awaiting Approval" style="color: grey; "></i></td>';
                            @endif

                            html += "</tr>";
                            table.append(html);
                        //}
                    });
                    table.append(tbody);
                    row.child(table).show();
                    row.child().addClass('no-selection child-row');
                    tr.addClass('shown');
                }
            });
        }
    }

</script>
<style>
#selectcheckbox{
    padding: 15px 10px;
}
.component-form-control {
    padding: 0;
}
</style>
@stop @section('footer_ext') @parent
<div class="modal fade" id="modal-rate-table">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="edit-rate-table-form" method="post" >

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Rate Table Rate</h4>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Access Type</label>
                                {{ Form::select('AccessType', array('' => 'Select') + $AccessType, '' , array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6" id="box-edit-OriginationRateID" style="display: none;">
                            <div class="form-group">
                                <label class="control-label">Origination</label>
                                <input type="hidden" class="rateid_list" name="OriginationRateID" />
                            </div>
                        </div>
                        <div class="col-md-6 clear">
                            <div class="form-group">
                                <label class="control-label">City</label>
                                {{ Form::select('City', array('' => 'Select') + $City, '' , array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Tariff</label>
                                {{ Form::select('Tariff', array('' => 'Select') + $Tariff, '' , array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Effective Date</label>
                                <input type="text"  name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">One-Off Cost</label>
                                <input type="text" name="OneOffCost" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('OneOffCostCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Monthly Cost</label>
                                <input type="text" name="MonthlyCost" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('MonthlyCostCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Cost Per Call</label>
                                <input type="text" name="CostPerCall" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('CostPerCallCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Cost Per Minute</label>
                                <input type="text" name="CostPerMinute" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('CostPerMinuteCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Surcharge Per Call</label>
                                <input type="text" name="SurchargePerCall" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('SurchargePerCallCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Surcharge Per Minute</label>
                                <input type="text" name="SurchargePerMinute" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('SurchargePerMinuteCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Outpayment Per Call</label>
                                <input type="text" name="OutpaymentPerCall" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('OutpaymentPerCallCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Outpayment Per Minute</label>
                                <input type="text" name="OutpaymentPerMinute" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('OutpaymentPerMinuteCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Surcharges</label>
                                <input type="text" name="Surcharges" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('SurchargesCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Chargeback</label>
                                <input type="text" name="Chargeback" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('ChargebackCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Collection Cost (Amount)</label>
                                <input type="text" name="CollectionCostAmount" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('CollectionCostAmountCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Collection Cost (Percentage)</label>
                                <input type="text" name="CollectionCostPercentage" class="form-control numbercheck" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Registration Cost Per Number</label>
                                <input type="text" name="RegistrationCostPerNumber" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('RegistrationCostPerNumberCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        {{--<div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">End Date</label>
                                <input type="text"  name="EndDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>--}}
                     </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateTableDIDRateID" value="">
                    <input type="hidden" name="RateID" value="">
                    <input type="hidden" name="criteria" value="">
                    <input type="hidden" name="updateEffectiveDate" value="on">
                    <input type="hidden" name="updateOriginationRateID" value="on">
                    <input type="hidden" name="updateCity" value="on">
                    <input type="hidden" name="updateTariff" value="on">
                    <input type="hidden" name="updateAccessType" value="on">
                    <input type="hidden" name="updateOneOffCost" value="on">
                    <input type="hidden" name="updateMonthlyCost" value="on">
                    <input type="hidden" name="updateCostPerCall" value="on">
                    <input type="hidden" name="updateCostPerMinute" value="on">
                    <input type="hidden" name="updateSurchargePerCall" value="on">
                    <input type="hidden" name="updateSurchargePerMinute" value="on">
                    <input type="hidden" name="updateOutpaymentPerCall" value="on">
                    <input type="hidden" name="updateOutpaymentPerMinute" value="on">
                    <input type="hidden" name="updateSurcharges" value="on">
                    <input type="hidden" name="updateChargeback" value="on">
                    <input type="hidden" name="updateCollectionCostAmount" value="on">
                    <input type="hidden" name="updateCollectionCostPercentage" value="on">
                    <input type="hidden" name="updateRegistrationCostPerNumber" value="on">
                    <input type="hidden" name="updateOneOffCostCurrency" value="on">
                    <input type="hidden" name="updateMonthlyCostCurrency" value="on">
                    <input type="hidden" name="updateCostPerCallCurrency" value="on">
                    <input type="hidden" name="updateCostPerMinuteCurrency" value="on">
                    <input type="hidden" name="updateSurchargePerCallCurrency" value="on">
                    <input type="hidden" name="updateSurchargePerMinuteCurrency" value="on">
                    <input type="hidden" name="updateOutpaymentPerCallCurrency" value="on">
                    <input type="hidden" name="updateOutpaymentPerMinuteCurrency" value="on">
                    <input type="hidden" name="updateSurchargesCurrency" value="on">
                    <input type="hidden" name="updateChargebackCurrency" value="on">
                    <input type="hidden" name="updateCollectionCostAmountCurrency" value="on">
                    <input type="hidden" name="updateRegistrationCostPerNumberCurrency" value="on">
                    <input type="hidden" name="updateEndDate" value="on">
                    <input type="hidden" name="updateType" value="singleEdit">
                    <input type="hidden" name="TimezonesID" value="">

                    <button type="submit" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i> Save
                    </button>
                    <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i> Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Bulk Update -->
<div class="modal fade" id="modal-bulk-rate-table">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="bulk-edit-rate-table-form" method="post">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Bulk Edit Rate Table</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateAccessType" class="" />
                                <label class="control-label">Access Type</label>
                                {{ Form::select('AccessType', array('' => 'Select') + $AccessType, '' , array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateOriginationRateID" class="" />
                                <label class="control-label">Origination</label>
                                <input type="hidden" class="rateid_list" name="OriginationRateID" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateCity" class="" />
                                <label class="control-label">City</label>
                                {{ Form::select('City', array('' => 'Select') + $City, '' , array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateTariff" class="" />
                                <label class="control-label">Tariff</label>
                                {{ Form::select('Tariff', array('' => 'Select') + $Tariff, '' , array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateEffectiveDate" class="" />
                                <label class="control-label">Effective Date</label>
                                <input type="text" name="EffectiveDate" class="form-control datepicker"  data-startdate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <input type="checkbox" name="updateOneOffCost" class="" />
                                <label class="control-label">One-Off Cost</label>
                                <input type="text" name="OneOffCost" class="form-control numbercheck" value="" />
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <input type="checkbox" name="updateOneOffCostCurrency" class="" />
                                <label class="control-label"> Currency</label>
                                {{ Form::select('OneOffCostCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <input type="checkbox" name="updateMonthlyCost" class="" />
                                <label class="control-label">Monthly Cost</label>
                                <input type="text" name="MonthlyCost" class="form-control numbercheck" value="" />
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <input type="checkbox" name="updateMonthlyCostCurrency" class="" />
                                <label class="control-label"> Currency</label>
                                {{ Form::select('MonthlyCostCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <input type="checkbox" name="updateCostPerCall" class="" />
                                <label class="control-label">Cost Per Call</label>
                                <input type="text" name="CostPerCall" class="form-control numbercheck" value="" />
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <input type="checkbox" name="updateCostPerCallCurrency" class="" />
                                <label class="control-label"> Currency</label>
                                {{ Form::select('CostPerCallCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <input type="checkbox" name="updateCostPerMinute" class="" />
                                <label class="control-label">Cost Per Minute</label>
                                <input type="text" name="CostPerMinute" class="form-control numbercheck" value="" />
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <input type="checkbox" name="updateCostPerMinuteCurrency" class="" />
                                <label class="control-label"> Currency</label>
                                {{ Form::select('CostPerMinuteCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <input type="checkbox" name="updateSurchargePerCall" class="" />
                                <label class="control-label">Surcharge Per Call</label>
                                <input type="text" name="SurchargePerCall" class="form-control numbercheck" value="" />
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <input type="checkbox" name="updateSurchargePerCallCurrency" class="" />
                                <label class="control-label"> Currency</label>
                                {{ Form::select('SurchargePerCallCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <input type="checkbox" name="updateSurchargePerMinute" class="" />
                                <label class="control-label">Surcharge Per Minute</label>
                                <input type="text" name="SurchargePerMinute" class="form-control numbercheck" value="" />
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <input type="checkbox" name="updateSurchargePerMinuteCurrency" class="" />
                                <label class="control-label"> Currency</label>
                                {{ Form::select('SurchargePerMinuteCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <input type="checkbox" name="updateOutpaymentPerCall" class="" />
                                <label class="control-label">Outpayment Per Call</label>
                                <input type="text" name="OutpaymentPerCall" class="form-control numbercheck" value="" />
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <input type="checkbox" name="updateOutpaymentPerCallCurrency" class="" />
                                <label class="control-label"> Currency</label>
                                {{ Form::select('OutpaymentPerCallCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <input type="checkbox" name="updateOutpaymentPerMinute" class="" />
                                <label class="control-label">Outpayment Per Minute</label>
                                <input type="text" name="OutpaymentPerMinute" class="form-control numbercheck" value="" />
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <input type="checkbox" name="updateOutpaymentPerMinuteCurrency" class="" />
                                <label class="control-label"> Currency</label>
                                {{ Form::select('OutpaymentPerMinuteCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <input type="checkbox" name="updateSurcharges" class="" />
                                <label class="control-label">Surcharges</label>
                                <input type="text" name="Surcharges" class="form-control numbercheck" value="" />
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <input type="checkbox" name="updateSurchargesCurrency" class="" />
                                <label class="control-label"> Currency</label>
                                {{ Form::select('SurchargesCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <input type="checkbox" name="updateChargeback" class="" />
                                <label class="control-label">Chargeback</label>
                                <input type="text" name="Chargeback" class="form-control numbercheck" value="" />
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <input type="checkbox" name="updateChargebackCurrency" class="" />
                                <label class="control-label"> Currency</label>
                                {{ Form::select('ChargebackCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <input type="checkbox" name="updateCollectionCostAmount" class="" />
                                <label class="control-label">Collection Cost (Amount)</label>
                                <input type="text" name="CollectionCostAmount" class="form-control numbercheck" value="" />
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <input type="checkbox" name="updateCollectionCostAmountCurrency" class="" />
                                <label class="control-label"> Currency</label>
                                {{ Form::select('CollectionCostAmountCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateCollectionCostPercentage" class="" />
                                <label class="control-label">Collection Cost (Percentage)</label>
                                <input type="text" name="CollectionCostPercentage" class="form-control numbercheck" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <input type="checkbox" name="updateRegistrationCostPerNumber" class="" />
                                <label class="control-label">Registration Cost Per Numb</label>
                                <input type="text" name="RegistrationCostPerNumber" class="form-control numbercheck" value="" />
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <input type="checkbox" name="updateRegistrationCostPerNumberCurrency" class="" />
                                <label class="control-label"> Currency</label>
                                {{ Form::select('RegistrationCostPerNumberCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateTableDIDRateID" value="">
                    <input type="hidden" name="RateID" value="">
                    <input type="hidden" name="criteria" value="">
                    <input type="hidden" name="TimezonesID" value="">

                    <button type="submit"
                            class="save btn btn-primary btn-sm btn-icon icon-left"
                            data-loading-text="Loading...">
                        <i class="entypo-floppy"></i> Save
                    </button>
                    <button type="button"
                            class="btn btn-danger btn-sm btn-icon icon-left"
                            data-dismiss="modal">
                        <i class="entypo-cancel"></i> Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-new">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="new-rate-form" method="post">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Rate</h4>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Access Type</label>
                                {{ Form::select('AccessType', array('' => 'Select') + $AccessType, '' , array("class"=>"select2 select_controls")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Origination</label>
                                {{--{{ Form::select('RateID', array(), '', array("class"=>"select2 rateid_list")) }}--}}
                                <input type="hidden" class="rateid_list" name="OriginationRateID" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Prefix</label>
                                {{--{{ Form::select('RateID', array(), '', array("class"=>"select2 rateid_list")) }}--}}
                                <input type="hidden" class="rateid_list" name="RateID" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">City</label>
                                {{ Form::select('City', array('' => 'Select') + $City, '' , array("class"=>"select2 select_controls")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Tariff</label>
                                {{ Form::select('Tariff', array('' => 'Select') + $Tariff, '' , array("class"=>"select2 select_controls")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Effective Date</label>
                                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>
                        <div class="col-md-6 clear">
                            <div class="form-group">
                                <label class="control-label">Timezone</label>
                                {{ Form::select('TimezonesID', $Timezone, '', array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">One-Off Cost</label>
                                <input type="text" name="OneOffCost" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('OneOffCostCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Monthly Cost</label>
                                <input type="text" name="MonthlyCost" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('MonthlyCostCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Cost Per Call</label>
                                <input type="text" name="CostPerCall" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('CostPerCallCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Cost Per Minute</label>
                                <input type="text" name="CostPerMinute" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('CostPerMinuteCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Surcharge Per Call</label>
                                <input type="text" name="SurchargePerCall" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('SurchargePerCallCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Surcharge Per Minute</label>
                                <input type="text" name="SurchargePerMinute" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('SurchargePerMinuteCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Outpayment Per Call</label>
                                <input type="text" name="OutpaymentPerCall" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('OutpaymentPerCallCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Outpayment Per Minute</label>
                                <input type="text" name="OutpaymentPerMinute" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('OutpaymentPerMinuteCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Surcharges</label>
                                <input type="text" name="Surcharges" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('SurchargesCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Chargeback</label>
                                <input type="text" name="Chargeback" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('ChargebackCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Collection Cost (Amount)</label>
                                <input type="text" name="CollectionCostAmount" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('CollectionCostAmountCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Collection Cost (Percentage)</label>
                                <input type="text" name="CollectionCostPercentage" class="form-control numbercheck" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group col-sm-8 component-form-control">
                                <label class="control-label">Registration Cost Per Number</label>
                                <input type="text" name="RegistrationCostPerNumber" class="form-control numbercheck" placeholder="">
                            </div>
                            <div class="form-group col-sm-4 component-form-control">
                                <label class="control-label"> &nbsp;</label>
                                {{ Form::select('RegistrationCostPerNumberCurrency', $CurrencyDropDown, $rateTable->CurrencyID, array("class"=>"select2")) }}
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
@stop
