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
                {{Form::select('InboundDiscountPlanID',$DiscountPlanDID, isset($InboundDiscountPlanID) ? $InboundDiscountPlanID : null,array('class'=>'form-control select2'))}}
            </div>
            <div class="col-sm-1">
                {{--@if(isset($account))
                    <button id="inbound_minutes_report" class="btn btn-sm btn-primary tooltip-primary" data-original-title="View Detail" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading...">
                        <i class="fa fa-eye"></i>
                    </button>
                @endif--}}
            </div>
            <label for="field-1" class="col-sm-1 control-label">Package</label>
            <div class="col-sm-2">
                {{Form::select('PackageDiscountPlanID',$DiscountPlanPACKAGE, isset($PackageDiscountPlanID) ? $PackageDiscountPlanID : null,array('class'=>'form-control select2'))}}
            </div>
            <div class="col-sm-1">
                {{--@if(isset($account))
                    <button id="package_minutes_report" class="btn btn-sm btn-primary tooltip-primary" data-original-title="View Detail" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading...">
                        <i class="fa fa-eye"></i>
                    </button>
                @endif--}}
            </div>
            <label for="field-1" class="col-sm-1 control-label">Termination</label>
            <div class="col-sm-2">
                {{Form::select('DiscountPlanID',$DiscountPlanVOICECALL, isset($DiscountPlanID) ? $DiscountPlanID : null,array('class'=>'form-control select2'))}}
            </div>
            <div class="col-sm-1">
                {{--@if(isset($account))
                    <button id="minutes_report" class="btn btn-sm btn-primary tooltip-primary" data-original-title="View Detail" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading...">
                        <i class="fa fa-eye"></i>
                    </button>
                @endif--}}
            </div>
        </div>
    </div>
</div>