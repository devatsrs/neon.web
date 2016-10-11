@extends('layout.main')
@section('content')
    <style>
        .panel-title{
            float:none;
        }
    </style>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            setInterval(function(){
                loadDashboard()
            }, 180000);
            loadDashboard();

        });


   </script>
    <script src="{{ URL::asset('assets/js/dashboard.js') }}"></script>
    <form class="hidden" id="hidden_form">
        <input type="hidden" name="Admin" value="{{$isAdmin}}">
		<input type="hidden" name="UserID" value="{{User::get_userID()}}">
    </form>
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="tile-stats tile-cyan stat-tile panel loading">
                <h3>Sales</h3>
                {{--<div class="icon"><i class="fa fa-line-chart"></i></div>--}}
                <p>Today Sales by hour</p>
                <span class="hourly-sales-cost"></span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="tile-stats tile-aqua stat-tile panel loading">
                <h3>Minutes 0</h3>
                {{--<div class="icon"><i class="fa fa-line-chart"></i></div>--}}
                <p>Today Minutes by hour</p>
                <span class="hourly-sales-minutes"></span>
            </div>
        </div>
    </div>

    <br />

    <div class="row">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab1" data-toggle="tab">Destination</a></li>
            <li ><a href="#tab2" data-toggle="tab">Prefix</a></li>
            <li ><a href="#tab3" data-toggle="tab">Trunk</a></li>
            <li ><a href="#tab4" data-toggle="tab">Account</a></li>
            <li ><a href="#tab5" data-toggle="tab">Gateway</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab1" >
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
            <div class="tab-pane" id="tab2" >
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
            <div class="tab-pane" id="tab3" >
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
            <div class="tab-pane" id="tab4" >
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Accounts - Call Count.</div>

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
                                <span class="account-call-count-pie-chart"></span>
                            </div>
                            <p class="call_count_desc"></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Accounts - Call Cost.</div>

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
                                <span class="account-call-cost-pie-chart"></span>
                            </div>
                            <p class="call_cost_desc"></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Accounts - Call Minutes.</div>

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
                                <span class="account-call-minutes-pie-chart"></span>
                            </div>
                            <p class="call_minutes_desc"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="tab5" >
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Gateways - Call Count.</div>

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
                                <span class="gateway-call-count-pie-chart"></span>
                            </div>
                            <p class="call_count_desc"></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Gateways - Call Cost.</div>

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
                                <span class="gateway-call-cost-pie-chart"></span>
                            </div>
                            <p class="call_cost_desc"></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">

                    <div class="panel loading panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                        <!-- panel head -->
                        <div class="panel-heading">
                            <div class="panel-title">Top 10 Gateways - Call Minutes.</div>

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
                                <span class="gateway-call-minutes-pie-chart"></span>
                            </div>
                            <p class="call_minutes_desc"></p>
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