<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
  <h4 class="modal-title">{{ucfirst($action_type)}} Email</h4>
</div>
<div class="modal-body">
  <div class="row">
    <div class="col-md-12 margin-top">
    <div class="form-Group" style="margin-bottom: 15px;">
                 <label >From</label>
                  {{Form::select('email-from',$FromEmails,'',array("class"=>"select2"))}}
         </div>
      <div class="form-group">
        <label for="EmailActionTo">* To:</label>
        <input type="text"  class="form-control" name="email-to" id="EmailActionTo" value="<?php 
	if($action_type!='forward')
	{
		if($response_data['EmailCall']=='Send')
		{
			echo $response_data['EmailTo'];
		}
		else
		{ 
			if(isset($parent_data->EmailTo))
			{  
				echo $parent_data->EmailTo;  
			}  
		}  
	} ?>" />
        <div class="field-options"> 
        <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#replycc').parent().removeClass('hidden'); $('#replycc').focus();">CC</a> 
        <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#replybcc').parent().removeClass('hidden'); $('#replybcc').focus();">BCC</a>
         </div>
      </div>
      <div class="form-group hidden">
        <label for="cc">CC</label>
        <input type="text" name="cc"  class="form-control tags"  id="replycc" />
      </div>
      <div class="form-group hidden">
        <label for="bcc">BCC</label>
        <input type="text" name="bcc"  class="form-control tags"  id="replybcc" />
      </div>
      <div class="form-Group" style="margin-bottom: 15px;">
                  <label >Email Template</label>
                  {{Form::select('email_template',$emailTemplates,'',array("class"=>"select2 email_template","parent_box"=>"EmailAction_box"))}} </div>
      <div class="form-group">
        <label for="EmailActionSubject">* Subject:</label>
        <input type="text"  class="form-control" name="Subject" id="EmailActionSubject" value="@if($action_type!='forward') RE: @else FW:  @endif {{$response_data['Subject']}}" />
      </div>
      <div class="form-group">
        <label for="EmailActionbody">* Message:</label>
        <textarea name="Message" id="EmailActionbody" class="form-control autogrow editor-email message"   style="height: 175px; overflow: hidden; word-wrap: break-word; resize: none;"> @if($action_type!='forward')<br><br><br> On <?php echo date('M d, Y,',strtotime($response_data['created_at'])).' at '.date('H:i A, ',strtotime($response_data['created_at'])); if($response_data['EmailCall']=='Send'){echo $AccountName."(".$AccountEmail.")";}else{echo $response_data['Emailfrom'];} ?> wrote: <br>
  @else <br><br><br> ---------- Forwarded message ----------<br>
From: <?php $AccountName."(".$AccountEmail.")"; ?><br>
Subject: <?php $response_data['Subject']; ?>....<br>
Date: <?php echo date('M d, Y,',strtotime($response_data['created_at'])).' at '.date('H:i A, ',strtotime($response_data['created_at'])); ?><br>
To: <?php if($response_data['EmailCall']=='Send'){echo $response_data['EmailTo'];}else{echo $response_data['Emailfrom'];} ?><br><br> @endif{{$response_data['Message']}}</textarea>
      </div>
      <p class="comment-box-options-activity"> <a id="addReplyTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)"> <i class="entypo-attach"></i> </a> </p>
      <div class="form-group email_attachment">
        <input type="hidden" value="1" name="email_send" id="email_send"  />
        <input id="emailattachment_sent" type="hidden" name="emailattachment_sent" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"   />
        <input id="info4" type="hidden" name="attachmentsinfo" />
        <span class="file-input-names">@if(isset($data['uploadtext']['text'])){{$data['uploadtext']['text']}}@endif</span> </div>
    </div>
  </div>
</div>
<div class="modal-footer">
  <input type="hidden" name="EmailParent" id="EmailParent" value="@if($response_data['EmailParent']==0)  {{$response_data['AccountEmailLogID']}} @else {{$response_data['EmailParent']}}  @endif " />
  <button type="submit" id="EmailAction-edit"  class="save btn btn-primary btn-send-mail btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Send </button>
  <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
</div>
<script>
@if(isset($data['uploadtext']['attachmentsinfo']))
	var img_array		   =    '{{$data['uploadtext']['attachmentsinfo']}}';
	
	$('#info3').val(img_array);
    $('#info4').val(img_array);
	var img_array_final = jQuery.parseJSON(img_array);
	for (var i = 0, len = img_array_final.length; i < len; ++i) {
   	 //emailFileList.push(img_array_final[i].filename);
	 emailFileListReply.push(img_array_final[i].filename);	
 }
	@endif
</script>