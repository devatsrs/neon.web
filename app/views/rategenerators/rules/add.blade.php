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
            <strong>Add Rate Generator Margin</strong>
        </li>
    </ol>
    <h3>Add Rate Generator Margin</h3>
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
    <div class="clearfix"></div>

    <form role="form" id="rategenerator-code-from" method="post" action="{{URL::to('rategenerators/'.$id.'/rule/store_code')}}">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="field-4" class="control-label">Component</label>
                    {{ Form::select('Component', RateGenerator::$Component, '', array("class"=>"select2")) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="field-5" class="control-label">Origination</label>
                    <input type="text" class="form-control" name="Origination"/>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="field-5" class="control-label">Time of Day</label>
                    {{ Form::select('TimeOfDay', $Timezones, '', array("class"=>"select2")) }}
                </div>
            </div>
        </div>
    </form>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(".saveall.btn").click(function(e){

                var Origination = $("#rategenerator-code-from input[name='Origination']").val();
                var Component = $("#rategenerator-code-from select[name='Component']").val();
                var TimeOfDay = $("#rategenerator-code-from select[name='TimeOfDay']").val();

                if(Origination == '' && Component == '' && TimeOfDay == ''){
                    setTimeout(function(){$('.btn').button('reset');},10);
                    toastr.error("Please Select Origination, Component, Time of Day", "Error", toastr_opts);
                    return false;
                }
                var _url = $("#rategenerator-code-from").attr("action");
                submit_ajax(_url,$("#rategenerator-code-from").serialize());

                return false;
            });

        });
    </script>
@stop
