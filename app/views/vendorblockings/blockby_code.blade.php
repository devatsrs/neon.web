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
    <li class="active"  >
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
    <li >
        <a href="{{ URL::to('/vendor_blocking/'.$id.'') }}" >
            <span class="visible-xs"><i class="entypo-home"></i></span>
            <span class="hidden-xs">Block by Country </span>
        </a>
    </li>
    <li class="active">
        <a href="{{ URL::to('/vendor_blocking/index_blockby_code/'.$id.'') }}" >
            <span class="visible-xs"><i class="entypo-user"></i></span>
            <span class="hidden-xs">Block by Code</span>
        </a>
    </li>
</ul>
<div class="tab-content">
     
    <div class="tab-pane active" id="bycode">
        <div class="row">
            <div class="col-md-12">
                <form id="block_by_code_form" method="post"  action="{{URL::to('vendor_blocking/index_blockby_code/'.$id)}}" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
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
                                <label for="field-1" class="col-sm-1 control-label">Code</label>
                                <div class="col-sm-2">
                                    <input type="text" value="" placeholder="Code" id="field-1" class="form-control" name="Code">
                                </div>
                                <label class="col-sm-1 control-label">Status</label>
                                <div class="col-sm-2">
                                    <?php
                                    $status_array = ["All" => "All", "Blocked" => "Blocked", "Not Blocked" => "Unblocked"];
                                    ?>
                                    {{Form::select('Status', $status_array, Input::get('Status') ,array("class"=>"select2"))}}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-1 control-label">Country</label>
                                <div class="col-sm-2">
                                    {{ Form::select('Country', $countries, Input::get('Country') , array("class"=>"select2")) }}
                                </div>
                                <label class="col-sm-1 control-label">Trunk</label>
                                <div class="col-sm-2">
                                    {{ Form::select('Trunk', $trunks, $trunk_keys, array("class"=>"select2")) }}
                                </div>
                                <label class="col-sm-1 control-label">Timezone</label>
                                <div class="col-sm-3">
                                    {{ Form::select('Timezones', $Timezones, '', array("class"=>"select2")) }}
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
                 <div class="   ">
                    <div style="text-align: right;padding:10px 0 ">
                        <form id="blockSelectedCode-form" method="post" action="{{URL::to('vendor_blocking/blockbycode/'.$id )}}"  style="margin: 0px; padding: 0px; display: inline-table;" >
                            <button id="blockSelectedCode" class="btn btn-primary btn-sm btn-icon icon-left"   data-loading-text="Loading...">
                                <i class="entypo-floppy"></i>
                                Unblock Selected Codes
                            </button>
                            <input type="hidden" name="RateID" value="">
                            <input type="hidden" name="Trunk" value="{{Input::get('Trunk')}}">
                            <input type="hidden" name="Timezones" value="{{Input::get('Timezones')}}">
                            <input type="hidden" name="criteria" value="">
                            <input type="hidden" name="action" value="unblock">
                        </form>
                        <form id="unblockSelectedCode-form" method="post" action="{{URL::to('vendor_blocking/blockbycode/'.$id )}}" style="margin: 0px; padding: 0px; display: inline-table;" >
                            <button type="submit" id="unblockSelectedCode" class="btn btn-danger btn-sm btn-icon icon-left" data-loading-text="Loading...">
                                <i class="entypo-cancel"></i>
                                Block Selected Codes
                            </button>
                            <input type="hidden" name="RateID" value="">
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
                            <!--<button type="button" id="selectallbutton"  class="btn btn-primary btn-xs" title="Select All Found Code"><i class="entypo-check"></i></button>-->
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
             </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var $searchFilter = {};
