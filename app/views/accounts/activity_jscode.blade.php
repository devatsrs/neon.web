<script type="text/javascript">
var editor_options 	  =  		{"Crm":true};
var GUID			  =			'{{$data['GUID']}}';
var show_popup		  = 	 	0;
var rowData 		  = 	 	[];
var scroll_more 	  =  		1;
var file_count 		  =  		0;
var current_tab       =  		'';
Not_ask_delete_Note   = 		0;
@if(empty($message)){
	var allow_extensions  = 		{{$response_extensions}};
}
@else {
	var allow_extensions  = 	'';	
	}
@endif;

var account_id		  =			'{{$AccountID}}';
var emailFileList	  =  		new Array();
var emailFileListReply  =  		new Array();
var token			  =			'{{$token}}';
var max_file_size_txt =	        '{{$max_file_size}}';
var max_file_size	  =	        '{{str_replace("M","",$max_file_size)}}';

    jQuery(document).ready(function ($) {	
	
	function biuldSwicth(container,name,formID,checked){
				var make = '<span class="make-switch switch-small">';
				make += '<input name="'+name+'" value="{{Task::Close}}" '+checked+' type="checkbox">';
				make +='</span>';
	
				var container = $(formID).find(container);
				container.empty();
				container.html(make);
				container.find('.make-switch').bootstrapSwitch();
			} 
	
	$( document ).on("click",'.ticket_conversations' ,function(e) {
		var data_fetch_id 		= 	$(this).attr('data_fetch_id');
		var conversations_type	= 	$(this).attr('conversations_type');		
		var url 				= 	baseurl + '/accounts/' + data_fetch_id + '/ajax_conversations';
		 $.ajax({
			url: url,
			type: 'POST',
			dataType: 'html',
			async :false,
			data:{s:1,conversations_type:conversations_type},
			success: function(response){
				$('#ticket-conversation #allComments').html(response);
				$('#ticket-conversation').modal('show');
			},
		});
	});
	
	$( document ).on("click",'.replyboxemail' ,function(e) {	
		$(this).find('.replyboxhidden').toggle();
	});
	
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
				show_summernote(mod.find(".message"),editor_options);

		    
			},
		});
	});
	

	 $("#EmailActionform").submit(function (event) {
		//////////////////////////          	
			var email_url 	= 	"<?php echo URL::to('/accounts/'.$AccountID.'/activities/sendemail/api/');?>?scrol="+1;
          	event.stopImmediatePropagation();
            event.preventDefault();			
			var formData = new FormData($('#EmailActionform')[0]);
			console.log(rowData);
			
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
	
	
	$( document ).on("click",'.mail_conversations' ,function(e) {
		var ticket_id 		= 	$(this).attr('ticket_id');
		var url 			= 	baseurl + '/accounts/' + ticket_id + '/ajax_conversations';
		 $.ajax({
			url: url,
			type: 'POST',
			dataType: 'html',
			async :false,
			data:{s:1},
			success: function(response){
				$('#ticket-conversation #allComments').html(response);
				$('#ticket-conversation').modal('show');
			},
		});
	});
	$( document ).on("click",'.delete_task_link' ,function(e) {
		
	    var del_task_id  = $(this).attr('task-id');
		var del_key_id   = $(this).attr('key_id');
		
		if(Not_ask_delete_Note==1 && $('#timeline-'+del_key_id).hasClass("followup_task")){
				Not_ask_delete_Note = 0;	
		}else{
		  if (!confirm("Are you sure to delete?")) {
				return false;
			}
		}
     
		
		var url_del_task1 	= 	"<?php echo URL::to('/task/{id}/delete_task'); ?>";
		var url_del_task	=	url_del_task1.replace( '{id}', del_task_id );
		 $.ajax({
			url: url_del_task,
			type: 'POST',
			dataType: 'json',
			async :false,
			data:{TaskID:del_task_id},
			success: function(response) {
				console.log('timeline-'+del_key_id);
				$('#timeline-'+del_key_id).remove();
				$('#timeline-ul').append('<li id="timeline-'+del_key_id+'" class="count-li timeline_task_entry"></li>');
				ShowToastr("success","Task Successfully Deleted"); 
			},
		});	
		
    });
	  
	
	$( document ).on("click",'.edit_task_link' ,function(e) {
	    var edit_task_id  = $(this).attr('task-id');
		var edit_key_id   = $(this).attr('key_id');	
        
		if(edit_task_id!='' && edit_key_id!=''){
			//
			
		var url_get_task 	= 	"<?php echo URL::to('task/GetTask'); ?>";
		 $.ajax({
					url: url_get_task,
					type: 'POST',
					dataType: 'json',
					async :false,
					data:{TaskID:edit_task_id},
					success: function(response) {
						if(response.Priority!='Low'){							
							biuldSwicth('.make','Priority','#edit-modal-task','checked');
						}else{
							biuldSwicth('.make','Priority','#edit-modal-task','');
						}
						
						$('#edit-modal-task #Subject').val(response.Subject);
						$('#edit-modal-task #Description_task').val(response.Description);
						var date_time = response.DueDate.split(" ");
						$('#edit-modal-task #DueDate_date').val(date_time[0]);
						$('#edit-modal-task #DueDate_time').val(date_time[1]);
						var status_id = 0;
						$('#edit-task-form  [name="TaskStatus"] option').each(function(){
						  if ($(this).text() == response.TaskStatus){
								$(this).attr("selected","selected");
								status_id = $(this).attr("value");
							}
						});
						var account_id = 0;
						$('#edit-task-form  [name="UsersIDs"] option').each(function(){
						  if ($(this).text() == response.Name){
								$(this).attr("selected","selected");
								account_id = $(this).attr("value");
							}
						});
						$('#edit-task-form  [name="TaskStatus"]').val(status_id).trigger("change");
						$('#edit-task-form [name="UsersIDs"]').val(account_id).trigger("change");
						$('#edit-task-form #TaskID').val(edit_task_id);
						$('#edit-task-form #KeyID').val(edit_key_id);
						$('#edit-modal-task').modal('show');												
					},
				});	
					
		}
    });
	
	
		$( document ).on("click",'.delete_note_link' ,function(e) {
			var del_note_id  	=   $(this).attr('note-id');
			var del_key_id   	=   $(this).attr('key_id');
			var edit_note_type  = 	$(this).attr('note_type');
			
			var followup = parseInt(del_key_id)+1;
			if ($('#timeline-'+followup).hasClass("followup_task"))
			{
					 if (!confirm("Are you sure you want delete? This note has follow up task against it."))
					 {
      	  				return false;
    				 }
			}
			else
			{
					if (!confirm("Are you sure to delete?"))
					{
      	  				return false;
    				}					
			}
			
		var url_del_note1 	= 	"<?php echo URL::to('/accounts/{id}/delete_note'); ?>";
		var url_del_note	=	url_del_note1.replace( '{id}', del_note_id );
		 $.ajax({
			url: url_del_note,
			type: 'POST',
			dataType: 'json',
			async :false,
			data:{NoteID:del_note_id,note_type:edit_note_type},
			success: function(response) {
				console.log('timeline-'+del_key_id);
				$('#timeline-'+del_key_id).remove();
				$('#timeline-ul').append('<li id="timeline-'+del_key_id+'" class="count-li timeline_note_entry"></li>');
				//follow up delete
				var followup = parseInt(del_key_id)+1;
				if ($('#timeline-'+followup).hasClass("followup_task")) {
					 if (!confirm("Delete Follow up Task?")) {
      	  				return false;
    				}
					else
					{ 
						Not_ask_delete_Note = 1;
						$('#timeline-'+followup+' .delete_task_link').click();
					}
					$('#timeline-'+del_key_id+1).remove();
					$('#timeline-ul').append('<li id="timeline-'+del_key_id+1+'" class="count-li timeline_task_entry"></li>');
				}
				ShowToastr("success","Note Successfully Deleted"); 
			},
		});	
		
    });
	
	$( document ).on("click",'.edit_note_link' ,function(e) {
		
        var edit_note_id 	= 	$(this).attr('note-id');
		var edit_key_id  	= 	$(this).attr('key_id');
		var edit_note_type  = 	$(this).attr('note_type');
		
		///////
		var url_get_note 	= 	"<?php echo URL::to('accounts/get_note'); ?>";
		 $.ajax({
					url: url_get_note,
					type: 'POST',
					dataType: 'json',
					async :false,
					data:{NoteID:edit_note_id,note_type:edit_note_type},
					success: function(response) {
						$('#edit-note-model #Description_edit_note').val(response.Note);
						$('#edit-note-model #NoteID').val(parseInt(edit_note_id));
						$('#edit-note-model #KeyID').val(parseInt(edit_key_id));
						$('#edit-note-model #NoteType').val(edit_note_type);						
						$('#edit-note-model').modal('show'); 								
					},
				});	
				
				      $('#edit-note-model').on('shown.bs.modal', function(event){
						var modal = $(this);
                        var modal = $('#edit-note-model');
						show_summernote(modal.find(".editor-note"),editor_options);
                    });

                   	
		/////////		
    });
	
			 $('#edit-note-model').on('hidden.bs.modal', function(event){				 	
                        var modal = $(this);
              });

			$("#form_timeline_filter [name=timeline_filter]").click(function(e){
        	var show_timeline_data = $(this).attr('show_data'); console.log(show_timeline_data);
			if(show_timeline_data!='')
			{
				if(show_timeline_data=='all'){
					$('#timeline-ul .count-li').show();
				}else{
					$('#timeline-ul .count-li').hide();
					$('#timeline-ul ').find('.'+show_timeline_data).show();
				}
			}
    	});
		
	
	
	@if(!empty($message))
 var status = '{{$message}}';
toastr.error(status, "Error", toastr_opts);
 @endif
	
	$('.redirect_link').click(function(e) {
		var id_redirect = $(this).attr('href_id');
		
		$('#'+id_redirect)[0].click();
    });
		
		var per_scroll 		= 	{{$per_scroll}};
		var per_scroll_inc  = 	per_scroll;
		
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
						
				   if(!Array.isArray(data)){
						var EmailTemplate = data['EmailTemplate'];
						doc.find('[name="Subject"]').val(EmailTemplate.Subject);
						doc.find('.message').val(EmailTemplate.TemplateBody);
					}else{
						doc.find('[name="Subject"]').val('');
						doc.find('.message').val('');
					}
					show_summernote(doc.find(".message"),editor_options);
        }
		
    // When Lead is converted to account.
    <?php if(Session::get('is_converted')){ ?>

        var toastr_opts = {
            "closeButton": true,
            "debug": false,
            "positionClass": "toast-top-right",
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
        toastr.success('<?php echo Session::get('is_converted');?>', "Success", toastr_opts);
    <?php } ?>
	//////////
	function last_msg_funtion() 
	{  
		if($("#timeline-ul").length == 0) {
			return false;  //it doesn't exist
		}

		if(scroll_more==0){
			return false;
		}
		var count = 0;
		var getClass =  $("#timeline-ul .count-li");
		getClass.each(function () {count++;}); 	
		var ID			=	$(".message_box:last").attr("id");
		var url_scroll 	= 	"<?php echo URL::to('accounts/{id}/GetTimeLineSrollData'); ?>";
		url_scroll 	   	= 	url_scroll.replace("{id}",<?php echo $AccountID; ?>);
		
		$('div#last_msg_loader').html('<img src="'+baseurl+'/assets/images/bigLoader.gif">');
		
		/////////////
		
				 $.ajax({
					url: url_scroll+'/'+per_scroll+"?scrol="+count,
					type: 'POST',
					dataType: 'html',
					async :false,
					data:{GUID:GUID},
					success: function(response1) {
							if (isJson(response1)) {
								
						var response_json  =  JSON.parse(response1);
						if(response_json.scroll=='end')
						{
							if($(".timeline-end").length > 0) {
								scroll_more= 0;	
								return false;
					}
							
							var html_end  ='<li class="timeline-end"><time class="cbp_tmtime"></time><div class="cbp_tmicon bg-info end_timeline_logo "><i class="entypo-infinity"></i></div><div class="end_timeline cbp_tmlabel"><h2></h2><div class="details no-display"></div></div></li>';
							$("#timeline-ul").append(html_end);	
							scroll_more= 0;	
							$('div#last_msg_loader').empty();
							console.log("Results completed");
							return false;
						}
						ShowToastr("error",response_json.message);
					} else {
							per_scroll 		= 	per_scroll_inc+per_scroll;	
							$("#timeline-ul").append(response1); 
						}
							$('div#last_msg_loader').empty();
							change_click_filter();
						},
				});	
			
		//////////////
	
	}

$(window).scroll(function(){
if ($(window).scrollTop() == $(document).height() - $(window).height()){

setTimeout(function() {
   last_msg_funtion();
}, 1000);
}
});
	//////////
    });

        function showDiv(divName, ctrl) {
			
			if(divName== current_tab)
			{return false;}
			
            $("#box-1").addClass("no-display");
            $("#box-2").addClass("no-display");
            $("#box-3").addClass("no-display");
			
            $("#box-4").addClass("no-display");            
            $("#" + divName).removeClass("no-display");
            $("#tab-btn").children("li").removeClass("active");
            $("#" + ctrl).addClass("active");
			if(divName=='box-2')
			{				
				var doc = $('.mail-compose');
				show_summernote(doc.find(".message"),editor_options);

			}else{
				 var doc = $('.mail-compose');
        		doc.find('.message').show();
			}
			
			if(divName=='box-1')
			{	
				var doc = $('#box-1');
				show_summernote(doc.find("#note-content"),editor_options);
			}
			else
			{
				var doc = $('#box-1');
        		doc.find('#note-content').show();
			
			}
			current_tab = divName;
			
        }
        $(document).ready(function () {
            if (window.location.href.indexOf("#box-2") >= 0) {
                debugger;
                showDiv("box-2", "2");
            }
            else {
                showDiv("box-1", "1");
            }
        });
        
        $(document).ready(function ($) {
			$( document ).on("click",".cbp_tmicon" ,function(e) {
				var id_toggle = $(this).attr('id_toggle');
				if(id_toggle)
				{
               		$('#hidden-timeline-'+id_toggle).toggle();
				}
            });
			
			$(document).on("click",".toggle_open", function(e) {
				var id_toggle = $(this).attr('id_toggle');
				if(id_toggle)
				{
					/*if( $('#hidden-timeline-'+id_toggle).css('display').toLowerCase() != 'block') {
							$('#hidden-timeline-'+id_toggle).css('display','block');	
					}*/
					$('#hidden-timeline-'+id_toggle).toggle();	
				}
                
            });
			
		 $('#addTtachment').click(function(){
			 file_count++;                
				//var html_img = '<input id="filecontrole'+file_count+'" multiple type="file" name="emailattachment[]" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"  />';
				//$('.emai_attachments_span').html(html_img);
				$('#filecontrole1').click();
				
            });
			
			 $(document).on("click","#addReplyTtachment",function(ee){
			 file_count++;                
				//var html_img = '<input id="filecontrole'+file_count+'" multiple type="file" name="emailattachment[]" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"  />';
				//$('.emai_attachments_span').html(html_img);
				$('#filecontrole2').click();
				
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
				
				
			
			//////////////
        });
        $("#notes-from").submit(function (event) {
            event.stopImmediatePropagation();
            event.preventDefault();			
			var type_submit  = $(this).val();			

            var formData = new FormData($('#notes-from')[0]);
		    var getClass =  $("#timeline-ul .count-li");
            var count = 0;
            getClass.each(function () {count++;}); 	
          // showAjaxScript($("#notes-from").attr("action")+"?scrol="+count, formData, FnAddNoteSuccess);
		   var formData = $($('#notes-from')[0]).serializeArray();
		   
		   	 $.ajax({
                url: $("#notes-from").attr("action")+"?scrol="+count,
                type: 'POST',
                dataType: 'html',
				data:formData,
				async :false,
                success: function(response) {
					
			   $(".save-note-btn").button('reset');
			   $(".save-note-btn").removeClass('disabled');
					
			  $(".save.btn").button('reset');
            	if (isJson(response)) {
					var response_json  =  JSON.parse(response);
					ShowToastr("error",response_json.message);
				} else {
					
				if(show_popup==1)
				{
					$('.followup_task_data ul li:eq(0)').before(response);
					document.getElementById('add-task-form').reset();
					$('#Task_type').val(3);
					$('#Task_ParentID').val($('.followup_task_data ul li:eq(0)').attr('row-id'));					
					$('#add-modal-task').modal('show');        	
				}
				else
				{
					ShowToastr("success","Note Successfully Created");
					document.getElementById('notes-from').reset();
					var empty_ul = 0;
					if($("#timeline-ul").length == 0) {
						var html_ul = ' <ul class="cbp_tmtimeline" id="timeline-ul"> <li></li></ul>';
						$('.timeline_start').html(html_ul);
						empty_ul = 1;
					}
					per_scroll = count;
					 $('#timeline-ul li:eq(0)').before(response);
					 if(empty_ul)
					 {
					 		var html_end  ='<li class="timeline-end"><time class="cbp_tmtime"></time><div class="cbp_tmicon bg-info end_timeline_logo "><i class="entypo-infinity"></i></div><div class="end_timeline cbp_tmlabel"><h2></h2><div class="details no-display"></div></div></li>';
							$("#timeline-ul").append(html_end);	
					 }
				}

            } show_popup=0;
			change_click_filter();
      			},
			});

        });
        $("#save-task-form").submit(function (e) {
			
			//////////////
			 $('#save-task').addClass('disabled');  $('#save-task').button('loading');
			
            e.preventDefault();
			e.stopImmediatePropagation();
            var formid 			= 	$(this).attr('id');            
            var formData 		= 	new FormData($('#'+formid)[0]);
			var count 			= 	0;
			var getClass =  $("#timeline-ul .count-li");
            getClass.each(function () {count++;}); 	
			var update_new_url 	= 	baseurl + '/task/create?scrol='+count;
			
            $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'html',
                success: function (response) {                  
					
					if (isJson(response)) {
						var response_json  =  JSON.parse(response);
						 ShowToastr("error",response_json.message);
					} else {
						var empty_ul = 0;
						if($("#timeline-ul").length == 0) {
							var html_ul = ' <ul class="cbp_tmtimeline" id="timeline-ul"> <li></li></ul>';
							$('.timeline_start').html(html_ul);
							empty_ul = 1;
						
						}
						per_scroll = count;
						ShowToastr("success","Task Successfully Created"); 
						                    
						$('#timeline-ul li:eq(0)').before(response);
						if(empty_ul)
						 {
					 		var html_end  ='<li class="timeline-end"><time class="cbp_tmtime"></time><div class="cbp_tmicon bg-info end_timeline_logo "><i class="entypo-infinity"></i></div><div class="end_timeline cbp_tmlabel"><h2></h2><div class="details no-display"></div></div></li>';
							$("#timeline-ul").append(html_end);	
						 }
						document.getElementById('save-task-form').reset();
						
						$('#save-task-form #Description').css("height","48px");
					}
                    show_popup=0;
				    $("#save-task").button('reset');
			   	    $("#save-task").removeClass('disabled');
                    //getOpportunities();
					change_click_filter();
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        
			//////////////
        });
		
		function change_click_filter()
		{
			var current_time_line_filter =  $(".timeline_filter:checked");
			$(current_time_line_filter).click();
		}
		
		function isJson(str) {
		try {
			JSON.parse(str);
		} catch (e) {			
			return false;
		}
    	return true;
}

        $("#save-log").click(function () {
            var getClass =  $("#timeline-ul .count-li");
            var count = 0;
            getClass.each(function () {
                count++;
            });
            var addCount = count + 1;
            var callNumber = $("#Log-call-number").val();
            var callDescription = $("#call-description").val();
            var html = '<li id="timeline-' + addCount + '" class="count-li"><time class="cbp_tmtime" datetime="2014-03-27T03:45"><span>Now</span></time><div class="cbp_tmicon bg-success"><i class="entypo-phone"></i></div><div class="cbp_tmlabel"><h2 onclick="expandTimeLine(' + addCount + ')">You <span>made a call to </span>' + callNumber + '</h2><a id="show-more-' + addCount + '" onclick="expandTimeLine(' + addCount + ')" class="pull-right show-less">Show More<i class="entypo-down-open"></i></a><div id="hidden-timeline-' + addCount + '"   class="details no-display"><p>' + callDescription + '</p><a class="pull-right show-less" onclick="hideDetail(' + addCount + ')">Show Less<i class="entypo-up-open"></i></a></div></div></li>';
            $('#timeline-ul li:eq(0)').before(html);
        });
        $("#save-deal").click(function () {
            var getClass =  $("#timeline-ul .count-li");
            var count = 0;
            getClass.each(function () {
                count++;
            });
            var addCount = count + 1;
            var dealOwner = $("#dealOwner").val();
            var dealName = $("#dealName").val();
            var selectBoard = $("#select-board").val();
            var checklead = $("#check-lead").val();
            var dealCompany;
            var dealContact;
            var dealPhone;
            var dealEmail;
            if (checklead == "No") {
                dealCompany = $("#dealCompany").val();
                dealContact = $("#dealContact").val();
                dealPhone = $("#dealPhone").val();
                dealEmail = $("#dealEmail").val();

            }
            else if (checklead == "Yes") {
                dealCompany = $("#lead-company").val();
                dealContact = $("#lead-contact").val();
                dealPhone = $("#lead-phone").val();
                dealEmail = $("#lead-email").val();
            }
            var html = '<li id="timeline-' + addCount + '" class="count-li"><time class="cbp_tmtime" datetime="2014-03-27T03:45"><span>Now</span></time><div class="cbp_tmicon bg-success"><i class="entypo-doc-text"></i></div><div class="cbp_tmlabel"><h2 a onclick="dealsDialog()" >You <span>added a new opportunity </span>' + dealName + '</h2><a id="show-more-' + addCount + '" onclick="expandTimeLine(' + addCount + ')" class="pull-right show-less">Show More<i class="entypo-down-open"></i></a><div id="hidden-timeline-' + addCount + '" class="details no-display"><p>Company: &nbsp; ' + dealCompany + '</p><p>Contact Person: &nbsp; ' + dealContact + '</p><p>Phone Number: &nbsp;' + dealPhone + '</p><p>Email Address: &nbsp; ' + dealEmail + '</p><a class="pull-right show-less" onclick="hideDetail('+addCount+')">Show Less<i class="entypo-up-open"></i></a></div></div></li>';
            $('#timeline-ul li:eq(0)').before(html);
        });
		
		$('#save-mail').click(function(e) {  empty_images_inputs(); $('#email_send').val(1);  $('.btn-send-mail').addClass('disabled'); $(this).button('loading');            show_popup = 0; });
		$('#save-email-follow').click(function(e) { empty_images_inputs(); $('#email_send').val(0); $('.btn-send-mail').addClass('disabled'); $(this).button('loading');    show_popup = 1; });
		
		$('#save-note').click(function(e) {       $('.save-note-btn').addClass('disabled'); $(this).button('loading');      show_popup = 0; });
		$('#save-note-follow').click(function(e) {  $('.save-note-btn').addClass('disabled'); $(this).button('loading');    show_popup = 1; });
		
		
		function empty_images_inputs()
		{
			$('.fileUploads').val();
			$('#emailattachment_sent').val(emailFileList);
		}
		
        $("#email-from").submit(function (event) {
		    var getClass =  $("#timeline-ul .count-li");
            var count = 0;
            getClass.each(function () {count++;}); 			
			var email_url 	= 	"<?php echo URL::to('/accounts/'.$AccountID.'/activities/sendemail/api/');?>?scrol="+count;
          	event.stopImmediatePropagation();
            event.preventDefault();			
			var formData = new FormData($('#email-from')[0]);
			console.log(rowData);
			
			// formData.push({ name: "emailattachment", value: $('#emailattachment').val() });
			// showAjaxScript(email_url, formData, FnAddEmailSuccess);
			
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
			   $(".btn-send-mail").button('reset');
			   $(".btn-send-mail").removeClass('disabled');			   
 	           if (isJson(response)) {				   
					var response_json  =  JSON.parse(response);
					
					ShowToastr("error",response_json.message);
				} else {
					
					
				//reset file upload	
				file_count = 0;
                   emailFileList = [];
				//$('.fileUploads').remove();
                   $('#info1').val('');
                   $('#info2').val('');
                   $('#emailattachment_sent').val('');
				$('.file_upload_span').remove();
               
					
				///
				if(show_popup==1)
				{
					$('.followup_task_data ul li:eq(0)').before(response);
					document.getElementById('add-task-form').reset();
					$('#Task_type').val(2);
					$('#Task_ParentID').val($('.followup_task_data ul li:eq(0)').attr('row-id'));					
					$('#add-modal-task').modal('show');        	
				}
				else
				{
					 ShowToastr("success","Email Sent Successfully"); 
					 document.getElementById('email-from').reset();	
					 $('.email_template').change();		
					var empty_ul = 0;
					if($("#timeline-ul").length == 0) {
						var html_ul = ' <ul class="cbp_tmtimeline" id="timeline-ul"> <li></li></ul>';
						$('.timeline_start').html(html_ul);
						empty_ul = 1;
					}
					 per_scroll = count;
					 $('#timeline-ul li:eq(0)').before(response);
					 if(empty_ul)
					 {
					 		var html_end  ='<li class="timeline-end"><time class="cbp_tmtime"></time><div class="cbp_tmicon bg-info end_timeline_logo "><i class="entypo-infinity"></i></div><div class="end_timeline cbp_tmlabel"><h2></h2><div class="details no-display"></div></div></li>';
							$("#timeline-ul").append(html_end);	
					 }
				}
				///				
				
            } show_popup=0; change_click_filter();
      			},
			});	
		 });
		 
		 /////////        
        function expandTimeLine(id)
        {
            $("#hidden-timeline-" + id).removeClass('no-display');
            $("#show-more-" + id).addClass('no-display');
			$("#show-less-" + id).removeClass('no-display');
        }
        function hideDetail(id) {
			$("#show-less-" + id).addClass('no-display');
            $("#hidden-timeline-" + id).addClass('no-display');
            $("#show-more-" + id).removeClass('no-display');
        }
		
	
		
    </script>
