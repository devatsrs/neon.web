<div class="modal fade" id="add-call-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="call-billing-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MODAL_ADD_MONITORING_TITLE')</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MODAL_ADD_MONITORING_FIELD_NAME')</label>
                                <input type="text" name="Name" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MODAL_ADD_MONITORING_FIELD_TYPE')</label>
                                {{ Form::select('AlertType', $call_monitor_alert_type, '', array("class"=>"select2")) }}
                            </div>
                        </div>
                    </div>
                    <div class="row custom_field">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MODAL_ADD_MONITORING_FIELD_MAX_DURATION_SEC')</label>
                                <input name="CallAlert[Duration]"  type="text" class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="row custom_field">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MODAL_ADD_MONITORING_FIELD_MAX_COST')</label>
                                <input name="CallAlert[Cost]"  type="text" class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="row custom_field">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MODAL_ADD_MONITORING_FIELD_BUSINESS_HOUR_FROM')</label>
                                <input name="CallAlert[OpenTime]"  type="text" class="form-control timepicker starttime2"  data-minute-step="5" data-show-meridian="false" data-default-time="09:00:00" data-show-seconds="true" data-template="dropdown"/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MODAL_ADD_MONITORING_FIELD_BUSINESS_HOUR_TO')</label>
                                <input name="CallAlert[CloseTime]"  type="text" class="form-control timepicker starttime2"  data-minute-step="5" data-show-meridian="false" data-default-time="17:00:00" data-show-seconds="true" data-template="dropdown"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label ReminderEmail">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MODAL_ADD_MONITORING_FIELD_SEND_COPY_TO')</label>
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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MODAL_ADD_MONITORING_FIELD_ACTIVE')</label>
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
                        <input type="hidden" name="CallAlert[AccountIDs]" value="{{Customer::get_accountID()}}">
                        <input type="hidden" name="CallAlert[AccountID]" value="{{Customer::get_accountID()}}">
                        <input type="hidden" name="CallAlert[EmailToAccount]" value="1">
                        <input type="hidden" name="AlertGroup" value="call">
                        <button type="submit" id="qos-update"  class="save btn btn-success btn-sm btn-icon icon-left" data-loading-text="@lang('routes.BUTTON_LOADING_CAPTION')">
                            <i class="entypo-floppy"></i>
                            @lang('routes.BUTTON_SAVE_CAPTION')

                        </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            @lang('routes.BUTTON_CLOSE_CAPTION')
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="{{ URL::asset('assets/js/billing_class.js') }}"></script>