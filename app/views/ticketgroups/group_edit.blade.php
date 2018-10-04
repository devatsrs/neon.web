@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li> <a href="{{action('ticketgroups')}}">Tickets Groups</a> </li>
  <li>
        <a><span>{{ticketgroup_dropbox($ticketdata->GroupID)}}</span></a>
    </li>
  <li class="active"> <strong>Edit Group</strong> </li>
</ol>
<h3>Edit Group</h3>
<div class="panel-title"> @include('includes.errors')
  @include('includes.success') </div>
<p style="text-align: right;">
@if(User::checkCategoryPermission('TicketsGroups','Edit'))  <button type='button' class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>@endif
  <a href="{{action('ticketgroups')}}" class="btn btn-danger btn-sm btn-icon icon-left"> <i class="entypo-cancel"></i> Close </a> </p>
<br>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-primary" data-collapsed="0">
      <div class="panel-heading">
        <div class="panel-title"> Group Detail </div>
        <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
      </div>
      <div class="panel-body">
        <form role="form" id="form-ticketgroup-edit" method="post" action="{{URL::to('ticketgroups/'.$ticketdata->GroupID.'/update')}}"
                      class="form-horizontal form-groups-bordered">
            <div class="form-group">
                <label for="GroupName" class="col-sm-3 control-label">Language</label>
                <div class="col-sm-9">
                    {{ddl_language("", "groupLanguage", $ticketdata->LanguageID, "", "id")}}
                </div>
            </div>
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">Name</label>
            <div class="col-sm-9">
              <input type="text" name='GroupName' class="form-control" id="GroupName" placeholder="Group Name" value="{{$ticketdata->GroupName}}">
            </div>
          </div>
          <div class="form-group">
            <label for="GroupDescription" class="col-sm-3 control-label">Description</label>
            <div class="col-sm-9">
              <textarea id="GroupDescription" name="GroupDescription" class="form-control" >{{$ticketdata->GroupDescription}}</textarea>
            </div>
          </div>
          <div class="form-group">
            <label for="GroupDescription" class="col-sm-3 control-label">Business Hours</label>
            <div class="col-sm-9">
             {{Form::select('GroupBusinessHours', $businessHours,$ticketdata->GroupBusinessHours,array("class"=>"select2","id"=>"GroupBusinessHours"))}}  
            </div>
          </div>          
          <div class="form-group">
            <label for="GroupAgent" class="col-sm-3 control-label">Agents</label>
            <div class="col-sm-9"> {{Form::select('GroupAgent[]', $Agents, $Groupagents ,array("class"=>"select2","multiple"=>"multiple","id"=>"GroupAgent"))}} </div>
          </div>
          <div class="form-group">
            <label for="GroupEmailAddress" class="col-sm-3 control-label">Support Email <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Any email sent on these email addresses (comma separated) gets automatically converted into a ticket that you can get working on." data-original-title="Support Email" class="label label-info popover-primary">?</span></label>
            <div class="col-sm-9">
              <div class="input-group"> <span class="input-group-addon"><i class="entypo-mail"></i></span>
                <input name='GroupEmailAddress' id="GroupEmailAddress" type="email" class="form-control" placeholder="Email" value="{{$ticketdata->GroupEmailAddress}}">             </div>
                @if($ticketdata->GroupEmailStatus==0 && !empty($ticketdata->GroupEmailAddress))
                <br>                
                  <div class="email-activation"><span>{{$ticketdata->GroupEmailAddress}}</span>&nbsp;<span> Unverified  </span> - <button email_id="{{$ticketdata->GroupID}}" type="button" class="btn btn-default btn-xs Send_activation">Send Activation Email</button></div><br>
                  @endif
            </div>
          </div> 
           <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">IMAP Server</label>
            <div class="col-sm-9">
              <input type="text" name='GroupEmailServer' class="form-control" id="imapserver" placeholder="IMAP Server" value="{{$ticketdata->GroupEmailServer}}">
            </div>            
          </div>
            <div class="form-group">
                <label for="GroupName" class="col-sm-3 control-label">Prot</label>
                <div class="col-sm-9">
                    <input type="text" name='GroupEmailPort' class="form-control" id="imapport" placeholder="IMAP Server Port" value="{{$ticketdata->GroupEmailPort}}">
                </div>
            </div>
            <div class="form-group">
                <label for="GroupName" class="col-sm-3 control-label">Enable SSL</label>
                <div class="col-sm-9">
                    <p class="make-switch switch-small">
                        <input type="checkbox" {{isset($ticketdata->GroupEmailIsSSL) && $ticketdata->GroupEmailIsSSL==1?'checked':'';}}  name="GroupEmailIsSSL" >
                    </p>
                </div>
            </div>
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">Password</label>
            <div class="col-sm-6">
              <input type="password" name='GroupEmailPassword' class="form-control" id="Imappassword" placeholder="Password" value="{{$ticketdata->GroupEmailPassword}}">
            </div><div class="col-sm-3"><button data-loading-text="Loading..." title="Validate Mail Settings"  type="button" class="ValidateSmtp btn btn-primary">Test</button></div>
          </div>
          
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">Reply From Address</label>
            <div class="col-sm-9">            
            <div class="input-group"> <span class="input-group-addon"><i class="entypo-mail"></i></span>
                <input name='GroupReplyAddress' id="GroupReplyAddress" type="text" class="form-control" placeholder="Reply From Address" value="{{$ticketdata->GroupReplyAddress}}">
              </div>
            </div>
          </div>
          <div class="form-group">
          <label for="GroupAssignTime" class="col-sm-3 control-label">Escalation Rule</label>              
              <div class="col-sm-6">  {{Form::select('GroupAssignTime', TicketGroups::$EscalationTimes, $ticketdata->GroupAssignTime ,array("class"=>"select2","id"=>"GroupAssignTime"))}}   </div>
              <div class="col-sm-3"> <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="if a ticket remains un-assigned for more than" data-original-title="Escalation Rule" class="label label-info popover-primary">?</span> </div>
            
            <div class="clear-both"></div> <br>
               <label for="GroupAssignEmail" class="col-sm-3  control-label">&nbsp;</label>                 
            <div class="col-sm-6"> {{Form::select('GroupAssignEmail', $AllUsers, $ticketdata->GroupAssignEmail ,array("class"=>"select2","id"=>"GroupAssignEmail"))}}  </div>
            <div class="col-sm-3"><span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="then send escalation email to" data-original-title="Escalation Rule" class="label label-info popover-primary">?</span></div>
         
          </div>                
        </form>
      </div>
    </div>
  </div>
