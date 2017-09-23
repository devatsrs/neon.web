@extends('layout.main')

@section('filter-button')
    <li>
        <a href="javascript:void(0);" data-toggle="datatable-filter" class="btn btn-default btn-xs" data-animate="1" data-collapse-sidebar="1"><i class="fa fa-filter"></i></a>
    </li>
@stop
@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate="novalidate" class="form-horizontal form-groups-bordered filter validate" method="post" id="customer_analysis">
                <div class="form-group">
                    <label class="control-label" for="field-1">Start Date</label>
                    <div class="row">
                        <div class="col-sm-6">
                            <input type="text" name="StartDate"  class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d')}}"/>
                        </div>
                        <div class="col-md-6 select_hour">
                            <input type="text" name="StartHour" data-minute-step="30"   data-show-meridian="false" data-default-time="00:00" value="00:00"  data-template="dropdown" class="form-control timepicker">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">End Date</label>
                    <div class="row">
                        <div class="col-sm-6">
                            <input type="text" name="EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d' )}}" />
                        </div>
                        <div class="col-sm-6 select_hour">
                            <input type="text" name="EndHour" data-minute-step="30"   data-show-meridian="false" data-default-time="23:30" value="23:30"   data-template="dropdown" class="form-control timepicker">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Gateway</label>
                    {{ Form::select('CompanyGatewayID',$gateway,'', array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Prefix</label>
                    <input type="text" name="Prefix"  class="form-control"/>
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Trunk</label>
                    {{ Form::select('TrunkID',$trunks,'', array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Account</label>
                    {{ Form::select('AccountID',$account,'', array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Currency</label>
                    {{ Form::select('CurrencyID',$currency,$DefaultCurrencyID,array("class"=>"select2")) }}
                    <input type="hidden" name="UserID" value="{{$UserID}}">
                    <input type="hidden" name="Admin" value="{{$isAdmin}}">
                    <input type="hidden" name="chart_type" value="destination">
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Country</label>
                    {{ Form::select('CountryID',$Country,'', array("class"=>"select2")) }}
                </div>
                <div class="form-group select_hour">
                    <label class="control-label select_hour" for="field-1">TimeZone</label>
                    {{ Form::select('TimeZone',$timezones,'', array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop


@section('content')
<br />
<style>
    .small_fld{width:80.6667%;}
</style>
{{--<link rel="stylesheet" type="text/css" href="assets/js/daterangepicker/daterangepicker-bs3.css" />--}}

    <ul class="nav nav-tabs">
        <li class="active"><a href="#">Customer</a></li>
        <li ><a href="{{ URL::to('/vendor_analysis') }}">Vendor</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="customer" >

            @include('analysis.map')
            @include('analysis.chartreport')
    <ul class="nav nav-tabs refresh_tab">
        @if( (empty($MonitorDashboardSetting)) ||  in_array('AnalysisMonitor',$MonitorDashboardSetting))
        <li class="active"><a href="#destination" data-toggle="tab">Destination</a></li>
        <li ><a href="#description" data-toggle="tab">Destination Break</a></li>
        <li ><a href="#prefix" data-toggle="tab">Prefix</a></li>
        <li ><a href="#trunk" data-toggle="tab">Trunk</a></li>
        <li ><a href="#account" data-toggle="tab">Account</a></li>
        <li ><a href="#gateway" data-toggle="tab">Gateway</a></li>
        @endif
        @if((empty($MonitorDashboardSetting)) ||  in_array('CallMonitor',$MonitorDashboardSetting))
            <li class="{{!in_array('AnalysisMonitor',$MonitorDashboardSetting)?'active':''}}"><a href="#tab6" data-toggle="tab">Most Dialled Number</a></li>
            <li ><a href="#tab7" data-toggle="tab">Longest Durations Calls</a></li>
            <li ><a href="#tab8" data-toggle="tab">Most Expensive Calls</a></li>
        @endif
    </ul>
    <div class="tab-content">
        @if( (empty($MonitorDashboardSetting)) ||  in_array('AnalysisMonitor',$MonitorDashboardSetting))
        <div class="tab-pane active" id="destination" >
            @include('analysis.destination')
            @include('analysis.destination_grid')
        </div>
        <div class="tab-pane" id="description" >
            @include('analysis.desc')
            @include('analysis.desc_grid')
        </div>
        <div class="tab-pane" id="prefix" >
            @include('analysis.prefix')
            @include('analysis.prefix_grid')
        </div>
        <div class="tab-pane" id="trunk" >
            @include('analysis.trunk')
            @include('analysis.trunk_grid')
        </div>
        <div class="tab-pane" id="account" >
            @include('analysis.account')
            @include('analysis.account_grid')
        </div>
        <div class="tab-pane" id="gateway" >
            @include('analysis.gateway')
            @include('analysis.gateway_grid')
        </div>
        @endif

        @if((empty($MonitorDashboardSetting)) ||  in_array('CallMonitor',$MonitorDashboardSetting))
            @include('dashboard.retailmonitor')
        @endif

    </div>
        </div>
    </div>

@include('analysis.script')
@stop