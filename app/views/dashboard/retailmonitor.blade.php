<div class="tab-pane {{!in_array('AnalysisMonitor',$MonitorDashboardSetting)?'active':''}}" id="tab6" >
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default loading">

                <div class="panel-body with-table">
                    <table class="table table-bordered table-responsive most-dialled-number">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Number</th>
                            <th>Number of Times Dialled</th>
                            <th>Total Talk Time</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane" id="tab7" >
    <div class="row">

        <div class="col-sm-12">
        <div class="panel panel-default loading">

            <div class="panel-body with-table">
                <table class="table table-bordered table-responsive long-duration-call">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Number</th>
                        <th>Extension</th>
                        <th>Duration</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</div>
<div class="tab-pane" id="tab8" >
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default loading">
                <div class="panel-body with-table">
                    <table class="table table-bordered table-responsive most-expensive-call">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Number</th>
                            <th>Extension</th>
                            <th>Duration</th>
                            <th>Cost</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var retailmonitor = 1;
    @if(!in_array('AnalysisMonitor',$MonitorDashboardSetting))
    var hidecallmonitor =1;
    @endif
</script>