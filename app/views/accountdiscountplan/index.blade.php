<div class="panel panel-primary discount-section-hide" data-collapsed="0">

    <div class="panel-heading">
        <div class="panel-title">
            Discount Plan
        </div>

        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>

    <div class="panel-body">
        <div class="form-group">
            <label for="field-1" class="col-sm-1 control-label">Access</label>
            <div class="col-sm-2">
                {{Form::select('InboundDiscountPlanID',$DiscountPlan, $InboundDiscountPlanID,array('class'=>'form-control select2'))}}
            </div>
            <div class="col-sm-1">
                <button id="inbound_minutes_report" class="btn btn-sm btn-primary tooltip-primary" data-original-title="View Detail" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading...">
                    <i class="fa fa-eye"></i>
                </button>
            </div>
            <label for="field-1" class="col-sm-1 control-label">Package</label>
            <div class="col-sm-2">
                {{Form::select('PackageDiscountPlanID',$DiscountPlan, $PackageDiscountPlanID,array('class'=>'form-control select2'))}}
            </div>
            <div class="col-sm-1">
                <button id="package_minutes_report" class="btn btn-sm btn-primary tooltip-primary" data-original-title="View Detail" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading...">
                    <i class="fa fa-eye"></i>
                </button>
            </div>
            <label for="field-1" class="col-sm-1 control-label">Termination</label>
            <div class="col-sm-2">
                {{Form::select('DiscountPlanID',$DiscountPlan, $DiscountPlanID,array('class'=>'form-control select2'))}}
            </div>
            <div class="col-sm-1">
                <button id="minutes_report" class="btn btn-sm btn-primary tooltip-primary" data-original-title="View Detail" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading...">
                    <i class="fa fa-eye"></i>
                </button>
            </div>
        </div>
    </div>
</div>