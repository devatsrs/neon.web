<div  class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">

        </div>

        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
        <form role="form" id="rategenerator-code-from" method="post" action="{{URL::to('rategenerators/rules/'.$id.'/update/'.$RateRuleID)}}" class="form-horizontal form-groups-bordered">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-1 control-label">Code</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control popover-primary" name="Code"  id="field-1" placeholder="" value="{{$Code}}" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Enter either Code Or Description. Use * for all codes or description. For wildcard search use  e.g. 92* or india*." data-original-title="Code/Description" />
                        </div>
                        <label for="field-1" class="col-sm-1 control-label">Description</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control popover-primary" name="Description" id="field-2" placeholder="" value="{{$Description}}" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Enter either Code Or Description. Use * for all codes or description. For wildcard search use  e.g. 92* or india*." data-original-title="Code/Description"  />
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