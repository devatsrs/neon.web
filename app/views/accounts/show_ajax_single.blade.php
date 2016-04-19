 @if(count($response))
          @if($response->type==2)
          <li id="timeline-{{$key}}" class="count-li">
  <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($response->created_at)); ?>">
              <?php if(date("Y-m-d h:i",strtotime($response->created_at)) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($response->created_at));  ?></span> <span>
    <?php if(date("d",strtotime($response->created_at)) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($response->created_at));} ?>
    </span>
              <?php } ?>
            </time>
  <div class="cbp_tmicon bg-success"> <i class="entypo-mail"></i> </div>
  <div class="cbp_tmlabel"> <a id="show-less-{{$key}}" class="pull-right show-less no-display" onclick="hideDetail({{$key}})"> &#45; </a> <a id="show-more-{{$key}}" onclick="expandTimeLine({{$key}})" class="pull-right show-less"> &#x2B; </a>
              <h2 onclick="expandTimeLine({{$key}})">@if($response->CreatedBy==$current_user_title) You @else {{$response->CreatedBy}}  @endif <span>sent an email to</span> @if($response->EmailTo==$current_user_title) You @else {{$response->EmailTo}}  @endif</h2>
              <div id="hidden-timeline-{{$key}}" class="details no-display">
      <p>CC: {{$response->Cc}}</p>
      <p>BCC: {{$response->Bcc}}</p>
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
					if($key_acttachment==(count($attachments)-1)){
						echo "<a href=".$Attachmenturl.">".$attachments_data['filename']."</a>";
					}else{
						echo "<a href=".$Attachmenturl.">".$attachments_data['filename']."</a>,";
					}
				}
				echo "</p>";
			}			
	  }
	   ?>
      <p>Email : {{$response->Message}}. </p>
    </div>
            </div>
</li>
@elseif($response->type==1)
<li id="timeline-{{$key}}" class="count-li">
  <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($response->created_at)); ?>">
    <?php if(date("Y-m-d h:i",strtotime($response->created_at)) == date('Y-m-d h:i')) { ?>
    <span>Now</span>
    <?php }else{ ?>
    <span><?php echo date("h:i a",strtotime($response->created_at));  ?></span> <span>
    <?php if(date("d",strtotime($response->created_at)) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($response->created_at));} ?>
    </span>
    <?php } ?>
  </time>
  <div class="cbp_tmicon bg-info"> <i class="entypo-tag"></i> </div>
  <div class="cbp_tmlabel"> <a id="show-less-{{$key}}" class="pull-right show-less no-display" onclick="hideDetail({{$key}})"> &#45; </a> <a id="show-more-{{$key}}" onclick="expandTimeLine({{$key}})" class="pull-right show-less"> &#x2B; </a>
    <h2 onclick="expandTimeLine({{$key}})">@if($rows[16]==$current_user_title) You @else $current_user_title  @endif <span>tagged @if($rows[8]==$current_user_title) You @else {{$current_user_title}} @endif in a</span>Task</h2>
    <div id="hidden-timeline-{{$key}}"  class="details no-display">
      <p>Change hospitality weather widget.</p>
    </div>
  </div>
</li>
@elseif($response->type==3)
<li id="timeline-{{$key}}" class="count-li">
  <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($response->created_at)); ?>">
    <?php if(date("Y-m-d h:i",strtotime($response->created_at)) == date('Y-m-d h:i')) { ?>
    <span>Now</span>
    <?php }else{ ?>
    <span><?php echo date("h:i a",strtotime($response->created_at));  ?></span> <span>
    <?php if(date("d",strtotime($response->created_at)) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($response->created_at));} ?>
    </span>
    <?php } ?>
  </time>
  <div class="cbp_tmicon bg-success"><i class="entypo-doc-text"></i></div>
  <div class="cbp_tmlabel"> <a id="show-less-{{$key}}" class="pull-right show-less no-display" onclick="hideDetail({{$key}})"> &#45; </a> <a id="show-more-{{$key}}" onclick="expandTimeLine({{$key}})" class="pull-right show-less"> &#x2B; </a>
    <h2 onclick="expandTimeLine({{$key}})">@if($response->created_by==$current_user_title) You @else {{$response->created_by}}  @endif <span>added a note</span></h2>
    <div id="hidden-timeline-{{$key}}" class="details no-display">
      <p>{{$response->Note}}</p>
    </div>
  </div>
</li>
@endif
          
        @endif 