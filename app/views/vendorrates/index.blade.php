@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('accounts')}}">Accounts</a>
    </li>
    <li>
        {{customer_dropbox($id,["IsVendor"=>1])}}
    </li>
    <li class="active">
        <strong>Vendor Rates</strong>
    </li>
</ol>
<h3>Vendor Rates</h3>
@include('accounts.errormessage')
<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
<li class="active">
    <a href="{{ URL::to('vendor_rates/'.$id) }}" >
        <span class="hidden-xs">Vendor Rate</span>
    </a>
</li>
{{--@if(User::checkCategoryPermission('VendorRates','Upload'))
<li>
    <a href="{{ URL::to('/vendor_rates/'.$id.'/upload') }}" >
        <span class="hidden-xs">Vendor Rate Upload</span>
    </a>
</li>
@endif--}}
@if(User::checkCategoryPermission('VendorRates','Download'))
<li>
    <a href="{{ URL::to('/vendor_rates/'.$id.'/download') }}" >
        <span class="hidden-xs">Vendor Rate Download</span>
    </a>
</li>
@endif
@if(User::checkCategoryPermission('VendorRates','Settings'))
<li>
    <a href="{{ URL::to('/vendor_rates/'.$id.'/settings') }}" >
        <span class="hidden-xs">Settings</span>
    </a>
</li>
@endif
@if(User::checkCategoryPermission('VendorRates','Blocking'))
<li >
    <a href="{{ URL::to('vendor_blocking/'.$id) }}" >
        <span class="hidden-xs">Blocking</span>
    </a>
</li>
@endif
@if(User::checkCategoryPermission('VendorRates','Preference'))
<li >
    <a href="{{ URL::to('/vendor_rates/vendor_preference/'.$id) }}" >
        <span class="hidden-xs">Preference</span>
    </a>
</li>
@endif
@if(User::checkCategoryPermission('VendorRates','History'))
<li>
    <a href="{{ URL::to('/vendor_rates/'.$id.'/history') }}" >
        <span class="hidden-xs">Vendor Rate History</span>
    </a>
</li>
@endif
@include('vendorrates.upload_rates_button')
</ul>
<div class="row">
<div class="col-md-12">
       <form role="form" id="vendor-rate-search" method="get" action="javascript:void(0);" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Search
                </div>

                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>

            <div class="panel-body">
                <div class="form-group">
                    <label for="field-1" class="col-sm-1 control-label">Code</label>
                    <div class="col-sm-3">
                        <input type="text" name="Code" class="form-control" id="field-1" placeholder="" value="{{Input::get('Code')}}" />
                    </div>

                    <label class="col-sm-1 control-label">Description</label>
                    <div class="col-sm-3">
                        <input type="text" name="Description" class="form-control" id="field-1" placeholder="" value="{{Input::get('Description')}}" />

                    </div>
                    <label for="field-1" class="col-sm-1 control-label EffectiveBox">Effective</label>
                    <div class="col-sm-3 EffectiveBox">
                        <select name="Effective" class="select2" data-allow-clear="true" data-placeholder="Select Effective">
                            <option value="Now">Now</option>
                            <option value="Future">Future</option>
                            <option value="All">All</option>
                        </select>
                    </div>

                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-1 control-label">Country</label>
                    <div class="col-sm-3">
                        {{ Form::select('Country', $countries, Input::get('Country') , array("class"=>"select2")) }}
                    </div>

                    <label for="field-1" class="col-sm-1 control-label">Trunk</label>
                    <div class="col-sm-3">
                        {{ Form::select('Trunk', $trunks, $trunk_keys, array("class"=>"select2")) }}
                    </div>

                    <label for="field-1" class="col-sm-1 control-label">Discontinued Codes</label>
                    <div class="col-sm-3">
                        <p class="make-switch switch-small">
                            {{Form::checkbox('DiscontinuedRates', '1', false, array("id"=>"DiscontinuedRates"))}}
                        </p>
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-sm-1 control-label">Timezone</label>
                    <div class="col-sm-3">
                        {{ Form::select('Timezones', $Timezones, '', array("class"=>"select2")) }}
                    </div>
                </div>

                <p style="text-align: right;">
                    <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </p>
            </div>
        </div>
    </form>
