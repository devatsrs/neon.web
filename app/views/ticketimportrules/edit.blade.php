@extends('layout.main')
@section('content')
<style>
.day_time_data .col-sm-4{padding-right:0px !important; width:25%; } 
.condtion_border li:hover {cursor:all-scroll; }
.action_border li:hover {cursor:all-scroll; }
.popover-primary{padding:4px 5px !important; font-size:11px !important; line-height:1.5 !important; }
</style>
<ol class="breadcrumb bc-3">
  <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li> <a href="{{URL::to('/tickets/importrules')}}">Import Rules</a> </li>
  <li><a><span>{{importrules_dropbox($EditImportData->TicketImportRuleID)}}</span></a></li>
  <li class="active"> <strong>Edit Import Rule</strong> </li>
</ol>
<h3>Edit Import Rules</h3>
<div class="panel-title"> @include('includes.errors')
  @include('includes.success') </div>
<p style="text-align: right;">
  <button type='button' class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
  <a href="{{URL::to('/tickets/importrules')}}" class="btn btn-danger btn-sm btn-icon icon-left"> <i class="entypo-cancel"></i> Close </a> </p>
<br>
<div class="row">
  <div class="col-md-12">
    <form role="form" id="form-import-rules" method="post"  class="form-horizontal form-groups-bordered">
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title">Detail</div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body">
          <div class="form-group">
            <label for="GroupName" class="col-sm-3 control-label">Title</label>
            <div class="col-sm-9">
              <input type="text" name='Title' class="form-control" id="Title" placeholder="Title" value="{{$EditImportData->Title}}">
            </div>
          </div>
          <div class="form-group">
            <label for="GroupDescription" class="col-sm-3 control-label">Description</label>
            <div class="col-sm-9">
              <textarea id="Description" name="Description" class="form-control" placeholder="Description">{{$EditImportData->Description}}</textarea>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Status</label>
            <div class="col-sm-9">
              <p class="make-switch switch-small">
                <input id="Status" name="Status" type="checkbox" @if($EditImportData->Status>0) checked @endif value="1"> </p>
            </div>
          </div>
        </div>
      </div>
      <div class="panel panel-primary import_conditions" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title">Conditions <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="When a ticket satisfies these conditions." data-original-title="Conditions" class="label label-info popover-primary">?</span>
            <button type="button" title="Add New" id="add_new_condition" class="btn btn-primary btn-xs ">+</button>
          </div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body">
          <div class="custom_hours form-group">
            <div class="col-sm-3">
              <input tabindex="7" class="icheck" value="{{TicketImportRule::MATCH_ANY}}" type="radio" name="Match" @if($EditImportData->
              Match==TicketImportRule::MATCH_ANY) checked @endif >
              <label for="HelpdeskHoursFixed">Match <strong>ANY</strong> of the below</label>
            </div>
            <div class="col-sm-3">
              <input tabindex="8" class="icheck" value="{{TicketImportRule::MATCH_ALL}}" type="radio"  name="Match"  @if($EditImportData->
              Match==TicketImportRule::MATCH_ALL) checked @endif>
              <label for="HelpdeskHoursCustom">Match <strong>ALL</strong> of the below</label>
            </div>
          </div>
          <ul class="condtion_border sortable-list  list-unstyled ui-sortable">
            <?php if(count($EditImportCondition)>0){ 
		  	foreach($EditImportCondition as $key=>$EditImportConditionData){
				$counter = $key+1;
				$sp = 0;
				//print_r($EditImportConditionData); exit;
				 ?>
            <li class="sortable-item">
              <div class="custom_hours condition_sort_list form-group">
                <div class="col-sm-1">
                @if($key>0)
                <a title="Delete" class="btn btn-danger conditionentry clickable btn-xs btn-default btn-sm"><i class="entypo-trash"></i></a>
                @endif
                </div>
                <div class="col-sm-3">
                  <select name="condition[{{$counter}}][rule_condition]" class="form-control select2 rule_condition drpdown">
                    <option  selected value="">Select Condition</option>
                    <?php foreach($Conditions as $ConditionsData){
						$conditionDbData  = TicketImportRuleConditionType::find($EditImportConditionData->TicketImportRuleConditionTypeID);						
						$value = $EditImportConditionData->Value;
						if(in_array($conditionDbData->Condition,TicketImportRuleConditionType::$DifferentCondtionsArray))
						{	
							$sp    = 1; 	
							$value = explode(",",$EditImportConditionData->Value);
						}
						 ?>
                    <?php if(!in_array($ConditionsData->Condition,TicketImportRuleConditionType::$DifferentCondtionsArray)){ ?>
                    <option <?php if($EditImportConditionData->TicketImportRuleConditionTypeID==$ConditionsData->TicketImportRuleConditionTypeID){echo "selected";} ?>   condition="condition_match_all" condition_value="condition_value_all"  value="<?php echo $ConditionsData->TicketImportRuleConditionTypeID; ?>"><?php echo $ConditionsData->ConditionText; ?></option>
                    <?php }else{ ?>
                    <option <?php if($EditImportConditionData->TicketImportRuleConditionTypeID==$ConditionsData->TicketImportRuleConditionTypeID){echo "selected";} ?>  condition="condition_match_sp" condition_value="{{TicketImportRuleConditionType::$DifferentCondtionsArrayValue[$ConditionsData->Condition]}}"  value="<?php echo $ConditionsData->TicketImportRuleConditionTypeID; ?>"><?php echo $ConditionsData->ConditionText; ?></option>
                    <?php } } ?>
                  </select>
                </div>
                <div class="col-sm-3  condition_match condition_match_all"> {{Form::select("condition[$counter][rule_match_all]",TicketImportRule::$OperandDropDownitemsAll,$EditImportConditionData->Operand,array("class"=>"form-control condtions_val_fields select2 small drpdown "))}} </div>
                <div class="col-sm-3 condition_match hidden condition_match_sp"> {{Form::select("condition[$counter][rule_match_sp]",TicketImportRule::$OperandDropDownitemsSpecific,$EditImportConditionData->Operand,array("class"=>"form-control select2 condtions_val_fields small drpdown "))}} </div>
                <div class="col-sm-4 condition_value_all condition_value">
                  <input type="text" class="form-control condtions_val_fields" value="@if($sp<1){{$value}}@endif" sp="{{$sp}}"  name="condition[{{$counter}}][condition_value]" placeholder="Enter a value">
                </div>
                <div class="col-sm-4 condition_value hidden condition_value_priority"> {{Form::select("condition[$counter][condition_value][]",$priority,$value,array("class"=>"form-control condtions_val_fields drpdown select2 ","multiple"=>"multiple"))}} </div>
                <div class="col-sm-4 condition_value hidden condition_value_status"> {{Form::select("condition[$counter][condition_value][]",$status,$value,array("class"=>"form-control condtions_val_fields drpdown  select2","multiple"=>"multiple"))}} </div>
                <div class="col-sm-4 condition_value hidden condition_value_group"> {{Form::select("condition[$counter][condition_value][]",$Groups,$value,array("class"=>"form-control condtions_val_fields drpdown select2 ","multiple"=>"multiple"))}} </div>
                <div class="col-sm-4 condition_value hidden condition_value_agent"> {{Form::select("condition[$counter][condition_value][]",$agentsAll,$value,array("class"=>"form-control condtions_val_fields drpdown select2 ","multiple"=>"multiple"))}} </div>
                <input type="hidden" name="condition[{{$counter}}][condition_order]" value="{{$EditImportConditionData->Order}}" class="condition_order"  />
              </div>
            </li>
            <?php } } ?>
          </ul>
        </div>
      </div>
      <div class="panel panel-primary import_actions" data-collapsed="0">
        <div class="panel-heading">
          <div class="panel-title">Actions <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Perform these actions
