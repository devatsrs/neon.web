@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a>CDR</a>
    </li>
    <li class="active">
        <strong>Customer CDR</strong>
    </li>
</ol>
<h3>Customer CDR</h3>

@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">
    <a href="javascript:void(0)" id="cdr_rerate" class="btn btn-primary hidden">
        <i class="entypo-check"></i>
        <span>CDR Rerate</span>
    </a>
</p>
<!--
<div class="row">
<div  class="col-md-12">
    <div class="input-group-btn pull-right" style="width:70px;">
        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
        <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #1f232a; border-color: #1f232a; margin-top:0px;">
            <li><a class="generate_rate create" id="bulk_clear_cdr" href="javascript:;" style="width:100%">
                    Bulk clear
                </a>
            </li>
        </ul>

    </div><!-- /btn-group -->
<!--</div>
<div class="clear"></div>
</div>-->
<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
    <li class="active">
        <a href="{{ URL::to('cdr_show') }}" >
            <span class="hidden-xs">Customer CDR</span>
        </a>
    </li>
    <li>
        <a href="{{ URL::to('/vendorcdr_show') }}" >
            <span class="hidden-xs">Vendor CDR</span>
        </a>
    </li>
</ul>
<div class="tab-content">
    <div class="tab-pane active">
        <div class="row">
            <div class="col-md-12">
                <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="post" id="cdr_filter">
                    <div data-collapsed="0" class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Filter
                            </div>
                            <div class="panel-options">
                                <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="field-1">Start Date</label>
                                <div class="col-sm-2">
                                    <input type="text" name="StartDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="" data-enddate="{{date('Y-m-d',strtotime(" -1 day"))}}" />
                                </div>
                                <div class="col-sm-2">
                                    <input type="text" name="StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00:01" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
                                </div>
                                <label class="col-sm-2 control-label" for="field-1">End Date</label>
                                <div class="col-sm-2">
                                    <input type="text" name="EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="" data-enddate="{{date('Y-m-d')}}" />
                                </div>
                                <div class="col-sm-2">
                                    <input type="text" name="EndTime" data-minute-step="5" data-show-meridian="false" data-default-time="23:59:59" value="23:59:59" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="field-1">Gateway</label>
                                <div class="col-sm-2">
                                    {{ Form::select('CompanyGatewayID',$gateway,'', array("class"=>"select2","id"=>"bluk_CompanyGatewayID")) }}
                                </div>
                                <label class="col-sm-2 control-label" for="field-1">Account</label>
                                <div class="col-sm-2">
                                    {{ Form::select('AccountID',array(''=>'Select an Account'),'', array("class"=>"select2","id"=>"bulk_AccountID",'allowClear'=>'true')) }}
                                </div>
                                <label class="col-sm-2 control-label" for="field-1">CDR Type</label>
                                <div class="col-sm-2">
                                    {{ Form::select('CDRType',array(''=>'Both',1 => "Inbound", 0 => "OutBound" ),'', array("class"=>"select2","id"=>"bulk_AccountID",'allowClear'=>'true')) }}
                                </div>

                            </div>
                            <p style="text-align: right;">
                                <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit">
                                    <i class="entypo-search"></i>
                                    Search
                                </button>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered datatable" id="table-4">
                    <thead>
                    <tr>
                        <th width="15%" >Account Name</th>
                        <th width="10%" >Connect Time</th>
                        <th width="10%" >Disconnect Time</th>
                        <th width="10%" >Duration</th>
                        <th width="10%" >Cost</th>
                        <th width="10%" >CLI</th>
                        <th width="10%" >CLD</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
