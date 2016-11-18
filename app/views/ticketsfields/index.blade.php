@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{ URL::to('/dashboard') }}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Ticket Fields</strong> </li>
</ol>
<h3>Ticket Fields</h3>
<div class="row">
  <div class="col-md-12">
    <div class="formbuilderdiv col-md-12">
      <div class="build-wrap">
        <div id="frmb-0-form-wrap" class="form-wrap form-builder">
          <div id="frmb-0-stage-wrap" class="stage-wrap pull-left">
            <ul id="draggablePanelList" class="frmb list-unstyled ui-sortable">
            	<?php foreach($Ticketfields as $TicketfieldsData){ 
				$arraydata 			= json_encode($TicketfieldsData);
				$TicketfieldsValues = json_encode(TicketfieldsValues::where(["FieldsID"=>$TicketfieldsData->TicketFieldsID])->orderBy('FieldOrder', 'asc')->get());
				 ?>
                
                <?php if($TicketfieldsData->FieldHtmlType==Ticketfields::$FIELD_HTML_TEXT){ ?>
                <li class="panel panel-info text-field form-field" type="text" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}">
                <div class="panel-heading">{{$TicketfieldsData->FieldName}}</div>
                <div class="panel-body">
                <div class="field-actions">
             <a id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-edit" linkid="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}"  modal_link="form-text-model" class="toggle-form btn icon-pencil" title="Edit"></a>
               @if($TicketfieldsData->FieldType==Ticketfields::$FIELD_TYPE_DYNAMIC)  <a id="del_frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}" class="del-button btn delete-confirm" title="Remove Element">×</a> @endif
               </div>                                
                <div class="prev-holder" style="display: block;">
                  <div class="fb-undefined form-group field-text-{{$TicketfieldsData->TicketFieldsID}}-preview">
                    <label for="text-{{$TicketfieldsData->TicketFieldsID}}-preview" class="fb-text-label">{{$TicketfieldsData->FieldName}}</label>                   <input disabled type="text"  name="text-{{$TicketfieldsData->TicketFieldsID}}" class="form-control" id="text-{{$TicketfieldsData->TicketFieldsID}}-preview" />
                     <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data" class="form-control" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-data" value='<?php echo $arraydata; ?>' />
                     <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data_multiple" class="form-control" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-data_multiple" value='<?php echo $TicketfieldsValues; ?>'  />
                  </div>
                </div>
                </div>
              </li><?php } ?>
              <?php if($TicketfieldsData->FieldHtmlType==Ticketfields::$FIELD_HTML_TEXTAREA){ ?>
                
              <li class="panel panel-info textarea-field form-field" type="textarea" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}">
               <div class="panel-heading">{{$TicketfieldsData->FieldName}}</div>
                <div class="panel-body">
                <div class="field-actions">
               <a id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-edit" linkid="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}" class="toggle-form btn icon-pencil" title="Edit"></a>
               @if($TicketfieldsData->FieldType==Ticketfields::$FIELD_TYPE_DYNAMIC)  <a id="del_frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}" class="del-button btn delete-confirm" title="Remove Element">×</a> @endif
               </div>                                
                <div class="prev-holder" style="display: block;">
                  <div class="fb-undefined form-group field-textarea-1479298302406-preview">
                    <label for="textarea-{{$TicketfieldsData->TicketFieldsID}}-preview" class="fb-textarea-label">{{$TicketfieldsData->FieldName}} </label>
                    <textarea disabled type="textarea" class="form-control" name="textarea-{{$TicketfieldsData->TicketFieldsID}}" id="textarea-{{$TicketfieldsData->TicketFieldsID}}-preview"></textarea>                    
                     <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data" class="form-control" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-data" value='<?php echo $arraydata; ?>' />
                      <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data_multiple" class="form-control" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-data_multiple" value='<?php echo $TicketfieldsValues; ?>'  />
                  </div>
                </div>
                </div>
              </li>
			  <?php } ?>
              <?php if($TicketfieldsData->FieldHtmlType==Ticketfields::$FIELD_HTML_CHECKBOX){ ?>
			   <li class="panel panel-info checkbox-field form-field" type="checkbox" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}">
                 <div class="panel-heading">{{$TicketfieldsData->FieldName}}</div>
                <div class="panel-body">
                <div class="field-actions">
               <a id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-edit" linkid="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}" class="toggle-form btn icon-pencil" title="Edit"></a>
               @if($TicketfieldsData->FieldType==Ticketfields::$FIELD_TYPE_DYNAMIC)  <a id="del_frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}" class="del-button btn delete-confirm" title="Remove Element">×</a> @endif
               </div>                                
                <div class="prev-holder" style="display: block;">
                  <div class="fb-undefined form-group field-checkbox-{{$TicketfieldsData->TicketFieldsID}}-preview">
                  <input type="checkbox" disabled  class="form-control" name="textarea-{{$TicketfieldsData->TicketFieldsID}}" id="textarea-{{$TicketfieldsData->TicketFieldsID}}-preview" />                  
                     <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data" class="form-control" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-data" value='<?php echo $arraydata; ?>' />
                     <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data_multiple" class="form-control" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-data_multiple" value='<?php echo $TicketfieldsValues; ?>'  />
                    <label for="checkbox-{{$TicketfieldsData->TicketFieldsID}}-preview" class="fb-checkbox-label">{{$TicketfieldsData->FieldName}}</label>                  </div>
                </div>
                </div>
              </li>
				<?php } ?>              
              <?php if($TicketfieldsData->FieldHtmlType==Ticketfields::$FIELD_HTML_TEXTNUMBER){ ?>
			  <li class="panel panel-info number-field form-field" type="number" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}">
                 <div class="panel-heading">{{$TicketfieldsData->FieldName}}</div>
                <div class="panel-body">
                <div class="field-actions">
               <a id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-edit" linkid="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}" class="toggle-form btn icon-pencil" title="Edit"></a>
               @if($TicketfieldsData->FieldType==Ticketfields::$FIELD_TYPE_DYNAMIC)  <a id="del_frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}" class="del-button btn delete-confirm" title="Remove Element">×</a> @endif
               </div>                                
                <div class="prev-holder" style="display: block;">
                  <div class="fb-undefined form-group field-number-{{$TicketfieldsData->TicketFieldsID}}-preview">
                   <label for="number-{{$TicketfieldsData->TicketFieldsID}}-preview" class="fb-number-label">{{$TicketfieldsData->FieldName}}</label>                  <input disabled type="number"  name="number-{{$TicketfieldsData->TicketFieldsID}}" class="form-control" id="number-{{$TicketfieldsData->TicketFieldsID}}-preview" />                   
                     <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data" class="form-control" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-data"  value='<?php echo $arraydata; ?>' />
                     <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data_multiple" class="form-control" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-data_multiple" value='<?php echo $TicketfieldsValues; ?>'  />
                  </div>
                </div>
                </div>
              </li>
			  <?php } ?>
              <?php if($TicketfieldsData->FieldHtmlType==Ticketfields::$FIELD_HTML_DROPDOWN){ ?>
              <li class="panel panel-info dropdown-field form-field" type="dropdown" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}">
                 <div class="panel-heading">{{$TicketfieldsData->FieldName}}</div>
                <div class="panel-body">
                <div class="field-actions">
               <a id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-edit" linkid="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}"  modal_link="form-dropdown-model" class="toggle-form btn icon-pencil" title="Edit"></a>
               @if($TicketfieldsData->FieldType==Ticketfields::$FIELD_TYPE_DYNAMIC)  <a id="del_frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}" class="del-button btn delete-confirm" title="Remove Element">×</a> @endif
               </div>                                
                <div class="prev-holder" style="display: block;">
                  <div class="fb-undefined form-group field-dropdown-{{$TicketfieldsData->TicketFieldsID}}-preview">
                    <label for="dropdown-{{$TicketfieldsData->TicketFieldsID}}-preview" class="fb-dropdown-label">{{$TicketfieldsData->FieldName}}</label>                   <select disabled  name="dropdown-{{$TicketfieldsData->TicketFieldsID}}" class="form-control" id="dropdown-{{$TicketfieldsData->TicketFieldsID}}-preview">
                    <option value="">Select</option>
                    </select>                    
                     <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data" class="form-control" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-data" value='<?php echo $arraydata; ?>' />
                         <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data_multiple" class="form-control" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-data_multiple" value='<?php echo $TicketfieldsValues; ?>'  />
                  </div>
                </div>
                </div>
              </li><?php } ?>
              <?php if($TicketfieldsData->FieldHtmlType==Ticketfields::$FIELD_HTML_DATE){ ?>
              <li class="panel panel-info date-field form-field" type="date" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}">
                 <div class="panel-heading">{{$TicketfieldsData->FieldName}}</div>
                <div class="panel-body">
                <div class="field-actions">
               <a id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-edit" linkid="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}" class="toggle-form btn icon-pencil" title="Edit"></a>
               @if($TicketfieldsData->FieldType==Ticketfields::$FIELD_TYPE_DYNAMIC)  <a id="del_frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}" class="del-button btn delete-confirm" title="Remove Element">×</a> @endif
               </div>                                
                <div class="prev-holder" style="display: block;">
                  <div class="fb-undefined form-group field-date-{{$TicketfieldsData->TicketFieldsID}}-preview">
                   <label for="date-{{$TicketfieldsData->TicketFieldsID}}-preview" class="fb-date-label">{{$TicketfieldsData->FieldName}}</label>                 <i class="fa fa-calendar"></i> <input disabled type="text"  name="date-{{$TicketfieldsData->TicketFieldsID}}" class="form-control" id="date-{{$TicketfieldsData->TicketFieldsID}}-preview" />                   
                     <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data" class="form-control" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-data" value='<?php echo $arraydata; ?>' />
                     <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data_multiple" class="form-control" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-data_multiple" value='<?php echo $TicketfieldsValues; ?>'  />
                  </div>
                </div>
                </div>
              </li>
			
			  <?php } ?>
              <?php if($TicketfieldsData->FieldHtmlType==Ticketfields::$FIELD_HTML_DECIMAL){ ?>
               <li class="panel panel-info decimal-field form-field" type="decimal" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}">
                 <div class="panel-heading">{{$TicketfieldsData->FieldName}}</div>
                <div class="panel-body">
                <div class="field-actions">
               <a id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-edit" linkid="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}" class="toggle-form btn icon-pencil" title="Edit"></a>
               @if($TicketfieldsData->FieldType==Ticketfields::$FIELD_TYPE_DYNAMIC)  <a id="del_frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}" class="del-button btn delete-confirm" title="Remove Element">×</a> @endif
               </div>                                
                <div class="prev-holder" style="display: block;">
                  <div class="fb-undefined form-group field-decimal-{{$TicketfieldsData->TicketFieldsID}}-preview">
                   <label for="date-{{$TicketfieldsData->TicketFieldsID}}-preview" class="fb-decimal-label">{{$TicketfieldsData->FieldName}}</label>                  <input disabled type="number"  name="decimal-{{$TicketfieldsData->TicketFieldsID}}" class="form-control" id="decimal-{{$TicketfieldsData->TicketFieldsID}}-preview" />                   
                     <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data" class="form-control" id="{frmb-0-fld-{$TicketfieldsData->TicketFieldsID}}-data"  value='<?php echo $arraydata; ?>' />
                 <input  type="hidden"  name="{{$TicketfieldsData->TicketFieldsID}}-data_multiple" class="form-control" id="frmb-0-fld-{{$TicketfieldsData->TicketFieldsID}}-data_multiple" value='<?php echo $TicketfieldsValues; ?>'  />
                  </div>
                </div>
                </div>
              </li>  
             <?php } ?>
                         
              <?php } ?>
            </ul>
          </div>
          <div id="frmb-0-cb-wrap" class="cb-wrap pull-right">
            <ul id="frmb-control-box" class="frmb-control ui-sortable">
              <li class="icon-checkbox" 	modal_link="form-checkbox-model" 	label="Checkbox"> <span>Checkbox</span></li>
              <li class="icon-calendar"     modal_link="form-calendar-model" 	label="Date Field"> <span>Date Field</span></li>
              <li class="icon-number"       modal_link="form-number-model" 		label="Number"> <span>Number</span></li>
              <li class="icon-radio-group"  modal_link="form-radio-model" 		label="Radio Group"> <span>Radio Group</span></li>
              <li class="icon-select"       modal_link="form-select-model" 		label="Select"> <span>Select</span></li>
              <li class="icon-text-input"   modal_link="form-text-model" 		label="Text Field"> <span>Text Field</span></li>
              <li class="icon-text-area"    modal_link="form-textarea-model" 	label="Text Area"> <span>Text Area</span></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@include('ticketsfields.fields_models')
@include('ticketsfields.fields_css_js')
@stop 