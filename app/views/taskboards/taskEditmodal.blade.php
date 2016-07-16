<div class="modal fade" id="edit-modal-task">
        <div class="modal-dialog" style="width: 70%;">
            <div class="modal-content">
                <form id="edit-task-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Task</h4>
                    </div>
                    <div class="modal-body">

                        <div class="row">

                            <div class="col-md-12 text-left">
                                <label for="field-5" class="control-label col-sm-2">Tag User</label>
                                <div class="col-sm-10" style="padding: 0px 10px;">
                                    <?php unset($account_owners['']); ?>
                                    {{Form::select('TaggedUsers[]',$account_owners,[],array("class"=>"select2","multiple"=>"multiple"))}}
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Task Status *</label>
                                    <div class="col-sm-8">
                                        {{Form::select('TaskStatus',$taskStatus,'',array("class"=>"selectboxit"))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Assign To*</label>
                                    <div class="col-sm-8">
                                        {{Form::select('UsersIDs',$account_owners,'',array("class"=>"select2"))}}
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
                                        {{Form::select('AccountIDs',$leadOrAccount,'',array("class"=>"select2"))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Priority</label>
                                    <div class="col-sm-3 make">
                                        <span class="make-switch switch-small">
                                            <input name="Priority" value="1" type="checkbox">
                                        </span>
                                    </div>
                                    <label class="col-sm-2 control-label">Close</label>
                                    <div class="col-sm-3 taskClosed">
                                        <p class="make-switch switch-small">
                                            <input name="taskClosed" type="checkbox" value="{{Task::Close}}">
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
                        <button type="submit" id="task-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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