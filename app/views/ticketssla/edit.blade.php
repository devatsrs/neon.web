@extends('layout.main')
@section('content')
<style>
.day_time_data .col-sm-4{padding-right:0px !important; width:25%; }
.NotrespondOnTimeDiv{display:none;} .NotresolveOnTimeDiv{display:none;}
</style>
<ol class="breadcrumb bc-3">
  <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li> <a href="{{URL::to('/tickets/sla_policies')}}">Ticket SLA Policies</a> </li>
  <li><a><span>{{slapolicies_dropbox($Sla->TicketSlaID)}}</span></a></li>
  <li class="active"> <strong>Edit SLA Policy</strong> </li>
</ol>
<h3>Edit SLA Policy</h3>
<div class="panel-title"> @include('includes.errors')
  @include('includes.success') </div>
<p style="text-align: right;">
  <button type='button' class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
  <a href="{{URL::to('/tickets/sla_policies')}}" class="btn btn-danger btn-sm btn-icon icon-left"> <i class="entypo-cancel"></i> Close </a> </p>
<br>
<div class="row">
  <div class="col-md-12">
    <form role="form" id="form-sla-add" method="post" action="{{URL::to('ticketgroups/create')}}" class="form-horizontal form-groups-bordered">
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title">Detail</div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body">
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">Name</label>
            <div class="col-sm-9">
              <input type="text" name='Name' class="form-control" id="Name" placeholder="Name" value="{{$Sla->Name}}">
            </div>
          </div>
          <div class="form-group">
            <label for="GroupDescription" class="col-sm-3 control-label">Description</label>
            <div class="col-sm-9">
              <textarea id="Description" name="Description" class="form-control" placeholder="Description">{{$Sla->Description}}</textarea>
            </div>
          </div>
          @if(!$Sla->IsDefault)
          <div class="form-group">
            <label class="col-sm-3 control-label">Status</label>
            <div class="col-sm-9">
              <p class="make-switch switch-small">
                    <input id="Status" name="Status"  @if($Sla->Status) checked @endif type="checkbox"  value="1">
                  </p>
            </div>
          </div>
          @else
          <input id="Status" name="Status" type="hidden"  value="1">
         @endif 
        </div>
      </div>
      <div class="panel panel-primary sla_targets" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title">SLA Targets</div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body">
          <div>
            <div class="custom_hours form-group">
              <div class="col-sm-1"><b>Priority</b></div>
              <div class="day_time_data col-sm-11">
                <div class="col-sm-3"><b>Respond within</b></div>
                <div class="col-sm-3"><b>Resolve within </b></div>
                <div class="col-sm-3"><b>Operational Hrs</b></div>
                <div class="col-sm-3"><b>Escalation Email</b></div>
              </div>
            </div>
            <div class="custom_hours form-group">
              <div class="col-sm-1"><b>Urgent</b></div>
              <div class="day_time_data col-sm-11">
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[Urgent][RespondTime]", isset($targetsData['Urgent']['RespondTime'])?$targetsData['Urgent']['RespondTime']:TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div>
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[Urgent][RespondType]",TicketSla::$SlaTargetTime,isset($targetsData['Urgent']['RespondType'])?$targetsData['Urgent']['RespondType']:0,array("class"=>"form-control   small select2",1))}}</div>
                </div>
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[Urgent][ResolveTime]", isset($targetsData['Urgent']['ResolveTime'])?$targetsData['Urgent']['ResolveTime']:TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div> 
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[Urgent][ResolveType]",TicketSla::$SlaTargetTime, isset($targetsData['Urgent']['ResolveType'])?$targetsData['Urgent']['ResolveType']:0,array("class"=>"form-control   small select2"))}}</div>
                </div>
                <div class="col-sm-3">{{Form::select("Target[Urgent][SlaOperationalHours]",TicketSla::$SlaOperationalHours,isset($targetsData['Urgent']['SlaOperationalHours'])?$targetsData['Urgent']['SlaOperationalHours']:0,array("class"=>"form-control   small select2",1))}}</div>
                <div class="col-sm-2">
                  <p class="make-switch switch-small">
                    <input id="" name="Target[Urgent][Escalationemail]" type="checkbox" @if($targetsData['Urgent']['Escalationemail']) checked @endif value="1">
                  </p>
                </div>
              </div>
            </div>
            <div class="custom_hours form-group">
              <div class="col-sm-1"><b>High</b></div>
              <div class="day_time_data col-sm-11">
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[High][RespondTime]", isset($targetsData['High']['RespondTime'])?$targetsData['High']['RespondTime']:TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div>
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[High][RespondType]",TicketSla::$SlaTargetTime,isset($targetsData['High']['RespondType'])?$targetsData['High']['RespondType']:0,array("class"=>"form-control   small select2",1))}}</div>
                </div>
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[High][ResolveTime]", isset($targetsData['High']['ResolveTime'])?$targetsData['High']['ResolveTime']:TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div> 
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[High][ResolveType]",TicketSla::$SlaTargetTime, isset($targetsData['High']['ResolveType'])?$targetsData['High']['ResolveType']:0,array("class"=>"form-control   small select2"))}}</div>
                </div>
                <div class="col-sm-3">{{Form::select("Target[High][SlaOperationalHours]",TicketSla::$SlaOperationalHours,isset($targetsData['High']['SlaOperationalHours'])?$targetsData['High']['SlaOperationalHours']:0,array("class"=>"form-control   small select2",1))}}</div>
                <div class="col-sm-2">
                  <p class="make-switch switch-small">
                    <input id="" name="Target[High][Escalationemail]" type="checkbox" @if($targetsData['High']['Escalationemail']) checked @endif value="1">
                  </p>
                </div>
              </div>
            </div>
            <div class="custom_hours form-group">
              <div class="col-sm-1"><b>Medium</b></div>
              <div class="day_time_data col-sm-11">
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[Medium][RespondTime]", isset($targetsData['Medium']['RespondTime'])?$targetsData['Medium']['RespondTime']:TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div>
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[Medium][RespondType]",TicketSla::$SlaTargetTime,isset($targetsData['Medium']['RespondType'])?$targetsData['Medium']['RespondType']:0,array("class"=>"form-control   small select2",1))}}</div>
                </div>
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[Medium][ResolveTime]", isset($targetsData['Medium']['ResolveTime'])?$targetsData['Medium']['ResolveTime']:TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div> 
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[Medium][ResolveType]",TicketSla::$SlaTargetTime, isset($targetsData['Medium']['ResolveType'])?$targetsData['Medium']['ResolveType']:0,array("class"=>"form-control   small select2"))}}</div>
                </div>
                <div class="col-sm-3">{{Form::select("Target[Medium][SlaOperationalHours]",TicketSla::$SlaOperationalHours,isset($targetsData['Medium']['SlaOperationalHours'])?$targetsData['Medium']['SlaOperationalHours']:0,array("class"=>"form-control   small select2",1))}}</div>
                <div class="col-sm-2">
                  <p class="make-switch switch-small">
                    <input id="" name="Target[Medium][Escalationemail]" type="checkbox" @if($targetsData['Medium']['Escalationemail']) checked @endif value="1">
                  </p>
                </div>
              </div>
            </div>
            <div class="custom_hours form-group">
              <div class="col-sm-1"><b>Low</b></div>
              <div class="day_time_data col-sm-11">
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[Low][RespondTime]", isset($targetsData['Low']['RespondTime'])?$targetsData['Low']['RespondTime']:TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div>
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[Low][RespondType]",TicketSla::$SlaTargetTime,isset($targetsData['Low']['RespondType'])?$targetsData['Low']['RespondType']:0,array("class"=>"form-control   small select2",1))}}</div>
                </div>
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[Low][ResolveTime]", isset($targetsData['Low']['ResolveTime'])?$targetsData['Low']['ResolveTime']:TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div> 
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[Low][ResolveType]",TicketSla::$SlaTargetTime, isset($targetsData['Low']['ResolveType'])?$targetsData['Low']['ResolveType']:0,array("class"=>"form-control   small select2"))}}</div>
                </div>
                <div class="col-sm-3">{{Form::select("Target[Low][SlaOperationalHours]",TicketSla::$SlaOperationalHours,isset($targetsData['Low']['SlaOperationalHours'])?$targetsData['Low']['SlaOperationalHours']:0,array("class"=>"form-control   small select2",1))}}</div>
                <div class="col-sm-2">
                  <p class="make-switch switch-small">
                    <input id="" name="Target[Low][Escalationemail]" type="checkbox" @if($targetsData['Low']['Escalationemail']) checked @endif value="1">
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title">Apply this to</div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body">
          <div class="custom_hours form-group">
            <div class="col-sm-2"><b>Group</b></div>
            <div class="day_time_data col-sm-10">  {{Form::select("Apply[Groups][]", $Groups, $slaApplyGroup ,array("class"=>"select2","multiple"=>"multiple","id"=>"Groups"))}} </div>
          </div>
          <div class="custom_hours form-group">
            <div class="col-sm-2"><b>Type</b></div>
            <div class="day_time_data col-sm-10">  {{Form::select("Apply[TicketTypes][]", $TicketTypes, $slaApplyType ,array("class"=>"select2","multiple"=>"multiple","id"=>"Groups"))}}  </div>
          </div>
          <div class="custom_hours form-group">
            <div class="col-sm-2"><b>Company</b></div>
            <div class="day_time_data col-sm-10"> {{Form::select("Apply[Accounts][]", $AccountList, $slaApplyCompany ,array("class"=>"select2","multiple"=>"multiple","id"=>"Groups"))}} </div>
          </div>
        </div>
      </div>
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title">What happens when this SLA is violated?</div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body">
          <div class="custom_hours form-group">
            <div class="col-sm-12">Set escalation rule when a ticket is not responded on time <span>&nbsp;
            <p class="make-switch switch-small">
                    <input id="NotrespondOnTime" @if(count($RespondedVoilation)>0) checked @endif name="NotrespondOnTime" type="checkbox" value="1">
                  </p>
            </span></div>
          </div>
          <div @if(count($RespondedVoilation)>0) style="display:block;" @endif class="custom_hours NotrespondOnTimeDiv form-group"> 
            <?php $RespondedValue  = explode(",",$RespondedVoilation['Value']);  ?>
            <div class="col-sm-2">{{Form::select("violated[NotResponded][EscalateTime]", $EscalateTime,$RespondedVoilation['Time'],array("class"=>"form-control   small select2",1))}}</div>
            <div class="day_time_data col-sm-8"> {{Form::select("violated[NotResponded][Agents][]", $agentsAll, $RespondedValue ,array("class"=>"select2","multiple"=>"multiple","id"=>"Groups"))}} </div>
          </div>
          <div class="custom_hours form-group">
            <div class="col-sm-12">Set escalation hierarchy when a ticket is not resolved on time <span>&nbsp;
            <p class="make-switch switch-small">
                    <input id="NotresolveOnTime" @if(count($ResolveVoilation)>0) checked @endif  name="NotresolveOnTime" type="checkbox" value="1">
                  </p>
            </span></div>
          </div>
          <!--first start -->
          
          <div @if(count($ResolveVoilation)>0) style="display:block;" @endif class="custom_hours NotresolveOnTimeDiv form-group">
            <div class="col-sm-1"><span class="badge badge-default">1</span></div>
            <div class="col-sm-1"><input  type="checkbox" @if(count($ResolveVoilation)>0) checked @endif class="icheck violatedCheck" option="1" name="violated[NotResolved][1][Enabled]" value="1"></div>
            
            <div class="col-sm-2">{{Form::select("violated[NotResolved][1][EscalateTime]",$EscalateTime,isset($ResolveVoilation[0])?$ResolveVoilation[0]->Time:1,array("class"=>"form-control   violated1 small select2",isset($ResolveVoilation[0])?"":"disabled"  ))}}</div>
           
            <div class="day_time_data col-sm-8"> {{Form::select("violated[NotResolved][1][Agents][]", $agentsAll, isset($ResolveVoilation[0])?explode(",",$ResolveVoilation[0]->Value):0 ,array("class"=>"select2 violated1","multiple"=>"multiple","id"=>"Groups", isset($ResolveVoilation[0])?"":"disabled"))}} </div>
          </div>
          <!--first end -->
          <!--second start -->
          
          <div @if(count($ResolveVoilation)>0) style="display:block;" @endif class="custom_hours NotresolveOnTimeDiv form-group">
            <div class="col-sm-1"><span class="badge badge-default">2</span></div>
            <div class="col-sm-1"><input  type="checkbox" @if(count($ResolveVoilation)>1) checked @endif class="icheck violatedCheck" option="2" name="violated[NotResolved][2][Enabled]" value="1"></div>
            
               <div class="col-sm-2">{{Form::select("violated[NotResolved][2][EscalateTime]",$EscalateTime,isset($ResolveVoilation[1])?$ResolveVoilation[1]->Time:1,array("class"=>"form-control   violated2 small select2",isset($ResolveVoilation[1])?"":"disabled"  ))}}</div>
               
            <div class="day_time_data col-sm-8"> {{Form::select("violated[NotResolved][2][Agents][]", $agentsAll, isset($ResolveVoilation[1])?explode(",",$ResolveVoilation[1]->Value):0 ,array("class"=>"select2 violated2","multiple"=>"multiple","id"=>"Groups", isset($ResolveVoilation[1])?"":"disabled"))}} </div>           
            
          </div>
          <!--second end -->
          <!--third start -->
          <div @if(count($ResolveVoilation)>0) style="display:block;" @endif class="custom_hours NotresolveOnTimeDiv form-group">
            <div class="col-sm-1"><span class="badge badge-default">3</span></div>
            <div class="col-sm-1"><input  type="checkbox" @if(count($ResolveVoilation)>2) checked @endif class="icheck violatedCheck" option="3" name="violated[NotResolved][3][Enabled]" value="1"></div>
            
              <div class="col-sm-2">{{Form::select("violated[NotResolved][3][EscalateTime]",$EscalateTime,isset($ResolveVoilation[2])?$ResolveVoilation[2]->Time:1,array("class"=>"form-control   violated3 small select2",isset($ResolveVoilation[2])?"":"disabled"  ))}}</div>
              
            <div class="day_time_data col-sm-8"> {{Form::select("violated[NotResolved][3][Agents][]", $agentsAll, isset($ResolveVoilation[2])?explode(",",$ResolveVoilation[2]->Value):0 ,array("class"=>"select2 violated3","multiple"=>"multiple","id"=>"Groups", isset($ResolveVoilation[2])?"":"disabled"))}} </div> 
            
          </div>
          <!--third end-->
          <!--fourth start -->
          
          <div @if(count($ResolveVoilation)>0) style="display:block;" @endif class="custom_hours NotresolveOnTimeDiv form-group">
            <div class="col-sm-1"><span class="badge badge-default">4</span></div>
            <div class="col-sm-1"><input  type="checkbox" @if(count($ResolveVoilation)>3) checked @endif  class="icheck violatedCheck" option="4" name="violated[NotResolved][4][Enabled]" value="1"></div>
             
               <div class="col-sm-2">{{Form::select("violated[NotResolved][4][EscalateTime]",$EscalateTime,isset($ResolveVoilation[3])?$ResolveVoilation[3]->Time:1,array("class"=>"form-control   violated4 small select2",isset($ResolveVoilation[3])?"":"disabled"  ))}}</div>
               
            <div class="day_time_data col-sm-8"> {{Form::select("violated[NotResolved][4][Agents][]", $agentsAll, isset($ResolveVoilation[3])?explode(",",$ResolveVoilation[3]->Value):0 ,array("class"=>"select2 violated4","multiple"=>"multiple","id"=>"Groups", isset($ResolveVoilation[3])?"":"disabled"))}} </div> 
            
          </div>
          <!--fourth end -->
          
        </div>
      </div>
    </form>
  </div>
