<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Prefix*</label>
    <div class="col-sm-4">
        {{Form::select('selection[DialString]', $columns,(isset($attrselection->DialString)?$attrselection->DialString:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Charge Code*</label>
    <div class="col-sm-4">
        {{Form::select('selection[ChargeCode]', $columns,(isset($attrselection->ChargeCode)?$attrselection->ChargeCode:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Description</label>
    <div class="col-sm-4">
        {{Form::select('selection[Description]', $columns,(isset($attrselection->Description)?$attrselection->Description:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Forbidden</label>
    <div class="col-sm-4">
        {{Form::select('selection[Forbidden]', $columns,(isset($attrselection->Forbidden)?$attrselection->Forbidden:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Action</label>
    <div class="col-sm-4">
        {{Form::select('selection[Action]', $columns,(isset($attrselection->Action)?$attrselection->Action:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Action Insert</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" name="selection[ActionInsert]" value="{{(!empty($attrselection->ActionInsert)?$attrselection->ActionInsert:'I')}}" />
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Action Update</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" name="selection[ActionUpdate]" value="{{(!empty($attrselection->ActionUpdate)?$attrselection->ActionUpdate:'U')}}" />
    </div>
    <label for="field-1" class="col-sm-2 control-label">Action Delete</label>
    <div class="col-sm-4">
        <input type="text" class="form-control" name="selection[ActionDelete]" value="{{(!empty($attrselection->ActionDelete)?$attrselection->ActionDelete:'D')}}" />
    </div>
</div>