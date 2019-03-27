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

        <form role="form" id="rategenerator-code-from" method="post" action="{{URL::to('rategenerators/rules/'.$id.'/update/'.$RateRuleID)}}" class="form-horizontal form-groups-bordered">
                    <div class="form-group">
                        <div class="col-sm-12 row">
                            <h5 align="center"><b>Origination</b></h5><br>
                            <label for="field-1" class="col-sm-1 control-label">Type</label>
                            <div class="col-sm-3">
                                {{Form::select('OriginationType',$type, $OriginationType ,array("class"=>"select2"))}}

                                {{--<input type="text" class="form-control popover-primary" name="OriginationCode"  id="field-1" placeholder="" value="{{$OriginationCode}}" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Enter either Origination Code Or Origination Description. Use * for all codes or description. For wildcard search use  e.g. 92* or india*." data-original-title="Origination Code/Description" />--}}
                            </div>
                            <label for="field-1" class="col-sm-1 control-label">Country</label>
                            <div class="col-sm-3">
                                {{Form::select('OriginationCountryID',$countryForRule, $OriginationCountryID ,array("class"=>"select2"))}}

                                {{--<input type="text" class="form-control popover-primary" name="OriginationCode"  id="field-1" placeholder="" value="{{$OriginationCode}}" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Enter either Origination Code Or Origination Description. Use * for all codes or description. For wildcard search use  e.g. 92* or india*." data-original-title="Origination Code/Description" />--}}
                            </div>
                            <label for="field-1" class="col-sm-1 control-label">Code</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control popover-primary" name="OriginationCode"  id="field-1" placeholder="" value="{{$OriginationCode}}" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Enter either Origination Code Or Origination Description. Use * for all codes or description. For wildcard search use  e.g. 92* or india*." data-original-title="Origination Code/Description" />
                            </div>
                            {{--<label for="field-1" class="col-sm-2 control-label">Description</label>--}}
                            {{--<div class="col-sm-4">--}}
                                {{--<input type="text" class="form-control popover-primary" name="OriginationDescription" id="field-2" placeholder="" value="{{$OriginationDescription}}" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Enter either Origination Code Or Origination Description. Use * for all codes or description. For wildcard search use  e.g. 92* or india*." data-original-title="Origination Code/Description"  />--}}
                            {{--</div>--}}
                        </div>
                    </div>

                        <div class="form-group">
                            <div class="col-sm-12 row">
                            <h5 align="center"><b>Destination</b></h5><br>
                            <label for="field-1" class="col-sm-1 control-label">Type</label>
                            <div class="col-sm-3">
                                {{Form::select('DestinationType',$type, $DestinationType ,array("class"=>"select2"))}}

                                {{--<input type="text" class="form-control popover-primary" name="OriginationCode"  id="field-1" placeholder="" value="{{$OriginationCode}}" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Enter either Origination Code Or Origination Description. Use * for all codes or description. For wildcard search use  e.g. 92* or india*." data-original-title="Origination Code/Description" />--}}
                            </div>
                            <label for="field-1" class="col-sm-1 control-label">Country</label>
                            <div class="col-sm-3">
                                {{Form::select('DestinationCountryID',$countryForRule, $DestinationCountryID ,array("class"=>"select2"))}}

                                {{--<input type="text" class="form-control popover-primary" name="OriginationCode"  id="field-1" placeholder="" value="{{$OriginationCode}}" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Enter either Origination Code Or Origination Description. Use * for all codes or description. For wildcard search use  e.g. 92* or india*." data-original-title="Origination Code/Description" />--}}
                            </div>
                            <label for="field-1" class="col-sm-1 control-label">Code</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control popover-primary" name="Code"  id="field-1" placeholder="" value="{{$DestinationCode}}" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Enter either Destination Code Or Destination Description. Use * for all codes or description. For wildcard search use  e.g. 92* or india*." data-original-title="Destination Code/Description" />
                            </div>
                            {{--<label for="field-1" class="col-sm-2 control-label">Description</label>--}}
                            {{--<div class="col-sm-4">--}}
                                {{--<input type="text" class="form-control popover-primary" name="Description" id="field-2" placeholder="" value="{{$DestinationDescription}}" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Enter either Destination Code Or Destination Description. Use * for all codes or description. For wildcard search use  e.g. 92* or india*." data-original-title="Destination Code/Description"  />--}}
                            {{--</div>--}}
                        </div>
                    </div>

                    <div class="clear clearfix"><br></div>
                   {{-- <p style="text-align: right;">
                        <button type="submit" class="save code btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="glyphicon glyphicon-circle-arrow-up"></i>
                            Save
                        </button>
                    </p>--}}
        </form>
    </div>
</div>