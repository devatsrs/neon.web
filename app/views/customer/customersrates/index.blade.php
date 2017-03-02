@extends('layout.customer.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="#"><i class="entypo-home"></i>Outbound Rate</a>
        </li>
    </ol>

<h3>Outbound Rate</h3>
{{--@include('accounts.errormessage')--}}
<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
    <li  >
        <a href="{{ URL::to('/customer/customers_rates') }}" >
            Settings
        </a>
    </li>
    <li class="active">
        <a href="{{ URL::to('/customer/customers_rates/rate') }}" >
           Outbound Rate
        </a>
    </li>
    @if(isset($displayinbound) && $displayinbound>0)
        <li>
            <a href="{{ URL::to('/customer/customers_rates/inboundrate') }}" >
                Inbound Rate
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
                                   <select name="Effective" class="select2 small" data-allow-clear="true" data-placeholder="Select Effective">
                                       <option value="Now">Now</option>
                                       <option value="Future">Future</option>
                                       <option value="All">All</option>
                                   </select>
                               </div>

                               <label for="field-1" class="col-sm-1 control-label">Trunk</label>
                               <div class="col-sm-2">
                                   {{ Form::select('Trunk', $trunks, $trunk_keys, array("class"=>"select2",'id'=>'ct_trunk')) }}
                               </div>

                              <!--<label class="col-sm-2 control-label">Show Applied Rates</label>
                               <div class="col-sm-1">
                                   <input id="Effected_Rates_on_off" class="icheck" name="Effected_Rates_on_off" type="checkbox" value="1" >
                               </div>-->

                           </div>
                           <div class="form-group">
                               <label for="field-1" class="col-sm-1 control-label">Country</label>
                               <div class="col-sm-3">
                                   {{ Form::select('Country', $countries, Input::get('Country') , array("class"=>"select2")) }}
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
                    <th width="5%"></th>
                    <th width="5%">Code</th>
                    <th width="20%">Description</th>
                    <th width="5%">Interval 1</th>
                    <th width="5%">Interval N</th>
                    <th width="5%">Connection Fee</th>
                    <th width="5%" class="RoutinePlan">Routing plan</th>
                    <th width="5%">Rate ({{$CurrencySymbol}})</th>
                    <th width="10%">Effective Date</th>
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

                            if($searchFilter.Trunk == '' || typeof $searchFilter.Trunk  == 'undefined' || $searchFilter.Trunk  == null){
                               toastr.error("Please Select a Trunk", "Error", toastr_opts);
                               return false;
                            }


                            data_table = $("#table-4").dataTable({
                                "bDestroy": true, // Destroy when resubmit form
                                "bProcessing": true,
                                "bServerSide": true,
                                "sAjaxSource": baseurl + "/customer/customers_rates/{{$id}}/search_ajax_datagrid/type",
                                "fnServerParams": function(aoData) {
                                    aoData.push({"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country}, {"name": "Trunk", "value": $searchFilter.Trunk}, {"name": "Effective", "value": $searchFilter.Effective},{"name": "Effected_Rates_on_off", "value": $searchFilter.Effected_Rates_on_off},{"name": "RoutinePlanFilter", "value": $searchFilter.RoutinePlanFilter});
                                    data_table_extra_params.length = 0;
                                    data_table_extra_params.push({"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country}, {"name": "Trunk", "value": $searchFilter.Trunk}, {"name": "Effective", "value": $searchFilter.Effective},{"name": "RoutinePlanFilter", "value": $searchFilter.RoutinePlanFilter},{"name":"Export","value":1},{"name": "Effected_Rates_on_off", "value": $searchFilter.Effected_Rates_on_off});
                                    console.log($searchFilter);
                                    console.log("Perm sent...");
                                },
                                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                                "sPaginationType": "bootstrap",
                                 "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                                 "aaSorting": [[8, "asc"]],
                                 "aoColumns":
                                        [
                                            {"bVisible": false, "bSortable": true
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
                                            {} //6Effective Date
                                        ],
                                        "oTableTools":
                                        {
                                            "aButtons": [
                                                {
                                                    "sExtends": "download",
                                                    "sButtonText": "EXCEL",
                                                    "sUrl": baseurl + "/customer/customers_rates/{{$id}}/search_ajax_datagrid/xlsx",
                                                    sButtonClass: "save-collection btn-sm"
                                                },
                                                {
                                                    "sExtends": "download",
                                                    "sButtonText": "CSV",
                                                    "sUrl": baseurl + "/customer/customers_rates/{{$id}}/search_ajax_datagrid/csv",
                                                    sButtonClass: "save-collection btn-sm"
                                                }
                                            ]
                                        },
                                "fnDrawCallback": function() {
                                    checkrouting($searchFilter.Trunk);

                                    $(".dataTables_wrapper select").select2({
                                        minimumResultsForSearch: -1
                                    });


                                }
                            });
                            @if(count($trunks_routing) ==0 || count($routine)  == 0)
                                $("#table-4 td:nth-child(6)").hide();
                            @endif

                        });
                        $("#ct_trunk").change(function(ev) {
                            currentval = $(this).val();
                            checkrouting(currentval);
                        });
                        $("#ct_trunk").trigger('change');

                        // Replace Checboxes
                        $(".pagination a").click(function(ev) {
                            replaceCheckboxes();
                        });

            });

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
                    $(".RoutinePlan").hide();

                    $("#table-4 td:nth-child(6)").hide();
                }else{
                    $("#customer-rate-table-search select[name='RoutinePlanFilter']").val('');
                    $(".RoutinePlan").show();
                    $("#table-4 td:nth-child(6)").show();
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
@stop



