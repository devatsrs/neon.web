<div class="tab-pane {{!in_array('AnalysisMonitor',$MonitorDashboardSetting)?'active':''}}" id="mdn" >
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default loading">

                <div class="panel-body with-table">
                    <table class="table table-bordered table-responsive most-dialled-number">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_MOST_DIALLED_NUMBER_TBL_COL2")</th>
                            <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_MOST_DIALLED_NUMBER_TBL_COL3")</th>
                            <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_MOST_DIALLED_NUMBER_TBL_COL4")</th>
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

<div class="tab-pane" id="ldc" >
    <div class="row">

        <div class="col-sm-12">
        <div class="panel panel-default loading">

            <div class="panel-body with-table">
                <table class="table table-bordered table-responsive long-duration-call">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_LONGEST_DURATIONS_CALLS_TBL_COL2")</th>
                        <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_LONGEST_DURATIONS_CALLS_TBL_COL3")</th>
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
<div class="tab-pane" id="mec" >
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default loading">
                <div class="panel-body with-table">
                    <table class="table table-bordered table-responsive most-expensive-call">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_MOST_EXPENSIVE_CALLS_TBL_COL2")</th>
                            <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_MOST_EXPENSIVE_CALLS_TBL_COL3")</th>
                            <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_MOST_EXPENSIVE_CALLS_TBL_COL4")</th>
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