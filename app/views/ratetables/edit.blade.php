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
                <div class="form-group">
                    <label class="control-label">Origination Code</label>
                    <input type="text" name="OriginationCode" class="form-control" placeholder="" />
                </div>
                <div class="form-group">
                    <label class="control-label">Origination Description</label>
                    <input type="text" name="OriginationDescription" class="form-control" placeholder="" />
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Destination Code</label>
                    <input type="text" name="Code" class="form-control" id="field-1" placeholder="" />
                </div>
                <div class="form-group">
                    <label class="control-label">Destination Description</label>
                    <input type="text" name="Description" class="form-control" id="field-1" placeholder="" />
                    <input type="hidden" name="TrunkID" value="{{$trunkID}}" >
                </div>
                <div class="form-group">
                    <label class="control-label">Timezone</label>
                    {{ Form::select('Timezones', $Timezones, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Country</label>
                    {{ Form::select('Country', $countries, Input::old('Country') , array("class"=>"select2")) }}
                </div>

                <div class="form-group">
                    <label for="field-1" class="control-label">Discontinued Codes</label>
                    <p class="make-switch switch-small">
                        {{Form::checkbox('DiscontinuedRates', '1', false, array("id"=>"DiscontinuedRates"))}}
                    </p>
                </div>

                <div class="form-group EffectiveBox">
                    <label for="field-1" class="control-label">Effective</label>
                    <select name="Effective" class="select2" data-allow-clear="true" data-placeholder="Select Effective">
                        <option value="Now">Now</option>
                        <option value="Future">Future</option>
                        <option value="All">All</option>
                    </select>
                </div>

                @if($rateTable->Type == $TypeVoiceCall && $rateTable->AppliedTo == RateTable::APPLIED_TO_VENDOR)
                    <div class="form-group">
                        <label class="control-label">Routing Category</label>
                        {{ Form::select('RoutingCategoryID', $RoutingCategories, '', array("class"=>"select2")) }}
                    </div>
                    <div class="form-group">
                        <label class="control-label">Preference</label>
                        <input type="text" name="Preference" class="form-control" placeholder="">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Blocked</label>
                        <select name="Blocked" class="select2" data-allow-clear="true" data-placeholder="Select Status">
                            <option value="" selected="selected">All</option>
                            <option value="1">Blocked</option>
                            <option value="0">Unblocked</option>
                        </select>
                    </div>
                @endif

                @if($RateApprovalProcess == 1)
                <div class="form-group">
                    <label class="control-label">Status</label>
                    <select name="ApprovedStatus" class="select2" data-allow-clear="true" data-placeholder="Select Status">
                        <option value="" selected="selected">All</option>
                        <option value="1">Approved</option>
                        <option value="0">Awaiting Approval</option>
                    </select>
                </div>
                @endif

                <div class="form-group">
                    <label for="field-1" class="control-label">Group By</label>
                    <select class="select2" name="GroupBy" id="GroupBy">
                        <option value="GroupByCode">Code</option>
                        <option value="GroupByDesc">Description</option>
                    </select>
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
    <li><a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a></li>
    <li><a href="{{URL::to('/rate_tables')}}">Rate Table</a></li>
    <li>
        <a><span>{{rate_tables_dropbox($id)}}</span></a>
    </li>
    <li class="active"><strong>{{$rateTable->RateTableName}}</strong></li>
</ol>
<h3>View Rate Table</h3>

<div class="row" style="margin-bottom: 10px;">
    <div  class="col-md-12">
        <div class="input-group-btn pull-right hidden dropdown" style="width:70px;">
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
            <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                @if($isBandTable)
                    @if(User::checkCategoryPermission('RateTables','Add') )
                        <li><a href="javascript:void(0)" id="add-new-rate"><i class="entypo-plus"></i><span>Add New</span></a></li>
                    @endif
                @endif
                @if(User::checkCategoryPermission('RateTables','Edit') )
                    <li><a href="javascript:void(0)" id="change-bulk-rate"><i class="entypo-pencil"></i><span>Change Selected</span></a></li>
                @endif
                @if(User::checkCategoryPermission('RateTables','Delete') )
                    <li><a href="javascript:void(0)" id="clear-bulk-rate"><i class="entypo-trash"></i><span>Delete Selected</span></a></li>
                @endif
                @if(User::checkCategoryPermission('RateTables','ApprovalProcess') )
                    @if($RateApprovalProcess == 1)
                        <li><a href="javascript:void(0)" id="approve-bulk-rate"><i class="entypo-check"></i><span>Approve Selected</span></a></li>
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
</div>
<form id="clear-bulk-rate-form" >
    <input type="hidden" name="RateTableRateID" />
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
            <th width="4%" id="OCode-Header">Orig. Code</th>
            <th width="10%">Orig. Description</th>
            <th width="4%" id="Code-Header">Dest. Code</th>
            <th width="10%">Dest. Description</th>
            <th width="3%">Interval 1/N</th>
            <th width="3%" style="display: none;">Interval N</th>
            <th width="5%">Connection Fee</th>
            <th width="5%">Previous Rate ({{$code}})</th>
            <th width="5%">Rate1 ({{$code}})</th>
            <th width="5%">RateN ({{$code}})</th>
            <th width="8%">Effective Date</th>
            <th width="8%">Modified By/Date</th>
            @if($RateApprovalProcess == 1)
            <th width="8%">Approved By/Date</th>
            @endif
            @if($rateTable->Type == $TypeVoiceCall && $rateTable->AppliedTo == RateTable::APPLIED_TO_VENDOR)
            <th width="5%">Routing Category</th>
            <th width="4%">Pref.</th>
            @endif
            <th width="10%" id="actionheader"> Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>





    <script type="text/javascript">
        var $searchFilter = {};
        var checked='';
        var codedeckid = '{{$id}}';
        var list_fields  = ['ID','OriginationCode','OriginationDescription','Code','Description','Interval1','IntervalN','ConnectionFee','PreviousRate','Rate','RateN','EffectiveDate','EndDate','updated_at','ModifiedBy','RateTableRateID','OriginationRateID','RateID','RoutingCategoryID','RoutingCategoryName','Preference','Blocked','ApprovedStatus','ApprovedBy','ApprovedDate'];
        jQuery(document).ready(function($) {

        $('#filter-button-toggle').show();

        var view = 1;
        var ratetableview = getCookie('ratetableview');
        if(ratetableview=='GroupByDesc'){
            view = 2;
            $('#rate-table-search #GroupBy').val('GroupByDesc');
        } else {
            view = 1;
            $('#rate-table-search #GroupBy').val('GroupByCode');
        }
        var ratetablepageview = getCookie('ratetablepageview');
        if(ratetablepageview=='AdvanceView'){
            $('#btn-basic-view').removeClass('active');
            $('#btn-advance-view').addClass('active');
        } else {
            $('#btn-advance-view').removeClass('active');
            $('#btn-basic-view').addClass('active');
        }

        $("#rate-table-search").submit(function(e) {
            /*if(view == 2)
                return rateDataTable2(view);
            else
                return rateDataTable(view);*/
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

            var RateTableRateIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                RateTableRateID = $(this).val();
                RateTableRateIDs[i++] = RateTableRateID;
            });

            if(RateTableRateIDs.length || $(this).hasClass('clear-rate-table')) {
                response = confirm('Are you sure?');
                if (response) {
                    var TimezonesID     = $searchFilter.Timezones;
                    $("#clear-bulk-rate-form").find("input[name='TimezonesID']").val(TimezonesID);

                    if($(this).hasClass('clear-rate-table')) {
                        var RateTableRateID = $(this).parent().find('.hiddenRowData input[name="RateTableRateID"]').val();
                        $("#clear-bulk-rate-form").find("input[name='RateTableRateID']").val(RateTableRateID);
                        $("#clear-bulk-rate-form").find("input[name='criteria']").val('');
                    }

                    if($(this).attr('id') == 'clear-bulk-rate') {
                        var criteria='';
                        if($('#selectallbutton').is(':checked')){
                            criteria = JSON.stringify($searchFilter);
                            $("#clear-bulk-rate-form").find("input[name='RateTableRateID']").val('');
                            $("#clear-bulk-rate-form").find("input[name='criteria']").val(criteria);
                        }else{
                            var RateTableRateIDs = [];
                            var i = 0;
                            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                                RateTableRateID = $(this).val();
                                RateTableRateIDs[i++] = RateTableRateID;
                            });
                            $("#clear-bulk-rate-form").find("input[name='RateTableRateID']").val(RateTableRateIDs.join(","))
                            $("#clear-bulk-rate-form").find("input[name='criteria']").val('');
                        }
                    }

                    var formData = new FormData($('#clear-bulk-rate-form')[0]);

                    $.ajax({
                        url: baseurl + '/rate_tables/{{$id}}/clear_rate', //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
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

            var RateTableRateIDs = [];
            var RateIDs = [];
            var TimezonesID = $searchFilter.Timezones;

            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                //console.log($(this).val());
                RateTableRateID = $(this).val();
                RateTableRateIDs[i] = RateTableRateID;
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
                $("#bulk-edit-rate-table-form").find("input[name='RateTableRateID']").val('');
                $("#bulk-edit-rate-table-form").find("input[name='criteria']").val(criteria);
            } else {
                $("#bulk-edit-rate-table-form").find("input[name='RateTableRateID']").val(RateTableRateIDs.join(","));
                $("#bulk-edit-rate-table-form").find("input[name='criteria']").val('');
            }

            if(RateIDs.length){
                jQuery('#modal-bulk-rate-table').modal('show', {backdrop: 'static'});
            }

        });

        //Bulk Form and Edit Single Form Submit
        $("#bulk-edit-rate-table-form,#edit-rate-table-form").submit(function() {
            var formData = new FormData($(this)[0]);
            $.ajax({
                url: baseurl + '/rate_tables/{{$id}}/update_rate_table_rate', //Server script to process data
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
        $(document).off('click.approve-bulk-rate','#approve-bulk-rate');
        $(document).on('click.approve-bulk-rate','#approve-bulk-rate',function(ev) {
            var $this = $(this);
            if(!$this.hasClass('processing')) {
                var RateTableRateIDs = [];
                var TimezonesID = $searchFilter.Timezones;
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    RateTableRateID = $(this).val();
                    RateTableRateIDs[i] = RateTableRateID;
                    i++;
                });

                var formdata = new FormData();
                formdata.append('TimezonesID', TimezonesID);
                var criteria = '';
                if ($('#selectallbutton').is(':checked')) {
                    criteria = JSON.stringify($searchFilter);
                    formdata.append('RateTableRateID', '');
                    formdata.append('criteria', criteria);
                } else {
                    formdata.append('RateTableRateID', RateTableRateIDs.join(","));
                    formdata.append('criteria', '');
                }

                if (RateTableRateIDs.length) {
                    $this.text('Processing...').addClass('processing');
                    $.ajax({
                        url: baseurl + '/rate_tables/{{$id}}/approve_rate_table_rate', //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            $this.html('<i class="entypo-check"></i><span>Approve Selected</span>').removeClass('processing');
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
            $("#new-rate-form .rateid_list").select2("val","");
            $("#new-rate-form select[name=RoutingCategoryID]").select2("val", "");
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
            placeholder: 'Enter a Code',
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
                table.append("<thead><tr><th style='width:10%'>Origination Code</th><th>Code</th></tr></thead>");
                var tbody = $("<tbody></tbody>");
                for (var i = 0; i < Code.length; i++) {
                    table.append("<tr class='no-selection'><td>" + OriginationCode[i] + "</td><td>" + Code[i] + "</td></tr>");
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
            getArchiveRateTableRates($this,RateID,OriginationRateID);
        });

        //set RateN value = Rate1 value if RateN value is blank
        $(document).on('focusout','.Rate1', function() {
            var formid = $(this).closest("form").attr('id');
            var val = $(this).val();

            if($('#'+formid+' .RateN').val() == '') {
                $('#'+formid+' .RateN').val(val);
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
    });

    function rateDataTable() {
        var GroupBy = $('#rate-table-search #GroupBy').val();
        if(GroupBy == 'GroupByDesc'){
            setCookie('ratetableview','GroupByDesc','30');
            view = 2;
        }else{
            setCookie('ratetableview','GroupByCode','30');
            view = 1;
        }
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
        $searchFilter.Country = $("#rate-table-search select[name='Country']").val();
        $searchFilter.TrunkID = $("#rate-table-search [name='TrunkID']").val();
        $searchFilter.Effective = Effective = $("#rate-table-search [name='Effective']").val();
        $searchFilter.DiscontinuedRates = DiscontinuedRates = $("#rate-table-search input[name='DiscontinuedRates']").is(':checked') ? 1 : 0;
        $searchFilter.Timezones = Timezones = $("#rate-table-search select[name='Timezones']").val();
        $searchFilter.RoutingCategoryID = RoutingCategoryID = $("#rate-table-search select[name='RoutingCategoryID']").val();

        @if($rateTable->Type == $TypeVoiceCall && $rateTable->AppliedTo == RateTable::APPLIED_TO_VENDOR)
        $searchFilter.Preference = Preference = $("#rate-table-search input[name='Preference']").val();
        $searchFilter.Blocked = Blocked = $("#rate-table-search select[name='Blocked']").val();
        @else
        $searchFilter.Preference = Preference = null;
        $searchFilter.Blocked = Blocked = null;
        @endif
        $searchFilter.ApprovedStatus = ApprovedStatus = $("#rate-table-search select[name='ApprovedStatus']").val();
        $searchFilter.ratetablepageview = ratetablepageview;

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
                aoData.push({"name": "OriginationCode", "value": $searchFilter.OriginationCode}, {"name": "OriginationDescription", "value": $searchFilter.OriginationDescription}, {"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country},{"name": "TrunkID", "value": $searchFilter.TrunkID},{"name": "Effective", "value": $searchFilter.Effective}, {"name": "DiscontinuedRates", "value": DiscontinuedRates},{"name": "view", "value": view},{"name": "Timezones", "value": Timezones},{"name": "RoutingCategoryID", "value": RoutingCategoryID},{"name": "Preference", "value": Preference},{"name": "Blocked", "value": Blocked},{"name": "ApprovedStatus", "value": ApprovedStatus},{"name": "ratetablepageview", "value": ratetablepageview});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name": "OriginationCode", "value": $searchFilter.OriginationCode}, {"name": "OriginationDescription", "value": $searchFilter.OriginationDescription}, {"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country},{"name": "TrunkID", "value": $searchFilter.TrunkID},{"name": "Effective", "value": $searchFilter.Effective}, {"name": "DiscontinuedRates", "value": DiscontinuedRates},{"name": "view", "value": view},{"name": "Timezones", "value": Timezones},{"name": "RoutingCategoryID", "value": RoutingCategoryID},{"name": "Preference", "value": Preference},{"name": "Blocked", "value": Blocked},{"name": "ApprovedStatus", "value": ApprovedStatus},{"name": "ratetablepageview", "value": ratetablepageview});
            },
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            //  "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[view==2 ? 4 : 3, "asc"]],
            "aoColumns":
                    [
                        {"bSortable": false,
                            mRender: function(id, type, full) {
                                return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                            }
                        }, //0Checkbox
                        {
                            mRender: function(id, type, full) {
                                if(view==1) {
                                    return full[1];
                                }else
                                    return '<div class="details-control" style="text-align: center; cursor: pointer;"><i class="entypo-plus-squared" style="font-size: 20px;"></i></div>';
                            },
                            "className":      'details-control',
                            "orderable":      false,
                            "data": null,
                            "defaultContent": ''
                        }, //1 Origination Code
                        {}, //2 Origination description
                        {
                            "bVisible" : view == 1 ? true : false,
                            mRender: function(id, type, full) {
                                return view == 1 ? full[3] : '';
                            }
                        }, //3 Destination Code
                        {}, //4 Destination description
                        {
                            mRender: function(id, type, full) {
                                return full[5] + '/' + full[6]; // interval1/intervalN
                            }
                        }, //5 interval 1
                        {
                            "bVisible" : false
                        }, //6 interval n
                        {}, //7 ConnectionFee
                        {
                            "bVisible" : bVisible
                        }, //8 PreviousRate
                        {
                            mRender: function(id, type, full) {
                                if(full[9] > full[8])
                                    return full[9]+'<span style="color: green;" data-toggle="tooltip" data-title="Rate Increase" data-placement="top">&#9650;</span>';
                                else if(full[9] < full[8])
                                    return full[9]+'<span style="color: red;" data-toggle="tooltip" data-title="Rate Decrease" data-placement="top">&#9660;</span>';
                                else
                                    return full[9]
                            }
                        }, //9 Rate
                        {}, //10 RateN
                        {}, //11 Effective Date
                        {
                            "bVisible" : bVisible,
                            mRender: function(id, type, full) {
                                full[13] = full[13] != null ? full[13] : '';
                                full[14] = full[14] != null ? full[14] : '';
                                if(full[13] != '' && full[14] != '')
                                    return full[14] + '<br/>' + full[13]; // modified by/modified date
                                else
                                    return '';
                            }
                        }, //14/13 ModifiedDate
                        @if($RateApprovalProcess == 1)
                        {
                            "bVisible" : bVisible,
                            mRender: function(id, type, full) {
                                full[23] = full[23] != null ? full[23] : '';
                                full[24] = full[24] != null ? full[24] : '';
                                if(full[23] != '' && full[24] != '')
                                    return full[23] + '<br/>' + full[24]; // modified by/modified date
                                else
                                    return '';
                            }
                        }, //23/24 Approved By/Approved Date
                        @endif
                        @if($rateTable->Type == $TypeVoiceCall && $rateTable->AppliedTo == RateTable::APPLIED_TO_VENDOR)
                        {
                            mRender: function(id, type, full) {
                                return full[19]
                            }
                        }, //19 RoutingCategoryName
                        {
                            mRender: function(id, type, full) {
                                return full[20]
                            }
                        }, //20 Preference
                        @endif
                        {
                            //"bVisible" : bVisible,
                            mRender: function(id, type, full) {
                                var action, edit_, delete_;
                                action = '<div class = "hiddenRowData" >';
                                for (var i = 0; i < list_fields.length; i++) {
                                    action += '<input type = "hidden"  name = "' + list_fields[i] + '" value = "' + (full[i] != null ? full[i] : '') + '" / >';
                                }
                                action += '</div>';

                                if(bVisible == true) {
                                    $('#actionheader').attr('width','10%');
                                    clerRate_ = "{{ URL::to('/rate_tables/{id}/clear_rate')}}";
                                    clerRate_ = clerRate_.replace('{id}', full[15]);

                                    @if($RateApprovalProcess == 1)
                                    if (full[22] == 1) {
                                        action += ' <button href="Javascript:;"  title="Approved" class="btn btn-default btn-xs"><i class="entypo-check" style="color: green; "></i>&nbsp;</button>';
                                    } else if (full[22] == 0) {
                                        action += ' <button href="Javascript:;"  title="Awaiting Approval" class="btn btn-default btn-xs"><i class="entypo-cancel" style="color: red; "></i>&nbsp;</button>';
                                    }
                                    @endif

                                    if (full[21] == 0) {
                                        action += ' <button href="Javascript:;"  title="Unblocked" class="btn btn-default btn-xs"><i class="entypo-lock-open" style="color: green; "></i>&nbsp;</button>';
                                    } else if (full[21] == 1) {
                                        action += ' <button href="Javascript:;"  title="Blocked" class="btn btn-default btn-xs"><i class="entypo-lock" style="color: red; "></i>&nbsp;</button>';
                                    }

                                    <?php if(User::checkCategoryPermission('RateTables', 'Edit')) { ?>
                                    if (DiscontinuedRates == 0) {
                                        action += ' <button href="Javascript:;"  title="Edit" class="edit-rate-table btn btn-default btn-xs"><i class="entypo-pencil"></i>&nbsp;</button>';
                                    }
                                    <?php } ?>

                                    action += ' <button href="Javascript:;" title="History" class="btn btn-default btn-xs btn-history details-control"><i class="entypo-back-in-time"></i>&nbsp;</button>';

                                    if (full[15] != null && full[15] != 0) {
                                        <?php if(User::checkCategoryPermission('RateTables', 'Delete')) { ?>
                                        if (DiscontinuedRates == 0) {
                                            action += ' <button title="Delete" href="' + clerRate_ + '"  class="btn clear-rate-table btn-danger btn-xs" data-loading-text="Loading..."><i class="entypo-trash"></i></button>';
                                        }
                                        <?php } ?>
                                    }
                                } else {
                                    $('#actionheader').attr('width','5%');

                                    @if($RateApprovalProcess == 1)
                                    if (full[22] == 1) {
                                        action += ' <button href="Javascript:;"  title="Approved" class="btn btn-default btn-xs"><i class="entypo-check" style="color: green; "></i>&nbsp;</button>';
                                    } else if (full[22] == 0) {
                                        action += ' <button href="Javascript:;"  title="Awaiting Approval" class="btn btn-default btn-xs"><i class="entypo-cancel" style="color: red; "></i>&nbsp;</button>';
                                    }
                                    @endif

                                    if (full[21] == 0) {
                                        action += ' <buttona href="Javascript:;"  title="Unblocked" class="btn btn-default btn-xs"><i class="entypo-lock-open" style="color: green; "></i>&nbsp;</buttona>';
                                    } else if (full[21] == 1) {
                                        action += ' <button href="Javascript:;"  title="Blocked" class="btn btn-default btn-xs"><i class="entypo-lock" style="color: red; "></i>&nbsp;</button>';
                                    }
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
                if(view==1){
                    $('#OCode-Header').html('Orig. Code');
                    $('#Code-Header').html('Dest. Code');
                }else{
                    $('#OCode-Header').html('');
                    $('#Code-Header').html('');
                }

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

                });
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
                    var cur_obj = $(this).prevAll("div.hiddenRowData");
                    for(var i = 0 ; i< list_fields.length; i++){
                        $("#edit-rate-table-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    }

                    var OriginationRateID = cur_obj.find("input[name=OriginationRateID]").val();
                    if(OriginationRateID == null || OriginationRateID == '') {
                        $('#box-edit-OriginationRateID').show();
                    } else {
                        $('#box-edit-OriginationRateID').hide();
                    }
                    var TimezonesID = $searchFilter.Timezones;
                    $("#edit-rate-table-form").find("input[name='TimezonesID']").val(TimezonesID);
                    $("#edit-rate-table-form").find("select[name='RoutingCategoryID']").select2("val",cur_obj.find("input[name='RoutingCategoryID']").val());

                    if(cur_obj.find("input[name='Blocked']").val() == 1) {
                        $("#edit-rate-table-form").find("input[name='Blocked']").attr("checked","checked");
                        $("#edit-rate-table-form").find("input[name='Blocked']").parent("div.switch-animate").removeClass('switch-off');
                        $("#edit-rate-table-form").find("input[name='Blocked']").parent("div.switch-animate").addClass('switch-on');
                    } else {
                        $("#edit-rate-table-form").find("input[name='Blocked']").removeAttr("checked");
                        $("#edit-rate-table-form").find("input[name='Blocked']").parent("div.switch-animate").removeClass('switch-on');
                        $("#edit-rate-table-form").find("input[name='Blocked']").parent("div.switch-animate").addClass('switch-off');
                    }

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

                if(Effective == 'All' || DiscontinuedRates == 1) {//if(Effective == 'All' || DiscontinuedRates == 1) {
                    $('#change-bulk-rate').hide();
                } else {
                    $('#change-bulk-rate').show();
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

    function getArchiveRateTableRates($clickedButton,RateID,OriginationRateID) {
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

            var view = 1;
            var ratetableview = getCookie('ratetableview');
            if(ratetableview=='GroupByDesc'){
                view = 2;
            } else {
                view = 1;
            }

            $.ajax({
                url: baseurl + "/rate_tables/{{$id}}/search_ajax_datagrid_archive_rates",
                type: 'POST',
                data: "RateID=" + RateID + "&OriginationRateID=" + OriginationRateID + "&view=" + view + "&TimezonesID=" + $searchFilter.Timezones,
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
                    var header = "<thead><tr><th>Orig. Code</th><th>Orig. Description</th>";
                    if(view == 1) {
                        header += "<th>Dest. Code</th>";
                    }
                    header += "<th>Dest. Description</th><th>Interval 1</th><th>Interval N</th><th>Connection Fee</th><th>Rate1</th><th>RateN</th><th class='sorting_desc'>Effective Date</th><th>End Date</th><th>Modified Date</th><th>Modified By</th>";
                    @if($rateTable->Type == $TypeVoiceCall && $rateTable->AppliedTo == RateTable::APPLIED_TO_VENDOR)
                        header += "<th>Routing Category</th>";
                        header += "<th>Preference</th>";
                        header += "<th>Blocked</th>";
                    @endif
                    header += "</tr></thead>";
                    table.append(header);
                    var tbody = $("<tbody></tbody>");

                    ArchiveRates.forEach(function (data) {
                        //if (data['Code'] == Code) {
                            data['OriginationCode'] = data['OriginationCode'] != null ? data['OriginationCode'] : '';
                            data['OriginationDescription'] = data['OriginationDescription'] != null ? data['OriginationDescription'] : '';
                            var html = "";
                            html += "<tr class='no-selection'>";
                            html += "<td>" + data['OriginationCode'] + "</td>";
                            html += "<td>" + data['OriginationDescription'] + "</td>";
                            if(view == 1) {
                                html += "<td>" + data['Code'] + "</td>";
                            }
                            html += "<td>" + data['Description'] + "</td>";
                            html += "<td>" + data['Interval1'] + "</td>";
                            html += "<td>" + data['IntervalN'] + "</td>";
                            html += "<td>" + data['ConnectionFee'] + "</td>";
                            html += "<td>" + data['Rate'] + "</td>";
                            html += "<td>" + data['RateN'] + "</td>";
                            html += "<td>" + data['EffectiveDate'] + "</td>";
                            html += "<td>" + data['EndDate'] + "</td>";
                            html += "<td>" + data['ModifiedDate'] + "</td>";
                            html += "<td>" + data['ModifiedBy'] + "</td>";

                            @if($rateTable->Type == $TypeVoiceCall && $rateTable->AppliedTo == RateTable::APPLIED_TO_VENDOR)
                                data['Preference'] = data['Preference'] != null ? data['Preference'] : '';
                                html += "<td>" + data['RoutingCategoryName'] + "</td>";
                                html += "<td>" + data['Preference'] + "</td>";

                                if(data['Blocked'] == 0)
                                    html += '<td><i class="fa fa-unlock" style="color: green; font-size: 20px;"></i></td>';
                                else if(data['Blocked'] == 1)
                                    html += '<td><i class="fa fa-lock" style="color: red; font-size: 20px;"></i></td>';
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
                    <div class="row" id="box-edit-OriginationRateID">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Origination Code</label>
                                <input type="hidden" class="rateid_list" name="OriginationRateID" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Effective Date</label>
                                <input type="text"  name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" placeholder="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Rate1</label>
                                <input type="text" name="Rate" class="form-control Rate1" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">RateN</label>
                                <input type="text" name="RateN" class="form-control RateN" placeholder="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Interval 1</label>
                                <input type="text" name="Interval1" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Interval N</label>
                                <input type="text" name="IntervalN" class="form-control" placeholder="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @if($rateTable->Type == $TypeVoiceCall && $rateTable->AppliedTo == RateTable::APPLIED_TO_VENDOR)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Routing Category</label>
                                    {{ Form::select('RoutingCategoryID', $RoutingCategories, '', array("class"=>"select2")) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Preference</label>
                                    <input type="text" name="Preference" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Blocked</label><br/>
                                    <p class="make-switch switch-small">
                                        {{Form::checkbox('Blocked', '1', false, array())}}
                                    </p>
                                </div>
                            </div>
                        @endif

                        {{--<div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">End Date</label>
                                <input type="text"  name="EndDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>--}}
                     </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateTableRateID" value="">
                    <input type="hidden" name="RateID" value="">
                    <input type="hidden" name="criteria" value="">
                    <input type="hidden" name="updateEffectiveDate" value="on">
                    <input type="hidden" name="updateOriginationRateID" value="on">
                    <input type="hidden" name="updateRate" value="on">
                    <input type="hidden" name="updateRateN" value="on">
                    <input type="hidden" name="updateInterval1" value="on">
                    <input type="hidden" name="updateIntervalN" value="on">
                    <input type="hidden" name="updateConnectionFee" value="on">
                    <input type="hidden" name="updateEndDate" value="on">
                    <input type="hidden" name="updateType" value="singleEdit">
                    @if($rateTable->Type == $TypeVoiceCall && $rateTable->AppliedTo == RateTable::APPLIED_TO_VENDOR)
                    <input type="hidden" name="updateRoutingCategoryID" value="on">
                    <input type="hidden" name="updatePreference" value="on">
                    <input type="hidden" name="updateBlocked" value="on">
                    @endif
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
                                <input type="checkbox" name="updateOriginationRateID" class="" />
                                <label class="control-label">Code</label>
                                <input type="hidden" class="rateid_list" name="OriginationRateID" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateEffectiveDate" class="" />
                                <label class="control-label">Effective Date</label>
                                <input type="text" name="EffectiveDate" class="form-control datepicker"  data-startdate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateConnectionFee" class="" />
                                <label class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" placeholder="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateRate" class="" />
                                <label class="control-label">Rate1</label>
                                <input type="text" name="Rate" class="form-control Rate1" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateRateN" class="" />
                                <label class="control-label">RateN</label>
                                <input type="text" name="RateN" class="form-control RateN" placeholder="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateInterval1" class="" />
                                <label class="control-label">Interval 1</label>
                                <input type="text" name="Interval1" class="form-control" value="" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateIntervalN" class="" />
                                <label class="control-label">Interval N</label>
                                <input type="text" name="IntervalN" class="form-control" placeholder="">
                            </div>
                        </div>

                    </div>
                    <div class="row">

                        @if($rateTable->Type == $TypeVoiceCall && $rateTable->AppliedTo == RateTable::APPLIED_TO_VENDOR)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="checkbox" name="updateRoutingCategoryID" class="" />
                                    <label class="control-label">Routing Category</label>
                                    {{ Form::select('RoutingCategoryID', $RoutingCategories, '', array("class"=>"select2")) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="checkbox" name="updatePreference" class="" />
                                    <label class="control-label">Preference</label>
                                    <input type="text" name="Preference" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="checkbox" name="updateBlocked" class="" />
                                    <label class="control-label">Blocked</label><br/>
                                    <p class="make-switch switch-small">
                                        {{Form::checkbox('Blocked', '1', false, array())}}
                                    </p>
                                </div>
                            </div>
                        @endif

                        {{--<div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateEndDate" class="" />
                                <label class="control-label">End Date</label>
                                <input type="text" name="EndDate" class="form-control datepicker"  data-startdate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>--}}
                     </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateTableRateID" value="">
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
                                <label class="control-label">Origination Code</label>
                                {{--{{ Form::select('RateID', array(), '', array("class"=>"select2 rateid_list")) }}--}}
                                <input type="hidden" class="rateid_list" name="OriginationRateID" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label"> Destination Code</label>
                                {{--{{ Form::select('RateID', array(), '', array("class"=>"select2 rateid_list")) }}--}}
                                <input type="hidden" class="rateid_list" name="RateID" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Effective Date</label>
                                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6 clear">
                            <div class="form-group">
                                <label class="control-label">Rate1</label>
                                <input type="text" name="Rate" class="form-control Rate1" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">RateN</label>
                                <input type="text" name="RateN" class="form-control RateN" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Interval 1</label>
                                <input type="text" name="Interval1" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Interval N</label>
                                <input type="text" name="IntervalN" class="form-control" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Timezone</label>
                                {{ Form::select('TimezonesID', $Timezones, '', array("class"=>"select2")) }}
                            </div>
                        </div>

                        @if($rateTable->Type == $TypeVoiceCall && $rateTable->AppliedTo == RateTable::APPLIED_TO_VENDOR)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Routing Category</label>
                                    {{ Form::select('RoutingCategoryID', $RoutingCategories, '', array("class"=>"select2")) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Preference</label>
                                    <input type="text" name="Preference" class="form-control" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Blocked</label><br/>
                                    <p class="make-switch switch-small">
                                        {{Form::checkbox('Blocked', '1', false, array())}}
                                    </p>
                                </div>
                            </div>
                        @endif

                        {{--<div class="col-md-6">

                            <div class="form-group">
                                <label class="control-label">End Date</label>

                                <input type="text" name="EndDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>

                        </div>--}}

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
