@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Retention</strong>
    </li>
</ol>
<h3>Retention</h3>

@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">
    @if( User::checkCategoryPermission('Retention','Add'))
        <button class="btn add-new-retention btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
@endif
</p>

<div class="row">
<div class="col-md-12">
<form id="retention-form" method="post" class="form-horizontal form-groups-bordered validate">
<div class="panel panel-primary" data-collapsed="0">
<div class="panel-heading" style="min-height: 55px;">
                    <div class="panel-title" style="min-height: 55px;">
                        Data Retention (Days)
                        <br><span class="small">Blank (do not delete)</span>
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">

    <div class="form-group">
        <label for="field-1" class="col-sm-2 control-label">CDR : </label>
        <div class="col-sm-2">
            <input type="text" name="TableData[CDR]" class="form-control" value="{{isset($DataRetenion->CDR)?$DataRetenion->CDR:''}}"/>
        </div>
        <div class="col-sm-8"></div>
    </div>
    <div class="form-group">
        <label for="field-1" class="col-sm-2 control-label">CDR Failed Calls: </label>
        <div class="col-sm-2">
            <input type="text" name="TableData[CDRFailedCalls]" class="form-control" value="{{isset($DataRetenion->CDRFailedCalls)?$DataRetenion->CDRFailedCalls:''}}"/>
        </div>
        <div class="col-sm-8"></div>
    </div>
    <div class="form-group">
        <label for="field-1" class="col-sm-2 control-label">Cron Job History :</label>
        <div class="col-sm-2">
            <input type="text" name="TableData[Cronjob]" class="form-control" value="{{isset($DataRetenion->Cronjob)?$DataRetenion->Cronjob:''}}"/>
        </div>
        <div class="col-sm-8"></div>
    </div>
    <div class="form-group">
        <label for="field-1" class="col-sm-2 control-label">Job :</label>
        <div class="col-sm-2">
            <input type="text" name="TableData[Job]" class="form-control" value="{{isset($DataRetenion->Job)?$DataRetenion->Job:''}}"/>
        </div>
        <div class="col-sm-8"></div>
    </div>
    <div class="form-group">
        <label for="field-1" class="col-sm-2 control-label">Customer Rate Sheet Download History : </label>
        <div class="col-sm-2">
            <input type="text" name="TableData[CustomerRateSheet]" class="form-control" value="{{isset($DataRetenion->CustomerRateSheet)?$DataRetenion->CustomerRateSheet:''}}"/>
        </div>
        <div class="col-sm-8"></div>
    </div>
    <div class="form-group">
        <label for="field-1" class="col-sm-2 control-label">Vendor Rate Sheet Upload/Download History : </label>
        <div class="col-sm-2">
            <input type="text" name="TableData[VendorRateSheet]" class="form-control" value="{{isset($DataRetenion->VendorRateSheet)?$DataRetenion->VendorRateSheet:''}}"/>
        </div>
        <div class="col-sm-8"></div>
    </div>

    <div class="form-group">
        <label for="field-1" class="col-sm-2 control-label">Tickets : </label>
        <div class="col-sm-2">
            <input type="text" name="TableData[DeleteTickets]" class="form-control" value="{{isset($DataRetenion->DeleteTickets)?$DataRetenion->DeleteTickets:''}}"/>
        </div>
        <div class="col-sm-8"></div>
    </div>
    <div class="form-group">
        <label for="field-1" class="col-sm-2 control-label">Delete archived rates : </label>
        <div class="col-sm-2">
            <input type="text" name="TableData[ArchiveOldRate]" class="form-control" value="{{isset($DataRetenion->ArchiveOldRate)?$DataRetenion->ArchiveOldRate:''}}"/>
        </div>
        <div class="col-sm-8"></div>
    </div>

</div>
</div>
@if(CronJob::checkCDRDownloadFiles())
    <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
            <div class="panel-title">
                File Retention (Days)
            </div>
            <div class="panel-options">
                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
            </div>
        </div>
        <div class="panel-body">

            <div class="form-group">
                <label for="field-1" class="col-sm-2 control-label">CDR Files : </label>
                <div class="col-sm-2">
                    <input type="text" name="FileData[CDR]" class="form-control" value="{{isset($FileRetenion->CDR)?$FileRetenion->CDR:''}}"/>
                </div>
                <div class="col-sm-8"></div>
            </div>

            <div class="form-group">
                <label for="field-1" class="col-sm-2 control-label">Delete CDR Files From Server:</label>
                <div class="col-sm-2">
                    <p class="make-switch switch-small">
                        <input name="FileData[CDRFilesDelete]" type="checkbox" value="1" @if(isset($FileRetenion->CDRFilesDelete) && $FileRetenion->CDRFilesDelete==1) checked @endif>
                    </p>
                </div>
                <div class="col-sm-8"></div>
            </div>

            @if(is_amazon())

                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Move CDR Files To Amazon S3:</label>
                    <div class="col-sm-2">
                        <p class="make-switch switch-small">
                            <input name="FileData[CDRMoves]" type="checkbox" value="1" @if(isset($FileRetenion->CDRMoves) && $FileRetenion->CDRMoves==1) checked @endif>
                        </p>
                    </div>
                    <div class="col-sm-8"></div>
                </div>

            @endif

        </div>
    </div>

    @endif

</form>
</div>
</div>

<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
       $(".add-new-retention.btn").click(function(e){
            e.preventDefault();
            $(this).button('loading');
            var url = baseurl + '/retention/create';
            var data = $('#retention-form').serialize();
            $.ajax({
                url:url, //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $(".add-new-retention.btn").button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        //location.reload();
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                data: data,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
            });
       });
</script>
<style>
.dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
    display:none !important;
}
</style>
@stop