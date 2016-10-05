@extends('layout.blank')
@section('content')
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <div class="modal-header">
            <h4 class="modal-title">Thanks!, Your invoice #{{$Invoice->FullInvoiceNumber}} has been paid </h4>
        </div>
        <div class="modal-body">
        </div>
    </div>
</div>
@stop