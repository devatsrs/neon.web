<div class="row">
<form role="form" id="billing-form" method="post" class="form-horizontal form-groups-bordered">
    <div class="col-sm-12">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab1" data-toggle="tab">Basic Info</a></li>
            <li ><a href="#tab2" data-toggle="tab">Payment Reminder</a></li>
            <li ><a href="#tab3" data-toggle="tab">Low Balance Reminder</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab1" >
                <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel body -->
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Class Name</label>
                                <div class="col-sm-4">
                                    <input type="text" name="Name" class="form-control" id="field-1" placeholder="" value="{{$BillingClass->Name or ''}}" />
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Description</label>
                                <div class="col-sm-4">
                                    <input type="text" name="Description" class="form-control" id="field-1" placeholder="" value="{{$BillingClass->Description or ''}}" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Tax Rate</label>
                                <div class="col-sm-4">
                                    {{Form::select('TaxRateID[]', $taxrates, (isset($BillingClass->TaxRateId)? explode(',',$BillingClass->TaxRateId) : array() ) ,array("class"=>"form-control select2",'multiple'))}}
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Payment is expected within (Days)*</label>
                                <div class="col-sm-4">
                                    <div class="input-spinner">
                                        <button type="button" class="btn btn-default">-</button>
                                        {{Form::text('PaymentDueInDays',( isset($BillingClass->PaymentDueInDays)?$BillingClass->PaymentDueInDays:'1' ),array("class"=>"form-control","data-min"=>0, "maxlength"=>"2", "data-max"=>30,"Placeholder"=>"Add Numeric value", "data-mask"=>"decimal"))}}
                                        <button type="button" class="btn btn-default">+</button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Round Charged Amount (123.45)*</label>
                                <div class="col-sm-4">
                                    <div class="input-spinner">
                                        <button type="button" class="btn btn-default">-</button>
                                        {{Form::text('RoundChargesAmount', ( isset($BillingClass->RoundChargesAmount)?$BillingClass->RoundChargesAmount:'2' ),array("class"=>"form-control", "maxlength"=>"1", "data-min"=>0,"data-max"=>4,"Placeholder"=>"Add Numeric value" , "data-mask"=>"decimal"))}}
                                        <button type="button" class="btn btn-default">+</button>
                                    </div>
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Billing Timezone*</label>
                                <div class="col-sm-4">
                                    {{Form::select('BillingTimezone', $timezones, ( isset($BillingClass->BillingTimezone)?$BillingClass->BillingTimezone:'' ),array("class"=>"form-control select2"))}}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Invoice Template*</label>
                                <div class="col-sm-4">
                                    {{Form::select('InvoiceTemplateID', $InvoiceTemplates, ( isset($BillingClass->InvoiceTemplateID)?$BillingClass->InvoiceTemplateID:'' ),array('id'=>'billing_type',"class"=>"selectboxit"))}}
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Send Invoice via Email*</label>
                                <div class="col-sm-4">
                                    {{Form::select('SendInvoiceSetting', $SendInvoiceSetting, ( isset($BillingClass->SendInvoiceSetting)?$BillingClass->SendInvoiceSetting:'' ),array("class"=>"form-control select2"))}}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Invoice Format*</label>
                                <div class="col-sm-4">
                                    {{Form::select('CDRType', Account::$cdr_type, ( isset($BillingClass->CDRType)?$BillingClass->CDRType:'' ),array('id'=>'billing_type',"class"=>"selectboxit"))}}
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="tab-pane" id="tab2" >
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#invoice_tab" data-toggle="tab">Invoice</a></li>
                    <li ><a href="#account_tab" data-toggle="tab">Account</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="invoice_tab" >
                        <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                                <!-- panel body -->
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Active</label>
                                        <div class="col-sm-4">
                                            <div class="make-switch switch-small">
                                                <input type="checkbox" @if( isset($BillingClass->InvoiceReminderStatus) && $BillingClass->InvoiceReminderStatus == 1 )checked="" @endif name="InvoiceReminderStatus" value="1">
                                            </div>
                                        </div>
                                        <label class="col-sm-2 control-label">Send To Account Manager</label>
                                        <div class="col-sm-4">
                                            <div class="make-switch switch-small">
                                                <input type="checkbox" @if( isset($InvoiceReminders->AccountManager) && $InvoiceReminders->AccountManager == 1 )checked="" @endif name="InvoiceReminder[AccountManager]" value="1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Send Copy To</label>
                                        <div class="col-sm-4">
                                            <input type="text" name="InvoiceReminder[ReminderEmail]" class="form-control" id="field-1" placeholder="" value="{{$InvoiceReminders->ReminderEmail or ''}}" />
                                        </div>

                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <table id="PaymentReminderTable" class="table table-bordered" style="margin-bottom: 0">
                                                <thead>
                                                <tr>
                                                    <th width="5%" ><button type="button" id="payment-add-row" class="btn btn-primary btn-xs ">+</button></th>
                                                    <th width="30%" >Days<span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Send reminder based on due dates. ex. send reminder before one day of due date(-1),send reminder after two day of due date(2)" data-original-title="Due Days">?</span></th>
                                                    <th width="30%" >Account Age<span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Reminder will not send if Account Age not less than this" data-original-title="Account Age">?</span></th>
                                                    <th width="30%" >Template </th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(!empty($InvoiceReminders->Day) && count($InvoiceReminders->Day)>0)
                                                    @foreach($InvoiceReminders->Day as $InvoiceReminder => $Day)
                                                        <tr>
                                                            <td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td>
                                                            <td>
                                                                <div class="input-spinner">
                                                                    <button type="button" class="btn btn-default">-</button>
                                                                    <input type="text" name="InvoiceReminder[Day][]" class="form-control" id="field-1" placeholder="" value="{{$Day}}" Placeholder="Add Numeric value" data-mask="decimal"/>
                                                                    <button type="button" class="btn btn-default">+</button>
                                                                </div>

                                                            </td>
                                                            <td>
                                                                <div class="input-spinner">
                                                                    <button type="button" class="btn btn-default">-</button>
                                                                    <input type="text" name="InvoiceReminder[Age][]" class="form-control" id="field-1" placeholder="" value="{{$InvoiceReminders->Age[$InvoiceReminder]}}" Placeholder="Add Numeric value" data-mask="decimal"/>
                                                                    <button type="button" class="btn btn-default">+</button>
                                                                </div>

                                                            </td>
                                                            <td>
                                                                {{Form::select('InvoiceReminder[TemplateID][]', $emailTemplates, $InvoiceReminders->TemplateID[$InvoiceReminder] ,array("class"=>"selectboxit form-control"))}}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="tab-pane " id="account_tab" >
                        <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                                <!-- panel body -->
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Active</label>
                                        <div class="col-sm-4">
                                            <div class="make-switch switch-small">
                                                <input type="checkbox" @if( isset($BillingClass->PaymentReminderStatus) && $BillingClass->PaymentReminderStatus == 1 )checked="" @endif name="PaymentReminderStatus" value="1">
                                            </div>
                                        </div>
                                        <label class="col-sm-2 control-label">Send To Account Manager</label>
                                        <div class="col-sm-4">
                                            <div class="make-switch switch-small">
                                                <input type="checkbox" @if( isset($PaymentReminders->AccountManager) && $PaymentReminders->AccountManager == 1 )checked="" @endif name="PaymentReminder[AccountManager]" value="1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Send Copy To</label>
                                        <div class="col-sm-4">
                                            <input type="text" name="PaymentReminder[ReminderEmail]" class="form-control" id="field-1" placeholder="" value="{{$PaymentReminders->ReminderEmail or ''}}" />
                                        </div>
                                        <label class="col-sm-2 control-label">Template</label>
                                        <div class="col-sm-4">
                                            {{Form::select('PaymentReminder[TemplateID]', $emailTemplates, (isset($PaymentReminders->TemplateID)?$PaymentReminders->TemplateID:'') ,array("class"=>"selectboxit form-control"))}}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="field-5" class="col-sm-2 control-label">Time</label>
                                        <div class="col-sm-4">
                                            {{Form::select('PaymentReminder[Time]',array(""=>"Select run time","MINUTE"=>"Minute","HOUR"=>"Hourly","DAILY"=>"Daily",'MONTHLY'=>'Monthly'),(isset($PaymentReminders->Time)?$PaymentReminders->Time:''),array( "class"=>"selectboxit"))}}
                                        </div>

                                        <label for="field-5" class="col-sm-2 control-label">Interval</label>
                                        <div class="col-sm-4">
                                            {{Form::select('PaymentReminder[Interval]',array(),'',array( "class"=>"selectboxit"))}}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="field-5" class="col-sm-2 control-label">Day</label>
                                        <div class="col-sm-4">
                                            {{Form::select('PaymentReminder[Day][]',array("SUN"=>"Sunday","MON"=>"Monday","TUE"=>"Tuesday","WED"=>"Wednesday","THU"=>"Thursday","FRI"=>"Friday","SAT"=>"Saturday"),(isset($PaymentReminders->Day)?$PaymentReminders->Day:''),array( "class"=>"select2",'multiple',"data-placeholder"=>"Select day"))}}
                                        </div>

                                        <label for="field-5" class="col-sm-2 control-label">Start Time</label>
                                        <div class="col-sm-4">
                                            <input name="PaymentReminder[StartTime]" type="text" data-template="dropdown" data-show-seconds="true" data-default-time="12:00:00 AM" data-show-meridian="true" data-minute-step="5" class="form-control timepicker starttime2" value="{{(isset($PaymentReminders->StartTime)?$PaymentReminders->StartTime:'')}}" >
                                        </div>
                                    </div>
                                    <div class="form-group PaymentReminderDay">
                                        <label for="field-5" class="col-sm-2 control-label">Start Day</label>
                                        <div class="col-sm-4">
                                            {{Form::select('PaymentReminder[StartDay]',array(),'',array( "class"=>"selectboxit"))}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab3" >
                <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel body -->
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Active</label>
                                <div class="col-sm-4">
                                    <div class="make-switch switch-small">
                                        <input type="checkbox" @if( isset($BillingClass->LowBalanceReminderStatus) && $BillingClass->LowBalanceReminderStatus == 1 )checked="" @endif name="LowBalanceReminderStatus" value="1">
                                    </div>
                                </div>
                                <label class="col-sm-2 control-label">Send To Account Manager</label>
                                <div class="col-sm-4">
                                    <div class="make-switch switch-small">
                                        <input type="checkbox" @if( isset($LowBalanceReminder->AccountManager) && $LowBalanceReminder->AccountManager == 1 )checked="" @endif name="LowBalanceReminder[AccountManager]" value="1">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Send Copy To</label>
                                <div class="col-sm-4">
                                    <input type="text" name="LowBalanceReminder[ReminderEmail]" class="form-control" id="field-1" placeholder="" value="{{$LowBalanceReminder->ReminderEmail or ''}}" />
                                </div>
                                <label class="col-sm-2 control-label">Email Template</label>
                                <div class="col-sm-4">
                                    {{Form::select('LowBalanceReminder[TemplateID]', $emailTemplates, (isset($LowBalanceReminder->TemplateID)?$LowBalanceReminder->TemplateID:'') ,array("class"=>"selectboxit form-control"))}}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-5" class="col-sm-2 control-label">Time</label>
                                <div class="col-sm-4">
                                    {{Form::select('LowBalanceReminder[Time]',array(""=>"Select run time","MINUTE"=>"Minute","HOUR"=>"Hourly","DAILY"=>"Daily",'MONTHLY'=>'Monthly'),(isset($LowBalanceReminder->Time)?$LowBalanceReminder->Time:''),array( "class"=>"selectboxit"))}}
                                </div>

                                <label for="field-5" class="col-sm-2 control-label">Interval</label>
                                <div class="col-sm-4">
                                    {{Form::select('LowBalanceReminder[Interval]',array(),'',array( "class"=>"selectboxit"))}}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-5" class="col-sm-2 control-label">Day</label>
                                <div class="col-sm-4">
                                    {{Form::select('LowBalanceReminder[Day][]',array("SUN"=>"Sunday","MON"=>"Monday","TUE"=>"Tuesday","WED"=>"Wednesday","THU"=>"Thursday","FRI"=>"Friday","SAT"=>"Saturday"),(isset($LowBalanceReminder->Day)?$LowBalanceReminder->Day:''),array( "class"=>"select2",'multiple',"data-placeholder"=>"Select day"))}}
                                </div>

                                <label for="field-5" class="col-sm-2 control-label">Start Time</label>
                                <div class="col-sm-4">
                                    <input name="LowBalanceReminder[StartTime]" type="text" data-template="dropdown" data-show-seconds="true" data-default-time="12:00:00 AM" data-show-meridian="true" data-minute-step="5" class="form-control timepicker starttime2" value="{{(isset($LowBalanceReminder->StartTime)?$LowBalanceReminder->StartTime:'')}}" >
                                </div>
                            </div>
                            <div class="form-group LowBalanceReminderDay">
                                <label for="field-5" class="col-sm-2 control-label">Start Day</label>
                                <div class="col-sm-4">
                                    {{Form::select('LowBalanceReminder[StartDay]',array(),'',array( "class"=>"selectboxit"))}}
                                </div>
                            </div>

                        </div>
                    </div>
            </div>
        </div>
    </div>
    @if(isset($accounts) && count($accounts))
    <div class="col-sm-12">
        <div data-collapsed="0" class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">
                    Accounts
                </div>
                <div class="panel-options"> <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> </div>
            </div>
            <div class="panel-body">
                <div class="bootstrap-tagsinput">
                    @foreach($accounts as $account)
                        <span class="tag label label-info">{{$account->AccountName}}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif


</form>
</div>
<script>
    var add_row_html_payment = '<tr><td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td><td><div class="input-spinner"><button type="button" class="btn btn-default">-</button><input type="text" name="InvoiceReminder[Day][]" class="form-control" id="field-1" placeholder="" value="" Placeholder="Add Numeric value" data-mask="decimal"/><button type="button" class="btn btn-default">+</button></div></td>';
    add_row_html_payment += '<td><div class="input-spinner"><button type="button" class="btn btn-default">-</button><input type="text" name="InvoiceReminder[Age][]" class="form-control" id="field-1" placeholder="" value="" Placeholder="Add Numeric value" data-mask="decimal"/><button type="button" class="btn btn-default">+</button></div></td>';
    add_row_html_payment += '<td>{{Form::select('InvoiceReminder[TemplateID][]', $emailTemplates, '' ,array("class"=>"selectboxit form-control"))}}</td><tr>';
    jQuery(document).ready(function ($) {
        $("#billing-form [name='PaymentReminder[Time]']").trigger('change');
        $("#billing-form [name='LowBalanceReminder[Time]']").trigger('change');
        @if(isset($PaymentReminders->Interval))
            setTimeout(function(){
                $("#billing-form [name='PaymentReminder[Interval]']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('{{$PaymentReminders->Interval}}');
                $("#billing-form [name='LowBalanceReminder[Interval]']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('{{$LowBalanceReminder->Interval}}');
            },5);
        @endif
    });
</script>
<script src="{{ URL::asset('assets/js/billing_class.js') }}"></script>