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
                setTimeout(function(){
                    set_search_parameter($(chart_type).find('form'));
                    if($('.bar_chart_'+$(chart_type).find("input[name='chart_type']").val()).html() == ''){
                        reloadCharts(table_name,'{{Config::get('app.pageSize')}}',$searchFilter);
                    }
                }, 10);
            });
            $(".tab-content").find('form').submit(function(e) {
                e.preventDefault();
                public_vars.$body = $("body");
                //show_loading_bar(40);
                set_search_parameter($(this));
                reloadCharts(table_name,'{{Config::get('app.pageSize')}}',$searchFilter);
                return false;
            });
            console.log($searchFilter);
            set_search_parameter($(chart_type).find('form'));
            Highcharts.theme = {
                colors: ['#ec3b83', '#ffa812', '#0073b7', '#333399', '#287AFF', '#236BFF','#1E5BFF', '#194CFF', '#143DFF']
            };
            // Apply the theme
            Highcharts.setOptions(Highcharts.theme);
            reloadCharts(table_name,'{{Config::get('app.pageSize')}}',$searchFilter);
        });
    </script>
@stop