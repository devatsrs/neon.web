@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('/rategenerators')}}">Rate Generator</a>
        </li>

        <li class="active">
            <strong>Add Rate Generator Rule</strong>
        </li>
    </ol>
    <h3>Add Rate Generator Rule</h3>
    <div class="float-right">
        <a href="{{URL::to('rategenerators/'.$id.'')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </div>



    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-tabs bordered" >
                <li class="active"><a data-toggle="tab" href="#tab-code_description">Code</a></li>
                <li><a class="disabled" href="#">Sources</a></li>
                <li><a class="disabled" href="#">Margin</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab-code_description">
                    @include('rategenerators.rules.add_code', array('id'))
                </div>
            </div>

        </div>
    </div>
@stop
