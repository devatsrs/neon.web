@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <div class="tab-content">
                <div class="tab-pane active notification">
                    <form id="notification_filter" method="get" class="form-horizontal form-groups-bordered validate" novalidate>
                        <div class="form-group">
                            <label for="field-1" class="control-label">Type</label>
                            {{Form::select('NotificationType',$notificationType,'',array("class"=>"select2 Notification_Type_dropdown"))}}
                        </div>
                        <div class="form-group">
                            <br/>
                            <button type="submit" class="btn btn-primary btn-md btn-icon icon-left" id="notification_submit">
                                <i class="entypo-search"></i>
                                Search
                            </button>
                        </div>
                    </form>
                </div>
                <div class="tab-pane qos">
                    <form id="notification_filter" method="get" class="form-horizontal form-groups-bordered validate" novalidate>
                        <div class="form-group">
                            <label for="field-1" class="control-label">Type</label>
                            {{Form::select('NotificationType',$notificationType,'',array("class"=>"select2 Notification_Type_dropdown"))}}
                        </div>
                        <div class="form-group">
                            <br/>
                            <button type="submit" class="btn btn-primary btn-md btn-icon icon-left" id="qos_submit">
                                <i class="entypo-search"></i>
                                Search
                            </button>
                        </div>
                    </form>
                </div>
                <div class="tab-pane callmonitor" >
                    <form id="notification_filter" method="get" class="form-horizontal form-groups-bordered validate" novalidate>
                        <div class="form-group">
                            <label for="field-1" class="control-label">Type</label>
                            {{Form::select('NotificationType',$notificationType,'',array("class"=>"select2 Notification_Type_dropdown"))}}
                        </div>
                        <div class="form-group">
                            <br/>
                            <button type="submit" class="btn btn-primary btn-md btn-icon icon-left" id="call_submit">
                                <i class="entypo-search"></i>
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

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
    <br/>
    <ul class="nav nav-tabs">
        <li class="active"><a href=".notification" data-toggle="tab">Notification</a></li>
        <li ><a href=".qos" data-toggle="tab">QoS</a></li>
        <li ><a href=".callmonitor" data-toggle="tab">Monitoring</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active notification" id="notification" >
            @include('notification.notification')
        </div>
        <div class="tab-pane qos" id="qos" >
            @include('notification.qos')
        </div>
        <div class="tab-pane callmonitor" id="callmonitor" >
            @include('notification.callmonitor')
        </div>
    </div>

    <script>
        jQuery(document).ready(function(){
            jQuery('#filter-button-toggle').show();
        });
    </script>
@stop
