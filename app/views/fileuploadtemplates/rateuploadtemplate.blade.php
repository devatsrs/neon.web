<ul class="nav nav-tabs">
    <li class="active"><a href="#tab1" data-toggle="tab">Rates</a></li>
    <li><a href="#tab2" data-toggle="tab">Dial Codes</a></li>
</ul>
<div class="tab-content" style="overflow: hidden;margin-top: 15px;">
<div class="tab-pane active" id="tab1">
    <div class="form-group">
        <label for="field-1" class="col-sm-2 control-label">Match Codes with DialCode On</label>
        <div class="col-sm-4">
            {{Form::select('selection[Join1]', $columns,(isset($attrselection->Join1)?$attrselection->Join1:''),array("class"=>"select2 small","id"=>"Join1"))}}
        </div>
    </div>
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
<div class="form-group {{RateUpload::vendor.'content'}} typecontentbox {{ $RateUploadType != RateUpload::vendor ? 'hidden' : '' }}">
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
</div>
    <div class="tab-pane " id="tab2">
        <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">Match Codes with Rates On</label>
            <div class="col-sm-4">
                {{Form::select('selection2[Join2]', $columns,(isset($attrselection2->Join2)?$attrselection2->Join2:''),array("class"=>"select2 small","id"=>"Join2"))}}
            </div>
        </div>
        <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">Country Code</label>
            <div class="col-sm-4">
                {{Form::select('selection2[CountryCode]', $columns,(isset($attrselection2->CountryCode)?$attrselection2->CountryCode:''),array("class"=>"select2 small"))}}
            </div>
            <label for="field-1" class="col-sm-2 control-label">Code* </label>
            <div class="col-sm-2">
                {{Form::select('selection2[Code]', $columns,(isset($attrselection2->Code)?$attrselection2->Code:''),array("class"=>"select2 small"))}}
            </div>
            <div class="col-sm-2 popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Code Separator">
                {{Form::select('selection2[DialCodeSeparator]',Company::$dialcode_separator ,(isset($attrselection2->DialCodeSeparator)?$attrselection2->DialCodeSeparator:''),array("class"=>"select2 small"))}}
            </div>
        </div>

        <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">Description*</label>
            <div class="col-sm-4">
                {{Form::select('selection2[Description]', $columns,(isset($attrselection2->Description)?$attrselection2->Description:''),array("class"=>"select2 small"))}}
            </div>
            <label for="field-1" class="col-sm-2 control-label">EffectiveDate <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If not selected then rates will be uploaded as effective immediately" data-original-title="EffectiveDate">?</span></label>
            <div class="col-sm-4">
                {{Form::select('selection2[EffectiveDate]', $columns,(isset($attrselection2->EffectiveDate)?$attrselection2->EffectiveDate:''),array("class"=>"select2 small"))}}
            </div>
        </div>

        <div class="form-group">
        <label for=" field-1" class="col-sm-2 control-label">Date Format <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Please check date format selected and date displays in grid." data-original-title="Date Format">?</span></label>
        <div class="col-sm-4">
            {{Form::select('selection2[DateFormat]',Company::$date_format ,(isset($attrselection2->DateFormat)?$attrselection2->DateFormat:''),array("class"=>"select2 small"))}}
        </div>

    </div>
</div>