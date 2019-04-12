<ul class="nav nav-tabs">
    <li class="active"><a href="#tab1" data-toggle="tab">Rates</a></li>
</ul>
<div class="tab-content" style="overflow: hidden;margin-top: 15px;">
    <div class="tab-pane active" id="tab1">
        <div class="form-group ">
            <label for="field-1" class="col-sm-2 control-label">Package Name* </label>
            <div class="col-sm-2">
                {{Form::select('selection[Code]', $columns,(isset($attrselection->Code)?$attrselection->Code:''),array("class"=>"select2 small"))}}
            </div>
        </div>
        <div class="form-group duo">
            <label for="field-1" class="col-sm-2 control-label">EffectiveDate <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="If not selected then rates will be uploaded as effective immediately" data-original-title="EffectiveDate">?</span></label>
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

    {{-- Timezones wise rate mapping --}}
    @if(count($AllTimezones) > 0)
        <?php $co = 0; ?>
        @foreach($AllTimezones as $TimezoneID => $Title)
            <?php
                $id = $TimezoneID == 1 ? '' : $TimezoneID;
                $PackageCostPerMinuteColumn             = 'PackageCostPerMinute'.$id;
                $RecordingCostPerMinuteColumn           = 'RecordingCostPerMinute'.$id;

                $PackageCostPerMinuteCurrencyColumn     = 'PackageCostPerMinuteCurrency'.$id;
                $RecordingCostPerMinuteCurrencyColumn   = 'RecordingCostPerMinuteCurrency'.$id;
            ?>
            <div class="control-{{$PackageCostPerMinuteColumn}}">
                <label class="col-sm-2 control-label control-{{$PackageCostPerMinuteColumn}}-controls">Package Cost Per Minute</label>
                <div class="col-sm-2 control-{{$PackageCostPerMinuteColumn}}-controls">
                    {{Form::select('selection['.$PackageCostPerMinuteColumn.']', $columns,(isset($attrselection->$PackageCostPerMinuteColumn)?$attrselection->$PackageCostPerMinuteColumn:''),array("class"=>" small"))}}
                </div>
                <div class="col-sm-2 control-{{$PackageCostPerMinuteColumn}}-controls">
                    {{Form::select('selection['.$PackageCostPerMinuteCurrencyColumn.']', $component_currencies,(isset($attrselection->$PackageCostPerMinuteCurrencyColumn)?$attrselection->$PackageCostPerMinuteCurrencyColumn:''),array("class"=>"CurrencyDD small"))}}
                </div>
            </div>
            <div class="control-{{$RecordingCostPerMinuteColumn}}">
                <label class="col-sm-2 control-label control-{{$RecordingCostPerMinuteColumn}}-controls">Recording Cost Per Minute</label>
                <div class="col-sm-2 control-{{$RecordingCostPerMinuteColumn}}-controls">
                    {{Form::select('selection['.$RecordingCostPerMinuteColumn.']', $columns,(isset($attrselection->$RecordingCostPerMinuteColumn)?$attrselection->$RecordingCostPerMinuteColumn:''),array("class"=>" small"))}}
                </div>
                <div class="col-sm-2 control-{{$RecordingCostPerMinuteColumn}}-controls">
                    {{Form::select('selection['.$RecordingCostPerMinuteCurrencyColumn.']', $component_currencies,(isset($attrselection->$RecordingCostPerMinuteCurrencyColumn)?$attrselection->$RecordingCostPerMinuteCurrencyColumn:''),array("class"=>"CurrencyDD small"))}}
                </div>
            </div>
            <?php $co++; ?>
        @endforeach
    @endif
</div>

<input type="hidden" name="occupied_fields" id="occupied_fields" value="" />
<input type="hidden" name="occupied_timezone_fields" id="occupied_timezone_fields" value="" />