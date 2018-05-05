<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
  <h4 class="modal-title">{{ucfirst($action_type)." ".cus_lang("CUST_PANEL_PAGE_TICKETS_MODAL_TICKET_EMAIL_ACTION_TITLE")}}</h4>
</div>
<div class="modal-body">
  <div class="row">
    <div class="col-md-12 margin-top">
    <?php if($action_type!='forward'){ ?>
    <div class="form-group">
        <label for="EmailActionTo">* @lang('routes.MAIL_LBL_FROM'):</label>
        <input type="text"  class="form-control" name="email-from" id="email-from" readonly value="<?php 	
		if(isset($AccountEmail))
		{  
			echo $AccountEmail;  
		} 		  
	?>" />
      </div>
      <div class="form-group">   
        <label for="email-from">* @lang('routes.MAIL_LBL_TO'):</label>
          <input type="text"  class="form-control" name="email-to" id="EmailActionTo"  readonly value="<?php echo $GroupEmail	?>" />
        <div class="field-options"> 
        <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#replycc').parent().removeClass('hidden'); $('#replycc').focus();">CC</a>         <!--<a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#replybcc').parent().removeClass('hidden'); $('#replybcc').focus();">BCC</a>--> 
        </div>
      </div>
    <?php }else{ ?>  
    <div class="form-group">
        <label for="email-from">* @lang('routes.MAIL_LBL_FROM'):</label>
        {{ Form::select('email-from', $FromEmails, '', array("class"=>"form-control select2","id"=>"email-from")) }} 
      </div>
      <div class="form-group">
        <label for="EmailActionTo">* @lang('routes.MAIL_LBL_TO'):</label>
        <input type="text"  class="form-control" name="email-to" id="EmailActionTo" value="" />
        <div class="field-options"> 
        <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#replycc').parent().removeClass('hidden'); $('#replycc').focus();">CC</a> 
        <!--<a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#replybcc').parent().removeClass('hidden'); $('#replybcc').focus();">BCC</a>-->
         </div>
      </div>
    <?php } ?>
      
      <div class="form-group hidden">
        <label for="cc">@lang('routes.MAIL_LBL_CC')</label>
        <input type="text" name="cc"  class="form-control tags"  id="replycc" />
      </div>
      <div class="form-group hidden">
        <label for="bcc">@lang('routes.MAIL_LBL_BCC')</label>
        <input type="text" name="bcc"  class="form-control tags"  id="replybcc" />
      </div>      
      <div class="form-group hidden">
        <label for="EmailActionSubject">* @lang('routes.MAIL_LBL_SUBJECT'):</label>
        <input type="text"  class="form-control" name="Subject" id="EmailActionSubject" value="@if($action_type!='forward') RE: @else FW:  @endif {{$response_data['Subject']}}" />
      </div>
      <div class="form-group">
        <label for="EmailActionbody">* @lang('routes.MAIL_LBL_MESSAGE'):</label>
        <textarea name="Message" id="EmailActionbody" class="form-control autogrow editor-email message"   style="height: 175px; overflow: hidden; word-wrap: break-word; resize: none;"> @if($action_type!='forward')<br><br><br> On <?php echo date('M d, Y,',strtotime($response_data['created_at'])).' at '.date('H:i A, ',strtotime($response_data['created_at'])); echo $response_data['Requester']; ?> wrote: <br>
  @else <br><br><br> ---------- Forwarded message ----------<br>
From: <?php $AccountEmail; ?><br>
Subject: <?php $response_data['Subject']; ?>....<br>
Date: <?php echo date('M d, Y,',strtotime($response_data['created_at'])).' at '.date('H:i A, ',strtotime($response_data['created_at'])); ?><br>
@endif{{$conversation}}</textarea>
      </div>
      <p class="comment-box-options-activity"> <a id="addReplyTtachment" class="btn-sm btn-white btn-xs" title="@lang('routes.MESSAGE_ADD_AN_ATTACHMENT')" href="javascript:void(0)"> <i class="entypo-attach"></i> </a> </p>
      <div class="form-group email_attachment">
        <input type="hidden" value="1" name="email_send" id="email_send"  />
        <input id="emailattachment_sent" type="hidden" name="emailattachment_sent" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"   />
        <input id="info4" type="hidden" name="attachmentsinfo" />
        <span class="file-input-names">@if(isset($data['uploadtext']['text'])){{$data['uploadtext']['text']}}@endif</span> </div>
    </div>
  </div>
</div>
<div class="modal-footer">
  <input type="hidden" name="TicketParent" id="TicketParent" value="{{$parent_id}}" />
  <button type="submit" id="EmailAction-edit"  class="save btn btn-primary btn-send-mail btn-sm btn-icon icon-left" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')"> <i class="entypo-floppy"></i> @lang('routes.BUTTON_SEND_CAPTION') </button>
  <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> @lang('routes.BUTTON_CLOSE_CAPTION') </button>
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