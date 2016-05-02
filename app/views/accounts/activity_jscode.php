<?php //echo $response_extensions; exit; ?>
<script type="text/javascript">
var show_popup		  = 	 	0;
var rowData 		  = 	 	[];
var scroll_more 	  =  		1;
var file_count 		  =  		0;
var current_tab       =  		'';
var allow_extensions  = 		<?php echo $response_extensions; ?>;
var account_id		  =			<?php echo $AccountID; ?>;
var email_file_list	  =  		new Array();
var token			  =			'<?php echo $token ; ?>';
var max_file_size_txt =	        '<?php echo $max_file_size; ?>';
var max_file_size	  =	        <?php echo str_replace("M","",$max_file_size); ?>;

    jQuery(document).ready(function ($) {	
		
		var per_scroll 		= 	<?php echo $per_scroll; ?>;
		var per_scroll_inc  = 	per_scroll;
		
		  $("#email-from [name=email_template]").change(function(e){
            var templateID = $(this).val();
            if(templateID>0) {
                var url = baseurl + '/accounts/' + templateID + '/ajax_template';
                $.get(url, function (data, status) {
                    if (Status = "success") {
                        editor_reset(data);
                    } else {
                        toastr.error(status, "Error", toastr_opts);
                    }
                });
            }
        });

		        function editor_reset(data){
				var doc = $('.mail-compose');
		  		doc.find('.wysihtml5-sandbox, .wysihtml5-toolbar').remove();
        		doc.find('.message').show();
						
								
            var doc = $(".mail-compose");
            if(!Array.isArray(data)){				
                var EmailTemplate = data['EmailTemplate'];
                doc.find('[name="Subject"]').val(EmailTemplate.Subject);
                doc.find('.message').val(EmailTemplate.TemplateBody);
            }else{
                doc.find('[name="Subject"]').val('');
                doc.find('.message').val('');
            }
			
			doc.find('.message').wysihtml5({
				"font-styles": true,
				"leadoptions":false,
				"Crm":true,
				"emphasis": true,
				"lists": true,
				"html": true,
				"link": true,
				"image": true,
				"color": false,
				parser: function(html) {
					return html;
				}
			});
           
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

	if(scroll_more==0)
	{
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
                success: function(response1) {
						if (isJson(response1)) {
							
					var response_json  =  JSON.parse(response1);
					if(response_json.message=='infinity')
					{
						var html_end  ='<li><time class="cbp_tmtime"></time><div class="cbp_tmicon bg-info end_timeline_logo "><i class="entypo-infinity"></i></div><div class="end_timeline cbp_tmlabel"><h2></h2><div class="details no-display"></div></div></li>';
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
       	 doc.find('.message').wysihtml5({
				"font-styles": true,
				"leadoptions":false,
				"Crm":true,
				"emphasis": true,
				"lists": true,
				"html": true,
				"link": true,
				"image": true,
				"color": false,
				parser: function(html) {
					return html;
				}
			});

			}else{
				 var doc = $('.mail-compose');
		  		doc.find('.wysihtml5-sandbox, .wysihtml5-toolbar').remove();
        		doc.find('.message').show();
			}
			
			if(divName=='box-1')
			{	
				var doc = $('#box-1');
			 doc.find('#note-content').wysihtml5({
					"font-styles": true,
					"leadoptions":false,
					"Crm":false,
					"emphasis": true,
					"lists": true,
					"html": true,
					"link": true,
					"image": true,
					"color": false,
					parser: function(html) {
						return html;
					}
				});
			}
			else
			{
				var doc = $('#box-1');
		  		doc.find('.wysihtml5-sandbox, .wysihtml5-toolbar').remove();
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
			
			$(document).on("click",".del_attachment",function(ee){
				var file_delete_url 	= 	baseurl + '/account/delete_actvity_attachment_file';
			
				
				var del_file_name   =  $(this).attr('del_file_name');
				$(this).parent().remove();
				var index_file = email_file_list.indexOf(del_file_name);
				 email_file_list.splice(index_file, 1);
				 
				$.ajax({
                url: file_delete_url,
                type: 'POST',
                dataType: 'html',
				data:{file:del_file_name,token:token},
				async :false,
                success: function(response1) {},
				});	
				
			});
			


$('#emai_attachments_form').submit(function(e) {
	e.stopImmediatePropagation();
    e.preventDefault();		
    var formData_attachment = 	new FormData(this);
	var file_upload_url 	= 	baseurl + '/account/upload_file';
	
		$.ajax({
                url: file_upload_url,
                type: 'POST',
                dataType: 'html',
				async :false,
				data:formData_attachment,
				cache: false,
				contentType: false,
				processData: false,
                success: function(response) {
                    if (isJson(response)) {
                        var response_json  =  JSON.parse(response);
                        ShowToastr("error",response_json.message);
                    } else {
                        $('.file-input-names').html(response);
                    }
					},
			})
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
					var index_file = email_file_list.indexOf(f.name);
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
						//email_file_list.push(f.name);
						local_array.push(f.name);
					}
				}
				else
				{
					ShowToastr("error",ext_current_file+" file type not allowed.");
					
				}
        });
        		if(local_array.length>0 && file_check==1)
				{	 email_file_list = email_file_list.concat(local_array);
   					$('#emai_attachments_form').submit();	
				}

            });
				
				
			
			//////////////
        });
        $("#check-lead").click(function () {
            var checkVal = $("#check-lead").val();
            if (checkVal == "No") {
                $("#new-deal-fields-timeline").removeClass('no-display');
                $("#exsiting-lead-timeline").addClass('no-display');
            }
            else if (checkVal == "Yes") {
                $("#exsiting-lead-timeline").removeClass('no-display');
                $("#new-deal-fields-timeline").addClass('no-display');
            }
        });
        $("#lead-company").click(function () {

            var companyName = $("#lead-company").val();
            debugger;
            if (companyName == "Code Desk") {

                $('#lead-contact-list').append('<option>Abdul</option><option>Aamir</option>');

                $("#lead-phone").val('123456');
                $("#lead-email").val('contact@code-desk.com');
            }
            else if (companyName == "Wave-Tel") {
                $('#lead-contact-list').append('<option>Sumera</option><option>Abubakar</option>');
                $("#lead-phone").val('123456');
                $("#lead-email").val('contact@wave-tel.com');
            }
        });
        $("#deal-add").click(function () {

            var dealName = $("#dealName").val();
            var dealOwner = $("#dealOwner").val();
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
            var Html = '<div class="col-md-6" ><div class="board-column-item-inner deal" style="border: 1px solid #333; margin-top: 5px;"><div class="deal-details text-center"> <h6 class="m-top-0"><a href="#"> <strong>' + dealCompany + '</strong></a></h6><p><strong>' + dealName + '</strong></p><p><strong>Deal Owner:</strong> ' + dealOwner + '</p></div></div></div>'
            $("#first-deal").append(Html);
            var dialogDeal = document.getElementById('window-deal');
            dialogDeal.close('destroy');


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
				
                ShowToastr("success","Note Successfully Created");  
				document.getElementById('notes-from').reset();
				$('#box-1 .wysihtml5-sandbox').contents().find('body').html('');
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
					per_scroll = count;
					 $('#timeline-ul li:eq(0)').before(response);
				}

            } show_popup=0;
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
						per_scroll = count;
						ShowToastr("success","Task Successfully Created");                     
						$('#timeline-ul li:eq(0)').before(response);
						document.getElementById('save-task-form').reset();
					}
                    show_popup=0;
				    $("#save-task").button('reset');
			   	    $("#save-task").removeClass('disabled');
                    //getOpportunities();
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
		
		$('#save-mail').click(function(e) {  empty_images_inputs();  $('.btn-send-mail').addClass('disabled'); $(this).button('loading');            show_popup = 0; });
		$('#save-email-follow').click(function(e) { empty_images_inputs();  $('.btn-send-mail').addClass('disabled'); $(this).button('loading');    show_popup = 1; });
		
		$('#save-note').click(function(e) {       $('.save-note-btn').addClass('disabled'); $(this).button('loading');      show_popup = 0; });
		$('#save-note-follow').click(function(e) {  $('.save-note-btn').addClass('disabled'); $(this).button('loading');    show_popup = 1; });
		
		
		function empty_images_inputs()
		{
			$('.fileUploads').val();
			$('#emailattachment_sent').val(email_file_list);
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
				email_file_list = [];
				//$('.fileUploads').remove();
				$('.file_upload_span').remove();
				 $('#box-2 .wysihtml5-sandbox').contents().find('body').html('');
                ShowToastr("success","Email Sent Successfully"); 
				document.getElementById('email-from').reset();				
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
					 per_scroll = count;
					 $('#timeline-ul li:eq(0)').before(response);
				}
				///				
				
            } show_popup=0;
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
