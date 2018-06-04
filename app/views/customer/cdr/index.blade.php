@extends('layout.customer.main')

@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="#"><i class="entypo-home"></i>@lang('routes.CUST_PANEL_PAGE_CDR_TITLE')</a>
        </li>
    </ol>
<h3>@lang('routes.CUST_PANEL_PAGE_CDR_TITLE')</h3>

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

.col-md-e1{ padding-left:8px;padding-right:8px;}
.col-md-e12{padding-left:5px;padding-right:5px; width:11%;}
</style>
<!--
<div class="row">
<div  class="col-md-12">
    <div class="input-group-btn pull-right" style="width:70px;">
        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
        <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
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
                    <div id="cdrfilter" data-collapsed="0" class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="panel-title">
                                @lang('routes.CUST_PANEL_FILTER_TITLE')
                            </div>
                            <div class="panel-options">
                                <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-md-1 control-label small_label" style="width: 8%;" for="field-1">@lang('routes.CUST_PANEL_PAGE_CDR_FILTER_FIELD_START_DATE')</label>
                                <div class="col-md-2" style="padding-right: 0px; width: 10%;">
                                    <input type="text" name="StartDate" class="form-control datepicker  small_fld"  data-date-format="yyyy-mm-dd" value="{{Input::get('StartDate')!=null?substr(Input::get('StartDate'),0,10):date('Y-m-d') }}" data-enddate="{{date('Y-m-d')}}" />
                                </div>
                                <div class="col-md-1" style="padding: 0px; width: 9%;">
                                    <input type="text" name="StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00:00" value="{{Input::get('StartDate')!=null && strlen(Input::get('StartDate'))> 10 && substr(Input::get('StartDate'),11,8) != '00:00:00'?substr(Input::get('StartDate'),11,8):'00:00:00'}}" data-show-seconds="true" data-template="dropdown" class="form-control timepicker small_fld">
                                </div>
                                <label class="col-md-1 control-label small_label" for="field-1" style="padding-left: 0px; width: 7%;">@lang('routes.CUST_PANEL_PAGE_CDR_FILTER_FIELD_END_DATE')</label>
                                <div class="col-md-2" style="padding-right: 0px; width: 9%; padding-left: 0px;">
                                    <input type="text" name="EndDate" class="form-control datepicker  small_fld"  data-date-format="yyyy-mm-dd" value="{{Input::get('EndDate')!=null?substr(Input::get('EndDate'),0,10):date('Y-m-d') }}" data-enddate="{{date('Y-m-d')}}" />
                                </div>
                                <div class="col-md-1" style="padding: 0px; width: 9%;">
                                    <input type="text" name="EndTime" data-minute-step="5" data-show-meridian="false" data-default-time="23:59:59" value="{{Input::get('EndDate')!=null && strlen(Input::get('EndDate'))> 10?substr(Input::get('EndDate'),11,2).':59:59':'23:59:59'}}" data-show-seconds="true" data-template="dropdown" class="form-control timepicker small_fld">
                                </div>
                                <label for="field-1" class="col-md-1 control-label" style="padding-left: 0px; width:5%;">@lang('routes.CUST_PANEL_PAGE_CDR_FILTER_FIELD_SHOW')</label>
                                <div class="col-md-2">
                                    <?php $options = [0=>cus_lang("DROPDOWN_OPTION_ALL"),1=>cus_lang("CUST_PANEL_PAGE_CDR_FILTER_FIELD_SHOW_DDL_ZERO_COST"),2=>cus_lang("CUST_PANEL_PAGE_CDR_FILTER_FIELD_SHOW_DDL_NON_ZERO_COST")] ?>
                                    {{ Form::select('zerovaluecost',$options,'', array("class"=>"select2 small","id"=>"bulk_AccountID",'allowClear'=>'true')) }}
                                </div>
                                <label class="col-md-1 control-label" for="field-1" style="padding-right: 0px; padding-left: 0px; width: 2%;">@lang('routes.CUST_PANEL_PAGE_CDR_FILTER_FIELD_CLI')</label>
                                <div class="col-md-2 col-md-e1" style="width: 10%;">
                                    <input type="text" name="CLI" class="form-control mid_fld "  value=""  />
                                </div>
                                <label class="col-md-1 control-label" for="field-1" style="padding-left: 0px; padding-right: 0px; width: 4%;">@lang('routes.CUST_PANEL_PAGE_CDR_FILTER_FIELD_CLD')</label>
                                <div class="col-md-2 col-md-e1" style="width: 10%;">
                                    <input type="text" name="CLD" class="form-control mid_fld  "  value=""  />
                                </div>

                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label " for="field-1" style="padding-left: 0px; padding-right: 0px; width: 4%;">@lang('routes.CUST_PANEL_PAGE_CDR_FILTER_FIELD_CDR_TYPE')</label>
                                <div class="col-md-1" style="padding-right: 0px; width: 17%;">
                                    <?php
                                        $CDRType=[
                                                ''=>cus_lang("CUST_PANEL_PAGE_CDR_FILTER_FIELD_CDR_TYPE_DLL_BOTH"),
                                                'inbound' => cus_lang("CUST_PANEL_PAGE_CDR_FILTER_FIELD_CDR_TYPE_DLL_INBOUND"),
                                                'outbound' => cus_lang("CUST_PANEL_PAGE_CDR_FILTER_FIELD_CDR_TYPE_DLL_OUTBOUND")
                                        ];
                                    ?>
                                    {{ Form::select('CDRType', $CDRType,'', array("class"=>"select2 small_fld","id"=>"bulk_AccountID",'allowClear'=>'true')) }}
                                </div>
                                <label class="col-md-1 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_CDR_FILTER_FIELD_PREFIX')</label>
                                <div class="col-md-2">
                                    <input type="text" name="area_prefix" class="form-control mid_fld "  value="{{Input::get('prefix')}}"  />
                                </div>
                                <?php
                                $trunk = Input::get('trunk');
                                if((int)Input::get('TrunkID') > 0){
                                    $trunk = Trunk::getTrunkName(Input::get('TrunkID'));
                                }
                                ?>
                                <label class="col-md-1 control-label" for="field-1">@lang('routes.CUST_PANEL_PAGE_CDR_FILTER_FIELD_TRUNK')</label>
                                <div class="col-md-2">
                                    {{ Form::select('Trunk',$trunks,$trunk, array("class"=>"select2","id"=>"bulk_AccountID",'allowClear'=>'true')) }}
                                </div>

                            </div>
                            <p class="pull-right">
                                <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit">
                                    <i class="entypo-search"></i>
                                    @lang('routes.BUTTON_SEARCH_CAPTION')
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
                        <th width="15%" class="hide">@lang('routes.CUST_PANEL_PAGE_CDR_TBL_AC_NAME')</th>
                        <th width="10%" >@lang('routes.CUST_PANEL_PAGE_CDR_TBL_CONNECT_TIME')</th>
                        <th width="10%" >@lang('routes.CUST_PANEL_PAGE_CDR_TBL_DISCONNECT_TIME')</th>
                        <th width="10%" >@lang('routes.CUST_PANEL_PAGE_CDR_TBL_BILLED_DURATION_SEC')</th>
                        <th width="10%" >@lang('routes.CUST_PANEL_PAGE_CDR_TBL_COST')</th>
                        @if($Hide_AvgRateMinute=='1')
                            <th width="10%" class="hide">@lang('routes.CUST_PANEL_PAGE_CDR_TBL_AVG_RATE_MIN')</th>
                        @else
                        <th width="10%" >@lang('routes.CUST_PANEL_PAGE_CDR_TBL_AVG_RATE_MIN')</th>
                        @endif
                        <th width="10%" >@lang('routes.CUST_PANEL_PAGE_CDR_TBL_CLI')</th>
                        <th width="10%" >@lang('routes.CUST_PANEL_PAGE_CDR_TBL_CLD')</th>
                        <th width="10%" >@lang('routes.CUST_PANEL_PAGE_CDR_TBL_PREFIX')</th>
                        <th width="10%" >@lang('routes.CUST_PANEL_PAGE_CDR_TBL_TRUNK')</th>
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
        $('input[name="StartTime"]').click();
        public_vars.$body = $("body");

        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

        $("#cdr_filter").submit(function(e) {
            e.preventDefault();
            var list_fields  =['UsageDetailID','AccountName','connect_time','disconnect_time','duration','cost','cli','cld','AccountID','CompanyGatewayID','start_date','end_date','CDRType'];
            var starttime = $("#cdr_filter [name='StartTime']").val();
            if(starttime =='00:00:01'){
                starttime = '00:00:00';
            }
            $searchFilter.StartDate 			= 		$("#cdr_filter [name='StartDate']").val();
            $searchFilter.EndDate 				= 		$("#cdr_filter [name='EndDate']").val();
            $searchFilter.CompanyGatewayID 		= 		'0';
            $searchFilter.AccountID 			= 		'{{$AccountID}}';
            $searchFilter.CDRType 				= 		$("#cdr_filter [name='CDRType']").val();			
			$searchFilter.CLI 					= 		$("#cdr_filter [name='CLI']").val();
			$searchFilter.CLD 					= 		$("#cdr_filter [name='CLD']").val();			
			$searchFilter.zerovaluecost 		= 		$("#cdr_filter [name='zerovaluecost']").val();
            $searchFilter.CurrencyID 			= 		'{{$CurrencyID}}';
            $searchFilter.area_prefix 			= 		$("#cdr_filter [name='area_prefix']").val();
            $searchFilter.Trunk 			    = 		$("#cdr_filter [name='Trunk']").val();


            if(typeof $searchFilter.StartDate  == 'undefined' || $searchFilter.StartDate.trim() == ''){
                toastr.error("@lang('routes.MESSAGE_SELECT_START_DATE')", "Error", toastr_opts);
                return false;
            }
            if(typeof $searchFilter.EndDate  == 'undefined' || $searchFilter.EndDate.trim() == ''){
                toastr.error("@lang('routes.MESSAGE_SELECT_END_DATE')", "Error", toastr_opts);
                return false;
            }
            $searchFilter.StartDate += ' '+starttime;
            $searchFilter.EndDate += ' '+$("#cdr_filter [name='EndTime']").val();

            data_table = $("#table-4").dataTable({
                "oLanguage": {
                    "sUrl": baseurl + "/translate/datatable_Label"
                },
                "bProcessing":true,
                "bDestroy": true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/customer/cdr/ajax_datagrid/type",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "fnServerParams": function(aoData) {
                    aoData.push(
                            {"name":"StartDate","value":$searchFilter.StartDate},
                            {"name":"EndDate","value":$searchFilter.EndDate},
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
                            {"name":"StartDate","value":$searchFilter.StartDate},
                            {"name":"EndDate","value":$searchFilter.EndDate},
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
                            "sButtonText": "@lang('routes.BUTTON_EXPORT_EXCEL_CAPTION')",
                            "sUrl": baseurl + "/customer/cdr/ajax_datagrid/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "@lang('routes.BUTTON_EXPORT_CSV_CAPTION')",
                            "sUrl": baseurl + "/customer/cdr/ajax_datagrid/csv",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "PDF",
                            "sUrl": baseurl + "/customer/cdr/ajax_datagrid/pdf",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "aoColumns":
                [
                    { "bVisible": false, "bSortable": false  }, //0Checkbox
                    { "bVisible": false,"bSortable": false }, //AccountName
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    {
                        @if($Hide_AvgRateMinute=='1')
                        "bVisible": false,
                        @else
                        "bSortable": false
                        @endif
                    },
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
                        $($(row).children().get(0)).html('<strong>{{cus_lang("TABLE_TOTAL")}}</strong>')
                        $($(row).children().get(2)).html('<strong>'+TotalCall+' {{cus_lang("CUST_PANEL_PAGE_INVOICE_PDF_TBL_CALLS")}}</strong>');
                        $($(row).children().get(3)).html('<strong>'+TotalDuration+' (mm:ss)</strong>');
                        $($(row).children().get(4)).html('<strong>' + CurrencyCode + TotalCost + '</strong>');
                    }else{
                        $("#table-4").find('tfoot').find('tr').html('');
                    }
                }
                });
            });

            if (isxs()|| is('tabletscreen')) {
                $('#cdrfilter').find('.col-md-1,.col-md-2').each(function () {
                    $(this).removeAttr('style');
                    $(this).removeClass("small_label");
                });
                $('#cdrfilter').find('.small_fld').each(function () {
                    $(this).removeClass("small_fld");
                });
            }

            });

</script>
<style>
.dataTables_filter label{
    /*display:none !important;*/
}
</style>
@stop
