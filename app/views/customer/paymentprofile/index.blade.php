@extends('layout.customer.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Payment Method Profiles</a>
        </li>
    </ol>
    @include('customer.paymentprofile.mainpaymentGrid')
@stop

