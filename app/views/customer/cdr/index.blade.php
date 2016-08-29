@extends('layout.customer.main')

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
                                <label class="col-sm-1 control-label small_label" style="width: 9%;" for="field-1">Date</label>
                                <div class="col-sm-3">
                                    <input type="text" name="DateRange" value="{{Input::get('StartDate')?Input::get('StartDate').' 00:00:00'.' - '.Input::get('EndDate').' 23:59:59':date('Y-m-d').' 00:00:00'.' - '.date('Y-m-d').' 23:59:59'}}" data-format="YYYY-MM-DD HH:mm:ss" data-start-date="{{Input::get('StartDate')?Input::get('StartDate'):date('Y-m-d')}}" data-end-date="{{Input::get('EndDate')?Input::get('EndDate').'23:59:59':date('Y-m-d').'23:59:59'}}" data-time-picker-increment="1" data-time-picker="true" data-time-picker24hour="true" class="form-control daterange active"  data-max-date="{{date('Y-m-d',strtotime('+1 day'))}}">
                                </div>
                                <label for="field-1" class="col-sm-1 control-label" style="padding-left: 0px; width: 8%;">Hide Zero Cost</label>
                                <div class="col-sm-1">
                                    <p class="make-switch switch-small">
                                        <input id="zerovaluecost" name="zerovaluecost" type="checkbox">
                                    </p>
                                </div>
                                <label class="col-sm-1 control-label" for="field-1" style="padding-right: 0px; padding-left: 0px; width: 4%;">CLI</label>
                                <div class="col-sm-2 col-sm-e1" style="width: 10%;">
                                    <input type="text" name="CLI" class="form-control mid_fld "  value=""  />
                                </div>
                                <label class="col-sm-1 control-label" for="field-1" style="padding-left: 0px; padding-right: 0px; width: 4%;">CLD</label>
                                <div class="col-sm-2 col-sm-e1" style="width: 10%;">
                                    <input type="text" name="CLD" class="form-control mid_fld  "  value=""  />
                                </div>
                                <label class="col-sm-1 control-label " for="field-1" style="padding-left: 0px; padding-right: 0px; width: 4%;">CDR Type</label>
                                <div class="col-sm-1" style="padding-right: 0px; width: 17%;">
                                    {{ Form::select('CDRType',array(''=>'Both',1 => "Inbound", 0 => "Outbound" ),'', array("class"=>"select2 small_fld","id"=>"bulk_AccountID",'allowClear'=>'true')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-1 control-label" for="field-1">Prefix</label>
                                <div class="col-sm-2">
                                    <input type="text" name="area_prefix" class="form-control mid_fld "  value="{{Input::get('prefix')}}"  />
                                </div>
                                <?php
                                $trunk = Input::get('trunk');
                                if((int)Input::get('TrunkID') > 0){
                                    $trunk = Trunk::getTrunkName(Input::get('TrunkID'));
                                }
                                ?>
                                <label class="col-sm-1 control-label" for="field-1">Trunk</label>
                                <div class="col-sm-2">
                                    {{ Form::select('Trunk',$trunks,$trunk, array("class"=>"select2","id"=>"bulk_AccountID",'allowClear'=>'true')) }}
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
                        <th width="5%" class="hide"></th>
                        <th width="15%" >Account Name</th>
                        <th width="10%" >Connect Time</th>
                        <th width="10%" >Disconnect Time</th>
                        <th width="10%" >Billed Duration (sec)</th>
                        <th width="10%" >Cost</th>
                        <th width="10%" >CLI</th>
                        <th width="10%" >CLD</th>
                        <th width="10%" >Prefix</th>
                        <th width="10%" >Trunk</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
var TotalCall = 0;
var TotalDuration = 0;
var TotalCost = 0;
var CurrencyCode = '';
var rate_cdr = jQuery.parseJSON('{{json_encode($rate_cdr)}}');
    jQuery(document).ready(function ($) {

        public_vars.$body = $("body");

        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

        $("#cdr_filter").submit(function(e) {
            e.preventDefault();
            var list_fields  =['UsageDetailID','AccountName','connect_time','disconnect_time','duration','cost','cli','cld','AccountID','CompanyGatewayID','start_date','end_date','CDRType'];

            $searchFilter.DateRange 			= 		$("#cdr_filter [name='DateRange']").val();
            $searchFilter.CompanyGatewayID 		= 		'0';
            $searchFilter.AccountID 			= 		'{{$AccountID}}';
            $searchFilter.CDRType 				= 		$("#cdr_filter [name='CDRType']").val();			
			$searchFilter.CLI 					= 		$("#cdr_filter [name='CLI']").val();
			$searchFilter.CLD 					= 		$("#cdr_filter [name='CLD']").val();			
			$searchFilter.zerovaluecost 		= 		$("#cdr_filter [name='zerovaluecost']").prop("checked");
            $searchFilter.CurrencyID 			= 		'{{$CurrencyID}}';
            $searchFilter.area_prefix 			= 		$("#cdr_filter [name='area_prefix']").val();
            $searchFilter.Trunk 			    = 		$("#cdr_filter [name='Trunk']").val();


            if(typeof $searchFilter.DateRange  == 'undefined' || $searchFilter.DateRange.trim() == ''){
                toastr.error("Please Select a Date Range", "Error", toastr_opts);
                return false;
            }
            data_table = $("#table-4").dataTable({

                "bProcessing":true,
                "bDestroy": true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/customer/cdr/ajax_datagrid/type",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                "fnServerParams": function(aoData) {
                    aoData.push(
                            {"name":"DateRange","value":$searchFilter.DateRange},
                            {"name":"CompanyGatewayID","value":$searchFilter.CompanyGatewayID},
                            {"name":"AccountID","value":$searchFilter.AccountID},
                            {"name":"CDRType","value":$searchFilter.CDRType},
                            {"name":"CLI","value":$searchFilter.CLI},
                            {"name":"CLD","value":$searchFilter.CLD},
                            {"name":"zerovaluecost","value":$searchFilter.zerovaluecost},
                            {"name":"area_prefix","value":$searchFilter.area_prefix},
                            {"name":"Trunk","value":$searchFilter.Trunk},
                            {"name":"CurrencyID","value":$searchFilter.CurrencyID}
                    );
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push(
                            {"name":"DateRange","value":$searchFilter.DateRange},
                            {"name":"CompanyGatewayID","value":$searchFilter.CompanyGatewayID},
                            {"name":"AccountID","value":$searchFilter.AccountID},
                            {"name":"CDRType","value":$searchFilter.CDRType},
                            {"name":"Export","value":1},
                            {"name":"CLI","value":$searchFilter.CLI},
                            {"name":"CLD","value":$searchFilter.CLD},
                            {"name":"zerovaluecost","value":$searchFilter.zerovaluecost},
                            {"name":"area_prefix","value":$searchFilter.area_prefix},
                            {"name":"Trunk","value":$searchFilter.Trunk},
                            {"name":"CurrencyID","value":$searchFilter.CurrencyID}
                    );
                },
                "sPaginationType": "bootstrap",
                "aaSorting"   : [[0, 'asc']],
                "oTableTools":
                {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/customer/cdr/ajax_datagrid/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/customer/cdr/ajax_datagrid/csv",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "aoColumns":
                [
                    { "bVisible": false, "bSortable": false  }, //0Checkbox
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false } /*,
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

                },
                "fnServerData": function ( sSource, aoData, fnCallback ) {
                    /* Add some extra data to the sender */
                    $.getJSON( sSource, aoData, function (json) {
                        /* Do whatever additional processing you want on the callback, then tell DataTables */
                        TotalCall = json.Total.totalcount;
                        TotalDuration = json.Total.total_duration;
                        TotalCost = json.Total.total_cost;
                        CurrencyCode = json.Total.CurrencyCode != null? json.Total.CurrencyCode : '';
                        fnCallback(json)
                    });
                },
                "fnFooterCallback": function ( row, data, start, end, display ) {
                    if (end > 0) {
                        $(row).html('');
                        for (var i = 0; i < 7; i++) {
                            var a = document.createElement('td');
                            $(a).html('');
                            $(row).append(a);
                        }
                        $($(row).children().get(0)).html('<strong>Total</strong>')
                        $($(row).children().get(2)).html('<strong>'+TotalCall+' Calls</strong>');
                        $($(row).children().get(3)).html('<strong>'+TotalDuration+' (mm:ss)</strong>');
                        $($(row).children().get(4)).html('<strong>' + CurrencyCode + TotalCost + '</strong>');
                    }else{
                        $("#table-4").find('tfoot').find('tr').html('');
                    }
                }
                });
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
