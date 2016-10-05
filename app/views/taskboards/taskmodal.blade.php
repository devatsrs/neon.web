<style>

        .margin-top{
            margin-top:10px;
        }
        .margin-top-group{
            margin-top:15px;
        }
        .paddingleft-0{
            padding-left: 3px;
        }
        .paddingright-0{
            padding-right: 0px;
        }
        #add-modal-task .btn-xs{
            padding:0px;
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
            'TaggedUsers',
            'BoardID'
        ];
        var readonly = ['Company','Phone','Email','Title','FirstName','LastName'];
        var ajax_complete = false;
        var BoardID = '';
        var leadOrAccountID = '';
        @if(isset($BoardID))
            BoardID = "{{$BoardID}}";
            <?php $disabled='';$leadOrAccountExist = 'No';$leadOrAccountID = '';$leadOrAccountCheck='' ?>
        @else
         <?php $leads = [];$disabled = 'disabled';$leadOrAccountExist = 'Yes'?>
        leadOrAccountID = '{{$leadOrAccountID}}';
        @endif
        var userId = "{{User::get_userID()}}";
        $(document).on('click','.task',function(){
            $('#add-task-form').trigger("reset");
            $('#add-task-form [name="UsersIDs"]').val('').trigger("change");
            $('#add-task-form [name="AccountIDs"]').val('').trigger("change");
            $('#add-modal-task h4').text('Add Task');
            if(!BoardID){
                accountID =$(this).attr('data-id');
                $('#add-task-form [name="AccountID"]').val(accountID).trigger("change");
            }
            $('#add-task-form [name="BoardID"]').val(BoardID);
            $('#add-task-form [name="UsersIDs"]').val(userId).trigger("change");
            $('#add-modal-task').modal('show');
        });

        $('#add-task-form,#edit-task-form').submit(function(e){
            e.preventDefault();
            var update_new_url = '';
            var formid = $(this).attr('id');
            var taskID = $('#'+formid).find('[name="TaskID"]').val();
            if(taskID){
                update_new_url = baseurl + '/task/'+taskID+'/update';
            }else{
                update_new_url = baseurl + '/task/create';
            }
            var formData = new FormData($('#'+formid)[0]);
            $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);
                        $('#add-modal-task').modal('hide');
                        if(BoardID){
                            $('#search-task-filter').submit();
                        }
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    $("#task-add").button('reset');
                    $("#task-update").button('reset');
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        });

        $(".tasktags").select2({
            tags:{{$tasktags}}
        });
    });
</script>

@section('footer_ext')
    @parent
<div class="modal fade" id="add-modal-task">
    <div class="modal-dialog modal-lg">
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
                                <div class="col-sm-8">
                                    {{Form::select('TaskStatus',CRMBoardColumn::getTaskStatusList($BoardID),'',array("class"=>"select2 small",$disabled))}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 pull-right">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Assign To *</label>
                                <div class="col-sm-8">
                                    {{Form::select('UsersIDs',$account_owners,'',array("class"=>"select2",$disabled))}}
                                </div>
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
                                    <input type="text" name="StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="23:59:59" value="23:59:59" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 margin-top pull-left">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-4">Company</label>
                                <div class="col-sm-8">
                                    {{Form::select('AccountIDs',$leadOrAccount,'',array("class"=>"select2",$disabled))}}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 margin-top pull-right">
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
                                    <textarea name="Description" class="form-control textarea autogrow resizevertical"> </textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="TaskID">
                    <input type="hidden" name="BoardID">
                    <input type="hidden" value="1" name="Task_view">
                    <button type="submit" id="task-add"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop