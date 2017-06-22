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

         <div id="subscription_filter" method="get" action="#" >
                                <div class="panel panel-primary panel-collapse" data-collapsed="1">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            Filter
                                        </div>
                                        <div class="panel-options">
                                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                        </div>
                                    </div>
                                    <div class="panel-body" style="display: none;">
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
											<div class="col-sm-3">
												<p style="text-align: right">
												<button class="btn btn-primary btn-sm btn-icon icon-left" id="subscription_submit">
													<i class="entypo-search"></i>
													Search
												</button>
												</p>
											</div>
                                        </div>
                                    </div>
                                </div>
        </div>
        <div class="text-right">
            <a  id="add-subscription" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New</a>
            <div class="clear clearfix"><br></div>
        </div>

        <div class="dataTables_wrapper">
            <table id="table-subscription" class="table table-bordered datatable">
                <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="5%">Subscription</th>
                    <th width="20%">Invoice Description</th>
                    <th width="5%">Qty</th>
                    <th width="10%">Start Date</th>
                    <th width="10%">End Date</th>
                    <th width="5%">Activation Fee</th>
                    <th width="5%">Daily Fee</th>
                    <th width="5%">Weekly Fee</th>
                    <th width="10%">Monthly Fee</th>
                    <th width="10%">Quarterly Fee</th>
                    <th width="10%">Yearly Fee</th>
                    <th width="20%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <script type="text/javascript">
            /**
            * JQuery Plugin for dataTable
            * */
          //  var list_fields_activity  = ['SubscriptionName','InvoiceDescription','StartDate','EndDate'];      
            $("#subscription_filter").find('[name="SubscriptionName"]').val('');
            $("#subscription_filter").find('[name="SubscriptionInvoiceDescription"]').val('');
            var data_table_subscription;
            var account_id='{{$account->AccountID}}';
            var ServiceID='{{$ServiceID}}';
            var update_new_url;
            var postdata;

            jQuery(document).ready(function ($) {

                $(document).on('change','#subscription-form [name="AnnuallyFee"],#subscription-form [name="QuarterlyFee"],#subscription-form [name="MonthlyFee"]',function(e){
                    e.stopPropagation();
                    var name = $(this).attr('name');
                    var Yearly = '';
                    var quarterly = '';
                    var monthly = '';
                    var decimal_places = 2;
                    if(name=='AnnuallyFee'){
                        var t = $(this).val();
                        t = parseFloat(t);
                        monthly = t/12;
                        quarterly = monthly * 3;
                    }else if(name=='QuarterlyFee'){
                        var t = $(this).val();
                        t = parseFloat(t);
                        monthly = t / 3;
                        Yearly  = monthly * 12;
                    } else if(name=='MonthlyFee'){
                        var monthly = $(this).val();
                        monthly = parseFloat(monthly);
                        Yearly  = monthly * 12;
                        quarterly = monthly * 3;
                    }

                    var weekly =  parseFloat(monthly / 30 * 7);
                    var daily = parseFloat(monthly / 30);

                    if(Yearly != '') {
                        $('#subscription-form [name="AnnuallyFee"]').val(Yearly.toFixed(decimal_places));
                    }
                    if(quarterly != '') {
                        $('#subscription-form [name="QuarterlyFee"]').val(quarterly.toFixed(decimal_places));
                    }
                    if(monthly != '' && name != 'MonthlyFee') {
                        $('#subscription-form [name="MonthlyFee"]').val(monthly.toFixed(decimal_places));
                    }

                    $('#subscription-form [name="WeeklyFee"]').val(weekly.toFixed(decimal_places));
                    $('#subscription-form [name="DailyFee"]').val(daily.toFixed(decimal_places));
                });

            var list_fields  = ["SequenceNo", "Name", "InvoiceDescription", "Qty", "StartDate", "EndDate" ,"tblBillingSubscription.ActivationFee","tblBillingSubscription.DailyFee","tblBillingSubscription.WeeklyFee","tblBillingSubscription.MonthlyFee", "tblBillingSubscription.QuarterlyFee", "tblBillingSubscription.AnnuallyFee", "AccountSubscriptionID", "SubscriptionID","ExemptTax","AnnuallyFee","QuarterlyFee","MonthlyFee","WeeklyFee","DailyFee","ActivationFee"];
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
                                        {"name": "ServiceID", "value": ServiceID},
                                        {"name": "SubscriptionName", "value": $search.SubscriptionName},
                                        {"name": "SubscriptionInvoiceDescription", "value": $search.SubscriptionInvoiceDescription},
                                        {"name": "SubscriptionActive", "value": $search.SubscriptionActive});

                                data_table_extra_params.length = 0;
                                data_table_extra_params.push({"name": "account_id", "value": account_id},
                                        {"name": "ServiceID", "value": ServiceID},
                                        {"name": "SubscriptionName", "value": $search.SubscriptionName},
                                        {"name": "SubscriptionInvoiceDescription", "value": $search.SubscriptionInvoiceDescription},
                                        {"name": "SubscriptionActive", "value": $search.SubscriptionActive});

                            },
                            "iDisplayLength": 10,
                            "sPaginationType": "bootstrap",
                            "sDom": "<'row'<'col-xs-6 col-left 'l><'col-xs-6 col-right'f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                            "aaSorting": [[0, 'asc']],
                            "aoColumns": [
                                {  "bSortable": true },  // 0 Sequence NO
                                {  "bSortable": true },  // 1 Subscription Name
                        {  "bSortable": true },  // 2 InvoiceDescription
                        {  "bSortable": true },  // 3 Qty
                        {  "bSortable": true },  // 4 StartDate
                        {  "bSortable": true },  // 5 EndDate
                        {  "bSortable": true },  // 6 ActivationFee
                        {  "bSortable": true },  // 7 DailyFee
                        {  "bSortable": true },  // 8 WeeklyFee
                        {  "bSortable": true },  // 9 MonthlyFee
                        {  "bSortable": true },  // 10 QuarterlyFee
                        {  "bSortable": true },  // 11 AnnuallyFee
                        {                        // 12 Action
                           "bSortable": false,
                            mRender: function ( id, type, full ) {
                                 action = '<div class = "hiddenRowData" >';
                                 for(var i = 0 ; i< list_fields.length; i++){									 
									list_fields[i] =  list_fields[i].replace("tblBillingSubscription.",'');
                                    action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                 }
                                 action += '</div>';
                                 action += ' <a href="' + subscription_edit_url.replace("{id}",id) +'" title="Edit" class="edit-subscription btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>'
                                 action += ' <a href="' + subscription_delete_url.replace("{id}",id) +'" title="Delete" class="delete-subscription btn btn-danger btn-sm"><i class="entypo-trash"></i></a>'
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
                            //data_table_subscription.fnFilter('', 0);
                           //console.log('delete');
                          // $('#subscription_submit').trigger('click');
                       }
                       return false;
                });

               $("#subscription-form").submit(function(e){

                   e.preventDefault();
                   var _url  = $(this).attr("action");
                   submit_ajax_datatable(_url,$(this).serialize(),0,data_table_subscription);
                   //data_table_subscription.fnFilter('', 0);
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
                                    $("#subscription-form [name='AnnuallyFee']").val(response.AnnuallyFee);
                                    $("#subscription-form [name='QuarterlyFee']").val(response.QuarterlyFee);
									$("#subscription-form [name='MonthlyFee']").val(response.MonthlyFee);
									$("#subscription-form [name='WeeklyFee']").val(response.WeeklyFee);
									$("#subscription-form [name='DailyFee']").val(response.DailyFee);
									$("#subscription-form [name='ActivationFee']").val(response.ActivationFee);
								}
							}
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
                    <div class="row">
                        <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Subscription</label>
                            {{ Form::select('SubscriptionID', BillingSubscription::getSubscriptionsArray($account->CompanyId,$account->CurrencyId) , '' , array("class"=>"select2")) }}
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Invoice Description</label>
                                <input type="text" name="InvoiceDescription" class="form-control" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">No</label>
                                <input type="text" name="SequenceNo" class="form-control" placeholder="AUTO" value=""  />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Qty</label>
                                <input type="text" name="Qty" class="form-control" value="" />
                            </div>
                        </div>
                    </div>
                    <!-- -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="AnnuallyFee" class="control-label">Yearly Fee</label>
                                <input type="text" name="AnnuallyFee" class="form-control"   maxlength="10" id="AnnuallyFee" placeholder="" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="QuarterlyFee" class="control-label">Quarterly Fee</label>
                                <input type="text" name="QuarterlyFee" class="form-control"   maxlength="10" id="QuarterlyFee" placeholder="" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="MonthlyFee" class="control-label">Monthly Fee</label>
                               <input type="text" name="MonthlyFee" class="form-control"   maxlength="10" id="MonthlyFee" placeholder="" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="WeeklyFee" class="control-label">Weekly Fee</label>
                                <input type="text" name="WeeklyFee" id="WeeklyFee" class="form-control" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-12">
                            <div class="form-group">
                                <label for="DailyFee" class="control-label">Daily Fee</label>
                                <input type="text" name="DailyFee" id="DailyFee" class="form-control" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-12">
                            <div class="form-group">
                                <label for="ActivationFee" class="control-label">Activation Fee</label>
                                <input type="text" name="ActivationFee" id="ActivationFee" class="form-control" value="" />
                            </div>
                        </div>
                    </div>
                    <!-- -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Start Date</label>
                                <input type="text" name="StartDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value=""   />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">End Date</label>
                                <input type="text" name="EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value=""  />
                            </div>
                        </div>
                    </div>
                    <div class="row">
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
                </div>
                <input type="hidden" name="AccountSubscriptionID">
                <input type="hidden" name="ServiceID" value="{{$ServiceID}}">
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