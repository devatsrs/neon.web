<div class="modal fade" id="modal-map">
    <div class="modal-dialog" style="width: 60%;">
        <div class="modal-content">
            <form id="TestMail-form" method="post" action="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">@lang('routes.CUST_PANEL_PAGE_MONITOR_MODAL_TRAFFIC_BY_PREFIX_TITLE') <span></span></h4>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered datatable" id="map_destination_table">
                        <thead>
                        <tr>
                            <th width="20%">@lang('routes.CUST_PANEL_PAGE_MONITOR_MODAL_TRAFFIC_BY_PREFIX_TBL_PREFIX')</th>
                            <th width="20%">@lang('routes.CUST_PANEL_PAGE_MONITOR_MODAL_TRAFFIC_BY_PREFIX_TBL_NO_OF_CALLS')</th>
                            <th width="10%">@lang('routes.CUST_PANEL_PAGE_MONITOR_MODAL_TRAFFIC_BY_PREFIX_TBL_BILLED_DURATION_MIN')</th>
                            <th width="10%">@lang('routes.CUST_PANEL_PAGE_MONITOR_MODAL_TRAFFIC_BY_PREFIX_TBL_CHARGED_AMOUNT')</th>
                            <th width="10%">@lang('routes.CUST_PANEL_PAGE_MONITOR_MODAL_TRAFFIC_BY_PREFIX_TBL_ACD')</th>
                            <th width="10%">@lang('routes.CUST_PANEL_PAGE_MONITOR_MODAL_TRAFFIC_BY_PREFIX_TBL_ASR_IN_PERCENTAGE')</th>
                            @if((int)Session::get('customer') == 0)
                            <th width="10%">@lang('routes.CUST_PANEL_PAGE_MONITOR_MODAL_TRAFFIC_BY_PREFIX_TBL_MARGIN')</th>
                            <th width="10%">@lang('routes.CUST_PANEL_PAGE_MONITOR_MODAL_TRAFFIC_BY_PREFIX_TBL_MARGIN_IN_PERCENTAGE')</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                        <tr>

                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="modal-footer">
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        @lang("routes.BUTTON_CLOSE_CAPTION")
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


