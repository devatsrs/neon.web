@extends($extends)
@section('content')
    <ol class="breadcrumb bc-3">
        <li> <a href="#"><i class="entypo-home"></i>@lang('routes.CUST_PANEL_PAGE_MOVEMENT_REPORT_TITLE')</a> </li>
    </ol>
    <h3>@lang('routes.CUST_PANEL_PAGE_MOVEMENT_REPORT_TITLE')</h3>
     <div id="table_filter" method="get" action="#" >
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    @lang('routes.CUST_PANEL_FILTER_TITLE')
                </div>
                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="field-1" class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_MOVEMENT_REPORT_FILTER_FIELD_START_DATE')</label>
					<div class="col-sm-2"> {{ Form::text('StartDate', $original_startdate, array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }} </div>
                  
                    <label for="field-5" class="col-sm-1 control-label">@lang('routes.CUST_PANEL_PAGE_MOVEMENT_REPORT_FILTER_FIELD_END_DATE')</label>
                    <div class="col-sm-2"> {{ Form::text('EndDate', $original_enddate, array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }} </div>
                    <input type="hidden" name="AccountID" value="{{$AccountID}}">
                </div>
                <p class="pull-right">
                    <button class="btn btn-primary btn-sm btn-icon icon-left" id="filter_submit" type="submit">
                        <i class="entypo-search"></i>
                        @lang('routes.BUTTON_SEARCH_CAPTION')
                    </button>
                </p>
            </div>
        </div>
    </div>

    <table id="table-list" class="table table-bordered datatable">
        <thead>
        <tr>
            <th width="20%">@lang('routes.CUST_PANEL_PAGE_MOVEMENT_REPORT_TBL_DATE')</th>
            <th width="20%">@lang('routes.CUST_PANEL_PAGE_MOVEMENT_REPORT_TBL_PAYMENTS')</th>
            <th width="20%">@lang('routes.CUST_PANEL_PAGE_MOVEMENT_REPORT_TBL_CONSUMPTION')</th>
            <th width="20%">@lang('routes.CUST_PANEL_PAGE_MOVEMENT_REPORT_TBL_TOTAL')</th>
            <th width="20%">@lang('routes.CUST_PANEL_PAGE_MOVEMENT_REPORT_TBL_BALANCE')</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
        <tr>
        </tr>
        </tfoot>
    </table>
    <script type="text/javascript">
        /**
         * JQuery Plugin for dataTable
         * */
        var TotalPayments = 0,TotalConsumption = 0,Total = 0,Balance = 0;

        jQuery(document).ready(function ($) {
            
            //public_vars.$body = $("body");
            var $search = {};
            var datagrid_url = baseurl + "/customer/daily_report_ajax_datagrid/type";
            $("#filter_submit").click(function(e) {
                e.preventDefault();

                $search.StartDate = $("#table_filter").find('[name="StartDate"]').val();
                $search.EndDate = $("#table_filter").find('[name="EndDate"]').val();
                $search.AccountID = $("#table_filter").find('[name="AccountID"]').val();
                data_table = $("#table-list").dataTable({
                    "oLanguage": {
                        "sUrl": baseurl + "/translate/datatable_Label"
                    },
                    "bDestroy": true,
                    "bProcessing":true,
                    "bServerSide": true,
                    "sAjaxSource": datagrid_url,
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-12'l>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[0, 'desc']],
                    "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "StartDate", "value": $search.StartDate},
                                {"name": "EndDate", "value": $search.EndDate},
                                {"name": "AccountID", "value": $search.AccountID}

                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "StartDate", "value": $search.StartDate},
                                {"name": "EndDate", "value": $search.EndDate},
                                {"name": "AccountID", "value": $search.AccountID},
                                {"name": "Export", "value": 1}
                        );

                    },
                    "aoColumns": [
                        {  "bSortable": false },  // 0 Date
                        {
                            "bSortable": false,
                            mRender: function (id, type, full) { return "<span class='leftsideview'>"+full[1]+"</span>"}
                        },  // 0 Payments
                        {
                            "bSortable": false,
                            mRender: function (id, type, full) { return "<span class='leftsideview'>"+full[2]+"</span>"}
                        },  // 0 Consumption
                        {
                            "bSortable": false,
                            mRender: function (id, type, full) { return "<span class='leftsideview'>"+full[3]+"</span>"}
                        },  // 0 Total
                        {
                            "bSortable": false,
                            mRender: function (id, type, full) { return "<span class='leftsideview'>"+full[4]+"</span>"}
                        }  // 0 Balance
                    ],
                    "oTableTools": {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "@lang('routes.BUTTON_EXPORT_EXCEL_CAPTION')",
                                "sUrl": baseurl + "/customer/daily_report_ajax_datagrid/xlsx", //baseurl + "/generate_xls.php",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "@lang('routes.BUTTON_EXPORT_CSV_CAPTION')",
                                "sUrl": baseurl + "/customer/daily_report_ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    },
                    "fnDrawCallback": function() {
                        get_total_grand(); //get result total
                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                    },


                });
            });
            $('#filter_submit').trigger('click');

            function get_total_grand() {
                $.ajax({
                    url: baseurl + "/customer/daily_report_ajax_datagrid_total",
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        "StartDate": $("[name='StartDate']").val(),
                        "EndDate":$("[name='EndDate']").val(),
                        "AccountID":$("[name='AccountID']").val()
                    },
                    success: function (response1) {
                        //console.log("sum of result"+response1);
                        if (response1.Balance != null) {
                            $('.result_row').remove();
                            $('.result_row').hide();
                            $('#table-list tbody').append('<tr class="result_row"><td><strong>{{cus_lang("TABLE_TOTAL")}}</strong></td><td class="leftsideview"><strong>' + response1.TotalPayment + '</strong></td><td class="leftsideview"><strong>' + response1.TotalCharge + '</strong></td><td class="leftsideview"><strong>' + response1.Total + '</strong></td><td class="leftsideview"><strong>' + response1.Balance + '</strong></td></tr>');
                        }
                    }
                });
            }

        });
    </script>
@stop