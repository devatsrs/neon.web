@if(isset($EmailMessage) && !empty($EmailMessage) )
<div class="row">
  <div class="col-md-12">
    <div class="form-group">
      <label for="field-1" class="control-label col-sm-12 text-left bold">Subject:</label>
      <div class="col-sm-12">{{$Emaildata->Subject}}</div>
    </div>
    @if( isset($EmailMessage->AccountID) && !empty($EmailMessage->AccountID))
    <div class="form-group">
      <label for="field-1" class="control-label col-sm-12 bold">Account Name</label>
      <div class="col-sm-12">{{Account::getCompanyNameByID($EmailMessage->AccountID)}}</div>
    </div>
    @endif        
    @if(isset($Options->AccountID) && is_array($Options->AccountID))
    @foreach($Options->AccountID as $row=>$AccountID)
    <?php if((int)$AccountID){$Accountname[] = Account::getCompanyNameByID((int)$AccountID);} ?>
    @endforeach
    <div class="form-group" style="max-height: 200px; overflow-y: auto; overflow-x: hidden;">
      <label for="field-1" class="control-label col-sm-12 bold">Account Names</label>
      <div class="col-sm-12">{{implode(',<br>
        ',$Accountname)}}</div>
    </div>
    @endif
    
    @if($EmailMessage->MatchType)
    <div class="form-group">
      <label for="field-1" class="control-label col-sm-12 bold">MatchType</label>
      <div class="col-sm-12">{{$EmailMessage->MatchType}}</div>
    </div>
    @endif
    @if($EmailMessage->MatchID)
     <div class="form-group">
      <label for="field-1" class="control-label col-sm-12 bold">MatchID</label>
      <div class="col-sm-12">{{$EmailMessage->MatchID}}</div>
    </div>
    @endif
    
    <div class="form-group">
      <label for="field-1" class="control-label col-sm-12 bold">From</label>
      <div class="col-sm-12">{{$Emaildata->Emailfrom}}</div>
    </div>    
    <?php
	$attachments = unserialize($Emaildata->AttachmentPaths);
		
	if(count($attachments)>0 && is_array($attachments))
	{
	 ?>
    <div class="form-group">
      <label for="field-1" class="control-label col-sm-12 bold">Attachments</label>
      <div class="col-sm-12">
        <?php
			
				 echo "<p><span class='underline'>Attachments</span><br>";
				foreach($attachments as $key_acttachment => $attachments_data)
				{
					//
					 if(is_amazon() == true)
					{
						$Attachmenturl =  AmazonS3::preSignedUrl($attachments_data['filepath']);
					}
					else
					{
						$Attachmenturl = CompanyConfiguration::get('UPLOAD_PATH')."/".$attachments_data['filepath'];
					}
					 $Attachmenturl = URL::to('emails/'.$rows['AccountEmailLogID'].'/getreplyattachment/'.$key_acttachment);
					if($key_acttachment==(count($attachments)-1)){
						echo "<a class='underline' target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a>";
					}else{
						echo "<a class='underline' target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a>,";
					}
					
				}
				echo "</p>";
						
	   ?>      
      </div>
    </div>    
    <?php } ?>
    <div class="form-group">
      <label for="field-1" class="control-label col-sm-12 bold">Message</label>
      <div class="col-sm-12">{{$Emaildata->Message}}</div>
    </div>
     <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Date Created</label>
            <div class="col-sm-12">{{$EmailMessage->created_at}}</div>
        </div>
        <div class="form-group">
            <label for="field-1" class="control-label col-sm-12 bold">Created By</label>
            <div class="col-sm-12">{{$EmailMessage->CreatedBy}}</div>
        </div>
    
  </div>
</div>
@endif