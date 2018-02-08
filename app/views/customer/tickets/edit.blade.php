@extends('layout.customer.main')

@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li> <a href="{{URL::to('/customer/tickets')}}">Tickets</a> </li>
  <li class="active"> <strong>Update Ticket</strong> </li>
</ol>
<h3>Update Ticket</h3>
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
        <form role="form" id="form-tickets-edit" method="post" action="{{URL::to('/customer/tickets/'.$TicketID.'/update')}}" class="form-horizontal form-groups-bordered">
          <?php  $required = array();
			   foreach($Ticketfields as $TicketfieldsData)
			   {
				  $id		    =  'Ticket'.str_replace(" ","",$TicketfieldsData->FieldName);
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXT)
				 {
				 ?>
          <div class="form-group">            
            <?php			
			if($TicketfieldsData->FieldType == 'default_requester')
			 {continue;}else{
				if($TicketfieldsData->CustomerReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->CustomerLabel); }
				 ?>
                 <label for="GroupName" class="col-sm-3 control-label">{{$TicketfieldsData->CustomerLabel}}</label>
            <div class="col-sm-9">
              <input type="text"  name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->CustomerLabel}}" value="{{$ticketSavedData[$TicketfieldsData->FieldType]}}">
            </div>
            <?php } ?>
          </div>
          <?php
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXTAREA)
				 { 
				 	$class_textarea = '';
				 	 if($TicketfieldsData->FieldType == 'default_description'){
						$class_textarea = 'wysihtml5box';
					 }
					 if($TicketfieldsData->CustomerReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->CustomerLabel); }
				 ?>
          <div class="form-group">
            <label for="GroupDescription" class="col-sm-3 control-label">{{$TicketfieldsData->CustomerLabel}}</label>
            <div class="col-sm-9">
              <textarea   id='{{$id}}'  name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld {{$class_textarea}}" >{{$ticketSavedData[$TicketfieldsData->FieldType]}}</textarea>
              <div class="form-group email_attachment">
            <input type="hidden" value="1" name="email_send" id="email_send"  />
            <!--   <input id="filecontrole" type="file" name="emailattachment[]" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden" multiple data-label="<i class='entypo-attach'></i>Attachments" />-->
            
            <input id="emailattachment_sent" type="hidden" name="emailattachment_sent" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"   />
            <input id="info2" type="hidden" name="attachmentsinfo" />
            <span class="file-input-names">@if(isset($ticketSavedData['AttachmentPaths']['text'])){{$ticketSavedData['AttachmentPaths']['text']}}@endif</span> </div>
            </div>
          </div>
          <?php if($class_textarea == 'wysihtml5box'){ ?>
          <p class="comment-box-options-activity"> <a id="addTtachment" class="btn-sm btn-white btn-xs" title="@lang('routes.MESSAGE_ADD_AN_ATTACHMENT')" href="javascript:void(0)"> <i class="entypo-attach"></i> </a> </p>
          
          <?php } ?>
          <?php
			     }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_CHECKBOX)
				 {
					  if($TicketfieldsData->CustomerReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->CustomerLabel); }
			     ?>
          <div class="form-group">
            <label for="GroupDescription" class="col-sm-3 control-label">{{$TicketfieldsData->CustomerLabel}}</label>
            <div class="col-sm-9">
              <input class="checkbox rowcheckbox formfldcheckbox" value="{{$ticketSavedData[$TicketfieldsData->FieldType]}}" name='Ticket[{{$TicketfieldsData->FieldType}}]' @if($ticketSavedData[$TicketfieldsData->FieldType]==1) checked @endif id='{{$id}}' type="checkbox">
            </div>
          </div>
          <?php 		  
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXTNUMBER)
				 { 
				 if($TicketfieldsData->CustomerReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->CustomerLabel); }
			       ?>
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">{{$TicketfieldsData->CustomerLabel}}</label>
            <div class="col-sm-9">
              <input type="number" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->CustomerLabel}}" value="{{$ticketSavedData[$TicketfieldsData->FieldType]}}">
            </div>
          </div>
          <?php		 
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DROPDOWN)
				 { 
				 if($TicketfieldsData->FieldType == 'default_group' || $TicketfieldsData->FieldType == 'default_agent'){
			 		continue;
				 }
				  if($TicketfieldsData->CustomerReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->CustomerLabel); }
					 ?>
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">{{$TicketfieldsData->CustomerLabel}}</label>
            <div class="col-sm-9">
              <select name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld select2" id="{{$id}}" >
              <option value="0">Select</option>
                <?php
			  if($TicketfieldsData->FieldType == 'default_priority'){
				$FieldValues = TicketPriority::orderBy('PriorityID', 'asc')->get(); 
					foreach($FieldValues as $FieldValuesData){
					?> 
                <option  @if($ticketSavedData[$TicketfieldsData->FieldType]==$FieldValuesData->PriorityID) selected @endif  value="{{$FieldValuesData->PriorityID}}">{{cus_lang("CUST_PANEL_PAGE_TICKET_FIELDS_PRIORITY_VAL_".$FieldValuesData->PriorityValue)}}</option>
                <?php 
					}
				}				
				else
				{	 
					$FieldValues = TicketfieldsValues::where(["FieldsID"=>$TicketfieldsData->TicketFieldsID])->orderBy('FieldOrder', 'asc')->get();
					foreach($FieldValues as $FieldValuesData){
					?>
                <option @if($ticketSavedData[$TicketfieldsData->FieldType]==$FieldValuesData->ValuesID) selected  @endif  value="{{$FieldValuesData->ValuesID}}">{{$FieldValuesData->FieldValueCustomer}}</option>
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
				 	if($TicketfieldsData->CustomerReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->CustomerLabel); }
				 ?>
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">{{$TicketfieldsData->CustomerLabel}}</label>
            <div class="col-sm-9">
              <input type="text" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld datepicker" data-date-format="yyyy-mm-dd" id="{{$id}}" placeholder="{{$TicketfieldsData->CustomerLabel}}" value="{{$ticketSavedData[$TicketfieldsData->FieldType]}}">
            </div>            
          </div>
          <?php					 
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DECIMAL)
				 {
					  if($TicketfieldsData->CustomerReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->CustomerLabel); }
				 ?>
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">{{$TicketfieldsData->CustomerLabel}}</label>
            <div class="col-sm-9">
              <input type="text" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->CustomerLabel}}" value="{{$ticketSavedData[$TicketfieldsData->FieldType]}}">
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

@if(isset($ticketSavedData['AttachmentPaths']['attachmentsinfo']))
var img_array		   =    '{{$ticketSavedData['AttachmentPaths']['attachmentsinfo']}}';
	
	jQuery('#info1').val(img_array);
    jQuery('#info2').val(img_array);
	var img_array_final = jQuery.parseJSON(img_array);
	for (var i = 0, len = img_array_final.length; i < len; ++i) {
   	 emailFileList.push(img_array_final[i].filename);	
 }
@endif

    jQuery(document).ready(function(e) {
		
		function validate_form()
		{
			
			 var required_flds_data = jQuery.parseJSON(required_flds);
			 var error_msg = '';
			 
				required_flds_data.forEach(function(element) {
					var  CurrentElementVal = 	jQuery('#'+element.id).val();  //console.log(element.id+'-'+CurrentElementVal);
				
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
        jQuery(".save.btn").click(function(ev) {
			if(validate_form()){
            	jQuery('#form-tickets-edit').submit();      
			}
      });
	  
	  jQuery(document).on('submit','#form-tickets-edit',function(e){		 
		 jQuery('.btn').attr('disabled', 'disabled');	 
		 jQuery('.btn').button('loading');
	
		e.stopImmediatePropagation();
		e.preventDefault();
		var formData = new FormData(jQuery(this)[0]);
		var ajax_url = baseurl+'/customer/tickets/{{$TicketID}}/update';
		 jQuery.ajax({
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
						window.location.href= baseurl+'/customer/tickets';
					}else{
						toastr.error(response.message, "Error", toastr_opts);
					}                   
					jQuery('.btn').button('reset');
					jQuery('.btn').removeClass('disabled');		
				}
				});	
		return false;		
    });

		show_summernote(jQuery('.wysihtml5box'),editor_options);
				jQuery('.unknownemailaction').click(function(e) {
				var unknown_action_type 	= 	jQuery(this).attr('unknown_action_type');			
				//window.location = baseurl+'/'+unknown_action_type+'/create';
				window.open(baseurl+'/customer/'+unknown_action_type+'/create', '_blank');
        });
		
		 jQuery('#addTtachment').click(function(){
			 file_count++;                
				//var html_img = '<input id="filecontrole'+file_count+'" multiple type="file" name="emailattachment[]" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"  />';
				//jQuery('.emai_attachments_span').html(html_img);
				jQuery('#filecontrole1').click();
				
            });


            jQuery(document).on('change','#filecontrole1',function(e){
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
   					jQuery('#emai_attachments_form').submit();
				}

            });
			
jQuery('#emai_attachments_form').submit(function(e) {
	e.stopImmediatePropagation();
    e.preventDefault();

    var formData = new FormData(this);
    var url = 	baseurl + '/customer/tickets/upload_file';
    jQuery.ajax({
        url: url,  //Server script to process data
        type: 'POST',
        dataType: 'json',
	    contentType: false,
        processData: false,
        success: function (response) {
            if(response.status =='success'){
                jQuery('.file-input-names').html(response.data.text);
                jQuery('#info1').val(JSON.stringify(response.data.attachmentsinfo));
                jQuery('#info2').val(JSON.stringify(response.data.attachmentsinfo));

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

jQuery(document).on("click",".del_attachment",function(ee){
                var url  =  baseurl + '/customer/tickets/delete_attachment_file';
                var fileName   =  jQuery(this).attr('del_file_name');
                var attachmentsinfo = jQuery('#info1').val();
                if(!attachmentsinfo){
                    return true;
                }
                attachmentsinfo = jQuery.parseJSON(attachmentsinfo);
                jQuery(this).parent().remove();
                var fileIndex = emailFileList.indexOf(fileName);
                var fileinfo = attachmentsinfo[fileIndex]; 
                emailFileList.splice(fileIndex, 1);
                attachmentsinfo.splice(fileIndex, 1);
                jQuery('#info1').val(JSON.stringify(attachmentsinfo));
                jQuery('#info2').val(JSON.stringify(attachmentsinfo));
                jQuery.ajax({
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
			
			jQuery('.formfldcheckbox').change(function(e) {
               if ( jQuery( this ).is( ":checked" ) ){
				  	jQuery( this ).val(1);
				  }else{
				  	jQuery( this ).val(0);
				  }
            });
    });
</script> 
<style>
.email_attachment{margin-left:10px !important; margin-top:10px !important; border:none !important;}
</style>
@stop