<?php if($DataType=='condition'){ ?>
<li class="sortable-item">
  <div class="custom_hours condition_sort_list form-group">
    <div class="col-sm-1"><a title="Delete" class="btn btn-danger conditionentry clickable btn-xs btn-default btn-sm"><i class="entypo-trash"></i></a></div>
    <div class="col-sm-3">
    
      <select name="condition[{{$counter}}][rule_condition]" class="form-control rule_condition drpdown">
        <option  selected value="">Select Condition</option>
        <?php foreach($Conditions as $ConditionsData){ ?>
        <?php if(!in_array($ConditionsData->Condition,TicketImportRuleConditionType::$DifferentCondtionsArray)){ ?>
        <option  condition="condition_match_all" condition_value="condition_value_all"  value="<?php echo $ConditionsData->TicketImportRuleConditionTypeID; ?>"><?php echo $ConditionsData->ConditionText; ?></option>
        <?php }else{ ?>
        <option  condition="condition_match_sp" condition_value="{{TicketImportRuleConditionType::$DifferentCondtionsArrayValue[$ConditionsData->Condition]}}"  value="<?php echo $ConditionsData->TicketImportRuleConditionTypeID; ?>"><?php echo $ConditionsData->ConditionText; ?></option>
		<?php } } ?>              
      </select>
      
    </div>
    <div class="col-sm-3  condition_match condition_match_all">
    {{Form::select("condition[$counter][rule_match_all]",TicketImportRule::$OperandDropDownitemsAll,array(),array("class"=>"form-control condtions_val_fields small drpdown "))}}    </div>
    <div class="col-sm-3 condition_match hidden condition_match_sp">
    {{Form::select("condition[$counter][rule_match_sp]",TicketImportRule::$OperandDropDownitemsSpecific,array(),array("class"=>"form-control condtions_val_fields small drpdown "))}}
    </div>
    <div class="col-sm-4 condition_value_all condition_value">
      <input type="text" class="form-control condtions_val_fields"  name="condition[{{$counter}}][condition_value]" placeholder="Enter a value" value="">
    </div>
    <div class="col-sm-4 condition_value hidden condition_value_priority"> {{Form::select("condition[$counter][condition_value][]",$priority,array(),array("class"=>"form-control condtions_val_fields drpdown ","multiple"=>"multiple"))}} </div>
    <div class="col-sm-4 condition_value hidden condition_value_status"> {{Form::select("condition[$counter][condition_value][]",$status,array(),array("class"=>"form-control condtions_val_fields drpdown ","multiple"=>"multiple"))}} </div>
    <div class="col-sm-4 condition_value hidden condition_value_group"> {{Form::select("condition[$counter][condition_value][]",$Groups,array(),array("class"=>"form-control condtions_val_fields drpdown ","multiple"=>"multiple"))}} </div>
    <div class="col-sm-4 condition_value hidden condition_value_agent"> {{Form::select("condition[$counter][condition_value][]",$agentsAll,array(),array("class"=>"form-control condtions_val_fields drpdown ","multiple"=>"multiple"))}} </div>
    <input type="hidden" name="condition[{{$counter}}][condition_order]" value="{{$counter}}" class="condition_order"  />
  </div>  
  </li>
  <?php }else if($DataType=='rule'){ ?>
  <li class="sortable-item">
  <div class="custom_hours form-group">
    <div class="col-sm-1"><a title="Delete" class="btn btn-danger actionentry clickable btn-xs btn-default btn-sm"><i class="entypo-trash"></i></a></div>
    <div class="col-sm-3">
      <select name="rule[{{$counter}}][rule_action]" class="form-control rule_action drpdown">
        <option  selected value="">Select Action</option>
         <?php foreach($Rules as $RulesData){ ?>       
		 <option    condition="<?php echo  TicketImportRuleActionType::$ActionArrayValue[$RulesData->Action]; ?>" value="<?php echo $RulesData->TicketImportRuleActionTypeID; ?>"><?php echo $RulesData->ActionText; ?></option>
		<?php } ?>      
      </select>
    </div>
    <div class="col-sm-4 action_value hidden condition_match_priority"> {{Form::select("rule[$counter][action_value][]",$priority,array(),array("class"=>"form-control action_val_fields drpdown "))}} </div>
    <div class="col-sm-4 action_value hidden condition_match_type"> {{Form::select("rule[$counter][action_value][]",$type,array(),array("class"=>"form-control action_val_fields drpdown"))}} </div>
    <div class="col-sm-4 action_value hidden condition_match_status"> {{Form::select("rule[$counter][action_value][]",$status,array(),array("class"=>"form-control action_val_fields drpdown "))}} </div>
    <div class="col-sm-4 action_value hidden condition_match_group"> {{Form::select("rule[$counter][action_value][]",$Groups,array(),array("class"=>"form-control action_val_fields drpdown "))}} </div>
    <div class="col-sm-4 action_value hidden condition_match_agent"> {{Form::select("rule[$counter][action_value][]",$agentsAll,array(),array("class"=>"form-control action_val_fields drpdown "))}} </div>
    <input type="hidden" name="rule[{{$counter}}][action_order]" value="{{$counter}}" class="action_order"  />
  </div>   
  </li>
  <?php } ?>