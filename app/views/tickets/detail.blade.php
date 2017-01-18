@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{ URL::to('/dashboard') }}"><i class="entypo-home"></i>Home</a> </li>
  <li><a href="{{ URL::to('/tickets') }}">Tickets</a></li>
  <li class="active"> <strong>Detail</strong> </li>
</ol>
@include('includes.errors')
@include('includes.success')

<div class="pull-left"> <a action_type="reply" data-toggle="tooltip" data-type="parent" data-placement="top"  ticket_number="{{$ticketdata->TicketID}}" data-original-title="Reply" class="btn btn-primary email_action tooltip-primary btn-sm"><i class="entypo-reply"></i> </a> <a action_type="forward"  data-toggle="tooltip" data-type="parent" data-placement="top"  ticket_number="{{$ticketdata->TicketID}}" data-original-title="Forward" class="btn btn-primary email_action tooltip-primary btn-sm"><i class="entypo-forward"></i> </a> <a data-toggle="tooltip"  data-placement="top" data-original-title="Edit" href="{{URL::to('tickets/'.$ticketdata->TicketID.'/edit/')}}" class="btn btn-primary tooltip-primary btn-sm"><i class="entypo-pencil"></i> </a> <a data-toggle="tooltip"  data-placement="top" data-original-title="Close Ticket" ticket_number="{{$ticketdata->TicketID}}"  class="btn btn-red close_ticket tooltip-primary btn-sm"><i class="glyphicon glyphicon-ban-circle"></i> </a> <a data-toggle="tooltip"  data-placement="top" data-original-title="Delete Ticket" ticket_number="{{$ticketdata->TicketID}}"   class="btn btn-red delete_ticket tooltip-primary btn-sm"><i class="fa fa-trash-o"></i> </a>  </div>
  <div class="pull-right">@if($PrevTicket) <a data-toggle="tooltip"  data-placement="top" data-original-title="Previous Ticket" href="{{URL::to('tickets/'.$PrevTicket.'/detail/')}}" class="btn btn-primary tooltip-primary btn-sm"><i class="fa fa-step-backward"></i> </a> @endif
  @if($NextTicket) <a data-toggle="tooltip"  data-placement="top" data-original-title="Next Ticket" href="{{URL::to('tickets/'.$NextTicket.'/detail/')}}" class="btn btn-primary tooltip-primary btn-sm"><i class="fa fa-step-forward"></i> </a> @endif</div>
 <div class="clear clearfix"></div>
