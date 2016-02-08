@extends('layout.customer.main')
@section('content')
<br />
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
<input name="CurrencyID" type="hidden" value="{{$account->CurrencyId}}">
<script type="text/javascript">
function reload_invoice_expense(){

     var get_url = baseurl + "/customer/invoice_expense_chart";
    CurrencyID = $("[name=CurrencyID]").val();
    data = {"CurrencyID":CurrencyID};

    $.get( get_url, data , function(response){
            $(".search.btn").button('reset');
            $(".panel.invoice_expsense #invoice_expense_bar_chart").html(response);
    }, "html" );

    var get_url = baseurl + "/customer/invoice_expense_total";
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