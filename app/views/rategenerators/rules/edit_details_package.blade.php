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
            <div class="col-md-4">
                <div class="form-group">
                    <label for="field-4" class="control-label">Package*</label>
                    {{ Form::select('PackageID', $Package,$rategenerator_rule['PackageID'], array("class"=>"select2")) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="field-4" class="control-label">Component*</label>
                    {{ Form::select('Component', DiscountPlan::$RateTablePKGRate_Components,$rategenerator_rule['Component'], array("class"=>"select2")) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="field-5" class="control-label">Time of Day*</label>
                    {{ Form::select('TimeOfDay', $Timezones, @$rategenerator_rule['TimeOfDay'], array("class"=>"select2")) }}
                </div>
            </div>
        </div>
    </form>
    </div>
</div>