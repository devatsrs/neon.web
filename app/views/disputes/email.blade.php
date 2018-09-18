<div class="row">
  <div class="col-md-12">
    <div class="form-group">
      <label for="field-4" class="control-label">From</label>
      {{Form::select('email_from',TicketGroups::GetGroupsFrom(),$from,array("class"=>"select22","style"=>"display:block;"))}} </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="form-group">
      <label for="field-4" class="control-label">To</label>
      {{Form::text('Email',$Account->BillingEmail,array("class"=>"form-control"))}} </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="form-group">
      <label for="field-4" class="control-label">Subject</label>
      {{Form::text('Subject',$Subject,array("class"=>" form-control"))}} </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="form-group">
      <label for="field-4" class="control-label">Message</label>
      {{Form::textarea('Message',$Message,array("class"=>"form-control","id"=>"InvoiceMessage","rows"=>8 ))}} <br>      
      <p class="comment-box-options-activity"> <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)"> <i class="entypo-attach"></i> </a> </p>
        <span id="attachment_filename">{{$Attachment}}</span>
        <div class="form-group email_attachment">
            <input type="hidden" value="1" name="email_send" id="email_send"  />
            <input id="emailattachment_sent" type="hidden" name="emailattachment_sent" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"   />
            <input id="info2" type="hidden" name="attachmentsinfo" />
            <span class="file-input-names"></span> </div>
            
      <span style="display:none;">
      <a target="_blank" href="{{URL::to('/invoice/'.$Dispute->DisputeID.'/invoice_preview')}}">View Invoice</a> <br>
       <br>
      <br>
      Best Regards,<br>
      <br>
      {{$CompanyName}}</span> </div>
  </div>
</div>
{{Form::hidden('DisputeID',$Dispute->DisputeID)}}
<form id="emai_attachments_form" class="hidden" name="emai_attachments_form">
    <span class="emai_attachments_span">
    <input type="file" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" name="emailattachment[]" multiple id="filecontrole1">
    </span>
    <input id="info1" type="hidden" name="attachmentsinfo"  />
    <button  class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
  </form>
<script>
var file_count 		  =  		0;
var allow_extensions  = 		{{$response_extensions}};
var emailFileList	  =  		new Array();
var max_file_size_txt =	        '{{$max_file_size}}';
var max_file_size	  =	        '{{str_replace("M","",$max_file_size)}}';

$(document).ready(function ($) {
	
	 $("#send-modal-disputes").find(".select22").select2();
    show_summernote($("#InvoiceMessage"),{});
	
		$('#addTtachment').click(function(){
			 file_count++;                
				$('#filecontrole1').click();
				
            });

	    $(document).on('change','#filecontrole1',function(e){
				e.stopImmediatePropagation();
  				e.preventDefault();		
                var files 			 = e.target.files;				
                var fileText 		 = new Array();
				var file_check		 =	1; 
				var local_array		 =  new Array();
				///////
	        var filesArr = Array.prototype.slice.call(files);
		
			filesArr.forEach(function(f) {     
				var ext_current_file  = f.name.split('.').pop();
				if(allow_extensions.indexOf(ext_current_file.toLowerCase()) > -1 )			
				{         
					var name_file = f.name;
					var index_file = emailFileList.indexOf(f.name); 
					if(index_file >-1 )
					{
						ShowToastr("error",f.name+" file already selected.");							
					}
					else if(bytesToSize(f.size))
					{						
						ShowToastr("error",f.name+" file size exceeds then upload limit ("+max_file_size_txt+"). Please select files again.");						
						file_check = 0;
						 return false;
						
					}else
					{
						//emailFileList.push(f.name);
						local_array.push(f.name);
					}
				}
				else
				{
					ShowToastr("error",ext_current_file+" file type not allowed.");
					
				}
        });
        		if(local_array.length>0 && file_check==1)
				{	 emailFileList = emailFileList.concat(local_array);
   					$('#emai_attachments_form').submit();
				}

            });
			
			$('#emai_attachments_form').submit(function(e) {
	e.stopImmediatePropagation();
    e.preventDefault();

    var formData = new FormData(this);
    var url = 	baseurl + '/user/upload_file';
    $.ajax({
        url: url,  //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            if(response.status =='success'){
                $('.file-input-names').html(response.data.text);
                $('#info1').val(JSON.stringify(response.data.attachmentsinfo));
                $('#info2').val(JSON.stringify(response.data.attachmentsinfo));

            }else{
                toastr.error(response.message, "Error", toastr_opts);
            }
        },
        // Form data
        data: formData,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
    });
});	

	function bytesToSize(filesize) {
  var sizeInMB = (filesize / (1024*1024)).toFixed(2);
  if(sizeInMB>max_file_size)
  {return 1;}else{return 0;}  
}

$(document).on("click",".del_attachment",function(ee){
                var url  =  baseurl + '/user/delete_attachment_file';
                var fileName   =  $(this).attr('del_file_name');
                var attachmentsinfo = $('#info1').val();
                if(!attachmentsinfo){
                    return true;
                }
                attachmentsinfo = jQuery.parseJSON(attachmentsinfo);
                $(this).parent().remove();
                var fileIndex = emailFileList.indexOf(fileName);
                var fileinfo = attachmentsinfo[fileIndex]; 
                emailFileList.splice(fileIndex, 1);
                attachmentsinfo.splice(fileIndex, 1);
                $('#info1').val(JSON.stringify(attachmentsinfo));
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data:{file:fileinfo},
                    async :false,
                    success: function(response) {
                        if(response.status =='success'){

                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    }
                });
            });
	
});

</script>