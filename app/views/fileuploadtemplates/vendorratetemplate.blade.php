<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Country Code</label>
    <div class="col-sm-4">
        {{Form::select('selection[CountryCode]', $columns,(isset($attrselection->CountryCode)?$attrselection->CountryCode:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Code* </label>
    <div class="col-sm-2">
        {{Form::select('selection[Code]', $columns,(isset($attrselection->Code)?$attrselection->Code:''),array("class"=>"select2 small"))}}
    </div>
    <div class="col-sm-2 popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Code Separator">
        {{Form::select('selection[DialCodeSeparator]',Company::$dialcode_separator ,(isset($attrselection->DialCodeSeparator)?$attrselection->DialCodeSeparator:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Description*</label>
    <div class="col-sm-4">
        {{Form::select('selection[Description]', $columns,(isset($attrselection->Description)?$attrselection->Description:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Rate*</label>
    <div class="col-sm-4">
        {{Form::select('selection[Rate]', $columns,(isset($attrselection->Rate)?$attrselection->Rate:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">EffectiveDate <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If not selected then rates will be uploaded as effective immediately" data-original-title="EffectiveDate">?</span></label>
    <div class="col-sm-4">
        {{Form::select('selection[EffectiveDate]', $columns,(isset($attrselection->EffectiveDate)?$attrselection->EffectiveDate:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">End Date <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If selected than rate will be deleted at this End Date" data-original-title="End Date">?</span></label>
    <div class="col-sm-4">
        {{Form::select('selection[EndDate]', $columns,(isset($attrselection->EndDate)?$attrselection->EndDate:''),array("class"=>"select2 small"))}}
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
    <label for="field-1" class="col-sm-2 control-label">Forbidden <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="0 - Unblock , 1 - Block" data-original-title="Forbidden">?</span></label>
    <div class="col-sm-4">
        {{Form::select('selection[Forbidden]', $columns,(isset($attrselection->Forbidden)?$attrselection->Forbidden:''),array("class"=>"select2 small"))}}
    </div>
    <label for="field-1" class="col-sm-2 control-label">Preference</label>
    <div class="col-sm-4">
        {{Form::select('selection[Preference]', $columns,(isset($attrselection->Preference)?$attrselection->Preference:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for="field-1" class="col-sm-2 control-label">Interval1</label>
    <div class="col-sm-4">
        {{Form::select('selection[Interval1]', $columns,(isset($attrselection->Interval1)?$attrselection->Interval1:''),array("class"=>"select2 small"))}}
    </div>
    <label for=" field-1" class="col-sm-2 control-label">IntervalN</label>
    <div class="col-sm-4">
        {{Form::select('selection[IntervalN]', $columns,(isset($attrselection->IntervalN)?$attrselection->IntervalN:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for=" field-1" class="col-sm-2 control-label">Connection Fee</label>
    <div class="col-sm-4">
        {{Form::select('selection[ConnectionFee]', $columns,(isset($attrselection->ConnectionFee)?$attrselection->ConnectionFee:''),array("class"=>"select2 small"))}}
    </div>
    <label for=" field-1" class="col-sm-2 control-label">Date Format <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Please check date format selected and date displays in grid." data-original-title="Date Format">?</span></label>
    <div class="col-sm-4">
        {{Form::select('selection[DateFormat]',Company::$date_format ,(isset($attrselection->DateFormat)?$attrselection->DateFormat:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for=" field-1" class="col-sm-2 control-label">Dial String <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If you want code to prefix mapping then select dial string." data-original-title="Dial String">?</span>
    </label>
    <div class="col-sm-4">
        {{Form::select('selection[DialString]',$dialstring ,(isset($attrselection->DialString)?$attrselection->DialString:''),array("class"=>"select2 small"))}}
    </div>
    <label for=" field-1" class="col-sm-2 control-label">Number Range <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Only Required when you have selected Dial String in mapping." data-original-title="Number Range">?</span></label>
    <div class="col-sm-4">
        {{Form::select('selection[DialStringPrefix]', $columns,(isset($attrselection->DialStringPrefix)?$attrselection->DialStringPrefix:''),array("class"=>"select2 small"))}}
    </div>
</div>
<div class="form-group">
    <label for=" field-1" class="col-sm-2 control-label">Currency Conversion <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Select currency to convert rates to your base currency" data-original-title="Currency Conversion">?</span></label>
    <div class="col-sm-4">
        {{Form::select('selection[FromCurrency]', $currencies ,(isset($attrselection->FromCurrency)?$attrselection->FromCurrency:''),array("class"=>"select2 small"))}}
    </div>
</div>