</div>
</div>
<div style="text-align: right;padding:10px 0 ">
    @if(User::checkCategoryPermission('VendorRates','Edit'))
    <a class="btn btn-primary btn-sm btn-icon icon-left" id="changeSelectedVendorRates" href="javascript:;">
        <i class="entypo-floppy"></i>
        Change Selected
    </a>
    @endif
    @if(User::checkCategoryPermission('VendorRates','Delete'))
    <button class="btn btn-danger btn-sm btn-icon icon-left" id="clear-bulk-rate" type="submit">
        <i class="entypo-trash"></i>
        Delete Selected
    </button>
    @endif
    <form id="clear-bulk-rate-form" >
        <input type="hidden" name="VendorRateID" value="">
        <input type="hidden" name="TrunkID" value="">
        <input type="hidden" name="TimezonesID" value="">
        <input type="hidden" name="criteria" value="">
    </form>
</div>


<table class="table table-bordered datatable" id="table-4">
<thead>
    <tr>
        <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
        <th width="5%">Code</th>
        <th width="20%">Description</th>
        <th width="5%">Connection Fee</th>
        <th width="5%">Interval 1</th>
        <th width="5%">Interval N</th>
        <th width="5%">Rate1 ({{$CurrencySymbol}})</th>
        <th width="5%">RateN ({{$CurrencySymbol}})</th>
        <th width="8%">Effective Date</th>
        <th width="8%">End Date</th>
        <th width="8%">Modified Date</th>
        <th width="8%">Modified By</th>
        <th width="20%">Action</th>
    </tr>
</thead>
<tbody>

</tbody>
</table>
 
<script type="text/javascript">
    var $searchFilter = {};
    var checked='';
    var list_fields  = ['VendorRateID','Code','Description','ConnectionFee','Interval1','IntervalN','Rate','RateN','EffectiveDate','EndDate','updated_at','updated_by'];
    var Code, Description, Country,Trunk,Effective,update_new_url;

