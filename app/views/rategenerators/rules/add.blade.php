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
    @if($rateGenerator->SelectType == 1)
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs bordered">
                    <li class="active"><a data-toggle="tab" href="#tab-code_description">Call Codes</a></li>
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
    @endif
    @if($rateGenerator->SelectType == 2)
        <form role="form" id="rategenerator-code-from" method="post" action="{{URL::to('rategenerators/'.$id.'/rule/store_code')}}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-4" class="control-label">Component*</label>
                        {{ Form::select('Component', DiscountPlan::$RateTableDIDRate_Components, '', array("class"=>"select2")) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-4" class="control-label">Country*</label>
                        {{ Form::select('CountryID', $country, '', array("class"=>"select2")) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-5" class="control-label">Access Type*</label>
                        {{ Form::select('AccessType', $AccessType, '', array("class"=>"select2")) }}
                    </div>
                </div>
                <div class="col-md-3">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Prefix*</label>
                            {{ Form::select('Prefix', $Prefix, '', array("class"=>"select2")) }}
                        </div>
                    </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-5" class="control-label">City*</label>
                        {{ Form::select('City', $City, null, array("class"=>"select2")) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-5" class="control-label">Tariff*</label>
                        {{ Form::select('Tariff', $Tariff, null, array("class"=>"select2")) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-5" class="control-label">Origination</label>
                        <input type="text" class="form-control" name="Origination"/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="field-5" class="control-label">Time of Day*</label>
                        {{ Form::select('TimeOfDay', $Timezones, '', array("class"=>"select2")) }}
                    </div>
                </div>
            </div>
        </form>
    @endif
    @if($rateGenerator->SelectType == 3)
        <form role="form" id="rategenerator-code-from" method="post" action="{{URL::to('rategenerators/'.$id.'/rule/store_code')}}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="field-4" class="control-label">Package*</label>
                        {{ Form::select('PackageID', $Package, '', array("class"=>"select2")) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="field-4" class="control-label">Component*</label>
                        {{ Form::select('Component', DiscountPlan::$RateTablePKGRate_Components, '', array("class"=>"select2")) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="field-5" class="control-label">Time of Day*</label>
                        {{ Form::select('TimeOfDay', $Timezones, '', array("class"=>"select2")) }}
                    </div>
                </div>
            </div>
           </form>
    @endif

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(".saveall.btn").click(function(e){

                @if($rateGenerator->SelectType != 1)
                    var Origination = $("#rategenerator-code-from input[name='Origination']").val();
                    var Component   = $("#rategenerator-code-from select[name='Component']").val();
                    var TimeOfDay   = $("#rategenerator-code-from select[name='TimeOfDay']").val();

                    if(Origination == '' && Component == '' && TimeOfDay == ''){
                        setTimeout(function(){$('.btn').button('reset');},10);
                        toastr.error("Please Select Origination, Component, Time of Day", "Error", toastr_opts);
                        return false;
                    }
                @endif
                var _url = $("#rategenerator-code-from").attr("action");
                submit_ajax(_url,$("#rategenerator-code-from").serialize());

                return false;
            });

        });
    </script>
@stop
