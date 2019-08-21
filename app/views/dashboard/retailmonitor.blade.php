<div class="tab-pane {{!in_array('DestinationMonitor',$MonitorDashboardSetting)?'active':''}}" id="mdn" >
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default loading">

                <div class="panel-body with-table">
                    <table class="table table-bordered table-responsive most-dialled-number">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_TAB_MOST_DIALLED_NUMBER_TBL_NUMBER")</th>
                            <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_TAB_MOST_DIALLED_NUMBER_TBL_NUMBER_OF_TIMES_DIALLED")</th>
                            <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_TAB_MOST_DIALLED_NUMBER_TBL_TOTAL_TALK_TIME_SEC")</th>
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
                        <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_TAB_LONGEST_DURATIONS_CALLS_TBL_NUMBER")</th>
                        <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_TAB_LONGEST_DURATIONS_CALLS_TBL_DURATION_SEC")</th>
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
                            <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_TAB_MOST_EXPENSIVE_CALLS_TBL_NUMBER")</th>
                            <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_TAB_MOST_EXPENSIVE_CALLS_TBL_COST")</th>
                            <th>@lang("routes.CUST_PANEL_PAGE_MONITOR_TAB_MOST_EXPENSIVE_CALLS_TBL_DURATION_SEC")</th>
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
    @if( (empty($MonitorDashboardSetting)) || in_array('DestinationMonitor',$MonitorDashboardSetting) ||  in_array('DestinationBreakMonitor',$MonitorDashboardSetting) ||  in_array('PrefixMonitor',$MonitorDashboardSetting)
       ||  in_array('ExtensionMonitor',$MonitorDashboardSetting) ||  in_array('TrunkMonitor',$MonitorDashboardSetting) ||  in_array('AccountMonitor',$MonitorDashboardSetting) ||  in_array('GatewayMonitor',$MonitorDashboardSetting))
    @else
        var hidecallmonitor =1;
    @endif
</script>