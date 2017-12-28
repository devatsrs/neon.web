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
    <label for="field-1" class="col-sm-2 control-label">Connect Date</label>
    <div class="col-sm-4">
        {{Form::select('selection[connect_date]', $columns,(isset($attrselection->connect_date)?$attrselection->connect_date:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Connect Time</label>
    <div class="col-sm-4">
        {{Form::select('selection[connect_time]', $columns,(isset($attrselection->connect_time)?$attrselection->connect_time:''),array("class"=>"select2 small"))}}
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
    <label for="field-1" class="col-sm-2 control-label">Account</label>
    <div class="col-sm-4">
        {{Form::select('selection[Account]', $columns,(isset($attrselection->Account)?$attrselection->Account:''),array("class"=>"select2 small"))}}
    </div>
    <label for=" field-1" class="col-sm-2 control-label">Selling Cost</label>
    <div class="col-sm-4">
        {{Form::select('selection[sellcost]', $columns,(isset($attrselection->sellcost)?$attrselection->sellcost:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for=" field-1" class="col-sm-2 control-label">Date Format</label>
    <div class="col-sm-4">
        {{Form::select('selection[DateFormat]',Company::$date_format ,(isset($attrselection->DateFormat)?$attrselection->DateFormat:''),array("class"=>"select2 small"))}}
    </div>
    <label for=" field-1" class="col-sm-2 control-label">Buying Cost</label>
    <div class="col-sm-4">
        {{Form::select('selection[buycost]',$columns,(isset($attrselection->buycost)?$attrselection->buycost:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for=" field-1" class="col-sm-2 control-label">Area Prefix</label>
    <div class="col-sm-4">
        {{Form::select('selection[area_prefix]',$columns,(isset($attrselection->area_prefix)?$attrselection->area_prefix:''),array("class"=>"select2 small"))}}
    </div>
</div>