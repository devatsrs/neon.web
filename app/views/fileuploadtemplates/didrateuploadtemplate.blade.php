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
        <div class="form-group ">
            <label for="field-1" class="col-sm-2 control-label">Destination Code* </label>
            <div class="col-sm-2">
                {{Form::select('selection[Code]', $columns,(isset($attrselection->Code)?$attrselection->Code:''),array("class"=>"select2 small"))}}
            </div>
            <div class="col-sm-2 popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Code Separator">
                {{Form::select('selection[DialCodeSeparator]',Company::$dialcode_separator ,(isset($attrselection->DialCodeSeparator)?$attrselection->DialCodeSeparator:''),array("class"=>"select2 small dialcodeseperator"))}}
            </div>
            <label for="field-1" class="col-sm-2 control-label">Destination Description*</label>
            <div class="col-sm-4">
                {{Form::select('selection[Description]', $columns,(isset($attrselection->Description)?$attrselection->Description:''),array("class"=>"select2 small"))}}
            </div>
        </div>
        <div class="form-group duo">
            <label for="field-1" class="col-sm-2 control-label">EffectiveDate <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If not selected then rates will be uploaded as effective immediately" data-original-title="EffectiveDate">?</span></label>
            <div class="col-sm-4">
                {{Form::select('selection[EffectiveDate]', $columns,(isset($attrselection->EffectiveDate)?$attrselection->EffectiveDate:''),array("class"=>"select2 small"))}}
            </div>
            <label for=" field-1" class="col-sm-2 control-label">Date Format <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Please check date format selected and date displays in grid." data-original-title="Date Format">?</span></label>
            <div class="col-sm-4">
                {{Form::select('selection[DateFormat]',Company::$date_format ,(isset($attrselection->DateFormat)?$attrselection->DateFormat:''),array("class"=>"select2 small"))}}
            </div>
        </div>

        {{-- Timezones wise rate mapping --}}
        @if(count($AllTimezones) > 0)
            <?php $co = 0; ?>
            @foreach($AllTimezones as $TimezoneID => $Title)
                <?php
                    $id = $TimezoneID == 1 ? '' : $TimezoneID;
                    $OneOffCostColumn           = 'OneOffCost'.$id;
                    $MonthlyCostColumn          = 'MonthlyCost'.$id;
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
                        <div class="form-group duo_timezone">
                            <label class="col-sm-2 control-label">One Off Cost</label>
                            <div class="col-sm-4">
                                {{Form::select('selection['.$OneOffCostColumn.']', $columns,(isset($attrselection->$OneOffCostColumn)?$attrselection->$OneOffCostColumn:''),array("class"=>"select2 small"))}}
                            </div>
                            <label class="col-sm-2 control-label">Monthly Cost</label>
                            <div class="col-sm-4">
                                {{Form::select('selection['.$MonthlyCostColumn.']', $columns,(isset($attrselection->$MonthlyCostColumn)?$attrselection->$MonthlyCostColumn:''),array("class"=>"select2 small"))}}
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
            <div class="col-sm-2 popover-primary " data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Origination Code Separator">
                {{Form::select('selection2[OriginationDialCodeSeparator]',Company::$dialcode_separator ,(isset($attrselection2->OriginationDialCodeSeparator)?$attrselection2->OriginationDialCodeSeparator:''),array("class"=>"select2 small dialcodeseperator"))}}
            </div>
            <label class="col-sm-2 control-label">Destination Code* </label>
            <div class="col-sm-2">
                {{Form::select('selection2[Code]', $columns,(isset($attrselection2->Code)?$attrselection2->Code:''),array("class"=>"select2 small"))}}
            </div>
            <div class="col-sm-2 popover-primary " data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Destination Code Separator">
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

