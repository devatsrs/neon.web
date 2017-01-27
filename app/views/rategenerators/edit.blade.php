@extends('layout.main')

@section('content')


<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('/rategenerators')}}">Rate Generator</a>
    </li>
		<li>
        <a><span>{{rategenerators_dropbox($rategenerators->RateGeneratorId)}}</span></a>
    </li>
   <!-- <li class="active">
        <strong>{{!empty($rategenerator)?$rategenerator->RateGeneratorName:''}}</strong>
    </li>-->
    <li class="active">
        <strong>Edit Rate Generator</strong>
    </li>
    
</ol>
<h3> Update Rate Generator</h3>
<div class="float-right" >
@if($rategenerators->Status==1)
<button title="Deactivate" href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/change_status/0" class="btn btn-default change_status btn-danger btn-sm" data-loading-text="Loading..."><i class="entypo-minus-circled"></i></button>    
@elseif($rategenerators->Status==0)   
<button title="Activate" href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/change_status/1" class="btn btn-default change_status btn-success btn-sm" data-loading-text="Loading..."><i class="entypo-check"></i></button>
@endif
<a title="Delete" href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/delete" data-redirect="{{URL::to('/rategenerators')}}" data-id="{{$rategenerators->RateGeneratorId}}" class="btn btn-default btn-sm delete_rate btn-danger"><i class="entypo-trash"></i></a>
@if($rategenerators->Status==1)
<div class="btn-group">
<button href="#" class="btn generate btn-success btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Generate Rate Table <span class="caret"></span></button>
 <ul class="dropdown-menu dropdown-green" role="menu">
    <li><a href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/generate_rate_table/create" class="generate_rate create" >Create New Rate Table</a></li>
    <li><a href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/generate_rate_table/update" class="generate_rate update" data-trunk="{{$rategenerators->TrunkID}}" data-codedeck="{{$rategenerators->CodeDeckId}}" data-currency="{{$rategenerators->CurrencyID}}">Update Existing Rate Table</a></li>
  </ul>
  </div>
  @endif
<button type="button"  class="update_form btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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

                                <!--{ Form::select('CurrencyID', $currencylist,  $rategenerators->CurrencyID, array_merge( array("class"=>"select2"),$array_op)) }}-->
                            {{Form::SelectControl('currency',0,$rategenerators->CurrencyID,($rategenerators->CurrencyID==''?0:0))}}
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



                

                    
                        
                            
                            
                            
                            <div class="pull-right">
                                <button type="submit"  class="btn addnew btn-primary btn-sm btn-icon icon-left" >
                                    <i class="entypo-floppy"></i>
                                    Add New
                                </button>
                                <br><br>
                            </div>

                    @if(count($rategenerator_rules))
                        <div class="dataTables_wrapper clear-both">
                            <table class="table table-bordered datatable" id="table-4">
                                <thead>
                                    <tr>
                                        <th>Rate Filter</th>
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
                        </div>

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

        $(".update_form.btn").click(function(ev) {
            $("#rategenerator-from").submit();
        });
		
		        $(".btn.change_status").click(function (e) {
                        //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
                        $(this).button('loading');
                        $.ajax({
                            url: $(this).attr("href"),
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                $(this).button('reset');
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    location.reload();
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                            },

                            // Form data
                            //data: {},
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                        return false;
                    });
					
					$(".generate_rate.create").click(function (e) {
                        e.preventDefault();
                        $('#update-rate-generator-form').trigger("reset");
                        $('#modal-update-rate').modal('show', {backdrop: 'static'});
                        $('#RateTableIDid').hide();
                        $('#RateTableNameid').show();
                        $('#modal-update-rate h4').html('Generate Rate Table');
                        update_rate_table_url = $(this).attr("href");

                        return false;

                    });
					
		$('body').on('click', '.generate_rate.update', function (e) {
            e.preventDefault();
            $('#modal-update-rate').modal('show', {backdrop: 'static'});
            $('#update-rate-generator-form').trigger("reset");
            var trunkID = $(this).attr("data-trunk");
            var codeDeckId = $(this).attr("data-codedeck");
            var CurrencyID = $(this).attr("data-currency");
            $.ajax({
                url: baseurl + "/rategenerators/ajax_load_rate_table_dropdown",
                type: 'GET',
                dataType: 'text',
                success: function(response) {

                    $("#modal-update-rate #DropdownRateTableID").html('');
                    $("#modal-update-rate #DropdownRateTableID").html(response);
                    $("#modal-update-rate #DropdownRateTableID select.select2").addClass('visible');
                    $("#modal-update-rate #DropdownRateTableID select.select2").select2();

                },
                // Form data
                data: "TrunkID="+trunkID+'&CodeDeckId='+codeDeckId+'&CurrencyID='+CurrencyID ,
                cache: false,
                contentType: false,
                processData: false
            });
            /*
            * Submit and Generate Joblog
            * */
            update_rate_table_url = $(this).attr("href");
            $('#RateTableIDid').show();
            $('#RateTableNameid').hide();
            $('#modal-update-rate h4').html('Update Rate Table');
        });
		
		$('#update-rate-generator-form').submit(function (e) {
            e.preventDefault();
            if( typeof update_rate_table_url != 'undefined' && update_rate_table_url != '' ){
                $.ajax({
                    url: update_rate_table_url,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        $(".btn.generate").button('reset');
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            reloadJobsDrodown(0);
                            $('#modal-update-rate').modal('hide');
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $(".btn.generate").button('reset');
                        $(".save.TrunkSelect").button('reset');

                    },
                    // Form data
                    data: $('#update-rate-generator-form').serialize(),
                    cache: false

                });
            }else{
                $(".btn").button('reset');
                $('#modal-update-rate').modal('hide');
                toastr.info('Nothing Changed. Try again', "info", toastr_opts);
            }
        });
		
		
		$(".btn.delete_rate").click(function (e) {
                        e.preventDefault();
                        var id = $(this).attr('data-id');
                        var url = baseurl + '/rategenerators/'+id+'/ajax_existing_rategenerator_cronjob';
                        $('#delete-rate-generator-form [name="RateGeneratorID"]').val(id);
                        if(confirm('Are you sure you want to delete selected rate generator?')) {
                            $.ajax({
                                url: url,
                                type: 'POST',
                                dataType: 'html',
                                success: function (response) {
                                    $(".btn.delete_rate").button('reset');
                                    if (response) {
                                        $('#modal-delete-rategenerator .container').html(response);
                                        $('#modal-delete-rategenerator').modal('show');
                                    }else{
                                        $('#delete-rate-generator-form').submit();
                                    }
                                },

                                // Form data
                                //data: {},
                                cache: false,
                                contentType: false,
                                processData: false
                            });
                        }
                        return false;

                    });
					
					
        $('#delete-rate-generator-form').submit(function (e) {
            e.preventDefault();
            if($('#modal-delete-rategenerator .container').is(':empty')) {
                var RateGeneratorID = $(this).find('[name="RateGeneratorID"]').val();
                var url = baseurl + '/rategenerators/' + RateGeneratorID + '/delete';
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);                          
                            $('#modal-delete-rategenerator').modal('hide');
							window.location = "{{URL::to('/rategenerators')}}";
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $(".save.TrunkSelect").button('reset');

                    },
                    // Form data
                    data: $('#update-rate-generator-form').serialize(),
                    cache: false

                });
            }else{
                var SelectedIDs = getselectedIDs("cronjob-table");
                if (SelectedIDs.length == 0) {
                    alert('No cron job selected.');
                    $("#rategenerator-select").button('reset');
                    return false;
                }else{
                    var deleteid = SelectedIDs.join(",");
                    cronjobsdelete(deleteid);
                }
            }
        });

        function cronjobsdelete(deleteid){
            if(confirm('Are you sure you want to delete selected cron job?')){
                var rateGeneratorID = $('#delete-rate-generator-form [name="RateGeneratorID"]').val();
                var url = baseurl + "/rategenerators/"+rateGeneratorID+"/deletecronjob";
                var cronjobs = deleteid;
                $('#modal-delete-rategenerator .container').html('');
                $('#modal-delete-rategenerator').modal('hide');
                $.ajax({
                    url: url,
                    type:'POST',
                    data:{cronjobs:cronjobs},
                    datatype:'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            toastr.success(response.message,'Success', toastr_opts);
                            var url = baseurl + '/rategenerators/'+rateGeneratorID+'/ajax_existing_rategenerator_cronjob';
                            $('#delete-rate-generator-form [name="RateGeneratorID"]').val(rateGeneratorID);
                            $.ajax({
                                url: url,
                                type: 'POST',
                                dataType: 'html',
                                success: function (response) {
                                    $(".btn.delete_rate").button('reset');
                                    if (response) {
                                        $('#modal-delete-rategenerator .container').html(response);
                                        $('#modal-delete-rategenerator').modal('show');
                                    }else{
                                        $('#delete-rate-generator-form').submit();
                                    }
                                },

                                // Form data
                                //data: {},
                                cache: false,
                                contentType: false,
                                processData: false
                            });
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    }

                });
            }
        }
		
		   function getselectedIDs(table){
            var SelectedIDs = [];
            $('#'+table+' tr .rowcheckbox:checked').each(function (i, el) {
                var cronjob = $(this).val();
                SelectedIDs[i++] = cronjob;
            });
            return SelectedIDs;
        }
		
		  $(document).on('click','#selectall',function(){
            if($(this).is(':checked')){
                checked = 'checked=checked';
                $(this).prop("checked", true);
                $(this).parents('table').find('tbody tr').each(function (i, el) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                    $(this).addClass('selected');
                });
            }else{
                checked = '';
                $(this).prop("checked", false);
                $(this).parents('table').find('tbody tr').each(function (i, el) {
                    $(this).find('.rowcheckbox').prop("checked", false);
                    $(this).removeClass('selected');
                });
            }
        });
    });
</script>
@include('includes.ajax_submit_script', array('formID'=>'rategenerator-from' , 'url' => ('rategenerators/'.$rategenerators->RateGeneratorId.'/update')))

@include('includes.errors')
@include('includes.success')
@include('currencies.currencymodal')
@stop
@section('footer_ext') @parent
@include('rategenerators.rategenerator_models')
@stop