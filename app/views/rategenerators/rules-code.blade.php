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
        <strong>Update Rate Generator</strong>
    </li>
</ol>
<h3> Update Rate Generator</h3>
<div class="float-right">
    <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
            <li class="active"><a href="{{URL::to('rategenerators/rules/'.$id.'/edit/'.$RateRuleID)}}">Code</a></li>
            <li  ><a href="{{URL::to('rategenerators/rules/'.$id.'/edit_source/'.$RateRuleID)}}">Sources</a></li>
            <li ><a href="{{URL::to('rategenerators/rules/'.$id.'/edit_margin/'.$RateRuleID)}}">Margin</a></li>
        </ul>

        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Rate Generator Rule Information
                </div>

                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>



            <div class="panel-body">
                <form role="form" id="rategenerator-from" method="post" action="{{URL::to('rategenerators/rules/'.$id.'/update/'.$RateRuleID)}}" class="form-horizontal form-groups-bordered">
                    @if(count($rategenerator_rules))
                    @foreach($rategenerator_rules as $rategenerator_rule)
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Code</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="Code"  id="field-1" placeholder="" value="{{$rategenerator_rule->Code}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Description</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="Description" id="field-2" placeholder="" value="{{$rategenerator_rule->Description}}" />
                        </div>
                    </div>
                    @endforeach
                    @endif
                </form>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        $(".save.btn").click(function(ev) {

            var Code = $("#rategenerator-from input[name='Code']").val();
            var Description = $("#rategenerator-from input[name='Description']").val();

            if((typeof Code  == 'undefined' || Code.trim() == '' ) && (typeof Description  == 'undefined' || Description.trim() == '' )){

                setTimeout(function(){$('.btn').button('reset');},10);
                toastr.error("Please Enter a Code Or Description", "Error", toastr_opts);
                return false;

            } else {

                $("#rategenerator-from").submit();
            }

        });
    });
</script>
@include('includes.ajax_submit_script', array('formID'=>'rategenerator-from' , 'url' => ('rategenerators/rules/'.$id.'/update/'.$RateRuleID)))
@stop         
