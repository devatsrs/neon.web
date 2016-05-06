<div class="col-md-12">
    <form novalidate="novalidate" class="form-horizontal form-groups-bordered filter validate" method="post">
        <div data-collapsed="0" class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">
                    Filter
                </div>
                <div class="panel-options">
                    <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-1 control-label" for="field-1">Start date</label>
                    <div class="col-sm-2">
                        <input type="text" name="StartDate"  class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d')}}"/>
                    </div>
                    <label class="col-sm-1 control-label" for="field-1">End date</label>
                    <div class="col-sm-2">
                        <input type="text" name="EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d')}}" />
                    </div>
                    <label class="col-sm-1 control-label" for="field-1">Gateway Account</label>
                    <div class="col-sm-2">
                        {{ Form::select('AccountID',$account,'', array("class"=>"select2")) }}
                    </div>
                    <label class="col-sm-1 control-label" for="field-1">Gateway</label>
                    <div class="col-sm-2">
                        {{ Form::select('GatewayID',$gateway,'', array("class"=>"select2")) }}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-1 control-label" for="field-1">Prefix</label>
                    <div class="col-sm-2">
                        <input type="text" name="Prefix"  class="form-control"/>
                    </div>
                </div>
                <input type="hidden" name="UserID" value="{{$UserID}}">
                <input type="hidden" name="Admin" value="{{$isAdmin}}">
                <input type="hidden" name="chart_type" value="prefix">
                <input type="hidden" name="CountryID" value="0">
                <input type="hidden" name="TrunkID" value="0">
                <p style="text-align: right;">
                    <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </p>
            </div>
        </div>
    </form>
</div>
<div class="clear"></div>
<div class="col-md-4">

    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
        <!-- panel head -->
        <div class="panel-heading">
            <div class="panel-title">Prefix</div>

            {{--<div class="panel-options">
                <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
            </div>--}}
        </div>

        <!-- panel body -->
        <div class="panel-body">
            <p>By Prefix - Call Count. </p>
            <br />

            <div class="text-center">
                <span class="prefix-call-count-pie-chart"></span>
            </div>
            <p class="call_count_desc"></p>
        </div>
    </div>
</div>
<div class="col-md-4">

    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
        <!-- panel head -->
        <div class="panel-heading">
            <div class="panel-title">Prefix</div>

            {{--<div class="panel-options">
                <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
            </div>--}}
        </div>

        <!-- panel body -->
        <div class="panel-body">
            <p>By Prefix - Call Cost. </p>
            <br />

            <div class="text-center">
                <span class="prefix-call-cost-pie-chart"></span>
            </div>
            <p class="call_cost_desc"></p>
        </div>
    </div>
</div>
<div class="col-md-4">

    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
        <!-- panel head -->
        <div class="panel-heading">
            <div class="panel-title">Prefix</div>

            {{--<div class="panel-options">
                <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
            </div>--}}
        </div>

        <!-- panel body -->
        <div class="panel-body">
            <p>By Prefix - Call Minutes. </p>
            <br />

            <div class="text-center">
                <span class="prefix-call-minutes-pie-chart"></span>
            </div>
            <p class="call_minutes_desc"></p>
        </div>
    </div>
</div>
<div class="clear"></div>
<div class="col-md-12">
    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
        <!-- panel head -->
        <div class="panel-heading">
            {{--<div class="panel-options">
                <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
            </div>--}}
        </div>

        <!-- panel body -->
        <div class="panel-body">
            <div class="text-center">
                <div class="bar_chart_prefix"></div>
            </div>
        </div>
    </div>

</div>
<div class="clear"></div>