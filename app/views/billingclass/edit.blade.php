@extends('layout.main')
@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li >
            <a href="{{URL::to('billing_class')}}">Billing Class</a>
        </li>
        <li>
            {{Form::select('BillingClassList', $BillingClassList, $BillingClass->BillingClassID ,array("id"=>"drp_toandfro_jump" ,"class"=>"selectboxit1 form-control1"));}}
        </li>
        <li class="active">
            <strong>Edit Billing Class ({{$BillingClass->Name}})</strong>
        </li>
    </ol>
    <h3>Billing Class</h3>

    @include('includes.errors')
    @include('includes.success')

    <p class="pull-right">
        @if(User::checkCategoryPermission('BillingClass','Edit'))
            <button id="save_billing" href="{{URL::to('billing_class/update/'.$BillingClass->BillingClassID)}}" class="btn btn-primary btn-sm btn-icon icon-left">
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