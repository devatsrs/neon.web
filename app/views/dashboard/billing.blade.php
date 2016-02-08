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
<script type="text/javascript">
function reload_invoice_expense(){

     var get_url = baseurl + "/billing_dashboard/invoice_expense_chart";
    CurrencyID = $("select[name=CurrencyID]").val();
    data = {"CurrencyID":CurrencyID};

    $.get( get_url, data , function(response){
            $(".search.btn").button('reset');
            $(".panel.invoice_expsense #invoice_expense_bar_chart").html(response);
    }, "html" );

    var get_url = baseurl + "/billing_dashboard/invoice_expense_total";
    $.get( get_url, data , function(response){
            $(".search.btn").button('reset');
            $("#invoice_expense_total").html(response);
    }, "html" );


}

$(function() {
     reload_invoice_expense();

    $('#billing_filter').submit(function(e){
        e.preventDefault();
        reload_invoice_expense();
        return false;
     });
})
</script>
@stop