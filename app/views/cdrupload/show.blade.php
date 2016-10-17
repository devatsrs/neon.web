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
<div class="tab-content" style="padding:0;">
    <div class="tab-pane active">
        <div class="row">
            <div class="col-md-12">
                <form novalidate class="form-horizontal form-groups-bordered validate" method="post" id="cdr_filter">
                    <div id="cdrfilter" data-collapsed="0" class="panel panel-primary filter">
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
                                <div class="col-sm-2" style="padding-left:0; padding-right:0; width:10%;">
                                    <input type="text" name="StartDate" class="form-control datepicker small_fld"  data-date-format="yyyy-mm-dd" value="{{Input::get('StartDate')!=null?Input::get('StartDate'):'' }}" data-enddate="{{Input::get('StartDate')!=null?Input::get('StartDate'):date('Y-m-d') }}" />
                                </div>
                                <div class="col-sm-1" style="padding: 0px; width: 9%;">
                                    <input type="text" name="StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00:01" data-show-seconds="true" data-template="dropdown" class="form-control timepicker small_fld">
                                </div>
                                <label class="col-sm-1 control-label small_label" for="field-1" style="padding-left: 0px; width: 7%;">End Date</label>
                                <div class="col-sm-2" style="padding-right: 0px; padding-left: 0px; width: 10%;">
                                    <input type="text" name="EndDate" class="form-control datepicker small_fld"  data-date-format="yyyy-mm-dd" value="{{Input::get('EndDate')!=null?Input::get('EndDate'):'' }}" data-enddate="{{Input::get('EndDate')!=null?Input::get('EndDate'):date('Y-m-d') }}" />
                                </div>
                                <div class="col-sm-1" style="padding: 0px; width: 9%;">
                                    <input type="text" name="EndTime" data-minute-step="5" data-show-meridian="false" data-default-time="23:59:59" value="23:59:59" data-show-seconds="true" data-template="dropdown" class="form-control timepicker small_fld">
                                </div>
                                <label for="field-1" class="col-sm-2 control-label" style="width: 6%;">Currency</label>
                                <div class="col-sm-2">
                                    {{Form::select('CurrencyID',Currency::getCurrencyDropdownIDList(),(Input::get('CurrencyID')>0?Input::get('CurrencyID'):$DefaultCurrencyID),array("class"=>"select2 small"))}}
                                </div>
                                <label class="col-sm-1 control-label small_label" for="field-1">Type</label>
                                <div class="col-sm-2" style="padding-right: 0px; width: 14%;">
                                    {{ Form::select('CDRType',array(''=>'Both',1 => "Inbound", 0 => "Outbound" ),'', array("class"=>"select2 small small_fld","id"=>"bulk_AccountID",'allowClear'=>'true')) }}
                                </div>
             
                            </div>
                            <div class="form-group">
                                <label class="col-sm-1 control-label" for="field-1">Gateway</label>
                                <div class="col-sm-2">
                                    {{ Form::select('CompanyGatewayID',$gateway,Input::get('CompanyGatewayID'), array("class"=>"select2","id"=>"bluk_CompanyGatewayID")) }}
                                </div>
                                <label class="col-sm-1 control-label" for="field-1">Account</label>
                                <div class="col-sm-2">
                                    {{ Form::select('AccountID',$accounts,Input::get('AccountID'), array("class"=>"select2","id"=>"bulk_AccountID",'allowClear'=>'true')) }}
                                </div>

                                         
                            
                               <label class="col-sm-1 control-label" for="field-1" style="padding-right: 0px; padding-left: 0px; width: 4%;">CLI</label>
                               <div class="col-sm-1 col-sm-e1" style="width: 10%;">
                                    <input type="text" name="CLI" class="form-control mid_fld "  value=""  />
                                </div>
                                 <label class="col-sm-1 control-label" for="field-1" style="padding-left: 0px; padding-right: 0px; width: 4%;">CLD</label>
                               <div class="col-sm-1 col-sm-e1" style="width: 10%;">
                                    <input type="text" name="CLD" class="form-control mid_fld  "  value=""  />
                                </div>

                                <label for="field-1" class="col-sm-1 control-label" style="width: 4%;">Show</label>
                                <div class="col-sm-2">
                                    <?php $options = [0=>'All',1=>'Zero Cost',2=>'Non Zero Cost'] ?>
                                    {{ Form::select('zerovaluecost',$options,'', array("class"=>"select2 small","id"=>"bulk_AccountID",'allowClear'=>'true')) }}
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
        <p style="text-align: right;">
            @if(User::checkCategoryPermission('CDR','Delete') )
                <button id="delete-customer-cdr" class="btn btn-danger btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-cancel"></i> Delete</button>
            @endif
            <form id="delete-customer-cdr-form" >
                <input type="hidden" name="UsageDetailIDs" />
                <input type="hidden" name="criteria" />
            </form>
        </p>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered datatable" id="table-4">
                    <thead>
                    <tr>
                        <th width="5%" >
                            <div class="checkbox ">
                                <input type="checkbox" id="selectall" name="checkbox[]" />
                            </div>
                        </th>
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
var currency_symbol = '';
var $searchFilter = {};
var update_new_url;
var postdata;
var checked='';
var TotalCall = 0;
var TotalDuration = 0;
var TotalCost = 0;
var CurrencyCode = '';
var rate_cdr = jQuery.parseJSON('{{json_encode($rate_cdr)}}');
    jQuery(document).ready(function ($) {
        $('input[name="StartTime"]').click();
        public_vars.$body = $("body");

        $('#bluk_CompanyGatewayID').change(function(e){
            if($(this).val()){
                $('#cdr_rerate').removeClass('hidden');
            }else{
                $('#cdr_rerate').addClass('hidden');
            }
        });
        $('#bluk_CompanyGatewayID').trigger('change');
        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

        $("#cdr_filter").submit(function(e) {
            e.preventDefault();
			$('.result_tr_end').remove();
            var list_fields  =['UsageDetailID','AccountName','connect_time','disconnect_time','duration','cost','cli','cld','AccountID','CompanyGatewayID','start_date','end_date','CDRType'];

            var starttime = $("#cdr_filter [name='StartTime']").val();
            if(starttime =='00:00:01'){
                starttime = '00:00:00';
            }

            $searchFilter.StartDate 			= 		$("#cdr_filter [name='StartDate']").val();
            $searchFilter.EndDate 				= 		$("#cdr_filter [name='EndDate']").val();
            $searchFilter.CompanyGatewayID 		= 		$("#cdr_filter [name='CompanyGatewayID']").val();
            $searchFilter.AccountID 			= 		$("#cdr_filter [name='AccountID']").val();
            $searchFilter.CDRType 				= 		$("#cdr_filter [name='CDRType']").val();			
			$searchFilter.CLI 					= 		$("#cdr_filter [name='CLI']").val();
			$searchFilter.CLD 					= 		$("#cdr_filter [name='CLD']").val();			
			$searchFilter.zerovaluecost 		= 		$("#cdr_filter [name='zerovaluecost']").val();
			$searchFilter.CurrencyID 			= 		$("#cdr_filter [name='CurrencyID']").val();
            $searchFilter.area_prefix 			= 		$("#cdr_filter [name='area_prefix']").val();
            $searchFilter.Trunk 			    = 		$("#cdr_filter [name='Trunk']").val();


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
                "sAjaxSource": baseurl + "/cdr_upload/ajax_datagrid/type",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
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
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/cdr_upload/ajax_datagrid/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/cdr_upload/ajax_datagrid/csv",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "aoColumns":
                [
                    {"bSortable": false,
                        mRender: function(id, type, full) {
                            return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                        }
                    }, //0Checkbox
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false },
                    { "bSortable": false }
                ],
                "fnDrawCallback": function() {
					$(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });

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

                    //select all button
                    $('#table-4 tbody tr').each(function(i, el) {
                        if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                            if (checked != '') {
                                $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                $(this).addClass('selected');
                                $('#selectallbutton').prop("checked", true);
                            } else {
                                $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                $(this).removeClass('selected');
                            }
                        }
                    });

                    $('#selectallbutton').click(function(ev) {
                        if($(this).is(':checked')){
                            checked = 'checked=checked disabled';
                            $("#selectall").prop("checked", true).prop('disabled', true);
                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                $('#table-4 tbody tr').each(function(i, el) {
                                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                        $(this).addClass('selected');
                                    }
                                });
                            }
                        }else{
                            checked = '';
                            $("#selectall").prop("checked", false).prop('disabled', false);
                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                $('#table-4 tbody tr').each(function(i, el) {
                                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                        $(this).removeClass('selected');
                                    }
                                });
                            }
                        }
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
                        for (var i = 0; i < 8; i++) {
                            var a = document.createElement('td');
                            $(a).html('');
                            $(row).append(a);
                        }
                        $($(row).children().get(0)).html('<strong>Total</strong>')
                        $($(row).children().get(3)).html('<strong>'+TotalCall+' Calls</strong>');
                        $($(row).children().get(4)).html('<strong>'+TotalDuration+' (mm:ss)</strong>');
                        $($(row).children().get(5)).html('<strong>' + CurrencyCode + TotalCost + '</strong>');
                    }else{
                        $("#table-4").find('tfoot').find('tr').html('');
                    }
                }
                });
                $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
            });

            $('#table-4 tbody').on('click', 'tr', function() {
                if (checked =='') {
                    $(this).toggleClass('selected');
                    if ($(this).hasClass('selected')) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                    }
                }
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

        $("#delete-customer-cdr").click(function(e) {
            e.preventDefault();
            var criteria='';
            if($('#selectallbutton').is(':checked')){
                criteria = JSON.stringify($searchFilter);
            }
            var UsageDetailIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                //console.log($(this).val());
                UsageDetailID = $(this).val();
                if(typeof UsageDetailID != 'undefined' && UsageDetailID != null && UsageDetailID != 'null'){
                    UsageDetailIDs[i++] = UsageDetailID;
                }
            });

            if(UsageDetailIDs.length){
                if (!confirm('Are you sure you want to delete cdr?')) {
                    return;
                }

                $("#delete-customer-cdr-form").find("input[name='UsageDetailIDs']").val(UsageDetailIDs.join(","));
                $("#delete-customer-cdr-form").find("input[name='criteria']").val(criteria);


                var formData = new FormData($('#delete-customer-cdr-form')[0]);
                $(this).button('loading');
                $.ajax({
                    url: baseurl + '/cdr_upload/delete_customer_cdr',
                    type: 'POST',
                    error: function () {
                        $('#delete-customer-cdr').button('reset');
                        toastr.error("error", "Error", toastr_opts);
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 'success') {
                            $('#delete-customer-cdr').button('reset');
                            toastr.success(response.message, "Success", toastr_opts);
                            data_table.fnFilter('', 0);
                        } else {
                            $('#delete-customer-cdr').button('reset');
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false
                });

            }else{
                alert("Please select cdr.");
                return false;
            }



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
.dataTables_wrapper .export-data{
    right: 30px !important;
}
#selectcheckbox{
    padding: 15px 10px;
}
</style>
@stop
