@extends('layout.main')
@section('content')
<style>
.day_time_data .col-sm-4{padding-right:0px !important; width:25%; } .NotrespondOnTimeDiv{display:none;} .NotresolveOnTimeDiv{display:none;}
</style>
<ol class="breadcrumb bc-3">
  <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li> <a href="{{URL::to('/tickets/sla_policies')}}">Ticket SLA Policies</a> </li>
  <li class="active"> <strong>New SLA Policy</strong> </li>
</ol>
<h3>New SLA Policy</h3>
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
              <input type="text" name='Name' class="form-control" id="Name" placeholder="Name" value="{{Input::old('Name')}}">
            </div>
          </div>
          <div class="form-group">
            <label for="GroupDescription" class="col-sm-3 control-label">Description</label>
            <div class="col-sm-9">
              <textarea id="Description" name="Description" class="form-control" placeholder="Description">{{Input::old('Description')}}</textarea>
            </div>
          </div>          
          <div class="form-group">
            <label class="col-sm-3 control-label">Status</label>
            <div class="col-sm-9">
              <p class="make-switch switch-small">
                    <input id="Status" name="Status" type="checkbox" checked value="1">
                  </p>
            </div>
          </div>
        </div>
      </div>
      <div class="panel panel-primary sla_targets" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title">SLA Targets <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="A service level agreement (SLA) policy lets you set standards of performance for your support team. You can set SLA policies for the time within which agents should respond to, and resolve tickets based on ticket priorities. You can choose whether you want each SLA rule to be calculated over calendar hours or your business hours." data-original-title="SLA Policy" class="label label-info popover-primary">?</span></div>
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
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[Urgent][RespondTime]", TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div>
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[Urgent][RespondType]", TicketSla::$SlaTargetTime,TicketSla::$TargetDefault,array("class"=>"form-control   small select2",1))}}</div>
                </div>
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[Urgent][ResolveTime]", TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div>
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[Urgent][ResolveType]", TicketSla::$SlaTargetTime,TicketSla::$TargetDefault,array("class"=>"form-control   small select2",1))}}</div>
                </div>
                <div class="col-sm-3">{{Form::select("Target[Urgent][SlaOperationalHours]",TicketSla::$SlaOperationalHours,'',array("class"=>"form-control   small select2",1))}}</div>
                <div class="col-sm-2">
                  <p class="make-switch switch-small">
                    <input id="" name="Target[Urgent][Escalationemail]" type="checkbox" checked value="1">
                  </p>
                </div>
              </div>
            </div>
            <div class="custom_hours form-group">
              <div class="col-sm-1"><b>High</b></div>
              <div class="day_time_data col-sm-11">
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[High][RespondTime]", TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div>
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[High][RespondType]", TicketSla::$SlaTargetTime,TicketSla::$TargetDefault,array("class"=>"form-control   small select2"))}}</div>
                </div>
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[High][ResolveTime]", TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div>
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[High][ResolveType]", TicketSla::$SlaTargetTime,TicketSla::$TargetDefault,array("class"=>"form-control   small select2"))}}</div>
                </div>
                <div class="col-sm-3">{{Form::select("Target[High][SlaOperationalHours]",TicketSla::$SlaOperationalHours,'',array("class"=>"form-control   small select2",1))}}</div>
                <div class="col-sm-2">
                  <p class="make-switch switch-small">
                    <input id="" name="Target[High][Escalationemail]" type="checkbox" checked value="1">
                  </p>
                </div>
              </div>
            </div>
            <div class="custom_hours form-group">
              <div class="col-sm-1"><b>Medium</b></div>
              <div class="day_time_data col-sm-11">
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[Medium][RespondTime]", TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div>
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[Medium][RespondType]", TicketSla::$SlaTargetTime,TicketSla::$TargetDefault,array("class"=>"form-control   small select2",1))}}</div>
                </div>
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[Medium][ResolveTime]", TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div>
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[Medium][ResolveType]", TicketSla::$SlaTargetTime,TicketSla::$TargetDefault,array("class"=>"form-control   small select2",1))}}</div>
                </div>
                <div class="col-sm-3">{{Form::select("Target[Medium][SlaOperationalHours]",TicketSla::$SlaOperationalHours,'',array("class"=>"form-control   small select2",1))}}</div>
                <div class="col-sm-2">
                  <p class="make-switch switch-small">
                    <input id="" name="Target[Medium][Escalationemail]" type="checkbox" checked value="1">
                  </p>
                </div>
              </div>
            </div>
            <div class="custom_hours form-group">
              <div class="col-sm-1"><b>Low</b></div>
              <div class="day_time_data col-sm-11">
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[Low][RespondTime]", TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div>
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[Low][RespondType]", TicketSla::$SlaTargetTime,TicketSla::$TargetDefault,array("class"=>"form-control   small select2",1))}}</div>
                </div>
                <div class="col-sm-3">
                  <div class="col-sm-3 no-padding no-margin">{{Form::text("Target[Low][ResolveTime]", TicketSla::$SlaTargetTimeValue,array("class"=>"form-control   small " , "data-mask"=>"decimal"))}}</div>
                  <div class="col-sm-6 no-padding no-margin">{{Form::select("Target[Low][ResolveType]", TicketSla::$SlaTargetTime,TicketSla::$TargetDefault,array("class"=>"form-control   small select2",1))}}</div>
                </div>
                <div class="col-sm-3">{{Form::select("Target[Low][SlaOperationalHours]",TicketSla::$SlaOperationalHours,'',array("class"=>"form-control   small select2",1))}}</div>
                <div class="col-sm-2">
                  <p class="make-switch switch-small">
                    <input id="" name="Target[Low][Escalationemail]" type="checkbox" checked value="1">
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
            <div class="day_time_data col-sm-10">  {{Form::select("Apply[Groups][]", $Groups, '' ,array("class"=>"select2","multiple"=>"multiple","id"=>"Groups"))}} </div>
          </div>
          <div class="custom_hours form-group">
            <div class="col-sm-2"><b>Type</b></div>
            <div class="day_time_data col-sm-10">  {{Form::select("Apply[TicketTypes][]", $TicketTypes, '' ,array("class"=>"select2","multiple"=>"multiple","id"=>"Groups"))}}  </div>
          </div>
          <div class="custom_hours form-group">
            <div class="col-sm-2"><b>Company</b></div>
            <div class="day_time_data col-sm-10"> {{Form::select("Apply[Accounts][]", $AccountList, '' ,array("class"=>"select2","multiple"=>"multiple","id"=>"Groups"))}} </div>
          </div>
        </div>
      </div>
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title">What happens when this SLA is violated? <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="You can also set up escalation rules that notify agents or managers when SLAs have been violated. You can set up multiple levels of escalation for resolution SLA. The violation emails can be configured in Admin > Email Templates" data-original-title="SLA violation notifications" class="label label-info popover-primary">?</span></div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body">
          <div class="custom_hours form-group">
            <div class="col-sm-12">Set escalation rule when a ticket is not responded on time <span>&nbsp;
            <p class="make-switch switch-small">
                    <input id="NotrespondOnTime" name="NotrespondOnTime" type="checkbox" value="1">
                  </p>
            </span></div>
          </div>
          <div class="custom_hours  NotrespondOnTimeDiv form-group">
            
            <div class="col-sm-2">{{Form::select("violated[NotResponded][EscalateTime]", $EscalateTime,1,array("class"=>"form-control   small select2",1))}}</div>
            <div class="day_time_data col-sm-8"> {{Form::select("violated[NotResponded][Agents][]", $agentsAll, 0 ,array("class"=>"select2","multiple"=>"multiple","id"=>"Groups"))}} </div>
          </div>
          <div class="custom_hours form-group">
            <div class="col-sm-12">Set escalation hierarchy when a ticket is not resolved on time <span>&nbsp;
            <p class="make-switch switch-small">
                    <input id="NotresolveOnTime" name="NotresolveOnTime" type="checkbox" value="1">
                  </p>
            </span></div>
          </div>
          <div class="custom_hours NotresolveOnTimeDiv form-group">
            <div class="col-sm-1"><span class="badge badge-default">1</span></div>
            <div class="col-sm-1"><input  type="checkbox"  class="icheck violatedCheck" option="1" name="violated[NotResolved][1][Enabled]" value="1"></div>
            <div class="col-sm-2">{{Form::select("violated[NotResolved][1][EscalateTime]",$EscalateTime,1,array("class"=>"form-control   violated1 small select2","disabled"))}}</div>
            <div class="day_time_data col-sm-8"> {{Form::select("violated[NotResolved][1][Agents][]", $agentsAll, 0 ,array("class"=>"select2 violated1","multiple"=>"multiple","id"=>"Groups","disabled"))}} </div>
          </div>
          <div class="custom_hours NotresolveOnTimeDiv form-group">
            <div class="col-sm-1"><span class="badge badge-default">2</span></div>
            <div class="col-sm-1"><input  type="checkbox" class="icheck violatedCheck" option="2" name="violated[NotResolved][2][Enabled]" value="1"></div>
            <div class="col-sm-2">{{Form::select("violated[NotResolved][2][EscalateTime]",$EscalateTime,1,array("class"=>"form-control violated2  small select2","disabled"))}}</div>
            <div class="day_time_data col-sm-8"> {{Form::select("violated[NotResolved][2][Agents][]", $agentsAll, 0 ,array("class"=>"select2 violated2","multiple"=>"multiple","id"=>"Groups","disabled"))}} </div>
          </div>
          <div class="custom_hours NotresolveOnTimeDiv form-group">
            <div class="col-sm-1"><span class="badge badge-default">3</span></div>
            <div class="col-sm-1"><input  type="checkbox" class="icheck violatedCheck" option="3" name="violated[NotResolved][3][Enabled]" value="1"></div>
            <div class="col-sm-2">{{Form::select("violated[NotResolved][3][EscalateTime]",$EscalateTime,1,array("class"=>"form-control violated3   small select2","disabled"))}}</div>
            <div class="day_time_data col-sm-8"> {{Form::select("violated[NotResolved][3][Agents][]", $agentsAll, 0 ,array("class"=>"violated3 select2","multiple"=>"multiple","id"=>"Groups","disabled"))}}</div>
          </div>
          <div class="custom_hours NotresolveOnTimeDiv form-group">
            <div class="col-sm-1"><span class="badge badge-default">4</span></div>
            <div class="col-sm-1"><input  type="checkbox" class="icheck violatedCheck" option="4" name="violated[NotResolved][4][Enabled]" value="1"></div>
            <div class="col-sm-2">{{Form::select("violated[NotResolved][4][EscalateTime]",$EscalateTime,1,array("class"=>"form-control violated4   small select2","disabled"))}}</div>
            <div class="day_time_data col-sm-8">{{Form::select("violated[NotResolved][4][Agents][]", $agentsAll, 0 ,array("class"=>"violated4 select2","multiple"=>"multiple","id"=>"Groups","disabled"))}} </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
	 jQuery(document).ready(function ($) {
    
	 $(".save.btn").click(function (ev) {
            $("#form-sla-add").submit();
            $(this).button('Loading');
        });
		
	jQuery('.violatedCheck').on('ifChecked', function(event){	 
			  var selected_option = 	$(this).attr('option');
			  if(selected_option){				 
					$('.violated'+selected_option).prop('disabled',false);
			  }
		});
		
		$('#NotrespondOnTime').change(function(e) {
            $('.NotrespondOnTimeDiv').toggle();
        });
		
		$('#NotresolveOnTime').change(function(e) {
            $('.NotresolveOnTimeDiv').toggle();
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
		   var formData = $($('#form-sla-add')[0]).serializeArray();	
			var url	 	 = baseurl+"/tickets/sla_policies/store";   
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