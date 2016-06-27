@extends('layout.main_only_sidebar')
@section('content')
 <iframe width="100%;" height="100%;" id="IframeServer" src="<?php echo getenv("SERVER_MONITOR_URL"); ?>"></iframe>
  @include('includes.errors')
  @include('includes.success')
<style>
iframe{border:0px;}
.main-content{ padding: 0 !important;}
</style>
<script>
    setTimeout(
            function(){
                sidebar_height = $(".sidebar-menu").height() - 70;
                $("#IframeServer").height(sidebar_height);
            },1000
    );
</script>
@stop
@section('footer_ext')
    @parent
@stop