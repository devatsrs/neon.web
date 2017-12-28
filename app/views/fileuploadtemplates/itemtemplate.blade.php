<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Name *</label>
    <div class="col-sm-4">
        {{Form::select('selection[Name]', $columns,(isset($attrselection->Name)?$attrselection->Name:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Code *</label>
    <div class="col-sm-4">
        {{Form::select('selection[Code]', $columns,(isset($attrselection->Code)?$attrselection->Code:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Description *</label>
    <div class="col-sm-4">
        {{Form::select('selection[Description]', $columns,(isset($attrselection->Description)?$attrselection->Description:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Unit Cost *</label>
    <div class="col-sm-4">
        {{Form::select('selection[Amount]', $columns,(isset($attrselection->Amount)?$attrselection->Amount:''),array("class"=>"select2 small"))}}
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
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Note</label>
    <div class="col-sm-4">
        {{Form::select('selection[Note]', $columns,(isset($attrselection->Note)?$attrselection->Note:''),array("class"=>"select2 small"))}}
    </div>

    @if (isset($DynamicFields) && $DynamicFields['totalfields'] > 0)
        <?php $l=0; ?>
        @foreach($DynamicFields['fields'] as $field)
            @if($field->Status == 1)
                @if($l%2 != 0)
</div>
<div class="form-group">
                @endif
    <label for="field-1" class="col-sm-2 control-label">{{ $field->FieldName }}</label>
    <div class="col-sm-4">
        <?php $DynamicField = 'DynamicFields-'.$field->DynamicFieldsID; ?>
        {{Form::select('selection[DynamicFields-'.$field->DynamicFieldsID.']', $columns,(isset($attrselection->$DynamicField)?$attrselection->$DynamicField:''),array("class"=>"select2 small"))}}
    </div>
                <?php $l++; ?>
            @endif
        @endforeach
    @endif
</div>