<style>
    .modal-ku {
        width: 750px;
        margin: auto;
    }
    .display{
        display:none;
    }
</style>
@section('footer_ext')
    @parent
    <div class="modal fade" id="add-new-modal-accounts">
        <div class="modal-dialog  modal-lg">
            <div class="modal-content">
                <form id="cancelation-contract">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3 class="modal-title">Cancelation Contract</h3>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Termination Fees</label>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="teminatingFee">
                                    </div>

                                    <label class="col-md-2 control-label">Cancelation Date</label>
                                    <div class="col-md-4">
                                        <input type="text" data-date-format="yyyy-mm-dd" class="form-control datepicker" name="cancelDate">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Include Termination Fees</label>
                                    <div class="col-md-4">
                                        <div class="panel-options">
                                            <div class="make-switch switch-small">
                                                <input type="checkbox"  name="incTerminationFees" value="1">
                                            </div>
                                        </div>
                                    </div>

                                    <label class="col-md-2 control-label">Include Discounts Offered</label>
                                    <div class="col-md-4">
                                        <div class="panel-options">
                                            <div class="make-switch switch-small">
                                                <input type="checkbox"  name="discountOffered" value="1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">

                                    <label class="col-md-2 control-label">Generate Invoice</label>
                                    <div class="col-md-4">
                                        <div class="panel-options">
                                            <div class="make-switch switch-small">
                                                <input type="checkbox"  name="generateInvoice" value="1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="currency-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
                        </button>
                        <button  type="button" class="btn  btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop