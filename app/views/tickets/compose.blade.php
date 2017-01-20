@extends('layout.main')
@section('content')
<?php $required  = array(); ?>
<ol class="breadcrumb bc-3">
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li> <a href="{{action('tickets')}}">Tickets</a> </li>
  <li class="active"> <strong>Email</strong> </li>
</ol>
<h3>Emails</h3>
@include('includes.errors')
@include('includes.success')
<div class="mail-env"> 
  <!-- compose new email button -->
  <div class="mail-sidebar-row visible-xs"> <a href="mailbox-compose.html" class="btn btn-success btn-icon btn-block"> Compose Mail <i class="entypo-pencil"></i> </a> </div>
  
  <!-- Mail Body -->
  <div class="mail-body">
    <div class="mail-header"> 
      <!-- title -->
      <div class="mail-title"> Compose Mail <i class="entypo-pencil"></i> </div>
      
      <!-- links -->
      <div class="mail-links">
        <button type="submit" data-loading-text="Loading..." submit_value="0" class="btn btn-success submit_btn btn-icon" style="visibility: visible;"> Send <i class="entypo-mail"></i> </button>
      </div>
    </div>
    <div class="mail-compose">
      <form  id="MailBoxCompose" name="MailBoxCompose">
        <div class="form-group">
          <label  for="subject">From:</label>
          {{ Form::select('email-from', $FromEmails, '', array("class"=>"form-control select2","id"=>"email-from")) }} </div>
        <div class="form-group">
          <label for="to">To:</label>
          {{ Form::select('email-to', $AllEmailsTo, '', array("class"=>"form-control useremailssingle","id"=>"email-to")) }}           
          <span><a href="javascript:;" onclick="$(this).hide(); $('#cc').parent().removeClass('hidden'); $('#cc').focus();">CC</a> <a href="javascript:;" onclick="$(this).hide(); $('#bcc').parent().removeClass('hidden'); $('#bcc').focus();">BCC</a> </span>
        </div>
        
        <div class="form-group hidden">
          <label for="cc">CC:</label>
          <input type="text" class="form-control useremails" id="cc" name="cc" value="" tabindex="2" />
        </div>
        <div class="form-group hidden">
          <label for="bcc">BCC:</label>
          <input type="text" class="form-control useremails" id="bcc" name="bcc" value="" tabindex="2" />
        </div>
        <div class="form-group">
          <label for="subject">Subject:</label>
          <input type="text" class="form-control subject" id="subject" name="Subject" value="" tabindex="1" />
        </div>
        <div class="compose-message-editor">
          <textarea id="Message" name="Message" class="form-control wysihtml5box" ></textarea>
        </div>
        <p class="comment-box-options-activity"> <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)"> <i class="entypo-attach"></i> </a> </p>
        <div class="form-group email_attachment">
          <input type="hidden" value="1" name="email_send" id="email_send"  />
          <input id="emailattachment_sent" type="hidden" name="emailattachment_sent" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"   />
          <input id="info2" type="hidden" name="attachmentsinfo"  />
          <span class="file-input-names"></span> </div>
        <input type="submit" class="hidden" value=""  />
        <input type="hidden" class="EmailCall" value="{{Messages::Sent}}" name="EmailCall" />
        <!-- ticket fields start -->
        <?php if(count($ticketsfields)>0){ ?>
        <table width="100%"  class="compose_table" cellpadding="10" cellspacing="10">
          <?php  $required = array();
			   foreach($ticketsfields as $TicketfieldsData)
			   {	 
		   		 if($TicketfieldsData->FieldType=='default_requester' || $TicketfieldsData->FieldType=='default_description' || $TicketfieldsData->FieldType=='default_subject'){ continue;	}
				 
				  $id		    =  'Ticket'.str_replace(" ","",$TicketfieldsData->FieldName);
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXT)
				 {
					 
					if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
				 ?>
          <tr>
            <td width="20%"><label for="GroupName" class="control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <td><input type="text"  name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" ></td>
          </tr>
          <?php
}
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXTAREA)
				 { 
					 if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				  
				 ?>
          <tr>
            <td width="20%"><label for="GroupDescription" class="control-label">{{$TicketfieldsData->AgentLabel}}</label></td>
            <td><textarea   id='{{$id}}'  name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld" ></textarea></td>
          </tr>
          <?php
					}
		}
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_CHECKBOX)
				 {
					  if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					  if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
			     ?>
          <tr>
            <td width="20%"><label for="GroupDescription" class="control-label">{{$TicketfieldsData->AgentLabel}}</label></td>
            <td><input class="checkbox rowcheckbox formfldcheckbox" value="" name='Ticket[{{$TicketfieldsData->FieldType}}]'  id='{{$id}}' type="checkbox"></td>
          </tr>
          <?php  }		  
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXTNUMBER)
				 { 
				 if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
				 if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
			       ?>
          <tr>
            <td width="20%"><label for="GroupName" class=" control-label">{{$TicketfieldsData->AgentLabel}}</label></td>
            <td><input type="number" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" value=""></td>
          </tr>
          <?php
		 }
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DROPDOWN)
				 {  
				 if($TicketfieldsData->FieldType == 'default_group' || $TicketfieldsData->FieldType == 'default_agent'){	continue;	}	
				  if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					 if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
					 ?>
          <tr>
            <td width="20%"><label for="GroupName" class="control-label">{{$TicketfieldsData->AgentLabel}}</label></td>
            <td><select name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld select2" id="{{$id}}" >
                <option value="0">Select</option>
                <?php
	          
			  if($TicketfieldsData->FieldType == 'default_priority'){
				$FieldValues = TicketPriority::orderBy('PriorityID', 'asc')->get(); 
					foreach($FieldValues as $key => $FieldValuesData){
					?>
                <option key="{{$key}}" @if($key==0) selected @endif   value="{{$FieldValuesData->PriorityID}}">{{$FieldValuesData->PriorityValue}}</option>
                <?php 
					}
				}	else  if($TicketfieldsData->FieldType == 'default_status'){	 
					$FieldValues = TicketfieldsValues::where(["FieldsID"=>$TicketfieldsData->TicketFieldsID])->orderBy('FieldOrder', 'asc')->get();
					foreach($FieldValues as $FieldValuesData){
					?>
                <option @if($FieldValuesData->ValuesID == $default_status) selected @endif value="{{$FieldValuesData->ValuesID}}">{{$FieldValuesData->FieldValueAgent}}</option>
                <?php
					}
				}								
				else
				{
			 	 
					$FieldValues = TicketfieldsValues::where(["FieldsID"=>$TicketfieldsData->TicketFieldsID])->orderBy('FieldOrder', 'asc')->get();
					foreach($FieldValues as $FieldValuesData){
					?>
                <option value="{{$FieldValuesData->ValuesID}}">{{$FieldValuesData->FieldValueAgent}}</option>
                <?php
					}
		}
			  	
				?>
              </select></td>
          </tr>
          <?php }
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DATE)
				 { 
				 	if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
				 ?>
          <tr>
            <td width="20%"><label for="GroupName" class="control-label">{{$TicketfieldsData->AgentLabel}}</label></td>
            <td><input type="text" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld datepicker" data-date-format="yyyy-mm-dd" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" ></td>
          </tr>
          <?php }					 
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DECIMAL)
				 {
					  if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				    
				 ?>
          <tr>
            <td width="20%"><label for="GroupName" class="control-label">{{$TicketfieldsData->AgentLabel}}</label></td>
            <td><input type="text" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" ></td>
              </td>
            <?php				  }
				 }
		  }
	?>
        </table>
        <input type="hidden" name="Page" value="DetailPage">
        <?php } ?>
        <!-- ticket fields end -->
      </form>
    </div>
  </div>
  <!-- Sidebar -->
  <form id="emai_attachments_form" class="hidden" name="emai_attachments_form">
    <span class="emai_attachments_span">
    <input type="file" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" name="emailattachment[]" multiple id="filecontrole1">
    </span>
    <input  hidden="" name="token_attachment" value="{{$random_token}}" />
    <input id="info1" type="hidden" name="attachmentsinfo"  />
    <button  class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
  </form>
</div>
<?php //print_r($required); exit; ?>
<style>
.mail-env .mail-body .mail-header .mail-title{width:70% !important;}
.mail-env .mail-body .mail-header .mail-search, .mail-env .mail-body .mail-header .mail-links{width:30% !important;}
.select2-container,#s2id_email-to{padding-left:30px !important;}
#s2id_email-from{padding-left:50px !important;}
.mail-env .mail-body{width:100% !important;}
.ticketboxlabel{}
.compose_table tr td{padding:2px; padding-top:15px;}
.compose_table tr {margin-top:10px;}
.compose_table .select2-container{padding-left:0px !important; }
#s2id_email-from a:first-child{border:none !important;}
</style>
<link rel="stylesheet" href="{{ URL::asset('assets/js/wysihtml5/bootstrap-wysihtml5.css') }}">
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/wysihtml5-0.4.0pre.min.js"></script> 
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.js"></script> 
<script>

$(document).ready(function(e) {
	 $('.useremails').select2({
            tags:{{$AllEmails}}
        });
		
		
		$('.useremailssingle').select2({           
			 maximumSelectionLength: 1,
        });
		
		
	
	var ajax_url 		   = 	baseurl+'/tickets/SendMail';
	var file_count 		   =  	0;
	var allow_extensions   = 	{{$response_extensions}};
	var emailFileList	   =  	new Array();
	var max_file_size_txt  =	'{{$max_file_size}}';
	var max_file_size	   =	'{{str_replace("M","",$max_file_size)}}';
	
	
		var required_flds	   =    '{{json_encode($required)}}';
	 
		
			$('.formfldcheckbox').change(function(e) {
               if ( $( this ).is( ":checked" ) ){
				  	$( this ).val(1);
				  }else{
				  	$( this ).val(0);
				  }
            });
			
    
	
		function validate_form()
		{
			
			 var required_flds_data = jQuery.parseJSON(required_flds);
			 var error_msg = '';
			 
				required_flds_data.forEach(function(element) {
					var  CurrentElementVal = 	$('#'+element.id).val();  //console.log(element.id+'-'+CurrentElementVal);
				
					if(CurrentElementVal=='' || CurrentElementVal==0)
					{
						error_msg += element.title+' field is required<br>';						
					}				
				});
				if(error_msg!='')
				{
					toastr.error(error_msg, "Error", toastr_opts);	
					return false;	
				}				
				else{
					return true;	
				}		
		}


	$('.submit_btn').click(function(e) {  
		if(validate_form()){
            $('#MailBoxCompose').submit();
		}
		
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
						ShowToastr("success",response.message); 			
						document.getElementById('MailBoxCompose').reset();		
						$('.select2-search-choice-close').click();								
					}else{
						toastr.error(response.message, "Error", toastr_opts);
					}                   
					$('.btn').button('reset');
					$('.submit_btn').removeClass('disabled');
				}
				});	
		return false;		
    });		
	$('.wysihtml5box').wysihtml5({
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
    var url = 	baseurl + '/tickets/upload_file';
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
                var url  =  baseurl + '/tickets/delete_attachment_file';
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