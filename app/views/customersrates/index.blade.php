@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <a href="{{URL::to('accounts')}}">Accounts</a>
    </li>
    <li>
        <a><span>{{customer_dropbox($id,["IsCustomer"=>1])}}</span></a>
    </li>
    <li class="active">
        <strong>Customer Rate</strong>
    </li>
</ol>

<h3>Customer Rate</h3>
@include('accounts.errormessage')
<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
    <li class="active">
        <a href="{{ URL::to('/customers_rates/'.$id) }}" >
            Customer Rate
        </a>
    </li>
    @if(User::checkCategoryPermission('CustomersRates','Settings'))
    <li  >
        <a href="{{ URL::to('/customers_rates/settings/'.$id) }}" >
            Settings
        </a>
    </li>
    @endif
    @if(User::checkCategoryPermission('CustomersRates','Download'))
    <li>
        <a href="{{ URL::to('/customers_rates/'.$id.'/download') }}" >
            Download Rate Sheet
        </a>
    </li>
    @endif
    @if(User::checkCategoryPermission('CustomersRates','History'))
    <li>
        <a href="{{ URL::to('/customers_rates/'.$id.'/history') }}" >
            History
        </a>
    </li>
    @endif
</ul>

<div class="tab-content">
    <div class="tab-pane active" id="customer_rate_tab_content">




        <div class="row">
            <div class="col-md-12">
                <form role="form" id="customer-rate-table-search" method="post"  action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                   <div class="panel panel-primary" data-collapsed="0">
                       <div class="panel-heading">
                           <div class="panel-title">
                               Search
                           </div>

                           <div class="panel-options">
                               <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                           </div>
                       </div>

                       <div class="panel-body">
                           <div class="form-group">
                               <label for="field-1" class="col-sm-1 control-label">Code</label>
                               <div class="col-sm-2">
                                   <input type="text" name="Code" class="form-control" id="field-1" placeholder="" value="{{Input::get('Code')}}" />
                               </div>

                               <label class="col-sm-1 control-label">Description</label>
                               <div class="col-sm-2">
                                   <input type="text" name="Description" class="form-control" id="field-1" placeholder="" value="{{Input::get('Description')}}" />

                               </div>

                               <label for="field-1" class="col-sm-1 control-label">Discontinued Codes</label>
                               <div class="col-sm-2">
                                   <p class="make-switch switch-small">
                                       {{Form::checkbox('DiscontinuedRates', '1', false, array("id"=>"DiscontinuedRates"))}}
                                   </p>
                               </div>

                              <label class="col-sm-2 control-label EffectiveBox">Show Applied Rates</label>
                               <div class="col-sm-1 EffectiveBox">
                                   <input id="Effected_Rates_on_off" class="icheck" name="Effected_Rates_on_off" type="checkbox" value="1" >
                               </div>

                           </div>
                           <div class="form-group">
                               <label for="field-1" class="col-sm-1 control-label">Country</label>
                               <div class="col-sm-2">
                                   {{ Form::select('Country', $countries, Input::get('Country') , array("class"=>"select2")) }}
                               </div>

                               <label for="field-1" class="col-sm-1 control-label">Trunk</label>
                               <div class="col-sm-2">
                                   {{ Form::select('Trunk', $trunks, $trunk_keys, array("class"=>"select2",'id'=>'ct_trunk')) }}
                               </div>

                               <label for="field-1" class="col-sm-1 control-label EffectiveBox">Effective</label>
                               <div class="col-sm-2 EffectiveBox">
                                   <select name="Effective" class="select2 small" data-allow-clear="true" data-placeholder="Select Effective">
                                       <option value="Now">Now</option>
                                       <option value="Future">Future</option>
                                       <option value="All">All</option>
                                       <option value="CustomDate">Custom Date</option>
                                   </select>
                               </div>

                               <label for="field-1" class="col-sm-1 control-label EffectiveBox CustomDateBox" style="display: none">Custom Date</label>
                               <div class="col-sm-2 EffectiveBox CustomDateBox" style="display: none">
                                   <input type="text" name="CustomDate" data-date-format="yyyy-mm-dd" placeholder="{{date('Y-m-d')}}" data-startdate="{{date('Y-m-d')}}" value="{{date('Y-m-d')}}" class="form-control datepicker">
                               </div>

                           </div>
                           <div class="form-group">

                               <label class="col-sm-1 control-label">Timezone</label>
                               <div class="col-sm-3">
                                   {{ Form::select('Timezones', $Timezones, '', array("class"=>"select2")) }}
                               </div>

                               <label for="field-1" class="col-sm-1 control-label RoutinePlan">Routing Plan</label>
                               <div class="col-sm-3">
                                   {{ Form::select('RoutinePlanFilter', $trunks_routing, '', array("class"=>"select2 RoutinePlan")) }}
                               </div>
                           </div>



                           <p style="text-align: right;">
                               <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                                   <i class="entypo-search"></i>
                                   Search
                               </button>
                           </p>
                       </div>
                   </div>
               </form>
            </div>
        </div>
        <div class="clear"></div>

        {{--<div class="row">
            <div class="col-md-12">
                <blockquote class="blockquote-red" style="padding: 6px;">
                    --}}{{--<p> <strong>Note</strong> </p>--}}{{--
                    <p> <small>In order to offer new rates use 'Bulk New Offer' OR 'New Offer Selected'.  In order to update rates use 'Update Selected Rates' OR 'Bulk Update'. To Delete rates use 'Bulk Clear' OR 'Clear Selected Rates'.</small> </p>
                </blockquote>
            </div>
        </div>--}}
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger" style="padding: 6px;">
                    In order to offer new rates use 'Bulk New Offer' OR 'New Offer Selected'.  In order to update rates use 'Update Selected Rates' OR 'Bulk Update'. To Delete rates use 'Bulk Clear' OR 'Clear Selected Rates'.
                </div>
            </div>
        </div>

        <div class="clear"></div>
        <div class="row">
         <div  class="col-md-12">
                <div class="input-group-btn pull-right" style="width:76px;" id="btn-action">
                    @if( User::checkCategoryPermission('CustomersRates','Edit,ClearRate'))
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                        @if(User::checkCategoryPermission('CustomersRates','Edit'))
                            <li>
                                <a class="generate_rate create" id="addSelectedCustomerRates" href="javascript:;" >
                                    New Offer Selected
                                </a>
                            </li>
                            <li>
                                <a class="generate_rate create" id="insertBulkCustomerRates" href="javascript:;" >
                                    Bulk New Offer
                                </a>
                            </li>
                            <li>
                                <a class="generate_rate create" id="changeSelectedCustomerRates" href="javascript:;" >
                                    Update Selected Rates
                                </a>
                            </li>
                            <li>
                                <a class="generate_rate create" id="bulk_set_cust_rate" href="javascript:;" style="width:100%">
                                    Bulk Update
                                </a>
                            </li>
                        @endif
                        @if(User::checkCategoryPermission('CustomersRates','ClearRate'))
                        <li><a class="generate_rate create" id="clear-bulk-rate" href="javascript:;" style="width:100%">
                                Clear Selected Rates
                            </a></li>
                        <li><a class="generate_rate create" id="bulk_clear_cust_rate" href="javascript:;" style="width:100%">
                                Bulk Clear
                            </a></li>
                        @endif
                    </ul>
                    @endif
                    <form id="clear-bulk-rate-form" >
                        <input type="hidden" name="CustomerRateID" value="">
                        <input type="hidden" name="TrunkID" value="">
                        <input type="hidden" name="TimezonesID" value="">
                    </form>
                </div><!-- /btn-group -->
                 {{--@if( User::checkCategoryPermission('CustomersRates','Create'))
                     <button id="add-new-rate" class="btn btn-primary btn-icon icon-left pull-right">
                         <i class="fa fa-plus"></i>
                         Add New
                     </button>
                 @endif--}}
         </div>
            <div class="clear"></div>
            </div>
        <br>

        <table class="table table-bordered datatable" id="table-4">
            <thead>
                <tr>
                    <th width="2%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
                    <th width="5%">Code</th>
                    <th width="20%">Description</th>
                    <th width="5%">Interval 1</th>
                    <th width="5%">Interval N</th>
                    <th width="5%">Connection Fee</th>
                    <th width="5%" class="routng_plan_cl">Routing plan</th>
                    <th width="5%">Rate1 ({{$CurrencySymbol}})</th>
                    <th width="5%">RateN ({{$CurrencySymbol}})</th>
                    <th width="8%">Effective Date</th>
                    <th width="8%" class="hidden">End Date</th>
                    <th width="8%">Modified Date</th>
                    <th width="8%">Modified By</th>
                    <th width="20%">Action</th>
                </tr>
            </thead>
            <tbody>



            </tbody>
        </table>
        <script type="text/javascript">
            var $searchFilter = {};
            var checked='';
            var update_new_url;
            var first_call = true;
            var list_fields  = ['RateID','Code','Description','Interval1','IntervalN','ConnectionFee','RoutinePlanName','Rate','RateN','EffectiveDate','EndDate','LastModifiedDate','LastModifiedBy','CustomerRateId','TrunkID','RateTableRateId'];
            var routinejson ='{{json_encode($routine)}}';
                    jQuery(document).ready(function($) {
                        checkrouting($("#customer-rate-table-search select[name='Trunk']").val());
                        //var data_table;

                        //$searchFilter.Code = $("#customer-rate-table-search input[name='Code']").val();
                        //$searchFilter.Description = $("#customer-rate-table-search input[name='Description']").val();
                        //$searchFilter.Country = $("#customer-rate-table-search select[name='Country']").val();
                        //$searchFilter.Trunk = $("#customer-rate-table-search select[name='Trunk']").val();
                        //$searchFilter.Effective = $("#customer-rate-table-search select[name='Effective']").val();
                        //$searchFilter.RoutinePlan = $("#customer-rate-table-search select[name='RoutinePlan']").val();

                        $("#customer-rate-table-search").submit(function(e) {

                            e.preventDefault();
                            $searchFilter.Code = $("#customer-rate-table-search input[name='Code']").val();
                            $searchFilter.Description = $("#customer-rate-table-search input[name='Description']").val();
                            $searchFilter.Country = $("#customer-rate-table-search select[name='Country']").val();
                            $searchFilter.Trunk = $("#customer-rate-table-search select[name='Trunk']").val();
                            $searchFilter.Effective = $("#customer-rate-table-search select[name='Effective']").val();
                            $searchFilter.CustomDate = $("#customer-rate-table-search input[name='CustomDate']").val();
                            $searchFilter.Effected_Rates_on_off = $("#customer-rate-table-search input[name='Effected_Rates_on_off']").prop("checked");
                            $searchFilter.RoutinePlanFilter = $("#customer-rate-table-search select[name='RoutinePlanFilter']").val();
                            $searchFilter.DiscontinuedRates = DiscontinuedRates = $("#customer-rate-table-search input[name='DiscontinuedRates']").is(':checked') ? 1 : 0;
                            $searchFilter.Timezones = Timezones = $("#customer-rate-table-search select[name='Timezones']").val();

                            if($searchFilter.Trunk == '' || typeof $searchFilter.Trunk  == 'undefined'){
                               toastr.error("Please Select a Trunk", "Error", toastr_opts);
                               return false;
                            }

                            data_table = $("#table-4").DataTable({
                                "bDestroy": true, // Destroy when resubmit form
                                "bProcessing": true,
                                "bServerSide": true,
                                "sAjaxSource": baseurl + "/customers_rates/{{$id}}/search_ajax_datagrid/type",
                                "fnServerParams": function(aoData) {
                                    aoData.push({"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country}, {"name": "Trunk", "value": $searchFilter.Trunk}, {"name": "Effective", "value": $searchFilter.Effective},{"name": "Effected_Rates_on_off", "value": $searchFilter.Effected_Rates_on_off},{"name": "RoutinePlanFilter", "value": $searchFilter.RoutinePlanFilter}, {"name": "DiscontinuedRates", "value": DiscontinuedRates}, {"name": "CustomDate", "value": $searchFilter.CustomDate}, {"name": "Timezones", "value": $searchFilter.Timezones});
                                    data_table_extra_params.length = 0;
                                    data_table_extra_params.push({"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country}, {"name": "Trunk", "value": $searchFilter.Trunk}, {"name": "Effective", "value": $searchFilter.Effective},{"name": "RoutinePlanFilter", "value": $searchFilter.RoutinePlanFilter},{"name":"Export","value":1},{"name": "Effected_Rates_on_off", "value": $searchFilter.Effected_Rates_on_off}, {"name": "DiscontinuedRates", "value": DiscontinuedRates}, {"name": "CustomDate", "value": $searchFilter.CustomDate}, {"name": "Timezones", "value": $searchFilter.Timezones});
                                },
                                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                                "sPaginationType": "bootstrap",
                                 "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                                 "aaSorting": [[9, "asc"]],
                                 "aoColumns":
                                        [
                                            {"bSortable": false, //RateID
                                                mRender: function(id, type, full) {
                                                    return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                                                }
                                            }, //0Checkbox
                                            {}, //1 Code
                                            {}, //2Description
                                            {}, //3Interval1
                                            {}, //4IntervalN
                                            {}, //5 ConnectionFee
                                            {}, //6RoutinePlanName
                                            {}, //7Rate
                                            {}, //8RateN
                                            {}, //9Effective Date
                                            {"bVisible": false}, //10End Date
                                            {}, //11LastModifiedDate
                                            {}, //12LastModifiedBy
                                            {// 13 CustomerRateId
                                                mRender: function(id, type, full) {
                                                    var action, edit_, delete_;
                                                    edit_ = "{{ URL::to('/customers_rates/{id}/edit')}}";
                                                    RateID = full[0];
                                                    //Trunk = $("#customer-rate-table-search select[name='Trunk']").val();

                                                    edit_ = edit_.replace('{id}', id);

                                                    CustomerRateID  = id;
                                                    RateID = full[0];

                                                    Rate = ( full[7] == null )? 0:full[7];
                                                    Interval1 = ( full[3] == null )? 1:full[3];
                                                    IntervalN = ( full[4] == null )? 1:full[4];
                                                    RoutinePlan = ( full[6] == null )? '':full[6];

                                                    date = new Date();
                                                    var month = date.getMonth()+1;
                                                    var day = date.getDate();
                                                    currentDate = date.getFullYear() + '-' +   (month<10 ? '0' : '') + month + '-' +     (day<10 ? '0' : '') + day;


                                                    if( full[7] == null ) EffectiveDate = currentDate;
                                                    else EffectiveDate = full[7];

                                                    clerRate_ = "{{ URL::to('/customers_rates/clear_rate')}}/"+CustomerRateID;


                                                    action = '<div class = "hiddenRowData" >';
                                                    for(var i = 0 ; i< list_fields.length; i++){
                                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                                    }
                                                    action += '</div>';
                                                    if (CustomerRateID > 0) {
                                                        <?php if(User::checkCategoryPermission('CustomersRates','Edit')) { ?>
                                                        if(DiscontinuedRates == 0) {
                                                            action += ' <a href="Javascript:;" class="edit-customer-rate btn btn-default btn-xs"><i class="entypo-pencil"></i>&nbsp;</a>';
                                                        }
                                                        <?php } ?>
                                                        action += ' <a href="Javascript:;" title="History" class="btn btn-default btn-xs btn-history details-control"><i class="entypo-back-in-time"></i>&nbsp;</a>';
                                                        <?php if(User::checkCategoryPermission('CustomersRates','ClearRate')) { ?>
                                                            if(DiscontinuedRates == 0) {
                                                                action += ' <button href="' + clerRate_ + '"  class="btn clear-customer-rate btn-danger btn-xs btn-icon icon-left" data-loading-text="Loading..."><i class="entypo-cancel"></i>Clear Rate</button>';
                                                            }
                                                        <?php } ?>
                                                    }
                                                    return action;
                                                }
                                            },
                                        ],
                                        "oTableTools":
                                        {
                                            "aButtons": [
                                                {
                                                    "sExtends": "download",
                                                    "sButtonText": "EXCEL",
                                                    "sUrl": baseurl + "/customers_rates/{{$id}}/search_ajax_datagrid/xlsx",
                                                    sButtonClass: "save-collection btn-sm"
                                                },
                                                {
                                                    "sExtends": "download",
                                                    "sButtonText": "CSV",
                                                    "sUrl": baseurl + "/customers_rates/{{$id}}/search_ajax_datagrid/csv",
                                                    sButtonClass: "save-collection btn-sm"
                                                }
                                            ]
                                        },
                                "fnDrawCallback": function() {
                                    checkrouting($searchFilter.Trunk);

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

                                    //Edit Button
                                    $(".edit-customer-rate.btn").click(function(ev) {
                                        ev.stopPropagation();

                                        var cur_obj = $(this).prev("div.hiddenRowData");
                                        for(var i = 0 ; i< list_fields.length; i++){

                                            $("#edit-customer-rate-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                                            if(list_fields[i] == 'RoutinePlanName'){
                                                RoutinePlan = cur_obj.find("input[name='"+list_fields[i]+"']").val();
                                            }
                                            if(list_fields[i] == 'EffectiveDate'){
                                                EffectiveDate = cur_obj.find("input[name='"+list_fields[i]+"']").val();
                                            }
                                        }
                                        var RoutinePlanval ='';
                                        RoutinePlanval = $("#ct_trunk option:contains('"+RoutinePlan+"')").attr('value');

                                        date = new Date();
                                        var month = date.getMonth()+1;
                                        var day = date.getDate();
                                        currentDate = date.getFullYear() + '-' +   (month<10 ? '0' : '') + month + '-' +     (day<10 ? '0' : '') + day;
                                        if(EffectiveDate < currentDate){
                                            EffectiveDate = currentDate;
                                        }


                                        $("#edit-customer-rate-form").find("input[name='EffectiveDate']").val(EffectiveDate);
                                        $("#edit-customer-rate-form").find("input[name='Trunk']").val($searchFilter.Trunk);
                                        $("#edit-customer-rate-form").find("input[name='TimezonesID']").val($searchFilter.Timezones);

                                        $("#edit-customer-rate-form [name='RoutinePlan']").select2().select2('val',RoutinePlanval);
                                        var display_routine = false;
                                        if(typeof routinejson != 'undefined' && routinejson != ''){
                                            $.each($.parseJSON(routinejson), function(key,value){
                                                if(key!= '' && $searchFilter.Trunk != ''  && key == $searchFilter.Trunk){
                                                    display_routine = true;
                                                }
                                            });
                                        }
                                        if(display_routine ==  true){
                                            $('#modal-CustomerRate .RoutinePlan-modal').show();
                                        }else{
                                            $('#modal-CustomerRate .RoutinePlan-modal').hide();
                                        }

                                        jQuery('#modal-CustomerRate').modal('show', {backdrop: 'static'});
                                    });

                                    $(".dataTables_wrapper select").select2({
                                        minimumResultsForSearch: -1
                                    });

                                    //select all records
                                    $('#table-4 tbody tr').each(function(i, el) {
                                        if (checked!='') {
                                            $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                            $(this).addClass('selected');
                                            $('#selectallbutton').prop("checked", true);
                                        } else {
                                            $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);;
                                            $(this).removeClass('selected');
                                        }
                                    });

                                    $('#selectallbutton').click(function(ev) {
                                        if($(this).is(':checked')){
                                            checked = 'checked=checked disabled';
                                            $("#selectall").prop("checked", true).prop('disabled', true);
                                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                                $('#table-4 tbody tr').each(function(i, el) {
                                                    $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                                    $(this).addClass('selected');
                                                });
                                            }
                                        }else{
                                            checked = '';
                                            $("#selectall").prop("checked", false).prop('disabled', false);
                                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                                $('#table-4 tbody tr').each(function(i, el) {
                                                    $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                                    $(this).removeClass('selected');
                                                });
                                            }
                                        }
                                    });

                                    if(DiscontinuedRates == 1) {//if(Effective == 'All' || DiscontinuedRates == 1) {
                                        $('#btn-action').hide();
                                    } else {
                                        $('#btn-action').show();
                                    }
                                }
                            });
                            $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
                            @if(count($trunks_routing) ==0 || count($routine)  == 0)
                                $("#table-4 td:nth-child(7)").hide();
                            @endif

                        });
                        $("#ct_trunk").change(function(ev) {
                            currentval = $(this).val();
                            checkrouting(currentval);
                        });


                        $('#table-4 tbody').on('click', 'tr', function() {
                            if(!$(this).hasClass('no-selection')) {
                                if (checked == '') {
                                    $(this).toggleClass('selected');
                                    if ($(this).hasClass('selected')) {
                                        $(this).find('.rowcheckbox').prop("checked", true);
                                    } else {
                                        $(this).find('.rowcheckbox').prop("checked", false);
                                    }
                                }
                            }
                        });
                        $('#table-5 tbody').on('click', 'tr', function() {
                            $(this).toggleClass('selected');
                            if ($(this).hasClass('selected')) {
                                $(this).find('.rowcheckbox').prop("checked", true);
                            } else {
                                $(this).find('.rowcheckbox').prop("checked", false);
                            }
                        });
                         $('#table-6 tbody,#table-7 tbody').on('click', 'tr', function() {
                            $(this).toggleClass('selected');
                            if ($(this).hasClass('selected')) {
                                $(this).find('.rowcheckbox').prop("checked", true);
                            } else {
                                $(this).find('.rowcheckbox').prop("checked", false);
                            }
                        });

                        //Edit Form Submit
                        $("#edit-customer-rate-form,#bulk-edit-customer-rate-form").submit(function() {

                            var formData = new FormData($(this)[0]);
                            $.ajax({
                                url: baseurl + '/customers_rates/update/{{$id}}', //Server script to process data
                                type: 'POST',
                                dataType: 'json',
                                success: function(response) {
                                    $(".save.btn").button('reset');

                                    if (response.status == 'success') {
                                        $("#modal-CustomerRate").modal("hide");
                                        $("#modal-BulkCustomerRate").modal("hide");
                                        toastr.success(response.message, "Success", toastr_opts);
                                        $("#customer-rate-table-search").submit();
                                    } else {
                                        toastr.error(response.message, "Error", toastr_opts);
                                    }
                                },
                                error: function(error) {
                                    $("#modal-CustomerRate").modal("hide");
                                    $("#modal-BulkCustomerRate").modal("hide");
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

                        //Add selected Form Submit
                        $("#add-selected-customer-rate-form").submit(function() {

                            var formData = new FormData($(this)[0]);
                            $.ajax({
                                url: baseurl + '/customers_rates/add_selected_customer_rate/{{$id}}', //Server script to process data
                                type: 'POST',
                                dataType: 'json',
                                success: function(response) {
                                    $(".save.btn").button('reset');

                                    if (response.status == 'success') {
                                        $("#add-selected-customer-rate-modal").modal("hide");
                                        toastr.success(response.message, "Success", toastr_opts);
                                        $("#customer-rate-table-search").submit();
                                    } else {
                                        toastr.error(response.message, "Error", toastr_opts);
                                    }
                                },
                                error: function(error) {
                                    $("#add-selected-customer-rate-modal").modal("hide");
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

                        // Replace Checboxes
                        $(".pagination a").click(function(ev) {
                            replaceCheckboxes();
                        });



                //Bulk Edit Button
                $("#changeSelectedCustomerRates").click(function(ev) {
                    if($('#selectallbutton').is(':checked')){
                        $( "#bulk_set_cust_rate" ).trigger( "click" );
                    }else{
                        var RateIDs = [];
                        var CustomerRateIDs = [];
                        var i = 0;
                        $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                            RateID = $(this).val();
                            RateIDs[i] = RateID;
                            CustomerRateID = $(this).closest('tr').find('td div.hiddenRowData input[name="CustomerRateId"]').val();
                            CustomerRateIDs[i] = CustomerRateID;
                            i++;
                        });
                        //Trunk = $("#customer-rate-table-search").find("select[name='Trunk']").val();
                        $("#bulk-edit-customer-rate-form").find("input[name='RateID']").val(RateIDs.join(","));
                        $("#bulk-edit-customer-rate-form").find("input[name='CustomerRateId']").val(CustomerRateIDs.join(","));
                        $("#bulk-edit-customer-rate-form").find("input[name='Trunk']").val($searchFilter.Trunk);
                        $("#bulk-edit-customer-rate-form").find("input[name='TimezonesID']").val($searchFilter.Timezones);

                        $("#bulk-edit-customer-rate-form")[0].reset();
                        $("#bulk-edit-customer-rate-form [name='Interval1']").val(1);
                        $("#bulk-edit-customer-rate-form [name='IntervalN']").val(1);
                        $("#bulk-edit-customer-rate-form [name='RoutinePlan']").select2().select2('val','');
                        /*date = new Date();
                        var month = date.getMonth()+1;
                        var day = date.getDate();
                        currentDate = date.getFullYear() + '-' +   (month<10 ? '0' : '') + month + '-' +     (day<10 ? '0' : '') + day;
                        $("#bulk-edit-customer-rate-form [name='EffectiveDate']").val(currentDate);*/
                        /*$("#bulk-edit-customer-rate-form").find("input[name='EffectiveDate']").val("");
                         $("#bulk-edit-customer-rate-form").find("input[name='Rate']").val("");
                         $("#bulk-edit-customer-rate-form").find("input[name='Interval1']").val("");
                         $("#bulk-edit-customer-rate-form").find("input[name='IntervalN']").val("");*/

                        CustomerRateIDs = CustomerRateIDs.filter(Boolean);
                        if(CustomerRateIDs.length){
                            var display_routine = false;
                            if(typeof routinejson != 'undefined' && routinejson != ''){
                                $.each($.parseJSON(routinejson), function(key,value){
                                    if(key!= '' && $searchFilter.Trunk != ''  && key == $searchFilter.Trunk){
                                        display_routine = true;
                                    }
                                });
                            }
                            if(display_routine ==  true){
                                $('#modal-BulkCustomerRate .RoutinePlan-modal').show();
                            }else{
                                $('#modal-BulkCustomerRate .RoutinePlan-modal').hide();
                            }
                            $('#modal-BulkCustomerRate').modal('show', {backdrop: 'static'});
                        }

                    initCustomerGrid('table-6');
                    }
                });
                //Add selected rates
                $("#addSelectedCustomerRates").click(function(ev) {
                    if($('#selectallbutton').is(':checked')){
                        $( "#insertBulkCustomerRates" ).trigger( "click" );
                    }else{
                        var RateIDs = [];
                        var i = 0;
                        $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                            RateID = $(this).val();
                            RateIDs[i] = RateID;
                            i++;
                        });
                        //Trunk = $("#customer-rate-table-search").find("select[name='Trunk']").val();
                        $("#add-selected-customer-rate-form").find("input[name='RateID']").val(RateIDs.join(","));
                        $("#add-selected-customer-rate-form").find("input[name='Trunk']").val($searchFilter.Trunk);
                        $("#add-selected-customer-rate-form").find("input[name='TimezonesID']").val($searchFilter.Timezones);

                        $("#add-selected-customer-rate-form")[0].reset();
                        $("#add-selected-customer-rate-form [name='Interval1']").val(1);
                        $("#add-selected-customer-rate-form [name='IntervalN']").val(1);
                        $("#add-selected-customer-rate-form [name='RoutinePlan']").select2().select2('val','');

                        if(RateIDs.length){
                            var display_routine = false;
                            if(typeof routinejson != 'undefined' && routinejson != ''){
                                $.each($.parseJSON(routinejson), function(key,value){
                                    if(key!= '' && $searchFilter.Trunk != ''  && key == $searchFilter.Trunk){
                                        display_routine = true;
                                    }
                                });
                            }
                            // Routine Plan dropdown in modal show/hide condition
                            if(display_routine ==  true){
                                $('#add-selected-customer-rate-modal .RoutinePlan-modal').show();
                            }else{
                                $('#add-selected-customer-rate-modal .RoutinePlan-modal').hide();
                            }
                            $('#add-selected-customer-rate-modal').modal('show', {backdrop: 'static'});
                        }

                        initCustomerGrid('table-7');
                    }
                });
                $("#account_owners").change(function(ev) {
                    var account_owners = $(this).val();
                    if(account_owners!=""){
                        initCustomerGrid('table-5',account_owners);
                    }else if(first_call ==false ){
                        initCustomerGrid('table-5','');
                    }
                    first_call = false;
                    //$('#table-5_filter').remove();
                });
                $("#account_owners_6").change(function(ev) {
                var account_owners = $(this).val();
                    if(account_owners!=""){
                        initCustomerGrid('table-6',account_owners);
                    }else if(first_call ==false ){
                        initCustomerGrid('table-6','');
                    }
                    first_call = false;
                });
                $("#account_owners_7").change(function(ev) {
                    var account_owners = $(this).val();
                    if(account_owners!=""){
                        initCustomerGrid('table-7',account_owners);
                    }else if(first_call ==false ){
                        initCustomerGrid('table-7','');
                    }
                    first_call = false;
                });


                //Bulk Clear Button
                $("#clearSelectedCustomerRates").click(function(ev) {


                    var RateIDs = [];
                    var i = 0;
                    $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                        RateID = $(this).val();
                        RateIDs[i++] = RateID;
                    });

                    $("#clearSelectedCustomerRates-form").find("input[name='RateID']").val(RateIDs.join(","));
                    $("#clearSelectedCustomerRates-form").submit();

                });

                // Replace Checboxes
                $(".pagination a").click(function(ev) {
                    replaceCheckboxes();
                });

                $("#bulk_set_cust_rate,#bulk_clear_cust_rate,#insertBulkCustomerRates").click(function(ev) {
                    var self = $(this);
                    var search_html='<div class="row">';
                    var col_count=1;
                    if($searchFilter.Code != ''){
                        search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Code</label><div class=""><p class="form-control-static" >'+$searchFilter.Code+'</p></div></div></div>';
                        col_count++;
                    }
                    if($searchFilter.Country != ''){
                        search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Country</label><div class=""><p class="form-control-static" >'+$("#customer-rate-table-search select[name='Country']").find("[value='"+$searchFilter.Country+"']").text()+'</p></div></div></div>';
                        col_count++;
                        if(col_count == 3){
                            search_html +='</div><div class="row">';
                            col_count=1;
                        }
                    }
                    if($searchFilter.Description != ''){
                        search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Description</label><div class=""><p class="form-control-static" >'+$searchFilter.Description+'</p></div></div></div>';
                        col_count++;
                        if(col_count == 3){
                            search_html +='</div><div class="row">';
                            col_count=1;
                        }
                    }
                    if(self.attr('id')=='bulk_set_cust_rate') {
                        if ($searchFilter.Effective != '') {
                            search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Effective</label><div class=""><p class="form-control-static" >' + $searchFilter.Effective + '</p></div></div></div>';
                            col_count++;
                            if (col_count == 3) {
                                search_html += '</div><div class="row">';
                                col_count = 1;
                            }
                        }
                        if ($searchFilter.Effective == 'CustomDate') {
                            search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Custom Date</label><div class=""><p class="form-control-static" >' + $searchFilter.CustomDate + '</p></div></div></div>';
                            col_count++;
                            if (col_count == 3) {
                                search_html += '</div><div class="row">';
                                col_count = 1;
                            }
                        }
                    }
                    if($searchFilter.Trunk != ''){
                        search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Trunk</label><div class=""><p class="form-control-static" >'+$("#customer-rate-table-search select[name='Trunk']").find("[value='"+$searchFilter.Trunk+"']").text()+'</p></div></div></div>';
                        col_count++;
                    }
                    if($searchFilter.Timezones != ''){
                        search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Timezones</label><div class=""><p class="form-control-static" >'+$("#customer-rate-table-search select[name='Timezones']").find("[value='"+$searchFilter.Timezones+"']").text()+'</p></div></div></div>';
                        col_count++;
                    }
                    search_html+='</div>';
                    $("#search_static_val").html(search_html);

                    if($searchFilter.Trunk == '' || typeof $searchFilter.Trunk  == 'undefined'){
                       toastr.error("Please Select a Trunk then Click Search", "Error", toastr_opts);
                       return false;
                    }

                    var display_routine = false;
                    if(typeof routinejson != 'undefined' && routinejson != ''){
                        $.each($.parseJSON(routinejson), function(key,value){
                            if(key!= '' && $searchFilter.Trunk != ''  && key == $searchFilter.Trunk){
                                display_routine = true;
                            }
                        });
                    }
                    if(display_routine ==  true){
                        $('#modal-BulkCustomerRate-new .RoutinePlan-modal').show();
                    }else{
                        $('#modal-BulkCustomerRate-new .RoutinePlan-modal').hide();
                    }

                    /*Clear Form Fields */
                    if(self.attr('id')=="bulk_clear_cust_rate"){
                        $('#text-boxes').hide();
                    }else{
                        $('#text-boxes').show();
                    }

                    $("#bulk-edit-customer-rate-form-new")[0].reset();
                    $("#bulk-edit-customer-rate-form-new [name='Interval1']").val(1);
                    $("#bulk-edit-customer-rate-form-new [name='IntervalN']").val(1);
                    $("#bulk-edit-customer-rate-form-new [name='RoutinePlan']").select2().select2('val','');
                    date = new Date();
                    var month = date.getMonth()+1;
                    var day = date.getDate();
                    $("#account_owners").prop('selectedIndex', 0);
                    currentDate = date.getFullYear() + '-' +   (month<10 ? '0' : '') + month + '-' +     (day<10 ? '0' : '') + day;
                    $("#bulk-edit-customer-rate-form-new [name='EffectiveDate']").val(currentDate);
                    $('#modal-BulkCustomerRate-new').modal('show');
                    if(self.attr('id')=="bulk_clear_cust_rate") {
                        $('#modal-BulkCustomerRate-new .modal-header h4').text('Bulk Clear');
                        $('#submit-bulk-data-new').html('<i class="entypo-cancel"></i> Clear');
                        $('#BulkInsert-EffectiveBox').hide();
                    }else if(self.attr('id')=='insertBulkCustomerRates'){
                        $('#modal-BulkCustomerRate-new .modal-header h4').text('Bulk New Offer');
                        $('#submit-bulk-data-new').html('<i class="entypo-floppy"></i> Save');
                        $('#BulkInsert-EffectiveBox').show();
                    }else{
                        $('#modal-BulkCustomerRate-new .modal-header h4').text('Bulk Update');
                        $('#submit-bulk-data-new').html('<i class="entypo-floppy"></i> Save');
                        $('#BulkInsert-EffectiveBox').hide();
                    }
                    $('#modal-BulkCustomerRate-new .modal-body').show();

					 //Bulk new Form Submit
					    $("#bulk-edit-customer-rate-form-new").unbind('submit');
                        $("#bulk-edit-customer-rate-form-new").submit(function() {
                            if(self.attr('id')=="bulk_clear_cust_rate"){
                                update_new_url = baseurl + '/customers_rates/process_bulk_rate_clear/{{$id}}';
                                bulk_update_or_clear(update_new_url,$searchFilter);
                            }else if(self.attr('id')=='insertBulkCustomerRates'){
                                update_new_url = baseurl + '/customers_rates/process_bulk_rate_insert/{{$id}}';
                                bulk_update_or_clear(update_new_url,$searchFilter);
                            }else{
                                update_new_url = baseurl + '/customers_rates/process_bulk_rate_update/{{$id}}';
                                bulk_update_or_clear(update_new_url,$searchFilter);
                            }

                            return false;
                        });
                        initCustomerGrid('table-5');

                });


                        $("#add-new-rate").click(function(e){
                            e.preventDefault();
                            $("#new-rate-form")[0].reset();
                            $("#modal-add-new").modal('show');
                        });
                        $('#rateid_list').select2({
                            placeholder: 'Enter a Code',
                            minimumInputLength: 1,
                            ajax: {
                                dataType: 'json',
                                url: baseurl+'/customers_rates/getCodeByAjax',
                                data: function (term) {
                                    return {
                                        q: term,
                                        page: "{{$id}}",
                                        trunk: $('#TrunkID').val()
                                    };
                                },
                                quietMillis: 500,
                                error: function (data) {
                                    return false;
                                },
                                results: function (data) {
                                    return {
                                        results: data
                                    };
                                }
                            }
                        });
                        $(document).on('change', '#TrunkID', function() {
                            $('#rateid_list').val('').trigger('change');
                            $('#s2id_rateid_list .select2-chosen').text('Enter a Code');
                            $('#s2id_rateid_list a').addClass('select2-default');
                        });
                        $("#new-rate-form").submit(function(e){
                            e.preventDefault();

                            var formData = new FormData($(this)[0]);
                            $.ajax({
                                url: baseurl + '/customers_rates/store/{{$id}}', //Server script to process data
                                type: 'POST',
                                dataType: 'json',
                                // Form data
                                data: formData,
                                //Options to tell jQuery not to process data or worry about content-type.
                                cache: false,
                                contentType: false,
                                processData: false,
                                success: function(response) {
                                    $(".save.btn").button('reset');

                                    if (response.status == 'success') {
                                        $("#modal-add-new").modal('hide');
                                        toastr.success(response.message, "Success", toastr_opts);
                                        $("#customer-rate-table-search").submit();
                                    } else {
                                        toastr.error(response.message, "Error", toastr_opts);
                                    }
                                },
                                error: function(error) {
                                    $("#modal-add-new").modal('hide');
                                    toastr.error(error, "Error", toastr_opts);
                                }
                            });
                            return false;
                        });


                        //Clear Rate Button
                        // click.clear-rate is specific event if we want to on/off perticuler event
                        var clear_rate_processing = 0;
                        $(document).off('click.clear-rate','.btn.clear-customer-rate,#clear-bulk-rate');
                        $(document).on('click.clear-rate','.btn.clear-customer-rate,#clear-bulk-rate',function(e) {
                            //to prevent multiple request, only allow second request after first request's response
                            if(clear_rate_processing == 0) {
                                clear_rate_processing = 1;
                                e.preventDefault();
                                var CustomerRateIDs = [];
                                var TrunkID = $searchFilter.Trunk;
                                var TimezonesID = $searchFilter.Timezones;
                                var i = 0;
                                $('#table-4 tr.selected td div.hiddenRowData input[name="CustomerRateId').each(function (i, el) {
                                    CustomerRateID = $(this).val();
                                    CustomerRateIDs[i++] = CustomerRateID;
                                });

                                $("#clear-bulk-rate-form").find("input[name='TrunkID']").val(TrunkID);
                                $("#clear-bulk-rate-form").find("input[name='TimezonesID']").val(TimezonesID);

                                if (CustomerRateIDs.length || $(this).hasClass('clear-customer-rate')) {
                                    response = confirm('Are you sure?');
                                    if (response) {
                                        $('.btn.clear-customer-rate,#clear-bulk-rate').attr('disabled', 'disabled');
                                        if ($(this).hasClass('clear-customer-rate')) {
                                            var CustomerRateID = $(this).parent().find('.hiddenRowData input[name="CustomerRateId"]').val();
                                            $("#clear-bulk-rate-form").find("input[name='CustomerRateID']").val(CustomerRateID);
                                            $("#clear-bulk-rate-form").find("input[name='criteria']").val('');
                                        }

                                        if ($(this).attr('id') == 'clear-bulk-rate') {
                                            var criteria = '';
                                            if ($('#selectallbutton').is(':checked')) {
                                                criteria = JSON.stringify($searchFilter);
                                                $("#clear-bulk-rate-form").find("input[name='CustomerRateID']").val('');
                                                $("#clear-bulk-rate-form").find("input[name='criteria']").val(criteria);
                                            } else {
                                                var CustomerRateIDs = [];
                                                var i = 0;
                                                $('#table-4 tr.selected td div.hiddenRowData input[name="CustomerRateId').each(function (i, el) {
                                                    CustomerRateID = $(this).val();
                                                    CustomerRateIDs[i++] = CustomerRateID;
                                                });
                                                $("#clear-bulk-rate-form").find("input[name='CustomerRateID']").val(CustomerRateIDs.join(","))
                                                $("#clear-bulk-rate-form").find("input[name='criteria']").val('');
                                            }
                                        }

                                        var formData = new FormData($('#clear-bulk-rate-form')[0]);

                                        $.ajax({
                                            url: baseurl + '/customers_rates/{{$id}}/clear_rate', //Server script to process data
                                            type: 'POST',
                                            dataType: 'json',
                                            success: function (response) {
                                                clear_rate_processing = 0;
                                                $(".save.btn").button('reset');
                                                $('.btn.clear-customer-rate,#clear-bulk-rate').removeAttr('disabled');

                                                if (response.status == 'success') {
                                                    toastr.success(response.message, "Success", toastr_opts);
                                                    $("#customer-rate-table-search").submit();
                                                } else {
                                                    toastr.error(response.message, "Error", toastr_opts);
                                                }
                                            },
                                            // Form data
                                            data: formData,
                                            //Options to tell jQuery not to process data or worry about content-type.
                                            cache: false,
                                            contentType: false,
                                            processData: false
                                        });
                                        return false;
                                    }
                                    return false;
                                } else {
                                    return false;
                                }
                            }
                        });

                        $("#DiscontinuedRates").on('change', function (event, state) {
                            if($("#DiscontinuedRates").is(':checked')) {
                                $(".EffectiveBox").hide();
                                $("#btn-action").hide();
                            } else {
                                $(".EffectiveBox").show();
                                $("#btn-action").show();
                            }
                        });

                        $(document).on('click', '.btn-history', function() {
                            var $this   = $(this);
                            var Codes   = $this.prevAll("div.hiddenRowData").find("input[name='Code']").val();
                            getArchiveRateTableRates($this,Codes);
                        });

                        $('#customer-rate-table-search select[name="Effective"]').on('change', function() {
                            var val = $(this).val();

                            if(val == 'CustomDate') {
                                $('.CustomDateBox').show();
                            } else {
                                $('.CustomDateBox').hide();
                            }
                        });
                        $('#customer-rate-table-search select[name="Effective"]').val('Now').trigger('change');

                        //set RateN value = Rate1 value if RateN value is blank
                        $(document).on('focusout','.Rate1', function() {
                            var formid = $(this).closest("form").attr('id');
                            var val = $(this).val();

                            if($('#'+formid+' .RateN').val() == '') {
                                $('#'+formid+' .RateN').val(val);
                            }
                        });
            });
            function bulk_update_or_clear(fullurl,searchFilter){
                $.ajax({
                    url:fullurl, //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        $("#submit-bulk-data-new").button('reset');
                        if (response.status == 'success') {
                            $('#modal-BulkCustomerRate-new').modal('hide');

                            toastr.success(response.message, "Success", toastr_opts);
                            $("#customer-rate-table-search").submit();
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },
                    // Form data
                    data: $('#bulk-edit-customer-rate-form-new').serialize()+'&'+$.param(searchFilter),
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false
                });
            }


            function initCustomerGrid(tableID,OwnerFilter){
                first_call = true;
                if(typeof OwnerFilter != 'undefined'){
                        $searchFilter.OwnerFilter = OwnerFilter ;
                }else{
                    var owner_filter = 0;
                    if($("[name=account_owners]") != 'undefined')
                        owner_filter = $("[name=account_owners]").val();

                    $searchFilter.OwnerFilter = owner_filter ;
                }
            var data_table_new = $("#"+tableID).dataTable({
                    "bDestroy": true, // Destroy when resubmit form
                    "sDom": "<'row'<'col-xs-12 border_left'f>r>t",
                    "bProcessing": false,
                    "bServerSide": false,
                    "bPaginate": false,
                    "fnServerParams": function(aoData) {
                        aoData.push({"name": "Trunk", "value": $searchFilter.Trunk},{"name": "OwnerFilter", "value": $searchFilter.OwnerFilter});
                    },
                    "sAjaxSource": baseurl + "/customers_rates/{{$id}}/search_customer_grid",
                    "aoColumns":
                                [
                                    {"bSortable": false, //RateID
                                        mRender: function(id, type, full) {
                                            return '<div class="checkbox "><input type="checkbox" name="customer[]" value="' + id + '" class="rowcheckbox" ></div>';
                                        }
                                    },
                                    {}
                                ],

                    "fnDrawCallback": function() {
                        $(".selectallcust").click(function(ev) {
                            var is_checked = $(this).is(':checked');
                            $('#'+tableID+' tbody tr').each(function(i, el) {
                                if (is_checked) {
                                    $(this).find('.rowcheckbox').prop("checked", true);
                                    $(this).addClass('selected');
                                } else {
                                    $(this).find('.rowcheckbox').prop("checked", false);
                                    $(this).removeClass('selected');
                                }
                            });
                        });
                         $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                    }

                });
                if(typeof OwnerFilter == 'undefined'){
                    $('#'+tableID).parents('div.dataTables_wrapper').first().hide();
                    $('.my_account_'+tableID).hide()
                }

                //$('#'+tableID).show();

            }
            function checkrouting(currentval){
                var display_routine = false;
                if(typeof routinejson != 'undefined' && routinejson != ''){
                $.each($.parseJSON(routinejson), function(key,value){
                    if(key!= '' && currentval != ''  && key == currentval){
                        display_routine = true;
                    }
                });
                }
                if(display_routine == false){
                    $("#customer-rate-table-search select[name='RoutinePlanFilter']").val('');
                    //$("#customer-rate-table-search select[name='RoutinePlan']").attr('disabled','disabled');
                    $(".RoutinePlan").hide();

                    $("#table-4 td:nth-child(7)").hide();
                    $("#table-4 th:nth-child(7)").hide();
                }else{
                    $("#customer-rate-table-search select[name='RoutinePlanFilter']").val('');
                    $(".RoutinePlan").show();
                    $("#table-4 td:nth-child(7)").show();
                    $("#table-4 th:nth-child(7)").show();
                    //$("#customer-rate-table-search select[name='RoutinePlan']").removeAttr('disabled')
                }
            }
            function animate_top_on_customer_grid(){
                $('.my_account_table-5').toggle();
                $('#table-5_wrapper').toggle();
                //$('#table-5_filter').remove();
            }


            function getArchiveRateTableRates($clickedButton,Codes) {
                var ArchiveRates;
                var tr  = $clickedButton.closest('tr');
                var row = data_table.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    $clickedButton.attr('disabled','disabled');

                    $.ajax({
                        url: baseurl + "/customers_rates/{{$id}}/search_ajax_datagrid_archive_rates",
                        type: 'POST',
                        data: "Codes=" + Codes+"&TimezonesID="+$searchFilter.Timezones,
                        dataType: 'json',
                        cache: false,
                        success: function (response) {
                            $clickedButton.removeAttr('disabled');

                            if (response.status == 'success') {
                                ArchiveRates = response.data;
                                //$('.details-control').show();
                            } else {
                                ArchiveRates = {};
                                toastr.error(response.message, "Error", toastr_opts);
                            }

                            var hiddenRowData = tr.find('.hiddenRowData');
                            var Code = hiddenRowData.find('input[name="Code"]').val();
                            var table = $('<table class="table table-bordered datatable dataTable no-footer" style="margin-left: 4%;width: 92% !important;"></table>');
                            table.append("<thead><tr><th>Code</th><th>Description</th><th>Interval 1</th><th>Interval N</th><th>Connection Fee</th><th>Rate1</th><th>RateN</th><th class='sorting_desc'>Effective Date</th><th>End Date</th><th>Modified Date</th><th>Modified By</th></tr></thead>");
                            //var tbody = $("<tbody></tbody>");

                            ArchiveRates.forEach(function (data) {
                                //if (data['Code'] == Code) {
                                var html = "";
                                html += "<tr class='no-selection'>";
                                html += "<td>" + data['Code'] + "</td>";
                                html += "<td>" + data['Description'] + "</td>";
                                html += "<td>" + data['Interval1'] + "</td>";
                                html += "<td>" + data['IntervalN'] + "</td>";
                                html += "<td>" + data['ConnectionFee'] + "</td>";
                                html += "<td>" + data['Rate'] + "</td>";
                                html += "<td>" + data['RateN'] + "</td>";
                                html += "<td>" + data['EffectiveDate'] + "</td>";
                                html += "<td>" + data['EndDate'] + "</td>";
                                html += "<td>" + data['ModifiedDate'] + "</td>";
                                html += "<td>" + data['ModifiedBy'] + "</td>";
                                html += "</tr>";
                                table.append(html);
                                //}
                            });
                            //table.append(tbody);
                            row.child(table).show();
                            row.child().addClass('no-selection child-row');
                            tr.addClass('shown');
                        }
                    });
                }
            }

        </script>
        <style>
                #table-4 .dataTables_filter label{
                    display:none !important;
                }
                #table-4 .dataTables_wrapper .export-data{
                    right: 30px !important;
                }
                .border_left .dataTables_filter {
                  border-left: 1px solid #eeeeee !important;
                  border-top-left-radius: 3px;
                }
                #table-5_filter label{
                    display:block !important;
                }
                #table-6_filter label{
                    display:block !important;
                }
                #selectcheckbox{
                    padding: 15px 10px;
                }
        </style>
        @include('includes.errors')
        @include('includes.success')

    </div>
