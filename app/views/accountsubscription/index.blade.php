<style>
#table-subscription_processing{
    position: absolute;
}
</style>
<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Subscriptions
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
         <div class="text-right">
              <a  id="add-subscription" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add Subscription</a>
              <div class="clear clearfix"><br></div>
        </div>
         <div id="subscription_filter" method="get" action="#" >
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
                                            <button class="btn btn-primary btn-sm btn-icon icon-left" id="subscription_submit">
                                                <i class="entypo-search"></i>
                                                Search
                                            </button>
                                        </p>
                                    </div>
                                </div>
        </div>

        <table id="table-subscription" class="table table-bordered datatable">
            <thead>
            <tr>
                <th width="5%">No</th>
                <th width="5%">Subscription</th>
                <th width="20%">Invoice Description</th>
                <th width="5%">Qty</th>
                <th width="10%">StartDate</th>
                <th width="10%">EndDate</th>
                <th width="5%">ActivationFee</th>
                <th width="5%">DailyFee</th>
                <th width="10%">WeeklyFee</th>
                <th width="10%">MonthlyFee</th>
                <th width="15%">Action</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <script type="text/javascript">
            /**
            * JQuery Plugin for dataTable
            * */
          //  var list_fields_activity  = ['SubscriptionName','InvoiceDescription','StartDate','EndDate'];      
            $("#subscription_filter").find('[name="SubscriptionName"]').val('');
            $("#subscription_filter").find('[name="SubscriptionInvoiceDescription"]').val('');
            var data_table_subscription;
            var account_id={{$account->AccountID}};            
            var update_new_url;
            var postdata;

            jQuery(document).ready(function ($) {
				
				$("#subscription-form [name=MonthlyFee]").change(function(){
        var monthly = $(this).val();
        weekly =  parseFloat(monthly / 30 * 7);
        daily = parseFloat(monthly / 30);

        decimal_places = 2;

        $("#subscription-form [name=WeeklyFee]").val(weekly.toFixed(decimal_places));
        $("#subscription-form [name=DailyFee]").val(daily.toFixed(decimal_places));
});
				
                var list_fields  = ["Name", "InvoiceDescription", "Qty", "StartDate", "EndDate" ,"tblBillingSubscription.ActivationFee","tblBillingSubscription.DailyFee","tblBillingSubscription.WeeklyFee","tblBillingSubscription.MonthlyFee", "AccountSubscriptionID", "SubscriptionID","ExemptTax","MonthlyFee","WeeklyFee","DailyFee","ActivationFee"];
            public_vars.$body = $("body");
            var $search = {};
            var subscription_add_url = baseurl + "/accounts/{{$account->AccountID}}/subscription/store";
            var subscription_edit_url = baseurl + "/accounts/{{$account->AccountID}}/subscription/{id}/update";
            var subscription_delete_url = baseurl + "/accounts/{{$account->AccountID}}/subscription/{id}/delete";
            var subscription_datagrid_url = baseurl + "/accounts/{{$account->AccountID}}/subscription/ajax_datagrid";              
            $("#subscription_submit").click(function(e) {                
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
                                aoData.push({"name": "account_id", "value": account_id},
                                        {"name": "SubscriptionName", "value": $search.SubscriptionName},
                                        {"name": "SubscriptionInvoiceDescription", "value": $search.SubscriptionInvoiceDescription},
                                        {"name": "SubscriptionActive", "value": $search.SubscriptionActive});

                                data_table_extra_params.length = 0;
                                data_table_extra_params.push({"name": "account_id", "value": account_id},
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
                                {                        // 9 Action
                           "bSortable": false,
                            mRender: function ( id, type, full ) {
                                 action = '<div class = "hiddenRowData" >';
                                 for(var i = 0 ; i< list_fields.length; i++){									 
									list_fields[i] =  list_fields[i].replace("tblBillingSubscription.",'');
                                    action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                 }
                                 action += '</div>';
                                 action += ' <a href="' + subscription_edit_url.replace("{id}",id) +'" class="edit-subscription btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>'
                                 action += ' <a href="' + subscription_delete_url.replace("{id}",id) +'" class="delete-subscription btn btn-danger btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Delete </a>'
                                 return action;
                            }
                          }
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
                    
                    /*$("#subscription_filter").submit(function(e) {
                        e.preventDefault();
                        $search.InvoiceDescription = $("#subscription_filter").find('[name="InvoiceDescription"]').val();
                        $search.StartDate = $("#subscription_filter").find('[name="StartDate"]').val();
                        $search.EndDate = $("#subscription_filter").find('[name="EndDate"]').val();
                        data_table_subscription.fnFilter('', 0);
                    });     */
                $('#subscription_submit').trigger('click');
                //inst.myMethod('I am a method');
                $('#add-subscription').click(function(ev){
                        ev.preventDefault();
                        $('#subscription-form').trigger("reset");
                        $('#modal-subscription h4').html('Add Subscription');
                        $("#subscription-form [name=SubscriptionID]").select2().select2('val',"");

                        $('#subscription-form').attr("action",subscription_add_url);
                        $('#modal-subscription').modal('show');                        
                });
                $('table tbody').on('click', '.edit-subscription', function (ev) {
                        ev.preventDefault();
                        $('#subscription-form').trigger("reset");
                        var edit_url  = $(this).attr("href");
                        $('#subscription-form').attr("action",edit_url);
                        $('#modal-subscription h4').html('Edit Subscription');
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){
                            $("#subscription-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            if(list_fields[i] == 'SubscriptionID'){
                                $("#subscription-form [name='"+list_fields[i]+"']").select2().select2('val',cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }else if(list_fields[i] == 'ExemptTax'){
                                if(cur_obj.find("input[name='ExemptTax']").val() == 1 ){
                                    $('[name="ExemptTax"]').prop('checked',true);
                                }else{
                                    $('[name="ExemptTax"]').prop('checked',false);
                                }

                            }
                        }
                        $('#modal-subscription').modal('show');
                });
                $('table tbody').on('click', '.delete-subscription', function (ev) {
                        ev.preventDefault();
                        result = confirm("Are you Sure?");
                       if(result){
                           var delete_url  = $(this).attr("href");
                           submit_ajax_datatable( delete_url,"",0,data_table_subscription);
                            data_table_subscription.fnFilter('', 0);
                           //console.log('delete');
                          // $('#subscription_submit').trigger('click');
                       }
                       return false;
                });

               $("#subscription-form").submit(function(e){

                   e.preventDefault();
                   var _url  = $(this).attr("action");
                   submit_ajax_datatable(_url,$(this).serialize(),0,data_table_subscription);
                   data_table_subscription.fnFilter('', 0);
                   //console.log('edit');
                  // $('#subscription_submit').trigger('click');
               });
               $('#subscription-form [name="SubscriptionID"]').change(function(e){

                       id = $(this).val();
				   		var UrlGetSubscription1 	= 	"<?php echo URL::to('/billing_subscription/{id}/getSubscriptionData_ajax'); ?>";
					   	var UrlGetSubscription		=	UrlGetSubscription1.replace( '{id}', id );
					 $.ajax({
						url: UrlGetSubscription,
						type: 'POST',
						dataType: 'json',
						async :false,
						
						success: function(response) {
								if(response){
									$("#subscription-form [name='InvoiceDescription']").val(response.InvoiceLineDescription);
									$("#subscription-form [name='MonthlyFee']").val(response.MonthlyFee);
									$("#subscription-form [name='WeeklyFee']").val(response.WeeklyFee);
									$("#subscription-form [name='DailyFee']").val(response.DailyFee);
									$("#subscription-form [name='DailyFee']").val(response.DailyFee);
									$("#subscription-form [name='ActivationFee']").val(response.ActivationFee);
								}
							},
					});	
					   
                    /*   getTableFieldValue("billing_subscription",id,"InvoiceLineDescription",function(description){
                           if( description != undefined && description.length > 0){
                                $("#subscription-form [name='InvoiceDescription']").val(description);
                           }

                       });*/


                });

            });
            </script>
    </div>
</div>



@section('footer_ext')
@parent

<div class="modal fade in" id="modal-subscription">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="subscription-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Subscription</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Subscription</label>
                            {{ Form::select('SubscriptionID', BillingSubscription::getSubscriptionsArray($account->CompanyId,$account->CurrencyId) , '' , array("class"=>"select2")) }}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Invoice Description</label>
                            <input type="text" name="InvoiceDescription" class="form-control" value="" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Qty</label>
                            <input type="text" name="Qty" class="form-control" value="" />
                        </div>
                    </div>
                    <!-- -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="MonthlyFee" class="control-label">Monthly Fee</label>
                           <input type="text" name="MonthlyFee" class="form-control"   maxlength="10" id="MonthlyFee" placeholder="" value="" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="WeeklyFee" class="control-label">Weekly Fee</label>
                            <input type="text" name="WeeklyFee" id="WeeklyFee" class="form-control" value="" />
                        </div>
                    </div>
                    
                     <div class="col-md-12">
                        <div class="form-group">
                            <label for="DailyFee" class="control-label">Daily Fee</label>
                            <input type="text" name="DailyFee" id="DailyFee" class="form-control" value="" />
                        </div>
                    </div>
                     <div class="col-md-12">
                        <div class="form-group">
                            <label for="ActivationFee" class="control-label">Activation Fee</label>
                            <input type="text" name="ActivationFee" id="ActivationFee" class="form-control" value="" />
                        </div>
                    </div>
                    <!-- -->
                    
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Start Date</label>
                            <input type="text" name="StartDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value=""   />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">End Date</label>
                            <input type="text" name="EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value=""  />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Exempt From Tax</label>
                            <div class="clear">
                                <p class="make-switch switch-small">
                                    <input type="checkbox" name="ExemptTax" value="0">
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
                <input type="hidden" name="AccountSubscriptionID">
                <div class="modal-footer">
                     <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading...">
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