." data-original-title="Actions" class="label label-info popover-primary">?</span>
            <button type="button" title="Add New" id="add_new_rule" class="btn btn-primary btn-xs ">+</button>
          </div>
          <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
        </div>
        <div class="panel-body">
          <ul class="action_border action-sortable-list  list-unstyled ui-sortable">
          <?php
		  if(count($EditImportAction)>0){ 
		  	foreach($EditImportAction as $key=>$EditImportActionData){
				$counter = $key+1;
				$value = $EditImportActionData->Value; 
		   ?>
           <li class="sortable-item">
  <div class="custom_hours form-group">
    <div class="col-sm-1">
    @if($key>0)
    <a title="Delete" class="btn btn-danger actionentry clickable btn-xs btn-default btn-sm"><i class="entypo-trash"></i></a>
    @endif
    </div>
    <div class="col-sm-3">
      <select name="rule[{{$counter}}][rule_action]" class="form-control rule_action drpdown">
        <option value="">Select Action</option>
         <?php foreach($Rules as $RulesData){ ?>       
		 <option  <?php if($EditImportActionData->TicketImportRuleActionTypeID==$RulesData->TicketImportRuleActionTypeID){echo "selected";} ?>   condition="<?php echo  TicketImportRuleActionType::$ActionArrayValue[$RulesData->Action]; ?>" value="<?php echo $RulesData->TicketImportRuleActionTypeID; ?>"><?php echo $RulesData->ActionText; ?></option>
		<?php } ?>      
      </select>
    </div>
    <div class="col-sm-4 action_value hidden condition_match_priority"> {{Form::select("rule[$counter][action_value][]",$priority,$value,array("class"=>"form-control action_val_fields drpdown "))}} </div>
    <div class="col-sm-4 action_value hidden condition_match_type"> {{Form::select("rule[$counter][action_value][]",$type,$value,array("class"=>"form-control action_val_fields drpdown"))}} </div>
    <div class="col-sm-4 action_value hidden condition_match_status"> {{Form::select("rule[$counter][action_value][]",$status,$value,array("class"=>"form-control action_val_fields drpdown "))}} </div>
    <div class="col-sm-4 action_value hidden condition_match_group"> {{Form::select("rule[$counter][action_value][]",$Groups,$value,array("class"=>"form-control action_val_fields drpdown "))}} </div>
    <div class="col-sm-4 action_value hidden condition_match_agent"> {{Form::select("rule[$counter][action_value][]",$agentsAll,$value,array("class"=>"form-control action_val_fields drpdown "))}} </div>
    <input type="hidden" name="rule[{{$counter}}][action_order]" value="{{$counter}}" class="action_order"  />
  </div>   
  </li>
            <?php } } ?>
          </ul>
        </div>
      </div>
    </form>
  </div>