<div class="mail-env margin-top"> 
  
  <!-- compose new email button -->
  <div class="mail-sidebar-row visible-xs"> <a href="mailbox-compose.html" class="btn btn-success btn-icon btn-block"> Compose Mail <i class="entypo-pencil"></i> </a> </div>
  
  <!-- Mail Body -->
  <div class="mail-body">
  
    <div class="mail-header"> 
      <!-- title -->
      <div class="mail-title">{{$ticketdata->Subject}} #{{$ticketdata->TicketID}}</div>
      <!-- links --> 
    </div>
    <div class="mail-info">
      <div class="mail-sender dropdown"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> 
      @if(!empty($ticketdata->RequesterName))<span>{{$ticketdata->RequesterName}}</span>@endif @if(empty($ticketdata->RequesterName)) {{$ticketdata->Requester}} @endif  </a> </div>
      <div class="mail-date"> {{\Carbon\Carbon::createFromTimeStamp(strtotime($ticketdata->created_at))->diffForHumans()}}</div>
    </div>
     <div class="mail-text @if(strlen($ticketdata->AttachmentPaths)<1) last_data  @endif "> {{$ticketdata->Description}} </div>
    @if(strlen($ticketdata->AttachmentPaths)>0)
    <?php $attachments = unserialize($ticketdata->AttachmentPaths); ?>
    <div class="mail-attachments last_data">
      <h4> <i class="entypo-attach"></i> Attachments <span>({{count($attachments)}})</span> </h4>
      <ul>
        @foreach($attachments as $key_acttachment => $attachments_data)
        <?php 
   		//$FilePath 		= 	AmazonS3::preSignedUrl($attachments_data['filepath']);
		$Filename		=	$attachments_data['filepath'];
		
		if(is_amazon() == true)
		{
			$Attachmenturl =  AmazonS3::preSignedUrl($attachments_data['filepath']);
		}
		else
		{
			$Attachmenturl = Config::get('app.upload_path')."/".$attachments_data['filepath'];
		}
		$Attachmenturl = URL::to('tickets/'.$ticketdata->TicketID.'/getattachment/'.$key_acttachment);		
   	    ?>
        <li> <a target="_blank" href="{{$Attachmenturl}}" class="thumb download"> <img width="75"   src="{{getimageicons($Filename)}}" class="img-rounded" /> </a> <a target="_blank" href="{{$Attachmenturl}}" class="shortnamewrap name"> {{$attachments_data['filename']}} </a>
          <div class="links"><a href="{{$Attachmenturl}}">Download</a> </div>
        </li>
        @endforeach
      </ul>
    </div>
    @endif
    <?php if(count($TicketConversation)>0){
		foreach($TicketConversation as $TicketConversationData){
		 ?>
    <div class="mail-reply-seperator"></div>
    <div class="mail-info first_data">
      <div class="mail-sender dropdown"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <span>@if($TicketConversationData->EmailCall==Messages::Received)From @elseif($TicketConversationData->EmailCall==Messages::Sent)To @endif</span> ({{$TicketConversationData->Requester}}) </a> </div>
      <div class="mail-date"> <a action_type="forward"  data-toggle="tooltip" data-type="child" data-placement="top"  ticket_number="{{$TicketConversationData->TicketConversationID}}" data-original-title="Forward" class="btn btn-info email_action tooltip-primary"><i class="entypo-forward"></i> </a> {{\Carbon\Carbon::createFromTimeStamp(strtotime($TicketConversationData->created_at))->diffForHumans()}} </div>
    </div>
    <div class="mail-text @if(strlen($TicketConversationData->AttachmentPaths)<1) last_data  @endif "> {{$TicketConversationData->TicketMessage}} </div>
    @if(strlen($TicketConversationData->AttachmentPaths)>0)
    <?php $attachments = unserialize($TicketConversationData->AttachmentPaths); ?>
    <div class="mail-attachments last_data">
      <h4> <i class="entypo-attach"></i> Attachments <span>({{count($attachments)}})</span> </h4>
      <ul>
        @foreach($attachments as $key_acttachment => $attachments_data)
        <?php 
   		//$FilePath 		= 	AmazonS3::preSignedUrl($attachments_data['filepath']);
		$Filename		=	$attachments_data['filepath'];
		
		if(is_amazon() == true)
		{
			$Attachmenturl =  AmazonS3::preSignedUrl($attachments_data['filepath']);
		}
		else
		{
			$Attachmenturl = Config::get('app.upload_path')."/".$attachments_data['filepath'];
		}
		$Attachmenturl = URL::to('ticketsconversation/'.$TicketConversationData->TicketConversationID.'/getattachment/'.$key_acttachment);		
   	    ?>
        <li> <a target="_blank" href="{{$Attachmenturl}}" class="thumb download"> <img width="75"   src="{{getimageicons($Filename)}}" class="img-rounded" /> </a> <a target="_blank" href="{{$Attachmenturl}}" class="shortnamewrap name"> {{$attachments_data['filename']}} </a>
          <div class="links"><a href="{{$Attachmenturl}}">Download</a> </div>
        </li>
        @endforeach
      </ul>
    </div>
    @endif
    <?php } } ?>
  </div>
  
  <!-- Sidebar -->
  <div class="mail-sidebar"> 
    <!-- menu -->
    <div class="mail-menu">
      <div class="row">
        <div class="col-md-12">
          <div class="panel panel-primary" data-collapsed="0"> 
            
            <!-- panel head -->
            <div class="panel-heading">
              <div class="panel-title"><strong>Requester Info</strong></div>
              <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
            </div>
            
            <!-- panel body -->
            <div class="panel-body">
            @if(!empty($ticketdata->RequesterName))
            <p><a class="blue_link" href="#">{{$ticketdata->RequesterName}}</a><br><a href="#">({{$ticketdata->Requester}})</a>. </p>
            @else
            <p><a href="#">{{$ticketdata->Requester}}</a></p>
            @endif
			@if($RequesterContact)           
