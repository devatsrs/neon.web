@if($alldata['grid_type'] == 'call_count')
<table class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ucwords($data['chart_type'])}}</th>
            <th>Calls</th>
            <th>ACD</th>
        </tr>
    </thead>
    <tbody>
        @foreach($alldata['call_count'] as $indexcount => $call_cost)
            <tr>
                <td>{{$indexcount+1}}</td>
                <td>{{$call_cost}}</td>
                <td>{{$alldata['call_count_val'][$indexcount]}}</td>
                <td>{{$alldata['call_count_acd'][$indexcount]}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

@if($alldata['grid_type'] == 'cost')
    <table class="table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>{{ucwords($data['chart_type'])}}</th>
            <th>Cost</th>
            <th>ACD</th>
        </tr>
        </thead>
        <tbody>
        @foreach($alldata['call_cost'] as $indexcount => $call_cost)
            <tr>
                <td>{{$indexcount+1}}</td>
                <td>{{$call_cost}}</td>
                <td>{{$alldata['call_cost_val'][$indexcount]}}</td>
                <td>{{$alldata['call_cost_acd'][$indexcount]}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if($alldata['grid_type'] == 'minutes')
    <table class="table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>{{ucwords($data['chart_type'])}}</th>
            <th>Minutes</th>
            <th>ACD</th>
        </tr>
        </thead>
        <tbody>
        @foreach($alldata['call_minutes'] as $indexcount => $call_cost)
            <tr>
                <td>{{$indexcount+1}}</td>
                <td>{{$call_cost}}</td>
                <td>{{$alldata['call_minutes_val'][$indexcount]}}</td>
                <td>{{$alldata['call_minutes_acd'][$indexcount]}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
