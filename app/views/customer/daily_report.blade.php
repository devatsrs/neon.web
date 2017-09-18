@extends('layout.customer.main')
@section('content')
    <ol class="breadcrumb bc-3">
        <li> <a href="#"><i class="entypo-home"></i>Movement Report</a> </li>
    </ol>
    <h3>Movement Report</h3>
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
                    <label for="field-1" class="col-sm-1 control-label">Start Date</label>
					<div class="col-sm-2"> {{ Form::text('StartDate', $original_startdate, array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }} </div>
                  
                    <label for="field-5" class="col-sm-1 control-label">End Date</label>
                    <div class="col-sm-2"> {{ Form::text('EndDate', $original_enddate, array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }} </div>
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
            <th width="20%">Date</th>
            <th width="20%">Payments</th>
            <th width="20%">Consumption</th>
            <th width="20%">Total</th>
            <th width="20%">Balance</th>
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
            var datagrid_url = baseurl + "/customer/daily_report/ajax_datagrid/type";
            $("#filter_submit").click(function(e) {
                e.preventDefault();

                $search.StartDate = $("#table_filter").find('[name="StartDate"]').val();
                $search.EndDate = $("#table_filter").find('[name="EndDate"]').val();
                data_table = $("#table-list").dataTable({
                    "bDestroy": true,
                    "bProcessing":true,
                    "bServerSide": true,
                    "sAjaxSource": datagrid_url,
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-12'l>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[0, 'asc']],
                    "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "StartDate", "value": $search.StartDate},
                                {"name": "EndDate", "value": $search.EndDate}

                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "StartDate", "value": $search.StartDate},
                                {"name": "EndDate", "value": $search.EndDate},
                                {"name": "Export", "value": 1}
                        );

                    },
                    "aoColumns": [
                        {  "bSortable": true },  // 0 Date
                        {  "bSortable": true },  // 0 Payments
                        {  "bSortable": true },  // 0 Consumption
                        {  "bSortable": true },  // 0 Total
                        {  "bSortable": true }  // 0 Balance
                    ],
                    "oTableTools": {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/customer/daily_report/ajax_datagrid/xlsx", //baseurl + "/generate_xls.php",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/customer/daily_report/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
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
                    url: baseurl + "/customer/daily_report/ajax_datagrid_total",
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        "StartDate": $("[name='StartDate']").val(),
                        "EndDate":$("[name='EndDate']").val()
                    },
                    success: function (response1) {
                        //console.log("sum of result"+response1);
                        if (response1.Balance != null) {
                            $('.result_row').remove();
                            $('.result_row').hide();
                            $('#table-list tbody').append('<tr class="result_row"><td><strong>Total</strong></td><td><strong>' + response1.TotalPayment + '</strong></td><td><strong>' + response1.TotalCharge + '</strong></td><td><strong>' + response1.Total + '</strong></td><td><strong>' + response1.Balance + '</strong></td></tr>');
                        }
                    }
                });
            }

        });
    </script>
@stop