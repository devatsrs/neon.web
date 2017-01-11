<br>
<hr>
<div class="row row margin-top margin-bottom">
  <div class="col-md-2">DropDown Items</div>
  <div class="col-md-2 pull-right"> <a  field_type="{{$field}}"  class="feild_choice_add btn btn-primary"> <i class="entypo-plus"></i> Add New </a> </div>
</div>
<?php if($field =='default_status'){ ?>
<div class="row">
  <div class="col-md-12">
    <div class="form-group">
      <div class="col-md-2">&nbsp;</div>
      <div class="col-md-2">For Agents</div>
      <div class="col-md-2">&nbsp;</div>
      <div class="col-md-2">For Customers</div>
      <div class="col-md-2">&nbsp;</div>
      <div class="col-md-2">SLA timer</div>
    </div>
  </div>
</div>
<?php } ?>
<ul class="sortable-list field_choices_ui board-column-list list-unstyled ui-sortable margin-top" data-name="closedwon">
  @foreach($values as $key => $valuesData) 
  @if(!empty($valuesData))
  <?php 
  if($field =='default_status'){ 
   ?>
  <li class="tile-stats sortable-item count-cards choices_field_li choices_field_li_data_{{$valuesData->ValuesID}}"   data-id="{{$valuesData->ValuesID}}">
    <div class="row">
      <div class="col-md-12">
        <div class="form-group">
          <div class="col-md-1 margin-top">
            <?php if($valuesData->FieldType==Ticketfields::FIELD_TYPE_DYNAMIC){ ?>
            <button type="button"  title="Delete Field" field_type="{{$field}}"  del_data_id="{{$valuesData->ValuesID}}" class="btn feild_choice_delete btn-default btn-xs"> <i class="entypo-cancel"></i> </button>
            <?php } ?>
          </div>
          <div class="col-md-4">
            <input type="text" name="title" class="form-control"  <?php if($valuesData->FieldType==Ticketfields::FIELD_TYPE_STATIC){echo "readonly";} ?> value="{{$valuesData->Title}}">
            <input type="hidden"  name="ValuesID" class="form-control"  value="{{$valuesData->ValuesID}}">
          </div>
          <div class="col-md-4">
            <input type="text" name="titlecustomer" class="form-control" value="{{$valuesData->TitleCustomer}}">
          </div>
          <div class="col-md-1">&nbsp;</div>
          <div class="col-md-2">
            <div class="make-switch switch-small">
              <input type="checkbox" value="1" name="Stop_sla_timer"  @if($valuesData->Stop_sla_timer == 1 )checked=""@endif>
              </div>
          </div>
        </div>
      </div>
    </div>
  </li>
  <?php }else{ ?>
  <li class="tile-stats sortable-item count-cards choices_field_li choices_field_li_data_{{$valuesData->ValuesID}}"   data-id="{{$valuesData->ValuesID}}">
    <div class="row">
      <div class="col-md-12">
        <div class="form-group">
          <div class="col-md-1 margin-top">
            <button type="button"  title="Delete Field" field_type="{{$field}}"  del_data_id="{{$valuesData->ValuesID}}" class="btn feild_choice_delete btn-default btn-xs"> <i class="entypo-cancel"></i> </button>
          </div>
          <div class="col-md-11">
            <input type="text" name="title" class="form-control" value="{{$valuesData->Title}}">
            <input type="hidden" name="ValuesID" class="form-control" value="{{$valuesData->ValuesID}}">
          </div>
        </div>
      </div>
    </div>
  </li>
  <?php } ?>
  @endif
  @endforeach
</ul>