</div>
@stop


@section('footer_ext')
@parent
<div class="modal fade" id="modal-CustomerRate">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="edit-customer-rate-form" method="post" action="{{URL::to('customers_rates/update/'.$id)}}">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Customer Rate</h4>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Effective Date</label>
                                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Rate1</label>
                                <input type="text" name="Rate" class="form-control Rate1" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">RateN</label>
                                <input type="text" name="RateN" class="form-control RateN" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Interval 1</label>
                                <input type="text" name="Interval1" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Interval N</label>
                                <input type="text" name="IntervalN" class="form-control" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" placeholder="">
                            </div>
                        </div>

                        {{--<div class="col-md-6">

                            <div class="form-group">
                                <label for="field-4" class="control-label">End Date</label>

                                <input type="text" name="EndDate" class="form-control datepicker"  data-startdate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" value="" />
                            </div>

                        </div>--}}

                         <div class="col-md-6 RoutinePlan-modal">
                            <div class="form-group">
                                <label class="control-label">Routing plan</label>
                                {{ Form::select('RoutinePlan', $trunks_routing, '', array("class"=>"select2")) }}
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateID" value="">
                    <input type="hidden" name="CustomerRateId" value="">
                    <input type="hidden" name="Type" value="1">
                    <input type="hidden" name="Trunk" value="{{Input::get('Trunk')}}">
                    <input type="hidden" name="TimezonesID" value="">

                    <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Add Selected Rates -->
<div class="modal fade" id="add-selected-customer-rate-modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="add-selected-customer-rate-form" method="post" action="{{URL::to('customers_rates/add_selected/'.$id)}}">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Selected Customer Rates</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Effective Date</label>
                                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" placeholder="">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Rate1</label>
                                <input type="text" name="Rate" class="form-control Rate1" placeholder="">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">RateN</label>
                                <input type="text" name="RateN" class="form-control RateN" placeholder="">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Interval 1</label>
                                <input type="text" name="Interval1" class="form-control" value="" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Interval N</label>
                                <input type="text" name="IntervalN" class="form-control" placeholder="">
                            </div>
                        </div>

                        <div class="col-md-6 RoutinePlan-modal">
                            <div class="form-group">
                                <label class="control-label">Routing plan</label>
                                {{ Form::select('RoutinePlan', $trunks_routing, '', array("class"=>"select2")) }}
                            </div>
                        </div>

                    </div>

                    <div style="max-height: 500px; overflow-y: auto; overflow-x: hidden;" >
                        <h4 > Click <span class="label label-info" onclick="$('.my_account_table-7').toggle();$('#table-7_wrapper').toggle();"  style="cursor: pointer">here</span> to select additional customer accounts you want to update.</h4>

                        <div class="row my_account_table-7">
                            @if(User::is_admin())
                                <div class="col-sm-4" style="float: right">
                                    {{Form::select('account_owners',$account_owners,Input::get('account_owners'),array("id"=>"account_owners_7","class"=>"select2"))}}

                                </div>
                                @else
                                <!-- For Account Manager -->
                                <input type="hidden" name="account_owners" value="{{User::get_userID()}}">
                            @endif
                        </div>

                        <table class="table table-bordered datatable" id="table-7">
                            <thead>
                            <tr>
                                <th><input type="checkbox" class="selectallcust" name="customer[]" /></th>
                                <th>Customer Name</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateID" value="">
                    <input type="hidden" name="Trunk" value="">
                    <input type="hidden" name="TimezonesID" value="">

                    <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Bulk Update -->
