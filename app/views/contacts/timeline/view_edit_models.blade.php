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
</style>
<script>
    $(document).ready(function ($) {
		
        var readonly = ['Company','Phone','Email','Title','FirstName','LastName'];
        var ajax_complete = false;
    
        var usetId = "{{User::get_userID()}}";
       

        $('#edit-note-form').submit(function(e){
            e.preventDefault();     	      
			     
            var formData 		= 	new FormData($('#edit-note-form')[0]);			
			var count 			= 	0;
			var getClass 		= 	$("#timeline-ul .count-li");
			var update_new_url 	= 	baseurl + '/contacts/note/update';
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
          <input type="hidden" id="KeyID" name="KeyID" value="">
          <button type="submit" id="note-edit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
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
   <input name="usertype" value="{{Messages::UserTypeContact}}" type="hidden" />
   </form>
</div>
@stop