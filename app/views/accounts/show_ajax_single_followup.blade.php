 @if(count($response))
          @if($response_data['type']==2)
          <li id="timeline-{{$key}}"  class="count-li">
  <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($response_data['created_at'])); ?>">
              <?php if(date("Y-m-d h:i",strtotime($response_data['created_at'])) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($response_data['created_at']));  ?></span> <span>
    <?php if(date("d",strtotime($response_data['created_at'])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($response_data['created_at']));} ?>
    </span>
              <?php } ?>
            </time>
  <div id_toggle="{{$key}}" class="cbp_tmicon bg-success"> <i class="entypo-mail"></i> </div>
  <div class="cbp_tmlabel normal">  
              <h2 class="toggle_open" id_toggle="{{$key}}">@if($response_data['CreatedBy']==$current_user_title) You @else {{$response_data['CreatedBy']}}  @endif <span>sent an email to</span> @if($response_data['EmailTo']==$current_user_title) You @else {{$response_data['EmailTo']}}  @endif</h2>
              <div id="hidden-timeline-{{$key}}" class="details no-display">
      @if($response_data['Cc'])<p>CC: {{$response_data['Cc']}}</p>@endif
      @if($response_data['Bcc'])<p>BCC: {{$response_data['Bcc']}}</p>@endif
      <p>Subject: {{$response_data['Subject']}}</p>
      <?php
	  if($response_data['AttachmentPaths']!='')
	  {
    		$attachments = unserialize($response_data['AttachmentPaths']);
			if(count($attachments)>0)
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
						$Attachmenturl = Config::get('app.upload_path')."/".$attachments_data['filepath'];
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
      <p>Message:<br>{{$response_data['Message']}}. </p>
    </div>
            </div>

            
</li>
@elseif($response_data['type']==3)
<li id="timeline-{{$key}}" row-id="{{$response_data['NoteID']}}" class="count-li">
  <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($response_data['created_at'])); ?>">
    <?php if(date("Y-m-d h:i",strtotime($response_data['created_at'])) == date('Y-m-d h:i')) { ?>
    <span>Now</span>
    <?php }else{ ?>
    <span><?php echo date("h:i a",strtotime($response_data['created_at']));  ?></span> <span>
    <?php if(date("d",strtotime($response_data['created_at'])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($response_data['created_at']));} ?>
    </span>
    <?php } ?>
  </time>
  <div id_toggle="{{$key}}" class="cbp_tmicon bg-success"><i class="entypo-doc-text"></i></div>
  <div class="cbp_tmlabel normal">  
    <h2 class="toggle_open" id_toggle="{{$key}}">@if($response_data['created_by']==$current_user_title) You @else {{$response_data['created_by']}}  @endif <span>added a note</span></h2>
    <div id="hidden-timeline-{{$key}}" class="details no-display">
      <p>{{$response_data['Note']}}</p>
    </div>
  </div> 
</li>
@endif
<li id="timeline-{{$key+1}}"  class="count-li followup_task">
       <time class="cbp_tmtime" datetime="{{date("Y-m-d h:i",strtotime($response->created_at))}}">
              <?php if(date("Y-m-d h:i",strtotime($response->created_at)) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($rows->created_at));  ?></span> <span>
              <?php if(date("d",strtotime($response->created_at)) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($response->created_at));} ?>
              </span>
              <?php } ?>
            </time>
         <div class="cbp_tmlabel">
              <h2 class="toggle_open" id_toggle="{{$key+1}}">@if($response->created_by==$current_user_title) You @else {{$current_user_title}}  @endif <span>tagged @if($response->Name==$current_user_title) You @else {{$response->Name}} @endif in a</span> follow up Task</h2>
              <div id="hidden-timeline-{{$key+1}}"  class="details no-display">
                <p>Subject: {{$response->Subject}}</p>
                <p>Assign To: {{$response->Name}}</p>
                <p>priority: {{$response->Priority}}</p>
                <p>Due Date: {{$response->DueDate}}</p>
                <p>Status: {{$response->TaskStatus}}. </p>
                <p>Description: {{$response->Description}} </p>
                 </div>
            </div>
</li>

          
        @endif 