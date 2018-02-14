@extends('layout.customer.main')
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
  
  <!-- Mail Body -->
  <div class="mail-body">
    <div class="mail-header"> 
      <!-- title -->
           
      <!-- links -->
      <div class="mail-links pull-right">      
        <button type="submit" data-loading-text="Loading..." submit_value="0" class="btn btn-primary icon-left submit_btn btn-icon" style="visibility: visible;"> Send <i class="entypo-mail"></i> </button>        
         <a href="{{URL::to('customer/tickets')}}" class="btn btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Close</a>
         </div>
    </div>
    <div class="row">
    <div class="col-md-12">
    <div class="mail-compose">
      <form  id="MailBoxCompose" name="MailBoxCompose">
        <div class="form-group">
          <label  for="subject">From:</label>
          {{ Form::select('email-from', $FromEmails, '', array("class"=>"form-control select2","id"=>"email-from")) }} </div>
        <div class="form-group">
          <label for="to">To:</label>
          <input type="text" class="form-control useremailssingle" id="email-to" name="email-to" value="" tabindex="1" />
          <span><a href="javascript:;" class="emailoptiontxt" onclick="$(this).hide(); $('#cc').parent().removeClass('hidden'); $('#cc').focus();">CC</a> <a href="javascript:;" class="emailoptiontxt" onclick="$(this).hide(); $('#bcc').parent().removeClass('hidden'); $('#bcc').focus();">BCC</a> </span>
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
          <label for="Message">Message:</label>
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
          <?php  $required = array();
			   foreach($ticketsfields as $TicketfieldsData)
			   {	 
		   		 if($TicketfieldsData->FieldType=='default_requester' || $TicketfieldsData->FieldType=='default_description' || $TicketfieldsData->FieldType=='default_subject'){
					 if($TicketfieldsData->FieldType=='default_subject'){
						 $required[]  = array("id"=>'subject',"title"=>$TicketfieldsData->AgentLabel);
					 }
					  continue;	}
				 
				  $id		    =  'Ticket'.str_replace(" ","",$TicketfieldsData->FieldName);
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXT)
				 {
					 
					if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
				 ?>
          <div class="form-group">
            <label for="GroupName" class="control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <input type="text"  name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" >
          </div>
          <?php
}
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXTAREA)
				 { 
					 if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				  
				 ?>
          <div class="form-group">
            <label for="GroupDescription" class="control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <textarea   id='{{$id}}'  name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld" ></textarea>
          </div>
          <?php
					}
		}
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_CHECKBOX)
				 {
					  if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					  if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
			     ?>
           <div class="form-group">
            <label for="GroupDescription" class="control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <input class="checkbox rowcheckbox formfldcheckbox" value="" name='Ticket[{{$TicketfieldsData->FieldType}}]'  id='{{$id}}' type="checkbox">
          </div>
          <?php  }		  
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXTNUMBER)
				 { 
				 if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
				 if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
			       ?>
           <div class="form-group">
            <label for="GroupName" class=" control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <input type="number" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" value="">
          </div>
          <?php
		 }
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DROPDOWN)
				 {  
				 if($TicketfieldsData->FieldType == 'default_group' || $TicketfieldsData->FieldType == 'default_agent'){	continue;	}	
				  if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					 if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
					 ?>
          <div class="form-group">
            <label for="GroupName" class="control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <select name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld select2" id="{{$id}}" >
                <option value="0">Select</option>
                <?php
	          
			  if($TicketfieldsData->FieldType == 'default_priority'){
				$FieldValues = TicketPriority::orderBy('PriorityID', 'asc')->get(); 
					foreach($FieldValues as $key => $FieldValuesData){
					?>
                <option key="{{$key}}" @if($key==0) selected @endif   value="{{$FieldValuesData->PriorityID}}">{{cus_lang("CUST_PANEL_PAGE_TICKET_FIELDS_PRIORITY_VAL_".$FieldValuesData->PriorityValue)}}</option>
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
              </select>
          </div>
          <?php }
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DATE)
				 { 
				 	if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
				 ?>
          <div class="form-group">
            <label for="GroupName" class="control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <input type="text" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld datepicker" data-date-format="yyyy-mm-dd" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" >
          </div>
          <?php }					 
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DECIMAL)
				 {
					  if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				    
				 ?>
           <div class="form-group">
            <label for="GroupName" class="control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <input type="text" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" >
              </div>
            <?php				  }
				 }
		  }
	?>
        <input type="hidden" name="Page" value="DetailPage">
        <?php } ?>
        <!-- ticket fields end -->
      </form>
    </div>
    </div>
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
/*#s2id_email-from{padding-left:50px !important;}*/
.mail-env .mail-body{width:100% !important;}
.ticketboxlabel{}
.compose_table tr td{padding:2px; padding-top:15px;}
.compose_table tr {margin-top:10px;}
.compose_table .select2-container{padding-left:0px !important; }
.mail-env .mail-body .mail-compose .form-group{position:static;border-bottom:none; padding-bottom:0px;}
.mail-env .mail-body .mail-compose .form-group label{position:static; left:auto;top:auto;}
/*#s2id_email-from a:first-child{border:none !important;}*/
.emailoptiontxt{font-size:10px;}
#subject{padding-left:10px !important;}
.mail-env .mail-body .mail-compose .form-group input{padding-left:10px !important; border-color: #c8cdd7;}
.mail-env .mail-body .mail-compose .form-group input:focus{background:none;}
.mail-env .mail-body .mail-compose .form-group input{

  padding: 6px 12px;
  font-size: 12px;
  line-height: 1.42857143;
  color: #555555;
  background-color: #ffffff;
  background-image: none;
  border-radius: 3px;
 -webkit-transition: border-color ease-in-out .15s, -webkit-box-shadow ease-in-out .15s;
  -moz-transition: border-color ease-in-out .15s, -moz-box-shadow ease-in-out .15s;
  -moz-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
  -o-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
  -webkit-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
}
.mail-env .mail-body .mail-compose .form-group input:focus {
  border-color: #c8cdd7;
  outline: 0;
  -moz-box-shadow:  0 2px 1px rgba(203, 208, 217, 0.08);
  -webkit-box-shadow:  0 2px 1px rgba(203, 208, 217, 0.08);
  box-shadow:  0 2px 1px rgba(203, 208, 217, 0.08);
}
</style>
<script>
var editor_options 	  =  		{};
$(document).ready(function(e) {
	 $('.useremails').select2({
            tags:{{$AllEmails}}
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
						window.location = "{{URL::to('tickets')}}";							
					}else{
						toastr.error(response.message, "Error", toastr_opts);
					}                   
					$('.btn').button('reset');
					$('.submit_btn').removeClass('disabled');
				}
				});	
		return false;		
    });
    show_summernote($(".wysihtml5box"),editor_options);

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
	$('.useremailssingle').select2({
    tags: true,
	 tags:{{$AllEmails}},
    tokenSeparators: [','],
  // max emails is 1
    maximumSelectionSize:1,

    // override message for max tags
    formatSelectionTooBig: function (limit) {
        return "Maximum "+limit+" email is allowed";
    }
});			
});
</script> 
@stop 