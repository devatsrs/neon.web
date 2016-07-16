@extends('layout.reseller.main')
@section('content')
<br />
{{--<link rel="stylesheet" type="text/css" href="assets/js/daterangepicker/daterangepicker-bs3.css" />--}}
<div class="row">
    <ul class="nav nav-tabs">
        @if($is_customer == 1)
            <li class="active"><a href="#">Customer</a></li>
        @endif
        @if($is_vendor == 1)
            <li ><a href="{{ URL::to('reseller/vendor_analysis') }}">Vendor</a></li>
        @endif
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="customer" >
            <div class="col-md-12">
                <form novalidate class="form-horizontal form-groups-bordered filter validate" method="post" id="customer_analysis">
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
                                @if(Session::get('reseller') == 1)
                                    <input type="hidden" name="CurrencyID" value="{{$CurrencyID}}">
                                    <input type="hidden" name="AccountID" value="{{Reseller::get_accountID()}}">
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
            @include('analysis.destination')
            @include('analysis.destination_grid')
        </div>
        <div class="tab-pane" id="prefix" >
            @include('analysis.prefix')
            @include('analysis.prefix_grid')
        </div>
        <div class="tab-pane" id="trunk" >
            @include('analysis.trunk')
            @include('analysis.trunk_grid')
        </div>
        <div class="tab-pane" id="gateway" >
            @include('analysis.gateway')
            @include('analysis.gateway_grid')
        </div>
    </div>
        </div>
    </div>
</div>
<script src="{{ URL::asset('assets/js/reports.js') }}"></script>
{{--<script src="{{ URL::asset('assets/js/daterangepicker/moment.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/daterangepicker/daterangepicker.js') }}"></script>--}}
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
                $("#customer_analysis").find("input[name='chart_type']").val(chart_type.slice(1));
                setTimeout(function(){
                    set_search_parameter($("#customer_analysis"));
                    if($('.bar_chart_'+$("#customer_analysis").find("input[name='chart_type']").val()).html() == ''){
                        reloadCharts(table_name,'{{Config::get('app.pageSize')}}',$searchFilter);
                    }
                }, 10);
            });
            $("#customer_analysis").submit(function(e) {
                e.preventDefault();
                public_vars.$body = $("body");
                //show_loading_bar(40);
                set_search_parameter($(this));
                reloadCharts(table_name,'{{Config::get('app.pageSize')}}',$searchFilter);
                return false;
            });
            set_search_parameter($("#customer_analysis"));
            Highcharts.theme = {
                colors: ['#ec3b83', '#ffa812', '#0073b7', '#333399', '#287AFF', '#236BFF','#1E5BFF', '#194CFF', '#143DFF']
            };
            // Apply the theme
            Highcharts.setOptions(Highcharts.theme);
            reloadCharts(table_name,'{{Config::get('app.pageSize')}}',$searchFilter);
        });
    </script>
@stop