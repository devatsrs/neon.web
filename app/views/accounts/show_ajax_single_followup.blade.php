 @if(count($response))
          @if($response_data['type']==Task::Mail)
          <li id="timeline-{{$key}}"  class="count-li timeline_task_entry">
  <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($response_data['created_at'])); ?>">
              <?php if(date("Y-m-d h:i",strtotime($response_data['created_at'])) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($response_data['created_at']));  ?></span> <span>
    <?php if(date("Y-m-d",strtotime($response_data['created_at'])) == date('Y-m-d')){echo "Today";}else{echo date("Y-m-d",strtotime($response_data['created_at']));} ?>
    </span>
              <?php } ?>
            </time>
  <div id_toggle="{{$key}}" class="cbp_tmicon bg-gold"> <i class="entypo-mail"></i> </div>
  <div class="cbp_tmlabel normal_tag">  
  <a email_number="{{$response_data['AccountEmailLogID']}}" action_type="forward" class="pull-right edit-deal email_action" title="Forward"><i class="entypo-forward"></i></a>            
         <a email_number="{{$response_data['AccountEmailLogID']}}" action_type="reply-all" class=" pull-right edit-deal email_action" title="Reply All"><i class="entypo-reply-all"></i></a>           
         <a email_number="{{$response_data['AccountEmailLogID']}}" action_type="reply" class="pull-right edit-deal email_action" title="Reply"><i class="entypo-reply"></i></a>
              <h2 class="toggle_open" id_toggle="{{$key}}">@if($response_data['CreatedBy']==$current_user_title) You @else {{$response_data['CreatedBy']}}  @endif <span>sent an email to</span> @if($response_data['EmailTo']==$current_user_title) You @else {{$response_data['EmailTo']}}  @endif <br>
 <p class="mail_subject">Subject: {{$response_data['Subject']}}</p></h2>
              <div id="hidden-timeline-{{$key}}" class="details no-display">
      @if($response_data['Cc'])<p>CC: {{$response_data['Cc']}}</p>@endif
      @if($response_data['Bcc'])<p>BCC: {{$response_data['Bcc']}}</p>@endif
      <?php
	  if($response_data['AttachmentPaths']!='')
	  {
    		$attachments = unserialize($response_data['AttachmentPaths']);
			if(count($attachments)>0 && is_array($attachments))
			{
				 echo "<p>Attachments: ";
				foreach($attachments as $key => $attachments_data)
				{
					//
					/* if(is_amazon() == true)
					{
						$Attachmenturl =  AmazonS3::preSignedUrl($attachments_data['filepath']);
					}
					else
					{
						$Attachmenturl = CompanyConfiguration::get('UPLOAD_PATH')."/".$attachments_data['filepath'];
					}*/
                    $Attachmenturl = URL::to('emails/'.$response_data['AccountEmailLogID'].'/getattachment/'.$key);
					if($key==(count($attachments)-1)){
						echo "<a target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a><br><br>";
					}else{
						echo "<a target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a><br>";
					}
				}
				echo "</p>";
			}			
	  }
	   ?>
      <p class="mail_message">Message:<br>{{$response_data['Message']}}. </p>
      <p><a data_fetch_id="{{$response_data['AccountEmailLogID']}}" conversations_type="mail"  class="ticket_conversations">View Conversation</a></p>
    </div>
            </div>

            
</li>
@elseif($response_data['type']==Task::Note)
<li id="timeline-{{$key}}" row-id="{{$response_data['NoteID']}}" class="count-li timeline_note_entry">
  <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($response_data['created_at'])); ?>">
    <?php if(date("Y-m-d h:i",strtotime($response_data['created_at'])) == date('Y-m-d h:i')) { ?>
    <span>Now</span>
    <?php }else{ ?>
    <span><?php echo date("h:i a",strtotime($response_data['created_at']));  ?></span> <span>
    <?php if(date("Y-m-d",strtotime($response_data['created_at'])) == date('Y-m-d')){echo "Today";}else{echo date("Y-m-d",strtotime($response_data['created_at']));} ?>
    </span>
    <?php } ?>
  </time>
  <div id_toggle="{{$key}}" class="cbp_tmicon bg-success"><i class="entypo-doc-text"></i></div>
  <?php
		$note_type 	= 	isset($response_data['NoteID'])?'NoteID':'ContactNote'; 
		$noteID		= 	isset($response_data['NoteID'])?$response_data['NoteID']:$response_data['ContactNoteID'];
	?>
  <div class="cbp_tmlabel normal_tag">  
                <a id="edit_note_{{$noteID}}" note_type="{{$note_type}}" note-id="{{$noteID}}"  key_id="{{$key}}" class="pull-right edit-deal edit_note_link"><i class="entypo-pencil"></i>&nbsp;</a>
            <a id="delete_note_{{$noteID}}" note_type="{{$note_type}}" note-id="{{$noteID}}"  key_id="{{$key}}" class="pull-right edit-deal delete_note_link"><i class="entypo-trash"></i></a>
    <h2 class="toggle_open" id_toggle="{{$key}}">@if($response_data['created_by']==$current_user_title) You @else {{$response_data['created_by']}}  @endif <span>added a note</span></h2>
    <div id="hidden-timeline-{{$key}}" class="details no-display">
      <p>{{$response_data['Note']}}</p>
    </div>
  </div> 
</li>
@endif
<li id="timeline-{{$key+1}}"  class="count-li timeline_task_entry followup_task">
       <time class="cbp_tmtime" datetime="{{date("Y-m-d h:i",strtotime($response->created_at))}}">
              <?php if(date("Y-m-d h:i",strtotime($response->created_at)) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($response->created_at));  ?></span> <span>
              <?php if(date("Y-m-d",strtotime($response->created_at)) == date('Y-m-d')){echo "Today";}else{echo date("Y-m-d",strtotime($response->created_at));} ?>
              </span>
              <?php } ?>
            </time>
            <div id_toggle="{{$key+1}}" class="cbp_tmicon bg-info"> <i class="entypo-tag"></i> </div>
         <div class="cbp_tmlabel">
          <a id="edit_task_{{$response->TaskID}}" task-id="{{$response->TaskID}}"  key_id="{{$key+1}}" class="pull-right edit-deal edit_task_link"><i class="entypo-pencil"></i>&nbsp;</a>
            <a id="delete_task_{{$response->TaskID}}" task-id="{{$response->TaskID}}"  key_id="{{$key+1}}" class="pull-right edit-deal delete_task_link"><i class="entypo-trash"></i></a>
                 <h2 class="toggle_open" id_toggle="{{$key+1}}">
                 @if($response->Priority=='High')  <i class="edit-deal entypo-record" style="color:#cc2424;font-size:15px;"></i> @endif
                 
                @if($response->created_by==$current_user_title && $response->Name==$current_user_title)<span>You created a follow up task</span>
                 @elseif ($response->created_by==$current_user_title && $response->Name!=$current_user_title)<span>You assigned follow up task to {{$response->Name}} </span> 
                 @elseif ($response->created_by!=$current_user_title && $response->Name==$current_user_title)<span> {{$response->created_by}} assigned follow up task to  you</span>
                 @else  <span> {{$response->created_by}} assigned follow up task to  {{$response->Name}} </span> 
                 @endif
</h2>
              
              
              <div id="hidden-timeline-{{$key+1}}"  class="details no-display">
                <p>Subject: {{$response->Subject}}</p>
                <p>Assigned To: {{$response->Name}}</p>
                <p>priority: {{$response->Priority}}</p>
                @if($response->DueDate!=''  && $response->DueDate!='0000-00-00 00:00:00')  <p>Due Date: {{$response->DueDate}}</p>@endif
                <p>Status: {{$response->TaskStatus}}. </p>
                <p>Description: {{$response->Description}} </p>
                 </div>
            </div>
</li>

          
        @endif 