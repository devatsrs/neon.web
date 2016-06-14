@extends('layout.main')
@section('content')


<ol class="breadcrumb bc-3">
  <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <a href="javascript:void(0)">Server Information</a> </li>
</ol>
<h3>Server Information</h3>
<div class="tab-content">
  <div class="tab-pane active">
 <iframe width="100%;" height="1024px;" id="IframeServer" src="<?php echo "http://".$_SERVER['HTTP_HOST'].":19999/"; ?>"></iframe>
   </div>
  @include('includes.errors')
  @include('includes.success')
<style>
iframe{border:0px;}
</style>
   </div>
@stop
@section('footer_ext')
    @parent
@stop