jQuery(document).ready(function($) {
    var ArchiveRates;
    //var data_table;

    $("#vendor-rate-search").submit(function(e) {
        return rateDataTable();
    });

               $('#table-4 tbody').on('click', 'tr', function() {
                   if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
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

    // Replace Checboxes
    $(".pagination a").click(function(ev) {
        replaceCheckboxes();
    });

    //Clear Rate Button
    $(document).off('click.clear-rate','.btn.clear-vendor-rate,#clear-bulk-rate');
    $(document).on('click.clear-rate','.btn.clear-vendor-rate,#clear-bulk-rate',function(ev) {

        var VendorRateIDs   = [];
        var TrunkID         = $searchFilter.Trunk;
        var TimezonesID     = $searchFilter.Timezones;
        var i = 0;
        $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
            VendorRateID = $(this).val();
            VendorRateIDs[i++] = VendorRateID;
        });

        $("#clear-bulk-rate-form").find("input[name='TrunkID']").val(TrunkID);
        $("#clear-bulk-rate-form").find("input[name='TimezonesID']").val(TimezonesID);

        if(VendorRateIDs.length || $(this).hasClass('clear-vendor-rate')) {
            response = confirm('Are you sure?');
            if (response) {

                if($(this).hasClass('clear-vendor-rate')) {
                    var VendorRateID = $(this).parent().find('.hiddenRowData input[name="VendorRateID"]').val();
                    $("#clear-bulk-rate-form").find("input[name='VendorRateID']").val(VendorRateID);
                    $("#clear-bulk-rate-form").find("input[name='criteria']").val('');
                }

                if($(this).attr('id') == 'clear-bulk-rate') {
                    var criteria='';
                    if($('#selectallbutton').is(':checked')){
                        criteria = JSON.stringify($searchFilter);
                        $("#clear-bulk-rate-form").find("input[name='VendorRateID']").val('');
                        $("#clear-bulk-rate-form").find("input[name='criteria']").val(criteria);
                    }else{
                        var VendorRateIDs = [];
                        var i = 0;
                        $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                            VendorRateID = $(this).val();
                            VendorRateIDs[i++] = VendorRateID;
                        });
                        $("#clear-bulk-rate-form").find("input[name='VendorRateID']").val(VendorRateIDs.join(","))
                        $("#clear-bulk-rate-form").find("input[name='criteria']").val('');
                    }
                }

                var formData = new FormData($('#clear-bulk-rate-form')[0]);

                $.ajax({
                    url: baseurl + '/vendor_rates/{{$id}}/clear_rate', //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        $(".save.btn").button('reset');

                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
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
    $(document).off('click.changeSelectedVendorRates','#changeSelectedVendorRates');
    $(document).on('click.changeSelectedVendorRates','#changeSelectedVendorRates',function(ev) {

        var VendorRateIDs   = [];
        var TrunkID         = $searchFilter.Trunk;
        var TimezonesID     = $searchFilter.Timezones;

        var i = 0;
        $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
            //console.log($(this).val());
            VendorRateID = $(this).val();
            VendorRateIDs[i] = VendorRateID;
            i++;
        });
        date = new Date();
        var month = date.getMonth()+1;
        var day = date.getDate();
        currentDate = date.getFullYear() + '-' +   (month<10 ? '0' : '') + month + '-' +     (day<10 ? '0' : '') + day;
        $("#bulk-edit-vendor-rate-form")[0].reset();
        $("#bulk-edit-vendor-rate-form").find("input[name='Interval1']").val(1);
        $("#bulk-edit-vendor-rate-form").find("input[name='IntervalN']").val(1);
        $("#bulk-edit-vendor-rate-form").find("input[name='EffectiveDate']").val(currentDate);
        $("#bulk-edit-vendor-rate-form").find("input[name='TrunkID']").val(TrunkID);
        $("#bulk-edit-vendor-rate-form").find("input[name='TimezonesID']").val(TimezonesID);

        var criteria = '';
        if ($('#selectallbutton').is(':checked')) {
            criteria = JSON.stringify($searchFilter);
            $("#bulk-edit-vendor-rate-form").find("input[name='VendorRateID']").val('');
            $("#bulk-edit-vendor-rate-form").find("input[name='criteria']").val(criteria);
        } else {
            $("#bulk-edit-vendor-rate-form").find("input[name='VendorRateID']").val(VendorRateIDs.join(","));
            $("#bulk-edit-vendor-rate-form").find("input[name='criteria']").val('');
        }

        if(VendorRateIDs.length){
            jQuery('#modal-BulkVendorRate').modal('show', {backdrop: 'static'});
        }
    });

    //Bulk Form and Edit Single Form Submit
    $("#bulk-edit-vendor-rate-form,#edit-vendor-rate-form").submit(function() {
        var formData = new FormData($(this)[0]);
        $.ajax({
            url: baseurl + '/vendor_rates/{{$id}}/update_vendor_rate', //Server script to process data
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $(".save.btn").button('reset');

                if (response.status == 'success') {
                    $('#modal-BulkVendorRate').modal('hide');
                    $('#modal-VendorRate').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                    //data_table.fnFilter('', 0);
                    rateDataTable();
                } else {
                    toastr.error(response.message, "Error", toastr_opts);
                }
            },
            error: function(error) {
                $("#modal-BulkVendorRate").modal("hide");
                $("#modal-VendorRate").modal("hide");
                toastr.error(error, "Error", toastr_opts);
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

    $("#DiscontinuedRates").on('change', function (event, state) {
        if($("#DiscontinuedRates").is(':checked')) {
            $(".EffectiveBox").hide();
        } else {
            $(".EffectiveBox").show();
        }
    });

    $(document).on('click', '.btn-history', function() {
        var $this   = $(this);
        var Codes   = $this.prevAll("div.hiddenRowData").find("input[name='Code']").val();
        getArchiveVendorRates($this,Codes);
    });

    //set RateN value = Rate1 value if RateN value is blank
    $(document).on('focusout','.Rate1', function() {
        var formid = $(this).closest("form").attr('id');
        var val = $(this).val();

        if($('#'+formid+' .RateN').val() == '') {
            $('#'+formid+' .RateN').val(val);
        }
    });
});

    function getArchiveVendorRates($clickedButton,Codes) {
        //var Codes = new Array();
        var ArchiveRates;
        /*$("#table-4 tr td:nth-child(2)").each(function(){
            Codes.push($(this).html());
        });*/

        var tr = $clickedButton.closest('tr');
        var row = data_table.row(tr);

        if (row.child.isShown()) {
            tr.find('.details-control i').toggleClass('entypo-plus-squared entypo-minus-squared');
            row.child.hide();
            tr.removeClass('shown');
        } else {
            tr.find('.details-control i').toggleClass('entypo-plus-squared entypo-minus-squared');
            $clickedButton.attr('disabled','disabled');

            $.ajax({
                url : baseurl + "/vendor_rates/{{$id}}/search_ajax_datagrid_archive_rates",
                type : 'POST',
                data : "Codes="+Codes+"&TimezonesID="+$searchFilter.Timezones+"&TrunkID="+$searchFilter.Trunk,
                dataType : 'json',
                cache: false,
                success : function(response){
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
                    table.append("<thead><tr><th>Code</th><th>Description</th><th>Connection Fee</th><th>Interval 1</th><th>Interval N</th><th>Rate1</th><th>RateN</th><th class='sorting_desc'>Effective Date</th><th>End Date</th><th>Modified Date</th><th>Modified By</th></tr></thead>");
                    var tbody = $("<tbody></tbody>");

                    /*ArchiveRates.sort(function(obj1, obj2) {
                     // Ascending: first age less than the previous
                     return new Date(obj2.EffectiveDate).getTime() - new Date(obj1.EffectiveDate).getTime();
                     });*/
                    ArchiveRates.forEach(function(data){
                        if(data['Code'] == Code) {
                            var html = "";
                            html += "<tr class='no-selection'>";
                            html += "<td>" + data['Code'] + "</td>";
                            html += "<td>" + data['Description'] + "</td>";
                            html += "<td>" + data['ConnectionFee'] + "</td>";
                            html += "<td>" + data['Interval1'] + "</td>";
                            html += "<td>" + data['IntervalN'] + "</td>";
                            html += "<td>" + data['Rate'] + "</td>";
                            html += "<td>" + data['RateN'] + "</td>";
                            html += "<td>" + data['EffectiveDate'] + "</td>";
                            html += "<td>" + data['EndDate'] + "</td>";
                            html += "<td>" + data['ModifiedDate'] + "</td>";
                            html += "<td>" + data['ModifiedBy'] + "</td>";
                            html += "</tr>";
                            table.append(html);
                        }
                    });
                    table.append(tbody);
                    row.child(table).show();
                    row.child().addClass('no-selection child-row');
                    tr.addClass('shown');
                }
            });
        }
    }

    function rateDataTable() {
        $searchFilter.Trunk = Trunk = $("#vendor-rate-search select[name='Trunk']").val();
        $searchFilter.Country = Country = $("#vendor-rate-search select[name='Country']").val();
        $searchFilter.Effective = Effective = $("#vendor-rate-search select[name='Effective']").val();
        $searchFilter.Code = Code = $("#vendor-rate-search input[name='Code']").val();
        $searchFilter.Description = Description = $("#vendor-rate-search input[name='Description']").val();
        $searchFilter.DiscontinuedRates = DiscontinuedRates = $("#vendor-rate-search input[name='DiscontinuedRates']").is(':checked') ? 1 : 0;
        $searchFilter.Timezones = Timezones = $("#vendor-rate-search select[name='Timezones']").val();

        if(Trunk == '' || typeof Trunk  == 'undefined'){
            toastr.error("Please Select a Trunk", "Error", toastr_opts);
            return false;
        }
        data_table = $("#table-4").DataTable({
            "bDestroy": true, // Destroy when resubmit form
            "bAutoWidth": false,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/vendor_rates/{{$id}}/search_ajax_datagrid",
            "fnServerParams": function(aoData) {
                aoData.push({"name": "Effective", "value": Effective}, {"name": "Trunk", "value": Trunk}, {"name": "Country", "value": Country}, {"name": "Code", "value": Code}, {"name": "Description", "value": Description}, {"name": "DiscontinuedRates", "value": DiscontinuedRates}, {"name": "Timezones", "value": Timezones});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name": "Effective", "value": Effective}, {"name": "Trunk", "value": Trunk}, {"name": "Country", "value": Country},  {"name": "Code", "value": Code}, {"name": "Description", "value": Description}, {"name": "DiscontinuedRates", "value": DiscontinuedRates}, {"name": "Timezones", "value": Timezones});
            },
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, "asc"], [1, "asc"]],
            "aoColumns":
                    [
                        {"bSortable": false, //RateID
                            mRender: function(id, type, full) {
                                return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                            }
                        },
                        {}, //1 Code
                        {}, //2 Description
                        {}, //3 ConnectionFee
                        {}, //4 Interval1
                        {}, //5 IntervalN
                        {}, //6 Rate
                        {}, //7 RateN
                        {}, //8 EffectiveDate
                        {}, //9 EndDate
                        {}, //10 updated at
                        {}, //11 updated by
                        {// 12 Action
                            mRender: function(id, type, full) {

                                var action, edit_, delete_,VendorRateID;
                                edit_ = "{{ URL::to('/vendor_rates/{id}/edit')}}";
                                VendorRateID = full[0];
                                clerRate_ = "{{ URL::to('/vendor_rates/bulk_clear_rate/'.$id)}}?VendorRateID=" + VendorRateID + "&Trunk=" + Trunk;

                                edit_ = edit_.replace('{id}', full[0]);

                                action = '<div class = "hiddenRowData" >';
                                for(var i = 0 ; i< list_fields.length; i++){
                                    action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                }
                                action += '</div>';
                                <?php if(User::checkCategoryPermission('VendorRates','Edit')) { ?>
                                if(DiscontinuedRates == 0) {
                                    action += ' <a href="Javascript:;" title="Edit" class="edit-vendor-rate btn btn-default btn-xs"><i class="entypo-pencil"></i>&nbsp;</a>';
                                }
                                <?php } ?>

                                        action += ' <a href="Javascript:;" title="History" class="btn btn-default btn-xs btn-history details-control"><i class="entypo-back-in-time"></i>&nbsp;</a>';

                                if (full[0] > 0) {
                                    <?php if(User::checkCategoryPermission('VendorRates','Delete')) { ?>
                                    if(DiscontinuedRates == 0) {
                                        action += ' <button href="' + clerRate_ + '" title="Delete"  class="btn clear-vendor-rate btn-danger btn-xs" data-loading-text="Loading..."><i class="entypo-trash"></i></button>';
                                    }
                                    <?php } ?>
                                }
                                return action;
                            }
                        }, // 11 Action
                    ],
            "oTableTools":
            {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/vendor_rates/{{$id}}/exports/xlsx",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/vendor_rates/{{$id}}/exports/csv",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
            "fnDrawCallback": function() {
                //getArchiveVendorRates(); //rate history for plus button
                $("#clear-bulk-rate-form").find("input[name='TrunkID']").val($searchFilter.Trunk);

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
                $(".edit-vendor-rate.btn").off('click');
                $(".edit-vendor-rate.btn").click(function(ev) {
                    ev.stopPropagation();
                    var TrunkID = $searchFilter.Trunk;
                    var TimezonesID = $searchFilter.Timezones;
                    var cur_obj = $(this).prev("div.hiddenRowData");
                    for(var i = 0 ; i< list_fields.length; i++){
                        $("#edit-vendor-rate-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    }
                    $("#edit-vendor-rate-form").find("input[name='TrunkID']").val(TrunkID);
                    $("#edit-vendor-rate-form").find("input[name='TimezonesID']").val(TimezonesID);
                    jQuery('#modal-VendorRate').modal('show', {backdrop: 'static'});
                });

                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });

                $('#table-4 tbody tr').each(function(i, el) {
                    if (checked!='') {
                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                        $(this).addClass('selected');
                        $('#selectallbutton').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);;
                        $(this).removeClass('selected');
                    }
                });

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

                if(Effective == 'All' || DiscontinuedRates == 1) {
                    $('#changeSelectedVendorRates').hide();
                } else {
                    $('#changeSelectedVendorRates').show();
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
</script>
<style>
.dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}
#selectcheckbox{
    padding: 15px 10px;
}
#table-4 tbody tr td.details-control{
    width: 8%;
}
</style>
@stop

@section('footer_ext')
@parent
<!-- single edit -->
<div class="modal fade" id="modal-VendorRate">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="edit-vendor-rate-form" method="post" >

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Vendor Rate</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Effective Date</label>
                                <input type="text"  name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">End Date</label>
                                <input type="text"  name="EndDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Rate1</label>
                                <input type="text" name="Rate" class="form-control Rate1" id="field-5" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">RateN</label>
                                <input type="text" name="RateN" class="form-control RateN" id="field-5" placeholder="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Interval 1</label>
                                <input type="text" name="Interval1" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Interval N</label>
                                <input type="text" name="IntervalN" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="VendorRateID" value="">
                    <input type="hidden" name="TrunkID" value="">
                    <input type="hidden" name="TimezonesID" value="">
                    <input type="hidden" name="criteria" value="">
                    <input type="hidden" name="updateEffectiveDate" value="on">
                    <input type="hidden" name="updateRate" value="on">
                    <input type="hidden" name="updateRateN" value="on">
                    <input type="hidden" name="updateInterval1" value="on">
                    <input type="hidden" name="updateIntervalN" value="on">
                    <input type="hidden" name="updateConnectionFee" value="on">
                    <input type="hidden" name="updateEndDate" value="on">
                    <input type="hidden" name="updateType" value="singleEdit">

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
<div class="modal fade" id="modal-BulkVendorRate">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="bulk-edit-vendor-rate-form" method="post" action="{{URL::to('vendor_rates/bulk_update/'.$id)}}">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Bulk Edit Vendor Rates</h4>
                </div>

                <div class="modal-body">
                    <div id="bulk-update-params-show">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateEffectiveDate" class="" />
                                <label for="field-4" class="control-label">Effective Date</label>
                                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}"  data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateEndDate" class="" />
                                <label for="field-4" class="control-label">End Date</label>
                                <input type="text" name="EndDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}"  data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>

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

                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateInterval1" class="" />
                                <label for="field-4" class="control-label">Interval 1</label>
                                <input type="text" name="Interval1" class="form-control" value="" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateIntervalN" class="" />
                                <label for="field-5" class="control-label">Interval N</label>
                                <input type="text" name="IntervalN" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateConnectionFee" class="" />
                                <label for="field-5" class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="VendorRateID" value="">
                    <input type="hidden" name="TrunkID" value="">
                    <input type="hidden" name="TimezonesID" value="">
                    <input type="hidden" name="criteria" value="">

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