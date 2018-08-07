@extends('layout.customer.main')
@section('content')
<ol class="breadcrumb bc-3">
  <li><a href="{{ URL::to('/customer/tickets') }}">@lang('routes.CUST_PANEL_PAGE_TICKETS_TITLE')</a></li>
  <li class="active"> <strong>@lang('routes.CUST_PANEL_PAGE_TICKETS_DETAIL_TITLE')</strong> </li>
</ol>
<div class="pull-left"> <a action_type="reply" data-toggle="tooltip" data-type="parent" data-placement="top"  ticket_number="{{$ticketdata->TicketID}}" data-original-title="@lang('routes.CUST_PANEL_PAGE_TICKETS_TOOLTIP_REPLY')" class="btn btn-primary email_action tooltip-primary btn-xs"><i class="entypo-reply"></i> </a>
<!-- <a action_type="forward"  data-toggle="tooltip" data-type="parent" data-placement="top"  ticket_number="{{$ticketdata->TicketID}}" data-original-title="Forward" class="btn btn-primary email_action tooltip-primary btn-xs"><i class="entypo-forward"></i> </a> -->
<?php if($show_edit==1){ ?>
<a data-toggle="tooltip"  data-placement="top" data-original-title="Edit" href="{{URL::to('/customer/tickets/'.$ticketdata->TicketID.'/edit/')}}" class="btn btn-primary tooltip-primary btn-xs"><i class="entypo-pencil"></i> </a> 
<a data-toggle="tooltip"  data-placement="top" data-original-title="Add Note"  class="btn btn-primary add_note tooltip-primary btn-xs"><i class="fa fa-sticky-note"></i> </a> 
 <a data-toggle="tooltip"  data-placement="top" data-original-title="Close Ticket" ticket_number="{{$ticketdata->TicketID}}"  class="btn btn-red close_ticket tooltip-primary btn-xs"><i class="glyphicon glyphicon-ban-circle"></i> </a> 
 <a data-toggle="tooltip"  data-placement="top" data-original-title="Delete Ticket" ticket_number="{{$ticketdata->TicketID}}" class="btn btn-red delete_ticket tooltip-primary btn-xs"><i class="entypo-trash"></i> </a>
<?php  } ?> 
 </div>
  <div class="pull-right">@if($PrevTicket) <a data-toggle="tooltip"  data-placement="top" data-original-title="@lang('routes.CUST_PANEL_PAGE_TICKETS_TOOLTIP_PREVIOUS_TICKET')" href="{{URL::to('/customer/tickets/'.$PrevTicket.'/detail/')}}" class="btn btn-primary tooltip-primary btn-xs"><i class="fa fa-step-backward"></i> </a> @endif
  @if($NextTicket) <a data-toggle="tooltip"  data-placement="top" data-original-title="@lang('routes.CUST_PANEL_PAGE_TICKETS_TOOLTIP_NEXT_TICKET')" href="{{URL::to('/customer/tickets/'.$NextTicket.'/detail/')}}" class="btn btn-primary tooltip-primary btn-xs"><i class="fa fa-step-forward"></i> </a> @endif</div>
 <div class="clear clearfix"></div>
