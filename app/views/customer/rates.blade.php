@extends('layout.customer.main')
@section('content')
    <ol class="breadcrumb bc-3">
        <li> <a href="#"><i class="entypo-home"></i>Rates</a> </li>
    </ol>
    <h3>Rates</h3>
    <div id="table_filter" method="get" action="#" >
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Filter
                </div>
                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="field-1" class="col-sm-1 control-label">Prefix</label>
                    <div class="col-sm-2"> {{ Form::text('Prefix', '', array("class"=>"form-control")) }} </div>
                    <label for="field-1" class="col-sm-1 control-label">Description</label>
                    <div class="col-sm-2"> {{ Form::text('Description', '', array("class"=>"form-control")) }} </div>
                </div>
                <p style="text-align: right;">
                    <button class="btn btn-primary btn-sm btn-icon icon-left" id="filter_submit" type="submit">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </p>
            </div>
        </div>
    </div>

    <table id="table-list" class="table table-bordered datatable">
        <thead>
        <tr>
            <th width="15%">Prefix</th>
            <th width="20%">Name</th>
            <th width="10%">Interval 1</th>
            <th width="10%">Interval N</th>
            <th width="10%">Connection Fee</th>
            <th width="15%">Rate</th>
            <th width="15%">Effective Date</th>
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
                    "bDestroy": true,
                    "bProcessing":true,
                    "bServerSide": true,
                    "sAjaxSource": datagrid_url,
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-12'l>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[1, 'asc']],
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
                        {  "bSortable": true },  // 0 Date
                        {  "bSortable": true },  // 0 Payments
                        {  "bSortable": true },  // 0 Consumption
                        {  "bSortable": true },  // 0 Total
                        {  "bSortable": true },  // 0 Total
                        {  "bSortable": true }, // 0 Balance
                        {  "bSortable": true }  // 0 Balance
                    ],
                    "oTableTools": {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/customer/rates_grid/xlsx", //baseurl + "/generate_xls.php",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
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