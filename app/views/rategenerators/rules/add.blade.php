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
        <button type="button"  class="saveall btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
            <i class="entypo-floppy"></i>
            Save
        </button>
        <a href="{{URL::to('rategenerators/'.$id.'/edit')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </div>



    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-tabs bordered" >
                <li class="active"><a data-toggle="tab" href="#tab-code_description">Destination</a></li>
                <li><a data-toggle="tab" href="#tab-source">Sources</a></li>
                <li><a class="disabled" href="#">Margin</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab-code_description">
                    @include('rategenerators.rules.add_code', array('id'))
                </div>

            </div>
        </div>
    </div>

<script type="text/javascript">
        jQuery(document).ready(function($) {

            $(".saveall.btn").click(function(e){

                var OriginationCode = $("#rategenerator-code-from input[name='OriginationCode']").val();
                var OriginationDescription = $("#rategenerator-code-from input[name='OriginationDescription']").val();
                var DestinationCode = $("#rategenerator-code-from input[name='DestinationCode']").val();
                var DestinationDescription = $("#rategenerator-code-from input[name='DestinationDescription']").val();

                if((typeof OriginationCode  == 'undefined' || OriginationCode.trim() == '' ) && (typeof OriginationDescription  == 'undefined' || OriginationDescription.trim() == '' )) {
                    if((typeof DestinationCode  == 'undefined' || DestinationCode.trim() == '' ) && (typeof DestinationDescription  == 'undefined' || DestinationDescription.trim() == '' )) {
                        setTimeout(function () {
                            $('.btn').button('reset');
                        }, 10);
                        toastr.error("Please Enter a Code Or Description", "Error", toastr_opts);
                        return false;
                    }
                }


                var _url = $("#rategenerator-code-from").attr("action");

                submit_ajax(_url,$("#rategenerator-code-from").serialize());

                return false;
           });

        });
    </script>
@stop
