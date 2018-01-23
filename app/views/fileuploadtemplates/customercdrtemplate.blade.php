<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Connect DateTime</label>
    <div class="col-sm-4">
        {{Form::select('selection[connect_datetime]', $columns,(isset($attrselection->connect_datetime)?$attrselection->connect_datetime:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Disconnect DateTime</label>
    <div class="col-sm-4">
        {{Form::select('selection[disconnect_time]', $columns,(isset($attrselection->disconnect_time)?$attrselection->disconnect_time:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Billed Duration</label>
    <div class="col-sm-4">
        {{Form::select('selection[billed_duration]', $columns,(isset($attrselection->billed_duration)?$attrselection->billed_duration:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Duration</label>
    <div class="col-sm-4">
        {{Form::select('selection[duration]', $columns,(isset($attrselection->duration)?$attrselection->duration:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for=" field-1" class="col-sm-2 control-label">CLI</label>
    <div class="col-sm-4">
        {{Form::select('selection[cli]', $columns,(isset($attrselection->cli)?$attrselection->cli:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">CLD</label>
    <div class="col-sm-4">
        {{Form::select('selection[cld]', $columns,(isset($attrselection->cld)?$attrselection->cld:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for=" field-1" class="col-sm-2 control-label">CLI Translation Rule<span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Syntax: /<match what>/<replace with>/" data-original-title="CLI Translation Rule">?</span></label>
    <div class="col-sm-4">
        <input type="text" class="form-control" name="selection[CLITranslationRule]" value="{{(!empty($attrselection->CLITranslationRule)?$attrselection->CLITranslationRule:'')}}" />
    </div>
    <label for=" field-1" class="col-sm-2 control-label">CLD Translation Rule<span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Syntax: /<match what>/<replace with>/" data-original-title="CLD Translation Rule">?</span></label>
    <div class="col-sm-4">
        <input type="text" class="form-control" name="selection[CLDTranslationRule]" value="{{(!empty($attrselection->CLDTranslationRule)?$attrselection->CLDTranslationRule:'')}}" />
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Account*</label>
    <div class="col-sm-4">
        {{Form::select('selection[Account]', $columns,(isset($attrselection->Account)?$attrselection->Account:''),array("class"=>"select2 small"))}}
    </div>
    <label for=" field-1" class="col-sm-2 control-label">Cost</label>
    <div class="col-sm-4">
        {{Form::select('selection[cost]', $columns,(isset($attrselection->cost)?$attrselection->cost:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for=" field-1" class="col-sm-2 control-label">Date Format</label>
    <div class="col-sm-4">
        {{Form::select('selection[DateFormat]',Company::$date_format ,(isset($attrselection->DateFormat)?$attrselection->DateFormat:''),array("class"=>"select2 small"))}}
    </div>
    <label for=" field-1" class="col-sm-2 control-label">Inbound/Outbound <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If not selected then cdrs will be uploaded as outbound" data-original-title="Inbound/Outbound">?</span></label>
    <div class="col-sm-4">
        {{Form::select('selection[is_inbound]',$columns,(isset($attrselection->is_inbound)?$attrselection->is_inbound:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label" for="field-1">Service</label>
    <div class="col-sm-4">
        {{ Form::select('selection[ServiceID]',$Services,(isset($attrselection->ServiceID)?$attrselection->ServiceID:''), array("class"=>"select2")) }}
    </div>
    <label class="col-sm-2 control-label" for="field-1">Trunk</label>
    <div class="col-sm-4">
        {{ Form::select('selection[TrunkID]',$trunks,(isset($attrselection->TrunkID)?$attrselection->TrunkID:''), array("class"=>"select2")) }}
    </div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label" for="field-1">Inbound Rate Table<span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If selected then rate will be ignored from service and account level." data-original-title="Inbound Rate Table">?</span></label>
    <div class="col-sm-4">
        {{ Form::select('selection[InboundRateTableID]',$ratetables,(isset($attrselection->InboundRateTableID)?$attrselection->InboundRateTableID:''), array("class"=>"select2")) }}
    </div>
    <label class="col-sm-2 control-label" for="field-1">Outbound Rate Table<span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If selected then rate will be ignored from service and account level." data-original-title="Outbound Rate Table">?</span></label>
    <div class="col-sm-4">
        {{ Form::select('selection[OutboundRateTableID]',$ratetables,(isset($attrselection->OutboundRateTableID)?$attrselection->OutboundRateTableID:''), array("class"=>"select2")) }}
    </div>
</div>
<div class="form-group">
    <label for=" field-1" class="col-sm-2 control-label">Extension</label>
    <div class="col-sm-4">
        {{Form::select('selection[extension]', $columns,(isset($attrselection->extension)?$attrselection->extension:''),array("class"=>"select2 small"))}}
    </div>
    <label for=" field-1" class="col-sm-2 control-label chargecode">Charge Code</label>
    <div class="col-sm-4 chargecode">
        {{Form::select('selection[ChargeCode]',$columns,(isset($attrselection->ChargeCode)?$attrselection->ChargeCode:''),array("class"=>"select2 small"))}}
    </div>
</div>