@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li> <a href="{{action('tickets')}}">Tickets</a> </li>
  <li class="active"> <strong>New Ticket</strong> </li>
</ol>
<h3>New Ticket</h3>
<div class="panel-title"> @include('includes.errors')
  @include('includes.success') </div>
<p style="text-align: right;">
  <button type='button' class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
  <a href="{{action('tickets')}}" class="btn btn-danger btn-sm btn-icon icon-left"> <i class="entypo-cancel"></i> Close </a> </p>
<br>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-primary" data-collapsed="0">
      <div class="panel-heading">
        <div class="panel-title"> Ticket Detail </div>
        <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
      </div>
      <div class="panel-body">
        <form role="form" id="form-tickets-add" method="post" action="{{URL::to('tickets/create')}}" class="form-horizontal form-groups-bordered">
          <?php  $required = array();
			   foreach($Ticketfields as $TicketfieldsData)
			   {
				  $id		    =  'Ticket'.str_replace(" ","",$TicketfieldsData->FieldName);
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXT)
				 {
				 ?>
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <?php
			
			if($TicketfieldsData->FieldType == 'default_requester')
			 { 			 	
				$required[] =  array("id"=>$id,"title"=>$TicketfieldsData->FieldName);
			?>
            <div class="col-sm-6">
            <input type="text" name='Ticket[{{$TicketfieldsData->FieldType}}]' required id="{{$id}}" class="form-control requestersearch typeahead formfld" spellcheck="false" dir="auto"     placeholder="{{$TicketfieldsData->AgentLabel}}" />           
            <span><a href="javascript:;" class="emailoptiontxt" onclick="$(this).hide(); $('#reqcc').removeClass('hidden'); $('#cc').focus();">CC</a> </span>
            </div>
            <div class="col-sm-3 dropdown" style="padding:0;">
              <button title="Add new requester" type="button" class="btn btn-primary btn-xs  dropdown-toggle" data-toggle="dropdown">+</button>
              <ul class="dropdown-menu dropdown-green"  role="menu">
                <li> <a class="unknownemailaction clickable" unknown_action_type="accounts"  >&nbsp;<i class="fa fa-building"></i>&nbsp;&nbsp; Add new Account </a> </li>
                <li> <a class="unknownemailaction clickable" unknown_action_type="contacts"  > <i class="entypo-user"></i> &nbsp;Add new Contact</a> </li>
              </ul>
            </div>
            <?php }else{
				if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
				 ?>
            <div class="col-sm-9">
              <input type="text"  name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" value="">
            </div>
            <?php } ?>
          </div>
          <?php if($TicketfieldsData->FieldType == 'default_requester')
			 {  ?>
                <div id="reqcc" class="form-group hidden">
          <label for="cc" class="col-sm-3 control-label" for="cc">CC</label>
          <div class="col-sm-9">
          <input type="text" class="form-control useremails" id="cc" name="Ticket[cc]" value="" tabindex="2" />
          </div>
        </div>        
        <?php } ?>
          <?php
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXTAREA)
				 { 
				 	$class_textarea = '';
				 	 if($TicketfieldsData->FieldType == 'default_description'){
						$class_textarea = 'wysihtml5box';
					 }
					 if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
				 ?>
          <div class="form-group">
            <label for="GroupDescription" class="col-sm-3 control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <div class="col-sm-9">
              <textarea   id='{{$id}}'  name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld {{$class_textarea}}" ></textarea>
              <div class="form-group email_attachment">
            <input type="hidden" value="1" name="email_send" id="email_send"  />
            <!--   <input id="filecontrole" type="file" name="emailattachment[]" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden" multiple data-label="<i class='entypo-attach'></i>Attachments" />-->
            
            <input id="emailattachment_sent" type="hidden" name="emailattachment_sent" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"   />
            <input id="info2" type="hidden" name="attachmentsinfo" />
            <span class="file-input-names"></span> </div>
            </div>
          </div>
          <?php if($class_textarea == 'wysihtml5box'){ ?>
          <p class="comment-box-options-activity"> <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)"> <i class="entypo-attach"></i> </a> </p>
          
          <?php } ?>
          <?php
			     }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_CHECKBOX)
				 {
					  if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
			     ?>
          <div class="form-group">
            <label for="GroupDescription" class="col-sm-3 control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <div class="col-sm-9">
              <input class="checkbox rowcheckbox formfldcheckbox" value="0" name='Ticket[{{$TicketfieldsData->FieldType}}]' id='{{$id}}' type="checkbox">
            </div>
          </div>
          <?php 		  
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXTNUMBER)
				 { 
				 if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
			       ?>
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <div class="col-sm-9">
              <input type="number" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" value="">
            </div>
          </div>
          <?php		 
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DROPDOWN)
				 { 
				  if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					 ?>
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <div class="col-sm-9">
              <select name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld select2" id="{{$id}}" >
              <option value="0">Select</option>
                <?php
			  if($TicketfieldsData->FieldType == 'default_priority'){
				$FieldValues = TicketPriority::orderBy('PriorityID', 'asc')->get(); 
					foreach($FieldValues as $key => $FieldValuesData){
					?>
                <option @if($key==0) selected @endif value="{{$FieldValuesData->PriorityID}}">{{$FieldValuesData->PriorityValue}}</option>
                <?php 
					}
				}else  if($TicketfieldsData->FieldType == 'default_group'){
				$htmlgroupID = 'Ticket'.$TicketfieldsData->FieldName;
				$FieldValues = TicketGroups::where(['CompanyID'=>$CompanyID])->orderBy('GroupID', 'asc')->get(); 
					?>               
                <?php
					foreach($FieldValues as $FieldValuesData){
					?>
                <option @if(count($FieldValues)==1) selected @endif  value="{{$FieldValuesData->GroupID}}">{{$FieldValuesData->GroupName}}</option>
                <?php 
					}
				} else  if($TicketfieldsData->FieldType == 'default_agent'){		
				$htmlagentID = 'Ticket'.$TicketfieldsData->FieldName;		
					?>               
                <?php
					foreach($agentsAll as $FieldValuesData){
					?>
                <option @if(count($agentsAll)==1) selected @endif  value="{{$FieldValuesData->UserID}}">{{$FieldValuesData->FirstName}} {{$FieldValuesData->LastName}}</option>
                <?php 
					}
				}		
				else  if($TicketfieldsData->FieldType == 'default_status'){	 
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
          </div>
          <?php
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DATE)
				 { 
				 	if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
				 ?>
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <div class="col-sm-9">
              <input type="text" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld datepicker" data-date-format="yyyy-mm-dd" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" value="">
            </div>            
          </div>
          <?php					 
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DECIMAL)
				 {
					  if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
				 ?>
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">{{$TicketfieldsData->AgentLabel}}</label>
            <div class="col-sm-9">
              <input type="text" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" value="">
            </div>
          </div>
          <?php	 
				 }
			   }
				?>
        </form>
      </div>
    </div>
  </div>
</div>
<form id="emai_attachments_form" class="hidden" name="emai_attachments_form">
  <span class="emai_attachments_span">
  <input type="file" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" name="emailattachment[]" multiple id="filecontrole1">
  </span>
  <input  hidden="" name="token_attachment" value="{{$random_token}}" />
  <input id="info1" type="hidden" name="attachmentsinfo" />
  <button  class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
</form>
<script type="text/javascript">
var editor_options 	 	=  		{};
var file_count 		  =  		0;
var allow_extensions  = 		{{$response_extensions}};
var emailFileList	  =  		new Array();
var max_file_size_txt =	        '{{$max_file_size}}';
var max_file_size	  =	        '{{str_replace("M","",$max_file_size)}}';
var required_flds	  =          '{{json_encode($required)}}';

    jQuery(document).ready(function($) {
		 $('.useremails').select2({
            tags:{{$AllEmails}}
        });
		
		
		
			$('.requestersearch').select2({
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
		
    // Replace Checboxes
        $(".save.btn").click(function(ev) {
			if(validate_form()){
            	$('#form-tickets-add').submit();      
			}
      });
	  
	  $(document).on('change','#{{$htmlgroupID}}',function(e){
		   var changeGroupID =  	$(this).val(); 
		   
		  	if(changeGroupID==0){
		   		 $('#{{$htmlagentID}} option:gt(0)').remove();
				 return false;
			}
		   if(changeGroupID)
		   {
		   	 changeGroupID = parseInt(changeGroupID);
			 var ajax_url  = baseurl+'/ticketgroups/'+changeGroupID+'/getgroupagents';
			 $.ajax({
					url: ajax_url,
					type: 'POST',
					dataType: 'json',
					async :false,
					cache: false,
					contentType: false,
					processData: false,
					data:{s:1},
					success: function(response) { console.log(response);
					   if(response.status =='success')
					   {			
						   var $el = this;		   
						   console.log(response.data);
						   //$('#{{$htmlagentID}} option:gt(0)').remove();
						   $('#{{$htmlagentID}} option').remove();
						   $.each(response.data, function(key,value) {							  
							  $('#{{$htmlagentID}}').append($("<option></option>").attr("value", value).text(key));
							});	
							$('#{{$htmlagentID}}').trigger('change');						
						}else{
							toastr.error(response.message, "Error", toastr_opts);
						}                   
					}
					});	
		return false;		
		   }
		   
	  });
	  
	  $(document).on('submit','#form-tickets-add',function(e){		 
		 $('.btn').attr('disabled', 'disabled');	 
		 $('.btn').button('loading');
	
		e.stopImmediatePropagation();
		e.preventDefault();
		var formData = new FormData($(this)[0]);
		var ajax_url = baseurl+'/tickets/store';
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
						window.location.href= baseurl+'/tickets';
					}else{
						toastr.error(response.message, "Error", toastr_opts);
					}                   
					$('.btn').button('reset');
					$('.btn').removeClass('disabled');		
				}
				});	
		return false;		
    });
	show_summernote($('.wysihtml5box'),editor_options);

				
				$('.unknownemailaction').click(function(e) {
				var unknown_action_type 	= 	$(this).attr('unknown_action_type');			
				//window.location = baseurl+'/'+unknown_action_type+'/create';
				window.open(baseurl+'/'+unknown_action_type+'/create', '_blank');
        });
		
		 $('#addTtachment').click(function(){
			 file_count++;                
				//var html_img = '<input id="filecontrole'+file_count+'" multiple type="file" name="emailattachment[]" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"  />';
				//$('.emai_attachments_span').html(html_img);
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
    var url = 	baseurl + '/tickets/upload_file';
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
			
			$('.formfldcheckbox').change(function(e) {
               if ( $( this ).is( ":checked" ) ){
				  	$( this ).val(1);
				  }else{
				  	$( this ).val(0);
				  }
            });
			
			$('#{{$htmlgroupID}}').change();
    });
</script> 
<style>
.email_attachment{margin-left:10px !important; margin-top:10px !important; border:none !important;}
</style>
@stop