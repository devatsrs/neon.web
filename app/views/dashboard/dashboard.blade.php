@extends('layout.main')
@section('content')
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            setInterval(function(){
                loadDashboard()
            }, 60000);
            loadDashboard();
        });
        function toggleFullScreen() {
            if (!document.fullscreenElement &&    // alternative standard method
                    !document.mozFullScreenElement && !document.webkitFullscreenElement) {  // current working methods
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                }
            } else {
                if (document.cancelFullScreen) {
                    document.cancelFullScreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitCancelFullScreen) {
                    document.webkitCancelFullScreen();
                }
            }
        }

   </script>
    <script src="{{ URL::asset('assets/js/dashboard.js') }}"></script>
    <form class="hidden" id="hidden_form">
        <input type="hidden" name="Admin" value="{{$isAdmin}}">
    </form>
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="tile-stats tile-white stat-tile">
                <h3>Total Sales 0</h3>
                <p>Today's Total Sales per hour</p>
                <span class="hourly-sales-cost"></span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="tile-stats tile-white stat-tile">
                <h3>Total Minutes 0</h3>
                <p>Today's Total Minutes per hour</p>
                <span class="hourly-sales-minutes"></span>
            </div>
        </div>


        <div class="col-md-3 col-sm-6">
            <div class="tile-stats tile-white stat-tile">
                <h3>Sales By Gateway</h3>
                <p>Today's gateway sales</p>
                <p class="gateway_desc"></p>
                <span class="gateway-pie-chart pie-chart"></span>
            </div>
        </div>


        <div class="col-md-3 col-sm-6">
            <div class="tile-stats tile-white stat-tile">
                <h3>Sales By trunk</h3>
                <p>Today's Top 2 trunk</p>
                <p class="trunk_desc"></p>
                <span class="trunk-pie-chart pie-chart"></span>
            </div>
        </div>
    </div>

    <br />

    <div class="row">
        <div class="col-md-9">



            <div class="tile-group tile-group-2">
                <div class="tile-left tile-white">
                    <div class="tile-entry">
                        <h3>Top Destination </h3>
                        <span>Where do our calls go</span>
                    </div>
                    <ul class="country-list">
                        <li><span class="badge badge-info">1</span>  Pakistan</li>
                        <li><span class="badge badge-info">2</span>  India</li>
                        <li><span class="badge badge-info">3</span>  Pakistan</li>
                    </ul>
                </div>

                <div class="tile-right">

                    <div id="map-2" class="map"></div>

                </div>

            </div>

        </div>



        <div class="col-md-3">
            <div class="tile-stats tile-neon-blue">
                <div class="icon"><i class="entypo-phone"></i></div>
                <div class="num" data-start="0" data-end="1165848" data-postfix="" data-duration="1400" data-delay="0">0</div>

                <h3>Calls</h3>
                <p>Today's Total Calls</p>
            </div>

            <br />

            <div class="tile-stats tile-primary">
                <div class="icon"><i class="entypo-users"></i></div>
                <div class="num" data-start="0" data-end="{{$newAccountCount}}" data-postfix="" data-duration="1400" data-delay="0">0</div>

                <h3>New Accounts</h3>
                <p>Statistics this week</p>
            </div>


        </div>
    </div>
    <div class="row">

        <div class="col-md-4">

            <div class="panel panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                <!-- panel head -->
                <div class="panel-heading">
                    <div class="panel-title">By Prefix – Displaying top 5 prefixes</div>

                    {{--<div class="panel-options">
                        <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                        <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                    </div>--}}
                </div>

                <!-- panel body -->
                <div class="panel-body">
                    <p>by prefix - call count. </p>
                    <br />

                    <div class="text-center">
                        <span class="prefix-call-count-pie-chart"></span>
                    </div>
                    <p class="call_count_desc"></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">

            <div class="panel panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                <!-- panel head -->
                <div class="panel-heading">
                    <div class="panel-title">By Prefix – Displaying top 5 prefixes</div>

                    {{--<div class="panel-options">
                        <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                        <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                    </div>--}}
                </div>

                <!-- panel body -->
                <div class="panel-body">
                    <p>by prefix - cost. </p>
                    <br />

                    <div class="text-center">
                        <span class="prefix-call-cost-pie-chart"></span>
                    </div>
                    <p class="call_cost_desc"></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">

            <div class="panel panel-default" data-collapsed="0"><!-- to apply shadow add class "panel-shadow" -->
                <!-- panel head -->
                <div class="panel-heading">
                    <div class="panel-title">By Prefix – Displaying top 5 prefixes</div>

                    {{--<div class="panel-options">
                        <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-3" class="bg"><i class="entypo-cog"></i></a>
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                        <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                    </div>--}}
                </div>

                <!-- panel body -->
                <div class="panel-body">
                    <p>by prefix - cost. </p>
                    <br />

                    <div class="text-center">
                        <span class="prefix-call-minutes-pie-chart"></span>
                    </div>
                    <p class="call_minutes_desc"></p>
                </div>
            </div>
        </div>
    </div>
    <br />

    <div class="row">
        <div class="col-sm-4">
            <div class="panel panel-primary panel-table">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3>Top Grossing Customer</h3>
                        <span>Weekly statistics from AppStore</span>
                    </div>

                    {{--<div class="panel-options">
                        <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-1" class="bg"><i class="entypo-cog"></i></a>
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                        <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                    </div>--}}
                </div>
                <div class="panel-body">
                    <table class="table table-responsive">
                        <thead>
                        <tr>
                            <th>Account Name</th>
                            <th>Download</th>
                            <th class="text-center">Graph</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td>Flappy Bird</td>
                            <td>2,215,215</td>
                            <td class="text-center"><span class="top-apps">4,3,5,4,5,6,3,2,5,3</span></td>
                        </tr>

                        <tr>
                            <td>Angry Birds</td>
                            <td>1,001,001</td>
                            <td class="text-center"><span class="top-apps">3,2,5,4,3,6,7,5,7,9</span></td>
                        </tr>

                        <tr>
                            <td>Asphalt 8</td>
                            <td>998,003</td>
                            <td class="text-center"><span class="top-apps">1,3,4,3,5,4,3,6,9,8</span></td>
                        </tr>


                        <tr>
                            <td>Viber</td>
                            <td>512,015</td>
                            <td class="text-center"><span class="top-apps">9,2,5,7,2,4,6,7,2,6</span></td>
                        </tr>


                        <tr>
                            <td>Whatsapp</td>
                            <td>504,135</td>
                            <td class="text-center"><span class="top-apps">1,4,5,4,4,3,2,5,4,3</span></td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="col-sm-4">
            <div class="panel panel-primary panel-table">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3>Top Grossing Vendor</h3>
                        <span>Weekly statistics from AppStore</span>
                    </div>

                   {{-- <div class="panel-options">
                        <a href="#sample-modal" data-toggle="modal" data-target="#sample-modal-dialog-1" class="bg"><i class="entypo-cog"></i></a>
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                        <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                    </div>--}}
                </div>
                <div class="panel-body">
                    <table class="table table-responsive">
                        <thead>
                        <tr>
                            <th>Account Name</th>
                            <th>Sales</th>
                            <th class="text-center">Graph</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td>Flappy Bird</td>
                            <td>2,215,215</td>
                            <td class="text-center"><span class="top-apps">4,3,5,4,5,6,3,2,5,3</span></td>
                        </tr>

                        <tr>
                            <td>Angry Birds</td>
                            <td>1,001,001</td>
                            <td class="text-center"><span class="top-apps">3,2,5,4,3,6,7,5,7,9</span></td>
                        </tr>

                        <tr>
                            <td>Asphalt 8</td>
                            <td>998,003</td>
                            <td class="text-center"><span class="top-apps">1,3,4,3,5,4,3,6,9,8</span></td>
                        </tr>


                        <tr>
                            <td>Viber</td>
                            <td>512,015</td>
                            <td class="text-center"><span class="top-apps">9,2,5,7,2,4,6,7,2,6</span></td>
                        </tr>


                        <tr>
                            <td>Whatsapp</td>
                            <td>504,135</td>
                            <td class="text-center"><span class="top-apps">1,4,5,4,4,3,2,5,4,3</span></td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    <link rel="stylesheet" href="assets/js/jvectormap/jquery-jvectormap-1.2.2.css">
    <script src="assets/js/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="assets/js/jvectormap/jquery-jvectormap-europe-merc-en.js"></script>
@stop