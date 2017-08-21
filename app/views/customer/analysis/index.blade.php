@extends('layout.customer.main')
@section('content')
    <style>
        .small_fld{width:80.6667%;}
    </style>
<br />
{{--<link rel="stylesheet" type="text/css" href="assets/js/daterangepicker/daterangepicker-bs3.css" />--}}

    <ul class="nav nav-tabs">
        @if($is_customer == 1)
            <li class="active"><a href="#">Customer</a></li>
        @endif
        @if($is_vendor == 1)
            <li ><a href="{{ URL::to('customer/vendor_analysis') }}">Vendor</a></li>
        @endif
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="customer" >
            <div class="row">
            <div class="col-md-12">
                <form novalidate="novalidate" class="form-horizontal form-groups-bordered filter validate" method="post" id="customer_analysis">
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
                                <div class="col-sm-2" style="padding-left:0; padding-right:0; width:10%;">
                                    <input type="text" name="StartDate"  class="form-control datepicker small_fld"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d')}}"/>
                                </div>
                                <div class="col-md-1 select_hour" style="padding: 0px; width: 9%;">
                                    <input type="text" name="StartHour" data-minute-step="30"   data-show-meridian="false" data-default-time="00:00" value="00:00"  data-template="dropdown" class="form-control timepicker small_fld">
                                </div>
                                <label class="col-sm-1 control-label" for="field-1">End Date</label>
                                <div class="col-sm-2" style="padding-left:0; padding-right:0; width:10%;">
                                    <input type="text" name="EndDate" class="form-control datepicker small_fld"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d' )}}" />
                                </div>
                                <div class="col-md-1 select_hour" style="padding: 0px; width: 9%;">
                                    <input type="text" name="EndHour" data-minute-step="30"   data-show-meridian="false" data-default-time="23:30" value="23:30"   data-template="dropdown" class="form-control timepicker small_fld">
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
                                <label class="col-sm-1 control-label select_hour" for="field-1">TimeZone</label>
                                <div class="col-sm-2 select_hour">
                                    {{ Form::select('TimeZone',$timezones,'', array("class"=>"select2")) }}
                                </div>
                                <input type="hidden" name="CurrencyID" value="{{$CurrencyID}}">
                                <input type="hidden" name="AccountID" value="{{Customer::get_accountID()}}">
                                <input type="hidden" name="CompanyGatewayID" value="0">
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
            </div>
            @include('analysis.map')
            @include('analysis.chartreport')
    <ul class="nav nav-tabs refresh_tab">
        @if( (empty($MonitorDashboardSetting)) ||  in_array('AnalysisMonitor',$MonitorDashboardSetting))
        <li class="active"><a href="#destination" data-toggle="tab">Destination</a></li>
        <li ><a href="#description" data-toggle="tab">Destination Break</a></li>
        <li ><a href="#prefix" data-toggle="tab">Prefix</a></li>
        <li ><a href="#trunk" data-toggle="tab">Trunk</a></li>
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
        @endif
        @if((empty($MonitorDashboardSetting)) ||  in_array('CallMonitor',$MonitorDashboardSetting))
            @include('dashboard.retailmonitor')
        @endif
    </div>
        </div>
    </div>

@include('analysis.script')
@stop