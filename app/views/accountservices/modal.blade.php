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
                                    <label class="col-md-2 control-label">Terminating Feee</label>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="teminatingFee">
                                    </div>

                                    <label class="col-md-2 control-label">Cancel Date</label>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="cancelDate">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Fee Charges </label>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="cancelationFeeCharges">
                                    </div>

                                    <label class="col-md-2 control-label">Remove Discounts </label>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="removeDiscounts">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">

                                    <label class="col-md-2 control-label">Discounts Offer</label>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="discountoffer">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                    <label class="col-md-2 control-label">Generate Invoice</label>
                                    <div class="col-md-4">
                                        <input type="checkbox" name="generateInvoice">
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