var checked='';
jQuery(document).ready(function($) {
    //var data_table;
    $("#block_by_code_form").submit(function(e) {
        $searchFilter.Trunk = $("#block_by_code_form select[name='Trunk']").val();
        $searchFilter.Status = $("#block_by_code_form select[name='Status']").val();
        $searchFilter.Country = $("#block_by_code_form select[name='Country']").val();
        $searchFilter.Code = $("#block_by_code_form [name='Code']").val();
        $searchFilter.Timezones = $("#block_by_code_form select[name='Timezones']").val();
        if(typeof $searchFilter.Trunk  == 'undefined' || $searchFilter.Trunk == '' ){
            toastr.error("Please Select a Trunk", "Error", toastr_opts);
            return false;
        }
        
        

        data_table = $("#table-4").dataTable({
            "bDestroy": true, // Destroy when resubmit form
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/vendor_blocking/{{$id}}/ajax_datagrid_blockbycode",
            "fnServerParams": function(aoData) {
                aoData.push({"name": "Trunk", "value": $searchFilter.Trunk}, {"name": "Status", "value": $searchFilter.Status}, {"name": "Country", "value": $searchFilter.Country},{"name": "Code", "value": $searchFilter.Code},{"name": "Timezones", "value": $searchFilter.Timezones});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name": "Trunk", "value": $searchFilter.Trunk}, {"name": "Status", "value": $searchFilter.Status}, {"name": "Country", "value": $searchFilter.Country},{"name": "Code", "value": $searchFilter.Code},{"name": "Timezones", "value": $searchFilter.Timezones});
            },
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
             "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
             "aaSorting": [[1, "asc"]],
             "aoColumns":
                    [
                        {"bSortable": false, //RateId
                            mRender: function(id, type, full) {
                                return '<input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" >';
                            }
                        },  //0 RateId
                        {}, //1 Code
                        {}, //2 Status
                        {}, //3 Description
                    ],
            "oTableTools":
            {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/vendor_blocking/{{$id}}/blockbycode_exports/xlsx",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/vendor_blocking/{{$id}}/blockbycode_exports/csv",
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
                                    
            },
             
        });
        $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
        return false;
    });
     

     //Unblock Selected Countries
     $("#unblockSelectedCode-form").submit(function() {
        var criteria='';
        var RateIDs = [];
         if($('#selectallbutton').is(':checked')){
             criteria = JSON.stringify($searchFilter);
        }else{
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                console.log($(this).val());
                RateID = $(this).val();
                RateIDs[i++] = RateID;
            });
        }
        $("#unblockSelectedCode-form").find("input[name='RateID']").val(RateIDs.join(","));
        
        //Trunk = $('#block_by_code_form').find("select[name='Trunk']").val();
        $("#unblockSelectedCode-form").find("input[name='Trunk']").val($searchFilter.Trunk);
        $("#unblockSelectedCode-form").find("input[name='Timezones']").val($searchFilter.Timezones);
        $("#unblockSelectedCode-form").find("input[name='criteria']").val(criteria);
        
        var formData = new FormData($('#unblockSelectedCode-form')[0]);
        $.ajax({
            url: $("#unblockSelectedCode-form").attr("action"),
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $("#unblockSelectedCode").button('reset');
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
    $("#blockSelectedCode-form").submit(function() {
        var criteria='';
        var RateIDs = [];
        if($('#selectallbutton').is(':checked')){
            criteria = JSON.stringify($searchFilter);
        }else{
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                console.log($(this).val());
                RateID = $(this).val();
                RateIDs[i++] = RateID;
            });
        }
        $("#blockSelectedCode-form").find("input[name='RateID']").val(RateIDs.join(","));
        //Trunk = $('#block_by_code_form').find("select[name='Trunk']").val();
        $("#blockSelectedCode-form").find("input[name='Trunk']").val($searchFilter.Trunk);
        $("#blockSelectedCode-form").find("input[name='Timezones']").val($searchFilter.Timezones);
        $("#blockSelectedCode-form").find("input[name='criteria']").val(criteria);

        var formData = new FormData($('#blockSelectedCode-form')[0]);
        $.ajax({
            url: $("#blockSelectedCode-form").attr("action"),
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $("#blockSelectedCode").button('reset');
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