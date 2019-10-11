<div class="modal fade in" id="modal-oneofcharge">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="oneofcharge-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Additional Charges</h4>
                </div>
                <div class="modal-body">
                    <div class="row hide">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">One of charge</label>
                                {{Form::select('ProductID',$products,'',array("class"=>"select2 product_dropdown"))}}
                                @if(isset($AccountService))
                                    <input type="hidden" name="AccountServiceID" value="{{$AccountService->AccountServiceID}}">
                                @else
                                    <input type="hidden" name="AccountServiceID" value="0" />
                                @endif
                                <input type="hidden" name="AccountOneOffChargeID" />
                                <input type="hidden" name="TaxAmount" />
                                <input type="hidden" name="ServiceID" value="{{$ServiceID}}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Description*</label>
                                <input type="text" name="Description" class="form-control" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Qty*</label>
                                <input type="number" name="Qty" min="0" class="form-control Qty" value="1" data-min="1" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Date*</label>
                                <input type="text" name="Date" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value=""   />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-521" class="control-label">Currency*</label>
                                {{ Form::select('CurrencyID', Currency::getCurrencyDropdownIDList(), '', array("class"=>"select2 small")) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Price*</label>
                                <input type="text" name="Price" min="0" class="form-control" value="0"   />
                            </div>
                        </div>
                    </div>
                    <div class="row tax  hide">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Tax 1</label>
                                {{Form::SelectExt(
                                            [
                                            "name"=>"TaxRateID",
                                            "data"=>$taxes,
                                            "selected"=>'',
                                            "value_key"=>"TaxRateID",
                                            "title_key"=>"Title",
                                            "data-title1"=>"data-amount",
                                            "data-value1"=>"Amount",
                                            "data-title2"=>"data-status",
                                            "data-value2"=>"FlatStatus",
                                            "class" =>"select2 small TaxRateID",
                                            ]
                                    )}}
                            </div>
                        </div>
                    </div>

                    <div class="row tax  hide">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Tax 2</label>
                                {{Form::SelectExt(
                                            [
                                            "name"=>"TaxRateID2",
                                            "data"=>$taxes,
                                            "selected"=>'',
                                            "value_key"=>"TaxRateID",
                                            "title_key"=>"Title",
                                            "data-title1"=>"data-amount",
                                            "data-value1"=>"Amount",
                                            "data-title2"=>"data-status",
                                            "data-value2"=>"FlatStatus",
                                            "class" =>"select2 small TaxRateID2",
                                            ]
                                    )}}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Discount</label>
                                <input type="text" name="DiscountAmount" class="form-control" value=""  />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Discount Type</label>
                                {{ Form::select('DiscountType', array('Flat' => 'Flat', 'Percentage' => 'Percentage') ,'', array("class"=>"form-control") ) }}
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>