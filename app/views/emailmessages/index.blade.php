@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Emails</strong> </li>
</ol>
<h3>Emails</h3>
@include('includes.errors')
@include('includes.success')
<div class="mail-env"> 
  
  <!-- compose new email button -->
  <div class="mail-sidebar-row visible-xs"> <a href="#" class="btn btn-success btn-icon btn-block"> Compose Mail <i class="entypo-pencil"></i> </a> </div>
  
  <!-- Sidebar --> 
  @include("emailmessages.mail_sidebar") 
  
  <!-- Mail Body -->
  <div class="mail-body">
    <div class="mail-header"> 
      <!-- title -->
      <h3 class="mail-title"> Inbox <span class="count">({{$TotalUnreads}})</span><br>
      </h3>
      
      <!-- search -->
      <form method="get" role="form" id="mail-search" class="mail-search">
        <div class="input-group">
          <input type="text" class="form-control"  name="MailSearchStr" id="MailSearchStr" placeholder="Search for mail..." />
          <div class="input-group-addon clickable"> <i class="entypo-search"></i> </div>
        </div>
      </form>
    </div>
    
    <!-- mail table -->
    <div class="inbox">
      <table id="table-4" class="table mail-table">
        <!-- mail table header -->
        <thead>
          <tr>
            <th width="5%"> <div class="checkbox checkbox-replace">
                <input class="mail_select_checkbox" type="checkbox" />
              </div>
            </th>
            <th colspan="4"> <div class="hidden mail-select-options">Mark as Read</div>           
               <?php if(count($result)>0){ ?>
              <div class="mail-pagination"> <a class="btn-apply mailaction btn btn-default">Apply</a>
              <strong>
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
              <div class="mail-pagination margin-left-mail">
                  <select id="selectmailaction" name="selectmailaction" action_type="HasRead" class="select2 selectmailaction">
                  <option value="">Select</option>
                  <option value="1">Mark as Read</option>
                  <option value="0">Mark as Unread</option>
                  </select> 
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
			$attachments  =  !empty($result_data->AttachmentPaths)?unserialize($result_data->AttachmentPaths):array();
			 ?>
          <tr class="<?php if($result_data->HasRead==0){echo "unread";} ?>"><!-- new email class: unread -->
            <td><div class="checkbox checkbox-replace">
                <input value="<?php  echo $result_data->AccountEmailLogID; ?>" class="mailcheckboxes" type="checkbox" />
              </div></td>
            <td class="col-name"><a target="_blank" href="{{URL::to('/')}}/emailmessages/{{$result_data->AccountEmailLogID}}/detail" class="col-name"><?php echo ShortName($result_data->EmailfromName,20); ?></a></td>
            <td class="col-subject"><a target="_blank" href="{{URL::to('/')}}/emailmessages/{{$result_data->AccountEmailLogID}}/detail">@if($result_data->AccountID==0)<span class="label label-info">Not matched</span> @endif <?php echo ShortName($result_data->Subject,40); ?> </a></td>
            <td class="col-options"><?php if(count($attachments)>0 && is_array($attachments)){ ?>
              <a target="_blank" href="{{URL::to('/')}}/emailmessages/{{$result_data->AccountEmailLogID}}/detail"><i class="entypo-attach"></i></a>
              <?php } ?></td>
            <td class="col-time"><?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($result_data->created_at))->diffForHumans();  ?></td>
          </tr>
          <?php } }else{ ?>
          <tr>
            <td align="center" colspan="5">No Result Found.</td>
          </tr>
          <?php } ?>
        </tbody>
        <!-- mail table footer -->
        <tfoot>
          <tr>
            <th width="5%"> <div class="hidden checkbox checkbox-replace">
                <input class="mail_select_checkbox" type="checkbox" />
              </div>
            </th>
            <th colspan="4"> <?php if(count($result)>0){ ?>
              <div class="mail-pagination" colspan="2"> <?php echo $current+1; ?>-
                <?php  echo $current+count($result); ?>
                <span>of {{$totalResults}}</span>
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
</div>
<style>
.margin-left-mail{margin-right:15px;width:21%; }.btn-apply{margin-right:10px;}
</style>
<script>
$(document).ready(function(e) {
	var currentpage 	= 	0;
	var next_enable 	= 	1;
	var back_enable 	= 	1;
	var per_page 		= 	<?php echo $iDisplayLength; ?>;
	var total			=	<?php echo $totalResults; ?>;
	var clicktype		=	'';
	var ajax_url 		= 	baseurl+'/emailmessages/ajex_result';
	var boxtype			=	'inbox';
	var EmailCall		=	"{{Messages::Received}}";
	var SearchStr		=	'';
	$(document).on('click','.move_mail',function(){
		var clicktype = $(this).attr('movetype');	
        ShowResult(clicktype);
    });
	
	$(document).on('click','.input-group-addon',function(e){
		$('#mail-search').submit();		
    });
	
	$(document).on('submit','#mail-search',function(e){		 
		e.stopImmediatePropagation();
		e.preventDefault();
		SearchStr   = $('#MailSearchStr').val();
		currentpage = -1;
		clicktype   = 'next';
		ShowResult(clicktype);
		return false;		
    });	
	
	function ShowResult(clicktype){
	SearchStr   = $('#MailSearchStr').val();
	
		 $.ajax({
					url: ajax_url,
					type: 'POST',
					dataType: 'html',
					async :false,
					data:{currentpage:currentpage,per_page:per_page,total:total,clicktype:clicktype,boxtype:boxtype,SearchStr:SearchStr,EmailCall:EmailCall},
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
							 } 		 console.log(currentpage);			 	
							 replaceCheckboxes();
							 $(".select2").select2();
							 $(".select2-container").css('visibility','visible');
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
	
	$(document).on('click','.mailaction',function(e){
        e.preventDefault();
		var selectmailaction = 	$('#selectmailaction').val(); 
		 var allVals = [];
   	  $('.mailcheckboxes:checked').each(function() {		
       allVals.push($(this).val());
     });
	 
	 if(allVals.length<1){return false;}
    if(selectmailaction==''){return false;}
	
	var action_type   =  $('#selectmailaction').attr('action_type');
	var action_value  =  selectmailaction;
	var ajax_url	  =  baseurl+'/emailmessages/ajax_action';
	
	if(action_type!='' && action_value!='' ){
			$.ajax({
				url: ajax_url,
				type: 'POST',
				dataType: 'json',
				async :false,
				data:{allVals:allVals,action_type:action_type,action_value:action_value},
				success: function(response) {
					 if(response.status =='success'){
						ShowToastr("success",response.message); 		 
						currentpage = currentpage-1;
						ShowResult('next');
					 }
					 else{
						toastr.error(response.message, "Error", toastr_opts);
					}  
				}
			});	
	}
	 
    });
	
	/*$("#checkall").click(function(){
    $('.mailcheckbox').prop('checked', $(this).prop("checked"));
	if($(this).prop("checked")==true){
		$('.checkbox-replace').addClass('checked');
	}
	else
	{
		$('.checkbox-replace').removeClass('checked');
	}
	//$('.checkbox-replace').toggleClass('checked');
});*/

	$(document).on('click','.mail_select_checkbox',function(e){
		var $cb = $(document).find('table thead input[type="checkbox"], table tfoot input[type="checkbox"]');
		$cb.attr('checked', this.checked).trigger('change');
		mail_toggle_checkbox_status(this.checked);
		$('#table-4 tbody').find('tr')[this.checked ? 'addClass' : 'removeClass']('highlight');
	});		
	$(document).on('change','.mailcheckboxes', function()
	{
		$(this).closest('tr')[this.checked ? 'addClass' : 'removeClass']('highlight');
	});
	
});
</script> 
<script src="<?php echo URL::to('/'); ?>/assets/js/neon-mail.js"></script> 
@stop 