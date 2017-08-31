@if(isset($response['data'])  && count($response['data'])>0)
<div class="col-md-6">
  <h4>Total Conversation(s): {{count($response['data'])}}</h4>
</div>
<div class="col-md-12 perfect-scrollbar" style="max-height:600px; overflow-y:auto">
  <div class="panel panel-primary">
    <div class="panel-body no-padding"> 
      <!-- List of Comments -->
      <ul class="comments-list">
        @if($data['conversations_type']=='ticket')
        @foreach($response['data'] as $rows)
        <li class="countComments" id="comment-1">
          <div class="comment-details">
            <p class="comment-text"> {{nl2br($rows['body_text'])}} </p>
            <div class="comment-footer">
              <div class="comment-time"> {{\Carbon\Carbon::createFromTimeStamp(strtotime($rows['created_at']))->diffForHumans()}} </div>
            </div>
          </div>
        </li>
        @endforeach 
        @elseif($data['conversations_type']=='mail')
         @foreach($response['data'] as $rows)
        <li class="countComments replyboxemail" id="comment-1">
          <div class="comment-details">
            <p class="comment-text">
	  <div class="comment-time pull-right">
         <a email_number="{{$rows['AccountEmailLogID']}}" action_type="reply" class="email_action" title="Reply"><i class="entypo-reply"></i></a>
         <a email_number="{{$rows['AccountEmailLogID']}}" action_type="reply-all" class="email_action" title="Reply All"><i class="entypo-reply-all"></i></a>
         <a email_number="{{$rows['AccountEmailLogID']}}" action_type="forward" class="email_action" title="Forward"><i class="entypo-forward"></i></a>
       </div>
            	<p><h3>{{$rows['Subject']}}</h3> </p>
                @if($rows['EmailCall']==Messages::Sent &&  $rows['EmailTo']!='')<p>To: {{$rows['EmailTo']}}</p> @endif
                @if($rows['EmailCall']==Messages::Received && $rows['Emailfrom']!='')<p>From: {{$rows['Emailfrom']}}</p> @endif
            	<div class="replyboxhidden">
             <p>Message:<br> {{$rows['Message']}}</p>
              </p>
              </div>
            <div class="comment-footer">
            <div class="replyboxhidden">
             <div class="comment-time pull-left"> 
        <?php
	  if($rows['AttachmentPaths']!='')
	  {
    		$attachments = unserialize($rows['AttachmentPaths']);
			
			if(count($attachments)>0 && is_array($attachments))
			{
				 echo "<p><span class='underline'>Attachments</span><br>";
				foreach($attachments as $key_acttachment => $attachments_data)
				{
					//
					/*if(is_amazon() == true)
					{
						$Attachmenturl =  AmazonS3::preSignedUrl($attachments_data['filepath']);
					}
					else
					{
						$Attachmenturl = CompanyConfiguration::get('UPLOAD_PATH')."/".$attachments_data['filepath'];
					}*/
					 $Attachmenturl = URL::to('emails/'.$rows['AccountEmailLogID'].'/getreplyattachment/'.$key_acttachment);
					if($key_acttachment==(count($attachments)-1)){
						echo "<a class='underline' target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a>";
					}else{
						echo "<a class='underline' target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a><br>";
					}
					
				}
				echo "</p>";
			}			
	  }	 
	   ?> </div>
       			</div>
       
              <div class="comment-time pull-right"> {{\Carbon\Carbon::createFromTimeStamp(strtotime($rows['created_at']))->diffForHumans()}} </div>
            </div>
          </div>
        </li>
        @endforeach 
        @endif
      </ul>
    </div>
  </div>
</div>
@else
@if(isset($response['message']))
<h3 style="text-align:center;">{{ $response['message'] }}</h3>
@endif 
@endif 