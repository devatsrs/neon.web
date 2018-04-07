{{--@if(User::checkCategoryPermission('VendorRates','History'))--}}
<li class="pull-right" style="margin-right: 10px;">
    <button class="btn btn-primary btn-sm btn-icon icon-left" onclick="location.href='{{ URL::to('/rate_upload/'.$id.'/'.RateUpload::vendor) }}'">
        <i class="fa fa-upload"></i>
        Upload Rates
    </button>
</li>
{{--@endif--}}