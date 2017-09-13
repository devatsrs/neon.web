@extends('layout.main')
@section('content')
    <style>
        .small_fld{width:80.6667%;}
    </style>
<br />
{{--<link rel="stylesheet" type="text/css" href="assets/js/daterangepicker/daterangepicker-bs3.css" />--}}

    <ul class="nav nav-tabs">
        <li ><a href="{{ URL::to('/analysis') }}">Customer</a></li>
        <li class="active"><a href="#">Vendor</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="customer" >
            <div class="row">
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
                                <label class="col-sm-1 control-label" for="field-1">Gateway</label>
                                <div class="col-sm-2">
                                    {{ Form::select('CompanyGatewayID',$gateway,'', array("class"=>"select2")) }}
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
                                <label class="col-sm-1 control-label" for="field-1">Account</label>
                                <div class="col-sm-2">
                                    {{ Form::select('AccountID',$account,'', array("class"=>"select2")) }}
                                </div>
                                <label class="col-sm-1 control-label" for="field-1">Currency</label>
                                <div class="col-sm-2">
                                    {{ Form::select('CurrencyID',$currency,$DefaultCurrencyID,array("class"=>"select2")) }}
                                </div>
                                <input type="hidden" name="UserID" value="{{$UserID}}">
                                <input type="hidden" name="Admin" value="{{$isAdmin}}">
                                <input type="hidden" name="chart_type" value="destination">
                                <input type="hidden" name="Prefix" value="">
                                <input type="hidden" name="TrunkID" value="0">
                            </div>
                            <div class="form-group">
                                <label class="col-sm-1 control-label" for="field-1">Country</label>
                                <div class="col-sm-2">
                                    {{ Form::select('CountryID',$Country,'', array("class"=>"select2")) }}
                                </div>
                                <label class="col-sm-1 control-label select_hour" for="field-1">TimeZone</label>
                                <div class="col-sm-2 select_hour">
                                    {{ Form::select('TimeZone',$timezones,'', array("class"=>"select2")) }}
                                </div>
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
                <li class="active"><a href="#destination" data-toggle="tab">Destination</a></li>
                <li ><a href="#description" data-toggle="tab">Destination Break</a></li>
                <li ><a href="#prefix" data-toggle="tab">Prefix</a></li>
                <li ><a href="#trunk" data-toggle="tab">Trunk</a></li>
                <li ><a href="#account" data-toggle="tab">Account</a></li>
                <li ><a href="#gateway" data-toggle="tab">Gateway</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="destination" >
                    @include('vendoranalysis.destination')
                    @include('vendoranalysis.destination_grid')
                </div>
                <div class="tab-pane" id="description" >
                    @include('vendoranalysis.desc')
                    @include('vendoranalysis.desc_grid')
                </div>
                <div class="tab-pane" id="prefix" >
                    @include('vendoranalysis.prefix')
                    @include('vendoranalysis.prefix_grid')
                </div>
                <div class="tab-pane" id="trunk" >
                    @include('vendoranalysis.trunk')
                    @include('vendoranalysis.trunk_grid')
                </div>
                <div class="tab-pane" id="account" >
                    @include('vendoranalysis.account')
                    @include('vendoranalysis.account_grid')
                </div>
                <div class="tab-pane" id="gateway" >
                    @include('vendoranalysis.gateway')
                    @include('vendoranalysis.gateway_grid')
                </div>
            </div>
        </div>
    </div>
@include('vendoranalysis.script')
@stop