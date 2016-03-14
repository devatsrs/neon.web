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
    @if(User::can('CustomersRatesController.settings'))
    <li  >
        <a href="{{ URL::to('/customers_rates/settings/'.$id) }}" >
            Settings
        </a>
    </li>
    @endif
    @if(User::can('CustomersRatesController.download'))
    <li>
        <a href="{{ URL::to('/customers_rates/'.$id.'/download') }}" >
            Download Rate sheet
        </a>
    </li>
    @endif
    @if(User::can('CustomersRatesController.history'))
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
                               <label for="field-1" class="col-sm-1 control-label">Effective</label>
                               <div class="col-sm-2">
                                   <select name="Effective" class="selectboxit" data-allow-clear="true" data-placeholder="Select Effective">
                                       <option value="Now">Now</option>
                                       <option value="Future">Future</option>
                                       <option value="All">All</option>
                                   </select>
                               </div>

                              <label class="col-sm-2 control-label">Show Applied Rates</label>
                               <div class="col-sm-1">
                                   <input id="Effected_Rates_on_off" class="icheck" name="Effected_Rates_on_off" type="checkbox" value="1" >
                               </div>

                           </div>
                           <div class="form-group">
                               <label for="field-1" class="col-sm-1 control-label">Country</label>
                               <div class="col-sm-3">
                                   {{ Form::select('Country', $countries, Input::get('Country') , array("class"=>"select2")) }}
                               </div>

                               <label for="field-1" class="col-sm-1 control-label">Trunk</label>
                               <div class="col-sm-3">
                                   {{ Form::select('Trunk', $trunks, $trunk_keys, array("class"=>"select2",'id'=>'ct_trunk')) }}
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
        <div class="row">
         <div  class="col-md-12">
                <div class="input-group-btn pull-right" style="width:70px;">
                    @if(User::can('CustomersRatesController.update') || User::can('CustomersRatesController.clear_rate'))
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #1f232a; border-color: #1f232a; margin-top:0px;">
                        @if(User::can('CustomersRatesController.update') && User::can('CustomersRatesController.process_bulk_rate_update'))
                        <li><a class="generate_rate create" id="bulk_set_cust_rate" href="javascript:;" style="width:100%">
                                Bulk update
                            </a>
                        </li>
                        <li><a class="generate_rate create" id="changeSelectedCustomerRates" href="javascript:;" >
                                Change Selected Rates
                            </a></li>
                        @endif
                        @if(User::can('CustomersRatesController.clear_rate') && User::can('CustomersRatesController.bulk_clear_rate') && User::can('CustomersRatesController.process_bulk_rate_clear'))
                        <li><a class="generate_rate create" id="clear-bulk-rate" href="javascript:;" style="width:100%">
                                Clear Selected Rates
                            </a></li>
                        <li><a class="generate_rate create" id="bulk_clear_cust_rate" href="javascript:;" style="width:100%">
                                Bulk clear
                            </a></li>
                        @endif
                    </ul>
                    @endif
                    <form id="clear-bulk-rate-form" >
                        <input type="hidden" name="CustomerRateIDs" value="">
                    </form>
                </div><!-- /btn-group -->
         </div>
            <div class="clear"></div>
            </div>
        <br>

        <table class="table table-bordered datatable" id="table-4">
            <thead>
                <tr>
                    <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
                    <th width="5%">Code</th>
                    <th width="20%">Description</th>
                    <th width="5%">Interval 1</th>
                    <th width="5%">Interval N</th>
                    <th width="5%">Connection Fee</th>
                    <th width="5%" class="routng_plan_cl">Routing plan</th>
                    <th width="5%">Rate ({{$CurrencySymbol}})</th>
                    <th width="10%">Effective Date</th>
                    <th width="10%">Modified Date</th>
                    <th width="10%">Modified By</th>
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
            var list_fields  = ['RateID','Code','Description','Interval1','IntervalN','ConnectionFee','RoutinePlanName','Rate','EffectiveDate','LastModifiedDate','LastModifiedBy','CustomerRateId','TrunkID','RateTableRateId'];
            var routinejson ='{{json_encode($routine)}}';
                    jQuery(document).ready(function($) {

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
                            $searchFilter.Effected_Rates_on_off = $("#customer-rate-table-search input[name='Effected_Rates_on_off']").prop("checked");
                            $searchFilter.RoutinePlanFilter = $("#customer-rate-table-search select[name='RoutinePlanFilter']").val();


                            if($searchFilter.Trunk == '' || typeof $searchFilter.Trunk  == 'undefined'){
                               toastr.error("Please Select a Trunk", "Error", toastr_opts);
                               return false;
                            }


                            data_table = $("#table-4").dataTable({
                                "bDestroy": true, // Destroy when resubmit form
                                "bProcessing": true,
                                "bServerSide": true,
                                "sAjaxSource": baseurl + "/customers_rates/{{$id}}/search_ajax_datagrid",
                                "fnServerParams": function(aoData) {
                                    aoData.push({"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country}, {"name": "Trunk", "value": $searchFilter.Trunk}, {"name": "Effective", "value": $searchFilter.Effective},{"name": "Effected_Rates_on_off", "value": $searchFilter.Effected_Rates_on_off},{"name": "RoutinePlanFilter", "value": $searchFilter.RoutinePlanFilter});
                                    data_table_extra_params.length = 0;
                                    data_table_extra_params.push({"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country}, {"name": "Trunk", "value": $searchFilter.Trunk}, {"name": "Effective", "value": $searchFilter.Effective},{"name": "RoutinePlanFilter", "value": $searchFilter.RoutinePlanFilter},{"name":"Export","value":1},{"name": "Effected_Rates_on_off", "value": $searchFilter.Effected_Rates_on_off});
                                    console.log($searchFilter);
                                    console.log("Perm sent...");
                                },
                                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                                "sPaginationType": "bootstrap",
                                 "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                                 "aaSorting": [[8, "asc"]],
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
                                            {@if(count($trunks_routing) ==0 || count($routine)  == 0)
                                                "visible": false
                                               @endif

                                            }, //4IntervalN
                                            {}, //5 ConnectionFee
                                            {}, //5Rate
                                            {}, //6Effective Date
                                            {}, //7LastModifiedDate
                                            {}, //8LastModifiedBy
                                            {// 9 CustomerRateId
                                                mRender: function(id, type, full) {
                                                    var action, edit_, delete_;
                                                    edit_ = "{{ URL::to('/customers_rates/{id}/edit')}}";
                                                    RateID = full[0];
                                                    //Trunk = $("#customer-rate-table-search select[name='Trunk']").val();

                                                    edit_ = edit_.replace('{id}', id);

                                                    CustomerRateID  = id;
                                                    RateID = full[0];

                                                    Rate = ( full[6] == null )? 0:full[6];
                                                    Interval1 = ( full[3] == null )? 1:full[3];
                                                    IntervalN = ( full[4] == null )? 1:full[4];
                                                    RoutinePlan = ( full[5] == null )? '':full[5];

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
                                                    if('{{User::can('CustomersRatesController.update')}}') {
                                                        action += '<a href="Javascript:;" class="edit-customer-rate btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit</a>';
                                                    }
                                                    if (CustomerRateID > 0) {
                                                        if('{{User::can('CustomersRatesController.clear_rate')}}') {
                                                            action += ' <button href="' + clerRate_ + '"  class="btn clear btn-danger btn-sm btn-icon icon-left" data-loading-text="Loading..."><i class="entypo-cancel"></i>Clear Rate</button>';
                                                        }
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
                                                    "sButtonText": "Export Data",
                                                    "sUrl": baseurl + "/customers_rates/{{$id}}/search_ajax_datagrid",
                                                    sButtonClass: "save-collection"
                                                }
                                            ]
                                        },
                                "fnDrawCallback": function() {
                                    checkrouting($searchFilter.Trunk);
                                    $(".btn.clear").click(function(e) {

                                        response = confirm('Are you sure?');
                                        //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/customers_rates')}}" : $(this).attr("data-redirect");
                                        if (response) {
                                            $.ajax({
                                                url: $(this).attr("href"),
                                                type: 'POST',
                                                dataType: 'json',
                                                success: function(response) {
                                                    $(".btn.clear").button('reset');

                                                    if (response.status == 'success') {
                                                        toastr.success(response.message, "Success", toastr_opts);
                                                        data_table.fnFilter('', 0);
                                                        console.log($searchFilter);
                                                        console.log("Clear---");
                                                    } else {
                                                        toastr.error(response.message, "Error", toastr_opts);
                                                    }
                                                },
                                                // Form data
                                                //data: {},
                                                cache: false,
                                                contentType: false,
                                                processData: false
                                            });
                                        }
                                        return false;

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
                            $(this).toggleClass('selected');
                            if ($(this).hasClass('selected')) {
                                $(this).find('.rowcheckbox').prop("checked", true);
                            } else {
                                $(this).find('.rowcheckbox').prop("checked", false);
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
                         $('#table-6 tbody').on('click', 'tr', function() {
                            $(this).toggleClass('selected');
                            if ($(this).hasClass('selected')) {
                                $(this).find('.rowcheckbox').prop("checked", true);
                            } else {
                                $(this).find('.rowcheckbox').prop("checked", false);
                            }
                        });

                        //Bulk Clear Submit
                        $("#clear-bulk-rate").click(function() {
                            if($('#selectallbutton').is(':checked')){
                                $("#bulk_clear_cust_rate").trigger( "click" );
                            }else{
                            var CustomerRateIds = [];
                            var i = 0;
                            $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                                //console.log($(this).val());
                                CustomerRateId = $(this).parent().parent().parent().find(".hiddenRowData input[name='CustomerRateId']").val();
                                if (CustomerRateId !== null && CustomerRateId !== 'null') {
                                    CustomerRateIds[i++] = CustomerRateId;
                                }
                            });
                            $("#clear-bulk-rate-form").find("input[name='CustomerRateIDs']").val(CustomerRateIds.join(","));

                             var formData = new FormData($('#clear-bulk-rate-form')[0]);
                             if(CustomerRateIds.length){

                                /*console.log(CustomerRateIds.join(","));return false;*/

                            $.ajax({
                                url: baseurl + '/customers_rates/bulk_clear_rate/{{$id}}', //Server script to process data
                                type: 'POST',
                                dataType: 'json',
                                success: function (response) {
                                    $("#clear-bulk-rate").button('reset');

                                    if (response.status == 'success') {
                                        toastr.success(response.message, "Success", toastr_opts);
                                        data_table.fnFilter('', 0);
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
                            }
                            return false;
                        }
                        });

                        //Edit Form Submit
                        $("#edit-customer-rate-form").submit(function() {

                            var formData = new FormData($('#edit-customer-rate-form')[0]);
                            $.ajax({
                                url: baseurl + '/customers_rates/update/{{$id}}', //Server script to process data
                                type: 'POST',
                                dataType: 'json',
                                success: function(response) {
                                    $(".save.btn").button('reset');

                                    if (response.status == 'success') {
                                        $("#modal-CustomerRate").modal("hide");
                                        toastr.success(response.message, "Success", toastr_opts);
                                        console.log($searchFilter);
                                        data_table.fnFilter('', 0);
                                    } else {
                                        toastr.error(response.message, "Error", toastr_opts);
                                    }
                                },
                                error: function(error) {
                                    $("#modal-CustomerRate").modal("hide");
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

                        //Bulk Form Submit
                        $("#bulk-edit-customer-rate-form").submit(function() {

                            var formData = new FormData($('#bulk-edit-customer-rate-form')[0]);
                            $.ajax({
                                url: baseurl + '/customers_rates/bulk_update/{{$id}}', //Server script to process data
                                type: 'POST',
                                dataType: 'json',
                                success: function(response) {
                                    $(".save.btn").button('reset');
                                    $("#modal-BulkCustomerRate").modal("hide");
                                    if (response.status == 'success') {

                                        toastr.success(response.message, "Success", toastr_opts);
                                        data_table.fnFilter('', 0);
                                    } else {
                                        toastr.error(response.message, "Error", toastr_opts);
                                    }
                                },
                                error: function(error) {
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


                        // Replace Checboxes
                        $(".pagination a").click(function(ev) {
                            replaceCheckboxes();
                        });


                //console.log(toastr_opts);

                //Bulk Edit Button
                $("#changeSelectedCustomerRates").click(function(ev) {
                    if($('#selectallbutton').is(':checked')){
                        $( "#bulk_set_cust_rate" ).trigger( "click" );
                    }else{
                        var RateIDs = [];
                        var i = 0;
                        $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                            console.log($(this).val());
                            RateID = $(this).val();
                            RateIDs[i++] = RateID;
                        });
                        //Trunk = $("#customer-rate-table-search").find("select[name='Trunk']").val();
                        $("#bulk-edit-customer-rate-form").find("input[name='RateID']").val(RateIDs.join(","));
                        $("#bulk-edit-customer-rate-form").find("input[name='Trunk']").val($searchFilter.Trunk);

                        $("#bulk-edit-customer-rate-form")[0].reset();
                        $("#bulk-edit-customer-rate-form [name='Interval1']").val(1);
                        $("#bulk-edit-customer-rate-form [name='IntervalN']").val(1);
                        $("#bulk-edit-customer-rate-form [name='RoutinePlan']").select2().select2('val','');
                        date = new Date();
                        var month = date.getMonth()+1;
                        var day = date.getDate();
                        currentDate = date.getFullYear() + '-' +   (month<10 ? '0' : '') + month + '-' +     (day<10 ? '0' : '') + day;
                        $("#bulk-edit-customer-rate-form [name='EffectiveDate']").val(currentDate);
                        /*$("#bulk-edit-customer-rate-form").find("input[name='EffectiveDate']").val("");
                         $("#bulk-edit-customer-rate-form").find("input[name='Rate']").val("");
                         $("#bulk-edit-customer-rate-form").find("input[name='Interval1']").val("");
                         $("#bulk-edit-customer-rate-form").find("input[name='IntervalN']").val("");*/
                        if(RateIDs.length){
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
                $("#account_owners").change(function(ev) {
                    var account_owners = $(this).val();
                    if(account_owners!=""){
                        initCustomerGrid('table-5',account_owners);
                    }else if(first_call ==false ){
                        initCustomerGrid('table-5','');
                    }
                    first_call = false;;
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


                //Bulk Clear Button
                $("#clearSelectedCustomerRates").click(function(ev) {


                    var RateIDs = [];
                    var i = 0;
                    $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                        console.log($(this).val());
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

                $("#bulk_set_cust_rate,#bulk_clear_cust_rate").click(function(ev) {
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
                    if($searchFilter.Trunk != ''){
                        search_html += '<div class="col-md-6"><div class="form-group"><label for="field-1" class="control-label">Trunk</label><div class=""><p class="form-control-static" >'+$("#customer-rate-table-search select[name='Trunk']").find("[value='"+$searchFilter.Trunk+"']").text()+'</p></div></div></div>';
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
                        $('#modal-BulkCustomerRate-new .modal-header h4').text('Bulk Clear Customer Rates');
                        $('#submit-bulk-data-new').html('<i class="entypo-cancel"></i> Clear');
                    }else{
                        $('#modal-BulkCustomerRate-new .modal-header h4').text('Bulk Update Customer Rates');
                    }
                    $('#modal-BulkCustomerRate-new .modal-body').show();

					 //Bulk new Form Submit
					    $("#bulk-edit-customer-rate-form-new").unbind('submit');
                        $("#bulk-edit-customer-rate-form-new").submit(function() {
                            if(self.attr('id')=="bulk_clear_cust_rate"){
                                update_new_url = baseurl + '/customers_rates/process_bulk_rate_clear/{{$id}}';
                                bulk_update_or_clear(update_new_url,$searchFilter);
                            }else{
                                update_new_url = baseurl + '/customers_rates/process_bulk_rate_update/{{$id}}';
                                bulk_update_or_clear(update_new_url,$searchFilter);
                            }

                            return false;
                        });
                        initCustomerGrid('table-5');

                        });



            });
            function bulk_update_or_clear(fullurl,searchFilter){
                $.ajax({
                    url:fullurl, //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        $("#submit-bulk-data-new").button('reset');
                        $('#modal-BulkCustomerRate-new').modal('hide');

                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            data_table.fnFilter('', 0);
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

                                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Rate</label>

                                <input type="text" name="Rate" class="form-control" id="field-5" placeholder="">

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
                                <label for="field-5" class="control-label">Interval N</label>

                                <input type="text" name="IntervalN" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                         <div class="col-md-6 RoutinePlan-modal">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Routing plan</label>

                                {{ Form::select('RoutinePlan', $trunks_routing, '', array("class"=>"select2")) }}

                            </div>

                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateID" value="">
                    <input type="hidden" name="Trunk" value="{{Input::get('Trunk')}}">

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
                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-4" class="control-label">Effective Date</label>

                                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" value="" />
                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Rate</label>

                                <input type="text" name="Rate" class="form-control" id="field-5" placeholder="">

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
                                <label for="field-5" class="control-label">Interval N</label>

                                <input type="text" name="IntervalN" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>

                        <div class="col-md-6 RoutinePlan-modal">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Routing plan</label>

                                {{ Form::select('RoutinePlan', $trunks_routing, '', array("class"=>"select2")) }}

                            </div>

                        </div>



                    </div>
                    <div style="max-height: 500px; overflow-y: auto; overflow-x: hidden;" >
                          <h4 > Click <span class="label label-info" onclick="$('.my_account_table-6').toggle();$('#table-6_wrapper').toggle();"  style="cursor: pointer">here</span> to select additional customer accounts you want to update.</h4>

                          <div class="row my_account_table-6">
                            @if(User::is_admin())
                               <div class="col-sm-4" style="float: right">
                               {{Form::select('account_owners',$account_owners,Input::get('account_owners'),array("id"=>"account_owners_6","class"=>"select2"))}}

                                   </div>
                                @else
                                   <!-- For Account Manager -->
                                  <input type="hidden" name="account_owners" value="{{User::get_userID()}}">
                                @endif
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
                        </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateID" value="">
                    <input type="hidden" name="Trunk" value="">

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
                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-4" class="control-label">Effective Date</label>

                                <input type="text" name="EffectiveDate" class="form-control datepicker"  data-startdate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" value="" />
                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Rate</label>

                                <input type="text" name="Rate" class="form-control" id="field-5" placeholder="">

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
                                <label for="field-5" class="control-label">Interval N</label>

                                <input type="text" name="IntervalN" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>

                         <div class="col-md-6 RoutinePlan-modal">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Routing plan</label>

                                {{ Form::select('RoutinePlan', $trunks_routing, '', array("class"=>"select2")) }}

                            </div>

                        </div>

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

@stop



