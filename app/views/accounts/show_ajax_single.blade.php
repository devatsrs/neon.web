 @if(count($response))
          @if($response->type==2)
          <li id="timeline-{{$key}}" row-id="{{$response->LogID}}" class="count-li">
  <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($response->created_at)); ?>">
              <?php if(date("Y-m-d h:i",strtotime($response->created_at)) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($response->created_at));  ?></span> <span>
    <?php if(date("d",strtotime($response->created_at)) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($response->created_at));} ?>
    </span>
              <?php } ?>
            </time>
  <div id_toggle="{{$key}}" class="cbp_tmicon bg-success"> <i class="entypo-mail"></i> </div>
  <div class="cbp_tmlabel">  
              <h2 class="toggle_open" id_toggle="{{$key}}">@if($response->CreatedBy==$current_user_title) You @else {{$response->CreatedBy}}  @endif <span>sent an email to</span> @if($response->EmailTo==$current_user_title) You @else {{$response->EmailTo}}  @endif</h2>
              <div id="hidden-timeline-{{$key}}" class="details no-display">
      @if($response->Cc)<p>CC: {{$response->Cc}}</p>@endif
      @if($response->Bcc)<p>BCC: {{$response->Bcc}}</p>@endif
      <p>Subject: {{$response->Subject}}</p>
      <?php
	  if($response->AttachmentPaths!='')
	  {
    		$attachments = unserialize($response->AttachmentPaths);
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
						echo "<a target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a>";
					}else{
						echo "<a target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a>,";
					}
				}
				echo "</p>";
			}			
	  }
	   ?>
      <p>Message:<br>{{$response->Message}}. </p>
    </div>
            </div>
</li>
@elseif($response->type==1)

          <li id="timeline-{{$key}}" class="count-li">
           <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($response->created_at)); ?>">
              <?php if(date("Y-m-d h:i",strtotime($response->created_at)) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($rows->created_at));  ?></span> <span>
              <?php if(date("d",strtotime($response->created_at)) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($response->created_at));} ?>
              </span>
              <?php } ?>
            </time>
            <div id_toggle="{{$key}}" class="cbp_tmicon bg-info"> <i class="entypo-tag"></i> </div>
            <div class="cbp_tmlabel">
              
              
              <h2 class="toggle_open" id_toggle="{{$key}}">@if($response->created_by==$current_user_title) You @else {{$current_user_title}}  @endif <span>tagged @if($response->Name==$current_user_title) You @else {{$response->Name}} @endif in a</span>Task</h2>
              <div id="hidden-timeline-{{$key}}"  class="details no-display">
                <p>Subject: {{$response->Subject}}</p>
                <p>Assign To: {{$response->Name}}</p>
                <p>priority: {{$response->Priority}}</p>
                <p>Due Date: {{$response->DueDate}}</p>
                <p>Status: {{$response->TaskStatus}}. </p>
                <p>Description: {{$response->Description}} </p>
                 </div>
            </div>
          </li>
@elseif($response->type==3)
<li id="timeline-{{$key}}" row-id="{{$response->NoteID}}" class="count-li">
  <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($response->created_at)); ?>">
    <?php if(date("Y-m-d h:i",strtotime($response->created_at)) == date('Y-m-d h:i')) { ?>
    <span>Now</span>
    <?php }else{ ?>
    <span><?php echo date("h:i a",strtotime($response->created_at));  ?></span> <span>
    <?php if(date("d",strtotime($response->created_at)) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($response->created_at));} ?>
    </span>
    <?php } ?>
  </time>
  <div id_toggle="{{$key}}" class="cbp_tmicon bg-success"><i class="entypo-doc-text"></i></div>
  <div class="cbp_tmlabel">  
    <h2 class="toggle_open" id_toggle="{{$key}}">@if($response->created_by==$current_user_title) You @else {{$response->created_by}}  @endif <span>added a note</span></h2>
    <div id="hidden-timeline-{{$key}}" class="details no-display">
      <p>{{$response->Note}}</p>
    </div>
  </div>
</li>
@endif
          
        @endif 