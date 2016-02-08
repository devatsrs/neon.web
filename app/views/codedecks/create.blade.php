@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>

        <a href="{{URL::to('/codedecks')}}">Code Decks</a>
    </li>
    <li class="active">
        <strong>New Code Deck</strong>
    </li>
</ol>
<h3> New Code Deck</h3>
@include('includes.errors')
@include('includes.success')

<p style="text-align:right;">
    <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>

    <a href="{{URL::to('codedecks')}}" class="btn btn-danger btn-sm btn-icon icon-left">
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
                    Code Deck Detail
                </div>

                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>

            <div class="panel-body">

                <form role="form" id="codedecks-from" method="post" action="{{URL::to('codedecks/store')}}" class="form-horizontal form-groups-bordered">

                    <div class="form-group">
                        <label for=" field-1" class="col-sm-3 control-label">
                            Country
                        </label>

                        <div class="col-sm-6">

                            {{Form::select('CountryID', $countries, Input::old('Country') ,array("class"=>"form-control select2"))}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for=" field-1" class="col-sm-3 control-label">
                            CodeDeck
                        </label>

                        <div class="col-sm-6">

                            {{ Form::select('codedeckid', $codedecklist, Input::get('codedeckid') , array("class"=>"select2")) }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Code</label>

                        <div class="col-sm-6">
                            <input type="text" name="Code" class="form-control" data-mask="999999999999" placeholder="Code">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Description</label>

                        <div class="col-sm-6">
                            <input type="text" name="Description" class="form-control" id="field-1" placeholder="Description">
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
        $(".save.btn").click(function (ev) {
            $("#codedecks-from").submit();
        });
    });

</script>
@include('includes.ajax_submit_script', array('formID'=>'codedecks-from' , 'url' => ('codedecks/store'),'update_url'=>'codedecks/update/{id}'))
@stop