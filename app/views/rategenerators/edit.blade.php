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
<div class="float-right" >
    <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>

    <a href="{{URL::to('/rategenerators')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
</div>
<br>
<br>


<div class="clear  row">
    <div class="col-md-12">
        <form role="form" id="rategenerator-from" method="post" action="{{URL::to('rategenerators/'.$rategenerators->RateGeneratorId.'/update')}}" class="form-horizontal form-groups-bordered">
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Detail
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">

                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="RateGeneratorName" data-validate="required" data-message-required="." id="field-1" placeholder="" value="{{$rategenerators->RateGeneratorName}}" />
                        </div>

                        <label class="col-sm-2 control-label">Rate Position</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="RatePosition" data-validate="required" data-message-required="." id="field-1" placeholder="" value="{{$rategenerators->RatePosition}}" />

                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Use Average</label>
                        <div class="col-sm-4">
                            <div class="make-switch switch-small">
                                {{Form::checkbox('UseAverage', 1,  $rategenerators->UseAverage );}}
                            </div>
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Trunk</label>
                        <div class="col-sm-4">
                            {{ Form::select('TrunkID', $trunks, $rategenerators->TrunkID , array("class"=>"select2")) }}
                        </div>


                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">CodeDeck</label>
                        <div class="col-sm-4">
                                {{ Form::select('codedeckid', $codedecklist,  $rategenerators->CodeDeckId, array_merge( array("class"=>"select2"),$array_op)) }}
                            @if(isset($array_op['disabled']) && $array_op['disabled'] == 'disabled')
                                <input type="hidden" name="codedeckid" readonly  value="{{$rategenerators->CodeDeckId}}">
                            @endif
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Use Preference</label>
                        <div class="col-sm-4">
                            <div class="make-switch switch-small">
                                {{Form::checkbox('UsePreference', 1,  $rategenerators->UsePreference );}}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Currency</label>
                        <div class="col-sm-4">
                        <?php if($rategenerators->CurrencyID == ''){
                            unset($array_op['disabled']);
                        }
                        ?>
                                {{ Form::select('CurrencyID', $currencylist,  $rategenerators->CurrencyID, array_merge( array("class"=>"select2"),$array_op)) }}
                            @if(isset($array_op['disabled']) && $array_op['disabled'] == 'disabled')
                                <input type="hidden" name="CurrencyID" readonly  value="{{$rategenerators->CurrencyID}}">
                            @endif
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Policy</label>
                        <div class="col-sm-4">
                            {{ Form::select('Policy', LCR::$policy, $rategenerators->Policy , array("class"=>"select2")) }}
                        </div>
                    </div>

                </div>
            </div>

        </form>

        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Rules
                </div>

                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>



            <div class="panel-body">



                

                    
                        
                            
                            
                            
                            <div class="float-right  ">
                                <button type="submit"  class="btn addnew btn-primary btn-sm btn-icon icon-left" >
                                    <i class="entypo-floppy"></i>
                                    Add New
                                </button>
                                <br><br>
                            </div>
                
                

                    @if(count($rategenerator_rules))
                    <table class="table table-bordered datatable" id="table-4">
                        <thead>
                            <tr>
                                <th>Rate Filter
                                </th>
                                <th>Sources</th>
                                <th>Margins</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        @foreach($rategenerator_rules as $rategenerator_rule)
                        <tbody>
                            <tr class="odd gradeX">
                                <td>
                                    {{$rategenerator_rule->Code}} 
                                </td>
                                <td>
                                    @if(count($rategenerator_rule['RateRuleSource']))
                                    @foreach($rategenerator_rule['RateRuleSource'] as $rateruleSource )
                                    {{Account::getCompanyNameByID($rateruleSource->AccountId);}}<br>
                                    @endforeach
                                    @endif

                                </td>
                                <td>

                                    @if(count($rategenerator_rule['RateRuleMargin']))
                                    @foreach($rategenerator_rule['RateRuleMargin'] as $index=>$materulemargin )
                                        {{$materulemargin->MinRate}} {{$index!=0?'<':'<='}}  rate <= {{$materulemargin->MaxRate}} {{$materulemargin->AddMargin}} <br>
                                    @endforeach
                                    @endif



                                </td>
                                <td>
                                    <a href="{{URL::to('/rategenerators/rules/'.$id. '/edit/' . $rategenerator_rule->RateRuleId )}}" id="add-new-margin" class="update btn btn-primary btn-sm btn-icon icon-left">
                                        <i class="entypo-floppy"></i>
                                        Edit
                                    </a>

                                    <a href="{{URL::to('/rategenerators/rules/'.$id.'/delete/'. $rategenerator_rule->RateRuleId)}}" class="btn delete btn-danger btn-sm btn-icon icon-left">
                                        <i class="entypo-cancel"></i>
                                        Delete
                                    </a>
                                </td>
                            </tr>

                        </tbody>
                        @endforeach
                    </table>


                    @endif

                </div>
            </div>
        </div>

    </div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        $(".btn.addnew").click(function(ev) {
            jQuery('#modal-rate-generator-rule').modal('show', {backdrop: 'static'});
        });

        $(".save.btn").click(function(ev) {
            $("#rategenerator-from").submit();
        });
    });
</script>
@include('includes.ajax_submit_script', array('formID'=>'rategenerator-from' , 'url' => ('rategenerators/'.$rategenerators->RateGeneratorId.'/update')))

@include('includes.errors')
@include('includes.success')

@stop
@section('footer_ext') @parent
<div class="modal fade" id="modal-rate-generator-rule">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="{{URL::to('rategenerators/' . $id . '/store_rule' )}}" id="insert-rate-generator-rule-form" method="post" >
                
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Rate Generator Rule</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-12">

                            <div class="form-group">
                                <label for="field-4" class="control-label">Code</label>

                                <input type="text" name="Code" class="form-control"  value="" />

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="save1 btn btn-primary btn-sm btn-icon icon-left">
                        <i class="entypo-floppy"></i> Save
                    </button>
                    <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i> Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop