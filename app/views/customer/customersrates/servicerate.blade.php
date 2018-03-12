@extends('layout.customer.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="#"><i class="entypo-home"></i>@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_SERVICE_RATE_TITLE")</a>
        </li>
    </ol>

<h3>@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_SERVICE_RATE_TITLE")</h3>
{{--@include('accounts.errormessage')--}}
<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
    @if(empty(is_reseller()))
    <li>
        <a href="{{ URL::to('/customer/customers_rates') }}" >
            @lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_SETTINGS_TITLE")
        </a>
    </li>
    @endif
    <li>
        <a href="{{ URL::to('/customer/customers_rates/rate') }}" >
            @lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_TITLE")
        </a>
    </li>
    @if(isset($displayinbound) && $displayinbound>0)
	<li>
		<a href="{{ URL::to('/customer/customers_rates/inboundrate') }}" >
            @lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_INBOUND_RATE_TITLE")
		</a>
	</li>
	@endif
    <li class="active">
        <a href="{{ URL::to('/customer/customers_rates/servicerate') }}" >
            @lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_SERVICE_RATE_TITLE")
        </a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane active" id="customer_rate_tab_content">




        <div class="row">
            <div class="col-md-12">
                <form role="form" id="customer-rate-table-search" method="post"  action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                   <div class="panel panel-primary" data-collapsed="0">
                       <div class="panel-heading">
                           <div class="panel-title">
                               @lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_FILTER_SEARCH_TITLE")
                           </div>

                           <div class="panel-options">
                               <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                           </div>
                       </div>

                       <div class="panel-body">
                           <div class="form-group">
                               <label for="field-1" class="col-sm-1 control-label">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_FILTER_FIELD_CODE")</label>
                               <div class="col-sm-2">
                                   <input type="text" name="Code" class="form-control" id="field-1" placeholder="" value="{{Input::get('Code')}}" />
                               </div>

                               <label class="col-sm-1 control-label">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_FILTER_FIELD_DESCRIPTION")</label>
                               <div class="col-sm-2">
                                   <input type="text" name="Description" class="form-control" id="field-1" placeholder="" value="{{Input::get('Description')}}" />

                               </div>
                               <label for="field-1" class="col-sm-1 control-label">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_FILTER_FIELD_EFFECTIVE")</label>
                               <div class="col-sm-2">
                                   <select name="Effective" class="select2 small" data-allow-clear="true" data-placeholder="Select Effective">
                                       <option value="Now">Now</option>
                                       <option value="Future">Future</option>
                                       <option value="All">All</option>
                                   </select>
                               </div>

                           </div>
                           <div class="form-group">
                               <label for="field-1" class="col-sm-1 control-label">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_FILTER_FIELD_COUNTRY")</label>
                               <div class="col-sm-3">
                                   {{ Form::select('Country', $countries, Input::get('Country') , array("class"=>"select2")) }}
                               </div>
                               <label for="field-1" class="col-sm-1 control-label">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_SERVICE_RATE_FILTER_FIELD_SERVICE")</label>
                               <div class="col-sm-3">
                                   {{ Form::select('Service', $services, Input::get('Service') , array("class"=>"select2")) }}
                               </div>
                               <label for="field-1" class="col-sm-1 control-label">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_SERVICE_RATE_FILTER_FIELD_RATE_TYPE")</label>
                               <div class="col-sm-2">
                                   {{ Form::select('RateType', $type, Input::get('RateType') , array("class"=>"select2 small")) }}
                               </div>

                           </div>



                           <p style="text-align: right;">
                               <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                                   <i class="entypo-search"></i>
                                   @lang("routes.BUTTON_SEARCH_CAPTION")
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
                    <th width="5%">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_TBL_CODE")</th>
                    <th width="20%">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_TBL_DESCRIPTION")</th>
                    <th width="5%">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_TBL_INTERVAL_1")</th>
                    <th width="5%">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_TBL_INTERVAL_N")</th>
                    <th width="5%">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_TBL_CONNECTION_FEE")</th>
                    <th width="5%">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_TBL_RATE") ({{$CurrencySymbol}})</th>
                    <th width="10%">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_TBL_EFFECTIVE_DATE")</th>
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
            var list_fields  = ['RateID','Code','Description','Interval1','IntervalN','ConnectionFee','Rate','EffectiveDate','LastModifiedDate','LastModifiedBy','CustomerRateId','TrunkID','RateTableRateId'];
                    jQuery(document).ready(function($) {

                        $("#customer-rate-table-search").submit(function(e) {

                            e.preventDefault();
                            $searchFilter.Code = $("#customer-rate-table-search input[name='Code']").val();
                            $searchFilter.Description = $("#customer-rate-table-search input[name='Description']").val();
                            $searchFilter.Country = $("#customer-rate-table-search select[name='Country']").val();
                            $searchFilter.Trunk = $("#customer-rate-table-search select[name='Trunk']").val();
                            $searchFilter.Effective = $("#customer-rate-table-search select[name='Effective']").val();
                            $searchFilter.Effected_Rates_on_off = $("#customer-rate-table-search input[name='Effected_Rates_on_off']").prop("checked");
							$searchFilter.Service = $("#customer-rate-table-search select[name='Service']").val();
							$searchFilter.RateType = $("#customer-rate-table-search select[name='RateType']").val();

                            if(typeof $searchFilter.Service  == 'undefined' || $searchFilter.Service == '' ) {
                                setTimeout(function () {
                                    $('.btn').button('reset');
                                }, 10);
                                toastr.error("Please Select a Service", "Error", toastr_opts);
                            }

                            if(typeof $searchFilter.RateType  == 'undefined' || $searchFilter.RateType == '' ){
                                setTimeout(function(){
                                    $('.btn').button('reset');
                                },10);
                                toastr.error("Please Select a Rate Type", "Error", toastr_opts);
                                return false;
                            }

                            data_table = $("#table-4").dataTable({
                                "oLanguage": {
                                    "sUrl": baseurl + "/translate/datatable_Label"
                                },
                            "bDestroy": true, // Destroy when resubmit form
                            "bProcessing": true,
                            "bServerSide": true,
                            "sAjaxSource": baseurl + "/customer/customers_rates/{{$id}}/search_service_ajax_datagrid/type",
                            "fnServerParams": function(aoData) {
                                aoData.push({"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country}, {"name": "Trunk", "value": $searchFilter.Trunk}, {"name": "Effective", "value": $searchFilter.Effective},{"name": "Effected_Rates_on_off", "value": $searchFilter.Effected_Rates_on_off},{"name": "Service", "value": $searchFilter.Service},{"name": "RateType", "value": $searchFilter.RateType});
                                data_table_extra_params.length = 0;
                                data_table_extra_params.push({"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country}, {"name": "Trunk", "value": $searchFilter.Trunk}, {"name": "Effective", "value": $searchFilter.Effective},{"name": "Effected_Rates_on_off", "value": $searchFilter.Effected_Rates_on_off},{"name": "Service", "value": $searchFilter.Service},{"name": "RateType", "value": $searchFilter.RateType},{"name":"Export","value":1});
                                console.log($searchFilter);
                                console.log("Perm sent...");
                            },
                            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                            "sPaginationType": "bootstrap",
                             "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                             "aaSorting": [[7, "asc"]],
                             "aoColumns":
                                    [
                                        {"bVisible": false, "bSortable": true}, //0Checkbox
                                        {}, //1 Code
                                        {}, //2Description
                                        {}, //3Interval1
                                        {}, //4IntervalN
                                        {}, //5 ConnectionFee
                                        {}, //5Rate
                                        {} //6Effective Date
                                    ],
                                    "oTableTools":
                                    {
                                        "aButtons": [
                                            {
                                                "sExtends": "download",
                                                "sButtonText": "@lang('routes.BUTTON_EXPORT_EXCEL_CAPTION')",
                                                "sUrl": baseurl + "/customer/customers_rates/{{$id}}/search_service_ajax_datagrid/xlsx",
                                                sButtonClass: "save-collection btn-sm"
                                            },
                                            {
                                                "sExtends": "download",
                                                "sButtonText": "@lang('routes.BUTTON_EXPORT_CSV_CAPTION')",
                                                "sUrl": baseurl + "/customer/customers_rates/{{$id}}/search_service_ajax_datagrid/csv",
                                                sButtonClass: "save-collection btn-sm"
                                            }
                                        ]
                                    },
                            "fnDrawCallback": function() {
                              //  checkrouting($searchFilter.Trunk);

                                $(".dataTables_wrapper select").select2({
                                    minimumResultsForSearch: -1
                                });


                            }
                        });

                    });

                    // Replace Checboxes
                    $(".pagination a").click(function(ev) {
                        replaceCheckboxes();
                    });

            });

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



