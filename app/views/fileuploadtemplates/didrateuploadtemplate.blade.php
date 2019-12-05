<ul class="nav nav-tabs">
    <li class="active box_dialcode"><a href="#tab1" data-toggle="tab">Rates</a></li>
    <li class="box_dialcode"><a href="#tab2" data-toggle="tab">Dial Codes</a></li>
</ul>
<div class="tab-content" style="overflow: hidden;margin-top: 15px;">
    <div class="tab-pane active" id="tab1">
        <div class="form-group box_dialcode">
            <label class="col-sm-2 control-label">Match Origination Prefix with</label>
            <div class="col-sm-4">
                {{Form::select('selection[Join1O]', $columns,(isset($attrselection->Join1O)?$attrselection->Join1O:''),array("class"=>"select2 small","id"=>"Join1O"))}}
            </div>
            <label class="col-sm-2 control-label">Match Prefix with</label>
            <div class="col-sm-4">
                {{Form::select('selection[Join1]', $columns,(isset($attrselection->Join1)?$attrselection->Join1:''),array("class"=>"select2 small","id"=>"Join1"))}}
            </div>
        </div>
        <div class="form-group ">
            <label class="col-sm-2 control-label">Access Type</label>
            <div class="col-sm-4">
                {{Form::select('selection[AccessType]', $AccessTypes ,(isset($attrselection->AccessType)?$attrselection->AccessType:''),array("class"=>"DualMapping small"))}}
            </div>

            <label class="col-sm-2 control-label control-CountryCode-controls">
                Country
                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="Country only requires when you have seperate columns for Country Codes and City Codes in your rate file." data-original-title="Country Code">?</span>
            </label>
            <div class="col-sm-3 control-CountryCode-controls">
                {{Form::select('selection[CountryCode]', $CountryPrefix,(isset($attrselection->CountryCode)?$attrselection->CountryCode:''),array("class"=>"DualMapping small"))}}
            </div>
            <div class="col-sm-1 control-CountryCode-controls">
                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="Tick this box if you are mapping country description e.g. USA. Leave it un tick if you are mapping country code e.g. 1" data-original-title="Country Mapping">?</span>
                {{Form::checkbox('selection[CountryMapping]', '1', false, array("class"=>"CountryMapping"))}}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Prefix* </label>
            <div class="col-sm-4">
                {{Form::select('selection[Code]', $Codes,(isset($attrselection->Code)?$attrselection->Code:''),array("class"=>"DualMapping select2 small"))}}
            </div>
            <label class="col-sm-2 control-label">Seperators </label>
            <div class="col-sm-2 popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split country and prefix" data-original-title="Country-Prefix Separator">
                {{Form::select('selection[CountryCodeSeparator]',Company::$countrycode_separator,(isset($attrselection->CountryCodeSeparator)?$attrselection->CountryCodeSeparator:''),array("class"=>"select2 small countrycodeseperator"))}}
            </div>
            <div class="col-sm-2 popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Code Separator">
                {{Form::select('selection[DialCodeSeparator]',Company::$dialcode_separator ,(isset($attrselection->DialCodeSeparator)?$attrselection->DialCodeSeparator:''),array("class"=>"select2 small dialcodeseperator countrycodeseperator"))}}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">City</label>
            <div class="col-sm-4">
                {{Form::select('selection[City]', $City ,(isset($attrselection->City)?$attrselection->City:''),array("class"=>"DualMapping small"))}}
            </div>
            <label class="col-sm-2 control-label">Tariff</label>
            <div class="col-sm-4">
                {{Form::select('selection[Tariff]', $Tariff ,(isset($attrselection->Tariff)?$attrselection->Tariff:''),array("class"=>"DualMapping small"))}}
            </div>
        </div>
        <div class="form-group duo">
            <label class="col-sm-2 control-label">EffectiveDate <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="If not selected then rates will be uploaded as effective immediately" data-original-title="EffectiveDate">?</span></label>
            <div class="col-sm-4">
                {{Form::select('selection[EffectiveDate]', $columns,(isset($attrselection->EffectiveDate)?$attrselection->EffectiveDate:''),array("class"=>"select2 small"))}}
            </div>
            <label for=" field-1" class="col-sm-2 control-label">Date Format <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="Please check date format selected and date displays in grid." data-original-title="Date Format">?</span></label>
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
                    $OneOffCostCurrencyColumn   = 'OneOffCostCurrency'.$id;
                    $MonthlyCostCurrencyColumn  = 'MonthlyCostCurrency'.$id;
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
                            <div class="col-sm-2">
                                {{Form::select('selection['.$OneOffCostColumn.']', $columns,'',array("class"=>"select2 small"))}}
                            </div>
                            <div class="col-sm-2">
                                {{Form::select('selection['.$OneOffCostCurrencyColumn.']', $component_currencies,(isset($attrselection->$OneOffCostCurrencyColumn)?$attrselection->$OneOffCostCurrencyColumn:''),array("class"=>"select2 CurrencyDD small"))}}
                            </div>
                            <label class="col-sm-2 control-label">Monthly Cost</label>
                            <div class="col-sm-2">
                                {{Form::select('selection['.$MonthlyCostColumn.']', $columns,'',array("class"=>"select2 small"))}}
                            </div>
                            <div class="col-sm-2">
                                {{Form::select('selection['.$MonthlyCostCurrencyColumn.']', $component_currencies,(isset($attrselection->$MonthlyCostCurrencyColumn)?$attrselection->$MonthlyCostCurrencyColumn:''),array("class"=>"select2 CurrencyDD small"))}}
                            </div>
                        </div>
                    </div>
                </div>
                <?php $co++; ?>
            @endforeach
        @endif

    </div>
    <!-- this tab2 is now no longer in use for did and pkg -->
    <div class="tab-pane" id="tab2">
        <div class="form-group">
            <label class="col-sm-2 control-label">Match Origination Prefix with</label>
            <div class="col-sm-4">
                {{Form::select('selection2[Join2O]', $columns,(isset($attrselection2->Join2O)?$attrselection2->Join2O:''),array("class"=>"select2 small","id"=>"Join2O"))}}
            </div>
            <label class="col-sm-2 control-label">Match Prefix with</label>
            <div class="col-sm-4">
                {{Form::select('selection2[Join2]', $columns,(isset($attrselection2->Join2)?$attrselection2->Join2:''),array("class"=>"select2 small","id"=>"Join2"))}}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Country Code</label>
            <div class="col-sm-3">
                {{Form::select('selection2[CountryCode]', $columns,(isset($attrselection2->CountryCode)?$attrselection2->CountryCode:''),array("class"=>"select2 small"))}}
            </div>
            <div class="col-sm-1 control-CountryCode-controls">
                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="Tick this box if you are mapping country description e.g. USA. Leave it un tick if you are mapping country code e.g. 1" data-original-title="Country Mapping">?</span>
                {{Form::checkbox('selection2[CountryMapping]', '1', false, array("class"=>"CountryMapping"))}}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Origination </label>
            <div class="col-sm-2">
                {{Form::select('selection2[OriginationCode]', $columns,(isset($attrselection2->OriginationCode)?$attrselection2->OriginationCode:''),array("class"=>"select2 small"))}}
            </div>
            <div class="col-sm-2 popover-primary " data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Origination Separator">
                {{Form::select('selection2[OriginationDialCodeSeparator]',Company::$dialcode_separator ,(isset($attrselection2->OriginationDialCodeSeparator)?$attrselection2->OriginationDialCodeSeparator:''),array("class"=>"select2 small dialcodeseperator"))}}
            </div>
            <label class="col-sm-2 control-label">Prefix* </label>
            <div class="col-sm-2">
                {{Form::select('selection2[Code]', $columns,(isset($attrselection2->Code)?$attrselection2->Code:''),array("class"=>"select2 small"))}}
            </div>
            <div class="col-sm-2 popover-primary " data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Code Separator">
                {{Form::select('selection2[DialCodeSeparator]',Company::$dialcode_separator ,(isset($attrselection2->DialCodeSeparator)?$attrselection2->DialCodeSeparator:''),array("class"=>"select2 small dialcodeseperator"))}}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">EffectiveDate <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="If not selected then rates will be uploaded as effective immediately" data-original-title="EffectiveDate">?</span></label>
            <div class="col-sm-4">
                {{Form::select('selection2[EffectiveDate]', $columns,(isset($attrselection2->EffectiveDate)?$attrselection2->EffectiveDate:''),array("class"=>"select2 small"))}}
            </div>
            <label for=" field-1" class="col-sm-2 control-label">Date Format <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="Please check date format selected and date displays in grid." data-original-title="Date Format">?</span></label>
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
    <div class="control-FromCurrency">
        <label class="col-sm-2 control-label control-FromCurrency-controls" style="display: none;">Currency Conversion <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Select currency to convert rates to your base currency" data-original-title="Currency Conversion">?</span></label>
        <div class="col-sm-4 control-FromCurrency-controls" style="display: none;">
            {{Form::select('selection[FromCurrency]', $currencies ,(isset($attrselection->FromCurrency)?$attrselection->FromCurrency:''),array("class"=>" small"))}}
        </div>
    </div>
    <div class="control-OriginationCode">
        <label class="col-sm-2 control-label control-OriginationCode-controls">Origination </label>
        <div class="col-sm-2 control-OriginationCode-controls">
            {{Form::select('selection[OriginationCode]', $Codes,(isset($attrselection->OriginationCode)?$attrselection->OriginationCode:''),array("class"=>"DualMapping small"))}}
        </div>
        <div class="col-sm-2 popover-primary control-OriginationCode-controls" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Use this to split codes in one line" data-original-title="Code Separator">
            {{Form::select('selection[OriginationDialCodeSeparator]',Company::$dialcode_separator ,(isset($attrselection->OriginationDialCodeSeparator)?$attrselection->OriginationDialCodeSeparator:''),array("class"=>" small dialcodeseperator"))}}
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

                $CostPerCallCurrencyColumn                  = 'CostPerCallCurrency'.$id;
                $CostPerMinuteCurrencyColumn                = 'CostPerMinuteCurrency'.$id;
                $SurchargePerCallCurrencyColumn             = 'SurchargePerCallCurrency'.$id;
                $SurchargePerMinuteCurrencyColumn           = 'SurchargePerMinuteCurrency'.$id;
                $OutpaymentPerCallCurrencyColumn            = 'OutpaymentPerCallCurrency'.$id;
                $OutpaymentPerMinuteCurrencyColumn          = 'OutpaymentPerMinuteCurrency'.$id;
                $SurchargesCurrencyColumn                   = 'SurchargesCurrency'.$id;
                $ChargebackCurrencyColumn                   = 'ChargebackCurrency'.$id;
                $CollectionCostAmountCurrencyColumn         = 'CollectionCostAmountCurrency'.$id;
                $RegistrationCostPerNumberCurrencyColumn    = 'RegistrationCostPerNumberCurrency'.$id;
            ?>
            <div class="control-{{$CostPerCallColumn}}">
                <label class="col-sm-2 control-label control-{{$CostPerCallColumn}}-controls">Cost Per Call</label>
                <div class="col-sm-2 control-{{$CostPerCallColumn}}-controls">
                    {{Form::select('selection['.$CostPerCallColumn.']', $columns,(isset($attrselection->$CostPerCallColumn)?$attrselection->$CostPerCallColumn:''),array("class"=>" small"))}}
                </div>
                <div class="col-sm-2 control-{{$CostPerCallColumn}}-controls">
                    {{Form::select('selection['.$CostPerCallCurrencyColumn.']', $component_currencies,(isset($attrselection->$CostPerCallCurrencyColumn)?$attrselection->$CostPerCallCurrencyColumn:''),array("class"=>"CurrencyDD small"))}}
                </div>
            </div>
            <div class="control-{{$CostPerMinuteColumn}}">
                <label class="col-sm-2 control-label control-{{$CostPerMinuteColumn}}-controls">Cost Per Minute</label>
                <div class="col-sm-2 control-{{$CostPerMinuteColumn}}-controls">
                    {{Form::select('selection['.$CostPerMinuteColumn.']', $columns,(isset($attrselection->$CostPerMinuteColumn)?$attrselection->$CostPerMinuteColumn:''),array("class"=>" small"))}}
                </div>
                <div class="col-sm-2 control-{{$CostPerMinuteColumn}}-controls">
                    {{Form::select('selection['.$CostPerMinuteCurrencyColumn.']', $component_currencies,(isset($attrselection->$CostPerMinuteCurrencyColumn)?$attrselection->$CostPerMinuteCurrencyColumn:''),array("class"=>"CurrencyDD small"))}}
                </div>
            </div>
            <div class="control-{{$SurchargePerCallColumn}}">
                <label class="col-sm-2 control-label control-{{$SurchargePerCallColumn}}-controls">Surcharge Per Call</label>
                <div class="col-sm-2 control-{{$SurchargePerCallColumn}}-controls">
                    {{Form::select('selection['.$SurchargePerCallColumn.']', $columns,(isset($attrselection->$SurchargePerCallColumn)?$attrselection->$SurchargePerCallColumn:''),array("class"=>" small"))}}
                </div>
                <div class="col-sm-2 control-{{$SurchargePerCallColumn}}-controls">
                    {{Form::select('selection['.$SurchargePerCallCurrencyColumn.']', $component_currencies,(isset($attrselection->$SurchargePerCallCurrencyColumn)?$attrselection->$SurchargePerCallCurrencyColumn:''),array("class"=>"CurrencyDD small"))}}
                </div>
            </div>
            <div class="control-{{$SurchargePerMinuteColumn}}">
                <label class="col-sm-2 control-label control-{{$SurchargePerMinuteColumn}}-controls">Surcharge Per Minute</label>
                <div class="col-sm-2 control-{{$SurchargePerMinuteColumn}}-controls">
                    {{Form::select('selection['.$SurchargePerMinuteColumn.']', $columns,(isset($attrselection->$SurchargePerMinuteColumn)?$attrselection->$SurchargePerMinuteColumn:''),array("class"=>" small"))}}
                </div>
                <div class="col-sm-2 control-{{$SurchargePerMinuteColumn}}-controls">
                    {{Form::select('selection['.$SurchargePerMinuteCurrencyColumn.']', $component_currencies,(isset($attrselection->$SurchargePerMinuteCurrencyColumn)?$attrselection->$SurchargePerMinuteCurrencyColumn:''),array("class"=>"CurrencyDD small"))}}
                </div>
            </div>
            <div class="control-{{$OutpaymentPerCallColumn}}">
                <label class="col-sm-2 control-label control-{{$OutpaymentPerCallColumn}}-controls">Outpayment Per Call</label>
                <div class="col-sm-2 control-{{$OutpaymentPerCallColumn}}-controls">
                    {{Form::select('selection['.$OutpaymentPerCallColumn.']', $columns,(isset($attrselection->$OutpaymentPerCallColumn)?$attrselection->$OutpaymentPerCallColumn:''),array("class"=>" small"))}}
                </div>
                <div class="col-sm-2 control-{{$OutpaymentPerCallColumn}}-controls">
                    {{Form::select('selection['.$OutpaymentPerCallCurrencyColumn.']', $component_currencies,(isset($attrselection->$OutpaymentPerCallCurrencyColumn)?$attrselection->$OutpaymentPerCallCurrencyColumn:''),array("class"=>"CurrencyDD small"))}}
                </div>
            </div>
            <div class="control-{{$OutpaymentPerMinuteColumn}}">
                <label class="col-sm-2 control-label control-{{$OutpaymentPerMinuteColumn}}-controls">Outpayment Per Minute</label>
                <div class="col-sm-2 control-{{$OutpaymentPerMinuteColumn}}-controls">
                    {{Form::select('selection['.$OutpaymentPerMinuteColumn.']', $columns,(isset($attrselection->$OutpaymentPerMinuteColumn)?$attrselection->$OutpaymentPerMinuteColumn:''),array("class"=>" small"))}}
                </div>
                <div class="col-sm-2 control-{{$OutpaymentPerMinuteColumn}}-controls">
                    {{Form::select('selection['.$OutpaymentPerMinuteCurrencyColumn.']', $component_currencies,(isset($attrselection->$OutpaymentPerMinuteCurrencyColumn)?$attrselection->$OutpaymentPerMinuteCurrencyColumn:''),array("class"=>"CurrencyDD small"))}}
                </div>
            </div>
            <div class="control-{{$SurchargesColumn}}">
                <label class="col-sm-2 control-label control-{{$SurchargesColumn}}-controls">Surcharges</label>
                <div class="col-sm-2 control-{{$SurchargesColumn}}-controls">
                    {{Form::select('selection['.$SurchargesColumn.']', $columns,(isset($attrselection->$SurchargesColumn)?$attrselection->$SurchargesColumn:''),array("class"=>" small"))}}
                </div>
                <div class="col-sm-2 control-{{$SurchargesColumn}}-controls">
                    {{Form::select('selection['.$SurchargesCurrencyColumn.']', $component_currencies,(isset($attrselection->$SurchargesCurrencyColumn)?$attrselection->$SurchargesCurrencyColumn:''),array("class"=>"CurrencyDD small"))}}
                </div>
            </div>
            <div class="control-{{$ChargebackColumn}}">
                <label class="col-sm-2 control-label control-{{$ChargebackColumn}}-controls">Chargeback</label>
                <div class="col-sm-2 control-{{$ChargebackColumn}}-controls">
                    {{Form::select('selection['.$ChargebackColumn.']', $columns,(isset($attrselection->$ChargebackColumn)?$attrselection->$ChargebackColumn:''),array("class"=>" small"))}}
                </div>
                <div class="col-sm-2 control-{{$ChargebackColumn}}-controls">
                    {{Form::select('selection['.$ChargebackCurrencyColumn.']', $component_currencies,(isset($attrselection->$ChargebackCurrencyColumn)?$attrselection->$ChargebackCurrencyColumn:''),array("class"=>"CurrencyDD small"))}}
                </div>
            </div>
            <div class="control-{{$CollectionCostAmountColumn}}">
                <label class="col-sm-2 control-label control-{{$CollectionCostAmountColumn}}-controls">Collection Cost Amount</label>
                <div class="col-sm-2 control-{{$CollectionCostAmountColumn}}-controls">
                    {{Form::select('selection['.$CollectionCostAmountColumn.']', $columns,(isset($attrselection->$CollectionCostAmountColumn)?$attrselection->$CollectionCostAmountColumn:''),array("class"=>" small"))}}
                </div>
                <div class="col-sm-2 control-{{$CollectionCostAmountColumn}}-controls">
                    {{Form::select('selection['.$CollectionCostAmountCurrencyColumn.']', $component_currencies,(isset($attrselection->$CollectionCostAmountCurrencyColumn)?$attrselection->$CollectionCostAmountCurrencyColumn:''),array("class"=>"CurrencyDD small"))}}
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
                <div class="col-sm-2 control-{{$RegistrationCostPerNumberColumn}}-controls">
                    {{Form::select('selection['.$RegistrationCostPerNumberColumn.']', $columns,(isset($attrselection->$RegistrationCostPerNumberColumn)?$attrselection->$RegistrationCostPerNumberColumn:''),array("class"=>" small"))}}
                </div>
                <div class="col-sm-2 control-{{$RegistrationCostPerNumberColumn}}-controls">
                    {{Form::select('selection['.$RegistrationCostPerNumberCurrencyColumn.']', $component_currencies,(isset($attrselection->$RegistrationCostPerNumberCurrencyColumn)?$attrselection->$RegistrationCostPerNumberCurrencyColumn:''),array("class"=>"CurrencyDD small"))}}
                </div>
            </div>
            <?php $co++; ?>
        @endforeach
    @endif
</div>

<input type="hidden" name="occupied_fields" id="occupied_fields" value="" />
<input type="hidden" name="occupied_timezone_fields" id="occupied_timezone_fields" value="" />

<div class="panel panel-primary">
    <div class="panel-heading">
        <div class="panel-title">
            City Mapping (Not Mapped)
        </div>
        <div class="panel-options">
            <div class="btn-group">
                <button type="button" id="btnCityMappingExportExcel" class="btn btn-default btn-sm" style="border: 1px solid #C0C0C0">Excel</button>
                <button type="button" id="btnCityMappingExportCSV" class="btn btn-default btn-sm" style="border: 1px solid #C0C0C0">CSV</button>
            </div>
            <button type="button" id="btnCityMapping" class="btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-arrows-ccw"></i>Refresh Mapping</button>
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>

    <div class="panel-body" id="CityMappingPanel" style="max-height: 500px; overflow-y: auto">
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <div class="panel-title">
            City Mapping (Mapped)
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>

    <div class="panel-body" id="CityMappingPanelMapped" style="max-height: 500px; overflow-y: auto">
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <div class="panel-title">
            Tariff Mapping (Not Mapped)
        </div>
        <div class="panel-options">
            <div class="btn-group">
                <button type="button" id="btnTariffMappingExportExcel" class="btn btn-default btn-sm" style="border: 1px solid #C0C0C0">Excel</button>
                <button type="button" id="btnTariffMappingExportCSV" class="btn btn-default btn-sm" style="border: 1px solid #C0C0C0">CSV</button>
            </div>
            <button type="button" id="btnTariffMapping" class="btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-arrows-ccw"></i>Refresh Mapping</button>
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>

    <div class="panel-body" id="TariffMappingPanel" style="max-height: 500px; overflow-y: auto">
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <div class="panel-title">
            Tariff Mapping (Mapped)
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>

    <div class="panel-body" id="TariffMappingPanelMapped" style="max-height: 500px; overflow-y: auto">
    </div>
</div>