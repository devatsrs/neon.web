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
        <strong>Vendor Preference</strong>
    </li>
</ol>
<h3>Vendor Preference</h3>
@include('accounts.errormessage')
<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
<li >
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
<li class="active">
    <a href="{{ URL::to('/vendor_rates/vendor_preference/'.$id) }}" >
        <span class="hidden-xs">Preference</span>
    </a>
</li>
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
    <!--<a class="btn btn-primary btn-sm btn-icon icon-left" id="bulk_set_vendor_rate" href="javascript:;">
        <i class="entypo-floppy"></i>
        Bulk update
    </a>-->
    <a class="btn btn-primary btn-sm btn-icon icon-left" id="changeSelectedVendorRates" href="javascript:;">
        <i class="entypo-floppy"></i>
        Change Selected Preference
    </a>
</div>

<?php $code = Currency::getCurrency($Account->CurrencyId);?>

<table class="table table-bordered datatable" id="table-4">
<thead>
    <tr>
        <th width="6%"><input type="checkbox" id="selectall" name="checkbox[]" class="" />
            <!--<button type="button" id="selectallbutton"  class="btn btn-primary btn-xs" title="Select All Preference" alt="Select All Preference"><i class="entypo-check"></i></button>-->
        </th>
        <th width="13%">Code</th>
        <th width="10%">Preference</th>
        <th width="10%">Description</th>
        <th width="15%">Action</th>
    </tr>
</thead>
<tbody>

</tbody>
</table>
 
