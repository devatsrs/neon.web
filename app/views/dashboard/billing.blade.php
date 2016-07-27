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
@if(CompanySetting::getKeyVal('PincodeWidget') == 1)
<div class="row">
    <div class="col-sm-12">
        <div class="pin_expsense panel panel-primary panel-table">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>Top Pincodes</h3>
                </div>

                <div class="panel-options">
                    <form id="filter-form" name="filter-form" style="display: inline" >
                        {{ Form::select('PinExt', array('pincode'=>'By Pincode','extension'=>'By Extension'), 1, array('id'=>'PinExt','class'=>'select_gray')) }}
                        {{ Form::select('Type', array(1=>'By Cost',2=>'By Duration'), 1, array('id'=>'Type','class'=>'select_gray')) }}
                        {{ Form::select('Limit', array(5=>5,10=>10,20=>20), 5, array('id'=>'pin_size','class'=>'select_gray')) }}
                    </form>

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
        <div class="pin_expsense_report panel panel-primary" style="position: static;">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>Pincodes Detail Report</h3>
                </div>

                <div class="panel-options">
                    <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a>
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
@endif
<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-primary panel-table">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>Missing Gateway Accounts ()</h3>
                    
                </div>

                <div class="panel-options">
                    {{ Form::select('CompanyGatewayID', $company_gateway, 1, array('id'=>'company_gateway','class'=>'select_gray')) }}
                    <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a>
                    <a data-rel="close" href="#"><i class="entypo-cancel"></i></a>
                </div>
            </div>
            <div class="panel-body" style="max-height: 450px; overflow-y: auto; overflow-x: hidden;">
                <table id="missingAccounts" class="table table-responsive">
                    <thead>
                    <tr>
                        <th>Account Name</th>
                        <th>Gateway</th>
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
        loadingUnload('#invoice_expense_bar_chart',1);
    $.get( get_url, data , function(response){
            $(".search.btn").button('reset');
        loadingUnload('#invoice_expense_bar_chart',0);
            $(".panel.invoice_expsense #invoice_expense_bar_chart").html(response);
    }, "html" );

    var get_url = baseurl + "/billing_dashboard/invoice_expense_total";
    loadingUnload('#invoice_expense_total',1);
    $.get( get_url, data , function(response){
        loadingUnload('#invoice_expense_total',0);
            $(".search.btn").button('reset');
            $("#invoice_expense_total").html(response);
    }, "html" );
    pin_report();
    missingAccounts();

}


function pin_title(){
    if($("#filter-form [name='PinExt']").val() == 'pincode'){
        $('.pin_expsense').find('h3').html('Top Pincodes');
        $('.pin_expsense_report').find('h3').html('Top Pincodes Detail Report');
    }
    if($("#filter-form [name='PinExt']").val() == 'extension'){
        $('.pin_expsense').find('h3').html('Top Extensions ');
        $('.pin_expsense_report').find('h3').html('Top Extensions Detail Report');

    }
}
function loadingUnload(table,bit){
    var panel = jQuery(table).closest('.panel');
    if(bit==1){
        blockUI(panel);
        panel.addClass('reloading');
    }else{
        unblockUI(panel);
        panel.removeClass('reloading');
    }
}

function pin_report() {
    @if(CompanySetting::getKeyVal('PincodeWidget') == 1)
    $("#pin_grid_main").addClass('hidden');
    loadingUnload('#pin_expense_bar_chart', 1);
    data = $('#billing_filter').serialize() + '&' + $('#filter-form').serialize() ;
    pin_title();
    var get_url = baseurl + "/billing_dashboard/ajax_top_pincode";
    $.get(get_url, data, function (response) {
        loadingUnload('#pin_expense_bar_chart', 0);
        $(".save.btn").button('reset');
        $("#pin_expense_bar_chart").html(response);
    }, "html");
    @endif
}
$('body').on('click', '.panel > .panel-heading > .panel-options > a[data-rel="reload"]', function(e){
    e.preventDefault();
    var id = $(this).parents('.panel-primary').find('table').attr('id');
    if(id=='missingAccounts'){
        missingAccounts();
    }
});
$(function() {
     reload_invoice_expense();
    $("#filter-pin").hide();
    $('#billing_filter').submit(function(e){
        e.preventDefault();
        reload_invoice_expense();
        return false;
     });
    $('#filter-form').submit(function(e){
        e.preventDefault();
        pin_report();
        return false;
    });
    $("#pin_fiter").click(function(){
        $("#filter-pin").slideToggle();
    });
    $("#pin_size").change(function(){
        pin_report();
    });
    $("#Type").change(function(){
        pin_report();
    });
    $("#PinExt").change(function(){
        pin_report();
    })
    $("#company_gateway").change(function(){
        missingAccounts();
    });
});
function missingAccounts(){
    var table = $('#missingAccounts');
    loadingUnload(table,1);
    var url = baseurl+'/dashboard/ajax_get_missing_accounts?CompanyGatewayID='+$("#company_gateway").val();
    $.ajax({
        url: url,  //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            var accounts = response.missingAccounts;
            html = '';
            table.parents('.panel-primary').find('.panel-title h3').html('Missing Gateway Accounts ('+accounts.length+')');
            table.find('tbody').html('');
            if(accounts.length > 0){
                for (i = 0; i < accounts.length; i++) {
                    html +='<tr>';
                    html +='      <td>'+accounts[i]["AccountName"]+'</td>';
                    html +='      <td>'+accounts[i]["Title"]+'</td>';
                    html +='</tr>';
                }
            }else{
                html = '<td colspan="3">No Records found.</td>';
            }
            table.find('tbody').html(html);
            loadingUnload(table,0);
        },
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
    });
}
    function dataGrid(Pincode,Startdate,Enddate,PinExt,CurrencyID){
        $("#pin_grid_main").removeClass('hidden');
        if(PinExt == 'pincode'){
            $('.pin_expsense_report').find('h3').html('Pincode '+Pincode+' Detail Report');
        }
        if(PinExt == 'extension'){
            $('.pin_expsense_report').find('h3').html('Extension'+Pincode+' Detail Report');

        }
        data_table = $("#pin_grid").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/billing_dashboard/ajaxgrid_top_pincode/type",
            "fnServerParams": function (aoData) {
                aoData.push(
                        {"name": "Pincode", "value": Pincode},
                        {"name": "Startdate", "value": Startdate},
                        {"name": "Enddate", "value": Enddate},
                        {"name": "PinExt", "value": PinExt},
                        {"name": "CurrencyID", "value": CurrencyID}
                );

                data_table_extra_params.length = 0;
                data_table_extra_params.push(
                        {"name": "Pincode", "value": Pincode},
                        {"name": "Startdate", "value": Startdate},
                        {"name": "Enddate", "value": Enddate},
                        {"name": "PinExt", "value": PinExt},
                        {"name": "CurrencyID", "value": CurrencyID},
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
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/billing_dashboard/ajaxgrid_top_pincode/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/billing_dashboard/ajaxgrid_top_pincode/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
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