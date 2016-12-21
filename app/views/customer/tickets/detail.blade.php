@extends('layout.customer.main')
@section('content')
<ol class="breadcrumb bc-3">
  <li><a href="{{ URL::to('/customer/tickets') }}">Tickets</a></li>
  <li class="active"> <strong>Detail</strong> </li>
</ol>
<h3>Tickets</h3>
@include('includes.errors')
@include('includes.success')
<p class="text-right"> <a action_type="reply" data-toggle="tooltip" data-type="parent" data-placement="top"  ticket_number="{{$ticketdata->TicketID}}" data-original-title="Reply" class="btn btn-primary email_action tooltip-primary"><i class="entypo-reply"></i> </a> <a action_type="forward"  data-toggle="tooltip" data-type="parent" data-placement="top"  ticket_number="{{$ticketdata->TicketID}}" data-original-title="Forward" class="btn btn-primary email_action tooltip-primary"><i class="entypo-forward"></i> </a> <a data-toggle="tooltip"  data-placement="top" data-original-title="Edit" href="{{URL::to('/customer/tickets/'.$ticketdata->TicketID.'/edit/')}}" class="btn btn-primary tooltip-primary"><i class="entypo-pencil"></i> </a> <a data-toggle="tooltip"  data-placement="top" data-original-title="Close Ticket" ticket_number="{{$ticketdata->TicketID}}"  class="btn btn-red close_ticket tooltip-primary"><i class="glyphicon glyphicon-ban-circle"></i> </a> <a data-toggle="tooltip"  data-placement="top" data-original-title="Delete Ticket" ticket_number="{{$ticketdata->TicketID}}"   class="btn btn-red delete_ticket tooltip-primary"><i class="fa fa-close"></i> </a> @if($PrevTicket) <a data-toggle="tooltip"  data-placement="top" data-original-title="Previous Ticket" href="{{URL::to('/customer/tickets/'.$PrevTicket->TicketID.'/detail/')}}" class="btn btn-primary tooltip-primary"><i class="fa fa-step-backward"></i> </a> @endif
  @if($NextTicket) <a data-toggle="tooltip"  data-placement="top" data-original-title="Next Ticket" href="{{URL::to('/customer/tickets/'.$NextTicket->TicketID.'/detail/')}}" class="btn btn-primary tooltip-primary"><i class="fa fa-step-forward"></i> </a> @endif </p>
