@extends('layout.main')
@section('filter')

    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="subscription_filter" method="get" action="#" class="form-horizontal form-groups-bordered validate" >
                <div class="form-group">
                    <label for="field-1" class="control-label">Account</label>
                    {{ Form::select('AccountID', $accounts, $SelectedAccount->AccountID, array("id"=>"filter_AccountID", "class"=>"select2 filter_AccountID","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Service</label>
                    {{ Form::select('ServiceID', $services,'', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Service")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Name</label>
                    <input type="text" name="SubscriptionName" class="form-control" value="" />
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Active</label><br/>
                    <p class="make-switch switch-small">
                        <input id="Active" name="Active" type="checkbox" value="1" checked="checked" >
                    </p>
                </div>
                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left" id="subscription_submit">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop


@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>  
  <li class="active"> <strong>Account Subscriptions</strong> </li>
</ol>
<h3>Account Subscriptions</h3>
@include('includes.errors')
@include('includes.success')
<div class="clear"></div>
@if(User::checkCategoryPermission('AccountSubscription','Add'))
    <div class="text-right"> <a  id="add-subscription" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New</a>
      <div class="clear clearfix"><br>
      </div>
    </div>
@endif
<table id="table-subscription" class="table table-bordered datatable">
  <thead>
    <tr>
      <th width="3%"></th>
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
      <th width="10%">Yearly Fee</th>
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

                $('#filter-button-toggle').show();

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

            var list_fields  = ["AID","SequenceNo","AccountName","ServiceName", "Name", "InvoiceDescription", "Qty", "StartDate", "EndDate" ,"tblBillingSubscription.ActivationFee","tblBillingSubscription.DailyFee","tblBillingSubscription.WeeklyFee","tblBillingSubscription.MonthlyFee", "tblBillingSubscription.QuarterlyFee", "tblBillingSubscription.AnnuallyFee", "AccountSubscriptionID", "SubscriptionID","ExemptTax","AccountID",'ServiceID','Status'];
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
					
		 data_table  = $("#table-subscription").DataTable({
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
            [           {  "bSortable": false,
                            mRender: function(id, type, full) {
                                return '<div class="details-control subscription_'+full[0]+'" style="text-align: center; cursor: pointer;"><i class="entypo-plus-squared" style="font-size: 20px;"></i></div>';
                            }
                        },
                        {  "bSortable": true },  // 0 Sequence NO
                        {  "bSortable": true },  // 1 account						
                        {  "bSortable": true },  // 2 service
						{  "bSortable": true },  // 2 Name
						{                          // InvoiceDescription
                           "bSortable": true,
                            mRender: function ( id, type, full ) {return id.length > 50 ? (id.substring(0,50)+' ...'):id}							
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
                                @if(User::checkCategoryPermission('AccountSubscription','Edit'))
                                 action += ' <a href="' + subscription_edit_url+'" title="Edit" class="edit-subscription btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>'
                                @endif
                                @if(User::checkCategoryPermission('AccountSubscription','Delete'))
                                 action += ' <a href="' + subscription_delete_url+'" title="Delete" class="delete-subscription btn btn-danger btn-sm"><i class="entypo-trash"></i></a>'
                                @endif
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
                        console.log('status');
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

                            }else if(list_fields[i] == 'Status'){
                                if(cur_obj.find("input[name='Status']").val() == 1 ){
                                    $('[name="Status"]').prop('checked',true).change();
                                }else{
                                    $('[name="Status"]').prop('checked',false).change();
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

                //discountplan start code
                //fetch discount plans click on '+' sign
                $('#table-subscription tbody').on('click', 'td div.details-control', function () {
                    var tr = $(this).closest('tr');
                    var row = data_table.row(tr);

                    if (row.child.isShown()) {
                        $(this).find('i').toggleClass('entypo-plus-squared entypo-minus-squared');
                        row.child.hide();
                        tr.removeClass('shown');
                    } else {
                        $(this).find('i').toggleClass('entypo-plus-squared entypo-minus-squared');
                        var hiddenRowData = tr.find('.hiddenRowData');
                        var AccountSubscriptionID = hiddenRowData.find('input[name="AccountSubscriptionID"]').val();
                        var ServiceID = hiddenRowData.find('input[name="ServiceID"]').val();
                        var AccountID = hiddenRowData.find('input[name="AccountID"]').val();
                        $.ajax({
                            url: baseurl + "/accounts/"+AccountID+"/subscription/get_discountplan",
                            type: 'POST',
                            data: "AccountSubscriptionID=" + AccountSubscriptionID + "&ServiceID=" + ServiceID,
                            dataType: 'json',
                            cache: false,
                            success: function (response) {

                                var table = $('<table class="table table-bordered datatable dataTable no-footer" style="margin-left: 4%;width: 92% !important;"></table>');

                                table.append('<thead><tr><th><input class="checkall_discount" name="chkall[]" onclick="check_all('+AccountSubscriptionID+')" type="checkbox"></th><th>Account Name</th><th>Account CLI</th><th>Inbound Discount Plan</th><th>Outbound Discount Plan</th><th>Actions <a class="btn btn-primary btn-sm entypo-plus" title="Add New" onClick="javascript:add_discountplan('+AccountSubscriptionID+','+AccountID+','+ServiceID+');"></a></th></tr></thead>');
                                var tbody = $("<tbody></tbody>");

                                response.forEach(function (data) {
                                    //alert(data.InboundDiscountPlans);
                                    if(data.AccountCLI == null)
                                        data.AccountCLI = '';
                                    if(data.InboundDiscountPlans == 0 || data.InboundDiscountPlans == null)
                                        data.InboundDiscountPlans = '';
                                    if(data.OutboundDiscountPlans == 0 || data.OutboundDiscountPlans == null)
                                        data.OutboundDiscountPlans = '';
                                    var html = "";
                                    html += "<tr class='no-selection'>";
                                    html += "<td><input name='chk[]' class='check_discount' type='checkbox' value='0' disc-id="+ data['SubscriptionDiscountPlanID'] + "></td>";
                                    html += "<td>" + data['AccountName'] + "</td>";
                                    html += "<td>" + data['AccountCLI'] + "</td>";
                                    html += '<td>' + data["InboundDiscountPlans"] + '&nbsp;&nbsp;<a href="javascript:void(0);" onclick ="view_discountplan('+ data["SubscriptionDiscountPlanID"] + ','+AccountSubscriptionID+','+"{{AccountDiscountPlan::INBOUND}}"+','+AccountID+','+ServiceID+')" class="btn btn-sm btn-primary tooltip-primary" data-original-title="View Detail" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading..."><i class="fa fa-eye"></i></a></td>';
                                    html += '<td>' + data["OutboundDiscountPlans"] + '&nbsp;&nbsp;<a href="javascript:void(0);" onclick ="view_discountplan('+ data["SubscriptionDiscountPlanID"] + ','+AccountSubscriptionID+','+"{{AccountDiscountPlan::OUTBOUND}}"+','+AccountID+','+ServiceID+')" class="btn btn-sm btn-primary tooltip-primary" data-original-title="View Detail" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading..."><i class="fa fa-eye"></i></a></td>';
                                    html += '<td><a href="javascript:void(0);" title="Edit" onclick ="edit_discountplan('+ data["SubscriptionDiscountPlanID"] + ','+AccountID+','+ServiceID+')" class="edit-discountplan btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a><a href="javascript:void(0);" onclick ="delete_discountplan('+ data["SubscriptionDiscountPlanID"] + ','+ AccountSubscriptionID +','+AccountID+')" title="Delete" class="delete-discountplan btn btn-danger btn-sm"><i class="entypo-trash"></i></a></td>';
                                    html += "</tr>";

                                    table.append(html);
                                });
                                table.append(tbody);
                                row.child(table).show();
                                row.child().addClass('no-selection child-row subrow_'+AccountSubscriptionID+'');
                                tr.addClass('shown');
                            }
                        });
                    }
                });

                //add & update discount plans
                $("#add_discountplan_form").submit(function(e){
                    e.preventDefault();
                    var _url  = $(this).attr("action");
                    var AccountSubscriptionID = $('[name="AccountSubscriptionID_dp"]').val();
                    //submit_ajax_datatable(_url,$(this).serialize(),0,data_table_subscription);
                    $.ajax({
                        url: _url,
                        type: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        cache: false,
                        success: function (response) {
                            $(".btn").button('reset');
                            if (response.status == 'success') {
                                $('.modal').modal('hide');
                                toastr.success(response.message, "Success", toastr_opts);
                                $('.subscription_'+AccountSubscriptionID).click();
                                $('.subscription_'+AccountSubscriptionID).click();
                                return false;
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        }
                    });
                });

                //bulkedit discount plans
                $("#bulkedit_discountplan_form").submit(function(e){
                    e.preventDefault();
                    var AccountSubscriptionID = $('[name="AccountSubscriptionID_bulk"]').val();
                    var _url  = $(this).attr("action");
                    $.ajax({
                        url: _url,
                        type: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        cache: false,
                        success: function (response) {
                            $(".btn").button('reset');
                            if (response.status == 'success') {
                                $('.modal').modal('hide');
                                toastr.success(response.message, "Success", toastr_opts);
                                $('.subscription_'+AccountSubscriptionID).click();
                                $('.subscription_'+AccountSubscriptionID).click();
                                return false;
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        }
                    });
                });
            });

            //check-uncheck all checkbox of subrow
            function check_all(AccountSubscriptionID){
                $('.subrow_'+AccountSubscriptionID+' .check_discount').prop("checked", $('.subrow_'+AccountSubscriptionID+' .checkall_discount').is(":checked"));
            }

            function bulk_edit(AccountSubscriptionID,AccountID)
            {
                var discountplan_bulkedit_url = baseurl + "/accounts/"+AccountID+"/subscription/bulkupdate_discountplan";
                var chklength = $("input:checkbox[name='chk[]']:checked").length;
                if(chklength > 0) {
                    $('#bulkedit_discountplan_form').trigger("reset");
                    $('#modal-bulkedit_discountplan h4').html('Bulk Edit Account');
                    $('#bulkedit_discountplan_form').attr("action", discountplan_bulkedit_url);
                    var temparr = [];
                    $("input:checkbox[name='chk[]']:checked").each(function () {
                        //alert($(this).attr('disc-id'));
                        temparr.push($(this).attr('disc-id'));
                    });
                    $('[name="AccountSubscriptionID_bulk"]').attr("value", AccountSubscriptionID);
                    $('[name="AllSubscriptionDiscountPlanID"]').attr("value", temparr);
                    $('[name="BulkInboundDiscountPlans"]').select2().select2('val','');
                    $('[name="BulkOutboundDiscountPlans"]').select2().select2('val','');
                    $('#modal-bulkedit_discountplan').modal('show');
                }
                else
                {
                    toastr.error("Select Atleast One Record", "Error", toastr_opts);
                    return false;
                }
            }

            function bulk_delete(AccountSubscriptionID,AccountID)
            {
                var discountplan_bulkdelete_url = baseurl + "/accounts/"+AccountID+"/subscription/bulkdelete_discountplan";
                var chklength = $("input:checkbox[name='chk[]']:checked").length;
                if(chklength > 0) {
                    var temparr = [];
                    $("input:checkbox[name='chk[]']:checked").each(function () {
                        temparr.push($(this).attr('disc-id'));
                    });

                    result = confirm("Are you Sure?");
                    if (result) {
                        $.ajax({
                            url: discountplan_bulkdelete_url,
                            type: 'POST',
                            data: "SubscriptionDiscountPlanID=" + temparr,
                            dataType: 'json',
                            cache: false,
                            success: function (response) {
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    $('.subscription_'+AccountSubscriptionID).click();
                                    $('.subscription_'+AccountSubscriptionID).click();
                                    return false;
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                            }
                        });
                        return false;
                    }
                }
                else
                {
                    toastr.error("Select Atleast One Record", "Error", toastr_opts);
                    return false;
                }

            }

            function add_discountplan(AccountSubscriptionID,AccountID,ServiceID){
                var discountplan_add_url = baseurl + "/accounts/"+AccountID+"/subscription/store_discountplan";
                $('#add_discountplan_form').trigger("reset");
                $('[name="AccountName"]').attr("value","");
                $('[name="AccountCLI"]').attr("value","");
                $('[name="ServiceID"]').attr("value",ServiceID);
                $('[name="InboundDiscountPlans"]').select2().select2('val','');
                $('[name="OutboundDiscountPlans"]').select2().select2('val','');
                getDiscountPlanByAccount =  '{{ URL::to('account_subscription/getDiscountPlanByAccount')}}';
                $.ajax({
                    url: getDiscountPlanByAccount,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        var html ='';
                        html+='<option value="0">Select</option>';
                        $.each(response.data, function (key, value) {
                            html+='<option value='+key+'>'+value+'</option>';
                        });
                        $('[name="InboundDiscountPlans"]').html(html);
                        $('[name="OutboundDiscountPlans"]').html(html);
                    },
                    data: {
                        "AccountID":AccountID
                    }

                });

                $('#modal-add_discountplan h4').html('Add Account');
                $('#add_discountplan_form').attr("action",discountplan_add_url);
                $('[name="AccountSubscriptionID_dp"]').attr("value",AccountSubscriptionID);
                $(".namehide").removeAttr('readonly');
                $('#modal-add_discountplan').modal('show');
            }
            function edit_discountplan(SubscriptionDiscountPlanID,AccountID,ServiceID){
                var discountplan_edit_url = baseurl + "/accounts/"+AccountID+"/subscription/update_discountplan";
                $('#add_discountplan_form').trigger("reset");
                getDiscountPlanByAccount =  '{{ URL::to('account_subscription/getDiscountPlanByAccount')}}';
                $.ajax({
                    url: getDiscountPlanByAccount,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        var options = [];
                        var html ='';
                        html+='<option value="0">Select</option>';
                        $.each(response.data, function (key, value) {
                            html+='<option value='+key+'>'+value+'</option>';
                        });
                        $('[name="InboundDiscountPlans"]').html(html);
                        $('[name="OutboundDiscountPlans"]').html(html);
                    },
                    data: {
                        "AccountID":AccountID
                    }

                });
                $('#modal-add_discountplan h4').html('Edit Account');
                $('#add_discountplan_form').attr("action",discountplan_edit_url);

                $.ajax({
                    url: baseurl + "/accounts/"+AccountID+"/subscription/edit_discountplan",
                    type: 'POST',
                    data: "SubscriptionDiscountPlanID=" + SubscriptionDiscountPlanID,
                    dataType: 'json',
                    cache: false,
                    success: function (response) {
                        setTimeout(function () {
                        response.forEach(function (data) {
                            $('[name="AccountName"]').attr("value",data.AccountName);
                            $('[name="AccountCLI"]').attr("value",data.AccountCLI);
                            $('[name="ServiceID"]').attr("value",ServiceID);
                            $('[name="InboundDiscountPlans"]').select2().select2('val',data.InboundDiscountPlans);
                            $('[name="InboundDiscountPlans"] option:selected').val(data.InboundDiscountPlans);
                            $('[name="OutboundDiscountPlans"]').select2().select2('val',data.OutboundDiscountPlans);
                            $('[name="OutboundDiscountPlans"] option:selected').val(data.OutboundDiscountPlans);
                            $('[name="AccountSubscriptionID_dp"]').attr("value",data.AccountSubscriptionID);
                        });

                        $('[name="SubscriptionDiscountPlanID"]').attr("value",SubscriptionDiscountPlanID);
                        $(".namehide").attr('readonly','true');
                        $('#modal-add_discountplan').modal('show');
                        }, 200);
                    }
                });

            }

            function delete_discountplan(SubscriptionDiscountPlanID,AccountSubscriptionID,AccountID) {
                result = confirm("Are you Sure?");
                if (result) {
                    $.ajax({
                        url: baseurl + "/accounts/"+AccountID+"/subscription/delete_discountplan",
                        type: 'POST',
                        data: "SubscriptionDiscountPlanID=" + SubscriptionDiscountPlanID,
                        dataType: 'json',
                        cache: false,
                        success: function (response) {
                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                                $('.subscription_'+AccountSubscriptionID).click();
                                $('.subscription_'+AccountSubscriptionID).click();
                                return false;
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        }
                    });
                    return false;
                }
            }

            function view_discountplan(SubscriptionDiscountPlanID,AccountSubscriptionID,Type,AccountID,ServiceID){
                var update_new_url 	= 	baseurl + '/account/used_discount_plan/'+AccountID;
                $.ajax({
                    url: update_new_url,  //Server script to process data
                    type: 'POST',
                    data:'SubscriptionDiscountPlanID='+SubscriptionDiscountPlanID+'&AccountSubscriptionID='+AccountSubscriptionID+'&Type='+Type+'&ServiceID='+ServiceID,
                    dataType: 'html',
                    success: function (response) {
                        $('#minutes_report').button('reset');
                        $('#inbound_minutes_report').button('reset');
                        $('#minutes_report-modal').modal('show');
                        $('#used_minutes_report').html(response);
                    }
                });
                return false;
            }
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
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="field-5" class="control-label">Active</label>
                        <div class="clear">
                            <p class="make-switch switch-small">
                                <input type="checkbox" name="Status" value="0">
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
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Active</label>
                                <div class="clear">
                                    <p class="make-switch switch-small">
                                        <input type="checkbox" name="Status" value="0">
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
<div class="modal fade in" id="modal-add_discountplan">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add_discountplan_form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Account</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Account Name</label>
                                <input type="text" name="AccountName" class="form-control namehide" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Account CLI</label>
                                <input type="text" name="AccountCLI" class="form-control namehide" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Inbound Discount Plan</label>
                                <!--<input type="text" name="InboundDiscountPlans" class="form-control" value="" />-->
                                {{Form::select('InboundDiscountPlans',$DiscountPlan,'',array('class'=>'form-control select2','id'=>'inboundplan'))}}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Outbound Discount Plan</label>
                                <!--<input type="text" name="OutboundDiscountPlans" class="form-control" value="" />-->
                                {{Form::select('OutboundDiscountPlans',$DiscountPlan,'',array('class'=>'form-control select2'))}}
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="ServiceID" value="">
                <input type="hidden" name="AccountSubscriptionID_dp">
                <input type="hidden" name="SubscriptionDiscountPlanID">
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
<div class="modal fade" id="minutes_report-modal">
    <div class="modal-dialog" style="width: 70%;">
        <div class="modal-content">
            <form id="add-minutes_report-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><strong> Discount Plan Detail</strong></h4>
                </div>
                <div class="modal-body">
                    <div class="row" id="used_minutes_report">

                    </div>
                </div>
                <div class="modal-footer">
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop