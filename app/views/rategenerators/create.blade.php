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
        <strong>Create Rate Generator</strong>
    </li>
</ol>
<h3>Create Rate Generator</h3>
<form role="form" id="rategenerator-from" method="post" action="{{URL::to('/rategenerators/store')}}" class="form-horizontal form-groups-bordered">
<div class="float-right">
    <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>

    <a href="{{URL::to('/rategenerators')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
</div>
<br/>




<div class="row">
    <div class="panel-body">
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

                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="RateGeneratorName" data-validate="required" data-message-required="." id="field-1" placeholder="" value="{{Input::old('RateGeneratorName')}}" />
                        </div>

                        <label class="col-sm-2 control-label">Rate Position</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="RatePosition" data-validate="required" data-message-required="." id="field-1" placeholder="" value="{{Input::old('RatePosition')}}" />

                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Use Average</label>
                        <div class="col-sm-4">
                            <div class="make-switch switch-small">
                                {{Form::checkbox('UseAverage', 1,  FALSE );}}
                            </div>
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Trunk</label>
                        <div class="col-sm-4">
                            {{Form::SelectControl('trunk')}}
                           <!-- { Form::select('TrunkID', $trunks, $trunk_keys, array("class"=>"select2")) }}-->
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">CodeDeck</label>
                        <div class="col-sm-4">
                                {{ Form::select('codedeckid', $codedecklist, Input::get('codedeckid') , array("class"=>"select2")) }}
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Use Preference</label>
                            <div class="col-sm-4">
                                <div class="make-switch switch-small">
                                    {{Form::checkbox('UsePreference', 1, '');}}
                                </div>
                            </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Currency</label>
                        <div class="col-sm-4">
                            {{Form::SelectControl('currency')}}
                               <!-- { Form::select('CurrencyID', $currencylist,  '', array_merge( array("class"=>"select2"))) }}-->
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Policy</label>
                        <div class="col-sm-4">
                            {{ Form::select('Policy', LCR::$policy, LCR::LCR_PREFIX , array("class"=>"select2")) }}
                        </div>

                    </div>
                    {{--<input type="hidden" name="GroupBy" value="Code">--}}

                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Group By</label>
                        <div class="col-sm-4">
                            {{ Form::select('GroupBy', array('Code'=>'Code','Desc'=>'Description'), 'Code' , array("class"=>"select2")) }}
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Timezones</label>
                        <div class="col-sm-4">
                            {{ Form::select('Timezones[]', $Timezones, [1] , array("class"=>"select2 multiselect", "multiple"=>"multiple")) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Merge Rate By Timezones</label>
                        <div class="col-sm-4">
                            <div class="make-switch switch-small">
                                {{Form::checkbox('IsMerge', 1,  false, array('id' => 'IsMerge') );}}
                            </div>
                        </div>
                        <label class="col-sm-2 control-label IsMerge">Take Price</label>
                        <div class="col-sm-4 IsMerge">
                            {{ Form::select('TakePrice', array(RateGenerator::HIGHEST_PRICE=>'Highest Price',RateGenerator::LOWEST_PRICE=>'Lowest Price'), 0 , array("class"=>"select2")) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label IsMerge">Merge Into</label>
                        <div class="col-sm-4 IsMerge">
                            {{ Form::select('MergeInto', $Timezones, null , array("class"=>"select2")) }}
                        </div>
                    </div>
                </div>
            </div>

    </div>
</div>
</form>
<style>
    .IsMerge {
        display: none;
    }
</style>
<script type="text/javascript">
    function ajax_form_success(response){
        if(typeof response.redirect != 'undefined' && response.redirect != ''){
            window.location = response.redirect;
        }
     }
    /*jQuery(document).ready(function($) {
        $(".save.btn").click(function(ev) {
            $("#rategenerator-from").submit();
        });
    });*/
    $(document).ready(function() {
        $('#IsMerge').on('change', function(e, s) {
            if($('#IsMerge').is(':checked')) {
                $('.IsMerge').show();
            } else {
                $('.IsMerge').hide();
            }
        });
    });
</script>
@include('currencies.currencymodal')
@include('trunk.trunkmodal')
@include('includes.ajax_submit_script', array('formID'=>'rategenerator-from' , 'url' => ('/rategenerators/store'),'update_url'=>'rategenerators/{id}/update'))
@stop         
