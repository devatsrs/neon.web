<style>
.margin-top {
	margin-top: 10px;
}
.margin-top {
	margin-top: 10px;
}
.margin-top-group {
	margin-top: 15px;
}
.paddingleft-0 {
	padding-left: 3px;
}
.paddingright-0 {
	padding-right: 0px;
}
#add-modal-task .btn-xs {
	padding: 0px;
}
</style>
<script>
    $(document).ready(function ($) {
		
		
		
		
		
		        $('#add-modal-task').on('hidden.bs.modal', function(event){
       			var task_type_del 		=    $('#add-modal-task #Task_type').val();
				var del_parent_id   	=	 $('#add-modal-task #Task_ParentID').val();
				var url_delete_parent	=	 baseurl+"/accounts/delete_task_prent"
				
				$('body').removeClass('modal-open'); 
				
				if(task_type_del==0){
					return false;
				}
				
				/////////////
		
				 $.ajax({
					url: url_delete_parent,
					type: 'POST',
					dataType: 'json',
					async :false,
					data:{parent_type:task_type_del,parent_id:del_parent_id},
					success: function(response1) {
					
					},
				});	
			
		//////////////
				
        });

		
        var task = [
            'BoardColumnID',
            'BoardColumnName',
            'TaskID',
            'UsersIDs',
            'Users',
            'AccountIDs',
            'Subject',
            'Description',
            'DueDate',
            'TaskStatus',
            'Priority',
            'PriorityText',
            'TaggedUsers',
            'BoardID'
        ];
        var readonly = ['Company','Phone','Email','Title','FirstName','LastName'];
        var ajax_complete = false;
        var BoardID = '';
    
        var usetId = "{{User::get_userID()}}";
        /*$('#add-task-form [name="Rating"]').knob();*/
        //getOpportunities();
       

        $('#edit-note-form').submit(function(e){
            e.preventDefault();     	      
			     
            var formData 		= 	new FormData($('#edit-note-form')[0]);			
			var count 			= 	0;
			var getClass 		= 	$("#timeline-ul .count-li");
			var update_new_url 	= 	baseurl + '/account/note/update';
			var NoteID			=	$('#edit-note-form #NoteID').val();  
			var KeyID			=	$('#edit-note-form #KeyID').val();  
			     
			
            $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'html',
                success: function (response) {
                	if (isJson(response)) {
						var response_json  =  JSON.parse(response);
						 ShowToastr("error",response_json.message);
						 $("#note-edit").button('reset');
					} else {
						ShowToastr("success","Note Successfully Updated");              
						document.getElementById('edit-note-form').reset();
						$("#note-edit").button('reset');
						//$("#add-task-form .btn-danger").click();
						$('#edit-note-model').modal('hide');	
                        $('#edit-note-model').find('.editor-note').show();
					}                    
                  // $('#edit-note-form #Description_edit_note').css("height","48px"); 
				  $('#hidden-timeline-'+KeyID).html(response);
				   change_click_filter();
				   
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        }); 
		
		
        $('#edit-task-form').submit(function(e){
            e.preventDefault();     	      
			     
            var formData 		= 	new FormData($('#edit-task-form')[0]);			
			var TaskID			=	$('#edit-task-form #TaskID').val();  
			var KeyID			=	$('#edit-task-form #KeyID').val();  
			var Priority		=	$('#edit-task-form  [name="Priority"]').val();
			var update_new_url 	= 	baseurl +'/task/'+TaskID+'/update';     
			
            $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'html',
                success: function (response) {
                	if (isJson(response)) {
						var response_json  =  JSON.parse(response);
						 ShowToastr("error",response_json.message);
						 $("#note-edit").button('reset');
					} else {
						ShowToastr("success","Task Successfully Updated");              
						document.getElementById('edit-task-form').reset();
						$("#task-edit").button('reset');
						//$("#add-task-form .btn-danger").click();
						$('#edit-modal-task').modal('hide');	
						 change_click_filter();
					   $('#timeline-'+KeyID).html(response);					
					}                    
                  // $('#edit-note-form #Description_edit_note').css("height","48px"); 
				  
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        });       
		
		   
    });
</script>
@section('footer_ext')
    @parent
<div class="modal fade" id="edit-note-model">
  <div class="modal-dialog" style="width: 70%;">
    <div class="modal-content">
      <form id="edit-note-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Update Note</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 margin-top pull-left">
              <div class="form-group">
                <textarea name="Note" id="Description_edit_note" class="form-control autogrow editor-note desciriptions " style="height: 175px; overflow: hidden; word-wrap: break-word; resize: none;"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" id="NoteID" name="NoteID" value="">
          <input type="hidden" id="NoteType" name="NoteType" value="">
          <input type="hidden" id="KeyID" name="KeyID" value="">
          <button type="submit" id="note-edit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="edit-modal-task">
  <div class="modal-dialog" style="width: 70%;">
    <div class="modal-content">
      <form id="edit-task-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Edit Task</h4>
        </div>
        <div class="modal-body">
          <div class="row"> @if(count($boards)>0)
            <div class="col-md-6 pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Task Status *</label>
                <div class="col-sm-8"> {{Form::select('TaskStatus',CRMBoardColumn::getTaskStatusList($boards->BoardID),'',array("class"=>"select2 small"))}} </div>
              </div>
            </div>
            @endif
            <div class="col-md-6 pull-right">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Assign To *</label>
                <div class="col-sm-8"> {{Form::select('UsersIDs',$account_owners,User::get_userID(),array("class"=>"select2"))}} </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Task Subject *</label>
                <div class="col-sm-8">
                  <input type="text" name="Subject" id="Subject" class="form-control" id="field-5" placeholder="">
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-right">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Due Date</label>
                <div class="col-sm-5">
                  <input autocomplete="off" type="text" name="DueDate" id="DueDate_date" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="" />
                </div>
                <div class="col-sm-3">
                  <input type="text"  id="DueDate_time" name="StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00 AM" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top">
              <div class="form-group">
                <label class="col-sm-4 control-label">Priority</label>
                <div class="col-sm-4 make">
                  <p class="make-switch switch-small">
                    <input name="Priority" type="checkbox" value="1" >
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-12 margin-top pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-2">Description</label>
                <div class="col-sm-10">
                  <textarea style="height:250px;" name="Description" id="Description_task" placeholder="I will grow as you type new lines." class="form-control descriptions autogrow resizevertical"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" value="" name="TaskID" id="TaskID" />
          <input type="hidden" id="KeyID" name="KeyID" value="">
          <input type="hidden" id="required_data" name="required_data" value="1">
          
          <button type="submit" id="task-edit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="ticket-conversation" data-backdrop="static">
        <div id="card-features-details" class="modal-dialog">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Conversations</h4>
                    </div>
                    <div class="modal-body left-padding">                      
                        <div id="allComments" class="form-group"></div>                      
                    </div>
                    <div class="modal-footer">
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
            </div>
        </div>
    </div>    
    <div class="modal fade " id="EmailAction-model">
     <form id="EmailActionform" method="post">     
  <div class="modal-dialog EmailAction_box"  style="width: 70%;">
    <div class="modal-content">     
    </div>
  </div>
   </form>
</div>
@stop