</div>
<style>
.email-activation span:first-child{text-decoration:line-through;}
.email-activation span:nth-child(2){color:red;}
</style>
<script type="text/javascript">
    var editor_options 	 	=  		{};
    jQuery(document).ready(function($) {
    // Replace Checboxes
        $(".save.btn").click(function(ev) {
            $('#form-ticketgroup-edit').submit();            
      });
	  $('.Send_activation').click(function(e) {
        var activation_id = $(this).attr("email_id");
		if(activation_id!='')
		{
			email_id = parseInt(activation_id);			
			var ajax_url = baseurl+'/ticketgroups/'+email_id+'/send_activation';
			 $.ajax({
				url: ajax_url,
				type: 'POST',
				dataType: 'json',
				async :false,
				cache: false,
                contentType: false,
                processData: false,
				data:{activation_id:activation_id},
				success: function(response) {
				   if(response.status =='success'){					   
						ShowToastr("success",response.message); 														
						//window.location.href= baseurl+'/ticketgroups';
					}else{
						toastr.error(response.message, "Error", toastr_opts);
					}		
				}
				});	
		}
		return false;
    });
	  
	  $(document).on('submit','#form-ticketgroup-edit',function(e){	
	 	 $('.btn').attr('disabled', 'disabled');	 
		 $('.btn').button('loading');
	
		e.stopImmediatePropagation();
		e.preventDefault();
		var formData = new FormData($(this)[0]);
		var ajax_url = baseurl+'/ticketgroups/{{$ticketdata->GroupID}}/update';
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
						//window.location.href= baseurl+'/ticketgroups';
						location.reload();
					}else{
						toastr.error(response.message, "Error", toastr_opts);
					}                   
					$('.btn').button('reset');
					$('.btn').removeClass('disabled');		
					//$('.btn').removeAttr('disabled');						
				}
				});	
		return false;		
    });
		show_summernote($('.wysihtml5box'),editor_options);
		$('.ValidateSmtp').click(function(e) {
			 console.log('form submitted');
			e.preventDefault();
			e.stopImmediatePropagation();
				var GroupEmailServer 		=  $("#form-ticketgroup-edit [name='GroupEmailServer']").val();				
				var GroupEmailPort 		    =  $("#form-ticketgroup-edit [name='GroupEmailPort']").val();
				var GroupEmailIsSSL 		=  $("#form-ticketgroup-edit [name='GroupEmailIsSSL']:checked").val();
				var GroupEmailPassword 		=  $("#form-ticketgroup-edit [name='GroupEmailPassword']").val();
				var GroupEmailAddress 		=  $("#form-ticketgroup-edit [name='GroupEmailAddress']").val();
				
				if(GroupEmailAddress==''){
					alert("Please add Support Email");
					return false;
				}
				if(GroupEmailServer==''){
					alert("Please add Imap Server");
					return false;
				}
                if(GroupEmailPort==''){
                    alert("Please add Imap Server Port");
                    return false;
                }
				if(GroupEmailPassword==''){
					alert("Please add Password");
					return false;
				}
				
				 $('.ValidateSmtp').attr('disabled', 'disabled');	 
				 $('.ValidateSmtp').button('loading');
			
				var ValidateUrl 			=  "<?php echo URL::to('/ticketgroups/validatesmtp'); ?>";
                var postData= {GroupEmailServer:GroupEmailServer,GroupEmailPort: GroupEmailPort, GroupEmailIsSSL: GroupEmailIsSSL,GroupEmailPassword:GroupEmailPassword,GroupEmailAddress:GroupEmailAddress};
				 $.ajax({
					url: ValidateUrl,
					type: 'POST',
					dataType: 'json',
					data:postData,
					success: function(Response) {
				    $('.ValidateSmtp').button('reset');
					$('.ValidateSmtp').removeAttr('disabled');
						 if (Response.status == 'failed') {
	                           toastr.error(Response.message, "Error", toastr_opts);
							   return false;
                          }else{
							ShowToastr("success",Response.message); 	
						  }
																	  
						}
				});	
        
            	
        });
				

    });
</script> 
@stop