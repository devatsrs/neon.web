@if($alldata['grid_type'] == 'call_count')
<table class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>{{ucwords($data['chart_type'])}}</th>
            <th>Calls</th>
            <th>ACD (mm:ss)</th>
            <th>ASR (%)</th>
            @if((int)Session::get('customer') == 0)
            <th>Margin</th>
            @endif

        </tr>
    </thead>
    <tbody>
        @foreach($alldata['call_count'] as $indexcount => $call_cost)
            <tr>
                <td>{{$indexcount+1}}</td>
                <td>
                    <?php
                    if((int)Session::get('customer') == 1){
                        $cdr_url = (isset($customer)&& $customer ==1 ? URL::to('customer/cdr') : '');
                    }else{
                        $cdr_url = (isset($customer)&& $customer ==1 ? URL::to('cdr_show') : URL::to('vendorcdr_show'));
                    }
                    ?>
                    @if(!empty($cdr_url) && ($data['chart_type'] == 'trunk' || $data['chart_type'] == 'prefix'))
                        <a target="_blank" href="{{$cdr_url.'?'.http_build_query(array_merge(array($data['chart_type']=>$call_cost,'StartDate'=>isset($data['StartDate'])?$data['StartDate']:date('Y-m-d'),'EndDate'=>isset($data['EndDate'])?$data['EndDate']:date('Y-m-d')),$param_array))}}">{{$call_cost}}</a>
                    @elseif($data['chart_type'] == 'gateway' && !empty($cdr_url))
                        <a target="_blank" href="{{$cdr_url.'?'.http_build_query(array_merge($param_array,array('CompanyGatewayID'=>CompanyGateway::getCompanyGatewayIDByName($call_cost),'StartDate'=>isset($data['StartDate'])?$data['StartDate']:date('Y-m-d'),'EndDate'=>isset($data['EndDate'])?$data['EndDate']:date('Y-m-d'))))}}">{{$call_cost}}</a>
                    @elseif($data['chart_type'] == 'account' && !empty($cdr_url))
                        <a target="_blank" href="{{$cdr_url.'?'.http_build_query(array_merge($param_array,array('AccountID'=>Account::getAccountIDByName($call_cost),'StartDate'=>isset($data['StartDate'])?$data['StartDate']:date('Y-m-d'),'EndDate'=>isset($data['EndDate'])?$data['EndDate']:date('Y-m-d'))))}}">{{$call_cost}}</a>
                    @else
                        {{$call_cost}}
                    @endif
                </td>
                <td>{{$alldata['call_count_val'][$indexcount]}}</td>
                <td>{{$alldata['call_count_acd'][$indexcount]}}</td>
                <td>{{$alldata['call_count_asr'][$indexcount]}}</td>
                @if((int)Session::get('customer') == 0)
                <td>{{$alldata['call_count_mar'][$indexcount]}}</td>
                @endif
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
            <th>ACD (mm:ss)</th>
            <th>ASR (%)</th>
            @if((int)Session::get('customer') == 0)
            <th>Margin</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach($alldata['call_cost'] as $indexcount => $call_cost)
            <tr>
                <td>{{$indexcount+1}}</td>
                <td>
                    <?php
                    if((int)Session::get('customer') == 1){
                        $cdr_url = (isset($customer)&& $customer ==1 ? URL::to('customer/cdr') : '');
                    }else{
                        $cdr_url = (isset($customer)&& $customer ==1 ? URL::to('cdr_show') : URL::to('vendorcdr_show'));
                    }
                    ?>
                    @if(!empty($cdr_url) && ($data['chart_type'] == 'trunk' || $data['chart_type'] == 'prefix'))
                        <a target="_blank" href="{{$cdr_url.'?'.http_build_query(array_merge(array($data['chart_type']=>$call_cost),$param_array))}}">{{$call_cost}}</a>
                    @elseif($data['chart_type'] == 'gateway' && !empty($cdr_url))
                        <a target="_blank" href="{{$cdr_url.'?'.http_build_query(array_merge($param_array,array('CompanyGatewayID'=>CompanyGateway::getCompanyGatewayIDByName($call_cost))))}}">{{$call_cost}}</a>
                    @elseif($data['chart_type'] == 'account' && !empty($cdr_url))
                        <a target="_blank" href="{{$cdr_url.'?'.http_build_query(array_merge($param_array,array('AccountID'=>Account::getAccountIDByName($call_cost))))}}">{{$call_cost}}</a>
                    @else
                        {{$call_cost}}
                    @endif
                </td>
                <td>{{$alldata['call_cost_val'][$indexcount]}}</td>
                <td>{{$alldata['call_cost_acd'][$indexcount]}}</td>
                <td>{{$alldata['call_cost_asr'][$indexcount]}}</td>
                @if((int)Session::get('customer') == 0)
                <td>{{$alldata['call_cost_mar'][$indexcount]}}</td>
                @endif
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
            <th>ACD (mm:ss)</th>
            <th>ASR (%)</th>
            @if((int)Session::get('customer') == 0)
            <th>Margin</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach($alldata['call_minutes'] as $indexcount => $call_cost)
            <tr>
                <td>{{$indexcount+1}}</td>
                <td>
                    <?php
                    if((int)Session::get('customer') == 1){
                        $cdr_url = (isset($customer)&& $customer ==1 ? URL::to('customer/cdr') : '');
                    }else{
                        $cdr_url = (isset($customer)&& $customer ==1 ? URL::to('cdr_show') : URL::to('vendorcdr_show'));
                    }
                    ?>
                    @if(!empty($cdr_url) && ($data['chart_type'] == 'trunk' || $data['chart_type'] == 'prefix'))
                        <a target="_blank" href="{{$cdr_url.'?'.http_build_query(array_merge(array($data['chart_type']=>$call_cost),$param_array))}}">{{$call_cost}}</a>
                    @elseif($data['chart_type'] == 'gateway' && !empty($cdr_url))
                        <a target="_blank" href="{{$cdr_url.'?'.http_build_query(array_merge($param_array,array('CompanyGatewayID'=>CompanyGateway::getCompanyGatewayIDByName($call_cost))))}}">{{$call_cost}}</a>
                    @elseif($data['chart_type'] == 'account' && !empty($cdr_url))
                        <a target="_blank" href="{{$cdr_url.'?'.http_build_query(array_merge($param_array,array('AccountID'=>Account::getAccountIDByName($call_cost))))}}">{{$call_cost}}</a>
                    @else
                        {{$call_cost}}
                    @endif
                </td>
                <td>{{$alldata['call_minutes_val'][$indexcount]}}</td>
                <td>{{$alldata['call_minutes_acd'][$indexcount]}}</td>
                <td>{{$alldata['call_minutes_asr'][$indexcount]}}</td>
                @if((int)Session::get('customer') == 0)
                <td>{{$alldata['call_minutes_mar'][$indexcount]}}</td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
