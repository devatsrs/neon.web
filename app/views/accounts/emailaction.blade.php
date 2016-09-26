<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
  <h4 class="modal-title">{{ucfirst($action_type)}} Email</h4>
</div>
<div class="modal-body">
  <div class="row">
    <div class="col-md-12 margin-top">
      <div class="form-group">
        <label for="EmailActionTo">* To:</label>
        <input type="text"  class="form-control" name="email-to" id="EmailActionTo" value=" @if($action_type!='forward') {{$response_data['Emailfrom']}} @endif" />
      </div>
      <div class="form-group">
        <label for="EmailActionSubject">* Subject:</label>
        <input type="text"  class="form-control" name="Subject" id="EmailActionSubject" value="@if($action_type!='forward') RE: @else FW:  @endif {{$response_data['Subject']}}" />
      </div>
      <div class="form-group">
        <label for="EmailActionbody">* Message:</label>        
          <textarea name="Message" id="EmailActionbody" class="form-control autogrow editor-email"   style="height: 175px; overflow: hidden; word-wrap: break-word; resize: none;"> @if($action_type!='forward') <br><br><hr>    @endif{{$response_data['Message']}}</textarea>        
      </div>
         <p class="comment-box-options-activity"> <a id="addReplyTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)"> <i class="entypo-attach"></i> </a> </p>
           <div class="form-group email_attachment">
                  <input type="hidden" value="1" name="email_send" id="email_send"  />
                  
                  <input id="emailattachment_sent" type="hidden" name="emailattachment_sent" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"   />
                  <input id="info4" type="hidden" name="attachmentsinfo" />
                  <span class="file-input-names"></span> </div>
    </div>
  </div>
</div>
<div class="modal-footer">
<input type="hidden" name="EmailParent" id="EmailParent" value="@if($response_data['EmailParent']==0)  {{$response_data['AccountEmailLogID']}} @else {{$response_data['EmailParent']}}  @endif " />
  <button type="submit" id="EmailAction-edit"  class="save btn btn-primary btn-send-mail btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Send </button>
  <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
</div>
