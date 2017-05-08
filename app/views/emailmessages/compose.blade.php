@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Emails</strong> </li>
</ol>
<h3>Emails</h3>
@include('includes.errors')
@include('includes.success')
<div class="mail-env"> 
  <!-- compose new email button -->
  <div class="mail-sidebar-row visible-xs"> <a href="mailbox-compose.html" class="btn btn-success btn-icon btn-block"> Compose Mail <i class="entypo-pencil"></i>&nbsp;</a> </div>
  
  <!-- Mail Body -->
  <div class="mail-body">
    <div class="mail-header"> 
      <!-- title -->
      <div class="mail-title"> Compose Mail <i class="entypo-pencil"></i> </div>
      
      <!-- links -->
      <div class="mail-links">        
        <button type="submit" data-loading-text="Loading..." href="#" submit_value="{{Messages::Draft}}" class="btn btn-default btn-icon submit_btn"> Draft <i class="entypo-tag"></i></button>
        <button type="submit" data-loading-text="Loading..." submit_value="{{Messages::Sent}}" class="btn btn-success submit_btn btn-icon"> Send <i class="entypo-mail"></i> </button>
      </div>
    </div>
    <div class="mail-compose">
      <form  id="MailBoxCompose" name="MailBoxCompose">
        <div class="form-group">
          <label for="to">To:</label>
          <input class="form-control useremails" id="email-to" name="email-to" value="@if($Emaildata){{$Emaildata->EmailTo}}@endif" type="text" >
          <div class="field-options">
						<a href="javascript:;" onclick="$(this).hide(); $('#cc').parent().removeClass('hidden'); $('#cc').focus();">CC</a>
						<a href="javascript:;" onclick="$(this).hide(); $('#bcc').parent().removeClass('hidden'); $('#bcc').focus();">BCC</a>
					</div>
        </div>
        <div class="form-group hidden">
          <label for="cc">CC:</label>
          <input type="text" class="form-control useremails" id="cc" name="cc" value="@if($Emaildata){{$Emaildata->Cc}}@endif" tabindex="2" />
        </div>
        <div class="form-group hidden">
          <label for="bcc">BCC:</label>
          <input type="text" class="form-control useremails" id="bcc" name="bcc" value="@if($Emaildata){{$Emaildata->Bcc}}@endif" tabindex="2" />
        </div>
        <div class="form-group">
          <label for="subject">Subject:</label>
          <input type="text" class="form-control subject" id="subject" name="Subject" value="@if($Emaildata){{$Emaildata->Subject}}@endif" tabindex="1" />
        </div>
        <div class="compose-message-editor">
          <textarea id="Message" name="Message" class="form-control wysihtml5box" >@if($Emaildata){{$Emaildata->Message}}@endif</textarea>
        </div>
        <p class="comment-box-options-activity"> <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)"> <i class="entypo-attach"></i> </a> </p>
        <div class="form-group email_attachment">
          <input type="hidden" value="1" name="email_send" id="email_send"  />
          <input id="emailattachment_sent" type="hidden" name="emailattachment_sent" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"   />
          <input id="info2" type="hidden" name="attachmentsinfo"  />
          <span class="file-input-names">@if(isset($data['uploadtext'])){{$data['uploadtext']['text']}}@endif</span> </div>
        <input type="submit" class="hidden" value=""  />
        <input type="hidden" class="EmailCall" value="{{Messages::Sent}}" name="EmailCall" />
        <input type="hidden" value="@if($Emaildata){{$Emaildata->AccountEmailLogID}}@endif" id="AccountEmailLogID" name="AccountEmailLogID" />
      </form>
    </div>
  </div>
  <!-- Sidebar --> 
  @include("emailmessages.mail_sidebar")
  <form id="emai_attachments_form" class="hidden" name="emai_attachments_form">
    <span class="emai_attachments_span">
    <input type="file" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" name="emailattachment[]" multiple id="filecontrole1">
    </span>
    <input  hidden="" name="token_attachment" value="{{$random_token}}" />
    <input id="info1" type="hidden" name="attachmentsinfo"  />
    <button  class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
  </form>
</div>
<style>
.mail-env .mail-body .mail-header .mail-title{width:70% !important;}
.mail-env .mail-body .mail-header .mail-search, .mail-env .mail-body .mail-header .mail-links{width:30% !important;}
.select2-container,#s2id_email-to{padding-left:30px !important;}
</style>
<script>
var editor_options 	 	=  		{};
$(document).ready(function(e) {
	 $('.useremails').select2({
            tags:{{$AllEmails}}
        });
	
	var ajax_url 		   = 	baseurl+'/emailmessages/SendMail';
	var file_count 		   =  	0;
	var allow_extensions   = 	{{$response_extensions}};
	var emailFileList	   =  	new Array();
	var max_file_size_txt  =	'{{$max_file_size}}';
	var max_file_size	   =	'{{str_replace("M","",$max_file_size)}}';
	@if(isset($data['uploadtext']['attachmentsinfo']))
	var img_array		   =    '{{$data['uploadtext']['attachmentsinfo']}}';
	
	$('#info1').val(img_array);
    $('#info2').val(img_array);
	var img_array_final = jQuery.parseJSON(img_array);
	for (var i = 0, len = img_array_final.length; i < len; ++i) {
   	 emailFileList.push(img_array_final[i].filename);	
 }
	@endif
	

	$('.submit_btn').click(function(e) {
        $('.EmailCall').val($(this).attr('submit_value'));
		$('.submit_btn').addClass('disabled');
		$('#MailBoxCompose').submit();
    });
	
	$(document).on('submit','#MailBoxCompose',function(e){		 
	//$('.btn').button('loading');
	
		e.stopImmediatePropagation();
		e.preventDefault();
		var formData = new FormData($(this)[0]);
		 $.ajax({
				url: ajax_url,
				type: 'POST',
				dataType: 'json',
				async :false,
				cache: false,
                contentType: false,
                processData: false,
				data:formData,
				success: function(response) {
				   if(response.status =='success'){
					   if($('#AccountEmailLogID').val()>0){
						window.location.href = "{{URL::to('/')}}/emailmessages/draft";  
						}
						ShowToastr("success",response.message); 			
						document.getElementById('MailBoxCompose').reset();		
						$('.select2-search-choice-close').click();
						$('.mailinboxcountersidebar').html(response.unreadinbox);
						$('.maildraftcountersidebar').html(response.totaldraft);						
					}else{
						toastr.error(response.message, "Error", toastr_opts);
					}                   
					$('.btn').button('reset');
					$('.submit_btn').removeClass('disabled');
				}
				});	
		return false;		
    });
    show_summernote($('.wysihtml5box'),editor_options);

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
	function bytesToSize(filesize) {
  var sizeInMB = (filesize / (1024*1024)).toFixed(2);
  if(sizeInMB>max_file_size)
  {return 1;}else{return 0;}  
}

$('#emai_attachments_form').submit(function(e) {
	e.stopImmediatePropagation();
    e.preventDefault();

    var formData = new FormData(this);
    var url = 	baseurl + '/account/upload_file';
    $.ajax({
        url: url,  //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            console.log(response);
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

            $(document).on("click",".del_attachment",function(ee){
                var url  =  baseurl + '/account/delete_actvity_attachment_file';
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
                $('#info2').val(JSON.stringify(attachmentsinfo));
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
@stop 