<style>
#last_msg_loader {
	text-align: center;
}
.file-input-names {
	text-align: right;
	display: block;
}
ul.grid li div.headerSmall {
	min-height: 31px;
}
ul.grid li div.box {
	height: auto;
}
ul.grid li div.blockSmall {
	min-height: 20px;
}
ul.grid li div.cellNoSmall {
	min-height: 20px;
}
ul.grid li div.action {
	position: inherit;
}
.col-md-3 {
	padding-right: 5px;
}
.big-col {
	padding-left: 5px;
}
.box-min {
	margin-top: 15px;
	min-height: 225px;
}
.del_attachment,.reply_del_attachment {
	cursor: pointer;
}
.no_margin_bt {
	margin-bottom: 0;
}
#account-timeline ul li.follow::before {
	background: #f5f5f6 none repeat scroll 0 0;
}
/*.cbp_tmtimeline > li.followup_task .cbp_tmlabel::before{margin:0;right:93%;top:-27px; border-color:transparent #f1f1f1 #fff transparent; position:absolute; border-style:solid; border-width:14px;  content: " ";}*/
.cbp_tmtimeline > li.followup_task .cbp_tmlabel::before {
	right: 100%;
	border: solid transparent;
	content: " ";
	height: 0;
	width: 0;
	position: absolute;
	pointer-events: none;
	border-right-color: #fff;
	border-width: 10px;
	top: 10px;
}
footer.main {
	clear: both;
}
.followup_task {
	margin-top: -30px;
}
#form_timeline_filter .radio + .radio, .checkbox + .checkbox {
	margin-top: 0px !important;
}
.cbp_tmtimeline > li.followup_task .cbp_tmlabel::before {
	margin: 0;
	right: 100%;
	top: 10px; /*border-color:transparent #f1f1f1 #fff transparent;*/
	position: absolute;
	border-style: solid;
	border-width: 14px;
	content: " ";
}
footer.main {
	clear: both;
}
.followup_task {
	margin-top: -30px;
}
.color-red {
	margin-left: 5px;
}
.ticket_conversations {
	cursor: pointer;
	text-decoration:underline;
}
.left-padding {
	padding-left: 0px !important;
}
.mail_subject {
	font-size: 14.4px !important;
}
.mail_message {
	font-family: "Noto Sans", sans-serif !important;
}
.no-display {
	overflow: auto;
}
.underline {
	text-decoration: underline;
}
.email_action {
	cursor: pointer;
}
.replyboxhidden{display:none; }
.replyboxemail{width:100% !important; cursor:pointer;}
</style>
