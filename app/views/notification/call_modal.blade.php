<div class="modal fade" id="add-call-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="call-billing-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Call Monitor Alert</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Name</label>
                                <input type="text" name="Name" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Alert Type</label>
                                {{ Form::select('AlertType', $call_monitor_alert_type, '', array("class"=>"select2")) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Blacklist Destination</label>
                                <input type="text" name="MaxDuration"  class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Active</label>
                                <div class="clear">
                                    <p class="make-switch switch-small">
                                        <input type="checkbox" checked=""  name="Status" value="0">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="AlertID" value="">
                        <input type="hidden" name="AlertGroup" value="call">
                        <button type="submit" id="qos-update"  class="save btn btn-success btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save

                        </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="{{ URL::asset('assets/js/billing_class.js') }}"></script>