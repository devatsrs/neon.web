<!-- default start -->

<div class="hidden default-row">
  <div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
      <div class="panel-title"> Behavior </div>
      <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
    </div>
    <div class="panel-body">
      <div class="row">
        <div class="col-md-6 margin-top">
          <div class="form-group">
            <label  class="control-label">For Agents </label>
          </div>
          <div class="form-group">
            <input id="AgentReqSubmit" class="icheck1"  name="field[AgentReqSubmit]" type="checkbox" value="1"  >
            <label for="AgentReqSubmit"  class="control-label">Required when submitting the form </label>
          </div>
          <div class="form-group">
            <input id="AgentReqClose" class="icheck1"  name="field[AgentReqClose]" type="checkbox" value="1"  >
            <label for="AgentReqClose"  class="control-label">Required when closing the ticket </label>
          </div>
          <div class="form-group">
            <input id="AgentCcDisplay" class="icheck1"  name="field[AgentCcDisplay]" type="checkbox" value="1"  >
            <label for="AgentCcDisplay"  class="control-label">Display CC Field</label>
          </div>
        </div>
        <div class="col-md-6 margin-top">
          <div class="form-group">
            <label  class="control-label">For Customers </label>
          </div>
          <div class="form-group">
            <input id="CustomerDisplay" class="icheck1"  name="field[CustomerDisplay]" type="checkbox" value="1"  >
            <label for="CustomerDisplay"   class="control-label">Display to customer </label>
          </div>
          <div class="form-group">
            <input id="CustomerEdit" class="icheck1"  name="field[CustomerEdit]" type="checkbox" value="1"  >
            <label for="CustomerEdit"   class="control-label">Customer can edit </label>
          </div>
          <div class="form-group">
            <input  id="CustomerReqSubmit" class="icheck1"  name="field[CustomerReqSubmit]" type="checkbox" value="1"  >
            <label  for="CustomerReqSubmit"  class="control-label">Required when submitting the form </label>
          </div>
          <div class="form-group">
            <input id="CustomerCcDisplay" class="icheck1"  name="field[CustomerCcDisplay]" type="checkbox" value="1"  >
            <label for="CustomerCcDisplay"  class="control-label">Display CC Field</label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- default end --> 
<!-- checkbox start -->
<div class="modal fade" id="form-checkbox-model">
  <div class="modal-dialog" style="width: 70%;">
    <div class="modal-content">
      <form id="edit-note-form" method="post">
        <div class="modal-header"><strong>Properties </strong>: <span class="fldtype"></span></div>
        <div class="modal-body">
          <div class="before_body"></div>
          <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
              <div class="panel-title"> Detail </div>
              <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
            </div>
            <div class="panel-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label  class="control-label">For Agents </label>
                  </div>
                  <div class="form-group">
                    <label  class="control-label">Label</label>
                    <input type="text"  class="form-control AgentLabel" modal_label="form-checkbox-model" id="AgentLabel" placeholder="Untitled" required name="field[AgentLabel]" value="" />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label  class="control-label">For Customers </label>
                  </div>
                  <div class="form-group">
                    <label  class="control-label">Label</label>
                    <input type="text"  class="form-control CustomerLabel" id="CustomerLabel" placeholder="Untitled" required name="field[CustomerLabel]" value="" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="note-edit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- checkbox end --> 
<!-- textbox start -->
<div class="modal fade" id="form-text-model">
  <div class="modal-dialog" style="width: 70%;">
    <div class="modal-content">
      <form id="edit-note-form" method="post">
        <div class="modal-header"><strong>Properties </strong>: <span class="fldtype"></span></div>
        <div class="modal-body">
          <div class="before_body"></div>
          <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
              <div class="panel-title"> Detail </div>
              <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
            </div>
            <div class="panel-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label  class="control-label">For Agents </label>
                  </div>
                  <div class="form-group">
                    <label  class="control-label">Label</label>
                    <input type="text"  class="form-control AgentLabel" modal_label="form-text-model" id="AgentLabel" placeholder="Untitled" required name="field[AgentLabel]" value="" />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label  class="control-label">For Customer </label>
                  </div>
                  <div class="form-group">
                    <label  class="control-label">Label</label>
                    <input type="text"  class="form-control CustomerLabel" id="CustomerLabel" placeholder="Untitled" required name="field[CustomerLabel]" value="" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="note-edit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- textbox end --> 
<!-- dropdown start -->
<div class="modal fade" id="form-dropdown-model">
  <div class="modal-dialog" style="width: 70%;">
    <div class="modal-content">
      <form id="edit-note-form" method="post">
        <div class="modal-header"><strong>Properties </strong>: <span class="fldtype"></span></div>
        <div class="modal-body">
          <div class="before_body"></div>
          <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
              <div class="panel-title"> Detail </div>
              <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
            </div>
            <div class="panel-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label  class="control-label">For Agents </label>
                  </div>
                  <div class="form-group">
                    <label  class="control-label">Label</label>
                    <input type="text"  class="form-control AgentLabel" modal_label="form-dropdown-model" id="AgentLabel" placeholder="Untitled" required name="field[AgentLabel]" value="" />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label  class="control-label">For Customer </label>
                  </div>
                  <div class="form-group">
                    <label  class="control-label">Label</label>
                    <input type="text"  class="form-control CustomerLabel" id="CustomerLabel" placeholder="Untitled" required name="field[CustomerLabel]" value="" />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="panel panel-primary fieldvalues" data-collapsed="0">
            <div class="panel-heading">
              <div class="panel-title"> Values </div>
              <div class="panel-options"> <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a> </div>
            </div>
            <div class="panel-body">
              <div class="row">
                <div class="fieldvaluescontect col-md-12">
                <div class="col-md-12">
                 <div class="col-md-2">&nbsp;</div>
                <div class="col-md-2">For Agents</div>
                <div class="col-md-2">For Customers</div>
                <div class="col-md-2">SLA timer</div>
                </div>
                  <ul id="draggableValuesList" class="draggableValuesList">
                    <li class="panel" id="">
                      <div class="panel-head"><i class="fa fa-reorder"></i></div>
                      <div class="panel-divbody">
                      <div class="col-md-12">    
                        <div class="col-md-2">
                          <input type="text"  class="form-control" value="abcvasdf1" />
                        </div>
                          <div class="col-md-2">
                          <input type="text"  class="form-control" value="abcvasdf1" />
                        </div>
                        <div class="col-md-2"></div>
                         <div class="col-md-2"> <button type="button" class="btn btn-danger"> <i class="entypo-cancel"></i> </button> </div>
                      </div>
                      </div>
                    </li>                
                    
                     
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="note-edit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- dropdown end -->