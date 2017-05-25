@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>  
  <li class="active"> <strong>Account Subscriptions</strong> </li>
</ol>
<h3>Account Subscriptions</h3>
@include('includes.errors')
@include('includes.success')
<div class="row">
  <div class="col-md-12">
    <form id="subscription_filter" method="get" action="#" class="form-horizontal form-groups-bordered validate" >
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title"> Filter </div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body">
          <div class="form-group">
          <label for="field-1" class="col-sm-1 control-label">Account</label>
          <div class="col-sm-2">{{ Form::select('AccountID', $accounts, $SelectedAccount->AccountID, array("id"=>"filter_AccountID", "class"=>"select2 filter_AccountID","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}</div>
          <label for="field-1" class="col-sm-1 control-label">Service</label>
          <div class="col-sm-2">{{ Form::select('ServiceID', $services,'', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Service")) }}</div>
          
            <label for="field-1" class="col-sm-1 control-label">Name</label>
            <div class="col-sm-2">
              <input type="text" name="SubscriptionName" class="form-control" value="" />
            </div>            
            <label for="field-1" class="col-sm-1 control-label">Active</label>
            <div class="col-sm-1">
              <p class="make-switch switch-small">
                <input id="Active" name="Active" type="checkbox" value="1" checked="checked" >
              </p>
            </div>            
          </div>
              <p style="text-align: right">
                <button class="btn btn-primary btn-sm btn-icon icon-left" id="subscription_submit"> <i class="entypo-search"></i> Search </button>
              </p>
        </div>
      </div>
    </form>
  </div>
</div>
<div class="clear"></div>
<div class="text-right"> <a  id="add-subscription" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New</a>
  <div class="clear clearfix"><br>
  </div>
</div>
<table id="table-subscription" class="table table-bordered datatable">
  <thead>
    <tr>
      <th width="5%">No</th>
      <th width="5%">Account</th>
      <th width="5%">Service</th>
      <th width="5%">Subscription</th>
      <th  width="5%">Invoice Description</th>
      <th width="5%">Qty</th>
      <th width="10%">Start Date</th>
      <th width="10%">End Date</th>
      <th width="5%">Activation Fee</th>
      <th width="5%">Daily Fee</th>
      <th width="5%">Weekly Fee</th>
      <th width="10%">Monthly Fee</th>
      <th width="10%">Quarterly Fee</th>
      <th width="10%">Annually Fee</th>
      <th width="20%">Action</th>
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
            var data_table_subscription;
            var account_id=$("#subscription_filter").find('[name="AccountID"]').val(); 
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

            var list_fields  = ["SequenceNo","AccountName","ServiceName", "Name", "InvoiceDescription", "Qty", "StartDate", "EndDate" ,"tblBillingSubscription.ActivationFee","tblBillingSubscription.DailyFee","tblBillingSubscription.WeeklyFee","tblBillingSubscription.MonthlyFee", "tblBillingSubscription.QuarterlyFee", "tblBillingSubscription.AnnuallyFee", "AccountSubscriptionID", "SubscriptionID","ExemptTax","AccountID",'ServiceID'];
            public_vars.$body = $("body");
            var $search = {};
            var subscription_add_url = baseurl + "/account_subscription/{id}/store";            
            var subscription_datagrid_url = baseurl + "/account_subscription/ajax_datagrid_page";     
           
		    $("#subscription_submit").click(function(e) {                
                e.preventDefault();
                 
                    $search.Name    	=  $("#subscription_filter").find('[name="SubscriptionName"]').val();
					$search.Account 	=  $("#subscription_filter").find('[name="AccountID"]').val();
					$search.ServiceID   =  $("#subscription_filter").find('[name="ServiceID"]').val();										
                    $search.Active  	=  $("#subscription_filter").find("[name='Active']").prop("checked");
					
		 data_table  = $("#table-subscription").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": subscription_datagrid_url,
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[3, 'desc']],
             "fnServerParams": function(aoData) {				
                aoData.push({"name":"Name","value":$search.Name},{"name":"AccountID","value":$search.Account},{"name":"ServiceID","value":$search.ServiceID},{"name":"Active","value":$search.Active});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"Name","value":$search.Name},{"name":"AccountID","value":$search.Account},{"name":"ServiceID","value":$search.ServiceID},{"name":"Active","value":$search.Active},{ "name": "Export", "value": 1});
            },
             "aoColumns":
            [
                        {  "bSortable": true },  // 0 Sequence NO
                        {  "bSortable": true },  // 1 account						
                        {  "bSortable": true },  // 2 service
						{  "bSortable": true },  // 2 Name
						{                          // InvoiceDescription
                           "bSortable": true,
                            mRender: function ( id, type, full ) {return '....';}
                          },  // 2 InvoiceDescription
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
								var edit_account = 0;
								
								var subscription_edit_url = baseurl + "/accounts/{AccountID}/subscription/{id}/update";
					            var subscription_delete_url = baseurl + "/accounts/{AccountID}/subscription/{id}/delete";
                                 action = '<div class = "hiddenRowData" >';
                                 for(var i = 0 ; i< list_fields.length; i++){									 
									list_fields[i] =  list_fields[i].replace("tblBillingSubscription.",'');
                                    action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';							if(list_fields[i]=='AccountID'){
										edit_account = full[i];
									}
                                 }
								 subscription_edit_url = subscription_edit_url.replace("{id}",id);
								 subscription_edit_url = subscription_edit_url.replace("{AccountID}",edit_account);
								 
								 subscription_delete_url = subscription_delete_url.replace("{id}",id);
								 subscription_delete_url = subscription_delete_url.replace("{AccountID}",edit_account);
								 
                                 action += '</div>';
                                 action += ' <a href="' + subscription_edit_url+'" title="Edit" class="edit-subscription btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>'
                                 action += ' <a href="' + subscription_delete_url+'" title="Delete" class="delete-subscription btn btn-danger btn-sm"><i class="entypo-trash"></i></a>'
                                 return action;
                            }
                          }
                         ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": subscription_datagrid_url+"/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": subscription_datagrid_url + "/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
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
                    
                
                $('#subscription_submit').trigger('click');
                //inst.myMethod('I am a method');
                $('#add-subscription').click(function(ev){
                        ev.preventDefault();
                        $('#subscription-form').trigger("reset");
                        $('#modal-subscription h4').html('Add Subscription');
                        $("#subscription-form [name=SubscriptionID]").select2().select2('val',"");

                        $('#subscription-form').attr("action",subscription_add_url);
						$('#modal-subscription').find('.dropdown1').removeAttr('disabled');
						document.getElementById('subscription-form').reset();
						$('#AccountID_add_change').val($('#filter_AccountID').val()); 
						$('#ServiceID_add_change').change();
						$('#SubscriptionID_add_change').change();
						$('#AccountID_add_change').change();
						//$('.dropdown1').change();						
                        $('#modal-subscription').modal('show');                        
                });
                $('table tbody').on('click', '.edit-subscription', function (ev) {
                        ev.preventDefault();
                        $('#modal-edit-subscription').trigger("reset");
                        var edit_url  = $(this).attr("href");
                        $('#subscription-form-edit').attr("action",edit_url);
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){ 
                            $("#subscription-form-edit [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            if(list_fields[i] == 'SubscriptionID'){
                                $("#subscription-form-edit [name='"+list_fields[i]+"']").select2().select2('val',cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }else if(list_fields[i] == 'ExemptTax'){
                                if(cur_obj.find("input[name='ExemptTax']").val() == 1 ){
                                    $('[name="ExemptTax"]').prop('checked',true);
                                }else{
                                    $('[name="ExemptTax"]').prop('checked',false);
                                }

                            }
                        }
                        $('#modal-edit-subscription').modal('show');
                });
                $('table tbody').on('click', '.delete-subscription', function (ev) {
                        ev.preventDefault();
                        result = confirm("Are you Sure?");
                       if(result){
                           var delete_url  = $(this).attr("href");
                           submit_ajax_datatable( delete_url,"",0,data_table);
                            //data_table_subscription.fnFilter('', 0);
                           //console.log('delete');
                          // $('#subscription_submit').trigger('click');
                       }
                       return false;
                });
				
				$(document).on("change","#AccountID_add_change",function(){
					var account_change = $(this).val();
					if(account_change){
						/*var UrlGetSubscription1 	= 	"<?php echo URL::to('/account_subscription/{id}/get_services'); ?>";
						var UrlGetSubscription		=	UrlGetSubscription1.replace( '{id}', account_change );
						 $.ajax({
							url: UrlGetSubscription,
							type: 'POST',
							dataType: 'json',
							async :false,
							
							success: function(response) {
								$('#ServiceID_add_change option').remove();
								$.each(response, function(key,value) {							  
							  $('#ServiceID_add_change').append($("<option></option>").attr("value", value).text(key));
							});	
							$('#ServiceID_add_change').trigger('change');		
							}
						});*/
						
						
						var UrlGetSubscription1 	= 	"<?php echo URL::to('/account_subscription/{id}/get_subscriptions'); ?>";
						var UrlGetSubscription		=	UrlGetSubscription1.replace( '{id}', account_change );
						 $.ajax({
							url: UrlGetSubscription,
							type: 'POST',
							dataType: 'json',
							async :false,
							
							success: function(response) {
								$('#SubscriptionID_add_change option').remove();
								$.each(response, function(key,value) {							  
							  $('#SubscriptionID_add_change').append($("<option></option>").attr("value", key).text(value));
							});	
							
							$('#SubscriptionID_add_change').trigger('change');		
							}
						});
							
					}	
					
				});
				
				 function ServiceSubmit(){
					var submit_error_service = 0;
					var ServiceID_add = new Array($('#ServiceID_add_change').val());
					var AccountID_add = $('#AccountID_add_change').val(); 
					if(!AccountID_add){
						toastr.error("The Account field is required", "Error", toastr_opts);
						 $('#modal-subscription').find(".btn").button('reset');
						return 0;
					} 
					if(ServiceID_add<1){ 
 						toastr.error("The Service field is required", "Error", toastr_opts);
						 $('#modal-subscription').find(".btn").button('reset');
						return 0;
					}
					
                    var post_data = {ServiceID:ServiceID_add,AccountID:AccountID_add};
                    var _url = baseurl + '/accountservices/' + AccountID_add + '/addservices';
					$.ajax({
							url: _url,
							type: 'POST',
							dataType: 'json',
							data:post_data,
							async :false,
							
							success: function(response) {								
								 if(response.status =='success'){
									 if(response.message.indexOf('Following service already exists')<0){
									   toastr.success(response.message, "Success", toastr_opts);      
									 }
									submit_error_service = 1;		
									 //window.location =  baseurl+"/tickets/importrules";               
								}else{
									toastr.error(response.message, "Error", toastr_opts);
								}					
							}
						});
						return submit_error_service;
				 }

				
               $("#subscription-form").submit(function(e){
				   e.preventDefault();
					var servicesubmited =  ServiceSubmit();                   
				   if(servicesubmited==1){
                   	var _url  = $(this).attr("action");
					var AccountID_add = $('#AccountID_add_change').val();  
					if(!AccountID_add){
						toastr.error("The Account field is required", "Error", toastr_opts);
						return false;
					}
					_url = _url.replace("{id}",AccountID_add);
                   	submit_ajax_datatable(_url,$(this).serialize(),0,data_table);
				   }else{ 				   
				  	setTimeout($('#modal-subscription').find('.btn').reset(),1000);
				   }
                  
               });
			   
			   $("#subscription-form-edit").submit(function(e){

                   e.preventDefault();
                   var _url  = $(this).attr("action");
                   submit_ajax_datatable(_url,$(this).serialize(),0,data_table);
                   //data_table_subscription.fnFilter('', 0);
                   //console.log('edit');
                  // $('#subscription_submit').trigger('click');
               });
			   
			     $('#modal-subscription').on('hidden.bs.modal', function(event){
					var modal = $(this);
					$('#subscription-form').trigger("reset");
					document.getElementById('subscription-form').reset();
					$('#ServiceID_add_change').change();
					$('#SubscriptionID_add_change').change();
					$('#AccountID_add_change').change();
					
				});
			   
               $('#subscription-form [name="SubscriptionID"]').change(function(e){

                       id = $(this).val();					   
					   if(id){
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

			   	}
                });

            });
            </script>
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
                <label for="field-5" class="control-label">Account</label>
                 {{ Form::select('AccountID',$accounts, '', array("class"=>"select2 dropdown1 AccountID_add_change","id"=>"AccountID_add_change")) }}
                </div>
            </div>
          </div>         
        
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Service</label>
                	<div>
        			{{ Form::select('ServiceID',$services,'', array("class"=>"select2 dropdown1","id"=>"ServiceID_add_change")) }}        
                    </div>
                </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-5" class="control-label">Subscription</label>
                	<div>
        			 {{ Form::select('SubscriptionID', array() , '' , array("class"=>"select2 dropdown1","id"=>"SubscriptionID_add_change")) }}
                    </div>
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
                <label for="AnnuallyFee" class="control-label">Annually Fee</label>
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
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade in" id="modal-edit-subscription">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="subscription-form-edit" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Subscription</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Subscription</label>
                            {{ Form::select('SubscriptionID', BillingSubscription::getSubscriptionsList(), '' , array("class"=>"select2")) }}
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
                                <label for="AnnuallyFee" class="control-label">Annually Fee</label>
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
                <input type="hidden" name="ServiceID" value="">
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