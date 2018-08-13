@extends('layout.blank')
@section('content')
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <div class="modal-header">
            @if(isset($Invoice))
                <h4 class="modal-title">Thanks!, Your invoice #{{$Invoice->FullInvoiceNumber}} has been paid </h4>
            @else
                <h4 class="modal-title">Thank You For Payment.</h4>
            @endif
        </div>
        <div class="modal-body">
        </div>
    </div>
</div>
@stop