<div style="display: none;" id="controls-selection">
    <div class="control-EndDate">
        <label class="col-sm-2 control-label control-EndDate-controls">End Date <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If selected than rate will be deleted at this End Date" data-original-title="End Date">?</span></label>
        <div class="col-sm-4 control-EndDate-controls">
            {{Form::select('selection[EndDate]', $columns,(isset($attrselection->EndDate)?$attrselection->EndDate:''),array("class"=>" small"))}}
        </div>
    </div>
    <div class="control-Action">
        <label class="col-sm-2 control-label control-Action-controls">Action</label>
        <div class="col-sm-4 control-Action-controls">
            {{Form::select('selection[Action]', $columns,(isset($attrselection->Action)?$attrselection->Action:''),array("class"=>" small"))}}
        </div>
    </div>
    <div class="control-ActionDelete">
        <label class="col-sm-2 control-label control-ActionDelete-controls">Action Delete</label>
        <div class="col-sm-4 control-ActionDelete-controls">
            <input type="text" class="form-control" name="selection[ActionDelete]" value="{{(!empty($attrselection->ActionDelete)?$attrselection->ActionDelete:'D')}}" />
        </div>
    </div>
    <div class="control-DialString">
        <label class="col-sm-2 control-label control-DialString-controls">Dial String <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If you want code to prefix mapping then select dial string." data-original-title="Dial String">?</span></label>
        <div class="col-sm-4 control-DialString-controls">
            {{Form::select('selection[DialString]',$dialstring ,(isset($attrselection->DialString)?$attrselection->DialString:''),array("class"=>" small"))}}
        </div>
    </div>
    <div class="control-DialStringPrefix">
        <label class="col-sm-2 control-label control-DialStringPrefix-controls">Number Range <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Only Required when you have selected Dial String in mapping." data-original-title="Number Range">?</span></label>
        <div class="col-sm-4 control-DialStringPrefix-controls">
            {{Form::select('selection[DialStringPrefix]', $columns,(isset($attrselection->DialStringPrefix)?$attrselection->DialStringPrefix:''),array("class"=>" small"))}}
        </div>
    </div>
    <div class="control-FromCurrency">
        <label class="col-sm-2 control-label control-FromCurrency-controls">Currency Conversion <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Select currency to convert rates to your base currency" data-original-title="Currency Conversion">?</span></label>
        <div class="col-sm-4 control-FromCurrency-controls">
            {{Form::select('selection[FromCurrency]', $currencies ,(isset($attrselection->FromCurrency)?$attrselection->FromCurrency:''),array("class"=>" small"))}}
        </div>
    </div>
    <div class="control-OriginationCountryCode">
        <label class="col-sm-2 control-label control-OriginationCountryCode-controls">
            Origination Country Code
            <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="Origination Country Code only requires when you have seperate columns for Origination Country Codes and Origination City Codes in your rate file." data-original-title="Origination Country Code">?</span>
        </label>
        <div class="col-sm-4 control-OriginationCountryCode-controls">
            {{Form::select('selection[OriginationCountryCode]', $columns,(isset($attrselection->OriginationCountryCode)?$attrselection->OriginationCountryCode:''),array("class"=>" small"))}}
        </div>
    </div>
    <div class="control-OriginationCode">
        <label class="col-sm-2 control-label control-OriginationCode-controls">Origination Code </label>
        <div class="col-sm-2 control-OriginationCode-controls">
            {{Form::select('selection[OriginationCode]', $columns,(isset($attrselection->OriginationCode)?$attrselection->OriginationCode:''),array("class"=>" small"))}}
        </div>
        <div class="col-sm-2 popover-primary control-OriginationCode-controls" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Code Separator">
            {{Form::select('selection[OriginationDialCodeSeparator]',Company::$dialcode_separator ,(isset($attrselection->OriginationDialCodeSeparator)?$attrselection->OriginationDialCodeSeparator:''),array("class"=>" small dialcodeseperator"))}}
        </div>
    </div>
    <div class="control-OriginationDescription">
        <label class="col-sm-2 control-label control-OriginationDescription-controls">Origination Description</label>
        <div class="col-sm-4 control-OriginationDescription-controls">
            {{Form::select('selection[OriginationDescription]', $columns,(isset($attrselection->OriginationDescription)?$attrselection->OriginationDescription:''),array("class"=>" small"))}}
        </div>
    </div>
    <div class="control-CountryCode">
        <label class="col-sm-2 control-label control-CountryCode-controls">
            Destination Country Code
            <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="Destination Country Code only requires when you have seperate columns for Destination Country Codes and Destination City Codes in your rate file." data-original-title="Country Code">?</span>
        </label>
        <div class="col-sm-4 control-CountryCode-controls">
            {{Form::select('selection[CountryCode]', $columns,(isset($attrselection->CountryCode)?$attrselection->CountryCode:''),array("class"=>" small"))}}
        </div>
    </div>

    {{-- Timezones wise rate mapping --}}
    @if(count($AllTimezones) > 0)
        <?php $co = 0; ?>
        @foreach($AllTimezones as $TimezoneID => $Title)
            <?php
                $id = $TimezoneID == 1 ? '' : $TimezoneID;
                $CostPerCallColumn                  = 'CostPerCall'.$id;
                $CostPerMinuteColumn                = 'CostPerMinute'.$id;
                $SurchargePerCallColumn             = 'SurchargePerCall'.$id;
                $SurchargePerMinuteColumn           = 'SurchargePerMinute'.$id;
                $OutpaymentPerCallColumn            = 'OutpaymentPerCall'.$id;
                $OutpaymentPerMinuteColumn          = 'OutpaymentPerMinute'.$id;
                $SurchargesColumn                   = 'Surcharges'.$id;
                $ChargebackColumn                   = 'Chargeback'.$id;
                $CollectionCostAmountColumn         = 'CollectionCostAmount'.$id;
                $CollectionCostPercentageColumn     = 'CollectionCostPercentage'.$id;
                $RegistrationCostPerNumberColumn    = 'RegistrationCostPerNumber'.$id;
            ?>
            <div class="control-{{$CostPerCallColumn}}">
                <label class="col-sm-2 control-label control-{{$CostPerCallColumn}}-controls">Cost Per Call</label>
                <div class="col-sm-4 control-{{$CostPerCallColumn}}-controls">
                    {{Form::select('selection['.$CostPerCallColumn.']', $columns,(isset($attrselection->$CostPerCallColumn)?$attrselection->$CostPerCallColumn:''),array("class"=>" small"))}}
                </div>
            </div>
            <div class="control-{{$CostPerMinuteColumn}}">
                <label class="col-sm-2 control-label control-{{$CostPerMinuteColumn}}-controls">Cost Per Minute</label>
                <div class="col-sm-4 control-{{$CostPerMinuteColumn}}-controls">
                    {{Form::select('selection['.$CostPerMinuteColumn.']', $columns,(isset($attrselection->$CostPerMinuteColumn)?$attrselection->$CostPerMinuteColumn:''),array("class"=>" small"))}}
                </div>
            </div>
            <div class="control-{{$SurchargePerCallColumn}}">
                <label class="col-sm-2 control-label control-{{$SurchargePerCallColumn}}-controls">Surcharge Per Call</label>
                <div class="col-sm-4 control-{{$SurchargePerCallColumn}}-controls">
                    {{Form::select('selection['.$SurchargePerCallColumn.']', $columns,(isset($attrselection->$SurchargePerCallColumn)?$attrselection->$SurchargePerCallColumn:''),array("class"=>" small"))}}
                </div>
            </div>
            <div class="control-{{$SurchargePerMinuteColumn}}">
                <label class="col-sm-2 control-label control-{{$SurchargePerMinuteColumn}}-controls">Surcharge Per Minute</label>
                <div class="col-sm-4 control-{{$SurchargePerMinuteColumn}}-controls">
                    {{Form::select('selection['.$SurchargePerMinuteColumn.']', $columns,(isset($attrselection->$SurchargePerMinuteColumn)?$attrselection->$SurchargePerMinuteColumn:''),array("class"=>" small"))}}
                </div>
            </div>
            <div class="control-{{$OutpaymentPerCallColumn}}">
                <label class="col-sm-2 control-label control-{{$OutpaymentPerCallColumn}}-controls">Outpayment Per Call</label>
                <div class="col-sm-4 control-{{$OutpaymentPerCallColumn}}-controls">
                    {{Form::select('selection['.$OutpaymentPerCallColumn.']', $columns,(isset($attrselection->$OutpaymentPerCallColumn)?$attrselection->$OutpaymentPerCallColumn:''),array("class"=>" small"))}}
                </div>
            </div>
            <div class="control-{{$OutpaymentPerMinuteColumn}}">
                <label class="col-sm-2 control-label control-{{$OutpaymentPerMinuteColumn}}-controls">Outpayment Per Minute</label>
                <div class="col-sm-4 control-{{$OutpaymentPerMinuteColumn}}-controls">
                    {{Form::select('selection['.$OutpaymentPerMinuteColumn.']', $columns,(isset($attrselection->$OutpaymentPerMinuteColumn)?$attrselection->$OutpaymentPerMinuteColumn:''),array("class"=>" small"))}}
                </div>
            </div>
            <div class="control-{{$SurchargesColumn}}">
                <label class="col-sm-2 control-label control-{{$SurchargesColumn}}-controls">Surcharges</label>
                <div class="col-sm-4 control-{{$SurchargesColumn}}-controls">
                    {{Form::select('selection['.$SurchargesColumn.']', $columns,(isset($attrselection->$SurchargesColumn)?$attrselection->$SurchargesColumn:''),array("class"=>" small"))}}
                </div>
            </div>
            <div class="control-{{$ChargebackColumn}}">
                <label class="col-sm-2 control-label control-{{$ChargebackColumn}}-controls">Chargeback</label>
                <div class="col-sm-4 control-{{$ChargebackColumn}}-controls">
                    {{Form::select('selection['.$ChargebackColumn.']', $columns,(isset($attrselection->$ChargebackColumn)?$attrselection->$ChargebackColumn:''),array("class"=>" small"))}}
                </div>
            </div>
            <div class="control-{{$CollectionCostAmountColumn}}">
                <label class="col-sm-2 control-label control-{{$CollectionCostAmountColumn}}-controls">Collection Cost Amount</label>
                <div class="col-sm-4 control-{{$CollectionCostAmountColumn}}-controls">
                    {{Form::select('selection['.$CollectionCostAmountColumn.']', $columns,(isset($attrselection->$CollectionCostAmountColumn)?$attrselection->$CollectionCostAmountColumn:''),array("class"=>" small"))}}
                </div>
            </div>
            <div class="control-{{$CollectionCostPercentageColumn}}">
                <label class="col-sm-2 control-label control-{{$CollectionCostPercentageColumn}}-controls">Collection Cost (%)</label>
                <div class="col-sm-4 control-{{$CollectionCostPercentageColumn}}-controls">
                    {{Form::select('selection['.$CollectionCostPercentageColumn.']', $columns,(isset($attrselection->$CollectionCostPercentageColumn)?$attrselection->$CollectionCostPercentageColumn:''),array("class"=>" small"))}}
                </div>
            </div>
            <div class="control-{{$RegistrationCostPerNumberColumn}}">
                <label class="col-sm-2 control-label control-{{$RegistrationCostPerNumberColumn}}-controls">Registration Cost Per Number</label>
                <div class="col-sm-4 control-{{$RegistrationCostPerNumberColumn}}-controls">
                    {{Form::select('selection['.$RegistrationCostPerNumberColumn.']', $columns,(isset($attrselection->$RegistrationCostPerNumberColumn)?$attrselection->$RegistrationCostPerNumberColumn:''),array("class"=>" small"))}}
                </div>
            </div>
            <?php $co++; ?>
        @endforeach
    @endif
</div>

<input type="hidden" name="occupied_fields" id="occupied_fields" value="" />
<input type="hidden" name="occupied_timezone_fields" id="occupied_timezone_fields" value="" />