<div class="mail-env margin-top"> 
  
  <!-- compose new email button -->  
  
  <!-- Mail Body -->
  <div class="mail-body">
  
    <div class="mail-header"> 
      <!-- title -->
      <div class="mail-title">{{emailHeaderDecode($ticketdata->Subject)}} #{{$ticketdata->TicketID}}</div>
      <div class="mail-date">
      @if(!empty($ticketemaildata->Cc))@lang('routes.MAIL_LBL_CC') {{$ticketemaildata->Cc}}<br>@endif @if(!empty($ticketemaildata->Bcc))@lang('routes.MAIL_LBL_BCC'){{$ticketemaildata->Bcc}}<br>@endif
       {{\Carbon\Carbon::createFromTimeStamp(strtotime($ticketdata->created_at))->diffForHumans()}}</div>
      <!-- links --> 
    </div>   
     <?php $attachments = unserialize($ticketdata->AttachmentPaths); ?> 
     <div class="mail-text @if(count($attachments)<1 || strlen($ticketdata->AttachmentPaths)<1) last_data  @endif ">
		 <div class="embed-responsive embed-responsive-4by3 ticketBody" style="display: none;">
		 	{{htmlentities($ticketdata->Description)}}
		 </div>
	 </div>
    @if(count($attachments)>0 && strlen($ticketdata->AttachmentPaths)>0)
    <div class="mail-attachments last_data">
      <h4> <i class="entypo-attach"></i> @lang('routes.MAIL_LBL_ATTACHMENTS') <span>({{count($attachments)}})</span> </h4>
      <ul>
      @if(is_array($attachments)) 
        @foreach($attachments as $key_acttachment => $attachments_data)
        <?php 
   		//$FilePath 		= 	AmazonS3::preSignedUrl($attachments_data['filepath']);
		$Filename		=	$attachments_data['filepath'];
		
		/*if(is_amazon() == true)
		{
			$Attachmenturl =  AmazonS3::preSignedUrl($attachments_data['filepath']);
		}
		else
		{
			$Attachmenturl = Config::get('app.upload_path')."/".$attachments_data['filepath'];
		}*/
		$Attachmenturl = URL::to('/customer/tickets/'.$ticketdata->TicketID.'/getattachment/'.$key_acttachment);		
   	    ?>
        <li> <a target="_blank" href="{{$Attachmenturl}}" class="thumb download"> <img width="75"   src="{{getimageicons($Filename)}}" class="img-rounded" /> </a> <a target="_blank" href="{{$Attachmenturl}}" class="shortnamewrap name"> {{$attachments_data['filename']}} </a>
          <div class="links"><a href="{{$Attachmenturl}}">@lang('routes.BUTTON_DOWNLOAD_CAPTION')</a> </div>
        </li>
        @endforeach
        @endif
      </ul>
    </div>
    @endif
    <?php if(count($TicketConversation)>0){
		if(is_array($TicketConversation)){ $loop = 0;
		foreach($TicketConversation as $key => $TicketConversationData){ 
		if($TicketConversationData->Timeline_type == TicketsTable::TIMELINEEMAIL){
		 ?>
    <div class="mail-reply-seperator"></div>
    <div id="message{{$TicketConversationData->AccountEmailLogID}}" class="panel loop{{$loop}} @if($loop>4) panel-collapse @endif  first_data panel-primary margin-top" data-collapsed="0">
      
      <!-- panel head -->
      <div class="panel-heading panel-heading-convesation">        
          <div class="panel-title" ><span><?php 
		  if($TicketConversationData->EmailCall==Messages::Received){
		   ?>@lang('routes.MAIL_LBL_FROM') <?php echo $TicketConversationData->Emailfrom;  ?>
		 <?php }elseif($TicketConversationData->EmailCall==Messages::Sent){ echo $TicketConversationData->Emailfrom; ?> @lang('routes.MAIL_LBL_REPLIED')<br>@lang('routes.MAIL_LBL_TO') (<?php echo $TicketConversationData->EmailTo; ?>) <?php } ?></span>
          
          <?php if(!empty($TicketConversationData->EmailCc)){ ?><br>@lang('routes.MAIL_LBL_CC'):  <?php echo $TicketConversationData->EmailCc; ?> <?php } ?>
		  <?php if(!empty($TicketConversationData->EmailBcc)){ ?><br>@lang('routes.MAIL_LBL_BCC'): <?php echo $TicketConversationData->EmailBcc; ?> <?php } ?> </div>
          
        <div class="panel-options"> <span> {{\Carbon\Carbon::createFromTimeStamp(strtotime($TicketConversationData->created_at))->diffForHumans()}}</span> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
      </div>
      
      <!-- panel body -->
      <div @if($loop>4) style="display:none;" @endif class="panel-body">
		  <div class="embed-responsive embed-responsive-4by3 ticketBody" style="display: none;">
		  	{{htmlentities($TicketConversationData->EmailMessage)}}
		  </div>
        <?php $attachments = unserialize($TicketConversationData->AttachmentPaths);  ?>
        @if(count($attachments)>0 && strlen($TicketConversationData->AttachmentPaths)>0)
        <div class="mail-attachments last_data">
          <h4> <i class="entypo-attach"></i> @lang('routes.MAIL_LBL_ATTACHMENTS') <span>({{count($attachments)}})</span> </h4>
          <ul>
            @foreach($attachments as $key_acttachment => $attachments_data)
            <?php 
   		//$FilePath 		= 	AmazonS3::preSignedUrl($attachments_data['filepath']);
		$Filename		=	$attachments_data['filepath'];
		
		/*if(is_amazon() == true)
		{
			$Attachmenturl =  AmazonS3::preSignedUrl($attachments_data['filepath']);
		}
		else
		{
			$Attachmenturl = Config::get('app.upload_path')."/".$attachments_data['filepath'];
		}*/
		$Attachmenturl = URL::to('emails/'.$TicketConversationData->AccountEmailLogID.'/getattachment/'.$key_acttachment);
   	    ?>
            <li> <a target="_blank" href="{{$Attachmenturl}}" class="thumb download"> <img width="75"   src="{{getimageicons($Filename)}}" class="img-rounded" /> </a> <a target="_blank" href="{{$Attachmenturl}}" class="shortnamewrap name"> {{$attachments_data['filename']}} </a>
              <div class="links"><a href="{{$Attachmenturl}}">@lang('routes.BUTTON_DOWNLOAD_CAPTION')</a> </div>
            </li>
            @endforeach
          </ul>
        </div>
        @endif </div>
    </div>
    <?php } ?>
    <?php  $loop++; } } } ?>
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
              <div class="panel-title"><strong>@lang('routes.CUST_PANEL_PAGE_TICKETS_DETAIL_TAB_REQUESTER_INFO')</strong></div>
              <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
            </div>
            
            <!-- panel body -->
            <div class="panel-body">
              @if(!empty($ticketdata->RequesterName))
            <p><a class="blue_link" href="#">{{$ticketdata->RequesterName}}</a><br><a href="#">({{$ticketdata->Requester}})</a>. </p>
            @else
            <p><a href="#">{{$ticketdata->Requester}}</a></p>
            @endif
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="panel panel-primary margin-top" data-collapsed="0"> 
            
            <!-- panel head -->
            <div class="panel-heading">
              <div class="panel-title"><strong>@lang('routes.CUST_PANEL_PAGE_TICKETS_DETAIL_TAB_TICKET_PROPERTIES')</strong></div>
              <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
            </div>
            
            <!-- panel body -->
            <div class="panel-body">@include('customer.tickets.ticket_detail_dynamic_fields')</div>
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
<div class="modal fade" id="add-note-model">
  <div class="modal-dialog" style="width: 70%;">
    <div class="modal-content">
      <form id="add-note-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Add Note</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 margin-top pull-left">
              <div class="form-group">
                <textarea name="Note" id="Description_edit_note" class="form-control autogrow editor-note desciriptions " style="height: 175px; overflow: hidden; word-wrap: break-word; resize: none;"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" id="TicketID" name="TicketID" value="{{$ticketdata->TicketID}}">
          <button type="submit" id="note-edit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')"> <i class="entypo-floppy"></i> @lang('routes.BUTTON_SAVE_CAPTION') </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> @lang('routes.BUTTON_CLOSE_CAPTION') </button>
        </div>
      </form>
    </div>
  </div>
</div>
<form id="emai_attachments_reply_form" class="hidden" name="emai_attachments_form">
  <span class="emai_attachments_span">
  <input type="file" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" name="emailattachment[]" multiple id="filecontrole2">
  </span>
  <input id="info3" type="hidden" name="attachmentsinfo" />
  <button  class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden" type="submit" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')"><i class="entypo-floppy"></i>@lang('routes.BUTTON_SAVE_CAPTION')</button>
</form>
<style>
/*.mail-env .mail-body{float:left; width:70% !important; margin-right:1%; border-right:1px solid #ccc; background:#fff none repeat scroll 0 0;}*/
.mail-env .mail-body{float:left; width:71% !important;   }
.mail-env .mail-sidebar{width:29%; background:#fff none repeat scroll 0 0;}
.mail-env .mail-body .mail-info{background:#fff none repeat scroll 0 0;}
.mail-reply-seperator{background:#f1f1f1 none repeat scroll 0 0; width:100%; height:20px;}
.mail-env{background:none !important;}
.mail-menu {background:#f1f1f1;}
.mail-menu .row{margin-right:0px !important; margin-left:0px !important;}
.mail-menu .panel{margin-bottom:5px;}
.blue_link{font-size:16px; font-weight:bold;}
/*.mail-header{padding:10px !important; padding-bottom:0px !important; border-bottom:none !important;}*/
.mail-env .mail-body .mail-info .mail-sender, .mail-env .mail-body .mail-info .mail-date{padding:10px;}
.mail-env .mail-body .mail-attachments{padding-top:10px; padding-left:10px; padding-right:0px; padding-bottom:0px; background:#fff none repeat scroll 0 0;}
.mail-env .mail-body .mail-attachments h4{margin-bottom:10px;}
#tickets_filter .form-groups-bordered .form-group{padding-bottom:10px !important;}
.form-groups-bordered .form-group{padding-bottom:6px;}
.mail-env .mail-body .mail-text{background:#fff none repeat scroll 0 0;}
.last_data{border-bottom-left-radius:10px; border-bottom-right-radius:10px;}
.mail-env .mail-body .mail-header,.first_data{background:#fff none repeat scroll 0 0; border-top-left-radius:10px; border-top-right-radius:10px;}
.mail-env .mail-body .mail-info .mail-sender{padding-top:2px;}
.mail-env .mail-body .mail-info .mail-sender.mail-sender span{color:#000;}
.mail-env .mail-body .mail-header .mail-date{display: table-cell; width: 50%; color: #a6a6a6; padding:10px; text-align:right;}
.mail-env .mail-body .mail-header .mail-title{float:none !important;}
.mail-env .mail-body .mail-header .mail-date{padding:0px; text-align:inherit;}
.Requester_Info{padding:10px !important;}
.panel-primary > .panel-heading-convesation{min-height:80px !important;}
.panel-primary > .panel-heading-convesation .panel-title{font-size:12px !important; }
</style>
<script>
var editor_options 	 	=  		{};
var agent 				= 		parseInt('{{$ticketdata->Agent}}');
var file_count 		  	=  		0;
var emailFileList     	=		[];
var allow_extensions  	= 		{{$response_extensions}};
var max_file_size_txt 	=	    '{{$max_file_size}}';
var max_file_size	  	=	    '{{str_replace("M","",$max_file_size)}}';
var emailFileListReply 	=		[];
var CloseStatus			=		'{{$CloseStatus}}';
$(document).ready(function(e) {	
	$('.ticketBody').each(function(){
		var iFrame = $('<iframe class="embed-responsive-item" frameborder="0" allowfullscreen></iframe>');
		var ticketBodyHtml = $(this).html();
		$(this).html("").append(iFrame).show();
		var iFrameDoc = iFrame[0].contentDocument || iFrame[0].contentWindow.document;
		iFrameDoc.write($("<textarea/>").html(ticketBodyHtml).val());
		iFrameDoc.close();
	});
	$( document ).on("click",'.email_action' ,function(e) {			
		var url 		    = 	  baseurl + '/customer/tickets/ticket_action';
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
				mod.find("select").select2({
                    minimumResultsForSearch: -1
                });
				mod.find('.select2-container').css('visibility','visible');
				show_summernote(mod.find('.message'),editor_options);
		    
			},
		});
	});
	
	
	$( document ).on("click",'.add_note' ,function(e) {			
		var mod = $('#add-note-model');
			mod.find('#Description_edit_note').show();
		
		mod.modal("show");
		show_summernote(mod.find('#Description_edit_note'),editor_options);
	});

	//
	
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
						ShowToastr("error",f.name+" @lang('routes.CUST_PANEL_PAGE_TICKETS_MSG_FILE_ALREADY_SELECTED')");
					}
					else if(bytesToSize(f.size))
					{						
//						ShowToastr("error",f.name+" file size exceeds then upload limit ("+max_file_size_txt+"). Please select files again.");
						ShowToastr("error","@lang('routes.CUST_PANEL_PAGE_TICKETS_MSG_MAX_FILE_SIZE_ERROR') "+max_file_size_txt);
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
					ShowToastr("error",ext_current_file+" @lang('routes.CUST_PANEL_PAGE_TICKETS_MSG_FILE_TYPE_NOT_ALLOWED')");
					
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
		 var url = 	baseurl + '/customer/tickets/upload_file';
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
                 var url  =  baseurl + '/customer/tickets/delete_attachment_file';
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
					var confirm_close = confirm("Are you sure you want to close this ticket?");
					if(confirm_close)
					{
						var url 		    = 	  baseurl + '/customer/tickets/'+ticket_number+'/close_ticket';
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
						var url 		    = 	  baseurl + '/customer/tickets/'+ticket_number+'/delete';
						$.ajax({
							url: url,
							type: 'POST',
							dataType: 'json',
							async :false,
							data:{s:1,ticket_number:ticket_number},
							success: function(response){	
									if(response.status =='success'){									
									toastr.success(response.message, "Success", toastr_opts);
									window.location = baseurl+'/customer/tickets';
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
</script>
@stop 