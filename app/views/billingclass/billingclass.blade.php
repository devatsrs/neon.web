<?php $emailTemplates = EmailTemplate::getTemplateArray();
$CompanyID = User::get_companyID();
$taxrates = TaxRate::getTaxRateDropdownIDList($CompanyID);
if(isset($taxrates[""])){unset($taxrates[""]);}
$type = EmailTemplate::$Type;
$privacy = EmailTemplate::$privacy;
$CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('processcallcharges',$CompanyID);
$cronJobs_count = CronJob::where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$CronJobCommandID,'Status'=>1])->count();
$BlockCronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('pbxaccountblock',$CompanyID);
$pbxaccountblock_count = CronJob::where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$BlockCronJobCommandID,'Status'=>1])->count();
?>
<div class="row">
<form role="form" id="billing-form" method="post" class="form-horizontal form-groups-bordered">
    <div class="col-sm-12">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab1" data-toggle="tab">Basic Info</a></li>
            <li ><a href="#tab2" data-toggle="tab">Payment Reminder</a></li>
            <li ><a href="#tab3" data-toggle="tab">Low Balance Reminder</a></li>
            <li ><a href="#tab4" data-toggle="tab">Account Balance Warning</a></li>
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
                                    {{Form::select('TaxRateID[]', TaxRate::getTaxRateDropdownIDList($CompanyID), (isset($BillingClass->TaxRateID)? explode(',',$BillingClass->TaxRateID) : array() ) ,array("class"=>"form-control select2",'multiple'))}}
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Payment is expected within (Days)*</label>
                                <div class="col-sm-4">
                                    <div class="input-spinner">
                                        <button type="button" class="btn btn-default">-</button>
                                        {{Form::text('PaymentDueInDays',( isset($BillingClass->PaymentDueInDays)?$BillingClass->PaymentDueInDays:'1' ),array("class"=>"form-control","data-min"=>0, "maxlength"=>"3", "data-max"=>300,"Placeholder"=>"Add Numeric value", "data-mask"=>"decimal"))}}
                                        <button type="button" class="btn btn-default">+</button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Round Charged Amount (123.45)*</label>
                                <div class="col-sm-4">
                                    <div class="input-spinner">
                                        <button type="button" class="btn btn-default">-</button>
                                        {{Form::text('RoundChargesAmount', ( isset($BillingClass->RoundChargesAmount)?$BillingClass->RoundChargesAmount:'2' ),array("class"=>"form-control", "maxlength"=>"1", "data-min"=>0,"data-max"=>6,"Placeholder"=>"Add Numeric value" , "data-mask"=>"decimal"))}}
                                        <button type="button" class="btn btn-default">+</button>
                                    </div>
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Billing Timezone*</label>
                                <div class="col-sm-4">
                                    {{Form::select('BillingTimezone', TimeZone::getTimeZoneDropdownList(), ( isset($BillingClass->BillingTimezone)?$BillingClass->BillingTimezone:'' ),array("class"=>"form-control select2"))}}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Round Charged CDR (123.45)*</label>
                                <div class="col-sm-4">
                                    <div class="input-spinner">
                                        <button type="button" class="btn btn-default">-</button>
                                        {{Form::text('RoundChargesCDR', ( isset($BillingClass->RoundChargesCDR)?$BillingClass->RoundChargesCDR:'2' ),array("class"=>"form-control", "maxlength"=>"1", "data-min"=>0,"data-max"=>6,"Placeholder"=>"Add Numeric value" , "data-mask"=>"decimal"))}}
                                        <button type="button" class="btn btn-default">+</button>
                                    </div>
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Invoice Template*</label>
                                <div class="col-sm-4">
                                    {{Form::SelectControl('invoice_template',1,( isset($BillingClass->InvoiceTemplateID)?$BillingClass->InvoiceTemplateID:'' ))}}
                                            <!--{Form::select('InvoiceTemplateID', $InvoiceTemplates, ( isset($BillingClass->InvoiceTemplateID)?$BillingClass->InvoiceTemplateID:'' ),array('id'=>'billing_type',"class"=>"select2 select2Add small"))}}-->
                                </div>
                            </div>
                            <div class="form-group">
                                @if($pbxaccountblock_count>0)
                                <label class="col-sm-2 control-label">Block Account</label>
                                <div class="col-sm-4">
                                    <div class="make-switch switch-small">
                                        <input type="checkbox" @if( isset($BillingClass->SuspendAccount) && $BillingClass->SuspendAccount == 1 )checked="" @endif name="SuspendAccount" value="1">
                                    </div>
                                </div>
                                @else

                                @endif
                                @if($cronJobs_count>0)
                                <label class="col-sm-2 control-label">Deduct Call Charge In Advance</label>
                                <div class="col-sm-4">
                                    <div class="make-switch switch-small">
                                        <input type="checkbox" @if( isset($BillingClass->DeductCallChargeInAdvance) && $BillingClass->DeductCallChargeInAdvance == 1 )checked="" @endif name="DeductCallChargeInAdvance" value="1">
                                    </div>
                                </div>
                                @else

                                @endif
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">Auto Pay</label>
                                <div class="col-md-4">
                                    {{Form::select('AutoPaymentSetting', BillingClass::$AutoPaymentSetting, ( isset($BillingClass->AutoPaymentSetting)?$BillingClass->AutoPaymentSetting:'never' ) ,array("class"=>"form-control select2 small"))}}
                                </div>
                                <label class="col-md-2 control-label">Auto Pay Method</label>
                                <div class="col-md-4">
                                    {{Form::select('AutoPayMethod', BillingClass::$AutoPayMethod, ( isset($BillingClass->AutoPayMethod)?$BillingClass->AutoPayMethod:'0' ),array("class"=>"form-control select2 small"))}}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Send Invoice via Email*</label>
                                <div class="col-sm-4">
                                    {{Form::select('SendInvoiceSetting', BillingClass::$SendInvoiceSetting, ( isset($BillingClass->SendInvoiceSetting)?$BillingClass->SendInvoiceSetting:'' ),array("class"=>"form-control select2"))}}
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="tab-pane" id="tab2" >
                <br/>
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
                                        <label class="col-sm-2 control-label">Send To Account Owner</label>
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
                                                    <th width="30%" >Account Age<span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="System will not send reminder if account age is less then specified no of Days" data-original-title="Account Age">?</span></th>
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
                                                                {{Form::select('InvoiceReminder[TemplateID][]', $emailTemplates, $InvoiceReminders->TemplateID[$InvoiceReminder] ,array("class"=>"select2 select2add small form-control","data-type"=>'email_template','data-active'=>0,'data-modal'=>'add-new-modal-template'))}}
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
                                        <label class="col-sm-2 control-label">Send To Account Owner</label>
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
                                            {{Form::select('PaymentReminder[TemplateID]', $emailTemplates, (isset($PaymentReminders->TemplateID)?$PaymentReminders->TemplateID:'') ,array("class"=>"select2 select2add small form-control","data-type"=>'email_template','data-active'=>0,'data-modal'=>'add-new-modal-template'))}}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="field-5" class="col-sm-2 control-label">Period</label>
                                        <div class="col-sm-4">
                                            {{Form::select('PaymentReminder[Time]',array(""=>"Select","MINUTE"=>"Minute","HOUR"=>"Hourly","DAILY"=>"Daily",'MONTHLY'=>'Monthly'),(isset($PaymentReminders->Time)?$PaymentReminders->Time:''),array( "class"=>"select2 small"))}}
                                        </div>

                                        <label for="field-5" class="col-sm-2 control-label">Interval</label>
                                        <div class="col-sm-4">
                                            {{Form::select('PaymentReminder[Interval]',array(),'',array( "class"=>"select2 small"))}}
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
                                            {{Form::select('PaymentReminder[StartDay]',array(),'',array( "class"=>"select2 small"))}}
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
                                <label class="col-sm-2 control-label">Send To Account Owner</label>
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
                                    {{Form::select('LowBalanceReminder[TemplateID]', $emailTemplates, (isset($LowBalanceReminder->TemplateID)?$LowBalanceReminder->TemplateID:'') ,array("class"=>"select2 select2add small form-control add-new-template-dp","data-type"=>'email_template','data-active'=>0,'data-modal'=>'add-new-modal-template'))}}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-5" class="col-sm-2 control-label">Period</label>
                                <div class="col-sm-4">
                                    {{Form::select('LowBalanceReminder[Time]',array(""=>"Select","MINUTE"=>"Minute","HOUR"=>"Hourly","DAILY"=>"Daily",'MONTHLY'=>'Monthly'),(isset($LowBalanceReminder->Time)?$LowBalanceReminder->Time:''),array( "class"=>"select2 small"))}}
                                </div>

                                <label for="field-5" class="col-sm-2 control-label">Interval</label>
                                <div class="col-sm-4">
                                    {{Form::select('LowBalanceReminder[Interval]',array(),'',array( "class"=>"select2 small"))}}
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
                                    {{Form::select('LowBalanceReminder[StartDay]',array(),'',array( "class"=>"select2 small"))}}
                                </div>
                            </div>

                        </div>
                    </div>
            </div>
            <div class="tab-pane" id="tab4" >
                <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                    <!-- panel body -->
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Active</label>
                            <div class="col-sm-4">
                                <div class="make-switch switch-small">
                                    <input type="checkbox" @if( isset($BillingClass->BalanceWarningStatus) && $BillingClass->BalanceWarningStatus == 1 )checked="" @endif name="BalanceWarningStatus" value="1">
                                </div>
                            </div>
                            <label class="col-sm-2 control-label">Send To Account Owner</label>
                            <div class="col-sm-4">
                                <div class="make-switch switch-small">
                                    <input type="checkbox" @if( isset($BalanceWarning->AccountManager) && $BalanceWarning->AccountManager == 1 )checked="" @endif name="BalanceWarning[AccountManager]" value="1">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Send Copy To</label>
                            <div class="col-sm-4">
                                <input type="text" name="BalanceWarning[ReminderEmail]" class="form-control" id="field-1" placeholder="" value="{{$BalanceWarning->ReminderEmail or ''}}" />
                            </div>
                            <label class="col-sm-2 control-label">Email Template</label>
                            <div class="col-sm-4">
                                {{Form::select('BalanceWarning[TemplateID]', $emailTemplates, (isset($BalanceWarning->TemplateID)?$BalanceWarning->TemplateID:'') ,array("class"=>"select2 select2add small form-control add-new-template-dp","data-type"=>'email_template','data-active'=>0,'data-modal'=>'add-new-modal-template'))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-5" class="col-sm-2 control-label">Days Before Next Invoice Date
                                <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="How many Days before Next Invoice Date send reminders" data-original-title="Reminder Days" class="label label-info popover-primary">?</span>
                            </label>
                            <div class="col-sm-4">
                                {{Form::input('number', 'BalanceWarning[RenewalDays]', (isset($BalanceWarning->RenewalDays)?$BalanceWarning->RenewalDays:''), ['min' => '0' ,'class' => 'form-control'])}}
                            </div>
                            <label for="field-5" class="col-sm-2 control-label">Include UnBilled Call Charges</label>
                            <div class="col-sm-4 ">
                                <div class="make-switch switch-small">
                                    <input type="checkbox" @if( isset($BalanceWarning->IncludeUnBilledAmount) && $BalanceWarning->IncludeUnBilledAmount == 1 )checked="" @endif name="BalanceWarning[IncludeUnBilledAmount]" value="1">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-5" class="col-sm-2 control-label">Period</label>
                            <div class="col-sm-4">
                                {{Form::select('BalanceWarning[Time]',array(""=>"Select","MINUTE"=>"Minute","HOUR"=>"Hourly","DAILY"=>"Daily",'MONTHLY'=>'Monthly'),(isset($BalanceWarning->Time)?$BalanceWarning->Time:''),array( "class"=>"select2 small"))}}
                            </div>

                            <label for="field-5" class="col-sm-2 control-label">Interval</label>
                            <div class="col-sm-4">
                                {{Form::select('BalanceWarning[Interval]',array(),'',array( "class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-5" class="col-sm-2 control-label">Day</label>
                            <div class="col-sm-4">
                                {{Form::select('BalanceWarning[Day][]',array("SUN"=>"Sunday","MON"=>"Monday","TUE"=>"Tuesday","WED"=>"Wednesday","THU"=>"Thursday","FRI"=>"Friday","SAT"=>"Saturday"),(isset($BalanceWarning->Day)?$BalanceWarning->Day:''),array( "class"=>"select2",'multiple',"data-placeholder"=>"Select day"))}}
                            </div>

                            <label for="field-5" class="col-sm-2 control-label">Start Time</label>
                            <div class="col-sm-4">
                                <input name="BalanceWarning[StartTime]" type="text" data-template="dropdown" data-show-seconds="true" data-default-time="12:00:00 AM" data-show-meridian="true" data-minute-step="5" class="form-control timepicker starttime2" value="{{(isset($BalanceWarning->StartTime)?$BalanceWarning->StartTime:'')}}" >
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
<div id="rowContainer"></div>
<script src="{{ URL::asset('assets/js/billing_class.js') }}"></script>
<script>
    var template_dp_html =  '{{Form::select('InvoiceReminder[TemplateID][]', $emailTemplates, '' ,array("class"=>"select22 select2add small form-control","data-type"=>'email_template','data-active'=>0,'data-modal'=>'add-new-modal-template'))}}';
    var add_row_html_payment = '<tr class="itemrow hidden"><td><button type="button" class=" remove-row btn btn-danger btn-xs">X</button></td><td><div class="input-spinner"><button type="button" class="btn btn-default">-</button><input type="text" name="InvoiceReminder[Day][]" class="form-control" id="field-1" placeholder="" value="" Placeholder="Add Numeric value" data-mask="decimal"/><button type="button" class="btn btn-default">+</button></div></td>';
    add_row_html_payment += '<td><div class="input-spinner"><button type="button" class="btn btn-default">-</button><input type="text" name="InvoiceReminder[Age][]" class="form-control" id="field-1" placeholder="" value="" Placeholder="Add Numeric value" data-mask="decimal"/><button type="button" class="btn btn-default">+</button></div></td>';
    add_row_html_payment += '<td>'+template_dp_html+'</td><tr>';
    $('#rowContainer').append(add_row_html_payment);
    var target = '';
    jQuery(document).ready(function ($) {

        $("[name='BalanceWarning[StartDay]']").keyup(function(){
            var days = $("[name='BalanceWarning[StartDay]']").val();
            if(days == 1)
            {
                $("[name='BalanceWarning[Time]']").select2().select2('val','HOUR');
            }
            else if(days >1 && days < 30)
            {
                $("[name='BalanceWarning[Time]']").select2().select2('val','DAILY');
            }
            else if(days >= 30)
            {
                $("[name='BalanceWarning[Time]']").select2().select2('val','MONTHLY');
            }
            //alert($("[name='BalanceWarning[Time]']").val());
        });
            setTimeout(function(){
                $("#billing-form [name='PaymentReminder[Time]']").trigger('change');
                $("#billing-form [name='LowBalanceReminder[Time]']").trigger('change');
                $("#billing-form [name='BalanceWarning[Time]']").trigger('change');
                @if(isset($PaymentReminders->Interval))
                $("#billing-form [name='PaymentReminder[Interval]']").val('{{$PaymentReminders->Interval}}').trigger('change');
                @endif
                @if(isset($LowBalanceReminder->Interval))
                $("#billing-form [name='LowBalanceReminder[Interval]']").val('{{$LowBalanceReminder->Interval}}').trigger('change');
                @endif
                 @if(isset($BalanceWarning->Interval))
                $("#billing-form [name='BalanceWarning[Interval]']").val('{{$BalanceWarning->Interval}}').trigger('change');
                @endif
            },50);
    });
</script>

@include('emailtemplate.emailtemplatemodal')
@include('invoicetemplates.invoicetemplatemodal')
