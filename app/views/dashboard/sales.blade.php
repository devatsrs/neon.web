@extends('layout.main')
@section('content')

<div class="row">
    <div class="col-md-12">
        <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="post" id="sales_filter">
            <div data-collapsed="0" class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        Filter
                    </div>
                    <div class="panel-options">
                        <a data-rel="collapse" href="#">
                            <i class="entypo-down-open"></i>
                        </a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-1 control-label" for="Startdate">Start date</label>
                        <div class="col-sm-2">
                            <input type="text" name="Startdate" class="form-control datepicker"   data-date-format="yyyy-mm-dd" value="{{$original_startdate}}" data-enddate="{{date('Y-m-d')}}" />
                        </div>
                        <label class="col-sm-1 control-label" for="field-1">End Date</label>
                        <div class="col-sm-2">
                            <input type="text" name="Enddate" class="form-control datepicker"   data-date-format="yyyy-mm-dd" value="{{$original_enddate}}" data-enddate="{{date('Y-m-d', strtotime('+1 day') )}}" />
                        </div>
                        <label class="col-sm-1 control-label">Compare to previous period</label>
                        <div class="col-sm-1">
                            <p class="make-switch switch-small">
                                <input id="compare_with" name="compare_with" @if($compare_with == 1) checked @endif type="checkbox" value="1">
                            </p>
                        </div>
                        @if(User::is_admin())
                         <label for="field-1" class="col-sm-1 control-label">Account Owner</label>
                        <div class="col-sm-2">
                        {{Form::select('account_owners',$account_owners,$userID,array("class"=>"select2"))}}
                        </div>
                        @endif

                    </div>
                    <p style="text-align: right;">
                        <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit">
                            <i class="entypo-search"></i>Search
                        </button>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="row">
    @if(isset($dashboardData['data']['TotalSales']))
    <div class="col-sm-6">
        <div class="panel panel-primary panel-table">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>Total Sales</h3>
                    <span>For selected period</span>
                </div>

                <div class="panel-options">
                    <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a>
                    <a data-rel="close" href="#"><i class="entypo-cancel"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-responsive">
                    <thead>
                    <tr>
                        <th >Current Sales</th>

                        @if(isset($dashboardData['data']['PrevTotalSales'][0]->PrevTotalCharges) && $compare_with == 1)
                        <th >Previous Sales</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        @if(isset($dashboardData['data']['TotalSales'][0]->TotalCharges))
                        <td><a>{{$dashboardData['data']['TotalSales'][0]->TotalCharges}}</a></td>
                        @endif
                        @if(isset($dashboardData['data']['PrevTotalSales'][0]->PrevTotalCharges) && $compare_with == 1)
                        <td><a>{{$dashboardData['data']['PrevTotalSales'][0]->PrevTotalCharges}}</a></td>
                        @endif
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @if(isset($dashboardData['data']['TotalActiveAccount']))
    <div class="col-sm-6">
        <div class="panel panel-primary panel-table">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>Total Account</h3>
                    <span>For selected period</span>
                </div>

                <div class="panel-options">
                    <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a>
                    <a data-rel="close" href="#"><i class="entypo-cancel"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <table class="table table-responsive">
                    <thead>
                    <tr>
                        <th >Active Account</th>
                        <th >InActive Account</th>
                        @if(isset($dashboardData['data']['PrevTotalActiveAccount'][0]->prevtotalactive) && $compare_with == 1)
                        <th >Previous Active Account</th>
                        @endif
                        @if(isset($dashboardData['data']['PrevTotalInActiveAccount'][0]->prevtotalinactive) && $compare_with == 1)
                        <th >Previous InActive Account</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        @if(isset($dashboardData['data']['TotalActiveAccount'][0]->totalactive))
                        <td><a>{{$dashboardData['data']['TotalActiveAccount'][0]->totalactive}}</a></td>
                        @endif
                        @if(isset($dashboardData['data']['TotalInActiveAccount'][0]->totalinactive))
                        <td><a>{{$dashboardData['data']['TotalInActiveAccount'][0]->totalinactive}}</a></td>
                        @endif
                        @if(isset($dashboardData['data']['PrevTotalActiveAccount'][0]->prevtotalactive) && $compare_with == 1)
                        <td><a>{{$dashboardData['data']['PrevTotalActiveAccount'][0]->prevtotalactive}}</a></td>
                        @endif
                        @if(isset($dashboardData['data']['PrevTotalInActiveAccount'][0]->prevtotalinactive) && $compare_with == 1)
                        <td><a>{{$dashboardData['data']['PrevTotalInActiveAccount'][0]->prevtotalinactive}}</a></td>
                        @endif
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    </div>
<div class="row">
@if(isset($dashboardData['data']['AccountSales']))

    <div class="col-sm-6">
        <div class="panel panel-primary panel-table">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>Active Account Sales @if(isset($dashboardData['data']['TotalActiveAccount'][0]->totalactive))({{$dashboardData['data']['TotalActiveAccount'][0]->totalactive}})@endif</h3>
                    <span>For selected period</span>
                </div>
                <div class="panel-options">
                    <a data-rel="collapse" href="#">
                        <i class="entypo-down-open"></i>
                    </a>
                    <a data-rel="reload" href="#">
                        <i class="entypo-arrows-ccw"></i>
                    </a>
                    <a data-rel="close" href="#">
                        <i class="entypo-cancel"></i>
                    </a>
                </div>
            </div>
            <div class="panel-body" style="max-height: 300px; overflow-y: auto; overflow-x: hidden;">
                <table class="table table-responsive">
                    <thead>
                        <tr>
                            <th >Account Name</th>
                            <th >Current Sales</th>
                            @if($compare_with == 1)
                            <th >Previous Sales</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dashboardData['data']['AccountSales'] as $account)
                        <tr>
                            <td>
                                <a href="{{ action('SummaryController@summrybycountry')}}">{{$account->AccountName}}</a>
                            </td>
                            <td>
                                <a>{{$account->TotalCharges}}</a>
                            </td>
                        @if( $compare_with == 1)

                            @if(isset($prevsales[$account->GatewayAccountID]))
                            <td>
                                <a>{{$prevsales[$account->GatewayAccountID]}}</a>
                            </td>
                            @else
                            <td>
                                <a>0.00</a>
                            </td>
                            @endif
                         @endif
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @if(isset($dashboardData['data']['TotalInActiveAccountList']))

        <div class="col-sm-6">
            <div class="panel panel-primary panel-table">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3>InActive Accounts @if(isset($dashboardData['data']['TotalInActiveAccount'][0]->totalinactive))({{$dashboardData['data']['TotalInActiveAccount'][0]->totalinactive}})@endif </h3>
                        <span>For selected period</span>
                    </div>
                    <div class="panel-options">
                        <a data-rel="collapse" href="#">
                            <i class="entypo-down-open"></i>
                        </a>
                        <a data-rel="reload" href="#">
                            <i class="entypo-arrows-ccw"></i>
                        </a>
                        <a data-rel="close" href="#">
                            <i class="entypo-cancel"></i>
                        </a>
                    </div>
                </div>
                <div class="panel-body" style="max-height: 300px; overflow-y: auto; overflow-x: hidden;">
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <th >Account Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dashboardData['data']['TotalInActiveAccountList'] as $account)
                            <tr>
                                <td>
                                    <a>{{$account->AccountName}}</a>
                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif


</div>
<div class="row">
@if(isset($dashboardData['data']['PrevTotalInActiveAccountList']) && $compare_with == 1)

        <div class="col-sm-6">
            <div class="panel panel-primary panel-table">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3>Previous InActive Accounts @if(isset($dashboardData['data']['PrevTotalInActiveAccount'][0]->prevtotalinactive) && $compare_with == 1)({{$dashboardData['data']['PrevTotalInActiveAccount'][0]->prevtotalinactive}})@endif </h3>
                        <span>For selected period</span>
                    </div>
                    <div class="panel-options">
                        <a data-rel="collapse" href="#">
                            <i class="entypo-down-open"></i>
                        </a>
                        <a data-rel="reload" href="#">
                            <i class="entypo-arrows-ccw"></i>
                        </a>
                        <a data-rel="close" href="#">
                            <i class="entypo-cancel"></i>
                        </a>
                    </div>
                </div>
                <div class="panel-body" style="max-height: 300px; overflow-y: auto; overflow-x: hidden;">
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <th >Account Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dashboardData['data']['PrevTotalInActiveAccountList'] as $account)
                            <tr>
                                <td>
                                    <a>{{$account->AccountName}}</a>
                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
</div>
<div class="row">
    <div class="col-sm-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">Top Destination</div>
                </div>
                <div class="panel-body">
                    <label >Current</label>
                    @if(isset($top_data))
                    <div class="pie-large" id="pie-large" style="height: 350px">
                    </div>
                    @else
                         <center>
                        No Data Found
                        </center>
                    @endif
                    @if( $compare_with == 1)
                    <label >Previous</label>
                    @if(isset($prev_top_data))
                            <div class="pie-large-prev" id="pie-large-prev" style="height: 350px">
                            </div>
                    @else
                         <center>
                        No Data Found
                        </center>
                    @endif
                    @endif
                </div>
            </div>
        </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div id="charts_env" class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">Sales Chart</div>
            </div>
            <div class="panel-body">
                <div class="tab-content">
                @if(isset($sales_data))
                    <div class="tab-pane active" id="line-chart">
                        <div id="line-chart-sales" class="morrischart" style="height: 300px"></div>
                    </div>
                @else
                     <center>
                    No Data Found
                    </center>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
