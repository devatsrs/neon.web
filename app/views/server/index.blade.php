@extends('layout.main_only_sidebar')
@section('content')
 <iframe width="100%;" height="1024px;" id="IframeServer" src="<?php echo getenv("SERVER_MONITOR_URL"); ?>"></iframe>
  @include('includes.errors')
  @include('includes.success')
<style>
iframe{border:0px;}
.main-content{ padding: 0 !important;}
</style>
@stop
@section('footer_ext')
    @parent
@stop