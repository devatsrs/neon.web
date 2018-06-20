@extends('layout.main')
@section('content')

<link rel="stylesheet" type="text/css" href="<?php echo URL::to('/').'/assets/Bootstrap-Dual-Listbox/bootstrap-duallistbox.css'; ?>">
<script src="<?php echo URL::to('/').'/assets/Bootstrap-Dual-Listbox/jquery.bootstrap-duallistbox.min.js'; ?>" ></script>

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
		<strong>Vendor Rate Sheet  Downloads</strong>
	</li>
</ol>
<h3>Vendor Rate Sheet  Download</h3>
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
<li class="active">
    <a href="{{ URL::to('/vendor_rates/'.$id.'/download') }}" >
        <span class="hidden-xs">Vendor Rate Download</span>
    </a>
</li>
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


 <div class="panel panel-primary" data-collapsed="0">
    
    <div class="panel-heading">
        <div class="panel-title">
            Vendor Rate Sheet Download
        </div>
        
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    
    <div class="panel-body">
        
        <form id="form-download" action="{{URL::to('vendor_rates/'.$id.'/process_download')}}" role="form" class="form-horizontal form-groups-bordered">
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label">Trunk</label>
                <div class="col-sm-5">
                   @foreach ((array)$trunks as $key => $value)
                        @if(!empty($key))
                        <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="Trunks[]" value="{{$key}}" >{{$value}}
                            </label>
                        </div>
                        </div>
                        @endif
                   @endforeach
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label">Timezones</label>
                <div class="col-sm-5">
                   @foreach ($Timezones as $key => $value)
                        @if(!empty($key))
                        <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="Timezones[]" value="{{$key}}" >{{$value}}
                            </label>
                        </div>
                        </div>
                        @endif
                   @endforeach
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label">Output format</label>
                <div class="col-sm-5">
 
                   {{ Form::select('Format', $rate_sheet_formates, Input::get('RateSheetFormate') , array("class"=>"select2 small","id"=>"fileformat")) }}
                    
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label">File Type</label>
                <div class="col-sm-5">

                   {{ Form::select('filetype', array(''=>'Select a Type'), Input::get('downloadtype') , array("class"=>"select2","id"=>"filetype",'allowClear'=>'true')) }}

                </div>
            </div>
            <div class="form-group effective">
                <label for="field-1" class="col-sm-3 control-label">Effective</label>
                <div class="col-sm-5">

                    <select name="Effective" class="select2 small" data-allow-clear="true" data-placeholder="Select Effective" id="fileeffective">
                        <option value="Now" selected="selected">Now</option>
                        <option value="Future">Future</option>
                        <option value="CustomDate">Custom Date</option>
                        <option value="All">All</option>
                    </select>
                </div>
            </div>
            <div class="form-group DateFilter" style="display: none;">
                <label for="field-1" class="col-sm-3 control-label">Date</label>
                <div class="col-sm-5">
                    {{ Form::text('CustomDate', date('Y-m-d'), array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd","placeholder"=>date('Y-m-d'),"data-startdate"=>date('Y-m-d'))) }} {{--  ,"data-enddate"=>date('Y-m-d') --}}
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label">Merge Output file By Trunk</label>
                <div class="col-sm-5">
                    <div class="make-switch switch-small" data-on-label="<i class='entypo-check'></i>" data-off-label="<i class='entypo-cancel'></i>" data-checked="false" data-animated="false">
                                <input type="hidden" name="isMerge" value="0">
                                <input type="checkbox" name="isMerge"   value="1" >
                    </div>
                </div>
            </div>

            <h4 >Click <span class="label label-info" onclick="$('#vendor_box').toggle();"    style="cursor: pointer">here</span> to select additional customer accounts for bulk ratesheet download.</h4>
            <div style="display: none;" id="vendor_box">
                {{Form::select('vendors[]',$Vendors,array(),array("id"=>"vendors","class"=>"","multiple"=>"multiple"))}}
                <br/>
            </div>

        </form>
         <p style="text-align: right;">
            <button class="btn download btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                <i class="entypo-floppy"></i>
                Download
            </button>
         </p>


    </div>
    
</div>



 
<script type="text/javascript">
jQuery(document).ready(function ($) {
    var vendors = $('#vendors').bootstrapDualListbox({
        nonselectedlistlabel: 'Non-selected',
        selectedlistlabel: 'Selected',
        filterPlaceHolder: 'Search',
        moveonselect: false,
        preserveselectiononmove: 'moved',
    });

    $('#fileformat').change(function(e){
        if($(this).val()){
            var url = baseurl +'/vendor_rates/{{$id}}/customerdownloadtype/'+$(this).val();
            $.ajax({
                url:  url,  //Server script to process data
                type: 'POST',
                success: function (response) {
                    $('#filetype').empty();
                    $('#filetype').append(response);
                    setTimeout(function(){
                        $("#filetype").select2('val','');
                    },200)
                },
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
            });

            if($(this).val()=='{{RateSheetFormate::RATESHEET_FORMAT_VOS20}}'){
                $('#fileeffective').empty();
                var html ='<option value="Now" selected="selected">Now</option><option value="Future">Future</option><option value="CustomDate">Custom Date</option>';
                $('#fileeffective').append(html).trigger('change');
            }else{
                $('#fileeffective').empty();
                var html ='<option value="Now" selected="selected">Now</option><option value="Future">Future</option><option value="All">All</option><option value="CustomDate">Custom Date</option>';
                $('#fileeffective').append(html).trigger('change');
            }

        }else{
            $('#filetype').empty();
            $("#filetype").select2('val','');
        }
    });

		$(".btn.download").click(function () {
           // return false;
            var formData = new FormData($('#form-download')[0]);
             $.ajax({
                url:  $('#form-download').attr("action"),  //Server script to process data
                type: 'POST',
                dataType: 'json',
                //Ajax events
                beforeSend: function(){
                    $('.btn.download').button('loading');
                },
                afterSend: function(){
                    console.log("Afer Send");
                },
                success: function (response) {
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        reloadJobsDrodown(0);
                     } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    //alert(response.message);
                    $('.btn.download').button('reset');

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

$(".dataTables_wrapper select").select2({
minimumResultsForSearch: -1
});

// Replace Checboxes
$(".pagination a").click(function (ev) {
replaceCheckboxes();
});

    $("#fileeffective").on("change", function() {
        if($(this).val() == "CustomDate") {
            $(".DateFilter").show();
        } else {
            $(".DateFilter").hide();
        }
    });
});
</script>
@stop