@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  @if($Emaildata->EmailCall==Messages::Sent)
  <li><a href="{{URL::to('emailmessages/sent')}}">Sentbox</a></li>
  @elseif ($Emaildata->EmailCall==Messages::Received)
  <li><a href="{{URL::to('emailmessages')}}">Inbox</a></li>
  @endif
  <li class="active"> <strong>Emails Detail</strong> </li>
</ol>
<h3>Emails</h3>
@include('includes.errors')
@include('includes.success')
<div class="mail-env"> 
  
  <!-- compose new email button -->
  <div class="mail-sidebar-row visible-xs"> <a href="#" class="btn btn-success btn-icon btn-block"> Compose Mail <i class="entypo-pencil"></i>&nbsp;</a> </div>
  <!-- Sidebar --> 
  @include("emailmessages.mail_sidebar") 
  <!-- Mail Body -->
  <div class="mail-body">
    <div class="mail-header"> 
      <!-- title -->
      <div class="mail-title"> {{$Emaildata->Subject}} <span class="label label-warning hidden">Friends</span> <span class="label label-info hidden">Sport</span> </div>
      
      <!-- links -->
      <div class="mail-links">
       <a action_type="reply" email_number="{{$Emaildata->AccountEmailLogID}}" data-toggle="tooltip" data-placement="top"  data-original-title="Reply" class="btn btn-black email_action tooltip-primary"><i class="entypo-reply"></i> </a> 
       <a action_type="forward" email_number="{{$Emaildata->AccountEmailLogID}}" data-toggle="tooltip" data-placement="top"  data-original-title="Forward" class="btn btn-info email_action tooltip-primary"><i class="entypo-forward"></i> </a>
        </div>
    </div>
    <div class="mail-info">
      <div class="mail-sender dropdown"> <a class="dropdown-toggle clickable" data-toggle="dropdown" href=""> <span>{{$from}}</span> ({{$Emaildata->Emailfrom}}) to <span>{{$to}}</span> </a>
      @if($Emaildata->AccountID==0 && $Emaildata->EmailCall==Messages::Received) 
        <ul class="dropdown-menu dropdown-red">
          <li> <a class="unknownemailaction" unknown_action_type="leads" firstname="{{$Emaildata->EmailfromName}}" emailaddress="{{$Emaildata->Emailfrom}}" href="#">&nbsp;<i class="fa fa-building"></i>&nbsp;&nbsp; Add as Lead </a> </li>
          <li> <a class="unknownemailaction" unknown_action_type="contacts" firstname="{{$Emaildata->EmailfromName}}" emailaddress="{{$Emaildata->Emailfrom}}" href="#"> <i class="entypo-user"></i> &nbsp;Add as Contact</a> </li>
        </ul>
        @endif
      </div>
      <div class="mail-date"> <?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($Emaildata->created_at))->diffForHumans();  ?> </div>
    </div>
    <div class="mail-text">{{$body}}</div>
    @if(count($attachments)>0 && is_array($attachments))
    <div class="mail-attachments">
      <h4> <i class="entypo-attach"></i> Attachments <span>({{count($attachments)}})</span> </h4>
      <ul>
        @foreach($attachments as $key_acttachment => $attachments_data)
        <?php 
   		//$FilePath 		= 	AmazonS3::preSignedUrl($attachments_data['filepath']);
		$Filename		=	$attachments_data['filepath'];
		
		/*if(is_amazon() == true)
		{
			$Attachmenturl =  AmazonS3::preSignedUrl($attachments_data['filepath']);
		}
		else
		{
			$Attachmenturl = CompanyConfiguration::get('UPLOAD_PATH')."/".$attachments_data['filepath'];
		}*/
		$Attachmenturl = URL::to('emails/'.$Emaildata->AccountEmailLogID.'/getattachment/'.$key_acttachment);
		
   	    ?>
        <li> <a href="{{$Attachmenturl}}" class="thumb download"> <img width="75"   src="{{getimageicons($Filename)}}" class="img-rounded" /> </a> <a href="{{$Attachmenturl}}" class="shortnamewrap name"> {{$attachments_data['filename']}} </a>
          <div class="links"><a href="{{$Attachmenturl}}">Download</a> </div>
        </li>
        @endforeach
      </ul>
    </div>
    @endif
    <div class="hidden mail-reply">
      <div class="fake-form">
        <div> <a class="email_action clickable" action_type="reply" email_number="{{$Emaildata->AccountEmailLogID}}" >Reply</a> or <a class="email_action clickable" action_type="forward" email_number="{{$Emaildata->AccountEmailLogID}}">Forward</a> this message... </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade " id="EmailAction-model">
  <form id="EmailActionform" method="post">
    <div class="modal-dialog EmailAction_box"  style="width: 70%;">
      <div class="modal-content"> </div>
    </div>
  </form>
</div>
<form id="emai_attachments_reply_form" class="hidden" name="emai_attachments_form">
  <span class="emai_attachments_span">
  <input type="file" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" name="emailattachment[]" multiple id="filecontrole2">
  </span>
  <input  hidden="" name="account_id" value="{{$Emaildata->AccountID}}" />
  <input  hidden="" name="token_attachment" value="{{$random_token}}" />
  <input id="info3" type="hidden" name="attachmentsinfo" />
  <button  class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
</form>
<style>
.mail-sender{width:60% !important;}
p{   
	-webkit-margin-before: 1em;
    -webkit-margin-after: 1em;
	-webkit-margin-start: 0px;
    -webkit-margin-end: 0px;
    
}
.dropdown-menu.dropdown-red
{
background-color: #d42020;
border-color: #b51b1b;
}
.dropdown-menu.dropdown-red > li > a {
    color: #ffffff;
}
.dropdown-menu.dropdown-red > li:hover a {
    background-color: #be1d1d;
    color: #ffffff;
}
</style>
<script>
var editor_options 	 	=  		{};
var account_id = '{{$Emaildata->AccountID}}';
var file_count 		  =  		0;
var emailFileList     =			[];
var allow_extensions  = 		{{$response_extensions}};
var max_file_size_txt =	        '{{$max_file_size}}';
var max_file_size	  =	        '{{str_replace("M","",$max_file_size)}}';
var emailFileListReply =		 [];
$(document).ready(function(e) {
	$( document ).on("click",'.email_action' ,function(e) {			
		var url 		   = 	 baseurl + '/emails/email_action';
		var action_type    =     $(this).attr('action_type');
		var email_number   =     $(this).attr('email_number');
		
		emailFileListReply = [];
	   $('#info3').val('');
	   $('#info4').val('');
	   $("#EmailActionform").find('#emailattachment_sent').val('');
	   $("#EmailActionform").find('.file_upload_span').remove();
		
		 $.ajax({
			url: url,
			type: 'POST',
			dataType: 'html',
			async :false,
			data:{s:1,action_type:action_type,email_number:email_number,AccountID:account_id},
			success: function(response){
				$('#EmailAction-model .modal-content').html('');
				$('#EmailAction-model .modal-content').html(response);				
					var mod =  $(document).find('.EmailAction_box');
					$('#EmailAction-model').modal('show');
				mod.find("select").select2({
                    minimumResultsForSearch: -1
                });
				mod.find('.select2-container').css('visibility','visible');
				show_summernote(mod.find('.message'),editor_options);
		    
			},
		});
	});
	
		 $("#EmailActionform").submit(function (event) {
		//////////////////////////          	
			var email_url 	= 	"<?php echo URL::to('/accounts/'.$Emaildata->AccountID.'/activities/sendemail/api/');?>?scrol="+1;
          	event.stopImmediatePropagation();
            event.preventDefault();			
			var formData = new FormData($('#EmailActionform')[0]);
			
			$("#EmailAction-model").find('.btn-send-mail').addClass('disabled');  $("#EmailAction-model").find('.btn-send-mail').button('loading');
			 $.ajax({
                url: email_url,
                type: 'POST',
                dataType: 'html',
				data:formData,
				async :false,
				cache: false,
                contentType: false,
                processData: false,
                success: function(response) {		
			   $("#EmailAction-model").find('.btn-send-mail').button('reset');
			   $("#EmailAction-model").find('.btn-send-mail').removeClass('disabled');			   
 	           if (isJson(response)) {				   
					var response_json  =  JSON.parse(response);
					
					ShowToastr("error",response_json.message);
				} else {
					ShowToastr("success","Mail Successfully Sent."); 
					//$('#EmailAction-model').hide();
					$('#EmailAction-model').modal('hide'); 		
					emailFileListReply = [];
                   $('#info3').val('');
                   $('#info4').val('');
                   $("#EmailActionform").find('#emailattachment_sent').val('');
				   $("#EmailActionform").find('.file_upload_span').remove();
				   
				}
				
      			},
			});	
		///////////////////////////////
		 
	 });
	 
	  $(document).on("click","#addReplyTtachment",function(ee){
			 file_count++;                
				$('#filecontrole2').click();
			 });
			
		 $(document).on('change','#filecontrole2',function(e){
				e.stopImmediatePropagation();
  				e.preventDefault();		
                var files 			 		 =  e.target.files;				
                var fileText 		 		 =  new Array();
				var file_check				 =	1; 
				var local_reply_array		 =  new Array();
				///////
	        var filesArr = Array.prototype.slice.call(files);
		
			filesArr.forEach(function(f) {     
				var ext_current_file  = f.name.split('.').pop();
				if(allow_extensions.indexOf(ext_current_file.toLowerCase()) > -1 )			
				{         
					var name_file = f.name;
					var index_file = emailFileListReply.indexOf(f.name);
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
						local_reply_array.push(f.name);
					}
				}
				else
				{
					ShowToastr("error",ext_current_file+" file type not allowed.");
					
				}
        });
        		if(local_reply_array.length>0 && file_check==1)
				{	 emailFileListReply = emailFileListReply.concat(local_reply_array);
   					$('#emai_attachments_reply_form').submit();
				}

            });
			
		$('#emai_attachments_reply_form').submit(function(e) {
	e.stopImmediatePropagation();
    e.preventDefault();

    var formData = new FormData(this);
    var url = 	baseurl + '/account/upload_file?add_type=reply';
    $.ajax({
        url: url,  //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            console.log(response);
            if(response.status =='success'){
                $("#EmailActionform").find('.file-input-names').html(response.data.text);             
                $('#info3').val(JSON.stringify(response.data.attachmentsinfo));
				$('#info4').val(JSON.stringify(response.data.attachmentsinfo));

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


		$( document ).on("change",'.email_template' ,function(e) {
            var templateID = $(this).val(); 
			var parent_box = $(this).attr('parent_box'); 
            if(templateID>0) {
                var url = baseurl + '/accounts/' + templateID + '/ajax_template';
                $.get(url, function (data, status) {
                    if (Status = "success") {						
                        editor_reset(data,parent_box);
                    } else {
                        toastr.error(status, "Error", toastr_opts);
                    }
                });
            }
        });
		
		     function editor_reset(data,parent_box){
				//var doc = $('.mail-compose');
				var doc = $(document).find('.'+parent_box);
        		doc.find('.message').show();
						
	       if(!Array.isArray(data)){				
                var EmailTemplate = data['EmailTemplate'];
                doc.find('[name="Subject"]').val(EmailTemplate.Subject);
                doc.find('.message').val(EmailTemplate.TemplateBody);
            }else{
                doc.find('[name="Subject"]').val('');
                doc.find('.message').val('');
            }

			show_summernote(mod.find('.message'),editor_options);

        }
		
		$(document).on("click",".reply_del_attachment",function(ee){
				var url  =  baseurl + '/account/delete_actvity_attachment_file';
					var fileName   =  $(this).attr('del_file_name');
					var attachmentsinfo = $('#info3').val();
					if(!attachmentsinfo){
						return true;
					}
					attachmentsinfo = jQuery.parseJSON(attachmentsinfo);
					$(this).parent().remove();
					var fileIndex = emailFileListReply.indexOf(fileName);
					var fileinfo = attachmentsinfo[fileIndex];
					emailFileListReply.splice(fileIndex, 1);
					attachmentsinfo.splice(fileIndex, 1);
					$('#info3').val(JSON.stringify(attachmentsinfo));
					$('#info4').val(JSON.stringify(attachmentsinfo));
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
			
		$('.unknownemailaction').click(function(e) {
            var unknown_action_type 	= 	$(this).attr('unknown_action_type');
			var firstname 				= 	$(this).attr('firstname');
			var emailaddress 			= 	$(this).attr('emailaddress');
			window.location = baseurl+'/'+unknown_action_type+'/create?email='+emailaddress+'&name='+firstname;
        });
});
</script> 
@stop 