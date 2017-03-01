@extends('layout.main')
@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li >
            <a href="{{URL::to('billing_class')}}">Billing Class</a>
        </li>
        <li class="active">
            <strong>Create Billing Class</strong>
        </li>
    </ol>


    @include('includes.errors')
    @include('includes.success')
    <p class="pull-right">
        @if(User::checkCategoryPermission('BillingClass','Edit'))
            <button id="save_billing" href="{{URL::to('billing_class/store/0')}}" class="btn btn-primary btn-sm btn-icon icon-left">
                <i class="entypo-floppy"></i>
                Save
            </button>
        @endif
        <a href="{{URL::to('billing_class')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </p>
    @include('billingclass.billingclass')
@stop