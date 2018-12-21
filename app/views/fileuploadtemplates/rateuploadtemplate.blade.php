<ul class="nav nav-tabs">
    <li class="active box_dialcode"><a href="#tab1" data-toggle="tab">Rates</a></li>
    <li class="box_dialcode"><a href="#tab2" data-toggle="tab">Dial Codes</a></li>
</ul>
<div class="tab-content" style="overflow: hidden;margin-top: 15px;">
    <div class="tab-pane active" id="tab1">
        <div class="form-group box_dialcode">
            <label for="field-1" class="col-sm-2 control-label">Match Origination Codes with</label>
            <div class="col-sm-4">
                {{Form::select('selection[Join1O]', $columns,(isset($attrselection->Join1O)?$attrselection->Join1O:''),array("class"=>"select2 small","id"=>"Join1O"))}}
            </div>
            <label class="col-sm-2 control-label">Match Destination Codes with</label>
            <div class="col-sm-4">
                {{Form::select('selection[Join1]', $columns,(isset($attrselection->Join1)?$attrselection->Join1:''),array("class"=>"select2 small","id"=>"Join1"))}}
            </div>
        </div>
        <div class="form-group box_code">
            <label for="field-1" class="col-sm-2 control-label">
                Origination Country Code
                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="Origination Country Code only requires when you have seperate columns for Origination Country Codes and Origination City Codes in your rate file." data-original-title="Origination Country Code">?</span>
            </label>
            <div class="col-sm-4">
                {{Form::select('selection[OriginationCountryCode]', $columns,(isset($attrselection->OriginationCountryCode)?$attrselection->OriginationCountryCode:''),array("class"=>"select2 small"))}}
            </div>
            <label for="field-1" class="col-sm-2 control-label">
                Destination Country Code
                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="Destination Country Code only requires when you have seperate columns for Destination Country Codes and Destination City Codes in your rate file." data-original-title="Country Code">?</span>
            </label>
            <div class="col-sm-4">
                {{Form::select('selection[CountryCode]', $columns,(isset($attrselection->CountryCode)?$attrselection->CountryCode:''),array("class"=>"select2 small"))}}
            </div>
        </div>
        <div class="form-group box_code">
            <label for="field-1" class="col-sm-2 control-label">Origination Code* </label>
            <div class="col-sm-2">
                {{Form::select('selection[OriginationCode]', $columns,(isset($attrselection->OriginationCode)?$attrselection->OriginationCode:''),array("class"=>"select2 small"))}}
            </div>
            <div class="col-sm-2 popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Code Separator">
                {{Form::select('selection[OriginationDialCodeSeparator]',Company::$dialcode_separator ,(isset($attrselection->OriginationDialCodeSeparator)?$attrselection->OriginationDialCodeSeparator:''),array("class"=>"select2 small dialcodeseperator"))}}
            </div>
            <label for="field-1" class="col-sm-2 control-label">Destination Code* </label>
            <div class="col-sm-2">
                {{Form::select('selection[Code]', $columns,(isset($attrselection->Code)?$attrselection->Code:''),array("class"=>"select2 small"))}}
            </div>
            <div class="col-sm-2 popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Code Separator">
                {{Form::select('selection[DialCodeSeparator]',Company::$dialcode_separator ,(isset($attrselection->DialCodeSeparator)?$attrselection->DialCodeSeparator:''),array("class"=>"select2 small dialcodeseperator"))}}
            </div>
        </div>
        <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">Origination Description*</label>
            <div class="col-sm-4">
                {{Form::select('selection[OriginationDescription]', $columns,(isset($attrselection->OriginationDescription)?$attrselection->OriginationDescription:''),array("class"=>"select2 small"))}}
            </div>
            <label for="field-1" class="col-sm-2 control-label">Destination Description*</label>
            <div class="col-sm-4">
                {{Form::select('selection[Description]', $columns,(isset($attrselection->Description)?$attrselection->Description:''),array("class"=>"select2 small"))}}
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
            <label for="field-1" class="col-sm-2 control-label">Action Delete</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" name="selection[ActionDelete]" value="{{(!empty($attrselection->ActionDelete)?$attrselection->ActionDelete:'D')}}" />
            </div>
        </div>
        <div class="form-group" style="display: none;">
            <label for="field-1" class="col-sm-2 control-label">Action Insert</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" name="selection[ActionInsert]" value="{{(!empty($attrselection->ActionInsert)?$attrselection->ActionInsert:'I')}}" />
            </div>
            <label for="field-1" class="col-sm-2 control-label">Action Update</label>
            <div class="col-sm-4">
                <input type="text" class="form-control" name="selection[ActionUpdate]" value="{{(!empty($attrselection->ActionUpdate)?$attrselection->ActionUpdate:'U')}}" />
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
            <label for=" field-1" class="col-sm-2 control-label">Date Format <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Please check date format selected and date displays in grid." data-original-title="Date Format">?</span></label>
            <div class="col-sm-4">
                {{Form::select('selection[DateFormat]',Company::$date_format ,(isset($attrselection->DateFormat)?$attrselection->DateFormat:''),array("class"=>"select2 small"))}}
            </div>
        </div>
        <div class="form-group {{RateUpload::vendor.'content'}} typecontentbox {{ $RateUploadType != RateUpload::vendor ? 'hidden' : '' }}">
            <label for=" field-1" class="col-sm-2 control-label">Currency Conversion <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Select currency to convert rates to your base currency" data-original-title="Currency Conversion">?</span></label>
            <div class="col-sm-4">
                {{Form::select('selection[FromCurrency]', $currencies ,(isset($attrselection->FromCurrency)?$attrselection->FromCurrency:''),array("class"=>"select2 small"))}}
            </div>
        </div>

        {{-- Timezones wise rate mapping --}}
        @if(count($AllTimezones) > 0)
            <?php $co = 0; ?>
            @foreach($AllTimezones as $TimezoneID => $Title)
                <?php
                    $id = $TimezoneID == 1 ? '' : $TimezoneID;
                    $Rate1Column           = 'Rate'.$id;
                    $RateNColumn           = 'RateN'.$id;
                    $Interval1Column       = 'Interval1'.$id;
                    $IntervalNColumn       = 'IntervalN'.$id;
                    $PreferenceColumn      = 'Preference'.$id;
                    $ConnectionFeeColumn   = 'ConnectionFee'.$id;
                    $BlockedColumn         = 'Blocked'.$id;
                    $RoutingCategory       = 'RoutingCategory'.$id;
                ?>
                <div class="panel panel-primary {{$id != '' ? 'panel-collapse' : ''}}" id="panel-mapping-{{$id}}" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            {{$Title}} Rate Mapping
                        </div>
                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>

                    <div class="panel-body field-remaping" id="mapping-{{$id}}" style="{{$id != '' ? 'display:none;' : ''}}">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Rate 1</label>
                            <div class="col-sm-4">
                                {{Form::select('selection['.$Rate1Column.']', $columns,(isset($attrselection->$Rate1Column)?$attrselection->$Rate1Column:''),array("class"=>"select2 small"))}}
                            </div>
                            <label class="col-sm-2 control-label">
                                Rate N
                                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="Optional. If RateN not selected then RateN will be same as Rate1." data-original-title="Rate N">?</span>
                            </label>
                            <div class="col-sm-4">
                                {{Form::select('selection['.$RateNColumn.']', $columns,(isset($attrselection->$RateNColumn)?$attrselection->$RateNColumn:''),array("class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Interval1</label>
                            <div class="col-sm-4">
                                {{Form::select('selection['.$Interval1Column.']', $columns,(isset($attrselection->$Interval1Column)?$attrselection->$Interval1Column:''),array("class"=>"select2 small"))}}
                            </div>
                            <label class="col-sm-2 control-label">IntervalN</label>
                            <div class="col-sm-4">
                                {{Form::select('selection['.$IntervalNColumn.']', $columns,(isset($attrselection->$IntervalNColumn)?$attrselection->$IntervalNColumn:''),array("class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="form-group vendor_selection_box">
                            <label class="col-sm-2 control-label">Preference</label>
                            <div class="col-sm-4">
                                {{Form::select('selection['.$PreferenceColumn.']', $columns,(isset($attrselection->$PreferenceColumn)?$attrselection->$PreferenceColumn:''),array("class"=>"select2 small"))}}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Blocked <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="0 - Unblock , 1 - Block" data-original-title="Blocked">?</span></label>
                            <div class="col-sm-4">
                                {{Form::select('selection['.$BlockedColumn.']', $columns,(isset($attrselection->$BlockedColumn)?$attrselection->$BlockedColumn:''),array("class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Connection Fee</label>
                            <div class="col-sm-4">
                                {{Form::select('selection['.$ConnectionFeeColumn.']', $columns,(isset($attrselection->$ConnectionFeeColumn)?$attrselection->$ConnectionFeeColumn:''),array("class"=>"select2 small"))}}
                            </div>
                            <label class="col-sm-2 control-label vendor_selection_box">Routing Category</label>
                            <div class="col-sm-4 vendor_selection_box">
                                {{Form::select('selection['.$RoutingCategory.']', $columns,(isset($attrselection->$RoutingCategory)?$attrselection->$RoutingCategory:''),array("class"=>"select2 small"))}}
                            </div>
                        </div>
                    </div>
                </div>
                <?php $co++; ?>
            @endforeach
        @endif

    </div>
    <div class="tab-pane" id="tab2">
        <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">Match Origination Codes with</label>
            <div class="col-sm-4">
                {{Form::select('selection2[Join2O]', $columns,(isset($attrselection2->Join2O)?$attrselection2->Join2O:''),array("class"=>"select2 small","id"=>"Join2O"))}}
            </div>
            <label class="col-sm-2 control-label">Match Destination Codes with</label>
            <div class="col-sm-4">
                {{Form::select('selection2[Join2]', $columns,(isset($attrselection2->Join2)?$attrselection2->Join2:''),array("class"=>"select2 small","id"=>"Join2"))}}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Origination Country Code</label>
            <div class="col-sm-4">
                {{Form::select('selection2[OriginationCountryCode]', $columns,(isset($attrselection2->OriginationCountryCode)?$attrselection2->OriginationCountryCode:''),array("class"=>"select2 small"))}}
            </div>
            <label class="col-sm-2 control-label">Destination Country Code</label>
            <div class="col-sm-4">
                {{Form::select('selection2[CountryCode]', $columns,(isset($attrselection2->CountryCode)?$attrselection2->CountryCode:''),array("class"=>"select2 small"))}}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Origination Code </label>
            <div class="col-sm-2">
                {{Form::select('selection2[OriginationCode]', $columns,(isset($attrselection2->OriginationCode)?$attrselection2->OriginationCode:''),array("class"=>"select2 small"))}}
            </div>
            <div class="col-sm-2 popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Origination Code Separator">
                {{Form::select('selection2[OriginationDialCodeSeparator]',Company::$dialcode_separator ,(isset($attrselection2->OriginationDialCodeSeparator)?$attrselection2->OriginationDialCodeSeparator:''),array("class"=>"select2 small dialcodeseperator"))}}
            </div>
            <label class="col-sm-2 control-label">Destination Code* </label>
            <div class="col-sm-2">
                {{Form::select('selection2[Code]', $columns,(isset($attrselection2->Code)?$attrselection2->Code:''),array("class"=>"select2 small"))}}
            </div>
            <div class="col-sm-2 popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Destination Code Separator">
                {{Form::select('selection2[DialCodeSeparator]',Company::$dialcode_separator ,(isset($attrselection2->DialCodeSeparator)?$attrselection2->DialCodeSeparator:''),array("class"=>"select2 small dialcodeseperator"))}}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Origination Description</label>
            <div class="col-sm-4">
                {{Form::select('selection2[OriginationDescription]', $columns,(isset($attrselection2->OriginationDescription)?$attrselection2->OriginationDescription:''),array("class"=>"select2 small"))}}
            </div>
            <label class="col-sm-2 control-label">Destination Description*</label>
            <div class="col-sm-4">
                {{Form::select('selection2[Description]', $columns,(isset($attrselection2->Description)?$attrselection2->Description:''),array("class"=>"select2 small"))}}
            </div>
        </div>
        <div class="form-group">
            <label for="field-1" class="col-sm-2 control-label">EffectiveDate <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If not selected then rates will be uploaded as effective immediately" data-original-title="EffectiveDate">?</span></label>
            <div class="col-sm-4">
                {{Form::select('selection2[EffectiveDate]', $columns,(isset($attrselection2->EffectiveDate)?$attrselection2->EffectiveDate:''),array("class"=>"select2 small"))}}
            </div>
            <label for=" field-1" class="col-sm-2 control-label">Date Format <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Please check date format selected and date displays in grid." data-original-title="Date Format">?</span></label>
            <div class="col-sm-4">
                {{Form::select('selection2[DateFormat]',Company::$date_format ,(isset($attrselection2->DateFormat)?$attrselection2->DateFormat:''),array("class"=>"select2 small"))}}
            </div>
        </div>
    </div>
</div>