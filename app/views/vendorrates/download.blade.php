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
@if(User::checkCategoryPermission('VendorRates','Upload'))
<li>
    <a href="{{ URL::to('/vendor_rates/'.$id.'/upload') }}" >
        <span class="hidden-xs">Vendor Rate Upload</span>
    </a>
</li>
@endif
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
                <label for="field-1" class="col-sm-3 control-label">Output format</label>
                <div class="col-sm-5">
 
                   {{ Form::select('Format', $rate_sheet_formates, Input::get('RateSheetFormate') , array("class"=>"selectboxit")) }}
                    
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label">Download Type</label>
                <div class="col-sm-5">

                   {{ Form::select('downloadtype', $downloadtype, Input::get('downloadtype') , array("class"=>"selectboxit")) }}

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
});
</script>
@stop