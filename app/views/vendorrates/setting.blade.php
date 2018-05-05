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
		<strong>Settings</strong>
	</li>
</ol>
<h3>Settings</h3>

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
<li >
    <a href="{{ URL::to('/vendor_rates/'.$id.'/download') }}" >
        <span class="hidden-xs">Vendor Rate Download</span>
    </a>
</li>
@endif
<li class="active">
    <a href="{{ URL::to('/vendor_rates/'.$id.'/settings') }}" >
        <span class="hidden-xs">Settings</span>
    </a>
</li>
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

<div class="tab-content">
    <div class="tab-pane active" id="vendor_rate_tab_content">
        <div class="row">
            <div class="col-md-12">
                <form  id="VendorTrunk-form" method="post" action="{{URL::to('vendor_rates/'.$id.'/update_settings')}}" role="form">
                    <div class="panel panel-primary" data-collapsed="0">
    
                        <div class="panel-heading">
                            <div class="panel-title">
                                Trunks
                            </div>

                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
    
                        <div class="panel-body">
                            <table class="table table-bordered datatable" id="table-4">
                                <thead>
                                    <tr>
                                        <th width="1%"><div class="checkbox "><input type="checkbox" id="selectall" name="checkbox[]" class="" ></div></th>
                                        <th width="20%">Trunk</th>
                                        <th width="20%">Prefix</th>
                                        <th width="20%">Use Prefix In CDR</th>
                                        <th width="30%">CodeDeck</th>
                                        <th width="9%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if(isset($trunks) && count($trunks)>0)
                                    @foreach($trunks as $trunk)
                                    <tr class="odd gradeX  @if(isset($vendor_trunks[$trunk->TrunkID]->Status) && $vendor_trunks[$trunk->TrunkID]->Status == 1) selected @endif">
                                    <td><input type="checkbox" name="VendorTrunk[{{{$trunk->TrunkID}}}][Status]" class="rowcheckbox" value="1" @if(isset($vendor_trunks[$trunk->TrunkID]->Status) && $vendor_trunks[$trunk->TrunkID]->Status == 1) checked @endif ></td>
                                    <td>{{$trunk->Trunk}}</td>
                                    <td><input type="text" class="form-control" name="VendorTrunk[{{{$trunk->TrunkID}}}][Prefix]" value="@if(isset($vendor_trunks[$trunk->TrunkID]->Prefix)){{$vendor_trunks[$trunk->TrunkID]->Prefix}}@endif"  /></td>
                                    <td class="center" style="text-align:center"><input type="checkbox" value="1" name="VendorTrunk[{{{$trunk->TrunkID}}}][UseInBilling]" @if( ( isset($vendor_trunks[$trunk->TrunkID]->UseInBilling) && $vendor_trunks[$trunk->TrunkID]->UseInBilling == 1)  || (CompanySetting::getKeyVal('UseInBilling') == 1 && !isset($vendor_trunks[$trunk->TrunkID]->UseInBilling)) ) checked @endif  ></td>
                                    <td>
                                    <?php $CodeDeckId =  isset($vendor_trunks[$trunk->TrunkID])? $vendor_trunks[$trunk->TrunkID]->CodeDeckId:''?>
                                        {{ Form::select('VendorTrunk['.$trunk->TrunkID.'][CodeDeckId]', $codedecklist, $CodeDeckId , array("class"=>"select2 codedeckid")) }}
                                        <input type="hidden" name="prev_codedeckid" value="{{$CodeDeckId}}">
                                        <input type="hidden" name="trunkid" value="{{$trunk->TrunkID}}">
                                    </td>
                                    <td>
                                        @if(isset($vendor_trunks[$trunk->TrunkID]->Status) && ($vendor_trunks[$trunk->TrunkID]->Status == 1)) Active @else Inactive
                                        @endif
                                    </td>
                                    <input type="hidden" name="VendorTrunk[{{{$trunk->TrunkID}}}][VendorTrunkID]" value="@if(isset($vendor_trunks[$trunk->TrunkID]->VendorTrunkID)){{$vendor_trunks[$trunk->TrunkID]->VendorTrunkID}}@endif"  />
                                </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                            <p class="float-right " >
                                <button  id="vendor-trunks-submit" class="btn save btn-primary btn-sm btn-icon icon-left">
                                    <i class="entypo-floppy"></i>
                                    Save
                                </button>
                            </p>
                        </div>
                    </div>
                    </form>
            </div>
        </div>
    </div>
</div>




 
<script type="text/javascript">
jQuery(document).ready(function ($) {
$(".dataTables_wrapper select").select2({
    minimumResultsForSearch: -1
});

// Replace Checboxes
$(".pagination a").click(function (ev) {
replaceCheckboxes();
});
$('#table-4 tbody .rowcheckbox').click(function () {
    if( $(this).prop("checked")){
        $(this).parent().parent().addClass('selected');
    }else{
        $(this).parent().parent().removeClass('selected');
    }
});
$("#selectall").click(function (ev) {

    var is_checked = $(this).is(':checked');

    $('#table-4 tbody tr').each(function (i, el) {
        if(is_checked){
            $(this).find('.rowcheckbox').prop("checked",true);
            $(this).addClass('selected');
        }else{
            $(this).find('.rowcheckbox').prop("checked",false);
            $(this).removeClass('selected');
        }
    });
});
$("#vendor-trunks-submit").click(function () {
    $("#VendorTrunk-form").submit();
    return false;
});
$(".codedeckid").bind('change',function (e) {
    var prev_val = $(this).parent().find('[name="prev_codedeckid"]').val()
    var trunkid = $(this).parent().find('[name="trunkid"]').val()
    var current_obj = $(this);

    $.ajax({
            url:baseurl + '/vendor_rates/{{$id}}/delete_vendorrates', //Server script to process data
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if(response > 0){
                    changeConfirmation = confirm("Are you sure? Related Rates will be deleted");
                    if(changeConfirmation){
                        setTimeout(function() {$("#vendor-trunks-submit").button('reset'); $("#vendor-trunks-submit").button('loading')},100);
                        prev_val = current_obj.val();
                        current_obj.prop('selected', prev_val);
                        current_obj.parent().find('select.select2').select2().select2('val',prev_val);
                        //selectBox.selectOption('');
                        current_obj.parent().find('[name="codedeckid"]').val(prev_val);
                        current_obj.select2().select2('val',prev_val);
                        submit_ajax(baseurl + '/vendor_rates/{{$id}}/delete_vendorrates','Trunkid='+trunkid);
                        $("#VendorTrunk-form").submit();
                        $("#vendor-trunks-submit").button('reset');
                    }else{
                        $("#vendor-trunks-submit").button('reset');
                        current_obj.val(prev_val);
                        current_obj.prop('selected', prev_val);
                        current_obj.parent().find('select.select2').select2().select2('val',prev_val);
                    }
                }

            },
            error: function (){
                $("#vendor-trunks-submit").button('reset');
            },
            data: 'action=check_count&Trunkid='+trunkid,
            //Options to tell jQuery not to process data or worry about content-type.
            cache: false
        });
    return false;
});
@if(count($vendor_trunks) == 0 )
    $('.nav-tabs').find('a').each(function () {
        if($.trim($(this).text()) != 'Settings'){
            $(this).prop('disabled', true);
            $(this).attr('disabled', 'disabled');
        }
    });
    $('a').click(function(){
        return ($(this).attr('disabled')) ? false : true;
    });
@endif
});
</script>
@include('includes.errors')
@include('includes.success')
@stop