<form role="form" id="form-tickets-owner_edit" method="post"  class="form-horizontal form-groups-bordered validate" novalidate>
           <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Contact Owner</label>

                        <div class="col-sm-9">
                            <?php
                                $selected_owner = $RequesterContact->Owner;
                            ?>
                             <select name="Owner" class="select2" data-allow-clear="true" data-placeholder="Account Owner...">
                                <option></option>
                                <optgroup label="Leads">
                                    @if( count($lead_owners))
                                    @foreach($lead_owners as $lead_owner)
                                    @if(!empty($lead_owner->AccountName) && $lead_owner->Status == 1)
                                    <option value="{{$lead_owner->AccountID}}" @if($selected_owner == $lead_owner->AccountID) {{"selected"}} @endif >
                                    {{$lead_owner->AccountName}}
                                    </option>
                                    @endif
                                    @endforeach
                                    @endif
                                </optgroup>
                                <optgroup label="Accounts">
                                    @if( count($account_owners))
                                    @foreach($account_owners as $account_owner)
                                    @if(!empty($account_owner->AccountName) && $account_owner->Status == 1)
                                    <option value="{{$account_owner->AccountID}}" @if($selected_owner == $account_owner->AccountID) {{"selected"}} @endif >
                                    {{$account_owner->AccountName}}
                                    </option>
                                    @endif
                                    @endforeach
                                    @endif
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
    <div class="col-md-5 pull-right">
      <button type="submit" class="btn save btn-primary btn-icon btn-sm icon-left" id="update_ticket_owner" data-loading-text="Loading..."> Update <i class="entypo-mail"></i> </button>
    </div>
  </div>
  </form>
            @endif
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="panel panel-primary margin-top" data-collapsed="0"> 
            
            <!-- panel head -->
            <div class="panel-heading">
              <div class="panel-title"><strong>Ticket Properties</strong></div>
              <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
            </div>
            
            <!-- panel body -->
            <div class="panel-body">@include('tickets.ticket_detail_dynamic_fields')</div>
          </div>
        </div>
      </div>
    </div>
    <!-- menu --> 
  </div>
</div>
<div class="modal fade " id="EmailAction-model">
  <form id="EmailActionform" method="post">
    <div class="modal-dialog EmailAction_box"  style="width: 70%;">
      <div class="modal-content"> </div>
    </div>
  </form>
</div>
<form id="emai_attachments_reply_form" class="hidden" name="emai_attachments_form">
  <span class="emai_attachments_span">
  <input type="file" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" name="emailattachment[]" multiple id="filecontrole2">
  </span>
  <input id="info3" type="hidden" name="attachmentsinfo" />
  <button  class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
