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
        <a href="{{URL::to('accounts/'.$id.'/show')}}">{{Account::getCompanyNameByID($id)}}</a>
    </li>
    <li class="active">
        <strong>Vendor Blocking</strong>
    </li>

</ol>
<h3>Vendor Blocking</h3>
@include('accounts.errormessage')
<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
    <li>
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
    <li class="active" >
        <a href="{{ URL::to('vendor_blocking/'.$id) }}" >
            <span class="hidden-xs">Blocking</span>
        </a>
    </li>
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

<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
    <li class="active">
        <a href="{{ URL::to('/vendor_blocking/'.$id.'') }}">
            <span class="visible-xs"><i class="entypo-home"></i></span>
            <span class="hidden-xs">Block by Country </span>
        </a>
    </li>
    <li >
        <a href="{{ URL::to('/vendor_blocking/index_blockby_code/'.$id.'') }}" >
            <span class="visible-xs"><i class="entypo-user"></i></span>
            <span class="hidden-xs">Block by Code</span>
        </a>
    </li>
</ul>
<div class="tab-content">
    <div class="tab-pane active" id="bycountry">
        <div class="row">
            <div class="col-md-12">
                <form id="block_by_country_form" method="get"  action="{{URL::to('vendor_blocking/'.$id)}}" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                    <div class="panel panel-primary" data-collapsed="0">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Filter
                            </div>
                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-1 control-label">Country</label>
                                <div class="col-sm-2">
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
                            <div class="form-group">
                                <label class="col-sm-1 control-label">Status</label>
                                <div class="col-sm-3">
                                    <?php
                                    $status_array = ["All" => "All", "Blocked" => "Blocked", "Not Blocked" => "Unblocked"];
                                    ?>
                                    {{Form::select('Status', $status_array, Input::get('Status') ,array("class"=>"select2"))}}
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
             
            <div style="text-align: right;padding:10px 0 ">
                <form id="unblockSelectedCountry-form" method="post" action="{{URL::to('vendor_blocking/blockbycountry/'.$id )}}" style="margin: 0px; padding: 0px; display: inline-table;" >
                    <button type="submit" id="unblockSelectedCountry" class="btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-cancel"></i>
                        Unblock Selected Country
                    </button>
                    <input type="hidden" name="CountryID" value="">
                    <input type="hidden" name="Trunk" value="{{Input::get('Trunk')}}">
                    <input type="hidden" name="Timezones" value="{{Input::get('Timezones')}}">
                    <input type="hidden" name="criteria" value="">
                    <input type="hidden" name="action" value="unblock">
                </form>
                <form id="blockSelectedCountry-form" method="post" action="{{URL::to('vendor_blocking/blockbycountry/'.$id )}}"  style="margin: 0px; padding: 0px; display: inline-table;" >
                    <button  id="blockSelectedCountry" type="submit" class="btn btn-danger btn-sm btn-icon icon-left" href="javascript:;" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Block Selected Country
                    </button>
                    <input type="hidden" name="CountryID" value="">
                    <input type="hidden" name="Trunk" value="{{Input::get('Trunk')}}">
                    <input type="hidden" name="Timezones" value="{{Input::get('Timezones')}}">
                    <input type="hidden" name="criteria" value="">
                    <input type="hidden" name="action" value="block">
                </form>
            </div>

            <table class="table table-bordered datatable" id="table-4">
                <thead>
                    <tr>
                        <th>
                <div class="checkbox ">
                    <input type="checkbox" id="selectall" name="checkbox[]" class="">
                    <!--<button type="button" id="selectallbutton"  class="btn btn-primary btn-xs" title="Select All Found Country"><i class="entypo-check"></i></button>-->
                </div>
                </th>
                <th>Country</th>
                <th>Staus</th>
                </tr>
                </thead>
                <tbody>
                      
                 </tbody>
            </table>
 
        </div>
    </div>
    <div class="tab-pane " id="bycode">
        <div class="row">
            <div class="col-md-12">
                <form role="form" id="form1" method="get"  action="{{URL::to('vendor_blocking/index_blockby_code/'.$id)}}" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                    <div class="panel panel-primary" data-collapsed="0">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Filter
                            </div>
                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-1 control-label">Country</label>
                                <div class="col-sm-2">
                                    {{ Form::select('Country', $countries, Input::get('Country') , array("class"=>"select2")) }}
                                </div>
                                <label class="col-sm-1 control-label">Trunk</label>
                                <div class="col-sm-2">
                                    {{ Form::select('Trunk', $trunks, Input::get('Trunk') , array("class"=>"select2")) }}
                                </div>
                                <label class="col-sm-1 control-label">Status</label>
                                <div class="col-sm-2">
                                    <?php
                                    $status_array = ["All" => "All", "Blocked" => "Blocked", "Not Blocked" => "Unblocked"];
                                    ?>
                                    {{Form::select('Status', $status_array, Input::get('Status') ,array("class"=>"select2"))}}
                                </div>
                            </div>
                            <p style="text-align: right" >
                                <button type="submit"  class="btn btn-primary btn-sm btn-icon icon-left">
                                    <i class="entypo-search"></i>
                                    Search
                                </button>
                            </p>
                        </div>
                    </div>
                </form>
                @if(isset($results_2) && count($results_2)>0)
                <div class="   ">
                    <div style="text-align: right;padding:10px 0 ">
                        <form id="blockSelectedCode-form" method="post" action="{{URL::to('vendor_blocking/unblockby_code/'.$id )}}"  style="margin: 0px; padding: 0px; display: inline-table;" >
                            <a    id="blockSelectedCode" class="btn btn-primary btn-sm btn-icon icon-left" href="javascript:;">
                                <i class="entypo-floppy"></i>
                                Unblock Selected Codes
                            </a>
                            <input type="hidden" name="RateID" value="">
                            <input type="hidden" name="Trunk" value="{{Input::get('Trunk')}}">
                        </form>
                        <form id="unblockSelectedCode-form" method="post" action="{{URL::to('vendor_blocking/blockby_code/'.$id )}}" style="margin: 0px; padding: 0px; display: inline-table;" >
                            <a type="submit" id="unblockSelectedCode" class="btn btn-danger btn-sm btn-icon icon-left">
                                <i class="entypo-cancel"></i>
                                Block Selected Codes
                            </a>
                            <input type="hidden" name="RateID" value="">
                            <input type="hidden" name="Trunk" value="{{Input::get('Trunk')}}">
                        </form>
                    </div>
                    <table class="table table-bordered datatable" id="table-5">
                        <thead>
                            <tr>
                                <th>
                        <div class="checkbox ">
                            <input type="checkbox" id="selectallbycode" name="checkbox[]" class="">
                        </div>
                        </th>
                        <th>Code</th>
                        <th>Status</th>
                        <th>Description</th>
                        </tr>
                        </thead>
                        <tbody>

                            @if(isset($results_2) && count($results_2)>0)
                            @foreach($results_2 as $result)
                            <tr class="odd gradeX">
                                <td>
                                    <div class="checkbox ">
                                        <input type="checkbox" name="checkbox[]" class="rowcheckbox" value="{{$result->RateId}}">
                                    </div>
                                </td>
                                <td>{{$result->Code}}</td>
                                <td>{{$result->Status}}</td>
                                <td>{{$result->Description}}</td>
                            </tr>
                            @endforeach
                            @endif

                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var $searchFilter = {};
var checked='';
jQuery(document).ready(function($) {
    //var data_table;
    $("#block_by_country_form").submit(function(e) {
        e.preventDefault();
        $searchFilter.Trunk = $("#block_by_country_form select[name='Trunk']").val();
        $searchFilter.Status = $("#block_by_country_form select[name='Status']").val();
        $searchFilter.Country = $("#block_by_country_form select[name='Country']").val();
        $searchFilter.Timezones = $("#block_by_country_form select[name='Timezones']").val();

        if(typeof $searchFilter.Trunk  == 'undefined' || $searchFilter.Trunk == '' ){
            toastr.error("Please Select a Trunk", "Error", toastr_opts);
            return false;
        }
        data_table = $("#table-4").dataTable({
            "bDestroy": true, // Destroy when resubmit form
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/vendor_blocking/{{$id}}/ajax_datagrid_blockbycountry",
            "fnServerParams": function(aoData) {
                aoData.push({"name": "Trunk", "value": $searchFilter.Trunk}, {"name": "Status", "value": $searchFilter.Status},{"name": "Country", "value": $searchFilter.Country},{"name": "Timezones", "value": $searchFilter.Timezones});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name": "Trunk", "value": $searchFilter.Trunk}, {"name": "Status", "value": $searchFilter.Status},{"name": "Country", "value": $searchFilter.Country},{"name": "Timezones", "value": $searchFilter.Timezones});
            },
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
             "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
             "aaSorting": [[1, "asc"]],
             "aoColumns":
                    [
                        {"bSortable": false, //CountryID
                            mRender: function(id, type, full) {
                                return '<input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" >';
                            }
                        }, //0Checkbox
                        {}, //1 Country
                        {}, //Status
                    ],
            "oTableTools":
            {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/vendor_blocking/{{$id}}/blockbycountry_exports/xlsx",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/vendor_blocking/{{$id}}/blockbycountry_exports/csv",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            }, 
            "fnDrawCallback": function() {
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
                                $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                $(this).addClass('selected');
                            });
                        }
                    }else{
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
     

     //Unblock Selected Countries
     $("#unblockSelectedCountry-form").submit(function() {
         var criteria='';
         var CountryIDs = [];
         if($('#selectallbutton').is(':checked')){
             criteria = JSON.stringify($searchFilter);
         }else{
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                console.log($(this).val());
                CountryID = $(this).val();
                CountryIDs[i++] = CountryID;
            });
         }
        $("#unblockSelectedCountry-form").find("input[name='CountryID']").val(CountryIDs.join(","));
        
        //Trunk = $('#block_by_country_form').find("select[name='Trunk']").val();
        $("#unblockSelectedCountry-form").find("input[name='Trunk']").val($searchFilter.Trunk);
        $("#unblockSelectedCountry-form").find("input[name='Timezones']").val($searchFilter.Timezones);
        $("#unblockSelectedCountry-form").find("input[name='criteria']").val(criteria);
        
        var formData = new FormData($('#unblockSelectedCountry-form')[0]);
        $.ajax({
            url: $("#unblockSelectedCountry-form").attr("action"),
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $("#unblockSelectedCountry").button('reset');
                if (response.status == 'success') {
                    toastr.success(response.message, "Success", toastr_opts);
                    data_table.fnFilter('', 0);
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

    //Block Selected Countries
    $("#blockSelectedCountry-form").submit(function() {
        var criteria='';
        var CountryIDs = [];
        if($('#selectallbutton').is(':checked')){
            criteria = JSON.stringify($searchFilter);
        }else{
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                //console.log($(this).val());
                CountryID = $(this).val();
                CountryIDs[i++] = CountryID;
            });
        }

        $("#blockSelectedCountry-form").find("input[name='CountryID']").val(CountryIDs.join(","));
        //Trunk = $('#block_by_country_form').find("select[name='Trunk']").val();
        $("#blockSelectedCountry-form").find("input[name='Trunk']").val($searchFilter.Trunk);
        $("#blockSelectedCountry-form").find("input[name='Timezones']").val($searchFilter.Timezones);
        $("#blockSelectedCountry-form").find("input[name='criteria']").val(criteria);

        var formData = new FormData($('#blockSelectedCountry-form')[0]);
        $.ajax({
            url: $("#blockSelectedCountry-form").attr("action"),
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $("#blockSelectedCountry").button('reset');
                if (response.status == 'success') {
                    toastr.success(response.message, "Success", toastr_opts);
                    data_table.fnFilter('', 0);
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

    //Select Row on click
    $('#table-4 tbody').on('click', 'tr', function() {
        if (checked =='') {
            $(this).toggleClass('selected');
            if ($(this).hasClass("selected")) {
                $(this).find('.rowcheckbox').prop("checked", true);
            } else {
                $(this).find('.rowcheckbox').prop("checked", false);
            }
        }
    });

    // Select all
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
    // Replace Checboxes
    $(".pagination a").click(function(ev) {
        replaceCheckboxes();
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
@include('includes.errors')
@include('includes.success')
@stop