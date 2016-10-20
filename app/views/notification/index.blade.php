@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Notifications</a>
        </li>
    </ol>
    <h3>Notifications</h3>

    <ul class="nav nav-tabs">
        <li class="active"><a href="#notification" data-toggle="tab">Notification</a></li>
        <li ><a href="#qos" data-toggle="tab">QOS Alerts</a></li>
        <li ><a href="#callmonitor" data-toggle="tab">Call Monitor</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="notification" >
            @include('notification.notification')
        </div>
        <div class="tab-pane" id="qos" >
            @include('notification.qos')
        </div><div class="tab-pane" id="callmonitor" >
            @include('notification.callmonitor')
        </div>
    </div>

@stop
