@extends('layout.main')
@section('content')
<div id="content">
  <ol class="breadcrumb bc-3">
    <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
    <li> <a href="{{URL::to('ticketsfields')}}">Ticket Fields</a> </li>
  </ol>
  <h3>Ticket Fields</h3>
  <div class="clear"></div>
  <div class="row dropdown">
    <div  class="col-md-12">
      <div class="input-group-btn pull-right" style="width:86px;">
        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Add New <span class="caret"></span></button>
        <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
          <li> <a class="add_new_field" field_type="custom_text"  	  FieldDomType="text" 		href="javascript:void(0)">  <span>Textbox</span> </a> </li>
          <li> <a class="add_new_field" field_type="custom_paragraph" FieldDomType="paragraph" 	href="javascript:void(0)">  <span>Textarea</span> </a> </li>
          <li> <a class="add_new_field" field_type="custom_checkbox"  FieldDomType="checkbox" 	href="javascript:void(0)">  <span>Checkbox</span> </a> </li>
          <li> <a class="add_new_field" field_type="custom_number"    FieldDomType="number" 	href="javascript:void(0)">  <span>Number</span> </a> </li>
          <li> <a class="add_new_field" field_type="custom_dropdown"  FieldDomType="dropdown"	href="javascript:void(0)">  <span>Dropdown</span> </a> </li>
          <li> <a class="add_new_field" field_type="custom_date"  	  FieldDomType="date" 		href="javascript:void(0)">  <span>Date</span> </a> </li>
          <li> <a class="add_new_field" field_type="custom_decimal"   FieldDomType="decimal" 	href="javascript:void(0)">  <span>Decimal</span> </a> </li>
        </ul>
      </div>
      <!-- /btn-group --> 
    </div>
    <div class="clear"></div>
  </div>
  <br>
  <section class="deals-board" >
    <form id="TicketfieldsDataFrom" method="POST" />
    
    <div id="board-start" class="board"  > </div>
    <input type="hidden" name="main_fields_sort" id="main_fields_sort" value="">
    <input type="hidden" name="deleted_main_fields" id="deleted_main_fields" value="">
    </form>
  </section>
  @include('ticketsfields.fields_css_js') </div>
@stop
@section('footer_ext')
    @parent
<div class="modal fade" id="edit-modal-ticketfield">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="edit-ticketfields-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 class="modal-title">Edit Field</h3>
        </div>
        <div class="modal-body">          
          <div class="row field_model_behavoiur">
            <div class="col-md-6">
              <div class="form-group">
                <label  class="control-label col-sm-12"><h4><strong>For Agents</strong></h4></label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="control-label col-sm-12"><h4><strong>For Customers</strong></h4></label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-6">Required when submitting the form</label>
                <div class="col-sm-6" id="AgentReqSubmitSwitch">
                  <p  class="make-switch switch-small">
                    <input id="required" name="required" type="checkbox" value="1">
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-6">Display to customer</label>
                <div class="col-sm-6" id="CustomerDisplaySwitch">
                  <p class="make-switch switch-small">
                    <input id="visible_in_portal" name="visible_in_portal" type="checkbox" value="1">
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-6">Required when closing the ticket</label>
                <div class="col-sm-6" id="AgentReqCloseSwitch">
                  <p class="make-switch switch-small">
                    <input id="required_for_closure" name="required_for_closure" type="checkbox" value="1">
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-6">Customer can edit</label>
                <div class="col-sm-6" id="CustomerEditSwitch">
                  <p class="make-switch switch-small">
                    <input id="editable_in_portal" name="editable_in_portal" type="checkbox" value="1">
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <div class="col-sm-12">&nbsp;</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-6">Required when submitting the form</label>
                <div class="col-sm-6" id="CustomerReqSubmitSwitch">
                  <p class="make-switch switch-small">
                    <input id="required_in_portal" name="required_in_portal" type="checkbox" value="1">
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">&nbsp;<br>
              <br>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-2">Label</label>
                <div class="col-sm-10">
                  <input type="text" name="label" class="form-control" id="label">
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-2">Label</label>
                <div class="col-sm-10">
                  <input type="text" name="label_in_portal" class="form-control" id="label_in_portal">
                </div>
              </div>
            </div>
          </div>
          <div id="choices_item" class="choices_item margin-top"> </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="id">
          <input type="hidden" name="choices" id="modalfieldchoicesdata" value="">
          <input type="hidden" name="field_type" id="field_type" value="">
          <input type="hidden" name="type" id="type" value="">
          <input type="hidden" name="position" id="position" value="">
          <input type="hidden" name="deleted_choices" id="deleted_choices" value="">
          <button type="submit" id="field_model_btn"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- -->