var rate_cdr = jQuery.parseJSON('{{json_encode($rate_cdr)}}');
    jQuery(document).ready(function ($) {
        $('input[name="StartTime"]').click();
        public_vars.$body = $("body");

        $('#bluk_CompanyGatewayID').change(function(e){
        if($(this).val()){
            $.ajax({
                url:  baseurl +'/cdr_upload/get_accounts/'+$(this).val(),  //Server script to process data
                type: 'POST',
                success: function (response) {
                $('#bulk_AccountID').empty();
                $('#bulk_AccountID').append(response);
                setTimeout(function(){
                    $("#bulk_AccountID").select2('val','');
                },200)
                },
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
            });
            $.each(rate_cdr, function(key, value) {
                 if(key == $('#bluk_CompanyGatewayID').val()){
                    if(value == 1){
                        $('#cdr_rerate').removeClass('hidden')
                    }else{
                        $('#cdr_rerate').addClass('hidden')
                    }
                 }
            });
        }
        });
        $('#bluk_CompanyGatewayID').trigger('change');
        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

        $("#cdr_filter").submit(function(e) {
            e.preventDefault();
            var list_fields  =['AccountName','connect_time','disconnect_time','duration','cost','cli','cld','AccountID','CompanyGatewayID','start_date','end_date','CDRType'];
            var starttime = $("#cdr_filter [name='StartTime']").val();
            if(starttime =='0:00:01'){
                starttime = '0:00:00';
            }
            $searchFilter.StartDate = $("#cdr_filter [name='StartDate']").val();
            $searchFilter.EndDate = $("#cdr_filter [name='EndDate']").val();
            $searchFilter.CompanyGatewayID = $("#cdr_filter [name='CompanyGatewayID']").val();
            $searchFilter.AccountID = $("#cdr_filter [name='AccountID']").val();
            $searchFilter.CDRType = $("#cdr_filter [name='CDRType']").val();
            if(typeof $searchFilter.StartDate  == 'undefined' || $searchFilter.StartDate.trim() == ''){
               toastr.error("Please Select a Start date", "Error", toastr_opts);
               return false;
            }
            if(typeof $searchFilter.EndDate  == 'undefined' || $searchFilter.EndDate.trim() == ''){
               toastr.error("Please Select a End date", "Error", toastr_opts);
               return false;
            }
            $searchFilter.StartDate += ' '+starttime;
            $searchFilter.EndDate += ' '+$("#cdr_filter [name='EndTime']").val();
            data_table = $("#table-4").dataTable({

                "bProcessing":true,
                "bDestroy": true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/cdr_upload/ajax_datagrid",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"StartDate","value":$searchFilter.StartDate},{"name":"EndDate","value":$searchFilter.EndDate},{"name":"CompanyGatewayID","value":$searchFilter.CompanyGatewayID},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"CDRType","value":$searchFilter.CDRType});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"StartDate","value":$searchFilter.StartDate},{"name":"EndDate","value":$searchFilter.EndDate},{"name":"CompanyGatewayID","value":$searchFilter.CompanyGatewayID},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"CDRType","value":$searchFilter.CDRType},{"name":"Export","value":1});
                },
                "sPaginationType": "bootstrap",
                "aaSorting"   : [[0, 'asc']],
                "oTableTools":
                {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "Export Data",
                            "sUrl": baseurl + "/cdr_upload/ajax_datagrid",
                            sButtonClass: "save-collection"
                        }
                    ]
                },
                "aoColumns":
                [
                    { "bSortable": true },
                    { "bSortable": true },
                    { "bSortable": true },
                    { "bSortable": true },
                    { "bSortable": true },
                    { "bSortable": true },
                    { "bSortable": true } /*,
                         { mRender: function(id, type, full) {
                             action = '<div class = "hiddenRowData" >';
                             for(var i = 0 ; i< list_fields.length; i++){
                                                         action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                             }
                              action += '</div>';

                             action += ' <button class="btn clear delete_cdr btn-danger btn-sm btn-icon icon-left" data-loading-text="Loading..."><i class="entypo-cancel"></i>Clear CDR</button>';

                             return action;
                             }*/
                ],
                "fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });

                }
                });
            });
            $('table tbody').on('click', '.delete_cdr', function (e) {
                response = confirm('Are you sure?');
                if (response) {
                    submit_ajax(baseurl + "/cdr_upload/delete_cdr",$(this).prev("div.hiddenRowData").find("input").serialize())
                }
            });
            $('#bulk_clear_cdr').on('click',function (e) {
                if(typeof $searchFilter.StartDate  == 'undefined' || $searchFilter.StartDate.trim() == ''){
                   toastr.error("Please Select a Start date then search", "Error", toastr_opts);
                   return false;
                }
                if(typeof $searchFilter.EndDate  == 'undefined' || $searchFilter.EndDate.trim() == ''){
                   toastr.error("Please Select a End date then search", "Error", toastr_opts);
                   return false;
                }
                response = confirm('Are you sure?');
                if (response) {
                   submit_ajax(baseurl + "/cdr_upload/delete_cdr",$.param($searchFilter))
                }
            });
            $('#cdr_rerate').on('click',function (e) {
                if(typeof $searchFilter.StartDate  == 'undefined' || $searchFilter.StartDate.trim() == ''){
                   toastr.error("Please Select a Start date then search", "Error", toastr_opts);
                   return false;
                }
                if(typeof $searchFilter.EndDate  == 'undefined' || $searchFilter.EndDate.trim() == ''){
                   toastr.error("Please Select a End date then search", "Error", toastr_opts);
                   return false;
                }
                if(typeof $searchFilter.CompanyGatewayID  == 'undefined' || $searchFilter.CompanyGatewayID.trim() == ''){
                   toastr.error("Please Select a Gateway then search", "Error", toastr_opts);
                   return false;
                }
                if($("#table-4 tbody tr").html().indexOf("No data available in table") > 0){
                    toastr.error("No data available To ReRate", "Error", toastr_opts);
                    return false;
                }
                response = confirm('Are you sure?');
                if (response) {
                   submit_ajax(baseurl + "/rate_cdr",$.param($searchFilter))
                }
            });


            });

</script>
<style>
.dataTables_filter label{
    /*display:none !important;*/
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}
</style>
@stop
