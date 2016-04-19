 @if(count($response))
<?php  foreach($response as $rows){ ?>
@if($rows[0]==2)
<li id="timeline-{{$key}}" class="count-li">
  <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows[18])); ?>">
    <?php if(date("Y-m-d h:i",strtotime($rows[18])) == date('Y-m-d h:i')) { ?>
    <span>Now</span>
    <?php }else{ ?>
    <span><?php echo date("h:i a",strtotime($rows[18]));  ?></span> <span>
    <?php if(date("d",strtotime($rows[18])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows[18]));} ?>
    </span>
    <?php } ?>
  </time>
  <div class="cbp_tmicon bg-success"> <i class="entypo-mail"></i> </div>
  <div class="cbp_tmlabel"> <a id="show-less-{{$key}}" class="pull-right show-less no-display" onclick="hideDetail({{$key}})"> &#45; </a> <a id="show-more-{{$key}}" onclick="expandTimeLine({{$key}})" class="pull-right show-less"> &#x2B; </a>
    <h2 onclick="expandTimeLine({{$key}})">@if($rows[17]==$current_user_title) You @else {{$rows[17]}}  @endif <span>sent an email to</span> @if($rows[8]==$current_user_title) You @else {{$rows[8]}}  @endif</h2>
    <div id="hidden-timeline-{{$key}}" class="details no-display">
      <p>CC: {{$rows[11]}}</p>
      <p>BCC: {{$rows[12]}}</p>
      <p>Subject: {{$rows[9]}}</p>
      <?php
	  if($rows[13]!='')
	  {
    		$attachments = unserialize($rows[13]);
			if(count($attachments)>0)
			{
				 echo "<p>Attachments: ";
				foreach($attachments as $key_acttachment => $attachments_data)
				{echo $key_acttachment; exit;
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
      <p>Email : {{$rows[10]}}. </p>
    </div>
  </div>
</li>
@elseif($rows[0]==1)
<li id="timeline-{{$key}}" class="count-li">
  <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows[18])); ?>">
    <?php if(date("Y-m-d h:i",strtotime($rows[18])) == date('Y-m-d h:i')) { ?>
    <span>Now</span>
    <?php }else{ ?>
    <span><?php echo date("h:i a",strtotime($rows[18]));  ?></span> <span>
    <?php if(date("d",strtotime($rows[18])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows[18]));} ?>
    </span>
    <?php } ?>
  </time>
  <div class="cbp_tmicon bg-info"> <i class="entypo-tag"></i> </div>
  <div class="cbp_tmlabel"> <a id="show-less-{{$key}}" class="pull-right show-less no-display" onclick="hideDetail({{$key}})"> &#45; </a> <a id="show-more-{{$key}}" onclick="expandTimeLine({{$key}})" class="pull-right show-less"> &#x2B; </a>
    <h2 onclick="expandTimeLine({{$key}})">@if($rows[17]==$current_user_title) You @else $current_user_title  @endif <span>tagged @if($rows[8]==$current_user_title) You @else {{$current_user_title}} @endif in a</span>Task</h2>
    <div id="hidden-timeline-{{$key}}"  class="details no-display">
      <p>Change hospitality weather widget.</p>
    </div>
  </div>
</li>
@elseif($rows[0]==3)
<li id="timeline-{{$key}}" class="count-li">
  <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows[18])); ?>">
    <?php if(date("Y-m-d h:i",strtotime($rows[18])) == date('Y-m-d h:i')) { ?>
    <span>Now</span>
    <?php }else{ ?>
    <span><?php echo date("h:i a",strtotime($rows[18]));  ?></span> <span>
    <?php if(date("d",strtotime($rows[18])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows[18]));} ?>
    </span>
    <?php } ?>
  </time>
  <div class="cbp_tmicon bg-success"><i class="entypo-doc-text"></i></div>
  <div class="cbp_tmlabel"> <a id="show-less-{{$key}}" class="pull-right show-less no-display" onclick="hideDetail({{$key}})"> &#45; </a> <a id="show-more-{{$key}}" onclick="expandTimeLine({{$key}})" class="pull-right show-less"> &#x2B; </a>
    <h2 onclick="expandTimeLine({{$key}})">@if($rows[17]==$current_user_title) You @else {{$rows[17]}}  @endif <span>added a note</span></h2>
    <div id="hidden-timeline-{{$key}}" class="details no-display">
      <p>{{$rows[16]}}</p>
    </div>
  </div>
</li>
@endif
<?php $key++;  } ?>
@endif 