<script type="text/javascript">
jQuery(document).ready(function($) {
    //var data_table;
    var Code, Description, Country,Trunk;
    var $searchFilter = {};
    var checked='';
    var list_fields  = ['RateID','Code','Preference','Description','VendorPreferenceID'];
    $("#vendor-rate-search").submit(function(e) {
        $searchFilter.Trunk = Trunk = $("#vendor-rate-search select[name='Trunk']").val();
        $searchFilter.Country = Country = $("#vendor-rate-search select[name='Country']").val();
        $searchFilter.Code = Code = $("#vendor-rate-search input[name='Code']").val();
        $searchFilter.Timezones = Timezones = $("#vendor-rate-search select[name='Timezones']").val();

        $searchFilter.Description = Description = $("#vendor-rate-search input[name='Description']").val();
        if(Trunk == '' || typeof Trunk  == 'undefined'){
           toastr.error("Please Select a Trunk", "Error", toastr_opts);
           return false;
        }
        data_table = $("#table-4").dataTable({
            "bDestroy": true, // Destroy when resubmit form
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/vendor_rates/{{$id}}/search_ajax_datagrid_preference/type",
            "fnServerParams": function(aoData) {
                aoData.push( {"name": "Trunk", "value": Trunk}, {"name": "Country", "value": Country}, {"name": "Code", "value": Code}, {"name": "Description", "value": Description}, {"name": "Timezones", "value": Timezones});
                data_table_extra_params.length = 0;
                data_table_extra_params.push(  {"name": "Trunk", "value": Trunk}, {"name": "Country", "value": Country},  {"name": "Code", "value": Code}, {"name": "Description", "value": Description}, {"name": "Timezones", "value": Timezones},{"name":"Export","value":1});
            },
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
             "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
             "aaSorting": [[1, "asc"]],
            "aoColumns":
                    [
                        {"bSortable": false, //RateID
                            mRender: function(id, type, full) {
                            return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                            }
                        },
                        {"bSortable": true}, //1 Code
                        {"bSortable": true}, //2 Prefrence
                        {"bSortable": true}, //8 Discription
                        {// 9 VendorPreferenceID
                            mRender: function(id, type, full) {
                                var action;
                                action = '<div class = "hiddenRowData" >';
                                for(var i = 0 ; i< list_fields.length; i++){
                                    action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                }
                                action += '</div>';

                                action += ' <a href="Javascript:;" title="Edit" class="edit-vendor-rate btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';

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
                                "sUrl": baseurl + "/vendor_rates/{{$id}}/search_ajax_datagrid_preference/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/vendor_rates/{{$id}}/search_ajax_datagrid_preference/csv",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    },
               "fnDrawCallback": function() {

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
                       $('#modal-BulkVendorRate .modal-header h4').text('Edit Vendor Preference')
                       jQuery('#modal-BulkVendorRate').modal('show', {backdrop: 'static'});
                       $("#bulk-edit-vendor-rate-form [name='Action']").val('single');
                   });

                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });

                   $('#selectallbutton').click(function(ev) {
                       if($(this).is(':checked')){
                           //if($(this).find('i').hasClass('entypo-check')){
                           //$(this).find('i').addClass('entypo-cancel').removeClass('entypo-check');
                           //$(this).find('span').text('Deselect all found Preference');
                           checked = 'checked=checked disabled';
                           $("#selectall").prop("checked", true).prop('disabled', true);
                           if(!$('#changeSelectedInvoice').hasClass('hidden')){
                               $('#table-4 tbody tr').each(function(i, el) {
                                   $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                   $(this).addClass('selected');
                               });
                           }
                       }else{
                          // $(this).find('i').addClass('entypo-check').removeClass('entypo-cancel');
                           //$(this).find('span').text('Select all found Preference');
                           checked = '';
                           $("#selectall").prop("checked", false).prop('disabled', false);
                           if(!$('#changeSelectedInvoice').hasClass('hidden')){
                               $('#table-4 tbody tr').each(function(i, el) {
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

               $('#table-4 tbody').on('click', 'tr', function() {
                   if (checked =='') {
                       $(this).toggleClass('selected');
                       if ($(this).hasClass('selected')) {
                           $(this).find('.rowcheckbox').prop("checked", true);
                       } else {
                           $(this).find('.rowcheckbox').prop("checked", false);
                       }
                   }
               });

    // Replace Checboxes
    $(".pagination a").click(function(ev) {
        replaceCheckboxes();
    });

    //Bulk Edit Button
    $("#changeSelectedVendorRates").click(function(ev) {
        var criteria='';
        if($('#selectallbutton').is(':checked')){
        //if($('#selectallbutton').find('i').hasClass('entypo-cancel')){
            criteria = JSON.stringify($searchFilter);
            if(criteria==''){
                return false;
            }
        }
        var RateIDs = [];
        var VendorPreferenceIDs = [];
        var i = 0;
        var j = 0;
        $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
            RateID = $(this).val();
            if($(this).parents('tr').children().find('div.hiddenRowData').find("[name='VendorPreferenceID']").val().trim() != ''){
            console.log($(this).parents('tr').children().find('div.hiddenRowData').find("[name='VendorPreferenceID']").val())
                VendorPreferenceIDs[j++] = $(this).parents('tr').children().find('div.hiddenRowData').find("[name='VendorPreferenceID']").val()
            }
            RateIDs[i++] = RateID;
        });
        $('#modal-BulkVendorRate .modal-header h4').text('Bulk Edit Vendor Preference')
        $('#bulk-update-params-show').hide();
        var cur_obj = $(this).prev("div.hiddenRowData");
        for(var i = 0 ; i< list_fields.length; i++){
            $("#bulk-edit-vendor-rate-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
        }
        $("#bulk-edit-vendor-rate-form [name='Preference']").val('{{CompanyConfiguration::get('DEFAULT_PREFERENCE')}}');
        if(criteria!=''){
            $('#modal-BulkVendorRate').modal('show', {backdrop: 'static'});
            $("#bulk-edit-vendor-rate-form [name='Action']").val('bulk');
            $("#bulk-edit-vendor-rate-form [name='RateID']").val('');
            $("#bulk-edit-vendor-rate-form [name='VendorPreferenceID']").val('');
        }else if(RateIDs.length){
            $('#modal-BulkVendorRate').modal('show', {backdrop: 'static'});
            $("#bulk-edit-vendor-rate-form [name='Action']").val('selected');
            $("#bulk-edit-vendor-rate-form [name='RateID']").val(RateIDs.join(","));
            $("#bulk-edit-vendor-rate-form [name='VendorPreferenceID']").val(VendorPreferenceIDs.join(","));
        }
    });
    //Bulk Form Submit
    $("#bulk-edit-vendor-rate-form").submit(function() {
        $.ajax({
            url: baseurl + '/vendor_rates/bulk_update_preference/{{$id}}', //Server script to process data
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

        $("#bulk-edit-vendor-rate-form").find("input[name='Preference']").val('{{CompanyConfiguration::get('DEFAULT_PREFERENCE')}}');
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
        $('#modal-BulkVendorRate .modal-header h4').text('Bulk Update Vendor Preference');
        $("#bulk-edit-vendor-rate-form [name='Preference']").val('{{CompanyConfiguration::get('DEFAULT_PREFERENCE')}}');


        $('#modal-BulkVendorRate .modal-body').show();
        $("#bulk-edit-vendor-rate-form [name='Action']").val('bulk');
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

            <form id="bulk-edit-vendor-rate-form" method="post" action="{{URL::to('vendor_rates/bulk_update_preference/'.$id)}}">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Bulk Edit Vendor Preference</h4>
                </div>

                <div class="modal-body">
                    <div id="bulk-update-params-show">
                    </div>
                    <div class="row">


                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Preference</label>

                                <input type="text" name="Preference" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="VendorPreferenceID" value="">
                    <input type="hidden" name="RateID" value="">
                    <input type="hidden" name="Action" value="">
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