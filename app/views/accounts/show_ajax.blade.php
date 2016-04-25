 @if(count($response)>0)
            <?php  foreach($response as  $rows){
			  $rows = json_decode(json_encode($rows), True); //convert std array to simple array
			   ?>
            @if(isset($rows['Timeline_type']) && $rows['Timeline_type']==2)
            <li id="timeline-{{$key}}" class="count-li">
              <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
                <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
                <span>Now</span>
                <?php }else{ ?>
                <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
                <?php if(date("d",strtotime($rows['created_at'])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
                </span>
                <?php } ?>
              </time>
              <div id_toggle="{{$key}}" class="cbp_tmicon bg-success"> <i class="entypo-mail"></i> </div>
              <div class="cbp_tmlabel">  
                <h2 class="toggle_open" id_toggle="{{$key}}">@if($rows['CreatedBy']==$current_user_title) You @else {{$rows['CreatedBy']}}  @endif <span>sent an email to</span> @if($rows['EmailToName']==$current_user_title) You @else {{$rows['EmailToName']}}  @endif</h2>
                <div id="hidden-timeline-{{$key}}" class="details no-display">
                  @if($rows['EmailCc'])<p>CC: {{$rows['EmailCc']}}</p>@endif
                  @if($rows['EmailBcc'])<p>BCC: {{$rows['EmailBcc']}}</p>@endif
                  <p>Subject: {{$rows['EmailSubject']}}</p>
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
					 if(is_amazon() == true)
					{
						$Attachmenturl =  AmazonS3::preSignedUrl($attachments_data['filepath']);
					}
					else
					{
						$Attachmenturl = Config::get('app.upload_path')."/".$attachments_data['filepath'];
					}			
					if($key_acttachment==(count($attachments)-1)){
						echo "<a target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a>";
					}else{
						echo "<a target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a>,";
					}
					
				}
				echo "</p>";
			}			
	  }	 
	   ?>
                  <p>Messsage:<br>{{$rows['EmailMessage']}}. </p>
                </div>
              </div>
            </li>
            @elseif(isset($rows['Timeline_type']) && $rows['Timeline_type']==1)
            <li id="timeline-{{$key}}" class="count-li">
              <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
                <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
                <span>Now</span>
                <?php }else{ ?>
                <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
                <?php if(date("d",strtotime($rows['created_at'])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
                </span>
                <?php } ?>
              </time>
              <div id_toggle="{{$key}}" class="cbp_tmicon bg-info"> <i class="entypo-tag"></i> </div>
              <div class="cbp_tmlabel">  
                <h2 class="toggle_open" id_toggle="{{$key}}">@if($rows['CreatedBy']==$current_user_title) You @else $current_user_title  @endif <span>tagged @if($rows['TaskName']==$current_user_title) You @else {{$rows['TaskName']}} @endif in a</span>Task</h2>
                <div id="hidden-timeline-{{$key}}"  class="details no-display">
                  <p>Subject: {{$rows['TaskTitle']}}</p>
                  <p>Assign To: {{$rows['TaskName']}}</p>
                  <p>priority: {{$rows['TaskPriority']}}</p>
                  <p>Due Date: {{$rows['DueDate']}}</p>
                  <p>Status: {{$rows['TaskStatus']}}. </p>
                  <p>Description: {{$rows['TaskDescription']}} </p>
                </div>
              </div>
            </li>
            @elseif(isset($rows['Timeline_type']) && $rows['Timeline_type']==3)
            <li id="timeline-{{$key}}" class="count-li">
              <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
                <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
                <span>Now</span>
                <?php }else{ ?>
                <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
                <?php if(date("d",strtotime($rows['created_at'])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
                </span>
                <?php } ?>
              </time>
              <div id_toggle="{{$key}}" class="cbp_tmicon bg-success"><i class="entypo-doc-text"></i></div>
              <div class="cbp_tmlabel"> <!--<a id="show-less-{{$key}}" class="pull-right show-less no-display" onclick="hideDetail({{$key}})"> &#45; </a> <a id="show-more-{{$key}}" onclick="expandTimeLine({{$key}})" class="pull-right show-less"> &#x2B; </a>-->
                <h2 class="toggle_open" id_toggle="{{$key}}">@if($rows['CreatedBy']==$current_user_title) You @else {{$rows['CreatedBy']}}  @endif <span>added a note</span></h2>
                <div id="hidden-timeline-{{$key}}" class="details no-display">
                  <p>{{$rows['Note']}}</p>
                </div>
              </div>
            </li>
            @endif
            <?php $key++;  } ?>
            @endif
