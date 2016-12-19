@extends('layout.customer.main')
@section('content')
    <script type="text/javascript">
        var $dashsearchFilter = {};
        $dashsearchFilter.map_url = "{{URL::to('getWorldMap')}}";
        $dashsearchFilter.mapdrill_url = "{{URL::to('getWorldMap')}}";
        $dashsearchFilter.pageSize = '{{Config::get('app.pageSize')}}';
        $dashsearchFilter.Admin = '{{$isAdmin}}';
        $dashsearchFilter.AccountID = '{{Customer::get_accountID()}}';
        $dashsearchFilter.StartDate = '{{date("Y-m-d")}}';
        $dashsearchFilter.EndDate = '{{date("Y-m-d")}}';
        var cdr_url = "{{URL::to('customer/cdr')}}";
        var toFixed = '{{get_round_decimal_places()}}';
        jQuery(document).ready(function ($) {
            setInterval(function(){
                loadDashboard()
            }, 180000);
            loadDashboard();

        });


    </script>
    <script src="{{ URL::asset('assets/js/reports.js') }}"></script>
    <script src="{{ URL::asset('assets/js/dashboard.js') }}"></script>
    <form class="hidden" id="hidden_form">
        <input type="hidden" name="Admin" value="{{$isAdmin}}">
        <input type="hidden" name="AccountID" value="{{Customer::get_accountID()}}">
    </form>
    <div class="row">
        <div class="col-md-9">
            @include('analysis.map')
        </div>
        <div class="col-md-3">
            <div class="row">
                <div class="col-md-12">
                    <div class="tile-stats tile-cyan stat-tile panel loading">
                        <h3>Sales</h3>
                        {{--<div class="icon"><i class="fa fa-line-chart"></i></div>--}}
                        <p>Today Sales by hour</p>
                        <span class="hourly-sales-cost"></span>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="tile-stats tile-aqua stat-tile panel loading">
                        <h3>Minutes 0</h3>
                        {{--<div class="icon"><i class="fa fa-line-chart"></i></div>--}}
                        <p>Today Minutes by hour</p>
                        <span class="hourly-sales-minutes"></span>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="tile-stats tile-pink stat-tile panel loading">
                        <h3>Account Manager</h3>
                        <div class="icon"><i class="fa fa-user"></i></div>
                        <p style="font-size:12px; ">
                            Name:{{$AccountManager}}
                            <br/>
                            Email:{{$AccountManagerEmail}}

                        </p>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab1" data-toggle="tab">Destination</a></li>
            <li ><a href="#tab2" data-toggle="tab">Prefix</a></li>
            <li ><a href="#tab3" data-toggle="tab">Trunk</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab1" >
                <div class="row">
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Destination - Call Count.</div>

                            {{--<div class="panel-options">
                                <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                                <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                            </div>--}}
                        </div>

                        <!-- panel body -->
                        <div class="panel-body">

                            <br />

                            <div class="text-center">
                                <span class="destination-call-count-pie-chart"></span>
                            </div>
                            <p class="call_count_desc"></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Destination - Call Cost.</div>

                            {{--<div class="panel-options">
                                <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                                <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                            </div>--}}
                        </div>

                        <!-- panel body -->
                        <div class="panel-body">

                            <br />

                            <div class="text-center">
                                <span class="destination-call-cost-pie-chart"></span>
                            </div>
                            <p class="call_cost_desc"></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Destination - Call Minutes.</div>

                            {{--<div class="panel-options">
                                <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                                <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                            </div>--}}
                        </div>

                        <!-- panel body -->
                        <div class="panel-body">

                            <br />

                            <div class="text-center">
                                <span class="destination-call-minutes-pie-chart"></span>
                            </div>
                            <p class="call_minutes_desc"></p>
                        </div>
                    </div>
                </div>
                </div>
            </div>
            <div class="tab-pane" id="tab2" >
                <div class="row">
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Prefix - Call Count.</div>

                            {{--<div class="panel-options">
                                <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                                <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                            </div>--}}
                        </div>

                        <!-- panel body -->
                        <div class="panel-body">

                            <br />

                            <div class="text-center">
                                <span class="prefix-call-count-pie-chart"></span>
                            </div>
                            <p class="call_count_desc"></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Prefix - Call Cost.</div>

                            {{--<div class="panel-options">
                                <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                                <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                            </div>--}}
                        </div>

                        <!-- panel body -->
                        <div class="panel-body">

                            <br />

                            <div class="text-center">
                                <span class="prefix-call-cost-pie-chart"></span>
                            </div>
                            <p class="call_cost_desc"></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Prefix - Call Minutes.</div>

                            {{--<div class="panel-options">
                                <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                                <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                            </div>--}}
                        </div>

                        <!-- panel body -->
                        <div class="panel-body">

                            <br />

                            <div class="text-center">
                                <span class="prefix-call-minutes-pie-chart"></span>
                            </div>
                            <p class="call_minutes_desc"></p>
                        </div>
                    </div>
                </div>
                </div>
            </div>
            <div class="tab-pane" id="tab3" >
                <div class="row">
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Trunks - Call Count.</div>

                            {{--<div class="panel-options">
                                <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                                <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                            </div>--}}
                        </div>

                        <!-- panel body -->
                        <div class="panel-body">

                            <br />

                            <div class="text-center">
                                <span class="trunk-call-count-pie-chart"></span>
                            </div>
                            <p class="call_count_desc"></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Trunks - Call Cost.</div>

                            {{--<div class="panel-options">
                                <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                                <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                            </div>--}}
                        </div>

                        <!-- panel body -->
                        <div class="panel-body">

                            <br />

                            <div class="text-center">
                                <span class="trunk-call-cost-pie-chart"></span>
                            </div>
                            <p class="call_cost_desc"></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Trunks - Call Minutes.</div>

                            {{--<div class="panel-options">
                                <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                                <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                            </div>--}}
                        </div>

                        <!-- panel body -->
                        <div class="panel-body">

                            <br />

                            <div class="text-center">
                                <span class="trunk-call-minutes-pie-chart"></span>
                            </div>
                            <p class="call_minutes_desc"></p>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        </div>
    </div>
    @if($isDesktop == 1)
        <button id="toNocWall" class="btn btn-primary pull-right" style="display: block;"><i class="fa fa-arrows-alt"></i></button>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/screenfull.js/3.0.0/screenfull.js"></script>
        <script>
            $(function () {
                //$('#supported').text('Supported/allowed: ' + !!screenfull.enabled);

                if (!screenfull.enabled) {
                    return false;
                }

                $('#toNocWall').click(function () {
                    screenfull.toggle($('.main-content')[0]);
                });

                function fullscreenchange() {
                    if (!screenfull.isFullscreen) {
                        document.body.style.overflow = 'auto';
                        $('#toNocWall').find('i').addClass('fa-arrows-alt').removeClass('fa-compress');
                    }else{
                        $('#toNocWall').find('i').addClass('fa-compress').removeClass('fa-arrows-alt');
                    }
                }

                document.addEventListener(screenfull.raw.fullscreenchange, fullscreenchange);

                // set the initial values
                fullscreenchange();
            });
        </script>
    @endif
@stop