<div class="modal fade" id="modal-BulkCustomerRate">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="bulk-edit-customer-rate-form" method="post" action="{{URL::to('customers_rates/bulk_update/'.$id)}}">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Bulk Edit Customer Rates</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        {{--<div class="col-md-6">

                            <div class="form-group">
                                <label for="field-4" class="control-label">Effective Date</label>

                                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" value="" />
                            </div>

                        </div>--}}

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Rate1</label>
                                <input type="text" name="Rate" class="form-control Rate1" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">RateN</label>
                                <input type="text" name="RateN" class="form-control RateN" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Interval 1</label>
                                <input type="text" name="Interval1" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Interval N</label>
                                <input type="text" name="IntervalN" class="form-control" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" placeholder="">
                            </div>
                        </div>

                        {{--<div class="col-md-6">

                            <div class="form-group">
                                <label for="field-4" class="control-label">End Date</label>

                                <input type="text" name="EndDate" class="form-control datepicker"  data-startdate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" value="" />
                            </div>

                        </div>--}}

                        <div class="col-md-6 RoutinePlan-modal">
                            <div class="form-group">
                                <label class="control-label">Routing plan</label>
                                {{ Form::select('RoutinePlan', $trunks_routing, '', array("class"=>"select2")) }}
                            </div>
                        </div>
                    </div>
                    {{--<div style="max-height: 500px; overflow-y: auto; overflow-x: hidden;" >
                        <h4 > Click <span class="label label-info" onclick="$('.my_account_table-6').toggle();$('#table-6_wrapper').toggle();"  style="cursor: pointer">here</span> to select additional customer accounts you want to update.</h4>

                        <div class="row my_account_table-6">
                            --}}{{--@if(User::is_admin())--}}{{--
                                <div class="col-sm-4" style="float: right">
                                    --}}{{--{{Form::select('account_owners',$account_owners,Input::get('account_owners'),array("id"=>"account_owners_6","class"=>"select2"))}}--}}{{--

                                </div>
                                --}}{{--@else--}}{{--
                                        <!-- For Account Manager -->
                                --}}{{--<input type="hidden" name="account_owners" value="{{User::get_userID()}}">--}}{{--
                            --}}{{--@endif--}}{{--
                        </div>


                        <table class="table table-bordered datatable" id="table-6">
                            <thead>
                            <tr>
                                <th><input type="checkbox" class="selectallcust" name="customer[]" /></th>
                                <th>Customer Name</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>--}}

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateID" value="">
                    <input type="hidden" name="CustomerRateId" value="">
                    <input type="hidden" name="Trunk" value="">
                    <input type="hidden" name="TimezonesID" value="">

                    <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Bulk new Update -->
