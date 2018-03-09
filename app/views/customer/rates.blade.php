@extends('layout.customer.main')
@section('content')
    <ol class="breadcrumb bc-3">
        <li> <a href="#"><i class="entypo-home"></i>@lang("routes.CUST_PANEL_PAGE_RATES_TITLE")</a> </li>
    </ol>
    <h3>@lang("routes.CUST_PANEL_PAGE_RATES_TITLE")</h3>
    <div id="table_filter" method="get" action="#" >
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    @lang("routes.CUST_PANEL_FILTER_TITLE")
                </div>
                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="field-1" class="col-sm-1 control-label">@lang("routes.CUST_PANEL_PAGE_RATES_FILTER_FIELD_PREFIX")</label>
                    <div class="col-sm-2"> {{ Form::text('Prefix', '', array("class"=>"form-control")) }} </div>
                    <label for="field-1" class="col-sm-1 control-label">@lang("routes.CUST_PANEL_PAGE_RATES_FILTER_FIELD_DESCRIPTION")</label>
                    <div class="col-sm-2"> {{ Form::text('Description', '', array("class"=>"form-control")) }} </div>
                </div>
                <p class="pull-right">
                    <button class="btn btn-primary btn-sm btn-icon icon-left" id="filter_submit" type="submit">
                        <i class="entypo-search"></i>
                        @lang("routes.BUTTON_SEARCH_CAPTION")
                    </button>
                </p>
            </div>
        </div>
    </div>

    <table id="table-list" class="table table-bordered datatable">
        <thead>
        <tr>
            <th width="5%"></th>
            <th width="15%">@lang("routes.CUST_PANEL_PAGE_RATES_TBL_PREFIX")</th>
            <th width="20%">@lang("routes.CUST_PANEL_PAGE_RATES_TBL_NAME")</th>
            <th width="10%">@lang("routes.CUST_PANEL_PAGE_RATES_TBL_INTERVAL_1")</th>
            <th width="10%">@lang("routes.CUST_PANEL_PAGE_RATES_TBL_INTERVAL_N")</th>
            <th width="5%"></th>
            <th width="10%">@lang("routes.CUST_PANEL_PAGE_RATES_TBL_CONNECTION_FEE")</th>
            <th width="15%">@lang("routes.CUST_PANEL_PAGE_RATES_TBL_RATE")</th>
            <th width="15%">@lang("routes.CUST_PANEL_PAGE_RATES_TBL_EFFECTIVE_DATE")</th>
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
            var datagrid_url = baseurl + "/customer/rates_grid/type";

            $("#filter_submit").click(function(e) {
                e.preventDefault();

                $search.Prefix = $("#table_filter").find('[name="Prefix"]').val();
                $search.Description = $("#table_filter").find('[name="Description"]').val();

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
                    "aaSorting": [[2, 'asc']],
                    "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "Prefix", "value": $search.Prefix},
                                {"name": "Description", "value": $search.Description}


                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "Prefix", "value": $search.Prefix},
                                {"name": "Description", "value": $search.Description},
                                {"name": "Export", "value": 1}
                        );

                    },
                    "aoColumns": [
                        {"bVisible": false
                        }, //0Checkbox
                        {}, //1 Code
                        {}, //2 Description
                        {}, //3 Interval1
                        {}, //4 IntervalN
                        {"bVisible": false}, //5 RoutinePlan
                        {}, //6 ConnectionFee
                        {}, //7 Rate
                        {}  //8 Effective Date
                    ],
                    "oTableTools": {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "@lang('routes.BUTTON_EXPORT_EXCEL_CAPTION')",
                                "sUrl": baseurl + "/customer/rates_grid/xlsx", //baseurl + "/generate_xls.php",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "@lang('routes.BUTTON_EXPORT_CSV_CAPTION')",
                                "sUrl": baseurl + "/customer/rates_grid/csv", //baseurl + "/generate_csv.php",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    },
                    "fnDrawCallback": function() {
                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                    },


                });
        });
            $('#filter_submit').trigger('click');

        });
    </script>
@stop