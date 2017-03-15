@extends('layout.main')
@section('content')
<div id="content">
  <ol class="breadcrumb bc-3">
    <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
    <li> <a href="{{URL::to('ticketsfields')}}">Ticket Fields</a> </li>
  </ol>
  <h3>Ticket Fields</h3>
  <div class="row">
    <div class="col-md-12 clearfix"> </div>
  </div>
  <section class="deals-board" >
    <div id="board-start" class="board" > </div>
    <form id="cardorder" method="POST" />    
    <input type="hidden" name="cardorder" />
    <input type="hidden" name="BoardColumnID" />
    </form>
  </section>
  @include('ticketsfields.fields_css_js') </div>
@stop
@section('footer_ext')
    @parent
@stop 