<div class="mail-env"> 
  
  <!-- compose new email button -->
  <div class="mail-sidebar-row visible-xs"> <a href="mailbox-compose.html" class="btn btn-success btn-icon btn-block"> Compose Mail <i class="entypo-pencil"></i> </a> </div>
  
  <!-- Mail Body -->
  <div class="mail-body">
    <div class="mail-header"> 
      <!-- title -->
      <div class="mail-title">{{$ticketdata->Subject}}</div>
      <!-- links --> 
    </div>
    <div class="mail-info">
      <div class="mail-sender dropdown"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <span>Requester</span> ({{$ticketdata->Requester}}) </a> </div>
      <div class="mail-date"> {{\Carbon\Carbon::createFromTimeStamp(strtotime($ticketdata->created_at))->diffForHumans()}}</div>
    </div>
    <div class="mail-text"> {{$ticketdata->Description}} </div>
    @if(strlen($ticketdata->AttachmentPaths)>0)
    <?php $attachments = unserialize($ticketdata->AttachmentPaths); ?>
    <div class="mail-attachments">
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
		$Attachmenturl = URL::to('/customer/tickets/'.$ticketdata->TicketID.'/getattachment/'.$key_acttachment);		
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
    <div class="mail-info">
      <div class="mail-sender dropdown"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <span>To</span> ({{$TicketConversationData->TicketTo}}) </a> </div>
      <div class="mail-date"> <a action_type="forward"  data-toggle="tooltip" data-type="child" data-placement="top"  ticket_number="{{$TicketConversationData->TicketConversationID}}" data-original-title="Forward" class="btn btn-info email_action tooltip-primary"><i class="entypo-forward"></i> </a> {{\Carbon\Carbon::createFromTimeStamp(strtotime($TicketConversationData->created_at))->diffForHumans()}} </div>
    </div>
    <div class="mail-text"> {{$TicketConversationData->TicketMessage}} </div>
    @if(strlen($TicketConversationData->AttachmentPaths)>0)
    <?php $attachments = unserialize($TicketConversationData->AttachmentPaths); ?>
    <div class="mail-attachments">
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
		$Attachmenturl = URL::to('/customer/ticketsconversation/'.$TicketConversationData->TicketConversationID.'/getattachment/'.$key_acttachment);		
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
          <div class="panel panel-primary margin-top" data-collapsed="0"> 
            
            <!-- panel head -->
            <div class="panel-heading">
              <div class="panel-title"><strong>Requester Info</strong></div>
              <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
            </div>
            
            <!-- panel body -->
            <div class="panel-body">
              <p> <a class="blue_link" href="#">{{$ticketdata->RequesterName}}</a> <a href="#">({{$ticketdata->Requester}})</a>. </p>
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
            <div class="panel-body">
              <form role="form" id="tickets_filter" method="post" style="padding-right:25px;"  class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group" style="margin-top: 30px;">
                  <label for="field-1" class="col-md-4 control-label">Status</label>
                  <div class="col-md-8"> {{Form::select('status',$status,$ticketdata->Status,array("class"=>"select2","id"=>"TicketStatus"))}} </div>
                </div>
                <div class="form-group">
                  <label for="field-1" class="col-md-4 control-label">Priority</label>
                  <div class="col-md-8"> {{Form::select('priority', $Priority, $ticketdata->Priority ,array("class"=>"select2"))}} </div>
                </div>
                <div class="form-group">
                  <label for="field-1" class="col-md-4 control-label">Group</label>
                  <div class="col-md-8"> {{Form::select('group', $Groups, $ticketdata->Group ,array("class"=>"select2 ticketgroup"))}} </div>
                </div>
                <div class="form-group">
                  <label for="field-1" class="col-md-4 control-label">Agent</label>
                  <div class="col-md-8"> {{Form::select('agent', $Agents, $ticketdata->Agent ,array("class"=>"select2","id"=>"ticketagent"))}} </div>
                </div>
                <div class="form-group margin-bottom">
                  <div class="col-md-4">&nbsp;</div>
                  <div class="col-md-8">
                    <button type="submit" class="btn save btn-primary btn-icon btn-sm icon-left hidden-print" id="update_ticket" data-loading-text="Loading..."> Update <i class="entypo-mail"></i> </button>
                  </div>
                </div>
              </form>
            </div>
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
.mail-env .mail-body{float:left; width:70% !important; margin-right:1%; border-right:1px solid #ccc; background:#fff none repeat scroll 0 0;}
.mail-env .mail-sidebar{width:29%; background:#fff none repeat scroll 0 0;}
.mail-env .mail-body .mail-info{background:#fff none repeat scroll 0 0;}
.mail-reply-seperator{background:#f3f4f4 none repeat scroll 0 0; width:100%; height:10px;}
.mail-env{background:none !important;}
.mail-menu .row{margin-right:0px !important; margin-left:0px !important;}
.blue_link{color:#0066cc; font-size:16px;}
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
			var email_url 	= 	"<?php echo URL::to('/customer/tickets/'.$ticketdata->TicketID.'/actionsubmit/');?>";
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
	 
	  $("#tickets_filter").submit(function (event) {
		  $('#update_ticket').button('loading');
			var email_url 	= 	"<?php echo URL::to('/customer/tickets/'.$ticketdata->TicketID.'/updateticketattributes/');?>";
          	event.stopImmediatePropagation();
            event.preventDefault();			
			var formData = new FormData($('#tickets_filter')[0]);
			
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
					$("#update_ticket").button('reset');
					if (response.status == 'success') {
						toastr.success(response.message, "Success", toastr_opts);
					} else {
						toastr.error(response.message, "Error", toastr_opts);
					}        
				},
			});	
		 
	 });
	 
	 	  $(document).on('change','.ticketgroup',function(e){
		   var changeGroupID =  	$(this).val();
		   if(changeGroupID)
		   {
		   	 changeGroupID = parseInt(changeGroupID);
			 var ajax_url  = baseurl+'/customer/ticketgroups/'+changeGroupID+'/getgroupagents';
			 $.ajax({
					url: ajax_url,
					type: 'POST',
					dataType: 'json',
					async :false,
					cache: false,
					contentType: false,
					processData: false,
					data:{s:1},
					success: function(response) {
					   if(response.status =='success')
					   {			
						   var $el = this;		   
						   console.log(response.data);
						   $('#ticketagent option:gt(0)').remove();
						   $.each(response.data, function(key,value) {							  
							  $('#ticketagent').append($("<option></option>").attr("value", value).text(key));
							});					
							$('#ticketagent').val('');
							$('#s2id_ticketagent .select2-chosen').html('Select');
						}else{
							toastr.error(response.message, "Error", toastr_opts);
						}                   
					}
					});	
		return false;		
		   }
		   
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
			
			
});
setTimeout(setagentval(),6000);
	function setagentval(){
		$('.ticketgroup').trigger('change');
		console.log(agent);
		$('#ticketagent').val(agent);
		
	}
</script> 
@stop 