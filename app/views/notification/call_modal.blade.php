<div class="modal fade" id="add-call-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="call-billing-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Monitoring</h4>
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
                                <label class="control-label">Type</label>
                                {{ Form::select('AlertType', $call_monitor_alert_type, '', array("class"=>"select2")) }}
                            </div>
                        </div>
                    </div>
                    <div class="row custom_field">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Blacklist Destination</label>
                                {{Form::select('CallAlert[BlacklistDestination][]',$MultiCountry,array(),array( "class"=>"select2",'multiple',"data-placeholder"=>"Select Destination"))}}
                            </div>
                        </div>
                    </div>

                    <div class="row custom_field">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Account</label>
                                {{ Form::select('CallAlert[AccountID]',$account,'', array("class"=>"select2")) }}
                            </div>
                        </div>
                    </div>
                    <div class="row custom_field">
                        <div class="col-md-12">
                            <div class="form-group">
                                <?php
                                $selectAll  = $account;
                                if(isset($selectAll[""])){unset($selectAll[""]);}
                                $selectAll = array(""=> "Select","-1"=>"All")+$selectAll
                                ?>
                                <label for="field-5" class="control-label">Account</label>
                                {{ Form::select('CallAlert[AccountIDs]',$selectAll,'', array("class"=>"select2")) }}
                            </div>
                        </div>
                    </div>
                    <div class="row custom_field">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Max. Duration(sec.)</label>
                                <input name="CallAlert[Duration]"  type="text" class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="row custom_field">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Max. Cost</label>
                                <input name="CallAlert[Cost]"  type="text" class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="row custom_field">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Business Hour From</label>
                                <input name="CallAlert[OpenTime]"  type="text" class="form-control timepicker starttime2"  data-minute-step="5" data-show-meridian="false" data-default-time="09:00:00" data-show-seconds="true" data-template="dropdown"/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Business Hour To</label>
                                <input name="CallAlert[CloseTime]"  type="text" class="form-control timepicker starttime2"  data-minute-step="5" data-show-meridian="false" data-default-time="17:00:00" data-show-seconds="true" data-template="dropdown"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label ReminderEmail">Send Email To</label>
                                <input name="CallAlert[ReminderEmail]"  type="text" class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="row custom_field">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label ReminderDays">Days Before Subscription Renewal
                                    <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="How many Days before renewal send reminders" data-original-title="Reminder Days" class="label label-info popover-primary">?</span>
                                </label>
                                <input name="CallAlert[ReminderDays]" type="number" class="form-control" min="0"/>
                            </div>
                        </div>
                    </div>
                    <div class="row custom_field">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Vendor</label>
                                {{ Form::select('CallAlert[VAccountID][]',$Multivendor,array(), array("class"=>"select2",'multiple',"data-placeholder"=>"Select Account")) }}
                            </div>
                        </div>
                    </div>
                    <div class="row custom_field">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Period</label>
                                {{Form::select('CallAlert[Time]',array(""=>"Select","MINUTE"=>"Minute","HOUR"=>"Hourly","DAILY"=>"Daily"),'',array( "class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Interval</label>
                                {{Form::select('CallAlert[Interval]',array(),'',array( "class"=>"select2 small"))}}
                            </div>
                        </div>
                    </div>
                    <div class="row custom_field">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Day</label>
                                {{Form::select('CallAlert[Day][]',array("SUN"=>"Sunday","MON"=>"Monday","TUE"=>"Tuesday","WED"=>"Wednesday","THU"=>"Thursday","FRI"=>"Friday","SAT"=>"Saturday"),array('SUN','MON','TUE','WED','THU','FRI','SAT'),array( "class"=>"select2",'multiple',"data-placeholder"=>"Select day"))}}
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
                        <div class="col-md-6 custom_field">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Email To Account</label>
                                <div class="clear">
                                    <p class="make-switch switch-small">
                                        <input type="checkbox" checked=""  name="CallAlert[EmailToAccount]" value="0">
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