@extends('layout.reseller.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="#"><i class="entypo-home"></i>Subscriptions</a>
    </li>
</ol>
<h3>Subscriptions</h3>

@include('includes.errors')
@include('includes.success')


<div class="row">
    <div class="col-md-12">
        <form id="subscription_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Filter
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                <label for="field-1" class="col-sm-1 control-label">Name</label>
                <div class="col-sm-2">
                  <input type="text" name="SubscriptionName" class="form-control" value="" />
                </div>
                <label for="field-1" class="col-sm-1 control-label">Invoice Description</label>
                <div class="col-sm-2">
                  <input type="text" name="SubscriptionInvoiceDescription" class="form-control" value="" />
                </div>
                <label for="field-1" class="col-sm-1 control-label">Active</label>
                <div class="col-sm-2">
                  <p class="make-switch switch-small">
                    <input id="SubscriptionActive" name="SubscriptionActive" type="checkbox" value="1" checked="checked" >
                  </p>
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

<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
       <th width="5%">Subscription</th>
          <th width="25%">Invoice Description</th>
          <th width="10%">Qty</th>
          <th width="10%">StartDate</th>
          <th width="10%">EndDate</th>
          <th width="10%">ActivationFee</th>
          <th width="10%">DailyFee</th>
          <th width="10%">WeeklyFee</th>
          <th width="10%">MonthlyFee</th>
    </tr>
    </thead>
    <tbody>


    </tbody>
</table>

<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");
        //show_loading_bar(40);
		  var list_fields  = ["Name", "InvoiceDescription", "Qty", "StartDate", "EndDate" ,"tblBillingSubscription.ActivationFee","tblBillingSubscription.DailyFee","tblBillingSubscription.WeeklyFee","tblBillingSubscription.MonthlyFee", "AccountSubscriptionID", "SubscriptionID","ExemptTax"];

     		$searchFilter.SubscriptionName 					= 	$("#subscription_filter [name='SubscriptionName']").val();
            $searchFilter.SubscriptionInvoiceDescription 	= 	$("#subscription_filter [name='SubscriptionInvoiceDescription']").val();
            $searchFilter.SubscriptionActive 				= 	$("#subscription_filter [name='SubscriptionActive']").prop("checked");

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/resellers/subscription/ajax_datagrid",
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[1, 'desc']],
             "fnServerParams": function(aoData) {
                aoData.push( 			{"name": "SubscriptionName", "value": $searchFilter.SubscriptionName},
                                        {"name": "SubscriptionInvoiceDescription", "value": $searchFilter.SubscriptionInvoiceDescription},
                                        {"name": "SubscriptionActive", "value": $searchFilter.SubscriptionActive}
										);
                data_table_extra_params.length = 0;
                data_table_extra_params.push(				
                                        {"name": "SubscriptionName", "value": $searchFilter.SubscriptionName},
                                        {"name": "SubscriptionInvoiceDescription", "value": $searchFilter.SubscriptionInvoiceDescription},
                                        {"name": "SubscriptionActive", "value": $searchFilter.SubscriptionActive}
				);
            },
             "aoColumns":
                     [					 
                        {  "bSortable": true },  // 0 Subscription Name
                        {  "bSortable": true },  // 1 InvoiceDescription
                        {  "bSortable": true },  // 2 Qty
                        {  "bSortable": true },  // 3 StartDate
                        {  "bSortable": true },  // 4 EndDate
                        {  "bSortable": true },  // 5 ActivationFee
                        {  "bSortable": true },  // 6 DailyFee
                        {  "bSortable": true },  // 7 WeeklyFee
                        {  "bSortable": true },  // 8 MonthlyFee             
					],            
           "fnDrawCallback": function() {
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });          
			   }

        });
        $("#subscription_filter").submit(function(e){
            e.preventDefault();
            $searchFilter.SubscriptionName 					= 	$("#subscription_filter [name='SubscriptionName']").val();
            $searchFilter.SubscriptionInvoiceDescription 	= 	$("#subscription_filter [name='SubscriptionInvoiceDescription']").val();
            $searchFilter.SubscriptionActive 				= 	$("#subscription_filter [name='SubscriptionActive']").prop("checked");

            data_table.fnFilter('', 0);
            return false;
        });	
});

</script>
<style>
.export-data{
    display:none !important;
}
</style>
@stop
@section('footer_ext')
@parent
@stop