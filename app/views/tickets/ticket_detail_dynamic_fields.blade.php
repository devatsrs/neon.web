<?php
$readonly = ''; $disable = ''; if(!User::checkCategoryPermission('Tickets','Edit')){  $readonly = 'readonly'; $disable = 'disabled'; } 
 if(count($ticketsfields)>0){ ?>
<form role="form" id="form-tickets-edit" method="post"  class="form-horizontal form-groups-bordered validate" novalidate>
  <?php  $required = array();
			   foreach($ticketsfields as $TicketfieldsData)
			   {	 
		   		 if($TicketfieldsData->FieldType=='default_requester' || $TicketfieldsData->FieldType=='default_description' || $TicketfieldsData->FieldType=='default_subject'){ continue;	}
				 
				  $id		    =  'Ticket'.str_replace(" ","",$TicketfieldsData->FieldName);
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXT)
				 {
					 
					if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
				 ?>
  <div class="form-group">
    <label for="GroupName" class="col-md-4 control-label">{{$TicketfieldsData->AgentLabel}}</label>
    <div class="col-sm-8">
      <input {{$readonly}} type="text"  name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" value="{{$ticketSavedData[$TicketfieldsData->FieldType]}}">
    </div>
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
    <label for="GroupDescription" class="col-md-4 control-label">{{$TicketfieldsData->AgentLabel}}</label>
    <div class="col-sm-8">
      <textarea   id='{{$id}}'  name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld" >{{$ticketSavedData[$TicketfieldsData->FieldType]}}</textarea>
    </div>
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
    <label for="GroupDescription" class="col-md-4 control-label">{{$TicketfieldsData->AgentLabel}}</label>
    <div class="col-sm-8">
      <input {{$readonly}} class="checkbox rowcheckbox formfldcheckbox" value="{{$ticketSavedData[$TicketfieldsData->FieldType]}}" name='Ticket[{{$TicketfieldsData->FieldType}}]' @if($ticketSavedData[$TicketfieldsData->
      FieldType]==1) checked @endif id='{{$id}}' type="checkbox"> </div>
  </div>
  <?php  }		  
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXTNUMBER)
				 { 
				 if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
				 if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
			       ?>
  <div class="form-group">
    <label for="GroupName" class="col-md-4 control-label">{{$TicketfieldsData->AgentLabel}}</label>
    <div class="col-sm-8">
      <input {{$readonly}} type="number" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" value="{{$ticketSavedData[$TicketfieldsData->FieldType]}}">
    </div>
  </div>
  <?php
		 }
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DROPDOWN)
				 { 
				  if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					 if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
					 ?>
  <div class="form-group">
    <label for="GroupName" class="col-md-4 control-label">{{$TicketfieldsData->AgentLabel}}</label>
    <div class="col-sm-8">
      <select {{$disable}} name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld select2" id="{{$id}}" >
        <option value="0">Select</option>
        <?php
	          
			  if($TicketfieldsData->FieldType == 'default_priority'){
				$FieldValues = TicketPriority::orderBy('PriorityID', 'asc')->get(); 
					foreach($FieldValues as $FieldValuesData){
					?>
        <option  @if($ticketSavedData[$TicketfieldsData->FieldType]==$FieldValuesData->PriorityID) selected @endif  value="{{$FieldValuesData->PriorityID}}">{{$FieldValuesData->PriorityValue}}</option>
        <?php 
					}
				}else  if($TicketfieldsData->FieldType == 'default_group'){
				$htmlgroupID = 'Ticket'.$TicketfieldsData->FieldName;
				$FieldValues = TicketGroups::where(['CompanyID'=>$CompanyID])->orderBy('GroupID', 'asc')->get(); 
					?>
        <?php
					foreach($FieldValues as $FieldValuesData){
					?>
        <option @if($ticketSavedData[$TicketfieldsData->FieldType]==$FieldValuesData->GroupID) selected  @endif value="{{$FieldValuesData->GroupID}}">{{$FieldValuesData->GroupName}}</option>
        <?php 
					}
				} else  if($TicketfieldsData->FieldType == 'default_agent'){		
				$htmlagentID = 'Ticket'.$TicketfieldsData->FieldName;		
					?>
        <?php
					foreach($agentsAll as $FieldValuesData){
					?>
        <option @if($ticketSavedData[$TicketfieldsData->FieldType]==$FieldValuesData->UserID) selected  @endif value="{{$FieldValuesData->UserID}}">{{$FieldValuesData->FirstName}} {{$FieldValuesData->LastName}}</option>
        <?php 
					}
				}					
				else
				{
			 	 	$FieldValues = TicketfieldsValues::where(["FieldsID"=>$TicketfieldsData->TicketFieldsID])->orderBy('FieldOrder', 'asc')->get();
					
					foreach($FieldValues as $FieldValuesData){						
							if($TicketfieldsData->FieldType == 'default_status')
							{
								if($FieldValuesData->FieldValueAgent==TicketfieldsValues::$Status_UnResolved){	
									continue;
								}
							}
					?>
        <option @if($ticketSavedData[$TicketfieldsData->FieldType]==$FieldValuesData->ValuesID) selected  @endif  value="{{$FieldValuesData->ValuesID}}">{{$FieldValuesData->FieldValueAgent}}</option>
        <?php
					}
		}
			  	
				?>
      </select>
    </div>
  </div>
  <?php }
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DATE)
				 { 
				 	if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
				 ?>
  <div class="form-group">
    <label for="GroupName" class="col-md-4 control-label">{{$TicketfieldsData->AgentLabel}}</label>
    <div class="col-sm-8">
      <input {{$readonly}} type="text" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld datepicker" data-date-format="yyyy-mm-dd" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" value="{{$ticketSavedData[$TicketfieldsData->FieldType]}}">
    </div>
  </div>
  <?php }					 
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DECIMAL)
				 {
					  if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				    
				 ?>
  <div class="form-group">
    <label for="GroupName" class="col-md-4 control-label">{{$TicketfieldsData->AgentLabel}}</label>
    <div class="col-sm-8">
      <input {{$readonly}} type="text" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" value="{{$ticketSavedData[$TicketfieldsData->FieldType]}}">
    </div>
  </div>
  <?php				  }
				 }
		  }
	?>
     <?php if(User::checkCategoryPermission('Tickets','Edit')){ ?>
  <div class="form-group">
    <div class="col-md-5 pull-right">
      <button type="submit" class="btn save btn-primary btn-icon btn-sm icon-left" id="update_ticket" data-loading-text="Loading..."> Update <i class="entypo-mail"></i> </button>
    </div>
  </div>
  <?php } ?> 
  <input type="hidden" name="Page" value="DetailPage">
</form>
<script type="text/javascript">
var required_flds	  =          '{{json_encode($required)}}';



    jQuery(document).ready(function($) {
		
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
	  $('#form-tickets-edit').submit(function(e) {
		 e.preventDefault();
		 e.stopImmediatePropagation();	  
       
		if(validate_form())
		{  	
			$("#form-tickets-edit").find('#update_ticket').addClass('disabled');
			$("#form-tickets-edit").find('#update_ticket').button('loading');					
			var formData = new FormData($(this)[0]);
			var ajax_url = baseurl+'/tickets/{{$ticketdata->TicketID}}/updatedetailpage';
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
							location.reload();	
						}else{
							toastr.error(response.message, "Error", toastr_opts);
						} 
						$("#form-tickets-edit").find('.btn').button('reset');	
					}
					});	
		}
		return false;		
    });	
		
		
			$('.formfldcheckbox').change(function(e) {
               if ( $( this ).is( ":checked" ) ){
				  	$( this ).val(1);
				  }else{
				  	$( this ).val(0);
				  }
            });
			
		//$('#{{$htmlgroupID}}').trigger('change');
			setagentval();

    });
</script> 
<?php } ?>