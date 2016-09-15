@extends('layout.main')

@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}/">
                <i class="entypo-home"></i>Home
            </a>
        </li>
        <li>
            <a href="{{URL::to('notification')}}">Notification</a>
        </li>
        <li class="active">
            <strong>{{Notification::$type[$NotificationType]}}</strong>
        </li>
    </ol>
    <p class="text-right">
        <button type="submit" id="save_notification" class="btn save btn-primary btn-icon btn-sm icon-left hidden-print" data-loading-text="Loading...">
            Save
            <i class="entypo-floppy"></i>
        </button>
        <a href="{{URL::to('notification')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </p>
    @if($NotificationType == Notification::PaymentReminder)
        @include('notification.paymentreminder')
    @endif



    <script src="{{ URL::asset('assets/js/jquery.multi-select.js') }}"></script>
    <script src="{{ URL::asset('assets/js/jquery.quicksearch.js') }}"></script>
    <script src="{{ URL::asset('assets/js/notification.js') }}"></script>
@stop
