@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{ URL::to('/dashboard') }}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Ticket Fields</strong> </li>
</ol>
<h3>Ticket Fields</h3>
<div class="row">
  <div class="col-md-12" >
    <iframe style="width:100%; height:1000px; border:none;" src="{{URL::to('/ticketsfields/iframe')}}?s=1"></iframe>
  </div>
</div>
@stop 