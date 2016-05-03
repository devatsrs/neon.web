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
            'TaggedUser',
            'BoardID'
        ];
        var readonly = ['Company','Phone','Email','Title','FirstName','LastName'];
        var ajax_complete = false;
        var BoardID = '';
    
        var usetId = "{{User::get_userID()}}";
        /*$('#add-task-form [name="Rating"]').knob();*/
        //getOpportunities();
       

        $('#add-task-form').submit(function(e){
            e.preventDefault();     	      
			     
            var formData 		= 	new FormData($('#add-task-form')[0]);			
			var count 			= 	0;
			var getClass 		= 	$("#timeline-ul .count-li");
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
						 $("#task-add").button('reset');
					} else {
						per_scroll = count;
						ShowToastr("success","Task Successfully Created");                     
						$('#timeline-ul li:eq(0)').before(response);
						document.getElementById('save-task-form').reset();
						$("#task-add").button('reset');
						//$("#add-task-form .btn-danger").click();
						$('#add-modal-task').modal('hide');						
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
    });
</script>

@section('footer_ext')
    @parent
<div class="modal fade" id="add-modal-task">
  <div class="modal-dialog" style="width: 70%;">
    <div class="modal-content">
      <form id="add-task-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Add New task</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Task Status *</label>
                <div class="col-sm-8"> {{Form::select('TaskStatus',CRMBoardColumn::getTaskStatusList($BoardID),'',array("class"=>"select2"))}} </div>
              </div>
            </div>
            <div class="col-md-6 pull-right">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Assign To</label>
                <div class="col-sm-8"> {{Form::select('UsersIDs',$account_owners,User::get_userID(),array("class"=>"select2"))}} </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Task Subject *</label>
                <div class="col-sm-8">
                  <input type="text" name="Subject" class="form-control" id="field-5" placeholder="">
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-right">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Due Date</label>
                <div class="col-sm-5">
                  <input autocomplete="off" type="text" name="DueDate" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="" />
                </div>
                        <div class="col-sm-3">
                                    <input type="text" name="StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00 AM" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
                                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top">
             <!-- <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Priority</label>
                <div class="col-sm-8"> {{Form::select('Priority',$priority,'',array("class"=>"select2"))}} </div>
              </div>-->
               <div class="form-group">
                                <label class="col-sm-4 control-label">Priority</label>
                                <div class="col-sm-4">
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
                  <textarea name="Description" class="form-control description autogrow resizevertical"> </textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" id="Task_type" name="Task_type">
          <input type="hidden" id="Task_ParentID" name="ParentID">
          <input type="hidden" id="BoardID" name="BoardID" value="13">
          <input type="hidden" id="AccountIDs" name="AccountIDs" value="{{$account->AccountID}}">
          <button type="submit" id="task-add"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop