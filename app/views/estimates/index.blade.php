@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="estimate_filter" method="get" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Account</label>
                    {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Status</label>
                    {{ Form::select('EstimateStatus', Estimate::get_estimate_status(), '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Status")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Currency</label>
                    {{Form::select('CurrencyID',Currency::getCurrencyDropdownIDList(),$DefaultCurrencyID,array("class"=>"select2"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Estimate Number</label>
                    {{ Form::text('EstimateNumber', '', array("class"=>"form-control")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Issue Date Start</label>
                    {{ Form::text('IssueDateStart', '', array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}<!-- Time formate Updated by Abubakar -->
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Issue Date End</label>
                    {{ Form::text('IssueDateEnd', '', array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}
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
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Estimate</strong> </li>
</ol>
<h3>Estimate</h3>
@include('includes.errors')
@include('includes.success')
  <!-- <a href="javascript:;" id="bulk-estimate" class="btn upload btn-primary ">
        <i class="entypo-upload"></i>
        Bulk Estimate Generate.
    </a>--> 
</p>
<div class="row">
  <div  class="col-md-12">
    <div class="input-group-btn pull-right" style="width:70px;"> @if( User::checkCategoryPermission('Estimate','Edit'))
      <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
      <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
        @if(User::checkCategoryPermission('Estimate','Edit'))
        <li> <a class="delete_bulk" id="delete_bulk" href="javascript:;" > Delete </a> </li>
        @endif       
        @if(User::checkCategoryPermission('Estimate','Edit'))
        <li> <a class="convert_invoice" id="convert_invoice" href="javascript:;" >Accept and generate invoice</a> </li>
        @endif
      </ul>
      @endif
      <form id="clear-bulk-rate-form" >
        <input type="hidden" name="CustomerRateIDs" value="">
      </form>
    </div>

      @if(User::checkCategoryPermission('Estimate','Add'))
          <a href="{{URL::to("estimate/create")}}" id="add-new-estimate" class="btn btn-primary pull-right"> <i class="entypo-plus"></i> Add New</a>
      @endif
    <!-- /btn-group --> 
  </div>
  <div class="clear"></div>
</div>
<br>
<table class="table table-bordered datatable" id="table-4">
  <thead>
    <tr>
      <th width="5%"><div class="pull-left">
          <input type="checkbox" id="selectall" name="checkbox[]" class="" />
        </div></th>
      <th width="20%">Account Name</th>
      <th width="10%">Estimate Number</th>
      <th width="15%">Issue Date</th>
      <th width="10%">Grand Total</th>
      <th width="10%">Estimate Status</th>
      <th width="20%">Action</th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>
<script type="text/javascript">
var $searchFilter 	= 	{};
var checked			=	'';
var update_new_url;
var postdata;
    jQuery(document).ready(function ($) {

        $('#filter-button-toggle').show();

        jQuery(document).on( 'click', '.delete_link', function(event){
			event.preventDefault();
			var url_del = jQuery(this).attr('href');
			
			//////////////////////////////////////
			
			 $.ajax({
                url: url_del,
                type: 'POST',
                dataType: 'json',
				data:{"del":1},
                success: function(response_del) {
                       if (response_del.status == 'success')
					   {
						   jQuery(this).parent().parent().parent().hide('slow').remove();                          
                           data_table.fnFilter('', 0);
                       }
					   else
					   {
                           ShowToastr("error",response.message);
                       }
                   
					},
			});	
		
			//////////////////////////////////////////
			
		});
		
        public_vars.$body = $("body");
        //show_loading_bar(40); 
		var base_url_estimate 		= 	"{{ URL::to('estimate')}}";
        var estimatestatus 			=	{{$estimate_status_json}};
        var estimate_Status_Url 	= 	"{{ URL::to('estimate/estimate_change_Status')}}";
		var delete_url_bulk 		= 	"{{ URL::to('estimate/estimate_delete_bulk')}}";
        var list_fields  			= 	['AccountName','EstimateNumber','IssueDate','GrandTotal','EstimateStatus','EstimateID','Description','Attachment','AccountID','BillingEmail'];
		
        $searchFilter.AccountID 			= 	$("#estimate_filter select[name='AccountID']").val();
        $searchFilter.EstimateStatus 		= 	$("#estimate_filter select[name='EstimateStatus']").val();
        $searchFilter.EstimateNumber 		= 	$("#estimate_filter [name='EstimateNumber']").val();
        $searchFilter.IssueDateStart 		= 	$("#estimate_filter [name='IssueDateStart']").val();
        $searchFilter.IssueDateEnd 			= 	$("#estimate_filter [name='IssueDateEnd']").val();	
		$searchFilter.CurrencyID            =   $("#estimate_filter [name='CurrencyID']").val();

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/estimate/ajax_datagrid/type",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[3, 'desc']],
             "fnServerParams": function(aoData) {				
                aoData.push({"name":"EstimateType","value":$searchFilter.EstimateType},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"EstimateNumber","value":$searchFilter.EstimateNumber},{"name":"EstimateStatus","value":$searchFilter.EstimateStatus},{"name":"IssueDateStart","value":$searchFilter.IssueDateStart},{"name":"IssueDateEnd","value":$searchFilter.IssueDateEnd},{"name":"CurrencyID","value":$searchFilter.CurrencyID});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"EstimateType","value":$searchFilter.EstimateType},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"EstimateNumber","value":$searchFilter.EstimateNumber},{"name":"EstimateStatus","value":$searchFilter.EstimateStatus},{"name":"IssueDateStart","value":$searchFilter.IssueDateStart},{"name":"IssueDateEnd","value":$searchFilter.IssueDateEnd},{ "name": "Export", "value": 1},{"name":"CurrencyID","value":$searchFilter.CurrencyID});
            },
             "aoColumns":
            [
                {  "bSortable": false,
                                mRender: function ( id, type, full ) {									
                                     var action , action = '<div class = "hiddenRowData" >';    
                                      //if (full[4] != 'accepted')
									  {
                                        action += '<div class="pull-left"><input type="checkbox" class="checkbox rowcheckbox" value="'+full[5]+'" name="EstimateID[]"></div>';
                                      }
									  action += '</div>';
                                        return action;
                                     }

                                    },  // 0 AccountName
                {  "bSortable": true,

                mRender:function( id, type, full){
                                        var output , account_url;
										
                                        output = '<a href="{url}" target="_blank" >{account_name}';
                                        if(full[9] =='')
										{
                                        	output+= '<br> <span class="text-danger"><small>(Email not setup)</small></span>';
                                        }
                                        output+= '</a>';
                                        account_url = baseurl + "/accounts/"+ full[8] + "/show";
                                        output = output.replace("{url}",account_url);
                                        output = output.replace("{account_name}",full[0]);
                                        return output;
                                     }

                },  // 1 EstimateNumber
                {  "bSortable": true,

                mRender:function( id, type, full){
                                                        var output , account_url;
                                                        output = '<a href="{url}" target="_blank"> ' +full[1] + '</a>';
                                                        account_url = baseurl + "/estimate/"+ full[5] + "/estimate_preview";
                                                        output = output.replace("{url}",account_url);
                                                        //output = output.replace("{account_name}",full[1]);
                                                        return output;
                                                     }

                },  // 2 IssueDate
                {  "bSortable": true,

                mRender:function( id, type, full){
                                                        var output = full[2];
                                                        return output;
                                                     } },  // 3 IssueDate
                {  "bSortable": true,

                mRender:function( id, type, full){
                                                        var output = full[3];
                                                        return output;
                                                     } },  // 4 GrandTotal
                {  "bSortable": true,
                    mRender:function( id, type, full){
                        return  estimatestatus[full[4]]; 
                    }

                },  // 5 EstimateStatus
                {
                   "bSortable": false,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ , delete_,view_url,edit_url,download_url,estimate_preview,delete_url;
                         
							action 				= 	'<div class = "hiddenRowData" >';
                            edit_url 			= 	(baseurl + "/estimate/{id}/edit").replace("{id}",full[5]);
							delete_url 			= 	(baseurl + "/estimate/{id}/delete").replace("{id}",full[5]);
                            estimate_preview	= 	(baseurl + "/estimate/{id}/estimate_preview").replace("{id}",full[5]);
                        

                         for(var i = 0 ; i< list_fields.length; i++)
						 {
                            action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                         }
						 
                         action += '</div>';

                          /*Multiple Dropdown*/              			
                            action += '<div class="btn-group">';
                            action += ' <a id="dLabel" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="#">Action<span class="caret"></span></a>';
                            action += '<ul class="dropdown-menu multi-level dropdown-menu-left" role="menu" aria-labelledby="dropdownMenu">';

                                if('{{User::checkCategoryPermission('Estimate','Edit')}}')
								{
									//if(full[4] != 'accepted')
									{
                                        action += ' <li><a class="icon-left"  href="' + (baseurl + "/estimate/{id}/edit").replace("{id}",full[5]) +'"><i class="entypo-pencil"></i>Edit </a></li>';
									}
										
                                }
                          
							
                            if (estimate_preview)
							{                                
                                action += '<li><a class="icon-left"  target="_blank" href="' + estimate_preview +'"> <i class="fa fa-eye"></i> View </a></li>';
                            }

                            action += ' <li><a class="icon-left"  href="' + (baseurl + "/estimate/estimatelog/{id}").replace("{id}",full[5]) +'"><i class="entypo-list"></i>Log </a></li>';

							if ('{{User::checkCategoryPermission('Estimate','Edit')}}' && delete_url)
							{     
								//if(full[4] != 'accepted')
								{                           
                                	action += '<li><a class="icon-left delete_link"  target="_blank" href="' + delete_url +'"><i class="entypo-trash"></i>Delete</a></li>';				}
                            }
                            
							//if(full[11]== 'N')
							{
	                                   action += ' <li><a class="icon-left send_estimate"  estimate="'+full[5]+'"><i class="entypo-mail"></i>Send</a></li>';    
											action += ' <li><a class="icon-left convert_estimate"  estimate="'+full[5]+'"><i class="entypo-check"></i>Accept and generate invoice</a></li>';
						}
                            

                            action += '</ul>';
                            action += '</div>';
							
							//if(full[4] != 'accepted')
							{
                             action += ' <div class="btn-group"><button href="#" class="btn generate btn-success btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Change Status <span class="caret"></span></button>'
                             action += '<ul class="dropdown-menu dropdown-green" role="menu">';
                             $.each(estimatestatus, function( index, value ) {
                                 
                                     action +='<li><a data-estimatestatus="' + index+ '" data-estimateid="' + full[5]+ '" href="' + estimate_Status_Url+ '" class="changestatus" >'+value+'</a></li>';
                                 

                             });
							 
                             action += '</ul>' +
                             '</div>';
							}
                       
                        return action;
                      }
                  },
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/estimate/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/estimate/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
           "fnDrawCallback": function() {
			  get_total_grand();
                $('#table-4 tbody tr').each(function(i, el) {
                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                        if (checked != '') {
                            $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                            $(this).addClass('selected');
                            $('#selectallbutton').prop("checked", true);
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                            ;
                            $(this).removeClass('selected');
                        }
						
                    }
                    });
                   //After Delete done
                   FnDeleteEstimateTemplateSuccess = function(response){

                       if (response.status == 'success') {
                           $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                           ShowToastr("success",response.message);
                           data_table.fnFilter('', 0);
                       }else{
                           ShowToastr("error",response.message);
                       }
                   }
                   //onDelete Click
                   FnDeleteEstimateTemplate = function(e){
                       result = confirm("Are you Sure?");
                       if(result){
                           var id  = $(this).attr("data-id");
                           showAjaxScript( baseurl + "/estimate/"+id+"/delete" ,"",FnDeleteEstimateTemplateSuccess );
                       }
                       return false;
                   }
                   $(".delete-estimate").click(FnDeleteEstimateTemplate); // Delete Note
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
               $('#selectallbutton').click(function(ev) {
                   if($(this).is(':checked')){
                       checked = 'checked=checked disabled';
                       $("#selectall").prop("checked", true).prop('disabled', true);
                       if(!$('#changeSelectedEstimate').hasClass('hidden')){
                           $('#table-4 tbody tr').each(function(i, el) {
                               if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {

                                   $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                   $(this).addClass('selected');
                               }
                           });
                       }
                   }else{
                       checked = '';
                       $("#selectall").prop("checked", false).prop('disabled', false);
                       if(!$('#changeSelectedEstimate').hasClass('hidden')){
                           $('#table-4 tbody tr').each(function(i, el) {
                               if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {

                                   $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                   $(this).removeClass('selected');
                               }
                           });
                       }
                   }
               });
           }

        });

        $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');

        $("#estimate_filter").submit(function(e){
            e.preventDefault();
            $searchFilter.AccountID 		= 	$("#estimate_filter select[name='AccountID']").val();
            $searchFilter.EstimateNumber 	= 	$("#estimate_filter [name='EstimateNumber']").val();
            $searchFilter.EstimateStatus 	= 	$("#estimate_filter select[name='EstimateStatus']").val();
            $searchFilter.IssueDateStart 	= 	$("#estimate_filter [name='IssueDateStart']").val();
            $searchFilter.IssueDateEnd 		= 	$("#estimate_filter [name='IssueDateEnd']").val();			
			$searchFilter.CurrencyID 		= 	$("#estimate_filter [name='CurrencyID']").val();			
            data_table.fnFilter('', 0);
			//get_total_grand();
            return false;
        });
		
		function get_total_grand()
		{
			 $.ajax({
                url: baseurl + "/estimate/ajax_datagrid_total",
                type: 'GET',
                dataType: 'json',
				data:{
			"AccountID":$("#estimate_filter select[name='AccountID']").val(),
			"EstimateNumber":$("#estimate_filter [name='EstimateNumber']").val(),
			"EstimateStatus":$("#estimate_filter select[name='EstimateStatus']").val(),
			"IssueDateStart":$("#estimate_filter [name='IssueDateStart']").val(),
			"IssueDateEnd":$("#estimate_filter [name='IssueDateEnd']").val(),
			"CurrencyID":$("#estimate_filter [name='CurrencyID']").val(),
			"bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/estimate/ajax_datagrid/type",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[3, 'desc']],},
                success: function(response1) {
					console.log("sum of result"+response1);
					 if(response1.total_grand!=null)
					{ 
						$('#table-4 tbody').append('<tr><td><strong>Total</strong></td><td align="right" colspan="3"></td><td><strong>'+response1.total_grand+'</strong></td><td colspan="2"></td></tr>');	
					}
					},
			});	
		}

        
         $("#add-estimate_in_template-form [name='AccountID']").change(function(){
            $("#add-estimate_in_template-form [name='AccountName']").val( $("#add-estimate_in_template-form [name='AccountID'] option:selected").text());
            var url = baseurl + '/payments/getcurrency/'+$("#add-estimate_in_template-form [name='AccountID'] option:selected").val();
            $.get( url, function( Currency ) {
                $("#currency").text('('+Currency+')');
                $("#add-estimate_in_template-form [name='Currency']").val(Currency);
            });
        });
        $("#add-estimate_in_template-form").submit(function(e){
            e.preventDefault();
            var formData = new FormData($('#add-estimate_in_template-form')[0]);
             var EstimateID = $("#add-estimate_in_template-form [name='EstimateID']").val()
            if( typeof EstimateID != 'undefined' && EstimateID != ''){
                update_new_url = baseurl + '/estimate/update_estimate_in/'+EstimateID;
            }else{
                update_new_url = baseurl + '/estimate/add_estimate_in';
            }
            submit_ajax_withfile(update_new_url,formData)
       });
   

        // Replace Checboxes
        $(".pagination a").click(function (ev) {			
            replaceCheckboxes();			
        });

        $("#selectall").click(function(ev) {
            var is_checked = $(this).is(':checked');
            $('#table-4 tbody tr').each(function(i, el) {
                if($(this).find('.rowcheckbox').hasClass('rowcheckbox')){
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                }
            });
        });
        $('#table-4 tbody').on('click', 'tr', function() {
            if (checked =='') {
                if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                    $(this).toggleClass('selected');
                    if ($(this).hasClass('selected')) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                    }
                }
            }
        });
		
		$('#convert_invoice').click(function(e) {			
	        e.preventDefault();
            var self = $(this);
            var text = self.text();
			
			var EstimateIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                EstimateID = $(this).val();
                if(typeof EstimateID != 'undefined' && EstimateID != null && EstimateID != 'null'){
                    EstimateIDs[i++] = EstimateID;
                }
            });
			var all_chceked = 0;
			if($('#selectallbutton').is(':checked')){
				all_chceked=1;
			}
			
			if(EstimateIDs.length<1)
			{
				alert("Please select atleast one estimate.");
				return false;
			}
            console.log(EstimateIDs);
			
            if (!confirm('Are you sure you to change status of selected estimates ?')) {
                return;
            }

            $.ajax({
                url: estimate_Status_Url+'_Bulk',
                type: 'POST',
                dataType: 'json',
				data:{
				'EstimateIDs':EstimateIDs,					
				"AccountID":$("#estimate_filter select[name='AccountID']").val(),
				"EstimateNumber":$("#estimate_filter [name='EstimateNumber']").val(),
				"EstimateStatus":$("#estimate_filter select[name='EstimateStatus']").val(),
				"IssueDateStart":$("#estimate_filter [name='IssueDateStart']").val(),
				"IssueDateEnd":$("#estimate_filter [name='IssueDateEnd']").val(),
				"CurrencyID":$("#estimate_filter [name='CurrencyID']").val(),
				"AllChecked":all_chceked
					},
                success: function(response) {
                    $(this).button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        data_table.fnFilter('', 0);
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                }
               

            });
            return false;
        });
		
	   $(document).on( 'click', '.send_estimate', function(e){			
			  estimate_id = $(this).attr('estimate');
        $('#send-modal-estimate').find(".modal-body").html("Loading Content...");
        var ajaxurl = "/estimate/"+estimate_id+"/estimate_email";
        showAjaxModal(ajaxurl,'send-modal-estimate');
        $("#send-estimate-form")[0].reset();
        $('#send-modal-estimate').modal('show');
    });
	
	$(document).on( 'click', '.convert_estimate', function(e){			
		$(this).attr('disabled', 'disabled');	 
		$(this).button('loading');
		$('.dataTables_processing').css("visibility","visible");
		estimate_id = $(this).attr('estimate');       
        var ajaxurl_convert = base_url_estimate+"/"+estimate_id+"/convert_estimate";
		
		   $.ajax({
			url: ajaxurl_convert,
			type: 'POST',
			dataType: 'json',
			data:{'eid':estimate_id,'convert':1},
			success: function(response) {
				$(this).button('reset');
				$('.dataTables_processing').css("visibility","hidden");
				if (response.status == 'success') {
					toastr.success(response.message, "Success", toastr_opts);
					data_table.fnFilter('', 0);
				} else {
					toastr.error(response.message, "Error", toastr_opts);
				}
			}
		   

		});
    });
	
	
		
		$('#delete_bulk').click(function(e) {
			
	        e.preventDefault();
            var self = $(this);
            var text = self.text();
			
			var EstimateIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                EstimateID = $(this).val();
                if(typeof EstimateID != 'undefined' && EstimateID != null && EstimateID != 'null'){
                    EstimateIDs[i++] = EstimateID;
                }
            });
			
			if(EstimateIDs.length<1)
			{
				alert("Please select atleast one estimate.");
				return false;
			}
            console.log(EstimateIDs);
			
            if (!confirm('Are you sure to delete selected estimates?')) {
                return;
            }

            $.ajax({
                url: delete_url_bulk,
                type: 'POST',
                dataType: 'json',
				data:'del_ids='+EstimateIDs,
                success: function(response) {
                    $(this).button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        data_table.fnFilter('', 0);
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                }
               

            });
            return false;
        });
		
        $("#changeSelectedEstimate").click(function(ev) {
            var criteria='';
            if($('#selectallbutton').is(':checked')){
                 criteria = JSON.stringify($searchFilter);
            }
            var EstimateIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                //console.log($(this).val());
                EstimateID = $(this).val();
                if(typeof EstimateID != 'undefined' && EstimateID != null && EstimateID != 'null'){
                    EstimateIDs[i++] = EstimateID;
                }
                
				if(EstimateIDs.length)
				{
                    $("#selected-estimate-status-form").find("input[name='EstimateIDs']").val(EstimateIDs.join(","));
                    $("#selected-estimate-status-form").find("input[name='criteria']").val(criteria);
                    $('#selected-estimate-status').modal('show');
                    $("#selected-estimate-status-form [name='EstimateStatus']").select2().select2('val','');
                    $("#selected-estimate-status-form [name='CancelReason']").val('');
                    $('#statuscancel').hide();
                }
            });
        });

        $("#selected-estimate-status-form").submit(function(e){
            e.preventDefault();
            var EstimateStatus = $(this).find("select[name='EstimateStatus']").val();

            if(EstimateStatus != '')
            {
                    formData = $("#selected-estimate-status-form").serialize();
                    update_new_url = baseurl +'/estimate/estimate_change_Status';
                    submit_ajax(update_new_url,formData)
               
            }else{
            toastr.error("Please Select Estimates Status", "Error", toastr_opts);
            $(this).find(".cancelbutton]").button("reset");
            return false;
            }

       });
       $("#selected-estimate-status-form [name='EstimateStatus']").change(function(e){
            e.preventDefault();
            $('#statuscancel').hide();
            var status = $(this).val();
       });

       $("#estimate-status-cancel-form").submit(function(e){
           e.preventDefault();
           if($(this).find("input[name='CancelReason']").val().trim() != ''){
                submit_ajax(estimate_Status_Url,$(this).serialize())
           }
       });
       $('table tbody').on('click', '.changestatus', function (e) {
            e.preventDefault();
            var self = $(this);
            var text = self.text();
            if (!confirm('Are you sure you want to change the estimate status to '+ text +'?')) {
                return;
            }

            $(this).button('loading');
            $.ajax({
                url: $(this).attr("href"),
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
                data:'EstimateStatus='+$(this).attr('data-estimatestatus')+'&EstimateIDs='+$(this).attr('data-estimateid')

            });
            return false;
        });

        $('table tbody').on('click', '.send-estimate', function (ev) {
            //var cur_obj = $(this).prevAll("div.hiddenRowData");
            var cur_obj 	= 	$(this).parent().parent().parent().parent().find("div.hiddenRowData");
            EstimateID 		= 	cur_obj.find("[name=EstimateID]").val();
            send_url 		=  	("/estimate/{id}/estimate_email").replace("{id}",EstimateID);
            console.log(send_url)
            showAjaxModal( send_url ,'send-modal-estimate');
            $('#send-modal-estimate').modal('show');
        });
		
		$('#send-modal-estimate').on('shown.bs.modal', function (event) {
				//setTimeout(function(){ console.log('select2');  $("#send-modal-estimate").find(".select22").select2();  }, 700);
			});

        $("#send-estimate-form").submit(function(e){
            e.preventDefault();
            var post_data  = $(this).serialize();
            var EstimateID = $(this).find("[name=EstimateID]").val();
            var _url = baseurl + '/estimate/'+EstimateID+'/send';
            submit_ajax(_url,post_data);
        });

        $("#bulk-estimate-send").click(function(ev) {
            var criteria='';
            if($('#selectallbutton').is(':checked')){
                 criteria = JSON.stringify($searchFilter);
            }
            var EstimateIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
				//console.log($(this).val());
                EstimateID = $(this).val();
                if(typeof EstimateID != 'undefined' && EstimateID != null && EstimateID != 'null'){
                    EstimateIDs[i++] = EstimateID;
                }
            });
            console.log(EstimateIDs);

            if(EstimateIDs.length){
                if (!confirm('Are you sure you want to send selected Estimates?')) {
                    return;
                }
                $.ajax({
                    url: baseurl + '/estimate/bulk_send_estimate_mail',
                    data: 'EstimateIDs='+EstimateIDs+'&criteria='+criteria,
                    error: function () {
                        toastr.error("error", "Error", toastr_opts);
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },
                    type: 'POST'
                });

            }

        });

        
        $("#test").click(function(e){
            e.preventDefault();
            $("#BulkMail-form").find('[name="test"]').val(1);
            $('#TestMail-form').find('[name="EmailAddress"]').val('');
            $('#modal-TestMail').modal({show: true});
        });
       $('.alert').click(function(e){
            e.preventDefault();
            var email = $('#TestMail-form').find('[name="EmailAddress"]').val();
            var accontID = $('.hiddenRowData').find('.rowcheckbox').val();
            if(email==''){
                toastr.error('Email field should not empty.', "Error", toastr_opts);
                $(".alert").button('reset');
                return false;
            }else if(accontID==''){
                toastr.error('Please select sample estimate', "Error", toastr_opts);
                $(".alert").button('reset');
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


});

</script>
<style>
#table-4 .dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}
 #table-5_filter label{
    display:block !important;
}
#selectcheckbox{
    padding: 15px 10px;
}
</style>
@stop
@section('footer_ext')
@parent 
<!-- Job Modal  (Ajax Modal)-->
<div class="modal fade custom-width" id="print-modal-estimate">
  <div class="modal-dialog" style="width: 60%;">
    <div class="modal-content">
      <form id="add-new-estimate_template-form" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
        <div class="modal-header">
          <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
          <h4 class="modal-title"> <a class="btn btn-primary print btn-sm btn-icon icon-left" href=""> <i class="entypo-print"></i> Print </a> </h4>
        </div>
        <div class="modal-body"> Content is loading... </div>
        <div class="modal-footer">
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade in" id="send-modal-estimate">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="send-estimate-form" method="post" >
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Send Estimate By Email</h4>
        </div>
        <div class="modal-body"> </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary send btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-mail"></i> Send </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop