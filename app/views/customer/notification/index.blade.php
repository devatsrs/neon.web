@extends('layout.customer.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="#"><i class="entypo-home"></i>@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_TITLE')</a>
        </li>
    </ol>
    <h3>@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_TITLE')</h3>

    <ul class="nav nav-tabs">
        <li class="active"><a  href="#callmonitor" data-toggle="tab">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_TAB_MONITORING')</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="callmonitor" >
            @include('customer.notification.callmonitor')
        </div>
    </div>

@stop
