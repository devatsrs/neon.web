@extends('layout.reseller.main')
@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="#"><i class="entypo-home"></i>Subscriptions</a> </li>
</ol>
<h3>Subscriptions</h3>
<div class="tab-content">
  <div class="tab-pane active" id="customer_rate_tab_content">
    <div class="row">
      <div class="col-md-12">
        <form role="form" id="subscription_filter" method="post"   class="form-horizontal form-groups-bordered validate" novalidate>
          <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
              <div class="panel-title"> Search </div>
              <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
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
                <button class="btn btn-primary btn-sm btn-icon icon-left" id="subscription_submit"> <i class="entypo-search"></i> Search </button>
              </p>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="clear"></div>
    <br>
    <table id="table-subscription" class="table table-bordered datatable">
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
            /**
            * JQuery Plugin for dataTable
            * */
            $("#subscription_filter").find('[name="SubscriptionName"]').val('');
            $("#subscription_filter").find('[name="SubscriptionInvoiceDescription"]').val('');
            var data_table_subscription;                
            var postdata;

            jQuery(document).ready(function ($) {
                var list_fields  = ["Name", "InvoiceDescription", "Qty", "StartDate", "EndDate" ,"tblBillingSubscription.ActivationFee","tblBillingSubscription.DailyFee","tblBillingSubscription.WeeklyFee","tblBillingSubscription.MonthlyFee", "AccountSubscriptionID", "SubscriptionID","ExemptTax"];
            public_vars.$body 				= 	$("body");
            var $search 				  	= 	{};
            var subscription_datagrid_url 	= 	baseurl + "/resellers/subscription/ajax_datagrid";   
			           
            $("#subscription_filter").submit(function(e) {                
                e.preventDefault();                 
                    $search.SubscriptionName = $("#subscription_filter").find('[name="SubscriptionName"]').val();
                    $search.SubscriptionInvoiceDescription = $("#subscription_filter").find('[name="SubscriptionInvoiceDescription"]').val();
                    $search.SubscriptionActive = $("#subscription_filter").find("[name='SubscriptionActive']").prop("checked");
                        data_table_subscription = $("#table-subscription").dataTable({
                            "bDestroy": true,
                            "bProcessing":true,
                            "bServerSide": true,
                            "sAjaxSource": subscription_datagrid_url,
                            "fnServerParams": function (aoData) {
                                aoData.push(
                                        {"name": "SubscriptionName", "value": $search.SubscriptionName},
                                        {"name": "SubscriptionInvoiceDescription", "value": $search.SubscriptionInvoiceDescription},
                                        {"name": "SubscriptionActive", "value": $search.SubscriptionActive});

                                data_table_extra_params.length = 0;
                                data_table_extra_params.push(
                                        {"name": "SubscriptionName", "value": $search.SubscriptionName},
                                        {"name": "SubscriptionInvoiceDescription", "value": $search.SubscriptionInvoiceDescription},
                                        {"name": "SubscriptionActive", "value": $search.SubscriptionActive});

                            },
                            "iDisplayLength": '{{Config::get('app.pageSize')}}',
                            "sPaginationType": "bootstrap",
                            "sDom": "<'row'r>",
                            "aaSorting": [[0, 'asc']],
                            "aoColumns": [
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
                            "oTableTools": {
                                "aButtons": [
                                    {
                                        "sExtends": "download",
                                        "sButtonText": "Export Data",
                                        "sUrl": subscription_datagrid_url,
                                        sButtonClass: "save-collection"
                                    }
                                ]
                            },
                            "fnDrawCallback": function() {
                                               $(".dataTables_wrapper select").select2({
                                                   minimumResultsForSearch: -1
                                               });
                             }

                        });                          
                    }); 

                $('#subscription_filter').trigger('submit');            
            });
            </script> 
    @include('includes.errors')
    @include('includes.success') </div>
</div>
@stop
@section('footer_ext')
@parent
@stop 