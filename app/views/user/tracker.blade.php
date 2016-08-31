@extends('layout.main_only_sidebar')
@section('content')
    <iframe width="100%;" height="100%;" id="IframeServer" src="{{URL::to('stats')}}" scrolling="no"></iframe>
    @include('includes.errors')
    @include('includes.success')
    <style>
        iframe{border:0px;}
        .main-content{ padding: 0 !important;}
    </style>
    <script>
        setInterval(function(){
            $( "#IframeServer" ).contents().find( "#side-menu .fa-bolt").parents('li').hide();
            $( "#IframeServer" ).contents().find( ".navbar-right li:first-child").hide();
            $( "#IframeServer" ).contents().find( ".navbar-header .navbar-brand").html('Neon Stats Tracker');
            sidebar_height = $(".sidebar-menu").height();
            $("#content").height(sidebar_height);
        },3000);
    </script>
@stop
@section('footer_ext')
    @parent
@stop