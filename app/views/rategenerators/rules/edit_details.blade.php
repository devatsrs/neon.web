<div  class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">

        </div>

        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
        @if (isset($RateRuleID))

        @else
            I don't have any records!
        @endif

        <form role="form" id="rategenerator-code-from" method="post" action="{{URL::to('rategenerators/rules/'.$id.'/update/'.$RateRuleID)}}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-4" class="control-label">Component*</label>
                        {{ Form::select('Component', DiscountPlan::$RateTableDIDRate_Components, $rategenerator_rule['Component'], array("class"=>"select2")) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-4" class="control-label">Country*</label>
                        {{ Form::select('CountryID', $country, @$rategenerator_rule['CountryID'], array("class"=>"select2")) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-5" class="control-label">Access Type*</label>
                        {{ Form::select('AccessType', $AccessType, @$rategenerator_rule['AccessType'], array("class"=>"select2")) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-5" class="control-label">Prefix*</label>
                        {{ Form::select('Prefix', $Prefix,@$rategenerator_rule['Prefix'], array("class"=>"select2")) }}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-5" class="control-label">City*</label>
                        {{ Form::select('City', $City, @$rategenerator_rule['City'], array("class"=>"select2")) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-5" class="control-label">Tariff*</label>
                        {{ Form::select('Tariff', $Tariff, @$rategenerator_rule['Tariff'], array("class"=>"select2")) }}
                    </div>
                    </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-5" class="control-label">Origination</label>
                        <input type="text" class="form-control" name="Origination" value="{{$rategenerator_rule['OriginationDescription']}}"/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-5" class="control-label">Time of Day*</label>
                        {{ Form::select('TimezonesID', $Timezones, @$rategenerator_rule['TimeOfDay'], array("class"=>"select2")) }}
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>