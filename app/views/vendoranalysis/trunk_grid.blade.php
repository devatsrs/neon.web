<div class="row">
<div class="col-md-12">
<table class="table table-bordered datatable" id="trunk_table">
    <thead>
    <tr>
        <th width="20%">@lang("routes.CUST_PANEL_PAGE_ANALYSIS_TAB_TRUNK_TBL_TRUNK")</th>
        <th width="20%">@lang("routes.CUST_PANEL_PAGE_ANALYSIS_TAB_TRUNK_TBL_NO_OF_CALLS")</th>
        <th width="10%">@lang("routes.CUST_PANEL_PAGE_ANALYSIS_TAB_TRUNK_TBL_BILLED_DURATION_MIN")</th>
        <th width="10%">@lang("routes.CUST_PANEL_PAGE_ANALYSIS_TAB_TRUNK_TBL_CHARGED_AMOUNT")</th>
        <th width="10%">@lang("routes.CUST_PANEL_PAGE_ANALYSIS_TAB_TRUNK_TBL_ACD")</th>
        <th width="10%">@lang("routes.CUST_PANEL_PAGE_ANALYSIS_TAB_TRUNK_TBL_ASR")</th>
        @if((int)Session::get('customer') == 0)
        <th width="10%">@lang("routes.CUST_PANEL_PAGE_ANALYSIS_TAB_TRUNK_TBL_MARGIN")</th>
        <th width="10%">@lang("routes.CUST_PANEL_PAGE_ANALYSIS_TAB_TRUNK_TBL_MARGIN") (%)</th>
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
</div>