@if(User::is_admin())
<div class="row">
    <div class="col-sm-12">
        <div id="charts_env" class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">Leadership Chart</div>
            </div>
            <div class="panel-body">
                <div class="tab-content">
                @if(isset($dashboardData['data']['SalesExecutive']) && count($dashboardData['data']['SalesExecutive']))
                    <div class="tab-pane active" id="line-chart-2">
                        <div id="bar-chart" class="morrischart" style="height: 300px"></div>
                    </div>
                @else
                     <center>
                    No Data Found
                    </center>
                @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endif
<script>
 jQuery(document).ready(function ($) {
    	// Line Charts
        @if(isset($sales_data))
        var line_chart_demo = $("#line-chart-sales");
        var sales_data =  [];
        var ykeys = [];
        ykeys[0]= 'a';
                @foreach($sales_data as $key => $saledatrow)

                        @if(isset($prev_sales_data)  && $compare_with == 1)
                         sales_data['{{$key}}'] = { y: '{{$saledatrow['sale_date']}}', a: '{{$saledatrow['sales']}}' ,b:'{{$prev_sales_data[$saledatrow['sale_date']]['sales']}}'};
                         ykeys[1]= 'b';
                         @else
                         sales_data['{{$key}}'] = { y: '{{$saledatrow['sale_date']}}', a: '{{$saledatrow['sales']}}'};
                        @endif

                @endforeach

            Morris.Line({
                element: 'line-chart-sales',
                data: sales_data,
                xkey: 'y',
                ykeys: ykeys,
                labels:['current sales','previous sales'],
                redraw: true,
                parseTime: false
            });
            line_chart_demo.parent().attr('style', '');
    	@endif
    	@if(isset($dashboardData['data']['SalesExecutive']) && count($dashboardData['data']['SalesExecutive']))
    	var line_chart_demo_2 = $("#bar-chart");
    	var sales_data_2 =  [];
    	var ykeys_2 = [];
                ykeys_2[0]= 'a';

            @foreach($dashboardData['data']['SalesExecutive'] as $key => $SalesExecutiverow)
                @if(isset($prev_sales_data)  && $compare_with == 1)
                 sales_data_2['{{$key}}'] = { y: '{{$SalesExecutiverow->FullName}}', a: '{{$SalesExecutiverow->TotalCharges}}' ,b:'{{$prevSalesExecutive[$SalesExecutiverow->Owner]}}'};
                 ykeys_2[1]= 'b';
                 @else
                 sales_data_2['{{$key}}'] = { y: '{{$SalesExecutiverow->FullName}}', a: '{{$SalesExecutiverow->TotalCharges}}'};
                @endif
            @endforeach
            Morris.Bar({
                        element: 'bar-chart',
                        data: sales_data_2,
                        xkey: 'y',
                        ykeys: ykeys_2,
                        labels:['current sales','previous sales'],
                        barColors: ['#269FFF', '#90D1FF', '#d13c3e'],
                        parseTime: false
                    });
            line_chart_demo_2.parent().attr('style', '');
        @endif
    	@if(isset($top_data))
    	var offset_str ='';
            @foreach($top_data as $key => $toprow)
                @if($key<count($top_data)-1)
                offset_str += '{"label":"{{$toprow['code']}}","value":"{{$toprow['sales']}}"},';
                @else
                offset_str += '{"label":"{{$toprow['code']}}","value":"{{$toprow['sales']}}"}';
                @endif
            @endforeach
            pie_chart('pie-large',JSON.parse("[" + offset_str + "]"));
    	@endif
    	@if(isset($prev_top_data))
    	    var prev_offset_str =  '';
            @foreach($prev_top_data as $key => $toprow)
                @if($key<count($prev_top_data)-1)
                prev_offset_str += '{"label":"{{$toprow['code']}}","value":"{{$toprow['sales']}}"},';
                @else
                prev_offset_str += '{"label":"{{$toprow['code']}}","value":"{{$toprow['sales']}}"}';
                @endif
            @endforeach
            pie_chart('pie-large-prev',JSON.parse("[" + prev_offset_str + "]"));
        @endif

	});

</script>
@stop