@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{ URL::to('/dashboard') }}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Tickets</strong> </li>
</ol>
<h3>Tickets</h3>
@if( User::checkCategoryPermission('Tickets','Add'))
<p class="text-right">
<div class="btn-group pull-right">
  <button href="#" class="btn  btn-primary btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Add New&nbsp;&nbsp;<span class="caret"></span></button>
  <ul class="dropdown-menu" style="background-color: #000; border-color: #000; margin-top:0px;" role="menu">
    <li><a href="{{URL::to('/tickets/add')}}">Ticket</a></li>
    <li><a href="{{URL::to('/tickets/compose_email')}}">Email</a></li>
  </ul>
</div>
</p>
@endif
<div class="clear clearfix"><br>
</div>
<div class="row">
  <div class="col-md-12">
    <form role="form" id="tickets_filter" method="post" action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate>
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title"> Filter </div>
          <div class="panel-options"> <a class="filter_minimize_btn" href="#" data-rel="collapse"><i class=" entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body" id="paymentsearch">
          <div class="form-group">
            <label for="field-1" class="col-sm-1 control-label small_label">Search</label>
            <div class="col-sm-2"> {{ Form::text('search', '', array("class"=>"form-control")) }} </div>
            <label for="field-1" class="col-sm-1 control-label small_label">Status</label>
            <div class="col-sm-2"> {{Form::select('status[]', $status, '' ,array("class"=>"select2","multiple"=>"multiple"))}} </div>
            <label for="field-1" class="col-sm-1 control-label small_label">Priority</label>
            <div class="col-sm-2"> {{Form::select('priority[]', $Priority, '' ,array("class"=>"select2","multiple"=>"multiple"))}} </div>
            <label for="field-1" class="col-sm-1 control-label small_label">Group</label>
            <div class="col-sm-2"> {{Form::select('group[]', $Groups, '' ,array("class"=>"select2","multiple"=>"multiple"))}} </div>
          </div>
          @if(User::is_admin())
          <div class="form-group">
            <label for="field-1" class="col-sm-1 control-label small_label">Agent</label>
            <div class="col-sm-2"> {{Form::select('agent[]', $Agents, '' ,array("class"=>"select2","multiple"=>"multiple"))}} </div>
          </div>
          @else
          @if( TicketsTable::GetTicketAccessPermission() == TicketsTable::TICKETRESTRICTEDACCESS)
          <input type="hidden" name="agent" value="{{user::get_userID()}}" >
          @else
          <input type="hidden" name="agent" value="" >
          @endif
          @endif
          <p style="text-align: right;">
            <button type="submit" class="btn btn-primary btn_form_submit btn-sm btn-icon icon-left"> <i class="entypo-search"></i> Search </button>
          </p>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="input-group-btn pull-right" style="width:70px;">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                        aria-expanded="false">Action <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-menu-left" role="menu"
                    style="background-color: #000; border-color: #000; margin-top:0px;">
                    <li> <a class="generate_rate create" id="bulk_delete" href="javascript:;" style="width:100%"> Delete </a></li>
                    <li> <a class="generate_rate create" id="bulk_change_status" href="javascript:;"> Change Status </a> </li>
                    <li> <a class="generate_rate create" id="bulk_assign" href="javascript:;"> Assign </a> </li>
                </ul>
            <form id="clear-bulk-rate-form">
                <input type="hidden" name="CustomerRateIDs" value="">
            </form>
        </div>
        <!-- /btn-group -->
        <div class="clear"><br>
        </div>
    </div>
</div>

<!-- mailbox start -->
<div class="mail-env"> 
  <!-- Mail Body start -->
  <div class="mail-body"> 
    <!-- mail table -->
    <div class="inbox">
      <table id="table-4" class="table datatable mail-table">
        <!-- mail table header -->
        <thead>
          <tr>
            <th width="1%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
            <th colspan="2"> <?php if(count($result)>0){ ?>
              <div class="mail-select-options" style=""> <span class="pull-left paginationTicket"> {{Form::select('page',$pagination,$per_page,array("class"=>"select2 small","id"=>"per_page"))}} </span><span class="pull-right per_page">records per page</span> </div>
              <div class="pull-right">
                <div class="hidden mail-pagination"> <strong>
                  <?php   $current = ($data['currentpage']*$iDisplayLength); echo $current+1; ?>
                  -
                  <?php  echo $current+count($result); ?>
                  </strong> <span>of {{$totalResults}}</span>
                  <div class="btn-group">
                    <?php if(count($result)>=$iDisplayLength){ ?>
                    <a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a>
                    <?php } ?>
                  </div>
                </div>
                <div class="pull-left btn-group">
                <button type="button" data-toggle="dropdown" class="btn  dropdown-toggle  btn-green">Export <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown_sort dropdown-green" role="menu">    
                    <li><a class="export_btn export_type" action_type="csv" href="#"> CSV</a> </li>
                    <li><a class="export_btn export_type" action_type="xlsx"  href="#">  EXCEL</a> </li>
                  </ul>
                </div>
                <div class="pull-right sorted btn-group">

                  <button type="button" class="btn btn-green dropdown-toggle" data-toggle="dropdown"> Sorted by {{$Sortcolumns[$data['iSortCol_0']]}} <span class="caret"></span> </button>
                  <ul class="dropdown-menu dropdown_sort dropdown-green" role="menu">
                    <?php foreach($Sortcolumns as $key => $SortcolumnsData){ ?>
                    <li><a class="sort_fld @if($key==$data['iSortCol_0']) checked @endif" action_type="sort_field" action_value="{{$key}}"   href="#"> <i class="entypo-check" @if($key!=$data['iSortCol_0']) style="visibility:hidden;" @endif ></i> {{@$SortcolumnsData}}</a></li>
                    <?php } ?>
                    <li class="divider"></li>
                    <li><a class="sort_type @if($data['sSortDir_0']=='asc') checked @endif" action_type="sort_type" action_value="asc" href="#"> <i class="entypo-check" @if($data['sSortDir_0']!='asc') style="visibility:hidden;" @endif  ></i> Ascending</a> </li>
                    <li><a class="sort_type @if($data['sSortDir_0']=='desc') checked @endif" action_type="sort_type" action_value="desc" href="#"> <i class="entypo-check" @if($data['sSortDir_0']!='desc') style="visibility:hidden;" @endif  ></i> Descending</a> </li>
                  </ul>
                </div>
              </div>
              <?php } ?>
            </th>
          </tr>
        </thead>
        <!-- email list -->
        <tbody>
          <?php
		  if(count($result)>0){
		 foreach($result as $result_data){ 
			 ?>
          <tr><!-- new email class: unread -->
              <td><div class="checkbox "><input type="checkbox" name="checkbox[]" value="{{$result_data->TicketID}}" class="rowcheckbox" ></div></td>
            <td class="col-name @if(!empty($result_data->PriorityValue)) borderside borderside{{$result_data->PriorityValue}} @endif"><a target="_blank" href="{{URL::to('/')}}/tickets/{{$result_data->TicketID}}/detail" class="col-name"> <span class="blue_link"> <?php echo ShortName($result_data->Subject,100); ?></span> </a> <span class="ticket_number"> #<?php echo $result_data->TicketID; ?></span>
              <?php if($result_data->Read==0){echo '<div class="label label-primary">New</div>';}else{if($result_data->CustomerResponse==$result_data->RequesterEmail){echo "<div class='label label-info'>CUSTOMER RESPONDED</div>";}else{ if($ClosedTicketStatus!=$result_data->TicketStatus && $ResolvedTicketStatus!=$result_data->TicketStatus) { echo '<div class="label label-warning">RESPONSE DUE</div>';} }} //if(empty($result_data->Agent)){echo '<div class="label label-danger">unassigned</div>';} ?>
              <br>
              <a target="_blank" href="@if(!empty($result_data->ACCOUNTID)) {{URL::to('/')}}/accounts/{{$result_data->ACCOUNTID}}/show @elseif(!empty($result_data->ContactID)) contacts/{{$result_data->ContactID}}/show @else # @endif" class="col-name">Requester: <?php echo $result_data->Requester; ?></a><br>
              <span> Created: <?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($result_data->created_at))->diffForHumans();  ?></span></td>
            <td  align="left" class="col-time"><div>Status:<span>&nbsp;&nbsp;<?php echo $result_data->TicketStatus; ?></span></div>
              <div>Priority:<span>&nbsp;&nbsp;<?php echo $result_data->PriorityValue; ?></span></div>
              <div>Agent:<span>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $result_data->Agent; ?></span></div>
              <div>Group:<span>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $result_data->GroupName; ?></span></div></td>
          </tr>
          <?php } }else{ ?>
          <tr>
            <td align="center" colspan="2">No Result Found.</td>
          </tr>
          <?php } ?>
        </tbody>
        <!-- mail table footer -->
        <tfoot>
          <tr>
            <th colspan="2"> <?php if(count($result)>0){ ?>
              <div class="mail-pagination" ><?php echo $current+1; ?> to
                <?php  echo $current+count($result); ?>
                <span>of {{$totalResults}}</span> entries
                <div class="btn-group">
                  <?php if(count($result)>=$iDisplayLength){ ?>
                  <a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a>
                  <?php } ?>
                </div>
              </div>
              <?php } ?>
            </th>
          </tr>
        </tfoot>
      </table>
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
@if(count($result)>0)	 
#table-4{display: block; padding-bottom:50px;}
@endif
.borderside{border-left-style: solid; border-left-width: 8px;}
.bordersideLow{border-left-color:#00A651;}
.bordersideMedium{border-left-color:#008ff9;}
.bordersideHigh{border-left-color:#ffb613;}
.bordersideUrgent{border-left-color:#CC2424;}
.responsedue{color:#CC2424;}
.customerresponded{color:#008ff9;}
.per_page{margin-left:10px; margin-top:5px; }
.paginationTicket{width:85px;}
</style>
<script type="text/javascript">
	
$(document).ready(function(e) {	
	
	var currentpage 	= 	0;
	var next_enable 	= 	1;
	var back_enable 	= 	1;
	var per_page 		= 	<?php echo $iDisplayLength; ?>;
	var total			=	<?php echo $totalResults; ?>;
	var clicktype		=	'';
	var ajax_url 		= 	baseurl+'/tickets/ajex_result';
	var ajax_url_export	= 	baseurl+'/tickets/ajex_result_export';
	var SearchStr		=	'';
	var sort_fld  		=   "{{$data['iSortCol_0']}}";
	var sort_type 		=   "{{$data['sSortDir_0']}}";
	var export_data		=   0;
	
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
		

		 $.ajax({
					url: ajax_url,
					type: 'POST',
					dataType: 'html',
					async :false,
					data:{formData:$search,currentpage:currentpage,per_page:per_page,total:total,clicktype:clicktype,sort_fld:sort_fld,sort_type:sort_type},
					success: function(response) {
						if(response.length>0)
						{
							if(isJson(response))
							{
								jsonstr =  JSON.parse(response);
								$('#table-4 tbody').html('<tr><td align="center" colspan="5">'+jsonstr.result+'</td></tr>');
								
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
		var export_type		=	$(this).attr('action_type');
		
		ajax_url_export = ajax_url_export+"?Search="+$search.Search+"&status="+$search.status+"&priority="+$search.priority+"&group="+$search.group+"&agent="+$search.agent+"&sort_fld="+sort_fld+"&sort_type="+sort_type+"&export_type="+export_type+"&Export=1";
		window.location = ajax_url_export;
		 /*$.ajax({
					url: ajax_url_export,
					type: 'POST',
					dataType: 'html',
					async :false,
					data:{formData:$search,currentpage:currentpage,per_page:per_page,total:total,clicktype:clicktype,sort_fld:sort_fld,sort_type:sort_type,Export:1},
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

    $(document).on('click','#bulk_delete,#bulk_change_status,#bulk_assign',function(e){
        e.preventDefault();
        var self = $(this);
        var url = baseurl + '/tickets/bulkaction';
        var modal = $('#modal-BulkAction');
        modal.find('.save').button('reset');
        $('#BulkAction-form').trigger("reset");
        if(self.prop('id')=='bulk_delete') {
            modal.find('[name="ActionType"]').val('delete');
        }else if(self.prop('id')=='bulk_change_status'){
            modal.find('[name="ActionType"]').val('status');
            $('#status').removeClass('hidden');
            $('#agent').addClass('hidden');
            modal.find('.modal-title').text('Change Bulk Status');
            modal.modal('show');
        }else if(self.prop('id')=='bulk_assign'){
            modal.find('[name="ActionType"]').val('agent');
            $('#agent').removeClass('hidden');
            $('#status').addClass('hidden');
            modal.find('.modal-title').text('Bulk Agent Assign');
            modal.modal('show');
        }
    });

    $("#BulkAction-form").submit(function(e){
        e.preventDefault();
        var SelectedIDs = getselectedIDs();
        if (SelectedIDs.length == 0) {
            $('#modal-BulkAction').modal('hide');
            toastr.error('Please select at least one Ticket.', "Error", toastr_opts);
            return true;
        }else{
            var message = $(this).find('[name="ActionType"]').val()=='status'?'change status of':'assign';
            if(confirm('Do you want to '+message+' selected Tickets')){
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
            }else{
                $('#modal-BulkAction').modal('hide');
            }
        }
    });

    function getselectedIDs(){
        var SelectedIDs = [];
        $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
            leadID = $(this).val();
            SelectedIDs[i++] = leadID;
        });
        return SelectedIDs;
    }

});
</script> 
@stop

@section('footer_ext')
    @parent
    <div class="modal fade" id="modal-BulkAction">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="BulkAction-form" method="post" action="">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Bulk Account tags</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div id="status" class="form-Group hidden">
                                <label class="col-sm-2 control-label">Status</label>
                                <div class="col-sm-8">
                                    {{Form::select('verification_status',TicketsTable::getTicketStatus(),'',array("class"=>"select2 small"))}}
                                    <input type="hidden" name="SelectedIDs" />
                                </div>
                            </div>
                            <div id="agent" class="form-Group hidden">
                                <label class="col-sm-2 control-label">Assign</label>
                                <div class="col-sm-8">
                                    {{Form::select('UserID',User::getUserIDList(),'',array("class"=>"select2 small"))}}
                                    <input type="hidden" name="SelectedIDs" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="ActionType" />
                    <div class="modal-footer">
                        <button type="submit" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
