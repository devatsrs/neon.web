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
@if(User::checkCategoryPermission('VendorRates','Upload'))
<li>
    <a href="{{ URL::to('/vendor_rates/'.$id.'/upload') }}" >
        <span class="hidden-xs">Vendor Rate Upload</span>
    </a>
</li>
@endif
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
</ul>
<div class="row">
<div class="col-md-12">
       <form role="form" id="vendor-rate-search" method="get"  action="{{URL::to('vendor_rates/'.$id.'/search')}}" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
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
                    <label for="field-1" class="col-sm-1 control-label">Effective</label>
                    <div class="col-sm-3">
                        <select name="Effective" class="select2" data-allow-clear="true" data-placeholder="Select Effective">
                            <option value="Now">Now</option>
                            <option value="Future">Future</option>
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
    <a class="btn btn-primary btn-sm btn-icon icon-left" id="bulk_set_vendor_rate" href="javascript:;">
        <i class="entypo-floppy"></i>
        Bulk update
    </a>
    <a class="btn btn-primary btn-sm btn-icon icon-left" id="changeSelectedVendorRates" href="javascript:;">
        <i class="entypo-floppy"></i>
        Change Selected
    </a>
    @endif
    @if(User::checkCategoryPermission('VendorRates','Delete'))
    <button class="btn btn-danger btn-sm btn-icon icon-left" id="clear-bulk-rate" type="submit">
        <i class="entypo-cancel"></i>
        Delete Selected
    </button>
    @endif
    <form id="clear-bulk-rate-form" >
        <input type="hidden" name="VendorRateID" value="">
        <input type="hidden" name="Trunk" value="">
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
        <th width="5%">Rate ({{$CurrencySymbol}})</th>
        <th width="10%">Effective Date</th>
        <th width="10%">Modified Date</th>
        <th width="10%">Modified By</th>
        <th width="20%">Action</th>
    </tr>
</thead>
<tbody>

</tbody>
</table>
 
<script type="text/javascript">
jQuery(document).ready(function($) {
    //var data_table;
    var Code, Description, Country,Trunk,Effective,update_new_url;
    var $searchFilter = {};
    var checked='';
    var list_fields  = ['VendorRateID','Code','Description','ConnectionFee','Interval1','IntervalN','Rate','EffectiveDate','updated_at','updated_by'];
    $("#vendor-rate-search").submit(function(e) {
        $searchFilter.Trunk = Trunk = $("#vendor-rate-search select[name='Trunk']").val();
        $searchFilter.Country = Country = $("#vendor-rate-search select[name='Country']").val();
        $searchFilter.Effective = Effective = $("#vendor-rate-search select[name='Effective']").val();
        $searchFilter.Code = Code = $("#vendor-rate-search input[name='Code']").val();
        $searchFilter.Description = Description = $("#vendor-rate-search input[name='Description']").val();

        if(Trunk == '' || typeof Trunk  == 'undefined'){
           toastr.error("Please Select a Trunk", "Error", toastr_opts);
           return false;
        }
        data_table = $("#table-4").dataTable({
            "bDestroy": true, // Destroy when resubmit form
            "bAutoWidth": false,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/vendor_rates/{{$id}}/search_ajax_datagrid",
            "fnServerParams": function(aoData) {
                aoData.push({"name": "Effective", "value": Effective}, {"name": "Trunk", "value": Trunk}, {"name": "Country", "value": Country}, {"name": "Code", "value": Code}, {"name": "Description", "value": Description});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name": "Effective", "value": Effective}, {"name": "Trunk", "value": Trunk}, {"name": "Country", "value": Country},  {"name": "Code", "value": Code}, {"name": "Description", "value": Description});
            },
            "iDisplayLength": parseInt('{{Config::get('app.pageSize')}}'),
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
                        {}, //2Description
                        {}, //3Interval1
                        {}, //4IntervalN
                        {}, //5Rate
                        {}, //6Effective Date
                        {}, //7 updated at
                        {}, //8 updated by
                        {}, //8 updated by
                        {// 9 VendorRateId
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
                                    action += '<a href="Javascript:;" class="edit-vendor-rate btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit</a>';
                                <?php } ?>
                                if (full[0] > 0) {
                                    <?php if(User::checkCategoryPermission('VendorRates','Delete')) { ?>
                                        action += ' <button href="' + clerRate_ + '"  class="btn clear btn-danger btn-sm btn-icon icon-left" data-loading-text="Loading..."><i class="entypo-cancel"></i>Delete</button>';
                                    <?php } ?>
                                }
                                return action;
                            }
                        },
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
                   $(".btn.clear").click(function(e) {

                       response = confirm('Are you sure?');
                       if (response) {
                           $.ajax({
                               url: $(this).attr("href"),
                               type: 'POST',
                               dataType: 'json',
                               beforeSend: function(){
                                   $(this).button('loading');
                               },
                               success: function(response) {
                                   $(this).button('reset');

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
                   $(".edit-vendor-rate.btn").click(function(ev) {
                        ev.stopPropagation();
                        $('#bulk-update-params-show').hide();
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){
                            $("#bulk-edit-vendor-rate-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                        }
                       $('#modal-BulkVendorRate .modal-header h4').text('Edit Vendor Rates')
                       jQuery('#modal-BulkVendorRate').modal('show', {backdrop: 'static'});
                       update_new_url = baseurl + '/vendor_rates/bulk_update/{{$id}}';
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
               }


        });
        $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
        return false;
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
    //Bulk Edit Button
    $("#changeSelectedVendorRates").click(function(ev) {
        if($('#selectallbutton').is(':checked')){
            $("#bulk-edit-vendor-rate-form").find("input[name='EffectiveDate']").val("");
            $("#bulk-edit-vendor-rate-form").find("input[name='Rate']").val("");
            $("#bulk-edit-vendor-rate-form").find("input[name='ConnectionFee']").val(0);
            $("#bulk-edit-vendor-rate-form").find("input[name='Interval1']").val("");
            $("#bulk-edit-vendor-rate-form").find("input[name='IntervalN']").val("");

            $('#bulk-update-params-show').show();
            var search_html='<div class="row">';
            var col_count=1;
            if(Code != ''){
                search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Code</label><div class=""><p class="form-control-static" >'+Code+'</p></div></div></div>';
                col_count++;
            }
            if(Country != '' && Country != 'All'){
                search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Country</label><div class=""><p class="form-control-static" >'+$("#vendor-rate-search select[name='Country']").find("[value='"+Country+"']").text()+'</p></div></div></div>';
                col_count++;
                if(col_count == 3){
                    search_html +='</div><div class="row">';
                    col_count=1;
                }
            }
            if(Description != ''){
                search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Description</label><div class=""><p class="form-control-static" >'+Description+'</p></div></div></div>';
                col_count++;
                if(col_count == 3){
                    search_html +='</div><div class="row">';
                    col_count=1;
                }
            }
            if(Effective != ''){
                search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Effective</label><div class=""><p class="form-control-static" >'+$("#vendor-rate-search select[name='Effective']").find("[value='"+Effective+"']").text()+'</p></div></div></div>';
                col_count++;
                if(col_count == 3){
                    search_html +='</div><div class="row">';
                    col_count=1;
                }
            }
            if(Trunk != ''){
                search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Trunk</label><div class=""><p class="form-control-static" >'+$("#vendor-rate-search select[name='Trunk']").find("[value='"+Trunk+"']").text()+'</p></div></div></div>';
                col_count++;
            }
            search_html+='</div>';
            $("#bulk-update-params-show").html(search_html);


            if(Trunk == '' || typeof Trunk  == 'undefined'){
                toastr.error("Please Select a Trunk then Click Search", "Error", toastr_opts);
                return false;
            }
            $('#modal-BulkVendorRate').modal('show');
            $('#modal-BulkVendorRate .modal-header h4').text('Bulk Update Vendor Rates');
            $("#bulk-edit-vendor-rate-form [name='Interval1']").val(1);
            $("#bulk-edit-vendor-rate-form [name='IntervalN']").val(1);
            date = new Date();
            var month = date.getMonth()+1;
            var day = date.getDate();
            currentDate = date.getFullYear() + '-' +   (month<10 ? '0' : '') + month + '-' +     (day<10 ? '0' : '') + day;
            $("#bulk-edit-vendor-rate-form [name='EffectiveDate']").val(currentDate);
            $('#modal-BulkVendorRate .modal-body').show();
            update_new_url = baseurl + '/vendor_rates/bulk_update_new/{{$id}}';
        }else{
            var VendorRateIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                console.log($(this).val());
                VendorRateID = $(this).val();
                VendorRateIDs[i++] = VendorRateID;
            });
            $('#modal-BulkVendorRate .modal-header h4').text('Bulk Edit Vendor Rates')
            $('#bulk-update-params-show').hide();
            date = new Date();
            var month = date.getMonth()+1;
            var day = date.getDate();
            currentDate = date.getFullYear() + '-' +   (month<10 ? '0' : '') + month + '-' +     (day<10 ? '0' : '') + day;
            $("#bulk-edit-vendor-rate-form").find("input[name='VendorRateID']").val(VendorRateIDs.join(","));
            $("#bulk-edit-vendor-rate-form").find("input[name='EffectiveDate']").val(currentDate);
            $("#bulk-edit-vendor-rate-form").find("input[name='Rate']").val("");
            $("#bulk-edit-vendor-rate-form").find("input[name='Interval1']").val(1);
            $("#bulk-edit-vendor-rate-form").find("input[name='IntervalN']").val(1);
            if(VendorRateIDs.length){
                $('#modal-BulkVendorRate').modal('show', {backdrop: 'static'});
                update_new_url = baseurl + '/vendor_rates/bulk_update/{{$id}}';
            }
        }
    });
     //Bulk Clear Submit
    $("#clear-bulk-rate").click(function() {
        var criteria='';
        if($('#selectallbutton').is(':checked')){
            response = confirm('Are you sure?');
            if (response) {
                criteria = JSON.stringify($searchFilter);
                $("#clear-bulk-rate-form").find("input[name='VendorRateID']").val('');
                $("#clear-bulk-rate-form").find("input[name='Trunk']").val(Trunk);
                $("#clear-bulk-rate-form").find("input[name='criteria']").val(criteria);
                $.ajax({
                    url: baseurl + '/vendor_rates/clear_all_vendorrate/{{$id}}', //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function(){
                        $('#clear-bulk-rate').button('loading');
                    },
                    success: function (response) {
                        $("#clear-bulk-rate").button('reset');

                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            data_table.fnFilter('', 0);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },
                    // Form data
                    data: $('#clear-bulk-rate-form').serialize(),
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false

                });
            }
            return false;
        }else {
            response = confirm('Are you sure?');
            if (response) {
                var VendorIDs = [];
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                    //console.log($(this).val());
                    VendorID = $(this).val();
                    VendorIDs[i++] = VendorID;
                });
                $("#clear-bulk-rate-form").find("input[name='VendorRateID']").val(VendorIDs.join(","));
                $("#clear-bulk-rate-form").find("input[name='Trunk']").val(Trunk);
                $("#clear-bulk-rate-form").find("input[name='criteria']").val('');

                if (VendorIDs.length) {
                    $.ajax({
                        url: baseurl + '/vendor_rates/bulk_clear_rate/{{$id}}', //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        beforeSend: function(){
                            $('#clear-bulk-rate').button('loading');
                        },
                        success: function (response) {
                            $("#clear-bulk-rate").button('reset');

                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                                data_table.fnFilter('', 0);
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        // Form data
                        data: $('#clear-bulk-rate-form').serialize(),
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false

                    });
                }
                return false;
            }
            return false;
        }

    });
    //Bulk Form Submit
    $("#bulk-edit-vendor-rate-form").submit(function() {
        $.ajax({
            url: update_new_url, //Server script to process data
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $(".save.btn").button('reset');
                if (response.status == 'success') {
                    $("#modal-BulkVendorRate").modal("hide");
                    toastr.success(response.message, "Success", toastr_opts);
                    data_table.fnFilter('', 0);
                } else {
                    toastr.error(response.message, "Error", toastr_opts);
                }
            },
            error: function(error) {
                $("#modal-BulkVendorRate").modal("hide");
            },
            // Form data
            data: $('#bulk-edit-vendor-rate-form').serialize()+'&'+$.param($searchFilter),
            //Options to tell jQuery not to process data or worry about content-type.
            cache: false

        });
        return false;
    });
    $("#bulk_set_vendor_rate").click(function(ev) {

        $("#bulk-edit-vendor-rate-form").find("input[name='EffectiveDate']").val("");
        $("#bulk-edit-vendor-rate-form").find("input[name='Rate']").val("");
        $("#bulk-edit-vendor-rate-form").find("input[name='ConnectionFee']").val(0);
        $("#bulk-edit-vendor-rate-form").find("input[name='Interval1']").val("");
        $("#bulk-edit-vendor-rate-form").find("input[name='IntervalN']").val("");

        $('#bulk-update-params-show').show();
        var search_html='<div class="row">';
        var col_count=1;
        if(Code != ''){
            search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Code</label><div class=""><p class="form-control-static" >'+Code+'</p></div></div></div>';
            col_count++;
        }
        if(Country != '' && Country != 'All'){
            search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Country</label><div class=""><p class="form-control-static" >'+$("#vendor-rate-search select[name='Country']").find("[value='"+Country+"']").text()+'</p></div></div></div>';
            col_count++;
            if(col_count == 3){
                search_html +='</div><div class="row">';
                col_count=1;
            }
        }
        if(Description != ''){
            search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Description</label><div class=""><p class="form-control-static" >'+Description+'</p></div></div></div>';
            col_count++;
            if(col_count == 3){
                search_html +='</div><div class="row">';
                col_count=1;
            }
        }
        if(Effective != ''){
            search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Effective</label><div class=""><p class="form-control-static" >'+$("#vendor-rate-search select[name='Effective']").find("[value='"+Effective+"']").text()+'</p></div></div></div>';
            col_count++;
            if(col_count == 3){
                search_html +='</div><div class="row">';
                col_count=1;
            }
        }
        if(Trunk != ''){
            search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Trunk</label><div class=""><p class="form-control-static" >'+$("#vendor-rate-search select[name='Trunk']").find("[value='"+Trunk+"']").text()+'</p></div></div></div>';
            col_count++;
        }
        search_html+='</div>';
        $("#bulk-update-params-show").html(search_html);


        if(Trunk == '' || typeof Trunk  == 'undefined'){
           toastr.error("Please Select a Trunk then Click Search", "Error", toastr_opts);
           return false;
        }
        $('#modal-BulkVendorRate').modal('show');
        $('#modal-BulkVendorRate .modal-header h4').text('Bulk Update Vendor Rates');
        $("#bulk-edit-vendor-rate-form [name='Interval1']").val(1);
        $("#bulk-edit-vendor-rate-form [name='IntervalN']").val(1);
        date = new Date();
        var month = date.getMonth()+1;
        var day = date.getDate();
        currentDate = date.getFullYear() + '-' +   (month<10 ? '0' : '') + month + '-' +     (day<10 ? '0' : '') + day;
        $("#bulk-edit-vendor-rate-form [name='EffectiveDate']").val(currentDate);
        $('#modal-BulkVendorRate .modal-body').show();
         update_new_url = baseurl + '/vendor_rates/bulk_update_new/{{$id}}';

    });
});
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
</style>
@stop

@section('footer_ext')
@parent
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
                                <label for="field-4" class="control-label">Effective Date</label>

                                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}"  data-date-format="yyyy-mm-dd" value="" />
                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Rate</label>

                                <input type="text" name="Rate" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>

                         <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Connection Fee</label>

                                <input type="text" name="ConnectionFee" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>


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

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="VendorRateID" value="">
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