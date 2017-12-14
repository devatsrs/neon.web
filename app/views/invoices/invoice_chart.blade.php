@extends('layout.blank')
@section('content')
<header class="x-title">
  <div class="payment-strip">
    <div class="x-content">
      <div class="x-row">
        <div class="x-span8">
          <div>
            <div class="due"></div>
          </div>

        </div>
        <div class="x-span8 pull-left" >
          <h1 class="text-center">Management Report</h1>
        </div>
        <div class="x-span4 pull-right" style="margin-top:5px;">
          <a href="#" onclick="goBack()" class="btn pull-right btn-success btn-sm btn-icon icon-left"><i class="entypo-left"></i>
            Go Back
          </a>
        </div>
      </div>
    </div>
  </div>
</header>
<div class="container">
    <hr>
    @include('invoices.management_chart')
</div>
<script>
  function goBack() {
    window.history.back();
  }
</script>
@stop