</div>
<script>

	 jQuery(document).ready(function ($) {
     var sla_policiesID = '{{$Sla->TicketSlaID}}';

	 $(".save.btn").click(function (ev) {
            $("#form-sla-add").submit();
            $(this).button('Loading');
        });
		
		$('#NotrespondOnTime').change(function(e) {
            $('.NotrespondOnTimeDiv').toggle();
        });
		
		$('#NotresolveOnTime').change(function(e) {
            $('.NotresolveOnTimeDiv').toggle();
        });
		
	jQuery('.violatedCheck').on('ifChecked', function(event){	 
			  var selected_option = 	$(this).attr('option');
			  if(selected_option){				 
					$('.violated'+selected_option).prop('disabled',false);
			  }
		});
		
		jQuery('.violatedCheck').on('ifUnchecked', function(event){			 		
			   var selected_option = 	$(this).attr('option');
			  if(selected_option){				 
					$('.violated'+selected_option).prop('disabled',true);						
			  }
		});	
			
	
	$("#form-sla-add").submit(function (event) {
            event.stopImmediatePropagation();
            event.preventDefault();					
			
		   var formData 	= 	$($('#form-sla-add')[0]).serializeArray();	 
		   var url 			= 	baseurl + '/tickets/sla_policies/' + sla_policiesID + '/update';
		   
		   	 $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
				data:formData,
				async :false,
                success: function(response) {
					
                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);      
						  window.location =  baseurl+"/tickets/sla_policies";               
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    $(".save.btn").button('reset');
                },
			});

        });
	});	
		
</script>
@stop