</div>
<div class="hidden new_condition_content"> </div>
<div class="hidden new_action_content"> </div>
<script>
	 jQuery(document).ready(function ($) {
	var  SubjectOrDescriptionID = {{$SubjectOrDescriptionID}};	 
	var  new_change = 0;
	var TicketImportRuleID = '{{$EditImportData->TicketImportRuleID}}';
    function initSortableCondition(){
                // Code using $ as usual goes here.
                $('.sortable-list').sortable({
                    connectWith: '.sortable-list',
                    placeholder: 'placeholder',
                    start: function() {
                        //setting current draggable item
                        currentDrageable = $('ul.sortable-list li.dragging');
                    },
                    stop: function(ev,ui) {
                      saveOrderConditions();
                        //de-setting draggable item after submit order.
                        currentDrageable = '';
                    }
                });
     }
	 
	 function saveOrderConditions(){
			$('.condtion_border .sortable-item').each(function(index, element) {
				$(element).find('.condition_order').val(index+1);
			});	
	}
	
	function saveOrderRules(){
			$('.action_border .sortable-item').each(function(index, element) {
				$(element).find('.action_order').val(index+1);
			});	
	}
	
	function GetData(Counter,DataType){		
			var url	 	 = baseurl+"/tickets/importrules/getdata";   
			var response_html='';
		   	 $.ajax({
                url: url,
                type: 'POST',
                dataType: 'html',
				data:{Counter:Counter,DataType:DataType},
				async :false,
                success: function(response) { 
					response_html = response;
				},
			});

		return   response_html;      
	}
	 
	 function initSortableAction(){
		$('.action-sortable-list').sortable({
			connectWith: '.action-sortable-list',
			placeholder: 'placeholder',
			start: function() {
				//setting current draggable item
				currentDrageable = $('ul.action-sortable-list li.dragging');
			},
			stop: function(ev,ui) {
			    saveOrderRules();
				//de-setting draggable item after submit order.
				currentDrageable = '';
			}
		});
     }
    
	 $(".save.btn").click(function (ev) {
            $("#form-import-rules").submit();
            $(this).button('Loading');
        });
		
			
			$(document).on("click","#add_new_condition",function(event){ 
				var count_conditions 	= 	0;
				var getClass 			=   $('.import_conditions').find('.panel-body .condtion_border .custom_hours');
				getClass.each(function () {count_conditions++;}); 	 
				var html_condition 		= 	GetData(count_conditions,"condition"); //$('.new_condition_content').html();							
				$('.import_conditions').find('.panel-body .condtion_border').append(html_condition);				
				count_conditions_first = count_conditions; 
				if(count_conditions_first==0){
					$('.import_conditions').find('.panel-body .condtion_border .custom_hours').last().find('.conditionentry').remove();
				}
				$('.import_conditions').find('.panel-body .condtion_border .custom_hours').last().find('.drpdown').select2();
				$('.import_conditions').find('.panel-body .condtion_border .custom_hours').last().find('.condition_order').val(count_conditions);
				initSortableCondition();
				saveOrderConditions();
			});	
			
			
			$(document).on("click","#add_new_rule",function(event){ 
				var count_rules		 	= 	0;
				var getClass 			=   $('.import_actions').find('.panel-body .action_border .custom_hours');
				getClass.each(function () {count_rules++;}); 	
				
				var html_condition = GetData(count_rules,"rule"); //$('.new_condition_content').html();	
				
				$('.import_actions').find('.panel-body .action_border').append(html_condition);
				count_rules_first = count_rules; 
				if(count_rules_first==0){
					$('.import_actions').find('.panel-body .action_border .custom_hours').last().find('.actionentry').remove();
				}
				$('.import_actions').find('.panel-body .action_border .custom_hours').last().find('.drpdown').select2();
				$('.import_actions').find('.panel-body .action_border .custom_hours').last().find('.action_order').val(count_rules);
				initSortableAction();
				saveOrderRules();
			});	
			
			
			
			$(document).on("change",".rule_condition",function(event){
			//$('.rule_condition').on("change",function(event){		
					 		
				var condition  		  = 	$(this).find(":selected").attr('condition');
				var condition_value   = 	$(this).find(":selected").attr('condition_value'); 
				var parent_row		  = 	$(this).parent().parent();
				parent_row.find('.condition_match').hide();
				parent_row.find('.condition_value').hide();
				parent_row.find('.condition_match').find(' .condtions_val_fields').attr("disabled","disabled");				
				parent_row.find('.condition_value').find(' .condtions_val_fields').attr("disabled","disabled");
				parent_row.find('.'+condition).show(); 
				parent_row.find('.'+condition_value).show();
				
				parent_row.find('.'+condition).find('.condtions_val_fields').removeAttr("disabled"); 
				parent_row.find('.'+condition_value).find('.condtions_val_fields').removeAttr("disabled");
				
			//	parent_row.find('.'+condition).find('.condtions_val_fields').select2('destroy');
			if(new_change>0){
				parent_row.find('.'+condition).find('.condtions_val_fields').val(parent_row.find('.'+condition).find('.condtions_val_fields option:first-child').val()).trigger('change');
			}
								
				parent_row.find('.'+condition).removeClass('hidden'); 
				parent_row.find('.'+condition_value).removeClass('hidden'); 		
				
				if(parseInt(SubjectOrDescriptionID)!= $(this).val()){ console.log('not found');
					parent_row.find('.'+condition).find("select option[value*='{{TicketImportRule::START_WITH}}']").prop('disabled',true);				
					parent_row.find('.'+condition).find("select option[value*='{{TicketImportRule::END_WITH}}']").prop('disabled',true);
				}else{ console.log('found!');
					parent_row.find('.'+condition).find("select option[value*='{{TicketImportRule::START_WITH}}']").prop('disabled',false);				
					parent_row.find('.'+condition).find("select option[value*='{{TicketImportRule::END_WITH}}']").prop('disabled',false);
				
				}
						
			});	
			
				$(document).on("change",".rule_action",function(event){
					 		
				var condition  		  = 	$(this).find(":selected").attr('condition');
				var parent_row		  = 	$(this).parent().parent();
				parent_row.find('.action_value').hide();
				parent_row.find('.action_value').find(' .action_val_fields').attr("disabled","disabled");
				
				parent_row.find('.'+condition).show(); 
				parent_row.find('.'+condition).find('.action_val_fields').removeAttr("disabled");
				parent_row.find('.'+condition).removeClass('hidden'); 
			});	
			
			$(document).on("click",".conditionentry ",function(event){
					$(this).parent().parent().remove();
			});
			
			$(document).on("click",".actionentry ",function(event){
					$(this).parent().parent().remove();
			});
	
	$("#form-import-rules").submit(function (event) {
             event.stopImmediatePropagation();
             event.preventDefault();					
		     var formData = 	$($('#form-import-rules')[0]).serializeArray();	
			 var url 	  = 	baseurl + '/tickets/importrules/' + TicketImportRuleID + '/update';
		   	 $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
				data:formData,
				async :false,
                success: function(response) {
					
                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);      
						 //window.location =  baseurl+"/tickets/importrules";               
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    $(".save.btn").button('reset');
                },
			});

        });
		
		setTimeout(TriggerChangedata(),1000);
		function startdata(){
			$('#add_new_condition').click();		
			$('#add_new_rule').click();
		}
		
		function TriggerChangedata(){			
			$('.rule_condition').change();		
			$('.rule_action').change();
			initSortableCondition();
			initSortableAction();
			new_change =1;
		}
		
	});	
		
</script> 
@stop