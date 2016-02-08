@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('trunks')}}">Trunks</a>
    </li>
    <li class="active">
        <strong>New Trunk</strong>
    </li>
</ol>
<h3>New Trunk</h3>
@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">
    <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>

    <a href="{{URL::to('trunks')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
</p>
<br>
<div class="row">
    <div class="col-md-12">

        <div class="panel panel-primary" data-collapsed="0">

            <div class="panel-heading">
                <div class="panel-title">
                    Trunk Detail
                </div>

                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>

            <div class="panel-body">

            <form role="form" id="form-trunk-add"  method="post" action="{{URL::current()}}"  class="form-horizontal form-groups-bordered">

                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Title</label>

                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="Trunk" name="Trunk" placeholder="Title" value="{{Input::old('Trunk')}}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Rate Prefix</label>

                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="RatePrefix" data-mask="999999999999" placeholder="Rate Prefix" value="{{Input::old('RatePrefix')}}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Area Prefix</label>

                        <div class="col-sm-6">
                            <input type="text" class="form-control" data-mask="999999999999" name="AreaPrefix" placeholder="Area Prefix" value="{{Input::old('AreaPrefix')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Prefix</label>

                        <div class="col-sm-6">
                            <input type="text" class="form-control" data-mask="999999999999" name="Prefix" placeholder="Prefix" value="{{Input::old('Prefix')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Active</label>
                        <div class="col-sm-6">
                            <div class="make-switch switch-small">
                                <input type="checkbox" name="Status"  @if(Input::old('Status') =='' )checked="" @else  @if( ( Input::old('Status') !='' ) && Input::old('Status') == 1 ) checked=""  @endif @endif value="1">
                            </div>
                        </div>
                    </div>

                </form>

            </div>

        </div>

    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function ($) {

        // Replace Checboxes
        $(".save.btn").click(function(ev) {
            $('#form-trunk-add').submit();
         });
    });

</script>
@include('includes.ajax_submit_script', array('formID'=>'form-trunk-add' , 'url' => ('trunks/store'),'update_url'=>'trunks/update/{id}'))
@stop