<div class="modal fade" id="add-modal-ticketfield">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="add-ticketfields-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Add New Field</h4>
        </div>
        <div class="modal-body">          
          <div class="row field_model_behavoiur">
            <div class="col-md-6">
              <div class="form-group">
                <label  class="control-label col-sm-12"><h4><strong>For Agents</strong></h4></label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="control-label col-sm-12"><h4><strong>For Customers</strong></h4></label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-6">Required when submitting the form</label>
                <div class="col-sm-6" id="AgentReqSubmitSwitch">
                  <p  class="make-switch switch-small">
                    <input id="required" name="required" type="checkbox" value="1">
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-6">Display to customer</label>
                <div class="col-sm-6" id="CustomerDisplaySwitch">
                  <p class="make-switch switch-small">
                    <input id="visible_in_portal" name="visible_in_portal" type="checkbox" value="1">
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-6">Required when closing the ticket</label>
                <div class="col-sm-6" id="AgentReqCloseSwitch">
                  <p class="make-switch switch-small">
                    <input id="required_for_closure" name="required_for_closure" type="checkbox" value="1">
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-6">Customer can edit</label>
                <div class="col-sm-6" id="CustomerEditSwitch">
                  <p class="make-switch  switch-small">
                    <input id="editable_in_portal" name="editable_in_portal" disabled type="checkbox" value="1">
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <div class="col-sm-12">&nbsp;</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-6">Required when submitting the form</label>
                <div class="col-sm-6" id="CustomerReqSubmitSwitch">
                  <p class="make-switch switch-small">
                    <input id="required_in_portal" name="required_in_portal" disabled type="checkbox" value="1">
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">&nbsp;<br>
              <br>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-2">Label</label>
                <div class="col-sm-10">
                  <input type="text" name="label" class="form-control" id="label">
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-2">Label</label>
                <div class="col-sm-10">
                  <input type="text" name="label_in_portal" class="form-control" id="label_in_portal">
                </div>
              </div>
            </div>
          </div>
          
        </div>
        <div class="modal-footer">
          <input type="hidden" name="field_type" id="field_type" value="">
          <input type="hidden" name="type" id="type" value="">
          <input type="hidden" name="position" id="position" value="">
          <button type="submit" id="field_model_btn_add"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- -->
<div class="hidden dropdown_fields_add_row">
  <li class="tile-stats sortable-item count-cards choices_field_li"   data-id="">
    <div class="row">
      <div class="col-md-12">
        <div class="form-group">
          <div class="col-md-1 margin-top">
            <button type="button"  title="Delete Field" field_type=""  del_data_id="" class="btn feild_choice_delete btn-red btn-xs"> <i class="entypo-trash"></i> </button>
          </div>
          <div class="col-md-11">
            <input type="text" name="title" class="form-control" value="">
          </div>
        </div>
      </div>
    </div>
  </li>
</div>
<div class="hidden dropdown_fields_add_row_status">
  <li class=" tile-stats sortable-item count-cards choices_field_li " data-id="">
    <div class="row">
      <div class="col-md-12">
        <div class="form-group">
          <div class="col-md-1 margin-top">
            <button type="button" title="Delete Field" field_type="default_status" del_data_id="" class="btn feild_choice_delete btn-red btn-xs"> <i class="entypo-trash"></i> </button>
          </div>
          <div class="col-md-4">
            <input name="title" class="form-control"  value="" type="text">
          </div>
          <div class="col-md-4">
            <input name="titlecustomer" class="form-control" value="" type="text">
          </div>
          <div class="col-md-1">&nbsp;</div>
          <div class="col-md-2">
            <div class="make-switch_sla switch-small">
              <input type="checkbox" name="Stop_sla_timer" value="1" checked >
            </div>
          </div>
        </div>
      </div>
    </div>
  </li>
</div>
@stop 