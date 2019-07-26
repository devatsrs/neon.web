
<!-- Account RateTables start -->
<div class="panel panel-primary ratetable-section-hide" data-collapsed="0">

    <div class="panel-heading">
        <div class="panel-title">
            RateTable
        </div>

        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>

    <div class="panel-body">
        <div class="form-group">
            <label for="field-1" class="col-md-1 control-label">Access</label>
            <div class="col-md-3">
                {{ Form::select('AccountAccessRateTableID', $rate_table , isset($AccountAccessRateTableID) ? $AccountAccessRateTableID : '', array("class"=>"select2")) }}
            </div>

            <label class="col-md-1 control-label">Package</label>
            <div class="col-md-3">
                {{ Form::select('AccountPackageRateTableID', $package_rate_table , isset($AccountPackageRateTableID) ? $AccountPackageRateTableID : '', array("class"=>"select2")) }}
            </div>

            <label class="col-md-1 control-label">Termination</label>
            <div class="col-md-3">
                {{ Form::select('AccountTerminationRateTableID', $termination_rate_table , isset($AccountTerminationRateTableID) ? $AccountTerminationRateTableID : '', array("class"=>"select2")) }}
            </div>

        </div>
    </div>
</div>
<!-- Account RateTables end -->

