@extends('layout.reseller.main')

@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="#"><i class="entypo-home"></i>CDR</a>
        </li>
    </ol>
<h3>CDR</h3>

@include('includes.errors')
@include('includes.success')

<!--<p style="text-align: right;">
    <a href="javascript:void(0)" id="cdr_rerate" class="btn btn-primary hidden">
        <i class="entypo-check"></i>
        <span>CDR Rerate</span>
    </a>
</p>-->
<style>
.small_fld{width:80.6667%;}
.small_label{width:5.0%;}

.col-sm-e1{ padding-left:8px;padding-right:8px;}
.col-sm-e12{padding-left:5px;padding-right:5px; width:11%;}
</style>
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
    <!--<li class="active">
        <a href="{{ URL::to('cdr_show') }}" >
            <span class="hidden-xs">Customer CDR</span>
        </a>
    </li>-->
    <!--<li>
        <a href="{{ URL::to('/vendorcdr_show') }}" >
            <span class="hidden-xs">Vendor CDR</span>
        </a>
    </li>-->
</ul>
<div class="tab-content" style="padding:0;">
    <div class="tab-pane active">
        <div class="row">
            <div class="col-md-12">
                <form novalidate class="form-horizontal form-groups-bordered validate" method="post" id="cdr_filter">
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
                                <label class="col-sm-1 control-label small_label" style="width: 9%;" for="field-1">Start Date</label>
                                <div class="col-sm-2" style="padding-right: 0px;">
                                    <input type="text" name="StartDate" class="form-control datepicker small_fld"  data-date-format="yyyy-mm-dd" value="" data-enddate="{{date('Y-m-d')}}" />
                                </div>
                                <div class="col-sm-2" style="padding: 0px; width: 15%;">
                                    <input type="text" name="StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00:01" data-show-seconds="true" data-template="dropdown" class="form-control timepicker small_fld">
                                </div>
                                <label class="col-sm-1 control-label small_label" for="field-1" style="padding-left: 0px; width: 7%;">End Date</label>
                                <div class="col-sm-2" style="padding-right: 0px; width: 15%; padding-left: 0px;">
                                    <input type="text" name="EndDate" class="form-control datepicker small_fld"  data-date-format="yyyy-mm-dd" value="" data-enddate="{{date('Y-m-d')}}" />
                                </div>
                                <div class="col-sm-2">
                                    <input type="text" name="EndTime" data-minute-step="5" data-show-meridian="false" data-default-time="23:59:59" value="23:59:59" data-show-seconds="true" data-template="dropdown" class="form-control timepicker small_fld">
                                </div>
                                <label for="field-1" class="col-sm-1 control-label" style="padding-left: 0px; width: 8%;">Hide Zero Cost</label>
                                <div class="col-sm-1">
                                    <p class="make-switch switch-small">
                                        <input id="zerovaluecost" name="zerovaluecost" type="checkbox">
                                    </p>
                                </div>
                            </div>
                            <div class="form-group">
                            <!--    <label class="col-sm-1 control-label" for="field-1">Gateway</label>
                                <div class="col-sm-2">
                                    {{ Form::select('CompanyGatewayID',$gateway,'', array("class"=>"select2","id"=>"bluk_CompanyGatewayID")) }}
                                </div>
                                <label class="col-sm-1 control-label" for="field-1">Account</label>
                                <div class="col-sm-2">
                                    {{ Form::select('AccountID',array(''=>'Select an Account'),'', array("class"=>"select2","id"=>"bulk_AccountID",'allowClear'=>'true')) }}
                                </div>-->
                                <label class="col-sm-1 control-label " for="field-1">CDR Type</label>
                                <div class="col-sm-2" style="padding-right: 0px; width: 18%;">
                                    {{ Form::select('CDRType',array(''=>'Both',1 => "Inbound", 0 => "Outbound" ),'', array("class"=>"select2 small_fld","id"=>"bulk_AccountID",'allowClear'=>'true')) }}
                                </div>
                                <label class="col-sm-1 control-label" for="field-1" style="padding-right: 0px; padding-left: 0px; width: 4%;">CLI</label>
                                <div class="col-sm-2 col-sm-e1" style="width: 10%;">
                                    <input type="text" name="CLI" class="form-control mid_fld "  value=""  />
                                 </div>
                                 <label class="col-sm-1 control-label" for="field-1" style="padding-left: 0px; padding-right: 0px; width: 4%;">CLD</label>
                                 <div class="col-sm-2 col-sm-e1" style="width: 10%;">
                                    <input type="text" name="CLD" class="form-control mid_fld  "  value=""  />
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
                        <th width="10%" >Billed Duration</th>
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
            $searchFilter.StartDate 			= 		$("#cdr_filter [name='StartDate']").val();
            $searchFilter.EndDate 				= 		$("#cdr_filter [name='EndDate']").val();
            $searchFilter.CompanyGatewayID 		= 		'';
            $searchFilter.AccountID 			= 		'';
            $searchFilter.CDRType 				= 		$("#cdr_filter [name='CDRType']").val();			
			$searchFilter.CLI 					= 		$("#cdr_filter [name='CLI']").val();
			$searchFilter.CLD 					= 		$("#cdr_filter [name='CLD']").val();			
			$searchFilter.zerovaluecost 		= 		$("#cdr_filter [name='zerovaluecost']").prop("checked");
			
			
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
                "sAjaxSource": baseurl + "/reseller/cdr/ajax_datagrid/type",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"StartDate","value":$searchFilter.StartDate},{"name":"EndDate","value":$searchFilter.EndDate},{"name":"CompanyGatewayID","value":$searchFilter.CompanyGatewayID},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"CDRType","value":$searchFilter.CDRType},{"name":"CLI","value":$searchFilter.CLI},{"name":"CLD","value":$searchFilter.CLD},{"name":"zerovaluecost","value":$searchFilter.zerovaluecost});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"StartDate","value":$searchFilter.StartDate},{"name":"EndDate","value":$searchFilter.EndDate},{"name":"CompanyGatewayID","value":$searchFilter.CompanyGatewayID},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"CDRType","value":$searchFilter.CDRType},{"name":"Export","value":1},{"name":"CLI","value":$searchFilter.CLI},{"name":"CLD","value":$searchFilter.CLD},{"name":"zerovaluecost","value":$searchFilter.zerovaluecost});
                },
                "sPaginationType": "bootstrap",
                "aaSorting"   : [[0, 'asc']],
                "oTableTools":
                {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/reseller/cdr/ajax_datagrid/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/reseller/cdr/ajax_datagrid/csv",
                            sButtonClass: "save-collection btn-sm"
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
                    get_total_grand(); //get result total
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });

                }
                });
            });

            function get_total_grand(){
                $.ajax({
                    url: baseurl + "/reseller/cdr/ajax_datagrid_total",
                    type: 'GET',
                    dataType: 'json',
                    data:{
                        "StartDate" : $("#cdr_filter [name='StartDate']").val()+' '+$("#cdr_filter [name='StartTime']").val(),
                        "EndDate" : $("#cdr_filter [name='EndDate']").val()+' '+$("#cdr_filter [name='EndTime']").val(),
                        "CompanyGatewayID" : '',
                        "AccountID" : '',
                        "CDRType" : $("#cdr_filter [name='CDRType']").val(),
                        "CLI" : $("#cdr_filter [name='CLI']").val(),
                        "CLD" : $("#cdr_filter [name='CLD']").val(),
                        "zerovaluecost" : $("#cdr_filter [name='zerovaluecost']").prop("checked"),
                        "bDestroy": true,
                        "bProcessing":true,
                        "bServerSide":true,
                        "sAjaxSource": baseurl + "/reseller/cdr/ajax_datagrid/type",
                        "iDisplayLength": '{{Config::get('app.pageSize')}}',
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[0, 'asc']],},
                    success: function(response1) {
                        console.log("sum of result"+response1);
                        if(response1.total_duration!=null)
                        {
                            $('.result_row').remove();
                            $('.result_row').hide();
                            $('#table-4 tbody').append('<tr class="result_row"><td><strong>Total</strong></td><td align="right" colspan="2"></td><td><strong>'+response1.total_duration+'</strong></td><td><strong>'+response1.total_cost+'</strong></td><td></td></tr>');
                        }
                    }
                });
            }

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
