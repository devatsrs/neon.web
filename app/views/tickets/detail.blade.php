@extends('layout.main')
@section('content')
    <link rel="stylesheet" type="text/css" href="<?php echo URL::to('/'); ?>/assets/lightbox/ekko-lightbox.css" />
<ol class="breadcrumb bc-3">
  <li> <a href="{{ URL::to('/dashboard') }}"><i class="entypo-home"></i>Home</a> </li>
  <li><a href="{{ URL::to('/tickets') }}">Tickets</a></li>
  <li class="active"> <strong>Detail</strong> </li>
</ol>
@include('includes.errors')
@include('includes.success')
<div class="pull-left"> 
@if( User::checkCategoryPermission('Tickets','Edit')) <a action_type="reply" data-toggle="tooltip" data-type="parent" data-placement="top"  ticket_number="{{$ticketdata->TicketID}}" data-original-title="Reply" class="btn btn-primary email_action tooltip-primary btn-xs"><i class="entypo-reply"></i> </a> <a action_type="forward"  data-toggle="tooltip" data-type="parent" data-placement="top"  ticket_number="{{$ticketdata->TicketID}}" data-original-title="Forward" class="btn btn-primary email_action tooltip-primary btn-xs"><i class="entypo-forward"></i> </a> <a data-toggle="tooltip"  data-placement="top" data-original-title="Edit" href="{{URL::to('tickets/'.$ticketdata->TicketID.'/edit/')}}" class="btn btn-primary tooltip-primary btn-xs"><i class="entypo-pencil"></i> </a> @endif
  @if( User::checkCategoryPermission('Tickets','Edit')) <a data-toggle="tooltip"  data-placement="top" data-original-title="Add Note"  class="btn btn-primary add_note tooltip-primary btn-xs"><i class="fa fa-sticky-note"></i> </a> @endif
  @if( User::checkCategoryPermission('Tickets','Edit')) @if($ClosedTicketStatus!=$ticketdata->Status) <a data-toggle="tooltip"  data-placement="top" data-original-title="Close Ticket" ticket_number="{{$ticketdata->TicketID}}"  title="Shift+Close to skip notification mail"  class="btn btn-red close_ticket tooltip-primary btn-xs"><i class="glyphicon glyphicon-ban-circle"></i> </a> @endif @endif  
  @if( User::checkCategoryPermission('Tickets','Delete')) <a data-toggle="tooltip"  data-placement="top" data-original-title="Delete Ticket" ticket_number="{{$ticketdata->TicketID}}" class="btn btn-red delete_ticket tooltip-primary btn-xs"><i class="entypo-trash"></i> </a> @endif  
     @if(User::checkCategoryPermission('TicketDashboardTimeLineWidgets','View'))
  <a data-toggle="tooltip"  data-placement="top" data-original-title="Ticket Log" href="{{URL::to('tickets/'.$ticketdata->TicketID.'/log/')}}" class="btn btn-primary tooltip-primary btn-xs"><i class="entypo-list"></i> </a>
  @endif
   </div>
<div class="pull-right">@if($PrevTicket) <a data-toggle="tooltip"  data-placement="top" data-original-title="Previous Ticket" href="{{URL::to('tickets/'.$PrevTicket.'/detail/')}}" class="btn btn-primary tooltip-primary btn-xs"><i class="fa fa-step-backward"></i> </a> @endif
  @if($NextTicket) <a data-toggle="tooltip"  data-placement="top" data-original-title="Next Ticket" href="{{URL::to('tickets/'.$NextTicket.'/detail/')}}" class="btn btn-primary tooltip-primary btn-xs"><i class="fa fa-step-forward"></i> </a> @endif</div>
<div class="clear clearfix"></div>
<div class="mail-env margin-top"> 
  
  <!-- compose new email button --> 
  
  <!-- Mail Body -->
  <div class="mail-body">
    <div class="mail-header"> 
      <!-- title -->
        <div class="mail-title">{{emailHeaderDecode($ticketdata->Subject)}} #{{$ticketdata->TicketID}}</div>
      <div class="mail-date">
          @if($ticketdata->TicketType)
              <a>{{$TicketEmail->Emailfrom}}</a> emailed <a>{{$ticketdata->EmailTo}}</a> <br>
              {{--From: {{$Requester['Email']}}<br>--}}
              @else
              From: {{$TicketEmail->Emailfrom}} <br>
              To: {{$ticketdata->EmailTo}} <br>
              {{--From: <a class="" href="{{$Requester['URL']}}">{{$Requester['Title']}}</a> ({{$Requester['Email']}})<br>--}}
          @endif
        @if(!empty($ticketdata->RequesterCC))Cc: {{$ticketdata->RequesterCC}} &nbsp; @endif
        @if(!empty($ticketdata->RequesterBCC))Bcc: {{$ticketdata->RequesterBCC}} @endif
        <br>
        {{\Carbon\Carbon::createFromTimeStamp(strtotime($ticketdata->created_at))->diffForHumans()}} ( {{\Carbon\Carbon::createFromTimeStamp(strtotime($ticketdata->created_at))}} )</div>
      <!-- links --> 
    </div>
    <?php if(count($TicketConversation)>0){
		if(is_array($TicketConversation)){ $loop = 0;
		foreach($TicketConversation as $key => $TicketConversationData){ 
		if($TicketConversationData->Timeline_type == TicketsTable::TIMELINEEMAIL){
		 ?>
    <div class="mail-reply-seperator"></div>
    <div id="message{{$TicketConversationData->AccountEmailLogID}}" class="panel loop{{$loop}} @if($loop>4) panel-collapse @endif  first_data panel-primary margin-top" data-collapsed="0">
      
      <!-- panel head -->
      <div class="panel-heading panel-heading-convesation">        
          <div class="panel-title col-md-10" ><span><?php
		  if($TicketConversationData->EmailCall==Messages::Received){
		   ?>From <?php if(!empty($TicketConversationData->EmailfromName)){ echo imap_mime_header_decode($TicketConversationData->EmailfromName)[0]->text." (".$TicketConversationData->Emailfrom.")"; ?> <?php }else{ ?> <?php echo $TicketConversationData->Emailfrom; } ?><br>to (<?php echo $TicketConversationData->EmailTo; ?>)
		 <?php }elseif($TicketConversationData->EmailCall==Messages::Sent){ echo $TicketConversationData->CreatedBy; ?> (<?php echo $TicketConversationData->Emailfrom; ?>) replied<br>to (<?php echo $TicketConversationData->EmailTo; ?>) <?php } ?></span>
          
          <?php if(!empty($TicketConversationData->EmailCc)){ ?><br>cc:  <?php echo str_replace(',',', ',$TicketConversationData->EmailCc); ?> <?php } ?>
		  <?php if(!empty($TicketConversationData->EmailBcc)){ ?><br>bcc: <?php echo str_replace(',',', ',$TicketConversationData->EmailBcc); ?> <?php } ?> </div>
          
        <div class="panel-options col-md-2" style="text-align: right; padding: 0 5px;"> <span> {{\Carbon\Carbon::createFromTimeStamp(strtotime($TicketConversationData->created_at))->diffForHumans()}}</span> @if( User::checkCategoryPermission('Tickets','Edit')) <a action_type="forward"  data-toggle="tooltip" data-type="child" data-placement="top"  ticket_number="{{$TicketConversationData->AccountEmailLogID}}" data-original-title="Forward" class="btn btn-xs btn-info email_action tooltip-primary"><i class="entypo-forward"></i> </a> @endif <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
      </div>
      
      <!-- panel body -->
      <div @if($loop>4) style="display:none;" @endif  class="panel-body">
          <div class="embed-responsive embed-responsive-4by3 ticketBody" style="display: none;">
            {{htmlentities($TicketConversationData->EmailMessage)}}
          </div>
        <?php $attachments = unserialize($TicketConversationData->AttachmentPaths);  ?>
        @if(!empty($attachments) && count($attachments)>0 && strlen($TicketConversationData->AttachmentPaths)>0)
        <div class="mail-attachments last_data">
          <h4> <i class="entypo-attach"></i> Attachments <span>({{count($attachments)}})</span> </h4>
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
              <div class="links"><a href="{{$Attachmenturl}}">Download</a> </div>
            </li>
            @endforeach
          </ul>
        </div>
        @endif </div>
    </div>
    <?php }else if($TicketConversationData->Timeline_type == TicketsTable::TIMELINENOTE){
	?>
    <div class="mail-reply-seperator"></div>
    <div id="note{{$TicketConversationData->NoteID}}" class="panel loop{{$loop}} @if($loop>4) panel-collapse @endif panel-primary margin-top" data-collapsed="0">
      
      <!-- panel head -->
      <div class="panel-heading">
        <div class="panel-title"><strong>Note</strong> by ({{$TicketConversationData->CreatedBy}}) </div>
        <div class="panel-options"> <span>{{\Carbon\Carbon::createFromTimeStamp(strtotime($TicketConversationData->created_at))->diffForHumans()}}</span> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
      </div>
      
      <!-- panel body -->
      <div @if($loop>4) style="display:none;" @endif  class="panel-body">
          <div class="embed-responsive embed-responsive-4by3 ticketBody" style="display: none;">
              {{$TicketConversationData->Note}}
          </div>
      </div>
    </div>
    <?php	} ?>
    <?php $loop++; } } } ?>

      <?php $attachments = unserialize($ticketdata->AttachmentPaths); ?>
      <div class="mail-text">
          <div class="embed-responsive embed-responsive-4by3 ticketBody" style="display: none;">
              {{htmlentities($ticketdata->Description)}}
          </div>
      </div>
      @if(count($attachments)>0 && strlen($ticketdata->AttachmentPaths)>0)
          <div class="mail-attachments last_data">
              <h4> <i class="entypo-attach"></i> Attachments <span>({{count($attachments)}})</span> </h4>
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
                          $Attachmenturl = URL::to('tickets/'.$ticketdata->TicketID.'/getattachment/'.$key_acttachment);
                          ?>
                          <li> <a target="_blank" href="{{$Attachmenturl}}" class="thumb download"> <img width="75"   src="{{getimageicons($Filename)}}" class="img-rounded" /> </a> <a target="_blank" href="{{$Attachmenturl}}" class="shortnamewrap name"> {{$attachments_data['filename']}} </a>
                              <div class="links"><a href="{{$Attachmenturl}}">Download</a> </div>
                          </li>
                      @endforeach
                  @endif
              </ul>
          </div>
      @endif
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
              <div class="panel-title"><strong>Ticket Info</strong></div>
              <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
            </div>            
            <!-- panel body -->
            <div class="panel-body">
            <form id="UpdateTicketDueTime" class="form-horizontal form-groups-bordered validate" role="form">
                <div class="form-group">
                    <div class="col-md-12">
                        <p><span class="blue_link" >{{$TicketStatus}}</span><br>
                            <?php
                            $ticket_status_data = get_ticket_status_date_array($ticketdata);
                            ?>
                            @if(isset($ticket_status_data["sla_timer"]) && !$ticket_status_data["sla_timer"])
                                {{----SLAOFF--
                                Waiting on Customer
                                Since 7 days ago
                                from Thu, 20 Apr at 4:49 PM--}}
                                Since {{$ticket_status_data["hunam_readable"]}}
                                <br>From {{date('D, d M',strtotime($ticket_status_data["the_date"]))}} at {{date('H:i A',strtotime($ticket_status_data["the_date"]))}}
                            @else
                                @if(isset($ticket_status_data["due"]) && $ticket_status_data["due"])
                                    Due in
                                @else
                                    Overdue by
                                @endif
                                {{$ticket_status_data["hunam_readable"]}}
                                <br>on {{date('D, d M',strtotime($ticket_status_data["the_date"]))}} at {{date('H:i A',strtotime($ticket_status_data["the_date"]))}}
                                <span class="blue_link clickable change_duetime"  ticket="{{$ticketdata->TicketID}}">Change</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="change_due_time form-group">
                  <div class="col-md-6">      
                  <input autocomplete="off" type="text" name="DueDate" id="DueDate" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d',strtotime($ticketdata->DueDate))}}" data-startdate="{{date('Y-m-d',strtotime(" today"))}}" />
                  </div><div class="col-md-6">
                    <input type="text" name="DueTime" id="DueTime" data-minute-step="5" data-show-meridian="false" data-default-time="{{date('H:i:s',strtotime($ticketdata->DueDate))}}" data-show-seconds="true" data-template="dropdown" placeholder="00:00:00" class="form-control timepicker">
                </div>
                </div>  
            <div class="change_due_time form-group"> 
                  <div class="col-md-12">                    
                  <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-mail"></i>Update</button>
                  </div>
                </div> 
                 <input type="hidden" id="TicketID" name="TicketID" value="{{$ticketdata->TicketID}}">                             
            </form>
            </div>
          </div>      
        
          <div class="panel panel-primary" data-collapsed="0"> 
            
            <!-- panel head -->
            <div class="panel-heading">
              <div class="panel-title"><strong>Requester Info</strong></div>
              <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
            </div>
            
            <!-- panel body -->
            <div class="Requester_Info panel-body">

                <p><a target="_blank" class="blue_link" href="{{$Requester['URL']}}">{{$Requester['Title']}}</a><br>
                ({{$Requester['Email']}}). </p>

              @if($ticketdata->ContactID>0 && $ticketdata->AccountID==0 && $ticketdata->UserID==0)
              @if(User::checkCategoryPermission('Contacts','Edit'))
              <form role="form" id="form-tickets-owner_edit" method="post"  class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                  <label for="field-1" class="col-sm-3 control-label">Contact Owner</label>
                  <div class="col-sm-9">
                    <?php
                                $selected_owner = $Requester['Owner'];
                            ?>
                    <select name="Owner" class="select2" data-allow-clear="true" data-placeholder="Account Owner...">
                      <option></option>
                      <optgroup label="Leads">
                                    @if( count($lead_owners))
                                    @foreach($lead_owners as $lead_owner)
                                    @if(!empty($lead_owner->AccountName) && $lead_owner->Status == 1)
                                    
                      <option value="{{$lead_owner->AccountID}}" @if($selected_owner == $lead_owner->AccountID) {{"selected"}} @endif >
                      {{$lead_owner->AccountName}} </option>
                      
                                    @endif
                                    @endforeach
                                    @endif
                                </optgroup>
                      <optgroup label="Accounts">
                                    @if( count($account_owners))
                                    @foreach($account_owners as $account_owner)
                                    @if(!empty($account_owner->AccountName) && $account_owner->Status == 1)
                                    
                      <option value="{{$account_owner->AccountID}}" @if($selected_owner == $account_owner->AccountID) {{"selected"}} @endif >
                      {{$account_owner->AccountName}} </option>
                      
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
              @endif </div>
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
          <button type="submit" id="note-edit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
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
  <button  class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
</form>
<style>
/*.mail-env .mail-body{float:left; width:70% !important; margin-right:1%; border-right:1px solid #ccc; background:#fff none repeat scroll 0 0;}*/
.mail-env .mail-body{float:left; width:71% !important;   }
.mail-env .mail-sidebar{width:29%; background:#fff none repeat scroll 0 0;}
.mail-env .mail-body .mail-info{background:#fff none repeat scroll 0 0;}
.mail-reply-seperator{background:#f1f1f1 none repeat scroll 0 0; width:100%; /*height:20px;*/}
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
.change_due_time{display:none;}
</style>
<script src="<?php echo URL::to('/'); ?>/assets/lightbox/ekko-lightbox.js"></script>
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
var ticketPreMSG        =       '';
$(document).ready(function(e) {
    $('.ticketBody').each(function(){
        var iFrame = $('<iframe class="embed-responsive-item" frameborder="0" allowfullscreen></iframe>');
        var ticketBodyHtml = $(this).html();
        $(this).html("").append(iFrame).show();
        var iFrameDoc = iFrame[0].contentDocument || iFrame[0].contentWindow.document;
        iFrameDoc.write($("<textarea/>").html(ticketBodyHtml).val());
        iFrameDoc.close();
    });

    var lightboxhtml = $('<a href="" data-type="image" data-toggle="lightbox" data-title="" data-footer=""></a>');
    $(".mail-body img").each(function(i){
        //var $this = $(this);
        lightboxhtml.attr('href',$(this).attr('src'));
        $(this).addClass("img-fluid");
        var $image = $('<div>').append($(this).clone()).html();
        lightboxhtml.html($image);
        $(this).replaceWith($('<div>').append(lightboxhtml.clone()).html());
    });

    $(document).on('click', '[data-toggle="lightbox"]:not([data-gallery="navigateTo"])', function(event) {
        event.preventDefault();
        return $(this).ekkoLightbox({
            onShown: function() {
                if (window.console) {
                    return console.log('Checking our the events huh?');
                }
            },
            onNavigate: function(direction, itemIndex) {
                if (window.console) {
                    return console.log('Navigating '+direction+'. Current item: '+itemIndex);
                }
            }
        });
    });
	
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
                ticketPreMSG=$('#EmailAction-model .modal-content').find('[name=Message]').val();
					var mod =  $(document).find('.EmailAction_box');
					$('#EmailAction-model').modal('show');

				mod.find('.emailaddresses').select2({
        	    tags:{{$AllEmailsTo}}
       	 		});
				mod.find("select").select2({
                    minimumResultsForSearch: -1
                });
				mod.find('.select2-container').css('visibility','visible');
                editor_options.withOutDestroy=true;
                show_summernote(mod.find('.message'),editor_options);
			
		},
	});
	
	});
    $( document ).on("click",'#EmailAction-model .modal-footer .btn-danger, #EmailAction-model .modal-header .close' ,function(e) {
            $('#EmailAction-model .modal-content').html('');
    });
	$( document ).on("click",'.add_note' ,function(e) {		 
		var mod = $('#add-note-model');
		mod.modal("show");	
	
	 $('#add-note-model').on('shown.bs.modal', function(event){
						  var modal = $(this);
                        modal.find('.editor-note').show();
						  
                        var modal = $('#add-note-model');
                        show_summernote(modal.find('.editor-note'),editor_options);
                    });
	 });
	////
	$( document ).on("submit",'#add-note-form' ,function(e) {			
		e.preventDefault();
		var formData = new FormData($('#add-note-form')[0]);
		var url 		    = 	  baseurl + '/tickets/add_note';
	 $.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			async :false,
			cache: false,
			contentType: false,
			processData: false,
			data:formData,
			success: function(response){
						$("#add-note-model").find('#note-edit').button('reset');
						if(response.status =='success'){									
							toastr.success(response.message, "Success", toastr_opts);
							//location.reload();
							//window.location.href = window.location.href+"#last_item";
							location.reload();
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
				},
		});
	});
	
	////

	//

	 $('#UpdateTicketDueTime').submit(function(e) {        
		//////////////////////////          	
			var email_url 	= 	"<?php echo URL::to('/tickets/'.$ticketdata->TicketID.'/updateTicketDueTime/');?>";
          	e.stopImmediatePropagation();
            e.preventDefault();			
			var formData = new FormData($(this)[0]);

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
				 e.preventDefault();
				var isSendEmail = 1;
				if(e.shiftKey){ 
			 		isSendEmail = 0;
				 }else{isSendEmail = 1;} 
                var ticket_number   =     parseInt($(this).attr('ticket_number'));
				if(ticket_number){
					var confirm_close = confirm("Are you sure you want to close this ticket?");
					if(confirm_close)
					{
						var url 	 = 	  baseurl + '/tickets/'+ticket_number+'/close_ticket';
						$.ajax({
							url: url,
							type: 'POST',
							dataType: 'json',
							async :false,
							data:{s:1,ticket_number:ticket_number,isSendEmail:isSendEmail},
							success: function(response){	  
									if(response.status =='success'){									
									toastr.success(response.message, "Success", toastr_opts);
									//$('#TicketStatus').val(response.close_id).trigger('change');
									location.reload();
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
					var confirm_close = confirm("Are you sure you want to delete this ticket?");
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
			
		@if($ticketdata->ContactID>0)	
	$('#form-tickets-owner_edit').submit(function(e) {
		e.preventDefault();
		e.stopImmediatePropagation();	  
		$("#form-tickets-owner_edit").find('#update_ticket_owner').addClass('disabled');
		$("#form-tickets-owner_edit").find('#update_ticket_owner').button('loading');					
		var formData = new FormData($(this)[0]);
		var ajax_url = baseurl+'/contacts/{{$ticketdata->ContactID}}/updatecontactowner';
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
			
			$('.change_duetime').click(function(e) {
                $('.change_due_time').toggle();
            });

            $( document ).on("change",'.email_template' ,function(e) {
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
});

    function editor_reset(data){
        //var doc = $('.mail-compose');
        var doc = $(document).find('#EmailActionform');
        doc.find('#EmailActionbody').show();

        if(!Array.isArray(data)){
            var EmailTemplate = data['EmailTemplate'];
            doc.find('[name="Subject"]').val(EmailTemplate.Subject);
            doc.find('[name="Message"]').val(EmailTemplate.TemplateBody + ticketPreMSG);
        }else{
            doc.find('[name="Subject"]').val('');
            doc.find('[name="Message"]').val('');
        }
        editor_options.withOutDestroy=false;
        show_summernote(doc.find('[name="Message"]'),editor_options);

    }
//setTimeout(setagentval(),6000);
	function setagentval(){
		$('#TicketGroup').trigger('change');		
		$('#TicketAgent').val(agent);
		$('#TicketAgent').trigger('change');
		console.log("Agent:"+agent);
		
	}

    function sumbitReplyTicket(ticketStatus){
        var email_url 	= 	"<?php echo URL::to('/tickets/'.$ticketdata->TicketID.'/actionsubmit/');?>";
        var formData = new FormData($('#EmailActionform')[0]);
        formData.append('ticketStatus', ticketStatus);
        $("#EmailAction-model").find('.btn-send-mail').addClass('disabled');
        $("#EmailAction-model").find('.btn-send-mail').eq(0).button('loading');
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
                $("#EmailAction-model").find('.btn-send-mail').removeClass('disabled');
                if(response.status =='success'){
                    toastr.success(response.message, "Success", toastr_opts);
                    //	window.location.href = window.location.href+"#last_item";
                    location.reload();
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            },
        })
    }
</script> 
@stop 