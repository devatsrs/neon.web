@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form role="form" id="tickets_filter" method="post" action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label small_label">Search</label>
                    {{ Form::text('search', '', array("class"=>"form-control")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label small_label">Status</label>
                    {{Form::select('status[]', $status, (Input::get('status')?explode(',',Input::get('status')):$OpenTicketStatus) ,array("class"=>"select2","multiple"=>"multiple"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label small_label">Priority</label>
                    {{Form::select('priority[]', $Priority, '' ,array("class"=>"select2","multiple"=>"multiple"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label small_label">Group</label>
                    {{Form::select('group[]', $Groups, '' ,array("class"=>"select2","multiple"=>"multiple"))}}
                </div>

                <div class="form-group">
                    @if(User::is_admin())
                        <label for="field-1" class="control-label small_label">Agent</label>
                        {{Form::select('agent[]', $Agents, (Input::get('agent')?0:'') ,array("class"=>"select2","multiple"=>"multiple"))}}
                    @else
                        @if( TicketsTable::GetTicketAccessPermission() == TicketsTable::TICKETRESTRICTEDACCESS)
                            <input type="hidden" name="agent" value="{{user::get_userID()}}" >
                        @else
                            <input type="hidden" name="agent" value="" >
                        @endif
                    @endif
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label small_label">Due by</label>
                    {{Form::select('overdue[]', TicketsTable::$DueFilter, $overdueVal ,array("class"=>"select2","multiple"=>"multiple"))}}
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
  <li> <a href="{{ URL::to('/dashboard') }}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Tickets</strong> </li>
</ol>
<h3>Tickets</h3>

<div class="clear clearfix"><br>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="input-group-btn pull-right" style="width:70px;">
            @if( User::checkCategoryPermission('Tickets','Edit'))
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                        aria-expanded="false">Action <span class="caret"></span></button>
                <ul class="action dropdown-menu dropdown-menu-left" role="menu"
                    style="background-color: #000; border-color: #000; margin-top:0px;">
                    <li> <a id="bulk-assign" href="javascript:;"> Assign </a> </li>
                    <li> <a id="bulk-close" href="javascript:;" title="Shift+Close to skip notification mail" data-placement="top" data-toggle="tooltip"> Close </a> </li>
                    <li> <a id="bulk-delete" href="javascript:;"> Delete </a> </li>
                    <li> <a id="bulk-action" href="javascript:;"> Bulk Actions </a> </li>
                    <li> <a id="bulk-pickup" href="javascript:;"> Bulk Pickup </a> </li>
                </ul>
            @endif
            <form id="clear-bulk-rate-form">
                <input type="hidden" name="CustomerRateIDs" value="">
            </form>
        </div>

        @if( User::checkCategoryPermission('Tickets','Add'))
            <div class="btn-group pull-right">
                <button href="#" class="btn  btn-primary btn-md  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Add New&nbsp;&nbsp;<span class="caret"></span></button>
                <ul class="dropdown-menu" style="background-color: #000; border-color: #000; margin-top:0px;" role="menu">
                    <li><a href="{{URL::to('/tickets/add')}}">Ticket</a></li>
                    <li><a href="{{URL::to('/tickets/compose_email')}}">Email</a></li>
                </ul>
            </div>
        @endif

        <!-- /btn-group -->
        <div class="clear"><br></div>
    </div>
</div>

<!-- mailbox start -->
<div class="mail-env"> 
  <!-- Mail Body start -->
  <div class="mail-body"> 
    <!-- mail table -->
    <div class="inbox">
        <div id="table-4_processing" class="dataTables_processing">Processing...</div>
    </div>
  </div>
  <!-- Mail Body end --> 
</div>
<!-- mailbox end -->
<style>
.sorted{margin-left:5px;}
.margin-right-10{margin-right:10px;}
.margin-left-mail{margin-right:15px;width:21%; }.mailaction{margin-right:10px;}.btn-blue{color:#fff !important;}
.mail-body{width:100% !important; float:none !important;}
.blue_link{font-size:13px; font-weight:bold;}
.ticket_number{font-size:16px;}
.col-time{text-align:left !important; font-size:12px;}
.col-time span{color:black;}
.dropdown_sort li  a{color:white !important;}
#table-4{display: block; padding-bottom:50px;}
.borderside{border-left-style: solid; border-left-width: 8px;}
.bordersideLow{border-left-color:#00A651;}
.bordersideMedium{border-left-color:#008ff9;}
.bordersideHigh{border-left-color:#ffb613;}
.bordersideUrgent{border-left-color:#CC2424;}
.responsedue{color:#CC2424;}
.customerresponded{color:#008ff9;}
.per_page{margin-left:10px; margin-top:5px; }
.paginationTicket{width:85px;}
#modal-bulk-actions .control-label>span{
        position: relative;
        bottom: 2px;
        left:   5px;
}
.full-width-error{text-align:center;}
.ticketaction{min-width:100px !important;}
</style>
<script type="text/javascript">
	
$(document).ready(function(e) {

    $('#filter-button-toggle').show();

    var currentpage 	= 	-1;
	var next_enable 	= 	1;
	var back_enable 	= 	1;
	var per_page 		= 	{{$iDisplayLength}}
	var clicktype		=	'';
	var ajax_url 		= 	baseurl+'/tickets/ajex_result';
	var ajax_url_export	= 	baseurl+'/tickets/ajex_result_export';
	var SearchStr		=	'';
	var sort_fld  		=   "{{$data['iSortCol_0']}}";
	var sort_type 		=   "{{$data['sSortDir_0']}}";
    //ShowResult('next');

    $(window).on('load',function(){
        $('#tickets_filter').submit();
    });

	$(document).on('click','.move_mail',function(){
		var clicktype = $(this).attr('movetype');	
        ShowResult(clicktype);
    });
	setTimeout(function(){
	$('.filter_minimize_btn').click();
	},100);

	
	$(document).on('submit','#tickets_filter',function(e){		 
		e.stopImmediatePropagation();
		e.preventDefault();		
		currentpage = -1;
		clicktype   = 'next';
		ShowResult(clicktype);
		return false;		
    });	
	
	function ShowResult(clicktype)
	{	
		var $search 		= 	{};
        $search.Search 		= 	$("#tickets_filter").find('[name="search"]').val();
		$search.status		= 	$("#tickets_filter").find('[name="status[]"]').val();
		$search.priority 	= 	$("#tickets_filter").find('[name="priority[]"]').val();		
		$search.group 		= 	$("#tickets_filter").find('[name="group[]"]').val();
		$search.agent 		= 	$("#tickets_filter").find('[name="agent[]"]').val();
		$search.DueBy 		= 	$("#tickets_filter").find('[name="overdue[]"]').val();
		

		 $.ajax({
					url: ajax_url,
					type: 'POST',
					dataType: 'html',
					async :false,
					data:{formData:$search,currentpage:currentpage,per_page:per_page,clicktype:clicktype,sort_fld:sort_fld,sort_type:sort_type},
					success: function(response) {
						
						if(response.length>0)
						{
							if(isJson(response))
							{
								jsonstr =  JSON.parse(response); 
                                //$('.inbox').html('<table id="table-4" class="table table-bordered datatable dataTable"><tr><td class="col-name full-width-error"  align="center" colspan="2">'+jsonstr.result+'</td></tr></table>');
								
								$('.inbox').html('<h3 class="full-width-error">'+jsonstr.result+'</h3>');
								
								if(clicktype=='next')
								 {
									$('.next').addClass('disabled');
								 }
								 else
								 {
									$('.back').addClass('disabled');
								 }
								 $('.mail-pagination').hide();
								return false;
							}
							
							 $('.inbox').html('');
							 $('.inbox').html(response);	
							 if(clicktype=='next')
							 {
								currentpage =  currentpage+1;
							 }
							 else
							 {
								currentpage =  currentpage-1;
							 } 	
							   $("#per_page").select2({
                    				minimumResultsForSearch: -1
                				});
								$('.mail-select-options .select2').css("visibility","visible");
						}
						else
						{ 	
												
												
							if(clicktype=='next')
							 {
								$('.next').addClass('disabled');
							 }
							 else
							 {
								$('.back').addClass('disabled');
							 }						
						}
					
					}
				});	
	}
	
	$(document).on('change','#per_page',function(e){
		e.stopImmediatePropagation();
		e.preventDefault();		
		per_page = $(this).val();		
		clicktype   = 'next';
		currentpage =  currentpage-1;
		ShowResult(clicktype);
		return false;		
		
	});
	
	$(document).on('click','.export_btn',function(e){
		e.stopImmediatePropagation();
		e.preventDefault();		
			
		var $search 		= 	{};
        $search.Search 		= 	$("#tickets_filter").find('[name="search"]').val();
		$search.status		= 	$("#tickets_filter").find('[name="status[]"]').val();
		$search.priority 	= 	$("#tickets_filter").find('[name="priority[]"]').val();		
		$search.group 		= 	$("#tickets_filter").find('[name="group[]"]').val();
		$search.agent 		= 	$("#tickets_filter").find('[name="agent[]"]').val();
		$search.DueBy 		= 	$("#tickets_filter").find('[name="overdue[]"]').val();
		var export_type		=	$(this).attr('action_type');
		
		ajax_url_export = ajax_url_export+"?Search="+$search.Search+"&status="+$search.status+"&priority="+$search.priority+"&group="+$search.group+"&agent="+$search.agent+"&DueBy="+$search.DueBy+"&sort_fld="+sort_fld+"&sort_type="+sort_type+"&export_type="+export_type+"&Export=1";
		window.location = ajax_url_export;
		 /*$.ajax({
					url: ajax_url_export,
					type: 'POST',
					dataType: 'html',
					async :false,
					data:{formData:$search,currentpage:currentpage,per_page:per_page,clicktype:clicktype,sort_fld:sort_fld,sort_type:sort_type,Export:1},
					success: function(response) {
						
					}	
			});	*/
	});
	
	
	
	$(document).on('click','.dropdown-green li a',function(e){
		e.preventDefault();	
		var setaction = 	$(this).attr('action_type'); 
		
		if(setaction=='sort_field'){
			sort_fld  		=   $(this).attr('action_value');			
		}
		if(setaction=='sort_type'){
			sort_type 		=    $(this).attr('action_value');	
		}	
		if(sort_fld!='' && sort_type!='' ){
			currentpage	 	=  -1;
			clicktype   	= 'next';			
			ShowResult(clicktype);
		}	 
    });

    $(document).on('click', '#table-4 tbody tr', function() {
        $(this).toggleClass('selected');
        if($(this).is('tr')) {
            if ($(this).hasClass('selected')) {
                $(this).find('.rowcheckbox').prop("checked", true);
            } else {
                $(this).find('.rowcheckbox').prop("checked", false);
            }
        }
    });

    $(document).on('click', '#selectall',function(ev) {
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

    function getselectedIDs(){
        var SelectedIDs = [];
        $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
            SelectedIDs[i++] = $(this).val();
        });
        return SelectedIDs;
    }

    $('.action li a').click(function(e){
        e.preventDefault();
        resetForm($('#BulkAction-form'),'ticket_bulk_option');
        var self = $(this);
        var modal = $('#modal-bulk-actions');
        modal.find('[name="isSendEmail"]').val(1);
        if(e.shiftKey){
            modal.find('[name="isSendEmail"]').val(0);
        } 
        $("#bulk-submit").button('reset');
        modal.find('.col-md-12').addClass('col-md-4').removeClass('col-md-12');
        modal.find('.col-md-4').each(function(){
            $(this).addClass('hidden');
            $(this).find('[type="checkbox"]').addClass('hidden');
        });
        modal.find('.modal-dialog').removeClass('modal-sm');
        if($(this).prop('id')=='bulk-action'){
            modal.find('.modal-title').text('Bulk Actions');
            modal.find('.col-md-4').each(function(){
                $(this).removeClass('hidden');
                $(this).find('[type="checkbox"]').removeClass('hidden');
            })
        }else if($(this).prop('id')=='bulk-assign'){
            modal.find('.modal-title').text('Bulk Assign');
            modal.find('#agent').removeClass('hidden col-md-4').addClass('col-md-12');
            modal.find('.modal-dialog').addClass('modal-sm');

            modal.find('#group').removeClass('hidden col-md-4').addClass('col-md-12');

        }else if($(this).prop('id')=='bulk-close'){
			
            //modal.find('.modal-title').text('Bulk Close');
            //modal.find('#status').removeClass('hidden col-md-4').addClass('col-md-12');
			if(confirm("Are you sure you want to close selected tickets?"))
			{
            modal.find('[name="Status"]').val('{{array_search(TicketfieldsValues::$Status_Closed,$status)}}').trigger('change');
            //modal.find('.modal-dialog').addClass('modal-sm');
			$('#BulkAction-form').submit();
			 }
			return false;
        }else if($(this).prop('id')=='bulk-delete') {
            var SelectedIDs = getselectedIDs();
            if (SelectedIDs.length == 0) {
                toastr.error('Please select at least one Ticket.', "Error", toastr_opts);
                return false;
            } else {
                if (confirm("Are you sure you want to delete selected tickets?")) {
                    var url = baseurl + '/tickets/bulkdelete';
                    $.post(url, {"SelectedIDs": SelectedIDs.join(",")}, function (response) {
                        if (response.status == 'success') {
                            $('#tickets_filter').submit();
                            toastr.success(response.message, "Success", toastr_opts);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    });
                }
            }
            return true;
        }else if($(this).prop('id')=='bulk-pickup') {
            var SelectedIDs = getselectedIDs();
            if (SelectedIDs.length == 0) {
                toastr.error('Please select at least one Ticket.', "Error", toastr_opts);
                return false;
            }else {
                if(confirm("Are you sure you want to Pickup selected tickets?")) {
                    var url = baseurl + '/tickets/bulkpickup';
                    $.post(url, {"SelectedIDs": SelectedIDs.join(",")}, function (response) {
                        if (response.status == 'success') {
                            $('#tickets_filter').submit();
                            toastr.success(response.message, "Success", toastr_opts);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    });
                }
            }
            return true;
        }
        $('#modal-bulk-actions').modal('show');
    });

    $('#BulkAction-form').submit(function(e){
        e.preventDefault();
        var SelectedIDs = getselectedIDs();
        if (SelectedIDs.length == 0) {
            $('#modal-bulk-actions').modal('hide');
            toastr.error('Please select at least one Ticket.', "Error", toastr_opts);
            return false;
        }else {
            var selectedIDs = $(this).find('[name="selectedIDs"]').val(SelectedIDs.join(","));
            var url = baseurl + '/tickets/bulkactions';
            showAjaxScript(url, new FormData(($('#BulkAction-form')[0])), function (response) {
                $("#bulk-submit").button('reset');
                if (response.status == 'success') {
                    $('#tickets_filter').submit();
                    $('#modal-bulk-actions').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                } else {
                    toastr.error(response.message, "Error", toastr_opts);
                }
            });
        }
    });

    $('#modal-bulk-actions .select2').change(function(e){
        var self = $(this);
        var label = self.siblings('.control-label');
        if(self.val() > 0){
            label.find('[type="checkbox"]').prop('checked',true);
            label.find('span').css('font-weight',700);
        }else{
            label.find('[type="checkbox"]').prop('checked',false);
            label.find('span').css('font-weight',400);
        }
    });

    $('#modal-bulk-actions [type="checkbox"]').change(function(){
        var self = $(this);
        if(self.prop('checked')){
            self.siblings('span').css('font-weight',700);
        }else{
            self.siblings('span').css('font-weight',400);
            self.parents('.control-label').siblings('.select2').val(0).trigger('change');
        }
    });
	
	
	$(document).on('change','#BulkGroupChange',function(e){
		   var changeGroupID =  	$(this).val(); 
		   
		  	if(changeGroupID==0){
		   		 $('#BulkAgentChange option').remove();
				 $('#BulkAgentChange').append($("<option></option>").attr("value", 0).text('Select'));
				 $("#BulkAgentChange").trigger('change');
				 return false;
			}
		   if(changeGroupID)
		   {
		   	 changeGroupID = parseInt(changeGroupID);
			 var ajax_url  = baseurl+'/ticketgroups/'+changeGroupID+'/getgroupagents';
			 $.ajax({
					url: ajax_url,
					type: 'POST',
					dataType: 'json',
					async :false,
					cache: false,
					contentType: false,
					processData: false,
					data:{s:1},
					success: function(response) { console.log(response);
					   if(response.status =='success')
					   {			
						   var $el = this;		   
						   $('#BulkAgentChange option').remove();
						   $.each(response.data, function(key,value) {							  
							  $('#BulkAgentChange').append($("<option></option>").attr("value", value).text(key));
							  $("#BulkAgentChange").val($("#BulkAgentChange option:first").val());
							  $("#BulkAgentChange").trigger('change');
							});					
						}else{
							toastr.error(response.message, "Error", toastr_opts);
						}                   
					}
					});	
		return false;		
		   }
		   
	  });

});
</script> 
@stop

@section('footer_ext')
    @parent
    <div class="modal fade" id="modal-bulk-actions">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="BulkAction-form" method="post" action="" enctype="multipart/form-data">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Bulk Actions</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div id="type" class="col-md-4">
                                <div class="form-group">
                                    <label for="field-1" class="control-label"><input type="checkbox" name="TypeCheck"> <span>Type</span></label>
                                    {{Form::select('Type',$Type,'',array("class"=>"select2 small"))}}
                                </div>
                            </div>
                            <div id="status" class="col-md-4">
                                <div class="form-group">
                                    <label for="field-3" class="control-label"><input type="checkbox"  name="StatusCheck"><span>Status</span></label>
                                    {{Form::select('Status',$status,'',array("class"=>"select2 small"))}}
                                </div>
                            </div>
                            <div id="priority" class="col-md-4">
                                <div class="form-group">
                                    <label for="field-3" class="control-label"><input type="checkbox"  name="PriorityCheck"><span>Priority</span></label>
                                    {{Form::select('Priority',$Priority,'',array("class"=>"select2 small"))}}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div id="group" class="col-md-4">
                                <div class="form-group">
                                    <label for="field-1" class="control-label"><input type="checkbox"  name="GroupCheck"><span>Group</span></label>
                                    {{Form::select('Group',$Groups,'',array("class"=>"select2 small","id"=>"BulkGroupChange"))}}
                                </div>
                            </div>
                            <div id="agent" class="col-md-4">
                                <div class="form-group">
                                    <label for="field-3" class="control-label"><input type="checkbox"  name="AgentCheck"><span>Agent</span></label>
                                    {{Form::select('Agent',array("0"=>"Select"),'',array("class"=>"select2 small","id"=>"BulkAgentChange"))}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="selectedIDs" />
                    <input type="hidden" name="isSendEmail" value="1" />
                    <div class="modal-footer">
                        <button  type="submit" id="bulk-submit" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop