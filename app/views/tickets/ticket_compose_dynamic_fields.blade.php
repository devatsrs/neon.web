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
  <td width="20%">
    <label for="GroupName" class="control-label">{{$TicketfieldsData->AgentLabel}}</label>
 <td>
      <input type="text"  name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" >
    </td>
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
  <td width="20%">
    <label for="GroupDescription" class="control-label">{{$TicketfieldsData->AgentLabel}}</label>
    </td>
    <td>
      <textarea   id='{{$id}}'  name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld" ></textarea>
    </td>
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
  <td width="20%">
    <label for="GroupDescription" class="control-label">{{$TicketfieldsData->AgentLabel}}</label>
    </td>
    <td>
      <input class="checkbox rowcheckbox formfldcheckbox" value="" name='Ticket[{{$TicketfieldsData->FieldType}}]'  id='{{$id}}' type="checkbox"> </td>
  </tr>
  <?php  }		  
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_TEXTNUMBER)
				 { 
				 if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
				 if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
			       ?>
  <tr>
  <td width="20%">
    <label for="GroupName" class=" control-label">{{$TicketfieldsData->AgentLabel}}</label>
    </td>
    <td>
      <input type="number" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" value="">
      </td>
  </tr>
  <?php
		 }
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DROPDOWN)
				 { 
				  if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					 if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
					 ?>
  <tr>
  	<td width="20%">
    <label for="GroupName" class="control-label">{{$TicketfieldsData->AgentLabel}}</label>
    </td>
    <td>
      <select name='Ticket[{{$TicketfieldsData->FieldType}}]' class="form-control formfld select2" id="{{$id}}" >
        <option value="0">Select</option>
        <?php
	          
			  if($TicketfieldsData->FieldType == 'default_priority'){
				$FieldValues = TicketPriority::orderBy('PriorityID', 'asc')->get(); 
					foreach($FieldValues as $key => $FieldValuesData){
					?>
        <option key="{{$key}}" @if($key==0) selected @endif   value="{{$FieldValuesData->PriorityID}}">{{$FieldValuesData->PriorityValue}}</option>
        <?php 
					}
				}else  if($TicketfieldsData->FieldType == 'default_group'){
				$htmlgroupID = 'Ticket'.$TicketfieldsData->FieldName;
				$FieldValues = TicketGroups::where(['CompanyID'=>$CompanyID])->orderBy('GroupID', 'asc')->get(); 
					?>
        <?php
					foreach($FieldValues as $FieldValuesData){
					?>
        <option  @if(count($FieldValues)==1) selected @endif value="{{$FieldValuesData->GroupID}}">{{$FieldValuesData->GroupName}}</option>
        <?php 
					}
				} else  if($TicketfieldsData->FieldType == 'default_agent'){		
				$htmlagentID = 'Ticket'.$TicketfieldsData->FieldName;		
					?>
        <?php
					foreach($agentsAll as $FieldValuesData){
					?>
        <option  @if(count($agentsAll)==1) selected @endif  value="{{$FieldValuesData->UserID}}">{{$FieldValuesData->FirstName}} {{$FieldValuesData->LastName}}</option>
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
    </td>
  </tr>
  <?php }
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DATE)
				 { 
				 	if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				 
				 ?>
  <tr>
  <td width="20%">
    <label for="GroupName" class="control-label">{{$TicketfieldsData->AgentLabel}}</label>
    </td>
    <td>
      <input type="text" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld datepicker" data-date-format="yyyy-mm-dd" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" >
    </td>
  </tr>
  <?php }					 
				 }
				 if($TicketfieldsData->FieldHtmlType == Ticketfields::FIELD_HTML_DECIMAL)
				 {
					  if($TicketfieldsData->AgentReqSubmit == '1'){$required[] = array("id"=>$id,"title"=>$TicketfieldsData->AgentLabel); }
					if(TicketsTable::checkTicketFieldPermission($TicketfieldsData)){				    
				 ?>
  <tr><td width="20%">
    <label for="GroupName" class="control-label">{{$TicketfieldsData->AgentLabel}}</label>
    </td>
    <td>
      <input type="text" name='Ticket[{{$TicketfieldsData->FieldType}}]'  class="form-control formfld" id="{{$id}}" placeholder="{{$TicketfieldsData->AgentLabel}}" >
    </td>
  </td>
  <?php				  }
				 }
		  }
	?>
  </table>
  <input type="hidden" name="Page" value="DetailPage">
<script type="text/javascript">

    jQuery(document).ready(function($) {
		var required_flds	   =    '{{json_encode($required)}}';
	 
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
		
		
			$('.formfldcheckbox').change(function(e) {
               if ( $( this ).is( ":checked" ) ){
				  	$( this ).val(1);
				  }else{
				  	$( this ).val(0);
				  }
            });
			
    });
</script> 
<?php } ?>