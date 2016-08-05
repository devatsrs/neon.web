@extends('layout.main')
@section('content')
<br />
{{--<link rel="stylesheet" type="text/css" href="assets/js/daterangepicker/daterangepicker-bs3.css" />--}}
<div class="row">
    <ul class="nav nav-tabs">
        <li ><a href="{{ URL::to('/analysis') }}">Customer</a></li>
        <li class="active"><a href="#">Vendor</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="customer" >
            <div class="col-md-12">
                <form novalidate="novalidate" class="form-horizontal form-groups-bordered filter validate" method="post" id="vendor_analysis">
                    <div data-collapsed="0" class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Filter
                            </div>
                            <div class="panel-options">
                                <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-sm-1 control-label" for="field-1">Start Date</label>
                                <div class="col-sm-2">
                                    <input type="text" name="StartDate"  class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d')}}"/>
                                </div>
                                <label class="col-sm-1 control-label" for="field-1">End Date</label>
                                <div class="col-sm-2">
                                    <input type="text" name="EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d' )}}" />
                                </div>
                                <label class="col-sm-1 control-label" for="field-1">Gateway</label>
                                <div class="col-sm-2">
                                    {{ Form::select('GatewayID',$gateway,'', array("class"=>"select2")) }}
                                </div>
                                <label class="col-sm-1 control-label" for="field-1">Country</label>
                                <div class="col-sm-2">
                                    {{ Form::select('CountryID',$Country,'', array("class"=>"select2")) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-1 control-label" for="field-1">Prefix</label>
                                <div class="col-sm-2">
                                    <input type="text" name="Prefix"  class="form-control"/>
                                </div>
                                <label class="col-sm-1 control-label" for="field-1">Trunk</label>
                                <div class="col-sm-2">
                                    {{ Form::select('TrunkID',$trunks,'', array("class"=>"select2")) }}
                                </div>
                            @if(Session::get('customer') == 1)
                                <input type="hidden" name="CurrencyID" value="{{$CurrencyID}}">
                                <input type="hidden" name="AccountID" value="{{Customer::get_accountID()}}">
                            @else
                                    <label class="col-sm-1 control-label" for="field-1">Account</label>
                                    <div class="col-sm-2">
                                        {{ Form::select('AccountID',$account,'', array("class"=>"select2")) }}
                                    </div>
                                    <label class="col-sm-1 control-label" for="field-1">Currency</label>
                                    <div class="col-sm-2">
                                        {{ Form::select('CurrencyID',$currency,$DefaultCurrencyID,array("class"=>"select2")) }}
                                    </div>
                            @endif
                            <input type="hidden" name="UserID" value="{{$UserID}}">
                            <input type="hidden" name="Admin" value="{{$isAdmin}}">
                            <input type="hidden" name="chart_type" value="destination">
                            <input type="hidden" name="Prefix" value="">
                            <input type="hidden" name="TrunkID" value="0">
                            </div>
                            <p style="text-align: right;">
                                <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit">
                                    <i class="entypo-search"></i>
                                    Search
                                </button>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="clear"></div>
            <ul class="nav nav-tabs">
                <li class="active"><a href="#destination" data-toggle="tab">Destination</a></li>
                <li ><a href="#prefix" data-toggle="tab">Prefix</a></li>
                <li ><a href="#trunk" data-toggle="tab">Trunk</a></li>
                <li ><a href="#gateway" data-toggle="tab">Gateway</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="destination" >
                    @include('vendoranalysis.destination')
                    @include('vendoranalysis.destination_grid')
                </div>
                <div class="tab-pane" id="prefix" >
                    @include('vendoranalysis.prefix')
                    @include('vendoranalysis.prefix_grid')
                </div>
                <div class="tab-pane" id="trunk" >
                    @include('vendoranalysis.trunk')
                    @include('vendoranalysis.trunk_grid')
                </div>
                <div class="tab-pane" id="gateway" >
                    @include('vendoranalysis.gateway')
                    @include('vendoranalysis.gateway_grid')
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ URL::asset('assets/js/reports_vendor.js') }}"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script>
        var $searchFilter = {};
        var toFixed = '{{CompanySetting::getKeyVal('RoundChargesAmount')=='Invalid Key'?2:CompanySetting::getKeyVal('RoundChargesAmount')}}';
        var table_name = '#destination_table';
        var chart_type = '#destination';
        jQuery(document).ready(function ($) {

            $(".nav-tabs li a").click(function(){
                table_name = $(this).attr('href')+'_table';
                chart_type = $(this).attr('href');
                $("#vendor_analysis").find("input[name='chart_type']").val(chart_type.slice(1));
                setTimeout(function(){
                    set_search_parameter($("#vendor_analysis"));
                    if($('.bar_chart_'+$("#vendor_analysis").find("input[name='chart_type']").val()).html() == ''){
                        reloadCharts(table_name,'{{Config::get('app.pageSize')}}',$searchFilter);
                    }
                }, 10);
            });
            $("#vendor_analysis").submit(function(e) {
                e.preventDefault();
                public_vars.$body = $("body");
                //show_loading_bar(40);
                set_search_parameter($(this));
                reloadCharts(table_name,'{{Config::get('app.pageSize')}}',$searchFilter);
                return false;
            });
            set_search_parameter($("#vendor_analysis"));
            Highcharts.theme = {
                colors: ['#3366cc', '#ff9900','#dc3912', '#109618', '#66aa00', '#dd4477','#0099c6', '#990099', '#143DFF']
            };
            // Apply the theme
            Highcharts.setOptions(Highcharts.theme);
            reloadCharts(table_name,'{{Config::get('app.pageSize')}}',$searchFilter);
        });
    </script>
@stop