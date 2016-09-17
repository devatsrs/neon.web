<div class="row {{$tabs}}" id="tab_{{Notification::PaymentReminder}}">
    <div class="col-md-12">
        <div data-collapsed="0" class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">
                    {{Notification::$type[Notification::PaymentReminder]}}
                </div>
                <div class="panel-options">
                    <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <form id="notification_setting_{{Notification::PaymentReminder}}" class="form-horizontal form-groups-bordered" role="form">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="field-1">Based On</label>
                        <div class="col-sm-4">
                            {{Form::select('Settings[InvoiceBase]', Notification::$paymentreminder_type, (isset($Settings->InvoiceBase) ? $Settings->InvoiceBase:1)  ,array("class"=>"selectboxit form-control"))}}
                        </div>
                        <label class="col-sm-2 control-label" for="field-2">Send Email to Account Manager </label>
                        <div class="col-sm-4">
                            <div class="make-switch switch-small">
                                <input type="checkbox" @if(isset($Settings->SendToAccountManager)) checked="" @endif name="Settings[SendToAccountManager]" value="1">
                            </div>
                        </div>
                    </div>
                    <div class="form-group invoice_base">
                        <label class="col-sm-2 control-label" for="field-2">Notify customer about upcoming due <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use comma to separate values Example “14, 7, 3”.Leave this field empty to disable notifications completely." data-original-title="Notification">?</span></label>
                        <div class="col-sm-4">
                            <input name="Settings[NotifyDueInvoice]" type="text" id="field-2" class="form-control" value="{{$Settings->NotifyDueInvoice or ''}}" >
                        </div>
                        <label class="col-sm-2 control-label" for="field-2">Email Template</label>
                        <div class="col-sm-4">
                            {{Form::select('Settings[DueInvoice]', $emailTemplates, (isset($Settings->InvoiceDue) ? $Settings->InvoiceDue:EmailTemplate::getDefaultSystemTemplate('DueInvoice')) ,array("class"=>"selectboxit form-control"))}}
                        </div>
                    </div>
                    <div class="form-group invoice_base">
                        <label class="col-sm-2 control-label" for="field-1">Notify customer about if unpaid invoice <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use comma to separate values Example “14, 7, 3”.Leave this field empty to disable notifications completely." data-original-title="Notification">?</span></label>
                        <div class="col-sm-4">
                            <input name="Settings[NotifyUnpaidInvoice]" type="text" id="field-2" class="form-control" value="{{$Settings->NotifyUnpaidInvoice or ''}}" >
                        </div>
                        <label class="col-sm-2 control-label" for="field-2">Email Template </label>
                        <div class="col-sm-4">
                            {{Form::select('Settings[UnpaidInvoice]', $emailTemplates, (isset($Settings->UnpaidInvoice) ? $Settings->UnpaidInvoice: EmailTemplate::getDefaultSystemTemplate('UnpaidInvoice')) ,array("class"=>"selectboxit form-control"))}}
                        </div>
                    </div>
                    <div class="form-group invoice_base">
                        <label class="col-sm-2 control-label " for="field-2">Send suspension warning if invoice unpaid <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use comma to separate values Example “14, 7, 3”.Leave this field empty to disable notifications completely." data-original-title="Notification">?</span></label>
                        <div class="col-sm-4">
                            <input name="Settings[SuspendWarning]" type="text" id="field-2" class="form-control"  value="{{$Settings->SuspendWarning or ''}}">
                        </div>
                        <label class="col-sm-2 control-label" for="field-2">Email Template </label>
                        <div class="col-sm-4">
                            {{Form::select('Settings[AccountCloseWarning]', $emailTemplates, (isset($Settings->AccountCloseWarning) ? $Settings->AccountCloseWarning: EmailTemplate::getDefaultSystemTemplate('AccountCloseWarning')) ,array("class"=>"selectboxit form-control"))}}
                        </div>
                    </div>
                    <div class="form-group invoice_base">
                        <label class="col-sm-2 control-label" for="field-1">Send close account soon <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use comma to separate values Example “14, 7, 3”.Leave this field empty to disable notifications completely." data-original-title="Notification">?</span></label>
                        <div class="col-sm-4">
                            <input name="Settings[NotifyAccountClose]" type="text" id="field-2" class="form-control"  value="{{$Settings->NotifyAccountClose or ''}}" >
                        </div>
                        <label class="col-sm-2 control-label" for="field-2">Email Template </label>
                        <div class="col-sm-4">
                            {{Form::select('Settings[AccountCloseSoon]', $emailTemplates, (isset($Settings->AccountCloseSoon) ? $Settings->AccountCloseSoon: EmailTemplate::getDefaultSystemTemplate('AccountCloseSoon')) ,array("class"=>"selectboxit form-control"))}}
                        </div>
                    </div>
                    <div class="form-group invoice_base">
                        <label class="col-sm-2 control-label" for="field-1">Select accounts to notify</label>
                        <div class="col-sm-4">
                            <a href='#' id='select-all' class="btn btn-success btn-sm btn-icon icon-left"><i class="fa fa-plus"></i>select all</a>
                            <a href='#' id='deselect-all' class="btn btn-success btn-sm btn-icon icon-left"><i class="fa fa-minus"></i>deselect all</a>
                            <br>
                            <br>
                            {{Form::select('Settings[AccountID][]', $accoutns, (isset($Settings->AccountID) ? $Settings->AccountID: array()) ,array("class"=>"form-control multi-select searchable","multiple"=>"multiple"))}}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
