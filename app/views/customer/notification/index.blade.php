@extends('layout.customer.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="#"><i class="entypo-home"></i>Notifications</a>
        </li>
    </ol>
    <h3>Notifications</h3>

    <ul class="nav nav-tabs">
        <li class="active"><a  href="#callmonitor" data-toggle="tab">Call Monitor</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="callmonitor" >
            @include('customer.notification.callmonitor')
        </div>
    </div>

@stop
