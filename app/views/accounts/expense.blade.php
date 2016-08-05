@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('accounts')}}">Accounts</a>
    </li>
    <li>
        <a><span>{{customer_dropbox($id)}}</span></a>
    </li>
    <li class="active">
        <strong> Account Activity</strong>
    </li>
</ol>
@include('includes.errors')
@include('includes.success')
<p style="text-align: right;">
    <a class="btn btn-primary print btn-sm btn-icon icon-left" type="button" href="{{URL::to('accounts/activity_pdf_download/'.$id)}}">
        <i class="entypo-print"></i>
        Print
    </a>
</p>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-primary panel-table loading">
            <div class="panel-body">
                <div id="account_expense_bar_chart">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="panel loading panel-default dataTables_wrapper" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->

            <!-- panel body -->
            <div class="panel-body">

                <table class="table table-bordered datatable" id="expense_customer_table">
                </table>
            </div>
        </div>

    </div>
    <div class="col-sm-12">
        <div class="panel loading panel-default dataTables_wrapper" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->

            <!-- panel body -->
            <div class="panel-body">

                <table class="table table-bordered datatable" id="expense_vendor_table">
                </table>
            </div>
        </div>

    </div>

</div>
<div class="row">
    <div class="col-sm-3">
        <div class="panel loading panel-default dataTables_wrapper" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
            <!-- panel body -->
            <div class="panel-body">
                <table class="table table-bordered datatable" id="expense_year_table">
                    <thead>
                    <tr>
                        <th width="30%">Year</th>
                        <th width="30%">Customer</th>
                        <th width="40%">Vendor</th>
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

    </div>
</div>
<form class="hidden" id="hidden_form">
    <input type="hidden" name="AccountID" value="{{$id}}">
</form>
<script src="{{ URL::asset('assets/js/reports.js') }}"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script type="text/javascript">
    var CurrencySymbol = '{{$CurrencySymbol}}';
    $(function() {
        Highcharts.theme = {
            colors: ['#3366cc', '#ff9900','#dc3912', '#109618', '#66aa00', '#dd4477','#0099c6', '#990099', '#143DFF']
        };
        // Apply the theme
        Highcharts.setOptions(Highcharts.theme);
        account_expense_chart($('#hidden_form').serialize());
    });
</script>
@stop