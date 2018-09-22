<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
  <h4 class="modal-title">{{ucfirst($action_type)}} Ticket</h4>
</div>
<div class="modal-body">
  <div class="row">
    <div class="col-md-12 margin-top">
    <div class="form-group">
        <label for="email-from">* From:</label>
        {{ Form::select('email-from', $FromEmails, $GroupEmail, array("class"=>"form-control select2","id"=>"email-from")) }} 
      </div>
      <div class="form-group">
        <label for="EmailActionTo">* To:</label>
        <input type="text"  class="form-control emailaddresses" name="email-to" id="EmailActionTo" value="<?php 
	if($action_type!='forward')
	{
		if(isset($AccountEmail))
		{  
			echo $AccountEmail;  
		}  
		  
	} ?>" />
        <div class="field-options"> 
        @if(empty($cc))
        <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#replycc').parent().removeClass('hidden'); $('#replycc').focus();">CC</a> 
        <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#replybcc').parent().removeClass('hidden'); $('#replybcc').focus();">BCC</a> 
        @endif      
         </div>
      </div>
      <div class="form-group @if(empty($cc)) hidden @endif">
        <label for="cc">CC</label>
        <input type="text" name="cc"  class="form-control emailaddresses tags"  value="{{$cc}}" id="replycc" />
      </div>   
      <div class="form-group @if(empty($bcc)) hidden @endif">
        <label for="cc">BCC</label>
        <input type="text" name="bcc"  class="form-control emailaddresses tags"  value="{{$bcc}}" id="replybcc" />
      </div>            
        <div class="form-group">
            <label for="bcc">Email Templates:</label>
            {{Form::select('email_template',$emailTemplates,'',array("class"=>"select2 email_template","parent_box"=>"mail-compose"))}}
        </div>
      <div class="form-group">
        <label for="EmailActionSubject">* Subject:</label>

        {{--<input type="text"  class="form-control" name="Subject" id="EmailActionSubject" value="@if($action_type!='forward') RE: @else FW:  @endif {{htmlentities(imap_mime_header_decode($response_data['Subject'])[0]->text)}}" />--}}

        <input type="text"  class="form-control" name="Subject" id="EmailActionSubject" value="@if($action_type!='forward') RE: @else FW:  @endif {{htmlentities(emailHeaderDecode($response_data['Subject']))}}" />

      </div>
      <div class="form-group">
        <label for="EmailActionbody">* Message:</label>
        <textarea name="Message" id="EmailActionbody" class="form-control autogrow editor-email message"   style="height: 175px; overflow: hidden; word-wrap: break-word; resize: none;">
            @if(!empty($EmailFooter))
            {{"<br><br><br>".$EmailFooter}}
            @endif
            @if($action_type!='forward')
                <br><br><br> On <?php echo date('M d, Y,',strtotime($response_data['created_at'])).' at '.date('H:i A, ',strtotime($response_data['created_at'])); echo $response_data['Requester']; ?> wrote: <br>
            @else
                <br><br><br> ---------- Forwarded message ----------<br>
                From: <?php $AccountEmail; ?><br>
                Subject: <?php echo $response_data['Subject']; ?>....<br>
                Date: <?php echo date('M d, Y,',strtotime($response_data['created_at'])).' at '.date('H:i A, ',strtotime($response_data['created_at'])); ?><br>
            @endif
                {{$conversation}}
        </textarea>
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
  <input type="hidden" name="TicketParent" id="TicketParent" value="{{$parent_id}}" />
    <div class="btn-group">
        <button type="button" class="btn btn-primary TicketStatus btn-sm btn-send-mail" data-status-id="">Send</button>
        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split btn-sm btn-send-mail" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
            @foreach($ticketStatusArr as $statusId=>$status)
                <li> <a href="javascript:;" class="TicketStatus" data-status-id="{{$statusId}}"> {{$status}}</a> </li>
            @endforeach
        </ul>
    </div>
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

	$(".TicketStatus").click(function () {
        var TicketStatus = $(this).attr("data-status-id");
        sumbitReplyTicket(TicketStatus);
    });
	
	
</script>