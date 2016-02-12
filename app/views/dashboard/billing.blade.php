@extends('layout.main')
@section('content')
<br />
<div class="row">
    <div class="col-sm-12">
            <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="post" id="billing_filter">
                        <div data-collapsed="0" class="panel panel-primary">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    Filter
                                </div>
                                <div class="panel-options">
                                    <a data-rel="collapse" href="#">
                                        <i class="entypo-down-open"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">

                                    @if(User::is_admin())
                                     <label for="field-1" class="col-sm-1 control-label">Currency</label>
                                    <div class="col-sm-2">
                                    {{Form::select('CurrencyID',Currency::getCurrencyDropdownIDList(),$DefaultCurrencyID,array("class"=>"select2"))}}
                                    </div>
                                    @endif
                                    <label class="col-sm-1 control-label" for="Startdate">Start date</label>
                                    <div class="col-sm-2">
                                        <input type="text" name="Startdate" class="form-control datepicker"   data-date-format="yyyy-mm-dd" value="{{$original_startdate}}" data-enddate="{{date('Y-m-d')}}" />
                                    </div>
                                    <label class="col-sm-1 control-label" for="field-1">End Date</label>
                                    <div class="col-sm-2">
                                        <input type="text" name="Enddate" class="form-control datepicker"   data-date-format="yyyy-mm-dd" value="{{$original_enddate}}" data-enddate="{{date('Y-m-d', strtotime('+1 day') )}}" />
                                    </div>

                                </div>
                                <p style="text-align: right;">
                                    <button class="btn search btn-primary btn-sm btn-icon icon-left" type="submit" data-loading-text="Loading...">
                                        <i class="entypo-search"></i>Search
                                    </button>
                                </p>
                            </div>
                        </div>
                    </form>
    </div>

</div>
<div class="row">
    <div class="col-sm-3">
                <div class="invoice_expsense panel panel-primary panel-table">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h3>Total Outstanding</h3>
                            <span>Total Outstanding</span>
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                            <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div id="invoice_expense_total"></div>
                    </div>
                </div>
    </div>
    <div class="col-sm-9">
            <div class="invoice_expsense panel panel-primary panel-table">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3>Invoices & Expenses</h3>
                        <span>Invoices & Expenses</span>
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                        <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                     <div id="invoice_expense_bar_chart"></div>
                </div>
            </div>
    </div>

</div>
<div class="row">
    <div class="col-sm-12">
        <div class="pin_expsense panel panel-primary panel-table">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>Top Pincode</h3>
                    <div class="pull-right">
                        <button class="btn btn-default btn-xs btn-filter" id="pin_fiter"><span class="glyphicon glyphicon-filter"></span> Filter</button>
                    </div>
                </div>
                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                    <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <div id="pin_expense_bar_chart"></div>
            </div>
        </div>
    </div>
</div>
<div class="row hidden" id="pin_grid_main">
    <div class="col-sm-12">
        <div class="panel panel-primary" style="position: static;">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>Pincode</h3>
                    <span>Pincode Detail Report</span>
                </div>

                <div class="panel-options">
                    <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a>
                    <a data-rel="close" href="#"><i class="entypo-cancel"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-bordered datatable" id="pin_grid">
                    <thead>
                    <tr>
                        <th width="30%">Destination Number</th>
                        <th width="30%">Total Cost</th>
                        <th width="30%">Number of Times Dialed</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
function reload_invoice_expense(){

     var get_url = baseurl + "/billing_dashboard/invoice_expense_chart";
    data = $('#billing_filter').serialize()+'&'+$('#filter-form').serialize();

    $.get( get_url, data , function(response){
            $(".search.btn").button('reset');
            $(".panel.invoice_expsense #invoice_expense_bar_chart").html(response);
    }, "html" );

    var get_url = baseurl + "/billing_dashboard/invoice_expense_total";
    $.get( get_url, data , function(response){
            $(".search.btn").button('reset');
            $("#invoice_expense_total").html(response);
    }, "html" );
    $("#pin_grid_main").addClass('hidden');
    var get_url = baseurl + "/billing_dashboard/ajax_top_pincode";
    $.get( get_url, data , function(response){
        $(".search.btn").button('reset');
        $("#pin_expense_bar_chart").html(response);
    }, "html" );


}

$(function() {
     reload_invoice_expense();

    $('#billing_filter').submit(function(e){
        e.preventDefault();
        reload_invoice_expense();
        return false;
     });
    $('#filter-form').submit(function(e){
        e.preventDefault();
        var get_url = baseurl + "/billing_dashboard/ajax_top_pincode";
        data = $('#billing_filter').serialize()+'&'+$('#filter-form').serialize();
        $.get( get_url, data , function(response){
            $("#filter-pin").modal('hide');
            $("#pin_expense_bar_chart").html(response);
        }, "html" );
        return false;
    });
    $("#pin_fiter").click(function(){
        $("#filter-pin").modal('show');
    });
})
    function dataGrid(Pincode,Startdate,Enddate){
        $("#pin_grid_main").removeClass('hidden');
        data_table = $("#pin_grid").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/billing_dashboard/ajaxgrid_top_pincode",
            "fnServerParams": function (aoData) {
                aoData.push(
                        {"name": "Pincode", "value": Pincode},
                        {"name": "Startdate", "value": Startdate},
                        {"name": "Enddate", "value": Enddate}
                );

                data_table_extra_params.length = 0;
                data_table_extra_params.push(
                        {"name": "Pincode", "value": Pincode},
                        {"name": "Startdate", "value": Startdate},
                        {"name": "Enddate", "value": Enddate},
                        {"name":"Export","value":1}
                );

            },
            "iDisplayLength": '10',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
            "aoColumns": [
                {"bSortable": true},  // 1 Destination Number
                {"bSortable": true},  // 2 Total Cost
                {"bSortable": true}  // 3 Number of Times Dialed
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "Export Data",
                        "sUrl": baseurl + "/billing_dashboard/ajaxgrid_top_pincode", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection"
                    }
                ]
            },
            "fnDrawCallback": function () {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });
            }

        });
    }
</script>
@stop
@section('footer_ext')
@parent
<div class="modal fade" id="filter-pin">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="filter-form" method="post" class="form-horizontal form-groups-bordered">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                    <h4 class="modal-title">
                        Filter Top Pincode Options
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="field-5" class="col-sm-4 control-label">Limit</label>
                        <div class="col-sm-4">
                            {{ Form::select('Limit', array(5=>5,10=>10,15=>15,20=>20,25=>25), 5, array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Limit")) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-5" class="col-sm-4 control-label">Pincode By Cost or Duration</label>
                        <div class="col-sm-4">
                            {{ Form::select('Type', array(1=>'By Cost',2=>'By Duration'), 1, array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Type")) }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button  type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                        <i class="fa fa-filter"></i>
                        Filter
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop