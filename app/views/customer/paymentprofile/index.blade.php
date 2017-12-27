@extends('layout.customer.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>@lang('routes.CUST_PANEL_BREADCRUMB_HOME')</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">@lang('routes.CUST_PANEL_PAGE_PAYMENT_METHOD_PROFILES_TITLE')</a>
        </li>
    </ol>
    @include('customer.paymentprofile.mainpaymentGrid')
@stop