<div class="modal fade " id="modal-BulkCustomerRate-new">
    <div class="modal-dialog " >
        <div class="modal-content">

            <form id="bulk-edit-customer-rate-form-new" method="post" action="{{URL::to('customers_rates/process_bulk_rate_update/'.$id)}}">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Bulk Edit Customer Rates</h4>
                </div>

                <div class="modal-body">
                    <div id="search_static_val">
                    </div>
                    <div id="text-boxes" class="row">
                        <div class="col-md-6" style="display: none;" id="BulkInsert-EffectiveBox">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Effective Date</label>
                                <input type="text" name="EffectiveDate" class="form-control datepicker"  data-startdate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Rate1</label>
                                <input type="text" name="Rate" class="form-control Rate1" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">RateN</label>
                                <input type="text" name="RateN" class="form-control RateN" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Interval 1</label>
                                <input type="text" name="Interval1" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Interval N</label>
                                <input type="text" name="IntervalN" class="form-control" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" placeholder="">
                            </div>
                        </div>
                         <div class="col-md-6 RoutinePlan-modal">
                            <div class="form-group">
                                <label class="control-label">Routing plan</label>
                                {{ Form::select('RoutinePlan', $trunks_routing, '', array("class"=>"select2")) }}
                            </div>
                        </div>
                        {{--<div class="col-md-6">

                            <div class="form-group">
                                <label for="field-4" class="control-label">End Date</label>

                                <input type="text" name="EndDate" class="form-control datepicker"  data-startdate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" value="" />
                            </div>

                        </div>--}}
                    </div>


                    <div style="max-height: 500px; overflow-y: auto; overflow-x: hidden;" >
                        <h4 >Click <span class="label label-info" onclick="animate_top_on_customer_grid();" style="cursor: pointer">here</span> to select additional customer accounts you want to update.</h4>
                        <div class="row my_account_table-5">
                        @if(User::is_admin())
                            <div class="col-sm-4" style="float: right">
                                {{Form::select('account_owners',$account_owners,Input::get('account_owners'),array("id"=>"account_owners","class"=>"select2"))}}
                            </div>
                            @else
                                    <!-- For Account Manager -->
                            <input type="hidden" name="account_owners" value="{{User::get_userID()}}">
                        @endif
                        </div>

                            <table class="table table-bordered datatable" id="table-5" style="margin-top:10px;" >
                                <thead>
                                <tr>
                                    <th><input type="checkbox" class="selectallcust" name="customer[]" /></th>
                                    <th>Customer Name</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" id="submit-bulk-data-new"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-add-new">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="new-rate-form" method="post">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Rate</h4>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Trunk</label>
                                {{ Form::select('TrunkID', $trunks, $trunk_keys, array("class"=>"select2","id"=>"TrunkID")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Timezones</label>
                                {{ Form::select('TimezonesID', $Timezones, '', array("class"=>"select2","id"=>"TimezonesID")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Code</label>
                                <input type="hidden" id="rateid_list" name="RateID" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Effective Date</label>
                                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>
                        {{--<div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">End Date</label>
                                <input type="text" name="EndDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>
                        </div>--}}
                        <div class="col-md-6 clear">
                            <div class="form-group">
                                <label class="control-label">Rate</label>
                                <input type="text" name="Rate" class="form-control" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6 clear">
                            <div class="form-group">
                                <label class="control-label">Interval 1</label>
                                <input type="text" name="Interval1" class="form-control" value="" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Interval N</label>
                                <input type="text" name="IntervalN" class="form-control" placeholder="">
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop



