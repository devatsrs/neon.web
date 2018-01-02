<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Account Name*</label>
    <div class="col-sm-4">
        {{Form::select('selection[AccountName]', $columns,(isset($attrselection->AccountName)?$attrselection->AccountName:''),array("class"=>"select2 small"))}}
    </div>

    <label for="field-1" class="col-sm-2 control-label">IP*</label>
    <div class="col-sm-4">
        {{Form::select('selection[IP]', $columns,(isset($attrselection->IP)?$attrselection->IP:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Type*
        <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Customer IP or Vendor IP" data-original-title="Type">?</span>
    </label>
    <div class="col-sm-4">
        {{Form::select('selection[Type]', $columns,(isset($attrselection->Type)?$attrselection->Type:''),array("class"=>"select2 small"))}}
    </div>

    <label for="field-1" class="col-sm-2 control-label">Service</label>
    <div class="col-sm-4">
        {{Form::select('selection[Service]', $columns,(isset($attrselection->Service)?$attrselection->Service:''),array("class"=>"select2 small"))}}
    </div>
</div>