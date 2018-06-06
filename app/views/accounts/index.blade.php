@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="account_filter" method=""  action="" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Name</label>
                    <input class="form-control" name="account_name"  type="text" >
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Number</label>
                    <input class="form-control" name="account_number" type="text"  >
                </div>
                <div class="form-group">
                    <label class="control-label">Contact Name</label>
                    <input class="form-control" name="contact_name" type="text" >
                </div>
                <div class="form-group">
                    <label class="control-label">Tag</label>
                    <input class="form-control tags" name="tag" type="text" >
                </div>
                <div class="form-group">
                    <label class="control-label">Low Balance</label><br/>
                    <p class="make-switch switch-small">
                        <input id="low_balance" name="low_balance" type="checkbox" value="1">
                    </p>
                </div>
                <div class="form-group">
                    <label class="control-label"  >Customer</label><br/>
                    <p class="make-switch switch-small">
                        <input id="Customer_on_off" name="customer_on_off" type="checkbox" value="1" >
                    </p>
                </div>
                <div class="form-group">
                    <label class="control-label"  >Vendor</label><br/>
                    <p class="make-switch switch-small">
                        <input id="Vendor_on_off" name="vendor_on_off" type="checkbox" value="1">
                    </p>
                </div>
                <div class="form-group">
                    <label class="control-label"  >Reseller</label><br/>
                    <p class="make-switch switch-small">
                        <input id="Reseller_on_off" name="reseller_on_off" type="checkbox" value="1">
                    </p>
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Account Reseller</label>
                    {{ Form::select('ResellerOwner',$reseller_owners,'', array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label class="control-label"  >Active</label><br/>
                    <p class="make-switch switch-small">
                        <input id="account_active" name="account_active" type="checkbox" value="1" checked="checked">
                    </p>
                </div>
                <div class="form-group">
                    <label class="control-label">Status</label>
                    {{Form::select('verification_status',Account::$doc_status,Account::VERIFIED,array("class"=>"select2 small"))}}
                </div>
                <div class="form-group">
                    @if(User::is_admin())
                        <label for="field-1" class="control-label">Owner</label>
                        {{Form::select('account_owners',$account_owners,Input::get('account_owners'),array("class"=>"select2"))}}
                    @endif
                </div>
                <div class="form-group">
                    <label for="field-5" class="control-label">IP/CLI</label>
                    <input type="text" name="IPCLIText" class="form-control">
                </div>
                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
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
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Accounts</strong>
    </li>
</ol>
<h3>Accounts</h3>

@include('includes.errors')
@include('includes.success')

<div class="clear"></div>
@if(User::checkCategoryPermission('Account','Email,Edit'))
<div class="row">
    <div  class="col-md-12">
        <div class="input-group-btn pull-right hidden dropdown" style="width:70px;">
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
            <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                @if(User::checkCategoryPermission('Account','Email'))
                <li>
                    <a href="javascript:void(0)" class="sendemail">
                        <i class="entypo-mail"></i>
                        <span>Bulk Email</span>
                    </a>
                </li>
                @endif
                @if(User::checkCategoryPermission('Account','Edit'))
                <li>
                    <a href="javascript:void(0)" id="bulk-tags">
                        <i class="entypo-tag"></i>
                        <span>Bulk Tags</span>
                    </a>
                </li>
                
                <li>
                    <a href="javascript:void(0)" id="bulk-Actions">
                        <i class="entypo-tag"></i>
                        <span>Bulk Actions</span>
                    </a>
                </li>
                
                @endif
                @if(User::checkCategoryPermission('Account','Email'))
                <li>
                    <a href="javascript:void(0)" id="bulk-Ratesheet">
                        <i class="entypo-mail"></i>
                        <span>Bulk Rate sheet Email</span>
                    </a>
                </li>
                @endif
                @if(User::checkCategoryPermission('Account','Add'))
                <li>
                   <a href="{{ URL::to('/import/account') }}" >
                        <i class="entypo-user-add"></i>
                        <span>Import</span>
                   </a>
                </li>
                <li>
                   <a href="{{ URL::to('/import/ips') }}" >
                        <i class="entypo-user-add"></i>
                        <span>Import IPs</span>
                   </a>
                </li>
                <li class="li_active">
                   <a class="type_active_deactive" type_ad="active" href="javascript:void(0);" >
                        <i class="fa fa-plus-circle"></i>
                        <span>Activate</span>
                   </a>
                </li>
                <li class="li_deactive">
                   <a class="type_active_deactive" type_ad="deactive" href="javascript:void(0);" >
                        <i class="fa fa-minus-circle"></i>
                        <span>Deactivate</span>
                   </a>
                </li>
                @endif
            </ul>
        </div><!-- /btn-group -->

        @if(User::checkCategoryPermission('Account','Add'))
            <a href="{{URL::to('accounts/create')}}" class="btn btn-primary pull-right">
                <i class="entypo-plus"></i>
                Add New
            </a>
        @endif
    </div>
    <div class="clear"></div>
</div>
@endif
<br>
<!--<p style="text-align: right;">
    <a href="javascript:void(0)" id="selectallbutton" class="btn btn-primary ">
        <i class="entypo-check"></i>
        <span>Select all found Accounts</span>
    </a>
</p>
<br />-->
<table class="table table-bordered datatable hidden" id="table-4">
    <thead>
    <tr>
        <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
        <th width="10%" >No.</th>
        <th width="15%" >Account Name</th>
        <th width="10%" >Name</th>
        <th width="10%">Phone</th>
        <th width="8%">OS</th>
        <th width="5%">UA</th>
        <th width="5%">CL</th>
        <th width="5%">AE</th>
        <th width="7%">Email</th>
        <th width="25%">Actions</th>
    </tr>
    </thead>
    <tbody>



    </tbody>
</table>

<script type="text/javascript">
    var $searchFilter = {};
    var checked = '';
    var view = 1;
    var accountview = getCookie('accountview');
    if(accountview=='list'){
        view = 2;
    }
    var readonly = ['Company','Phone','Email','ContactName'];
    var editor_options 	  =  		{"leadoptions":true};
    jQuery(document).ready(function ($) {

        $('#filter-button-toggle').show();

		function check_status(){
            var selected_active_type =  $("#account_filter [name='account_active']").prop("checked");
			if(selected_active_type){


				$('.li_active').hide();
				$('.li_deactive').show();
			}else{
				$('.li_active').show();
				$('.li_deactive').hide();		
			}
		}
		
		$('.type_active_deactive').click(function(e) {
			
            var type_active_deactive  =  $(this).attr('type_ad');
			var SelectedIDs 		  =  getselectedIDs();	
			var criteria_ac			  =  '';
			
			if($('#selectallbutton').is(':checked')){
				criteria_ac = 'criteria';
			}else{
				criteria_ac = 'selected';				
			}
			
			if(SelectedIDs=='' || criteria_ac=='')
			{
				alert("Please select atleast one account.");
				return false;
			}
			
			account_ac_url =  '{{ URL::to('accounts/update_bulk_account_status')}}';
			$.ajax({
				url: account_ac_url,
				type: 'POST',
				dataType: 'json',
				success: function(response) {
					   if(response.status =='success'){
							toastr.success(response.message, "Success", toastr_opts);
							data_table.fnFilter('', 0);
						}else{
							toastr.error(response.message, "Error", toastr_opts);
                   	 	}				
					},				
				data: {
			"account_name":$("#account_filter [name='account_name']").val(),
			"account_number":$("#account_filter [name='account_number']").val(),
			"contact_name":$("#account_filter [name='contact_name']").val(),
			"tag":$("#account_filter [name='tag']").val(),
			"verification_status":$("#account_filter [name='verification_status']").val(),
			"account_owners":$("#account_filter [name='account_owners']").val(),			
			"ResellerOwner":$("#account_filter [name='ResellerOwner']").val(),
			"customer_on_off":$("#account_filter [name='customer_on_off']").prop("checked"),
			"reseller_on_off":$("#account_filter [name='reseller_on_off']").prop("checked"),
			"vendor_on_off":$("#account_filter [name='vendor_on_off']").prop("checked"),
            "low_balance":$("#account_filter [name='low_balance']").prop("checked"),
			"account_active":$("#account_filter [name='account_active']").prop("checked"),
			"SelectedIDs":SelectedIDs,
			"criteria_ac":criteria_ac,	
			"type_active_deactive":type_active_deactive,
			}			
				
			});
			
        });
		

        //["tblAccount.Number",
        // "tblAccount.AccountName",
        // DB::raw("(tblUser.FirstName+' '+tblUser.LastName) as Ownername"),
        // "tblAccount.Phone",
        // "tblAccount.Email",
        // "tblAccount.AccountID",
        // "tblAccount.IsCustomer",
        // "tblAccount.IsVendor",
        // 'tblAccount.VerificationStatus']

        var varification_status = [{{Account::NOT_VERIFIED}},{{Account::VERIFIED}}];
        var varification_status_text = ["{{Account::$doc_status[Account::NOT_VERIFIED]}}","{{Account::$doc_status[Account::VERIFIED]}}"];

        $searchFilter.account_name = $("#account_filter [name='account_name']").val();
        $searchFilter.account_number = $("#account_filter [name='account_number']").val();
        $searchFilter.contact_name = $("#account_filter [name='contact_name']").val();
        $searchFilter.tag = $("#account_filter [name='tag']").val();
        $searchFilter.verification_status = $("#account_filter [name='verification_status']").val();
        $searchFilter.account_owners = $("#account_filter [name='account_owners']").val();
        $searchFilter.ResellerOwner = $("#account_filter [name='ResellerOwner']").val();
        $searchFilter.customer_on_off = $("#account_filter [name='customer_on_off']").prop("checked");
        $searchFilter.reseller_on_off = $("#account_filter [name='reseller_on_off']").prop("checked");
        $searchFilter.vendor_on_off = $("#account_filter [name='vendor_on_off']").prop("checked");
        $searchFilter.low_balance = $("#account_filter [name='low_balance']").prop("checked");
        $searchFilter.account_active = $("#account_filter [name='account_active']").prop("checked");
        $searchFilter.ipclitext = $("#account_filter [name='IPCLIText']").val();

                data_table = $("#table-4").dataTable({

                    "bProcessing":true,
                    "bDestroy": true,
                    "bServerSide":true,
                    "sAjaxSource": baseurl + "/accounts/ajax_datagrid/type",
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting"   : [[2, 'asc']],
                      "fnServerParams": function(aoData) {
                        aoData.push(
                                {"name":"account_name","value":$searchFilter.account_name},
                                {"name":"account_number","value":$searchFilter.account_number},
                                {"name":"tag","value":$searchFilter.tag},
                                {"name":"contact_name","value":$searchFilter.contact_name},
                                {"name":"customer_on_off","value":$searchFilter.customer_on_off},
                                {"name":"reseller_on_off","value":$searchFilter.reseller_on_off},
                                {"name":"vendor_on_off","value":$searchFilter.vendor_on_off},
                                {"name":"low_balance","value":$searchFilter.low_balance},
                                {"name":"account_active","value":$searchFilter.account_active},
                                {"name":"verification_status","value":$searchFilter.verification_status},
                                {"name":"account_owners","value":$searchFilter.account_owners},
                                {"name":"ResellerOwner","value":$searchFilter.ResellerOwner},
                                {"name":"ipclitext","value":$searchFilter.ipclitext}
                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name":"account_name","value":$searchFilter.account_name},
                                {"name":"account_number","value":$searchFilter.account_number},
                                {"name":"tag","value":$searchFilter.tag},
                                {"name":"contact_name","value":$searchFilter.contact_name},
                                {"name":"customer_on_off","value":$searchFilter.customer_on_off},
                                {"name":"reseller_on_off","value":$searchFilter.reseller_on_off},
                                {"name":"vendor_on_off","value":$searchFilter.vendor_on_off},
                                {"name":"low_balance","value":$searchFilter.low_balance},
                                {"name":"account_active","value":$searchFilter.account_active},
                                {"name":"verification_status","value":$searchFilter.verification_status},
                                {"name":"account_owners","value":$searchFilter.account_owners},
                                {"name":"ResellerOwner","value":$searchFilter.ResellerOwner},
                                {"name":"ipclitext","value":$searchFilter.ipclitext},
                                {"name":"Export","value":1}
                        );
                    },
                    "aoColumns":
                    [
                        {"bSortable": false,
                            mRender: function(id, type, full) {
                                return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                            }
                        }, //0Checkbox
                        { "bSortable": true}, //AccountName
                        { "bSortable": true}, //Name
                        { "bSortable": true}, //Phone
                        { "bSortable": true},
                        { "bSortable": true,
                            mRender:function(id, type, full){
                                if(id !== null) {
                                    popup_html = "<label class='col-sm-6' >Invoice Outstanding:</label><div class='col-sm-6' >" + id + "</div>";
                                    popup_html += "<div class='clear'></div><label class='col-sm-6' >Customer Unbilled Amount:</label><div class='col-sm-6' >" + (full[21] !== null ? full[21] : '')  + "</div>";
                                    popup_html += "<div class='clear'></div><label class='col-sm-6' >Vendor Unbilled Amount:</label><div class='col-sm-6' >" + (full[22] !== null ? full[22] : '') + "</div>";
                                    popup_html += "<div class='clear'></div><label class='col-sm-6' >Account Exposure:</label><div class='col-sm-6' >" + (full[23] !== null ? full[23] : '') + "</div>";
                                    popup_html += "<div class='clear'></div><label class='col-sm-6' >Available Credit Limit:</label><div class='col-sm-6' >" + (full[24] !== null ? full[24] : '') + "</div>";
                                    popup_html += "<div class='clear'></div><label class='col-sm-6' >Balance Threshold:</label><div class='col-sm-6' >" + (full[25] !== null ? full[25] : '') + "</div>";

                                    return '<div class="pull-left" data-toggle="popover" data-trigger="hover" data-original-title="aaa" data-content="'+popup_html+'">' +id+ '</div>';
                                }else{
                                    return '';
                                }
                            }
                        },
						{ "bSortable": true,
                            mRender:function(id, type, full){
                                if(id !== null) {
                                    return '<a class="unbilled_report" data-id="' + full[0] + '">' + id + '</a>';
                                }else{
                                    return '';
                                }

                            }
                        },
						{ "bSortable": true},
                        { "bSortable": true},//Account exposure
                        { "bSortable": true},
                        {
                            "bSortable": false,
                            mRender: function ( id, type, full ) {
                                var action , edit_ , show_,chart_,credit_;
                                action='';
                                edit_ = "{{ URL::to('accounts/{id}/edit')}}";
                                show_ = "{{ URL::to('accounts/{id}/show')}}";
                                log_ = "{{ URL::to('accounts/{id}/log')}}";
                                chart_ = "{{ URL::to('accounts/activity/{id}')}}";
                                credit_ = "{{ URL::to('account/get_credit/{id}')}}";
                                customer_rate_ = "{{Url::to('/customers_rates/{id}')}}";
                                movement_report = "{{Url::to('/customer/daily_report/{id}')}}";
                                vendor_blocking_ = "{{Url::to('/vendor_rates/{id}')}}";
								subscriptions_ = "{{ URL::to('account_subscription/')}}?id={id}";
								authenticate_ = "{{Url::to('/accounts/authenticate/{id}')}}";

                                edit_ = edit_.replace( '{id}', full[0] );
                                show_ = show_.replace( '{id}', full[0] );
                                log_ = log_.replace( '{id}', full[0] );
                                chart_ = chart_.replace( '{id}', full[0] );
                                credit_ = credit_.replace( '{id}', full[0] );
                                customer_rate_ = customer_rate_.replace( '{id}', full[0] );
                                movement_report = movement_report.replace( '{id}', full[0] );
                                vendor_blocking_ = vendor_blocking_.replace( '{id}', full[0] );
								subscriptions_ = subscriptions_.replace( '{id}', full[0] );
								authenticate_ = authenticate_.replace( '{id}', full[0] );
                                action = '';
                                
								
								<?php if(User::checkCategoryPermission('Opportunity','Add') && CompanyConfiguration::get('ACCOUNT_ADD_OPP') == 1) { ?>
                                action +='&nbsp;<button class="btn  btn-default btn-xs small_icons" title="Add Opportunity" data-id="'+full[0]+'" type="button"> <i class="fa fa-line-chart"></i> </button>';
                                <?php } ?>

                                <?php if(User::checkCategoryPermission('AccountActivityChart','View') && CompanyConfiguration::get('ACCOUNT_ACT_CHART') == 1){ ?>
                                action +='&nbsp;<button redirecto="'+chart_+'" class="btn small_icons btn-default btn-xs" title="Account Activity Chart" data-id="'+full[0]+'" type="button"> <i class="fa fa-bar-chart"></i> </button>';
                                //action += '&nbsp;<a href="'+edit_+'" class="btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                                <?php } ?>

                                <?php if(User::checkCategoryPermission('CreditControl','View') && CompanyConfiguration::get('ACCOUNT_CC') == 1){ ?>
                                        action +='&nbsp;<button redirecto="'+credit_+'" class="btn small_icons btn-default btn-xs" title="Credit Control" data-id="'+full[0]+'" type="button"> <i class="fa fa-credit-card"></i> </button>';
                                <?php } ?>
								
								if(full[10]==1 || full[11]==1){
                                 	action += '&nbsp;<button redirecto="'+authenticate_+'" title="Authentication Rule" class="btn small_icons btn-default btn-xs"><i class="entypo-lock"></i></button>';
                                }

								<?php if(User::checkCategoryPermission('AccountSubscription','View') && CompanyConfiguration::get('ACCOUNT_SUB') == 1) { ?>
                                action +='&nbsp;<button class="btn btn-default small_icons btn-xs " redirecto="'+subscriptions_+'" title="View Account Subscriptions" data-id="'+full[0]+'" type="button"> <i class="fa fa-refresh"></i> </button>';
                                <?php } ?>
								
                                <?php if(User::checkCategoryPermission('Account','Edit')){ ?>
                                action +='&nbsp;<button redirecto="'+edit_+'" class="btn small_icons btn-default btn-xs" title="Edit" data-id="'+full[0]+'" type="button"> <i class="entypo-pencil"></i></button>';
                                <?php } ?>
                                <?php if(CompanyConfiguration::get('ACCOUNT_VIEW') == 1){ ?>
                                action +='&nbsp;<button redirecto="'+show_+'" class="btn small_icons btn-default btn-xs" title="View" data-id="'+full[0]+'" type="button"> <i class="fa fa-eye"></i></button>';//entypo-info
                                <?php } ?>
                                <?php if(CompanyConfiguration::get('ACCOUNT_LOG') == 1){ ?>
                                action +='&nbsp;<button redirecto="'+log_+'" class="btn small_icons btn-default btn-xs" title="View Account Logs" data-id="'+full[0]+'" type="button"> <i class="fa fa-file-text-o"></i></button>';//entypo-info
                                <?php } ?>
                                <?php if(CompanyConfiguration::get('ACCOUNT_MOV_REPORT') == 1){ ?>
                                action +='&nbsp;<button redirecto="'+movement_report+'" class="btn small_icons btn-default btn-xs" title="Movement Report" data-id="'+full[0]+'" type="button"> <i class="fa fa-calendar-plus-o"></i></button>';//entypo-info
                                <?php } ?>
                                /*full[6] == Customer verified
                                 full[7] == Vendor verified */
                                varification_url =  '{{ URL::to('accounts/{id}/change_verifiaction_status')}}/';
                                varification_url = varification_url.replace('{id}',full[0]);

                                NOT_VERIFIED = varification_url +'{{Account::NOT_VERIFIED}}';
                               
                                VERIFIED = varification_url + '{{Account::VERIFIED}}';

                                 
                                <?php if(User::checkCategoryPermission('Account','Edit')){ ?>
                                /* action += '<select name="varification_status" class="change_verification_status">';
                                 for(var i = 0; i < varification_status.length ; i++){
                                    var selected = "";
                                    if(full[9] == varification_status[i]){
                                        selected = "selected";
                                    }
                                    action += '<option data-id="'+full[0]+'" value="' + varification_status[i] + '" ' + selected   +'     >'+varification_status_text[i]+'</option>';
                                 }
                                 action += '</select>';*/
                                <?php } ?>
								

                                if(full[10]==1 && full[12]=='{{Account::VERIFIED}}'){
                                    <?php if(User::checkCategoryPermission('CustomersRates','View')){ ?>
                                        action += '&nbsp;<button redirecto="'+customer_rate_+'" title="Customer" class="btn small_icons btn-warning btn-xs"><i class="entypo-user"></i></button>';
                                    <?php } ?>
                                }

                                if(full[11]==1 && full[12]=='{{Account::VERIFIED}}'){
                                    <?php if(User::checkCategoryPermission('VendorRates','View')){ ?>
                                        action += '&nbsp;<button redirecto="'+vendor_blocking_+'" title="Vendor" class="btn small_icons btn-info btn-xs"><i class="fa fa-slideshare"></i></button>';
                                    <?php } ?>
                                } 								
								
                                action +='<input type="hidden" name="accountid" value="'+full[0]+'"/>';
                                action +='<input type="hidden" name="address1" value="'+full[13]+'"/>';
                                action +='<input type="hidden" name="address2" value="'+full[14]+'"/>';
                                action +='<input type="hidden" name="address3" value="'+full[15]+'"/>';
                                action +='<input type="hidden" name="city" value="'+full[16]+'"/>';
                                action +='<input type="hidden" name="country" value="'+full[17]+'"/>';
								action +='<input type="hidden" name="PostCode" value="'+full[18]+'"/>';
                                action +='<input type="hidden" name="picture" value="'+full[19]+'"/>';
                                action +='<input type="hidden" name="UnbilledAmount" value="'+full[6]+'"/>';
                                action +='<input type="hidden" name="PermanentCredit" value="'+full[7]+'"/>';
                                action +='<input type="hidden" name="LowBalance" value="'+full[20]+'"/>';
                                action +='<input type="hidden" name="CUA" value="'+full[21]+'"/>';
                                action +='<input type="hidden" name="VUA" value="'+full[22]+'"/>';
                                action +='<input type="hidden" name="AE" value="'+full[23]+'"/>';
                                action +='<input type="hidden" name="ACL" value="'+full[24]+'"/>';
                                action +='<input type="hidden" name="BalanceThreshold" value="'+full[25]+'"/>';
                                action +='<input type="hidden" name="Blocked" value="'+full[26]+'"/>';
                                return action;
                            }
                        },
                    ],
            "oTableTools": {
            "aButtons": [
                {
                    "sExtends": "download",
                    "sButtonText": "EXCEL",
                    "sUrl": baseurl + "/accounts/ajax_datagrid/xlsx", //baseurl + "/generate_xls.php",
                    sButtonClass: "save-collection btn-sm"
                },
                {
                    "sExtends": "download",
                    "sButtonText": "CSV",
                    "sUrl": baseurl + "/accounts/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                    sButtonClass: "save-collection btn-sm"
                }
            ]
        },
        "fnDrawCallback": function() {
			check_status();
             $(".dataTables_wrapper select").select2({
                minimumResultsForSearch: -1
            });

            $(".dropdown").removeClass("hidden");
            var toggle = '<header>';
            toggle += '<span class="list-style-buttons">';
            if(view==1){
                toggle += '<a href="javascript:void(0)" title="Grid View" class="btn btn-primary switcher grid active"><i class="entypo-book-open"></i></a>';
                toggle += '<a href="javascript:void(0)" title="List View" class="btn btn-primary switcher list"><i class="entypo-list"></i></a>';
            }else{
                toggle += '<a href="javascript:void(0)" title="Grid View" class="btn btn-primary switcher grid"><i class="entypo-book-open"></i></a>';
                toggle += '<a href="javascript:void(0)" title="List View" class="btn btn-primary switcher list active"><i class="entypo-list"></i></a>';
            }
            toggle +='</span>';
            toggle += '</header>';
            $('.change-view').html(toggle);
            var html = '<ul class="clearfix grid col-md-12">';
            var checkClass = '';
            if($(this).parents('.page-container').hasClass('sidebar-collapsed')) {
                checkClass = '1';
            }else{
                checkClass = '0';
            }
            $('#table-4 tbody tr').each(function (i, el) {
                var childrens = $(this).children();
                if(childrens.eq(0).hasClass('dataTables_empty')){
                    return true;
                }
                var temp = childrens.eq(10).clone();
                /*$(temp).find('a').each(function () {
                   // $(this).find('i').remove();
                    $(this).removeClass('btn btn-icon icon-left');
                    $(this).addClass('label');
                    $(this).addClass('padding-4');
                });*/
                $(temp).find('.select2-container').remove();
                $(temp).find('select[name="varification_status"]').remove();
                var address1 = $(temp).find('input[name="address1"]').val();
                var address2 = $(temp).find('input[name="address2"]').val();
                var address3 = $(temp).find('input[name="address3"]').val();
                var city = $(temp).find('input[name="city"]').val();
                var country = $(temp).find('input[name="country"]').val();
				var PostCode = $(temp).find('input[name="PostCode"]').val();

                var PermanentCredit = $(temp).find('input[name="PermanentCredit"]').val();
                var UnbilledAmount = $(temp).find('input[name="UnbilledAmount"]').val();
                var accountid =  $(temp).find('input[name="accountid"]').val();
                var LowBalance =  $(temp).find('input[name="LowBalance"]').val();
                var CUA =  $(temp).find('input[name="CUA"]').val();
                var VUA =  $(temp).find('input[name="VUA"]').val();
                var AE =  $(temp).find('input[name="AE"]').val();
                var ACL =  $(temp).find('input[name="ACL"]').val();
                var BalanceThreshold =  $(temp).find('input[name="BalanceThreshold"]').val();
                var Blocked =  $(temp).find('input[name="Blocked"]').val();

				
                address1 = (address1=='null'||address1==''?'':''+address1+'<br>');
                address2 = (address2=='null'||address2==''?'':address2+'<br>');
                address3 = (address3=='null'||address3==''?'':address3+'<br>');
                city 	 = (city=='null'||city==''?'':city+'<br>');
				PostCode = (PostCode=='null'||PostCode==''?'':PostCode+'<br>');
                country  = (country=='null'||country==''?'':country);
                PermanentCredit = PermanentCredit=='null'||PermanentCredit==''?'':''+PermanentCredit;
                UnbilledAmount = UnbilledAmount=='null'||UnbilledAmount==''?'':''+UnbilledAmount;
                CUA = CUA=='null'||CUA==''?'':''+CUA;
                VUA = VUA=='null'||VUA==''?'':''+VUA;
                AE = AE=='null'||AE==''?'':''+AE;
                ACL = ACL=='null'||ACL==''?'':''+ACL;
                BalanceThreshold = BalanceThreshold=='null'||BalanceThreshold==''?'':''+BalanceThreshold;
                Blocked = Blocked=='null'||Blocked==''?'':''+Blocked;

                var url  = baseurl + '/assets/images/placeholder-male.gif';
                var select = '';
                if (checked != '') {
                    select = ' selected';
                }

                if(LowBalance == 1){
                    select+= ' low_balance_account'
                    $(this).addClass('low_balance_account');
                }
                if(Blocked == 1){
                    select+= ' blocked_account'
                    $(this).addClass('blocked_account');
                }
				
				//col-xl-2 col-md-4 col-sm-6 col-xsm-12 col-lg-3
				
                if(checkClass=='1')
				{
                    html += '<li class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xsm-12">';
                }
				else
				{
                    html += '<li class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xsm-12">';
                }
				var account_title = childrens.eq(2).text();
				if(account_title.length>22){
					account_title  = account_title.substring(0,22)+"...";	
				}
				
				var account_name = childrens.eq(3).text();
				if(account_name.length>40){
					account_name  = account_name.substring(0,40)+"...";	
				}
				var account_email = childrens.eq(9).text();
				if(account_email.length>65){
					account_email  = account_email.substring(0,65)+"...";
				}

                popup_html = "<label class='col-sm-6' >Invoice Outstanding:</label><div class='col-sm-6' >" + childrens.eq(5).text() + "</div>";
                popup_html += "<div class='clear'></div><label class='col-sm-6' >Customer Unbilled Amount:</label><div class='col-sm-6' >" + CUA + "</div>";
                popup_html += "<div class='clear'></div><label class='col-sm-6' >Vendor Unbilled Amount:</label><div class='col-sm-6' >" + VUA + "</div>";
                popup_html += "<div class='clear'></div><label class='col-sm-6' >Account Exposure:</label><div class='col-sm-6' >" + AE + "</div>";
                popup_html += "<div class='clear'></div><label class='col-sm-6' >Available Credit Limit:</label><div class='col-sm-6' >" + ACL + "</div>";
                popup_html += "<div class='clear'></div><label class='col-sm-6' >Balance Threshold:</label><div class='col-sm-6' >" + BalanceThreshold + "</div>";

                html += '  <div class="box clearfix ' + select + '">';
               // html += '  <div class="col-sm-4 header padding-0"> <img class="thumb" alt="default thumb" height="50" width="50" src="' + url + '"></div>';
                html += '  <div class="col-sm-12 header padding-left-1">  <span class="head">' + account_title + '</span><br>';
                html += '  <span class="meta complete_name">' + account_name + '</span></div>';
                html += '  <div class="col-sm-6 padding-0">';
                html += '  <div class="block">';
                html += '     <div class="meta">Email</div>';
                html += '     <div style="word-break:break-all;"><a href="javascript:void(0)" class="sendemail">' + account_email + '</a></div>';
                html += '  </div>';
                html += '  <div class="cellNo">';
                html += '     <div class="meta">Phone</div>';
                html += '     <div><a href="tel:' + childrens.eq(4).text() + '">' + childrens.eq(4).text() + '</a></div>';
                html += '  </div>';
                html += '  <div class="block"><div class="meta clear pull-left tooltip-primary" data-original-title="Invoice Outstanding" title="" data-placement="right" data-toggle="tooltip">OS : </div> <div class="pull-left" data-toggle="popover"  data-trigger="hover" data-original-title="" data-content="'+popup_html+'"> ' + childrens.eq(5).text() + ' </div>';
                html += '  <div class="meta clear pull-left tooltip-primary" data-original-title="(Unbilled Amount). Click on amount to view breakdown" title="" data-placement="right" data-toggle="tooltip">UA : </div> <div class="pull-left"> <a class="unbilled_report" data-id="'+accountid+'">' + UnbilledAmount + '</a> </div>';
                html += '  <div class="meta clear pull-left tooltip-primary" data-original-title="Credit Limit" title="" data-placement="right" data-toggle="tooltip">CL : </div> <div class="pull-left"> ' + PermanentCredit + ' </div></div>';
                html += '  </div>';
                html += '  <div class="col-sm-6 padding-0">';
                html += '  <div class="block">';
                html += '     <div class="meta">Address</div>';
                html += '     <div class="address account-address">' + address1 + ''+address2+''+address3+''+city+''+PostCode+''+country+'</div>';
                html += '  </div>';
                html += '  </div>';
                html += '  <div class="col-sm-12 padding-0 action">';
                html += '   ' + temp.html();
                html += '  </div>';
                html += ' </div>';
                html += '</li>';
                if (checked != '') {
                    $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                    $(this).addClass('selected');
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                    ;
                    $(this).removeClass('selected');
                }
            });

            html += '</ul>';
            $('.gridview').html(html);
            if(view==2){
                $('.gridview').addClass('hidden');
                $('#table-4').removeClass('hidden');
            }else{
                $('#table-4').addClass('hidden');
                $('.gridview').removeClass('hidden');
            }

            $(".change_verification_status").change(function(e) {
                    if (!confirm('Are you sure you want to change verification status?')) {
                        return false;
                    }
                    $('#table-4_processing').hide();
                    $('#table-4_processing').show();

                    var id = $("option:selected", this).attr("data-id");
                    varification_url =  '{{ URL::to('accounts/{id}/change_verifiaction_status')}}/'+ $(this).val();
                    varification_url = varification_url.replace('{id}',id);

                    $.ajax({
                        url: varification_url,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            $(this).button('reset');
                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                                data_table.fnFilter('', 0);
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
                    return false;
                });
            //select all record
            $('#selectallbutton').click(function(){
                if($('#selectallbutton').is(':checked')){
                    checked = 'checked=checked disabled';
                    $("#selectall").prop("checked", true).prop('disabled', true);
                    //if($('.gridview').is(':visible')){
                        $('.gridview li div.box').each(function(i,el){
                            $(this).addClass('selected');
                        });
                    //}else{
                        $('#table-4 tbody tr').each(function (i, el) {
                            $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                            $(this).addClass('selected');
                        });
                    //}
                }else{
                    checked = '';
                    $("#selectall").prop("checked", false).prop('disabled', false);
                    //if($('.gridview').is(':visible')){
                        $('.gridview li div.box').each(function(i,el){
                            $(this).removeClass('selected');
                        });
                    //}else{
                        $('#table-4 tbody tr').each(function (i, el) {
                            $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                            $(this).removeClass('selected');
                        });
                    //}
                }
            });
            $("body").popover({
                selector: '[data-toggle="popover"]',
                trigger:'hover',
                html:true,
                template:'<div class="popover3" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>'
                //template:'<div class="popover3" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>'
            });

        }
    });
    $("#account_filter").submit(function(e) {
        e.preventDefault();

        $searchFilter.account_name = $("#account_filter [name='account_name']").val();
        $searchFilter.account_number = $("#account_filter [name='account_number']").val();
        $searchFilter.contact_name = $("#account_filter [name='contact_name']").val();
        $searchFilter.tag = $("#account_filter [name='tag']").val();
        $searchFilter.verification_status = $("#account_filter [name='verification_status']").val();
        $searchFilter.account_owners = $("#account_filter [name='account_owners']").val();
        $searchFilter.ResellerOwner = $("#account_filter [name='ResellerOwner']").val();
        $searchFilter.customer_on_off = $("#account_filter [name='customer_on_off']").prop("checked");
        $searchFilter.vendor_on_off = $("#account_filter [name='vendor_on_off']").prop("checked");
        $searchFilter.reseller_on_off = $("#account_filter [name='reseller_on_off']").prop("checked");
        $searchFilter.low_balance = $("#account_filter [name='low_balance']").prop("checked");
        $searchFilter.account_active = $("#account_filter [name='account_active']").prop("checked");
        $searchFilter.ipclitext = $("#account_filter [name='IPCLIText']").val();

        data_table.fnFilter('', 0);
        return false;
    });
    $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');

    $('#AccountStatus').change(function() {
        if ($(this).is(":checked")) {
            data_table.fnFilter(1,0);  // 1st value 2nd column index
        } else {
            data_table.fnFilter(0,0);
        }
    });


    $(".dataTables_wrapper select").select2({
        minimumResultsForSearch: -1
    });

    // Highlighted rows
    $("#table-4 tbody input[type=checkbox]").each(function (i, el) {
        var $this = $(el),
            $p = $this.closest('tr');

        $(el).on('change', function () {
            var is_checked = $this.is(':checked');

            $p[is_checked ? 'addClass' : 'removeClass']('highlight');
        });
    });

    $(document).on('click', '#table-4 tbody tr,.gridview ul li div.box', function() {
        if (checked =='') {
            $(this).toggleClass('selected');
            if($(this).is('tr')) {
                if ($(this).hasClass('selected')) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false);
                }
            }
        }
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

    // Replace Checboxes
    $(".pagination a").click(function (ev) {
        replaceCheckboxes();
    });

        $("#BulkMail-form [name=email_template]").change(function(e){
            var templateID = $(this).val();
            if(templateID>0) {
                var url = baseurl + '/accounts/' + templateID + '/ajax_template';
                $.get(url, function (data, status) {
                    if (Status = "success") {
                        editor_reset(data);
                    } else {
                        toastr.error(status, "Error", toastr_opts);
                    }
                });
            }
        });

        $('#BulkMail-form [name="email_template_privacy"]').change(function(e){
            setTimeout(function(){ drodown_reset(); }, 100);
        });

        $("#BulkMail-form [name=template_option]").change(function(e){
            if($(this).val()==1){
                $('#templatename').removeClass("hidden");
            }else{
                $('#templatename').addClass("hidden");
            }
        });

        $("#BulkMail-form").submit(function(e){
            e.preventDefault();
            var SelectedIDs = [];
            var i = 0;
            if($("#BulkMail-form").find('[name="test"]').val()==0){
                if(checked=='') {
                    var SelectedIDs = getselectedIDs();
                    if(SelectedIDs.length==0){
                        $(".save").button('reset');
                        $(".savetest").button('reset');
                        $('#modal-BulkMail').modal('hide');
                        toastr.error('Please select at least one account or select all found accounts.', "Error", toastr_opts);
                        return false;
                    }
                }
                var criteria = JSON.stringify($searchFilter);
                $("#BulkMail-form").find("input[name='criteria']").val(criteria);
                $("#BulkMail-form").find("input[name='SelectedIDs']").val(SelectedIDs.join(","));

                if($("#BulkMail-form").find("input[name='SelectedIDs']").val()!="" && confirm("Are you sure to send mail to selected Accounts")!=true){
                    $(".btn").button('reset');
                    $(".savetest").button('reset');
                    $('#modal-BulkMail').modal('hide');
                    return false;
                }
            }
            var formData = new FormData($('#BulkMail-form')[0]);
            var url = baseurl + "/accounts/bulk_mail"
            $.ajax({
                url: url,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);
                        $(".save").button('reset');
                        $(".savetest").button('reset');
                        $('#modal-BulkMail').modal('hide');
                        data_table.fnFilter('', 0);
                        reloadJobsDrodown(0);
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                        $(".save").button('reset');
                        $(".savetest").button('reset');
                    }
                    $('.file-input-name').text('');
                    $('#attachment').val('');
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        });

        $('#modal-BulkMail').on('shown.bs.modal', function(event){
            var modal = $(this);
            //show_summernote(modal.find(".message"),editor_options);
        });

        $('#modal-BulkMail').on('hidden.bs.modal', function(event){
            var modal = $(this);
        });

        $(document).on('click','#bulk-Ratesheet,.sendemail',function(){
            var modal = $('#modal-BulkMail');
            if($(this).hasClass('sendemail')){
                editor_options 	  =  		{"leadoptions":true};
                show_summernote(modal.find(".message"),editor_options);
            }else{
                editor_options 	  =  		{"ratetemplateoptions":true};
                show_summernote(modal.find(".message"),editor_options);
            }
			document.getElementById('BulkMail-form').reset();
			$("#modal-BulkMail").find('.file-input-name').html("");
            $("#BulkMail-form [name='template_option']").val('').trigger("change");
            $('#BulkMail-form [name="email_template_privacy"]').val(0).trigger("change");
            $("#BulkMail-form")[0].reset();
            if($(this).hasClass('sendemail')){
                $("#BulkMail-form [name='type']").val('BAE');
                $("#BulkMail-form [name='Type']").val({{EmailTemplate::ACCOUNT_TEMPLATE}});
                $(".attachment").show();
                $("#test").show();
                $(".CD").hide();

            }else{
                $("#BulkMail-form [name='type']").val('CD');
                $("#BulkMail-form [name='Type']").val({{EmailTemplate::RATESHEET_TEMPLATE}});
                $(".attachment").hide();
                $("#test").hide();
                $(".CD").show();
            }
            //drodown_reset();
            $("#modal-BulkMail").modal({
                show: true
            });
        });

        $("#test").click(function(e){
            e.preventDefault();
            $("#BulkMail-form").find('[name="test"]').val(1);
            $('#TestMail-form').find('[name="EmailAddress"]').val('');
            $('#modal-TestMail').modal({show: true});
        });
        $("#mail-send").click(function(e){
            $("#BulkMail-form").find('[name="test"]').val(0);
        });
        $('.lead').click(function(e){
            e.preventDefault();
            var email = $('#TestMail-form').find('[name="EmailAddress"]').val();
            var accontID = $('#TestMail-form').find('[name="accountID"]').val();
            if(email==''){
                toastr.error('Email field should not empty.', "Error", toastr_opts);
                $(".lead").button('reset');
                return false;
            }else if(accontID==''){
                toastr.error('Please select sample account from dropdown', "Error", toastr_opts);
                $(".lead").button('reset');
                return false;
            }
            $('#BulkMail-form').find('[name="testEmail"]').val(email);
            $('#BulkMail-form').find('[name="SelectedIDs"]').val(accontID);
            $("#BulkMail-form").submit();
            $('#modal-TestMail').modal('hide');

        });

        $('#modal-TestMail').on('hidden.bs.modal', function(event){
            var modal = $(this);
            modal.find('[name="test"]').val(0);
        });

        $('#modal-BulkTags').on('hidden.bs.modal', function(event){
            var modal = $(this);
            var el = $('#account_filter').find('[name="tags"]');
            el.siblings('div').remove();
            el.removeClass('select2-offscreen');
            el.select2({tags:{{$accountTags}}});
        });

        $("#bulk-tags").click(function() {
            var el = $('#modal-BulkTags').find('[name="tags"]');
            el.siblings('div').remove();
            el.removeClass('select2-offscreen');
            el.val('');
            el.select2({tags:{{$accountTags}}});
            $('#modal-BulkTags').find('[name="SelectedIDs"]').val('');
            $('.save').button('reset');
            $('#modal-BulkTags').modal('show');
        });
		
		  $("#bulk-Actions").click(function() {
            var SelectedIDs = getselectedIDs();
            if (SelectedIDs.length == 0) {
              toastr.error('Please select at least one Account.', "Error", toastr_opts);
              return false;
            }
            var el = $('#modal-bulk-actions');
            $('#BulkAction-form')[0].reset();
             $('#BulkOwnerChange').val('').trigger('change');
             $('#BulkCurrencyChange').val('').trigger('change');
            $("#BulkAction-form [name='BillingClassID']").select2().select2('val', '');
            $("#BulkAction-form [name='BillingType']").select2().select2('val', '');
            $("#BulkAction-form [name='BillingTimezone']").select2().select2('val', '');
            $("#BulkAction-form [name='BillingCycleType']").select2().select2('val', '');
            $("#BulkAction-form [name='SendInvoiceSetting']").select2().select2('val', '');
            $("#BulkAction-form [name='AutoPaymentSetting']").select2().select2('val', 'never');
            $("#BulkAction-form [name='ResellerOwner']").select2().select2('val', '');
            $('.save').button('reset');
            el.modal('show');
        });

        $(document).on('click','.switcher',function(){
            var self = $(this);
            if(self.hasClass('active')){
                return false;
            }
            var activeurl;
            var desctiveurl;
            if(self.hasClass('grid')){
                setCookie('accountview','grid','30');
                view = 1;
            }else{
                setCookie('accountview','list','30');
                view = 2;
            }
            self.addClass('active');
            var sibling = self.siblings('a').removeClass('active');
            $('.gridview').toggleClass('hidden');
            $('#table-4').toggleClass('hidden');
        });

        $('#add-edit-modal-opportunity .reset').click(function(){
            var colorPicker = $(this).parents('.form-group').find('[type="text"].colorpicker');
            var color = $(this).attr('data-color');
            setcolor(colorPicker,color);
        });

        $(document).on('mouseover','#rating i',function(){
            var currentrateid = $(this).attr('rate-id');
            setrating(currentrateid);
        });
        $(document).on('click','#rating i',function(){
            var currentrateid = $(this).attr('rate-id');
            $('#rating input[name="Rating"]').val(currentrateid);
            setrating(currentrateid);
        });
        $(document).on('mouseleave','#rating',function(){
            var defultrateid = $('#rating input[name="Rating"]').val();
            setrating(defultrateid);
        });

        $("#BulkTag-form").submit(function(e){
            e.preventDefault();
            var SelectedIDs = getselectedIDs();
            if (SelectedIDs.length == 0) {
                $(".save").button('reset');
                $('#modal-BulkTags').modal('hide');
                toastr.error('Please select at least one Account.', "Error", toastr_opts);
                return false;
            }else{
                if(confirm('Do you want to add tags to selected Accounts')){
                    var url = baseurl + "/accounts/bulk_tags";
                    $("#BulkTag-form").find("input[name='SelectedIDs']").val(SelectedIDs.join(","));
                    var formData = new FormData($('#BulkTag-form')[0]);
                    $.ajax({
                        url: url,  //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            if(response.status =='success'){
                                toastr.success(response.message, "Success", toastr_opts);
                                $(".save").button('reset');
                                $('#modal-BulkTags').modal('hide');
                                data_table.fnFilter('', 0);
                                reloadJobsDrodown(0);
                            }else{
                                toastr.error(response.message, "Error", toastr_opts);
                                $(".save").button('reset');
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
            }
        });

        $(".accountTags").select2({
            tags:{{$accountTags}}
        });

        $('.opportunityTags').select2({
            tags:{{$opportunityTags}}
        });

        function drodown_reset(){
            var privacyID = $('#BulkMail-form [name="email_template_privacy"]').val();
            if(privacyID == null){
                return false;
            }
            var Type = $('#BulkMail-form [name="Type"]').val();
            var url = baseurl + '/accounts/' + privacyID + '/ajax_getEmailTemplate/'+Type;
            $.get(url, function (data, status) {
                if (Status = "success") {
                    var modal = $("#modal-BulkMail");
                    var el = modal.find('#BulkMail-form [name=email_template]');
                    rebuildSelect2(el,data,'');
                } else {
                    toastr.error(status, "Error", toastr_opts);
                }
            });
        }
        function editor_reset(data){
            var modal = $("#modal-BulkMail");
            if(!Array.isArray(data)){
                var EmailTemplate = data['EmailTemplate'];
                modal.find('[name="subject"]').val(EmailTemplate.Subject);
                modal.find('.message').val(EmailTemplate.TemplateBody);
                if(EmailTemplate.Type=='{{EmailTemplate::RATESHEET_TEMPLATE}}'){
                    editor_options 	  =  		{"ratetemplateoptions":true};
                }
            }else{
                modal.find('[name="subject"]').val('');
                modal.find('.message').val('');
            }
            show_summernote(modal.find(".message"),editor_options);
        }
		
		
		$('#BulkAction-form').submit(function(e){
			e.preventDefault();
			var SelectedIDs = getselectedIDs();
            var criteria = '';
            if($('#selectallbutton').is(':checked')){
                criteria = JSON.stringify($searchFilter);
            }
			if (SelectedIDs.length == 0) {
				$('#modal-bulk-actions').modal('hide');
				toastr.error('Please select at least one Account.', "Error", toastr_opts);
				return false;
			}else {
				var selectedIDs = $(this).find('[name="BulkselectedIDs"]').val(SelectedIDs.join(","));
				var criterias = $(this).find('[name="BulkActionCriteria"]').val(criteria);
				var url = baseurl + '/accounts/bulkactions';
				showAjaxScript(url, new FormData(($('#BulkAction-form')[0])), function (response) {
                    $("#bulk-submit").button('reset');
					if (response.status == 'success') {
						data_table.fnFilter('', 0);
						//$('#modal-bulk-actions').modal('hide');
						$('#BulkAction-form')[0].reset();
						$('#BulkOwnerChange').val('').trigger('change');
						$('#BulkCurrencyChange').val('').trigger('change');
                        $("#BulkAction-form [name='BillingClassID']").select2().select2('val', '');
                        $("#BulkAction-form [name='BillingType']").select2().select2('val', '');
                        $("#BulkAction-form [name='BillingTimezone']").select2().select2('val', '');
                        $("#BulkAction-form [name='BillingCycleType']").select2().select2('val', '');
                        $("#BulkAction-form [name='SendInvoiceSetting']").select2().select2('val', '');
                        $('#modal-bulk-actions').modal('hide');
						toastr.success(response.message, "Success", toastr_opts);
					} else {
						toastr.error(response.message, "Error", toastr_opts);
                        return false;
					}
				});
			}
   	    });

        /* bulk action billing class on change event */
        $("#BulkAction-form select[name='BillingClassID']").change(function(e){
            if($(this).val()>0) {
                $.ajax({
                    url: baseurl+'/billing_class/getInfo/' + $(this).val(),
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $(this).button('reset');
                        if (response.status == 'success') {

                            $("#BulkAction-form select[name='BillingTimezone']").select2().select2('val', response.data.BillingTimezone);

                            $("#BulkAction-form [name='SendInvoiceSetting']").select2().select2('val',response.data.SendInvoiceSetting);

                            if(response.data.AutoPaymentSetting == null || response.data.AutoPaymentSetting == '') {
                                $("[name='AutoPaymentSetting']").select2().select2('val', 'never');
                            }
                            else{
                                $("[name='AutoPaymentSetting']").select2().select2('val', response.data.AutoPaymentSetting);
                            }
                            $("[name='AutoPayMethod']").select2().select2('val', response.data.AutoPayMethod);
                        }
                    }
                });
            }
        }); // billingclass change event over

        $('#BulkAction-form select[name="BillingCycleType"]').change(function(e){
            var selection = $(this).val();
            var hidden = false;
            if($(this).hasClass('hidden')){
                hidden = true;
            }
            $(".billing_options input, .billing_options select").attr("disabled", "disabled");
            $(".billing_options").hide();
            console.log(selection);
            switch (selection){
                case "weekly":
                    $("#billing_cycle_weekly").show();
                    $("#billing_cycle_weekly select").removeAttr("disabled");
                    $("#billing_cycle_weekly select").addClass('billing_options_active');
                    if(hidden){
                        $("#billing_cycle_weekly select").addClass('hidden');
                    }
                    break;
                case "monthly_anniversary":
                    $("#billing_cycle_monthly_anniversary").show();
                    $("#billing_cycle_monthly_anniversary input").removeAttr("disabled");
                    $("#billing_cycle_monthly_anniversary input").addClass('billing_options_active');
                    if(hidden){
                        $("#billing_cycle_monthly_anniversary input").addClass('hidden');
                    }
                    break;
                case "in_specific_days":
                    $("#billing_cycle_in_specific_days").show();
                    $("#billing_cycle_in_specific_days input").removeAttr("disabled");
                    $("#billing_cycle_in_specific_days input").addClass('billing_options_active');
                    if(hidden){
                        $("#billing_cycle_in_specific_days input").addClass('hidden');
                    }
                    break;
                case "subscription":
                    $("#billing_cycle_subscription").show();
                    $("#billing_cycle_subscription input").removeAttr("disabled");
                    $("#billing_cycle_subscription input").addClass('billing_options_active');
                    if(hidden){
                        $("#billing_cycle_subscription input").addClass('hidden');
                    }
                    break;
            }
        });

        $('#BulkAction-form [name="BillingCheck"]').change(function(e){
            var checked = $(this).is(':checked');
            if(checked){
                $('.bulkbillinghide').hide();
            }else{
                $('.bulkbillinghide').show();
            }

        });

        $("#BulkAction-form [name='ResellerCheck']").on("change",function(e){
            if($("#BulkAction-form [name='ResellerCheck']").prop("checked") == true){
                $("#BulkAction-form [name='ResellerOwnerAddCheck']").prop("checked", false).trigger('change');
            }
        });

        $("#BulkAction-form [name='ResellerOwnerAddCheck']").on("change",function(e){
            if($("#BulkAction-form [name='ResellerOwnerAddCheck']").prop("checked") == true){
                $("#BulkAction-form [name='ResellerCheck']").prop("checked", false).trigger('change');
            }
        });
		
    }); // main script over

    function getselectedIDs(){
        var SelectedIDs = [];
        if($('.gridview').is(':visible')){
            $('.gridview li div.selected .action input[name="accountid"]').each(function(i,el){
                AccountID = $(this).val();
                SelectedIDs[i++] = AccountID;
            });
        }else{
            $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                leadID = $(this).val();
                SelectedIDs[i++] = leadID;
            });
        }
       return SelectedIDs;
    }

    function setCookie(cname,cvalue,exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires=" + d.toGMTString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

</script>
<style>
    .dataTables_filter label{
        display:none !important;
    }
    .dataTables_wrapper .export-data{
        right: 30px !important;
    }
    #selectcheckbox{
        padding: 15px 10px;
    }

	.li_active{display:none;}
</style>
@include('opportunityboards.opportunitymodal')
@include('accounts.unbilledreportmodal')
@include('accounts.bulk_email')
@stop

@section('footer_ext')
    @parent
    <div class="modal fade" id="modal-BulkTags">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="BulkTag-form" method="post" action="">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Bulk Account tags</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-Group">
                                <label class="col-sm-2 control-label">Tag</label>
                                <div class="col-sm-8">
                                    <input class="form-control tags" name="tags" type="text" >
                                    <input type="hidden" name="SelectedIDs" />
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
    <div class="modal fade" id="modal-bulk-actions">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="BulkAction-form" method="post" action="" enctype="multipart/form-data">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Bulk Actions</h4>
        </div>
        <div class="modal-body">
          <div class="row"> @if(User::is_admin())
            <div id="Owner" class="col-md-6">
              <div class="form-group">
                <label for="field-1" class="control-label">
                  <input type="checkbox" name="OwnerCheck">
                  <span>Owner</span></label>
                {{Form::select('account_owners',$account_owners,'',array("class"=>"select2 small","id"=>"BulkOwnerChange"))}} </div>
            </div>
            @endif
            <div id="Currency" class="col-md-6">
              <div class="form-group">
                <label for="field-1" class="control-label">
                  <input type="checkbox"  name="CurrencyCheck">
                  <span>Currency</span></label>
                {{Form::select('Currency',$Currencies,'',array("class"=>"select2 small","id"=>"BulkCurrencyChange"))}} </div>
            </div>
          </div>
          <div class="row">
            <div id="Vendor" class="col-md-6">
              <div class="form-group">
                <label for="field-3" class="control-label">
                  <input type="checkbox"  name="VendorCheck">
                  <span>Vendor</span></label><br>
                <p class="make-switch switch-small">
                  <input id="BulkVendorChange" name="vendor_on_off" type="checkbox" value="1">
                </p>
              </div>
            </div>
            <div id="Customer" class="col-md-6">
              <div class="form-group">
                <label for="field-3" class="control-label">
                  <input type="checkbox"  name="CustomerCheck">
                  <span>Customer</span></label><br>
                <p class="make-switch switch-small">
                  <input id="BulkCustomerChange" name="Customer_on_off" type="checkbox" value="1">
                </p>
              </div>
            </div>
          </div>
            <div class="row">
                <div id="" class="col-md-6">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <input type="checkbox"  name="ResellerCheck">
                            <span>Reseller</span></label><br>
                        <p class="make-switch switch-small">
                            <input id="BulkResellerChange" name="Reseller_on_off" type="checkbox" value="1">
                        </p>
                    </div>
                </div>
                <div id="" class="col-md-6">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <input type="checkbox"  name="ResellerOwnerAddCheck">
                            <span>Account Reseller</span></label><br>
                            {{Form::select('ResellerOwner', $reseller_owners, '' ,array("id"=>"ResellerOwner_id","class"=>"select2 small form-control1"));}}
                    </div>
                </div>
            </div>
            <div class="row">
                <div id="" class="col-md-6">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <input type="checkbox"  name="CustomerPaymentAddCheck">
                            <span>Customer Payment Add</span></label><br>
                        <p class="make-switch switch-small">
                            <input id="BulkCustomerPaymentAdd" name="customerpayment_on_off" type="checkbox" value="1">
                        </p>
                    </div>
                </div>
            </div>
            <hr>
          <!-- billing section start -->
            <div class="row">
                <div id="BillingRow" class="col-md-12">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <input type="checkbox"  name="BillingCheck">
                            <span>Billing</span></label><br>
                            <p class="make-switch switch-small">
                            <input id="BulkBillingChange" name="billing_on_off" type="checkbox" value="1">
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <input type="checkbox" name="BulkBillingClassCheck" class="bulkbillinghide">
                            <span>Billing Class*</span></label><br>
                        {{Form::select('BillingClassID', $BillingClass, '' ,array("id"=>"billingclass_id","class"=>"select2 small form-control1"));}}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <input type="checkbox" name="BulkBillingTypeCheck" class="bulkbillinghide">
                            <span>Billing Type*</span></label><br>
                            {{Form::select('BillingType', AccountApproval::$billing_type, '' ,array('id'=>'billing_type',"class"=>"select2 small"))}}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <input type="checkbox" name="BulkBillingTimezoneCheck" class="bulkbillinghide">
                            <span>Billing Timezone*</span></label><br>
                            {{Form::select('BillingTimezone', $timezones, '' ,array("class"=>"form-control select2"))}}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <input type="checkbox" name="BulkBillingStartDateCheck" class="bulkbillinghide">
                            <span>Billing Start Date*</span>
                            <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Billing Start Date will be updated if NO invoice is generated OR No discount plan applied OR all invoices are marked as Cancel against the selected account." data-original-title="Billing Start Date">?</span>
                        </label><br>
                        {{Form::text('BillingStartDate', '',array('class'=>'form-control datepicker',"data-date-format"=>"yyyy-mm-dd"))}}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <input type="checkbox" name="BulkBillingCycleTypeCheck" class="bulkbillinghide">
                            <span>Billing Cycle*</span>
                            <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Billing Cycle will be updated instantly if no invoice is generated OR No discount plan applied OR all invoices are marked as Cancel against the selected account. Otherwise it will be effective from after current billing period." data-original-title="Billing Cycle">?</span>
                            </label><br>
                        {{Form::select('BillingCycleType', SortBillingType(1), '' ,array("class"=>'form-control select2'))}}                            
                    </div>
                </div>
                <div  id="billing_cycle_weekly" class="billing_options col-md-6" style="display: none">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <span>Billing Cycle - Start of Day*</span></label><br>
                        <?php $Days = array( ""=>"Select",
                                "monday"=>"Monday",
                                "tuesday"=>"Tuesday",
                                "wednesday"=>"Wednesday",
                                "thursday"=>"Thursday",
                                "friday"=>"Friday",
                                "saturday"=>"Saturday",
                                "sunday"=>"Sunday");?>
                        {{Form::select('BillingCycleValue',$Days,''  ,array("class"=>"form-control select2"))}}
                    </div>
                </div>
                <div  id="billing_cycle_in_specific_days" class="billing_options col-md-6" style="display: none">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <span>Billing Cycle - for Days*</span></label><br>
                        {{Form::text('BillingCycleValue', '' ,array("data-mask"=>"decimal", "data-min"=>1, "maxlength"=>"3", "data-max"=>365, "class"=>"form-control","Placeholder"=>"Enter Billing Days"))}}
                    </div>
                </div>
                <div  id="billing_cycle_subscription" class="billing_options col-md-6" style="display: none">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <span>Billing Cycle - Subscription Qty*</span></label><br>
                        {{Form::text('BillingCycleValue', '' ,array("data-mask"=>"decimal", "data-min"=>1, "maxlength"=>"3", "data-max"=>365, "class"=>"form-control","Placeholder"=>"Enter Subscription Qty"))}}
                    </div>
                </div>
                <div  id="billing_cycle_monthly_anniversary" class="billing_options col-md-6" style="display: none">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <span>Billing Cycle - Monthly Anniversary Date*</span></label><br>
                        {{Form::text('BillingCycleValue', '' ,array("class"=>"form-control datepicker","Placeholder"=>"Anniversary Date" , "data-start-date"=>"" ,"data-date-format"=>"yyyy-mm-dd", "data-end-date"=>"+1w", "data-start-view"=>"2"))}}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <input type="checkbox" name="BulkSendInvoiceSettingCheck" class="bulkbillinghide">
                            <span>Send Invoice via Email</span></label><br>
                        {{Form::select('SendInvoiceSetting', BillingClass::$SendInvoiceSetting, '' ,array("class"=>'form-control select2 '))}}
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="field-3" class="control-label">
                            <input type="checkbox" name="BulkAutoPaymentSettingCheck" class="bulkbillinghide">
                            <span>Auto Pay</span></label><br>
                        {{Form::select('AutoPaymentSetting', BillingClass::$AutoPaymentSetting, 'never' ,array("class"=>'form-control select2 small'))}}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">
                            <input type="checkbox" name="BulkAutoPaymentMethodCheck" class="bulkbillinghide">
                            <span>Auto Pay Method</span></label>
                            {{Form::select('AutoPayMethod', BillingClass::$AutoPayMethod,'0',array("class"=>"form-control select2 small"))}}
                    </div>
                </div>
            </div>
           <!-- billing section end -->
        </div>
        <input type="hidden" name="BulkselectedIDs" />
        <input type="hidden" name="BulkActionCriteria" />
        <div class="modal-footer">
          <button  type="submit" id="bulk-submit" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop