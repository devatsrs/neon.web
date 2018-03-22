@extends('layout.customer.main')
@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{ URL::to('/dashboard') }}"><i class="entypo-home"></i>@lang('routes.CUST_PANEL_BREADCRUMB_HOME')</a> </li>
  <li class="active"> <strong>@lang('routes.CUST_PANEL_PAGE_TICKETS_TITLE')</strong> </li>
</ol>
<h3>@lang('routes.CUST_PANEL_PAGE_TICKETS_TITLE')</h3>
<p class="text-right"> 
<div class="btn-group pull-right">
<button href="#" class="btn  btn-primary btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')">@lang('routes.CUST_PANEL_PAGE_TICKETS_BUTTON_ADD_NEW')&nbsp;&nbsp;<span class="caret"></span></button>
 <ul class="dropdown-menu" style="background-color: #000; border-color: #000; margin-top:0px;" role="menu">
    <li><a href="{{URL::to('customer/tickets/add')}}">@lang('routes.CUST_PANEL_PAGE_TICKETS_ADD_TICKET')</a></li>
  </ul>
  </div>
   </p>
  <div class="clear clearfix"><br></div>
<div class="row">
  <div class="col-md-12">
    <form role="form" id="tickets_filter" method="post" action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate>
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title"> @lang('routes.CUST_PANEL_FILTER_TITLE') </div>
          <div class="panel-options"> <a class="filter_minimize_btn" href="#" data-rel="collapse"><i class=" entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body" id="paymentsearch">
          <div class="form-group">
            <label for="field-1" class="col-sm-1 control-label small_label">@lang('routes.CUST_PANEL_PAGE_TICKETS_FILTER_FIELD_SEARCH')</label>
            <div class="col-sm-2"> {{ Form::text('search', '', array("class"=>"form-control")) }} </div>
            <label for="field-1" class="col-sm-1 control-label small_label">@lang('routes.CUST_PANEL_PAGE_TICKETS_FILTER_FIELD_STATUS')</label>
            <div class="col-sm-2"> {{Form::select('status[]', $status, $OpenTicketStatus ,array("class"=>"select2","multiple"=>"multiple"))}} </div>
            <label for="field-1" class="col-sm-1 control-label small_label">@lang('routes.CUST_PANEL_PAGE_TICKETS_FILTER_FIELD_PRIORITY')</label>
            <div class="col-sm-2"> {{Form::select('priority[]', $Priority, '' ,array("class"=>"select2","multiple"=>"multiple"))}} </div>
          </div>
          @if(User::is_admin())
          <div class="form-group">
            <label for="field-1" class="col-sm-1 control-label small_label">Agent</label>
            <div class="col-sm-2"> {{Form::select('agent[]', $Agents, '' ,array("class"=>"select2","multiple"=>"multiple"))}} </div>
          </div>
          @else
          <input type="hidden" name="agent" value="{{user::get_userID()}}" >
          @endif
          <p class="pull-right">
            <button type="submit" class="btn btn-primary btn_form_submit btn-sm btn-icon icon-left"> <i class="entypo-search"></i> @lang('routes.BUTTON_SEARCH_CAPTION') </button>
          </p>
        </div>
      </div>
    </form>
  </div>
</div>
<!-- mailbox start -->
<div class="mail-env"> 
  <!-- Mail Body start -->
  <div class="mail-body"> 
    <!-- mail table -->
    <div class="inbox">
        <div id="table-4_processing" class="dataTables_processing">@lang('routes.DATATABLE_PROCESSING')</div>
    </div>
  </div>
  <!-- Mail Body end --> 
</div>
<!-- mailbox end -->
<style>
.sorted{margin-left:5px;}
.margin-left-mail{margin-right:15px;width:21%; }.mailaction{margin-right:10px;}.btn-blue{color:#fff !important;}
.mail-body{width:100% !important; float:none !important;}
.blue_link{font-size:13px; font-weight:bold;}
.ticket_number{font-size:16px;}
.col-time{text-align:left; font-size:12px;}
.col-time span{color:black;}
.dropdown_sort li  a{color:white !important;}
@if(count($result)>0)	 
#table-4{padding-bottom:50px;}
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
.full-width-error{text-align:center;}
</style>
<script type="text/javascript">
	
$(document).ready(function(e) {	
	
	var currentpage 	= 	-1;
	var next_enable 	= 	1;
	var back_enable 	= 	1;
	var per_page 		= 	{{$iDisplayLength}};
	var clicktype		=	'';
	var ajax_url 		= 	baseurl+'/customer/tickets/ajex_result';
	var ajax_url_export	= 	baseurl+'/customer/tickets/ajex_result_export';
	var SearchStr		=	'';
	var sort_fld  		=   "{{$data['iSortCol_0']}}";
	var sort_type 		=   "{{$data['sSortDir_0']}}";

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
		var export_type		=	$(this).attr('action_type');
		
		ajax_url_export = ajax_url_export+"?Search="+$search.Search+"&status="+$search.status+"&priority="+$search.priority+"&sort_fld="+sort_fld+"&sort_type="+sort_type+"&export_type="+export_type+"&Export=1";
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

});
</script> 
@stop 