@if(count($response)) 
          @if($response->type==Task::Mail)
          <li id="timeline" row-id="{{$response->LogID}}" class="count-li timeline_mail_entry">
  <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($response->created_at)); ?>">
              <?php if(date("Y-m-d h:i",strtotime($response->created_at)) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($response->created_at));  ?></span> <span>
    <?php if(date("Y-m-d",strtotime($response->created_at)) == date('Y-m-d')){echo "Today";}else{echo date("Y-m-d",strtotime($response->created_at));} ?>
    </span>
              <?php } ?>
            </time>
  <div id_toggle="{{$key}}" class="cbp_tmicon bg-gold"> <i class="entypo-mail"></i> </div>
  <div class="cbp_tmlabel normal_tag">  
  <a email_number="{{$response->AccountEmailLogID}}" action_type="forward" class="pull-right edit-deal email_action" title="Forward"><i class="entypo-forward"></i></a>            
         <a email_number="{{$response->AccountEmailLogID}}" action_type="reply-all" class=" pull-right edit-deal email_action" title="Reply All"><i class="entypo-reply-all"></i></a>           
         <a email_number="{{$response->AccountEmailLogID}}" action_type="reply" class="pull-right edit-deal email_action" title="Reply"><i class="entypo-reply"></i></a>
              <h2 class="toggle_open" id_toggle="{{$key}}">@if($response->CreatedBy==$current_user_title) You @else {{$response->CreatedBy}}  @endif <span>sent an email to</span> @if($response->EmailTo==$current_user_title) You @else {{$response->EmailTo}}  @endif <br><p class="mail_subject">Subject: {{$response_data['Subject']}}</p></h2>
              <div id="hidden-timeline-{{$key}}" class="details no-display">
      @if($response->Cc)<p>CC: {{$response->Cc}}</p>@endif
      @if($response->Bcc)<p>BCC: {{$response->Bcc}}</p>@endif
      <?php
	  if($response->AttachmentPaths!='')
	  {
    		$attachments = unserialize($response->AttachmentPaths);
			if(count($attachments)>0 && is_array($attachments))
			{
				 echo "<p>Attachments: ";
				foreach($attachments as $key => $attachments_data)
				{
					//
					 if(is_amazon() == true)
					{
						$Attachmenturl =  AmazonS3::preSignedUrl($attachments_data['filepath']);
					}
					else
					{
						$Attachmenturl = CompanyConfiguration::get('UPLOAD_PATH')."/".$attachments_data['filepath'];
					}			
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
      <p class="mail_message">Message:<br>{{$response->Message}}. </p>
      <p><a data_fetch_id="{{$response->AccountEmailLogID}}" conversations_type="mail"  class="ticket_conversations">View Conversation</a></p>
    </div>
            </div>
</li>
@elseif($response->type==Task::Tasks)
          <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($response->created_at)); ?>">
              <?php if(date("Y-m-d h:i",strtotime($response->created_at)) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($response->created_at));  ?></span> <span>
              <?php if(date("Y-m-d",strtotime($response->created_at)) == date('Y-m-d')){echo "Today";}else{echo date("Y-m-d",strtotime($response->created_at));} ?>
              </span>
              <?php } ?>
            </time>
            <div id_toggle="{{$key}}" class="cbp_tmicon bg-info"> <i class="entypo-tag"></i> </div>
            <div class="cbp_tmlabel normal_tag">
               <a id="edit_task_{{$response->TaskID}}" task-id="{{$response->TaskID}}"  key_id="{{$key}}" class="pull-right edit-deal edit_task_link"><i class="entypo-pencil"></i>&nbsp;</a>
            <a id="delete_task_{{$response->TaskID}}" task-id="{{$response->TaskID}}"  key_id="{{$key}}" class="pull-right edit-deal delete_task_link"><i class="entypo-trash"></i></a>
                  <h2 class="toggle_open" id_toggle="{{$key}}">
                @if($response->Priority=='High')  <i class="edit-deal entypo-record" style="color:#cc2424;font-size:15px;"></i> @endif
                @if($response->created_by==$current_user_title && $response->Name==$current_user_title)<span>You created a @if($response->followup_task) follow up @endif task</span>
                 @elseif ($response->created_by==$current_user_title && $response->Name!=$current_user_title)<span>You assigned task to {{$response->Name}} </span> 
                 @elseif ($response->created_by!=$current_user_title && $response->Name==$current_user_title)<span> {{$response->created_by}} assigned task to  You </span>
                 @else  <span> {{$response->created_by}} assigned task to  {{$response->Name}} </span> 
                 @endif
</h2>
              
              <div id="hidden-timeline-{{$key}}"  class="details no-display">
                <p>Subject: {{$response->Subject}}</p>
                <p>Assigned To: {{$response->Name}}</p>
                <p>priority: {{$response->Priority}}</p>
              @if($response->DueDate!='' && $response->DueDate!='0000-00-00 00:00:00')  <p>Due Date: {{$response->DueDate}}</p>@endif
                <p>Status: {{$response->TaskStatus}}. </p>
                <p>Description: {{$response->Description}} </p>
                 </div>
            </div>          
@elseif($response->type==Task::Note)
<p>{{$response->Note}}</p>
@endif
@endif 