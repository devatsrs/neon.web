@extends('layout.main')
@section('content')
<style>
        ul.grid li div.box{
            min-height:16.5em;
        }
    </style>
<div  style="min-height: 1050px;">
  <ol class="breadcrumb bc-3">
    <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
    <li> <a href="{{URL::to('contacts')}}">Contacts</a> </li>
    <li> <a><span>{{contacts_dropbox($contacts->ContactID)}}</span></a> </li>
    <li class="active"> <strong>View Contact</strong> </li>
  </ol>
  @include('includes.errors')
  @include('includes.success')
  <div id="account-timeline">
    <section> 
      <!-- -->
      <div id="contact-column" class="about-account col-md-3 col-sm-12 col-xs-12 pull-left">
        <div class=""> 
          <!--<div class="list-contact-slide" style="height:500px; overflow-x:scroll;"> -->
          <div class="list-contact-slide"> 
            <!--contacts card start -->
            <div class="gridview">
              <ul class="clearfix grid col-md-12">
                <li>
                  <div class="box clearfix ">
                    <div class="col-sm-12 headerSmall padding-left-1"> <span class="head">{{$contacts->NamePrefix}} {{$contacts->FirstName}} {{$contacts->LastName}}</span><br>
                      <span class="meta complete_name"> </span></div>
                    <div class="col-sm-12 padding-0">
                      <div class="block blockSmall">
                        <div class="meta">Department: <a class="sendemail">{{$contacts->Department}}</a></div>
                      </div>
                      <div class="block blockSmall">
                        <div class="meta">Job Title: <a class="sendemail" href="javascript:void(0)">{{$contacts->Title}}</a></div>
                      </div>
                      <div class="block blockSmall">
                        <div class="meta">Email: <a class="sendemail" href="javascript:void(0)">{{$contacts->Email}}</a></div>
                      </div>
                      <div class="cellNo cellNoSmall">
                        <div class="meta">Phone: <a href="tel:{{$contacts->Phone}}">{{$contacts->Phone}}</a></div>
                      </div>
                      <div class="cellNo cellNoSmall">
                        <div class="meta">Fax:{{$contacts->Fax}}</div>
                      </div>
                      <div class="block blockSmall">
                        <div class="meta">Skype: <a class="sendemail" href="javascript:void(0)">{{$contacts->Skype}}</a></div>
                      </div>
                    </div>
                    <div class="col-sm-11 padding-0 action"> <a class="btn-default btn-sm label padding-3" href="{{ URL::to('contacts/'.$contacts->ContactID.'/edit')}}"><i class="entypo-pencil"></i> </a></div>
                  </div>
                </li>
              </ul>
            </div>
            
            <!--contacts card end --> 
            
          </div>
        </div>
      </div>
      <!-- -->
      <div id="text-boxes" class="timeline col-md-9 col-sm-12 col-xs-12  upper-box">
        <div class="row">
          <ul id="tab-btn" class="interactions-list">
            <li id="1" class="interactions-tab"> <a href="#Note" class="interaction-link note" onclick="showDiv('box-1',1)"><i class="entypo-doc-text"></i>New Note</a> </li>
            <li id="4" class="interactions-tab"> <a href="#email" class="interaction-link task" onclick="showDiv('box-2',4)"><i class="entypo-mail"></i>Email</a> </li>
          </ul>
        </div>
        <div class="row margin-top-5 box-min" id="box-1">
          <div class="col-md-12">
            <form role="form" id="notes-from" action="{{URL::to('contacts/'.$contacts->ContactID.'/store_note/')}}" method="post">
              <div class="form-group ">
                <textarea name="Note" id="note-content" class="form-control autogrow editor-note"   style="height: 175px; overflow: hidden; word-wrap: break-word; resize: none;"></textarea>
              </div>
              <div class="form-group end-buttons-timeline">
                <button value="save" id="save-note" class="pull-right save btn btn-primary btn-sm btn-icon icon-left save-note-btn hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>                
              </div>
            </form>
          </div>
        </div>
        <div class="row no-display margin-top-5 box-min" id="box-2" style="margin-bottom: 5px;">
          <div class="col-md-12">
            <div class="mail-compose">
              <form method="post" id="email-from" role="form" enctype="multipart/form-data">
              <div class="form-Group" >                  
                  <div class=" @if($SystemTickets) col-md-10 pull-left @else col-md-12 @endif" style="padding-left:0px; @if(!$SystemTickets) padding-right:0px; @endif" >
                  <label >From</label>
                  {{Form::select('email-from',TicketGroups::GetGroupsFrom(),'',array("class"=>"select2"))}}
                  </div>
                  @if($SystemTickets)
                  <div class="col-md-2 pull-right">
                   <label class="control-label" >Open ticket</label>
                    <p class="make-switch switch-small">
                      <input name="createticket" type="checkbox" value="1" >
                    </p>
                  </div>
                  @endif
                  </div>
                  <div class="clearfix" style="margin-bottom: 15px;"></div>
                <div class="form-group">
                  <label for="to">To *</label>
                  <!--{{ Form::select('email-to', USer::getUserIDList(), '', array("class"=>"select2","id"=>"email-to","tabindex"=>"1")) }}-->
                  <input type="text" class="form-control" value="{{$contacts->Email}}" id="email-to" name="email-to" tabindex="1"  />
                  <div class="field-options"> <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#cc').parent().removeClass('hidden'); $('#cc').focus();">CC</a> <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#bcc').parent().removeClass('hidden'); $('#bcc').focus();">BCC</a> </div>
                </div>
                <div class="form-group hidden">
                  <label for="cc">CC</label>
                  <input type="text" name="cc"  class="form-control tags"  id="cc" />
                </div>
                <div class="form-group hidden">
                  <label for="bcc">BCC</label>
                  <input type="text" name="bcc"  class="form-control tags"  id="bcc" />
                </div>
                <div class="form-Group" style="margin-bottom: 15px;">
                  <label >Email Template</label>
                  {{Form::select('email_template',$emailTemplates,'',array("class"=>"select2 email_template","parent_box"=>"mail-compose"))}} </div>
                <div class="form-group">
                  <label for="subject">Subject *</label>
                  <input type="text" class="form-control" id="subject" name="Subject" tabindex="4" />
                  <input  hidden="" name="token_attachment" value="{{$random_token}}" />
                </div>
                <div class="form-group">
                  <label for="subject">Email *</label>
                  <textarea id="Message" class="form-control message"    name="Message"></textarea>
                </div>
                <p class="comment-box-options-activity"> <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)"> <i class="entypo-attach"></i> </a> </p>
                <div class="form-group email_attachment">
                  <input type="hidden" value="1" name="email_send" id="email_send"  />
                  <!--   <input id="filecontrole" type="file" name="emailattachment[]" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden" multiple data-label="<i class='entypo-attach'></i>Attachments" />-->
                  
                  <input id="emailattachment_sent" type="hidden" name="emailattachment_sent" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"   />
                  <input id="info2" type="hidden" name="attachmentsinfo" />
                  <span class="file-input-names"></span> </div>
                <div class="form-group end-buttons-timeline">
                  <button name="mail_submit" value="save_mail" id="save-mail" class="pull-right save btn btn-primary btn-sm btn-icon btn-send-mail icon-left hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-mail"></i>Send</button>                  
                </div>
                <input name="usertype" value="{{Messages::UserTypeContact}}" type="hidden" />
              </form>
            </div>
          </div>
        </div>        
      </div>
      <!-- --> 
      <!-- --> 
      <!--<div class="timeline col-md-11 col-sm-12 col-xs-12">-->
      <div class="timeline timeline_start col-md-9 col-sm-12 col-xs-12 big-col pull-right"> @if(count($response_timeline)>0 && $message=='')
        <div class="row" style="padding:9px 7px 0;">
          <div class="col-sm-12">
            <ul class="icheck-list">
              <li>
                <form id="form_timeline_filter">
                  <div class="pull-left">
                    <label >Show</label>
                    &nbsp;&nbsp;&nbsp;&nbsp;</div>
                  <div class="radio radio-replace color-primary pull-left">
                    <input class="icheck-11 timeline_filter" show_data="all" type="radio" id="minimal-radio-1" name="timeline_filter" checked>
                    <label for="minimal-radio-1">All</label>
                    &nbsp;&nbsp;</div>
                  <div class="radio radio-replace color-green pull-left">
                    <input class="icheck-11 timeline_filter" show_data="timeline_note_entry" type="radio" id="minimal-radio-2" name="timeline_filter">
                    <label for="minimal-radio-2">Notes</label>
                    &nbsp;&nbsp;</div>                  
                  <div class="radio radio-replace color-gold pull-left">
                    <input class="icheck-11 timeline_filter" show_data="timeline_mail_entry" type="radio" id="minimal-radio-4" name="timeline_filter">
                    <label for="minimal-radio-4">Emails</label>
                  </div>
                  @if($ShowTickets)
                  <div class="radio radio-replace color-red pull-left">
                    <input class="icheck-11 timeline_filter" show_data="timeline_ticket_entry" type="radio" id="minimal-radio-5" name="timeline_filter">
                    <label for="minimal-radio-5">Tickets</label>
                  </div>
                  @endif
                </form>
              </li>
            </ul>
          </div>
        </div>
        <ul class="cbp_tmtimeline" id="timeline-ul">
          <li></li>
          <?php  foreach($response_timeline as $key => $rows){
			 // $rows = json_decode(json_encode($rows), True); //convert std array to simple array
			   ?>
          @if(isset($rows['Timeline_type']) && $rows['Timeline_type']==Task::Mail)
          <li id="timeline-{{$key}}" class="count-li timeline_mail_entry">
            <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
              <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
              <?php if(date("Y-m-d",strtotime($rows['created_at'])) == date('Y-m-d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
              </span>
              <?php } ?>
            </time>
            <div id_toggle="{{$key}}" class="cbp_tmicon bg-gold"> <i class="entypo-mail"></i> </div>
            <div class="cbp_tmlabel normal_tag"> <a email_number="{{$rows['AccountEmailLogID']}}" action_type="forward" class="pull-right edit-deal email_action" title="Forward"><i class="entypo-forward"></i></a> <a email_number="{{$rows['AccountEmailLogID']}}" action_type="reply-all" class=" pull-right edit-deal email_action" title="Reply All"><i class="entypo-reply-all"></i></a> <a email_number="{{$rows['AccountEmailLogID']}}" action_type="reply" class="pull-right edit-deal email_action" title="Reply"><i class="entypo-reply"></i></a>
              <h2 class="toggle_open" id_toggle="{{$key}}">@if($rows['CreatedBy']==$current_user_title) You @else {{$rows['CreatedBy']}}  @endif <span>sent an email to</span> @if($rows['EmailToName']==$current_user_title) You @else {{$rows['EmailToName']}}  @endif <br>
                <p class="mail_subject">Subject: {{$rows['EmailSubject']}}</p>
              </h2>
              <div id="hidden-timeline-{{$key}}" class="details no-display"> @if($rows['EmailCc'])
                <p>CC: {{$rows['EmailCc']}}</p>
                @endif
                @if($rows['EmailBcc'])
                <p>BCC: {{$rows['EmailBcc']}}</p>
                @endif
                <?php
	  if($rows['EmailAttachments']!='')
	  {
    		$attachments = unserialize($rows['EmailAttachments']);
			
			if(count($attachments)>0)
			{
				 echo "<p>Attachments: ";
				foreach($attachments as $key_acttachment => $attachments_data)
				{
					//
					/* if(is_amazon() == true)
					{
						$Attachmenturl =  AmazonS3::preSignedUrl($attachments_data['filepath']);
					}
					else
					{
						$Attachmenturl = Config::get('app.upload_path')."/".$attachments_data['filepath'];
					}*/
                    $Attachmenturl = URL::to('emails/'.$rows['AccountEmailLogID'].'/getattachment/'.$key_acttachment);
					if($key_acttachment==(count($attachments)-1)){
						echo "<a target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a><br><br>";
					}else{
						echo "<a target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a><br>";
					}
					
				}
				echo "</p>";
			}			
	  }	 
	   ?>
                <div class="mail_message">Message:<br>
                  {{$rows['EmailMessage']}}</div>
                <br>
                <p><a data_fetch_id="{{$rows['AccountEmailLogID']}}" conversations_type="mail"  class="ticket_conversations">View Conversation</a></p>
              </div>
            </div>
          </li>
          @elseif(isset($rows['Timeline_type']) && $rows['Timeline_type']==Task::Note)
          <li id="timeline-{{$key}}" class="count-li timeline_note_entry">
            <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
              <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
              <?php if(date("Y-m-d",strtotime($rows['created_at'])) == date('Y-m-d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
              </span>
              <?php } ?>
            </time>
            <div id_toggle="{{$key}}" class="cbp_tmicon bg-success"><i class="entypo-doc-text"></i></div>
            <div class="cbp_tmlabel normal_tag"> <a id="edit_note_{{$rows['NoteID']}}" note-id="{{$rows['NoteID']}}"  key_id="{{$key}}" class="pull-right edit-deal edit_note_link"><i class="entypo-pencil"></i></a> <a id="delete_note_{{$rows['NoteID']}}" note-id="{{$rows['NoteID']}}"  key_id="{{$key}}" class="pull-right edit-deal delete_note_link"><i class="entypo-trash"></i></a>
              <h2 class="toggle_open" id_toggle="{{$key}}">@if($rows['CreatedBy']==$current_user_title) You @else {{$rows['CreatedBy']}}  @endif <span>added a note</span></h2>
              <div id="hidden-timeline-{{$key}}" class="details no-display">
                <p>{{$rows['Note']}}</p>
              </div>
            </div>
          </li>
          @elseif(isset($rows['Timeline_type']) && $rows['Timeline_type']==Task::Ticket)
          <li id="timeline-{{$key}}" class="count-li timeline_ticket_entry">
            <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
              <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
              <?php if(date("Y-m-d",strtotime($rows['created_at'])) == date('Y-m-d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
              </span>
              <?php } ?>
            </time>
            <div id_toggle="{{$key}}" class="cbp_tmicon bg-danger"><i class="entypo-ticket"></i></div>
            <div class="cbp_tmlabel normal_tag">
              <h2 class="toggle_open" id_toggle="{{$key}}">Ticket<br>
                <p>Subject: {{$rows['TicketSubject']}}</p>
              </h2>
              <div id="hidden-timeline-{{$key}}" class="details no-display">
                <p>Status: {{$rows['TicketStatus']}}</p>
                <p>Requester: {{$rows['RequestEmail']}}</p>
                <p>Priority: {{$rows['TicketPriority']}}</p>
                <p>Type: {{$rows['TicketType']}}</p>
                <p>Group: {{$rows['TicketGroup']}}</p>
                <p>Date Created: {{$rows['created_at']}}</p>
                <p>Description: {{$rows['TicketDescription']}}</p>
                <p><a data_fetch_id="{{$rows['TicketID']}}" conversations_type="ticket" class="ticket_conversations">View Ticket Conversations</a></p>
              </div>
            </div>
          </li>
          @endif
          <?php  }
			if(count($response_timeline)<10)
			{
			?>
          <li class="timeline-end">
            <time class="cbp_tmtime"></time>
            <div class="cbp_tmicon bg-info end_timeline_logo "><i class="entypo-infinity"></i></div>
            <div class="end_timeline cbp_tmlabel">
              <h2></h2>
              <div class="details no-display"></div>
            </div>
          </li>
          <?php
			}
			 ?>
        </ul>
        @if(count($response_timeline)>($data['iDisplayLength'])-1)
        <div id="last_msg_loader"></div>
        @endif
        @else <span style="padding:1px;">
        <h3>No Activity Found.</h3>
        </span> @endif </div>
    </section>
  </div>
</div>
<div class="followup_task_data hidden">
  <ul>
    <li></li>
  </ul>
</div>
<form id="emai_attachments_form" class="hidden" name="emai_attachments_form">
  <span class="emai_attachments_span">
  <input type="file" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" name="emailattachment[]" multiple id="filecontrole1">
  </span>
  <input  hidden="" name="account_id" value="{{$contacts->ContactID}}" />
  <input  hidden="" name="token_attachment" value="{{$random_token}}" />
  <input id="info1" type="hidden" name="attachmentsinfo" />
  <button  class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
</form>
<form id="emai_attachments_reply_form" class="hidden" name="emai_attachments_form">
  <span class="emai_attachments_span">
  <input type="file" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" name="emailattachment[]" multiple id="filecontrole2">
  </span>
  <input  hidden="" name="account_id" value="{{$contacts->ContactID}}" />
  <input  hidden="" name="token_attachment" value="{{$random_token}}" />
  <input id="info3" type="hidden" name="attachmentsinfo" />
  <button  class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
</form>
@include('includes.submit_note_script',array("controller"=>"contacts")) 
@include("contacts.timeline.activity_jscode",array("response_extensions"=>$response_extensions,"AccountID"=>$contacts->ContactID,"per_scroll"=>$per_scroll,"token"=>$random_token))
@include('contacts.timeline.view_edit_models')
<script>
  jQuery(document).ready(function ($) {
    $("body").popover({
      selector: '[data-toggle="popover3"]',
      trigger:'hover',
      html:true,
      template:'<div class="popover3" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>'
      //template:'<div class="popover3" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>'
    });
  });
	$(".tags").select2({
                        tags:<?php echo $users; ?>

        });
		
</script> 
@stop