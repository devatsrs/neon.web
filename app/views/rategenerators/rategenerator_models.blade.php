<div class="modal fade" id="modal-update-rate" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="update-rate-generator-form" method="post" >
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Update Rate Table</h4>
        </div>
        <div class="modal-body">
          <div class="row" id="RateTableIDid">
            <div class="col-md-12">
              <div class="form-group">
                <label for="field-4" class="control-label">Select Rate Table</label>
                <div id="DropdownRateTableID"> </div>
              </div>
            </div>
          </div>
          <div class="row" id="RateTableNameid">
            <div class="col-md-12">
              <div class="form-group" >
                <label for="field-4" class="control-label">Rate Table Name</label>
                <input type="text" name="RateTableName" class="form-control"  value="" />
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group" >
                <label for="field-4" class="control-label">Effective Date</label>
                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}"  data-date-format="yyyy-mm-dd" value="" />
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="RateGeneratorID" value="">
          <button type="submit"  class="save TrunkSelect btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Ok </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="modal-delete-rategenerator" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="delete-rate-generator-form" method="post" >
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Delete Rate Generator cron job</h4>
        </div>
        <div class="modal-body">
          <div class="container col-md-12"></div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="RateGeneratorID" value="">
          <button id="rategenerator-select"  class="save TrunkSelect btn btn-danger btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-trash"></i> Delete </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php if(!isset($id)){$id=0;} ?>
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