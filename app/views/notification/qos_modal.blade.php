<div class="modal fade" id="add-qos-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="billing-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Qos Alert</h4>
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
                                {{ Form::select('AlertType', $qos_alert_type, '', array("class"=>"select2")) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Low Value</label>
                                <input type="text" name="LowValue"  class="form-control"/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">High Value</label>
                                <input type="text" name="HighValue"  class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Gateway</label>
                                {{ Form::select('QosAlert[CompanyGatewayID]',$gateway,'', array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Country</label>
                                {{ Form::select('QosAlert[CountryID]',$Country,'', array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="clear"></div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Trunk</label>
                                {{ Form::select('QosAlert[TrunkID]',$trunks,'', array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Prefix</label>
                                <input type="text" name="QosAlert[Prefix]"  class="form-control"/>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Account</label>
                                {{ Form::select('QosAlert[AccountID]',$account,'', array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">Send Copy To</label>
                                <input type="text" name="QosAlert[ReminderEmail]" class="form-control" id="field-1" placeholder="" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Time</label>
                                {{Form::select('QosAlert[Time]',array(""=>"Select run time","MINUTE"=>"Minute","HOUR"=>"Hourly","DAILY"=>"Daily",'MONTHLY'=>'Monthly'),'',array( "class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Interval</label>
                                {{Form::select('QosAlert[Interval]',array(),'',array( "class"=>"select2 small"))}}
                            </div>
                        </div>

                        <div class="clear"></div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Day</label>
                                {{Form::select('QosAlert[Day][]',array("SUN"=>"Sunday","MON"=>"Monday","TUE"=>"Tuesday","WED"=>"Wednesday","THU"=>"Thursday","FRI"=>"Friday","SAT"=>"Saturday"),'',array( "class"=>"select2",'multiple',"data-placeholder"=>"Select day"))}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Start Time</label>
                                <!--<input type="text"  name="Setting[JoStratTime]" value="" class="form-control timepicker starttime2" data-minute-step="5" data-show-meridian="true"  data-default-time="12:00:00 AM" data-show-seconds="true" data-template="dropdown">-->
                                <input name="QosAlert[StartTime]" type="text" data-template="dropdown" data-show-seconds="true" data-default-time="12:00:00 AM" data-show-meridian="true" data-minute-step="5" class="form-control timepicker starttime2" value="12:00:00 AM" >
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 QosAlertDay">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Start Day</label>
                                {{Form::select('QosAlert[StartDay]',array(),'',array( "class"=>"select2 small"))}}
                            </div>
                        </div>
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
                        <input type="hidden" name="AlertGroup" value="{{Alert::GROUP_QOS}}">
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