</form>
<style>
/*.mail-env .mail-body{float:left; width:70% !important; margin-right:1%; border-right:1px solid #ccc; background:#fff none repeat scroll 0 0;}*/
.mail-env .mail-body{float:left; width:70% !important; margin-right:1%;  }
.mail-env .mail-sidebar{width:29%; background:#fff none repeat scroll 0 0;}
.mail-env .mail-body .mail-info{background:#fff none repeat scroll 0 0;}
.mail-reply-seperator{background:#f1f1f1 none repeat scroll 0 0; width:100%; height:20px;}
.mail-env{background:none !important;}
.mail-menu {background:#f1f1f1;}
.mail-menu .row{margin-right:0px !important; margin-left:0px !important;}
.mail-menu .panel{margin-bottom:5px;}
.blue_link{font-size:16px; font-weight:bold;}
.mail-header{padding:10px !important; padding-bottom:0px !important; border-bottom:none !important;}
.mail-env .mail-body .mail-info .mail-sender, .mail-env .mail-body .mail-info .mail-date{padding:10px;}
.mail-env .mail-body .mail-attachments{padding-top:10px; padding-left:10px; padding-right:0px; padding-bottom:0px; background:#fff none repeat scroll 0 0;}
.mail-env .mail-body .mail-attachments h4{margin-bottom:10px;}
#tickets_filter .form-groups-bordered .form-group{padding-bottom:10px !important;}
.form-groups-bordered .form-group{padding-bottom:6px;}
.mail-env .mail-body .mail-text{background:#fff none repeat scroll 0 0;}
.last_data{border-bottom-left-radius:10px; border-bottom-right-radius:10px;}
.mail-env .mail-body .mail-header,.first_data{background:#fff none repeat scroll 0 0; border-top-left-radius:10px; border-top-right-radius:10px;}
.mail-env .mail-body .mail-info .mail-sender{padding-top:2px;}
</style>
<link rel="stylesheet" href="{{ URL::asset('assets/js/wysihtml5/bootstrap-wysihtml5.css') }}">
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/wysihtml5-0.4.0pre.min.js"></script> 
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.js"></script> 
<script>
var agent 				= 		parseInt('{{$ticketdata->Agent}}');
var file_count 		  	=  		0;
var emailFileList     	=		[];
var allow_extensions  	= 		{{$response_extensions}};
var max_file_size_txt 	=	    '{{$max_file_size}}';
var max_file_size	  	=	    '{{str_replace("M","",$max_file_size)}}';
var emailFileListReply 	=		[];
var CloseStatus			=		'{{$CloseStatus}}';
$(document).ready(function(e) {	
	
	$( document ).on("click",'.email_action' ,function(e) {			
		var url 		    = 	  baseurl + '/tickets/ticket_action';
		var action_type     =     $(this).attr('action_type');
		var ticket_number   =     $(this).attr('ticket_number');
		var ticket_type		=	  $(this).attr('data-type');
		
		emailFileListReply = [];
	   $('#info3').val('');
	   $('#info4').val('');
	   $("#EmailActionform").find('#emailattachment_sent').val('');
	   $("#EmailActionform").find('.file_upload_span').remove();
		
		 $.ajax({
			url: url,
			type: 'POST',
			dataType: 'html',
			async :false,
			data:{s:1,action_type:action_type,ticket_number:ticket_number,ticket_type:ticket_type},
			success: function(response){
				$('#EmailAction-model .modal-content').html('');
				$('#EmailAction-model .modal-content').html(response);				
					var mod =  $(document).find('.EmailAction_box');
					$('#EmailAction-model').modal('show');
				 	//mod.find('.wysihtml5-sandbox, .wysihtml5-toolbar').remove();
        			//mod.find('.message').show();
				mod.find("select").select2({
                    minimumResultsForSearch: -1
                });
				mod.find('.select2-container').css('visibility','visible');
				setTimeout(function(){ 
				mod.find('.message').wysihtml5({
						"font-styles": true,
						"leadoptions":false,
						"Crm":false,
						"emphasis": true,
						"lists": true,
						"html": true,
						"link": true,
						"image": true,
						"color": false,
						parser: function(html) {
							return html;
						}
				});
				 }, 500);
				
		    
			},
		});
	});
	
		 $("#EmailActionform").submit(function (event) {
		//////////////////////////          	
			var email_url 	= 	"<?php echo URL::to('/tickets/'.$ticketdata->TicketID.'/actionsubmit/');?>";
          	event.stopImmediatePropagation();
            event.preventDefault();			
			var formData = new FormData($('#EmailActionform')[0]);
			
			$("#EmailAction-model").find('.btn-send-mail').addClass('disabled');
			$("#EmailAction-model").find('.btn-send-mail').button('loading');
			 $.ajax({
                url: email_url,
                type: 'POST',
                dataType: 'json',
				data:formData,
				async :false,
				cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
						$("#EmailAction-model").find('.btn-send-mail').button('reset');
						if(response.status =='success'){									
							toastr.success(response.message, "Success", toastr_opts);
							location.reload();
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
				},
			});	
		///////////////////////////////
		 
	 });
	 
	
	 
	    $(document).on("click","#addReplyTtachment",function(ee){
			 file_count++;                
				$('#filecontrole2').click();
			 });
			
		 $(document).on('change','#filecontrole2',function(e){
				e.stopImmediatePropagation();
  				e.preventDefault();		
                var files 			 		 =  e.target.files;				
                var fileText 		 		 =  new Array();
				var file_check				 =	1; 
				var local_reply_array		 =  new Array();
				///////
	        var filesArr = Array.prototype.slice.call(files);
		
			filesArr.forEach(function(f) {     
				var ext_current_file  = f.name.split('.').pop();
				if(allow_extensions.indexOf(ext_current_file.toLowerCase()) > -1 )			
				{         
					var name_file = f.name;
					var index_file = emailFileListReply.indexOf(f.name);
					if(index_file >-1 )
					{
						ShowToastr("error",f.name+" file already selected.");							
					}
					else if(bytesToSize(f.size))
					{						
						ShowToastr("error",f.name+" file size exceeds then upload limit ("+max_file_size_txt+"). Please select files again.");						
						file_check = 0;
						 return false;
						
					}else
					{
						//emailFileList.push(f.name);
						local_reply_array.push(f.name);
					}
				}
				else
				{
					ShowToastr("error",ext_current_file+" file type not allowed.");
					
				}
        });
        		if(local_reply_array.length>0 && file_check==1)
				{	 emailFileListReply = emailFileListReply.concat(local_reply_array);
   					$('#emai_attachments_reply_form').submit();
				}

            });
			
	$('#emai_attachments_reply_form').submit(function(e) {
		e.stopImmediatePropagation();
		e.preventDefault();
	
		var formData = new FormData(this);
		 var url = 	baseurl + '/tickets/upload_file';
		$.ajax({
			url: url,  //Server script to process data
			type: 'POST',
			dataType: 'json',
			success: function (response) {
				console.log(response);
				if(response.status =='success'){
					$("#EmailActionform").find('.file-input-names').html(response.data.text);             
					$('#info3').val(JSON.stringify(response.data.attachmentsinfo));
					$('#info4').val(JSON.stringify(response.data.attachmentsinfo));
	
				}else{
					toastr.error(response.message, "Error", toastr_opts);
				}
			},
			// Form data
			data: formData,
			//Options to tell jQuery not to process data or worry about content-type.
			cache: false,
			contentType: false,
			processData: false
    });
	
});	

			$(document).on("click",".del_attachment",function(ee){
                 var url  =  baseurl + '/tickets/delete_attachment_file';
                var fileName   =  $(this).attr('del_file_name');
                var attachmentsinfo = $('#info4').val();
                if(!attachmentsinfo){
                    return true;
                }
                attachmentsinfo = jQuery.parseJSON(attachmentsinfo);
                $(this).parent().remove();
                var fileIndex = emailFileListReply.indexOf(fileName);
                var fileinfo = attachmentsinfo[fileIndex]; 
                emailFileListReply.splice(fileIndex, 1);
                attachmentsinfo.splice(fileIndex, 1);
                $('#info3').val(JSON.stringify(attachmentsinfo));
                $('#info4').val(JSON.stringify(attachmentsinfo));
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data:{file:fileinfo},
                    async :false,
                    success: function(response) {
                        if(response.status =='success'){									
							toastr.success(response.message, "Success", toastr_opts);
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    }
                });
            });
			
			$('.close_ticket').click(function(e) {
                var ticket_number   =     parseInt($(this).attr('ticket_number'));
				if(ticket_number){
					var confirm_close = confirm("Are you sure to close this ticket?");
					if(confirm_close)
					{
						var url 		    = 	  baseurl + '/tickets/'+ticket_number+'/close_ticket';
						$.ajax({
							url: url,
							type: 'POST',
							dataType: 'json',
							async :false,
							data:{s:1,ticket_number:ticket_number},
							success: function(response){	
									if(response.status =='success'){									
									toastr.success(response.message, "Success", toastr_opts);
									$('#TicketStatus').val(response.close_id).trigger('change');
								}else{
									toastr.error(response.message, "Error", toastr_opts);
								}								
							},
						});
					
					}else{
						return false;
					}
				}
            });
			
			
			$('.delete_ticket').click(function(e) { 
				e.preventDefault();
                var ticket_number   =     parseInt($(this).attr('ticket_number'));
				if(ticket_number){
					var confirm_close = confirm("Are you sure to delete this ticket?");
					if(confirm_close)
					{
						var url 		    = 	  baseurl + '/tickets/'+ticket_number+'/delete';
						$.ajax({
							url: url,
							type: 'POST',
							dataType: 'json',
							async :false,
							data:{s:1,ticket_number:ticket_number},
							success: function(response){	
									if(response.status =='success'){									
									toastr.success(response.message, "Success", toastr_opts);
									window.location = baseurl+'/tickets';
								}else{
									toastr.error(response.message, "Error", toastr_opts);
								}								
							},
						});
					
					}else{
						return false;
					}
				}
            });
			
		@if($RequesterContact)	
	$('#form-tickets-owner_edit').submit(function(e) {
		e.preventDefault();
		e.stopImmediatePropagation();	  
		$("#form-tickets-owner_edit").find('#update_ticket_owner').addClass('disabled');
		$("#form-tickets-owner_edit").find('#update_ticket_owner').button('loading');					
		var formData = new FormData($(this)[0]);
		var ajax_url = baseurl+'/contacts/{{$RequesterContact->ContactID}}/updatecontactowner';
		 $.ajax({
				url: ajax_url,
				type: 'POST',
				dataType: 'json',
				async :false,
				cache: false,
				contentType: false,
				processData: false,
				data:formData,
				success: function(response) { 
				   if(response.status =='success'){					   
						ShowToastr("success",response.message); 		
					}else{
						toastr.error(response.message, "Error", toastr_opts);
					} 
					$("#form-tickets-owner_edit").find('.btn').button('reset');	
				}
				});	
	
				return false;
            });
			@endif
});
setTimeout(setagentval(),6000);
	function setagentval(){
		$('.ticketgroup').trigger('change');
		console.log(agent);
		$('#ticketagent').val(agent);
		
	}
</script> 
@stop 